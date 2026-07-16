<?php
/**
 * Endpoint taxonomie headless : catégories, marques (fabricants), pays.
 * Sert la navigation et les filtres du front (données réelles PrestaShop).
 *   GET ?action=categories[&id_parent=]   -> arbre / niveau de catégories actives
 *   GET ?action=manufacturers             -> marques actives + nb de produits
 *   GET ?action=countries                 -> pays actifs (pour le formulaire d'adresse)
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/cache.php';

class HfmstorefrontTaxonomyModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $action = (string) $this->in('action', 'categories');

        // Lecture publique, identique pour tous -> cacheable (tag taxonomy, TTL 3600).
        // Clé = action + params discriminants + id_lang + id_shop, versionnée par tag.
        $key = HfmCache::key(HfmCache::TAG_TAXONOMY, $action, [
            'id_lang' => $idLang,
            'id_shop' => $idShop,
            'id_parent' => (int) $this->in('id_parent'),
            'slug' => (string) $this->in('slug'),
        ]);

        return HfmCache::remember($key, HfmCache::TTL_TAXONOMY, function () use ($action, $idLang) {
            switch ($action) {
                case 'categories':
                    return ['categories' => $this->categories($idLang)];
                case 'category':
                    return ['category' => $this->categoryBySlug($idLang)];
                case 'all_active':
                    return ['categories' => $this->allActiveCategories($idLang)];
                case 'menu':
                    return ['menu' => $this->menuTree($idLang)];
                case 'manufacturers':
                    return ['manufacturers' => $this->manufacturers($idLang)];
                case 'countries':
                    return ['countries' => $this->countries($idLang)];
                default:
                    return ['error' => 'unknown_action'];
            }
        });
    }

    /**
     * Résout une catégorie par son slug (link_rewrite), dans la langue courante.
     * Renvoie active + nb_products pour que le front décide : page dédiée (active + produits),
     * redirection (inactive/vide) ou 404 (slug inconnu). Sert la parité des URLs catégorie prod.
     */
    protected function categoryBySlug($idLang)
    {
        $slug = (string) $this->in('slug');
        if ($slug === '') {
            return null;
        }
        $idShop = (int) $this->context->shop->id;
        $row = Db::getInstance()->getRow(
            'SELECT c.id_category, c.id_parent, c.active, cl.name, cl.link_rewrite,
                    cl.meta_title, cl.meta_description, cl.description
             FROM ' . _DB_PREFIX_ . 'category_lang cl
             INNER JOIN ' . _DB_PREFIX_ . 'category c ON c.id_category = cl.id_category
             WHERE cl.link_rewrite = \'' . pSQL($slug) . '\'
               AND cl.id_lang = ' . (int) $idLang . ' AND cl.id_shop = ' . $idShop . '
             ORDER BY c.active DESC'
        );
        if (!$row) {
            return null;
        }
        $nbp = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'category_product cp
             INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                ON (ps.id_product = cp.id_product AND ps.id_shop = ' . $idShop . ' AND ps.active = 1 AND ps.visibility != "none")
             WHERE cp.id_category = ' . (int) $row['id_category']
        );
        // Slugs par langue (id_lang => link_rewrite) pour des hreflang corrects : le slug catégorie
        // varie d'une langue à l'autre (ex. FR « comblement » -> EN « filler »).
        $alt = [];
        $altRows = Db::getInstance()->executeS(
            'SELECT id_lang, link_rewrite FROM `' . _DB_PREFIX_ . 'category_lang`
             WHERE id_category = ' . (int) $row['id_category'] . ' AND id_shop = ' . $idShop
        );
        foreach ((array) $altRows as $r) {
            if ((string) $r['link_rewrite'] !== '') {
                $alt[(int) $r['id_lang']] = (string) $r['link_rewrite'];
            }
        }
        return [
            'id_category' => (int) $row['id_category'],
            'name' => $row['name'],
            'link_rewrite' => $row['link_rewrite'],
            'active' => (bool) (int) $row['active'],
            'nb_products' => $nbp,
            'id_parent' => (int) $row['id_parent'],
            'meta_title' => (string) $row['meta_title'],
            'meta_description' => (string) $row['meta_description'],
            'description' => (string) $row['description'],
            'alternates' => $alt,
        ];
    }

    /** TOUTES les catégories actives ayant des produits (pour le sitemap : pages /{categorie}). */
    protected function allActiveCategories($idLang)
    {
        $idShop = (int) $this->context->shop->id;
        $rows = Db::getInstance()->executeS(
            'SELECT c.id_category, cl.link_rewrite,
                    (SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'category_product cp
                       INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                          ON (ps.id_product = cp.id_product AND ps.id_shop = ' . $idShop . ' AND ps.active = 1 AND ps.visibility != "none")
                       WHERE cp.id_category = c.id_category) AS nb_products
             FROM ' . _DB_PREFIX_ . 'category c
             INNER JOIN ' . _DB_PREFIX_ . 'category_shop cs ON (cs.id_category = c.id_category AND cs.id_shop = ' . $idShop . ')
             INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (cl.id_category = c.id_category AND cl.id_lang = ' . (int) $idLang . ' AND cl.id_shop = ' . $idShop . ')
             WHERE c.active = 1 AND c.id_parent > 0
             HAVING nb_products > 0
             ORDER BY nb_products DESC'
        );
        $out = [];
        $ids = [];
        foreach ((array) $rows as $r) {
            if (empty($r['link_rewrite'])) {
                continue;
            }
            $ids[] = (int) $r['id_category'];
            $out[] = ['id_category' => (int) $r['id_category'], 'link_rewrite' => $r['link_rewrite'], 'nb_products' => (int) $r['nb_products']];
        }
        if (!$ids) {
            return $out;
        }
        // Slugs par langue (id_category => id_lang => link_rewrite) pour l'hreflang du sitemap.
        $altByCat = [];
        foreach ((array) Db::getInstance()->executeS(
            'SELECT id_category, id_lang, link_rewrite FROM ' . _DB_PREFIX_ . 'category_lang
             WHERE id_shop = ' . $idShop . ' AND id_category IN (' . implode(',', $ids) . ')'
        ) as $r) {
            if ((string) $r['link_rewrite'] !== '') {
                $altByCat[(int) $r['id_category']][(int) $r['id_lang']] = (string) $r['link_rewrite'];
            }
        }
        foreach ($out as &$c) {
            $c['alternates'] = isset($altByCat[$c['id_category']]) ? $altByCat[$c['id_category']] : [];
        }
        unset($c);
        return $out;
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
