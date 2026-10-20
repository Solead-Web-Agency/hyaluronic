<?php
/**
 * Produits réservés aux professionnels : règle appliquée selon le PAYS DE LIVRAISON
 * (demande client 16 & 19/10/2026, modèle « FD Restrictions professionnelles ») :
 *   - rpps        : numéro RPPS / attestation pro (parcours existant, France) ;
 *   - declaration : déclaration sur l'honneur + double validation (reste de l'Europe) ;
 *   - forbidden   : vente impossible vers ce pays ;
 *   - none        : aucune exigence.
 * Listes de pays éditables en BO (configuration du module hfmstorefront).
 *
 * Chaque commande concernée garde une trace dans `hfm_pro_declaration` (règle, pays, champs saisis,
 * double validation, produits, date, IP, langue, version et texte de la déclaration), affichée en
 * haut de la fiche commande en BO.
 */
class HfmProRules
{
    const RULE_NONE = 'none';
    const RULE_RPPS = 'rpps';
    const RULE_DECLARATION = 'declaration';
    const RULE_FORBIDDEN = 'forbidden';
    // Adresse de livraison pas encore choisie : règle inconnue.
    const RULE_PENDING = 'pending';

    // Version du texte de déclaration affiché au client (à incrémenter si le texte change).
    const TEXT_VERSION = '2026-10-20';

    const DEFAULT_RPPS = 'FR,GP,MQ,GF,RE,YT';
    const DEFAULT_DECLARATION = 'AT,BE,BG,HR,CY,CZ,DK,EE,FI,DE,GR,HU,IE,IT,LV,LT,LU,MT,NL,PL,PT,RO,SK,SI,ES,SE,IS,LI,NO,CH,GB,MC,AD,SM';

    /** Liste ISO normalisée d'une clé de configuration (repli sur la valeur par défaut si jamais définie). */
    public static function countryList($key, $default = '')
    {
        $raw = Configuration::get($key);
        if ($raw === false) {
            $raw = $default;
        }
        $out = [];
        foreach (preg_split('/[\s,;]+/', Tools::strtoupper((string) $raw)) as $iso) {
            if (preg_match('/^[A-Z]{2}$/', $iso)) {
                $out[$iso] = true;
            }
        }
        return array_keys($out);
    }

    public static function ruleForIso($iso)
    {
        $iso = Tools::strtoupper(trim((string) $iso));
        if ($iso === '') {
            return self::RULE_PENDING;
        }
        if (in_array($iso, self::countryList('HFM_PRO_FORBIDDEN_COUNTRIES'), true)) {
            return self::RULE_FORBIDDEN;
        }
        if (in_array($iso, self::countryList('HFM_PRO_RPPS_COUNTRIES', self::DEFAULT_RPPS), true)) {
            return self::RULE_RPPS;
        }
        if (in_array($iso, self::countryList('HFM_PRO_DECLARATION_COUNTRIES', self::DEFAULT_DECLARATION), true)) {
            return self::RULE_DECLARATION;
        }
        $default = (string) Configuration::get('HFM_PRO_DEFAULT_RULE');
        return in_array($default, [self::RULE_RPPS, self::RULE_DECLARATION, self::RULE_FORBIDDEN], true) ? $default : self::RULE_NONE;
    }

    public static function deliveryIso(Cart $cart)
    {
        if (!$cart->id_address_delivery) {
            return '';
        }
        $idCountry = (int) Db::getInstance()->getValue(
            'SELECT id_country FROM `' . _DB_PREFIX_ . 'address` WHERE id_address = ' . (int) $cart->id_address_delivery
        );
        return $idCountry ? (string) Country::getIsoById($idCountry) : '';
    }

    public static function ruleForCart(Cart $cart)
    {
        return self::ruleForIso(self::deliveryIso($cart));
    }

    // ---------- Journal des déclarations ----------

    public static function table()
    {
        return _DB_PREFIX_ . 'hfm_pro_declaration';
    }

