<?php
/**
 * 2007-2021 ETS-Soft
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
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class AdminEtsSeoSearchAppearanceContentTypeController extends ModuleAdminController
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->bootstrap  = true;
        parent::__construct();
        
        $seoDef = Ets_Seo_Define::getInstance();
        $this->fields_options = array(
            'product' => array(
                'title' => $this->l('Products'),
                'fields' => $seoDef->fields_config()['search_content_type_product'],
                'icon'=> '',
                'short_code'=> array(
                    'title' => $this->module->getMetaCodeTemplate('product', true),
                    'desc' => $this->module->getMetaCodeTemplate('product', false),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
            'category' => array(
                'title' => $this->l('Category pages'),
                'fields' => $seoDef->fields_config()['search_content_type_category'],
                'icon'=> '',
                'short_code'=> array(
                    'title' => $this->module->getMetaCodeTemplate('category', true),
                    'desc' => $this->module->getMetaCodeTemplate('category', false),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
            'cms' => array(
                'title' => $this->l('CMS pages'),
                'fields' => $seoDef->fields_config()['search_content_type_cms'],
                'icon'=> '',
                'short_code'=> array(
                    'title' => $this->module->getMetaCodeTemplate('cms', true),
                    'desc' => $this->module->getMetaCodeTemplate('cms', false),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
            'cms_category' => array(
                'title' => $this->l('Cms category pages'),
                'fields' => $seoDef->fields_config()['search_content_type_cms_cate'],
                'icon'=> '',
                'short_code'=> array(
                    'title' => $this->module->getMetaCodeTemplate('cms_category', true),
                    'desc' => $this->module->getMetaCodeTemplate('cms_category', false),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
            'manufacturer' => array(
                'title' => $this->l('Brand (manufacturer) pages'),
                'fields' => $seoDef->fields_config()['search_content_type_manufacturer'],
                'icon'=> '',
                'short_code'=> array(
                    'title' => $this->module->getMetaCodeTemplate('manufacturer', true),
                    'desc' => $this->module->getMetaCodeTemplate('manufacturer', false),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
            'supplier' => array(
                'title' => $this->l('Supplier pages'),
                'icon'=> '',
                'short_code'=> array(
                    'title' => $this->module->getMetaCodeTemplate('supplier', true),
                    'desc' => $this->module->getMetaCodeTemplate('supplier', false),
                ),
                'fields' => $seoDef->fields_config()['search_content_type_supplier'],
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }
    }

    public function renderOptions()
    {
        return parent::renderOptions();
    }

}