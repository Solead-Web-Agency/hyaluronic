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

class EtsTransModule extends EtsTransCore
{

    public function __construct($moduleName = null)
    {
        parent::__construct('module');
        $this->selectedName = rtrim($moduleName, '/ ');
    }

    public function setModuleName($moduleName)
    {
        $this->selectedName = $moduleName;
    }

    public function getModuleName()
    {
        if (isset($this->selectedName))
            return $this->selectedName;
        return null;
    }

    public function loadModuleFiles()
    {
        $context = Context::getContext();
        $this->deleteModuleCache();
        $moduleDir = rtrim(_PS_MODULE_DIR_, '/') . '/' . $this->selectedName;
        $moduleFiles = scandir($moduleDir);
        $rootDirFiles = array();
        foreach ($moduleFiles as $file) {
            if (in_array($file, self::$ignorefile)) {
                continue;
            }
            $filePath = $moduleDir . DIRECTORY_SEPARATOR . $file;

            if ($file == '.' || $file == '..' || !@is_file($filePath)) {
                continue;
            }
            if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }
            $rootDirFiles[] = $moduleDir . DIRECTORY_SEPARATOR . $file;
        }
        unset($file);
        $phpFiles = array_merge($rootDirFiles, $this->listFiles($moduleDir . '/classes/', array(), 'php'));
        $phpFiles = array_merge($phpFiles, $this->listFiles($moduleDir . '/controllers/', array(), 'php'));
        $directories = array(
            'php' => $phpFiles,
            'twig' => $this->listFiles($moduleDir . '/views/templates/', array(), 'twig'),
            'tpl' => $this->listFiles($moduleDir . '/views/templates/', array(), 'tpl'),
        );
        foreach ($directories as $type => $files) {
            foreach ($files as $file) {
                $cache = new EtsTransCache();
                $cache->cache_type = 'module';
                $cache->name = $this->selectedName;
                $cache->file_path = $file;
                $cache->file_type = $type;
                $cache->id_shop = $context->shop->id;
                $cache->save();
            }
        }
    }

    public function translateModule()
    {
        $result = array(
            'errors' => true,
            'nb_translated' => 0,
            'nb_char_translated' => 0,
            'stop_translate' => 1,
            'file_name' => '',
            'text_translated' => array()
        );
        if (!isset($this->langSource) || !isset($this->langTarget)) {
            return $result;
        }
        $context = Context::getContext();
        $cacheTrans = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `cache_type`='module' AND `name`='" . pSQL($this->selectedName) . "' AND id_shop=" . (int)$context->shop->id);
        if (!$cacheTrans) {
            $config = EtsTransConfig::getInstance();
            $result['errors'] = false;
            $config->deletePauseData('module', $this->selectedName, '', $this->langTarget);
            return $result;
        }
        $textTrans = $this->getTextTranslateFormFilePath($cacheTrans['file_path'], $cacheTrans['file_type']);
        if (!$textTrans) {
            $this->deleteCacheItem($cacheTrans['id_ets_trans_cache']);
            return $this->translateModule();
        }
        $textTrans = $this->removeDuplicateItem($textTrans);
        $nbWillTranslate = count($textTrans);
        $moduleDir = rtrim(_PS_MODULE_DIR_, '/') . '/' . $this->selectedName;
        $textData = array();
        $textWillTranslate = array();
        $api = new EtsTransApi();
        $source = Language::getIsoById($this->langSource);
        $fileName = basename($cacheTrans['file_path'], '.' . $cacheTrans['file_type']);
        foreach ($this->langTarget as $idLang) {
            $target = Language::getIsoById($idLang);
            $textTrans = $this->filterTransOption($textTrans, $moduleDir . '/translations/' . $target . '.php', $fileName);

            if (!$textWillTranslate)
                $textWillTranslate = $textTrans;
            else
                $textWillTranslate = array_merge($textWillTranslate, $textTrans);
            $originalText = $textTrans;
            $textTrans = $this->modifyTextTranslating($textTrans);
            $nbWillTranslate = count($textTrans);
            $resultTrans = $api->translate($source, $target, $textTrans, 'module');
            if (isset($resultTrans['errors']) && $resultTrans['errors']) {
                $result['errors'] = true;
                $result['message'] = isset($resultTrans['message']) ? $resultTrans['message'] : '';
                return $result;
            }
            $textTranslated = $resultTrans['data'];
            $textTranslated = $this->modifyTextTranslated($textTranslated);
            if (!@is_dir($moduleDir . '/translations')) {
                @mkdir($moduleDir . '/translations');
            }

            if (!@file_exists(($trans_file = $moduleDir . '/translations/' . $target . '.php'))) {
                $content = "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n";
                @file_put_contents($trans_file, $content);
            }
            if (!is_writable($trans_file)) {
                continue;
            }
            $content = Tools::file_get_contents($trans_file);
            if (!$content || strpos($content, '<?php') === false) {
                $content = "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n";
                @file_put_contents($trans_file, $content);
            }
            $originData = array();
            foreach ($originalText as $key => $text) {
                if (!isset($textTranslated[$key]) || !$textTranslated[$key]) {
                    continue;
                }
                $originData[$key] = $text;
                $textData[$key][$idLang] = $textTranslated[$key];
                $text = preg_replace("/\\\*'/", "\'", $text);
                $strMd5 = md5($text);
                $keyMd5 = '<{' . $this->selectedName . '}prestashop>' . $fileName . '_' . $strMd5;
                preg_match('/\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]/', $content, $matches);
                if ($matches) {
                    $content = preg_replace('/(\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]\s*=\s*\')(.*)(\';)/', '${1}' . pSQL($textTranslated[$key]) . '${3}', $content);
                } else
                    $content .= "\n\$_MODULE['" . $keyMd5 . "']='" . pSQL($textTranslated[$key]) . "';";
            }

            @file_put_contents($trans_file, $content);
        }
        $result['errors'] = false;
        $result['nb_translated'] = $nbWillTranslate;
        $nbChar = 0;
        foreach ($textWillTranslate as $text) {
            $nbChar += Tools::strlen($text);
        }
        $result['nb_char_translated'] = $nbChar;
        $result['stop_translate'] = false;
        $result['text_translated'] = $textData;
        $result['text_key'] = $originData;
        $result['file_name'] = $fileName;
        if (!connection_aborted())
            $this->deleteCacheItem($cacheTrans['id_ets_trans_cache']);
        return $result;
    }

    protected function filterTransOption($textTrans, $filePath, $fileName)
    {
        if (!@file_exists($filePath)) {
            return $textTrans;
        }
        $content = Tools::file_get_contents($filePath);
        $result = array();
        foreach ($textTrans as $text) {

            $strMd5 = md5($text);
            $keyMd5 = '<{' . $this->selectedName . '}prestashop>' . $fileName . '_' . $strMd5;
            $transSaved = null;
            preg_match('/\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]\s*=\s*\'(.*)\';/', $content, $matches);

            if ($matches) {
                $transSaved = isset($matches[1]) ? $matches[1] : '';
            }

            $textTrans = addslashes($text);
            switch ($this->transOption) {
                case 'both':
                    if (!$transSaved || $transSaved == $textTrans) {
                        $result[] = $text;
                    }
                    break;
                case 'only_empty':
                    if (!$transSaved) {
                        $result[] = $text;
                    }
                    break;
                case 'same_source':
                    if ($transSaved == $textTrans) {
                        $result[] = $text;
                    }
                    break;
                case 'all':
                    $result[] = $text;
                    break;
            }

        }

        return $result;
    }

    public function deleteModuleCache()
    {
        $context = Context::getContext();
        return Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `cache_type`='module' AND `name`='" . pSQL($this->selectedName) . "' AND id_shop=" . (int)$context->shop->id);
    }

    public function analysisModule()
    {
        $context = Context::getContext();
        $cacheTrans = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `cache_type`='module' AND `name`='" . pSQL($this->selectedName) . "' AND status=0 AND id_shop=" . (int)$context->shop->id);
        if (!$cacheTrans) {
            return array(
                'nb_text' => 0,
                'nb_char' => 0,
                'nb_money' => 0,
                'stop' => 1
            );
        }
        $textTrans = $this->getTextTranslateFormFilePath($cacheTrans['file_path'], $cacheTrans['file_type']);
        if (!$textTrans) {
            $this->updateStatusCacheItem($cacheTrans['id_ets_trans_cache'], 1);
            return $this->analysisModule();
        }

        $textTrans = $this->removeDuplicateItem($textTrans);
        $moduleDir = rtrim(_PS_MODULE_DIR_, '/') . '/' . $this->selectedName;
        $textWillTranslate = array();
        $fileName = basename($cacheTrans['file_path'], '.' . $cacheTrans['file_type']);
        foreach ($this->langTarget as $idLang) {
            $target = Language::getIsoById($idLang);
            $textTrans = $this->filterTransOption($textTrans, $moduleDir . '/translations/' . $target . '.php', $fileName);
            $nbWillTranslate = count($textTrans);
            if (!$textWillTranslate)
                $textWillTranslate = $textTrans;
            else
                $textWillTranslate = array_merge($textWillTranslate, $textTrans);
            $textTrans = $this->modifyTextTranslating($textTrans);
        }
        $nbChar = 0;
        foreach ($textWillTranslate as $text) {
            $nbChar += Tools::strlen($text);
        }
        $api = EtsTransApi::getInstance();
        $totalItem = (int)Db::getInstance()->getValue("SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "ets_trans_cache` WHERE `cache_type`='module' AND `name`='" . pSQL($this->selectedName) . "' AND status=0 AND id_shop=" . (int)$context->shop->id);
        $this->updateStatusCacheItem($cacheTrans['id_ets_trans_cache'], 1);
        return array(
            'nb_text' => $nbWillTranslate,
            'nb_char' => $nbChar,
            'nb_money' => $api->getTotalFeeTranslate($nbChar),
            'stop' => $totalItem > 0 ? 0 : 1
        );
    }

    public static function analysisModuleMegamenu($formData)
    {
        if (!isset($formData['trans_source']) || !isset($formData['trans_target']) || !isset($formData['trans_option'])) {
            return false;
        }
        $source = $formData['trans_source'];
        $target = is_array($formData['trans_target']) ? $formData['trans_target'] : explode(',', $formData['trans_target']);
        $transOption = $formData['trans_option'];
        $tableTrans = array(
            'ets_mm_menu_lang' => array('title', 'bubble_text'),
            'ets_mm_tab_lang' => array('title', 'bubble_text'),
            'ets_mm_block_lang' => array('title', 'content'),
        );
        $result = array(
            'nb_text' => 0,
            'nb_char' => 0,
            'nb_money' => 0,
            'stop' => 1
        );
        $api = EtsTransApi::getInstance();
        foreach ($tableTrans as $tblName => $fields) {
            $menus = self::getMegamenuData($tblName);
            $dataSource = array();
            $dataTarget = array();
            foreach ($menus as $menu) {
                if ($menu['id_lang'] == $source) {
                    foreach ($fields as $field) {
                        $dataSource[$field] = $menu[$field];
                    }
                } else {
                    foreach ($target as $kl=>$idLang) {
                        if ($menu['id_lang'] == $idLang) {
                            foreach ($fields as $field) {
                                if ($dataSource[$field] && EtsTransCore::checkTransOption($transOption, $dataSource[$field], $menu[$field])) {
                                    $dataTarget[$idLang][$field] = 1;
                                    $result['nb_char'] += Tools::strlen($dataSource[$field]);
                                    if($kl== 0){
                                        $result['nb_text']++;
                                    }
                                } else {
                                    $dataTarget[$idLang][$field] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }
        $result['nb_money'] = $api->getTotalFeeTranslate($result['nb_char']);
        if(!$result['nb_char']){
            $result['nb_text'] = 0;
        }
        return $result;
    }

    public static function transAllMegamenu($formData)
    {
        if (!isset($formData['trans_source']) || !isset($formData['trans_target']) || !isset($formData['trans_option'])) {
            return false;
        }
        $source = $formData['trans_source'];
        $target = is_array($formData['trans_target']) ? $formData['trans_target'] : explode(',', $formData['trans_target']);
        $transOption = $formData['trans_option'];
        $tableTrans = array(
            'ets_mm_menu_lang' => array('id_menu', 'title', 'bubble_text'),
            'ets_mm_tab_lang' => array('id_tab', 'title', 'bubble_text'),
            'ets_mm_block_lang' => array('id_block', 'title', 'content'),
        );
        $result = array(
            'nb_text' => 0,
            'nb_char' => 0,
            'nb_money' => 0,
            'stop' => 1
        );
        $api = EtsTransApi::getInstance();
        foreach ($tableTrans as $tblName => $fields) {
            $menus = self::getMegamenuData($tblName);
            $dataSource = array();
            $dataTarget = array();
            foreach ($menus as $menu) {
                if ($menu['id_lang'] == $source) {
                    foreach ($fields as $k => $field) {
                        if ($k == 0) {
                            continue;
                        }
                        $dataSource[$menu[$fields[0]] . '|' . $field] = $menu[$field];
                    }
                }
            }
            foreach ($target as $idLang) {
                foreach ($menus as $menu) {
                    if ($menu['id_lang'] == $idLang) {
                        foreach ($fields as $k => $field) {
                            if ($k == 0) {
                                continue;
                            }
                            $keyData = $menu[$fields[0]] . '|' . $field;
                            if (isset($dataSource[$keyData]) && $dataSource[$keyData] && EtsTransCore::checkTransOption($transOption, $dataSource[$keyData], $menu[$field])) {
                                $dataTarget[$idLang][$keyData] = 1;
                            } else {
                                $dataTarget[$idLang][$keyData] = 0;
                            }
                        }
                    }
                }
            }
            $textTrans = array();
            $listKey = array();
            foreach ($target as $kl=>$ldLang) {
                $nbChar = 0;
                foreach ($dataSource as $key => $text) {
                    if ($text && isset($dataTarget[$ldLang][$key]) && $dataTarget[$ldLang][$key]) {
                        $textTrans[] = $text;
                        $nbChar += Tools::strlen($text);
                        if($kl == 0){
                            $result['nb_text']++;
                        }
                        $listKey[] = $key;
                    }
                }
                $result['nb_char'] += $nbChar;
                if ($textTrans) {
                    $timStartTrans = microtime(true);
                    $source = (int)Configuration::get('ETS_TRANS_AUTO_DETECT_LANG') ? null : Language::getIsoById($formData['trans_source']);
                    $resTrans = $api->translate($source, Language::getIsoById($ldLang), $textTrans, 'megamenu');
                    if ($resTrans && (!isset($resTrans['errors']) || !$resTrans['errors'])) {
                        $translated = $resTrans['data'];
                        $idTrans = array();
                        foreach ($listKey as $k => $key) {
                            $keyData = explode('|', $key);
                            $idTrans[] = $keyData[0];
                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . (string)$tblName . " SET " . (string)$keyData[1] . "='" . pSQL($translated[$k], true) . "' WHERE id_lang=" . (int)$idLang . " AND `" . (string)$fields[0] . "`=" . (int)$keyData[0]);
                        }
                        $result['nb_money'] += $api->getTotalFeeTranslate($nbChar);
                        EtsTransLog::logTranslate('megamenu', true, null, self::getLogIdTrans($listKey), null, $timStartTrans, $source, $idLang);
                    }
                    else{
                        EtsTransLog::logTranslate('megamenu', false, null, self::getLogIdTrans($listKey), null, $timStartTrans, $source, $idLang, isset($resTrans['message']) ? $resTrans['message'] : null);

                        return $resTrans;
                    }
                }
            }

        }
        return $result;
    }

    public static function getMegamenuData($tblName, $idShop = null)
    {
        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }
        switch ($tblName) {
            case 'ets_mm_menu_lang':
                $menus = Db::getInstance()->executeS("SELECT a.title, a.bubble_text, a.id_lang, a.id_menu FROM " . _DB_PREFIX_ . (string)$tblName . " as a 
                LEFT JOIN " . _DB_PREFIX_ . "ets_mm_menu m ON m.id_menu = a.id_menu 
                LEFT JOIN " . _DB_PREFIX_ . "ets_mm_menu_shop ms ON ms.id_menu = m.id_menu
                WHERE ms.id_shop=" . (int)$idShop);
                break;
            case 'ets_mm_tab_lang':
                $menus = Db::getInstance()->executeS("SELECT a.title, a.bubble_text, a.id_lang, a.id_tab FROM " . _DB_PREFIX_ . (string)$tblName . " as a 
                LEFT JOIN " . _DB_PREFIX_ . "ets_mm_tab t ON t.id_tab = a.id_tab 
                LEFT JOIN " . _DB_PREFIX_ . "ets_mm_menu m ON m.id_menu = t.id_menu 
                LEFT JOIN " . _DB_PREFIX_ . "ets_mm_menu_shop ms ON ms.id_menu = m.id_menu
                WHERE ms.id_shop=" . (int)$idShop);
                break;
            case 'ets_mm_block_lang':
                $menus = Db::getInstance()->executeS("SELECT a.title, a.content, a.id_block, a.id_lang FROM `" . _DB_PREFIX_ . "ets_mm_block_lang` a
                            LEFT JOIN `" . _DB_PREFIX_ . "ets_mm_block` b ON b.id_block = a.id_block
                            LEFT JOIN (SELECT c1.id_column,m1.id_menu FROM " . _DB_PREFIX_ . "ets_mm_column c1
                            INNER JOIN `" . _DB_PREFIX_ . "ets_mm_tab` t1 ON (t1.id_tab= c1.id_tab)
                            INNER JOIN `" . _DB_PREFIX_ . "ets_mm_menu` m1 ON(t1.id_menu=m1.id_menu)
                            ) as column1 ON (b.id_column = column1.id_column)
                            LEFT JOIN (SELECT c2.id_column,m2.id_menu FROM `" . _DB_PREFIX_ . "ets_mm_column` c2
                            INNER JOIN `" . _DB_PREFIX_ . "ets_mm_menu` m2 ON(m2.id_menu=c2.id_menu)
                            ) as column2 ON (b.id_column = column2.id_column)
                            LEFT JOIN `" . _DB_PREFIX_ . "ets_mm_menu_shop` ms ON (ms.id_menu = column1.id_menu or ms.id_menu = column2.id_menu)
                            WHERE ms.id_shop = " . (int)$idShop);
                break;
            default:
                $menus = array();
                break;
        }
        return $menus;
    }

    public static function translateAllBlog($formData, $analysis = false)
    {
        if (!isset($formData['trans_source']) || !isset($formData['trans_target']) || !isset($formData['trans_option']) || !isset($formData['blog_type'])) {
            return false;
        }
        $blogType = $formData['blog_type'];
        $id_shop = Context::getContext()->shop->id;
        $offset = isset($formData['offset']) ? (int)$formData['offset'] : 0;
        $limit = 10;
        $pageId = isset($formData['page_id']) ? $formData['page_id'] : '';
        $ids = array();
        if($blogType == 'post'){
            $tblName = 'ybc_blog_post_lang';
            $idCol = 'id_post';
            $fields = array('title', 'meta_title', 'description', 'short_description', 'meta_keywords', 'meta_description');
            if($pageId){
                $ids = explode(',', $pageId);
            }
            else{
                $idData  = Db::getInstance()->executeS("SELECT p.id_post FROM `"._DB_PREFIX_."ybc_blog_post` p 
            LEFT JOIN `"._DB_PREFIX_."ybc_blog_post_shop` ps ON p.id_post = ps.id_post
            WHERE ps.id_shop=".(int)$id_shop." LIMIT ".(int)$offset.",".(int)$limit);
                $ids = array();
                foreach ($idData as $item){
                    $ids[] = $item['id_post'];
                }
            }
            if(!$ids){
                $posts = array();
            }
            else
                $posts = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."ybc_blog_post_lang` WHERE id_post IN(".implode(',', $ids).")");
        }
        elseif($blogType == 'category'){
            $tblName = 'ybc_blog_category_lang';
            $idCol = 'id_category';
            $fields = array('title', 'meta_title', 'description', 'meta_keywords', 'meta_description');
            if($pageId){
                $ids = explode(',', $pageId);
            }
            else{
                $idData = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."ybc_blog_category` c 
            LEFT JOIN `"._DB_PREFIX_."ybc_blog_category_shop` cs ON c.id_category = cs.id_category 
            WHERE cs.id_shop=".(int)$id_shop." LIMIT ".(int)$offset.",".(int)$limit);
                $ids = array();
                foreach ($idData as $item){
                    $ids[] = $item['id_category'];
                }
            }

            if(!$ids){
                $posts = array();
            }
            else
                $posts = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."ybc_blog_category_lang` WHERE `id_category` IN(".implode(',', $ids).")");
        }
        if(!isset($tblName) || !isset($fields) || !isset($posts)){
            return array(
                'errors' => true
            );
        }

        $result = array(
            'nb_text' => 0,
            'nb_char' => 0,
            'nb_money' => 0,
            'stop' => 0,
            'total_item' => 0,
            'blog_type' => $blogType,
            'offset' => $offset+$limit,
        );

        if(!$posts){
            $result['stop'] = 1;
            if(!$analysis){
                $config = EtsTransConfig::getInstance();
                $config->deletePauseData('blog_'.$blogType);
            }
            if($analysis){
                $result['total_item'] = self::getTotalBlogItem($blogType);
            }
            return $result;
        }

        $dataSource = array();
        foreach ($posts as $post){
            if((int)$post['id_lang'] == (int)$formData['trans_source']){
                foreach ($fields as $field){
                    $keyTrans = $post[$idCol].'|'.$field;
                    $dataSource[$keyTrans] = $post[$field];
                }
            }

        }
        if(!$dataSource){
            return $result;
        }
        $dataTarget = array();
        $langTarget = is_array($formData['trans_target']) ? $formData['trans_target'] : explode(',', $formData['trans_target']);
        foreach ($langTarget as $k=>$idLang){
            foreach ($posts as $post){
                if($post['id_lang'] !== $idLang){
                    continue;
                }
                foreach ($fields as $field){
                    $keyTrans = $post[$idCol].'|'.$field;
                    if(isset($dataSource[$keyTrans]) && $dataSource[$keyTrans] && EtsTransCore::checkTransOption($formData['trans_option'], $dataSource[$keyTrans], $post[$field])){
                        $dataTarget[$idLang][$keyTrans] = 1;
                        if($k==0){
                            $result['nb_text']++;
                        }
                        $result['nb_char'] += Tools::strlen($dataSource[$keyTrans]);
                    }
                    else{

                        $dataTarget[$idLang][$keyTrans] = 0;
                    }

                }
            }
        }
        $api = EtsTransApi::getInstance();
        if(!$analysis){
            foreach ($dataTarget as $idLang=>$item){
                $textTrans = array();
                $listKey = array();
                foreach ($item as $ik=>$status){
                    if($status){
                        $textTrans[] = $dataSource[$ik];
                        $listKey[] = $ik;
                    }
                }
                if($textTrans){
                    $timStartTrans = microtime(true);
                    $source = (int)Configuration::get('ETS_TRANS_AUTO_DETECT_LANG') ? null : Language::getIsoById($formData['trans_source']);
                    $resultTrans = $api->translate($source, Language::getIsoById($idLang), $textTrans, 'blog');
                    if(!isset($resultTrans['errors']) || !$resultTrans['errors']){
                        $translated = $resultTrans['data'];
                        foreach ($listKey as $i=>$key){
                            $keyData = explode('|', $key);
                            Db::getInstance()->execute("UPDATE "._DB_PREFIX_.(string)$tblName." SET ".(string)$keyData[1]."='".pSQL($translated[$i], true)."' WHERE id_lang=".(int)$idLang." AND ".(string)$idCol."=".(int)$keyData[0]);
                            if($keyData[1] == 'title' && (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE')){
                                if($urlRewrite = EtsTransPage::slugify($translated[$i])){
                                    Db::getInstance()->execute("UPDATE "._DB_PREFIX_.(string)$tblName." SET `url_alias`='".pSQL($urlRewrite)."' WHERE id_lang=".(int)$idLang." AND ".(string)$idCol."=".(int)$keyData[0]);
                                }
                            }
                        }
                        if(!$source){
                            $source = isset($resultTrans['detectedSourceLanguage']) ? $resultTrans['detectedSourceLanguage'] : null;
                        }
                        EtsTransLog::logTranslate($blogType == 'post' ? 'blog_post' : 'blog_category', true, null, self::getLogIdTrans($listKey), null, $timStartTrans, $source, $idLang);
                    }
                    else{
                        EtsTransLog::logTranslate($blogType == 'post' ? 'blog_post' : 'blog_category', false, null, self::getLogIdTrans($listKey), null, $timStartTrans, $source, $idLang, isset($resultTrans['message'])? $resultTrans['message'] : null);

                        return $resultTrans;
                    }
                }
            }
        }

        $result['nb_money'] = $api->getTotalFeeTranslate($result['nb_char']);
        /*if($blogType == 'category' && (!$posts || count($posts) < $limit)){
            $result['stop'] = 1;
        }*/
        if(!$posts || count($ids) < $limit || (isset($formData['page_id']) && $formData['page_id'])){
            $result['stop'] = 1;
            if(!$analysis){
                $config = EtsTransConfig::getInstance();
                $config->deletePauseData('blog_'.$blogType);
            }
            if($analysis)
                $result['total_item'] = self::getTotalBlogItem($blogType);
        }
        return $result;
    }

    public static function getTotalBlogItem($blogType, $id_shop = null){
        if($id_shop){
            $id_shop = Context::getContext()->shop->id;
        }
        if($blogType == 'post'){
            return (int)Db::getInstance()->getValue("SELECT * FROM `"._DB_PREFIX_."ybc_blog_post_shop` WHERE `id_shop`=".(int)$id_shop);
        }
        elseif($blogType == 'category'){
            return (int)Db::getInstance()->getValue("SELECT * FROM `"._DB_PREFIX_."ybc_blog_category_shop` WHERE `id_shop`=".(int)$id_shop);
        }
    }


    public static function analysisModuleBlog($formData)
    {
        if (!isset($formData['trans_source']) || !isset($formData['trans_target']) || !isset($formData['trans_option']) || !isset($formData['blog_type'])) {
            return false;
        }

        return self::translateAllBlog($formData, true);
    }

    public static function translateAllModulePc($formData, $analysis = false)
    {
        if (!isset($formData['trans_source']) || !isset($formData['trans_target']) || !isset($formData['trans_option'])) {
            return false;
        }

        $offset = isset($formData['offset']) ? (int)$formData['offset'] : 0;
        $limit = 20;
        $pageId = isset($formData['page_id']) ? $formData['page_id'] : '';
        $ids = array();
        $result = array(
            'nb_text' => 0,
            'nb_char' => 0,
            'nb_money' => 0,
            'stop' => 0,
            'total_item' => 0,
            'offset' => $offset+$limit,
        );
        if($pageId){
            $ids = is_array($pageId) ? $pageId : explode(',', $pageId);
        }
        else{
            $comments = Db::getInstance()->executeS("SELECT id_ets_pc_product_comment as id_pc FROM `"._DB_PREFIX_."ets_pc_product_comment` 
                                        WHERE 1".(($pcType = Tools::getValue('pcType')) ? " AND `question`=".($pcType == 'question' ? 1 : 0) : "")." 
                                        LIMIT ".(int)$offset.",".(int)$limit);
            if(!$comments){
                $result['stop'] = 1;
                if(!$analysis){
                    $config = EtsTransConfig::getInstance();
                    $config->deletePauseData('pc');
                }
                if($analysis){
                    $result['total_item'] = self::getTotalProductComments();
                }
                return $result;
            }
            foreach ($comments as $cmt){
                $ids[] = $cmt['id_pc'];
            }
        }


        $cmtLang = Db::getInstance()->executeS("SELECT cl.id_ets_pc_product_comment as id_pc, cl.title, cl.content, cl.id_lang, col.id_lang as origin_lang 
                                                    FROM `"._DB_PREFIX_."ets_pc_product_comment_lang` cl
                                                    JOIN `"._DB_PREFIX_."ets_pc_product_comment` c ON cl.id_ets_pc_product_comment = c.id_ets_pc_product_comment
                                                    LEFT JOIN `"._DB_PREFIX_."ets_pc_product_comment_origin_lang` col ON c.id_ets_pc_product_comment=col.id_ets_pc_product_comment
                                                    WHERE c.id_ets_pc_product_comment IN (".implode(',', $ids).") GROUP BY cl.id_ets_pc_product_comment, cl.id_lang, col.id_lang");
        $fields = array('title', 'content');
        if(!$cmtLang){
            $result['stop'] = 1;
            if(!$analysis){
                $config = EtsTransConfig::getInstance();
                $config->deletePauseData('pc');
            }
            $config = EtsTransConfig::getInstance();
            $config->deletePauseData('pc');
            if($analysis){
                $result['total_item'] = self::getTotalProductComments();
            }
            return $result;
        }

        $dataSource = array();
        foreach ($cmtLang as $cmtItem){
            if((int)$cmtItem['id_lang'] == (int)$cmtItem['origin_lang']){
                foreach ($fields as $field){
                    $keyTrans = $cmtItem['id_pc'].'|'.$field;
                    $dataSource[$keyTrans] = $cmtItem[$field];
                }
            }
        }
        if(!$dataSource){
            return $result;
        }
        $dataTarget = array();
        if((int)Configuration::get('ETS_TRANS_AUTO_DETECT_LANG')){
            $langTarget = Language::getIDs(true);
        }
        else
            $langTarget = is_array($formData['trans_target']) ? $formData['trans_target'] : explode(',', $formData['trans_target']);
        //Get all languages excerpt source language;

        foreach ($langTarget as $k=>$idLang){
            $dataTarget[$idLang] = array();
            foreach ($cmtLang as $cmtItem){
                if($cmtItem['id_lang'] !== $idLang){
                    continue;
                }
                foreach ($fields as $field){
                    $keyTrans = $cmtItem['id_pc'].'|'.$field;
                    if(isset($dataSource[$keyTrans]) && $dataSource[$keyTrans] && EtsTransCore::checkTransOption($formData['trans_option'], $dataSource[$keyTrans], $cmtItem[$field])){
                        $dataTarget[$idLang][$keyTrans] = array(
                            'is_trans' => 1,
                            'lang_source' => $cmtItem['origin_lang']
                        );
                        if($k==0){
                            $result['nb_text']++;
                        }
                        $result['nb_char'] += Tools::strlen($dataSource[$keyTrans]);
                    }
                    else{
                        $dataTarget[$idLang][$keyTrans] = array(
                            'is_trans' => 0,
                            'lang_source' => $cmtItem['origin_lang']
                        );;
                    }
                }
            }
        }
        $api = EtsTransApi::getInstance();
        if(!$analysis){
            foreach ($dataTarget as $idLang=>$item){
                $textTrans = array();
                $listKey = array();
                foreach ($item as $ik=>$statusData){
                    if($statusData['is_trans']){
                        $textTrans[] = $dataSource[$ik];
                        $listKey[] = array(
                            'key' => $ik,
                            'lang_source' => $statusData['lang_source']
                        );
                    }
                }
                if($textTrans){
                    $timStartTrans = microtime(true);
                    //Auto detect source language if config is ON
                    $source = (int)Configuration::get('ETS_TRANS_AUTO_DETECT_LANG') ? null : Language::getIsoById($formData['trans_source']);
                    $langSource =$formData['trans_source'];
                    $resultTrans = $api->translate($source, Language::getIsoById($idLang), $textTrans, 'pc');
                    if(!isset($resultTrans['errors']) || !$resultTrans['errors']){
                        $translated = $resultTrans['data'];
                        foreach ($listKey as $i=>$key){
                            $keyData = explode('|', $key['key']);
                            if($key['lang_source'] !== $idLang)
                                Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."ets_pc_product_comment_lang` SET ".(string)$keyData[1]."='".pSQL($translated[$i], true)."' WHERE id_lang=".(int)$idLang." AND id_ets_pc_product_comment=".(int)$keyData[0]);
                        }
                        //Check data in source language if auto detect is ON
                        if(!$source){
                            $detectedSourceLanguage = isset($resultTrans['detectedSourceLanguage']) ? $resultTrans['detectedSourceLanguage'] : null;

                            if($detectedSourceLanguage){
                                $langSource = $detectedSourceLanguage;
                                foreach ($listKey as $i=>$key){
                                    if($detectedSourceLanguage[$i] !== EtsTransApi::getLangCodeFromIdLang($key['lang_source'])){
                                        $resultSourceTrans = $api->translate(null, Language::getIsoById($key['lang_source']), array($textTrans[$i]), 'pc');
                                        if(!isset($resultSourceTrans['errors']) || !$resultSourceTrans['errors']){
                                            $sourceTranslated = $resultSourceTrans['data'];
                                            $keyData = explode('|', $key['key']);
                                            Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."ets_pc_product_comment_lang` SET ".(string)$keyData[1]."='".pSQL($sourceTranslated[0], true)."' WHERE id_lang=".(int)$key['lang_source']." AND id_ets_pc_product_comment=".(int)$keyData[0]);
                                        }
                                    }
                                }
                            }
                        }
                        EtsTransLog::logTranslate('pc', true, null, self::getLogIdTrans(array_column($listKey, 'key')),null, $timStartTrans, $langSource, $idLang, null);
                    }
                    else{
                        EtsTransLog::logTranslate('pc', false, null, self::getLogIdTrans(array_column($listKey, 'key')), null, $timStartTrans, $langSource, $idLang, isset($resultTrans['message']) ? $resultTrans['message'] : null);
                        return $resultTrans;
                    }
                }
            }
        }

        $result['nb_money'] = $api->getTotalFeeTranslate($result['nb_char']);
        if(!$cmtLang || count($ids) < $limit || (isset($formData['page_id']) && $formData['page_id'])){
            $result['stop'] = 1;
            if(!$analysis){
                $config = EtsTransConfig::getInstance();
                $config->deletePauseData('pc');
            }
            if($analysis)
                $result['total_item'] = self::getTotalProductComments();
        }
        if(!$result['nb_char']){
            $result['nb_text'] = 0;
        }
        return $result;
    }

    public static function analysisModulePc($formData)
    {
        if (!isset($formData['trans_source']) || !isset($formData['trans_target']) || !isset($formData['trans_option'])) {
            return false;
        }

        return self::translateAllModulePc($formData, true);
    }

    public static function getTotalProductComments($idShop = null)
    {
        if(!$idShop){
            $idShop = Context::getContext()->shop->id;
        }
        return (int)Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."ets_pc_product_comment` pc 
                                                LEFT JOIN `"._DB_PREFIX_."product_shop` ps ON pc.id_product = ps.id_product WHERE ps.id_shop=".(int)$idShop);
    }

    public static  function updateTextModule($dataTrans, $moduleName, $fileName)
    {
        if(!is_array($dataTrans)){
            return false;
        }

        foreach ($dataTrans as $idLang=> $textTrans){
            $target = Language::getIsoById($idLang);
            if (!@file_exists(($trans_file = _PS_MODULE_DIR_.$moduleName . '/translations/' . $target . '.php'))) {
                $content = "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n";
                @file_put_contents($trans_file, $content);
            }
            if (!is_writable($trans_file)) {
                return false;
            }
            $content = Tools::file_get_contents($trans_file);
            if (!$content || strpos($content, '<?php') === false) {
                $content = "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n";
                @file_put_contents($trans_file, $content);
            }
            foreach ($textTrans as $textOrigin=>$translated){
                $text = preg_replace("/\\\*'/", "\'", $textOrigin);
                if(!$text || !$translated){
                    continue;
                }
                $strMd5 = md5($text);
                $keyMd5 = '<{' . $moduleName . '}prestashop>' . $fileName . '_' . $strMd5;
                preg_match('/\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]/', $content, $matches);
                if ($matches) {
                    $content = preg_replace('/(\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]\s*=\s*\')(.*)(\';)/', '${1}' . pSQL($translated) . '${3}', $content);
                } else{
                    $content .= "\n\$_MODULE['" . $keyMd5 . "']='" . pSQL($translated) . "';";
                }
            }
            @file_put_contents($trans_file, $content);
        }

    }

    public static function updateTransMegamenuItem($id, $menuType, $colData, $transData)
    {
        $tableName = '';
        $idItem = '';
        switch ($menuType){
            case 'menu':
                $tableName = 'ets_mm_menu_lang';
                $idItem = 'id_menu';
                break;
            case 'tab':
                $tableName = 'ets_mm_tab_lang';
                $idItem = 'id_tab';
                break;
            case 'block':
                $tableName = 'ets_mm_block_lang';
                $idItem = 'id_block';
                break;
        }
        if(!$tableName || !$idItem || !$id){
            return false;
        }
        foreach ($transData as $idLang => $itemTrans){
            $dataUpdate = array();
            foreach ($itemTrans as $key=>$text){
                if(isset($colData[$key])){
                    $dataUpdate[] = $colData[$key]."='".pSQL($text)."'";
                }
            }
            if($dataUpdate)
                Db::getInstance()->execute("UPDATE `"._DB_PREFIX_.$tableName."` SET ".implode(',', $dataUpdate)." WHERE ".(string)$idItem."=".(int)$id." AND `id_lang`=".(int)$idLang);
        }
        return true;
    }

    public static function updateTransBlogItem($id, $blogType, $colData, $transData)
    {
        $tableName = '';
        $idItem = '';
        switch ($blogType){
            case 'post':
                $tableName = 'ybc_blog_post_lang';
                $idItem = 'id_post';
                break;
            case 'category':
                $tableName = 'ybc_blog_category_lang';
                $idItem = 'id_category';
                break;
        }

        if(!$tableName || !$idItem || !$id){
            return false;
        }
        foreach ($transData as $idLang => $itemTrans){
            $dataUpdate = array();
            foreach ($itemTrans as $key=>$text){
                if(isset($colData[$key])){
                    $dataUpdate[] = $colData[$key]."='".pSQL($text)."'";
                }
            }
            if($dataUpdate)
                Db::getInstance()->execute("UPDATE `"._DB_PREFIX_.$tableName."` SET ".implode(',', $dataUpdate)." WHERE ".(string)$idItem."=".(int)$id." AND `id_lang`=".(int)$idLang);
        }
        return true;
    }

    public static function updateTransPCItem($id, $colData, $transData)
    {
        if(!$id){
            return false;
        }

        foreach ($transData as $idLang => $itemTrans){
            $dataUpdate = array();
            foreach ($itemTrans as $key=>$text){
                if(isset($colData[$key])){
                    $dataUpdate[] = $colData[$key]."='".pSQL($text)."'";
                }
            }
            if($dataUpdate)
                Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."ets_pc_product_comment_lang` SET ".implode(',', $dataUpdate)." WHERE id_ets_pc_product_comment=".(int)$id." AND `id_lang`=".(int)$idLang);
        }
        return true;
    }

    public static function getTextLog($textSource)
    {
        $textLog = "";
        foreach ($textSource as $text){
            if(Tools::strlen($textLog) >= 50){
                break;
            }
            $textLog .= $text.";";
        }
        return Tools::substr(strip_tags(rtrim($textLog, ';')), 0, 50);
    }

    public static function getLogIdTrans($listData, $delimiter = '|')
    {
        $ids = array();
        foreach ($listData as $keyData){
            $id = explode($delimiter, $keyData)[0];
            if(!in_array($id, $ids))
                $ids[] = $id;
        }
        return implode(',', $ids);
    }
}