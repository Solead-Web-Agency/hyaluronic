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

class ProductBundlesProAjaxModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
        $this->route();
    }

    public function route()
    {
        $module = Module::getInstanceByName('productbundlespro');
        switch (Tools::getValue('route')) {
            case 'pbpfrontproductcontroller':
                $pbp_front_product_controller = new PBPFrontProductController($this);
                die($pbp_front_product_controller->route());
        }
    }
}
