<?php
/**
 * Endpoint taxonomie headless : catégories, marques (fabricants), pays.
 * Sert la navigation et les filtres du front (données réelles PrestaShop).
 *   GET ?action=categories[&id_parent=]   -> arbre / niveau de catégories actives
 *   GET ?action=manufacturers             -> marques actives + nb de produits
 *   GET ?action=countries                 -> pays actifs (pour le formulaire d'adresse)
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';

class HfmstorefrontTaxonomyModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        $idLang = (int) $this->context->language->id;
        switch ((string) $this->in('action', 'categories')) {
            case 'categories':
                return ['categories' => $this->categories($idLang)];
            case 'menu':
                return ['menu' => $this->menuTree($idLang)];
            case 'manufacturers':
                return ['manufacturers' => $this->manufacturers($idLang)];
            case 'countries':
                return ['countries' => $this->countries($idLang)];
            default:
                return ['error' => 'unknown_action'];
        }
    }

    /** Catégories actives sous un parent (par défaut les enfants de la catégorie racine de la boutique). */
    protected function categories($idLang)
    {
        $idParent = (int) $this->in('id_parent');
        if (!$idParent) {
            $idParent = (int) Configuration::get('PS_HOME_CATEGORY') ?: (int) Configuration::get('PS_ROOT_CATEGORY');
        }
        return $this->fetchChildren($idParent, $idLang);
    }

    /** Enfants actifs directs d'une catégorie (nom + nb de produits), triés par position boutique. */
    protected function fetchChildren($idParent, $idLang)
    {
        $idShop = (int) $this->context->shop->id;
        $sql = 'SELECT c.id_category, c.id_parent, c.level_depth, cl.name, cl.link_rewrite,
                       (SELECT COUNT(*)
                          FROM ' . _DB_PREFIX_ . 'category_product cp
                          INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                              ON (ps.id_product = cp.id_product AND ps.id_shop = ' . $idShop . ')
                          WHERE cp.id_category = c.id_category
                            AND ps.active = 1
                            AND ps.visibility != "none") AS nb_products
                FROM ' . _DB_PREFIX_ . 'category c
                INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                    ON (cl.id_category = c.id_category AND cl.id_lang = ' . (int) $idLang . ' AND cl.id_shop = ' . $idShop . ')
                INNER JOIN ' . _DB_PREFIX_ . 'category_shop cs
                    ON (cs.id_category = c.id_category AND cs.id_shop = ' . $idShop . ')
                WHERE c.active = 1 AND c.id_parent = ' . (int) $idParent . '
                ORDER BY cs.position ASC';
        $out = [];
        foreach ((array) Db::getInstance()->executeS($sql) as $r) {
            $out[] = [
                'id_category' => (int) $r['id_category'],
                'id_parent' => (int) $r['id_parent'],
                'name' => $r['name'],
                'link_rewrite' => $r['link_rewrite'],
                'nb_products' => (int) $r['nb_products'],
            ];
        }
        return $out;
    }

    /**
     * Méga-menu éditorial : 4 racines curatées dans un ordre figé, chacune avec ses
     * sous-catégories actives (la 5e entrée « Promos & Top » est gérée côté front car
     * ce sont des filtres dynamiques, pas des catégories).
     *   #30  MARQUES        (les maisons)
     *   #301 Catalogue      (typologies)
     *   #302 Par zone       (zones du visage/corps)
     *   #303 Par effet      (effet recherché)
     */
    protected function menuTree($idLang)
    {
        $rootIds = [30, 301, 302, 303];
        $out = [];
        foreach ($rootIds as $rid) {
            $cat = $this->fetchCategory($rid, $idLang);
            if (!$cat) {
                continue;
            }
            $cat['children'] = $this->fetchChildren($rid, $idLang);
            $out[] = $cat;
        }
        return $out;
    }

    /** Une catégorie active (nom localisé + nb de produits actifs), ou null. */
    protected function fetchCategory($idCategory, $idLang)
    {
        $idShop = (int) $this->context->shop->id;
        $sql = 'SELECT c.id_category, c.id_parent, c.level_depth, cl.name, cl.link_rewrite,
                       (SELECT COUNT(*)
                          FROM ' . _DB_PREFIX_ . 'category_product cp
                          INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                              ON (ps.id_product = cp.id_product AND ps.id_shop = ' . $idShop . ')
                          WHERE cp.id_category = c.id_category
                            AND ps.active = 1
                            AND ps.visibility != "none") AS nb_products
                FROM ' . _DB_PREFIX_ . 'category c
                INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                    ON (cl.id_category = c.id_category AND cl.id_lang = ' . (int) $idLang . ' AND cl.id_shop = ' . $idShop . ')
                WHERE c.active = 1 AND c.id_category = ' . (int) $idCategory;
        $r = Db::getInstance()->getRow($sql, false);
        if (!$r) {
            return null;
        }
        return [
            'id_category' => (int) $r['id_category'],
            'id_parent' => (int) $r['id_parent'],
            'name' => $r['name'],
            'link_rewrite' => $r['link_rewrite'],
            'nb_products' => (int) $r['nb_products'],
        ];
    }

    /** Marques ayant au moins un produit actif, avec comptage réel (trié par volume). */
    protected function manufacturers($idLang)
    {
        $sql = 'SELECT m.id_manufacturer, m.name, COUNT(p.id_product) AS nb_products
                FROM ' . _DB_PREFIX_ . 'manufacturer m
                INNER JOIN ' . _DB_PREFIX_ . 'product p ON (p.id_manufacturer = m.id_manufacturer AND p.active = 1)
                WHERE m.active = 1
                GROUP BY m.id_manufacturer
                HAVING nb_products > 0
                ORDER BY nb_products DESC, m.name ASC';
        $rows = Db::getInstance()->executeS($sql);
        $out = [];
        foreach ((array) $rows as $r) {
            $out[] = [
                'id_manufacturer' => (int) $r['id_manufacturer'],
                'name' => $r['name'],
                'nb_products' => (int) $r['nb_products'],
            ];
        }
        return $out;
    }

    protected function countries($idLang)
    {
        $rows = Country::getCountries($idLang, true);
        $out = [];
        foreach ((array) $rows as $r) {
            $out[] = [
                'id_country' => (int) $r['id_country'],
                'name' => $r['country'],
                'iso_code' => $r['iso_code'],
            ];
        }
        return $out;
    }
}
