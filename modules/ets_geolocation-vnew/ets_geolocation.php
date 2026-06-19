<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once __DIR__ . '/classes/EtsGeoDefine.php';
require_once __DIR__ . '/classes/Geo_rules.php';
require_once __DIR__ . '/classes/Geo_country_rule.php';
require_once __DIR__ . '/classes/Geo_visit.php';
require_once __DIR__ . '/classes/Geo_visit_day.php';
require_once __DIR__ . '/classes/EtsGeoConfigService.php';
require_once __DIR__ . '/classes/EtsProductHelper.php';
require_once __DIR__ . '/classes/EtsGeoJsDefHelper.php';
require_once __DIR__ . '/classes/EtsGeoCountryHelper.php';
require_once __DIR__ . '/classes/EtsGeoDbHelper.php';
require_once __DIR__ . '/classes/EtsGeoIpCityDatabaseUpdater.php';
if (!class_exists('\GeoIp2\Database\Reader')) {
    require_once __DIR__ . '/vendor/geoip2/autoload.php';
}
/**
 * Class Ets_geolocation
 *
 * @property \Context|\ContextCore $context
 *
 * @mixin \ModuleCore
 */
class Ets_geolocation extends Module
{
    const MAXMIND_API_KEY = 'eUvNCKA9SwdtNdQj';
    public $toolbar_btn;
    public $is17;
    private $_html = null;
    protected $list_id = null;
    public $_filterHaving;
    public $errorMessage = null;
    public $_filter;
    protected $ssl_enable;
    protected $urlShopId = null;

    /**
     * @var \EtsGeoConfigService
     */
    private $configService;

    /**
     * @var \EtsProductHelper
     */
    private $productHelper;

    /**
     * @var \EtsGeoJsDefHelper
     */
    private $jsDefHelper;

    /**
     * @var \EtsGeoDbHelper
     */
    private $dbHelper;

    /**
     * Exclude host name from renaming Prestashop Twig override directory
     *
     * @var string[]
     */
    private $_excludedHosts = [
        'localhost',
        '*.zuko.pro',
        '*.presta-demos.com',
        '*.test',
    ];

    /**
     * Ets_geolocation constructor.
     */
    public function __construct()
    {
        $this->name = 'ets_geolocation';
        $this->tab = 'front_office_features';
        $this->version = '1.2.1';
        $this->author = 'PrestaHero';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '39afc9f65da1a39fc616eab831648228';
        parent::__construct();

        $this->list_id = Geo_rules::$definition['table'];
        $this->displayName = $this->l('Geolocation');
        $this->description = $this->l('Auto language, currency, taxes and shipping cost base on location of customer');
$this->refs = 'https://prestahero.com/';
        $this->ps_versions_compliancy = ['min' => '1.6.0.0', 'max' => _PS_VERSION_];
        $this->is17 = version_compare(_PS_VERSION_, '1.7', '>=');
        $this->ssl_enable = Configuration::get('PS_SSL_ENABLED');
    }

    /**
     * Detect address before add cart
     */
    public function hookActionObjectCartAddBefore()
    {
        $this->detectedAddress($this->context->cookie->iso_code_country);
    }

    /**
     * @param $params
     *
     * @return false|string
     *
     * @throws \PrestaShopException
     */
    public function hookDisplayGeoRuleHiddenProductList($params)
    {
        $list = [];
        if (isset($params['rule']) && $params['rule'] && $params['rule'] instanceof \Geo_rules && $params['rule']->id) {
            $list = explode(',', $params['rule']->hidden_products);
        }
        if (isset($params['ids']) && $params['ids']) {
            $list = array_merge($list, !is_array($params['ids']) ? explode(',', $params['ids']) : $params['ids']);
        }
        if (count($list) == 0) {
            return '';
        }
        $products = [];
        foreach ($list as $item) {
            if (!Validate::isUnsignedInt($item)) {
                continue;
            }
            $product = new Product($item, false, (int) $this->context->language->id);
            if ($product && Validate::isLoadedObject($product) && ($image = Product::getCover($item))) {
                $imagePath = $this->context->link->getImageLink($product->link_rewrite, $image['id_image']);
                $product_url = $this->context->link->getProductLink((int) $item);
                $product->image = $imagePath;
                $product->product_url = $product_url;
                $product->id_product = (int) $item;
                $products[] = $product;
            }
        }
        $this->smarty->assign([
            'products' => $products,
            'default_lang' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'rule' => $params['rule'],
        ]);
        return $this->display(__FILE__, 'block_prd_items.tpl');
    }
    public function getContent()
    {

        $this->_processAjaxRequest();
        $control = trim((string) Tools::getValue('control'));
        if (!$control) {
            Tools::redirectAdmin(EtsGeoDefine::getInstance()->getAdminLink(['control' => 'statistics']));
        }

        if (!$this->isGeoLiteCityAvailable()) {
            $urlUpdateDbFile = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=' . EtsGeoIpCityDatabaseUpdater::MAXMIND_API_KEY . '&suffix=tar.gz';
            $this->smarty->assign([
                'is_17' => $this->is17,
                'link_download' => $urlUpdateDbFile,
                'link_auto' => EtsGeoDefine::getInstance()->getAdminLink(['control' => 'upload_geolite']),
            ]);
            $this->_html = $this->displayWarning(trim($this->display(__FILE__, 'admin_warning.tpl')));
            Configuration::updateValue('PS_GEOLOCATION_ENABLED', 0);
        }
        if (!Configuration::get('PS_GEOLOCATION_ENABLED')) {
            $this->smarty->assign([
                'message_waring' => '"' . $this->l('Geolocation by IP address') . '" ' . $this->l('is disabled. Please enable this option '),
            ]);
        }
        if ($control == 'upload_geolite') {
            $this->_postUploadFile();
        } elseif ($control == 'statistics') {
            $this->_postStatistics();
        } elseif ($control == 'settings') {
            $this->_postConfig(EtsGeoDefine::getInstance()->getConfigSettings($control != 'settings'));
        } elseif ($control == 'rules') {
            $this->_postRules();
        } elseif ($control == 'messages') {
            $this->_postConfig(EtsGeoDefine::getInstance()->getConfigMessages());
        }
        return $this->getAminHtml($control);
    }

    public function getAminHtml($control)
    {
        $this->getJsDefHelper()->addBo('ajaxUrl', EtsGeoDefine::getInstance()->getAdminLink());
        $this->getJsDefHelper()->addBo('transMsg.hasBeenTagged', $this->l('has been tagged'));
        $this->getJsDefHelper()->addBo('transMsg.anErrorOccur', $this->l('An error occur'));
        $this->getJsDefHelper()->addBo('transMsg.successfully', $this->l('Successfully'));
        $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        if ((string) Tools::getValue('control')) {
            Media::addJsDef(['control' => (string) Tools::getValue('control')]);
        }
        $this->smarty->assign([
            'ets_geolocation_ajax_url' => EtsGeoDefine::getInstance()->getAdminLink(['ajaxproductsearch' => 1]),
            'ets_geolocation_author_ajax_url' => EtsGeoDefine::getInstance()->getAdminLink(['ajaxproductsearch' => 1]),
            'ets_geolocation_default_lang' => Configuration::get('PS_LANG_DEFAULT'),
            'ets_geolocation_is_updating' => (int) (Tools::getValue('id_post') || Tools::getValue('id_category')),
            'ets_geolocation_is_config_page' => Tools::getValue('control') == 'config',
            'ets_geolocation_invalid_file' => $this->l('Invalid file'),
            'ets_geolocation_module_dir' => $this->_path,
            'ets_geolocation_sidebar' => $this->renderTabs(),
            'ets_geolocation_body_html' => $this->renderAdminBodyHtml($control),
            'ets_geolocation_error_message' => $this->errorMessage,
            'control' => (string) Tools::getValue('control'),
        ]);

        return $this->display(__FILE__, 'admin.tpl');
    }

    public function renderAdminBodyHtml($control)
    {
        switch ($control) {
            case 'cronjob':
                $this->renderCronjob();
                break;
            case 'statistics':
                $this->renderStatic();
                break;
            case 'settings':
                $this->renderForm([
                    'fields' => EtsGeoDefine::getInstance()->getConfigSettings(Tools::getValue('control') != 'settings'),
                    'title' => $this->l('Settings'),
                    'icon' => '',
                ]);
                break;
            case 'rules':
                $this->renderRulesForm();
                break;
            case 'messages':
                $this->renderForm([
                    'fields' => EtsGeoDefine::getInstance()->getConfigMessages(),
                    'title' => $this->l('Messages'),
                    'icon' => '',
                ]);
                break;
            case 'help':
                $this->renderHelp();
                break;
        }

        return $this->_html;
    }

    public function renderHelp()
    {
        $cacheId = $this->_getCacheId(['renderHelp' => (int) $this->is17]);
        if (!$this->isCached('admin_help.tpl', $cacheId)) {
            $urlUpdateDbFile = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=' . EtsGeoIpCityDatabaseUpdater::MAXMIND_API_KEY . '&suffix=tar.gz';
            $this->context->smarty->assign([
                'is_17' => $this->is17,
                'link_download' => $urlUpdateDbFile,
            ]);
        }

        $this->_html .= $this->display(__FILE__, 'admin_help.tpl', $cacheId);
    }

    public function renderStatic()
    {
        $first_filter = 'year';
        $max_year = date('Y');
        $min_year = Geo_visit::getMinYear();
        if (($max_year - $min_year) >= 5) {
            $first_filter = 'all_times';
        }
        $_postFilter = Tools::getValue('geo_option_filter');
        $_allowedFilters = ['all_times', 'year', 'month'];
        $filter = Tools::isSubmit('submit_ajax') && in_array($_postFilter, $_allowedFilters, true) ? $_postFilter : $first_filter;
        $cacheId = $this->_getCacheId(['renderStatic' => $filter, 'date' => date('Ymd')]);

        if (!$this->isCached('statics_form.tpl', $cacheId)) {
            $this->_clearCache('*', $this->_getCacheId(['renderStatic' => $filter], true));
            $this->smarty->assign([
                'url_post' => EtsGeoDefine::getInstance()->getAdminLink(['control' => 'statistics']),
            ]);
        }

        $this->_html .= $this->display(__FILE__, 'statics_form.tpl', $cacheId);
    }

    private function _postUploadFile()
    {
        try {
            EtsGeoIpCityDatabaseUpdater::getInstance()->runUpdate();
            if ($this->isGeoLiteCityAvailable()) {
                Configuration::updateValue('PS_GEOLOCATION_ENABLED', 1);
            }
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
        }
        exit(json_encode([
            'error' => (bool) $this->_errors,
            'message' => !$this->_errors && $this->isGeoLiteCityAvailable() ? $this->displaySuccessMessage($this->l('Successfully downloaded')) : $this->displayError($this->_errors),
        ]));
    }

