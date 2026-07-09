<?php
/**
 * HFM Storefront API — bridge headless pour Next.js
 * Expose en JSON les flux que le webservice PrestaShop ne couvre pas proprement :
 * panier, checkout, client. Appelé en serveur-à-serveur par le backend Next.js
 * (protégé par un secret partagé), jamais directement par le navigateur.
 *
 * C'est aussi le MODULE DE PAIEMENT du front headless : les commandes passées via
 * le tunnel Next sont validées par ce module (validateOrder), donc rattachées
 * proprement à "hfmstorefront" dans le back-office, avec le bon libellé de paiement
 * (Carte bancaire PayPlug / Virement / Chèque).
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/cache.php';

class Hfmstorefront extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'hfmstorefront';
        $this->tab = 'payments_gateways';
        $this->version = '1.1.0';
        $this->author = 'HFM';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        parent::__construct();
        $this->displayName = $this->l('HFM Storefront (headless + paiement)');
        $this->description = $this->l('API JSON panier/checkout/client et passerelle de paiement pour un front Next.js headless.');

        // Module déjà installé : garantit l'enregistrement des hooks de purge ajoutés
        // après-coup (les hooks de revalidation ne passent pas forcément par install()).
        // Idempotent et no-op une fois enregistrés, dans le contexte back-office/CLI.
        if ($this->id && (defined('_PS_ADMIN_DIR_') || (php_sapi_name() === 'cli'))) {
            $this->ensurePurgeHooksRegistered();
        }
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        if (!Configuration::get('HFMSTOREFRONT_SECRET')) {
            Configuration::updateValue('HFMSTOREFRONT_SECRET', Tools::passwdGen(48));
        }
        Configuration::updateValue('HFMSTOREFRONT_CORS', 'http://localhost:3000');

        // Hooks de paiement : rend le module reconnu comme passerelle de paiement.
        $this->registerHook('paymentOptions');
        $this->registerHook('paymentReturn');
        // Affichage du RPPS praticien en haut de la fiche commande (back-office).
        $this->registerHook('displayAdminOrderTop');
        // Franco « Livraison offerte dès N € HT » : on applique le seuil en HORS TAXES
        // (PrestaShop compare nativement en TTC). Seuil paramétrable, défaut 400 € HT.
        if (Configuration::get('HFM_FREE_SHIPPING_HT') === false) {
            Configuration::updateValue('HFM_FREE_SHIPPING_HT', 400);
        }
        $this->registerHook('actionOverrideShippingFreePrice');

        // --- Revalidation / cache headless ---
        // Valeurs par défaut : l'admin renseignera le secret (même valeur que le front).
        if (Configuration::get('HFM_FRONT_URL') === false) {
            Configuration::updateValue('HFM_FRONT_URL', 'https://hyaluronic.vercel.app');
        }
        if (Configuration::get('HFM_REVALIDATE_SECRET') === false) {
            Configuration::updateValue('HFM_REVALIDATE_SECRET', '');
        }
        // Hooks de purge : invalident le cache backend + notifient le front à chaque
        // changement de catalogue / catégorie / page CMS en back-office.
        foreach ($this->purgeHooks() as $hook) {
            $this->registerHook($hook);
        }

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('HFMSTOREFRONT_SECRET');
        Configuration::deleteByName('HFMSTOREFRONT_CORS');

        return parent::uninstall();
    }

    /** Hooks d'objets ObjectModel qui doivent déclencher une purge de cache. */
    protected function purgeHooks()
    {
        return [
            'actionObjectProductAddAfter',
            'actionObjectProductUpdateAfter',
            'actionObjectProductDeleteAfter',
            'actionProductSave',
            'actionObjectCategoryAddAfter',
            'actionObjectCategoryUpdateAfter',
            'actionObjectCategoryDeleteAfter',
            'actionObjectCmsAddAfter',
            'actionObjectCmsUpdateAfter',
            'actionObjectCmsDeleteAfter',
            // Blog (module ph_simpleblog) : article ou catégorie modifié -> liste + article.
            'actionObjectSimpleBlogPostAddAfter',
            'actionObjectSimpleBlogPostUpdateAfter',
            'actionObjectSimpleBlogPostDeleteAfter',
            'actionObjectSimpleBlogCategoryAddAfter',
            'actionObjectSimpleBlogCategoryUpdateAfter',
            'actionObjectSimpleBlogCategoryDeleteAfter',
            // Stock : rupture / réappro changent 'quantity' et 'available' des cartes/fiches.
            'actionUpdateQuantity',
            // Prix spécifiques / promotions : changent price_incl_tax/excl_tax + liste « promo ».
            'actionObjectSpecificPriceAddAfter',
            'actionObjectSpecificPriceUpdateAfter',
            'actionObjectSpecificPriceDeleteAfter',
            // Fabricants : liste des marques (taxonomy) + nom de marque affiché dans les cartes.
            'actionObjectManufacturerAddAfter',
            'actionObjectManufacturerUpdateAfter',
            'actionObjectManufacturerDeleteAfter',
        ];
    }

    /**
     * Garantit l'enregistrement des hooks de purge même si le module est DÉJÀ installé
     * (les hooks ajoutés après-coup ne passent pas par install()). Idempotent :
     * registerHook ne duplique pas un hook déjà enregistré.
     */
    protected function ensurePurgeHooksRegistered()
    {
        foreach ($this->purgeHooks() as $hook) {
            if (!$this->isRegisteredInHook($hook)) {
                $this->registerHook($hook);
            }
        }
    }

    /**
     * Tunnel d'achat en headless : aucun bouton de paiement n'est rendu par le thème PS
     * (le front Next gère l'UI). On renvoie donc une liste vide tout en restant un module
     * de paiement valide pour validateOrder().
     */
    public function hookPaymentOptions($params)
    {
        return [];
    }

    public function hookPaymentReturn($params)
    {
        return '';
    }

    /**
     * Franco en HORS TAXES. La boutique annonce « Livraison offerte dès N € HT »,
     * mais PrestaShop compare nativement le total TTC au seuil. On ajuste donc le seuil
     * à la volée pour que la comparaison TTC revienne exactement à « total HT >= N »
     * (cohérent y compris pour les clients B2B exonérés, où HT = TTC).
     * Seuil : Configuration HFM_FREE_SHIPPING_HT (défaut 400). S'applique à toute la
     * boutique (front classique + tunnel headless), conformément à l'affichage.
     */
    public function hookActionOverrideShippingFreePrice($params)
    {
        $cart = Context::getContext()->cart;
        if (!Validate::isLoadedObject($cart)) {
            return;
        }
        $thresholdHT = (float) Configuration::get('HFM_FREE_SHIPPING_HT');
        if ($thresholdHT <= 0) {
            return;
        }
        // Total marchandise (hors livraison), HT et TTC, remises déduites.
        $ht = (float) $cart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING);
        $ttc = (float) $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
        // PS testera ensuite : (total TTC >= shippingFreePrice) ET (shippingFreePrice > 0).
        //  - seuil HT atteint  -> on pose shippingFreePrice = total TTC => franco déclenché ;
        //  - sinon             -> seuil hors d'atteinte => livraison facturée.
        if ($ht >= $thresholdHT) {
            $params['shippingFreePrice'] = $ttc > 0 ? $ttc : 0.01;
        } else {
            $params['shippingFreePrice'] = $ttc + 1000000.0;
        }
    }

    // ---------- Revalidation / purge de cache (back-office -> front headless) ----------
    //
    // À chaque modification de catalogue en BO, on (a) invalide le cache backend du bridge
    // pour les tags concernés (incrément de version, cf. HfmCache), puis (b) on notifie le
    // front Next.js via POST {HFM_FRONT_URL}/api/revalidate. Tout échec est SILENCIEUX :
    // une purge ne doit JAMAIS faire échouer l'enregistrement d'un produit/catégorie/CMS.

    /** Produit modifié -> les listes/fiches ET les compteurs de catégorie changent. */
    public function hookActionObjectProductAddAfter($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS, HfmCache::TAG_TAXONOMY]);
    }

    public function hookActionObjectProductUpdateAfter($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS, HfmCache::TAG_TAXONOMY]);
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS, HfmCache::TAG_TAXONOMY]);
    }

    /** Sauvegarde produit (édition BO complète) -> même périmètre que ci-dessus. */
    public function hookActionProductSave($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS, HfmCache::TAG_TAXONOMY]);
    }

    /** Catégorie modifiée -> menu/arbre (taxonomy) ET listes filtrées par catégorie. */
    public function hookActionObjectCategoryAddAfter($params)
    {
        $this->purge([HfmCache::TAG_TAXONOMY, HfmCache::TAG_PRODUCTS]);
    }

    public function hookActionObjectCategoryUpdateAfter($params)
    {
        $this->purge([HfmCache::TAG_TAXONOMY, HfmCache::TAG_PRODUCTS]);
    }

    public function hookActionObjectCategoryDeleteAfter($params)
    {
        $this->purge([HfmCache::TAG_TAXONOMY, HfmCache::TAG_PRODUCTS]);
    }

    /** Page CMS modifiée -> contenu éditorial. */
    public function hookActionObjectCmsAddAfter($params)
    {
        $this->purge([HfmCache::TAG_CONTENT]);
    }

    public function hookActionObjectCmsUpdateAfter($params)
    {
        $this->purge([HfmCache::TAG_CONTENT]);
    }

    public function hookActionObjectCmsDeleteAfter($params)
    {
        $this->purge([HfmCache::TAG_CONTENT]);
    }

    /** Article ou catégorie de blog (ph_simpleblog) modifié -> liste + article. */
    public function hookActionObjectSimpleBlogPostAddAfter($params)
    {
        $this->purge([HfmCache::TAG_BLOG]);
    }

    public function hookActionObjectSimpleBlogPostUpdateAfter($params)
    {
        $this->purge([HfmCache::TAG_BLOG]);
    }

    public function hookActionObjectSimpleBlogPostDeleteAfter($params)
    {
        $this->purge([HfmCache::TAG_BLOG]);
    }

    public function hookActionObjectSimpleBlogCategoryAddAfter($params)
    {
        $this->purge([HfmCache::TAG_BLOG]);
    }

    public function hookActionObjectSimpleBlogCategoryUpdateAfter($params)
    {
        $this->purge([HfmCache::TAG_BLOG]);
    }

    public function hookActionObjectSimpleBlogCategoryDeleteAfter($params)
    {
        $this->purge([HfmCache::TAG_BLOG]);
    }

    /** Stock modifié (rupture / réappro) -> 'quantity' et 'available' des produits. */
    public function hookActionUpdateQuantity($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS]);
    }

    /** Prix spécifique / promotion ajouté|modifié|supprimé -> prix + liste « promo ». */
    public function hookActionObjectSpecificPriceAddAfter($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS]);
    }

    public function hookActionObjectSpecificPriceUpdateAfter($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS]);
    }

    public function hookActionObjectSpecificPriceDeleteAfter($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS]);
    }

    /** Fabricant modifié -> liste des marques (taxonomy) ET nom de marque des cartes. */
    public function hookActionObjectManufacturerAddAfter($params)
    {
        $this->purge([HfmCache::TAG_TAXONOMY, HfmCache::TAG_PRODUCTS]);
    }

    public function hookActionObjectManufacturerUpdateAfter($params)
    {
        $this->purge([HfmCache::TAG_TAXONOMY, HfmCache::TAG_PRODUCTS]);
    }

    public function hookActionObjectManufacturerDeleteAfter($params)
    {
        $this->purge([HfmCache::TAG_TAXONOMY, HfmCache::TAG_PRODUCTS]);
    }

    /**
     * Purge effective : (a) flush backend PS (incrément de version par tag) ;
     * (b) notification du front (POST /api/revalidate). Best effort, jamais bloquant.
     *
     * @param string[] $tags Tags fermés : taxonomy | products | content.
     */
    protected function purge(array $tags)
    {
        // (a) Flush backend : toujours, indépendant de la connectivité front.
        try {
            HfmCache::flushTags($tags);
        } catch (\Throwable $e) {
            // Silencieux.
        }
        // (b) Notification du front.
        $this->notifyFront($tags);
    }

    /**
     * POST {HFM_FRONT_URL}/api/revalidate  (en-tête x-hfm-revalidate-secret, corps {tags}).
     * Ne poste RIEN si l'URL ou le secret sont vides. Timeout court, échec silencieux.
     */
    protected function notifyFront(array $tags)
    {
        $frontUrl = rtrim((string) Configuration::get('HFM_FRONT_URL'), '/');
        $secret = (string) Configuration::get('HFM_REVALIDATE_SECRET');
        if ($frontUrl === '' || $secret === '') {
            // Dégradation gracieuse : rien à notifier (log discret possible).
            return;
        }

        $url = $frontUrl . '/api/revalidate';
        $body = json_encode(['tags' => array_values(array_unique($tags))], JSON_UNESCAPED_SLASHES);

        try {
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $body,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'x-hfm-revalidate-secret: ' . $secret,
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 3,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_FOLLOWLOCATION => false,
                ]);
                curl_exec($ch);
                curl_close($ch);
                return;
            }

            // Repli sans cURL : POST via stream context, timeout court.
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n"
                        . 'x-hfm-revalidate-secret: ' . $secret . "\r\n",
                    'content' => $body,
                    'timeout' => 3,
                    'ignore_errors' => true,
                ],
            ]);
            @Tools::file_get_contents($url, false, $context);
        } catch (\Throwable $e) {
            // Silencieux : ne jamais faire échouer l'enregistrement en BO.
        }
    }

    /**
     * Back-office, haut de la fiche commande : affiche le N° RPPS du praticien
     * UNIQUEMENT si le client de la commande en a renseigné un (lecture conditionnelle,
     * aucune donnée ajoutée à la commande). Badge vert si rapproché au registre ANS local.
     */
    public function hookDisplayAdminOrderTop($params)
    {
        $idOrder = (int) (isset($params['id_order']) ? $params['id_order'] : 0);
        if (!$idOrder) {
            return '';
        }
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }
        $db = Db::getInstance();
        $rpps = '';
        $attestation = 0;
        $proDoc = '';

        // 1) Données "pro" rattachées à la COMMANDE (cas commande invité).
        $hasOrderTable = (bool) $db->getValue(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . _DB_PREFIX_ . "hfm_order_pro_doc'",
            false
        );
        if ($hasOrderTable) {
            $orow = $db->getRow('SELECT rpps, attestation, pro_doc FROM `' . _DB_PREFIX_ . 'hfm_order_pro_doc` WHERE id_order = ' . (int) $idOrder, false);
            if ($orow) {
                $rpps = (string) $orow['rpps'];
                $attestation = (int) $orow['attestation'];
                $proDoc = $orow['pro_doc'] ? (string) $orow['pro_doc'] : '';
            }
        }

        // 2) Sinon, données sur le CLIENT (cas client connecté, réutilisable).
        if ($rpps === '' && !$attestation && $proDoc === '') {
            $hasCustTable = (bool) $db->getValue(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . _DB_PREFIX_ . "hfm_customer_rpps'",
                false
            );
            if ($hasCustTable) {
                $crow = $db->getRow('SELECT rpps, attestation, pro_doc FROM `' . _DB_PREFIX_ . 'hfm_customer_rpps` WHERE id_customer = ' . (int) $order->id_customer, false);
                if ($crow) {
                    $rpps = (string) $crow['rpps'];
                    $attestation = (int) $crow['attestation'];
                    $proDoc = $crow['pro_doc'] ? (string) $crow['pro_doc'] : '';
                }
            }
        }

        if ($rpps === '' && !$attestation) {
            return '';
        }

        // Cas 1 : numéro RPPS fourni -> rapprochement au registre local (si importé).
        if ($rpps !== '') {
            $found = false;
            $praticien = '';
            $hasReg = (bool) $db->getValue(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . _DB_PREFIX_ . "hfm_rpps_registry'",
                false
            );
            if ($hasReg) {
                $row = $db->getRow('SELECT nom, prenom, profession FROM `' . _DB_PREFIX_ . 'hfm_rpps_registry` WHERE rpps = \'' . pSQL($rpps) . '\'', false);
                if ($row) {
                    $found = true;
                    $praticien = trim($row['prenom'] . ' ' . $row['nom'] . ($row['profession'] ? ' — ' . $row['profession'] : ''));
                }
            }

            $rppsHtml = htmlspecialchars($rpps, ENT_QUOTES, 'UTF-8');
            if ($found) {
                $badge = '<span style="display:inline-flex;align-items:center;gap:6px;background:#e6f4ea;color:#1e7e34;border:1px solid #b7dfc3;border-radius:999px;padding:4px 12px;font-size:12px;font-weight:600;">&#10003; Vérifié au registre ANS</span>';
                $detail = '<div style="font-size:12px;color:#5a6a52;margin-top:6px;">Praticien : <strong>' . htmlspecialchars($praticien, ENT_QUOTES, 'UTF-8') . '</strong></div>';
                $accent = '#1e7e34';
            } else {
                $badge = '<span style="display:inline-flex;align-items:center;gap:6px;background:#fff4e5;color:#9a6700;border:1px solid #ffd8a8;border-radius:999px;padding:4px 12px;font-size:12px;font-weight:600;">&#9888; À confirmer manuellement</span>';
                $detail = '<div style="font-size:12px;color:#9a6700;margin-top:6px;">Non rapproché au registre ANS — à vérifier.</div>';
                $accent = '#9a6700';
            }

            return '<div class="card mb-2" style="border-left:4px solid ' . $accent . ';">'
                . '<div class="card-body" style="padding:14px 18px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">'
                . '<div style="font-size:12px;font-weight:700;letter-spacing:.04em;color:#6b7280;text-transform:uppercase;">Numéro RPPS praticien</div>'
                . '<div style="font-size:18px;font-weight:700;color:#1f2937;letter-spacing:.02em;">' . $rppsHtml . '</div>'
                . $badge
                . '<div style="flex-basis:100%;">' . $detail . '</div>'
                . '</div></div>';
        }

        // Cas 2 : pas de numéro RPPS, mais attestation "professionnel de santé".
        if ($proDoc !== '') {
            $url = rtrim(Tools::getShopDomainSsl(true) . __PS_BASE_URI__, '/') . '/modules/hfmstorefront/pro_docs/' . rawurlencode($proDoc);
            $docHtml = '<div style="font-size:12px;color:#5a6a52;margin-top:6px;">Justificatif fourni : <a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank"><strong>télécharger</strong></a> — à vérifier.</div>';
        } else {
            $docHtml = '<div style="font-size:12px;color:#9a6700;margin-top:6px;">Aucun justificatif joint — à recevoir par email / à vérifier.</div>';
        }

        return '<div class="card mb-2" style="border-left:4px solid #1e7e34;">'
            . '<div class="card-body" style="padding:14px 18px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">'
            . '<div style="font-size:12px;font-weight:700;letter-spacing:.04em;color:#6b7280;text-transform:uppercase;">Professionnel de santé</div>'
            . '<span style="display:inline-flex;align-items:center;gap:6px;background:#e6f4ea;color:#1e7e34;border:1px solid #b7dfc3;border-radius:999px;padding:4px 12px;font-size:12px;font-weight:600;">&#10003; Attestation sur l\'honneur</span>'
            . '<div style="flex-basis:100%;">' . $docHtml . '</div>'
            . '</div></div>';
    }

    // ---------- Éditeur de pages CMS multilingues (table hfm_cms_i18n) ----------

    /** Pages légales éditables (slug => libellé BO). */
    protected function cmsPages()
    {
        return [
            'livraison-retours' => 'Livraison & retours',
            'conditions-generales-de-ventes' => 'Conditions générales de vente',
            'politique-de-confidentialite' => 'Politique de confidentialité',
        ];
    }

    /** Les 22 langues du front (locale => nom FR). */
    protected function cmsLocales()
    {
        return [
            'fr' => 'Français', 'en' => 'Anglais', 'de' => 'Allemand', 'es' => 'Espagnol',
            'it' => 'Italien', 'pt' => 'Portugais', 'nl' => 'Néerlandais', 'pl' => 'Polonais',
            'ja' => 'Japonais', 'zh' => 'Chinois', 'ko' => 'Coréen', 'ar' => 'Arabe',
            'he' => 'Hébreu', 'sv' => 'Suédois', 'no' => 'Norvégien', 'da' => 'Danois',
            'fi' => 'Finnois', 'cs' => 'Tchèque', 'el' => 'Grec', 'ro' => 'Roumain',
            'bg' => 'Bulgare', 'sl' => 'Slovène',
        ];
    }

    protected function cmsTable()
    {
        return _DB_PREFIX_ . 'hfm_cms_i18n';
    }

    protected function ensureCmsTable()
    {
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . $this->cmsTable() . '` (
                `slug` VARCHAR(128) NOT NULL, `locale` VARCHAR(8) NOT NULL,
                `title` VARCHAR(255) DEFAULT NULL, `content` LONGTEXT,
                `date_upd` DATETIME NOT NULL, PRIMARY KEY (`slug`,`locale`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
    }

    /**
     * Config du module (BO) : infos API + éditeur des pages CMS multilingues.
     */
    public function getContent()
    {
        $this->ensureCmsTable();
        $db = Db::getInstance();
        $pages = $this->cmsPages();
        $locales = $this->cmsLocales();
        $table = $this->cmsTable();
        $confirm = '';

        // Sélection courante (POST prioritaire, sinon GET, sinon défauts).
        $slug = (string) Tools::getValue('hfm_slug', array_key_first($pages));
        $locale = (string) Tools::getValue('hfm_locale', 'fr');
        if (!isset($pages[$slug])) {
            $slug = array_key_first($pages);
        }
        if (!isset($locales[$locale])) {
            $locale = 'fr';
        }

        // Enregistrement.
        if (Tools::isSubmit('submitHfmCms')) {
            $title = trim((string) Tools::getValue('hfm_title'));
            // Contenu HTML : on récupère la valeur brute sans nettoyage HTML.
            $content = (string) Tools::getValue('hfm_content');
            $db->execute(
                'INSERT INTO `' . $table . '` (slug, locale, title, content, date_upd)
                 VALUES (\'' . pSQL($slug) . '\', \'' . pSQL($locale) . '\', \'' . pSQL($title) . '\', \'' . pSQL($content, true) . '\', NOW())
                 ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), date_upd = NOW()'
            );
            // L'éditeur CMS multilingue est la source des pages lues par le controller
            // content (tag content). Sans cette purge, une édition resterait invisible
            // jusqu'à expiration du TTL (backend 3600s + Redis + CDN).
            $this->purge([HfmCache::TAG_CONTENT]);
            $confirm = $this->displayConfirmation(
                'Page « ' . htmlspecialchars($pages[$slug]) . ' » (' . htmlspecialchars($locales[$locale]) . ') enregistrée.'
            );
        }

        // Valeurs courantes pour le formulaire.
        $row = $db->getRow(
            'SELECT title, content FROM `' . $table . '` WHERE slug = \'' . pSQL($slug) . '\' AND locale = \'' . pSQL($locale) . '\'',
            false
        );
        $curTitle = $row ? (string) $row['title'] : '';
        $curContent = $row ? (string) $row['content'] : '';

        // Couverture par page (nb de langues remplies) pour info.
        $coverage = [];
        foreach (array_keys($pages) as $s) {
            $coverage[$s] = (int) $db->getValue(
                'SELECT COUNT(*) FROM `' . $table . '` WHERE slug = \'' . pSQL($s) . '\' AND TRIM(content) <> \'\'',
                false
            );
        }

        $token = Tools::getAdminTokenLite('AdminModules');
        $action = 'index.php?controller=AdminModules&configure=' . $this->name . '&token=' . $token;
        $total = count($locales);

        // URL d'aperçu front (origine CORS).
        $cors = array_filter(array_map('trim', explode(',', (string) Configuration::get('HFMSTOREFRONT_CORS'))));
        $frontBase = $cors ? rtrim(reset($cors), '/') : '';
        $previewUrl = $frontBase ? $frontBase . '/' . $locale . '/content/' . $slug : '';

        $out = $confirm;

        // --- Éditeur CMS ---
        $out .= '<div class="panel">';
        $out .= '<h3><i class="icon icon-file-text"></i> Pages CMS multilingues (front headless)</h3>';
        $out .= '<p class="text-muted">Édite le titre et le contenu HTML des pages légales du front, dans chacune des ' . $total . ' langues. '
              . 'Les modifications sont visibles immédiatement sur le front.</p>';

        // Sélecteurs (auto-submit pour charger la page/langue).
        $out .= '<form method="post" action="' . $action . '" id="hfm-cms-select" class="form-horizontal" style="margin-bottom:14px;">';
        $out .= '<div class="row"><div class="col-lg-5">';
        $out .= '<label>Page</label><select name="hfm_slug" class="form-control" onchange="document.getElementById(\'hfm-cms-select\').submit();">';
        foreach ($pages as $s => $label) {
            $out .= '<option value="' . htmlspecialchars($s) . '"' . ($s === $slug ? ' selected' : '') . '>'
                  . htmlspecialchars($label) . ' (' . $coverage[$s] . '/' . $total . ' langues)</option>';
        }
        $out .= '</select></div>';
        $out .= '<div class="col-lg-4">';
        $out .= '<label>Langue</label><select name="hfm_locale" class="form-control" onchange="document.getElementById(\'hfm-cms-select\').submit();">';
        foreach ($locales as $lc => $name) {
            $filled = (bool) $db->getValue('SELECT COUNT(*) FROM `' . $table . '` WHERE slug = \'' . pSQL($slug) . '\' AND locale = \'' . pSQL($lc) . '\' AND TRIM(content) <> \'\'', false);
            $out .= '<option value="' . htmlspecialchars($lc) . '"' . ($lc === $locale ? ' selected' : '') . '>'
                  . htmlspecialchars($name) . ' (' . htmlspecialchars($lc) . ')' . ($filled ? '' : ' — vide') . '</option>';
        }
        $out .= '</select></div></div></form>';

        // Formulaire d'édition.
        $out .= '<form method="post" action="' . $action . '" class="form-horizontal">';
        $out .= '<input type="hidden" name="hfm_slug" value="' . htmlspecialchars($slug) . '"/>';
        $out .= '<input type="hidden" name="hfm_locale" value="' . htmlspecialchars($locale) . '"/>';
        $out .= '<div class="form-group"><label class="control-label col-lg-2">Titre</label><div class="col-lg-10">';
        $out .= '<input type="text" name="hfm_title" class="form-control" value="' . htmlspecialchars($curTitle, ENT_QUOTES, 'UTF-8') . '"/>';
        $out .= '</div></div>';
        $out .= '<div class="form-group"><label class="control-label col-lg-2">Contenu (HTML)</label><div class="col-lg-10">';
        $out .= '<textarea name="hfm_content" rows="22" class="form-control" style="font-family:Menlo,Consolas,monospace;font-size:12.5px;line-height:1.5;">'
              . htmlspecialchars($curContent, ENT_QUOTES, 'UTF-8') . '</textarea>';
        $out .= '<p class="help-block">HTML autorisé (titres, paragraphes, listes, liens). Les chemins relatifs d\'images/liens sont automatiquement préfixés vers la boutique.</p>';
        $out .= '</div></div>';
        $out .= '<div class="panel-footer">';
        $out .= '<button type="submit" name="submitHfmCms" class="btn btn-default pull-right"><i class="process-icon-save"></i> Enregistrer</button>';
        if ($previewUrl) {
            $out .= '<a href="' . htmlspecialchars($previewUrl) . '" target="_blank" class="btn btn-default"><i class="icon icon-external-link"></i> Aperçu front</a>';
        }
        $out .= '</div>';
        $out .= '</form>';
        $out .= '</div>';

        // --- Infos API (existant) ---
        $secret = Configuration::get('HFMSTOREFRONT_SECRET');
        $base = $this->context->link->getModuleLink($this->name, 'cart');
        $out .= '<div class="panel"><h3>HFM Storefront API</h3>';
        $out .= '<p><strong>Secret (en-tête <code>X-Storefront-Token</code>) :</strong> <code>' . htmlspecialchars($secret) . '</code></p>';
        $out .= '<p><strong>Endpoint panier :</strong> <code>' . htmlspecialchars($base) . '</code></p>';
        $out .= '<p>Autres endpoints : <code>customer</code>, <code>checkout</code>, <code>products</code>, <code>taxonomy</code>, <code>orders</code>, <code>wishlist</code>, <code>content</code> (même base, remplacer le contrôleur).</p>';
        $out .= '<p>CORS autorisé : <code>' . htmlspecialchars(Configuration::get('HFMSTOREFRONT_CORS')) . '</code></p>';
        $out .= '<p>Passerelle de paiement headless : les commandes sont validées via ce module.</p>';
        $out .= '</div>';

        return $out;
    }
}
