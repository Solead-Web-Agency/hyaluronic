<?php
/**
 * Endpoint catalogue headless : liste + fiche produit, en JSON propre (prix corrects via le moteur PS).
 *   GET ?id_lang=&id_currency=[&id_customer=]&limit=&page=&id_category=   -> liste de cartes produit
 *   GET ?id_product=..                                                    -> fiche produit détaillée
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/cache.php';

class HfmstorefrontProductsModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        $idLang = (int) $this->context->language->id;
        $idCurrency = (int) $this->context->currency->id;
        $idShop = (int) $this->context->shop->id;
        $idProduct = (int) $this->in('id_product');

        // Cross-selling d'une fiche produit (accessoires, marque, catégorie, zone).
        $idRelated = (int) $this->in('related');
        if ($idRelated) {
            $key = HfmCache::key(HfmCache::TAG_PRODUCTS, 'related', [
                'id_product' => $idRelated,
                'id_lang' => $idLang,
                'id_shop' => $idShop,
                'id_currency' => $idCurrency,
            ]);
            return HfmCache::remember($key, HfmCache::TTL_PRODUCTS, function () use ($idRelated, $idLang) {
                return $this->related($idRelated, $idLang);
            });
        }

        // Lectures publiques -> cacheables (tag products, TTL 300).
        if ($idProduct) {
            // Fiche : clé = id_product + id_lang (+ shop/devise pour cohérence prix).
            $key = HfmCache::key(HfmCache::TAG_PRODUCTS, 'single', [
                'id_product' => $idProduct,
                'id_lang' => $idLang,
                'id_shop' => $idShop,
                'id_currency' => $idCurrency,
            ]);
            return HfmCache::remember($key, HfmCache::TTL_PRODUCTS, function () use ($idProduct, $idLang) {
                return $this->single($idProduct, $idLang);
            });
        }

        // Liste : clé = id_category|id_manufacturer|q|filter|page|limit|id_lang|id_currency.
        $key = HfmCache::key(HfmCache::TAG_PRODUCTS, 'list', [
            'id_category' => (int) $this->in('id_category'),
            'id_manufacturer' => (int) $this->in('id_manufacturer'),
            'q' => trim((string) $this->in('q')),
            'filter' => (string) $this->in('filter'),
            'page' => max(1, (int) $this->in('page', 1)),
            'limit' => min(300, max(1, (int) $this->in('limit', 24))),
            'id_lang' => $idLang,
            'id_shop' => $idShop,
            'id_currency' => $idCurrency,
        ]);
        return HfmCache::remember($key, HfmCache::TTL_PRODUCTS, function () use ($idLang) {
            return $this->listing($idLang);
        });
    }

    protected function listing($idLang)
    {
        $limit = min(300, max(1, (int) $this->in('limit', 24)));
        $page = max(1, (int) $this->in('page', 1));
        $start = ($page - 1) * $limit;
        $idCategory = (int) $this->in('id_category');
        $idManufacturer = (int) $this->in('id_manufacturer');
        $q = trim((string) $this->in('q'));
        // Filtres dynamiques de l'onglet « Promos & Top » (pas des catégories).
        $filter = (string) $this->in('filter');

        if ($q !== '') {
            // Recherche plein-texte sur le nom : on récupère les ids puis on pagine.
            $found = Product::searchByName($idLang, $q);
            $ids = array_map(function ($r) { return (int) $r['id_product']; }, (array) $found);
            $ids = array_slice($ids, $start, $limit);
            $rows = array_map(function ($id) { return ['id_product' => $id]; }, $ids);
        } elseif ($filter !== '') {
            $rows = $this->filtered($filter, $idLang, $start, $limit);
        } elseif ($idManufacturer) {
            $rows = Manufacturer::getProducts($idManufacturer, $idLang, $page, $limit, 'id_product', 'DESC');
        } elseif ($idCategory) {
            $category = new Category($idCategory, $idLang);
            $rows = $category->getProducts($idLang, $page, $limit, 'id_product', 'DESC');
        } else {
            $rows = Product::getProducts($idLang, $start, $limit, 'id_product', 'DESC', false, true);
        }
        $items = [];
        foreach ((array) $rows as $r) {
            $items[] = $this->card((int) $r['id_product'], $idLang);
        }
        return ['page' => $page, 'limit' => $limit, 'count' => count($items), 'products' => array_values(array_filter($items))];
    }

    /**
     * Listes dynamiques de l'onglet « Promos & Top » :
     *   new     -> nouveautés (moteur PS)
     *   best    -> meilleures ventes (moteur PS)
     *   promo   -> prix en baisse / promotions (moteur PS)
     *   nolido  -> produits SANS lidocaïne (le complément de la recherche « lidocaïne »)
     * Renvoie un tableau de lignes contenant au moins 'id_product'.
     */
    protected function filtered($filter, $idLang, $start, $limit)
    {
        switch ($filter) {
            case 'new':
                return (array) Product::getNewProducts($idLang, $start, $limit);
            case 'best':
                return (array) ProductSale::getBestSalesLight($idLang, $start, $limit);
            case 'promo':
                return (array) Product::getPricesDrop($idLang, $start, $limit);
            case 'nolido':
                $idShop = (int) $this->context->shop->id;
                $sql = 'SELECT p.id_product
                        FROM ' . _DB_PREFIX_ . 'product p
                        INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                            ON (ps.id_product = p.id_product AND ps.id_shop = ' . $idShop . ' AND ps.active = 1 AND ps.visibility != "none")
                        INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl
                            ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ' . $idShop . ')
                        WHERE pl.name NOT LIKE "%lidoca%"
                        ORDER BY p.id_product DESC
                        LIMIT ' . (int) $start . ', ' . (int) $limit;
                return (array) Db::getInstance()->executeS($sql, true, false);
            default:
                return [];
        }
    }

    /**
     * Cross-selling de la fiche produit, en un appel :
     *   bought_together  accessoires PrestaShop du produit (croisement curatif BO)
     *   you_may_like     mélange marque + catégorie (recommandations génériques)
     *   brand            autres produits de la même marque (+ id/nom pour le lien)
     *   category         autres produits de la catégorie par défaut
     *   zone             autres produits de la même zone (catégories filles de « Par zone »)
     * Chaque produit n'apparaît que dans UNE section (dédoublonnage en cascade).
     */
    protected function related($idProduct, $idLang)
    {
        $p = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($p)) {
            return ['error' => 'product_not_found'];
        }

        $seen = [$idProduct => true];
        // Transforme des lignes produit en cartes uniques (jamais déjà vues).
        $cards = function ($rows, $max) use (&$seen, $idLang) {
            $out = [];
            foreach ((array) $rows as $r) {
                $id = (int) (isset($r['id_product']) ? $r['id_product'] : 0);
                if (!$id || isset($seen[$id]) || count($out) >= $max) {
                    continue;
                }
                $card = $this->card($id, $idLang);
                if ($card) {
                    $seen[$id] = true;
                    $out[] = $card;
                }
            }
            return $out;
        };

        // 1) Accessoires (croisement curatif renseigné dans le BO).
        $boughtTogether = $cards(Product::getAccessoriesLight($idLang, $idProduct), 4);

        // Viviers marque / catégorie / zone (élargis, dédoublonnés à la sélection).
        $brandRows = $p->id_manufacturer
            ? (array) Manufacturer::getProducts((int) $p->id_manufacturer, $idLang, 1, 24, 'id_product', 'DESC')
            : [];
        $catRows = [];
        $catName = '';
        if ((int) $p->id_category_default) {
            $cat = new Category((int) $p->id_category_default, $idLang);
            if (Validate::isLoadedObject($cat)) {
                $catName = (string) $cat->name;
                $catRows = (array) $cat->getProducts($idLang, 1, 24, 'id_product', 'DESC');
            }
        }
        // Zone : première catégorie du produit fille de la racine « Par zone » (302).
        $zoneId = (int) Db::getInstance()->getValue(
            'SELECT cp.id_category FROM ' . _DB_PREFIX_ . 'category_product cp
             INNER JOIN ' . _DB_PREFIX_ . 'category c ON c.id_category = cp.id_category AND c.id_parent = 302
             WHERE cp.id_product = ' . (int) $idProduct . ' ORDER BY cp.id_category'
        );
        $zoneRows = [];
        $zoneName = '';
        if ($zoneId) {
            $zoneCat = new Category($zoneId, $idLang);
            if (Validate::isLoadedObject($zoneCat)) {
                $zoneName = (string) $zoneCat->name;
                $zoneRows = (array) $zoneCat->getProducts($idLang, 1, 24, 'id_product', 'DESC');
            }
        }

        // 2-4) Sections spécifiques d'abord (elles ont priorité sur le mélange générique).
        $brand = [
            'id_manufacturer' => (int) $p->id_manufacturer,
            'name' => $p->id_manufacturer ? (string) Manufacturer::getNameById((int) $p->id_manufacturer) : '',
            'products' => $cards($brandRows, 8),
        ];
        $category = [
            'id_category' => (int) $p->id_category_default,
            'name' => $catName,
            'products' => $cards($catRows, 8),
        ];
        $zone = [
            'id_category' => $zoneId,
            'name' => $zoneName,
            'products' => $cards($zoneRows, 8),
        ];

        // 5) « Vous aimerez aussi » : marque et catégorie entrelacées (le reste).
        $mixRows = [];
        $max = max(count($brandRows), count($catRows));
        for ($i = 0; $i < $max; $i++) {
            if (isset($brandRows[$i])) {
                $mixRows[] = $brandRows[$i];
            }
            if (isset($catRows[$i])) {
                $mixRows[] = $catRows[$i];
            }
        }
        $youMayLike = $cards($mixRows, 12);

        return [
            'bought_together' => $boughtTogether,
            'you_may_like' => $youMayLike,
            'brand' => $brand,
            'category' => $category,
            'zone' => $zone,
        ];
    }

    /**
     * Disponibilité en 3 états, pour des CTA distincts côté front :
     *   in_stock     quantité > 0
     *   backorder    épuisé mais commandable (précommande, out_of_stock résolu via le réglage global)
     *   unavailable  épuisé et non commandable
     */
    protected function availability($idProduct, $qty)
    {
        if ((int) $qty > 0) {
            return 'in_stock';
        }
        $backorder = Product::isAvailableWhenOutOfStock((int) StockAvailable::outOfStock((int) $idProduct));

        return $backorder ? 'backorder' : 'unavailable';
    }

    protected function card($idProduct, $idLang)
    {
        $p = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($p) || !$p->active) {
            return null;
        }
        $cover = Product::getCover($idProduct);
        $idImage = $cover ? (int) $cover['id_image'] : 0;
        $qty = (int) Product::getQuantity($idProduct);
        return [
            'id_product' => (int) $p->id,
            'name' => $p->name,
            'reference' => $p->reference,
            'link_rewrite' => $p->link_rewrite,
            'brand' => $p->id_manufacturer ? Manufacturer::getNameById((int) $p->id_manufacturer) : null,
            'price_incl_tax' => (float) Tools::ps_round($p->getPrice(true), 2),
            'price_excl_tax' => (float) Tools::ps_round($p->getPrice(false), 2),
            'image' => $idImage ? $this->context->link->getImageLink($p->link_rewrite, $idImage, 'home_default') : null,
            'quantity' => $qty,
            'available' => $this->availability($idProduct, $qty) !== 'unavailable',
            'availability' => $this->availability($idProduct, $qty),
            'rpps_required' => $this->productRequiresRpps($idProduct),
        ];
    }

    protected function single($idProduct, $idLang)
    {
        $p = new Product($idProduct, true, $idLang);
        if (!Validate::isLoadedObject($p)) {
            return ['error' => 'product_not_found'];
        }
        $images = [];
        foreach ($p->getImages($idLang) as $img) {
            $images[] = $this->context->link->getImageLink($p->link_rewrite, (int) $img['id_image'], 'large_default');
        }
        $features = [];
        foreach ($p->getFrontFeatures($idLang) as $f) {
            $features[] = ['name' => $f['name'], 'value' => $f['value']];
        }
        // Contenus éditoriaux générés par l'IA (module hfmaiproducts), si présents.
        $extra = false;
        try {
            $extra = Db::getInstance()->getRow(
                'SELECT key_points, faq, composition FROM `' . _DB_PREFIX_ . 'hfm_product_extra`
                 WHERE id_product = ' . (int) $idProduct . ' AND id_lang = ' . (int) $idLang
            );
        } catch (Exception $e) {
            // Table absente (module hfmaiproducts non installé) : pas de contenus IA.
        }
        $decode = static function ($json) {
            $v = is_string($json) ? json_decode($json, true) : null;
            return is_array($v) ? $v : [];
        };
        return ['product' => [
            'id_product' => (int) $p->id,
            'name' => $p->name,
            'reference' => $p->reference,
            'ean13' => (string) $p->ean13,
            'link_rewrite' => $p->link_rewrite,
            'description' => $p->description,
            'description_short' => $p->description_short,
            'meta_title' => (string) $p->meta_title,
            'meta_description' => (string) $p->meta_description,
            'price_incl_tax' => (float) Tools::ps_round($p->getPrice(true), 2),
            'price_excl_tax' => (float) Tools::ps_round($p->getPrice(false), 2),
            'manufacturer' => $p->id_manufacturer ? Manufacturer::getNameById((int) $p->id_manufacturer) : null,
            'quantity' => (int) Product::getQuantity($idProduct),
            'available' => $this->availability($idProduct, (int) Product::getQuantity($idProduct)) !== 'unavailable',
            'availability' => $this->availability($idProduct, (int) Product::getQuantity($idProduct)),
            'rpps_required' => $this->productRequiresRpps($idProduct),
            'images' => $images,
            'features' => $features,
            'key_points' => $extra ? $decode($extra['key_points']) : [],
            'faq' => $extra ? $decode($extra['faq']) : [],
            'composition' => $extra ? $decode($extra['composition']) : [],
        ]];
    }
}