    /**
     * @param string $filterValue
     *
     * @return string
     */
    private function displayFilterLabel($filterValue)
    {
        $map = ['all_times' => $this->l('All times'), 'month' => $this->l('Day'), 'year' => $this->l('This year')];
        if (array_key_exists($filterValue, $map)) {
            return $map[$filterValue];
        }

        return $this->l('This year');
    }

    private function _postStatistics()
    {
        $maps_visit = [];
        $maps_visit_color = [];
        $param_maps = [];

        $first_filter = 'year';
        $max_year = date('Y');
        $min_year = Geo_visit::getMinYear();
        if (($max_year - $min_year) >= 5) {
            $first_filter = 'all_times';
        }
        $_postFilter = Tools::getValue('geo_option_filter');
        $_allowedFilters = ['all_times', 'year', 'month'];
        $param_maps['value'] = Tools::isSubmit('submit_ajax') && in_array($_postFilter, $_allowedFilters, true) ? $_postFilter : $first_filter;
        $param_maps['status'] = 'ajax';
        $param_maps['type'] = 'maps';
        $data_visits = Geo_visit::getDataVisit($param_maps);
        $total_all = 0;
        $caculation_total = [];
        /* Prosess maps */
        $arr_color = ['#004e64', '#2485a0', '#00a1ce', '#79cee5'];
        if (!empty($data_visits) && is_array($data_visits)) {
            for ($i = 0, $total_i = count($data_visits); $i < $total_i; ++$i) {
                $maps_visit[$data_visits[$i]['iso_code']] = $data_visits[$i]['visit'];
                $total_all += (int) $data_visits[$i]['visit'];
                if ($i < 3) {
                    $caculation_total['best_' . $i]['visit'] = (int) $data_visits[$i]['visit'];
                    $caculation_total['best_' . $i]['name'] = $data_visits[$i]['name'];
                    if (isset($data_visits[$i - 1]['visit']) && $caculation_total['best_' . $i]['visit'] == $data_visits[$i - 1]['visit']) {
                        $caculation_total['best_' . $i]['color'] = $arr_color[$i - 1];
                    } else {
                        $caculation_total['best_' . $i]['color'] = $arr_color[$i];
                    }
                    $maps_visit_color[$data_visits[$i]['iso_code']] = $arr_color[$i];
                } else {
                    if (!isset($caculation_total['other_visit']['visit'])) {
                        $caculation_total['other_visit']['visit'] = (int) $data_visits[$i]['visit'];
                        $caculation_total['other_visit']['name'] = $this->l('Other');
                        $caculation_total['other_visit']['color'] = $arr_color[3];
                    } else {
                        $caculation_total['other_visit']['visit'] += (int) $data_visits[$i]['visit'];
                    }
                    $maps_visit_color[$data_visits[$i]['iso_code']] = $arr_color[3];
                }
            }
        }
        $res_mapojb = [
            'res_visit' => (object) $maps_visit,
            'res_visit_color' => (object) $maps_visit_color,
            'total_line' => [],
            'param_filter_map' => $this->displayFilterLabel($param_maps['value']),
        ];

        if (!empty($caculation_total) && is_array($caculation_total)) {
            $arr_tem = [];
            foreach ($caculation_total as $key => $value) {
                $arr_tem[$key]['percent'] = round($value['visit'] * 100 / $total_all, 2);
                $arr_tem[$key]['name'] = $value['name'];
                $arr_tem[$key]['color'] = $value['color'];
                $arr_tem[$key]['visit'] = $value['visit'];
            }
            $res_mapojb['total_line'] = $arr_tem;
        }

        if (Tools::isSubmit('ajax') && (bool) Tools::getValue('get_maps')) {
            exit(
                json_encode(
                    $res_mapojb
                )
            );
        }
        Media::addJsDef(['res_visit' => $res_mapojb['res_visit'], 'res_visit_color' => $res_mapojb['res_visit_color']]);
        $this->context->smarty->assign(
            $res_mapojb
        );
        $arr_color = ['#f381aa', '#79ceb3', '#f9998c', '#6ac9e8'];
        $visit_last_day = Geo_visit::getDataVisit($param_maps);

        $doughnut_data = [];
        $sum = 0;
        $total_sum = 0;
        $tolta = count($visit_last_day);

        $arr_data_tron = [];
        $arr_color_tron = [];
        $arr_label_tron = [];
        $tron_dataset = [];

        if ($visit_last_day && is_array($visit_last_day)) {
            for ($i = 0; $i < $tolta; ++$i) {
                $total_sum += $visit_last_day[$i]['visit'];
                if ($i < 3) {
                    $doughnut_data[$visit_last_day[$i]['name']] = $visit_last_day[$i]['visit'];
                    $arr_data_tron[$i] = $visit_last_day[$i]['visit'];
                    $arr_color_tron[$i] = $arr_color[$i];
                    $arr_label_tron[$i] = $visit_last_day[$i]['name'];
                } else {
                    $sum += $visit_last_day[$i]['visit'];
                }
            }
            if ($tolta > 3) {
                $doughnut_data['Other'] = $sum;
                $arr_data_tron[3] = $sum;
                $arr_color_tron[3] = $arr_color[3];
                $arr_label_tron[3] = $this->l('Other');
            }

            $tron_dataset['data'] = $arr_data_tron;
            $tron_dataset['backgroundColor'] = $arr_color_tron;
        }
        $label_filter = $this->displayFilterLabel($param_maps['value']);
        Media::addJsDef([
            'datasets_tron' => $tron_dataset,
            'labels_tron' => $arr_label_tron,
            'doughnut_data' => $doughnut_data,
            'visit_total' => $total_sum,
        ]);
        $this->context->smarty->assign(
            [
                'data_filter_tron' => $label_filter,
                'visit_total' => $total_sum,
            ]
        );
        if (Tools::isSubmit('ajax') && (bool) Tools::getValue('doughnut')) {
            exit(
                json_encode(
                    [
                        'doughnut_data' => $tron_dataset,
                        'label_tron' => $arr_label_tron,
                        'data_filter_tron' => $label_filter,
                        'visit_text' => $total_sum,
                    ]
                )
            );
        }

        $param_line = array_merge($param_maps, ['chart' => 'linechar']);
        unset($param_line['type']);
        $data_linechar = Geo_visit::getDataVisit($param_line);
        $arr_temp_data = [];
        $char_index = 0;
        $max_year = (int) date('Y');
        $min_year = Geo_visit::getMinYear();
        $distance = ($max_year - $min_year);
        $is_alltimes = false;
        if ($param_line['value'] == 'all_times' && $distance > 3) {
            $is_alltimes = true;
            $char_index = $distance;
        } else {
            $char_index = $char_index != 0 ? $char_index : ($param_line['value'] == 'year' ? 12 : (int) date('t'));
        }
        foreach ($data_linechar as $value) {
            if ($is_alltimes) {
                $arr_temp_data[$value['id_country']]['day_visit'][$value['year']] = isset($arr_temp_data[$value['id_country']]['day_visit'][$value['year']]) ? ($arr_temp_data[$value['id_country']]['day_visit'][$value['year']] + $value['total_visit']) : $value['total_visit'];
                $arr_temp_data[$value['id_country']]['name'] = $value['name'];
            } else {
                $arr_temp_data[$value['id_country']]['day_visit'][$param_line['value'] == 'year' ? $value['month'] : $value['day']] = isset($arr_temp_data[$value['id_country']]['day_visit'][$param_line['value'] == 'year' ? $value['month'] : $value['day']]) ? ($arr_temp_data[$value['id_country']]['day_visit'][$param_line['value'] == 'year' ? $value['month'] : $value['day']] + $value['total_visit']) : $value['total_visit'];
                $arr_temp_data[$value['id_country']]['name'] = $value['name'];
            }
        }

        foreach ($arr_temp_data as &$arr) {
            $arr['total_visit'] = array_sum($arr['day_visit']);
        }
        unset($arr);
        usort($arr_temp_data, static function ($a, $b) {
            return $b['total_visit'] - $a['total_visit'];
        });
        $tolta_linechar = count($arr_temp_data);
        $line_data = [];
        $arr_temp_four = [];
        $arr_label = [];

        for ($i = 0; $i < $tolta_linechar; ++$i) {
            if ($i < 3) {
                $arr_temp = [];
                $arr_temp['label'] = $arr_temp_data[$i]['name'];
                $arr_temp['backgroundColor'] = $arr_color[$i];
                $arr_temp['borderColor'] = $arr_color[$i];
                $arr_temp['borderWidth'] = 1;
                $arr_temp['fill'] = 'boundary';
                if ($is_alltimes) {
                    $flag = 0;
                    for ($j = $min_year; $j <= $max_year; ++$j) {
                        if (array_key_exists($j, $arr_temp_data[$i]['day_visit'])) {
                            $arr_temp['data'][$flag] = (int) $arr_temp_data[$i]['day_visit'][$j];
                        } else {
                            $arr_temp['data'][$flag] = 0;
                        }
                        ++$flag;
                    }
                } else {
                    for ($j = 1; $j <= $char_index; ++$j) {
                        if (array_key_exists($j, $arr_temp_data[$i]['day_visit'])) {
                            $arr_temp['data'][$j - 1] = (int) $arr_temp_data[$i]['day_visit'][$j];
                        } else {
                            $arr_temp['data'][$j - 1] = 0;
                        }
                    }
                }

                $line_data[$i] = (object) $arr_temp;
            } else {
                $arr_temp_four['label'] = 'Other';
                $arr_temp_four['backgroundColor'] = $arr_color[3];
                $arr_temp_four['borderColor'] = $arr_color[3];
                $arr_temp_four['borderWidth'] = 1;
                $arr_temp_four['fill'] = 'boundary';
                if ($is_alltimes) {
                    $flag = 0;
                    for ($j = $min_year; $j <= $max_year; ++$j) {
                        $arr_temp_four['data'][$flag] = (isset($arr_temp_four['data'][$flag]) && $arr_temp_four['data'][$flag]) ? $arr_temp_four['data'][$flag] : 0;
                        if (array_key_exists($j, $arr_temp_data[$i]['day_visit'])) {
                            $arr_temp_four['data'][$flag] = (int) $arr_temp_four['data'][$flag] + $arr_temp_data[$i]['day_visit'][$j];
                        } else {
                            $arr_temp['data'][$flag] = 0;
                        }
                        ++$flag;
                    }
                } else {
                    for ($j = 1; $j <= $char_index; ++$j) {
                        $arr_temp_four['data'][$j - 1] = (isset($arr_temp_four['data'][$j - 1]) && $arr_temp_four['data'][$j - 1]) ? $arr_temp_four['data'][$j - 1] : 0;
                        if (array_key_exists($j, $arr_temp_data[$i]['day_visit'])) {
                            $arr_temp_four['data'][$j - 1] = (int) $arr_temp_four['data'][$j - 1] + $arr_temp_data[$i]['day_visit'][$j];
                        } else {
                            $arr_temp['data'][$j] = 0;
                        }
                    }
                }
            }
        }

        if (isset($arr_temp_four['data']) && $arr_temp_four['data']) {
            $line_data[3] = (object) $arr_temp_four;
        }
        if ($is_alltimes) {
            for ($i = $min_year; $i <= $max_year; ++$i) {
                $arr_label[] = (int) $i;
            }
        } else {
            for ($i = 1; $i <= $char_index; ++$i) {
                $arr_label[] = $i;
            }
        }
        Media::addJsDef([
            'label_value' => $this->l('Visits'),
            'linechar_data' => $line_data,
            'data_label' => $arr_label,
            'data_filter' => $this->displayFilterLabel($param_line['value']),
            'data_filter_label' => $param_line['value'] == 'all_times' ? $this->l('Year') : ($param_line['value'] == 'month' ? $this->l('Day') : $this->l('Month')),
        ]);
        if (Tools::isSubmit('ajax') && (Tools::getValue('line_char') || Tools::getValue('horizontal_char'))) {
            exit(json_encode(
                [
                    'char_lists' => ['wrapper_doughnut', 'wrapper_linechar'],
                    'label_value' => $this->l('Visits'),
                    'linechar_data' => $line_data,
                    'data_label' => $arr_label,
                    'data_filter' => $this->displayFilterLabel($param_line['value']),
                    'data_filter_label' => $param_line['value'] == 'all_times' ? $this->l('Year') : ($param_line['value'] == 'month' ? $this->l('Day') : $this->l('Month')),
                ]
            ));
        }
    }

