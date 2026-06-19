<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('_AGDIR_')) {
    define('_AGDIR_', dirname(__FILE__));
}

define("SAGAPIENDPOINT", "wp-content/plugins/ag-core/api/");

class STEAVISGARANTIS extends Module
{
    public $_errors = array();

    public function __construct()
    {
        $this->bootstrap = true;
        $this->name = 'steavisgarantis';
        $this->tab = 'advertising_marketing';
        $this->version = '5.3.8';
        $this->author = 'Société des Avis Garantis';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5.0.0', 'max' => '9.99.99');
        $this->module_key = '7925df33d223a2b4c7f1786e1efb51f7';
        parent::__construct();
        $this->displayName = $this->l('Guaranteed Reviews Company');
        $this->description = $this->l('Collect, guarantee and publish your customers reviews. Increase your sales fastly and easily.');
        $this->initContext();
    }

    public function installDatabase()
    {
        $query = array();

        $query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'steavisgarantis_average_rating;';
        $query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'steavisgarantis_reviews;';
        $query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'steavisgarantis_customer;';
        $query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'steavisgarantis_custom_answers;';

        $query[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'steavisgarantis_reviews (
            `id` bigint(20) AUTO_INCREMENT,
            `id_product_avisg` varchar(38) NOT NULL,
            `product_id` varchar(30) NOT NULL,
            `ag_reviewer_name` varchar(35) NOT NULL,
            `rate` varchar(4) NOT NULL,
            `review` text NOT NULL,
            `date_time` bigint(20) NOT NULL,
            `answer_text` text DEFAULT NULL,
            `answer_date_time` DATETIME DEFAULT NULL,
            `order_date` DATETIME DEFAULT NULL,
            `id_lang` varchar(11) NOT NULL,
            `translated` INT(1) NOT NULL DEFAULT "0",
            `source_lang` VARCHAR(2) NOT NULL DEFAULT "",
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        
        $query[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'steavisgarantis_average_rating` (
            `id` bigint(20) AUTO_INCREMENT,
            `id_product_avisg` varchar(38) NOT NULL,
            `product_id` varchar(30) NOT NULL,
            `rate` varchar(4) NOT NULL,
            `percent1` int(11) NOT NULL,
            `percent2` int(11) NOT NULL,
            `percent3` int(11) NOT NULL,
            `percent4` int(11) NOT NULL,
            `percent5` int(11) NOT NULL,
            `nb1` int(11) NOT NULL,
            `nb2` int(11) NOT NULL,
            `nb3` int(11) NOT NULL,
            `nb4` int(11) NOT NULL,
            `nb5` int(11) NOT NULL,
            `date_time_update` text NOT NULL,
            `reviews_nb` int(11) NOT NULL,
            `id_lang` varchar(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        
        $query[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'steavisgarantis_customer` (
            `id_steavisgarantis_customfield` int(10) unsigned NOT NULL,
            `id_customer` int(10) unsigned NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY (`id_steavisgarantis_customfield`, `id_customer`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';


        $query[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'steavisgarantis_custom_answers` (
            `id` bigint(20) AUTO_INCREMENT,
            `id_product_avisg` varchar(38) NOT NULL,
            `id_question` bigint(20) NOT NULL,
            `question_label` varchar(64) NOT NULL,
            `answer` varchar(500) NOT NULL,
            `unit` varchar(32) DEFAULT NULL,
            `date_add` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_reviews ADD INDEX( `translated`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_reviews ADD INDEX( `source_lang`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_reviews ADD INDEX( `product_id`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_reviews ADD INDEX( `id_product_avisg`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_reviews ADD INDEX( `rate`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_reviews ADD INDEX( `date_time`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_average_rating ADD INDEX( `id_product_avisg`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_average_rating ADD INDEX( `product_id`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_average_rating ADD INDEX( `rate`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_average_rating ADD INDEX( `reviews_nb`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_average_rating ADD INDEX( `id_lang`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_custom_answers ADD INDEX( `id_product_avisg`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_custom_answers ADD INDEX( `id_question`);';
        $query[] = 'ALTER TABLE '._DB_PREFIX_.'steavisgarantis_custom_answers ADD INDEX( `question_label`);';

        foreach ($query as $key => $sqlQuery) {
            if (!Db::getInstance()->Execute($sqlQuery)) {
                $this->_errors[] = $this->l('SQL database creation error');
                return false;
            }
        }

        return true;
    }

    public function uninstallDatabase()
    {
        $query = array();
        $query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'steavisgarantis_average_rating';
        $query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'steavisgarantis_reviews';
        $query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'steavisgarantis_customer';
        $query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'steavisgarantis_custom_answers';

        foreach ($query as $key => $sqlQuery) {
            if (!Db::getInstance()->Execute($sqlQuery)) {
                $this->_errors[] = $this->l('Error while deleting SQL database table');
                return false;
            }
        }

        return true;
    }

    public function install()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) { // Installation pour PrestaShop 1.4
            if (!parent::install()) {
                return false;
            }

            if (!$this->installDatabase()
            || !$this->registerHook('header')
            || !$this->registerHook('footer')
            || !$this->registerHook('leftColumn')
            || !$this->registerHook('productTab')
            || !$this->registerHook('productTabContent')
            || !$this->registerHook('rightColumn')
            || !$this->registerHook('productActions')) {
                return false;
            }
        } else { // Installation pour PrestaShop 1.5, 1.6 et 1.7+
            if (!$this->installDatabase()
            || !parent::install()
            || !$this->registerHook('displayRightColumnProduct')
            || !$this->registerHook('displayLeftColumn')
            || !$this->registerHook('displayRightColumn')
            || !$this->registerHook('displayCustomerAccount')
            || !$this->registerHook('displayBeforeCarrier')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayFooter')
            || !$this->registerHook('displayProductTab')
            || !$this->registerHook('displayProductButtons')
            || !$this->registerHook('displayProductListReviews')
            || !$this->registerHook('displayProductPriceBlock')
            || !$this->registerHook('displayProductStarCategorySag') 
            || !$this->registerHook('displayProductAdditionalInfo') 
			|| !$this->registerHook('displayHome')
            || !$this->registerHook('displayProductTabContent') 
            || !$this->registerHook('actionCarrierProcess') 
            || !$this->registerHook('actionOrderHistoryAddAfter')) {
                $this->_errors[] = array('Erreur d\'installation du module.');
                return false;
            }
        }

        // Hook spécifique pour PrestaShop 1.7+
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            if (!$this->registerHook('displayProductExtraContent') || !$this->registerHook('actionObjectOrderHistoryAddAfter')) {
                $this->_errors[] = array('Erreur d\'installation du hook displayProductExtraContent et actionObjectOrderHistoryAddAfter.');
                return false;
            }
        }

        //On configure par défaut le bloc d'avis iFrame en désactivé (si on a jamais installé le module)
        if (!Configuration::get('steavisgarantis_widgetPosition')) {
            Configuration::updateValue('steavisgarantis_widgetPosition', "none");
        }

        //On configure par défaut le widget Javascript en désactivé
        if (!Configuration::get('steavisgarantis_widgetJavascript')) {
            Configuration::updateValue('steavisgarantis_widgetJavascript', false);
        }

        //On configure par défaut le widget étoiles catégories en désactivé
        if (!Configuration::get('steavisgarantis_catStars')) {
            Configuration::updateValue('steavisgarantis_catStars', false);
        }

        //On configure par défaut le customCSS à vide
        if (!Configuration::get('steavisgarantis_customCSS')) {
            Configuration::updateValue('steavisgarantis_customCSS', "");
        }

        //On configure par défaut le lien Footer en désactivé
        if (!Configuration::get('steavisgarantis_footerLink')) {
            Configuration::updateValue('steavisgarantis_footerLink', true);
        }

        //On configure par défaut les status à inclure à expedié ce jour et livré
        if (!Configuration::get('steavisgarantis_includeStatus')) {
            Configuration::updateValue('steavisgarantis_includeStatus', "4,5");
        }

        //On configure par défaut le délai s'il est vide
        if (!Configuration::get('steavisgarantis_afterDays')) {
            Configuration::updateValue('steavisgarantis_afterDays', 10);
        }

        //On configure par défaut le mode d'affichage en normal s'il est vide
        if (!Configuration::get('steavisgarantis_normalBehaviour')) {
            Configuration::updateValue('steavisgarantis_normalBehaviour', 1);
        }

        //On met à 0 le recueil du consentement
        if (!Configuration::get('steavisgarantis_rgpd')) {
            Configuration::updateValue('steavisgarantis_rgpd', 0);
        }

        //On configure par défaut le nombre max d'avis affichés dans la fiche produit
        if (!Configuration::get('steavisgarantis_maxReviewPerPage')) {
            Configuration::updateValue('steavisgarantis_maxReviewPerPage', 10);
        }

        //On configure par défaut le design du widget summary à 1
        if (!Configuration::get('steavisgarantis_summaryDesign')) {
            Configuration::updateValue('steavisgarantis_summaryDesign', 1);
        }

        //On configure par défaut le format des données structurées
        if (!Configuration::get('steavisgarantis_structuredFormat')) {
            Configuration::updateValue('steavisgarantis_structuredFormat', "microdata");
        }
		
		//On configure par défaut le couleur des étoiles
        if (!Configuration::get('steavisgarantis_starColor')) {
            Configuration::updateValue('steavisgarantis_starColor', "#f5d700");
        }

        //On configure par défaut les nouveaux widgets activés
        if (!Configuration::get('steavisgarantis_newWidgets')) {
            Configuration::updateValue('steavisgarantis_newWidgets', true);
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||  !$this->uninstallDatabase()) {
            $this->_errors[] = $this->l('Uninstall failed');
            return false;
        }

        return true;
    }
    
    private function initContext()
    {
        if (class_exists('Context')) {
            $this->context = Context::getContext();
        } else {
            // Retrocompatibility 1.4
            $this->context = new StdClass();
            $this->context->smarty = isset($GLOBALS['smarty']) ? $GLOBALS['smarty'] : null;
            $this->context->cookie = isset($GLOBALS['cookie']) ? $GLOBALS['cookie'] : null;
        }

        //Sécurité (si aucun statut sélectionné on met Expedié et livré)
        if (!Configuration::get('steavisgarantis_includeStatus')) {
            Configuration::updateValue('steavisgarantis_includeStatus', "4,5");
        }

        //On configure par défaut le délai s'il est vide
        if (!Configuration::get('steavisgarantis_afterDays')) {
            Configuration::updateValue('steavisgarantis_afterDays', 10);
        }

        //On configure par défaut le mode d'affichage en normal s'il est vide
        if (!Configuration::get('steavisgarantis_normalBehaviour')) {
            Configuration::updateValue('steavisgarantis_normalBehaviour', 1);
        }

        //On configure par défaut le nombre max d'avis affiché dans la fiche produit
        if (!Configuration::get('steavisgarantis_maxReviewPerPage')) {
            Configuration::updateValue('steavisgarantis_maxReviewPerPage', 10);
        }

        //On configure par défaut le design du widget summary à 1
        if (!Configuration::get('steavisgarantis_summaryDesign')) {
            Configuration::updateValue('steavisgarantis_summaryDesign', 1);
        }

        //On configure par défaut le format des données structurées
        if (!Configuration::get('steavisgarantis_structuredFormat')) {
            Configuration::updateValue('steavisgarantis_structuredFormat', "microdata");
        }
		
		if (!Configuration::get('steavisgarantis_starColor')) {
            Configuration::updateValue('steavisgarantis_starColor', "#f5d700");
        }
    }

    public function displayIframeWidget()
    {
        if(Configuration::get('steavisgarantis_newWidgets')) {
            $this->context->smarty->assign(array(
                'type' => 'vertical'
            ));

            return $this->display(__FILE__, 'views/templates/front/widgets/siteIframe.tpl');
        }

        $apiKey = Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id);
        $shopID = self::getShopId($this->context->language->id);
        $url=self::getCertificateUrl($this->context->language->id);
        //Pass lang to tpl to manage style through CSS
        $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));
        //Quand c'est de l'iFrame il faut mieux ne pas mentionner le protocole
        $domain = str_replace("https:", "", self::getDomainUrl($apiKey));

        $this->context->smarty->assign(array(
            'url_ag' => $url,
            'shopID' => $shopID,
            'sagLang' => $lang,
            'domain' => $domain
        ));

        return $this->display(__FILE__, 'views/templates/front/displayIframeWidget.tpl');
    }

    public function displayIframeFooterWidget()
    {
        if(Configuration::get('steavisgarantis_newWidgets')) {
            $this->context->smarty->assign(array(
                'type' => 'horizontal'
            ));

            return $this->display(__FILE__, 'views/templates/front/widgets/siteIframe.tpl');
        }

        $apiKey = Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id);
        $shopID = self::getShopId($this->context->language->id);
        $url = self::getCertificateUrl($this->context->language->id);

        // Pass lang to tpl to manage style through CSS
        $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));

        // Quand c'est de l'iFrame il faut mieux ne pas mentionner le protocole
        $domain = str_replace("https:", "", self::getDomainUrl($apiKey));

        $this->context->smarty->assign(array(
            'url_ag' => $url,
            'shopID' => $shopID,
            'sagLang' => $lang,
            'domain' => $domain
        ));

        return $this->display(__FILE__, 'views/templates/front/displayIframeFooterWidget.tpl');
    }
    
    public function hookdisplayHome() 
    {
		if (Configuration::get('steavisgarantis_widgetPosition') == "home") {
            return $this->displayIframeFooterWidget();
        } else {
            return false;
        }
	}

    public function hookdisplayLeftColumn()
    {
        if (Configuration::get('steavisgarantis_widgetPosition') == "left") {
            return $this->displayIframeWidget();
        } else {
            return false;
        }
    }
    
    //
    // Manage category stars
    //
    public function hookDisplayProductListReviews($params)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return $this->hookSagCategoryStars($params);
        }
    }

    public function hookDisplayProductStarCategorySag($params)
    {
            return $this->hookSagCategoryStars($params);
    }

    public function hookDisplayProductPriceBlock($params)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>') && ($params['type'] == "before_price")) {
            return $this->hookSagCategoryStars($params);
        }
    }

    public function hookSagCategoryStars($params)
    {
        $starsEnabled = Configuration::get('steavisgarantis_catStars');

        if ($starsEnabled) {
            $productLink = null;
            $productSku = null;
            
            if(!is_object(($params['product']))){
                $productID = (int)$params['product']['id_product'];
                if(isset($params['product']['link'])){
                    $productLink = $params['product']['link'];
                }
                if(isset($params['product']['reference']) && !empty($params['product']['reference'])) {
                    $productSku = $params['product']['reference'];
                }
            } else {
                $productID = (int)$params['product']->id_product;
                if(isset($params['product']->link)){
                    $productLink = $params['product']->link;
                }
                if(isset($params['product']->reference) && !empty($params['product']->reference)) {
                    $productSku = $params['product']->reference;
                }
            }

            if(!isset($productSku)) {
                // Get product SKU
                $product = new Product($productID);
                $productSku = $product->reference;
            }

            if(Configuration::get('steavisgarantis_newWidgets')) {
                $this->context->smarty->assign(array(
                    'productId' => $productID,
                    'productSku' => $productSku
                ));

                return $this->display(__FILE__, 'views/templates/front/widgets/categoryStars.tpl');
            }

            $id_lang = (int)$this->context->language->id;
            $sql = "SELECT count(*) FROM "._DB_PREFIX_."steavisgarantis_reviews WHERE product_id='$productID' and id_lang='$id_lang'";
            $nb= Db::getInstance()->getValue($sql);
            $sql = "SELECT rate FROM "._DB_PREFIX_."steavisgarantis_average_rating WHERE product_id='$productID' and id_lang='$id_lang'";
            $rating= Db::getInstance()->getValue($sql);

            if ($nb < 1) {
                return ""; // Si Aucun avis, on retourne vide
            }

            // Pass lang to tpl to manage style through CSS
            $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));

            $this->context->smarty->assign(array(
                'nbReviews'  => $nb,
                'reviewRate' =>  $rating,
                'modules_dir'=> _MODULE_DIR_,
                'sagLang'    => $lang,
                'starColor' => Configuration::get('steavisgarantis_starColor'),
            ));

            return $this->display(__FILE__, 'views/templates/front/categoryStars.tpl');
        }
    }

    public function hookdisplayFooter()
    {
        $widgetFooter = (Configuration::get('steavisgarantis_widgetPosition') == "footer") ? 1 : 0;
        $footerLink = Configuration::get('steavisgarantis_footerLink');
        
        if(Configuration::get('steavisgarantis_newWidgets')) {
            $output = '';

            if($widgetFooter) {
                $this->context->smarty->assign(array(
                    'type' => 'horizontal'
                ));

                $output .= $this->display(__FILE__, 'views/templates/front/widgets/siteIframe.tpl');
            }
            
            if($footerLink) {
                $apiKey = Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id);
                $domain = str_replace("https:", "", self::getDomainUrl($apiKey));
                $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));
                $certificateUrl = self::getCertificateUrlByLang($lang);

                $this->context->smarty->assign(array(
                    'widgetFooter' => 0,
                    'footerLink' => 1,
                    'domain' => $domain,
                    'sagLang' => $lang,
                    'url_steavisgarantis' => $certificateUrl,
                    'modules_dir' => _MODULE_DIR_
                ));

                $output .= $this->display(__FILE__, 'views/templates/front/displayFooter.tpl');
            }

            return $output;
        }

        $apiKey = Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id);

