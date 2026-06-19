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

class AdminCAPListingController extends ModuleAdminController
{
    /**
     * __construct
     *
     * @return void
     */

    /**
     * ajaxProcessLoadTemplate
     *
     * @return void
     */
    public function ajaxProcessLoadTemplate()
    {
        $aReminderList = new CartReminderInfo();

        $this->context->smarty->assign(array(
            'reminderList' => $aReminderList->getReminderList(),
            'reminder_test_email' => Configuration::get('ADMINCAP_EMAIL_TEST', null, $this->context->shop->id_shop_group, $this->context->shop->id)
        ));

        exit($this->context->smarty->fetch(_PS_MODULE_DIR_.'/'.$this->module->name.'/views/templates/admin/tabs/reminder_plan/listing.tpl'));
    }

    /**
     * ajaxProcessLoadReminderInfos
     *
     * @return void
     */
    public function ajaxProcessLoadReminderInfos()
    {
        $iCartAbandonmentId = (int) Tools::getValue('id');

        $oCartAbandonmentInformations = new CartReminderInfo();
        
        // Get Target, Discount and Template general informations
        $aTargetInfos = $oCartAbandonmentInformations->getTargetInfos($iCartAbandonmentId);
        $aDiscountInfos = $oCartAbandonmentInformations->getDiscountInfos($iCartAbandonmentId);       
        $sEmailTemplateName = $oCartAbandonmentInformations->getEmailTemplateName($iCartAbandonmentId);

        $this->context->smarty->assign(array(
            'reminder_id' => $iCartAbandonmentId,
            'sFinalTargetInfos' => $this->prepareTargetInfos($aTargetInfos),
            'aFinalDiscountInfos' => $this->preparediscountInfos($aDiscountInfos),
            'sEmailTemplateName' => $sEmailTemplateName,
            'module_url' => $this->module->ps_url.'modules/'.$this->module->name,
        ));

        exit($this->context->smarty->fetch(_PS_MODULE_DIR_.'/'.$this->module->name.'/views/templates/admin/tabs/reminder_plan/listing/reminder_infos.tpl'));
    }

    /**
     * Delete all informations of a cart reminder in all concerned tables
     *
     * @return void
     */
    public function ajaxProcessDeleteReminder()
    {
        $iCartAbandonmentId = (int) Tools::getValue('id');

        $oCartReminderInfos = new CartReminderInfo();
        $bDeletion = $oCartReminderInfos->deleteReminderById($iCartAbandonmentId);

        if (!$bDeletion) {
            exit('0');
        }

        $this->ajaxProcessLoadTemplate();
    }

    /**
     * ajaxProcessChangeReminderStatus
     *
     * @return bool
     */
    public function ajaxProcessChangeReminderStatus()
    {
        $iCartAbandonmentId = (int) Tools::getValue('id');
        $iStatus = (int) Tools::getValue('status');

        $aUpdatedDatas = array(
            'active' => $iStatus
        );
        $sWhere = 'id_cart_abandonment = '.$iCartAbandonmentId;

        $bStatusChanged = Db::getInstance()->update('cart_abandonment', $aUpdatedDatas, $sWhere);

        exit($bStatusChanged);
    }

    /**
     * ajaxPreviewTemplate
     *
     * @return void
     */
    public function ajaxProcessPreviewTemplate()
    {
        $iEmployeeLangId = (int)$this->context->employee->id_lang;
        $iCartAbandonmentId = (int) Tools::getValue('id');
        $oCartReminderInfos = new CartReminderInfo();  
        
        // Get the templates informations : Model Name & Appearance & Datas
        $sModelName = $oCartReminderInfos->getEmailTemplateName($iCartAbandonmentId);
        $aTemplateAppearance = $oCartReminderInfos->getEmailTemplateAppearance($iCartAbandonmentId);
        $aTemplateDatas = $oCartReminderInfos->getEmailTemplateDatas($iCartAbandonmentId);
        
        // Assign smarty's variables 
        $oTemplateController = new AdminCAPTemplateController;
        $oTemplateController->initializeTemplate();

        $this->context->smarty->assign(array(
            'template_datas' => $this->finalizedTemplateDatas($aTemplateDatas, $iEmployeeLangId),
            'template_appearance' => $aTemplateAppearance,
            'employeeLangId' => $iEmployeeLangId,
        ));

        exit($this->context->smarty->fetch(_PS_MODULE_DIR_.'/'.$this->module->name.'/views/templates/admin/emails/'.$sModelName.'.tpl'));
    }

