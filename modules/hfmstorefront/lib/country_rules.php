<?php
/**
 * Règles PAR PAYS du site headless (remplacent ce que faisaient, sur l'ancien thème, les modules
 * `groupinc` — majorations/remises — et `blockproductsbycountry` — produits interdits par pays —
 * dont les surcharges ne sont pas compatibles PrestaShop 9 / headless).
 *
 *  - PRIX : les règles restent celles saisies dans la table `groupinc_configuration`. Elles sont
 *    appliquées via le hook NATIF PS9 `actionProductPriceCalculation` (déclenché en fin de
 *    Product::priceCalculation) -> catalogue, panier, commande et facture cohérents.
 *    Pris en charge : mode 0 (majoration) / 1 (remise), type 0 (montant) / 1 (pourcentage),
 *    sur le prix de vente (price_calculation / price_application 1 = HT, 3 = TTC), filtres pays,
 *    zones, groupes, clients, produits, catégories, fabricants, fournisseurs, langues, devises,
 *    dates, quantité mini, prix résultat mini/maxi. Toute autre option (base prix d'achat /
 *    fournisseur, prix fixe importé, seuils, caractéristiques/attributs, horaires) -> la règle est
 *    IGNORÉE (et journalisée une fois) plutôt que mal appliquée.
 *
 *  - PAYS retenu : celui de l'ADRESSE DE LIVRAISON du panier quand elle existe (PrestaShop le
 *    passe au hook), sinon le « pays de tarification » du visiteur transmis par le front
 *    (géolocalisation Vercel, paramètre `country`). La TVA, elle, reste calculée par PrestaShop
 *    comme avant (pays de livraison ou pays par défaut) : on ne touche qu'aux règles ci-dessus.
 *
 *  - BLOCAGES : tables `bpbc_products` / `bpbc_manufacturers` / `bpbc_categories` /
 *    `bpbc_suppliers` du module blockproductsbycountry (toujours éditables dans son BO).
 */
class HfmCountryRules
{
    /** @var int|null pays de tarification du visiteur (hors panier adressé) */
    protected static $pricingCountryId = null;

    protected static $rulesCache = null;
    protected static $blockedCache = [];
    protected static $specialCache = null;
    protected static $productInfoCache = [];
    protected static $loggedSkips = [];

    // ---------------------------------------------------------------- pays de tarification

    public static function setPricingCountryIso($iso)
    {
        $iso = Tools::strtoupper(trim((string) $iso));
        if (!preg_match('/^[A-Z]{2}$/', $iso)) {
            self::$pricingCountryId = null;
            return;
        }
        $id = (int) Country::getByIso($iso);
        self::$pricingCountryId = $id > 0 ? $id : null;
    }

    public static function pricingCountryId()
    {
        return self::$pricingCountryId;
    }

    /**
     * Compartiment de cache : les pays SANS aucune règle ni blocage partagent le même cache ('').
     * Seuls les pays concernés (aujourd'hui JP, US, IL) ont leur propre compartiment.
     */
    public static function cacheBucket()
    {
        $id = (int) self::$pricingCountryId;
        if (!$id) {
            return '';
        }
        $special = self::specialCountries();
        if ($special === true) {
            return 'c' . $id; // une règle s'applique à tous les pays : un compartiment par pays
        }
        return in_array($id, $special, true) ? 'c' . $id : '';
    }

    /**
     * Codes ISO des pays concernés par une règle ou un blocage, pour que le front ne transmette
     * le pays du visiteur QUE lorsqu'il change quelque chose (cache partagé sinon).
     * ['*'] si une règle s'applique à tous les pays.
     */
    public static function specialCountryIsos()
    {
        $special = self::specialCountries();
        if ($special === true) {
            return ['*'];
        }
        $out = [];
        foreach ($special as $id) {
            $iso = Country::getIsoById((int) $id);
            if ($iso) {
                $out[] = Tools::strtoupper($iso);
            }
        }
        sort($out);
        return array_values(array_unique($out));
    }

