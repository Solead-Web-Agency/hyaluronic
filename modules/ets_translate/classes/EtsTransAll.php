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

 class EtsTransAll
 {
     protected $pageType;
     public function __construct($pageType = 'all')
     {
         $this->pageType = $pageType;
     }

     public function setPageType($pageType)
     {
         $this->pageType = $pageType;
     }

     public function loadFileTranslateAll($formData)
     {
         $this->indexDataTranslate($formData['trans_wd']);
         return array(
            'errors'=> false,
            'total_item' => self::getTotalItemTransAll(null, $this->pageType),
         );
     }

     public function translateAllWebData($formData)
     {
         $id_shop = Context::getContext()->shop->id;
         $result = array(
             'errors'=> false,
             'nb_text' => 0,
             'nb_char' => 0,
             'nb_money'=> 0,
             'nb_item' => 0,
             'nb_cache' => 0,
             'page_type' => '',
             'item_name' => '',
             'stop_translate' => 0,
         );
         $cacheItem = Db::getInstance()->getRow("SELECT *  FROM `"._DB_PREFIX_."ets_trans_cache` WHERE `is_oneclick`=".($this->pageType == 'all' ? 1 : 2)." AND `id_shop`=".(int)$id_shop);
         if(!$cacheItem){
             $result['stop_translate'] = 1;
             return $result;
         }
         $pages = array('product', 'category', 'cms', 'cms_category', 'manufacturer', 'supplier', 'attribute', 'attribute_group', 'feature', 'feature_value',
             'blockreassurance', 'ps_linklist', 'ps_mainmenu', 'ps_customtext', 'ps_imageslider', 'ets_extraproducttabs');
         if(in_array($cacheItem['cache_type'], $pages)){
             $offset = isset($formData['nb_item']) && isset($formData['page_type']) && $formData['page_type'] == $cacheItem['cache_type'] ? (int)$formData['nb_item'] : (int)$cacheItem['nb_translated'];
             $dataTrans = $this->transAllPages($cacheItem['cache_type'], $formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
             if(isset($dataTrans['errors']) && $dataTrans['errors']){
                 return $dataTrans;
             }
             $result['nb_text'] = isset($dataTrans['nb_text']) ? $dataTrans['nb_text'] : 0;
             $result['nb_char'] = isset($dataTrans['nb_char']) ? $dataTrans['nb_char'] : 0;
             $result['nb_item'] = isset($dataTrans['nb_item']) ? $dataTrans['nb_item'] : 0;
             $result['page_type'] = $cacheItem['cache_type'];
             $result['item_name'] = $cacheItem['name'];
             if(isset($dataTrans['stop_translate']) && $dataTrans['stop_translate']){
                 self::deleteCacheItem($cacheItem['id_ets_trans_cache']);
             }


         }
         elseif($cacheItem['cache_type'] == 'blog_post'){
             //$offset = isset($formData['nb_item']) && isset($formData['page_type']) && $formData['page_type'] == $cacheItem['cache_type'] ? (int)$formData['nb_item'] : (int)$cacheItem['nb_translated'];
             $formData['blog_type'] = 'post';
             $dataTrans = EtsTransModule::translateAllBlog($formData);
             $result = $dataTrans;
             if(isset($result['errors']) && $result['errors']){
                 return $result;
             }
             $result['nb_item'] = $result['offset'];
             $result['page_type'] = $cacheItem['cache_type'];
             if(isset($result['stop']) && $result['stop']){
                 self::deleteCacheItem($cacheItem['id_ets_trans_cache']);
             }
             else{
                 $cacheBP = new EtsTransCache($cacheItem['id_ets_trans_cache']);
                 $cacheBP->nb_translated = (int)$cacheBP->nb_translated + (int)$result['nb_item'];
                 $cacheBP->save();
             }
         }
         elseif($cacheItem['cache_type'] == 'blog_category'){
             //$offset = isset($formData['nb_item']) && isset($formData['page_type']) && $formData['page_type'] == $cacheItem['cache_type'] ? (int)$formData['nb_item'] : (int)$cacheItem['nb_translated'];
             $formData['blog_type'] = 'category';
             $dataTrans = EtsTransModule::translateAllBlog($formData);
             $result = $dataTrans;
             if(isset($result['errors']) && $result['errors']){
                 return $result;
             }
             $result['nb_item'] = $result['offset'];
             $result['page_type'] = $cacheItem['cache_type'];
             if(isset($result['stop']) && $result['stop']){
                 self::deleteCacheItem($cacheItem['id_ets_trans_cache']);
             }
             else{
                 $cacheBC = new EtsTransCache($cacheItem['id_ets_trans_cache']);
                 $cacheBC->nb_translated = (int)$cacheBC->nb_translated + (int)$result['nb_item'];
                 $cacheBC->save();
             }
         }
         elseif($cacheItem['cache_type'] == 'megamenu'){
             $dataTrans = EtsTransModule::transAllMegamenu($formData);
             $result = $dataTrans;
             if(isset($result['errors']) && $result['errors']){
                 return $result;
             }
             $result['nb_item'] = 0;
             $result['page_type'] = 'megamenu';
             if(isset($result['stop']) && $result['stop']){
                 self::deleteCacheItem($cacheItem['id_ets_trans_cache']);
             }

         }
         elseif($cacheItem['cache_type'] == 'pc'){
             //$offset = isset($formData['nb_item']) && isset($formData['page_type']) && $formData['page_type'] == $cacheItem['cache_type'] ? (int)$formData['nb_item'] : (int)$cacheItem['nb_translated'];
             $dataTrans = EtsTransModule::translateAllModulePc($formData);
             $result = $dataTrans;
             if(isset($result['errors']) && $result['errors']){
                 return $result;
             }
             $result['nb_item'] = $result['offset'];
             $result['page_type'] = $cacheItem['cache_type'];
             if(isset($result['stop']) && $result['stop']){
                 self::deleteCacheItem($cacheItem['id_ets_trans_cache']);
             }
             else{
                 $cacheBP = new EtsTransCache($cacheItem['id_ets_trans_cache']);
                 $cacheBP->nb_translated = (int)$cacheBP->nb_translated + (int)$result['nb_item'];
                 $cacheBP->save();
             }

         }
         elseif($cacheItem['cache_type'] == 'email'){
             $result['page_type'] = 'email';
             if(!$cacheItem['file_path']){
                 self::deleteCacheItem($cacheItem['id_ets_trans_cache']);
                 $result['stop_translate'] = 1;
             }
             else{
                 $arrayEmail = explode(',', $cacheItem['file_path']);
                 $mailsToTrans = array_splice($arrayEmail, 0, 2);
                 $resultTrans = $this->transEmailBody($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $mailsToTrans, $cacheItem['name']);
                 if(!isset($resultTrans['errors']) || !$resultTrans['errors']){
                     if($arrayEmail){
                         Db::getInstance()->execute("UPDATE "._DB_PREFIX_."ets_trans_cache SET file_path = '".implode(',', $arrayEmail)."' WHERE id_ets_trans_cache=".(int)$cacheItem['id_ets_trans_cache']);
                     }
                     else{
                         self::deleteCacheItem($cacheItem['id_ets_trans_cache']);
                         $result['stop_translate'] = self::getTotalItemTransAll(null, $this->pageType) ? 0 : 1;
                     }
                     $result['nb_text'] = isset($result['nb_translated']) ? $result['nb_translated'] : 0;
                     $result['nb_char'] = isset($result['nb_char_translated']) ? $result['nb_char_translated'] : 0;
                     $result['nb_email'] = count($mailsToTrans);
                 }
             }
         }
         else{
             $dataTrans = $this->translateSf($cacheItem['cache_type'], $formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $cacheItem['name']);
             if(!isset($dataTrans['errors']) || !$dataTrans['errors']){
                 $result['nb_text'] = $dataTrans['nb_translated'];
                 $result['nb_char'] = $dataTrans['nb_char_translated'];
                 $result['stop_translate'] = $dataTrans['stop_translate'];
                 $result['page_type'] = $cacheItem['cache_type'];
                 $result['item_name'] = $cacheItem['name'];
                 $result['file_path'] = isset($dataTrans['file_name']) && $dataTrans['file_name'] ? $dataTrans['file_name'] : array();
                 $totalItem = self::getTotalItemTransAll(null, $this->pageType);
                 if(isset($dataTrans['stop_translate']) && $dataTrans['stop_translate']){
                     self::deleteCacheItem($cacheItem['id_ets_trans_cache']);
                     if($totalItem){
                         $result['stop_translate'] = 0;
                     }
                 }
             }
             else{
                 $result['errors'] = true;
             }
         }
         $result['total_path_remain'] = self::getTotalItemTransAll(null, $this->pageType);
         return $result;
     }

     public function indexDataTranslate($webData)
     {
         $context = Context::getContext();
         Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."ets_trans_cache` WHERE is_oneclick=".($this->pageType == 'all' ? 1 : 2)." AND id_shop=".(int)$context->shop->id);
         $transData = $this->getWebData($webData);
         foreach ($transData as $key=>$item){
             if($key == 'catalog' || $key == 'page' || $key == 'blog'){
                 foreach ($item as $page){
                     $this->setCacheTrans($page);
                 }
             }
             elseif($key == 'megamenu'){
                 $this->setCacheTrans('megamenu');
             }
             elseif($key == 'pc'){
                 $this->setCacheTrans('pc');
             }
             elseif(in_array($key, array('blockreassurance', 'ps_linklist', 'ps_mainmenu', 'ps_customtext', 'ps_imageslider', 'ets_extraproducttabs'))){
                 $this->setCacheTrans($key);
             }
             elseif($key == 'inter'){
                 if($item == 'all'){
                    $this->setTransForAllInter();
                 }
                 else{
                     foreach ($item as $k=> $inter){
                         if ($k == 'back') {
                             $this->setCacheTrans('back');
                         }
                         elseif ($k == 'theme') {
                             if (is_array($inter)) {
                                 foreach ($inter as $theme) {
                                     $this->setCacheTrans('theme', $theme);
                                 }
                                 if(isset($theme))
                                     unset($theme);
                             } else {
                                 foreach (EtsTransCore::getAllThemes() as $themeItem) {
                                     $this->setCacheTrans('theme', $themeItem['title']);
                                 }
                             }
                         }
                         elseif ($k == 'module') {
                             if (is_array($inter)) {
                                 foreach ($inter as $moduleName) {
                                     $this->setCacheTrans('module', $moduleName);
                                 }
                                 if(isset($theme))
                                     unset($theme);
                             } else {
                                 foreach (EtsTransCore::getAllModules(true) as $moduleItem) {
                                     $this->setCacheTrans('module', $moduleItem['title']);
                                 }
                             }
                         }
                         elseif($k == 'email'){
                             if(!is_array($inter)){
                                 $this->setCacheTrans('mail_subject');
                                 $this->setCacheTrans('email');
                                 $themes = EtsTransCore::getAllThemes();
                                 foreach ($themes as $theme){
                                     $this->setCacheTrans('email', $theme['title']);
                                 }
                                 if(isset($theme))
                                    unset($theme);
                             }
                             else{
                                 foreach ($inter as $ki=> $vi){
                                     if($ki == 'subject'){
                                         $this->setCacheTrans('mail_subject');
                                     }
                                     elseif($ki == 'body'){
                                         if(!is_array($vi)){
                                             $this->setCacheTrans('email');
                                             $themes = EtsTransCore::getAllThemes();
                                             foreach ($themes as $theme){
                                                 $this->setCacheTrans('email', $theme['title']);
                                             }
                                             if(isset($theme))
                                                 unset($theme);
                                         }
                                         else{
                                             foreach ($vi as $etk=>$emailType){
                                                 if($etk == 'core'){
                                                     if(!is_array($emailType)){
                                                         $this->setCacheTrans('email');
                                                     }
                                                     else{
                                                         $this->setCacheTrans('email', null, implode(',',$emailType));
                                                     }
                                                 }
                                                 elseif($etk == 'theme'){
                                                     if(!is_array($emailType)){
                                                         $themes = EtsTransCore::getAllThemes();
                                                         foreach ($themes as $theme){
                                                             $this->setCacheTrans('email', $theme['title']);
                                                         }
                                                         if(isset($theme))
                                                             unset($theme);
                                                     }
                                                     else{
                                                         foreach ($emailType as $tName=>$itemTheme){
                                                             if(!is_array($emailType)){
                                                                 $this->setCacheTrans('email', $tName);
                                                             }
                                                             else{
                                                                 $this->setCacheTrans('email', $tName, $itemTheme);
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
                         elseif($k == 'other'){
                             $this->setCacheTrans('other');
                         }
                     }
                 }
             }
         }
         return true;
     }

     public function setTransForAllInter()
     {
         $this->setCacheTrans('back');
         $this->setCacheTrans('mail_subject');
         $this->setCacheTrans('email');
         $themes = EtsTransCore::getAllThemes();
         $modules = EtsTransCore::getAllModules(true);
         foreach ($modules as $module){
             $this->setCacheTrans('module', $module['title']);
         }
         foreach ($themes as $theme){
             $this->setCacheTrans('email', $theme['title']);
             $this->setCacheTrans('theme', $theme['title']);
         }
         $this->setCacheTrans('other');
     }

     public function setCacheTrans($type, $selectedTheme = null, $emailOption = null)
     {
         $id_shop = Context::getContext()->shop->id;
         $oneClickVal = $this->pageType == 'all' ? 1 : 2;
         switch ($type){
             case 'product':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'product';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'category':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'category';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'cms':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'cms';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'cms_category':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'cms_category';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'manufacturer':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'manufacturer';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'supplier':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'supplier';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'blog_post':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'blog_post';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'blog_category':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'blog_category';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'megamenu':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'megamenu';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'pc':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'pc';
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;
             case 'email':
                 $cache = new EtsTransCache();
                 $cache->cache_type = 'email';
                 $cache->file_path = $emailOption ? (is_array($emailOption) ? implode(',', $emailOption) : $emailOption) : implode(',', array_column(EtsTransInternational::getEmailTemplate(null, true), 'key'));
                 $cache->name = $selectedTheme;
                 $cache->nb_translated = 0;
                 $cache->is_oneclick = $oneClickVal;
                 $cache->id_shop = $id_shop;
                 $cache->save();
                 break;

         }

         if(in_array($type, array('blockreassurance', 'ps_linklist', 'ps_mainmenu', 'ps_customtext', 'ps_imageslider', 'ets_extraproducttabs'))){
             $cache = new EtsTransCache();
             $cache->cache_type = $type;
             $cache->nb_translated = 0;
             $cache->is_oneclick = $oneClickVal;
             $cache->id_shop = $id_shop;
             $cache->save();
         }
         elseif(in_array($type, array('attribute', 'attribute_group', 'feature', 'feature_value'))){
             $cache = new EtsTransCache();
             $cache->cache_type = $type;
             $cache->nb_translated = 0;
             $cache->is_oneclick = $oneClickVal;
             $cache->id_shop = $id_shop;
             $cache->save();
         }

         if(in_array($type, array('back','theme', 'mail_subject', 'other', 'module'))){
            if($type == 'theme'){
                $etsNs = new EtsTransNewSystem('theme');
                $etsNs->setIsOneClick($this->pageType == 'all' ? 1 : 2);
                $etsNs->setSelectedName($selectedTheme);
                $etsNs->loadFileSystem();
            }
            elseif($type == 'module'){
                $etsNs = new EtsTransNewSystem('sfmodule');
                $etsNs->setIsOneClick($this->pageType == 'all' ? 1 : 2);
                $etsNs->setSelectedName($selectedTheme);
                $etsNs->loadFileSystem();
            }
            elseif($type == 'mail_subject'){
                $etsNs = new EtsTransNewSystem('mail');
                $etsNs->setIsOneClick($this->pageType == 'all' ? 1 : 2);
                $etsNs->loadFileSystem();
            }
            elseif($type == 'other'){
                $etsNs = new EtsTransNewSystem('others');
                $etsNs->setIsOneClick($this->pageType == 'all' ? 1 : 2);
                $etsNs->loadFileSystem();
            }
            elseif($type == 'back'){
                $etsNs = new EtsTransNewSystem('back');
                $etsNs->setIsOneClick($this->pageType == 'all' ? 1 : 2);
                for ($step=1; $step<=7;$step++){
                    $etsNs->loadFileSystem($step);
                }
            }
         }
     }

     public function analysisBeforeTranslate($formData, $offset=0)
     {
         $cacheItem = $this->getItemAnalysis();
         if(in_array($cacheItem['cache_type'], array('product', 'category', 'cms', 'cms_category', 'manufacturer', 'supplier','attribute_group', 'attribute', 'feature', 'feature_group',
             'blockreassurance', 'ps_linklist', 'ps_mainmenu', 'ps_customtext', 'ps_imageslider', 'ets_extraproducttabs'))){
             $result =  EtsTransPage::analysisTranslate($cacheItem['cache_type'], $formData, $offset);
             if(isset($result['stop']) && $result['stop']){
                 $result['offset'] = 0;
             }
         }
         elseif($cacheItem['cache_type'] == 'blog_post'){
             $formData['offset'] = $offset;
             $formData['blog_type'] = 'post';
             $result = EtsTransModule::analysisModuleBlog($formData);
             if(isset($result['stop']) && $result['stop']){
                 $result['offset'] = 0;
             }
         }
         elseif($cacheItem['cache_type'] == 'blog_category'){
             $formData['offset'] = $offset;
             $formData['blog_type'] = 'category';
             $result = EtsTransModule::analysisModuleBlog($formData);
             if(isset($result['stop']) && $result['stop']){
                 $result['offset'] = 0;
             }
         }
         elseif($cacheItem['cache_type'] == 'megamenu'){
             $result = EtsTransModule::analysisModuleMegamenu($formData);
         }
         elseif($cacheItem['cache_type'] == 'pc'){
             $formData['offset'] = $offset;
             $result = EtsTransModule::analysisModulePc($formData);
         }
         elseif($cacheItem['cache_type'] == 'email'){
             $mailOptions = explode(',', $cacheItem['file_path']);
             if(isset($formData['mail_checked']) && $formData['mail_checked']){
                foreach ($mailOptions as $k=>$item){
                    if(in_array($item, $formData['mail_checked'])){
                        unset($mailOptions[$k]);
                    }
                }
             }
            $mailCheck = array_splice($mailOptions, 0, 4);
            $result =  EtsTransInternational::analysisEmail($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $mailCheck, $cacheItem['name']);
         }
         else{
             $ens = new EtsTransNewSystem($cacheItem['cache_type']);
             $ens->setLangSource($formData['trans_source']);
             $ens->setLangTarget($formData['trans_target']);
             $ens->setTransOption($formData['trans_option']);
             $ens->setSelectedName($cacheItem['name']);
             $ens->setAnalyzing(true);
             $ens->setIsOneClick($this->pageType == 'all' ? 1 : 2);
             $result = $ens->analysisFiles();
         }

         if(isset($result['stop']) && $result['stop']){
             $cacheUpdate = new EtsTransCache($cacheItem['id_ets_trans_cache']);
             $cacheUpdate->status =1;
             $cacheUpdate->update();
             if($this->getItemAnalysis()){
                 $result['stop'] = 0;
             }
         }
         if($result['stop']){
             $result['nb_path'] = self::getTotalItemTransAll(null, $this->pageType);
         }
         $result['page_type'] = $cacheItem['cache_type'];
         return $result;
     }

     public function getItemAnalysis()
     {
         $id_shop = Context::getContext()->shop->id;
         return Db::getInstance()->getRow("SELECT * FROM `"._DB_PREFIX_."ets_trans_cache` WHERE is_oneclick=".($this->pageType == 'all' ? 1 : 2)." AND status=0 AND id_shop=".(int)$id_shop);
     }

     public function getWebData($transItems)
     {
         $arrTransItems = !is_array($transItems) ? explode(',',$transItems) : $transItems;
         $data = array();
         if(in_array('wd_all',$arrTransItems)){
             $data =  array(
                 'catalog' => array('product', 'category', 'manufacturer', 'supplier', 'attribute', 'attribute_group', 'feature', 'feature_value'),
                 'page' => array('cms', 'cms_category'),
                 'inter' => 'all',
             );
             if(Module::isInstalled('ets_megamenu')){
                 $data['megamenu'] = 'megamenu';
             }
             if(Module::isInstalled('ybc_blog')){
                 $data['blog'] = array('blog_post', 'blog_category');
             }
             if(Module::isInstalled('ets_productcomments')){
                 $data['pc'] = 'pc';
             }
             if(Module::isInstalled('blockreassurance')){
                 $data['blockreassurance'] = 'blockreassurance';
             }
             if(Module::isInstalled('ps_linklist')){
                 $data['ps_linklist'] = 'ps_linklist';
             }
             if(Module::isInstalled('ps_mainmenu')){
                 $data['ps_mainmenu'] = 'ps_mainmenu';
             }
             if(Module::isInstalled('ps_customtext')){
                 $data['ps_customtext'] = 'ps_customtext';
             }
             if(Module::isInstalled('ps_imageslider')){
                 $data['ps_imageslider'] = 'ps_imageslider';
             }
             if(Module::isInstalled('ets_extraproducttabs')){
                 $data['ets_extraproducttabs'] = 'ets_extraproducttabs';
             }
             return $data;
         }

         //Catalog
         if(in_array('catalog_all',$arrTransItems)){
             $data['catalog'] = array('product', 'category', 'manufacturer', 'supplier','attribute', 'attribute_group', 'feature', 'feature_value');
         }
         else if(!in_array('catalog_all',$arrTransItems)){
             if(in_array('catalog_product', $arrTransItems)){
                 $data['catalog'][] = 'product';
             }
             if(in_array('catalog_category', $arrTransItems)){
                 $data['catalog'][] = 'category';
             }
             if(in_array('catalog_manufacturer', $arrTransItems)){
                 $data['catalog'][] = 'manufacturer';
             }
             if(in_array('catalog_supplier', $arrTransItems)){
                 $data['catalog'][] = 'supplier';
             }
             if(in_array('catalog_attribute', $arrTransItems)){
                 $data['catalog'][] = 'attribute';
             }
             if(in_array('catalog_attribute_group', $arrTransItems)){
                 $data['catalog'][] = 'attribute_group';
             }
             if(in_array('catalog_feature', $arrTransItems)){
                 $data['catalog'][] = 'feature';
             }
             if(in_array('catalog_feature_value', $arrTransItems)){
                 $data['catalog'][] = 'feature_value';
             }
         }

         //CMS
         if(in_array('page_all',$arrTransItems)){
             $data['page'] = array('cms', 'cms_category');
         }
         else if(!in_array('page_all',$arrTransItems)){
             if(in_array('page_cms', $arrTransItems)){
                 $data['page'][] = 'cms';
             }
             if(in_array('page_cms_category', $arrTransItems)){
                 $data['page'][] = 'cms_category';
             }
         }

         //Blog
         if(in_array('blog_all', $arrTransItems)){
             $data['blog'] = array('blog_post', 'blog_category');
         }
         elseif (preg_grep('/blog_(.+)/', $arrTransItems)){
            $data['blog'] = array();
            if(in_array('blog_category', $arrTransItems)){
                $data['blog'][] = 'blog_category';
            }
            if(in_array('blog_post', $arrTransItems)){
                $data['blog'][] = 'blog_post';
            }
         }

         //Megamenu
         if(in_array('megamenu', $arrTransItems)){
             $data['megamenu'] = 'megamenu';
         }
         if(in_array('pc', $arrTransItems)){
             $data['pc'] = 'pc';
         }
         if(in_array('blockreassurance', $arrTransItems)){
             $data['blockreassurance'] = 'blockreassurance';
         }
         if(in_array('ps_linklist', $arrTransItems)){
             $data['ps_linklist'] = 'ps_linklist';
         }
         if(in_array('ps_mainmenu', $arrTransItems)){
             $data['ps_mainmenu'] = 'ps_mainmenu';
         }
         if(in_array('ps_customtext', $arrTransItems)){
             $data['ps_customtext'] = 'ps_customtext';
         }
         if(in_array('ps_imageslider', $arrTransItems)){
             $data['ps_imageslider'] = 'ps_imageslider';
         }
         if(in_array('ets_extraproducttabs', $arrTransItems)){
             $data['ets_extraproducttabs'] = 'ets_extraproducttabs';
         }

         //Localization

         if(in_array('inter_all', $arrTransItems)){
             $data['inter'] = 'all';
         }
         else if(preg_grep('/inter_(.+)/', $arrTransItems)){
             $data['inter'] = array();
             if(in_array('inter_back', $arrTransItems)){
                 $data['inter']['back'] = 'back';
             }
             if(in_array('inter_theme', $arrTransItems)){
                 $data['inter']['theme'] = 'theme';
             }
             if(in_array('inter_module', $arrTransItems)){
                 $data['inter']['module'] = 'module';
             }
             if(in_array('inter_email', $arrTransItems)){
                 $data['inter']['email'] = 'email';
             }
             else if(preg_grep('/inter_email_(.+)/', $arrTransItems)){
                 $data['inter']['email'] = array();
                 if(in_array('inter_email_subject', $arrTransItems)){
                     $data['inter']['email']['subject'] = 'subject';
                 }

                 if(in_array('inter_email_body', $arrTransItems)){
                     $data['inter']['email']['subject'] = 'body';
                 }
                 elseif(preg_grep('/inter_email_body_(.+)/', $arrTransItems)){
                     $data['inter']['email']['body'] = array();
                     if(in_array('inter_email_body_core', $arrTransItems)){
                         $data['inter']['email']['body']['core'] = 'core';
                     }
                     elseif($matches = preg_grep('/inter_email_body_core_(.+)/', $arrTransItems)){
                         $data['inter']['email']['body']['core'] = array();
                         foreach ($matches as $item){
                             $data['inter']['email']['body']['core'][] = str_replace('inter_email_body_core_', '', $item);
                         }
                         unset($matches);
                         if(isset($item)){
                             unset($item);
                         }
                     }
                     if(in_array('inter_email_body_theme', $arrTransItems)){
                         $data['inter']['email']['body']['theme'] = 'theme';
                     }
                     elseif(preg_grep('/inter_email_body_theme_(.+)/', $arrTransItems)){
                         $themes = EtsTransCore::getAllThemes();
                         foreach ($themes as $theme){
                             if(in_array('inter_email_body_theme_'.$theme['title'], $arrTransItems)){
                                 $data['inter']['email']['body']['theme'][$theme['title']] = $theme['title'];
                             }
                             else if(preg_grep('/inter_email_body_theme_(.+)/', $arrTransItems)){
                                 if($matches = preg_grep('/inter_email_body_theme_'.$theme['title'].'_(.+)/', $arrTransItems)){
                                     $data['inter']['email']['body']['theme'][$theme['title']] = array();
                                     foreach ($matches as $item){
                                         $data['inter']['email']['body']['theme'][$theme['title']][] = str_replace('inter_email_body_theme_'.$theme['title'].'_', '', $item);
                                     }
                                     unset($matches);
                                     if(isset($item)){
                                         unset($item);
                                     }
                                 }
                             }
                         }
                     }
                 }
             }
             if(in_array('inter_other', $arrTransItems)){
                 $data['inter']['other'] = 'other';
             }

             if($matches = preg_grep('/inter_theme_(.+)/', $arrTransItems)){
                 $data['inter']['theme'] = array();
                 foreach ($matches as $item){
                     $data['inter']['theme'][] = str_replace('inter_theme_', '', $item);
                 }
                 unset($matches);
                 if(isset($item)){
                     unset($item);
                 }
             }
             if($matches = preg_grep('/inter_module_(.+)/', $arrTransItems)){
                 $data['inter']['module'] = array();
                 foreach ($matches as $item){
                     $data['inter']['module'][] = str_replace('inter_module_', '', $item);
                 }
                 unset($matches);
                 if(isset($item)){
                     unset($item);
                 }
             }
         }
         return $data;
     }

     public static function getTotalItemTransAll($id_shop=null, $pageType = 'all')
     {
         if(!$id_shop){
             $id_shop = Context::getContext()->shop->id;
         }
         $oneClickVal = $pageType == 'all' ? 1 : 2;
         return (int)Db::getInstance()->getValue("SELECT COUNT(*) FROM `"._DB_PREFIX_."ets_trans_cache` WHERE is_oneclick=".(int)$oneClickVal." AND id_shop=".(int)$id_shop);
     }

     public function transAllPages($pageType, $langSource, $langTarget, $transOption, $offset = 0)
     {
         $id_shop = Context::getContext()->shop->id;
         switch ($pageType){
             case 'product':
                 $result = EtsTransPage::transAllProduct($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'category':
                 $result = EtsTransPage::translateAllCategory($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'cms':
                 $result = EtsTransPage::translateAllCMS($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'cms_category':
                 $result = EtsTransPage::translateAllCMSCategory($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'manufacturer':
                 $result = EtsTransPage::translateAllManufacturer($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'supplier':
                 $result = EtsTransPage::translateAllSupplier($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'attribute':
                 $result = EtsTransPage::translateAllAttribute($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'attribute_group':
                 $result = EtsTransPage::translateAllAttributeGroup($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'feature':
                 $result = EtsTransPage::translateAllFeature($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'feature_value':
                 $result = EtsTransPage::translateAllFeatureValue($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'blockreassurance':
                 $result = EtsTransPage::translateAllBlockReassurance($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'ps_linklist':
                 $result = EtsTransPage::translateAllLinkList($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'ps_mainmenu':
                 $result = EtsTransPage::translateAllMainMenu($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'ps_imageslider':
                 $result = EtsTransPage::translateAllImageSliders($langSource, $langTarget, $transOption, $offset);
                 break;
             case 'ets_extraproducttabs':
                 $result = EtsTransPage::translateAllExtraProductTabs($langSource, $langTarget, $transOption, $offset);
                 break;
         }
         $data = array(
             'errors'=> false,
             'nb_text' => 0,
             'nb_char' => 0,
             'nb_money'=> 0,
             'nb_item' => 0,
             'stop_translate' => 1,
         );
         if(isset($result) && $result){
             $data['nb_text'] = isset($result['nb_text']) ? (int)$result['nb_text'] : 0;
             $data['nb_char'] = isset($result['translated_length']) ? (int)$result['translated_length'] : 0;
             $data['nb_item'] = isset($result['nb_translated']) ? $result['nb_translated'] : 0;
             $data['stop_translate'] = isset($result['stop_translate']) ? $result['stop_translate'] : 1;
             $cacheItem = Db::getInstance()->getRow("SELECT `id_ets_trans_cache`, `nb_translated` 
                                            FROM `"._DB_PREFIX_."ets_trans_cache` WHERE `cache_type`='".(string)$pageType."' AND `is_oneclick`=".($this->pageType == 'all' ? 1 : 2)." AND `id_shop`=".(int)$id_shop);
             if($cacheItem){
                 $cache = new EtsTransCache($cacheItem['id_ets_trans_cache']);
                 $cache->nb_translated = (int)$cacheItem['nb_translated'] + (int)$data['nb_item'];
                 $cache->save();
             }
         }

         return $data;
     }

     public function translateSf($type, $langSource, $langTarget, $transOption, $selected)
     {
         $etsTransSf = new EtsTransNewSystem($type);
         $etsTransSf->setSelectedName($selected);
         $etsTransSf->setLangSource($langSource);
         $etsTransSf->setLangTarget($langTarget);
         $etsTransSf->setTransOption($transOption);
         $etsTransSf->setIsOneClick($this->pageType == 'all' ? 1 : 2);
         if(in_array($type, array('theme', 'mail', 'back', 'others', 'sfmodule'))){
             return $etsTransSf->translateData($selected);
         }
     }

     public static function deleteCacheItem($id)
     {
         return Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."ets_trans_cache` WHERE id_ets_trans_cache=".(int)$id);
     }

     public function transEmailBody($langSource, $langTarget, $transOption, $mailOptions, $themeName=0)
     {
         if($themeName == '0' || !$themeName){
             $themeName = 0;
         }
        $emailSource = $this->getEmailSource($langSource, $langTarget, $transOption, $mailOptions, $themeName);
        $resTrans = EtsTransPage::translate((int)$langSource, $emailSource, 'email');
        $results = isset($resTrans['result']) ? $resTrans['result'] : array();
        $results = EtsTransInternational::modifyResultTranslated($results, $emailSource);
        $textTranslatedLength = EtsTransInternational::getTextLength($emailSource['source']);
        $results['nb_translated'] = EtsTransInternational::getTotalEmailTranslate($emailSource['target']);
        $results['nb_char_translated'] = $textTranslatedLength;
         if($results && (!isset($results['errors']) || !$results['errors'])){
             EtsTransInternational::saveEmailTranslated($results, $themeName);
         }
         return $results;
     }

     public function getEmailSource($langSource, $langTarget, $transOption, $mailOptions, $themeName=0)
     {
         $data = array(
             'source' => array(),
             'target' => array(),
         );

         foreach ($mailOptions as $option){
             foreach (array('html', 'txt') as $ext){
                 foreach ($langTarget as $idLang){
                     $nameEmail = str_replace(array('[', ']'), array('|', ''), $option);
                     $nameEmail = str_replace(array('core_mail|', 'module_mail|'), array('core_mail|'.$ext.'|', 'module_mail|'.$ext.'|'), $nameEmail);
                     $source = Language::getIsoById($langSource);
                     $target = Language::getIsoById($idLang);
                     $dataItem = EtsTransInternational::getEmailItemData($nameEmail, $transOption, $source, $target, $themeName, null, true);
                     if($dataItem['isTranslatable']){
                         $keyItem = urlencode(str_replace(array('core_mail[', 'module_mail['), array('core_mail['.$ext.'][', 'module_mail['.$ext.']['), $option));
                         $data['source'][$keyItem] = $dataItem['content'];
                         $data['target'][$idLang][$keyItem] = 1;
                         $data['original_content'][$keyItem] = isset($dataItem['original_content']) ? $dataItem['original_content'] : null;
                     }
                 }
             }
         }

         return $data;
     }

 }