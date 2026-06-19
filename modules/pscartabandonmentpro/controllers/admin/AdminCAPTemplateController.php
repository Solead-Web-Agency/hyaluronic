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

class AdminCAPTemplateController extends ModuleAdminController
{

    /**
     * ajaxProcessLoadTemplate
     */
    public function ajaxProcessLoadTemplate()
    {
        $this->initializeTemplate();

        // If $reminderId > 0 that means we are updating a Reminder and we must load the reminder datas
        $reminderId = (int) Tools::getValue('reminder_id');

        if ($reminderId > 0) {
            $this->loadTemplateExistingDatas($reminderId);
        }
        
        exit($this->context->smarty->fetch(_PS_MODULE_DIR_.'/'.$this->module->name.'/views/templates/admin/tabs/reminder_plan/email_template.tpl'));
    }

    /**
     * Return a cart demo
     */
    public function ajaxProcessGetDemoCart()
	{
        $oProductInfo = new CartReminderProductInfo();
        $iShopId = $this->context->shop->id;
        $iLangId = $this->context->employee->id_lang;

        $iReturnProductQuantity = 2;
        $aRandomProductIds = $oProductInfo->getRandomProducts($iReturnProductQuantity);
        $aProductList = $oProductInfo->prepareProductListForTemplate($aRandomProductIds, $iShopId, $iLangId);

		$this->context->smarty->assign(array(
            'aProducts' => $aProductList,
            'currency' => $this->context->currency->sign,
        ));

		exit($this->context->smarty->fetch(_PS_MODULE_DIR_.'/'.$this->module->name.'/views/templates/admin/ajax/cart.tpl'));
    }

    /**
     * Initialize medias JS and smarty variables
     */
    public function initializeTemplate() 
    {
        $aPresetsTags = $this->getCustomEmailContent();
        $this->context->smarty->assign(array(
            'employeeLangId' => $this->context->employee->id_lang,
            'custom_content' => $aPresetsTags['content'],
            'discount_content' => $aPresetsTags['discount'],
            'unsubscribe_content' => $aPresetsTags['unsubscribe'],
            'shop_logo' => $this->module->ps_url.'/img/'.Configuration::get('PS_LOGO'),
            'shop_name' => Configuration::get('PS_SHOP_NAME'),
            'shop_addr1' => Configuration::get('PS_SHOP_ADDR1'),
            'shop_addr2' => Configuration::get('PS_SHOP_ADDR2'),
            'shop_zipcode' => Configuration::get('PS_SHOP_CODE'),
            'shop_city' => Configuration::get('PS_SHOP_CITY'),
            'shop_country' => Configuration::get('PS_SHOP_COUNTRY'),
            'shop_phone' => Configuration::get('PS_SHOP_PHONE'),
            'shop_fax' => Configuration::get('PS_SHOP_FAX'),
            'shop_url' => $this->module->ps_url,
            'img_url' => $this->module->img_path,
            'languages' => Language::getLanguages(),
            'email_content_default' => array(
                $this->module->l('Hello'),
                $this->module->l('There is your cart :')
            ),
            'email_discount_default' => array(
                $this->module->l('You are ready to finish your purchase with a fabulous code'),
                $this->module->l('{discount_code}'),
                $this->module->l('that will apply a {discount_value} discount on your next order.'),
                $this->module->l('You have up to {discount_validity} to take advantage of this personalized discount.'),
            ),
            'color1' => '#00b9dc',
            'color2' => '#D78F00',
            'pickr' => $this->module->js_path.'admin/tabs/reminder_plan/steps/template_appearance.js',
        ));
    }

    /**
     * loadTemplateExistingDatas
     *
     * @param  int $reminderId
     */
    public function loadTemplateExistingDatas($reminderId) 
    {
        $oReminderInfos = new CartReminderInfo;

        $templateAppearance = $oReminderInfos->getEmailTemplateAppearance($reminderId);
        $templateDatasRaw = $oReminderInfos->getEmailTemplateDatas($reminderId);
        $templateDatasFinal = array();

        // the key must be the id_lang to be used in the smarty's template.
        foreach ($templateDatasRaw as $key => $value) {
            $templateDatasFinal[$value['id_lang']] = $templateDatasRaw[$key];
        }
        
        $this->context->smarty->assign(array(
            'template_appearance' => $templateAppearance,
            'template_datas' => $templateDatasFinal,
            'employeeLangId' => $this->context->employee->id_lang,
            'color1' => $templateAppearance['primary_color'],
            'color2' => $templateAppearance['secondary_color'],
        ));
    }

    /**
    * Return the custom content you will show on the template email
    *
    * @return array
    */
    protected function getCustomEmailContent()
    {
        return array( 
            'content' => array(
                $this->module->l('First Name') => '{first_name}',
                $this->module->l('Last Name') => '{last_name}',
                $this->module->l('Gender') => '{gender}',
                $this->module->l('Number Of Product') => '{nb_product}',
                $this->module->l('Products in cart') => '{cart}',
                $this->module->l('Cart link') => '{cart_link}',
                $this->module->l('Shop link') => '{shop_link}', 
            ), 
            'discount' => array(
                $this->module->l('Discount Code') => '{discount_code}',
                $this->module->l('Discount Value') => '{discount_value}',
                $this->module->l('Discount Validity') => '{discount_validity}',
            ),
            'unsubscribe' => array(
                $this->module->l('Unsubscribe') => '{unsubscribe}',
            ),
        );
    }

} 