    /**
     * Groupes clients dont les prix diffèrent du tarif public : réduction de groupe, réductions
     * par catégorie, prix spécifiques, exonération de TVA (module taxexempt), affichage HT, ou
     * règle groupinc ciblant le groupe. Le catalogue n'est calculé « par groupe » que pour eux.
     */
    public static function specialGroupIds()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $db = Db::getInstance();
        $p = _DB_PREFIX_;
        $ids = [];
        foreach ((array) $db->executeS("SELECT id_group FROM `{$p}group` WHERE reduction <> 0 OR price_display_method = 1") as $r) {
            $ids[] = (int) $r['id_group'];
        }
        foreach ((array) $db->executeS("SELECT DISTINCT id_group FROM `{$p}group_reduction`") as $r) {
            $ids[] = (int) $r['id_group'];
        }
        foreach ((array) $db->executeS("SELECT DISTINCT id_group FROM `{$p}specific_price` WHERE id_group > 0") as $r) {
            $ids[] = (int) $r['id_group'];
        }
        foreach (preg_split('/[;,]/', (string) Configuration::get('TAXEXEMPT_GROUPS')) as $g) {
            $ids[] = (int) $g;
        }
        foreach (self::rules() as $r) {
            $ids = array_merge($ids, $r['groups']);
        }
        $ids = array_values(array_unique(array_filter($ids, function ($x) { return $x > 0; })));
        sort($ids);
        return $cache = $ids;
    }

    /** Compartiment de cache « groupe » du client du contexte ('' = tarif public). */
    public static function groupBucket()
    {
        $customer = Context::getContext()->customer;
        if (!$customer || !Validate::isLoadedObject($customer)) {
            return '';
        }
        $g = (int) $customer->id_default_group;
        return in_array($g, self::specialGroupIds(), true) ? 'g' . $g : '';
    }

    /** Pays ayant au moins une règle de prix ou un blocage. `true` = règle sans filtre pays. */
    protected static function specialCountries()
    {
        if (self::$specialCache !== null) {
            return self::$specialCache;
        }
        $ids = [];
        foreach (self::rules() as $r) {
            if (!$r['countries'] && !$r['zones']) {
                return self::$specialCache = true;
            }
            $ids = array_merge($ids, $r['countries']);
            if ($r['zones']) {
                $rows = Db::getInstance()->executeS(
                    'SELECT id_country FROM `' . _DB_PREFIX_ . 'country` WHERE id_zone IN (' . implode(',', $r['zones']) . ')'
                );
                foreach ((array) $rows as $row) {
                    $ids[] = (int) $row['id_country'];
                }
            }
        }
        foreach (['bpbc_products', 'bpbc_manufacturers', 'bpbc_categories', 'bpbc_suppliers'] as $t) {
            if (self::tableExists($t)) {
                foreach ((array) Db::getInstance()->executeS('SELECT DISTINCT id_country FROM `' . _DB_PREFIX_ . $t . '`') as $row) {
                    $ids[] = (int) $row['id_country'];
                }
            }
        }
        return self::$specialCache = array_values(array_unique(array_map('intval', $ids)));
    }

    // ---------------------------------------------------------------- blocages par pays

    /** Ids des produits interdits pour un pays (produits + fabricants + catégories + fournisseurs). */
    public static function blockedProductIds($idCountry)
    {
        $idCountry = (int) $idCountry;
        if ($idCountry <= 0) {
            return [];
        }
        if (isset(self::$blockedCache[$idCountry])) {
            return self::$blockedCache[$idCountry];
        }
        $p = _DB_PREFIX_;
        $parts = [];
        if (self::tableExists('bpbc_products')) {
            $parts[] = "SELECT id_product FROM `{$p}bpbc_products` WHERE id_country = $idCountry";
        }
        if (self::tableExists('bpbc_manufacturers')) {
            $parts[] = "SELECT pr.id_product FROM `{$p}product` pr INNER JOIN `{$p}bpbc_manufacturers` b ON b.id_manufacturer = pr.id_manufacturer AND b.id_country = $idCountry";
        }
        if (self::tableExists('bpbc_categories')) {
            $parts[] = "SELECT cp.id_product FROM `{$p}category_product` cp INNER JOIN `{$p}bpbc_categories` b ON b.id_category = cp.id_category AND b.id_country = $idCountry";
        }
        if (self::tableExists('bpbc_suppliers')) {
            $parts[] = "SELECT pr.id_product FROM `{$p}product` pr INNER JOIN `{$p}bpbc_suppliers` b ON b.id_supplier = pr.id_supplier AND b.id_country = $idCountry";
        }
        $ids = [];
        if ($parts) {
            foreach ((array) Db::getInstance()->executeS(implode(' UNION ', $parts)) as $row) {
                $ids[(int) $row['id_product']] = true;
            }
        }
        return self::$blockedCache[$idCountry] = array_map('intval', array_keys($ids));
    }

    public static function isBlocked($idProduct, $idCountry)
    {
        return in_array((int) $idProduct, self::blockedProductIds($idCountry), true);
    }

    /** Produits bloqués pour le pays de tarification courant (catalogue). */
    public static function blockedForVisitor()
    {
        return self::blockedProductIds((int) self::$pricingCountryId);
    }

    // ---------------------------------------------------------------- règles de prix

    /** Règles actives de groupinc, normalisées et triées par priorité. */
    public static function rules()
    {
        if (self::$rulesCache !== null) {
            return self::$rulesCache;
        }
        self::$rulesCache = [];
        if (!self::tableExists('groupinc_configuration')) {
            return self::$rulesCache;
        }
        $list = function ($v) {
            return array_values(array_filter(array_map('intval', preg_split('/[;,]/', (string) $v)), function ($x) {
                return $x > 0;
            }));
        };
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'groupinc_configuration` WHERE active = 1 ORDER BY priority ASC, id_groupinc_configuration ASC'
        );
        foreach ((array) $rows as $r) {
            $unsupported = [];
            if (!in_array((int) $r['mode'], [0, 1], true)) {
                $unsupported[] = 'mode ' . $r['mode'];
            }
            if (!in_array((int) $r['type'], [0, 1], true)) {
                $unsupported[] = 'type ' . $r['type'];
            }
            if (!in_array((int) $r['price_calculation'], [1, 3], true) && (int) $r['type'] === 1) {
                $unsupported[] = 'price_calculation ' . $r['price_calculation'];
            }
            if (!in_array((int) $r['price_application'], [1, 3], true)) {
                $unsupported[] = 'price_application ' . $r['price_application'];
            }
            foreach (['features', 'attributes'] as $f) {
                if (trim((string) $r[$f]) !== '') {
                    $unsupported[] = $f;
                }
            }
            if ((int) $r['threshold_price'] || (float) $r['threshold_min_price'] > 0 || (float) $r['threshold_max_price'] > 0) {
                $unsupported[] = 'seuils';
            }
            if ((float) $r['cart_amount'] > 0 || (int) $r['filter_stock'] || (int) $r['filter_weight']) {
                $unsupported[] = 'filtres panier/stock/poids';
            }
            if (self::scheduleRestricts($r['schedule'])) {
                $unsupported[] = 'horaires';
            }
            if ($unsupported) {
                self::logSkip((int) $r['id_groupinc_configuration'], implode(', ', $unsupported));
                continue;
            }
            self::$rulesCache[] = [
                'id' => (int) $r['id_groupinc_configuration'],
                'mode' => (int) $r['mode'],
                'type' => (int) $r['type'],
                'percentage' => (float) $r['percentage'],
                'fix' => (float) $r['fix'],
                'fix_incl_tax' => (int) $r['price_application'] === 3,
                'id_shop' => (int) $r['id_shop'],
                'countries' => $list($r['countries']),
                'zones' => $list($r['zones']),
                'groups' => $list($r['groups']),
                'customers' => $list($r['customers']),
                'customers_excluded' => $list($r['customers_excluded']),
                'products' => $list($r['products']),
                'products_excluded' => $list($r['products_excluded']),
                'categories' => $list($r['categories']),
                'manufacturers' => $list($r['manufacturers']),
                'suppliers' => $list($r['suppliers']),
                'languages' => $list($r['languages']),
                'currencies' => $list($r['currencies']),
                'product_qty' => (int) $r['product_qty'],
                'min_result' => (float) $r['min_result_price'],
                'max_result' => (float) $r['max_result_price'],
                'backoffice' => (int) $r['backoffice'] === 1,
                'from' => self::date($r['date_from']),
                'to' => self::date($r['date_to']),
            ];
        }
        return self::$rulesCache;
    }

    /**
     * Corps du hook actionProductPriceCalculation (PS9 : appelé en fin de calcul, `price` par
     * référence, déjà taxes/remises comprises selon use_tax). Applique la PREMIÈRE règle
     * correspondante (ordre de priorité groupinc).
     */
    public static function applyPrice(array $params)
    {
        if (!array_key_exists('price', $params) || !empty($params['only_reduc'])) {
            return;
        }
        $rules = self::rules();
        if (!$rules) {
            return;
        }
        $context = Context::getContext();
        $isAdmin = isset($context->controller) && is_object($context->controller)
            && isset($context->controller->controller_type) && $context->controller->controller_type === 'admin';

        $idProduct = (int) $params['id_product'];
        $idCountry = self::countryFor($params);
        $info = self::productInfo($idProduct);
        if (!$info) {
            return;
        }
        $idZone = $idCountry ? (int) Country::getIdZone($idCountry) : 0;
        $idGroup = (int) ($params['id_group'] ?? 0);
        $idCustomer = (int) ($params['id_customer'] ?? 0);
        $idShop = (int) ($params['id_shop'] ?? 0);
        $idCurrency = (int) ($params['id_currency'] ?? 0);
        $idLang = isset($context->language->id) ? (int) $context->language->id : 0;
        $qty = (int) ($params['quantity'] ?? 1);
        $now = time();

        foreach ($rules as $r) {
            if ($isAdmin && !$r['backoffice']) {
                continue;
            }
            if ($r['id_shop'] && $idShop && $r['id_shop'] !== $idShop) {
                continue;
            }
            if (($r['from'] && $now < $r['from']) || ($r['to'] && $now > $r['to'])) {
                continue;
            }
            if ($r['countries'] && !in_array($idCountry, $r['countries'], true)) {
                continue;
            }
            if ($r['zones'] && !in_array($idZone, $r['zones'], true)) {
                continue;
            }
            if ($r['groups'] && !in_array($idGroup, $r['groups'], true)) {
                continue;
            }
            if ($r['customers'] && !in_array($idCustomer, $r['customers'], true)) {
                continue;
            }
            if ($r['customers_excluded'] && in_array($idCustomer, $r['customers_excluded'], true)) {
                continue;
            }
            if ($r['products'] && !in_array($idProduct, $r['products'], true)) {
                continue;
            }
            if ($r['products_excluded'] && in_array($idProduct, $r['products_excluded'], true)) {
                continue;
            }
            if ($r['manufacturers'] && !in_array($info['id_manufacturer'], $r['manufacturers'], true)) {
                continue;
            }
            if ($r['suppliers'] && !in_array($info['id_supplier'], $r['suppliers'], true)) {
                continue;
            }
            if ($r['categories'] && !array_intersect($r['categories'], $info['categories'])) {
                continue;
            }
            if ($r['languages'] && !in_array($idLang, $r['languages'], true)) {
                continue;
            }
            if ($r['currencies'] && !in_array($idCurrency, $r['currencies'], true)) {
                continue;
            }
            if ($r['product_qty'] > 1 && $qty < $r['product_qty']) {
                continue;
            }

            $price = (float) $params['price'];
            if ($r['type'] === 1) {
                $delta = $price * $r['percentage'] / 100;
            } else {
                $delta = self::fixInPriceUnit($r['fix'], $r['fix_incl_tax'], !empty($params['use_tax']), $idProduct, $params);
            }
            $new = $r['mode'] === 0 ? $price + $delta : $price - $delta;
            if ($r['min_result'] > 0 && $new < $r['min_result']) {
                $new = $r['min_result'];
            }
            if ($r['max_result'] > 0 && $new > $r['max_result']) {
                $new = $r['max_result'];
            }
            $params['price'] = max(0, $new);
            return; // première règle correspondante uniquement
        }
    }

    /** Pays d'application : livraison du panier si connue, sinon pays de tarification du visiteur. */
    protected static function countryFor(array $params)
    {
        $idCart = (int) ($params['id_cart'] ?? 0);
        if ($idCart) {
            $idAddress = (int) Db::getInstance()->getValue(
                'SELECT id_address_delivery FROM `' . _DB_PREFIX_ . 'cart` WHERE id_cart = ' . $idCart
            );
            if ($idAddress > 0) {
                return (int) ($params['id_country'] ?? 0);
            }
        }
        if (self::$pricingCountryId) {
            return (int) self::$pricingCountryId;
        }
        return (int) ($params['id_country'] ?? 0);
    }

    /** Montant fixe de la règle exprimé dans la même base (HT/TTC) que le prix calculé. */
    protected static function fixInPriceUnit($fix, $fixInclTax, $useTax, $idProduct, array $params)
    {
        if ($fix <= 0 || $fixInclTax === $useTax) {
            return $fix;
        }
        $address = isset($params['address']) && $params['address'] instanceof Address ? $params['address'] : new Address();
        $calculator = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct($idProduct))->getTaxCalculator();
        return $useTax ? $calculator->addTaxes($fix) : $calculator->removeTaxes($fix);
    }

    protected static function productInfo($idProduct)
    {
        if (isset(self::$productInfoCache[$idProduct])) {
            return self::$productInfoCache[$idProduct];
        }
        $row = Db::getInstance()->getRow(
            'SELECT id_manufacturer, id_supplier FROM `' . _DB_PREFIX_ . 'product` WHERE id_product = ' . (int) $idProduct
        );
        if (!$row) {
            return self::$productInfoCache[$idProduct] = null;
        }
        $cats = [];
        foreach ((array) Db::getInstance()->executeS(
            'SELECT id_category FROM `' . _DB_PREFIX_ . 'category_product` WHERE id_product = ' . (int) $idProduct
        ) as $c) {
            $cats[] = (int) $c['id_category'];
        }
        return self::$productInfoCache[$idProduct] = [
            'id_manufacturer' => (int) $row['id_manufacturer'],
            'id_supplier' => (int) $row['id_supplier'],
            'categories' => $cats,
        ];
    }

    // ---------------------------------------------------------------- utilitaires

    protected static function scheduleRestricts($json)
    {
        $days = json_decode((string) $json, true);
        if (!is_array($days)) {
            return false;
        }
        foreach ($days as $d) {
            if (!is_array($d)) {
                continue;
            }
            if (empty($d['isActive'])) {
                return true;
            }
            if (($d['timeFrom'] ?? '00:00') !== '00:00' || ($d['timeTill'] ?? '23:59') !== '23:59') {
                return true;
            }
        }
        return false;
    }

    protected static function date($v)
    {
        $v = (string) $v;
        if ($v === '' || strpos($v, '0000-00-00') === 0) {
            return 0;
        }
        $t = strtotime($v);
        return $t ?: 0;
    }

    protected static function tableExists($table)
    {
        static $known = [];
        if (!isset($known[$table])) {
            $known[$table] = (bool) Db::getInstance()->getValue(
                'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = \''
                . pSQL(_DB_PREFIX_ . $table) . '\'',
                false
            );
        }
        return $known[$table];
    }

    protected static function logSkip($idRule, $why)
    {
        if (isset(self::$loggedSkips[$idRule])) {
            return;
        }
        self::$loggedSkips[$idRule] = true;
        // Une trace par règle et par jour (pas à chaque requête).
        $key = 'HFM_GI_SKIP_' . (int) $idRule;
        try {
            if (Configuration::get($key) === date('Y-m-d')) {
                return;
            }
            Configuration::updateValue($key, date('Y-m-d'));
            PrestaShopLogger::addLog('HFM règles prix : règle groupinc #' . $idRule . ' ignorée (option non prise en charge : ' . $why . ')', 2);
        } catch (\Throwable $e) {
            // journalisation impossible : sans conséquence
        }
    }
}
