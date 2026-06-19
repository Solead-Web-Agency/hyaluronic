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

class EtsTransPage
{
    public static $instance;

    public static function translate($langSource, $dataTrans, $pageType = null, $colData = array(), $pageId = 0)
    {
        if(!isset($dataTrans['source']) || !isset($dataTrans['target'])){
            return false;
        }
        $sources = $dataTrans['source'];
        $resultTrans = array();
        $resultTextTrans = array();
        $api = EtsTransApi::getInstance();
        $textLog = '';
        $addTextLog = true;

        foreach ($dataTrans['target'] as $idLang => $targetItem)
        {
            $timStartTrans = microtime(true);
            $textTrans = array();
            $keyTrans = array();
            foreach ($targetItem as $key=> $isTrans){
                if((int)$isTrans && isset($sources[$key]) && $sources[$key]){
                    $textTrans[] = $sources[$key];
                    /**/
                    if($addTextLog){
                        $textLog .= preg_replace('/[\s]{2,}/', ' ',str_replace("\r\n", " ", $sources[$key]));
                        if(Tools::strlen($textLog) > 50){
                            $addTextLog = false;
                        }
                    }
                    /**/
                    $keyTrans[] = $key;
                }
            }

            $langCodeSource = $langSource ?  Language::getIsoById($langSource) : null;
            $langCodeTarget = Language::getIsoById($idLang);

            if($langCodeTarget)
            {
                $res = $api->translate($langCodeSource, $langCodeTarget, $textTrans, $pageType);
                if(!$res['errors']){
                    foreach ($textTrans as $key=>$item){
                        if($item){
                            //
                        }
                        $resultTrans[$idLang][$keyTrans[$key]] = isset($res['data'][$key]) ? $res['data'][$key] : '';
                        $resultTextTrans[$idLang][$textTrans[$key]] = isset($res['data'][$key]) ? $res['data'][$key] : '';
                    }
                    if(!$langSource && isset($dataTrans['lang_source']) && isset($res['detectedSourceLanguage']) && $res['detectedSourceLanguage']){
                        if($res['detectedSourceLanguage'][0] != EtsTransApi::getLangCodeFromIdLang($dataTrans['lang_source'])){
                            $resSource = $api->translate(null, Language::getIsoById($dataTrans['lang_source']), $textTrans, $pageType);
                            if(!$resSource['errors']){
                                foreach ($textTrans as $key=>$item){
                                    if($item){
                                        //
                                    }
                                    $resultTrans[$dataTrans['lang_source']][$keyTrans[$key]] = isset($resSource['data'][$key]) ? $resSource['data'][$key] : '';
                                    $resultTrans[$dataTrans['lang_source']][$textTrans[$key]] = isset($resSource['data'][$key]) ? $resSource['data'][$key] : '';
                                }
                            }
                        }
                    }
                    EtsTransLog::logTranslate($pageType, true, EtsTransModule::getTextLog($textTrans), null,null, $timStartTrans, $langSource, $idLang, null);
                }
                else{
                    EtsTransLog::logTranslate($pageType, false, EtsTransModule::getTextLog($textTrans), null,null, $timStartTrans, $langSource, $idLang, isset($res['message']) ? $res['message'] : null);

                    return array(
                        'text_log' => strip_tags(rtrim($textLog, ';')),
                        'result' => $res
                    );
                }
            }
        }

        $savePsPageSuccess = false;
        if($pageType && $colData && $pageId){
            $savePsPageSuccess = self::updateOnDB($pageType, $pageId, $colData, $resultTrans);
        }

        return array(
            'text_log' => strip_tags(rtrim($textLog, ';')),
            'result' => $resultTrans,
            'resultText' => $resultTextTrans,
            'dataSaved' => $savePsPageSuccess
        );
    }

