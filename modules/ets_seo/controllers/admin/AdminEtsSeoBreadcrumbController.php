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

class AdminEtsSeoBreadcrumbController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        $seoDef = Ets_Seo_Define::getInstance();
        $this->fields_options = array(
            'breadcrumb' => array(
                'title' => $this->l('Breadcrumb snippet'),
                'fields' => array_merge($seoDef->fields_config()['breadcrumb_general'],$seoDef->fields_config()['breadcrumb_types']),
                'icon'=> '',
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'description' => $this->l('Search engines use breadcrumb markup to categorize the information from the page in search results. A breadcrumb trail indicates the page position in the site hierarchy, allows users to navigate through your site easier.'),
            ),
        );
        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }
    }

}