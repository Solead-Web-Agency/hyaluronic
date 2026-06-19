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
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

class PBPAdminProductTabController extends PBPControllerCore
{

    public function __construct($sibling, $params = array())
    {
        parent::__construct($sibling, $params);
        $this->sibling = $sibling;
        $this->base_url = Tools::getShopProtocol() . Tools::getShopDomain() . __PS_BASE_URI__;
    }

    public function setMedia()
    {
        if (Tools::getValue('controller') == 'AdminProducts') {
            Context::getContext()->controller->addCSS($this->sibling->_path . 'views/css/lib/tools.css');
            Context::getContext()->controller->addCSS($this->sibling->_path . 'views/css/lib/popup.css');
            Context::getContext()->controller->addCSS($this->sibling->_path . 'views/css/admin/producttab.css');

            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/lib/Tools.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/lib/Popup.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/lib/pbpproductsearchwidget.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/admin/producttab/PBPAdminProductTabGeneralController.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/admin/producttab/PBPAdminProductTabBundlesController.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/admin/producttab/PBPAdminProductTabBundleExistingController.js');
        }
    }

    public function render()
    {
        Context::getContext()->smarty->assign(array(
            'module_ajax_url' => $this->module_bo_ajax_url,
            'module_config_url' => $this->module_config_url,
            'module_url' => $this->getShopBaseUrl(),
            'id_product' => $this->params['id_product'],
            'id_shop' => Context::getContext()->shop->id,
            'http_get' => $this->params
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/producttab/main.tpl');
    }

    public function route()
    {
        $return = '';

        switch (Tools::getValue('route')) {
            case 'pbpadminproducttabgeneralcontroller':
                $pbp_admin_producttab_general_controller = new PBPAdminProductTabGeneralController($this->sibling, $this->params);
                return $pbp_admin_producttab_general_controller->route();

            case 'pbpadminproducttabbundlescontroller':
                $pbp_admin_producttab_bundles_controller = new PBPAdminProductTabBundlesController($this->sibling, $this->params);
                return $pbp_admin_producttab_bundles_controller->route();

            case 'pbpadminproducttabbundlesexistingcontroller':
                $pbp_admin_bundles_existing_controller = new PBPAdminProductTabBundlesExistingController($this->sibling);
                die($pbp_admin_bundles_existing_controller->route());

            default:
                return $this->render();
        }
    }
}
