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
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2021 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class EtsImportTranslation
{
    protected $fileImportDir = null;
    protected $moduleName = null;

    public function __construct($moduleName, $fileImportDir)
    {
        $moduleName = str_replace([' ', '_', '-'], ['', '', ''], trim($moduleName));
        $moduleName = Tools::ucfirst($moduleName);
        $this->moduleName = $moduleName;
        $this->fileImportDir = $fileImportDir;
    }

    public function import()
    {
        if(!$this->fileIsValid())
        {
           return false;
        }

        $xml = simplexml_load_file($this->fileImportDir);
        if(isset($xml->translation_item))
        {
            foreach ($xml->translation_item as $item)
            {
                $id_lang = (int)Language::getIdByIso($item->iso_code);
                if($id_lang)
                {
                    $exists = Db::getInstance()->getValue("SELECT id_translation FROM `"._DB_PREFIX_."translation` WHERE BINARY `key` = BINARY '".pSQL($item->key)."' AND `domain` = '".pSQL($item->domain)."' AND id_lang = ".(int)$id_lang."");
                    if(!$exists)
                        Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."translation` (id_lang, `key`, `translation`, `domain`, `theme`) VALUES (".(int)$id_lang.", '".pSQL($item->key)."', '".pSQL($item->translation)."', '".pSQL($item->domain)."', '".(string)$item->theme."')");
                }

            }
        }

    }

    public function fileIsValid()
    {
        $ext = pathinfo($this->fileImportDir, PATHINFO_EXTENSION);
        if($ext !== 'xml')
        {
            return false;
        }
        return true;
    }

    public function removeTranslation()
    {
        return Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."translation` WHERE `domain` LIKE 'Modules".$this->moduleName."%'");
    }
}