    /**
     * Finalize the datas to be shown in the preview template
     * If there is an unsubscribe text, we replace the tag {unsubscribe} into the specific text
     *
     * @param  array $aTemplateDatas
     *
     * @return array $aTemplateDatas
     */
    protected function finalizedTemplateDatas($aTemplateDatas, $iLangId)
    {
        if (!empty($aTemplateDatas[$iLangId]['email_unsubscribe_text'])) {
            $sReplace = '<a href="#">'.$aTemplateDatas[$iLangId]['email_unsubscribe_text'].'</a>';
            $aTemplateDatas[$iLangId]['email_unsubscribe'] = str_replace('{unsubscribe}', $sReplace, $aTemplateDatas[$iLangId]['email_unsubscribe']);
        }

        return $aTemplateDatas;
    }

    /**
     * prepareTargetInfos
     *
     * @param  array $aTargetInfos
     *
     * @return string
     */
    protected function prepareTargetInfos($aTargetInfos)
    {
        $sTargetText = '';

        // Prepare translations
        $sTargetTextAll = $this->l('All');
        $sTargetTextActive = $this->l('Active customers');
        $sTargetTextInactive = $this->l('Inactive customers');
        $sTargetTextNoOrders = $this->l('Customers without orders');
        $sTargetTextNewsletter = $this->l('Suscribed to the newsletter');

        // If all is checked, show "All"
        // Else, show every targets like : "Inactive customers, Customers without orders" 
        if ($aTargetInfos['cart_target_active'] && $aTargetInfos['cart_target_inactive'] && !$aTargetInfos['cart_target_no_orders']) {
            $sTargetText .= $sTargetTextAll;
        } else {
            if ($aTargetInfos['cart_target_active']) {
                $sTargetText .= ', '.$sTargetTextActive;
            }
            if ($aTargetInfos['cart_target_inactive']) {
                $sTargetText .= ', '.$sTargetTextInactive;
            }
            if ($aTargetInfos['cart_target_no_orders']) {
                $sTargetText .= ', '.$sTargetTextNoOrders;
            }
        }

        // If subscribed to the newsletter, add ", Suscribed to the newsletter"
        if ($aTargetInfos['cart_target_newsletter']){
            $sTargetText .= ', '.$sTargetTextNewsletter;
        } 

        return trim($sTargetText, ', ');
    }

    /**
     * prepareDiscountInfos
     *
     * @param  array $aDiscountInfos
     *
     * @return string
     */
    protected function prepareDiscountInfos($aDiscountInfos)
    {
        $sFinalText = '';
        $aFinalDiscountInfos = array();
        $iDiscountAmount = count($aDiscountInfos);
        $bMultipleDiscounts = ($iDiscountAmount > 1) ? true : false;

        // Prepare translations
        $sBetweenText = $this->l('Between');
        $sAfterText = $this->l('Above');
        $sApplyText = $this->l('of the cart value, apply');
        $sAndText = $this->l('and');
        $sDiscountText = $this->l('discount');
        $sFreeshippingText = $this->l('freeshipping');
        $sNoDiscountText = $this->l('no discount');
        

        foreach ($aDiscountInfos as $key => $aDiscount) {

            $valueFrom = $aDiscount['discount_from'];
            $valueTo = $aDiscount['discount_to']+0.99;

            // If there is a multiple discount for this reminder we show a text for each discount
            // Example :   Between 1$ and 5$ apply [...]
            // Example :   After 500$ apply [...]
            if ($bMultipleDiscounts) {
                $iDiscountNumber = $key + 1;

                // If we are not in the last discount, we show the text : Between ... and ...
                // Else, we show the text :   After ...
                if ($iDiscountAmount > $iDiscountNumber) {
                    $sFinalText .= $sBetweenText.' ';
                    $sFinalText .= Tools::displayPrice($valueFrom).' ';
                    $sFinalText .= $sAndText. ' ';
                    $sFinalText .= Tools::displayPrice($valueTo).' ';
                } else {
                    $sFinalText .= $sAfterText.' ';
                    $sFinalText .= Tools::displayPrice($valueFrom).' ';
                }

                $sFinalText .= $sApplyText. ' ';
            }
            
            // Show discount type Currency | Percentage | Freeshiping
            if ($aDiscount['discount_value_type'] == 'amount') {
                $sFinalText .= Tools::displayPrice($aDiscount['discount_value']).' ';
                $sFinalText .= $sDiscountText;
            } else if ($aDiscount['discount_value_type'] == 'percentage') {
                $sFinalText .= $aDiscount['discount_value'].'% ';
                $sFinalText .= $sDiscountText;
            } else if ($aDiscount['discount_value_type'] == 'freeshipping') {
                $sFinalText .= $sFreeshippingText;
            } else if ($aDiscount['discount_value_type'] == 'no_discount') {
                $sFinalText .= $sNoDiscountText;
            }
            
            $aFinalDiscountInfos[] = $sFinalText;
            $sFinalText = '';
        }

        return $aFinalDiscountInfos;
    }

    
} 