        $domain = str_replace("https:", "", self::getDomainUrl($apiKey));
        $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));

        $this->context->smarty->assign(array(
            'widgetFooter' => $widgetFooter,
            'footerLink' => $footerLink,
            'domain' => $domain,
            'sagLang' => $lang,
        ));
        
        // Si on doit afficher l'un ou l'autre il faut récupérer certaines variables
        if ($widgetFooter or $footerLink) {
            $url=self::getCertificateUrlByLang($lang);
            $this->context->smarty->assign(array(
                'url_steavisgarantis' => $url,
            ));
            // Si on doit afficher le widget iframe dans le footer
            if ($widgetFooter) {
                $shopID = self::getShopId($this->context->language->id);
                $this->context->smarty->assign(array('shopID' => $shopID));
            }

            // Si on doit afficher le lien dans le footer
            if ($footerLink) {
                $this->context->smarty->assign(array('modules_dir' => _MODULE_DIR_));
            }
        }

        return $this->display(__FILE__, 'views/templates/front/displayFooter.tpl');
    }

    public function hookdisplayRightColumn()
    {
        if (Configuration::get('steavisgarantis_widgetPosition')== "right") {
            return $this->displayIframeWidget();
        } else {
            return false;
        }
    }

    public function hookdisplayBeforeCarrier()
    {
        if (Configuration::get('steavisgarantis_rgpd') == "1") {
            //Pass lang to tpl to manage style through CSS
            $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));

            if ((int)$this->context->customer->id) {
                $consent = STEAVISGARANTIS::getSagConsent((int)$this->context->customer->id);
            }
            else {
                $consent = 0;
            }

            //si on a pas le consentement on le propose
            if (!$consent) {
                $this->context->smarty->assign(array(
                    'modules_dir'=> _MODULE_DIR_,
                    'steavisgarantis_customer_id' => (int)$this->context->customer->id,
                    'consent' => (int)$consent,
                    'sagLang' => $lang,
                    'id_lang'=>$this->context->language->id
                ));
                return $this->display(__FILE__, 'views/templates/front/displayReviewConsent.tpl');
            }
            else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function hookdisplayCustomerAccount($params)
    {
        if (Configuration::get('steavisgarantis_rgpd')== "1") {
            // Pass lang to tpl to manage style through CSS
            $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));

            $this->context->smarty->assign(array(
                'modules_dir'=> _MODULE_DIR_,
                'sagLang' => $lang,
                'id_lang'=>$this->context->language->id
            ));
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $template = $this->display(__FILE__, 'views/templates/front/my-account-link.tpl');
            }
            else {
                $template = $this->display(__FILE__, 'views/templates/front/my-account-link17.tpl');
            }

            // Display link in my account
            return $template;
        } else {
            return false;
        }
    }

    public function hookActionCarrierProcess($params)
    {
        // Consent only works if user register (no id_customer devined if invite order)
        if ((int)$this->context->customer->id) {
            self::saveSagCustom((int)$this->context->customer->id);
        }
    }

    public function hookActionOrderHistoryAddAfter($params)
    {
        if (empty($params['order_history']) || !($params['order_history'] instanceof OrderHistory)) {
            return;
        }

        $this->processOrderHistory($params['order_history']);
    }

    public function hookActionObjectOrderHistoryAddAfter($params)
    {
        if (empty($params['object']) || !($params['object'] instanceof OrderHistory)) {
            return;
        }

        $this->processOrderHistory($params['object']);
    }

    private function processOrderHistory(OrderHistory $history)
    {
        static $processed = array();

        $key = (int)$history->id . '_' . (int)$history->id_order_state;

        if (in_array($key, $processed)) {
            return;
        }

        $processed[] = $key;

        if (Configuration::get('steavisgarantis_useOldOrdersMethod')) {
            return;
        }

        $orderId = (int)$history->id_order;
        $newStatusId = (int)$history->id_order_state;

        $statusesConfig = Configuration::get('steavisgarantis_includeStatus');

        if (!$statusesConfig) {
            return;
        }

        $triggerStatuses = array_map('intval', explode(',', $statusesConfig));

        if (in_array($newStatusId, $triggerStatuses)) {
            // Send order to GRC
            $this->sendOrder($orderId);
        }
    }
    
    private function sendOrder($orderId) 
    {
        // Get order object
        $order = new Order($orderId);
        $orderLangId = $order->id_lang;

        // Get API key based on lang
        $apiKey = Configuration::get('steavisgarantis_apiKey_' . $orderLangId);
        if (!$apiKey) {
            return;
        }

        // Get address and customer objects
        $address = new Address((int)$order->id_address_invoice);
        $customer = new Customer((int)$order->id_customer);

        $firstname = (!empty($address->firstname) ? $address->firstname : $customer->firstname);
        $lastname = (!empty($address->lastname) ? $address->lastname : $customer->lastname);

        // Check if phone transmission is enabled
        $sendPhone = Configuration::get('steavisgarantis_sendPhone');

        $data = array(
            'api_key' => $apiKey,
            'source'  => 'ps',
            'orders'  => array(
                array(
                    'id_order'   => (int)$order->id,
                    'order_date' => $order->date_add,
                    'firstname'  => $firstname,
                    'lastname'   => $lastname,
                    'email'      => $customer->email,
                    'phone'      => $sendPhone ? (!empty($address->phone_mobile) ? $address->phone_mobile : $address->phone) : null,
                    'reference'  => $order->reference,
                    'products'   => array()
                )
            )
        );

        // Get order products
        $products = $order->getProducts();

        foreach ($products as $item) {

            $idProduct = (int)$item['product_id'];

            $idProductAttribute = 0;
            if (isset($item['product_attribute_id'])) {
                $idProductAttribute = (int)$item['product_attribute_id'];
            } elseif (isset($item['id_product_attribute'])) {
                $idProductAttribute = (int)$item['id_product_attribute'];
            }

            $qty = (int)$item['product_quantity'];
            $unitPrice = (float)$item['unit_price_tax_excl'];

            $product = new Product($idProduct, false, $orderLangId);

            // Product name
            $productName = is_array($product->name) ? $product->name[$orderLangId] : $product->name;

            // Parent product data
            $reference = $product->reference;
            $ean13 = $product->ean13;
            $upc = isset($product->upc) ? $product->upc : '';

            // Get combination data if exists
            if ($idProductAttribute > 0) {
                $attrList = $product->getAttributeCombinations($orderLangId);

                if (is_array($attrList)) {
                    $combinationData = null;
                    $tmp = array();

                    foreach ($attrList as $ac) {
                        if ((int)$ac['id_attribute'] === $idProductAttribute) {
                            $tmp[] = $ac['group_name'] . ': ' . $ac['attribute_name'];
                            $combinationData = $ac;
                        }
                    }

                    if (!empty($tmp)) {
                        $productName .= ' - ' . implode(', ', $tmp);
                    }

                    if (!empty($combinationData['reference'])) {
                        $reference = $combinationData['reference'];
                    }

                    if (!empty($combinationData['ean13'])) {
                        $ean13 = $combinationData['ean13'];
                    }

                    if (!empty($combinationData['upc'])) {
                        $upc = $combinationData['upc'];
                    }
                }
            }

            // Get main category
            $idCategory = isset($product->id_category_default) ? (int)$product->id_category_default : 0;
            $category = new Category($idCategory, $orderLangId);
            $categoryName = is_array($category->name) ? $category->name[$orderLangId] : $category->name;

            $imageId = null;
            $imageUrl = null;

            // Get combination image if exists
            if ($idProductAttribute > 0) {
                $attributeImages = Product::_getAttributeImageAssociations($idProductAttribute);

                if (is_array($attributeImages) && !empty($attributeImages)) {
                    $imageId = (int)$attributeImages[0];
                }
            }

            // Get parent product image
            if (!$imageId) {
                $cover = Image::getCover($idProduct);
                if ($cover && isset($cover['id_image'])) {
                    $imageId = (int)$cover['id_image'];
                }
            }
            
            // Get product image URL
            if ($imageId) {
                $rewrite = is_array($product->link_rewrite) ? $product->link_rewrite[$orderLangId] : $product->link_rewrite;
                $imageUrl = $this->context->link->getImageLink($rewrite, $imageId, 'home_default');
            }

            // Get product public URL
            $productUrl = $this->context->link->getProductLink($product, null, null, null, null, null, $idProductAttribute);

            $data['orders'][0]['products'][] = array(
                'id'            => $idProduct,
                'name'          => $productName,
                'category_id'   => $idCategory,
                'category_name' => $categoryName,
                'qty'           => $qty,
                'unit_price'    => $unitPrice,
                'ean13'         => (!empty($ean13) ? $ean13 : ''),
                'sku'           => (!empty($reference) ? $reference : ''),
                'upc'           => (!empty($upc) ? $upc : ''),
                'url'           => $productUrl,
                'image_url'     => $imageUrl
            );
        }

        // Send non-blocking request to GRC API
        $url = 'https://api.guaranteed-reviews.com/private/v3/orders';
        $headers = array('Content-Type: application/json; charset=utf-8');
        $data = json_encode($data);
        
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        curl_exec($ch);
        curl_close($ch);
    }
    
    // Widget javascript
    public function hookdisplayHeader()
    {
        $newWidgets = Configuration::get('steavisgarantis_newWidgets');
        $publicKey = Configuration::get('steavisgarantis_publicKey');
        
        if (!$newWidgets || !$publicKey) {
            // Load old integration scripts
            $this->context->controller->addCSS(($this->_path).'views/css/style.css', 'all');
            $this->context->controller->addJS(($this->_path).'views/js/steavisgarantis.js', 'all');

            // Load popover library on product pages only
            if($this->context->controller instanceof ProductController) {
                $this->context->controller->addJS(($this->_path).'views/js/lib/popper.min.js');
                $this->context->controller->addJS(($this->_path).'views/js/lib/tippy-bundle.umd.min.js');
            }

            $apiKey = Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id);

            // Si on a une apiKey on load les siteIndics
            if ($apiKey) {
                $sagSiteCount = self::getReviewsCount($this->context->language->id);
                $sagSiteRate = self::getSiteRate($this->context->language->id);
                $url=self::getCertificateUrl($this->context->language->id);

                if (filter_var($url, FILTER_VALIDATE_URL)) {
                } else {
                    $url = Tools::substr($url, 0, 10);
                }
            }
            else {
                $sagSiteCount = false;
                $sagSiteRate = false;
                $url = false;
            }

            if (Configuration::get('steavisgarantis_widgetJavascript') and $apiKey) {
                $shopID = self::getShopId($this->context->language->id);
                $url=self::getCertificateUrl($this->context->language->id);
                $domain = self::getDomainUrl($apiKey);

                if (filter_var($url, FILTER_VALIDATE_URL)) {
                } else {
                    $url = Tools::substr($url, 0, 10);
                }

                $this->context->smarty->assign(array(
                    'url_ag' => $url,
                    'shopID' => $shopID,
                    'domain' => $domain,
                    'sagSiteCount' => $sagSiteCount,
                    'sagSiteRate' => $sagSiteRate,
                    'displayJSWidget' => 1
                ));
            } else {
                $this->context->smarty->assign(array(
                    'url_ag' => $url,
                    'sagSiteCount' => $sagSiteCount,
                    'sagSiteRate' => $sagSiteRate,
                    'displayJSWidget' => 0
                ));
            }
        }

        // Custom CSS
        $this->context->smarty->assign(array(
            'newWidgets' => $newWidgets,
            'publicKey' => $publicKey,
            'customCSS' => Configuration::get('steavisgarantis_customCSS')
        ));

        return $this->display(__FILE__, 'views/templates/front/displayHeader.tpl');
    }

    // Presta <1.5
    public function hookHeader()
    {
        return $this->hookdisplayHeader();
    }

    public function hookFooter()
    {
        return $this->hookdisplayFooter();
    }

    public function hookProductActions()
    {
        return $this->hookdisplayRightColumnProduct();
    }

    public function hookdisplayProductAdditionalInfo()
    {
        return $this->hookdisplayRightColumnProduct();   
    }

    public function hookRightColumn()
    {
        return $this->hookdisplayRightColumn();
    }

    public function hookLeftColumn()
    {
        return $this->hookdisplayLeftColumn();
    }

    public function hookProductTab()
    {
        return $this->hookdisplayProductTab();
    }

    public function hookProductTabContent()
    {
        return $this->hookdisplayProductTabContent();
    }

    public function hookdisplayProductTab()
    {
        // Si la version est inférieure à 1.6.0 on utilise le product tab sinon non
        if (version_compare(_PS_VERSION_, '1.6.0', '<') == Configuration::get("steavisgarantis_normalBehaviour")) {
            $productID = (int)(Tools::getValue('id_product'));
            $id_lang = (int)$this->context->language->id;
            $sqlQuery = "SELECT count(*) FROM "._DB_PREFIX_."steavisgarantis_reviews WHERE product_id='$productID' and id_lang='$id_lang'";
            $nb= Db::getInstance()->getValue($sqlQuery);

            if ($nb < 1) {
                return "";
            }

            $this->context->smarty->assign(array(
                'reviewTabStr' => $this->l('Customer reviews'),
            ));

            return $this->display(__FILE__, 'views/templates/front/displayProductTab.tpl');
        } else {
            return false;
        }
    }

    public function hookdisplayProductTabContent()
    {
        $productID = (int)(Tools::getValue('id_product'));

        if(Configuration::get('steavisgarantis_newWidgets')) {
            // Get product SKU
            $product = new Product($productID);
            $productSku = $product->reference;

            $this->context->smarty->assign(array(
                'productId' => $productID,
                'productSku' => $productSku
            ));

            return $this->display(__FILE__, 'views/templates/front/widgets/productReviews.tpl');
        }

        $id_lang = (int)$this->context->language->id;

        // Récupération du nombre d'avis
        $sqlQuery = "SELECT count(*) FROM "._DB_PREFIX_."steavisgarantis_reviews WHERE product_id='$productID' and id_lang='$id_lang'";
        $nb = Db::getInstance()->getValue($sqlQuery);
        $sqlQuery = "SELECT * FROM "._DB_PREFIX_."steavisgarantis_average_rating WHERE product_id='$productID' and id_lang='$id_lang'";
        $ratingValues = Db::getInstance()->getRow($sqlQuery);

        $rating = 0;
        if($ratingValues) {
            $rating = $ratingValues['rate'];
        }

        if ($nb < 1) {
            return ""; // Si Aucun avis, on retourne vide
        }

        $nbMaxReviews = Configuration::get('steavisgarantis_maxReviewPerPage');
        $sqlQuery = "SELECT * FROM "._DB_PREFIX_."steavisgarantis_reviews WHERE product_id='$productID' and id_lang='$id_lang' ORDER BY date_time DESC LIMIT $nbMaxReviews";
        $reviews = Db::getInstance()->ExecuteS($sqlQuery);

        // Add potential custom answers
        $reviews = self::addCustomAnswers($reviews);

        // Récupération de l'objet produit
        if (!isset($params['product'])) {
            if (!$id_product = Tools::getValue('id_product')) {
                return $this->l('Missing product object. Set new object mode from the back office Configuration');
            }

            // Fix context undefined bug in 1.4, maybe PS_LANG_DEFAULT works in 1.5 1.6... etc but not tested so we keep old code
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'));
            } else {
                $product = new Product($id_product, false, $this->context->language->id);
            }

            $params['product'] = $product;
        }

        // On formate la date des avis
        foreach ($reviews as $key => $review) {
            $reviews[$key]["date_time"] = self::formatDate($review["date_time"], $id_lang);
            if ($reviews[$key]["order_date"] and (strtotime($reviews[$key]["order_date"])>0)) {
                $reviews[$key]["order_date"] = self::formatOrderDate($review["order_date"], $id_lang);
            }
            else {
                $reviews[$key]["order_date"] = false;
            }
        }

        // Get product url for json-ld structured datas "@id"
        $product_url = self::getProductUrl($productID, $id_lang);

        // Pass lang to tpl to manage style through CSS
        $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));

        $sagLogo = self::getImg($id_lang, "steavisgarantis_logo_");
        $url = self::getCertificateUrlByLang($lang);

        $this->context->smarty->assign(array(
            'reviews' => $reviews,
            'ratingValues' => $ratingValues,
            'nbOfReviews' => $nb,
            'reviewsAverage' => round($rating, 1),
            'certificateUrl'=> $url,
            'modules_dir'=> _MODULE_DIR_,
            'structuredFormat'=> Configuration::get('steavisgarantis_structuredFormat'),
            'showStructured'=> Configuration::get('steavisgarantis_showStructured'),
            'maxReviewsPage' => Configuration::get('steavisgarantis_maxReviewPerPage'),
            'sagProduct'=> $params['product'],
            'reviewTabStr' => $this->l('Customer reviews'),
            'sagLogo' => $sagLogo,
            'sagLang' => $lang,
            'id_lang'=>$this->context->language->id,
            'productUrl' => $product_url,
			'starColor' => Configuration::get('steavisgarantis_starColor')
        ));

        if (version_compare(_PS_VERSION_, '1.6.0', '<') == Configuration::get("steavisgarantis_normalBehaviour")) {
            return $this->display(__FILE__, 'views/templates/front/displayProductTabContent.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/front/displayProductTabContent16.tpl');
        }
    }

    // New product tab and tab content for PS1.7
    public function hookDisplayProductExtraContent($params)
    {
        $productID = (int)(Tools::getValue('id_product'));

        if(Configuration::get('steavisgarantis_newWidgets')) {
            // Get product SKU
            $product = new Product($productID);
            $productSku = $product->reference;

            $this->context->smarty->assign(array(
                'productId' => $productID,
                'productSku' => $productSku
            ));

            $output = $this->display(__FILE__, 'views/templates/front/widgets/productReviews.tpl');

            $productExtraContent = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
            $array[] = $productExtraContent->setTitle($this->l('Customer reviews'))->setContent($output);
            
            return $array;
        }

        $id_lang = (int)$this->context->language->id;

        // Récupération du nombre d'avis
        $sqlQuery = "SELECT count(*) FROM "._DB_PREFIX_."steavisgarantis_reviews WHERE product_id='$productID' and id_lang='$id_lang'";
        $nb= Db::getInstance()->getValue($sqlQuery);
        $sqlQuery = "SELECT * FROM "._DB_PREFIX_."steavisgarantis_average_rating WHERE product_id='$productID' and id_lang='$id_lang'";
        $ratingValues = Db::getInstance()->getRow($sqlQuery);

        $rating = 0;
        if($ratingValues) {
            $rating = $ratingValues['rate'];
        }

        $array = array();
        if ($nb > 0) {
            $nbMaxReviews=Configuration::get('steavisgarantis_maxReviewPerPage');
            $sqlQuery = "SELECT * FROM "._DB_PREFIX_."steavisgarantis_reviews WHERE product_id='$productID' and id_lang='$id_lang' ORDER BY date_time DESC LIMIT $nbMaxReviews";
            $reviews = Db::getInstance()->ExecuteS($sqlQuery);

            //Add potential custom answers
            $reviews = self::addCustomAnswers($reviews);

            //Récupération de l'objet produit
            if (!isset($params['product'])) {
                if (!$id_product = Tools::getValue('id_product')) {
                    return $this->l('Missing product object. Set new object mode from the back office Configuration');
                }

                $product = new Product($id_product, false, $id_lang);
                $params['product'] = $product;
            }

            //On formate la date des avis
            foreach ($reviews as $key => $review) {
                $reviews[$key]["date_time"] = self::formatDate($review["date_time"], $id_lang);
                if ($reviews[$key]["order_date"] and (strtotime($reviews[$key]["order_date"])>0)) {
                    $reviews[$key]["order_date"] = self::formatOrderDate($review["order_date"], $id_lang);
                }
                else {
                    $reviews[$key]["order_date"] = false;
                }
            }

            //Get product url for json-ld structured datas "@id"
            $product_url = self::getProductUrl($productID, $id_lang);

            //Pass lang to tpl to manage style through CSS
            $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));

            $sagLogo = self::getImg($id_lang, "steavisgarantis_logo_");
            $url = self::getCertificateUrlByLang($lang);

            $this->context->smarty->assign(array(
                'reviews' => $reviews,
                'ratingValues' => $ratingValues,
                'nbOfReviews' => $nb,
                'reviewsAverage' => round($rating, 1),
                'certificateUrl'=> $url,
                'structuredFormat'=> Configuration::get('steavisgarantis_structuredFormat'),
                'showStructured'=> Configuration::get('steavisgarantis_showStructured'),
                'maxReviewsPage' => Configuration::get('steavisgarantis_maxReviewPerPage'),
                'sagProduct'=> $params['product'],
                'modules_dir' => _MODULE_DIR_,
                'reviewTabStr' => $this->l('Customer reviews'),
                'sagLogo' => $sagLogo,
                'sagLang' => $lang,
                'id_lang'=>$this->context->language->id,
                'productUrl' => $product_url,
				'starColor' => Configuration::get('steavisgarantis_starColor'),
            ));

            $output = ($this->display(__FILE__, 'views/templates/front/displayProductTabContent.tpl'));
            $productExtraContent = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
            $array[] = $productExtraContent->setTitle($this->l('Customer reviews'))->setContent($output);
        }

        return $array;
    }

    // New rightcolumnproduct for ps 1.7
    public function hookDisplayProductButtons($params)
    {
        // Only if version > 1.7
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->hookdisplayRightColumnProduct();
        }

        return "";
    }

    public function hookdisplayRightColumnProduct()
    {
        $productID = (int)(Tools::getValue('id_product'));

        if(Configuration::get('steavisgarantis_newWidgets')) {
            // Get product SKU
            $product = new Product($productID);
            $productSku = $product->reference;

            $this->context->smarty->assign(array(
                'productId' => $productID,
                'productSku' => $productSku
            ));

            return $this->display(__FILE__, 'views/templates/front/widgets/productSummary.tpl');
        }

        $id_lang = (int)$this->context->language->id;
        $sqlQuery = "SELECT count(*) FROM "._DB_PREFIX_."steavisgarantis_reviews WHERE product_id='$productID' and id_lang='$id_lang'";
        $nb= Db::getInstance()->getValue($sqlQuery);
        $sqlQuery = "SELECT rate FROM "._DB_PREFIX_."steavisgarantis_average_rating WHERE product_id='$productID' and id_lang='$id_lang'";
        $rating= Db::getInstance()->getValue($sqlQuery);
        if ($nb < 1) {
            return ""; //Si Aucun avis, on retourne vide
        }

        $sagLogo1 = self::getImg($id_lang, "steavisgarantis_logo_badge_");
        $sagLogo2 = self::getImg($id_lang, "cocarde_", "svg");

        //Pass lang to tpl to manage style through CSS
        $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_' . $this->context->language->id));

        //Get summary design style
        $stylewidget = Configuration::get('steavisgarantis_summaryDesign');

        $this->context->smarty->assign(array(
            'nbReviews' => $nb,
            'reviewRate' =>  $rating,
            'sagLogoBadge' =>  $sagLogo1,
            'sagLogoCocarde' =>  $sagLogo2,
            'modules_dir'=> _MODULE_DIR_,
            'sagLang'=> $lang,
			'starColor' => Configuration::get('steavisgarantis_starColor'),
        ));

        return $this->display(__FILE__, 'views/templates/front/displayRightColumnProduct-' . $stylewidget . '.tpl');
    }

    public function getContent()
    {
        $output = null;

        //Si on a soumis le formulaire de création de certificat
        if (Tools::getValue('createCertificate')) {
            if (!Tools::getValue('cgv_1')) { //Si on a pas validé les CGV on renvoie une erreur
                $output .= $this->displayError($this->l('You must accept our terms and conditions to continue'));
            } elseif (!(filter_var(Tools::getValue('steavisgarantis_accountMail'), FILTER_VALIDATE_EMAIL))) {
                $output .= $this->displayError($this->l('You must enter a valid email address to continue'));
            } elseif (!Tools::getValue('steavisgarantis_certificate_lang')) {
                $output .= $this->displayError($this->l('You must choose a language'));
            } else {
                //Define on which domain we have to create certificate
                $certifLang = Tools::getValue('steavisgarantis_certificate_lang');
                $domain = self::getDomainUrlFromLang($certifLang);

                $datas = self::createCertificate(
                    $domain,
                    Tools::getValue('api_siteName'),
                    Tools::getValue('steavisgarantis_accountAddress'),
                    Tools::getValue('steavisgarantis_accountAddress2'),
                    Tools::getValue('steavisgarantis_accountCP'),
                    Tools::getValue('steavisgarantis_accountCity'),
                    Tools::getValue('steavisgarantis_accountMail'),
                    Configuration::get('PS_LOGO')
                );

                if ($datas["apiKey"]) { //Si on a une réponse contenant une clé d'api
                    //On met à jour la clé d'api pour toutes les langues
                    $languages = Language::getLanguages(true, Context::getContext()->shop->id);

                    foreach ($languages as $language) {
                        Configuration::updateValue('steavisgarantis_apiKey_'.$language["id_lang"], $datas["apiKey"]);    //On enregistre l'apiKey
                    }

                    Configuration::updateValue('steavisgarantis_accountMail', Tools::getValue('steavisgarantis_accountMail')); //On enregistre le mail du compte
                    Configuration::updateValue('steavisgarantis_password', $datas["password"]); //Et le mot de passe
                    Configuration::updateValue('steavisgarantis_apiKeyFromApi', $datas["apiKey"]); //Et la clé générée depuis l'api

                    //Et on active tous les widgets
                    Configuration::updateValue('steavisgarantis_widgetPosition', "left");
                    Configuration::updateValue('steavisgarantis_widgetJavascript', true);
                    Configuration::updateValue('steavisgarantis_catStars', false);
                    Configuration::updateValue('steavisgarantis_customCSS', "");
					Configuration::updateValue('steavisgarantis_starColor', "#f5d700");
                    Configuration::updateValue('steavisgarantis_footerLink', true);

                    //On regénère les indicateurs du site
                    $setSiteIndicators = self::setSiteIndicators();
                    Configuration::updateValue('steavisgarantis_rgpd', 0);

                    //On regénère l'url du certificat + sauvegarde
                    $urlCertificat = self::setCertificateUrlFromAPI();
                    $output .= $this->displayConfirmation($this->l('Attestation successfully created : ') . $urlCertificat);
                } else {
                    //Si on a déjà un certificat
                    if ($datas["error"] == "Domain already registered") {
                        $getApiKeyUrl = $domain . 'configuration/prestashop/';
                        $output .= $this->displayConfirmation($this->l('This website is already registered, to get your Api Key, clic here : ') . $getApiKeyUrl);
                    }

                    //Si l'email existe déjà
                    elseif (array_key_exists("existing_user_login", $datas["error"]) or array_key_exists("existing_user_email", $datas["error"])) {
                        $output .= $this->displayError($this->l('Given email address already used with another account. Thanks for entering another address.'));
                    }

                    //Sinon on a une erreur mais on ne sait pas pourquoi
                    else {
                        $registerUrl = $domain . 'wp-login.php?action=register';
                        $output .= $this->displayConfirmation($this->l('Thanks to follow this url to finish your registration : ') . $registerUrl);
                        Tools::redirect($registerUrl);
                        exit();
                    }
                }
            }
        } elseif (Tools::getValue('mainConfig')) { // Sinon si on a soumis le formulaire de configuration principal

            // On récupère les données passéees
            $steavisgarantis_afterDays = Tools::getValue('steavisgarantis_afterDays');
            if ($steavisgarantis_afterDays < 1) {
                $steavisgarantis_afterDays = 1;
                $output .= $this->displayError($this->l('Email sending delay mustn\'t be less than 1 day.'));
            }

            $steavisgarantis_maxReviewPerPage = Tools::getValue('steavisgarantis_maxReviewPerPage');
            if ($steavisgarantis_maxReviewPerPage < 1) {
                $steavisgarantis_maxReviewPerPage = 1;
                $output .= $this->displayError($this->l('Maximum number of reviews showed in the product page mustn\'t be less than 1 review.'));
            }

            $steavisgarantis_structuredFormat = (Tools::getValue('steavisgarantis_structuredFormat'));
            $steavisgarantis_showStructured = (Tools::getValue('steavisgarantis_showStructured'));
            $steavisgarantis_normalBehaviour = (Tools::getValue('steavisgarantis_normalBehaviour'));
            $steavisgarantis_widgetPosition = Tools::getValue('steavisgarantis_widgetPosition');
            $steavisgarantis_summaryDesign = Tools::getValue('steavisgarantis_summaryDesign');
            $steavisgarantis_catStars = (Tools::getValue('steavisgarantis_catStars'));
            $steavisgarantis_customCSS = (Tools::getValue('steavisgarantis_customCSS'));
			$steavisgarantis_starColor = (Tools::getValue('steavisgarantis_starColor'));
            $steavisgarantis_widgetJavascript = (Tools::getValue('steavisgarantis_widgetJavascript'));
            $steavisgarantis_footerLink = (Tools::getValue('steavisgarantis_footerLink'));
            $steavisgarantis_rgpd = (Tools::getValue('steavisgarantis_rgpd'));
            $steavisgarantis_includeStatus = (Tools::getValue('steavisgarantis_includeStatus'));
            $steavisgarantis_includeStatusString = "";

            if ($steavisgarantis_includeStatus) {
                $steavisgarantis_includeStatusString = implode(",", $steavisgarantis_includeStatus);
            }

            $steavisgarantis_publicKey = (Tools::getValue('steavisgarantis_publicKey'));
            $steavisgarantis_newWidgets = (Tools::getValue('steavisgarantis_newWidgets_on'));
            $steavisgarantis_useOldOrdersMethod = (Tools::getValue('steavisgarantis_useOldOrdersMethod_on'));
            $steavisgarantis_sendPhone = (Tools::getValue('steavisgarantis_sendPhone_on'));

            // On récupère et met à jour les clé d'api pour chaque langue
            $languages = Language::getLanguages(true, Context::getContext()->shop->id);

            foreach ($languages as $language) {
                $steavisgarantis = Tools::getValue('steavisgarantis_apiKey_'.$language["id_lang"]);
                Configuration::updateValue('steavisgarantis_apiKey_'.$language["id_lang"], $steavisgarantis);
            }

            Configuration::updateValue('steavisgarantis_publicKey', $steavisgarantis_publicKey);
            Configuration::updateValue('steavisgarantis_newWidgets', $steavisgarantis_newWidgets);
            Configuration::updateValue('steavisgarantis_useOldOrdersMethod', $steavisgarantis_useOldOrdersMethod);
            Configuration::updateValue('steavisgarantis_sendPhone', $steavisgarantis_sendPhone);

            Configuration::updateValue('steavisgarantis_includeStatus', $steavisgarantis_includeStatusString);
            Configuration::updateValue('steavisgarantis_afterDays', $steavisgarantis_afterDays);
            Configuration::updateValue('steavisgarantis_widgetPosition', $steavisgarantis_widgetPosition);
            Configuration::updateValue('steavisgarantis_summaryDesign', $steavisgarantis_summaryDesign);
            Configuration::updateValue('steavisgarantis_catStars', $steavisgarantis_catStars);
            Configuration::updateValue('steavisgarantis_customCSS', $steavisgarantis_customCSS);
			Configuration::updateValue('steavisgarantis_starColor', $steavisgarantis_starColor);
            Configuration::updateValue('steavisgarantis_widgetJavascript', $steavisgarantis_widgetJavascript);
            Configuration::updateValue('steavisgarantis_footerLink', $steavisgarantis_footerLink);
            Configuration::updateValue('steavisgarantis_rgpd', $steavisgarantis_rgpd);
            Configuration::updateValue('steavisgarantis_structuredFormat', $steavisgarantis_structuredFormat);
            Configuration::updateValue('steavisgarantis_showStructured', $steavisgarantis_showStructured);
            Configuration::updateValue('steavisgarantis_normalBehaviour', $steavisgarantis_normalBehaviour);
            Configuration::updateValue('steavisgarantis_maxReviewPerPage', $steavisgarantis_maxReviewPerPage);

            //On regénère les indicateurs du site
            self::setSiteIndicators();

            //On regénère l'url du certificat + sauvegarde
            self::setCertificateUrlFromAPI();

            $output .= $this->displayConfirmation($this->l('Successfully updated'));

            //Si le module a été mis à jour, la table steavisgarantis_customer n'existe peut être pas
            if ($steavisgarantis_rgpd == 1) {
                //Si la table n'existe pas, on la créée
                $sqlQuery = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'steavisgarantis_customer` (
                      `id_steavisgarantis_customfield` int(10) unsigned NOT NULL,
                      `id_customer` int(10) unsigned NOT NULL,
                      `value` text NOT NULL,
                      PRIMARY KEY (`id_steavisgarantis_customfield`, `id_customer`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

                if (!Db::getInstance()->Execute($sqlQuery)) {
                    $output .= $this->displayError($this->l('SQL database creation error'));
                }

                //Et les hooks ne sont peut être pas ajoutés non plus
                if (!$this->isRegisteredInHook('displayCustomerAccount')) {
                    $output .= $this->displayError($this->l('Module updated and RGPD Consent enabled, please uninstall / reinstall or add hooks manually'));
                }
            }
        } elseif (Tools::getValue('alreadyUser')) { //Si on a soumis le formulaire "déjà inscrit"
            $steavisgarantis = Tools::getValue('steavisgarantis_apiKey');
            //On vérifie qu'on a une api KEY
            if (!$steavisgarantis) {
                $output .= $this->displayError($this->l('You must enter an Api Key to continue'));
            } else { //Si c'est bon, on active tous les widgets
                $steavisgarantis_widgetPosition = "left";
                $steavisgarantis_footerLink = true;
                $steavisgarantis_widgetJavascript = true;
                $steavisgarantis_catStars = false;  //On laisse les étoiles catégories désactivées
                $steavisgarantis_customCSS = "";  //Par défaut vide
				$steavisgarantis_starColor = "#f5d700"; //la couleur par défaut est le jaune SAG
                $steavisgarantis_summaryDesign = 1;
                $output .= $this->displayConfirmation($this->l('Module successfully configured, widgets activated'));

                //On a entré une clé, on la met pour toutes les langues
                $languages = Language::getLanguages(true, Context::getContext()->shop->id);

                foreach ($languages as $language) {
                    Configuration::updateValue('steavisgarantis_apiKey_'.$language["id_lang"], $steavisgarantis);
                }

                Configuration::updateValue('steavisgarantis_widgetPosition', $steavisgarantis_widgetPosition);
                Configuration::updateValue('steavisgarantis_summaryDesign', $steavisgarantis_summaryDesign);
                Configuration::updateValue('steavisgarantis_widgetJavascript', $steavisgarantis_widgetJavascript);
                Configuration::updateValue('steavisgarantis_catStars', $steavisgarantis_catStars);
                Configuration::updateValue('steavisgarantis_customCSS', $steavisgarantis_customCSS);
				Configuration::updateValue('steavisgarantis_starColor', $steavisgarantis_starColor);
                Configuration::updateValue('steavisgarantis_footerLink', $steavisgarantis_footerLink);
                Configuration::updateValue('steavisgarantis_rgpd', 0);
                Configuration::updateValue('steavisgarantis_accountMail', ""); //On efface le mail du compte
                Configuration::updateValue('steavisgarantis_password', ""); //Et le mot de passe

                //On regénère les indicateurs du site
                self::setSiteIndicators();

                //On regénère l'url du certificat + sauvegarde
                self::setCertificateUrlFromAPI();
            }
        } elseif (Tools::getValue('forceRegisterHooks')) {
            // PS 1.5+
            $this->registerHook('actionOrderHistoryAddAfter');

            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                // PS 1.7+
                $this->registerHook('actionObjectOrderHistoryAddAfter');
            }
            
            $output .= $this->displayConfirmation('Hooks successfully registered.');
        }

        switch ($this->context->language->iso_code) {
            case "fr" : $cgvUrl = "https://www.societe-des-avis-garantis.fr/cgv/";break;
            case "en" : $cgvUrl = "https://www.guaranteed-reviews.com/terms/";break;
            case "es" : $cgvUrl = "https://www.sociedad-de-opiniones-contrastadas.es/cgv/";break;
            case "it" : $cgvUrl = "https://www.societa-recensioni-garantite.it/cgv/";break;
            case "de" : $cgvUrl = "https://www.g-g-b.de/verkaufsbedingungen/";break;
            case "nl" : $cgvUrl = "https://www.g-b-n.nl/algemene-verkoopvoorwaarden/";break;
            case "pl" : $cgvUrl = "https://www.gwarantowane-opinie.pl/ogolne-warunki-sprzedazy/";break;
            case "pt" : $cgvUrl = "https://www.sdag.pt/condicoes-gerais-de-venda/";break;
            default   : $cgvUrl = "https://www.guaranteed-reviews.com/terms/";break;
        }

        $this->context->smarty->assign(array(
            'cgvUrl' => $cgvUrl,
        ));

        return $output . $this->displayForm() . $this->display(__FILE__, 'views/templates/front/displayConfiguration.tpl');
    }

    public function displayForm()
    {
        $stateList = array();

        // Get default Language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        //Get order state list
        $sqlQuery = "SELECT * FROM "._DB_PREFIX_."order_state_lang WHERE id_lang=$default_lang";
        $orderStates = Db::getInstance()->ExecuteS($sqlQuery);

        //Format datas
        foreach ($orderStates as $orderState) {
            $stateList[] = array("key" => $orderState['id_order_state'], "name" => $orderState['name']);
        }

        // Register hooks link
        $registerHooksLink = $this->context->link->getAdminLink('AdminModules', true, array(), array(
            'configure' => $this->name,
            'forceRegisterHooks' => 1
        ));

        $installedForm = array();

        // Init Fields form array
        $installedForm['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Public API Key'),
                    'name' => 'steavisgarantis_publicKey',
                    'size' => 30,
                    'desc' => $this->l('Retrieve your public API key from your account : ') . '<a href="' . $this->l('https://www.guaranteed-reviews.com/settings/plugin/') . '" target="_blank">' . $this->l('https://www.guaranteed-reviews.com/settings/plugin/') . '</a>',
                    'required' => false
                ),

                array(
                    'type' => 'select',
                    'label' => $this->l('Included statuses'),
                    'class' => "steavisgarantisIncludeStatus",
                    'name' => 'steavisgarantis_includeStatus[]',
                    'desc' => $this->l('Select order statuses you want to send review requests (Use "Ctrl" keyboard key to select many ones)'),
                    'multiple' => true,
                    'options' => array(
                        'query' => $stateList,
                        'id' => 'key',
                        'name' => 'name'
                    )
                ),

                array(
                    'type' => 'checkbox',
                    'label' => $this->l('New widgets integration'),
                    'name' => 'steavisgarantis_newWidgets',
                    'desc' => $this->l('Enjoy the latest features and advanced customization options by enabling the new widget integration.') . '<br>' . 
                        '<p class="alert alert-info"><strong>' . $this->l('Requires that the Public API key be configured in the above configuration.') . '</strong><br>' . 
                        $this->l('Widgets settings : ') . ' <a href="' . $this->l('https://www.guaranteed-reviews.com/settings/widget-integration/') . '" target="_blank">' . $this->l('https://www.guaranteed-reviews.com/settings/widget-integration/') . '</a></p>',
                    'values' => array(
                        'query' => array(
                            array('id' => 'on', 'name' => $this->l('Enabled'), 'val' => 1)
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),

                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Use old method to retrieve orders'),
                    'name' => 'steavisgarantis_useOldOrdersMethod',
                    'values' => array(
                        'query' => array(
                            array('id' => 'on', 'name' => $this->l('Enabled') . ' ' . $this->l('(only change this upon request from support)'), 'val' => 1)
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),

                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Phone number transmission'),
                    'name' => 'steavisgarantis_sendPhone',
                    'desc' => '<p class="alert alert-info">'. $this->l('By enabling this option, you authorize the transmission of your customers phone numbers in order to send them an invitation to leave a review. You are responsible for ensuring that this use complies with applicable regulations (including GDPR): informing customers, having an appropriate legal basis, and allowing them to opt out where required. By enabling this option, you confirm that the necessary information has been included in your privacy policy.') . '</p>',
                    'values' => array(
                        'query' => array(
                            array('id' => 'on', 'name' => $this->l('Enabled'), 'val' => 1)
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Review request delay'),
                    'name' => 'steavisgarantis_afterDays',
                    'size' => 30,
                    'desc' => $this->l('Number of days before sending review request (after order passed to an included statuses).'),
                    'required' => false
                ),

                // Choix de l'emplacement du widget iFrame
                array(
                  'type'      => 'radio',
                  'label'     => $this->l('Widget iFrame'),
                  'desc'      => $this->l('Left / Right display only if your theme uses columns'),
                  'name'      => 'steavisgarantis_widgetPosition',
                  'required'  => true,
                'class'     => 't',
                 'is_bool'   => false,
                  'values'    => array(
                    array(
                      'id'    => 'active_on',
                      'value' => "left",
                      'label' => $this->l('Left')
                    ),
                    array(
                      'id'    => 'active_off',
                      'value' => "right",
                      'label' => $this->l('Right')
                    ),
                    array(
                      'id'    => 'active_footer',
                      'value' => "footer",
                      'label' => $this->l('Footer')
                    ),
                    array(
                        'id'    => 'active_home',
                        'value' => "home",
                        'label' => $this->l('Home')
                    ),
                    array(
                      'id'    => 'active_none',
                      'value' => "none",
                      'label' => $this->l('Disable')
                    )
                  ),
                ),
                //Choix de l'emplacement du widget Javascript
                array(
                  'type'      => 'radio',
                  'label'     => $this->l('Widget Javascript'),
                  'desc'      => $this->l('To change this widget and position, go to Guaranteed Reviews Company website'),
                  'name'      => 'steavisgarantis_widgetJavascript',
                  'required'  => true,
                'class'     => 't',
                 'is_bool'   => false,
                  'values'    => array(
                    array(
                      'id'    => 'wjs_on',
                      'value' => true,
                      'label' => $this->l('Enabled')
                    ),
                    array(
                      'id'    => 'wjs_off',
                      'value' => false,
                      'label' => $this->l('Disabled')
                    )
                  ),
                ),

                //Choix de si on affiche ou pas le lien de verif du certificat dans le footer
                array(
                  'type'      => 'radio',
                  'label'     => $this->l('Checking link'),
                  'desc'      => $this->l('Display a checking link in the footer pointing to your attestation page. (Important for your SEO)'),
                  'name'      => 'steavisgarantis_footerLink',
                  'required'  => true,
                'class'     => 't',
                 'is_bool'   => true,
                  'values'    => array(
                    array(
                      'id'    => 'showFooterLink_on',
                      'value' => 1,
                      'label' => $this->l('Enable')
                    ),
                    array(
                      'id'    => 'showFooterLink_off',
                      'value' => 0,
                      'label' => $this->l('Disable')
                    )
                  ),
                ),
                
                //Pour les étoiles catégorie
                array(
                  'type'      => 'radio',
                  'label'     => $this->l('Stars on categories'),
                  'desc'      => $this->l('Display stars on categories pages'),
                  'name'      => 'steavisgarantis_catStars',
                  'required'  => true,
                'class'     => 't',
                 'is_bool'   => true,
                  'values'    => array(
                    array(
                      'id'    => 'catStars_on',
                      'value' => 1,
                      'label' => $this->l('Enable')
                    ),
                    array(
                      'id'    => 'catStars_off',
                      'value' => 0,
                      'label' => $this->l('Disable')
                    )
                  ),
                ),                

                //Choix de si on déclare les données structurées "Product" et "Product name" ou pas sur les fiches produit
                array(
                  'type'      => 'radio',
                  'label'     => $this->l('Force structured datas'),
                  'desc'      => $this->l('Enable only if your theme doesn\'t implement them. Check your product pages with Google structured datas testing tool'),
                  'name'      => 'steavisgarantis_showStructured',
                  'required'  => true,
                'class'     => 't',
                 'is_bool'   => true,
                  'values'    => array(
                    array(
                      'id'    => 'showStructured_on',
                      'value' => 1,
                      'label' => $this->l('Yes')
                    ),
                    array(
                      'id'    => 'showStructured_off',
                      'value' => 0,
                      'label' => $this->l('No')
                    )
                  ),
                ),
                
                //Choix du type de données structurées à afficher sur les pages produits
                array(
                  'type'      => 'radio',
                  'label'     => $this->l('Structured datas format'),
                  'desc'      => $this->l('Choose the structured datas format'),
                  'name'      => 'steavisgarantis_structuredFormat',
                  'required'  => true,
                'class'     => 't',
                 'is_bool'   => false,
                  'values'    => array(
                    array(
                      'id'    => 'structuredFormat_microdata',
                      'value' => "microdata",
                      'label' => $this->l('Microdata')
                    ),
                    array(
                      'id'    => 'structuredFormat_json',
                      'value' => "json-ld",
                      'label' => $this->l('JSON-LD')
                    )
                  ),
                ),

                //Pour les avis sur les fiches produit, fonctionnement normal ou pas
                array(
                  'type'      => 'radio',
                  'label'     => $this->l('Recent theme'),
                  'desc'      => $this->l('Correct potential reviews widget display conflict on product pages'),
                  'name'      => 'steavisgarantis_normalBehaviour',
                  'required'  => true,
                'class'     => 't',
                 'is_bool'   => true,
                  'values'    => array(
                    array(
                      'id'    => 'normalBehaviour_on',
                      'value' => 1,
                      'label' => $this->l('Normal')
                    ),
                    array(
                      'id'    => 'normalBehaviour_off',
                      'value' => 0,
                      'label' => $this->l('Retro')
                    )
                  ),
                ),

                //Pour le design du Summary
                array(
                  'type'      => 'radio',
                  'label'     => $this->l('Widget product rating'),
                  'desc'      => $this->l('Choose widget product rating style (on product pages)'),
                  'name'      => 'steavisgarantis_summaryDesign',
                  'required'  => true,
                'class'     => 't',
                 'is_bool'   => false,
                  'values'    => array(
                    array(
                      'id'    => 'design_1',
                      'value' => 1,
                      'label' => $this->l('Classic style')
                    ),
                    array(
                      'id'    => 'design_2',
                      'value' => 2,
                      'label' => $this->l('Logo & stars')
                    ),
                    array(
                      'id'    => 'design_3',
                      'value' => 3,
                      'label' => $this->l('Stars')
                    )
                  ),
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Maximum product reviews'),
                    'name' => 'steavisgarantis_maxReviewPerPage',
                    'size' => 30,
                    'desc' => $this->l('Choose how many product reviews you would like to show by default before showing the "Show more reviews" button'),
                    'required' => true
                ),

				array(
                    'type' => 'color',
                    'label' => $this->l('Stars color'),
                    'name' => 'steavisgarantis_starColor',
                    'class' => 'stars-color-picker',
                    'size' => 30,
                    'desc' => $this->l('Customize stars color'),
                    'required' => true
                ),

                array(
                    'type' => 'textarea',
                    'label' => $this->l('Custom CSS'),
                    'name' => 'steavisgarantis_customCSS',
                    'size' => 2000,
                    'desc' => $this->l('Input your custom css here'),
                    'required' => false
                ),

                array(
                    'type' => 'html',
                    'label' => 'Debug',
                    'name' => 'steavisgarantis_check_hooks',
                    'html_content' => (version_compare(_PS_VERSION_, '1.7', '>=') ? '
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Functionality</th>
                                    <th>Hook</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Order transmission</td>
                                    <td>
                                        <code>actionObjectOrderHistoryAddAfter</code>
                                    </td>
                                    <td>
                                        '. ($this->isRegisteredInHook('actionObjectOrderHistoryAddAfter') ? 
                                                '<span class="text-success">✔</span>' : 
                                                '<span class="text-danger">✖</span>') . '
                                    </td>
                                </tr>
                            </tbody>
                        </table>' : '') . '

                        <a href="'. $registerHooksLink .'" class="btn btn-default btn-xs">Force hook registration</a>
                    '
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button btn btn-default',
                'name' => 'mainConfig'
            )
        );

        //Init Api inputs fields
        $languages = Language::getLanguages(true, Context::getContext()->shop->id);
        foreach ($languages as $language) {
            $reviewManagement = array(
                'type' => 'text',
                'label' => $this->l('Private API Key - Lang ') . $language["name"],
                'name' => 'steavisgarantis_apiKey_'.$language["id_lang"],
                'size' => 30,
                'required' => false
            );

            $currentApiKey = Configuration::get('steavisgarantis_apiKey_'.$language["id_lang"]);
            if ($currentApiKey) {
                $domainUrl = self::getDomainUrl($currentApiKey);
                $reviewManagement["desc"] = $this->l('Reviews management : ') . '<a href="'. $domainUrl .'" target="_blank">' . $domainUrl . '</a>';
            }

            //Si on est sur la clé d'api générée depuis l'api
            if ($currentApiKey == Configuration::get('steavisgarantis_apiKeyFromApi') and $currentApiKey) {
                //On affiche les identifiants de connexion
                $reviewManagement["desc"] .= $this->l(' - Login : ') . Configuration::get('steavisgarantis_accountMail');
                $reviewManagement["desc"] .= $this->l(' - Password : ') . Configuration::get('steavisgarantis_password');
            }

            array_unshift($installedForm['form']['input'], $reviewManagement);
        }

        //Formulaire d'inscription
        $firstInstall = array();
        $firstInstall['form'] = array(
            'legend' => array('title'=>$this->l('New user ?')),
            'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Shop name'),
                        'name' => 'api_siteName',
                        'size' => 30,
                        'desc' => $this->l('Will be on your attestation page'),
                        'required' => true
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Address'),
                        'name' => 'steavisgarantis_accountAddress',
                        'size' => 30,
                        'desc' => $this->l('Will be on your attestation page'),
                        'required' => false
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Address 2'),
                        'name' => 'steavisgarantis_accountAddress2',
                        'size' => 30,
                        'desc' => $this->l('Will be on your attestation page'),
                        'required' => false
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Postal code'),
                        'name' => 'steavisgarantis_accountCP',
                        'size' => 30,
                        'desc' => $this->l('Will be on your attestation page'),
                        'required' => false
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('City'),
                        'name' => 'steavisgarantis_accountCity',
                        'size' => 30,
                        'desc' => $this->l('Will be on your attestation page'),
                        'required' => false
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Your email'),
                        'name' => 'steavisgarantis_accountMail',
                        'size' => 30,
                        'desc' => $this->l('Will be used as Guaranteed Reviews Company account login'),
                        'required' => true
                    ),
                    array(
                      'type' => 'select',
                      'label' => $this->l('Lang:'),
                      'name' => 'steavisgarantis_certificate_lang',
                      'required' => true,
                      'options' => array(
                        'query' => array(
                            array(
                                'certificate_lang_id' => "fr",
                                'lang' => $this->l('French')
                            ),
                            array(
                                'certificate_lang_id' => "en",
                                'lang' => $this->l('English')
                            ),
                            array(
                                'certificate_lang_id' => "de",
                                'lang' => $this->l('German')
                            ),
                            array(
                                'certificate_lang_id' => "it",
                                'lang' => $this->l('Italian')
                            ),
                            array(
                                'certificate_lang_id' => "es",
                                'lang' => $this->l('Spanish')
                            ),
                            array(
                                'certificate_lang_id' => "nl",
                                'lang' => $this->l('Dutch')
                            ),
                            array(
                                'certificate_lang_id' => "pl",
                                'lang' => $this->l('Polish')
                            ),
                            array(
                                'certificate_lang_id' => "pt",
                                'lang' => $this->l('Portuguese')
                            )
                        ),
                        'id' => 'certificate_lang_id',
                        'name' => 'lang'
                      )
                    ),
                    array(
                      'type'    => 'checkbox',
                      'label'   => '',
                      'name'    => 'cgv',
                      'values'  => array(
                        'query' => array(
                            array(
                                'id_option' => 1,
                                'name' => $this->l('I accept Guaranteed Reviews Company\'s Terms and conditions : https://www.guaranteed-reviews-company.com/terms/'),
                                'class' => 'cgv_link',
                            ),
                        ),
                        'id'    => 'id_option',
                        'name'  => 'name'
                      ),
                    ),
            ),
            'submit' => array(
                'title' => $this->l('Create account'),
                'name' => "createCertificate",
                'class' => 'button btn btn-default'
            )
        );

        //Déjà inscrit ?
        $alreadyInstalled = array();
        $alreadyInstalled['form'] = array(
            'legend' => array('title'=>$this->l('Already registered ?')),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Api Key'),
                    'name' => 'steavisgarantis_apiKey',
                    'size' => 30,
                    'desc' => $this->l('Find your Api Key on the PrestaShop page of Guaranteed Reviews Company\'s website'),
                    'required' => true
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => "alreadyUser",
                'class' => 'button btn btn-default'
            )
        );

        //Gestion des formulaires à afficher
        $fields_form = array();

        //Si on a aucune clé d'api, on affiche "Première installation", et "Déjà inscrit?"
        $atLeastOneApiKey = 0;
        $languages = Language::getLanguages(true, Context::getContext()->shop->id);

        foreach ($languages as $language) {
            if (Configuration::get('steavisgarantis_apiKey_'.$language["id_lang"])) {
                $atLeastOneApiKey = 1;
            }
        }

        if (!$atLeastOneApiKey) {
            array_unshift($fields_form, $alreadyInstalled);
            array_unshift($fields_form, $firstInstall);
        }
        else { //Sinon on affiche le formulaire classique
            array_unshift($fields_form, $installedForm);
        }

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = false; // false -> remove toolbar
        $helper->toolbar_scroll = true; // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['steavisgarantis_includeStatus[]'] = explode(",", Configuration::get('steavisgarantis_includeStatus'));

        //Load Api Keys for each language
        $languages = Language::getLanguages(true, Context::getContext()->shop->id);
        $firstApiKey = null;

        foreach ($languages as $language) {
            $helper->fields_value['steavisgarantis_apiKey_'.$language["id_lang"]] = Configuration::get('steavisgarantis_apiKey_'.$language["id_lang"]);

            if(!$firstApiKey && !empty($helper->fields_value['steavisgarantis_apiKey_'.$language["id_lang"]])) {
                $firstApiKey = $helper->fields_value['steavisgarantis_apiKey_'.$language["id_lang"]];
            }
        }

        $helper->fields_value['steavisgarantis_afterDays'] = Configuration::get('steavisgarantis_afterDays');
        $helper->fields_value['steavisgarantis_widgetPosition'] = Configuration::get('steavisgarantis_widgetPosition');
        $helper->fields_value['steavisgarantis_summaryDesign'] = Configuration::get('steavisgarantis_summaryDesign');
        $helper->fields_value['steavisgarantis_widgetJavascript'] = Configuration::get('steavisgarantis_widgetJavascript');
        $helper->fields_value['steavisgarantis_catStars'] = Configuration::get('steavisgarantis_catStars');
        $helper->fields_value['steavisgarantis_customCSS'] = Configuration::get('steavisgarantis_customCSS');
		$helper->fields_value['steavisgarantis_starColor'] = Configuration::get('steavisgarantis_starColor');
        $helper->fields_value['steavisgarantis_footerLink'] = Configuration::get('steavisgarantis_footerLink');
        $helper->fields_value['steavisgarantis_rgpd'] = Configuration::get('steavisgarantis_rgpd');
        $helper->fields_value['steavisgarantis_structuredFormat'] = Configuration::get('steavisgarantis_structuredFormat');
        $helper->fields_value['steavisgarantis_showStructured'] = Configuration::get('steavisgarantis_showStructured');
        $helper->fields_value['steavisgarantis_normalBehaviour'] = Configuration::get('steavisgarantis_normalBehaviour');
        $helper->fields_value['steavisgarantis_maxReviewPerPage'] = Configuration::get('steavisgarantis_maxReviewPerPage');
        $helper->fields_value['steavisgarantis_accountMail'] = Configuration::get('PS_SHOP_EMAIL');
        $helper->fields_value['api_siteName'] = Configuration::get('PS_SHOP_NAME');
        $helper->fields_value['steavisgarantis_accountAddress'] = Configuration::get('PS_SHOP_ADDR1');
        $helper->fields_value['steavisgarantis_accountAddress2'] = Configuration::get('PS_SHOP_ADDR2');
        $helper->fields_value['steavisgarantis_certificate_lang'] = "fr";
        $helper->fields_value['steavisgarantis_accountCP'] = Configuration::get('PS_SHOP_CODE');
        $helper->fields_value['steavisgarantis_accountCity'] = Configuration::get('PS_SHOP_CITY');
        $helper->fields_value['steavisgarantis_password'] = Configuration::get('steavisgarantis_password');

        $publicKey = Configuration::get('steavisgarantis_publicKey');

        if(!$publicKey && $firstApiKey) {
            // If we do not have public key, try to retrieve it with private key
            $url = 'https://api.guaranteed-reviews.com/private/v3/stats?api_key=' . urlencode($firstApiKey) . '&scope=site&include_public_key=true';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlerr  = curl_error($ch);

            curl_close($ch);

            if (!$curlerr && $httpcode == 200) {
                $response = json_decode($response);

                if(isset($response->public_key)) {
                    Configuration::updateValue('steavisgarantis_publicKey', $response->public_key);
                    $publicKey = $response->public_key;
                }
            }
        }

        $helper->fields_value['steavisgarantis_publicKey'] = $publicKey;
        $helper->fields_value['steavisgarantis_newWidgets_on'] = Configuration::get('steavisgarantis_newWidgets');
        $helper->fields_value['steavisgarantis_useOldOrdersMethod_on'] = Configuration::get('steavisgarantis_useOldOrdersMethod');
        $helper->fields_value['steavisgarantis_sendPhone_on'] = Configuration::get('steavisgarantis_sendPhone');

        // Mandatory to avoid notice on install form
        $helper->fields_value['steavisgarantis_apiKey'] = Configuration::get('steavisgarantis_apiKey');

        return $helper->generateForm($fields_form);
    }

    //////////////////////////////////////////////////////////////////////////////////
    //                                                                              //
    //                           COMMON FUNCTIONS                                   //
    //                                                                              //
    //////////////////////////////////////////////////////////////////////////////////

    //Permet d'ajouter les réponses personnalisées au tableau d'avis produits
    public static function addCustomAnswers($reviews)
    {
        //Récupération des potentielles custom anwers
        $idsStr = "";
        foreach ($reviews as $review) {
            $idsStr .= $review['id_product_avisg'] . ", ";
        }
        $idsStr = Tools::substr($idsStr, 0, -2);

        $sql = "SELECT * FROM "._DB_PREFIX_."steavisgarantis_custom_answers WHERE id_product_avisg IN (". $idsStr .")";
        $customAnswers = Db::getInstance()->ExecuteS($sql);

        //On parcour la liste des avis qu'on doit exporter
        foreach ($reviews as $key=>$result) {
            $answersArray = array();
            //Et pour chaque custom answer
            if ($customAnswers) {
                foreach ($customAnswers as $key2=>$customAnswer) {
                    //Si elle correspond à l'idSAG du review
                    if ($result["id_product_avisg"] == $customAnswer["id_product_avisg"]) {
                        //On l'ajoute dans un tableau
                        $answersArray[] = $customAnswer;

                        //On le retire des résultats
                        unset($customAnswers[$key2]);
                    }
                }
            }
            $reviews[$key]["customAnswers"] = $answersArray;
        }
        return $reviews;
    }

    // Récupérer l'URL du certificat en fonction de la langue
	public static function getCertificateUrlByLang($lang)
    {
        $url = self::getDomainUrlFromLang($lang);
        
        $ch = curl_init();
		$lang_id = Context::getContext()->language->id;

		$sql = "SELECT `value` FROM `"._DB_PREFIX_."configuration` WHERE `name` = 'steavisgarantis_apiKey_".$lang_id."'";
		$apiKey = Db::getInstance()->getValue($sql);

        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url . "wp-content/plugins/ag-core/api/getInfos.php?method=certificateUrl&apiKey=" . $apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = str_replace("\xEF\xBB\xBF", '', curl_exec($ch));
        curl_close($ch);

        $urlCertificate = $file_contents;
		$tab = explode('/', $urlCertificate);
		$new_tab = array_filter($tab);
		$domain = end($new_tab);

        // Ajout du slash à la fin pour éviter un code http 301 redirection
		$urlFinal = $url . $domain . '/';

        return $urlFinal;
    }
    
    // Permet de savoir à quel domaine on doit s'adresser en fonction d'une langue : en, fr...
    public static function getDomainUrlFromLang($lang)
    {
        switch ($lang) {
            case "fr": $url = "https://www.societe-des-avis-garantis.fr/";break;
            case "en": $url = "https://www.guaranteed-reviews.com/";break;
            case "de": $url = "https://www.g-g-b.de/";break;
            case "es": $url = "https://www.sociedad-de-opiniones-contrastadas.es/";break;
            case "it": $url = "https://www.societa-recensioni-garantite.it/";break;
            case "nl": $url = "https://www.g-b-n.nl/";break;
            case "pl": $url = "https://www.gwarantowane-opinie.pl/";break;
            case "pt": $url = "https://www.sdag.pt/";break;
            default: $url = "https://www.societe-des-avis-garantis.fr/";break;
        }
        return $url;
    }

    public static function getShopId($lang_id)
    {
        $apiKey=Configuration::get('steavisgarantis_apiKey_' . $lang_id);
        return Tools::substr($apiKey, 0, strpos($apiKey, "/"));
    }

    //Fonction permettant de déduire le domaine en fonction de la clé d'Api
    public static function getDomainUrl($apiKey)
    {
        $nudeApiKey = Tools::substr($apiKey, strpos($apiKey, "/") +1);
        $lang = Tools::substr($nudeApiKey, 0, strpos($nudeApiKey, "/"));
        return self::getDomainUrlFromLang($lang);
    }

    //Fonction permettant de déduire le domaine en fonction de la clé d'Api
    public static function getImg($lang_id, $name, $format = "png")
    {
        $lang = self::getLangFromApiKey(Configuration::get('steavisgarantis_apiKey_'. $lang_id));
        return $name . $lang . "." . $format;
    }

    //public static function to get lang from apiKey
    public static function getLangFromApiKey($apiKey)
    {
        $nudeApiKey = Tools::substr($apiKey, strpos($apiKey, "/") +1);
        $lang = Tools::substr($nudeApiKey, 0, strpos($nudeApiKey, "/"));
        $langList = array("en", "fr", "de", "it", "es", "be", "nl", "pl", "pt");

        // Si on ne trouve pas la langue dans la clé c'est qu'on est sur une ancienne typo de clé
        if (!in_array($lang, $langList)) {
            $lang = "fr";
        }

        return $lang;
    }

    //Format date depending on lang from apiKey
    public static function formatDate($date, $lang_id)
    {
        $dateStr = "";
        $lang = self::getLangFromApiKey(Configuration::get("steavisgarantis_apiKey_".$lang_id));

        switch ($lang) {
            case "fr": $dateStr = date("d/m/Y", $date) . " à " . date("H:i", $date); break;
            case "de": $dateStr = date("d/m/Y", $date) . " um " . date("H:i", $date) . " Uhr"; break;
            case "en": $dateStr = date("M d\, Y", $date) . " at " . date("h:i a", $date); break;
            case "es": $dateStr = date("d/m/Y", $date) . " a las " . date("h:i a", $date); break;
            case "it": $dateStr = date("d/m/Y", $date) . " alle ore " . date("H:i", $date); break;
            case "nl": $dateStr = date("d/m/Y", $date) . " om " . date("H:i", $date); break;
            case "pl": $dateStr = date("d/m/Y", $date) . " o " . date("H:i", $date); break;
            case "pt": $dateStr = date("d/m/Y", $date) . " às " . date("H:i", $date); break;
            default: break;
        }

        return $dateStr;
    }

    //Format date depending on lang from apiKey
    public static function formatOrderDate($date, $lang_id)
    {
        $dateStr = "";
        $lang = self::getLangFromApiKey(Configuration::get("steavisgarantis_apiKey_".$lang_id));

        switch ($lang) {
            case "fr": $dateStr = date("d/m/Y", strtotime($date)); break;
            case "es": $dateStr = date("d/m/Y", strtotime($date)); break;
            case "it": $dateStr = date("d/m/Y", strtotime($date)); break;
            case "de": $dateStr = date("d/m/Y", strtotime($date)); break;
            case "en": $dateStr = date("M d\, Y", strtotime($date)); break;
            case "nl": $dateStr = date("d/m/Y", strtotime($date)); break;
            case "pl": $dateStr = date("d/m/Y", strtotime($date)); break;
            case "pt": $dateStr = date("d/m/Y", strtotime($date)); break;
            default: break;
        }

        return $dateStr;
    }

    public static function getApiKeyFromLang($lang)
    {
        $languages = Language::getLanguages(true, Context::getContext()->shop->id);
        //Pour chaque langue active, on recupère la potentielle clé d'api
        foreach ($languages as $language) {
            //Si on a une clé d'api
            if ($apiKey = Configuration::get('steavisgarantis_apiKey_'.$language["id_lang"])) {
                //On en déduit la langue (en, fr..)
                $nudeApiKey = Tools::substr($apiKey, strpos($apiKey, "/") +1);
                $apiLang = Tools::substr($nudeApiKey, 0, strpos($nudeApiKey, "/"));
                //Si la langue de la clé d'api correspond à la langue demandée en paramètre
                if ($lang == $apiLang) {
                    return $apiKey;
                }
            }
        }

        //Si on arrive là c'est qu'on a trouvé aucune clé d'api
        foreach ($languages as $language) {
            if ($apiKey = Configuration::get('steavisgarantis_apiKey_'.$language["id_lang"])) {
                //On vérifie que la clé d'api ne renseigne pas déjà une langue
                $nudeApiKey = Tools::substr($apiKey, strpos($apiKey, "/") +1);
                $apiLang = Tools::substr($nudeApiKey, 0, strpos($nudeApiKey, "/"));
                $langList = array("en", "fr", "de", "it", "es", "be", "nl", "pl", "pt");
                if (!in_array($apiLang, $langList)) {
                    //Très forte probabilité qu'on ai un ancien format de clé d'api
                    return $apiKey;
                }
            }
        }
    }

    public static function getLangsId($lang)
    {
        $langIds = array();
        $languages = Language::getLanguages(true, Context::getContext()->shop->id);
        //Pour chaque langue active, on recupère la potentielle clé d'api
        foreach ($languages as $language) {
            //Si on a une clé d'api
            if ($apiKey = Configuration::get('steavisgarantis_apiKey_'.$language["id_lang"])) {
                //On en déduit la langue (en, fr..)
                $nudeApiKey = Tools::substr($apiKey, strpos($apiKey, "/") +1);
                $apiLang = Tools::substr($nudeApiKey, 0, strpos($nudeApiKey, "/"));
                //Si la langue de la clé d'api correspond à la langue demandée en paramètre
                if ($lang == $apiLang) {
                    $langIds[] = $language["id_lang"];
                }
            }
        }

        //Si on a aucune correspondance ça veut dire qu'on a un ancien format de clé d'api ne contenant pas la langue
        if (!count($langIds)) {
            //On prend en compte toutes les langues actives qui ont une clé d'api associée
            foreach ($languages as $language) {
                if ($apiKey = Configuration::get('steavisgarantis_apiKey_'.$language["id_lang"])) {
                    //On vérifie que la clé d'api ne renseigne pas déjà une langue
                    $nudeApiKey = Tools::substr($apiKey, strpos($apiKey, "/") +1);
                    $apiLang = Tools::substr($nudeApiKey, 0, strpos($nudeApiKey, "/"));
                    $langList = array("en", "fr", "de", "it", "es", "be", "nl", "pl", "pt");
                    if (!in_array($apiLang, $langList)) {
                        //Très forte probabilité qu'on ai un ancien format de clé d'api
                        $langIds[] = $language["id_lang"];
                    }
                }
            }
        }
        return $langIds;
    }

    //Fonction permettant de regénérer l'url de certificat
    public static function setCertificateUrlFromAPI($lang_id = false)
    {
        $urlCertificate = "";

        // Si on ne sait pas pour quelle langue on doit mettre à jour l'url du certificat
        if (!$lang_id) {
            $languages = Language::getLanguages(true, Context::getContext()->shop->id);
            foreach ($languages as $language) {
                //On met à jour de façon récursive
                $urlCertificate = self::setCertificateUrlFromAPI($language["id_lang"]);
            }
        }
        // Sinon on sait et on met à jour
        else {
            $apiKey = Configuration::get('steavisgarantis_apiKey_'. $lang_id);
            $url_ag=self::getDomainUrl($apiKey);
            $ch = curl_init();
            $timeout = 5; // set to zero for no timeout
            curl_setopt($ch, CURLOPT_URL, $url_ag."wp-content/plugins/ag-core/api/getInfos.php?method=certificateUrl&apiKey=".$apiKey);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = str_replace("\xEF\xBB\xBF", '', curl_exec($ch));
            curl_close($ch);
            $urlCertificate = $file_contents;
            Configuration::updateValue('steavisgarantis_certificateUrl_'. $lang_id, $urlCertificate);
        }

        return $urlCertificate;
    }

    //Fonction retournant l'Url de la page du certificat (page qui montre les avis)
    public static function getCertificateUrl($lang_id)
    {
        //On va chercher en base l'url du certificat
        if (Configuration::get('steavisgarantis_certificateUrl_' . $lang_id)) {
            return Configuration::get('steavisgarantis_certificateUrl_' . $lang_id);
        }
        //Si on ne la trouve pas en base, on demande à l'Api et on l'enregistre
        else {
            return self::setCertificateUrlFromAPI($lang_id);
        }
    }

    // Fonction permettant de regénérer les indicateurs d'avis du site
    public static function setSiteIndicators($lang_id = false)
    {
        $shopStats = null;

        if (!$lang_id) {
            $languages = Language::getLanguages(true, Context::getContext()->shop->id);
            foreach ($languages as $language) {
                //On met à jour de façon récursive
                $shopStats = self::setSiteIndicators($language["id_lang"]);
            }
        }
        //Sinon on sait et on met à jour
        else {
            $apiKey = Configuration::get('steavisgarantis_apiKey_'. $lang_id);
            $url_ag=self::getDomainUrl($apiKey);
            $ch = curl_init();
            $timeout = 5; // set to zero for no timeout
            curl_setopt($ch, CURLOPT_URL, $url_ag."wp-content/plugins/ag-core/api/getInfos.php?method=shopStats&apiKey=".$apiKey);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = str_replace("\xEF\xBB\xBF", '', curl_exec($ch));
            curl_close($ch);
            $shopStats = $file_contents;
            Configuration::updateValue('steavisgarantis_shopStats_'. $lang_id, $shopStats);

            //Save date
            $savedDate = date ("Y-m-d H:i:s");
            Configuration::updateValue('steavisgarantis_shopStatsDate_'. $lang_id, $savedDate);

            //Reload configuration cache
            $configurationFile = dirname(__FILE__) . '/../../classes/cache/configuration.cache';
            if (file_exists($configurationFile)) unlink($configurationFile);
        }

        return $shopStats;
    }

    //Fonction retournant les indicateurs (nb avis et note) du site
    public static function getSiteIndicators($lang_id)
    {
        $shopStatsDate = Configuration::get('steavisgarantis_shopStatsDate_'. $lang_id);
        $currentDate = date("Y-m-d H:i:s");
        $secs = strtotime($currentDate) - strtotime($shopStatsDate);// == <seconds between the two times>

        //Refresh indicators every xxx seconds
        if ($secs>600) {
            return self::setSiteIndicators($lang_id);
        }

        //On va chercher en base
        if (Configuration::get('steavisgarantis_shopStats_' . $lang_id)) {
            return Configuration::get('steavisgarantis_shopStats_' . $lang_id);
        }
        //Sinon on regénère via l'api
        else {
            return self::setSiteIndicators($lang_id);
        }
    }

    //Fonction retournant l'Url de la page du certificat (page qui montre les avis)
    public static function getReviewsCount($lang_id)
    {
        $siteIndicators = json_decode(self::getSiteIndicators($lang_id), true);
        return $siteIndicators["reviewsCount"];
    }

    //Fonction retournant l'Url de la page du certificat (page qui montre les avis)
    public static function getSiteRate($lang_id)
    {
        $siteIndicators = json_decode(self::getSiteIndicators($lang_id), true);
        return $siteIndicators["globalRate"];
    }

    public static function createCertificate($domain, $siteName = "", $address1 = "", $address2 = "", $CP = "", $city = "", $email = "", $logo = "")
    {
        //On vérifie qu'on a bien un mail
        if (!$email) {
            return array("apiKey" => false);
        }

        //On urlencode
        $siteName = urlencode($siteName);
        $address1 = urlencode($address1);
        $address2 = urlencode($address2);
        $CP       = urlencode($CP);
        $city = urlencode($city);
        $email = urlencode($email);
        if (!$logo) {
            $logo = "logo.jpg";
        }
        $logo = urlencode($logo);
        $raison_sociale = $siteName;

        //On récupère l'url racine
        $url = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__;

        $logoUrl = $url . "/img/" . ltrim($logo, '/'); //On enleve le premier caractère de $logo si c'est un slash
        $params = "cms=prestashop&email=$email&url=$url&address1=$address1&address2=$address2&CP=$CP&city=$city&logo_url=$logoUrl&raison_sociale=$raison_sociale";
        $apiUrl = $domain . SAGAPIENDPOINT . "createCertificate.php?" . $params;
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $datas = curl_exec($ch);
        $datas = json_decode($datas, true);

        return $datas;
    }

    public static function sendUpdatePath()
    {
        $apiKey = urlencode(Configuration::get('steavisgarantis_apiKey'));
        $domain = self::getDomainUrlFromLang("fr"); //Car il n'y a que dans cette langue que la version nommée "SAG" du module existait
        $params = "apiKey=". $apiKey;
        $url = $domain . SAGAPIENDPOINT . "updatePath.php?" . $params;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $datas = curl_exec($ch);
        return $datas;
    }

    //Permet de migrer toutes les anciennes versions sur la nouvelle structure de table
    public static function updateDataTable()
    {
        //On insert les nouvelles colonnes
        $tables = array("steavisgarantis_reviews", "steavisgarantis_average_rating");
        foreach ($tables as $table) {
            $table = pSQL($table);
            //On supprime la table qui servait à sauvegarder
            $sql = "DROP TABLE if exists ". _DB_PREFIX_ . $table ."_save";
            Db::getInstance()->execute($sql);

            //On sauvegarde l'ancienne table en changeant son nom
            $sql = "RENAME TABLE ". _DB_PREFIX_ . $table ." TO ". _DB_PREFIX_ . $table ."_save";
            Db::getInstance()->execute($sql);
        }

        //On installe la nouvelle
        STEAVISGARANTIS::installDatabase();

        //On met les anciennes données dans la nouvelle table steavisgarantis_reviews
        $sql = "INSERT INTO ". _DB_PREFIX_ . "steavisgarantis_reviews (id_product_avisg, product_id, ag_reviewer_name, rate, review, date_time, answer_text, answer_date_time)
        SELECT id_product_avisg, product_id, ag_reviewer_name, rate, review, date_time, answer_text, answer_date_time FROM ". _DB_PREFIX_ . "steavisgarantis_reviews_save";
        Db::getInstance()->execute($sql);

        //On met les anciennes données dans la nouvelle table steavisgarantis_average_rating
        $sql = "INSERT INTO ". _DB_PREFIX_ . "steavisgarantis_average_rating (id_product_avisg, product_id, rate, percent1, percent2, percent3, percent4, percent5, nb1, nb2, nb3, nb4, nb5, date_time_update, reviews_nb)
        SELECT id_product_avisg, product_id, rate, percent1, percent2, percent3, percent4, percent5, nb1, nb2, nb3, nb4, nb5, date_time_update, reviews_nb FROM ". _DB_PREFIX_ . "steavisgarantis_average_rating_save";
        Db::getInstance()->execute($sql);

        foreach ($tables as $table) {
            $table = pSQL($table);
            //On configure la langue par défaut de la boutique sur les avis produits existant
            $default_lang = (Configuration::get('PS_LANG_DEFAULT') ? Configuration::get('PS_LANG_DEFAULT') : 1);
            $sql = "UPDATE ". _DB_PREFIX_ . $table ." SET id_lang = '" . (int)$default_lang . "'";
            Db::getInstance()->execute($sql);

            //On supprime la table qui servait à sauvegarder
            $sql = "DROP TABLE ". _DB_PREFIX_ . $table ."_save";
            Db::getInstance()->execute($sql);
        }
    }

    //////////////////////////////////////////////////////////////////////////////////
    //                                                                              //
    //                              API FUNCTIONS                                   //
    //                                                                              //
    //////////////////////////////////////////////////////////////////////////////////

    //
    //Function to clean datas
    //
    public static function removeBOM($data)
    {
        if (0 === strpos(bin2hex($data), 'efbbbf')) {
            return Tools::substr($data, 3);
        }
    }

    //
    //Déclaration de la fonction de récupération des avis
    //
    public static function importProductReviews($url_ag, $apiKey, $productID, $idSAG, $from, $minDate, $maxDate, $maxResults, $token, $update, $stepLimit, $stepOffset)
    {
        // Préparation des paramètres à passer en variable
        $productID =  $productID ? '&productID='.$productID : '';                         //Filtre sur l'ID produit
        $idSAG =  $idSAG ? '&idSAG='.$idSAG : '';     //Filtre sur l'ID de l'avis
        $from =  $from ? '&from='.$from : '';                                             //Si from est à 1, $idSAG deviens le point de départ des avis à récupérer
        $minDate =  $minDate ? '&minDate='.$minDate : '';                                 //Filtre sur la date de l'avis
        $maxDate =  $maxDate ? '&maxDate='.$maxDate : '';                                 //Filtre sur la date de l'avis
        $maxResults = $maxResults ? '&maxR='.$maxResults : '';                             //Valeur de la clause sql LIMIT
        
        $stepLimit = $stepLimit ? '&stepLimit='.$stepLimit : '';
        $stepOffset = $stepOffset ? '&stepOffset='.$stepOffset : '';

        $token = $token ? '&token='.$token : '';                                           //Valeur de la clause sql LIMIT
        $apiUrl = $url_ag."wp-content/plugins/ag-core/api/reviews.php?translation=1&apiPost=1" . $productID . $idSAG . $from . $minDate . $maxDate . $maxResults . $stepLimit . $stepOffset . $token;

        //
        //REMOTE AUTHENTIFICATION
        //If token is Wrong, nothing will be returned and code following cURL request won't be executed
        //
        $ch = curl_init();
        //echo $apiUrl;
        $timeout = 5; // set to zero for no timeout
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "apiKey= ".urlencode($apiKey));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);

        //
        // Exploitation des données récupérées
        //
        $file_contentsWithoutBom = self::removeBOM($file_contents);

        // si on peut enlever le bom on l'enlève
        if ($file_contentsWithoutBom) {
            $file_contents = $file_contentsWithoutBom;
        }
        
        $file_contents = json_decode($file_contents, true); // Décodage du contenu JSON récupéré

        // Si on a une erreur de decodage JSON
        if (json_last_error()) {
            var_dump(json_last_error());
        }

        // Pour chaque avis
        foreach ($file_contents as $val) {
            // On détermine la ou les langues concernées par la langue de l'avis
            $langsId = STEAVISGARANTIS::getLangsId($val["lang"]);
            foreach ($langsId as $langId) {

                $langId = (int)$langId;
                $updateAverage = 0; // Initialisation variable déterminant si on doit updater la moyenne des avis

                // On va vérifier qu'il n'existe pas dans la base de données (si traduit, on spécifie la source lang)
                $sql = "SELECT * FROM "._DB_PREFIX_."steavisgarantis_reviews 
                WHERE id_product_avisg = ". (int)$val["idSAG"] ." 
                AND id_lang = '$langId' 
                AND translated = " . (int)$val["translated"] . 
                ($val["translated"] ? " AND source_lang = '" . pSQL($val["sourceLang"]) . "' " : "");

                // Si l'avis a le statut 0 (en attente) ou 2 (supprimé)
                if ($val["review_status"] == 0 or $val["review_status"] == 2) {
                    // Et qu'il existe, il faut le supprimer
                    if ($reviews = Db::getInstance()->ExecuteS($sql)) {
                        // Il peut y avoir plusieurs avis à supprimer s'il a été traduit
                        foreach ($reviews as $row) {
                            // Supprimer l'avis
                            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                                $table = _DB_PREFIX_ . 'steavisgarantis_reviews';
                            } else {
                                $table = 'steavisgarantis_reviews';
                            }
                            // On delete la ligne dans la table
                            Db::getInstance()->delete($table, 'id = '. (int)$row["id"], 1);
                            $updateAverage = 1; // On passe updateAverage à true pour updater les moyennes des avis
                        }
                    }
                }
                else {
                    // Sinon l'avis a le statut validé (1)
                    if ($row = Db::getInstance()->getRow($sql)) {
                        // Si l'avis est déjà dans la base de données et que $update est à true, on update l'avis
                        if ($update) {
                            $lastName = (isset($val['lastname'][0]) ? " " . $val['lastname'][0] . "." : "");
                            $reviewerName = Tools::ucfirst($val["reviewer_name"]) . $lastName;
                            $datas = array(
                                'id_product_avisg' => (int)$val["idSAG"],
                                'product_id' => (int)$val["idProduct"],
                                'rate' => (int)$val["review_rating"],
                                'review' => pSQL($val["review_text"]),
                                'ag_reviewer_name' => pSQL($reviewerName),
                                'date_time'=> (int)strtotime($val["date_time"]),
                                'answer_text' => pSQL($val["answer_text"]),
                                'answer_date_time' => pSQL($val["answer_date_time"]),
                                'order_date' => pSQL($val["order_date"]),
                                'id_lang' => (int)$langId,
                            );

                            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                                Db::getInstance()->autoExecute(_DB_PREFIX_.'steavisgarantis_reviews', $datas, 'UPDATE', 'id = '.(int)$row["id"]);
                            } else {
                                Db::getInstance()->update('steavisgarantis_reviews', $datas, 'id = '.(int)$row["id"]);
                            }

                            $updateAverage = 1; // On passe updateAverage à true pour updater les moyennes des avis
                        }
                    }
                    else { // Sinon l'enregistrement n'existe pas et on l'insert

                        $lastName = (isset($val['lastname'][0]) ? " " . $val['lastname'][0] . "." : "");
                        $reviewerName = Tools::ucfirst($val["reviewer_name"]) . $lastName;

                        $datas = array(
                            'id_product_avisg' => (int)$val["idSAG"],
                            'product_id' => (int)$val["idProduct"],
                            'rate' => (int)$val["review_rating"],
                            'review' => pSQL($val["review_text"]),
                            'ag_reviewer_name' => pSQL($reviewerName),
                            'date_time'=> (int)strtotime($val["date_time"]),
                            'answer_text' => pSQL($val["answer_text"]),
                            'answer_date_time' => pSQL($val["answer_date_time"]),
                            'order_date' => pSQL($val["order_date"]),
                            'id_lang' => (int)$langId,
                            'translated' => (int)$val["translated"],
                            'source_lang' => ((isset($val["sourceLang"]) and $val["translated"]) ? pSQL($val["sourceLang"]) : ""), // Ne surtout pas spécifier la source LANG l'avis n'est pas traduit
                        );

                        // Presta < 1.5
                        if (version_compare(_PS_VERSION_, '1.5', '<')) {
                            Db::getInstance()->autoExecute(_DB_PREFIX_.'steavisgarantis_reviews', $datas, 'INSERT');
                        } else {
                            Db::getInstance()->insert('steavisgarantis_reviews', $datas);
                        }

                        $updateAverage = 1; // On passe updateAverage à true pour updater les moyennes des avis
                    }
                }

                // Custom answers insertion
                if (isset($val['customAnswers'])) {
                    foreach ($val['customAnswers'] as $customerAnswer) {
                        $answersDatas = array(
                            'id_product_avisg' => pSQL($val["idSAG"]),
                            'id_question' => (int)$customerAnswer["id_question"],
                            'question_label' => pSQL($customerAnswer["question_label"]),
                            'answer' => pSQL($customerAnswer["answer"]),
                            'unit' => pSQL($customerAnswer["unit"])
                        );

                        // Test if custom answer exists
                        $sql = "SELECT id FROM "._DB_PREFIX_."steavisgarantis_custom_answers WHERE id_question= ". (int)$customerAnswer["id_question"] ." and id_product_avisg='".pSQL($val["idSAG"]) . "'";
                        $row = Db::getInstance()->getRow($sql, false);

                        // If custom answer exists, update
                        if ($row) {
                            if ($update) {
                                // Update custom answer if exists
                                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                                    $update = Db::getInstance()->autoExecute(_DB_PREFIX_.'steavisgarantis_custom_answers', $answersDatas, 'UPDATE', "id= ".(int)$row["id"]);
                                } else {
                                    // On update une ligne dans la table
                                    $update = Db::getInstance()->update('steavisgarantis_custom_answers', $answersDatas, "id= ".(int)$row["id"], 0, false, false);
                                }
                            }
                        }
                        else { // Sinon on l'ajoute
                            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                                Db::getInstance()->autoExecute(_DB_PREFIX_.'steavisgarantis_custom_answers', $answersDatas, 'INSERT');
                            } else {
                                Db::getInstance()->insert('steavisgarantis_custom_answers', $answersDatas, false, false);
                            }
                        }
                    }
                }

                // Si on a fait une insertion, update, ou suppression, il faut updater la table des moyennes d'avis et la répartition des notes + pourcent
                if ($updateAverage) {

                    $sql = "SELECT 
                        COUNT(*) as total_reviews,
                        SUM(rate) as sum_rates,
                        SUM(CASE WHEN rate = 1 THEN 1 ELSE 0 END) as nb1,
                        SUM(CASE WHEN rate = 2 THEN 1 ELSE 0 END) as nb2,
                        SUM(CASE WHEN rate = 3 THEN 1 ELSE 0 END) as nb3,
                        SUM(CASE WHEN rate = 4 THEN 1 ELSE 0 END) as nb4,
                        SUM(CASE WHEN rate = 5 THEN 1 ELSE 0 END) as nb5
                    FROM ". _DB_PREFIX_ ."steavisgarantis_reviews 
                    WHERE product_id = '". (int)$val["idProduct"] ."' AND id_lang = {$langId}";
                    
                    $avg = Db::getInstance()->getRow($sql);

                    // On calcule la note moyenne
                    if ($avg["total_reviews"] > 0) {
                        $rate = round($avg["sum_rates"] / $avg["total_reviews"], 2);
                    } else {
                        $rate = 0;
                    }

                    // On calcule la répartition des notes en pourcentages
                    $percent1 = ($avg["total_reviews"] ? round($avg["nb1"] / $avg["total_reviews"], 2) * 100 : 0);
                    $percent2 = ($avg["total_reviews"] ? round($avg["nb2"] / $avg["total_reviews"], 2) * 100 : 0);
                    $percent3 = ($avg["total_reviews"] ? round($avg["nb3"] / $avg["total_reviews"], 2) * 100 : 0);
                    $percent4 = ($avg["total_reviews"] ? round($avg["nb4"] / $avg["total_reviews"], 2) * 100 : 0);
                    $percent5 = ($avg["total_reviews"] ? round($avg["nb5"] / $avg["total_reviews"], 2) * 100 : 0);

                    // On regarde si on a déjà une ligne de moyenne pour ce produit
                    $sql = "SELECT * FROM ". _DB_PREFIX_ ."steavisgarantis_average_rating WHERE product_id = '". (int)$val["idProduct"] ."' AND id_lang = {$langId}";

                    // Si on a des résultats, on update la ligne
                    if ($row = Db::getInstance()->getRow($sql)) {
                        $datas = array(
                            'id_product_avisg'  => (int)$val["idSAG"],
                            'product_id'        => (int)$val["idProduct"],
                            'rate'              => $rate,
                            'reviews_nb'        => (int)$avg["total_reviews"],
                            'percent1'          => $percent1,
                            'percent2'          => $percent2,
                            'percent3'          => $percent3,
                            'percent4'          => $percent4,
                            'percent5'          => $percent5,
                            'nb1'               => (int)$avg["nb1"],
                            'nb2'               => (int)$avg["nb2"],
                            'nb3'               => (int)$avg["nb3"],
                            'nb4'               => (int)$avg["nb4"],
                            'nb5'               => (int)$avg["nb5"],
                            'date_time_update'  => (int)strtotime($val["date_time"]),
                            'id_lang'           => (int)$langId
                        );

                        if (version_compare(_PS_VERSION_, '1.5', '<')) {
                            Db::getInstance()->autoExecute(_DB_PREFIX_.'steavisgarantis_average_rating', $datas, 'UPDATE', 'id='.(int)$row["id"]);
                        } else {
                            // On update une ligne dans la table
                            Db::getInstance()->update('steavisgarantis_average_rating', $datas, 'id='.(int)$row["id"]);
                        }
                    }
                    else {
                        // Sinon on insert une nouvelle ligne
                        $nb = 1;
                        $datas = array(
                            'id_product_avisg'  => (int)$val["idSAG"],
                            'product_id'        => (int)$val["idProduct"],
                            'rate'              => $rate,
                            'reviews_nb'        => (int)$avg["total_reviews"],
                            'percent1'          => $percent1,
                            'percent2'          => $percent2,
                            'percent3'          => $percent3,
                            'percent4'          => $percent4,
                            'percent5'          => $percent5,
                            'nb1'               => (int)$avg["nb1"],
                            'nb2'               => (int)$avg["nb2"],
                            'nb3'               => (int)$avg["nb3"],
                            'nb4'               => (int)$avg["nb4"],
                            'nb5'               => (int)$avg["nb5"],
                            'date_time_update'  => (int)strtotime($val["date_time"]),
                            'id_lang'           => (int)$langId
                        );

                        if (version_compare(_PS_VERSION_, '1.5', '<')) {
                            Db::getInstance()->autoExecute(_DB_PREFIX_.'steavisgarantis_average_rating', $datas, 'INSERT');
                        } else {
                            Db::getInstance()->insert('steavisgarantis_average_rating', $datas);
                        }
                    }
                }
            }
        }
    }

    //
    // Verify a token
    //
    public static function tokenCheck($token, $lang)
    {
        $domainUrl = STEAVISGARANTIS::getDomainUrlFromLang($lang);
        $apiKey = urlencode(STEAVISGARANTIS::getApiKeyFromLang($lang));
        $url = $domainUrl . SAGAPIENDPOINT . "checkToken.php?token=" . $token . "&apiKey=" . $apiKey;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        return curl_exec($ch);
    }

    //
    // Securely connect and post data to API
    //
    public static function postData($data, $dest, $token, $lang)
    {
        $domainUrl = STEAVISGARANTIS::getDomainUrlFromLang($lang);
        $apiKey = urlencode(STEAVISGARANTIS::getApiKeyFromLang($lang));
        $url = $domainUrl . SAGAPIENDPOINT . $dest ."?token=$token&apiKey=" . $apiKey;
        $dataString = base64_encode(json_encode($data));    //Remote API only accepts Base64 encoded datas
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("data" => $dataString));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        return curl_exec($ch);
    }

    public static function saveSagCustom($id_customer = null)
    {
        if (Tools::getIsset('steavisgarantis_custom')) {
            $errors = array();
            if (is_null($id_customer)) {
                $id_customer = (int)Tools::getValue('steavisgarantis_customer_id');
            }

            $value = Tools::getValue("steavisgarantis_consent");
            if (!$value) {
                $value = 0;
            }

            $data = array(
                'id_steavisgarantis_customfield' => 1, //ID DU Custom field correspondant au recueil du consentement
                'id_customer' => (int)$id_customer,
                'value' => (int)$value
            );

            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                Db::getInstance()->autoExecute(_DB_PREFIX_ . 'steavisgarantis_customer', $data, 'REPLACE');
            } else {
                // PS > 1.5
                Db::getInstance()->insert('steavisgarantis_customer', $data, false, true, Db::REPLACE);
            }

            return true;
        }
    }

    public static function getSagConsent ($id_customer) {
        $sql = "SELECT value FROM "._DB_PREFIX_."steavisgarantis_customer WHERE id_customer = '".(int)$id_customer."' and id_steavisgarantis_customfield = 1";
        $consent= Db::getInstance()->getValue($sql);
        return ($consent ? 1 : 0);
    }

    public static function getProductUrl($id_product, $id_lang)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $product = new Product((int)$id_product, false, $id_lang);
        }
        else {
            $product = new Product((int)$id_product);
        }

        $link = new Link();
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $product_url = $link->getProductLink($product, null, null, null, $id_lang);
        }
        else {
            $product_url = $link->getProductLink((int)$id_product);
        }

        return $product_url;
    }

    public static function getVersion()
    {
        $module = Module::getInstanceByName('steavisgarantis');
        return $module ? $module->version : null;
    }
}
