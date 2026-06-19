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

class AdminEtsSeoSearchAppearanceSitemapController extends ModuleAdminController
{
    /**
     * __construct
     *
     * @return void
     */
    public $priority_options;

    public function __construct()
    {
        $this->bootstrap  = true;
        parent::__construct();
        
        $seoDef = Ets_Seo_Define::getInstance();
        $this->fields_options = array(
            'rss_setting' => array(
                'title' => $this->l('Sitemap settings'),
                'fields' => $seoDef->fields_config()['sitemap_setting'],
                'icon'=> '',
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Reset sitemap'),
                        'type'=> 'submit',
                        'class' => 'ets-seo-btn-reset-robotstxt btn btn-default',
                        'icon' => 'process-icon-refresh',
                        'name' => 'resetSitemap'
                    )
                )
            ),
        );
        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }
        if($this->context->cookie->__get('reset_sitemap_success'))
        {
            $this->confirmations[] = $this->l('Reseted sitemap successful');
            $this->context->cookie->__unset('reset_sitemap_success');
        }
    }

    public function renderOptions()
    {
        $this->setPriorityOptions();
        $this->context->smarty->assign(array(
            'ets_seo_base_uri' => __PS_BASE_URI__,
            'ets_seo_multilang_activated' => Language::isMultiLanguageActivated($this->context->shop->id),
            'ets_seo_languages' => Language::getLanguages(true),
            'ets_seo_baseurl' => $this->context->shop->getBaseURL(true, true),
            'ets_seo_priority_options' => $this->priority_options,
            'ETS_SEO_SITEMAP_OPTION' => explode(',', Configuration::get('ETS_SEO_SITEMAP_OPTION'))
        ));
        return parent::renderOptions();

    }

    public function postProcess()
    {
        $this->setPriorityOptions();
        $priorityValues = array(
            0.0,
            0.1,
            0.2,
            0.3,
            0.4,
            0.5,
            0.6,
            0.7,
            0.8,
            0.9,
            1.0);
        $priorityValuesStr = '';
        foreach ($priorityValues as $p)
        {
            $priorityValuesStr .= number_format($p, 1, '.', '').', ';
        }
        //Reset to default
        if(Tools::isSubmit('resetSitemap'))
        {
            foreach ($this->priority_options as $k=> $option)
            {
                if($k == 'product'){
                    $_POST[$option['name']] = 0.8;
                }
                elseif($k == 'category'){
                    $_POST[$option['name']] = 0.6;
                }
                else{
                    $_POST[$option['name']] = 0.1;
                }
                $_POST[$option['changefreq_name']] = 'weekly';
            }

            $_POST['ETS_SEO_SITEMAP_OPTION'] = array(
                'product', 'category', 'cms', 'cms_category', 'supplier', 'manufacturer', 'meta'
            );
            $_POST['ETS_SEO_PROD_SITEMAP_LIMIT'] = '';
        }
        if(Tools::isSubmit('submitOptionsconfiguration') || Tools::isSubmit('resetSitemap'))
        {
            $limit = Tools::getValue('ETS_SEO_PROD_SITEMAP_LIMIT');
            if($limit || $limit == '0')
            {
                if($limit == '0')
                {
                    $this->errors[] = $this->l('The Number product per page in sitemap pagination must be an unsigned integer');
                }
                elseif(!Validate::isUnsignedInt($limit))
                {
                    $this->errors[] = $this->l('The Number product per page in sitemap pagination must be an unsigned integer');
                }

            }
            if(($sitemapOp = Tools::getValue('ETS_SEO_SITEMAP_OPTION')) && is_array($sitemapOp))
            {
                $_POST['ETS_SEO_SITEMAP_OPTION'] = implode(',', $sitemapOp);
            }
            foreach ($this->priority_options as $option)
            {
                Configuration::updateValue($option['name'], (float)Tools::getValue($option['name']));
                Configuration::updateValue($option['changefreq_name'], Tools::getValue($option['changefreq_name']));
            }
        }

        parent::postProcess();
        if(Tools::isSubmit('submitOptionsconfiguration') || Tools::isSubmit('resetSitemap'))
        {
            if((int)Tools::getValue('ETS_SEO_ENABLE_XML_SITEMAP'))
            {
                $this->module->setSitemap();
            }
            else{
                $this->module->removeSitemap();
            }
            $sitemapOp = ($sitemapOp = Tools::getValue('ETS_SEO_SITEMAP_OPTION')) && Validate::isCleanHtml($sitemapOp) ? $sitemapOp : '';
            Configuration::updateValue('ETS_SEO_SITEMAP_OPTION', $sitemapOp);
            if(Tools::isSubmit('resetSitemap'))
            {
                $this->context->cookie->__set('reset_sitemap_success', 1);
            }
        }
    }

    public function setPriorityOptions()
    {
        $this->priority_options = array(
            'product' => array(
                'label' => $this->l('Products'),
                'value' => Configuration::get('ETS_SEO_SITEMAP_PRIORITY_PRODUCT'),
                'name' => 'ETS_SEO_SITEMAP_PRIORITY_PRODUCT',
                'changefreq_name' => 'ETS_SEO_SITEMAP_FREQ_PRODUCT',
                'changefreq_value' => Configuration::get('ETS_SEO_SITEMAP_FREQ_PRODUCT'),
            ),
            'category' =>  array(
                'label' => $this->l('Categories'),
                'value' => Configuration::get('ETS_SEO_SITEMAP_PRIORITY_CATEGORY'),
                'name' => 'ETS_SEO_SITEMAP_PRIORITY_CATEGORY',
                'changefreq_name' => 'ETS_SEO_SITEMAP_FREQ_CATEGORY',
                'changefreq_value' => Configuration::get('ETS_SEO_SITEMAP_FREQ_CATEGORY'),

            ),
            'cms' => array(
                'label' => $this->l('CMS'),
                'value' => Configuration::get('ETS_SEO_SITEMAP_PRIORITY_CMS'),
                'name' => 'ETS_SEO_SITEMAP_PRIORITY_CMS',
                'changefreq_name' => 'ETS_SEO_SITEMAP_FREQ_CMS',
                'changefreq_value' => Configuration::get('ETS_SEO_SITEMAP_FREQ_CMS'),
            ),
            'cms_category' => array(
                'label' => $this->l('CMS categories'),
                'value' => Configuration::get('ETS_SEO_SITEMAP_PRIORITY_CMS_CATEGORY'),
                'name' => 'ETS_SEO_SITEMAP_PRIORITY_CMS_CATEGORY',
                'changefreq_name' => 'ETS_SEO_SITEMAP_FREQ_CMS_CATEGORY',
                'changefreq_value' => Configuration::get('ETS_SEO_SITEMAP_FREQ_CMS_CATEGORY'),
            ),
            'supplier' => array(
                'label' => $this->l('Suppliers'),
                'value' => Configuration::get('ETS_SEO_SITEMAP_PRIORITY_SUPPLIER'),
                'name' => 'ETS_SEO_SITEMAP_PRIORITY_SUPPLIER',
                'changefreq_name' => 'ETS_SEO_SITEMAP_FREQ_SUPPLIER',
                'changefreq_value' => Configuration::get('ETS_SEO_SITEMAP_FREQ_SUPPLIER'),
            ),
            'manufacturer' => array(
                'label' => $this->l('Brands (manufacturers)'),
                'value' => Configuration::get('ETS_SEO_SITEMAP_PRIORITY_MANUFACTURER'),
                'name' => 'ETS_SEO_SITEMAP_PRIORITY_MANUFACTURER',
                'changefreq_name' => 'ETS_SEO_SITEMAP_FREQ_MANUFACTURER',
                'changefreq_value' => Configuration::get('ETS_SEO_SITEMAP_FREQ_MANUFACTURER'),
            ),
            'meta' =>array(
                'label' => $this->l('Other pages'),
                'value' => Configuration::get('ETS_SEO_SITEMAP_PRIORITY_META'),
                'name' => 'ETS_SEO_SITEMAP_PRIORITY_META',
                'changefreq_name' => 'ETS_SEO_SITEMAP_FREQ_META',
                'changefreq_value' => Configuration::get('ETS_SEO_SITEMAP_FREQ_META'),
            ),
        );
    }

}