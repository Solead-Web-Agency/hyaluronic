<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */

if (!defined('_PS_VERSION_')) { exit; }

require_once(dirname(__FILE__).'/../../amazon.php');
require_once(dirname(__FILE__).'/../../classes/amazon.tools.class.php');

class AdminAmazonOrdersController extends ModuleAdminController
{
    public $module = 'amazon';
    public $name   = 'amazon';
    /** @var Amazon */
    public $amazon = null;
    public $url;
    public $images;
    public $url_without_virtual; // including js/css files for shops with virtual URL

    public $ps17x = false;
    public $ps16x = false;

    public function __construct()
    {
        $this->amazon = new Amazon();

        $this->className = $this->amazon->name;
        $this->display = 'edit';

        $this->id_lang = (int)Context::getContext()->language->id;

        $this->lang = true;
        $this->deleted = false;
        $this->colorOnBackground = false;

        $this->url = $this->amazon->url;
        $this->url_without_virtual = $this->amazon->url_without_virtual;
        $this->images = $this->amazon->images;
        $this->path = $this->amazon->path;

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->ps17x = true;
            $this->ps16x = true;
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->ps16x = true;
        } else {
            $this->ps16x = false;
        }

        $this->context = Context::getContext();
        $this->bootstrap = true;

        parent::__construct();
    }

    public function renderForm()
    {
        require_once dirname(__FILE__) . '/../../classes/amazon.cron_failed_order.class.php';

        if ($this->ps17x) {
            $this->addCSS($this->url_without_virtual.'views/css/admin_controller/general16.css', 'screen');
            $this->addCSS($this->url_without_virtual.'views/css/OrdersAmazon16.css', 'screen');
            $this->addCSS($this->url_without_virtual.'views/css/OrdersAmazon17.css', 'screen');
        } elseif ($this->ps16x) {
            $this->addCSS($this->url_without_virtual.'views/css/admin_controller/general16.css', 'screen');
            $this->addCSS($this->url_without_virtual.'views/css/OrdersAmazon16.css', 'screen');
        } else {
            $this->addCSS($this->url_without_virtual.'views/css/admin_controller/general.css', 'screen');
            $this->addCSS($this->url_without_virtual.'views/css/OrdersAmazon.css', 'screen');
        }

        if (version_compare(_PS_VERSION_, '1.6.1.2', '<')) {
            $this->addJS($this->url_without_virtual . 'views/js/orders.js');
            $this->addJS($this->url_without_virtual . 'views/js/reports.js');
        } else {
            $this->addJS($this->url_without_virtual . 'views/js/orders.js?v=' . $this->amazon->version);
            $this->addJS($this->url_without_virtual . 'views/js/reports.js?v=' . $this->amazon->version);
        }

        $smarty = $this->context->smarty->assign(array(
            'path' => $this->url,
            'images' => $this->images,
            'debug' => (bool)Configuration::get('AMAZON_DEBUG_MODE'),
            'ps16x' => $this->ps16x,
            'report_url' => $this->url.'functions/products_report.php',
            'feed_result_url' => $this->url . 'functions/feeds/orders_result.php',
            'cron_failed_orders' => AmazonCronFailedOrder::getAllCronFailedOrderInstances(),
            'alert_class' => $this->amazon->getNotificationClassNames(),
            'active_import_orders' => (bool)Amazon::getAmazonFeatures()['orders'],
        ));

        return $this->tabHeader() . $this->languageSelector() .
            $smarty->fetch($this->path . 'views/templates/admin/AdminOrdersAmazon.tpl') .
            $this->content . parent::renderForm();
    }

    public function tabHeader()
    {
        $this->context->smarty->assign('images', $this->images);

        $amazonTokens = AmazonConfiguration::get('CRON_TOKEN');

        $tokenOrders = Tools::getAdminToken('AdminOrders'.(int)Tab::getIdFromClassName('AdminOrders').(int)$this->context->employee->id);

        $day = 86400;
        $days = 2;
        $startDate = date('Y-m-d', time() - ($day * $days));
        $currentDate = date('Y-m-d');

        $this->addJqueryUI('ui.datepicker');

        if (version_compare(_PS_VERSION_, '1.5', '>=') && Shop::isFeatureActive() && in_array($this->context->shop->getContext(), array(
                    Shop::CONTEXT_GROUP,
                    Shop::CONTEXT_ALL
                ))
        ) {
            $this->context->smarty->assign('shop_warning', $this->l('You are in multishop environment. To use Amazon module, you must select a target shop.'));
        }
        $amazon_features = Amazon::getAmazonFeatures();

        $amazon_support = new AmazonSupport();
        $documentation = $amazon_support->gethreflink();

        return $this->context->smarty->assign(array(
            'context_key' => AmazonContext::getKey($this->context->shop),
            'debug' => (bool)Configuration::get('AMAZON_DEBUG_MODE'),
            // Aug-23-2018: Remove Carriers/Modules option
            'experimental' => Amazon::ENABLE_EXPERIMENTAL_FEATURES,
            'tokens' => $amazonTokens,
            'token_order' => $tokenOrders,
            'module_path' => $this->path,
            'tpl_path' => $this->path,
            'orders_url' => $this->url . 'functions/orders.php',
            'import_enhancement_url' => $this->url . 'functions/orders/import_selection.php',
            'orders_report_url' => $this->url . 'functions/orders_reports.php',
            'img_loader' => $this->images . 'loading.gif',
            'img_loader_small' => $this->images . 'small-loader.gif',
            'current_date' => $currentDate,
            'fba' => (bool)$amazon_features['fba'],
            'start_date' => $startDate,
            'psIs16' => $this->ps16x,
            'instant_token' => Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0),
            'alert_class' => $this->amazon->getNotificationClassNames(),
            'id_lang' => $this->id_lang,
            'documentation' => $documentation,
            'support' => $amazon_support->gethreflink(AmazonSupport::TUTORIAL_GET_SUPPORT),
            'widget' => $amazon_support->getWidget($this->amazon->name, $this->amazon->displayName, $this->amazon->version),
        ))->fetch($this->path . 'views/templates/admin/items/orders_header.tpl');
    }

    public function languageSelector()
    {
        $master = AmazonConfiguration::get('MASTER');
        $amazon_features = Amazon::getAmazonFeatures();
        $europe = $amazon_features['amazon_europe'] && !empty($master);

        $actives = AmazonConfiguration::get('ACTIVE');
        $regions = AmazonConfiguration::get('REGION');
        $marketPlaceIds = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_LANG_TO_AMZ_MKP_ID);
        $isSeparateEUConfig = (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_AMZ_EU_SEPARATE_SELLER_ACC);

        $this->addCSS($this->url_without_virtual.'/views/css/country_selector.css', 'screen');

        $debugInfo = sprintf('Master: %s', print_r($master, true) . Amazon::LF)
            . sprintf('Europe: %s', print_r($europe, true) . Amazon::LF)
            . sprintf('Features: %s', print_r($amazon_features, true) . Amazon::LF)
            . sprintf('Actives: %s', print_r($actives, true) . Amazon::LF)
            . sprintf('Regions: %s', print_r($regions, true) . Amazon::LF)
            . sprintf('Marketplace Ids: %s', print_r($marketPlaceIds, true) . Amazon::LF);

        $html = $this->context->smarty->assign('debugInfo', $debugInfo)
            ->fetch($this->path . 'views/templates/admin/items/debug_orders.tpl');

        $allMarketplaces = array();
        if (is_array($marketPlaceIds) && count($marketPlaceIds)) {
            foreach ($marketPlaceIds as $marketPlaceId) {
                if ($marketPlaceId) {
                    $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($marketPlaceId);
                    $region = $spMkp->getRegion(); // e.g: eu-west-1
                    $allMarketplaces[$region]['isUnified'] = $spMkp->belongToUnifiedEU();
                    $allMarketplaces[$region]['regionData'][] = array(
                        'mkpId' => $spMkp->getMarketplaceId(),
                        'iso' => $spMkp->getIso(),
                    );
                }
            }
        }

        return $html . $this->context->smarty->assign(array(
                'images' => $this->images,
                'psIsGt15' => $this->amazon->psIsGt15x,
                'regions' => $allMarketplaces,
                'isSeparateEU' => $isSeparateEUConfig,
            ))->fetch($this->path . 'views/templates/admin/order_tab/region_selector.tpl');
    }
}
