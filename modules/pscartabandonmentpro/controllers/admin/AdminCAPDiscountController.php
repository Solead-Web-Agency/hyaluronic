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

class AdminCAPDiscountController extends ModuleAdminController
{
    /**
     * ajaxProcessLoadTemplate
     *
     * @return void
     */
    public function ajaxProcessLoadTemplate()
    {
        $this->initializeTemplate();
        
        // If $reminderId > 0 that means we are updating a Reminder and we must load the reminder datas
        $reminderId = (int) Tools::getValue('reminder_id');
        
        if ($reminderId > 0) {
            $this->loadTemplateExistingDatas($reminderId);
        }

        exit($this->context->smarty->fetch(_PS_MODULE_DIR_.'/'.$this->module->name.'/views/templates/admin/tabs/reminder_plan/discount.tpl'));
    }

    /**
     * prepareDefaultDiscount
     *
     * @return array
     */
    public function prepareDefaultDiscount()
    {
        return array(
            array(
                'discount_from' => 1,
                'discount_to' => 29,
            ),
            array(
                'discount_from' => 30,
                'discount_to' => 31,
            )
        );
    }

    /**
     * Initialize medias JS and smarty variables
     *
     * @return void
     */
    public function initializeTemplate() 
    {
        $this->context->smarty->assign(array(
            'currency' => $this->context->currency->sign,
            'discount_infos' => $this->prepareDefaultDiscount()
        ));
    }

    /**
     * loadTemplateExistingDatas
     *
     * @param  int $reminderId
     *
     * @return void
     */
    public function loadTemplateExistingDatas($reminderId) 
    {
        $oReminderInfos = new CartReminderInfo;

        $allReminderdiscountInformations = $oReminderInfos->getDiscountInfos($reminderId);

        $this->context->smarty->assign(array(
            'multiple_discount' => count($allReminderdiscountInformations),
            'discount_infos' => $allReminderdiscountInformations,
        ));
    }
} 
