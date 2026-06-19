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
 * @copyright 2016-2020 Musaffar Patel
 * @license   LICENSE.txt
 */

class AdminAjaxProductBundlesProController extends ModuleAdminController
{
    public function initContent()
    {
        $controller = Tools::getValue('controller');
        $token = Tools::getValue('token');

        // check security token
        if ($token != Tools::getAdminTokenLite($controller)) {
            die;
        }

        $this->ajax = true;
        parent::initContent();
        die($this->route());
    }

    public function route()
    {
        $module = Module::getInstanceByName('productbundlespro');
        if (Tools::getValue('section') != '') {
            switch (Tools::getValue('section')) {
                case 'pbpproductsearchwidgetcontroller':
                    $pbp_product_search_widget = new PBPProductSearchWidgetController(Tools::getValue('id'), $module);
                    die(json_encode($pbp_product_search_widget->route()));
            }
        }
    }
}
