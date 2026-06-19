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

class Dispatcher extends DispatcherCore
{

    public $etsSeoDispatcher = null;

    protected function __construct()
    {
        if(file_exists(_PS_MODULE_DIR_.'ets_seo/classes/dispatcher/EtsSeoDispatcher.php'))
        {
            require_once _PS_MODULE_DIR_ . 'ets_seo/classes/dispatcher/EtsSeoDispatcher.php';
        }
        if(class_exists('EtsSeoDispatcher'))
        {
            $this->etsSeoDispatcher = EtsSeoDispatcher::getDispatcher();
            if($this->enabledRemoveIdInUrl() && (int)Configuration::get('PS_REWRITING_SETTINGS'))
            {
                $this->default_routes = $this->etsSeoDispatcher->getDefaultRouteNoId();
            }
        }
        parent::__construct();
    }

    public function getController($id_shop = null)
    {
        parent::getController($id_shop);
        if($this->etsSeoDispatcher)
        {
            if($this->enabledRemoveIdInUrl())
            {
                $this->controller = $this->etsSeoDispatcher->getController($this, $this->controller,self::FC_MODULE);
            }
            $this->controller = $this->etsSeoDispatcher->getSitemapAndRssController($this, $this->controller, $this->request_uri, self::FC_MODULE);

            if(($this->controller == '404' || $this->controller == 'pagenotfound') && (int)Configuration::get('ETS_SEO_ENABLE_REDRECT_NOTFOUND'))
            {
                if($this->enabledRemoveIdInUrl())
                {
                    $this->etsSeoDispatcher->redirectToOldUrl($this,true);
                }
                else{
                    $this->etsSeoDispatcher->redirectToOldUrl($this);
                }
            }
        }

        return $this->controller;
    }

    public function getControllerForRedirect($id_shop = null)
    {
        if($this->etsSeoDispatcher)
        {
            $this->controller = null;
            unset($_GET['controller']);
            $controller =  parent::getController($id_shop);
            $this->controller = $this->etsSeoDispatcher->getSitemapAndRssController($this, $controller, $this->request_uri, self::FC_MODULE);
        }
        return $this->controller;
    }

    public function getControllerChecking($id_shop= null)
    {
        $this->controller = null;
        unset($_GET['controller']);
        return $this->getController($id_shop);
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function setRoutes($routes)
    {
        $this->routes = $routes;

    }

    public  function enabledRemoveIdInUrl()
    {
        return (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')
            && !defined('_PS_ADMIN_DIR_')
            && (int)Configuration::get('PS_REWRITING_SETTINGS');
    }

    public function setOldRoutes($id_shop = null)
    {
        $context = Context::getContext();
        if (isset($context->shop) && $id_shop === null) {
            $id_shop = (int) $context->shop->id;
        }
        $language_ids = Language::getIDs();
        if (isset($context->language) && !in_array($context->language->id, $language_ids)) {
            $language_ids[] = (int) $context->language->id;
        }
        foreach ($this->default_routes as $id => $route) {
            $route = $this->computeRoute(
                $route['rule'],
                $route['controller'],
                $route['keywords'],
                isset($route['params']) ? $route['params'] : array()
            );
            foreach ($language_ids as $id_lang) {
                $this->routes[$id_shop][$id_lang][$id] = $route;
            }
        }
        if ($this->use_routes) {
            $sql = 'SELECT m.page, ml.url_rewrite, ml.id_lang
					FROM `' . _DB_PREFIX_ . 'meta` m
					LEFT JOIN `' . _DB_PREFIX_ . 'meta_lang` ml ON (m.id_meta = ml.id_meta' . Shop::addSqlRestrictionOnLang('ml', (int) $id_shop) . ')
					ORDER BY LENGTH(ml.url_rewrite) DESC';
            if ($results = Db::getInstance()->executeS($sql)) {
                foreach ($results as $row) {
                    if ($row['url_rewrite']) {
                        $this->addRoute(
                            $row['page'],
                            $row['url_rewrite'],
                            $row['page'],
                            $row['id_lang'],
                            array(),
                            array(),
                            $id_shop
                        );
                    }
                }
            }
            if (!$this->empty_route) {
                $this->empty_route = array(
                    'routeID' => 'index',
                    'rule' => '',
                    'controller' => 'index',
                );
            }
        }
    }

    public function validateRoute($route_id, $rule, &$errors = array())
    {
        if((int)Tools::getValue('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')
            || ((int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL') && !(int)Configuration::get('PS_REWRITING_SETTINGS')))
        {
            $errors = array();
            if (!isset($this->default_routes[$route_id])) {
                return false;
            }
            foreach ($this->default_routes[$route_id]['keywords'] as $keyword => $data) {
                if (isset($data['param']) && ($route_id == 'module' || ($route_id != 'module' && $keyword == 'rewrite')) && !preg_match('#\{([^{}]*:)?' . $keyword . '(:[^{}]*)?\}#', $rule)) {
                    $errors[] = $keyword;
                }
            }
            return (count($errors)) ? false : true;
        }
        return parent::validateRoute($route_id, $rule, $errors);
    }

    public function getFontController()
    {
        return $this->front_controller;
    }

    public function setFrontController(int $front_controller): DispatcherCore
    {
        $this->front_controller = $front_controller;
        return $this;
    }

    public function getUseDefaultController()
    {
        return $this->useDefaultController();
    }

    public function setDefaultRoutes($routes)
    {
        $this->default_routes = $routes;
    }

    public function publicLoadRoutes()
    {
        parent::loadRoutes();
    }

    protected function setRequestUri()
    {
        parent::setRequestUri();
        if(!isset(${'_GET'}['isolang']) && (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL') && ($idLang = (int)Configuration::get('PS_LANG_DEFAULT'))){
            if($idLang && ($iso = Language::getIsoById($idLang))){
                $_GET['isolang'] = $iso;
            }
        }
    }

}