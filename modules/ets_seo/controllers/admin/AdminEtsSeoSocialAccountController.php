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

class AdminEtsSeoSocialAccountController extends ModuleAdminController
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
            'rss_setting' => array(
                'title' => $this->l('Organization social profiles'),
                'icon'=> '',
                'fields' => $seoDef->fields_config()['social_account'],
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

    public function postProcess()
    {
        $seoDef = Ets_Seo_Define::getInstance();
        $configs = $seoDef->fields_config()['social_account'];
        if(Tools::isSubmit('submitOptionsconfiguration')){
            foreach ($configs as $key=>$config)
            {
                if(($url = Tools::getValue($key)) && $key != 'ETS_SEO_URL_TWITTER')
                {
                    if(!Validate::isAbsoluteUrl($url))
                    {
                        $this->errors[] = $this->l('The').' '.$config['title'].' '.$this->l('must start with http:// or https://');
                    }
                }
            }
        }
        return parent::postProcess();
    }

}