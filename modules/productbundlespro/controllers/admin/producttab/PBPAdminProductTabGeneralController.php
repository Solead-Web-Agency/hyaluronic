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

class PBPAdminProductTabGeneralController extends PBPControllerCore
{

    public function __construct($sibling, $params = array())
    {
        parent::__construct($sibling, $params);
        $this->sibling = $sibling;
        $this->base_url = Tools::getShopProtocol() . Tools::getShopDomain() . __PS_BASE_URI__;
    }

    public function setMedia()
    {
    }

    public function render()
    {
        $pbp_product_option = new PBPProductOptionModel();
        $pbp_product_option->load(Tools::getValue('id_product'));

        Context::getContext()->smarty->assign(array(
            'module_ajax_url' => $this->module_bo_ajax_url,
            'id_product' => $this->params['id_product'],
            'pbp_product_option' => $pbp_product_option,
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/producttab/general.tpl');
    }

    public function processForm()
    {
        $product_option_model = new PBPProductOptionModel();
        $product_option_model->load(Tools::getValue('id_product'));
        $product_option_model->id_product = (int)Tools::getValue('id_product');
        $product_option_model->disabled_addtocart = (int)Tools::getValue('pbp-disabled_addtocart');
        $product_option_model->save();
    }

    public function route()
    {
        $return = '';

        switch (Tools::getValue('action')) {
            case 'processform':
                die($this->processForm());

            default:
                return $this->render();
        }
    }
}
