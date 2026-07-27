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

        // URL headless « parité prod » : /{categorie}/{slug} -> résolution par slug.
        // Le segment catégorie est cosmétique ; on résout par link_rewrite (id_product retourné
        // dans la réponse permet au front de rediriger 301 vers l'URL canonique).
        if (!$idProduct) {
            $slug = (string) $this->in('link_rewrite');
            if ($slug !== '') {
                $idProduct = $this->productIdFromSlug($slug);
                // Slug fourni mais introuvable -> 404 explicite (pas de repli sur la liste complète).
                if (!$idProduct) {
                    return ['error' => 'product_not_found'];
                }
            }
        }

        // Produit discontinué : renvoie le slug de sa catégorie par défaut (produit actif OU inactif)
        // pour que le front redirige 301 une ancienne URL produit indexée vers sa catégorie.
        $discSlug = (string) $this->in('disc_slug');
        if ($discSlug !== '') {
            $key = HfmCache::key(HfmCache::TAG_PRODUCTS, 'disc', ['slug' => $discSlug, 'id_lang' => $idLang, 'id_shop' => $idShop]);
            return HfmCache::remember($key, HfmCache::TTL_PRODUCTS, function () use ($discSlug, $idLang) {
                return ['discontinued_category' => $this->discontinuedCategory($discSlug, $idLang)];
            });
        }

        // Comparateur : cartes + caractéristiques pour une liste d'ids (max 4, comme l'ancien site).
        // NB : aujourd'hui la matière est maigre (4 produits actifs sur 578 ont une caractéristique
        // non vide) -> le tableau s'appuie surtout sur prix/marque/dispo. Il se remplira de lui-même
        // quand les fiches produit seront refondues, sans toucher à ce code.
        if ((string) $this->in('action') === 'compare') {
            $ids = $this->parseIds((string) $this->in('ids'), 4);
            if (!$ids) {
                return ['items' => []];
            }
            $key = HfmCache::key(HfmCache::TAG_PRODUCTS, 'compare', [
                'ids' => implode(',', $ids),
                'id_lang' => $idLang,
                'id_shop' => $idShop,
                'id_currency' => $idCurrency,
            ]);
            return HfmCache::remember($key, HfmCache::TTL_PRODUCTS, function () use ($ids, $idLang) {
                $cards = $this->cardsForIds($ids, $idLang);
                $items = [];
                foreach ($ids as $id) {
                    if (!isset($cards[$id])) {
                        continue;
                    }
                    $p = new Product($id, false, $idLang);
                    $features = [];
                    if (Validate::isLoadedObject($p)) {
                        foreach ($p->getFrontFeatures($idLang) as $f) {
                            // Caractéristique sans valeur = bruit dans un tableau comparatif.
                            if (trim((string) $f['value']) !== '') {
                                $features[] = ['name' => $f['name'], 'value' => $f['value']];
                            }
                        }
                    }
                    $items[] = array_merge($cards[$id], ['features' => $features]);
                }
                return ['items' => $items];
            });
        }

        // Cartes produit pour une LISTE d'ids, dans l'ordre demandé (« Déjà vus » : l'ordre porte
        // l'information de récence). Les ids inconnus/inactifs sont simplement omis.
        $idsRaw = (string) $this->in('ids');
        if ($idsRaw !== '') {
            $ids = $this->parseIds($idsRaw, 24);
            if (!$ids) {
                return ['products' => []];
            }
            $key = HfmCache::key(HfmCache::TAG_PRODUCTS, 'byids', [
                'ids' => implode(',', $ids),
                'id_lang' => $idLang,
                'id_shop' => $idShop,
                'id_currency' => $idCurrency,
            ]);
            return HfmCache::remember($key, HfmCache::TTL_PRODUCTS, function () use ($ids, $idLang) {
                $cards = $this->cardsForIds($ids, $idLang);
                $out = [];
                foreach ($ids as $id) {
                    if (isset($cards[$id])) {
                        $out[] = $cards[$id];
                    }
                }
                return ['products' => $out];
            });
        }

        // Carte des slugs par langue de TOUS les produits actifs (pour l'hreflang du sitemap).
        // Sortie compacte { items: [ { id, alt: { <id_lang>: { c: catSlug, s: slug } } } ] }.
        if ((string) $this->in('action') === 'slugmap') {
            $key = HfmCache::key(HfmCache::TAG_PRODUCTS, 'slugmap', ['id_shop' => $idShop]);
            return HfmCache::remember($key, HfmCache::TTL_PRODUCTS, function () use ($idShop) {
                return ['items' => $this->slugMap($idShop)];
            });
        }

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

        // Liste : clé = id_category|id_manufacturer|q|filter|order|page|limit|id_lang|id_currency.
        // « order » DOIT figurer dans la clé : deux tris différents ne partagent pas le même cache.
        $key = HfmCache::key(HfmCache::TAG_PRODUCTS, 'list', [
            'id_category' => (int) $this->in('id_category'),
            'id_manufacturer' => (int) $this->in('id_manufacturer'),
            'q' => trim((string) $this->in('q')),
            'filter' => (string) $this->in('filter'),
            'order' => (string) $this->in('order', 'position'),
            'page' => max(1, (int) $this->in('page', 1)),
            'limit' => min(1000, max(1, (int) $this->in('limit', 24))),
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
        // Plafond 1000 : garde-fou anti-DoS conservé. La VRAIE pagination se fait désormais côté
        // front (infinite scroll, ~48/page) qui demande page 2, 3… à la demande ; le catalogue ne
        // « charge plus tout ». On renvoie aussi le TOTAL du filtre courant (avant pagination) pour
        // que le front sache quand s'arrêter (hasMore = chargés < total).
        $limit = min(1000, max(1, (int) $this->in('limit', 24)));
        $page = max(1, (int) $this->in('page', 1));
        $start = ($page - 1) * $limit;
        $idCategory = (int) $this->in('id_category');
        $idManufacturer = (int) $this->in('id_manufacturer');
        $q = trim((string) $this->in('q'));
        // Filtres dynamiques de l'onglet « Promos & Top » (pas des catégories).
        $filter = (string) $this->in('filter');
        // Tri demandé : position (défaut) | name-asc | price-asc | price-desc.
        $order = (string) $this->in('order', 'position');

        $total = 0;
        if ($q !== '') {
            // Recherche plein-texte sur le nom : on récupère TOUS les ids (pour le total) puis on pagine.
            // NB : l'ordre est celui de searchByName (pertinence/nom) ; le tri « order » ne s'y applique pas.
            $found = Product::searchByName($idLang, $q);
            $allIds = array_map(function ($r) { return (int) $r['id_product']; }, (array) $found);
            $total = count($allIds);
            $ids = array_slice($allIds, $start, $limit);
            $rows = array_map(function ($id) { return ['id_product' => $id]; }, $ids);
        } elseif ($filter !== '') {
            $rows = $this->filtered($filter, $idLang, $page, $limit);
            $total = $this->filteredTotal($filter, $idLang);
        } elseif ($idManufacturer) {
            list($orderBy, $orderWay) = $this->orderFor($order, 'manufacturer');
            $rows = Manufacturer::getProducts($idManufacturer, $idLang, $page, $limit, $orderBy, $orderWay);
            $total = (int) Manufacturer::getProducts($idManufacturer, $idLang, 1, 1, null, null, true);
        } elseif ($idCategory) {
            list($orderBy, $orderWay) = $this->orderFor($order, 'category');
            $category = new Category($idCategory, $idLang);
            $rows = $category->getProducts($idLang, $page, $limit, $orderBy, $orderWay);
            // 6e argument getTotal=true -> compte les produits de la catégorie (mêmes filtres actif/visibilité).
            $total = (int) $category->getProducts($idLang, 1, 1, null, null, true);
        } else {
            list($orderBy, $orderWay) = $this->orderFor($order, 'all');
            $rows = Product::getProducts($idLang, $start, $limit, $orderBy, $orderWay, false, true);
            $total = $this->allProductsTotal($idLang);
        }
        // Batch : une seule passe groupée pour toutes les cartes de la liste.
        $ids = array_map(function ($r) { return (int) $r['id_product']; }, (array) $rows);
        $cards = $this->cardsForIds($ids, $idLang);
        $items = [];
        foreach ($ids as $id) {
            if (isset($cards[$id])) {
                $items[] = $cards[$id];
            }
        }
        return ['page' => $page, 'limit' => $limit, 'total' => (int) $total, 'count' => count($items), 'products' => $items];
    }

    /**
     * Mappe le tri demandé par le front -> couple (orderBy, orderWay) de PrestaShop.
     *   position (défaut) | name-asc | price-asc | price-desc
     *
     * NOTE HONNÊTE (tri prix) : l'ordre s'appuie sur la colonne `price` de base du produit
     * (product_shop.price). Le prix FINAL réellement affiché sur la carte (specific_price,
     * promotions, remises groupe/quantité via getPriceStatic) n'est PAS triable en SQL simple :
     * l'ordre peut donc différer à la marge du prix affiché. Limitation PrestaShop assumée.
     *
     * @param string $order  clé de tri front
     * @param string $source 'category' | 'manufacturer' | 'all' (pour le défaut « position »)
     *
     * @return array{0:string,1:string} [orderBy, orderWay]
     */
    protected function orderFor($order, $source)
    {
        switch ($order) {
            case 'name-asc':
                return ['name', 'ASC'];
            case 'price-asc':
                return ['price', 'ASC'];
            case 'price-desc':
                return ['price', 'DESC'];
            case 'position':
            default:
                // « position » = ordre catalogue PS (drag-drop BO). Hors catégorie (liste « tout le
                // catalogue »), la position n'existe pas -> on garde l'ordre historique id_product DESC.
                // Pour un fabricant, PS remappe lui-même « position » -> nom.
                return $source === 'all' ? ['id_product', 'DESC'] : ['position', 'ASC'];
        }
    }

    /** Total du catalogue complet (mêmes conditions que Product::getProducts en front : actif + visible). */
    protected function allProductsTotal($idLang)
    {
        $idShop = (int) $this->context->shop->id;
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(DISTINCT p.id_product)
             FROM ' . _DB_PREFIX_ . 'product p
             INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                ON (ps.id_product = p.id_product AND ps.id_shop = ' . $idShop . '
                    AND ps.active = 1 AND ps.visibility IN ("both","catalog"))
             INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl
                ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ' . $idShop . ')'
        );
    }

    /**
     * Total d'un filtre « Promos & Top » (avant pagination), pour la même population que filtered() :
     *   new/promo -> compteur natif PS ; best -> pas de compteur natif (compte les produits vendus,
     *   actifs & visibles, comme getBestSalesLight) ; nolido -> COUNT du même SQL.
     */
    protected function filteredTotal($filter, $idLang)
    {
        $idShop = (int) $this->context->shop->id;
        switch ($filter) {
            case 'new':
                return (int) Product::getNewProducts($idLang, 1, 1, true);
            case 'promo':
                return (int) Product::getPricesDrop($idLang, 1, 1, true);
            case 'best':
                return (int) Db::getInstance()->getValue(
                    'SELECT COUNT(DISTINCT ps.id_product)
                     FROM ' . _DB_PREFIX_ . 'product_sale ps
                     INNER JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = ps.id_product
                     INNER JOIN ' . _DB_PREFIX_ . 'product_shop pshop
                        ON (pshop.id_product = p.id_product AND pshop.id_shop = ' . $idShop . ' AND pshop.active = 1)
                     WHERE p.visibility != "none"'
                );
            case 'nolido':
                return (int) Db::getInstance()->getValue(
                    'SELECT COUNT(DISTINCT p.id_product)
                     FROM ' . _DB_PREFIX_ . 'product p
                     INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                        ON (ps.id_product = p.id_product AND ps.id_shop = ' . $idShop . ' AND ps.active = 1 AND ps.visibility != "none")
                     INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl
                        ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ' . $idShop . ')
                     WHERE pl.name NOT LIKE "%lidoca%"'
                );
            default:
                return 0;
        }
    }

    /**
     * Listes dynamiques de l'onglet « Promos & Top » :
     *   new     -> nouveautés (moteur PS)
     *   best    -> meilleures ventes (moteur PS)
     *   promo   -> prix en baisse / promotions (moteur PS)
     *   nolido  -> produits SANS lidocaïne (le complément de la recherche « lidocaïne »)
     * Renvoie un tableau de lignes contenant au moins 'id_product'.
     */
    protected function filtered($filter, $idLang, $page, $limit)
    {
        // NB conventions PS : getNewProducts / getPricesDrop attendent un numéro de page
        // 1-based ; getBestSalesLight est 0-based. Passer un offset comme numéro de page
        // (bug d'origine) faisait sauter offset*limit produits dès la page 2.
        $start = ($page - 1) * $limit;
        switch ($filter) {
            case 'new':
                return (array) Product::getNewProducts($idLang, $page, $limit);
            case 'best':
                return (array) ProductSale::getBestSalesLight($idLang, $page - 1, $limit);
            case 'promo':
                return (array) Product::getPricesDrop($idLang, $page, $limit);
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
        // Transforme des lignes produit en cartes uniques (jamais déjà vues), en
        // GROUPANT le chargement (cardsForIds) : ~5 requêtes par section au lieu de N.
        $cards = function ($rows, $max) use (&$seen, $idLang) {
            $ids = [];
            foreach ((array) $rows as $r) {
                $id = (int) (isset($r['id_product']) ? $r['id_product'] : 0);
                if ($id && !isset($seen[$id])) {
                    $ids[] = $id;
                }
            }
            $loaded = $this->cardsForIds($ids, $idLang);
            $out = [];
            foreach ($ids as $id) {
                if (count($out) >= $max) {
                    break;
                }
                if (isset($loaded[$id]) && !isset($seen[$id])) {
                    $seen[$id] = true;
                    $out[] = $loaded[$id];
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
        $cards = $this->cardsForIds([(int) $idProduct], $idLang);
        return isset($cards[(int) $idProduct]) ? $cards[(int) $idProduct] : null;
    }

    /**
     * Construit les cartes produit d'une LISTE d'ids en ~5 requêtes GROUPÉES au lieu
     * d'une boucle N+1 (elle faisait new Product + getCover + getQuantity + outOfStock
     * + rpps par produit, soit ~1800 requêtes pour 300 cartes). Seul le prix reste
     * calculé par produit via le moteur PS (getPriceStatic, résultat identique à
     * getPrice) — irréductible et correct.
     *
     * @param int[] $ids
     * @param int   $idLang
     *
     * @return array<int, array> [id_product => carte] ; produits inactifs absents.
     */
    protected function cardsForIds(array $ids, $idLang)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }
        $idLang = (int) $idLang;
        $idShop = (int) $this->context->shop->id;
        $in = implode(',', $ids);
        $db = Db::getInstance();

        // 1) Champs de base (produits ACTIFS uniquement) + nom localisé + marque.
        $base = [];
        $rows = $db->executeS(
            'SELECT p.id_product, p.id_manufacturer, p.reference, pl.name, pl.link_rewrite, m.name AS brand
             FROM ' . _DB_PREFIX_ . 'product p
             INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                ON (ps.id_product = p.id_product AND ps.id_shop = ' . $idShop . ' AND ps.active = 1)
             INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl
                ON (pl.id_product = p.id_product AND pl.id_lang = ' . $idLang . ' AND pl.id_shop = ' . $idShop . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m ON m.id_manufacturer = p.id_manufacturer
             WHERE p.id_product IN (' . $in . ')'
        );
        foreach ((array) $rows as $r) {
            $base[(int) $r['id_product']] = $r;
        }
        if (empty($base)) {
            return [];
        }

        // 2) Couvertures (une image de couverture par produit).
        $covers = [];
        $rows = $db->executeS(
            'SELECT id_product, id_image FROM ' . _DB_PREFIX_ . 'image
             WHERE cover = 1 AND id_product IN (' . $in . ')'
        );
        foreach ((array) $rows as $r) {
            $covers[(int) $r['id_product']] = (int) $r['id_image'];
        }

        // 3) Stock (quantité + réglage out_of_stock) — pour la dispo en 3 états.
        $stock = [];
        $rows = $db->executeS(
            'SELECT id_product, quantity, out_of_stock FROM ' . _DB_PREFIX_ . 'stock_available
             WHERE id_product IN (' . $in . ') AND id_product_attribute = 0 AND id_shop = ' . $idShop
        );
        foreach ((array) $rows as $r) {
            $stock[(int) $r['id_product']] = ['qty' => (int) $r['quantity'], 'oos' => (int) $r['out_of_stock']];
        }

        // 4) Flag RPPS (produits réservés aux professionnels).
        $rpps = [];
        $rows = $db->executeS(
            'SELECT DISTINCT fp.id_product FROM ' . _DB_PREFIX_ . 'feature_product fp
             JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON fl.id_feature = fp.id_feature
             WHERE fl.name = \'RPPS\' AND fp.id_product IN (' . $in . ')'
        );
        foreach ((array) $rows as $r) {
            $rpps[(int) $r['id_product']] = true;
        }

        // 5) Slug de la catégorie par défaut (pour l'URL /{categorie}/{slug}).
        $catSlug = [];
        $rows = $db->executeS(
            'SELECT p.id_product, cl.link_rewrite AS cat_slug
             FROM ' . _DB_PREFIX_ . 'product p
             INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON (cl.id_category = p.id_category_default AND cl.id_lang = ' . $idLang . ' AND cl.id_shop = ' . $idShop . ')
             WHERE p.id_product IN (' . $in . ')'
        );
        foreach ((array) $rows as $r) {
            $catSlug[(int) $r['id_product']] = $r['cat_slug'];
        }

        // 6) Note moyenne « Société des Avis Garantis » (étoiles sur les cartes), produits notés.
        $ratings = [];
        $rows = $db->executeS(
            'SELECT product_id, rate, reviews_nb FROM `' . _DB_PREFIX_ . 'steavisgarantis_average_rating`
             WHERE id_lang = \'' . (int) $idLang . '\' AND reviews_nb > 0 AND product_id IN (' . $in . ')'
        );
        foreach ((array) $rows as $r) {
            $ratings[(int) $r['product_id']] = [
                'rate' => (float) $r['rate'],
                'count' => (int) $r['reviews_nb'],
            ];
        }

        // Assemblage — l'ordre d'entrée est préservé (utile pour le tri des listes).
        $out = [];
        foreach ($ids as $id) {
            if (!isset($base[$id])) {
                continue; // produit inactif / hors boutique : exclu comme avant
            }
            $b = $base[$id];
            $qty = isset($stock[$id]) ? $stock[$id]['qty'] : 0;
            $oos = isset($stock[$id]) ? $stock[$id]['oos'] : 0;
            $availability = $this->availabilityFromStock($qty, $oos);
            $idImage = isset($covers[$id]) ? $covers[$id] : 0;

            $out[$id] = [
                'id_product' => $id,
                'name' => $b['name'],
                'reference' => $b['reference'],
                'link_rewrite' => $b['link_rewrite'],
                'category' => isset($catSlug[$id]) ? $catSlug[$id] : null,
                'rating' => isset($ratings[$id]) ? $ratings[$id] : null,
                'brand' => $b['id_manufacturer'] ? $b['brand'] : null,
                'price_incl_tax' => (float) Tools::ps_round(Product::getPriceStatic($id, true), 2),
                'price_excl_tax' => (float) Tools::ps_round(Product::getPriceStatic($id, false), 2),
                'image' => $idImage ? $this->context->link->getImageLink($b['link_rewrite'], $idImage, 'home_default') : null,
                'quantity' => $qty,
                'available' => $availability !== 'unavailable',
                'availability' => $availability,
                'rpps_required' => isset($rpps[$id]),
            ];
        }
        return $out;
    }

    /**
     * Dispo en 3 états à partir de la quantité et du réglage out_of_stock (0/1/2)
     * DÉJÀ lus en base (pas de requête ici, contrairement à availability()).
     */
    protected function availabilityFromStock($qty, $oos)
    {
        if ((int) $qty > 0) {
            return 'in_stock';
        }
        return Product::isAvailableWhenOutOfStock((int) $oos) ? 'backorder' : 'unavailable';
    }

    /** Slug de la catégorie par défaut d'un produit (ACTIF OU INACTIF) résolu par son slug. null sinon. */
    protected function discontinuedCategory($slug, $idLang)
    {
        $idShop = (int) $this->context->shop->id;
        $idProduct = (int) Db::getInstance()->getValue(
            'SELECT id_product FROM ' . _DB_PREFIX_ . 'product_lang
             WHERE link_rewrite = \'' . pSQL($slug) . '\' AND id_lang = ' . (int) $idLang . '
             ORDER BY id_product DESC'
        );
        if (!$idProduct) {
            return null;
        }
        $idCat = (int) Db::getInstance()->getValue(
            'SELECT id_category_default FROM ' . _DB_PREFIX_ . 'product WHERE id_product = ' . $idProduct
        );
        if (!$idCat) {
            return null;
        }
        $lr = Db::getInstance()->getValue(
            'SELECT link_rewrite FROM ' . _DB_PREFIX_ . 'category_lang
             WHERE id_category = ' . $idCat . ' AND id_lang = ' . (int) $idLang . ' AND id_shop = ' . $idShop
        );
        return $lr ?: null;
    }

    /** Résout un slug produit (link_rewrite) en id_product actif. Newest gagne (slugs ~uniques). */
    protected function productIdFromSlug($slug)
    {
        $idShop = (int) $this->context->shop->id;
        $idLang = (int) $this->context->language->id;
        return (int) Db::getInstance()->getValue(
            'SELECT pl.id_product FROM ' . _DB_PREFIX_ . 'product_lang pl
             INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                ON (ps.id_product = pl.id_product AND ps.id_shop = ' . $idShop . ' AND ps.active = 1)
             WHERE pl.link_rewrite = \'' . pSQL($slug) . '\' AND pl.id_lang = ' . $idLang . '
             ORDER BY pl.id_product DESC'
        );
    }

    /** Slug + nom de la catégorie par défaut d'un produit (pour l'URL /{categorie}/{slug}). */
    protected function defaultCategory($idCategoryDefault, $idLang)
    {
        if (!(int) $idCategoryDefault) {
            return [null, null];
        }
        $cat = new Category((int) $idCategoryDefault, (int) $idLang);
        if (!Validate::isLoadedObject($cat)) {
            return [null, null];
        }
        return [$cat->link_rewrite, $cat->name];
    }

    /**
     * Slugs par langue (id_lang => ['category' => ..., 'slug' => ...]) pour des hreflang corrects.
     * Le link_rewrite du produit ET celui de sa catégorie par défaut varient d'une langue à l'autre
     * (ex. FR « vivacy » -> DE « lebhaftigkeit ») : sans ça, l'alternate /de pointerait vers le slug FR
     * (404). Indexé sur id_lang ; le front mappe locale -> id_lang. Ne renvoie que les langues où le
     * produit existe réellement (ligne product_lang présente).
     */
    protected function alternateSlugs($idProduct, $idCategoryDefault)
    {
        $idShop = (int) $this->context->shop->id;
        $out = [];
        // INNER JOIN lang active : product_lang porte encore des slugs pour des langues
        // DÉSINSTALLÉES (6 et 7 ici, sur ~325 produits) -> sans ce filtre elles fuiteraient dans
        // chaque réponse (bloat) et piégeraient tout consommateur qui itère les clés de la map.
        $rows = Db::getInstance()->executeS(
            'SELECT pl.id_lang, pl.link_rewrite FROM `' . _DB_PREFIX_ . 'product_lang` pl
             INNER JOIN `' . _DB_PREFIX_ . 'lang` l ON (l.id_lang = pl.id_lang AND l.active = 1)
             WHERE pl.id_product = ' . (int) $idProduct . ' AND pl.id_shop = ' . $idShop
        );
        foreach ((array) $rows as $r) {
            if ((string) $r['link_rewrite'] === '') {
                continue;
            }
            $out[(int) $r['id_lang']] = ['slug' => (string) $r['link_rewrite'], 'category' => null];
        }
        if ((int) $idCategoryDefault) {
            $catRows = Db::getInstance()->executeS(
                'SELECT id_lang, link_rewrite FROM `' . _DB_PREFIX_ . 'category_lang`
                 WHERE id_category = ' . (int) $idCategoryDefault . ' AND id_shop = ' . $idShop
            );
            foreach ((array) $catRows as $r) {
                $l = (int) $r['id_lang'];
                if (isset($out[$l]) && (string) $r['link_rewrite'] !== '') {
                    $out[$l]['category'] = (string) $r['link_rewrite'];
                }
            }
        }
        return $out;
    }

    /**
     * Slugs par langue de TOUS les produits actifs, en 3 requêtes batch (pas d'objet Product).
     * Sert l'hreflang du sitemap : chaque produit -> { id, alt: { id_lang: { c: catSlug, s: slug } } }.
     * Ne renvoie que les langues où le produit a un slug non vide ; catégorie = catégorie par défaut.
     */
    protected function slugMap($idShop)
    {
        // 1) Produits actifs + leur catégorie par défaut.
        $prod = [];
        $catIds = [];
        foreach ((array) Db::getInstance()->executeS(
            'SELECT p.id_product, p.id_category_default
             FROM ' . _DB_PREFIX_ . 'product p
             INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                ON (ps.id_product = p.id_product AND ps.id_shop = ' . (int) $idShop . '
                    AND ps.active = 1 AND ps.visibility != "none")'
        ) as $r) {
            $id = (int) $r['id_product'];
            $prod[$id] = ['cat' => (int) $r['id_category_default'], 'alt' => []];
            if ((int) $r['id_category_default']) {
                $catIds[(int) $r['id_category_default']] = true;
            }
        }
        if (!$prod) {
            return [];
        }
        $ids = array_map('intval', array_keys($prod));

        // 2) Slugs produit par langue (langues INSTALLÉES uniquement : cf. alternateSlugs).
        foreach ((array) Db::getInstance()->executeS(
            'SELECT pl.id_product, pl.id_lang, pl.link_rewrite FROM ' . _DB_PREFIX_ . 'product_lang pl
             INNER JOIN ' . _DB_PREFIX_ . 'lang l ON (l.id_lang = pl.id_lang AND l.active = 1)
             WHERE pl.id_shop = ' . (int) $idShop . ' AND pl.id_product IN (' . implode(',', $ids) . ')'
        ) as $r) {
            $id = (int) $r['id_product'];
            if (isset($prod[$id]) && (string) $r['link_rewrite'] !== '') {
                $prod[$id]['alt'][(int) $r['id_lang']] = ['s' => (string) $r['link_rewrite'], 'c' => null];
            }
        }

        // 3) Slugs catégorie par défaut par langue.
        $catSlugs = [];
        if ($catIds) {
            foreach ((array) Db::getInstance()->executeS(
                'SELECT id_category, id_lang, link_rewrite FROM ' . _DB_PREFIX_ . 'category_lang
                 WHERE id_shop = ' . (int) $idShop . ' AND id_category IN (' . implode(',', array_map('intval', array_keys($catIds))) . ')'
            ) as $r) {
                if ((string) $r['link_rewrite'] !== '') {
                    $catSlugs[(int) $r['id_category']][(int) $r['id_lang']] = (string) $r['link_rewrite'];
                }
            }
        }

        $out = [];
        foreach ($prod as $id => $info) {
            $cat = $info['cat'];
            foreach ($info['alt'] as $l => &$entry) {
                if ($cat && isset($catSlugs[$cat][$l])) {
                    $entry['c'] = $catSlugs[$cat][$l];
                }
            }
            unset($entry);
            if ($info['alt']) {
                $out[] = ['id' => (int) $id, 'alt' => $info['alt']];
            }
        }
        return $out;
    }

    /** Liste d'ids « ?ids=1,2,3 » -> entiers positifs uniques, bornés (garde-fou de charge). */
    protected function parseIds($raw, $max)
    {
        $out = [];
        foreach (explode(',', (string) $raw) as $v) {
            $v = (int) trim($v);
            if ($v > 0 && !in_array($v, $out, true)) {
                $out[] = $v;
            }
        }
        return array_slice($out, 0, (int) $max);
    }

    /**
     * Déclinaisons vendables d'un produit (taille, teinte…).
     *
     * Le bridge ne lisait que `id_product_attribute = 0` : les variantes étaient donc invisibles et
     * INVENDABLES côté headless (11 variantes en stock sur 5 produits actifs, dont une à 248,75 € HT
     * avec 98 en stock), alors que l'ancien site affichait un sélecteur. Le panier (cart.php) sait
     * déjà les gérer : il ne manquait que l'exposition.
     *
     * Le prix est demandé au moteur PS PAR déclinaison (getPriceStatic) : il inclut donc les
     * promotions/specific_price propres à la variante, au lieu du simple « impact » brut.
     */
    protected function combinations($idProduct, $idLang)
    {
        $p = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($p)) {
            return [];
        }
        $rows = $p->getAttributeCombinations($idLang);
        if (!$rows) {
            return [];
        }
        // Une ligne PAR attribut : on regroupe par déclinaison et on concatène les libellés
        // (ex. « Volume : 10 ml » + « Teinte : Ivoire »).
        $byId = [];
        foreach ((array) $rows as $r) {
            $id = (int) $r['id_product_attribute'];
            if (!isset($byId[$id])) {
                $byId[$id] = [
                    'id_product_attribute' => $id,
                    'parts' => [],
                    'reference' => (string) $r['reference'],
                    'default' => (bool) (int) $r['default_on'],
                    'quantity' => (int) $r['quantity'],
                ];
            }
            $label = trim((string) $r['group_name'] . ' : ' . (string) $r['attribute_name']);
            $byId[$id]['parts'][] = $label;
        }
        $out = [];
        foreach ($byId as $id => $c) {
            $qty = (int) $c['quantity'];
            $out[] = [
                'id_product_attribute' => $id,
                'label' => implode(' · ', array_unique($c['parts'])),
                'reference' => $c['reference'],
                'default' => $c['default'],
                'quantity' => $qty,
                'available' => $qty > 0,
                'price_incl_tax' => (float) Tools::ps_round(Product::getPriceStatic($idProduct, true, $id), 2),
                'price_excl_tax' => (float) Tools::ps_round(Product::getPriceStatic($idProduct, false, $id), 2),
            ];
        }
        // Déclinaison par défaut en tête (c'est celle dont le prix s'affiche sur la fiche).
        usort($out, function ($a, $b) {
            return ($b['default'] ? 1 : 0) - ($a['default'] ? 1 : 0);
        });
        return $out;
    }

    /**
     * Paliers de remise par quantité (« à partir de 10 : x € »).
     *
     * L'ancien site affichait ce tableau sur la fiche (2 produits actifs concernés). Le nouveau
     * front APPLIQUE bien le dégressif au panier (le moteur PS le fait), mais ne l'affichait pas :
     * le client B2B ignorait donc que le palier existait -> upsell perdu.
     * Le prix de chaque palier est demandé au moteur PS avec la quantité correspondante.
     */
    protected function quantityDiscounts($idProduct)
    {
        $idShop = (int) $this->context->shop->id;
        $idCurrency = (int) $this->context->currency->id;
        $idCountry = (int) $this->context->country->id;
        $idGroup = (int) Group::getCurrent()->id;

        $rows = SpecificPrice::getQuantityDiscounts(
            (int) $idProduct,
            $idShop,
            $idCurrency,
            $idCountry,
            $idGroup,
            null,
            false,
            0
        );
        $seen = [];
        $out = [];
        foreach ((array) $rows as $r) {
            $q = (int) (isset($r['from_quantity']) ? $r['from_quantity'] : 0);
            // from_quantity <= 1 = une promo simple, pas un palier : déjà reflétée par le prix affiché.
            if ($q < 2 || isset($seen[$q])) {
                continue;
            }
            $seen[$q] = true;
            $out[] = [
                'from_quantity' => $q,
                'price_incl_tax' => (float) Tools::ps_round(Product::getPriceStatic($idProduct, true, null, 2, null, false, true, $q), 2),
                'price_excl_tax' => (float) Tools::ps_round(Product::getPriceStatic($idProduct, false, null, 2, null, false, true, $q), 2),
            ];
        }
        usort($out, function ($a, $b) {
            return $a['from_quantity'] - $b['from_quantity'];
        });
        return $out;
    }

    protected function single($idProduct, $idLang)
    {
        $p = new Product($idProduct, true, $idLang);
        if (!Validate::isLoadedObject($p)) {
            return ['error' => 'product_not_found'];
        }
        list($catSlug, $catName) = $this->defaultCategory($p->id_category_default, $idLang);
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
            'category' => $catSlug,
            'category_name' => $catName,
            'alternates' => $this->alternateSlugs($idProduct, $p->id_category_default),
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
            'reviews' => $this->productReviews($idProduct, $idLang),
            'combinations' => $this->combinations($idProduct, $idLang),
            // Prix AVANT remise : permet au front d'afficher le prix barré + le badge « -X € »
            // (l'ancien site les affichait ; sans ça la promo s'applique mais ne se voit pas).
            'price_without_reduction_incl_tax' => (float) Tools::ps_round(
                Product::getPriceStatic($idProduct, true, null, 2, null, false, false),
                2
            ),
            // Paliers dégressifs publics (l'ancien site affichait le tableau « 2 → x € »).
            'quantity_discounts' => $this->quantityDiscounts($idProduct),
        ]];
    }

    /**
     * Avis « Société des Avis Garantis » d'un produit (langue courante).
     * Fetch LIVE depuis l'API SAG (frais), avec repli sur le snapshot en base si l'API échoue.
     * Gate sur la présence en base : évite d'appeler l'API sur les produits jamais notés.
     *
     * Cache DÉDIÉ (tag reviews, TTL 12 h) et NON products : la fiche produit se reconstruit à
     * chaque commande (purge products via actionUpdateQuantity) ; sans ce découplage, l'appel API
     * SAG bloquant (curl jusqu'à 5 s) se relancerait au premier affichage post-commande. Ici il ne
     * se relance qu'à l'expiration du TTL avis -> ~2 appels/produit/jour, indépendants des stocks.
     */
    protected function productReviews($idProduct, $idLang)
    {
        $lang = (string) (int) $idLang;
        $hasReviews = (int) Db::getInstance()->getValue(
            'SELECT reviews_nb FROM `' . _DB_PREFIX_ . 'steavisgarantis_average_rating`
             WHERE product_id = \'' . pSQL((string) (int) $idProduct) . '\' AND id_lang = \'' . pSQL($lang) . '\''
        );
        if ($hasReviews < 1) {
            return null;
        }
        $key = HfmCache::key(HfmCache::TAG_REVIEWS, 'sag', [
            'id_product' => (int) $idProduct,
            'id_lang' => (int) $idLang,
        ]);
        return HfmCache::remember($key, HfmCache::TTL_REVIEWS, function () use ($idProduct, $idLang) {
            $live = $this->sagLiveReviews($idProduct, $idLang);
            return $live !== null ? $live : $this->reviewsFromDb($idProduct, $idLang);
        });
    }

    /** Récupère les avis EN DIRECT depuis l'API SAG (reviews.php par produit). null si échec. */
    protected function sagLiveReviews($idProduct, $idLang)
    {
        $apiKey = Configuration::get('steavisgarantis_apiKey_' . (int) $idLang);
        if (!$apiKey || strpos($apiKey, '/') === false) {
            return null;
        }
        $parts = explode('/', $apiKey);
        $langCode = isset($parts[1]) ? $parts[1] : 'fr';
        $url = $this->sagDomain($langCode)
            . 'wp-content/plugins/ag-core/api/reviews.php?translation=1&apiPost=1&productID=' . (int) $idProduct;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'apiKey=' . urlencode($apiKey));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // TLS vérifié (la clé API transite dans le POST -> pas de MITM). Domaines SAG en HTTPS valide.
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $resp = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        $curlErr = $curlErrNo ? curl_error($ch) : '';
        curl_close($ch);
        if (!$resp) {
            // Échec = repli SILENCIEUX sur le snapshot en base : les avis se figeraient
            // indéfiniment sans aucun signal (typiquement un bundle CA manquant/périmé côté
            // hébergeur depuis l'activation de la vérification TLS). On loggue pour rendre
            // la panne détectable ; on ne casse jamais la fiche produit pour autant.
            if ($curlErrNo) {
                try {
                    PrestaShopLogger::addLog('HFM SAG: curl #' . $curlErrNo . ' ' . $curlErr, 2);
                } catch (\Throwable $e) {
                    // le log ne doit jamais faire échouer la lecture des avis
                }
            }
            return null;
        }
        $resp = preg_replace('/^\xEF\xBB\xBF/', '', $resp);
        $data = json_decode($resp, true);
        if (!is_array($data) || empty($data)) {
            return null;
        }

        $items = [];
        $sum = 0;
        $dist = [0, 0, 0, 0, 0];
        foreach ($data as $r) {
            if (!is_array($r) || (string) (isset($r['review_status']) ? $r['review_status'] : '') !== '1') {
                continue;
            }
            $rate = (int) (isset($r['review_rating']) ? $r['review_rating'] : 0);
            if ($rate < 1 || $rate > 5) {
                continue;
            }
            $name = trim((string) (isset($r['reviewer_name']) ? $r['reviewer_name'] : ''));
            $last = trim((string) (isset($r['lastname']) ? $r['lastname'] : ''));
            if ($last !== '') {
                $name = trim($name . ' ' . Tools::strtoupper(Tools::substr($last, 0, 1)) . '.');
            }
            $order = (string) (isset($r['order_date']) ? $r['order_date'] : '');
            $answer = trim((string) (isset($r['answer_text']) ? $r['answer_text'] : ''));
            $items[] = [
                'name' => $name,
                'rate' => $rate,
                'review' => (string) (isset($r['review_text']) ? $r['review_text'] : ''),
                'date' => $this->sagDate(isset($r['date_time']) ? $r['date_time'] : ''),
                'orderDate' => ($order !== '' && $order !== 'None' && $order !== '0000-00-00 00:00:00') ? $order : null,
                'translated' => (string) (isset($r['translated']) ? $r['translated'] : '0') === '1',
                'sourceLang' => (string) (isset($r['sourceLang']) ? $r['sourceLang'] : ''),
                'answer' => $answer !== '' ? $answer : null,
                'answerDate' => null,
            ];
            $sum += $rate;
            $dist[$rate - 1]++;
        }
        $count = count($items);
        if ($count < 1) {
            return null;
        }
        usort($items, function ($a, $b) {
            return strcmp((string) $b['date'], (string) $a['date']);
        });
        $rate = round($sum / $count, 2);
        $cert = Configuration::get('steavisgarantis_certificateUrl_' . (int) $idLang);
        if (!$cert) {
            $cert = Configuration::get('steavisgarantis_certificateUrl_1');
        }

        return [
            'rate' => (float) $rate,
            'rate10' => round($rate * 2, 1),
            'count' => $count,
            'distribution' => $dist,
            'certificateUrl' => $cert ?: null,
            'items' => $items,
        ];
    }

    /** Domaine de l'API SAG selon le code langue. */
    protected function sagDomain($langCode)
    {
        switch ($langCode) {
            case 'en': return 'https://www.guaranteed-reviews.com/';
            case 'de': return 'https://www.g-g-b.de/';
            case 'es': return 'https://www.sociedad-de-opiniones-contrastadas.es/';
            case 'it': return 'https://www.societa-recensioni-garantite.it/';
            case 'nl': return 'https://www.g-b-n.nl/';
            case 'pl': return 'https://www.gwarantowane-opinie.pl/';
            case 'pt': return 'https://www.sdag.pt/';
            case 'fr':
            default: return 'https://www.societe-des-avis-garantis.fr/';
        }
    }

    /** Avis SAG lus dans le snapshot en base (repli si l'API live échoue). */
    protected function reviewsFromDb($idProduct, $idLang)
    {
        $pid = (string) (int) $idProduct;
        $lang = (string) (int) $idLang;
        $db = Db::getInstance();

        $avg = $db->getRow(
            'SELECT rate, reviews_nb, nb1, nb2, nb3, nb4, nb5
             FROM `' . _DB_PREFIX_ . 'steavisgarantis_average_rating`
             WHERE product_id = \'' . pSQL($pid) . '\' AND id_lang = \'' . pSQL($lang) . '\''
        );
        if (!$avg || (int) $avg['reviews_nb'] < 1) {
            return null;
        }

        $rows = $db->executeS(
            'SELECT ag_reviewer_name, rate, review, date_time, order_date, translated, source_lang, answer_text, answer_date_time
             FROM `' . _DB_PREFIX_ . 'steavisgarantis_reviews`
             WHERE product_id = \'' . pSQL($pid) . '\' AND id_lang = \'' . pSQL($lang) . '\''
        );
        $items = [];
        foreach ((array) $rows as $r) {
            $items[] = [
                'name' => (string) $r['ag_reviewer_name'],
                'rate' => (int) $r['rate'],
                'review' => (string) $r['review'],
                'date' => $this->sagDate($r['date_time']),
                'orderDate' => $r['order_date'] && $r['order_date'] !== '0000-00-00 00:00:00' ? $r['order_date'] : null,
                'translated' => (int) $r['translated'] === 1,
                'sourceLang' => (string) $r['source_lang'],
                'answer' => trim((string) $r['answer_text']) !== '' ? (string) $r['answer_text'] : null,
                'answerDate' => $r['answer_date_time'] && $r['answer_date_time'] !== '0000-00-00 00:00:00' ? $r['answer_date_time'] : null,
            ];
        }
        // Tri par date de publication décroissante (dates normalisées en ISO).
        usort($items, function ($a, $b) {
            return strcmp((string) $b['date'], (string) $a['date']);
        });

        $cert = Configuration::get('steavisgarantis_certificateUrl_' . $lang);
        if (!$cert) {
            $cert = Configuration::get('steavisgarantis_certificateUrl_1');
        }

        return [
            'rate' => (float) $avg['rate'],
            'rate10' => round((float) $avg['rate'] * 2, 1),
            'count' => (int) $avg['reviews_nb'],
            'distribution' => [
                (int) $avg['nb1'], (int) $avg['nb2'], (int) $avg['nb3'], (int) $avg['nb4'], (int) $avg['nb5'],
            ],
            'certificateUrl' => $cert ?: null,
            'items' => $items,
        ];
    }

    /** Normalise une date SAG (timestamp Unix OU datetime) en 'Y-m-d H:i:s'. */
    /**
     * Date d'avis normalisée en ISO 8601 (ex. 2024-10-24T19:08:08+00:00).
     * schema.org/datePublished exige de l'ISO : « 2024-10-24 19:08:08 » (espace, sans fuseau)
     * n'est pas valide et fait tomber le champ côté Google.
     */
    protected function sagDate($v)
    {
        $v = trim((string) $v);
        if ($v === '') {
            return '';
        }
        $ts = ctype_digit($v) ? (int) $v : strtotime($v);
        if (!$ts) {
            return '';
        }
        return date('c', $ts);
    }
}