    public static function updateOnDB($pageType, $pageId, $colData, $transData)
    {
        $context = Context::getContext();
        switch ($pageType){
            case 'product':
                $obj = new Product($pageId);
                $fieldName = 'name';
                $fieldRewrite = 'link_rewrite';
                break;
            case 'category':
                $obj = new Category($pageId);
                $fieldName = 'name';
                $fieldRewrite = 'link_rewrite';
                break;
            case 'cms':
                $obj = new CMS($pageId);
                $fieldName = 'meta_title';
                $fieldRewrite = 'link_rewrite';
                break;
            case 'cms_category':
                $obj = new CMSCategory($pageId);
                $fieldName = 'name';
                $fieldRewrite = 'link_rewrite';
                break;
            case 'manufacturer':
                $obj = new Manufacturer($pageId);
                break;
            case 'supplier':
                $obj = new Supplier($pageId);
                break;
            case 'attribute_group':
                $obj = new AttributeGroup($pageId);
                $fieldName = 'name';
                $fieldRewrite = 'url_name';
                break;
            case 'attribute':
                $obj = new Attribute($pageId);
                $fieldName = 'name';
                $fieldRewrite = 'url_name';
                break;
            case 'feature':
                $obj = new Feature($pageId);
                $fieldName = 'name';
                $fieldRewrite = 'url_name';
                break;
            case 'feature_value':
                $obj = new FeatureValue($pageId);
                $fieldName = 'value';
                $fieldRewrite = 'url_name';
                break;
            default:
                $obj = null;
        }

        if(!in_array($pageType, array('blockreassurance', 'ps_linklist', 'ps_mainmenu', 'ps_customtext', 'ps_imageslider', 'ets_extraproducttabs')) && (!isset($obj) || !$obj || !$obj->id)){
            return false;
        }

        $transLinkRewrite = (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE');
        if($pageType == 'attribute' && ($attrLangData = self::getAttributeLangData($pageId))){
            foreach ($attrLangData as $attrLangDataItem)
            {
                foreach ($attrLangDataItem as $ka=>$va){
                    if($ka !== 'id_lang'){
                        ${'_GET'}[$ka.'_'.$attrLangDataItem['id_lang']] = $va;
                    }
                }
            }
        }
        if($pageType == 'feature_value' && ($featureLangData = self::getFeatureValueLang($pageId))){
            foreach ($featureLangData as $featureLangDataItem)
            {
                foreach ($featureLangDataItem as $ka=>$va){
                    if($ka !== 'id_lang'){
                        ${'_GET'}[$ka.'_'.$featureLangDataItem['id_lang']] = $va;
                    }
                }
            }
        }

        foreach ($transData as $idLang=>$textTrans){
            foreach ($textTrans as $key=>$text){
                if($pageType == 'blockreassurance'){
                    if(Tools::getValue('isNewBlockreassurance')){
                        Db::getInstance()->execute("UPDATE "._DB_PREFIX_."psreassurance_lang SET `".(string)$colData[$key]."`='".pSQL($text)."' WHERE id_psreassurance=".(int)$pageId." AND id_lang=".(int)$idLang);
                    }
                    else
                        Db::getInstance()->execute("UPDATE "._DB_PREFIX_."reassurance_lang SET `text`='".pSQL($text)."' WHERE id_reassurance=".(int)$pageId." AND id_lang=".(int)$idLang);
                    continue;
                }
                elseif($pageType == 'ps_linklist'){
                    if($key == 'form_link_block_block_name_'){
                        Db::getInstance()->execute("UPDATE "._DB_PREFIX_."link_block_lang SET `name`='".pSQL($text)."' WHERE id_link_block=".(int)$pageId." AND id_lang=".(int)$idLang);
                    }
                    elseif(strpos($key, 'form_link_block_custom_') !== false){

                        $linkBlockItem = Db::getInstance()->getRow("SELECT `custom_content` FROM `"._DB_PREFIX_."link_block_lang` WHERE id_link_block=".(int)$pageId." AND id_lang=".(int)$idLang);
                        if($linkBlockItem){
                            $customContent  = $linkBlockItem['custom_content'];
                            if($customContent){
                                $customContent = Tools::jsonDecode($customContent, true);
                            }
                            else{
                                $customContent = array();
                            }
                            preg_match('/^form_link_block_custom_([0-9]+)_([0-9]+)_title$/', 'form_link_block_custom_0_1_title', $matchesLinkBlock);
                            $indexLb = isset($matchesLinkBlock[1]) ? $matchesLinkBlock[1] : null;
                            $idLangLb  = isset($matchesLinkBlock[2]) ? $matchesLinkBlock[2] : null;
                            if($indexLb !== null && $idLangLb !== null){
                                if(isset($customContent[(int)$indexLb])){
                                    $customContent[(int)$indexLb] = array(
                                        'title' =>$text,
                                        'url' => $transLinkRewrite ? Tools::str2url($text) : $customContent[(int)$indexLb]['url']
                                    );
                                }
                                else{
                                    $customContent[] = array(
                                        'title' =>$text,
                                        'url' => $transLinkRewrite ? Tools::str2url($text) : ''
                                    );
                                }
                            }

                            Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."link_block_lang` SET `custom_content`='".Tools::jsonEncode($customContent)."' WHERE id_link_block=".(int)$pageId." AND id_lang=".(int)$idLang);
                        }
                    }
                    continue;
                }
                elseif ($pageType == 'ps_mainmenu'){
                    Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."linksmenutop_lang` SET `label`='".pSQL($text)."' WHERE id_linksmenutop=".(int)$pageId." AND id_lang=".(int)$idLang);
                    continue;
                }
                elseif ($pageType == 'ps_customtext'){
                    Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."info_lang` SET `text`='".pSQL($text, true)."' WHERE id_lang=".(int)$idLang)." AND id_shop=".(int)$context->shop->id;
                    continue;
                }
                elseif ($pageType == 'ps_imageslider'){
                    Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."homeslider_slides_lang` SET `".(string)$colData[$key]."`='".pSQL($text, true)."' WHERE id_homeslider_slides=".(int)$pageId." AND id_lang=".(int)$idLang);
                    continue;
                }
                elseif ($pageType == 'ets_extraproducttabs'){
                    Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."ets_ept_tab_lang` SET `".(string)$colData[$key]."`='".pSQL($text, true)."' WHERE id_ets_ept_tab=".(int)$pageId." AND id_lang=".(int)$idLang);
                    continue;
                }
                if($pageType == 'attribute_group' && $key == 'meta_title_'){
                    self::createAttributeGroupIndexable($pageId);
                    Db::getInstance()->execute("UPDATE "._DB_PREFIX_."layered_indexable_attribute_group_lang_value SET meta_title='".pSQL($text)."' WHERE id_attribute_group=".(int)$pageId." AND id_lang=".(int)$idLang);
                    continue;
                }
                elseif($pageType == 'feature' && $key == 'meta_title_'){
                    self::createFeatureIndexable($pageId);
                    Db::getInstance()->execute("UPDATE "._DB_PREFIX_."layered_indexable_feature_lang_value SET meta_title='".pSQL($text)."' WHERE id_feature=".(int)$pageId." AND id_lang=".(int)$idLang);
                    continue;
                }
                else if($pageType == 'attribute' && $key == 'meta_title_'){
                    ${'_GET'}['meta_title_'.(int)$idLang] = $text;
                    continue;
                }
                else if($pageType == 'feature_value' && $key == 'meta_title_'){
                    ${'_GET'}['meta_title_'.(int)$idLang] = $text;
                    continue;
                }
                if(isset($colData[$key]) && $colData[$key]){

                    if(strpos($key, 'keywords') !== false){
                        $obj->{$colData[$key]}[$idLang] = str_replace('|', ',', $text);
                    }
                    else {
                        $obj->{$colData[$key]}[$idLang] = $text;
                    }
                    if($pageType == 'attribute' || $pageType == 'feature_value'){
                        ${'_GET'}[$colData[$key].'_'.$idLang] = $obj->{$colData[$key]}[$idLang];
                        if($transLinkRewrite && isset($fieldRewrite) && $colData[$key] == $fieldName){
                            ${'_GET'}[$fieldRewrite.'_'.$idLang] = Tools::str2url($obj->{$colData[$key]}[$idLang]);
                        }
                    }
                    elseif($transLinkRewrite && isset($fieldRewrite) && $colData[$key] == $fieldName){
                        if($pageType == 'attribute_group'){
                            Db::getInstance()->execute("UPDATE "._DB_PREFIX_."layered_indexable_attribute_group_lang_value SET url_name='".pSQL(Tools::str2url($obj->{$colData[$key]}[$idLang]))."' WHERE id_attribute_group=".(int)$pageId." AND id_lang=".(int)$idLang);
                        }
                        elseif($pageType == 'attribute_group'){
                            Db::getInstance()->execute("UPDATE "._DB_PREFIX_."layered_indexable_feature_lang_value SET url_name='".pSQL(Tools::str2url($obj->{$colData[$key]}[$idLang]))."' WHERE id_feature=".(int)$pageId." AND id_lang=".(int)$idLang);
                        }
                        else{
                            $obj->{$fieldRewrite}[$idLang] = Tools::str2url($obj->{$colData[$key]}[$idLang]);
                        }
                    }

                }
            }
        }
        return isset($obj) && $obj->update() ? true : (!isset($obj) ? true : false);
    }

    public static function transProduct($ids, $sourceLang, $targetLang, $transOption){
        if(!$ids){
            return false;
        }

        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $products = array();
        foreach ($ids as $id)
        {
            $products[] = new Product((int)$id);
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = ['name', 'description_short', 'description', 'available_now', 'available_later', 'delivery_in_stock', 'delivery_out_stock','meta_title', 'meta_description'];
        $strTranslatedLength = 0;
        if(!is_array($targetLang)){
            $targetLang = explode(',', $targetLang);
        }
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($products, $transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'product');
            if(!$res['errors'] && $res['data']){
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $translatedText = $res['data'];
                $productUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $productId = $keySplit[0];
                    $productField = $keySplit[1];

                    if(!isset($productUpdate[$productId])){
                        $p = new Product($productId);
                        $productUpdate[$productId] = $p;
                        if(strpos($productField, 'keywords') !== false){
                            $p->{$productField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$productField}[$idLang] = $translatedText[$key];
                            if($productField == 'name'){
                                if((int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') && $urlRewrite = self::slugify($translatedText[$key])){
                                    $p->link_rewrite[$idLang] = $urlRewrite;
                                }
                            }
                        }

                    }
                    else{
                        $p = &$productUpdate[$productId];
                        if(strpos($productField, 'keywords') !== false){
                            $p->{$productField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$productField}[$idLang] = $translatedText[$key];
                            if($productField == 'name'){
                                if((int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') && $urlRewrite = self::slugify($translatedText[$key])){
                                    $p->link_rewrite[$idLang] = $urlRewrite;
                                }
                            }
                        }

                        if (isset($keyTrans[$key + 1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if ($keySplit2[0] != $productId) {
                                $p->save();
                            }
                        } else {
                            $p->save();
                        }
                    }
                }
                unset($productUpdate);
                EtsTransLog::logTranslate('product', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('product', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function transAllProduct($sourceLang, $targetLang, $transOption, $offset = 0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_product` FROM `"._DB_PREFIX_."product_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_product'];
        }
        $result = self::transProduct($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllCategory($sourceLang, $targetLang, $transOption, $offset = 0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_category` FROM `"._DB_PREFIX_."category_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_category'];
        }
        $result = self::translateCategory($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllCMS($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_cms` FROM `"._DB_PREFIX_."cms_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_cms'];
        }
        $result = self::translateCMS($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllCMSCategory($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_cms_category` FROM `"._DB_PREFIX_."cms_category_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);

        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_cms_category'];
        }
        $result = self::translateCMSCategory($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllManufacturer($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_manufacturer` FROM `"._DB_PREFIX_."manufacturer_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_manufacturer'];
        }
        $result = self::translateManufacturer($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllSupplier($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_supplier` FROM `"._DB_PREFIX_."supplier_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_supplier'];
        }
        $result = self::translateSupplier($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllBlockReassurance($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 10;
        $moduleObj = Module::getInstanceByName('blockreassurance');
        $isNewBlockreassurance = (int)Tools::getValue('isNewBlockreassurance') || ($moduleObj && version_compare('4.0.0', $moduleObj->version, '<='));
        if($isNewBlockreassurance) {
            $idsData = Db::getInstance()->executeS("SELECT `id_psreassurance` FROM `" . _DB_PREFIX_ . "psreassurance` WHERE `id_shop`=" . (int)$context->shop->id . " LIMIT " . (int)$offset . ", " . (int)$limit);
            $idCol = 'id_psreassurance';
        }
        else {
            $idsData = Db::getInstance()->executeS("SELECT `id_reassurance` FROM `" . _DB_PREFIX_ . "reassurance` WHERE `id_shop`=" . (int)$context->shop->id . " LIMIT " . (int)$offset . ", " . (int)$limit);
            $idCol = 'id_reassurance';
        }
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item[$idCol];
        }
        $result = self::translateBlockReassurance($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllLinkList($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_link_block` FROM `"._DB_PREFIX_."link_block` LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_link_block'];
        }
        $result = self::translateLinkList($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }
    public static function translateAllMainMenu($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_linksmenutop` FROM `"._DB_PREFIX_."linksmenutop` WHERE id_shop=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_linksmenutop'];
        }
        $result = self::translateMainMenu($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllImageSliders($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_homeslider_slides` FROM `"._DB_PREFIX_."homeslider` WHERE id_shop=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_homeslider_slides'];
        }
        $result = self::translateImageSliders($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }
    public static function translateAllExtraProductTabs($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_ets_ept_tab` FROM `"._DB_PREFIX_."ets_ept_tab` WHERE id_shop=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_ets_ept_tab'];
        }
        $result = self::translateExtraProductTabs($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllAttributeGroup($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 2;
        $idsData = Db::getInstance()->executeS("SELECT `id_attribute_group` FROM `"._DB_PREFIX_."attribute_group_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_attribute_group'];
        }
        $result = self::translateAttributeGroup($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }
    public static function translateAllAttribute($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 10;
        $idsData = Db::getInstance()->executeS("
            SELECT attrs.`id_attribute` FROM `"._DB_PREFIX_."attribute_shop` attrs 
            LEFT JOIN "._DB_PREFIX_."attribute a ON attrs.id_attribute=a.id_attribute
            WHERE attrs.`id_shop`=".(int)$context->shop->id.(($idAttributeGroup = Tools::getValue('idAttributeGroup')) ? " AND a.id_attribute_group=".(int)$idAttributeGroup : "")." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_attribute'];
        }
        $result = self::translateAttribute($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllFeature($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 10;
        $idsData = Db::getInstance()->executeS("SELECT `id_feature` FROM `"._DB_PREFIX_."feature_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_feature'];
        }
        $result = self::translateFeature($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateAllFeatureValue($sourceLang, $targetLang, $transOption, $offset=0)
    {
        $context = Context::getContext();
        $limit = 10;
        $idsData = Db::getInstance()->executeS("
            SELECT fv.`id_feature_value` FROM `"._DB_PREFIX_."feature_value` fv
            LEFT JOIN "._DB_PREFIX_."feature_shop fs ON fv.id_feature=fs.id_feature
            WHERE fs.`id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
        if(!$idsData || !count($idsData)){
            return array(
                'nb_translated' => $offset,
                'stop_translate' => true
            );
        }
        $ids = array();
        foreach ($idsData as $item){
            $ids[] = $item['id_feature'];
        }
        $result = self::translateFeature($ids, $sourceLang, $targetLang, $transOption);
        if(!is_array($result)){
            $result = array();
        }

        $result['nb_translated'] = $offset + count($idsData);
        $result['ids_translated'] = $ids;
        $stopTranslate = false;
        if(count($idsData) < $limit){
            $stopTranslate = true;
        }
        $result['stop_translate'] = $stopTranslate;
        if(isset($result['errors']) && $result['errors']){
            $result['stop_translate'] = true;
        }
        return $result;
    }

    public static function translateCategory($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $categories = array();
        foreach ($ids as $id)
        {
            $categories[] = new Category((int)$id);
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = ['name', 'description', 'meta_title', 'meta_description', 'meta_keywords'];
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($categories,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'category');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];

                    if(!isset($itemUpdate[$itemId])){
                        $p = new Category($itemId);
                        $itemUpdate[$itemId] = $p;
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                            if($itemField == 'name'){
                                if((int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') && $urlRewrite = self::slugify($translatedText[$key])){
                                    $p->link_rewrite[$idLang] = $urlRewrite;
                                }
                            }
                        }

                    }
                    else{
                        $p = &$itemUpdate[$itemId];
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                            if($itemField == 'name'){
                                if((int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') && $urlRewrite = self::slugify($translatedText[$key])){
                                    $p->link_rewrite[$idLang] = $urlRewrite;
                                }
                            }
                        }

                        if(isset($keyTrans[$key+1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if($keySplit2[0] != $itemId){
                                $p->save();
                            }
                        }
                        else{
                            $p->save();
                        }
                    }
                }
                unset($itemUpdate);
                EtsTransLog::logTranslate('category', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('category', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateCMS($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $cms = array();
        foreach ($ids as $id)
        {
            $cms[] = new CMS((int)$id);
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = ['meta_title', 'head_seo_title', 'meta_description', 'meta_keywords', 'content'];
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($cms,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'cms');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];

                    if(!isset($itemUpdate[$itemId])){
                        $p = new CMS($itemId);
                        $itemUpdate[$itemId] = $p;
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                            if($itemField == 'meta_title'){
                                if((int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') && $urlRewrite = self::slugify($translatedText[$key])){
                                    $p->link_rewrite[$idLang] = $urlRewrite;
                                }
                            }
                        }

                    }
                    else{
                        $p = &$itemUpdate[$itemId];
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                            if($itemField == 'meta_title'){
                                if((int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') && $urlRewrite = self::slugify($translatedText[$key])){
                                    $p->link_rewrite[$idLang] = $urlRewrite;
                                }
                            }
                        }

                        if(isset($keyTrans[$key+1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if($keySplit2[0] != $itemId){
                                $p->save();
                            }
                        }
                        else{
                            $p->save();
                        }
                    }
                }
                unset($itemUpdate);

                EtsTransLog::logTranslate('cms', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('cms', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateCMSCategory($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $cmsCategory = array();
        foreach ($ids as $id)
        {
            $cmsCategory[] = new CMSCategory((int)$id);
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = ['name', 'meta_title', 'description', 'meta_description', 'meta_keywords'];
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($cmsCategory,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'cms_category');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];

                    if(!isset($itemUpdate[$itemId])){
                        $p = new CMSCategory($itemId);
                        $itemUpdate[$itemId] = $p;
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                            if($itemField == 'name'){
                                if((int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') && $urlRewrite = self::slugify($translatedText[$key])){
                                    $p->link_rewrite[$idLang] = $urlRewrite;
                                }
                            }
                        }

                    }
                    else{
                        $p = &$itemUpdate[$itemId];
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                            if($itemField == 'name'){
                                if((int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') && $urlRewrite = self::slugify($translatedText[$key])){
                                    $p->link_rewrite[$idLang] = $urlRewrite;
                                }
                            }
                        }

                        if(isset($keyTrans[$key+1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if($keySplit2[0] != $itemId){
                                $p->save();
                            }
                        }
                        else{
                            $p->save();
                        }
                    }
                }
                unset($itemUpdate);
                EtsTransLog::logTranslate('cms_category', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('cms_category', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateManufacturer($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $manufacturer = array();
        foreach ($ids as $id)
        {
            $manufacturer[] = new Manufacturer((int)$id);
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = ['description', 'short_description', 'meta_title', 'meta_description', 'meta_keywords'];
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($manufacturer,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'manufacturer');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];

                    if(!isset($itemUpdate[$itemId])){
                        $p = new Manufacturer($itemId);
                        $itemUpdate[$itemId] = $p;
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }
                    }
                    else{
                        $p = &$itemUpdate[$itemId];
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }

                        if(isset($keyTrans[$key+1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if($keySplit2[0] != $itemId){
                                $p->save();
                            }
                        }
                        else{
                            $p->save();
                        }
                    }
                }
                unset($itemUpdate);
                EtsTransLog::logTranslate('manufacturer', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('manufacturer', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateSupplier($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $manufacturer = array();
        foreach ($ids as $id)
        {
            $manufacturer[] = new Supplier((int)$id);
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = ['description', 'meta_title', 'meta_description', 'meta_keywords'];
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($manufacturer,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'supplier');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];

                    if(!isset($itemUpdate[$itemId])){
                        $p = new Supplier($itemId);
                        $itemUpdate[$itemId] = $p;
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }
                    }
                    else{
                        $p = &$itemUpdate[$itemId];
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }

                        if(isset($keyTrans[$key+1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if($keySplit2[0] != $itemId){
                                $p->save();
                            }
                        }
                        else{
                            $p->save();
                        }
                    }
                }
                unset($itemUpdate);
                EtsTransLog::logTranslate('supplier', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('supplier', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateImageSliders($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $imageSliderObj = self::getImageSliderObjects($ids);
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = ['title', 'description', 'legend'];
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($imageSliderObj,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'ps_imageslider');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];
                    if((int)$itemId){
                        Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."homeslider_slides_lang` SET `".(string)$itemField."` = '".pSQL($translatedText[$key], $itemField == 'description')."' WHERE `id_homeslider_slides`=".(int)$itemId." AND id_lang=".(int)$idLang);
                    }
                }
                EtsTransLog::logTranslate('ps_imageslider', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('ps_imageslider', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }
    public static function translateExtraProductTabs($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $extraProductTabs = self::getExtraProductTabObjects($ids);
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = ['name', 'content'];
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($extraProductTabs,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'ets_extraproducttabs');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];
                    if((int)$itemId){
                        Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."ets_ept_tab_lang` SET `".(string)$itemField."` = '".pSQL($translatedText[$key], $itemField == 'description')."' WHERE `id_ets_ept_tab`=".(int)$itemId." AND id_lang=".(int)$idLang);
                    }
                }
                EtsTransLog::logTranslate('ets_extraproducttabs', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('ets_extraproducttabs', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateLinkList($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $linkListObj = self::getLinkListBlockObject($ids);
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = array('name', 'custom_content');
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;

        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($linkListObj,$transFields,$idLang,$sourceLang,$transOption, 'ps_linklist');
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'ps_linklist');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit)  < 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];
                    if($itemField == 'name'){
                        Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."link_block_lang` SET `name`='".pSQL($translatedText[$key])."' WHERE id_link_block=".(int)$itemId." AND id_lang=".(int)$idLang);
                    }
                    elseif($itemField == 'custom_content'){
                        $indexCustomContent = $keySplit[2];
                        //$keyCustomContent = $keySplit[3];
                        $rowLinkBlock = Db::getInstance()->getRow("SELECT `custom_content` FROM `"._DB_PREFIX_."link_block_lang` WHERE id_link_block=".(int)$itemId." AND id_lang=".(int)$idLang);
                        if(!$rowLinkBlock){
                            continue;
                        }
                        $customContentDb = $rowLinkBlock['custom_content'] ? Tools::jsonDecode($rowLinkBlock['custom_content'], true) : array();

                        if(!isset($customContentDb[(int)$indexCustomContent])){
                            $customContentDb[] = array(
                                'title' => $translatedText[$key],
                                'url' => (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') ? Tools::str2url($translatedText[$key]) : ''
                            );
                        }
                        else{
                            $customContentDb[(int)$indexCustomContent] = array(
                                'title' => $translatedText[$key],
                                'url' => (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE') ? Tools::str2url($translatedText[$key]) : $customContentDb[(int)$indexCustomContent]['url'],
                            );
                        }
                        Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."link_block_lang` SET `custom_content`='".Tools::jsonEncode($customContentDb)."' WHERE id_link_block=".(int)$itemId." AND id_lang=".(int)$idLang);
                    }

                }
                unset($itemUpdate);
                EtsTransLog::logTranslate('ps_linklist', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('ps_linklist', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateMainMenu($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $linkListObj = self::getMainMenuObject($ids);
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = array('label');
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;

        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($linkListObj,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'ps_mainmenu');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit)  < 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];
                    if($itemField == 'label'){
                        Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."linksmenutop_lang` SET `label`='".pSQL($translatedText[$key])."' WHERE id_linksmenutop=".(int)$itemId." AND id_lang=".(int)$idLang);
                    }
                }
                EtsTransLog::logTranslate('ps_mainmenu', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('ps_mainmenu', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateBlockReassurance($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        $moduleObj = Module::getInstanceByName('blockreassurance');
        $isNewBlockreassurance = (int)Tools::getValue('isNewBlockreassurance') || ($moduleObj && version_compare('4.0.0', $moduleObj->version, '<='));
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $blockReassurance = self::getReassuranceObject($ids, $isNewBlockreassurance);
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        if($isNewBlockreassurance){
            $transFields = array('title', 'description');
        }
        else{
            $transFields = array('text');
        }

        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($blockReassurance,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'blockreassurance');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];
                    if($isNewBlockreassurance){
                        Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."psreassurance_lang` SET `".(string)$itemField."`='".pSQL($translatedText[$key])."' WHERE id_psreassurance=".(int)$itemId." AND id_lang=".(int)$idLang);
                    }
                    else
                        Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."reassurance_lang` SET `text`='".pSQL($translatedText[$key])."' WHERE id_reassurance=".(int)$itemId." AND id_lang=".(int)$idLang);
                }
                EtsTransLog::logTranslate('blockreassurance', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('blockreassurance', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function getReassuranceObject($ids, $isNewVersion = false)
    {
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $blockReassurance = array();
        if(!$ids)
            return $blockReassurance;
        if($isNewVersion) {
            $dataDb = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "psreassurance_lang` WHERE `id_psreassurance` IN (" . implode(',', $ids) . ")");
            $idCol = 'id_psreassurance';
        }
        else {
            $dataDb = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "reassurance_lang` WHERE `id_reassurance` IN (" . implode(',', $ids) . ")");
            $idCol = 'id_reassurance';
        }
        $objectData = array();
        foreach ($dataDb as $item)
        {
            if(!isset($objectData[$item[$idCol]])){
                if($isNewVersion){
                    $objectData[$item[$idCol]] = array(
                        $item['id_lang'] => array(
                            'title' => $item['title'],
                            'description' => $item['description'],
                        )
                    );
                }
                else{
                    $objectData[$item[$idCol]] = array(
                        $item['id_lang'] => $item['text']
                    );
                }

            }
            else{
                if($isNewVersion){
                    $objectData[$item[$idCol]][$item['id_lang']] = array(
                        'title' => $item['title'],
                        'description' => $item['description'],
                    );
                }
                else {
                    $objectData[$item[$idCol]][$item['id_lang']] = $item['text'];
                }
            }
        }
        foreach ($objectData as $id=>$itemObj){
            $objBlock = new stdClass();
            if($isNewVersion){
                $objBlock->title = array();
                $objBlock->description = array();
            }
            else
                $objBlock->text = array();
            $objBlock->id = $id;
            foreach ($itemObj as $idLangItem=>$textVal){
                if($isNewVersion) {
                    $objBlock->title[$idLangItem] = $textVal['title'];
                    $objBlock->description[$idLangItem] = $textVal['description'];
                }
                else
                    $objBlock->text[$idLangItem] = $textVal;
            }
            $blockReassurance[$id] = $objBlock;
        }
        return $blockReassurance;
    }
    public static function getLinkListBlockObject($ids)
    {
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $blockReassurance = array();
        if(!$ids)
            return $blockReassurance;
        $dataDb = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."link_block_lang` WHERE `id_link_block` IN (".implode(',', $ids).")");
        $objectData = array();
        foreach ($dataDb as $item)
        {
            if(!isset($objectData[$item['id_link_block']])){
                $customContent = $item['custom_content'] ? Tools::jsonDecode($item['custom_content'], true) : array();
                $customContentData = array();
                foreach ($customContent as $icc){
                    $customContentData[] = array('title'=>$icc['title']);
                }
                $objectData[$item['id_link_block']] = array(
                    $item['id_lang'] => array(
                        'name' => $item['name'],
                        'custom_content' => $customContentData,
                    )
                );
            }
            else{
                $customContent = $item['custom_content'] ? Tools::jsonDecode($item['custom_content'], true) : array();
                $customContentData = array();
                foreach ($customContent as $icc){
                    $customContentData[] = array('title'=>$icc['title']);
                }
                $objectData[$item['id_link_block']][$item['id_lang']] = array(
                    'name' => $item['name'],
                    'custom_content' => $customContentData,
                );
            }
        }
        foreach ($objectData as $id=>$itemObj){
            $objBlock = new stdClass();
            $objBlock->name = array();
            $objBlock->custom_content = array();
            $objBlock->id = $id;
            foreach ($itemObj as $idLangItem=>$textVal){
                $objBlock->name[$idLangItem] = $textVal['name'];
                $objBlock->custom_content[$idLangItem] = $textVal['custom_content'];
            }
            $blockReassurance[$id] = $objBlock;
        }
        return $blockReassurance;
    }

    public static function getMainMenuObject($ids)
    {
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $mainMenuObjects = array();
        if(!$ids)
            return $mainMenuObjects;
        $dataDb = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."linksmenutop_lang` WHERE `id_linksmenutop` IN (".implode(',', $ids).")");

        $objectData = array();
        foreach ($dataDb as $item)
        {
            if(!isset($objectData[$item['id_linksmenutop']])){
                $objectData[$item['id_linksmenutop']] = array(
                    $item['id_lang'] => array(
                        'label' => $item['label']
                    )
                );
            }
            else{
                $objectData[$item['id_linksmenutop']][$item['id_lang']] = array(
                    'label' => $item['label']
                );
            }
        }
        foreach ($objectData as $id=>$itemObj){
            $objBlock = new stdClass();
            $objBlock->label = array();
            $objBlock->id = $id;
            foreach ($itemObj as $idLangItem=>$textVal){
                $objBlock->label[$idLangItem] = $textVal['label'];
            }
            $mainMenuObjects[$id] = $objBlock;
        }
        return $mainMenuObjects;
    }
    public static function getImageSliderObjects($ids)
    {
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $mainMenuObjects = array();
        if(!$ids)
            return $mainMenuObjects;
        $dataDb = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."homeslider_slides_lang` WHERE `id_homeslider_slides` IN (".implode(',', $ids).")");

        $objectData = array();
        foreach ($dataDb as $item)
        {
            if(!isset($objectData[$item['id_homeslider_slides']])){
                $objectData[$item['id_homeslider_slides']] = array(
                    $item['id_lang'] => array(
                        'title' => $item['title'],
                        'description' => $item['description'],
                        'legend' => $item['legend'],
                    )
                );
            }
            else{
                $objectData[$item['id_homeslider_slides']][$item['id_lang']] = array(
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'legend' => $item['legend'],
                );
            }
        }
        foreach ($objectData as $id=>$itemObj){
            $objBlock = new stdClass();
            $objBlock->title = array();
            $objBlock->description = array();
            $objBlock->legend = array();
            $objBlock->id = $id;
            foreach ($itemObj as $idLangItem=>$textVal){
                $objBlock->title[$idLangItem] = $textVal['title'];
                $objBlock->description[$idLangItem] = $textVal['description'];
                $objBlock->legend[$idLangItem] = $textVal['legend'];
            }
            $mainMenuObjects[$id] = $objBlock;
        }
        return $mainMenuObjects;
    }

    public static function getExtraProductTabObjects($ids)
    {
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $extraTabObj = array();
        if(!$ids)
            return $extraTabObj;
        $dataDb = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."ets_ept_tab_lang` WHERE `id_ets_ept_tab` IN (".implode(',', $ids).")");

        $objectData = array();
        foreach ($dataDb as $item)
        {
            if(!isset($objectData[$item['id_ets_ept_tab']])){
                $objectData[$item['id_ets_ept_tab']] = array(
                    $item['id_lang'] => array(
                        'name' => $item['name'],
                        'content' => $item['content'],
                    )
                );
            }
            else{
                $objectData[$item['id_ets_ept_tab']][$item['id_lang']] = array(
                    'name' => $item['name'],
                    'content' => $item['content'],
                );
            }
        }
        foreach ($objectData as $id=>$itemObj){
            $objBlock = new stdClass();
            $objBlock->name = array();
            $objBlock->content = array();
            $objBlock->id = $id;
            foreach ($itemObj as $idLangItem=>$textVal){
                $objBlock->name[$idLangItem] = $textVal['name'];
                $objBlock->content[$idLangItem] = $textVal['content'];
            }
            $extraTabObj[$id] = $objBlock;
        }
        return $extraTabObj;
    }

    public static function translateAttributeGroup($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $attributeGroups = array();
        foreach ($ids as $id)
        {
            $attributeGroupsItem = new AttributeGroup((int)$id);
            $agls = Db::getInstance()->executeS("SELECT `meta_title`, `id_lang` FROM `"._DB_PREFIX_."layered_indexable_attribute_group_lang_value` WHERE id_attribute_group=".(int)$id);

            $attributeGroupsItem->meta_title = array();
            if($agls){
                foreach ($agls as $agl){
                    $attributeGroupsItem->meta_title[$agl['id_lang']] = $agl['meta_title'];
                }
            }
            else{
                foreach (Language::getLanguages(false) as $lang){
                    $attributeGroupsItem->meta_title[$lang['id_lang']] = '';
                }
            }
            $attributeGroups[] = $attributeGroupsItem;
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = array('name', 'public_name', 'meta_title');
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($attributeGroups,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'attribute_group');
            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];
                    if($itemField == 'meta_title'){
                        self::createAttributeGroupIndexable($itemId);
                        Db::getInstance()->execute("UPDATE "._DB_PREFIX_."layered_indexable_attribute_group_lang_value SET meta_title='".pSQL($translatedText[$key])."' WHERE id_attribute_group=".(int)$itemId." AND id_lang=".(int)$idLang);
                        continue;
                    }
                    if(!isset($itemUpdate[$itemId])){
                        $p = new AttributeGroup($itemId);
                        $itemUpdate[$itemId] = $p;
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }
                        $p->save();
                    }
                    else{
                        $p = &$itemUpdate[$itemId];
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }

                        if(isset($keyTrans[$key+1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if($keySplit2[0] != $itemId){
                                $p->save();
                            }
                        }
                        else{
                            $p->save();
                        }
                    }
                }
                EtsTransLog::logTranslate('attribute_group', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
                unset($itemUpdate);
            }
            else{

                EtsTransLog::logTranslate('attribute_group', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateAttribute($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $attributes = array();
        foreach ($ids as $id)
        {
            $attributeItem = new Attribute((int)$id);
            $agls = Db::getInstance()->executeS("SELECT `meta_title`, `id_lang` FROM `"._DB_PREFIX_."layered_indexable_attribute_lang_value` WHERE id_attribute=".(int)$id);

            $attributeItem->meta_title = array();
            if($agls){
                foreach ($agls as $agl){
                    $attributeItem->meta_title[$agl['id_lang']] = $agl['meta_title'];
                }
            }
            else{
                foreach (Language::getLanguages(false) as $lang){
                    $attributeItem->meta_title[$lang['id_lang']] = '';
                }
            }
            $attributes[] = $attributeItem;
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = array('name', 'meta_title');
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;

        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($attributes,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'attribute');

            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();

                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];

                    if(!isset($itemUpdate[$itemId]) && ($attrLangData = self::getAttributeLangData($itemId))){
                        foreach ($attrLangData as $itemLangData)
                        {
                            foreach ($itemLangData as $kid=>$vid){
                                if($kid !== 'id_lang')
                                    ${'_GET'}[$kid.'_'.$itemLangData['id_lang']] = $vid;
                            }
                        }
                    }

                    $itemField = $keySplit[1];
                    if($itemField == 'meta_title'){
                       ${'_GET'}['meta_title_'.(int)$idLang] = $translatedText[$key];
                        $p = new Attribute($itemId);
                        $p->save();
                        continue;
                    }
                    if(!isset($itemUpdate[$itemId])){
                        $p = new Attribute($itemId);
                        $itemUpdate[$itemId] = $p;
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }
                       ${'_GET'}[$itemField.'_'.$idLang] = $p->{$itemField}[$idLang];
                        if($itemField == 'name' && (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE')){
                            ${'_GET'}['url_name_'.$idLang] = Tools::str2url($p->{$itemField}[$idLang]);
                        }
                        $p->save();
                    }
                    else{
                        $p = &$itemUpdate[$itemId];
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }
                        ${'_GET'}[$itemField.'_'.$idLang] = $p->{$itemField}[$idLang];
                        if($itemField == 'value' && (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE')){
                            ${'_GET'}['url_name_'.$idLang] = Tools::str2url($p->{$itemField}[$idLang]);
                        }
                        if(isset($keyTrans[$key+1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if($keySplit2[0] != $itemId){
                                $p->save();
                            }
                        }
                        else{
                            $p->save();
                        }

                    }
                }

                unset($itemUpdate);
                EtsTransLog::logTranslate('attribute', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);

            }
            else{
                EtsTransLog::logTranslate('attribute', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);

                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }

        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateFeature($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $features = array();
        foreach ($ids as $id)
        {
            $featureItem = new Feature((int)$id);
            $agls = Db::getInstance()->executeS("SELECT `meta_title`, `id_lang` FROM `"._DB_PREFIX_."layered_indexable_feature_lang_value` WHERE id_feature=".(int)$id);

            $featureItem->meta_title = array();
            if($agls){
                foreach ($agls as $agl){
                    $featureItem->meta_title[$agl['id_lang']] = $agl['meta_title'];
                }
            }
            else{
                foreach (Language::getLanguages(false) as $lang){
                    $featureItem->meta_title[$lang['id_lang']] = '';
                }
            }
            $features[] = $featureItem;
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = array('name', 'meta_title');
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($features,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'feature');

            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];
                    if($itemField == 'meta_title'){
                        self::createFeatureIndexable($itemId);
                        Db::getInstance()->execute("UPDATE "._DB_PREFIX_."layered_indexable_feature_lang_value SET meta_title='".pSQL($translatedText[$key])."' WHERE id_feature=".(int)$itemId." AND id_lang=".(int)$idLang);
                        continue;
                    }
                    if(!isset($itemUpdate[$itemId])){
                        $p = new Feature($itemId);
                        $itemUpdate[$itemId] = $p;
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }
                        if($itemField == 'name' && (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE')){
                            Db::getInstance()->execute("UPDATE "._DB_PREFIX_."layered_indexable_feature_lang_value SET url_name='".pSQL(Tools::str2url($p->{$itemField}[$idLang]))."' WHERE id_feature=".(int)$itemId." AND id_lang=".(int)$idLang);
                        }
                        $p->save();
                    }
                    else{
                        $p = &$itemUpdate[$itemId];
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }

                        if(isset($keyTrans[$key+1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if($keySplit2[0] != $itemId){
                                $p->save();
                            }
                        }
                        else{
                            $p->save();
                        }
                    }
                }
                unset($itemUpdate);
                EtsTransLog::logTranslate('feature', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('feature', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function translateFeatureValue($ids, $sourceLang, $targetLang, $transOption)
    {
        if(!$ids){
            return false;
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $features = array();
        foreach ($ids as $id)
        {
            $featureItem = new FeatureValue((int)$id);
            $agls = Db::getInstance()->executeS("SELECT `meta_title`, `id_lang` FROM `"._DB_PREFIX_."layered_indexable_feature_value_lang_value` WHERE id_feature_value=".(int)$id);

            $featureItem->meta_title = array();
            if($agls){
                foreach ($agls as $agl){
                    $featureItem->meta_title[$agl['id_lang']] = $agl['meta_title'];
                }
            }
            else{
                foreach (Language::getLanguages(false) as $lang){
                    $featureItem->meta_title[$lang['id_lang']] = '';
                }
            }
            $features[] = $featureItem;
        }
        $sourceIsoCode = Language::getIsoById((int)$sourceLang);
        $api = EtsTransApi::getInstance();
        $transFields = array('value', 'meta_title');
        $strTranslatedLength = 0;
        $nbTextTranslated = 0;
        foreach ($targetLang as $idLang)
        {
            $timStartTrans = microtime(true);
            $textTransFormat = self::getTextTrans($features,$transFields,$idLang,$sourceLang,$transOption);
            $textTrans = $textTransFormat['textTrans'];
            $keyTrans = $textTransFormat['keyTrans'];
            if(!$nbTextTranslated){
                $nbTextTranslated = count($textTrans);
            }
            $res = $api->translate($sourceIsoCode, Language::getIsoById($idLang), $textTrans, 'feature_value');

            if(!$res['errors'] && $res['data']){
                $translatedText = $res['data'];
                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $itemUpdate = array();
                foreach ($keyTrans as $key => $item){
                    $keySplit = explode('.', $item);
                    if(count($keySplit) !== 2){
                        continue;
                    }
                    $itemId = $keySplit[0];
                    $itemField = $keySplit[1];

                    if(!isset($itemUpdate[$itemId]) && ($featureLangData = self::getFeatureValueLang($itemId))){
                        foreach ($featureLangData as $itemLangData)
                        {
                            foreach ($itemLangData as $kid=>$vid){
                                if($kid !== 'id_lang')
                                    ${'_GET'}[$kid.'_'.$itemLangData['id_lang']] = $vid;
                            }
                        }
                    }
                    if($itemField == 'meta_title'){
                        ${'_GET'}['meta_title_'.(int)$idLang] = $translatedText[$key];
                        $p = new FeatureValue($itemId);
                        $p->save();
                        continue;
                    }
                    if(!isset($itemUpdate[$itemId])){
                        $p = new FeatureValue($itemId);
                        $itemUpdate[$itemId] = $p;
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }
                        ${'_GET'}[$itemField.'_'.$idLang] = $p->{$itemField}[$idLang];
                        if($itemField == 'value' && (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE')){
                            ${'_GET'}['url_name_'.$idLang] = Tools::str2url($p->{$itemField}[$idLang]);
                        }
                        $p->save();
                    }
                    else{
                        $p = &$itemUpdate[$itemId];
                        if(strpos($itemField, 'keywords') !== false){
                            $p->{$itemField}[$idLang] = str_replace('|', ',', $translatedText[$key]);
                        }
                        else{
                            $p->{$itemField}[$idLang] = $translatedText[$key];
                        }
                        ${'_GET'}[$itemField.'_'.$idLang] = $p->{$itemField}[$idLang];
                        if($itemField == 'value' && (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE')){
                            ${'_GET'}['url_name_'.$idLang] = Tools::str2url($p->{$itemField}[$idLang]);
                        }
                        if(isset($keyTrans[$key+1])) {
                            $keySplit2 = explode('.', $keyTrans[$key + 1]);
                            if($keySplit2[0] != $itemId){
                                $p->save();
                            }
                        }
                        else{
                            $p->save();
                        }
                    }
                }
                unset($itemUpdate);
                EtsTransLog::logTranslate('feature_value', true, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang);
            }
            else{
                EtsTransLog::logTranslate('feature_value', false, null, EtsTransModule::getLogIdTrans($keyTrans, '.'),null, $timStartTrans, $sourceLang, $idLang, isset($res['message']) ? $res['message'] : null);

                $strTranslatedLength+= self::countTextTranslated($textTrans);
                $res['translated_length'] = $strTranslatedLength;
                return $res;
            }
        }
        return array(
            'translated_length' => $strTranslatedLength,
            'nb_text' => $nbTextTranslated,
        );
    }

    public static function getTextTrans($objects, $transFields, $idLang, $sourceLang, $transOption, $pageType = null){
        $textTrans = array();
        $keyTrans = array();
        foreach ($objects as $obj)
        {
            switch ($transOption){
                case 'both':
                    foreach ($transFields as $field) {
                        
                        if(!isset($obj->{$field}[$sourceLang])){
                            continue;
                        }
                        if($pageType == 'ps_linklist' && is_array($obj->{$field}[$sourceLang]) && is_array($obj->{$field}[$idLang])){
                            foreach ($obj->{$field}[$sourceLang] as $subkey=>$subtext){
                                if(!$subtext || !is_array($subtext)){
                                    continue;
                                }
                                foreach ($subtext as $bk=>$bv){
                                    if(!isset($obj->{$field}[$idLang][$subkey][$bk]) || !trim($obj->{$field}[$idLang][$subkey][$bk]) || trim($obj->{$field}[$idLang][$subkey][$bk]) == trim($bv)){
                                        $textTrans[] = $bv;
                                        $keyTrans[] = $obj->id.'.'.$field.'.'.$subkey.'.'.$bk;
                                    }
                                }
                            }
                        }
                        elseif(!trim($obj->{$field}[$idLang]) || trim($obj->{$field}[$sourceLang]) == trim($obj->{$field}[$idLang])){
                            if(strpos($field, 'keywords') !== false){
                                $textTrans[] = str_replace(',', '|', $obj->{$field}[$sourceLang]);
                            }
                            else{
                                $textTrans[] = $obj->{$field}[$sourceLang];
                            }
                            $keyTrans[] = $obj->id.'.'.$field;
                        }
                    }
                    break;
                case 'only_empty':
                    foreach ($transFields as $field) {
                        if(!$obj->{$field}[$sourceLang]){
                            continue;
                        }
                        if($pageType == 'ps_linklist' && is_array($obj->{$field}[$sourceLang]) && is_array($obj->{$field}[$idLang])){
                            foreach ($obj->{$field}[$sourceLang] as $subkey=>$subtext){
                                if(!$subtext || !is_array($subtext)){
                                    continue;
                                }
                                foreach ($subtext as $bk=>$bv){
                                    if(!isset($obj->{$field}[$idLang][$subkey][$bk]) || !trim($obj->{$field}[$idLang][$subkey][$bk])){
                                        $textTrans[] = $bv;
                                        $keyTrans[] = $obj->id.'.'.$field.'.'.$subkey.'.'.$bk;
                                    }
                                }
                            }
                        }
                        elseif(!trim($obj->{$field}[$idLang])){
                            if(strpos($field, 'keywords') !== false){
                                $textTrans[] = str_replace(',', '|', $obj->{$field}[$sourceLang]);
                            }
                            else{
                                $textTrans[] = $obj->{$field}[$sourceLang];
                            }
                            $keyTrans[] = $obj->id.'.'.$field;
                        }
                    }
                    break;
                case 'same_source':
                    foreach ($transFields as $field) {
                        if(!$obj->{$field}[$sourceLang]){
                            continue;
                        }
                        if($pageType == 'ps_linklist' && is_array($obj->{$field}[$sourceLang]) && is_array($obj->{$field}[$idLang])){
                            foreach ($obj->{$field}[$sourceLang] as $subkey=>$subtext){
                                if(!$subtext || !is_array($subtext)){
                                    continue;
                                }
                                foreach ($subtext as $bk=>$bv){
                                    if(isset($obj->{$field}[$idLang][$subkey][$bk]) && trim($obj->{$field}[$idLang][$subkey][$bk]) == trim($bv)){
                                        $textTrans[] = $bv;
                                        $keyTrans[] = $obj->id.'.'.$field.'.'.$subkey.'.'.$bk;
                                    }
                                }
                            }
                        }
                        elseif(trim($obj->{$field}[$sourceLang]) == trim($obj->{$field}[$idLang])){
                            if(strpos($field, 'keywords') !== false){
                                $textTrans[] = str_replace(',', '|', $obj->{$field}[$sourceLang]);
                            }
                            else{
                                $textTrans[] = $obj->{$field}[$sourceLang];
                            }
                            $keyTrans[] = $obj->id.'.'.$field;
                        }
                    }
                    break;
                default:
                    foreach ($transFields as $field) {
                        if(!isset($obj->{$field}[$sourceLang]) || !$obj->{$field}[$sourceLang]){
                            continue;
                        }
                        if($pageType == 'ps_linklist' && is_array($obj->{$field}[$sourceLang]) && is_array($obj->{$field}[$idLang])){
                            foreach ($obj->{$field}[$sourceLang] as $subkey=>$subtext){
                                if(!$subtext || !is_array($subtext)){
                                    continue;
                                }
                                foreach ($subtext as $bk=>$bv){
                                    $textTrans[] = $bv;
                                    $keyTrans[] = $obj->id.'.'.$field.'.'.$subkey.'.'.$bk;
                                }
                            }
                        }
                        else{
                            if(strpos($field, 'keywords') !== false){
                                $textTrans[] = str_replace(',', '|', $obj->{$field}[$sourceLang]);
                            }
                            else{
                                $textTrans[] = $obj->{$field}[$sourceLang];
                            }
                            $keyTrans[] = $obj->id.'.'.$field;
                        }
                    }
                    break;
            }
        }

        return array(
            'textTrans' => $textTrans,
            'keyTrans' => $keyTrans,
        );
    }

    public static function translatePage($type,$formData)
    {
        $limit = 2;
        $ids = $formData['page_id'];
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        $idsTrans = array_splice($ids, 0,$limit);
        $nbTrans = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] + count($idsTrans) : count($idsTrans);
        switch ($type){
            case 'product':
                $result = self::transProduct($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'category':
                $result = self::translateCategory($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'cms':
                $result = self::translateCMS($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'cms_category':
                $result = self::translateCMSCategory($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'manufacturer':
                $result = self::translateManufacturer($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'supplier':
                $result = self::translateSupplier($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'attribute_group':
                $result = self::translateAttributeGroup($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'attribute':
                $result = self::translateAttribute($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'feature':
                $result = self::translateFeature($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'feature_value':
                $result = self::translateFeatureValue($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'blockreassurance':
                $result = self::translateBlockReassurance($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'ps_linklist':
                $result = self::translateLinkList($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;
            case 'ps_mainmenu':
                $result = self::translateMainMenu($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
            case 'ps_imageslider':
                $result = self::translateImageSliders($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
            case 'ets_extraproducttabs':
                $result = self::translateExtraProductTabs($idsTrans, $formData['trans_source'], $formData['trans_target'], $formData['trans_option']);
                break;

        }

        if(isset($result) && $result){
            if(!is_array($result)){
                $result = array();
            }
            $result['page_id'] = implode(',', $ids);
            $result['ids_translated'] = $idsTrans;
            $result['nb_translated'] = $nbTrans;
            $result['stop_translate'] = $ids ? false : true;
            if(isset($result['errors']) && $result['errors']){
                $result['stop_translate'] = true;
            }
            return $result;
        }
        return array();
    }

    public static function slugify($text)
    {
       return Tools::str2url($text);
    }

    public static function countTextTranslated($arrText)
    {
        $count = 0;
        foreach ($arrText as $item){
            $count += Tools::strlen($item);
        }
        return $count;
    }

    public static function analysisTranslate($pageType, $formData, $offset=0)
    {
        if(!isset($formData['trans_source']) || !isset($formData['trans_target']) || !isset($formData['trans_option'])){
            return false;
        }
        $limit = 2;
        $langSource = $formData['trans_source'];
        $langTarget = $formData['trans_target'];
        $transOption = $formData['trans_option'];
        $result = array(
            'nb_text' => 0,
            'nb_char' => 0,
            'nb_money' => 0,
            'stop' => 0,
            'offset' => $offset + $limit,
        );
        $context = Context::getContext();
        switch ($pageType){
            case 'product':
                $transFields = ['name', 'description_short', 'description', 'available_now', 'available_later', 'delivery_in_stock', 'delivery_out_stock','meta_title', 'meta_description'];
                $idItems = Db::getInstance()->executeS("SELECT `id_product` FROM `"._DB_PREFIX_."product_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_product';
                $objClass = 'Product';
                break;
            case 'category':
                $transFields = ['name', 'description', 'meta_title', 'meta_description', 'meta_keywords'];
                $idItems = Db::getInstance()->executeS("SELECT `id_category` FROM `"._DB_PREFIX_."category_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_category';
                $objClass = 'Category';
                break;
            case 'cms':
                $transFields = ['meta_title', 'head_seo_title', 'meta_description', 'meta_keywords', 'content'];
                $idItems = Db::getInstance()->executeS("SELECT `id_cms` FROM `"._DB_PREFIX_."cms_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_cms';
                $objClass = 'CMS';
                break;
            case 'cms_category':
                $transFields = ['name', 'meta_title', 'description', 'meta_description', 'meta_keywords'];
                $idItems = Db::getInstance()->executeS("SELECT `id_cms_category` FROM `"._DB_PREFIX_."cms_category_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_cms_category';
                $objClass = 'CMSCategory';
                break;
            case 'manufacturer':
                $transFields = ['description', 'short_description', 'meta_title', 'meta_description', 'meta_keywords'];
                $idItems = Db::getInstance()->executeS("SELECT `id_manufacturer` FROM `"._DB_PREFIX_."manufacturer_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_manufacturer';
                $objClass = 'Manufacturer';
                break;
            case 'supplier':
                $transFields = ['description', 'meta_title', 'meta_description', 'meta_keywords'];
                $idItems = Db::getInstance()->executeS("SELECT `id_supplier` FROM `"._DB_PREFIX_."supplier_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_supplier';
                $objClass = 'Supplier';
            case 'attribute_group':
                $transFields = ['name', 'public_name', 'meta_title'];
                $idItems = Db::getInstance()->executeS("SELECT `id_attribute_group` FROM `"._DB_PREFIX_."attribute_group_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_attribute_group';
                $objClass = 'AttributeGroup';
                break;
            case 'attribute':
                $transFields = ['name', 'meta_title'];
                $idItems = Db::getInstance()->executeS("
                    SELECT a.id_attribute FROM `"._DB_PREFIX_."attribute_shop` attrs 
                    LEFT JOIN "._DB_PREFIX_."attribute a ON attrs.id_attribute=a.id_attribute 
                    WHERE attrs.`id_shop`=".(int)$context->shop->id.(($idAttributeGroup = Tools::getValue('idAttributeGroup')) ? " AND a.id_attribute_group=".(int)$idAttributeGroup : "")." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_attribute';
                $objClass = 'Attribute';
                break;
            case 'feature':
                $transFields = ['name', 'meta_title'];
                $idItems = Db::getInstance()->executeS("SELECT `id_feature` FROM `"._DB_PREFIX_."feature_shop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_feature';
                $objClass = 'Feature';
                break;
            case 'feature_value':
                $transFields = ['value', 'meta_title'];
                $idItems = Db::getInstance()->executeS("
                    SELECT fv.id_feature_value FROM `"._DB_PREFIX_."feature_value` fv 
                    LEFT JOIN "._DB_PREFIX_."feature_shop fs ON fv.id_feature=fs.id_feature WHERE fs.`id_shop`=".(int)$context->shop->id.(($idFeature = Tools::getValue('idFeature')) ? " AND fv.id_feature=".(int)$idFeature : "")." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_feature_value';
                $objClass = 'FeatureValue';
            case 'blockreassurance':
                $moduleObj = Module::getInstanceByName('blockreassurance');
                if(Tools::getValue('isNewBlockreassurance') || ($moduleObj && version_compare('4.0.0', $moduleObj->version, '<='))){
                    $idItems = Db::getInstance()->executeS("SELECT `id_psreassurance` FROM `"._DB_PREFIX_."psreassurance` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                    $idField = 'id_psreassurance';
                    $transFields = ['title', 'description'];
                }
                else{
                    $idItems = Db::getInstance()->executeS("SELECT `id_reassurance` FROM `"._DB_PREFIX_."reassurance` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                    $idField = 'id_reassurance';
                    $transFields = ['text'];
                }
                $objClass = '';
                break;
            case 'ps_linklist':
                $transFields = ['name', 'custom_content'];
                $idItems = Db::getInstance()->executeS("SELECT `id_link_block` FROM `"._DB_PREFIX_."link_block` LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_link_block';
                $objClass = '';
                break;
            case 'ps_mainmenu':
                $transFields = ['label'];
                $idItems = Db::getInstance()->executeS("SELECT `id_linksmenutop` FROM `"._DB_PREFIX_."linksmenutop` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_linksmenutop';
                $objClass = '';
                break;
            case 'ps_imageslider':
                $transFields = ['title', 'description', 'legend'];
                $idItems = Db::getInstance()->executeS("SELECT `id_homeslider_slides` FROM `"._DB_PREFIX_."homeslider` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_homeslider_slides';
                $objClass = '';
                break;
            case 'ets_extraproducttabs':
                $transFields = ['name', 'content'];
                $idItems = Db::getInstance()->executeS("SELECT `id_ets_ept_tab` FROM `"._DB_PREFIX_."ets_ept_tab` WHERE `id_shop`=".(int)$context->shop->id." LIMIT ".(int)$offset.", ".(int)$limit);
                $idField = 'id_ets_ept_tab';
                $objClass = '';
                break;
        }

        if(!isset($idItems)){
            $result['stop'] = 1;
            return $result;
        }
        $ids = array();
        foreach ($idItems as $item){
            $ids[] = $item[$idField];
        }
        $objs = array();
        if($pageType == 'blockreassurance'){
            $moduleObj = Module::getInstanceByName('blockreassurance');
            $blockReassuranceObject = self::getReassuranceObject($ids, (int)Tools::getValue('isNewBlockreassurance') || ($moduleObj && version_compare('4.0.0', $moduleObj->version, '<=')));
        }
        elseif($pageType == 'ps_linklist'){
            $linkListObject = self::getLinkListBlockObject($ids);
        }
        elseif($pageType == 'ps_mainmenu'){
            $mainMenuObject = self::getMainMenuObject($ids);
        }
        elseif($pageType == 'ps_imageslider'){
            $imageSliderObject = self::getImageSliderObjects($ids);
        }
        elseif($pageType == 'ets_extraproducttabs'){
            $extraTabsObject = self::getExtraProductTabObjects($ids);
        }
        foreach ($ids as $i){
            if($pageType == 'ps_linklist'){
                $itemObj = $linkListObject[$i];
            }
            elseif($pageType == 'blockreassurance'){
                $itemObj = $blockReassuranceObject[$i];
            }
            elseif($pageType == 'ps_mainmenu'){
                $itemObj = $mainMenuObject[$i];
            }
            elseif($pageType == 'ps_imageslider'){
                $itemObj = $imageSliderObject[$i];
            }
            elseif($pageType == 'ets_extraproducttabs'){
                $itemObj = $extraTabsObject[$i];
            }
            else{
                $itemObj = new $objClass($i);
            }
            if($pageType == 'attribute_group' || $pageType =='attribute'){
                if($pageType == 'attribute_group')
                    $agls = Db::getInstance()->executeS("SELECT `meta_title`, `id_lang` FROM `"._DB_PREFIX_."layered_indexable_attribute_group_lang_value` WHERE id_attribute_group=".(int)$i);
                else
                    $agls = Db::getInstance()->executeS("SELECT `meta_title`, `id_lang` FROM `"._DB_PREFIX_."layered_indexable_attribute_lang_value` WHERE id_attribute=".(int)$i);
                $itemObj->meta_title = array();
                if($agls){
                    foreach ($agls as $agl){
                        $itemObj->meta_title[$agl['id_lang']] = $agl['meta_title'];
                    }
                }
                else{
                    foreach (Language::getLanguages(false) as $lang){
                        $itemObj->meta_title[$lang['id_lang']] = '';
                    }
                }

            }
            $objs[] = $itemObj;
        }
        foreach ($langTarget as $idLang){
            $dataTrans = self::getTextTrans($objs, $transFields, $idLang, $langSource, $transOption, $pageType);
            $textTrans = $dataTrans['textTrans'];
            $result['nb_text'] = count($textTrans);
            $result['nb_char'] += self::countTextTranslated($textTrans);
        }
        $api = EtsTransApi::getInstance();
        $result['nb_money'] = $api->getTotalFeeTranslate($result['nb_char']);
        if(count($ids) < $limit){
            $result['stop'] = 1;
        }
        if(!$result['nb_char']){
            $result['nb_text'] = 0;
        }
        return $result;
    }


    public static function getAttributeLangData($idAttribute, $id_shop = null)
    {
        if(!$id_shop){
            $id_shop = Context::getContext()->shop->id;
        }

        return Db::getInstance()->executeS("
            SELECT al.name, lial.url_name, lial.meta_title, al.id_lang FROM "._DB_PREFIX_."attribute_lang al 
            LEFT JOIN "._DB_PREFIX_."layered_indexable_attribute_lang_value lial ON al.id_attribute=lial.id_attribute AND al.id_lang=lial.id_lang 
            JOIN "._DB_PREFIX_."attribute_shop attrs ON al.id_attribute=attrs.id_attribute AND attrs.id_shop=".(int)$id_shop."
            WHERE al.id_attribute=".(int)$idAttribute);
    }

    public static function getFeatureValueLang($idFeatureValue, $id_shop=null)
    {
        if(!$id_shop){
            $id_shop = Context::getContext()->shop->id;
        }
        return Db::getInstance()->executeS("
            SELECT fvl.value, lifvl.url_name, lifvl.meta_title, fvl.id_lang FROM "._DB_PREFIX_."feature_value_lang fvl 
            LEFT JOIN "._DB_PREFIX_."layered_indexable_feature_value_lang_value lifvl ON fvl.id_feature_value = lifvl.id_feature_value AND fvl.id_lang=lifvl.id_lang 
            JOIN "._DB_PREFIX_."feature_value fv ON fvl.id_feature_value=fv.id_feature_value 
            JOIN "._DB_PREFIX_."feature_shop fs ON fv.id_feature=fs.id_feature AND fs.id_shop=".(int)$id_shop."
            WHERE fvl.id_feature_value=".(int)$idFeatureValue);
    }

    public static function createAttributeGroupIndexable($idAttributeGroup)
    {
        foreach(Language::getLanguages(true) as $lang){
            if(!Db::getInstance()->getRow("SELECT * FROM `"._DB_PREFIX_."layered_indexable_attribute_group_lang_value` WHERE id_attribute_group=".(int)$idAttributeGroup." AND id_lang=".(int)$lang['id_lang'])){
                Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."layered_indexable_attribute_group_lang_value (id_attribute_group, id_lang,url_name, meta_title) VALUES(".(int)$idAttributeGroup.",".(int)$lang['id_lang'].", '', '')");
            }
        }
    }
    public static function createFeatureIndexable($idFeature)
    {
        foreach(Language::getLanguages(true) as $lang){
            if(!Db::getInstance()->getRow("SELECT * FROM `"._DB_PREFIX_."layered_indexable_feature_lang_value` WHERE id_feature=".(int)$idFeature." AND id_lang=".(int)$lang['id_lang'])){
                Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."layered_indexable_feature_lang_value (id_feature, id_lang,url_name, meta_title) VALUES(".(int)$idFeature.",".(int)$lang['id_lang'].", '', '')");
            }
        }
    }
}