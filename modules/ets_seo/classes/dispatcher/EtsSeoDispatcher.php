<?php
/**
 * 2007-2021 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2021 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
{
    exit;
}

class EtsSeoDispatcher
{
    public $default_routes;
    public $old_routes;
    public $config_schema;
    public $request_uri = null;
    public $listDefaultControllers;
    public static $instance = null;
    public $routes = null;
    const REWRITE_PATTERN = '[_a-zA-Z0-9\x{0600}-\x{06FF}\pL\pS-]+';
    const OLD_REWRITE_PATTERN = '[_a-zA-Z0-9\x{0600}-\x{06FF}\pL\pS-]*';

    public function __construct()
    {
        $this->default_routes = array(
            'category_rule' => array(
                'controller' => 'category',
                'rule' => '{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_product'),
                    'rewrite' => array('regexp' => self::REWRITE_PATTERN, 'param' => 'rewrite'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'supplier_rule' => array(
                'controller' => 'supplier',
                'rule' => 'supplier/{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_supplier'),
                    'rewrite' => array('regexp' => self::REWRITE_PATTERN, 'param' => 'rewrite'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'manufacturer_rule' => array(
                'controller' => 'manufacturer',
                'rule' => 'manufacturer/{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_manufacturer'),
                    'rewrite' => array('regexp' => self::REWRITE_PATTERN, 'param' => 'rewrite'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'cms_rule' => array(
                'controller' => 'cms',
                'rule' => 'content/{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_cms'),
                    'rewrite' => array('regexp' => self::REWRITE_PATTERN, 'param' => 'rewrite'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'cms_category_rule' => array(
                'controller' => 'cms',
                'rule' => 'content/category/{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    'rewrite' => array('regexp' => self::REWRITE_PATTERN, 'param' => 'rewrite'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'module' => array(
                'controller' => null,
                'rule' => 'module/{module}{/:controller}',
                'keywords' => array(
                    'module' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
                    'controller' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'),
                ),
                'params' => array(
                    'fc' => 'module',
                ),
            ),
            'product_rule' => array(
                'controller' => 'product',
                'rule' => '{category}/{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_product'),
                    'id_product_attribute' => array('regexp' => '[0-9]+', 'param' => 'id_product_attribute'),
                    'rewrite' => array('regexp' => self::REWRITE_PATTERN, 'param' => 'rewrite'),
                    'ean13' => array('regexp' => '[0-9\pL]*'),
                    'category' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'category'),
                    'categories' => array('regexp' => '[/_a-zA-Z0-9-\pL]*'),
                    'reference' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'manufacturer' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'supplier' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'price' => array('regexp' => '[0-9\.,]*'),
                    'tags' => array('regexp' => '[a-zA-Z0-9-\pL]*'),
                ),
            ),

            'layered_rule' => array(
                'controller' => 'category',
                'rule' => '{rewrite}/filter/{selected_filters}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_category'),

                    'selected_filters' => array('regexp' => '.*', 'param' => 'selected_filters'),
                    'rewrite' => array('regexp' => self::REWRITE_PATTERN, 'param' => 'rewrite'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
        );

        $this->old_routes = array(
            'category_rule' => array(
                'controller' => 'category',
                'rule' => '{id}-{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    'rewrite' => array('regexp' => self::OLD_REWRITE_PATTERN),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'supplier_rule' => array(
                'controller' => 'supplier',
                'rule' => '{id}__{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_supplier'),
                    'rewrite' => array('regexp' => self::OLD_REWRITE_PATTERN),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'manufacturer_rule' => array(
                'controller' => 'manufacturer',
                'rule' => '{id}_{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_manufacturer'),
                    'rewrite' => array('regexp' => self::OLD_REWRITE_PATTERN),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'cms_rule' => array(
                'controller' => 'cms',
                'rule' => 'content/{id}-{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_cms'),
                    'rewrite' => array('regexp' => self::OLD_REWRITE_PATTERN),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'cms_category_rule' => array(
                'controller' => 'cms',
                'rule' => 'content/category/{id}-{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_cms_category'),
                    'rewrite' => array('regexp' => self::OLD_REWRITE_PATTERN),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
            'module' => array(
                'controller' => null,
                'rule' => 'module/{module}{/:controller}',
                'keywords' => array(
                    'module' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
                    'controller' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'),
                ),
                'params' => array(
                    'fc' => 'module',
                ),
            ),
            'product_rule' => array(
                'controller' => 'product',
                'rule' => '{category:/}{id}{-:id_product_attribute}-{rewrite}{-:ean13}.html',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_product'),
                    'id_product_attribute' => array('regexp' => '[0-9]+', 'param' => 'id_product_attribute'),
                    'rewrite' => array('regexp' => self::OLD_REWRITE_PATTERN, 'param' => 'rewrite'),
                    'ean13' => array('regexp' => '[0-9\pL]*'),
                    'category' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'categories' => array('regexp' => '[/_a-zA-Z0-9-\pL]*'),
                    'reference' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'manufacturer' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'supplier' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'price' => array('regexp' => '[0-9\.,]*'),
                    'tags' => array('regexp' => '[a-zA-Z0-9-\pL]*'),
                ),
            ),
            /* Must be after the product and category rules in order to avoid conflict */
            'layered_rule' => array(
                'controller' => 'category',
                'rule' => '{id}-{rewrite}{/:selected_filters}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    /* Selected filters is used by the module blocklayered */
                    'selected_filters' => array('regexp' => '.*', 'param' => 'selected_filters'),
                    'rewrite' => array('regexp' => self::OLD_REWRITE_PATTERN),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
            ),
        );

        $this->config_schema = array(
            'category_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_CATEGORY_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_CATEGORY_RULE',
                'name' => 'ETS_SEO_URL_CATEGORY_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_CATEGORY_RULE',
            ),
            'supplier_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_SUPPLIER_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_SUPPLIER_RULE',
                'name' => 'ETS_SEO_URL_SUPPLIER_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_SUPPLIER_RULE',
            ),
            'manufacturer_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_MANUF_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_MANUF_RULE',
                'name' => 'ETS_SEO_URL_MANUF_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_MANUF_RULE',
            ),
            'cms_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_CMS_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_CMS_RULE',
                'name' => 'ETS_SEO_URL_CMS_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_CMS_RULE',
            ),
            'cms_category_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_CMS_CATEGORY_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_CMS_CATEGORY_RULE',
                'name' => 'ETS_SEO_URL_CMS_CATEGORY_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_CMS_CATEGORY_RULE',
            ),
            'module' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_MODULE_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_MODULE_RULE',
                'name' => 'ETS_SEO_URL_MODULE_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_MODULE_RULE',
            ),
            'product_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_PRODUCT_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_PRODUCT_RULE',
                'name' => 'ETS_SEO_URL_PRODUCT_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_PRODUCT_RULE',
            ),
            'layered_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_LAYERED_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_LAYERED_RULE',
                'name' => 'ETS_SEO_URL_LAYERED_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_LAYERED_RULE',
            ),
        );

        $this->listDefaultControllers = array(
            'product',
            'category',
            'cms',
            'cms_category',
            'manufacturer',
            'supplier',
        );
    }

    public static function getDispatcher()
    {
        if (!isset(self::$instance)) {
            self::$instance = new EtsSeoDispatcher();
        }
        return self::$instance;
    }

    protected function changeOldToNewUlr($type)
    {
        $url = $_SERVER['REQUEST_URI'];
        switch ($type) {
            case 'product':
                $pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
                $key = 'product_rule';
                $contain = '/content/';
                $rule = '{categories:/}{rewrite}';
                break;

            case 'category':
                $pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
                $key = 'category_rule';
                $contain = '';
                $rule = '{rewrite}';
                break;

            case 'supplier':
                $pattern = '/.*?([0-9]+)\_\_([_a-zA-Z0-9-\pL]*)/';
                $key = 'supplier_rule';
                $contain = '';
                $rule = '{rewrite}';
                break;

            case 'manufacturer':
                $pattern = '/.*?([0-9]+)\_([_a-zA-Z0-9-\pL]*)/';
                $key = 'manufacturer_rule';
                $contain = '';
                $rule = '{rewrite}';
                break;

            case 'cms':
                $pattern = '/.*?content\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
                $key = 'cms_rule';
                $contain = '';
                $rule = 'content/{rewrite}';
                break;

            case 'cms_category':
                $pattern = '/.*?content\/category\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
                $key = 'cms_category_rule';
                $contain = '/content/category/';
                $rule = 'content/category/{rewrite}';
                break;

        }
        preg_match($pattern, $url, $result);
        if ($result) {
            if (!$contain || ($contain && strstr($url, $contain))) {
                $this->default_routes[$key]['rule'] = $rule;
            }
        }
    }

    public function enabledRemoveIdInUrl()
    {
        return (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')
            && !defined('_PS_ADMIN_DIR_')
            && (int)Configuration::get('PS_REWRITING_SETTINGS');
    }

    public function setMetaPage()
    {
        $uriArray = explode('/', $this->request_uri);
        $metaLinkRewrite = str_replace('.html', '', $uriArray[count($uriArray) - 1]);
        if ($idMeta = $this->getMetaIdBySlug($metaLinkRewrite)) {
            $_GET['id_meta'] = $idMeta;
        }
    }

    public function getLinkRewrite()
    {
        if (($rewrite = Tools::getValue('rewrite')) && (is_array($rewrite) || (!is_array($rewrite) && Validate::isCleanHtml($rewrite)))) {
            return $rewrite;
        }

        return null;
    }


    public function checkConfigRewrite($config, $default, $use_2_param = false)
    {
        if ($use_2_param) {
            $arrrayUrl = explode('/', $config);
            if (count($arrrayUrl) > 2) {
                $prefix = $config ? $arrrayUrl[0] . '/' . $arrrayUrl[1] : '';
            } else {
                $prefix = $config ? $arrrayUrl[0] : '';
            }

        } else {
            $prefix = $config ? explode('/', $config)[0] : '';
        }
        if (($prefix && preg_match("/" . trim(Tools::jsonEncode($prefix), '"') . "/", $this->request_uri))
            || (!$prefix && preg_match("/" . trim(Tools::jsonEncode($default), '"') . "/", $this->request_uri))) {
            return true;
        }
        return false;
    }


    public function getMetaPageId($link_rewrite = '', $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        if (strpos($link_rewrite, '?')) {
            $link_rewrite = explode('?', $link_rewrite)[0];
        }
        return (int)Db::getInstance()->getValue("SELECT `id_meta` 
                                            FROM `" . _DB_PREFIX_ . "meta_lang` 
                                            WHERE `url_rewrite`='" . pSQL($link_rewrite) . "' AND id_shop=" . (int)$id_shop . " AND id_lang=" . (int)$id_lang);
    }

    public function getMetaPageById($id)
    {
        return Db::getInstance()->getValue("SELECT `page` FROM `" . _DB_PREFIX_ . "meta` WHERE `id_meta`=" . (int)$id);
    }

    public function getProductIdBySlug($link_rewrite, $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        $idProduct = (int)Db::getInstance()->getValue("SELECT pl.`id_product` 
                                            FROM `" . _DB_PREFIX_ . "product_lang` pl
                                            JOIN `" . _DB_PREFIX_ . "product` p ON (p.`id_product` = pl.`id_product`)
                                            JOIN `" . _DB_PREFIX_ . "product_shop` ps ON (ps.`id_product` = pl.`id_product` AND ps.`id_shop` = " . (int)$id_shop . ")
                                            WHERE pl.`link_rewrite`='" . pSQL($link_rewrite) . "' AND pl.id_shop=" . (int)$id_shop . " AND pl.id_lang=" . (int)$id_lang);
        if(!$idProduct){
            $idProduct = (int)Db::getInstance()->getValue("SELECT pl.`id_product` 
                                            FROM `" . _DB_PREFIX_ . "product_lang` pl
                                            JOIN `" . _DB_PREFIX_ . "product` p ON (p.`id_product` = pl.`id_product`)
                                            JOIN `" . _DB_PREFIX_ . "product_shop` ps ON (ps.`id_product` = pl.`id_product` AND ps.`id_shop` = " . (int)$id_shop . ")
                                            WHERE pl.`link_rewrite`='" . pSQL($link_rewrite) . "' AND pl.id_shop=" . (int)$id_shop );
            if(isset(${'_GET'}['category'])){
                unset($_GET['category']);
            }
        }
        return $idProduct;
    }

    public function getCategoryIdBySlug($link_rewrite, $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        return (int)Db::getInstance()->getValue("SELECT `id_category` 
                                            FROM `" . _DB_PREFIX_ . "category_lang` 
                                            WHERE `link_rewrite`='" . pSQL($link_rewrite) . "' AND id_shop=" . (int)$id_shop . " AND id_lang=" . (int)$id_lang);
    }

    public function getCmsIdBySlug($link_rewrite, $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        return (int)Db::getInstance()->getValue("SELECT `id_cms` 
                                            FROM `" . _DB_PREFIX_ . "cms_lang` 
                                            WHERE `link_rewrite`='" . pSQL($link_rewrite) . "' AND id_shop=" . (int)$id_shop . " AND id_lang=" . (int)$id_lang);
    }

    public function getMetaIdBySlug($link_rewrite, $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        return (int)Db::getInstance()->getValue("SELECT `id_meta` 
                                            FROM `" . _DB_PREFIX_ . "meta_lang` 
                                            WHERE `url_rewrite`='" . pSQL($link_rewrite) . "' AND id_shop=" . (int)$id_shop . " AND id_lang=" . (int)$id_lang);
    }

    public function getManufIdBySlug($link_rewrite)
    {
        $name = str_replace('-', ' ', $link_rewrite);
        return (int)Db::getInstance()->getValue("SELECT `id_manufacturer` 
                                            FROM `" . _DB_PREFIX_ . "manufacturer` 
                                            WHERE REPLACE(REPLACE(REPLACE(REPLACE(`name`, '!', ''), '.', ''), '\'', ' '), '"."\""."', '') LIKE '%" . pSQL($name) . "%'");
    }


    public function getSupplierIdBySlug($link_rewrite)
    {

        $name = str_replace('-', ' ', $link_rewrite);
        return (int)Db::getInstance()->getValue("SELECT `id_supplier` 
                                            FROM `" . _DB_PREFIX_ . "supplier` 
                                            WHERE REPLACE(REPLACE(REPLACE(REPLACE(`name`, '!', ''), '.', ''), '\'', ' '), '"."\""."', '') LIKE '%" . pSQL($name) . "%'");
    }


    public function getCmsCategoryIdBySlug($link_rewrite, $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        return (int)Db::getInstance()->getValue("SELECT `id_cms_category` 
                                            FROM `" . _DB_PREFIX_ . "cms_category_lang` 
                                            WHERE `link_rewrite`='" . pSQL($link_rewrite) . "' AND id_shop=" . (int)$id_shop . " AND id_lang=" . (int)$id_lang);
    }


    public function getPageModuleBySlug($link_rewrite, $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        return (string)Db::getInstance()->getValue("SELECT m.`page` 
            FROM `" . _DB_PREFIX_ . "meta` as `m`
            LEFT JOIN `" . _DB_PREFIX_ . "meta_lang` ml ON (m.`id_meta`=ml.`id_meta`)
            WHERE ml.`url_rewrite`='" . pSQL($link_rewrite) . "' AND m.`page` LIKE '%module%' 
            AND ml.`id_shop`=" . (int)$id_shop . " AND ml.`id_lang`=" . (int)$id_lang);
    }

    public function setOldDefaultRoute($type = 'nearest')
    {
        $is178 = version_compare(_PS_VERSION_, '1.7.8.0', '>=');
        $urlSchemaConfigs = $this->config_schema;
        $routes = $this->old_routes;
        $errors = false;
        foreach ($routes as $k => &$route) {
            if ($is178 && $k == 'layered_rule'){
                continue;
            }
            if ($type == 'root') {
                $config = Configuration::get($urlSchemaConfigs[$k]['root_name']);
                if (isset($urlSchemaConfigs[$k]['root_name']) && $config && ($k == 'module' || ($k != 'module' && preg_match('/\{id\}/', $config)))) {
                    $route['rule'] = Configuration::get($urlSchemaConfigs[$k]['root_name']);
                } else {
                    $errors = true;
                    break;
                }

            } elseif ($type == 'old') {
                $config = Configuration::get($urlSchemaConfigs[$k]['old_name']);
                if (isset($urlSchemaConfigs[$k]['old_name']) && ($k == 'module' || ($k != 'module' && preg_match('/\{id\}/', $config)))) {
                    $route['rule'] = Configuration::get($urlSchemaConfigs[$k]['old_name']);
                } else {
                    $errors = true;
                    break;
                }
            } else {
                $config = Configuration::get($urlSchemaConfigs[$k]['name']);
                if (isset($urlSchemaConfigs[$k]['name']) && ($k == 'module' || ($k != 'module' && preg_match('/\{id\}/', $config)))) {
                    $route['rule'] = Configuration::get($urlSchemaConfigs[$k]['name']);
                } else {
                    $errors = true;
                    break;
                }
            }
        }

        if ($errors) {
            return false;
        }

        $this->old_routes = $routes;
        return true;
    }

    public function setDefaultRoute()
    {
        //Copy from "ets_seo/defines.php" if has change please update this
        $urlSchemaConfigs = $this->config_schema;
        foreach ($this->default_routes as $k => &$route) {
            if ($config = Configuration::get($urlSchemaConfigs[$k]['no_id'])) {
                $route[$k] = $config;
            }
        }
    }

    public function getDefaultRouteNoId()
    {
        $routes = $this->default_routes;
        foreach ($this->default_routes as $key => $route) {
            if($route){}
            if ($config = Configuration::get('PS_ROUTE_' . $key)) {
                $routes[$key]['rule'] = $config;
            }
        }
        return $routes;
    }

    public function getController($dispatcherCore, $controller, $moduleDefined)
    {
        if($moduleDefined)
        {
            //
        }
        $linkRewrite = $this->getLinkRewrite();
        $success = false;
        $token = ($token = Tools::getValue('token')) && Validate::isCleanHtml($token) ? $token : '';
        switch ($controller) {
            case 'product':
                if (!(int)Tools::getValue('id_product') && $linkRewrite) {
                    if ($idProduct = $this->getProductIdBySlug($linkRewrite)) {
                        $_GET['id_product'] = $idProduct;
                        $success = true;
                    }

                }
                elseif(!Tools::getValue('editmp_front_products') && isset(${'_GET'}['id_product']) && (int)${'_GET'}['id_product'])
                {
                    $success = true;
                }
                elseif(!Tools::getValue('editmp_front_products') && isset(${'_POST'}['id_product']) && (int)${'_POST'}['id_product'] && !$token)
                {
                    $_GET['id_product'] = ${'_POST'}['id_product'];
                    $success = true;
                }
                break;
            case 'category':
                if (!(int)Tools::getValue('id_category') && $linkRewrite) {
                    if ($idCategory = $this->getCategoryIdBySlug($linkRewrite)) {
                        $_GET['id_category'] = $idCategory;
                        $success = true;
                    }
                }
                elseif(isset(${'_GET'}['id_category']) && (int)${'_GET'}['id_category'] && !$token)
                {
                    $success = true;
                }
                elseif(isset(${'_POST'}['id_category']) && (int)${'_POST'}['id_category'] && !$token)
                {
                    $_GET['id_category'] = ${'_POST'}['id_category'];
                    $success = true;
                }
                break;
            case 'cms':
                if (!(int)Tools::getValue('id_cms') && $linkRewrite) {
                    if ($idCms = $this->getCmsIdBySlug($linkRewrite)) {
                        $_GET['id_cms'] = $idCms;
                        $success = true;
                    }
                }
                elseif(isset(${'_GET'}['id_cms']) && (int)${'_GET'}['id_cms'])
                {
                    $success = true;
                }
                elseif(isset(${'_POST'}['id_cms']) && (int)${'_POST'}['id_cms'] && !$token)
                {
                    $_GET['id_cms'] = ${'_POST'}['id_cms'];
                    $success = true;
                }
                if(!$success){
                    if (!(int)Tools::getValue('id_cms_category') && $linkRewrite) {
                        if ($idCmsCategory = $this->getCmsCategoryIdBySlug($linkRewrite)) {
                            $_GET['id_cms_category'] = $idCmsCategory;
                            $success = true;
                        }
                    }
                    elseif(isset(${'_GET'}['id_cms_category']) && (int)${'_GET'}['id_cms_category']){
                        $success = true;
                    }
                    elseif(isset(${'_POST'}['id_cms_category']) && (int)${'_POST'}['id_cms_category'] && !$token)
                    {
                        $_GET['id_cms_category'] = ${'_POST'}['id_cms_category'];
                        $success = true;
                    }
                }
                break;
            case 'cms_category':
                if (!(int)Tools::getValue('id_cms_category') && $linkRewrite) {
                    if ($idCmsCategory = $this->getCmsCategoryIdBySlug($linkRewrite)) {
                        $_GET['id_cms_category'] = $idCmsCategory;
                        $success = true;
                    }
                }
                elseif(isset(${'_GET'}['id_cms_category']) && (int)${'_GET'}['id_cms_category']){
                    $success = true;
                }
                elseif(isset(${'_POST'}['id_cms_category']) && (int)${'_POST'}['id_cms_category'] && !$token)
                {
                    $_GET['id_cms_category'] = ${'_POST'}['id_cms_category'];
                    $success = true;
                }
                break;
            case 'manufacturer':
                if (!(int)Tools::getValue('id_manufacturer') && $linkRewrite) {
                    if ($idManufacturer = $this->getManufIdBySlug($linkRewrite)) {
                        $_GET['id_manufacturer'] = $idManufacturer;
                        $success = true;
                    }
                }

                elseif(isset(${'_GET'}['id_manufacturer']) && (int)${'_GET'}['id_manufacturer']){
                    $success = true;
                }
                elseif(isset(${'_POST'}['id_manufacturer']) && (int)${'_POST'}['id_manufacturer'] && !$token)
                {
                    $_GET['id_manufacturer'] = ${'_POST'}['id_manufacturer'];
                    $success = true;
                }
                break;
            case 'supplier':
                if (!(int)Tools::getValue('id_supplier') && $linkRewrite) {
                    if ($idSupplier = $this->getSupplierIdBySlug($linkRewrite)) {
                        $_GET['id_supplier'] = $idSupplier;
                        $success = true;
                    }
                }
                elseif(isset(${'_GET'}['id_supplier']) && (int)${'_GET'}['id_supplier']){
                    $success = true;
                }
                elseif(isset(${'_POST'}['id_supplier']) && (int)${'_POST'}['id_supplier'] && !$token)
                {
                    $_GET['id_supplier'] = ${'_POST'}['id_supplier'];
                    $success = true;
                }
                break;
            default:
                break;
        }
        $context = Context::getContext();
        $routes = $dispatcherCore->getRoutes();

        if(!$this->routes)
        {
            $this->routes = $routes;
        }
        if(!in_array($controller, $this->listDefaultControllers))
        {
            $success = true;
        }
        if(!$success && $controller && isset($routes[$context->shop->id][$context->language->id]) && count($routes[$context->shop->id][$context->language->id]))
        {
            foreach ($routes[$context->shop->id][$context->language->id] as $key=> $route)
            {
                if(!isset($route['controller']) || !$route['controller'])
                {
                    unset($routes[$context->shop->id][$context->language->id][$key]);
                    continue;
                }

                if($route['controller'] == $controller)
                {
                    if(isset($route['keywords']) && (count($route['keywords']) == 0))
                    {
                        unset(${'_GET'}['rewrite']);
                        $success = true;
                        break;
                    }

                    if(isset($route['params']) && isset($route['params']['fc']) && $route['params']['fc'] == 'module'
                        && isset($route['params']['module']) && $route['params']['module'])
                    {
                        $success = true;
                    }
                    else{
                        unset($routes[$context->shop->id][$context->language->id][$key]);
                    }

                    break;
                }

            }
            if(!$success)
            {
                $dispatcherCore->setRoutes($routes);
                $controller = $dispatcherCore->getControllerChecking();
            }

        }

        if($this->routes) {
            $dispatcherCore->setRoutes($this->routes);
        }

        $controller = str_replace('-', '', $controller);
        return $controller;
    }

    public function getSitemapAndRssController($dispatcherCore, $controller, $uri, $moduleDefined)
    {

        if ($controller == '404' || $controller == 'pagenotfound' || $controller == 'sitemap' || $controller == 'rss' || Tools::getValue('rewrite') == 'rss') {
            if (preg_match("/sitemap(\/(\w+(\/(\w+)|))|)\.xml$/", $uri)) {
                $_GET['module'] = 'ets_seo';
                $controller = 'sitemap';
                $_GET['fc'] = 'module';
                $dispatcherCore->setFrontController($moduleDefined);
            } elseif (preg_match("/^\/rss/", $uri)) {
                $_GET['module'] = 'ets_seo';
                $controller = 'rss';
                $_GET['fc'] = 'module';
                $dispatcherCore->setFrontController($moduleDefined);
            }
            elseif(preg_match("/^\/robots.txt/", $uri) && (int)Configuration::get('ETS_SEO_ENABLE_XML_SITEMAP')
                && (int)Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')
                && ($robots = Tools::file_get_contents(_PS_ROOT_DIR_.'/_robots.txt')))
            {
                $sitemap = Context::getContext()->shop->getBaseURL(true, true).'sitemap.xml';
                $robots = preg_replace('/^(Sitemap: .+\/sitemap.xml)$/im','#$1',$robots);
                $robots = preg_replace('/^#(Sitemap: '.str_replace('/', '\/', $sitemap).')$/im','$1',$robots);
                header("Content-Type: text/plain");
                echo $robots;
                exit;
            }

        }
        return $controller;
    }

    public function redirectToOldUrl($dispatcherCore, $redirectToUrlHasId = false)
    {
        $controller = '404';
        if ($redirectToUrlHasId && (int)Configuration::get('ETS_SEO_SET_REMOVE_ID')) {
            if ($this->setOldDefaultRoute('old')) {
                $dispatcherCore->setDefaultRoutes($this->old_routes);
                $dispatcherCore->setOldRoutes();
                $controller = $dispatcherCore->getControllerForRedirect();
            }
            if ($controller == '404' && $this->setOldDefaultRoute('nearest')) {
                $dispatcherCore->setDefaultRoutes($this->old_routes);
                $dispatcherCore->setOldRoutes();
                $controller = $dispatcherCore->getControllerForRedirect();
            }
            if ($controller == '404' && $this->setOldDefaultRoute('root')) {
                $dispatcherCore->setDefaultRoutes($this->old_routes);
                $dispatcherCore->setOldRoutes();
                $controller = $dispatcherCore->getControllerForRedirect();
            }

            $rules = $this->getConfigRule(true);
            $dispatcherCore->setDefaultRoutes($rules);
            $dispatcherCore->publicLoadRoutes();

        } elseif (!$redirectToUrlHasId && (int)Configuration::get('ETS_SEO_SET_REMOVE_ID')) {
            $this->setDefaultRoute();
            $dispatcherCore->setDefaultRoutes($this->default_routes);
            $dispatcherCore->setOldRoutes();
            $controller = $dispatcherCore->getControllerForRedirect();

            $dispatcherCore->setDefaultRoutes($this->getConfigRule());
            $dispatcherCore->publicLoadRoutes();

        }

        $redirectLink = null;
        if ($controller && $controller != '404') {
            $linkRewrite = $this->getLinkRewrite();
            $id_lang = (int)Context::getContext()->language->id;
            $link = Context::getContext()->link;
            switch ($controller) {
                case 'product':

                    $idProduct = (int)Tools::getValue('id_product');
                    if (!$idProduct && $linkRewrite) {
                        $idProduct = $this->getProductIdBySlug($linkRewrite);
                    }
                    $product = new Product($idProduct, null, $id_lang);
                    $redirectLink = Validate::isLoadedObject($product) ? $product->getLink() : null;
                    break;
                case 'category':

                    $idCategory = (int)Tools::getValue('id_category');
                    if (!$idCategory && $linkRewrite) {
                        $idCategory = $this->getCategoryIdBySlug($linkRewrite);
                    }
                    $category = new Category($idCategory, $id_lang);
                    $redirectLink = Validate::isLoadedObject($category) ? $category->getLink($link, $id_lang) : null;
                    break;
                case 'cms':

                    $idCms = (int)Tools::getValue('id_cms');
                    if (!$idCms && $linkRewrite) {
                        $idCms = $this->getCmsIdBySlug($linkRewrite);
                    }
                    $cms = new CMS($idCms, (int)$id_lang);
                    $redirectLink = Validate::isLoadedObject($cms) ? $link->getCMSLink($cms, null,null, $id_lang) : null;
                    break;
                case 'cms_category':

                    $idCmsCategory = (int)Tools::getValue('id_cms_category');
                    if (!$idCmsCategory && $linkRewrite) {
                        $idCmsCategory = $this->getCmsCategoryIdBySlug($linkRewrite);
                    }
                    $cmsCategory = new CMSCategory($idCmsCategory, (int)$id_lang);
                    $redirectLink = Validate::isLoadedObject($cmsCategory) ? $link->getCMSCategoryLink($cmsCategory, null, $id_lang) : null;
                    break;
                case 'manufacturer':

                    $idManufacturer = (int)Tools::getValue('id_manufacturer');
                    if (!$idManufacturer && $linkRewrite) {
                        $idManufacturer = $this->getManufIdBySlug($linkRewrite);
                    }
                    $manufacturer = new Manufacturer($idManufacturer, $id_lang);
                    $redirectLink = Validate::isLoadedObject($manufacturer) ? $link->getManufacturerLink($manufacturer, null, $id_lang) : null;
                    break;
                case 'supplier':
                    $idSupplier = (int)Tools::getValue('id_supplier');
                    if (!$idSupplier && $linkRewrite) {
                        $idSupplier = $this->getSupplierIdBySlug($linkRewrite);
                    }
                    $supplier = new Supplier($idSupplier, $id_lang);
                    $redirectLink = Validate::isLoadedObject($supplier) ? $link->getSupplierLink($supplier, null, $id_lang) : null;
                    break;
            }

            if($redirectLink)
            {
                $statusCode = (int)Configuration::get('ETS_SEO_REDIRECT_STATUS_CODE');
                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                if($statusCode == 301)
                {
                    $header = $protocol. ' 301 Moved Permanently';
                }
                else{
                    $header = $protocol. ' 302 Moved Temporarily';
                }

                Tools::redirect($redirectLink, null, null, $header);
            }
        }

    }

    public function getConfigRule($noid = false)
    {
        if($noid)
        {
            return $this->getDefaultRouteNoId();
        }
        else
        {
            $routes = $this->old_routes;
            foreach ($routes as $key => &$route)
            {
                $route['rule'] = Configuration::get('PS_ROUTE_'.$key);
            }
            return $routes;
        }
    }
}