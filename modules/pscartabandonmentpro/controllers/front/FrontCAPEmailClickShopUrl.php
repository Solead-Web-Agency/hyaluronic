<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

class pscartabandonmentproFrontCAPEmailClickShopUrlModuleFrontController extends ModuleFrontController
{
    /**
     *  Redirect the customer to the home after saving this action 
     *
     */
    public function initContent()
    {
        // Retrieve values
        $iCustomerId = (int)Tools::getValue('id_customer');
        $iReminderId = (int)Tools::getValue('id_reminder');
        $iCartId = (int)Tools::getValue('id_cart');
        $sUrlToken = Tools::getValue('token');

        $sVisualizeToken = sha1($iCartId.$iReminderId.$this->module->name.'shop');

        // if the token is good : save the data 'click'
        if ($sUrlToken == $sVisualizeToken) {
            $data = array('click' => 1, 'visualize' => 1);
            $where = 'id_customer = '.$iCustomerId.' AND id_cart_abandonment = '.$iReminderId.' AND id_cart = '.$iCartId;

            if (!Db::getInstance()->update('cart_abandonment_customer_send', $data, $where)) {
                return false;
            }
        }

        // redirect to front index
        Context::getContext()->cookie->id_cart = $iCartId;
        Tools::redirect(__PS_BASE_URI__);
    }
}