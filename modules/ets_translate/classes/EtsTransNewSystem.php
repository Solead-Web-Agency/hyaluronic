<?php
/**
 * 2007-2020 ETS-Soft
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
 * @copyright  2007-2020 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class EtsTransNewSystem extends EtsTransCore
{
    public $themeName = null;
    public $moduleName = null;
    public $selectedName = null;
    protected $isAnalyzing = false;
    protected $context;

    public function __construct($type = 'theme', $context = null)
    {
        parent::__construct($type);
        if (!$context) {
            $context = Context::getContext();
        }
        $this->context = $context;
    }

    public function setSelectedName($selectedName)
    {
        $this->selectedName = $selectedName;
    }

    public function setAnalyzing($status)
    {
        $this->isAnalyzing = $status;
    }

    public function loadFileSystem($step = 1)
    {
        switch ($this->type) {
            case 'theme':
                $this->loadThemeFiles();
                return true;
            case 'sfmodule':
                $this->loadModuleFiles();
                return true;
            case 'mail':
                $this->setCacheMailSubject();
                return true;
            case 'others':
                $this->setCacheOthers();
                return true;
            case 'back':
                $this->loadBackOfficeFiles($step);
                if ($step >= 7) {
                    return true;
                }
                return null;
            default:
                return false;
        }
    }


    public function translateData($selectedName = null)
    {
        if (!$selectedName) {
            $selectedName = $this->selectedName;
        }
        $result = array(
            'errors' => true,
            'nb_translated' => 0,
            'nb_char_translated' => 0,
            'stop_translate' => 1,
            'file_name' => array(),
            'text_translated' => array()
        );
        if (!isset($this->langSource) || !isset($this->langTarget)) {
            return $result;
        }
        if ($this->type == 'mail') {
            $originTextArray = $this->getEmailSubject();
        } elseif ($this->type == 'others') {
            $originTextArray = $this->getOtherTranslate();
        } else
            $originTextArray = $this->getTextTranslate($this->type, $selectedName);
        if (!$originTextArray && !$this->getTotalRemainFile($this->type, $selectedName)) {
            $this->deleteCacheTranslate($originTextArray);
            $result['errors'] = false;
            return $result;
        }
        if (!$originTextArray) {
            $result['errors'] = false;
            $result['nb_translated'] = 0;
            $result['nb_char_translated'] = 0;
            $result['stop_translate'] = 0;
            return $result;
        }
        $textTransArray = $this->formatTextArray($originTextArray);
        $api = new EtsTransApi();
        $source = Language::getIsoById($this->langSource);
        $totalChar = 0;
        $textWillTrans = array();
        foreach ($this->langTarget as $idLang) {
            $target = Language::getIsoById($idLang);
            $textTransArray = $this->filterTextTranslate($textTransArray, $target);
            $textTrans = array_column($textTransArray, 'text');
            if (!$textWillTrans) {
                $textWillTrans = $textTransArray;
            }
            $textTrans = $this->modifyTextTranslating($textTrans);
            $totalChar += $this->getTotalCharTrans($textTrans);
            $resultTrans = $api->translate($source, $target, $textTrans, $this->type);
            if (isset($resultTrans['errors']) && $resultTrans['errors']) {
                $result['errors'] = true;
                $result['message'] = isset($resultTrans['message']) ? $resultTrans['message'] : '';
                $result['stop_translate'] = 1;
                return $result;
            }
            $textTranslated = $resultTrans['data'];
            $textTranslatedArray = $this->modifyTextTranslated($textTranslated, $textTransArray);
            $this->executeTextTranslate($textTranslatedArray, $source, $target);
        }
        if (!connection_aborted())
            $this->deleteCacheTranslate($originTextArray);
        $result['errors'] = false;
        $result['nb_translated'] = count($textWillTrans);
        $result['nb_char_translated'] = $totalChar;
        $result['stop_translate'] = $this->type == 'mail' ? 1 : 0;
        $result['file_name'] = $this->getFilePathTranslated($originTextArray);
        return $result;
    }

    public function getFilePathTranslated($textArray)
    {
        $result = array();
        foreach ($textArray as $item)
        {
            if(!isset($item['path'])){
                continue;
            }
            $path = str_replace(_PS_ROOT_DIR_, '',$item['path']);
            if(!in_array($path, $result)){
                $result[] = $path;
            }
        }
        return $result;
    }

    public function deleteCacheTranslate($textTransArray)
    {
        $id_shop = Context::getContext()->shop->id;
        $filterWhere = "";
        if ($this->isOneClick) {
            $filterWhere .= " AND is_oneclick=" . (int)$this->isOneClick;
        }
        if ($this->type == 'mail') {
            return Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `cache_type`= 'mail' AND id_shop=" . (int)$id_shop . (string)$filterWhere);
        }
        if ($this->type == 'others') {
            return Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `cache_type`= 'others' AND id_shop=" . (int)$id_shop . (string)$filterWhere);
        }
        foreach ($textTransArray as $item) {
            if (!isset($item['id_cache'])) {
                continue;
            }
            Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `id_ets_trans_cache`=" . (int)$item['id_cache'] . (string)$filterWhere);
        }
        return true;
    }

    public function getTextTranslate($type, $selected, $textTransBefore = array(), $minId = 0, $nbFile = 1)
    {
        $filterExtra = "";
        if ($this->isAnalyzing) {
            $filterExtra .= " AND status = 0";
        }
        $cacheTrans = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "ets_trans_cache` 
        WHERE `cache_type`='" . (string)$type . "' AND `name`='" . (string)$selected . "' AND `id_ets_trans_cache` > " . (int)$minId . " AND `id_shop`=" . (int)$this->context->shop->id . (string)$filterExtra);
        if (!$cacheTrans) {
            $config = EtsTransConfig::getInstance();
            $config->deletePauseData($type, $selected, $type, $this->langTarget);
            return $textTransBefore;
        }
        $filePath = $cacheTrans['file_path'];
        $fileType = $cacheTrans['file_type'];

        $textTrans = $this->getTextTranslateFormFilePath($filePath, $fileType, true);
        foreach ($textTrans as &$text) {
            if ($fileType !== 'xlf') {
                $text['path'] = $filePath;
            }
            $text['id_cache'] = $cacheTrans['id_ets_trans_cache'];
        }
        if (!$textTrans) {
            $filterWhere = "";
            if ($this->isOneClick) {
                $filterWhere = " AND is_oneclick=" . (int)$this->isOneClick;
            }
            Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `id_ets_trans_cache`=" . (int)$cacheTrans['id_ets_trans_cache'] . (string)$filterWhere);
        } else {
            if ($this->isAnalyzing) {
                $cacheItem = new EtsTransCache((int)$cacheTrans['id_ets_trans_cache']);
                $cacheItem->status = 1;
                $cacheItem->update();
            }
        }
        if ($textTransBefore) {
            $textTrans = array_merge($textTransBefore, $textTrans);
        }

        if (count($textTrans) < 50 && $nbFile < 10) {
            return $this->getTextTranslate($type, $selected, $textTrans, $cacheTrans['id_ets_trans_cache'], $nbFile += 1);
        }

        return $textTrans;
    }


    public function loadThemeFiles()
    {
        if (!isset($this->selectedName) || !$this->selectedName) {
            return false;
        }
        $filterWhere = "";
        if ($this->isOneClick) {
            $filterWhere = " AND `is_oneclick`=" . (int)$this->isOneClick;
        }
        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` 
                WHERE `cache_type`='theme' AND `name`='" . pSQL($this->selectedName) . "' AND `id_shop`=" . (int)$this->context->shop->id . (string)$filterWhere);
        $themeDir = _PS_ROOT_DIR_ . '/themes/' . $this->selectedName;
        $directories = array(
            'tpl' => $this->listFiles($themeDir . '/templates/', array(), 'tpl'),
            'xlf' => $this->getTransShopDomain(),
        );
        foreach ($directories as $type => $files) {
            foreach ($files as $file) {
                $cache = new EtsTransCache();
                $cache->cache_type = 'theme';
                $cache->name = $this->selectedName;
                $cache->file_path = $file;
                $cache->file_type = $type;
                $cache->id_shop = $this->context->shop->id;
                if ($this->isOneClick) {
                    $cache->is_oneclick = (int)$this->isOneClick;
                }
                $cache->save();
            }

        }
    }

    public function loadModuleFiles()
    {
        if (!isset($this->selectedName) || !$this->selectedName) {
            return false;
        }
        $filterWhere = "";
        if ($this->isOneClick) {
            $filterWhere = " AND `is_oneclick`=" . (int)$this->isOneClick;
        }
        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` 
                WHERE `cache_type`='sfmodule' AND `name`='" . pSQL($this->selectedName) . "' AND `id_shop`=" . (int)$this->context->shop->id . (string)$filterWhere);
        $moduleDir = _PS_MODULE_DIR_ . $this->selectedName;
        if(!@file_exists($moduleDir)){
            return false;
        }
        $moduleFiles = scandir($moduleDir);
        $context = Context::getContext();
        $rootDirFiles = array();
        $rootDirFilesTpl = array();
        foreach ($moduleFiles as $file) {
            if (in_array($file, self::$ignorefile)) {
                continue;
            }
            $filePath = $moduleDir . DIRECTORY_SEPARATOR . $file;

            if ($file == '.' || $file == '..' || !@is_file($filePath)) {
                continue;
            }
            if (pathinfo($filePath, PATHINFO_EXTENSION) == 'php') {
                $rootDirFiles[] = $moduleDir . DIRECTORY_SEPARATOR . $file;
            } else if (pathinfo($filePath, PATHINFO_EXTENSION) == 'tpl') {
                $rootDirFilesTpl[] = $moduleDir . DIRECTORY_SEPARATOR . $file;
            }
        }
        unset($file);
        $phpFiles = array_merge($rootDirFiles, $this->listFiles($moduleDir . '/classes/', array(), 'php'));
        $phpFiles = array_merge($phpFiles, $this->listFiles($moduleDir . '/controllers/', array(), 'php'));
        $directories = array(
            'tpl' => array_merge($rootDirFilesTpl, $this->listFiles($moduleDir . '/views/templates/', array(), 'tpl')),
            'twig' => $this->listFiles($moduleDir . '/views/PrestaShop/', array(), 'twig'),
            'php' => $phpFiles,
        );

        foreach ($directories as $type => $files) {
            foreach ($files as $file) {
                $cache = new EtsTransCache();
                $cache->cache_type = 'sfmodule';
                $cache->name = $this->selectedName;
                $cache->file_path = $file;
                $cache->file_type = $type;
                $cache->id_shop = $context->shop->id;
                if ($this->isOneClick) {
                    $cache->is_oneclick = (int)$this->isOneClick;
                }
                $cache->save();
            }
        }
    }

    public function loadBackOfficeFiles($step = 1)
    {
        if ($step == 1) {
            $filterWhere = "";
            if ($this->isOneClick) {
                $filterWhere = " AND `is_oneclick`=" . (int)$this->isOneClick;
            }
            Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` 
                WHERE `cache_type`='back' AND `name`='" . pSQL($this->selectedName) . "' AND `id_shop`=" . (int)$this->context->shop->id . (string)$filterWhere);
            $themeDir = _PS_ROOT_DIR_ . '/' . $this->adminFD . '/themes';
            $listThemes = scandir($themeDir);
            foreach ($listThemes as $folder) {
                if ($folder == '.' || $folder == '..' || in_array($folder, self::$ignorefile)) {
                    continue;
                }
                if (!@is_dir($themeDir . '/' . $folder)) {
                    continue;
                }

                $directories = array(
                    'tpl' => $this->listFiles($themeDir . '/' . $folder . '/template/', array(), 'tpl'),
                );

                foreach ($directories as $type => $files) {
                    foreach ($files as $file) {
                        $cache = new EtsTransCache();
                        $cache->cache_type = 'back';
                        $cache->name = $this->selectedName;
                        $cache->file_path = $file;
                        $cache->file_type = $type;
                        $cache->id_shop = $this->context->shop->id;
                        if ($this->isOneClick) {
                            $cache->is_oneclick = (int)$this->isOneClick;
                        }
                        $cache->save();
                    }
                }
            }
        } elseif (in_array($step, array(2, 3, 4, 5, 6, 7))) {
            $fileType = 'php';
            switch ($step) {
                case 2:
                    $files = $this->scanClassesPres();
                    break;
                case 3:
                    $files = $this->scanControllerPresta();
                    break;
                case 4:
                    $files = $this->scanSrcPrestaBundle('Adapter');
                    break;
                case 5:
                    $files = $this->scanSrcPrestaBundle('Core');
                    break;
                case 6:
                    $files = $this->scanSrcPrestaBundle('PrestaShopBundle');
                    break;
                case 7:
                    $files = $this->scanTwigFilePresta();
                    $fileType = 'twig';
                    break;
            }
            if (isset($files)) {
                foreach ($files as $item) {
                    if ($step == 3 && (int)Configuration::get('ETS_TRANS_IGNORE_OLD_CONTROLLER')) {
                        $fileName = basename($item);
                        if (@is_file(_PS_ROOT_DIR_ . '/src/PrestaShopBundle/Controller/Admin/' . $fileName)) {
                            continue;
                        }
                    }

                    $cache = new EtsTransCache();
                    $cache->cache_type = 'back';
                    $cache->name = $this->selectedName;
                    $cache->file_path = $item;
                    $cache->file_type = $fileType;
                    $cache->id_shop = $this->context->shop->id;
                    if ($this->isOneClick) {
                        $cache->is_oneclick = (int)$this->isOneClick;
                    }
                    $cache->save();
                }
            }
        }

    }

    public function getEmailSubject()
    {
        $dom = new DOMDocument();
        $dom->formatOutput = true;
        $xmlStr = Tools::file_get_contents(_PS_ROOT_DIR_ . '/app/Resources/translations/default/EmailsSubject.xlf');
        if (!$xmlStr) {
            return array();
        }
        $textTrans = array();
        $dom->loadXML($xmlStr);
        $nodeFiles = $dom->getElementsByTagName('file');
        foreach ($nodeFiles as $nodeFile) {
            $original = $nodeFile->getAttribute('original');
            $transUnits = $nodeFile->getElementsByTagName('trans-unit');
            foreach ($transUnits as $transUnit) {
                $textTrans[] = array(
                    'text' => $transUnit->getElementsByTagName('source')->item(0)->nodeValue,
                    'path' => $original,
                    'domain' => 'EmailsSubject',
                );
            }
        }
        return $textTrans;
    }

    public function getOtherTranslate()
    {
        $dom = new DOMDocument();
        $dom->formatOutput = true;
        $xmlStr = Tools::file_get_contents(_PS_ROOT_DIR_ . '/app/Resources/translations/default/messages.xlf');
        if (!$xmlStr) {
            return array();
        }
        $textTrans = array();
        $dom->loadXML($xmlStr);
        $nodeFiles = $dom->getElementsByTagName('file');
        foreach ($nodeFiles as $nodeFile) {
            $original = $nodeFile->getAttribute('original');
            $transUnits = $nodeFile->getElementsByTagName('trans-unit');
            foreach ($transUnits as $transUnit) {
                $textTrans[] = array(
                    'text' => $transUnit->getElementsByTagName('source')->item(0)->nodeValue,
                    'path' => $original,
                    'domain' => 'messages',
                );
            }
        }
        return $textTrans;
    }


    public function getTransShopDomain()
    {
        $transDefaultDir = _PS_ROOT_DIR_ . '/app/Resources/translations/default';
        if (!@is_dir($transDefaultDir)) {
            return array();
        }
        $files = scandir($transDefaultDir);
        $paths = array();
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (strpos($file, 'Shop.xlf') !== false) {
                $paths[] = $transDefaultDir . '/' . $file;
            }
        }
        return $paths;
    }

    public function scanClassesPres()
    {
        $whitelist = array(
            'controller',
            'form',
            'helper',
            'checkout',
            'module',
            'order',
            'pdf',
            'stock',
            'tax',
            'webservice',
        );
        $classDir = _PS_ROOT_DIR_ . '/classes';
        $classes = scandir($classDir);
        $listPaths = array();
        foreach ($classes as $item) {
            if (in_array($item, self::$ignorefile)) {
                continue;
            }
            if (@is_dir($classDir . '/' . $item) && in_array($item, $whitelist)) {
                $paths = $this->listFiles($classDir . '/' . $item, array(), 'php');
                $listPaths = array_merge($listPaths, $paths);
            } else if (@is_file($classDir . '/' . $item)
                && pathinfo($classDir . '/' . $item, PATHINFO_EXTENSION) == 'php') {
                $listPaths[] = $classDir . '/' . $item;
            }
        }
        return $listPaths;
    }

    public function scanControllerPresta()
    {
        $controllerDir = _PS_ROOT_DIR_ . '/controllers';
        return $this->listFiles($controllerDir, array(), 'php');
    }

    public function scanSrcPrestaBundle($folderName = '')
    {
        $srcDir = _PS_ROOT_DIR_ . '/src';
        if ($folderName) {
            $srcDir = $srcDir . '/' . trim($folderName, '/');
        }
        $listFiles = scandir($srcDir);
        $listPaths = array();
        foreach ($listFiles as $item) {
            if (in_array($item, self::$ignorefile)) {
                continue;
            }
            if (@is_dir($srcDir . '/' . $item)) {
                $paths = $this->listFiles($srcDir . '/' . $item, array(), 'php');
                $listPaths = array_merge($listPaths, $paths);
            } else if (@is_file($srcDir . '/' . $item)
                && pathinfo($srcDir . '/' . $item, PATHINFO_EXTENSION) == 'php') {
                $listPaths[] = $srcDir . '/' . $item;
            }
        }
        return $listPaths;
    }

    public function scanTwigFilePresta()
    {
        $twigDir = _PS_ROOT_DIR_ . '/src/PrestaShopBundle/Resources/views/Admin';
        $twigFiles = scandir($twigDir);
        $listPaths = array();
        foreach ($twigFiles as $item) {
            if (in_array($item, self::$ignorefile)) {
                continue;
            }
            if (@is_dir($twigDir . '/' . $item)) {
                $paths = $this->listFiles($twigDir . '/' . $item, array(), 'twig');
                $listPaths = array_merge($listPaths, $paths);
            } else if (@is_file($twigDir . '/' . $item)
                && pathinfo($twigDir . '/' . $item, PATHINFO_EXTENSION) == 'twig') {
                $listPaths[] = $twigDir . '/' . $item;
            }
        }
        return $listPaths;
    }

    public function analysisFiles()
    {
        if ($this->type == 'mail') {
            $originTextArray = $this->getEmailSubject();
        } else if ($this->type == 'others') {
            $originTextArray = $this->getOtherTranslate();
        } else
            $originTextArray = $this->getTextTranslate($this->type, $this->selectedName);
        $textTransArrayOrigin = $this->formatTextArray($originTextArray);
        $api = new EtsTransApi();
        $nbChar = 0;
        $textToCount = array();
        foreach ($this->langTarget as $idLang) {
            $target = Language::getIsoById($idLang);
            $textTransArray = $this->filterTextTranslate($textTransArrayOrigin, $target);
            foreach ($textTransArray as $text) {
                $nbChar += Tools::strlen($text['text']);
            }
            if (!$textToCount)
                $textToCount = $textTransArray;
        }
        $filterWhere = "";
        if ($this->isOneClick) {
            $filterWhere .= " AND is_oneclick=" . (int)$this->isOneClick;
        }
        if ($this->isOneClick && ($this->type = 'mail' || $this->type == 'others')) {
            Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "ets_trans_cache` SET `status`=1 WHERE `cache_type` = '" . (string)$this->type . "' AND `is_oneclick`=" . (int)$this->isOneClick . " AND `id_shop`=" . (int)$this->context->shop->id);
        }
        $totalItem = (int)Db::getInstance()->getValue("SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `cache_type`='" . (string)$this->type . "' AND `name`='" . pSQL($this->selectedName) . "' AND status=0 AND id_shop=" . (int)$this->context->shop->id . (string)$filterWhere);
        return array(
            'nb_text' => $nbChar ? count($textToCount) : 0,
            'nb_char' => $nbChar,
            'nb_money' => $api->getTotalFeeTranslate($nbChar),
            'stop' => $totalItem ? 0 : 1,
        );
    }

    public function setCacheMailSubject()
    {
        if (!$this->isOneClick) {
            return true;
        }
        $id_shop = Context::getContext()->shop->id;
        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE cache_type = 'mail' AND is_oneclick=" . (int)$this->isOneClick . " AND id_shop=" . (int)$id_shop);
        $cache = new EtsTransCache();
        $cache->cache_type = 'mail';
        $cache->name = null;
        $cache->file_path = _PS_ROOT_DIR_ . '/app/Resources/translations/default/EmailsSubject.xlf';
        $cache->file_type = 'xlf';
        $cache->is_oneclick = (int)$this->isOneClick;
        $cache->id_shop = $id_shop;
        $cache->save();
    }

    public function setCacheOthers()
    {
        if (!$this->isOneClick) {
            return true;
        }
        $id_shop = Context::getContext()->shop->id;
        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `cache_type` = 'other' AND `is_oneclick`=" . (int)$this->isOneClick . " AND `id_shop`=" . (int)$id_shop);
        $cache = new EtsTransCache();
        $cache->cache_type = 'others';
        $cache->name = null;
        $cache->file_path = _PS_ROOT_DIR_ . '/app/Resources/translations/default/messages.xlf';
        $cache->file_type = 'xlf';
        $cache->is_oneclick = (int)$this->isOneClick;
        $cache->id_shop = $id_shop;
        $cache->save();
    }

}