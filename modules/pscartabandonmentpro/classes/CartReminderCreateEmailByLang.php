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
* versions in the future.If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

class CartReminderCreateEmailByLang
{
    public $folderBase;

    public function __construct()
    {
        $this->folderBase = 'en';
    }

    /**
     * Check if we can create a new folder in the folder and if we can read/write the copied files.
     *
     * @param  string $folder
     *
     * @return bool
     */
    public function checkIfWeCanCreateFolders($folder)
    {
        if (!is_writable($folder)) {
            return false;
        }

        if (!is_executable($folder)) {
            return false;
        }

        if (!is_readable($folder.$this->folderBase)) {
            return false;
        }

        if (!is_executable($folder.$this->folderBase)) {
            return false;
        }

        return true;
    }

    /**
     * For each languages, check if the language's folder exists and create it if it doesn't
     *
     * @param  string $folder
     * @param  array $aLanguages
     *
     * @return bool
     */
    public function intializeEmailTemplatesByLanguage($folder, $aLanguages)
    {
        foreach ($aLanguages as $key => $aLanguage) {
            $isoCode = $aLanguage['iso_code'];
            
            if ($this->checkIfEmailLanguageFolderExists($folder, $isoCode)) {
                continue;
            }

            $this->createEmailLanguageFolder($folder, $isoCode);
        }

        return true;
    }

    /**
     * Check if the folder exists
     *
     * @param  string $folder
     *
     * @return void
     */
    private function checkIfEmailLanguageFolderExists($folder, $isoCode)
    {
        if (file_exists($folder.$isoCode)) {
            return true;
        }

        return false;
    }

    /**
     * Copy recursively the default folder (folderBase) for each language
     *
     * @param  string $folder
     * @param  string $isoCode
     *
     * @return void
     */
    private function createEmailLanguageFolder($folder, $isoCode)
    {
        $sourceFolder = $folder.$this->folderBase;
        $destinationFolder = $folder.$isoCode;

        $dir = opendir($sourceFolder);
        mkdir($destinationFolder);
        while ($file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($sourceFolder.'/'.$file)) {
                    $this->recursive_copy($sourceFolder.'/'.$file, $destinationFolder.'/'.$file);
                }
                else {
                    copy($sourceFolder.'/'.$file,$destinationFolder.'/'.$file);
                }
            }
        }
        closedir($dir);
    }

}