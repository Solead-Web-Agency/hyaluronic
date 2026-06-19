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

class CartReminderFrontControllerRouter
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var Link
     */
    private $link;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('pscartabandonmentpro');
        $this->link = Context::getContext()->link;
    }

    /**
     * Return a Module Front Controller Link
     *
     * @param  mixed $sControllerName
     * @param  mixed $aParamsUrl
     *
     * @return string
     */
    public function getModuleLink($sControllerName, $aParamsUrl)
    {
        return $this->link->getModulelink(
            $this->module->name, 
            $this->module->controllers[$sControllerName],
            $aParamsUrl
        );
    }

    /**
     * Get the shop url controller
     *
     * @param  int $iCustomerId
     * @param  int $iReminderId
     * @param  int $iCartId
     *
     * @return string
     */
    public function getShopUrlController($iCustomerId, $iReminderId, $iCartId) 
    {
        return $this->getModulelink(
            'clickShopUrl', 
            array(
                'id_customer' => $iCustomerId,
                'id_reminder' => $iReminderId,
                'id_cart' => $iCartId,
                'token' =>  sha1($iCartId.$iReminderId.$this->module->name.'shop')
            )
        );
    }

    /**
     * Get the shop cart controller
     *
     * @param  int $iCustomerId
     * @param  int $iReminderId
     * @param  int $iCartId
     *
     * @return string
     */
    public function getShopCartController($iCustomerId, $iReminderId, $iCartId) 
    {
        return $this->getModulelink(
            'clickCartUrl', 
            array(
                'id_customer' => $iCustomerId,
                'id_reminder' => $iReminderId,
                'id_cart' => $iCartId,
                'token' =>  sha1($iCartId.$iReminderId.$this->module->name.'cart')
            )
        );
    }

    /**
     * Get the unsubscribe controller
     *
     * @param  int $iCustomerId
     * @param  int $iReminderId
     * @param  int $iCartId
     *
     * @return string
     */
    public function getUnsubscribeController($iCustomerId, $iReminderId, $iCartId) 
    {
        return  $this->getModulelink(
            'unsubscribeJob', 
            array(
                'unsubscribe' => sha1($iCustomerId.date('m').$this->module->name),
                'id_reminder' => $iReminderId,
                'id_cart' => $iCartId,
            )
        );
    }

    /**
     * Get the shop product controller
     *
     * @param  int $iCustomerId
     * @param  int $iReminderId
     * @param  int $iCartId
     *
     * @return string
     */
    public function getShopProductController($iCustomerId, $iReminderId, $iCartId, $iProductId) 
    {
        return $this->getModulelink(
            'clickProductUrl', 
            array(
                'id_customer' => $iCustomerId,
                'id_reminder' => $iReminderId,
                'id_cart' => $iCartId,
                'id_product' => $iProductId,
                'token' =>  sha1($iCartId.$iReminderId.$this->module->name.'cart')
            )
        );
    }
    
}