    private function _postConfig($configs = [])
    {
        if (Tools::isSubmit('saveConfig')) {
            $languages = Language::getLanguages(false);
            $id_lang_default = (int) Configuration::get('PS_LANG_DEFAULT');
            if ($configs) {
                foreach ($configs as $key => $config) {
                    if ($key == 'PS_GEOLOCATION_ENABLED' && !$this->isGeoLiteCityAvailable()) {
                        $this->_errors[] = $this->l('The geolocation database is unavailable.');
                    } elseif (isset($config['lang']) && $config['lang']) {
                        if (isset($config['required']) && $config['required'] && $config['type'] != 'switch' && trim((string) Tools::getValue($key . '_' . $id_lang_default)) == '') {
                            $this->_errors[] = $config['label'] . ' ' . $this->l('is required');
                        }
                    } else {
                        if (isset($config['required']) && $config['required'] && $config['type'] != 'switch' && trim((string) Tools::getValue($key)) == '') {
                            $this->_errors[] = $config['label'] . ' ' . $this->l('is required');
                        }
                        if (isset($config['validate']) && method_exists('Validate', $config['validate'])) {
                            $validate = $config['validate'];
                            if (!Validate::$validate(trim((string) Tools::getValue($key)))) {
                                $this->_errors[] = $config['label'] . ' ' . $this->l('is invalid');
                            }
                            unset($validate);
                        } elseif (!Validate::isCleanHtml(trim((string) Tools::getValue($key)))) {
                            $this->_errors[] = $config['label'] . ' ' . $this->l('is invalid');
                        }
                    }
                }
            }

            if (!$this->_errors && $configs) {
                foreach ($configs as $key => $config) {
                    if (isset($config['lang']) && $config['lang']) {
                        $values = [];
                        foreach ($languages as $lang) {
                            if ($config['type'] == 'switch') {
                                $values[$lang['id_lang']] = (bool) trim(Tools::getValue($key . '_' . $lang['id_lang'])) ? 1 : 0;
                            } else {
                                $values[$lang['id_lang']] = trim(Tools::getValue($key . '_' . $lang['id_lang'])) ?: trim(Tools::getValue($key . '_' . $id_lang_default));
                            }
                        }
                        Configuration::updateValue($key, $values, true);
                    } else {
                        if ($config['type'] == 'switch') {
                            Configuration::updateValue($key, (int) trim(Tools::getValue($key)) ? 1 : 0);
                        } elseif ($config['type'] == 'checkbox') {
                            Configuration::updateValue($key, implode(',', $this->_removeInvalidValueFromArray((array) Tools::getValue($key))));
                        } else {
                            Configuration::updateValue($key, trim(Tools::getValue($key)));
                        }
                    }
                }
            }
            if (count($this->_errors)) {
                $this->errorMessage = $this->displayError($this->_errors);
            }
            if (!count($this->_errors)) {
                Tools::redirectAdmin(EtsGeoDefine::getInstance()->getAdminLink([
                    'conf' => 4,
                    'control' => Tools::getValue('control', 'statistics'),
                ]));
            }
        }
    }

    /**
     * @param array $array
     * @param bool $keepZero
     *
     * @return array
     */
    private function _removeInvalidValueFromArray($array, $keepZero = true)
    {
        if (!is_array($array)) {
            throw new \RuntimeException('_removeInvalidValueFromArray accept array only.');
        }

        return array_filter($array, static function ($v) use ($keepZero) {
            if (!$keepZero && $v == 0) {
                return false;
            }

            return trim($v) != '';
        });
    }

