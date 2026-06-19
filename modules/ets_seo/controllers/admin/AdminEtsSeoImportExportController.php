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

require_once _PS_MODULE_DIR_ . 'ets_seo/classes/EtsSeoImportExport.php';

class AdminEtsSeoImportExportController extends ModuleAdminController
{
    public $import_export_options;
    /**
     * __construct
     *
     * @return void
     */
    
    public function __construct()
    {
        $this->bootstrap  = true;
        parent::__construct();

        $this->fields_options = array(
            'inport_export' => array(
                'title' => $this->l('Backup'),
                'icon'=> '',
                'fields' => array(),
            ),
        );

        $this->import_export_options = array(
            'config' => $this->l('Global SEO settings'),
            'redirect' => $this->l('URL redirects'),
            'product' => $this->l('Product'),
            'category' => $this->l('Product category'),
            'cms' => $this->l('CMS (pages)'),
            'cms_category' => $this->l('CMS category'),
            'supplier' => $this->l('Supplier'),
            'manufacturer' => $this->l('Brand (manufacturer)'),
            'meta' => $this->l('Other pages'),
        );
        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }
    }

    public function renderOptions()
    {
        $this->context->smarty->assign(array(
            'ets_seo_shops' => Shop::getShops(true),
            'ets_seo_options' => $this->import_export_options,
        ));
        return parent::renderOptions();
    }

    public function postProcess()
    {
        if(Tools::isSubmit('exportData'))
        {
            if(($shops = Tools::getValue('export_shops')) && ($seo_options = Tools::getValue('export_seo_options')))
            {
                if(is_array($shops) && is_array($seo_options)){
                    $importExport = new EtsSeoImportExport($shops, $seo_options);
                    $this->confirmations[] = $this->l("Export data successfully.");
                    $importExport->generateArchive();
                }
                else
                    $this->errors[] = $this->l("Options to export is invalid.");
            }
            else
                $this->errors[] = $this->l("Please choose shop and seo option to export data.");
            
        }
        elseif(Tools::isSubmit('importData'))
        {
            if(($shops = Tools::getValue('import_shops')) 
                && ($seo_options = Tools::getValue('import_seo_options'))
                && $_FILES['import_file']
            )
            {
                if(is_array($shops) && is_array($seo_options)) {
                    $importExport = new EtsSeoImportExport($shops, $seo_options);
                    $errors = $importExport->processImport();
                    if ($errors) {
                        $this->errors = $errors;
                    } else {
                        $this->confirmations[] = $this->l("Import data successfully.");
                    }
                }
                else{
                    $this->errors[] = $this->l("Options to import is invalid.");
                }
            }
            else
                $this->errors[] = $this->l("Please choose shop and seo option to import data.");
        }
    }


}