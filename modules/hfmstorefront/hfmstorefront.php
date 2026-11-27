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
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/country_rules.php';
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/pro_rules.php';
require_once __DIR__ . '/lib/viva_admin.php';

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
        $this->registerHook('displayAdminOrderMain');
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
        // Règles de prix par pays / groupe (table groupinc_configuration), cf. lib/country_rules.php.
        $this->registerHook('actionProductPriceCalculation');
        // Boutique native (thème PrestaShop) renvoyée vers le front : voir hookActionDispatcherBefore.
        $this->registerHook('actionDispatcherBefore');

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
            // Règles de prix (écran « Majorations et remises » du module groupinc) : prix et liste
            // des pays/groupes concernés changent -> purge immédiate.
            'actionObjectGroupincConfigurationAddAfter',
            'actionObjectGroupincConfigurationUpdateAfter',
            'actionObjectGroupincConfigurationDeleteAfter',
            // Pays ouvert / fermé (International > Zones géographiques > Pays) : la liste des pays
            // du formulaire d'adresse (taxonomy, action=countries) doit suivre tout de suite.
            'actionObjectCountryUpdateAfter',
        ];
    }

    /**
     * Garantit l'enregistrement des hooks de purge même si le module est DÉJÀ installé
     * (les hooks ajoutés après-coup ne passent pas par install()). Idempotent :
     * registerHook ne duplique pas un hook déjà enregistré.
     */
    protected function ensurePurgeHooksRegistered()
    {
        $this->ensureVivaTab();
        // + hooks fonctionnels ajoutés après l'installation (règles de prix par pays/groupe).
        foreach (array_merge($this->purgeHooks(), ['actionProductPriceCalculation', 'actionSetInvoice', 'displayInvoiceLegalFreeText', 'displayPDFOrderSlip', 'actionDispatcherBefore', 'displayAdminOrderMain']) as $hook) {
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
    /**
     * Le back n'est PAS un site public. PrestaShop y sert pourtant encore sa boutique native (thème
     * d'origine : accueil, fiches, création de compte…) sur le domaine du back. Toute page de cette
     * boutique est renvoyée vers la page équivalente du front (HFM_FRONT_URL).
     *
     * Ne concerne QUE les contrôleurs natifs passés par index.php (Dispatcher::FC_FRONT). Restent
     * intacts : l'API du front et tous les contrôleurs de modules (fc=module), le back-office, le
     * webservice, les images et fichiers statiques, les scripts à la racine (flux Google…).
     * Coupe-circuit sans déploiement : HFM_NATIVE_REDIRECT = 0 dans ps_configuration.
     * 302 et non 301 : l'adresse du front change le jour de la bascule (pré-prod -> vrai domaine).
     */
    public function hookActionDispatcherBefore($params)
    {
        if ((int) ($params['controller_type'] ?? 0) !== Dispatcher::FC_FRONT || php_sapi_name() === 'cli') {
            return;
        }
        if ((string) Configuration::get('HFM_NATIVE_REDIRECT') === '0') {
            return;
        }
        $front = rtrim((string) Configuration::get('HFM_FRONT_URL'), '/');
        $frontHost = (string) parse_url($front, PHP_URL_HOST);
        // Pas d'adresse de front exploitable, ou front servi par CE domaine : on ne touche à rien
        // (jamais de boucle de redirection).
        if (!preg_match('#^https?://#i', $front) || $frontHost === ''
            || strcasecmp($frontHost, (string) Tools::getHttpHost(false, false, true)) === 0) {
            return;
        }
        $controller = Tools::strtolower(str_replace('-', '', (string) Dispatcher::getInstance()->getController()));
        // Laissés à PrestaShop : la page 404 (un fichier ou une image manquante doit rester une vraie
        // erreur 404, pas une redirection vers l'accueil) et les téléchargements de fichiers.
        if (in_array($controller, ['404', 'pagenotfound', 'attachment', 'getfile', 'pdfinvoice', 'pdforderslip', 'pdforderreturn', 'upload', 'statistics'], true)) {
            return;
        }
        header('Cache-Control: no-store');
        header('X-Robots-Tag: noindex, nofollow');
        header('Location: ' . $front . $this->nativePageToFrontPath($controller), true, 302);
        exit;
    }

    /**
     * Chemin du front équivalent à une page native. Sans préfixe de langue : le front choisit
     * (cookie du visiteur, sinon langue du navigateur). Inconnu -> accueil.
     */
    protected function nativePageToFrontPath($controller)
    {
        $id = function ($name) {
            return (int) Tools::getValue($name);
        };
        switch ($controller) {
            case 'product':
                return $id('id_product') > 0 ? '/produit/' . $id('id_product') : '/catalogue';
            case 'category':
                return $id('id_category') > 0 ? '/catalogue?category=' . $id('id_category') : '/catalogue';
            case 'manufacturer':
                return $id('id_manufacturer') > 0 ? '/marque/' . $id('id_manufacturer') : '/marque';
            case 'search':
                $q = trim((string) Tools::getValue('s', Tools::getValue('search_query', '')));

                return $q !== '' ? '/catalogue?q=' . rawurlencode(Tools::substr($q, 0, 80)) : '/catalogue';
            case 'supplier':
            case 'bestsales':
            case 'newproducts':
            case 'pricesdrop':
                return '/catalogue';
            case 'cms':
                // Le front résout le slug d'une page CMS dans n'importe quelle langue.
                $slug = $id('id_cms') > 0 ? (string) Db::getInstance()->getValue(
                    'SELECT link_rewrite FROM ' . _DB_PREFIX_ . 'cms_lang WHERE id_cms = ' . $id('id_cms')
                    . ' AND id_lang = ' . (int) Configuration::get('PS_LANG_DEFAULT') . ' AND id_shop = ' . (int) $this->context->shop->id
                ) : '';

                return preg_match('/^[a-zA-Z0-9_-]+$/', $slug) ? '/content/' . $slug : '';
            case 'contact':
            case 'contactform':
                return '/contact';
            case 'cart':
            case 'order':
            case 'orderconfirmation':
                return '/checkout';
            case 'history':
            case 'orderdetail':
                return '/compte/commandes';
            case 'auth':
            case 'authentication':
            case 'registration':
            case 'myaccount':
            case 'identity':
            case 'address':
            case 'addresses':
            case 'discount':
            case 'orderslip':
            case 'orderfollow':
            case 'orderreturn':
                return '/compte';
            case 'password':
                return '/mot-de-passe-oublie';
            case 'guesttracking':
                return '/suivi-commande';
            default:
                return '';
        }
    }

    /**
     * Majorations / remises par pays, groupe, fabricant… (règles groupinc) appliquées par le
     * moteur de prix NATIF de PrestaShop 9 : catalogue, panier, commande et facture cohérents.
     */
    public function hookActionProductPriceCalculation($params)
    {
        HfmCountryRules::applyPrice($params);
    }

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

    public function hookActionObjectGroupincConfigurationAddAfter($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS, HfmCache::TAG_TAXONOMY]);
    }

    public function hookActionObjectGroupincConfigurationUpdateAfter($params)
    {
        $this->purge([HfmCache::TAG_PRODUCTS, HfmCache::TAG_TAXONOMY]);
    }

    public function hookActionObjectGroupincConfigurationDeleteAfter($params)
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

    /** Pays modifié (ouvert / fermé) -> liste des pays du formulaire d'adresse (taxonomy). */
    public function hookActionObjectCountryUpdateAfter($params)
    {
        $this->purge([HfmCache::TAG_TAXONOMY]);
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

    /**
     * La facture n'est émise qu'à l'expédition (cf. deploy/preprod-facture-expedition-*.sql), souvent
     * par le module DHL qui change le statut sans « use_existing_payment » : PS crée alors la facture
     * sans y rattacher le paiement encaissé à la commande. On le rattache comme le fait le BO.
     */
    public function hookActionSetInvoice($params)
    {
        $invoice = $params['OrderInvoice'] ?? null;
        if (!$invoice instanceof OrderInvoice) {
            return null;
        }
        $db = Db::getInstance();
        // Bug du cœur PrestaShop (constaté le 18/11, commandes 51622-51624) : quand le bon de livraison est
        // créé AVANT la facture (« Paiement accepté » a delivery=1 / invoice=0 sur cette boutique), setInvoice()
        // réutilise la même ligne order_invoice et rappelle setInvoiceDetails() -> la TVA du transport (et de
        // l'emballage) est enregistrée DEUX fois dans order_invoice_tax ; la facture affiche alors une ligne
        // de TVA transport en trop (montant réparti en deux). Une ligne par (type, taxe) suffit : on retire
        // les doublons.
        foreach ($db->executeS('SELECT `type`, id_tax, COUNT(*) n FROM `' . _DB_PREFIX_ . 'order_invoice_tax`
            WHERE id_order_invoice = ' . (int) $invoice->id . ' GROUP BY `type`, id_tax HAVING COUNT(*) > 1') ?: [] as $dup) {
            $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'order_invoice_tax` WHERE id_order_invoice = ' . (int) $invoice->id
                . ' AND `type` = \'' . pSQL($dup['type']) . '\' AND id_tax = ' . (int) $dup['id_tax'] . ' LIMIT ' . ((int) $dup['n'] - 1));
        }
        if (!empty($params['use_existing_payment'])) {
            return null;
        }
        $payments = $db->executeS(
            'SELECT DISTINCT op.id_order_payment FROM `' . _DB_PREFIX_ . 'order_payment` op
             INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON o.reference = op.order_reference
             LEFT JOIN `' . _DB_PREFIX_ . 'order_invoice_payment` oip ON oip.id_order_payment = op.id_order_payment
             WHERE oip.id_order_payment IS NULL AND o.id_order = ' . (int) $invoice->id_order
        ) ?: [];
        foreach ($payments as $p) {
            $db->insert('order_invoice_payment', [
                'id_order_invoice' => (int) $invoice->id,
                'id_order_payment' => (int) $p['id_order_payment'],
                'id_order' => (int) $invoice->id_order,
            ]);
        }
        if ($payments) {
            Cache::clean('order_invoice_paid_*');
        }
        return null; // ne pas imposer de numéro de facture
    }

    /**
     * Mentions légales de la facture (remplacent PS_INVOICE_LEGAL_FREE_TEXT, qui affichait en
     * anglais le n° de TVA d'une autre société) : exonération avec le BON article du CGI, SIREN de
     * l'acheteur pro (facturation électronique), état du règlement, conditions de retard entre
     * professionnels. En français ; traduction anglaise en dessous si la commande est dans une
     * autre langue. Texte brut (le modèle échappe et passe à la ligne).
     */
    public function hookDisplayInvoiceLegalFreeText($params)
    {
        $order = $params['order'] ?? null;
        if (!$order instanceof Order || !Validate::isLoadedObject($order)) {
            return '';
        }
        $mentions = [];
        if ($exemption = $this->vatExemptionMention($order)) {
            $mentions[] = $exemption;
        }
        $customer = new Customer((int) $order->id_customer);
        $siret = Validate::isLoadedObject($customer) ? preg_replace('/\D+/', '', (string) $customer->siret) : '';
        if (Tools::strlen($siret) === 14) {
            $siren = substr($siret, 0, 3) . ' ' . substr($siret, 3, 3) . ' ' . substr($siret, 6, 3);
            $mentions[] = ['SIREN de l\'acheteur : ' . $siren . ' (SIRET ' . $siret . ').', 'Buyer SIREN: ' . $siren . ' (SIRET ' . $siret . ').'];
        }
        $paid = 0.0;
        $paidOn = '';
        foreach (OrderPayment::getByOrderReference($order->reference) as $payment) {
            $paid += (float) $payment->amount;
            $paidOn = $paidOn ?: (string) $payment->date_add;
        }
        if ($paid + 0.01 >= (float) $order->total_paid_tax_incl && $paidOn !== '') {
            $d = date('d/m/Y', strtotime($paidOn));
            $mentions[] = ['Facture acquittée le ' . $d . ' (' . $order->payment . ').', 'Invoice paid on ' . $d . ' (' . $order->payment . ').'];
        } else {
            $mentions[] = ['Règlement à réception de facture (' . $order->payment . ').', 'Payment due upon receipt of invoice (' . $order->payment . ').'];
        }
        $mentions[] = [
            'Pas d\'escompte pour paiement anticipé. Tout retard de paiement entraîne des pénalités au taux de trois fois '
                . 'le taux d\'intérêt légal et une indemnité forfaitaire pour frais de recouvrement de 40 € '
                . '(articles L441-10 et D441-5 du Code de commerce).',
            'No discount for early payment. Late payment incurs penalties at three times the French legal interest rate '
                . 'and a fixed recovery fee of €40 (French Commercial Code, articles L441-10 and D441-5).',
        ];
        return implode("\n", $this->bilingual($mentions, $order));
    }

    /**
     * Avoir : rappel obligatoire de la facture d'origine + mention d'exonération identique à la
     * facture. HTML (bloc de bas de page du modèle d'avoir).
     */
    public function hookDisplayPDFOrderSlip($params)
    {
        $slip = $params['object'] ?? null;
        $order = $slip && !empty($slip->id_order) ? new Order((int) $slip->id_order) : null;
        if (!$order || !Validate::isLoadedObject($order)) {
            return '';
        }
        $mentions = [];
        $invoice = $order->hasInvoice() ? $order->getInvoicesCollection()->getFirst() : null;
        if ($invoice && Validate::isLoadedObject($invoice)) {
            $number = $invoice->getInvoiceNumberFormatted((int) $order->id_lang, (int) $order->id_shop);
            $d = date('d/m/Y', strtotime((string) $invoice->date_add));
            $mentions[] = [
                'Avoir relatif à la facture n° ' . $number . ' du ' . $d . ' (commande ' . $order->reference . ').',
                'Credit note for invoice ' . $number . ' dated ' . $d . ' (order ' . $order->reference . ').',
            ];
        } else {
            $mentions[] = ['Avoir relatif à la commande ' . $order->reference . '.', 'Credit note for order ' . $order->reference . '.'];
        }
        if ($exemption = $this->vatExemptionMention($order)) {
            $mentions[] = $exemption;
        }
        return '<table style="width:100%;font-size:8pt;color:#444"><tr><td>'
            . implode('<br />', array_map(function ($l) { return htmlspecialchars($l, ENT_QUOTES, 'UTF-8'); }, $this->bilingual($mentions, $order)))
            . '</td></tr></table>';
    }

    /**
     * [fr, en] -> lignes : le français (langue légale de la facture), plus l'anglais entre
     * parenthèses pour une commande passée dans une autre langue (question client 28/10).
     */
    protected function bilingual(array $mentions, Order $order)
    {
        $foreign = Tools::strtolower((string) Language::getIsoById((int) $order->id_lang)) !== 'fr';
        $out = [];
        foreach ($mentions as $m) {
            $out[] = $m[0];
            if ($foreign) {
                $out[] = '(' . $m[1] . ')';
            }
        }
        return $out;
    }

    /**
     * Mention d'exonération de TVA [fr, en] d'une commande vendue sans TVA, selon la destination :
     * livraison intracommunautaire de biens (262 ter I, n° de TVA de l'acquéreur) ou exportation
     * hors UE (262 I). null si la commande porte de la TVA.
     */
    protected function vatExemptionMention(Order $order)
    {
        $tax = (float) $order->total_paid_tax_incl - (float) $order->total_paid_tax_excl;
        if ($tax > 0.01 || (float) $order->total_paid_tax_excl <= 0) {
            return null;
        }
        $delivery = new Address((int) $order->id_address_delivery);
        $iso = Validate::isLoadedObject($delivery) ? Tools::strtoupper((string) Country::getIsoById((int) $delivery->id_country)) : '';
        if ($iso === '' || $iso === 'FR' || $iso === 'COS' || $iso === 'MC') {
            return null;
        }
        $eu = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT',
            'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];
        if (in_array($iso, $eu, true)) {
            $invoiceAddress = new Address((int) $order->id_address_invoice);
            $vat = Validate::isLoadedObject($invoiceAddress) ? trim((string) $invoiceAddress->vat_number) : '';
            return [
                'Exonération de TVA : livraison intracommunautaire de biens (article 262 ter I du CGI)'
                    . ($vat !== '' ? ' — n° de TVA intracommunautaire de l\'acquéreur : ' . $vat : '') . '.',
                'VAT exempt: intra-Community supply of goods (article 138 of Directive 2006/112/EC)'
                    . ($vat !== '' ? ' — buyer VAT number: ' . $vat : '') . '.',
            ];
        }
        return [
            'Exonération de TVA : exportation hors de l\'Union européenne (article 262 I du CGI).',
            'VAT exempt: export outside the European Union (article 146 of Directive 2006/112/EC).',
        ];
    }

    /** Stock modifié (rupture / réappro) -> 'quantity' et 'available' des produits. */
    public function hookActionUpdateQuantity($params)
    {
        // Le front n'affiche que la disponibilité (en stock / rupture), jamais la quantité : on ne vide
        // le cache produits que si le produit PASSE en rupture ou REVIENT en stock. Avant, chaque commande
        // vidait tout le cache produits et faisait recalculer toutes les pages d'un coup sur le serveur.
        // Variation inconnue (delta_quantity absent) : purge par sécurité.
        if (isset($params['quantity'], $params['delta_quantity']) && $params['delta_quantity'] !== null) {
            $after = (int) $params['quantity'];
            $before = $after - (int) $params['delta_quantity'];
            if (($before > 0) === ($after > 0)) {
                return;
            }
        }
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
    /**
     * Fiche commande : bloc Viva puis « Déclaration professionnelle », sous les onglets État /
     * Documents / Transporteurs et AVANT la traçabilité des lots (fdlots, même crochet, position
     * suivante) — ordre demandé le 17/11. Anciennement en haut de page (displayAdminOrderTop).
     */
    public function hookDisplayAdminOrderMain($params)
    {
        $idOrder = (int) (isset($params['id_order']) ? $params['id_order'] : 0);
        return $idOrder ? $this->vivaOrderCard($idOrder) . $this->amazonOrderCard($idOrder) . $this->proDeclarationCard($idOrder) : '';
    }

    /** Onglet caché AdminHfmViva (action de remboursement du bloc Viva). Idempotent. */
    protected function ensureVivaTab()
    {
        if ((int) Tab::getIdFromClassName('AdminHfmViva')) {
            return;
        }
        $tab = new Tab();
        $tab->class_name = 'AdminHfmViva';
        $tab->module = $this->name;
        $tab->id_parent = -1;
        $tab->active = 1;
        foreach (Language::getLanguages(false) as $l) {
            $tab->name[(int) $l['id_lang']] = 'Remboursement Viva';
        }
        $tab->add();
    }

    /**
     * Bloc « Viva — paiement par carte » de la fiche commande (comme le bloc PayPlug de l'ancien site) :
     * transaction, statut et montant lus chez Viva, remboursements déjà faits, et formulaire
     * d'annulation / remboursement (total si le montant est vide).
     */
    protected function vivaOrderCard($idOrder)
    {
        $order = new Order((int) $idOrder);
        if (!Validate::isLoadedObject($order) || !($tx = HfmVivaAdmin::orderTransaction($order))) {
            return '';
        }
        $e = function ($v) {
            return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        };
        $money = function ($v) {
            return number_format((float) $v, 2, ',', ' ') . ' EUR';
        };
        $flash = '';
        $flashKey = 'HFM_VIVA_FLASH_' . (int) ($this->context->employee->id ?? 0);
        if ((string) Configuration::getGlobalValue($flashKey) !== '') {
            $f = (string) Configuration::getGlobalValue($flashKey);
            Configuration::deleteByName($flashKey);
            $flash = '<div class="alert ' . (strpos($f, 'Échec') === 0 ? 'alert-danger' : 'alert-success') . '">' . $e($f) . '</div>';
        }
        $env = HfmVivaAdmin::env();
        $badge = '<span style="display:inline-block;margin-left:6px;padding:1px 6px;font-size:11px;font-weight:700;color:#fff;background:'
            . ($env === 'live' ? '#1e7e34' : '#9a6700') . ';border-radius:2px;">' . strtoupper($env) . '</span>';
        $info = HfmVivaAdmin::fetch($tx['transaction_id']);
        $refunded = HfmVivaAdmin::refundedTotal((int) $order->id);
        $paid = $info && $info['amount'] > 0 ? $info['amount'] : $tx['amount'];
        $left = max(0, round($tx['amount'] - $refunded, 2));
        $rows = [
            'Transaction' => '<code>' . $e($tx['transaction_id']) . '</code>' . $badge,
            'Montant capturé' => $e($money($paid)),
            'Statut chez Viva' => $info ? $e(HfmVivaAdmin::statusLabel($info['status'])) : '<span class="text-muted">non lu (Viva injoignable ou identifiants absents)</span>',
        ];
        if ($info && $info['card'] !== '') {
            $rows['Carte'] = $e(trim($info['card_type'] . ' ' . $info['card']));
        }
        if ($refunded > 0) {
            $rows['Déjà remboursé'] = $e($money($refunded));
        }
        $html = '<div class="card mb-2" id="hfm-viva"><div class="card-header"><h3 class="card-header-title">'
            . '<i class="material-icons">credit_card</i> Viva — paiement par carte</h3></div><div class="card-body">' . $flash
            . '<table class="table table-sm" style="max-width:720px">';
        foreach ($rows as $k => $v) {
            $html .= '<tr><th style="width:200px;border-top:0">' . $e($k) . '</th><td style="border-top:0">' . $v . '</td></tr>';
        }
        $html .= '</table>';
        foreach (HfmVivaAdmin::refunds((int) $order->id) as $r) {
            $html .= '<div class="small text-muted">' . $e($r['date_add']) . ' — remboursé ' . $e($money($r['amount']))
                . ($r['reason'] !== '' ? ' — ' . $e($r['reason']) : '') . '</div>';
        }
        if ($left > 0 && HfmVivaAdmin::configured()) {
            $action = $this->context->link->getAdminLink('AdminHfmViva', true, [], ['id_order' => (int) $order->id]);
            $html .= '<form method="post" action="' . $e($action) . '" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-top:12px"'
                . ' onsubmit="var a=this.hfm_viva_amount.value.trim();return confirm(\'Rembourser \' + (a ? a + \' €\' : \'la totalité (' . number_format($left, 2, ',', ' ') . ' €)\') + \' au client via Viva ? Cette opération est définitive.\');">'
                . '<div><label class="form-control-label" style="font-size:12px;text-transform:uppercase;color:#6c868e">Montant à rembourser (€)</label>'
                . '<input class="form-control" name="hfm_viva_amount" inputmode="decimal" placeholder="(vide = total : ' . $e(number_format($left, 2, ',', ' ')) . ')" style="width:220px"></div>'
                . '<div style="flex:1;min-width:240px"><label class="form-control-label" style="font-size:12px;text-transform:uppercase;color:#6c868e">Raison</label>'
                . '<input class="form-control" name="hfm_viva_reason" maxlength="255" placeholder="ex : produit non disponible"></div>'
                . '<button type="submit" name="submitHfmVivaRefund" value="1" class="btn btn-danger">Rembourser via Viva</button>'
                . '</form><p class="small text-muted" style="margin:8px 0 0">Avant la compensation (en général le jour même), Viva annule la transaction ; après, il rembourse. '
                . 'Un remboursement total passe la commande à l\'état « Remboursé » (e-mail au client) ; un partiel ajoute une note privée.</p>';
        } elseif ($left <= 0) {
            $html .= '<p class="text-muted" style="margin:8px 0 0">Transaction entièrement remboursée.</p>';
        }
        return $html . '</div></div>';
    }

    /**
     * Bloc « Amazon Pay » de la fiche commande (18/11, même présentation que le bloc Viva) : paiement lu chez
     * Amazon, remboursements journalisés (état relu chez Amazon : un remboursement Amazon est asynchrone)
     * et formulaire de remboursement (total si le montant est vide).
     */
    protected function amazonOrderCard($idOrder)
    {
        require_once __DIR__ . '/lib/amazon_admin.php';
        $order = new Order((int) $idOrder);
        if (!Validate::isLoadedObject($order) || !($ch = HfmAmazonAdmin::orderCharge($order))) {
            return '';
        }
        $e = function ($v) {
            return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        };
        $money = function ($v) {
            return number_format((float) $v, 2, ',', ' ') . ' EUR';
        };
        $flash = '';
        $flashKey = 'HFM_AMZ_FLASH_' . (int) ($this->context->employee->id ?? 0);
        if ((string) Configuration::getGlobalValue($flashKey) !== '') {
            $f = (string) Configuration::getGlobalValue($flashKey);
            Configuration::deleteByName($flashKey);
            $flash = '<div class="alert ' . (strpos($f, 'Échec') === 0 ? 'alert-danger' : 'alert-success') . '">' . $e($f) . '</div>';
        }
        $live = HfmAmazonAdmin::live();
        $badge = '<span style="display:inline-block;margin-left:6px;padding:1px 6px;font-size:11px;font-weight:700;color:#fff;background:'
            . ($live ? '#1e7e34' : '#9a6700') . ';border-radius:2px;">' . ($live ? 'LIVE' : 'SANDBOX') . '</span>';
        $info = HfmAmazonAdmin::fetch($ch['charge_id']);
        $logged = HfmAmazonAdmin::refunds((int) $order->id);
        $captured = $info && $info['captured'] > 0 ? $info['captured'] : $ch['amount'];
        $refunded = max($info ? $info['refunded'] : 0, HfmAmazonAdmin::refundedTotal((int) $order->id, $logged));
        $left = max(0, round($captured - $refunded, 2));
        $rows = [
            'Paiement (charge)' => '<code>' . $e($ch['charge_id']) . '</code>' . $badge,
            'Montant capturé' => $e($money($captured)),
            'Statut chez Amazon' => $info ? $e(HfmAmazonAdmin::chargeStateLabel($info['state'])) . ($info['reason'] !== '' ? ' <span class="text-muted">(' . $e($info['reason']) . ')</span>' : '')
                : '<span class="text-muted">non lu (Amazon injoignable ou clés absentes)</span>',
        ];
        if ($refunded > 0) {
            $rows['Déjà remboursé'] = $e($money($refunded)) . ($info ? ' <span class="text-muted">(selon Amazon : ' . $e($money($info['refunded'])) . ')</span>' : '');
        }
        $html = '<div class="card mb-2" id="hfm-amazon"><div class="card-header"><h3 class="card-header-title">'
            . '<i class="material-icons">account_balance_wallet</i> Amazon Pay</h3></div><div class="card-body">' . $flash
            . '<table class="table table-sm" style="max-width:720px">';
        foreach ($rows as $k => $v) {
            $html .= '<tr><th style="width:200px;border-top:0">' . $e($k) . '</th><td style="border-top:0">' . $v . '</td></tr>';
        }
        $html .= '</table>';
        foreach ($logged as $r) {
            $html .= '<div class="small ' . ($r['state'] === 'Declined' ? 'text-danger' : 'text-muted') . '">' . $e($r['date_add']) . ' — remboursement de ' . $e($money($r['amount']))
                . ' : ' . $e(HfmAmazonAdmin::refundStateLabel($r['state'])) . ($r['reason'] !== '' ? ' — ' . $e($r['reason']) : '') . '</div>';
        }
        if ($left > 0 && HfmAmazonAdmin::configured() && $info && $info['state'] === 'Captured') {
            $action = $this->context->link->getAdminLink('AdminHfmViva', true, [], ['id_order' => (int) $order->id]);
            $html .= '<form method="post" action="' . $e($action) . '" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-top:12px"'
                . ' onsubmit="var a=this.hfm_amz_amount.value.trim();return confirm(\'Rembourser \' + (a ? a + \' €\' : \'la totalité (' . number_format($left, 2, ',', ' ') . ' €)\') + \' au client via Amazon Pay ? Cette opération est définitive.\');">'
                . '<div><label class="form-control-label" style="font-size:12px;text-transform:uppercase;color:#6c868e">Montant à rembourser (€)</label>'
                . '<input class="form-control" name="hfm_amz_amount" inputmode="decimal" placeholder="(vide = total : ' . $e(number_format($left, 2, ',', ' ')) . ')" style="width:220px"></div>'
                . '<div style="flex:1;min-width:240px"><label class="form-control-label" style="font-size:12px;text-transform:uppercase;color:#6c868e">Raison</label>'
                . '<input class="form-control" name="hfm_amz_reason" maxlength="255" placeholder="ex : produit non disponible"></div>'
                . '<button type="submit" name="submitHfmAmazonRefund" value="1" class="btn btn-danger">Rembourser via Amazon Pay</button>'
                . '</form><p class="small text-muted" style="margin:8px 0 0">Amazon traite le remboursement en quelques minutes (parfois quelques heures) ; son état se met à jour ici. '
                . 'Un remboursement total passe la commande à l\'état « Remboursé » (e-mail au client) ; un partiel ajoute une note privée.</p>';
        } elseif ($left <= 0) {
            $html .= '<p class="text-muted" style="margin:8px 0 0">Paiement entièrement remboursé.</p>';
        }
        return $html . '</div></div>';
    }

    /**
     * Encart unique « Déclaration professionnelle » de la fiche commande (demande du 17/11, présentation
     * reprise du module fdrpps de Filler Distribution) : il remplace l'ancien bandeau « Professionnel de
     * santé ». Rassemble le journal lib/pro_rules.php (règle du pays de livraison, déclaration, date / IP /
     * langue), les données pro de la commande ou du client (RPPS, attestation, justificatif) et le
     * rapprochement à l'annuaire RPPS importé (table hfm_rpps_registry).
     */
    protected function proDeclarationCard($idOrder)
    {
        $order = new Order((int) $idOrder);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }
        $d = HfmProRules::forOrder($idOrder) ?: [];
        $pro = $this->orderProData($order);
        $proProducts = $this->orderProProducts($order);
        if (!$d && $pro['rpps'] === '' && !$pro['attestation'] && $pro['pro_doc'] === '') {
            if (!$proProducts) {
                return '';
            }
        }

        $dash = '—';
        $e = function ($v) use ($dash) {
            $v = trim((string) $v);
            return $v === '' ? $dash : htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        };
        $db = Db::getInstance();
        $rule = (string) ($d['rule'] ?? '');
        $rpps = $pro['rpps'] !== '' ? $pro['rpps'] : (string) ($d['rpps'] ?? '');

        // Rapprochement à l'annuaire RPPS (extraction publique de l'Annuaire Santé, importée en base).
        $registry = null;
        if ($rpps !== '') {
            $hasReg = (bool) $db->getValue("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . _DB_PREFIX_ . "hfm_rpps_registry'", false);
            if ($hasReg) {
                $registry = $db->getRow('SELECT nom, prenom, profession FROM `' . _DB_PREFIX_ . 'hfm_rpps_registry` WHERE rpps = \'' . pSQL($rpps) . '\'', false) ?: null;
            }
        }

        if ($rule === HfmProRules::RULE_DECLARATION) {
            $ruleLabel = 'Déclaration sur l\'honneur (modèle Centrale Fillers)';
        } elseif ($rule === HfmProRules::RULE_FORBIDDEN) {
            $ruleLabel = 'Vente interdite vers ce pays';
        } elseif ($rpps !== '') {
            $ruleLabel = 'Numéro RPPS du praticien, rapproché de l\'annuaire';
        } elseif ($pro['attestation']) {
            $ruleLabel = 'Attestation sur l\'honneur de professionnel de santé';
        } else {
            $ruleLabel = 'Aucune déclaration enregistrée';
        }

        $iso = (string) ($d['country_iso'] ?? '');
        $idCountry = $iso !== '' ? (int) Country::getByIso($iso) : (int) (new Address((int) $order->id_address_delivery))->id_country;
        $country = $idCountry ? Country::getNameById((int) $this->context->language->id, $idCountry) : '';

        if ($rpps === '') {
            $rppsCell = $dash;
        } elseif ($registry) {
            $who = trim($registry['prenom'] . ' ' . $registry['nom']);
            $rppsCell = $e($rpps) . ' — ' . $e($registry['profession'] ?: 'praticien') . ($who !== '' ? ' (' . $e($who) . ')' : '')
                . ' <span style="color:#1e7e34;font-weight:600;">&#10003; trouvé dans l\'Annuaire Santé</span>';
        } else {
            $rppsCell = $e($rpps) . ' <span style="color:#9a6700;font-weight:600;">&#9888; non trouvé dans l\'Annuaire Santé — à vérifier</span>';
        }

        if ($pro['pro_doc'] !== '') {
            $url = rtrim(Tools::getShopDomainSsl(true) . __PS_BASE_URI__, '/') . '/modules/hfmstorefront/pro_docs/' . rawurlencode($pro['pro_doc']);
            $docCell = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank"><strong>télécharger</strong></a> — à vérifier';
        } elseif ($pro['attestation'] && $rpps === '') {
            $docCell = '<span style="color:#9a6700;">Aucun justificatif joint — à recevoir par email / à vérifier</span>';
        } else {
            $docCell = $dash;
        }

        $profession = (string) ($d['profession'] ?? '');
        if ($profession === '' && $registry) {
            $profession = (string) $registry['profession'];
        }
        $products = (string) ($d['products'] ?? '');
        if ($products === '' && $proProducts) {
            $products = implode(' | ', $proProducts);
        }
        if (!empty($d['date_accepted'])) {
            $accepted = $e($d['date_accepted']) . (!empty($d['ip']) ? ' — IP ' . $e($d['ip']) : '') . (!empty($d['lang']) ? ' — langue ' . $e($d['lang']) : '');
        } else {
            $accepted = $e($order->date_add) . ' <span style="color:#6b7280;">(date de la commande)</span>';
        }

        $rows = [
            'Règle appliquée' => $e($ruleLabel),
            'Pays de livraison' => $e($country ?: $iso),
            'RPPS' => $rppsCell,
            'Attestation professionnelle' => $pro['attestation'] ? 'oui (sur l\'honneur)' : $dash,
            'Justificatif' => $docCell,
            'Double validation (popup)' => $rule === HfmProRules::RULE_DECLARATION ? ((int) ($d['double_validation'] ?? 0) ? 'oui' : 'non') : $dash,
            'Profession' => $e($profession),
            'N° d\'inscription pro' => $e($d['pro_number'] ?? ''),
            'N° fiscal (TVA / NIF)' => $e($d['tax_number'] ?? ''),
            'Produits concernés' => $e($products),
            'Accepté le' => $accepted,
            'Version du texte' => $e($d['text_version'] ?? ''),
        ];

        // [26/11/2026] Saisie du RPPS par l'équipe (lib/rpps_admin.php) : auteur et date de la saisie,
        // message de retour du formulaire, et formulaire en bas du bloc.
        require_once __DIR__ . '/lib/rpps_admin.php';
        if ($bo = HfmRppsAdmin::boEntry((int) $order->id)) {
            $rows['RPPS saisi en back-office'] = 'par ' . $e($bo['employee'] ?: 'employé n° ' . (int) $bo['id_employee']) . ' le ' . $e($bo['date_bo']);
        }
        $flash = '';
        $flashKey = 'HFM_RPPS_FLASH_' . (int) ($this->context->employee->id ?? 0);
        if ((string) Configuration::getGlobalValue($flashKey) !== '') {
            $f = (string) Configuration::getGlobalValue($flashKey);
            Configuration::deleteByName($flashKey);
            $flash = '<div class="alert ' . (strpos($f, 'Échec') === 0 ? 'alert-danger' : (strpos($f, 'ATTENTION') !== false ? 'alert-warning' : 'alert-success')) . '">'
                . htmlspecialchars($f, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $html = '<div class="card mb-2" id="hfm-pro"><div class="card-header"><h3 class="card-header-title">'
            . '<i class="material-icons">verified_user</i> Déclaration professionnelle</h3></div>'
            . '<div class="card-body">' . $flash . '<table class="table table-sm mb-0">';
        foreach ($rows as $k => $v) {
            $html .= '<tr><th style="width:220px">' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '</th><td>' . $v . '</td></tr>';
        }
        if (trim((string) ($d['text'] ?? '')) !== '') {
            $html .= '<tr><th>Texte accepté</th><td style="font-size:12px;color:#55606F;">' . $e($d['text']) . '</td></tr>';
        }
        $html .= '</table>';

        // Formulaire : renseigner ou corriger le RPPS du client (remplace la note dans le champ « Autre »
        // de l'adresse). Enregistré sur la commande, sur le compte du client et par e-mail.
        $action = $this->context->link->getAdminLink('AdminHfmViva', true, [], ['id_order' => (int) $order->id]);
        $html .= '<form method="post" action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-top:14px">'
            . '<div><label class="form-control-label" style="font-size:12px;text-transform:uppercase;color:#6c868e">'
            . ($rpps !== '' ? 'Corriger le n° RPPS' : 'Renseigner le n° RPPS du client') . '</label>'
            . '<input class="form-control" name="hfm_rpps" inputmode="numeric" autocomplete="off" maxlength="20" style="width:240px"'
            . ' placeholder="11 chiffres (ou ADELI : 9)" value="' . htmlspecialchars($rpps, ENT_QUOTES, 'UTF-8') . '"></div>'
            . '<button type="submit" name="submitHfmRpps" value="1" class="btn btn-primary">Enregistrer le RPPS</button>'
            . '</form><p class="small text-muted" style="margin:8px 0 0">Le numéro est vérifié (clé de contrôle, puis Annuaire Santé), enregistré sur cette commande et sur le client : '
            . 'le site ne le lui redemandera pas à ses prochaines commandes. Plus besoin de le noter dans le champ « Autre » de l\'adresse.</p>';
        return $html . '</div></div>';
    }

    /** Données « pro » de la commande (invité) ou, à défaut, du client : RPPS, attestation, justificatif. */
    protected function orderProData(Order $order)
    {
        $db = Db::getInstance();
        $out = ['rpps' => '', 'attestation' => 0, 'pro_doc' => ''];
        $sources = [
            ['hfm_order_pro_doc', 'id_order = ' . (int) $order->id],
            ['hfm_customer_rpps', 'id_customer = ' . (int) $order->id_customer],
        ];
        foreach ($sources as list($table, $where)) {
            $exists = (bool) $db->getValue("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . _DB_PREFIX_ . $table . "'", false);
            if (!$exists) {
                continue;
            }
            $row = $db->getRow('SELECT rpps, attestation, pro_doc FROM `' . _DB_PREFIX_ . $table . '` WHERE ' . $where, false);
            if ($row && ((string) $row['rpps'] !== '' || (int) $row['attestation'] || (string) $row['pro_doc'] !== '')) {
                return ['rpps' => (string) $row['rpps'], 'attestation' => (int) $row['attestation'], 'pro_doc' => (string) $row['pro_doc']];
            }
        }
        return $out;
    }

    /** Produits de la commande réservés aux professionnels (caractéristique « RPPS »), « id:nom ×qté ». */
    protected function orderProProducts(Order $order)
    {
        // Lecture directe des lignes de commande (Order::getProducts recalcule les prix : inutile ici).
        $rows = Db::getInstance()->executeS(
            'SELECT od.product_id, od.product_name, od.product_quantity FROM `' . _DB_PREFIX_ . 'order_detail` od
             WHERE od.id_order = ' . (int) $order->id . ' AND EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'feature_product` fp
               JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON fl.id_feature = fp.id_feature
               WHERE fp.id_product = od.product_id AND fl.name = \'RPPS\')'
        ) ?: [];
        $out = [];
        foreach ($rows as $p) {
            $out[] = (int) $p['product_id'] . ':' . $p['product_name'] . ((int) $p['product_quantity'] > 1 ? ' ×' . (int) $p['product_quantity'] : '');
        }
        return $out;
    }

    // ---------- Éditeur de pages CMS multilingues (table hfm_cms_i18n) ----------

    /** Pages légales éditables (slug => libellé BO). */
    protected function cmsPages()
    {
        return [
            'livraison-retours' => 'Livraison & retours',
            'conditions-generales-de-ventes' => 'Conditions générales de vente',
            'politique-de-confidentialite' => 'Politique de confidentialité',
            'mentions-legales' => 'Mentions légales',
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

        // --- Produits réservés aux professionnels : règle par pays de livraison (lib/pro_rules.php) ---
        $proFields = [
            'HFM_PRO_RPPS_COUNTRIES' => ['Numéro RPPS', HfmProRules::DEFAULT_RPPS, 'Parcours RPPS / attestation professionnelle.'],
            'HFM_PRO_DECLARATION_COUNTRIES' => ['Déclaration sur l\'honneur', HfmProRules::DEFAULT_DECLARATION, 'Profession, n° d\'inscription et n° fiscal facultatifs + double validation.'],
            'HFM_PRO_FORBIDDEN_COUNTRIES' => ['Vente interdite', '', 'Commande refusée si le panier contient un produit réservé.'],
        ];
        $proSaved = '';
        if (Tools::isSubmit('submitHfmProRules')) {
            foreach (array_keys($proFields) as $key) {
                $isos = preg_split('/[\s,;]+/', Tools::strtoupper((string) Tools::getValue($key)));
                Configuration::updateValue($key, implode(',', array_filter($isos, function ($i) {
                    return (bool) preg_match('/^[A-Z]{2}$/', $i);
                })));
            }
            $default = (string) Tools::getValue('HFM_PRO_DEFAULT_RULE');
            Configuration::updateValue('HFM_PRO_DEFAULT_RULE', in_array($default, ['none', 'rpps', 'declaration', 'forbidden'], true) ? $default : 'none');
            $proSaved = $this->displayConfirmation('Règles « produits réservés aux professionnels » enregistrées.');
        }
        $out .= '<div class="panel">' . $proSaved;
        $out .= '<h3><i class="icon icon-shield"></i> Produits réservés aux professionnels — règle par pays de livraison</h3>';
        $out .= '<p class="text-muted">Codes pays ISO à 2 lettres séparés par des virgules (FR, BE, DE…). Un produit est « réservé » s\'il porte la caractéristique RPPS. '
              . 'Chaque commande concernée garde sa déclaration (visible en haut de la fiche commande).</p>';
        $out .= '<form method="post" action="' . $action . '" class="form-horizontal">';
        foreach ($proFields as $key => $f) {
            $val = HfmProRules::countryList($key, $f[1]);
            $out .= '<div class="form-group"><label class="control-label col-lg-3">' . htmlspecialchars($f[0]) . '</label><div class="col-lg-9">'
                  . '<input type="text" name="' . $key . '" class="form-control" value="' . htmlspecialchars(implode(', ', $val)) . '"/>'
                  . '<p class="help-block">' . htmlspecialchars($f[2]) . '</p></div></div>';
        }
        $curDefault = (string) Configuration::get('HFM_PRO_DEFAULT_RULE') ?: 'none';
        $out .= '<div class="form-group"><label class="control-label col-lg-3">Autres pays</label><div class="col-lg-9"><select name="HFM_PRO_DEFAULT_RULE" class="form-control">';
        foreach (['none' => 'Aucune exigence', 'rpps' => 'Numéro RPPS', 'declaration' => 'Déclaration sur l\'honneur', 'forbidden' => 'Vente interdite'] as $v => $l) {
            $out .= '<option value="' . $v . '"' . ($v === $curDefault ? ' selected' : '') . '>' . htmlspecialchars($l) . '</option>';
        }
        $out .= '</select></div></div>';
        $out .= '<div class="panel-footer"><button type="submit" name="submitHfmProRules" class="btn btn-default pull-right"><i class="process-icon-save"></i> Enregistrer</button></div>';
        $out .= '</form></div>';

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
