<?php
/**
 * Base commune des contrôleurs d'API headless HFM.
 * - Auth par secret partagé (en-tête X-Storefront-Token) : appels serveur-à-serveur depuis Next.js
 * - CORS pour les origines configurées
 * - Entrée/sortie JSON, dispatch par méthode HTTP (get()/post()/...)
 * On n'appelle PAS parent::init() pour éviter le rendu thème / redirections SSL : on lie le contexte à la main.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class HfmStorefrontApiController extends ModuleFrontController
{
    /** @var array données fusionnées (corps JSON + query string) */
    protected $input = [];

    public function init()
    {
        $this->handleCors();
        if (Tools::strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
        if (!$this->checkSecret()) {
            $this->respond(['error' => 'unauthorized'], 401);
        }

        // Initialise le contexte + le conteneur Symfony (nécessaire aux opérations panier/prix PS9),
        // puis on rendra du JSON et on sortira avant tout rendu de thème.
        parent::init();

        $this->input = $this->readInput();
        $this->bindContext();

        // Préfixe "handle" pour éviter toute collision avec les méthodes du Controller PS (ex: get($serviceId))
        $handler = 'handle' . Tools::ucfirst(Tools::strtolower($_SERVER['REQUEST_METHOD']));
        if (!method_exists($this, $handler)) {
            $this->respond(['error' => 'method_not_allowed'], 405);
        }
        try {
            $this->respond($this->{$handler}(), 200);
        } catch (\Throwable $e) {
            // Détail journalisé côté serveur uniquement : jamais renvoyé au client (même authentifié),
            // pour ne pas exposer chemins de fichiers, lignes ou messages SGBD.
            // addLog fait des I/O BASE : si l'exception vient justement d'une base indisponible,
            // il relancerait et on servirait un 500 vide au lieu du JSON d'erreur. On l'isole donc
            // pour garantir une dégradation propre — c'est précisément là qu'on en a besoin.
            try {
                PrestaShopLogger::addLog(
                    'HFM bridge: ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine(),
                    3
                );
            } catch (\Throwable $logError) {
                // journalisation impossible : on répond quand même proprement
            }
            $this->respond(['error' => 'server_error'], 400);
        }
    }

    protected function handleCors()
    {
        $allowed = array_filter(array_map('trim', explode(',', (string) Configuration::get('HFMSTOREFRONT_CORS'))));
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if ($origin && in_array($origin, $allowed, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
            header('Access-Control-Allow-Credentials: true');
        }
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Storefront-Token');
    }

    protected function checkSecret()
    {
        $expected = (string) Configuration::get('HFMSTOREFRONT_SECRET');
        $given = isset($_SERVER['HTTP_X_STOREFRONT_TOKEN']) ? $_SERVER['HTTP_X_STOREFRONT_TOKEN'] : '';
        return $expected !== '' && hash_equals($expected, (string) $given);
    }

    protected function readInput()
    {
        $body = [];
        $raw = Tools::file_get_contents('php://input');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }
        return array_merge($_GET, $body);
    }

    /** Lie langue/devise/boutique/panier/client au contexte à partir des paramètres reçus. */
    protected function bindContext()
    {
        $ctx = $this->context;
        if ($idLang = (int) $this->in('id_lang')) {
            $ctx->language = new Language($idLang);
        }
        if ($idCurrency = (int) $this->in('id_currency')) {
            $ctx->currency = new Currency($idCurrency);
        }
        if ($idCustomer = (int) $this->in('id_customer')) {
            $customer = new Customer($idCustomer);
            if (Validate::isLoadedObject($customer)) {
                $ctx->customer = $customer;
            }
        }
    }

    protected function in($key, $default = null)
    {
        return array_key_exists($key, $this->input) ? $this->input[$key] : $default;
    }

    protected function respond($data, $code = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ---------- Contenu CMS multilingue (pages éditables en BO, 22 langues) ----------

    protected function cmsI18nTable()
    {
        return _DB_PREFIX_ . 'hfm_cms_i18n';
    }

    protected function ensureCmsI18nTable()
    {
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . $this->cmsI18nTable() . '` (
                `slug` VARCHAR(128) NOT NULL,
                `locale` VARCHAR(8) NOT NULL,
                `title` VARCHAR(255) DEFAULT NULL,
                `content` LONGTEXT,
                `date_upd` DATETIME NOT NULL,
                PRIMARY KEY (`slug`,`locale`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
    }

    /** Traduction d'une page CMS pour une locale (2 lettres), ou null. */
    protected function getCmsTranslation($slug, $locale)
    {
        $this->ensureCmsI18nTable();
        $row = Db::getInstance()->getRow(
            'SELECT title, content, date_upd FROM `' . $this->cmsI18nTable() . '`
             WHERE slug = \'' . pSQL($slug) . '\' AND locale = \'' . pSQL($locale) . '\'',
            false
        );
        return ($row && trim((string) $row['content']) !== '') ? $row : null;
    }

    protected function setCmsTranslation($slug, $locale, $title, $content)
    {
        $this->ensureCmsI18nTable();
        Db::getInstance()->execute(
            'INSERT INTO `' . $this->cmsI18nTable() . '` (slug, locale, title, content, date_upd)
             VALUES (\'' . pSQL($slug) . '\', \'' . pSQL($locale) . '\', \'' . pSQL($title) . '\', \'' . pSQL($content, true) . '\', NOW())
             ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), date_upd = NOW()'
        );
    }

    // ---------- RPPS (produits réservés aux professionnels de santé) ----------
    // Le RPPS reste OPTIONNEL : il n'est requis que pour commander un produit qui le nécessite.

    /** Le produit porte-t-il la caractéristique "RPPS" (= achat réservé aux médecins) ? */
    protected function productRequiresRpps($idProduct)
    {
        return (bool) Db::getInstance()->getValue(
            'SELECT 1 FROM ' . _DB_PREFIX_ . 'feature_product fp
             JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON fl.id_feature = fp.id_feature
             WHERE fp.id_product = ' . (int) $idProduct . ' AND fl.name = \'RPPS\'',
            false
        );
    }

    /** Le panier contient-il au moins un produit soumis à RPPS ? */
    protected function cartRequiresRpps(Cart $cart)
    {
        foreach ($cart->getProducts() as $p) {
            if ($this->productRequiresRpps((int) $p['id_product'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Format RPPS/ADELI PERMISSIF (pour limiter les frictions au checkout) :
     * 9 à 13 chiffres, chiffres uniquement. La validité réelle est confirmée en back-office
     * (annotation sur la commande, aidée par le rapprochement au registre).
     */
    protected function isValidRpps($rpps)
    {
        $rpps = preg_replace('/\s+/', '', (string) $rpps);
        return (bool) preg_match('/^\d{9,13}$/', $rpps);
    }

    /**
     * Le numéro RPPS existe-t-il dans le registre officiel importé localement
     * (table ps_hfm_rpps_registry, ~1,8 M de praticiens, MAJ quotidienne) ?
     * Fail-open : si la table est absente/vide, on ne bloque pas.
     */
    protected function rppsRegistryTableExists()
    {
        return (bool) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = \'' . _DB_PREFIX_ . 'hfm_rpps_registry\'',
            false
        );
    }

    protected function rppsExistsInRegistry($rpps)
    {
        if (!$this->rppsRegistryTableExists()) {
            return true; // fail-open si registre non importé
        }
        return (bool) Db::getInstance()->getValue(
            'SELECT 1 FROM `' . _DB_PREFIX_ . 'hfm_rpps_registry` WHERE rpps = \'' . pSQL($rpps) . '\'',
            false
        );
    }

    /** Données du praticien depuis le registre local (nom/prénom/profession), ou null. */
    protected function rppsRegistryInfo($rpps)
    {
        if (!$this->rppsRegistryTableExists()) {
            return null;
        }
        $row = Db::getInstance()->getRow(
            'SELECT nom, prenom, profession FROM `' . _DB_PREFIX_ . 'hfm_rpps_registry` WHERE rpps = \'' . pSQL($rpps) . '\'',
            false
        );
        return $row ?: null;
    }

    /** Vérifie une clé de Luhn (dernier chiffre = checksum). */
    protected function luhnValid($number)
    {
        $sum = 0;
        $alt = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return $sum % 10 === 0;
    }

    protected function ensureRppsTable()
    {
        $t = _DB_PREFIX_ . 'hfm_customer_rpps';
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . $t . '` (
                `id_customer` INT UNSIGNED NOT NULL,
                `rpps` VARCHAR(16) NOT NULL DEFAULT \'\',
                `attestation` TINYINT(1) NOT NULL DEFAULT 0,
                `pro_doc` VARCHAR(255) DEFAULT NULL,
                `date_upd` DATETIME NOT NULL,
                PRIMARY KEY (`id_customer`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
        // Migration des tables déjà créées avant l'ajout de l'attestation pro.
        $cols = Db::getInstance()->executeS(
            'SELECT COLUMN_NAME FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = \'' . $t . '\'',
            true,
            false
        );
        $have = array_map(function ($r) { return $r['COLUMN_NAME']; }, (array) $cols);
        if (!in_array('attestation', $have, true)) {
            Db::getInstance()->execute('ALTER TABLE `' . $t . '` ADD COLUMN `attestation` TINYINT(1) NOT NULL DEFAULT 0');
        }
        if (!in_array('pro_doc', $have, true)) {
            Db::getInstance()->execute('ALTER TABLE `' . $t . '` ADD COLUMN `pro_doc` VARCHAR(255) DEFAULT NULL');
        }
    }

    protected function getCustomerRpps($idCustomer)
    {
        if (!$idCustomer) {
            return '';
        }
        $this->ensureRppsTable();
        return (string) Db::getInstance()->getValue(
            'SELECT rpps FROM `' . _DB_PREFIX_ . 'hfm_customer_rpps` WHERE id_customer = ' . (int) $idCustomer,
            false
        );
    }

    protected function setCustomerRpps($idCustomer, $rpps)
    {
        $this->ensureRppsTable();
        Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'hfm_customer_rpps` (id_customer, rpps, date_upd)
             VALUES (' . (int) $idCustomer . ', \'' . pSQL($rpps) . '\', NOW())
             ON DUPLICATE KEY UPDATE rpps = \'' . pSQL($rpps) . '\', date_upd = NOW()'
        );
        // Mémorise aussi par email (réutilisable d'une commande/compte à l'autre).
        $this->setRppsByEmail($this->customerEmail($idCustomer), $rpps, 0, null);
    }

    /**
     * Attestation "professionnel de santé" : alternative au numéro RPPS pour réduire la
     * friction. Le client coche une attestation sur l'honneur ; un justificatif peut être
     * joint (ou envoyé par email). Stocké sur le CLIENT, comme le RPPS.
     */
    protected function getCustomerProAttestation($idCustomer)
    {
        if (!$idCustomer) {
            return ['attestation' => 0, 'pro_doc' => ''];
        }
        $this->ensureRppsTable();
        $row = Db::getInstance()->getRow(
            'SELECT attestation, pro_doc FROM `' . _DB_PREFIX_ . 'hfm_customer_rpps` WHERE id_customer = ' . (int) $idCustomer,
            false
        );
        return [
            'attestation' => $row ? (int) $row['attestation'] : 0,
            'pro_doc' => ($row && $row['pro_doc']) ? (string) $row['pro_doc'] : '',
        ];
    }

    protected function setCustomerProAttestation($idCustomer, $attestation, $docFile = null)
    {
        $this->ensureRppsTable();
        $att = $attestation ? 1 : 0;
        if ($docFile !== null) {
            Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'hfm_customer_rpps` (id_customer, rpps, attestation, pro_doc, date_upd)
                 VALUES (' . (int) $idCustomer . ', \'\', ' . $att . ', \'' . pSQL($docFile) . '\', NOW())
                 ON DUPLICATE KEY UPDATE attestation = ' . $att . ', pro_doc = \'' . pSQL($docFile) . '\', date_upd = NOW()'
            );
        } else {
            Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'hfm_customer_rpps` (id_customer, rpps, attestation, date_upd)
                 VALUES (' . (int) $idCustomer . ', \'\', ' . $att . ', NOW())
                 ON DUPLICATE KEY UPDATE attestation = ' . $att . ', date_upd = NOW()'
            );
        }
        // Mémorise aussi par email (réutilisable d'une commande/compte à l'autre).
        $this->setRppsByEmail($this->customerEmail($idCustomer), '', $att, $docFile);
    }

    /**
     * Exigence "produit réservé praticiens" satisfaite ? RPPS valide OU attestation,
     * pour le compte OU pour l'email (validation mémorisée d'une commande à l'autre).
     */
    protected function proRequirementMet($idCustomer)
    {
        $d = $this->resolveProData($idCustomer);
        return $this->isValidRpps($d['rpps']) || (int) $d['attestation'] === 1;
    }

    /**
     * Rattachement "pro" à la COMMANDE (pour les commandes invité : le compte est éphémère,
     * la pièce justificative doit donc vivre sur la commande, pas sur le client).
     */
    protected function ensureOrderProDocTable()
    {
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hfm_order_pro_doc` (
                `id_order` INT UNSIGNED NOT NULL,
                `rpps` VARCHAR(16) NOT NULL DEFAULT \'\',
                `attestation` TINYINT(1) NOT NULL DEFAULT 0,
                `pro_doc` VARCHAR(255) DEFAULT NULL,
                `date_add` DATETIME NOT NULL,
                PRIMARY KEY (`id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
    }

    protected function setOrderProDoc($idOrder, $rpps, $attestation, $docFile)
    {
        $this->ensureOrderProDocTable();
        $doc = ($docFile !== null && $docFile !== '') ? '\'' . pSQL($docFile) . '\'' : 'NULL';
        Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'hfm_order_pro_doc` (id_order, rpps, attestation, pro_doc, date_add)
             VALUES (' . (int) $idOrder . ', \'' . pSQL($rpps) . '\', ' . ($attestation ? 1 : 0) . ', ' . $doc . ', NOW())
             ON DUPLICATE KEY UPDATE rpps = VALUES(rpps), attestation = VALUES(attestation), pro_doc = VALUES(pro_doc)'
        );
    }

    protected function getOrderProDoc($idOrder)
    {
        $this->ensureOrderProDocTable();
        $row = Db::getInstance()->getRow(
            'SELECT rpps, attestation, pro_doc FROM `' . _DB_PREFIX_ . 'hfm_order_pro_doc` WHERE id_order = ' . (int) $idOrder,
            false
        );
        return $row ?: null;
    }

    /** Supprime la ligne "pro" du client (après l'avoir déplacée sur la commande, cas invité). */
    protected function deleteCustomerProRow($idCustomer)
    {
        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'hfm_customer_rpps` WHERE id_customer = ' . (int) $idCustomer);
    }

    // ---- Mémorisation du statut "pro" PAR EMAIL ----
    // Une fois le RPPS / l'attestation validés pour un email (compte OU invité), on les
    // mémorise ici afin qu'une nouvelle commande avec le MÊME email ne refasse pas l'étape.

    protected function ensureRppsEmailTable()
    {
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hfm_rpps_by_email` (
                `email` VARCHAR(191) NOT NULL,
                `rpps` VARCHAR(16) NOT NULL DEFAULT \'\',
                `attestation` TINYINT(1) NOT NULL DEFAULT 0,
                `pro_doc` VARCHAR(255) DEFAULT NULL,
                `date_upd` DATETIME NOT NULL,
                PRIMARY KEY (`email`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
    }

    protected function customerEmail($idCustomer)
    {
        if (!$idCustomer) {
            return '';
        }
        return (string) Db::getInstance()->getValue(
            'SELECT email FROM ' . _DB_PREFIX_ . 'customer WHERE id_customer = ' . (int) $idCustomer,
            false
        );
    }

    protected function getRppsByEmail($email)
    {
        $email = Tools::strtolower(trim((string) $email));
        if ($email === '') {
            return null;
        }
        $this->ensureRppsEmailTable();
        $row = Db::getInstance()->getRow(
            'SELECT rpps, attestation, pro_doc FROM `' . _DB_PREFIX_ . 'hfm_rpps_by_email` WHERE email = \'' . pSQL($email) . '\'',
            false
        );
        return $row ?: null;
    }

    /** Upsert "fusionnant" : une valeur vide ne remplace pas une valeur déjà mémorisée. */
    protected function setRppsByEmail($email, $rpps, $attestation, $docFile = null)
    {
        $email = Tools::strtolower(trim((string) $email));
        if ($email === '') {
            return;
        }
        $this->ensureRppsEmailTable();
        $existing = $this->getRppsByEmail($email);
        $rpps = ($rpps !== '' && $rpps !== null) ? $rpps : ($existing ? (string) $existing['rpps'] : '');
        $att = $attestation ? 1 : ($existing ? (int) $existing['attestation'] : 0);
        $doc = ($docFile !== null && $docFile !== '') ? $docFile : ($existing ? (string) $existing['pro_doc'] : '');
        Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'hfm_rpps_by_email` (email, rpps, attestation, pro_doc, date_upd)
             VALUES (\'' . pSQL($email) . '\', \'' . pSQL($rpps) . '\', ' . $att . ', ' . ($doc !== '' ? '\'' . pSQL($doc) . '\'' : 'NULL') . ', NOW())
             ON DUPLICATE KEY UPDATE rpps = VALUES(rpps), attestation = VALUES(attestation), pro_doc = VALUES(pro_doc), date_upd = NOW()'
        );
    }

    /**
     * Données "pro" effectives d'un client : compte d'abord, sinon mémorisation par email.
     * Renvoie ['rpps','attestation','pro_doc'].
     */
    protected function resolveProData($idCustomer, $email = '')
    {
        $rpps = $this->getCustomerRpps($idCustomer);
        $pa = $this->getCustomerProAttestation($idCustomer);
        $attestation = (int) $pa['attestation'];
        $proDoc = (string) $pa['pro_doc'];
        if ($rpps === '' && !$attestation) {
            if ($email === '') {
                $email = $this->customerEmail($idCustomer);
            }
            $er = $this->getRppsByEmail($email);
            if ($er) {
                $rpps = (string) $er['rpps'];
                $attestation = (int) $er['attestation'];
                $proDoc = $er['pro_doc'] ? (string) $er['pro_doc'] : '';
            }
        }
        return ['rpps' => $rpps, 'attestation' => $attestation, 'pro_doc' => $proDoc];
    }

}
