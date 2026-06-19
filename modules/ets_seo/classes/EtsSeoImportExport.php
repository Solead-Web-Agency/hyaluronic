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

if (!defined('_PS_VERSION_')) {
    exit();
}
require_once dirname(__FILE__).'/EtsSeoProduct.php';
require_once dirname(__FILE__).'/EtsSeoCategory.php';
require_once dirname(__FILE__).'/EtsSeoCms.php';
require_once dirname(__FILE__).'/EtsSeoCmsCategory.php';
require_once dirname(__FILE__).'/EtsSeoManufacturer.php';
require_once dirname(__FILE__).'/EtsSeoSupplier.php';
require_once dirname(__FILE__).'/EtsSeoMeta.php';
require_once dirname(__FILE__).'/EtsSeoRedirect.php';
//require_once _PS_MODULE_DIR_.'ets_seo/defines.php';

class EtsSeoImportExport extends Module
{
    /**
     * @var array
     */
    public $process = array();

    public $export_cache_path;
    public $import_cache_path;

    public $shops_selected;
    public $options_selected;
    public $l;

    public	function __construct($shops_selected = array(), $options_selected = array())
	{
		$this->name = 'ets_seo';
		parent::__construct();
        $this->module= new Ets_Seo();
        $this->process = array(
            'product' => 'category',
            'category' => 'cms',
            'cms' => 'cms_category',
            'cms_category' => 'meta',
            'meta' => 'supplier',
            'supplier' => 'manufacturer',
            'manufacturer' => 'redirect',
            'redirect' => 'config',
            'config' => 'image',
            'image' => null
        );

        $this->export_cache_path = _PS_MODULE_DIR_.'ets_seo/cache/export';
        $this->import_cache_path = _PS_MODULE_DIR_.'ets_seo/cache/import';
        $this->shops_selected = $shops_selected;
        $this->options_selected = $options_selected;

    }
    
    private function archiveThisFile($obj, $file, $server_path, $archive_path)
    {
        if (is_dir($server_path.$file)) {
            $dir = scandir($server_path.$file);

            foreach ($dir as $row) {
                if ($row[0] != '.') {
                    $this->archiveThisFile($obj, $row, $server_path.$file.'/', $archive_path.$file.'/');
                }
            }
        } else 
        {
            $obj->addFile($server_path.$file, $archive_path.$file);
        }
    }