    private function _postRules()
    {
        if (Tools::isSubmit('submitResetets_geo_rule')) {
            $this->processResetFilters();
        } else {
            $id_rule = (int) Tools::getValue('id_rule');
            $geo_rule = new Geo_rules($id_rule);
            if ($id_rule && !$geo_rule->id && !Tools::isSubmit('list')) {
                Tools::redirectAdmin(EtsGeoDefine::getInstance()->getAdminLink());
            } elseif ($geo_rule->id && Tools::isSubmit('change_enabled')) {
                $field = (string) Tools::getValue('field');
                $geo_rule->$field = !(int) $geo_rule->$field;
                if ($geo_rule->update()) {
                    if (Tools::isSubmit('ajax')) {
                        exit(json_encode([
                            'listId' => $id_rule,
                            'enabled' => $geo_rule->$field,
                            'field' => $field,
                            'message' => $this->displaySuccessMessage($this->l('Successfully updated')),
                            'messageType' => 'success',
                            'href' => EtsGeoDefine::getInstance()->getAdminLink(['control' => 'rules', 'field' => $field, 'id_rule' => $id_rule]),
                        ]));
                    }

                    Tools::redirectAdmin(EtsGeoDefine::getInstance()->getAdminLink(['conf' => 4, 'control' => 'rules', 'list' => 1]));
                }
            } elseif (Tools::isSubmit('deleteets_geo_rule') && $geo_rule->id) {
                if ($geo_rule->delete()) {
                    Tools::redirectAdmin(EtsGeoDefine::getInstance()->getAdminLink(['conf' => 4, 'control' => 'rules', 'list' => 1]));
                } else {
                    $this->_errors[] = $this->l('Could not delete the rule. Please try again');
                }
            } elseif (Tools::isSubmit('saveRules')) {
                if (!($all_countries = (bool) Tools::getValue('all_countries')) && empty(Tools::getValue('countries'))) {
                    $this->_errors[] = $this->l('Countries is required.');
                }
                if ($urlRedirect = Tools::getValue('url_redirect')) {
                    if (!Validate::isAbsoluteUrl(trim($urlRedirect))) {
                        $this->_errors[] = $this->l('Redirect to is invalid');
                    } elseif (preg_match('#^http(?:s?):\/\/(?:www\.)?(?:' . $this->context->shop->domain . ')((' . str_replace('/', "\/", rtrim($this->context->shop->getBaseURI(), '/')) . ')(.*)?)$#', $urlRedirect, $matches)) {
                        $ok = 1;

                        if (!trim($matches[3], '/')) {
                            $ok = 1;
                        } elseif ($shops = Shop::getShops()) {
                            foreach ($shops as $shop) {
                                if ($shop['domain'] == $this->context->shop->domain) {
                                    if ((rtrim($shop['uri'], '/') == rtrim($matches[2], '/')) && (int) $shop['id_shop'] == $this->context->shop->id) {
                                        $ok = 0;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($ok) {
                            $this->_errors[] = $this->l('The domain of the redirect url must not be the same as your website domain');
                        }
                    }
                }

                if (!$this->_errors) {
                    $geo_rule->enabled = Tools::getValue('enabled') ? 1 : 0;
                    $geo_rule->all_countries = $all_countries;
                    $geo_rule->countries = $geo_rule->all_countries ? [] : $this->_removeInvalidValueFromArray((array) Tools::getValue('countries'));
                    $geo_rule->disable_geo = (bool) Tools::getValue('disable_geo');
                    $geo_rule->lang_to_set = (int) Tools::getValue('lang_to_set');
                    $geo_rule->currency_to_set = (int) Tools::getValue('currency_to_set');
                    $geo_rule->block_user = (bool) Tools::getValue('block_user');
                    $geo_rule->hidden_products = (string) Tools::getValue('hidden_products');
                    $geo_rule->url_redirect = (string) Tools::getValue('url_redirect');
                    $geo_rule->priority = (float) Tools::getValue('priority');
                    $geo_rule->id_shop = $this->context->shop->id;
                    // Validate inputs
                    $msg = $geo_rule->validateFields(false, true);
                    if ($msg && $msg !== true) {
                        $this->_errors[] = $msg;
                    }
                    if (!$this->_errors && !$geo_rule->save()) {
                        $this->_errors[] = $this->l('Failed to save. There was an unknown error happens');
                    }
                }
            }
            if ($this->_errors) {
                $this->errorMessage = $this->displayError($this->_errors);
            } else {
                if (Tools::isSubmit('saveRules') && ($id_rule = (int) Tools::getValue('id_rule'))) {
                    Tools::redirectAdmin(EtsGeoDefine::getInstance()->getAdminLink(['conf' => 4, 'update'.$this->list_id =>'1', 'id_rule' => $id_rule, 'control' => 'rules']));
                } elseif (Tools::isSubmit('saveRules')) {
                    Tools::redirectAdmin(EtsGeoDefine::getInstance()->getAdminLink(['conf' => 3, 'update'.$this->list_id=>'1', 'id_rule' => $geo_rule->id, 'control' => 'rules']));
                }
            }
        }
    }

    public function renderForm($args = [])
    {
        if (!isset($args['fields']) || !$args['fields']) {
            return;
        }
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => isset($args['title']) ? $args['title'] : $this->l('Configurations'),
                    'icon' => isset($args['icon']) ? $args['icon'] : 'icon-Admin',
                ],
                'input' => [],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
        if ($configs = $args['fields']) {
            foreach ($configs as $key => $config) {
                $fields = $config;
                $fields['name'] = $key;
                if (isset($config['type']) && $config['type'] == 'switch') {
                    $fields['values'] = isset($config['values']) ? $config['values'] : EtsGeoDefine::getInstance()->getSwitchValues();
                }
                $fields_form['form']['input'][] = $fields;
            }
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $language = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveConfig';
        $helper->currentIndex = EtsGeoDefine::getInstance()->getAdminLink(['token' => false, 'control' => (string) Tools::getValue('control')]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $fields = [];
        $languages = Language::getLanguages(false);
        $helper->override_folder = '/';
        if (Tools::isSubmit('saveConfig')) {
            if ($configs) {
                foreach ($configs as $key => $config) {
                    if (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $fields[$key][$l['id_lang']] = Tools::getValue($key . '_' . $l['id_lang'], isset($config['default']) ? $config['default'] : '');
                        }
                    } else {
                        $fields[$key] = Tools::getValue($key, isset($config['default']) ? $config['default'] : '');
                    }
                }
            }
        } else {
            if ($configs) {
                foreach ($configs as $key => $config) {
                    if (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $fields[$key][$l['id_lang']] = Configuration::get($key, $l['id_lang']);
                        }
                    } elseif ($config['type'] == 'checkbox') {
                        $fields[$key] = $this->_removeInvalidValueFromArray(explode(',', Configuration::get($key)));
                    } else {
                        $fields[$key] = Configuration::get($key);
                    }
                }
            }
        }
        $helper->tpl_vars = [
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => [
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code,
            ],
            'fields_value' => $fields,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'configTabs' => Tools::getValue('control') == 'config' ? EtsGeoDefine::getInstance()->getConfigTabs() : [],
        ];
        $this->_html .= $helper->generateForm([$fields_form]);
    }

    public function renderTabs()
    {
        if ($control = Tools::getValue('control')) {
            $active = trim($control);
        } else {
            $active = Tools::getValue('controller') == 'AdminEtsGeoStatistics' ? 'statistics' : 'post';
        }
        $cacheId = $this->_getCacheId(['renderTabs' => $active]);
        if (!$this->isCached('sidebar.tpl', $cacheId)) {
            $this->context->smarty->assign([
                'link' => $this->context->link,
                'list' => EtsGeoDefine::getInstance()->getMenuTabList(),
                'admin_path' => EtsGeoDefine::getInstance()->getAdminLink(),
                'active' => 'ets_tab_' . $active,
            ]);
        }

        return $this->display(__FILE__, 'sidebar.tpl', $cacheId);
    }

    private function renderCronjob()
    {
        $cacheId = $this->_getCacheId(['renderCronjob']);
        if (!($cronjobToken = Configuration::getGlobalValue('ETS_GEO_CRONJOB_TOKEN'))) {
            Configuration::updateGlobalValue('ETS_GEO_TOKEN_FIRST_GEN_TIMESTAMP', time());
            Configuration::updateGlobalValue('ETS_GEO_CRONJOB_TOKEN', $cronjobToken = Tools::passwdGen(12));
        }
        if (!$this->isCached('cronjob.tpl', $cacheId)) {
            $this->context->smarty->assign([
                'phpBin' => (defined('PHP_BINDIR') && PHP_BINDIR && is_string(PHP_BINDIR) ? PHP_BINDIR . '/' : '') . 'php ',
                'linkCronjob' => $this->context->link->getModuleLink('ets_geolocation', 'cron'),
                'cronjobFile' => _PS_MODULE_DIR_ . 'ets_geolocation/cronjob.php',
                'cronjobToken' => $cronjobToken,
            ]);
        }

        $this->_html .= $this->display(__FILE__, 'cronjob.tpl', $cacheId) . $this->getLastCronjobRun();
    }

    /**
     * @return string
     *
     * @throws \PrestaShopException
     */
    public function getLastCronjobRun()
    {
        $lastRun = '';
        $needRunNow = true;
        if ($cronjob_time = Configuration::getGlobalValue('ETS_GEO_TIME_LOG_CRONJOB')) {
            $last_time = strtotime($cronjob_time);
            $time = strtotime(date('Y-m-d H:i:s')) - $last_time;
            // 37 days
            if ($time >= 3196800) {
                $needRunNow = true;
            } else {
                $needRunNow = false;
            }
            if ($time > 86400) {
                $lastRun = Tools::displayDate($cronjob_time, null, true);
            } elseif ($time) {
                if ($hours = floor($time / 3600)) {
                    $lastRun .= $hours . ' ' . $this->l('hours') . ' ';
                    $time %= 3600;
                }
                if ($minutes = floor($time / 60)) {
                    $lastRun .= $minutes . ' ' . $this->l('minutes') . ' ';
                    $time %= 60;
                }
                if ($time) {
                    $lastRun .= $time . ' ' . $this->l('seconds') . ' ';
                }
                $lastRun .= $this->l('ago');
            }
        }
        if ($needRunNow) {
            $tokenFirstGenAt = Configuration::getGlobalValue('ETS_GEO_TOKEN_FIRST_GEN_TIMESTAMP');
            $needRunNow = (time() - $tokenFirstGenAt) > (7 * 24 * 60);
        }

        $this->context->smarty->assign([
            'lastRun' => $lastRun,
            'needRunNow' => $needRunNow,
        ]);

        return $this->display(__FILE__, 'last-cronjob-run.tpl');
    }

    public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 60)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = stream_context_create([
                'http' => [
                    'timeout' => $curl_timeout,
                    'max_redirects' => 101,
                    'header' => 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
                ],
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
        }
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => html_entity_decode($url),
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => $curl_timeout,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $content = curl_exec($curl);
            curl_close($curl);

            return $content;
        }

        if (in_array(ini_get('allow_url_fopen'), ['On', 'on', '1']) || !preg_match('/^https?:\/\//', $url)) {
            return Tools::file_get_contents($url, $use_include_path, $stream_context);
        }

        return false;
    }

    public function ets_getCountriesNotBlock($idLang, $active = false, $containStates = false, $listStates = true)
    {
        $countries = [];
        if ($result = $this->getDbHelper()->getListCountriesNotBlock($idLang, $active, $containStates)) {
            foreach ($result as $row) {
                $countries[$row['id_country']] = $row;
            }
        }

        if ($listStates) {
            $result = $this->getDbHelper()->getStates('name');
            foreach ($result as $row) {
                if (isset($countries[$row['id_country']]) && $row['active'] == 1) { /* Does not keep the state if its country has been disabled and not selected */
                    $countries[$row['id_country']]['states'][] = $row;
                }
            }
        }

        return $countries;
    }

    public function renderRulesForm()
    {
        if (Tools::isSubmit('addets_geo_rule') || Tools::isSubmit('updateets_geo_rule')) {
            $geo_rule = new Geo_rules((int) Tools::getValue('id_rule'));
            $fields = EtsGeoDefine::getInstance()->getRuleFormFieldList($geo_rule);
            if ($geo_rule->id) {
                $this->getJsDefHelper()->addBo('currentRuleId', $geo_rule->id);
            }
            $fields_form = [
                'form' => [
                    'legend' => [
                        'title' => (bool) Tools::getValue('id_rule') ? $this->l('Edit rule') : $this->l('Add rule'),
                    ],
                    'name' => 'rules',
                    'submit' => [
                        'title' => $this->l('Save'),
                    ],
                    'buttons' => [
                        'back' => [
                            'href' => EtsGeoDefine::getInstance()->getAdminLink(['control' => 'rules', 'list' => 1]),
                            'title' => $this->l('Back to list'),
                            'icon' => 'process-icon-back',
                        ],
                    ],
                    'input' => $fields,
                ],
            ];
            $helper = new HelperForm();
            $helper->show_toolbar = false;
            $helper->table = $this->table;
            $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
            $helper->default_form_language = $lang->id;
            $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
            $helper->module = $this;
            $helper->identifier = $this->identifier;
            $helper->submit_action = 'saveRules';
            $helper->currentIndex = EtsGeoDefine::getInstance()->getAdminLink(['token' => false, 'control' => 'rules']) . (Tools::isSubmit('updateets_geo_rule') ? '&updateets_geo_rule' : '') . (Tools::isSubmit('addets_geo_rule') ? '&addets_geo_rule' : '');
            $helper->token = Tools::getAdminTokenLite('AdminModules');
            $helper->tpl_vars = [
                'base_url' => $this->context->shop->getBaseURL(),
                'language' => ['id_lang' => $lang->id, 'iso_code' => $lang->iso_code],
                'PS_ALLOW_ACCENTED_CHARS_URL' => (int) Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
                'fields_value' => $this->getFieldsValues($fields, $geo_rule, $helper->submit_action),
                'languages' => $this->context->controller->getLanguages(),
                'id_language' => $this->context->language->id,
                'geo_rule' => $geo_rule->id ? $geo_rule : null,
            ];
            $helper->override_folder = '/';
            $this->_html .= $helper->generateForm([$fields_form]);

            return $this->_html;
        }

        return $this->_html .= $this->renderList([
            'fields_list' => EtsGeoDefine::getInstance()->getRuleFieldList(),
            'title' => $this->l('Rules'),
            'actions' => ['edit', 'delete'],
            'orderBy' => 'id_rule',
            'orderWay' => 'DESC',
            'model' => 'Geo_rules',
            'no_link' => true,
            'bulk_actions' => false,
            'list_id' => 'ets_geo_rule',
        ]);
    }

    public function getFieldsValues($configs, $obj, $submit)
    {
        $fields = [];
        $languages = Language::getLanguages(false);
        if (Tools::isSubmit($submit)) {
            if ($configs) {
                foreach ($configs as $config) {
                    $key = $config['name'];
                    if (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $fields[$key][$l['id_lang']] = Tools::getValue($key . '_' . $l['id_lang'], isset($config['default']) ? $config['default'] : '');
                        }
                    } elseif ($config['type'] == 'select' && isset($config['multiple']) && $config['multiple']) {
                        $fields[$key . ($config['type'] == 'select' ? '[]' : '')] = Tools::getValue($key, []);
                    } elseif (isset($config['type']) && $config['type'] == 'geo_countries') {
                        $fields[$key] = ($all_countries = (bool) Tools::getValue('all_countries')) ? [] : $this->_removeInvalidValueFromArray((array) Tools::getValue($key));
                        $fields['all_countries'] = $all_countries;
                    } else {
                        $fields[$key] = Tools::getValue($key, isset($config['default']) ? $config['default'] : '');
                    }
                }
            }
        } else {
            if ($configs) {
                foreach ($configs as $config) {
                    $key = $config['name'];
                    if ($config['type'] == 'checkbox') {
                        $fields[$key] = $obj->id ? explode(',', $obj->$key) : (isset($config['default']) ? $config['default'] : []);
                    } elseif (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $values = $obj->$key;
                            $fields[$key][$l['id_lang']] = $obj->id ? $values[$l['id_lang']] : (isset($config['default']) ? $config['default'] : '');
                        }
                    } elseif (isset($config['type']) && $config['type'] == 'geo_countries') {
                        $fields[$key] = $obj->all_countries ? [] : $obj->getCountries();
                        $fields['all_countries'] = $obj->all_countries;
                    } else {
                        $fields[$key] = $obj->id && property_exists($obj, $key) ? $obj->$key : (isset($config['default']) ? $config['default'] : null);
                    }
                }
            }
        }

