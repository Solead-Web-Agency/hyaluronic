<?php
/**
 * 2007-2022 ETS-Soft
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
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class Ets_abandonedcartThankModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $alias = ($alias = Tools::getValue('url_alias')) && Validate::isCleanHtml($alias) ? $alias : '';
        $form = EtsAbancartForm::getThankyouPageByAlias($alias, $this->context->language->id, true, true);
        $tpTitle = null;
        $tpContent = null;
        if($form){
            $tpTitle = $form['thankyou_page_title'];
            $tpContent = $form['thankyou_page_content'];
        }
        $this->context->smarty->assign(array(
            'tpTitle' => $tpTitle,
            'tpContent' => $tpContent,
            'moduleDir' => _PS_MODULE_DIR_.$this->module->name,
            'path' => $this->module->getBreadCrumb(),
            'breadcrumb' => $this->module->is17 ? $this->module->getBreadCrumb() : false,
        ));
        if($this->module->is17){
            $this->setTemplate('module:'.$this->module->name.'/views/templates/front/thankyou_page.tpl');
        }
        else{
            $this->setTemplate('thankyou_page16.tpl');
        }
    }
}