    public function generateArchive()
    {

        $errors = array();
        $zip = new ZipArchive();
        $cacheDir = _PS_ROOT_DIR_.'/cache/ets_seo';
        $zip_file_name = 'ets_seo_'.date('dmYHis').'.zip';
        if ($zip->open($cacheDir.$zip_file_name, ZipArchive::OVERWRITE | ZipArchive::CREATE) === true) {
            if (!$zip->addFromString('ets_seo_data.xml', $this->exportData())) {
               $errors[] = $this->module->l('Cannot create ets_seo_data.xml');
            }
            //$this->archiveThisFile($zip,'banner', dirname(__FILE__).'/../views/img/', 'img/');
            $zip->close();
            if (!is_file($cacheDir.$zip_file_name)) {
                $errors[] = $this->module->l(sprintf('Could not create %1s', _PS_CACHE_DIR_.$zip_file_name));
            }
            if (!$errors) {
                if (ob_get_length() > 0) {
                    ob_end_clean();
                }
    
                ob_start();
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: public');
                header('Content-Description: File Transfer');
                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.$zip_file_name.'"');
                header('Content-Transfer-Encoding: binary');
                ob_end_flush();
                readfile($cacheDir.$zip_file_name);
                @unlink($cacheDir.$zip_file_name);
                exit;
            }
        }
        return $errors;
    }

    protected function exportData()
    {

    	$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<entity_profile>'."\n";	
        
        foreach($this->shops_selected as $id_shop)
        {
            foreach($this->options_selected as $option)
            {
                if($option !== 'config')
                {
                    $table_name = $this->getTableNameByType($option);
                    $lists = Db::getInstance()->executeS("SELECT * FROM "._DB_PREFIX_.(string)$table_name." WHERE id_shop=".(int)$id_shop);
                    foreach($lists as $item)
                    {
                        $xml .= '<'.$table_name.'>';
                            foreach($item as $col=>$val)
                            {
                                $xml .= '<'.$col.'><![CDATA['.(string)$val.']]></'.$col.'>';
                            }
                        $xml .= '</'.$table_name.'>';
                    }
                }
                else{
                    $xml .= $this->exportConfig($id_shop);
                }
               
            }
            
        }
        

		$xml .= '</entity_profile>'."\n";
		$xml = str_replace('&', 'and', $xml);
		return $xml;	
    }

    public function getTableNameByType($table_type)
    {
        $table_name = '';
        switch ($table_type) {
                case 'product':
                    $table_name = 'ets_seo_product';
                    break;
                
                case 'category':
                    $table_name = 'ets_seo_category';
                    break;
                
                case 'cms':
                    $table_name = 'ets_seo_cms';
                    break;
                
                case 'cms_category':
                    $table_name = 'ets_seo_cms_category';
                    break;
                
                case 'meta':
                    $table_name = 'ets_seo_meta';
                    break;
                
                case 'manufacturer':
                    $table_name = 'ets_seo_manufacturer';
                    break;
                
                case 'supplier':
                    $table_name = 'ets_seo_supplier';
                    break;
                
                case 'redirect':
                    $table_name = 'ets_seo_redirect';
                    break;
                case 'config':
                    $table_name = 'configuration';
                    break;
            }
        return $table_name;
    }

    protected function exportConfig($id_shop)
    {
        $seoDef = Ets_Seo_Define::getInstance();
        $configs = $seoDef->fields_config();
        $languages = Language::getLanguage(false);
        
        $xml = '';
        foreach($configs as $group)
        {
            foreach($group as $k=>$config)
            {
                if(isset($config['textlang']) || isset($config['textareaLang']) || isset($config['selectLang']))
                {
                    $xml .= '<configuration>';
                        $xml .= '<name><![CDATA['.$k.']]></name>';
                        $xml .= '<id_shop>'.(int)$id_shop.'</id_shop>';
                    foreach($languages as $lang)
                    {
                        $xml .= '<language>';
                            $xml .= '<id_lang>'.(int)$lang['id_lang'].'</id_lang>';
                            $xml .= '<iso_code>'.(string)$lang['iso_code'].'</iso_code>';
                            $xml .= '<value><![CDATA['.(string)Configuration::get($k, $lang['id_lang'], null, (int)$id_shop).']]></value>';
                        $xml .= '</language>';
                    }
                    $xml .= '</configuration>';
                    
                }
                else{
                    $xml .= '<configuration>';
                        $xml .= '<name><![CDATA['.$k.']]></name>';
                        $xml .= '<value><![CDATA['.(string)Configuration::get($k, null,  null, (int)$id_shop).']]></value>';
                    $xml .= '</configuration>';
                }
            }
        }

        //Export config seo url 
        $urlRules = $seoDef->url_rules();
        foreach($urlRules as $k=>$rule)
        {
            if($rule){}
            $xml .= '<configuration>';
                $xml .= '<name>PS_ROUTE_'.$k.'</name>';
                $xml .= '<value><![CDATA['.(string)Configuration::get('PS_ROUTE_'.$k, null,  null, (int)$id_shop).']]></value>';
            $xml .= '</configuration>';
        }
        return $xml;
    }

    public function processImport($zipfile = false)
    {
        $errors = array();
        if($_FILES['import_file'])
        {
            if(!$zipfile)
            {
                $savePath = _PS_ROOT_DIR_.'/cache/ets_seo/';
                if(@file_exists($savePath.'ets_seo_data.zip')){
                    @unlink($savePath.'ets_seo_data.zip');
                }
                
                $uploader = new Uploader('import_file');
                $uploader->setMaxSize(1048576000);
                $uploader->setAcceptTypes(array('zip'));        
                $uploader->setSavePath($savePath);
                $file = $uploader->process('ets_seo_data.zip');

                if ($file[0]['error'] === 0) {
                    if (!Tools::ZipTest($savePath.'ets_seo_data.zip')) 
                        $errors[] = $this->module->l('Zip file seems to be broken');
                } else {
                    $errors[] = $file[0]['error'];
                }
                $extractUrl = $savePath.'ets_seo_data.zip';
            }
            else      
                $extractUrl = $zipfile;
            if(!@file_exists($extractUrl))
                $errors[] = $this->module->l('Zip file doesn\'t exist');
            if(!$errors)
            {
                $zip = new ZipArchive();
                if($zip->open($extractUrl) === true)
                {
                    if ($zip->locateName('ets_seo_data.xml') === false)
                    {
                        $errors[] = $this->module->l('ets_seo_data.xml doesn\'t exist');                    
                        if($extractUrl && !$zipfile)
                        {
                            @unlink($extractUrl);                        
                        }                      
                    }
                }
                else
                    $errors[] = $this->module->l('Cannot open zip file. It might be broken or damaged');
            } 
            if(!$errors)
            {
                if(!Tools::ZipExtract($extractUrl, dirname(__FILE__).'/../views/'))
                    $errors[] = $this->module->l('Cannot extract zip data');
                if(!@file_exists(dirname(__FILE__).'/../views/ets_seo_data.xml'))
                    $errors[] = $this->module->l('Neither ets_seo_data.xml exist');
            }       
            if(!$errors)
            {            
                if(@file_exists(dirname(__FILE__).'/../views/ets_seo_data.xml'))
                {
                    $this->importData(dirname(__FILE__).'/../views/ets_seo_data.xml');
                    @unlink(dirname(__FILE__).'/../views/ets_seo_data.xml');
                }
                $zip->close();
                if(@file_exists($extractUrl))
                    @unlink($extractUrl);              
            }
        }
        else
        {
            $errors[]= $this->module->l('Data import is null');
        }
        return $errors;
    }

    /**
     * importData
     *
     * @param  string $file_xml path to data xml file
     *
     * @return void
     */
    public function importData($file_xml)
    {
        $configImgName = array(
            'ETS_SEO_SITE_ORIG_LOGO',
            'ETS_SEO_SITE_PERSON_AVATAR',
            'ETS_SEO_FACEBOOK_FP_IMG_URL',
            'ETS_SEO_FACEBOOK_DEFULT_IMG_URL'
        );
        if (file_exists($file_xml))	
		{	
            $xml = simplexml_load_file($file_xml);
            
            foreach($this->shops_selected as $id_shop)
            {
                foreach($this->options_selected as $option)
                {
                    if($option !== 'config')
                    {
                        $table = $this->getTableNameByType($option);
                        if(isset($xml->{$table}) && $xml->{$table})
                        {
                            foreach($xml->{$table} as $tbl)
                            {
                                $col_list = array();
                                $val_list = array();
                                if(isset($tbl->id_shop) && (int)$tbl->id_shop == $id_shop)
                                {
                                    foreach($tbl as $col=>$val)
                                    {
                                        if($col == 'social_img')
                                        {
                                            $col_list[] = (string)$col;
                                            if((string)$val && !file_exists(_PS_ROOT_DIR_.'img/social/'.(string)$val))
                                            {
                                                $val_list[] = null;
                                            }
                                            else{
                                                 $val_list[] = (string)$val;
                                            }
                                        }
                                        elseif($col !== 'id_'.$table)
                                        {
                                            
                                            $col_list[] = (string)$col;
                                            
                                            if($col == 'id_lang')
                                            {
                                                $id_lang = isset($tbl->id_lang) ? (int)$tbl->id_lang : '';
                                                if(isset($tbl->id_lang) && isset($tbl->iso_code) && $tbl->iso_code)
                                                {
                                                    $id_lang = Language::getIdByIso((int)$tbl->iso_code); 
                                                }
                                                $val_list[] = (int)$id_lang;
                                            }
                                            else{
                                                $val_list[] = (string)$val;
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                }
                                if($col_list && $val_list)
                                {
                                    try{
                                        Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_.(string)$table." (".implode(',',$col_list).") VALUES( '".implode('\',\'', $val_list)."')");
                                    }
                                    catch(Exception $ex){
                                        //
                                    }
                                    
                                }
                                
                            }
                        }
                    }
                    else{
                        if(isset($xml->configuration))
                        {
                            foreach($xml->configuration as $config)
                            {
                                if((int)$config->id_shop == $id_shop)
                                {
                                    if(isset($config->language))
                                    {
                                        $configLang = array();
                                        foreach($config->language as $cl)
                                        {
                                            $id_lang = Language::getIdByIso((string)$cl->iso_code);
                                            $configLang[(int)$id_lang] = (string)$cl->value;
                                        }
                                        
                                        Configuration::updateValue((string)$config->name, $configLang, false, null, $id_shop);
                                    }
                                    else{
                                        $configVal = (string)$config->value;
                                        if(in_array((string)$config->name, $configImgName) && $configVal)
                                        {  
                                            if(!file_exists(_PS_ROOT_DIR_.'img/social/'.$configVal))
                                            {
                                                $configVal = null;
                                            }
                                        }
                                        Configuration::updateValue((string)$config->name, $configVal, false, null, $id_shop);
                                    }
                                }
                                
                            }
                        }
                        
                    }
                }
            }
        }

    }
}