        return $fields;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7.7', '<')) {
            $this->context->controller->addJquery();
        }
        $this->context->controller->addCSS($this->_path . 'views/css/admin-all16.css');
        $this->context->controller->addJS($this->_path . 'views/js/admin_all.js');
        if (Tools::isSubmit('configure') && Tools::getValue('configure') == $this->name) {
            $this->context->controller->addCSS('https://fonts.googleapis.com/css?family=Poppins:400,600,700,900');
            if (Tools::isSubmit('control') && Tools::getValue('control') == 'statistics') {
                $this->context->controller->addJqueryUI('ui.datepicker');
                $this->context->controller->addCSS($this->_path . 'views/css/jquery-jvectormap.css');
                $this->context->controller->addJS($this->_path . 'views/js/jquery-jvectormap.js');
                $this->context->controller->addJS($this->_path . 'views/js/Chart.min.js');
                $this->context->controller->addJS($this->_path . 'views/js/utils.js');
                $this->context->controller->addJS($this->_path . 'views/js/chartjs-plugin-labels.min.js');
            }

            $this->context->controller->addCSS($this->_path . 'views/css/admin_all.css');

            $this->context->smarty->assign([
                'control' => Tools::getValue('control'),
            ]);
            if (!$this->is17) {
                $this->context->controller->addCSS($this->_path . 'views/css/admin16.css');
            }
        }
    }

    protected function filterToField($key, $filter)
    {
        $fieldsList = EtsGeoDefine::getInstance()->getRuleFieldList();
        foreach ($fieldsList as $field) {
            if (array_key_exists('filter_key', $field) && $field['filter_key'] == $key) {
                return $field;
            }
        }
        if (array_key_exists($filter, $fieldsList)) {
            return $fieldsList[$filter];
        }

        return false;
    }

    public function hookActionTaxManager($params)
    {
        return $this->hookTaxManager($params);
    }

    public function hookTaxManager($params)
    {
        if ((!isset($params['address']) || !$params['address']->id) && isset($this->context->cookie->iso_code_country)) {
            $id_country = Tools::getValue('id_country', EtsGeoCountryHelper::getIdByIso($this->context->cookie->iso_code_country));
            $address = $params['address'];
            if ($id_country && Validate::isUnsignedId($id_country)) {
                $address->id_country = $id_country;
            }
            if (($id_state = (int) Tools::getValue('id_state')) && Validate::isUnsignedId($id_state)) {
                $address->id_state = $id_state;
            }
            if (($postal_code = Tools::getValue('postal_code')) && Validate::isPostCode($postal_code)) {
                $address->postcode = $postal_code;
            }
            $type = isset($params['type']) ? $params['type'] : null;
            if (!$type && isset($params['params'])) {
                $type = $params['params'];
            }

            return new TaxRulesTaxManager($params['address'], $type);
        }
    }

    // <editor-fold desc="Method for install" defaultstate="collapsed">
    /**
     * @param string $host
     *
     * @return bool
     */
    private function isExcludeHost($host)
    {
        $rs = false;
        foreach ($this->_excludedHosts as $excludedHost) {
            if (strpos($excludedHost, '*.') !== false) {
                $excludedHost = explode('*.', $excludedHost)[1];
                if (false !== EtsGeoConfigService::strEndsWith($host, $excludedHost)) {
                    $rs = true;
                }
            } elseif ($host === $excludedHost) {
                $rs = true;
            }
            if ($rs) {
                return true;
            }
        }

        return false;
    }

    /**
     * Only for 1.6
     */
    public function _onBeforeEnableModule()
    {
        if ($this->is17 && !$this->isExcludeHost(Tools::getHttpHost(false, false, true)) && @file_exists(__DIR__ . '/override/classes/controller/FrontController.php')) {
            @rename(__DIR__ . '/override/classes/controller/FrontController.php', __DIR__ . '/override/classes/controller/FrontController.php_renamed');
        }
    }

    /**
     * @param bool $forceAll
     *
     * @return bool
     */
    public function enable($forceAll = false)
    {
        $this->_onBeforeEnableModule();

        return parent::enable($forceAll);
    }

    public function install()
    {
        return parent::install()
            && $this->_registerHook()
            && $this->_installDb()
            && $this->_installConfigs()
            && $this->_installTabs();
    }

    private function _registerHook()
    {
        return $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook(!$this->is17 ? 'displayNav' : 'displayNav1')
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionDispatcher')
            && $this->registerHook('taxManager')
            && $this->registerHook('actionTaxManager')
            && $this->registerHook('actionObjectCartAddBefore')
            && $this->registerHook('displayGeoRuleHiddenProductList');
    }

    private function _installDb()
    {
        return $this->getDbHelper()->executeInstall();
    }

    public function _installTabs()
    {
        $languages = Language::getLanguages(false);
        if (!($parentTabId = Tab::getIdFromClassName('AdminGeolocationParent'))) {
            $tab = new Tab();
            $tab->class_name = 'AdminGeolocationParent';
            $tab->module = $this->name;
            $tab->id_parent = 0;
            foreach ($languages as $lang) {
                $tab->name[$lang['id_lang']] = ($tabName = $this->getConfigService()->getTextLang('Geolocation', $lang)) ? $tabName : $this->l('Geolocation');
            }
            $tab->save();
            $parentTabId = Tab::getIdFromClassName('AdminGeolocationParent');
        }
        if (!isset($tab)) {
            $tab = new \Tab($parentTabId);
        }
        if ($tab->id && $quickTabs = EtsGeoDefine::getInstance()->getQuickTabs()) {
            foreach ($quickTabs as $t) {
                $oldId = Tab::getIdFromClassName($t['class_name']) ?: null;
                $child = new \Tab($oldId);
                $child->class_name = $t['class_name'];
                $child->module = $this->name;
                $child->id_parent = (int) $tab->id;
                $child->icon = $t['icon'];
                foreach ($languages as $l) {
                    $child->name[(int) $l['id_lang']] = ($langName = $this->getConfigService()->getTextLang($t['tab_name_origin'], $l)) ? $langName : $this->l($t['tab_name_origin']);
                }
                $child->save();
            }
        }

        return true;
    }

    private function _installConfigs($upgrade = false)
    {
        $languages = Language::getLanguages(false);
        if ($configTabs = EtsGeoDefine::getInstance()->getConfigTabs()) {
            foreach ($configTabs as $tab) {
                if (isset($tab['subTabs']) && ($subTabs = $tab['subTabs'])) {
                    foreach ($subTabs as $subTab) {
                        $this->_installTabConfig($subTab, $languages, $upgrade);
                    }
                } else {
                    $this->_installTabConfig($tab, $languages, $upgrade);
                }
            }
        }

        return true;
    }

    public function _installTabConfig($TAB, $languages, $upgrade = false)
    {
        if ($TAB['value'] == 'configs_messages') {
            require_once __DIR__ . '/classes/Geo_trans.php';
        }
        if (!$languages) {
            $languages = Language::getLanguages(false);
        }
        if ($TAB && isset($TAB['is_conf'], $TAB['value'], $TAB['getMethod']) && $TAB['is_conf'] && method_exists(EtsGeoDefine::class, $TAB['getMethod']) && ($configs = EtsGeoDefine::getInstance()->{$TAB['getMethod']}())) {
            foreach ($configs as $key => $config) {
                if (isset($config['lang']) && $config['lang']) {
                    $values = [];
                    foreach ($languages as $lang) {
                        if ($TAB['value'] !== 'configs_messages') {
                            $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                        } else {
                            $values[$lang['id_lang']] = isset(Geo_trans::$trans[$key][$lang['iso_code']]) && ($res = Geo_trans::$trans[$key][$lang['iso_code']]) ? EtsGeoDefine::getInstance()->formatWithinTags($res, 'strong') : (isset($config['default']) ? $config['default'] : '');
                        }
                    }
                    if (($upgrade && !Configuration::hasKey($key)) || !$upgrade) {
                        Configuration::updateValue($key, $values, true);
                    }
                } elseif (($upgrade && !Configuration::hasKey($key)) || !$upgrade) {
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '', true);
                }
            }
        }
    }
    // </editor-fold>

    // <editor-fold desc="Method for uninstall" defaultstate="collapsed">
    public function uninstall()
    {
        return parent::uninstall() && $this->_uninstallConfigs() && $this->_uninstallDb() && $this->_uninstallTab();
    }

    private function _uninstallTab()
    {
        $Id = Tab::getIdFromClassName('AdminGeolocationParent');
        $tab = new Tab($Id);
        if (($quickTabs = EtsGeoDefine::getInstance()->getQuickTabs()) && $tab->delete()) {
            foreach ($quickTabs as $t) {
                if ($tabId = Tab::getIdFromClassName($t['class_name'])) {
                    $stab = new Tab($tabId);
                    $stab->delete();
                }
            }
        }

        return true;
    }

    private function _uninstallDb()
    {
        return $this->getDbHelper()->executeUninstall();
    }

    private function _uninstallConfigs()
    {
        if ($configTabs = EtsGeoDefine::getInstance()->getConfigTabs()) {
            foreach ($configTabs as $tab) {
                if (isset($configTab['subTabs']) && ($subTabs = $configTab['subTabs'])) {
                    foreach ($subTabs as $subTab) {
                        $this->_uninstallTabConfig($subTab);
                    }
                } else {
                    $this->_uninstallTabConfig($tab);
                }
            }
        }

        return true;
    }

    private function _uninstallTabConfig($TAB)
    {
        if (isset($TAB['is_conf'], $TAB['value'], $TAB['getMethod']) && $TAB['is_conf'] && method_exists(EtsGeoDefine::class, $TAB['getMethod']) && ($configs = EtsGeoDefine::getInstance()->{$TAB['getMethod']}())) {
            foreach ($configs as $key => $config) {
                if ($key != 'PS_GEOLOCATION_ENABLED'
                    && $key != 'PS_LANG_DEFAULT'
                    && $key != 'PS_CURRENCY_DEFAULT'
                    && $key != 'PS_COUNTRY_DEFAULT'
                ) {
                    Configuration::deleteByName($key);
                }
            }
        }
    }
    // </editor-fold>

    public function displayError($error)
    {
        if ($error) {
            $this->context->smarty->assign(['errors_blog' => $error]);

            return $this->display(__FILE__, 'errors.tpl');
        }

        return '';
    }

    public function displaySuccessMessage($msg, $title = false, $link = false)
    {
        $this->smarty->assign([
            'msg' => $msg,
            'title' => $title,
            'link' => $link,
        ]);
        if ($msg) {
            return $this->displayConfirmation($this->display(__FILE__, 'success_message.tpl'));
        }
    }

    public function showSwitchNav()
    {
        $idCountry = $this->detectUserCountry(true, true);
        $firstAdd = $this->context->customer->isLogged() ? Address::getFirstCustomerAddressId($this->context->customer->id) : false;
        if (in_array($this->getRemoteAddr(), ['localhost', '127.0.0.1', '::1'])
            || !$this->isGeoLiteCityAvailable()
            || !(int) Configuration::get('PS_GEOLOCATION_ENABLED')
            || !$this->getConfigService()->isShowSwitchNav()
            || (($geo_rule = Geo_rules::getRulesByIdCountry($idCountry)) && ($geo_rule['disable_geo'] || $geo_rule['block_user']))
        ) {
            return false;
        }
        // TODO : recheck this statement
        //        $checkDisable = false;
        //        if (Db::getInstance()->getValue('SELECT `id_country_rule` FROM `' . _DB_PREFIX_ . 'ets_geo_country_rule`')
        //            && !Db::getInstance()->getValue('SELECT `id_country_rule`
        //                         FROM `' . _DB_PREFIX_ . 'ets_geo_country_rule` as gcrl
        //                         JOIN `' . _DB_PREFIX_ . 'ets_geo_rule` grl ON (grl.`id_rule` = gcrl.`id_rule`)
        //                         WHERE `block_user`= 0 OR `disable_geo` = 0
        //                        GROUP BY id_country_rule ')) {
        //            $checkDisable = true;
        //        }
        if (($idCountry = $this->detectUserCountry(true)) && !$this->getConfigService()->isManualSelectCountry()) {
            $country = new Country($idCountry, $this->context->language->id);
        } else {
            $country = new Country($this->context->country->id, $this->context->language->id);
        }
        $this->smarty->assign(
            [
                'geo_country' => $country,
                'is17' => $this->is17,
                'hasFirstAddress' => $firstAdd,
            ]
        );

        return $this->display(__FILE__, 'switching_button.tpl');
    }

    public function hookDisplayNav()
    {
        return $this->showSwitchNav();
    }

    public function hookDisplayNav1()
    {
        return $this->showSwitchNav();
    }

    public function hookDisplayTop()
    {
        return $this->showSwitchNav();
    }

    /**
     * Copy from DispatcherCore
     *
     * @param string $requestUri To retrieve the request URI from it
     * @param bool $isMultiLanguageActivated
     * @param \Shop|null $shop
     *
     * @return array
     */
    private function buildRequestUri($requestUri, $isMultiLanguageActivated, Shop $shop = null)
    {
        // Decode raw request URI
        $requestUri = rawurldecode($requestUri);

        // Remove the shop base URI part from the request URI
        if (null !== $shop) {
            $requestUri = preg_replace(
                '#^' . preg_quote($shop->getBaseURI(), '#') . '#i',
                '/',
                $requestUri
            );
        }
        $hasIsoLang = false;
        $isoLang = null;
        // If there are several languages, set $_GET['isolang'] and remove the language part from the request URI
        if (
            $isMultiLanguageActivated &&
            preg_match('#^/([a-z]{2})(?:/.*)?$#', $requestUri, $matches)
        ) {
            $hasIsoLang = true;
            $isoLang = $matches[1];
            $requestUri = substr($requestUri, 3);
        }

        return ['uri' => $requestUri, 'hasIsoLang' => $hasIsoLang, 'lang' => $isoLang];
    }

    /**
     * Correct getRemoteAddr from class Tools, some time it doesnt return IP in X-FORWARDED-FOR Header.
     *
     * @return string
     */
    public function getRemoteAddr()
    {
        $ip = Tools::getRemoteAddr();
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                return $ips[0];
            }

            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $ip;
    }

    /**
     * Do redirect when user country ip matched a installed language
     */
    public function hookActionDispatcher()
    {
        if ($this->getConfigService()->isBackoffice()) {
            return;
        }
        if ($this->getConfigService()->isBotAndIgnored()) {
            return;
        }
        if (!$this->isGeoLiteCityAvailable() || !(int) Configuration::get('PS_GEOLOCATION_ENABLED')) {
            return;
        }
        if ($this->getConfigService()->isAjaxRequest()) {
            return;
        }
        $ctx = Context::getContext();
        $userCountry = $this->detectUserCountry();
        $uri = $this->buildRequestUri($_SERVER['REQUEST_URI'], Language::isMultiLanguageActivated(), $ctx->shop);
        /* Process rule by country */
        if ($userCountry && $rule = \Geo_rules::getRulesByIdCountry($userCountry->id)) {
            if ($rule['block_user']) {
                $blockLink = $ctx->link->getModuleLink($this->name, 'block');
                if (EtsGeoConfigService::strEndsWith($blockLink, $uri['uri'])) {
                    return;
                }
                Tools::redirect($blockLink);
                exit;
            }
            if ($rule['url_redirect'] !== '') {
                $redirectUrl = $rule['url_redirect'];
                // Check is redirect url & current url is same domain
                if (parse_url($redirectUrl, PHP_URL_HOST) === $ctx->shop->domain) {
                    // is same lang & end with same string
                    if ($uri['hasIsoLang'] && ($uri['lang'] === $ctx->language->iso_code) && EtsGeoConfigService::strEndsWith($redirectUrl, $uri['uri'])) {
                        return;
                    }
                }
                Tools::redirect($redirectUrl);
                exit;
            }
            if (($uri['uri'] !== '/' || $uri['hasIsoLang'] !== false) && $this->getConfigService()->isWorkingOnHomePageOnly()) {
                return;
            }
            if ($this->getConfigService()->isGeoRedirected()) {
                return;
            }
            if ($rule['disable_geo']) {
                return;
            }
            $this->detectedAddress($userCountry->iso_code, true);
//            $redirectUrl = Context::getContext()->shop->getBaseURL(true);
//            $redirectUrl = '/' . $_SERVER['REQUEST_URI'];
            $redirectUrl = $this->context->link->getLanguageLink($this->context->language->id);
            $doRedirect = false;
            if (Language::isMultiLanguageActivated() && $this->getConfigService()->isAutoSetLang() && isset($rule['lang_to_set']) && $rule['lang_to_set']) {
                $langLink = $this->context->link->publicGetLangLink($rule['lang_to_set']);
                $langLink = rtrim($langLink, '/');
                if (
                    ($ctx->language->id != $rule['lang_to_set']) ||
                    ($langLink !== '' && $uri['hasIsoLang'] && $uri['lang'] != $langLink) ||
                    ($uri['uri'] === '/' && $uri['hasIsoLang'] === false)
                ) {
                    $redirectUrl = $this->context->link->getLanguageLink($rule['lang_to_set']);
                    $doRedirect = true;
                }
            }
            if (Currency::isMultiCurrencyActivated() && $this->getConfigService()->isAutoSetCurrency() && isset($rule['currency_to_set']) && $rule['currency_to_set']) {
                if (
                    (
                        (!isset($ctx->cookie->id_currency) && null === $ctx->currency) ||
                        (isset($ctx->cookie->id_currency) && $ctx->cookie->id_currency != $rule['currency_to_set'])
                    ) && false === strpos($redirectUrl, 'SubmitCurrency=1')
                ) {
                    $redirectUrl .= false === strpos($redirectUrl, '?') ? '?' : '&';
                    $redirectUrl .= http_build_query(['SubmitCurrency' => '1', 'id_currency' => $rule['currency_to_set']]);
                    $doRedirect = true;
                }
            }

            if ($doRedirect) {
                header('X-Geo-Redirected: 1');
                $this->getConfigService()->setCookieCountryIso($userCountry->iso_code);
                $this->getConfigService()->setGeoRedirected(true);
                Tools::redirect($redirectUrl);
                exit;
            }
        }
    }

    public function hookDisplayHeader()
    {
        if ($this->getConfigService()->isBotAndIgnored()) {
            return '';
        }
        if (!$this->isGeoLiteCityAvailable() || !(int) Configuration::get('PS_GEOLOCATION_ENABLED')) {
            return;
        }

        $select_country = (!$this->context->customer->isLogged() || ($this->context->customer->isLogged() && !Address::getFirstCustomerAddressId($this->context->customer->id))) && $this->getConfigService()->isShowSwitchNav();
        $this->context->controller->addCSS($this->_path . 'views/css/front_geo.css');
        $frontJsAdded = false;
        $currentCtl = Tools::getValue('controller');
        $affectedControllers = ['index', 'product', 'category', 'supplier', 'manufacturer', 'search'];
        if (in_array($currentCtl, $affectedControllers, true)) {
            $this->getJsDefHelper()->addFo('currentController', $currentCtl);
            if (($idCountry = $this->detectUserCountry(true)) && ($rule = \Geo_rules::getRulesByIdCountry($idCountry))) {
                if (count($hiddenIds = \Geo_rules::formatCommaSeparatedId($rule['hidden_products'], true))) {
                    $disProductMsg = $this->getConfigService()->getMessage('disabledProduct', $this->context->language->id);
                    if (!$disProductMsg) {
                        $disProductMsg = $this->l('This product is not available.');
                    }
                    $this->getJsDefHelper()->addFo('isHasHiddenProduct', true)
                        ->addFo('hiddenProductIds', $hiddenIds)
                        ->addFo('transMsg.productNotAvail', $disProductMsg);
                    if ($currentCtl === 'product') {
                        $this->getJsDefHelper()->addFo('currentProductId', (int) Tools::getValue('id_product'));
                    }
                    $this->context->controller->addJS($this->_path . 'views/js/front_geo.js');
                    $frontJsAdded = true;
                }
            }
        }
        if (!$this->getConfigService()->isGeoAutoCountryProcessed() || $select_country) {
            if ($currentCtl != 'process') {
                $this->context->cookie->page_controller = trim($currentCtl);
                $this->getAlternativeLangsUrl();
            }
            if (!$frontJsAdded) {
                $this->context->controller->addJS($this->_path . 'views/js/front_geo.js');
            }
            if ($select_country) {
                $this->context->controller->addCSS($this->_path . 'views/css/chosen.min.css');
                $this->context->controller->addJS($this->_path . 'views/js/chosen.jquery.js');
            }

            Media::addJsDef([
                'popup_is_load' => $this->getConfigService()->isGeoAutoCountryProcessed(),
                'ajax_url' => $this->context->link->getModuleLink('ets_geolocation', 'process', [], Tools::usingSecureMode()),
                'page_controller' => $this->context->cookie->page_controller,
            ]);

            return $this->display(__FILE__, 'front_header.tpl');
        }
    }

    public function getAlternativeLangsUrl()
    {
        $alternativeLangs = [];
        $languages = Language::getLanguages(true, $this->context->shop->id);
        if ($languages < 2) {
            return $alternativeLangs;
        }
        foreach ($languages as $lang) {
            $alternativeLangs[$lang['iso_code']] = $this->context->link->getLanguageLink($lang['id_lang']);
        }
        $this->smarty->assign([
            'lang_links' => $alternativeLangs,
        ]);
    }

    public function getDateFormat()
    {
        $format = Context::getContext()->language->date_format_lite;
        $search = ['d', 'm', 'Y'];
        $replace = ['DD', 'MM', 'YYYY'];
        $format = str_replace($search, $replace, $format);

        return $format;
    }

    /**
     * @return string
     */
    public function getGeoLiteDbFilePath()
    {
        $dbFile = _PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_;
        if (!$this->is17) {
            $dbFile = _PS_GEOIP_DIR_ . 'GeoLite2-City.mmdb';
        }

        return $dbFile;
    }

    public function isGeoLiteCityAvailable()
    {
        if (@filemtime($this->getGeoLiteDbFilePath())) {
            return true;
        }

        return false;
    }

    public function renderList($params)
    {
        if (!$params) {
            return $this->_html;
        }
        $fields_list = $params['fields_list'];
        $this->initToolbar();
        $helper = new HelperList();
        $helper->title = $params['title'];
        $helper->table = 'ets_geo_rule';
        $helper->identifier = 'id_rule';
        if (version_compare(_PS_VERSION_, '1.6.1.0', '>=')) {
            $helper->_pagination = [10, 50, 100, 300, 1000];
            $helper->_default_pagination = 10;
        }
        $helper->_defaultOrderBy = isset($params['orderBy']) && $params['orderBy'] ? $params['orderBy'] : '';
        $helper->lang = true;
        $helper->explicitSelect = true;
        if (isset($params['orderBy']) && $params['orderBy'] == 'position') {
            $helper->position_identifier = 'position';
        }
        $this->processFilter($params);
        // Sort order
        $order_by = urldecode((string) Tools::getValue($this->list_id . 'Orderby'));
        if (!$order_by) {
            if ($this->context->cookie->{$this->list_id . 'Orderby'}) {
                $order_by = $this->context->cookie->{$this->list_id . 'Orderby'};
            } elseif ($helper->orderBy) {
                $order_by = $helper->orderBy;
            } else {
                $order_by = $helper->_defaultOrderBy;
            }
        }
        $order_way = urldecode((string) Tools::getValue($this->list_id . 'Orderway'));
        if (!$order_way) {
            if ($this->context->cookie->{$this->list_id . 'Orderway'}) {
                $order_way = $this->context->cookie->{$this->list_id . 'Orderway'};
            } elseif ($helper->orderWay) {
                $order_way = $helper->orderWay;
            } else {
                $order_way = $params['orderWay'];
            }
        }
        $fieldsList = EtsGeoDefine::getInstance()->getRuleFieldList();
        if (isset($fieldsList[$order_by]['filter_key'])) {
            $order_by = $fieldsList[$order_by]['filter_key'];
        }
        // Pagination.
        $limit = Tools::getValue($helper->table . '_pagination');
        if (!$limit) {
            if (isset($this->context->cookie->{$this->list_id . '_pagination'}) && $this->context->cookie->{$this->list_id . '_pagination'}) {
                $limit = $this->context->cookie->{$this->list_id . '_pagination'};
            } else {
                $limit = (version_compare(_PS_VERSION_, '1.6.1.0', '>=') ? $helper->_default_pagination : 20);
            }
        }
        if ($limit) {
            $this->context->cookie->{$this->list_id . '_pagination'} = $limit;
        } else {
            unset($this->context->cookie->{$this->list_id . '_pagination'});
        }
        $start = 0;
        if ((int) Tools::getValue('submitFilter' . $this->list_id)) {
            $start = ((int) Tools::getValue('submitFilter' . $this->list_id) - 1) * $limit;
        } elseif (isset($this->context->cookie->{$this->list_id . '_start'}) && Tools::isSubmit('export' . $helper->table)) {
            $start = $this->context->cookie->{$this->list_id . '_start'};
        }
        if ($start) {
            $this->context->cookie->{$this->list_id . '_start'} = $start;
        } elseif (isset($this->context->cookie->{$this->list_id . '_start'})) {
            unset($this->context->cookie->{$this->list_id . '_start'});
        }
        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way) || !is_numeric($start) || !is_numeric($limit)) {
            $this->errorMessage = Tools::displayError($this->l('get list params is not valid'));
        }
        $helper->orderBy = $order_by;
        if (preg_match('/[.!]/', $order_by)) {
            $order_by_split = preg_split('/[.!]/', $order_by);
            $order_by = bqSQL($order_by_split[0]) . '.`' . bqSQL($order_by_split[1]) . '`';
        } elseif ($order_by) {
            $order_by = '`' . bqSQL($order_by) . '`';
        }
        if (!$model = isset($params['model']) ? $params['model'] : false) {
            return false;
        }
        $helper->listTotal = $model::getLists([
            'nb' => true,
            'filter' => $this->_filter,
            'having' => $this->_filterHaving,
        ]);
        $list = $model::getLists([
            'filter' => $this->_filter,
            'having' => $this->_filterHaving,
            'start' => $start,
            'limit' => $limit,
            'sort' => $order_by . ' ' . Tools::strtoupper($order_way),
        ]);

        $helper->orderWay = Tools::strtoupper($order_way);
        $helper->toolbar_btn = $this->toolbar_btn;
        $helper->shopLinkType = '';
        $helper->row_hover = true;
        $helper->no_link = $params['no_link'];
        $helper->simple_header = false;
        $helper->actions = $params['actions'];
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = EtsGeoDefine::getInstance()->getAdminLink(['token' => false, 'control' => 'rules']);
        $helper->bulk_actions = $params['bulk_actions'] ? [
            'enableSelection' => [
                'text' => $this->l('Enable selection'),
                'icon' => 'icon-power-off text-success',
            ],
            'disableSelection' => [
                'text' => $this->l('Disable selection'),
                'icon' => 'icon-power-off text-danger',
            ],
            'divider' => [
                'text' => 'divider',
            ],
            'delete' => [
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ],
        ] : false;

        $this->_html .= $helper->generateList($list, $fields_list);
    }

    public function processFilter($params)
    {
        if (empty($params)) {
            return false;
        }
        if (!isset($this->list_id)) {
            $this->list_id = 'ets_geo_rule';
        }
        $prefix = null;
        // Filter memorization
        if (!empty($_POST) && isset($this->list_id)) {
            foreach ($_POST as $key => $value) {
                if ($value === '') {
                    unset($this->context->cookie->{$prefix . $key});
                } elseif (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key} = !is_array($value) ? $value : serialize($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : serialize($value);
                }
            }
        }
        if (!empty($_GET) && isset($this->list_id)) {
            foreach ($_GET as $key => $value) {
                if (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key} = !is_array($value) ? $value : serialize($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : serialize($value);
                }
                if (stripos($key, $this->list_id . 'Orderby') === 0 && Validate::isOrderBy($value)) {
                    if ($value === '' || $value == $params['orderBy']) {
                        unset($this->context->cookie->{$prefix . $key});
                    } else {
                        $this->context->cookie->{$prefix . $key} = $value;
                    }
                } elseif (stripos($key, $this->list_id . 'Orderway') === 0 && Validate::isOrderWay($value)) {
                    if ($value === '' || $value == $params['orderWay']) {
                        unset($this->context->cookie->{$prefix . $key});
                    } else {
                        $this->context->cookie->{$prefix . $key} = $value;
                    }
                }
            }
        }

        $filters = $this->context->cookie->getFamily($prefix . $this->list_id . 'Filter_');

        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */
            if ($value != null && !strncmp($key, $prefix . $this->list_id . 'Filter_', 7 + Tools::strlen($prefix . $this->list_id))) {
                $key = Tools::substr($key, 7 + Tools::strlen($prefix . $this->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmp_tab = explode('!', $key);
                $filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

                if ($field = $this->filterToField($key, $filter)) {
                    $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));
                    if (($type == 'date' || $type == 'datetime') && is_string($value)) {
                        $value = Tools::unSerialize($value);
                    }
                    $key = isset($tmp_tab[1]) ? $tmp_tab[0] . '.`' . $tmp_tab[1] . '`' : '`' . $tmp_tab[0] . '`';
                    $sql_filter = '';
                    /* Only for date filtering (from, to) */
                    if (is_array($value)) {
                        if (isset($value[0]) && !empty($value[0])) {
                            if (!Validate::isDate($value[0])) {
                                $this->_errors[] = Tools::displayError('The \'From\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sql_filter .= ' AND ' . pSQL($key) . ' >= \'' . pSQL(Tools::dateFrom($value[0])) . '\'';
                            }
                        }

                        if (isset($value[1]) && !empty($value[1])) {
                            if (!Validate::isDate($value[1])) {
                                $this->_errors[] = Tools::displayError('The \'To\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sql_filter .= ' AND ' . pSQL($key) . ' <= \'' . pSQL(Tools::dateTo($value[1])) . '\'';
                            }
                        }
                    } else {
                        $sql_filter .= ' AND ';
                        $check_key = ($key == 'id_rule' || $key == '`id_rule`');
                        $alias = 'rl';

                        if ($type == 'int' || $type == 'bool') {
                            $sql_filter .= (($check_key || $key == '`active`') ? $alias . '.' : '') . pSQL($key) . ' = ' . (int) $value . ' ';
                        } elseif ($type == 'decimal') {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = ' . (float) $value . ' ';
                        } elseif ($type == 'select') {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = \'' . pSQL($value) . '\' ';
                        } elseif ($type == 'price') {
                            $value = (float) str_replace(',', '.', $value);
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = ' . pSQL(trim($value)) . ' ';
                        } else {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' LIKE \'%' . pSQL(trim($value)) . '%\' ';
                        }
                    }
                    if (isset($field['havingFilter']) && $field['havingFilter']) {
                        $this->_filterHaving .= $sql_filter;
                    } else {
                        $this->_filter .= $sql_filter;
                    }
                }
            }
        }
    }

    public function initToolbar()
    {
        $this->toolbar_btn['new'] = [
            'short' => 'Add RULE',
            'href' => EtsGeoDefine::getInstance()->getAdminLink(['control' => 'rules', 'addets_geo_rule' => 1]),
            'desc' => $this->l('Add Rule'),
        ];
    }

    public function processResetFilters($list_id = 'ets_geo_rule')
    {
        if ($list_id === null) {
            $list_id = isset($this->list_id) ? $this->list_id : $this->name;
        }
        $prefix = null;
        $filters = $this->context->cookie->getFamily($prefix . $list_id . 'Filter_');
        if (!empty($filters)) {
            foreach ($filters as $cookie_key => $filter) {
                if (strncmp($cookie_key, $prefix . $list_id . 'Filter_', 7 + Tools::strlen($prefix . $list_id)) == 0) {
                    $key = Tools::substr($cookie_key, 7 + Tools::strlen($prefix . $list_id));
                    $fieldsList = EtsGeoDefine::getInstance()->getRuleFieldList();
                    if (is_array($fieldsList) && array_key_exists($key, $fieldsList)) {
                        $this->context->cookie->$cookie_key = null;
                    }
                    unset($this->context->cookie->$cookie_key, $filter);
                }
            }
        }

        if (isset($this->context->cookie->{'submitFilter' . $list_id})) {
            unset($this->context->cookie->{'submitFilter' . $list_id});
        }
        if (isset($this->context->cookie->{$prefix . $list_id . 'Orderby'})) {
            unset($this->context->cookie->{$prefix . $list_id . 'Orderby'});
        }
        if (isset($this->context->cookie->{$prefix . $list_id . 'Orderway'})) {
            unset($this->context->cookie->{$prefix . $list_id . 'Orderway'});
        }

        $_POST = [];
    }

    public function detectedAddress($countryIsoCode, $autoAdd = false)
    {
        if ($this->getConfigService()->isAutoSetTax() && $countryIsoCode && isset($this->context->cart) && !$this->context->cart->id_customer && ($id_address = EtsGeoCountryHelper::getAutoDetectedIdAddressByIso($countryIsoCode, $autoAdd))) {
            $this->context->cart->id_address_invoice = $this->context->cart->id_address_delivery = $id_address;
            $this->context->cart->update();
//            $sql = sprintf('UPDATE `%scart_product` SET `id_address_delivery` = %d WHERE `id_cart` = %d', _DB_PREFIX_, $id_address, $this->context->cart->id);
//            $this->getDbHelper()->getDb()->execute($sql);
        }
    }

    /**
     * @param string|null $ip
     *
     * @return \GeoIp2\Model\City|int|null
     *
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     * @throws \PrestaShopException
     */
    public function detectAddressFromIp($ip = null)
    {
        if (!$this->isGeoLiteCityAvailable()) {
            throw new \PrestaShopException($this->l('GEO IP Lite not available. Please install first.'));
        }
        if ($ip === null) {
            $ip = $this->getRemoteAddr();
        }

        static $record = null;
        if (!$record) {
            $reader = new GeoIp2\Database\Reader($this->getGeoLiteDbFilePath());

            try {
                $record = $reader->city($ip);
            } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                $record = null;
            }
        }

        return $record;
    }

    /**
     * Detect user country. If user has a address using country from address, if not using Geo to detect from user's IP
     *
     * @param bool $returnOnlyId
     * @param bool $loggedUserOnly
     *
     * @return \Country|int|null
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function detectUserCountry($returnOnlyId = false, $loggedUserOnly = false)
    {
        if ($loggedUserOnly && !$this->context->customer->isLogged()) {
            return null;
        }
        $firstAddress = null;
        if ($this->context->customer && $this->context->customer->isLogged()) {
            $firstAddress = Address::getFirstCustomerAddressId($this->context->customer->id);
            if ($firstAddress) {
                $idCountry = \EtsGeoCountryHelper::getIdByAddressId($firstAddress);
                if ($idCountry !== null) {
                    if ($returnOnlyId) {
                        return (int) $idCountry;
                    }

                    return new \Country($idCountry);
                }
            }
        }
        try {
            if (($city = $this->detectAddressFromIp($this->getRemoteAddr())) && isset($city->country->isoCode)) {
                $idCountry = \EtsGeoCountryHelper::getIdByIso($city->country->isoCode);
                if ($returnOnlyId) {
                    return (int) $idCountry;
                }

                return $idCountry ? new \Country($idCountry) : null;
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return null;
    }

    /**
     * @param int|null $idShop
     * @param bool|null $ssl
     * @param bool $relativeProtocol
     *
     * @return string
     */
    public function getAdminBaseLink($idShop = null, $ssl = null, $relativeProtocol = false)
    {
        if (null === $ssl) {
            $ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
        }

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            if (null === $idShop) {
                $idShop = $this->getMatchingUrlShopId();
            }

            // Use the matching shop if present, or fallback on the default one
            if (null !== $idShop) {
                $shop = new Shop($idShop);
            } else {
                $shop = new Shop(Configuration::get('PS_SHOP_DEFAULT'));
            }
        } else {
            $shop = Context::getContext()->shop;
        }

        if ($relativeProtocol) {
            $base = '//' . ($ssl && $this->ssl_enable ? $shop->domain_ssl : $shop->domain);
        } else {
            $base = (($ssl && $this->ssl_enable) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
        }

        return $base . $shop->getBaseURI();
    }

    /**
     * Search for a shop whose domain matches the current url.
     *
     * @return int|null
     */
    public function getMatchingUrlShopId()
    {
        if (null === $this->urlShopId) {
            $this->urlShopId = $this->getDbHelper()->getMatchingUrlShopId();
        }

        return $this->urlShopId;
    }

    public function getGeoIDLang($id_country = 0)
    {
        if ($id_country) {
            $iso_code = EtsGeoCountryHelper::getIsoById($id_country);
        } else {
            $iso_code = $this->context->cookie->iso_code_country;
        }
        if ($iso_code && ($lang = EtsGeoCountryHelper::getLangByIso($iso_code))) {
            return $lang->id;
        }

        return 0;
    }

    public function checkThisCountryEnabled($idCountry = false)
    {
        if (!$idCountry) {
            $idCountry = $this->context->country->id;
        }
        $country = new \Country($idCountry);

        return (bool) $country->active;
    }

    /**
     * Serve AJAX requests. DIE json content if a request matched
     */
    private function _processAjaxRequest()
    {
        if (($query = Tools::getValue('q')) && Validate::isCleanHtml($query)) {
            $limit = Tools::getValue('limit') ?: 20;
            $offset = 0;
            if (($page = Tools::getValue('page')) && Validate::isUnsignedInt($page)) {
                $offset += ($page * $limit) - $limit;
            }
            $this->getProductHelper()->searchProducts($query, true, $limit, $offset);
        }
        if (Tools::isSubmit('product_type') && ($ids = Tools::getValue('ids')) && Validate::isCleanHtml($ids)) {
            $rule = null;
            if (($geoId = Tools::getValue('ruleId')) && Validate::isUnsignedInt($geoId)) {
                $rule = new \Geo_rules($geoId);
            }
            exit(json_encode([
                'html' => $this->hookDisplayGeoRuleHiddenProductList(['ids' => $ids, 'rule' => $rule]),
            ]));
        }
        if (Tools::isSubmit('geoRuleRemoveProduct')) {
            $rule = null;
            if (($geoId = Tools::getValue('ruleId')) && Validate::isUnsignedInt($geoId)) {
                $rule = new \Geo_rules($geoId);
            }
            if (!$rule) {
                exit(json_encode(['ok' => false]));
            }
            if (($pId = Tools::getValue('productId')) && Validate::isUnsignedInt($pId)) {
                $rs = $rule->removeHiddenProductById($pId)->save();
                if ($rs) {
                    exit(json_encode(['ok' => true, 'ids' => $rule->hidden_products, 'html' => $this->hookDisplayGeoRuleHiddenProductList(['rule' => $rule])]));
                }
            }
            exit(json_encode(['ok' => false]));
        }
        if (Tools::isSubmit('submitUpdateToken')) {
            $cronjobToken = Tools::passwdGen(12);
            $cacheId = $this->_getCacheId(['renderCronjob'], true);
            $this->_clearCache('*', $cacheId);
            Configuration::updateGlobalValue('ETS_GEO_CRONJOB_TOKEN', $cronjobToken);
            header('Content-Type: application/json');
            exit(json_encode(['value' => $cronjobToken, 'success' => true]));
        }
    }

    /**
     * @return \EtsGeoConfigService
     */
    public function getConfigService()
    {
        if (!$this->configService instanceof \EtsGeoConfigService) {
            $this->configService = \EtsGeoConfigService::getInstance();
        }

        return $this->configService;
    }

    /**
     * @return \EtsProductHelper
     */
    public function getProductHelper()
    {
        if (!$this->productHelper instanceof \EtsProductHelper) {
            $this->productHelper = \EtsProductHelper::getInstance();
        }

        return $this->productHelper;
    }

    /**
     * @return \EtsGeoJsDefHelper
     */
    public function getJsDefHelper()
    {
        if (!$this->jsDefHelper instanceof \EtsGeoJsDefHelper) {
            $this->jsDefHelper = \EtsGeoJsDefHelper::getInstance();
        }

        return $this->jsDefHelper;
    }

    /**
     * @return \EtsGeoDbHelper
     */
    public function getDbHelper()
    {
        if (!$this->dbHelper instanceof \EtsGeoDbHelper) {
            $this->dbHelper = \EtsGeoDbHelper::getInstance();
        }

        return $this->dbHelper;
    }

    /**
     * @param array|null $params
     * @param bool $onlySuffix
     *
     * @return string
     */
    public function _getCacheId($params = null, $onlySuffix = false)
    {
        $cacheId = $this->getCacheId($this->name);
        $cacheId = str_replace($this->name, '', $cacheId);
        $suffix = '';
        if ($params) {
            $depthLimit = 8;
            $maxLengthLimit = 255;
            if (is_array($params)) {
                $params = EtsGeoArrayHelper::dot($params);
                foreach ($params as $key => $value) {
                    if (is_numeric($key)) {
                        $v = (string) $value;
                    } else {
                        $v = sprintf('%s|%s', $key, $value);
                    }
                    $suffix .= '|' . $v;
                }
            } else {
                $suffix .= '|' . $params;
            }
            if (strlen($suffix) > $maxLengthLimit || count(explode('|', $suffix)) > $depthLimit) {
                $suffix = '|' . md5($suffix);
            }
        }
        if ($onlySuffix) {
            return $this->name . $suffix;
        }
        if(defined('_PS_ADMIN_DIR_') && isset($this->context->employee) && isset($this->context->employee->id))
        {
            $cacheId .='|'.$this->context->employee->id;
        }
        return $this->name . $suffix . $cacheId;
    }

    /**
     * @param string|null $template
     * @param string|null $cache_id
     * @param string|null $compile_id
     *
     * @return static
     */
    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        if (null === $cache_id) {
            $cache_id = $this->name;
        }
        $template = '*' === $template ? null : $template;
        Tools::clearCache(Context::getContext()->smarty, $template ? $this->getTemplatePath($template) : null, $cache_id, $compile_id);

        return $this;
    }

    /**
     * @param string $template
     *
     * @return string|null
     */
    public function getTemplatePath($template)
    {
        if (@file_exists($template)) {
            return $template;
        }

        return parent::getTemplatePath($template);
    }

    // <editor-fold desc="ModuleCore Missing methods on 1.6">
    /**
     * Helper displaying warning message(s).
     *
     * @param string|array $warning
     *
     * @return string
     */
    public function displayWarning($warning)
    {
        $output = '
        <div class="bootstrap">
        <div class="module_warning alert alert-warning" >
            <button type="button" class="close" data-dismiss="alert">&times;</button>';

        if (is_array($warning)) {
            $output .= '<ul>';
            foreach ($warning as $msg) {
                $output .= '<li>' . $msg . '</li>';
            }
            $output .= '</ul>';
        } else {
            $output .= $warning;
        }

        // Close div openned previously
        $output .= '</div></div>';

        return $output;
    }
    // </editor-fold>
}
