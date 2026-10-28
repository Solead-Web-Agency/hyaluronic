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
        // Règles de prix par pays / groupe (table groupinc_configuration), cf. lib/country_rules.php.
        $this->registerHook('actionProductPriceCalculation');

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
        ];
    }

    /**
     * Garantit l'enregistrement des hooks de purge même si le module est DÉJÀ installé
     * (les hooks ajoutés après-coup ne passent pas par install()). Idempotent :
     * registerHook ne duplique pas un hook déjà enregistré.
     */
    protected function ensurePurgeHooksRegistered()
    {
        // + hooks fonctionnels ajoutés après l'installation (règles de prix par pays/groupe).
        foreach (array_merge($this->purgeHooks(), ['actionProductPriceCalculation', 'actionSetInvoice', 'displayInvoiceLegalFreeText', 'displayPDFOrderSlip']) as $hook) {
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
        if (!empty($params['use_existing_payment']) || !$invoice instanceof OrderInvoice) {
            return null;
        }
        $db = Db::getInstance();
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
     * anglais le n° de TVA d'une autre société) : exonération avec le BON article du CGI, état du
     * règlement, conditions de retard entre professionnels. Texte brut (le modèle échappe et
     * passe à la ligne).
     */
    public function hookDisplayInvoiceLegalFreeText($params)
    {
        $order = $params['order'] ?? null;
        if (!$order instanceof Order || !Validate::isLoadedObject($order)) {
            return '';
        }
        $lines = [];
        if ($exemption = $this->vatExemptionMention($order)) {
            $lines[] = $exemption;
        }
        $paid = 0.0;
        $paidOn = '';
        foreach (OrderPayment::getByOrderReference($order->reference) as $payment) {
            $paid += (float) $payment->amount;
            $paidOn = $paidOn ?: (string) $payment->date_add;
        }
        if ($paid + 0.01 >= (float) $order->total_paid_tax_incl && $paidOn !== '') {
            $lines[] = 'Facture acquittée le ' . date('d/m/Y', strtotime($paidOn)) . ' (' . $order->payment . ').';
        } else {
            $lines[] = 'Règlement à réception de facture (' . $order->payment . ').';
        }
        $lines[] = 'Pas d\'escompte pour paiement anticipé. Tout retard de paiement entraîne des pénalités au taux de trois fois '
            . 'le taux d\'intérêt légal et une indemnité forfaitaire pour frais de recouvrement de 40 € '
            . '(articles L441-10 et D441-5 du Code de commerce).';
        return implode("\n", $lines);
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
        $lines = [];
        $invoice = $order->hasInvoice() ? $order->getInvoicesCollection()->getFirst() : null;
        if ($invoice && Validate::isLoadedObject($invoice)) {
            $lines[] = 'Avoir relatif à la facture n° ' . $invoice->getInvoiceNumberFormatted((int) $order->id_lang, (int) $order->id_shop)
                . ' du ' . date('d/m/Y', strtotime((string) $invoice->date_add)) . ' (commande ' . $order->reference . ').';
        } else {
            $lines[] = 'Avoir relatif à la commande ' . $order->reference . '.';
        }
        if ($exemption = $this->vatExemptionMention($order)) {
            $lines[] = $exemption;
        }
        return '<table style="width:100%;font-size:8pt;color:#444"><tr><td>'
            . implode('<br />', array_map(function ($l) { return htmlspecialchars($l, ENT_QUOTES, 'UTF-8'); }, $lines))
            . '</td></tr></table>';
    }

    /**
     * Mention d'exonération de TVA d'une commande vendue sans TVA, selon la destination :
     * livraison intracommunautaire de biens (262 ter I, n° de TVA de l'acquéreur) ou exportation
     * hors UE (262 I). Rien si la commande porte de la TVA.
     */
    protected function vatExemptionMention(Order $order)
    {
        $tax = (float) $order->total_paid_tax_incl - (float) $order->total_paid_tax_excl;
        if ($tax > 0.01 || (float) $order->total_paid_tax_excl <= 0) {
            return '';
        }
        $delivery = new Address((int) $order->id_address_delivery);
        $iso = Validate::isLoadedObject($delivery) ? Tools::strtoupper((string) Country::getIsoById((int) $delivery->id_country)) : '';
        if ($iso === '' || $iso === 'FR' || $iso === 'COS' || $iso === 'MC') {
            return '';
        }
        $eu = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT',
            'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];
        if (in_array($iso, $eu, true)) {
            $invoiceAddress = new Address((int) $order->id_address_invoice);
            $vat = Validate::isLoadedObject($invoiceAddress) ? trim((string) $invoiceAddress->vat_number) : '';
            return 'Exonération de TVA : livraison intracommunautaire de biens (article 262 ter I du CGI)'
                . ($vat !== '' ? ' — n° de TVA intracommunautaire de l\'acquéreur : ' . $vat : '') . '.';
        }
        return 'Exonération de TVA : exportation hors de l\'Union européenne (article 262 I du CGI).';
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
    public function hookDisplayAdminOrderTop($params)
    {
        $idOrder = (int) (isset($params['id_order']) ? $params['id_order'] : 0);
        return $this->rppsOrderBanner($params) . ($idOrder ? $this->proDeclarationCard($idOrder) : '');
    }

    /**
     * Encart « Déclaration professionnelle » (journal lib/pro_rules.php) : règle appliquée au pays de
     * livraison, champs saisis, double validation, produits concernés, date / IP / langue, version du texte.
     */
    protected function proDeclarationCard($idOrder)
    {
        $d = HfmProRules::forOrder($idOrder);
        if (!$d) {
            return '';
        }
        $labels = [
            HfmProRules::RULE_RPPS => 'Numéro RPPS (France)',
            HfmProRules::RULE_DECLARATION => 'Déclaration sur l\'honneur (modèle Centrale Fillers)',
            HfmProRules::RULE_FORBIDDEN => 'Pays interdit',
        ];
        $country = $d['country_iso'] ? Country::getNameById((int) $this->context->language->id, (int) Country::getByIso($d['country_iso'])) : '';
        $dash = '—';
        $e = function ($v) use ($dash) {
            $v = trim((string) $v);
            return $v === '' ? $dash : htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        };
        $accepted = $d['date_accepted']
            ? $e($d['date_accepted']) . ($d['ip'] ? ' — IP ' . $e($d['ip']) : '') . ($d['lang'] ? ' — langue ' . $e($d['lang']) : '')
            : $dash;
        $rows = [
            'Règle appliquée' => $e($labels[$d['rule']] ?? $d['rule']),
            'Pays de livraison' => $e($country ?: $d['country_iso']),
            'RPPS' => $e($d['rpps']),
            'Double validation (popup)' => $d['rule'] === HfmProRules::RULE_DECLARATION ? ((int) $d['double_validation'] ? 'oui' : 'non') : $dash,
            'Profession' => $e($d['profession']),
            'N° d\'inscription pro' => $e($d['pro_number']),
            'N° fiscal (TVA / NIF)' => $e($d['tax_number']),
            'Produits concernés' => $e($d['products']),
            'Accepté le' => $accepted,
            'Version du texte' => $e($d['text_version']),
        ];
        $html = '<div class="card mb-2"><div class="card-header"><h3 class="card-header-title">&#128737; Déclaration professionnelle</h3></div>'
            . '<div class="card-body" style="padding:0;"><table class="table" style="margin:0;">';
        foreach ($rows as $k => $v) {
            $html .= '<tr><td style="width:240px;font-weight:600;">' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '</td><td>' . $v . '</td></tr>';
        }
        if (trim((string) $d['text']) !== '') {
            $html .= '<tr><td style="font-weight:600;">Texte accepté</td><td style="font-size:12px;color:#55606F;">' . $e($d['text']) . '</td></tr>';
        }
        return $html . '</table></div></div>';
    }

    /** Bandeau RPPS / attestation pro (données client ou commande invité). */
    protected function rppsOrderBanner($params)
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