    public static function ensureTable()
    {
        static $done = false;
        if ($done) {
            return;
        }
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . self::table() . '` (
                `id_cart` INT UNSIGNED NOT NULL,
                `id_order` INT UNSIGNED NOT NULL DEFAULT 0,
                `id_customer` INT UNSIGNED NOT NULL DEFAULT 0,
                `rule` VARCHAR(16) NOT NULL,
                `country_iso` VARCHAR(2) NOT NULL DEFAULT \'\',
                `rpps` VARCHAR(16) NOT NULL DEFAULT \'\',
                `double_validation` TINYINT(1) NOT NULL DEFAULT 0,
                `profession` VARCHAR(128) NOT NULL DEFAULT \'\',
                `pro_number` VARCHAR(64) NOT NULL DEFAULT \'\',
                `tax_number` VARCHAR(64) NOT NULL DEFAULT \'\',
                `products` TEXT,
                `ip` VARCHAR(45) NOT NULL DEFAULT \'\',
                `lang` VARCHAR(8) NOT NULL DEFAULT \'\',
                `text_version` VARCHAR(16) NOT NULL DEFAULT \'\',
                `text` TEXT,
                `date_accepted` DATETIME NULL,
                `date_upd` DATETIME NOT NULL,
                PRIMARY KEY (`id_cart`),
                KEY `id_order` (`id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
        $done = true;
    }

    /** Enregistre (ou remplace) la déclaration sur l'honneur du panier. */
    public static function saveDeclaration(Cart $cart, array $d)
    {
        self::ensureTable();
        $clip = function ($v, $n) {
            return pSQL(Tools::substr(trim(strip_tags((string) $v)), 0, $n));
        };
        return Db::getInstance()->execute(
            'REPLACE INTO `' . self::table() . '`
               (id_cart, id_order, id_customer, rule, country_iso, rpps, double_validation, profession, pro_number,
                tax_number, products, ip, lang, text_version, text, date_accepted, date_upd)
             VALUES (' . (int) $cart->id . ', 0, ' . (int) $cart->id_customer . ', \'' . self::RULE_DECLARATION . '\', \''
                . pSQL(self::deliveryIso($cart)) . '\', \'\', 1, \'' . $clip($d['profession'] ?? '', 128) . '\', \''
                . $clip($d['pro_number'] ?? '', 64) . '\', \'' . $clip($d['tax_number'] ?? '', 64) . '\', \''
                . pSQL(self::productsLabel($cart)) . '\', \'' . $clip($d['ip'] ?? '', 45) . '\', \''
                . $clip($d['lang'] ?? '', 8) . '\', \'' . pSQL(self::TEXT_VERSION) . '\', \''
                . $clip($d['text'] ?? '', 2000) . '\', NOW(), NOW())'
        );
    }

    public static function declarationForCart($idCart)
    {
        self::ensureTable();
        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . self::table() . '` WHERE id_cart = ' . (int) $idCart . ' AND rule = \'' . self::RULE_DECLARATION . '\''
        );
        return $row ?: null;
    }

    /**
     * Rattache la trace à la commande. Déclaration : la ligne saisie dans le tunnel. RPPS : une
     * ligne est créée avec le numéro (ou l'attestation) retenu, pour un journal homogène.
     */
    public static function recordForOrder(Cart $cart, $idOrder, $rule, $rpps = '')
    {
        self::ensureTable();
        $db = Db::getInstance();
        if ($rule === self::RULE_DECLARATION) {
            $db->execute(
                'UPDATE `' . self::table() . '` SET id_order = ' . (int) $idOrder . ', country_iso = \''
                . pSQL(self::deliveryIso($cart)) . '\', date_upd = NOW() WHERE id_cart = ' . (int) $cart->id
            );
            return;
        }
        $db->execute(
            'REPLACE INTO `' . self::table() . '`
               (id_cart, id_order, id_customer, rule, country_iso, rpps, products, date_upd)
             VALUES (' . (int) $cart->id . ', ' . (int) $idOrder . ', ' . (int) $cart->id_customer . ', \''
                . pSQL($rule) . '\', \'' . pSQL(self::deliveryIso($cart)) . '\', \'' . pSQL(Tools::substr((string) $rpps, 0, 16))
                . '\', \'' . pSQL(self::productsLabel($cart)) . '\', NOW())'
        );
    }

    public static function forOrder($idOrder)
    {
        self::ensureTable();
        $row = Db::getInstance()->getRow('SELECT * FROM `' . self::table() . '` WHERE id_order = ' . (int) $idOrder);
        return $row ?: null;
    }

    /** Produits du panier soumis à la restriction (même critère que le RPPS : caractéristique « RPPS »). */
    public static function proProducts(Cart $cart)
    {
        $out = [];
        foreach ($cart->getProducts() as $p) {
            $isPro = (bool) Db::getInstance()->getValue(
                'SELECT 1 FROM `' . _DB_PREFIX_ . 'feature_product` fp
                 JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON fl.id_feature = fp.id_feature
                 WHERE fp.id_product = ' . (int) $p['id_product'] . ' AND fl.name = \'RPPS\''
            );
            if ($isPro) {
                $out[] = ['id_product' => (int) $p['id_product'], 'name' => (string) $p['name'], 'quantity' => (int) $p['cart_quantity']];
            }
        }
        return $out;
    }

    protected static function productsLabel(Cart $cart)
    {
        $parts = [];
        foreach (self::proProducts($cart) as $p) {
            $parts[] = $p['id_product'] . ':' . $p['name'] . ($p['quantity'] > 1 ? ' ×' . $p['quantity'] : '');
        }
        return Tools::substr(implode(' | ', $parts), 0, 4000);
    }
}
