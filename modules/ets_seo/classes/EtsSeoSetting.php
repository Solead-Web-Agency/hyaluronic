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

if (!defined('_PS_VERSION_'))
{
    exit;
}

class EtsSeoSetting
{
    public $isNewTheme = false;
    public static $instance;
    public $context = null;
    public function __construct($context = null)
    {
        if(!$this->context){
            $this->context = $context ? $context : Context::getContext();
        }
        $this->isNewTheme = $this->getRequestContainer() ? true : false;
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new EtsSeoSetting();
        }
        return self::$instance;
    }

    public function updateSeoCms($params)
    {

        if (
            (!$this->isNewTheme && Tools::getValue('controller', '') == 'AdminCmsContent') ||
            ($this->isNewTheme && isset($params['object']) && $params['object'] instanceof CMS)
        ) {
            $cms = $params['object'];
            if ($cms instanceof CMS) {
                $id_cms = $cms->id;
                $key_phrase = Tools::getValue('ets_seo_key_phrase', array());
                $minor_key_phrase = Tools::getValue('ets_seo_minor_keyphrase', array());
                $social_title = Tools::getValue('ets_seo_social_title', array());
                $social_desc = Tools::getValue('ets_seo_social_desc', array());
                $social_img = Tools::getValue('ets_seo_social_img', array());

                if ($key_phrase) {
                    foreach ($key_phrase as $id_lang => $value) {

                        $id_ets_seo_cms = Db::getInstance()->getValue("SELECT id_ets_seo_cms 
                                                        FROM `" . _DB_PREFIX_ . "ets_seo_cms` WHERE `id_cms` = " . (int)$id_cms . " 
                                                        AND id_shop = " . (int)$this->context->shop->id . " 
                                                        AND id_lang = " . (int)$id_lang);

                        if ((int)$id_ets_seo_cms) {
                            $seoCms = new EtsSeoCms($id_ets_seo_cms);
                        } else {
                            $seoCms = new EtsSeoCms();
                        }

                        $seoCms->id_cms = $id_cms;
                        $seoCms->id_shop = $this->context->shop->id;
                        $seoCms->id_lang = $id_lang;
                        $seoCms->key_phrase = $value;
                        $seoCms->minor_key_phrase = isset($minor_key_phrase[$id_lang]) ? $this->getMinorKeyphrase($minor_key_phrase[$id_lang]) : '';
                        $seoCms->social_title = isset($social_title[$id_lang]) ? $social_title[$id_lang] : null;
                        $seoCms->social_desc = isset($social_desc[$id_lang]) ? $social_desc[$id_lang] : null;
                        $seoCms->social_img = isset($social_img[$id_lang]) ? $this->getSocialImage($social_img[$id_lang]) : null;

                        if ($advanced = Tools::getValue('ets_seo_advanced', array())) {

                            foreach ($advanced as $key => $val) {
                                if (isset($val[$id_lang])) {
                                    if (is_array($val[$id_lang])) {
                                        $val[$id_lang] = implode(',', $val[$id_lang]);
                                    }
                                    $seoCms->{$key} = $val[$id_lang];
                                }

                            }
                        }

                        $seoCms->save();
                    }

                    //Add score
                    if ($score_data = Tools::getValue('ets_seo_score_data')) {
                        $scoreArray = Tools::jsonDecode($score_data, true);
                        if (isset($scoreArray['seo_score']) && isset($scoreArray['readability_score'])) {
                            $contentAnalysis = Tools::getValue('ets_seo_content_analysis');
                            $contentAnalysis = $contentAnalysis ? Tools::jsonDecode($contentAnalysis, true) : array();

                            $this->saveScore('AdminCmsContent', $id_cms, $scoreArray['seo_score'], $scoreArray['readability_score'], $contentAnalysis);
                        }
                    }
                }

                $this->updateSeoRating('cms', $id_cms);

            }
        }
    }

    public function updateSeoMeta($params)
    {
        if (
            (!$this->isNewTheme && Tools::getValue('controller', '') == 'AdminMeta') ||
            ($this->isNewTheme && isset($params['object']) && $params['object'] instanceof Meta)
        ) {
            $metaObj = $params['object'];
            if ($metaObj instanceof Meta) {

                $id_meta = $metaObj->id;
                $key_phrase = Tools::getValue('ets_seo_key_phrase', array());
                $minor_key_phrase = Tools::getValue('ets_seo_minor_keyphrase', array());
                $social_title = Tools::getValue('ets_seo_social_title', array());
                $social_desc = Tools::getValue('ets_seo_social_desc', array());
                $social_img = Tools::getValue('ets_seo_social_img', array());

                if ($key_phrase) {

                    foreach ($key_phrase as $id_lang => $value) {
                        $id_ets_seo_meta = Db::getInstance()->getValue("SELECT id_ets_seo_meta 
                                                        FROM `" . _DB_PREFIX_ . "ets_seo_meta` 
                                                        WHERE `id_meta` = " . (int)$id_meta . " 
                                                            AND id_shop = " . (int)$this->context->shop->id . " 
                                                            AND id_lang = " . (int)$id_lang);

                        $advanced = Tools::getValue('ets_seo_advanced', array());

                        if ((int)$id_ets_seo_meta) {
                            $sql = "UPDATE `" . _DB_PREFIX_ . "ets_seo_meta` SET `key_phrase` = '" . pSQL($value) . "', `minor_key_phrase` = '".pSQL($this->getMinorKeyphrase($minor_key_phrase[$id_lang]))."'";
                            foreach ($advanced as $key => $val) {
                                if (isset($val[$id_lang])) {
                                    $sql .= ",`" . (string)$key . "` = '" . pSQL($val[$id_lang]) . "'";
                                }
                                $sql .= ",`social_title`='" . (isset($social_title[$id_lang]) ? $social_title[$id_lang] : '') . "'";
                                $sql .= ",`social_desc`='" . (isset($social_desc[$id_lang]) ? $social_desc[$id_lang] : '') . "'";
                                $sql .= ",`social_img`='" . (isset($social_img[$id_lang]) ? $social_img[$id_lang] : '') . "'";
                            }
                            $sql .= " WHERE `id_meta` = " . (int)$id_meta . " 
                                    AND id_shop = " . (int)$this->context->shop->id . " 
                                    AND id_lang = " . (int)$id_lang;

                            Db::getInstance()->execute($sql);
                        } else {
                            $meta = new EtsSeoMeta();
                            $meta->key_phrase = $value;
                            $meta->minor_key_phrase = $this->getMinorKeyphrase($minor_key_phrase[$id_lang]);
                            $meta->id_meta = $id_meta;
                            $meta->id_shop = $this->context->shop->id;
                            $meta->id_lang = $id_lang;
                            $meta->social_title = isset($social_title[$id_lang]) ? $social_title[$id_lang] : null;
                            $meta->social_desc = isset($social_desc[$id_lang]) ? $social_desc[$id_lang] : null;
                            $meta->social_img = isset($social_img[$id_lang]) ? $this->getSocialImage($social_img[$id_lang]) : null;
                            foreach ($advanced as $key => $val) {
                                if (isset($val[$id_lang])) {
                                    if (is_array($val[$id_lang])) {
                                        $val[$id_lang] = implode(',', $val[$id_lang]);
                                    }
                                    $meta->{$key} = $val[$id_lang];
                                }
                            }
                            $meta->add();
                        }

                    }

                    //Add score
                    if ($score_data = Tools::getValue('ets_seo_score_data')) {
                        $scoreArray = Tools::jsonDecode($score_data, true);
                        if (isset($scoreArray['seo_score']) && isset($scoreArray['readability_score'])) {
                            $contentAnalysis = Tools::getValue('ets_seo_content_analysis');
                            $contentAnalysis = $contentAnalysis ? Tools::jsonDecode($contentAnalysis, true) : array();
                            $this->saveScore('AdminMeta', $id_meta, $scoreArray['seo_score'], $scoreArray['readability_score'], $contentAnalysis);
                        }
                    }
                }
                $this->updateSeoRating('meta', $id_meta);
            }
        }
    }

    public function updateSeoCategory($params)
    {
        if (
            (!$this->isNewTheme && Tools::getValue('controller', '') == 'AdminCategories') ||
            ($this->isNewTheme && isset($params['object']) && $params['object'] instanceof Category)
        ) {
            $category = $params['object'];
            if ($category instanceof Category) {

                $id_category = $category->id;
                $key_phrase = Tools::getValue('ets_seo_key_phrase', array());
                $minor_key_phrase = Tools::getValue('ets_seo_minor_keyphrase', array());
                $social_title = Tools::getValue('ets_seo_social_title', array());
                $social_desc = Tools::getValue('ets_seo_social_desc', array());
                $social_img = Tools::getValue('ets_seo_social_img', array());

                if ($key_phrase) {
                    foreach ($key_phrase as $id_lang => $value) {

                        $id_ets_seo_category = Db::getInstance()->getValue("SELECT id_ets_seo_category 
                                                        FROM `" . _DB_PREFIX_ . "ets_seo_category` WHERE `id_category` = " . (int)$id_category . " 
                                                        AND id_shop = " . (int)$this->context->shop->id . " 
                                                        AND id_lang = " . (int)$id_lang);
                        if ((int)$id_ets_seo_category) {
                            $seoCategory = new EtsSeoCategory($id_ets_seo_category);
                        } else {
                            $seoCategory = new EtsSeoCategory();
                        }
                        $seoCategory->id_category = $id_category;
                        $seoCategory->id_shop = $this->context->shop->id;
                        $seoCategory->id_lang = $id_lang;
                        $seoCategory->key_phrase = $value;
                        $seoCategory->minor_key_phrase = $this->getMinorKeyphrase($minor_key_phrase[$id_lang]);
                        $seoCategory->social_title = isset($social_title[$id_lang]) ? $social_title[$id_lang] : '';
                        $seoCategory->social_desc = isset($social_desc[$id_lang]) ? $social_desc[$id_lang] : '';
                        $seoCategory->social_img = isset($social_img[$id_lang]) ? $this->getSocialImage($social_img[$id_lang]) : '';

                        if ($advanced = Tools::getValue('ets_seo_advanced', array())) {
                            foreach ($advanced as $key => $val) {
                                if (isset($val[$id_lang])) {
                                    if (is_array($val[$id_lang])) {
                                        $val[$id_lang] = implode(',', $val[$id_lang]);
                                    }
                                    $seoCategory->{$key} = $val[$id_lang];
                                }
                            }
                        }
                        $seoCategory->save();
                    }

                    //Add score
                    if ($score_data = Tools::getValue('ets_seo_score_data')) {
                        $scoreArray = Tools::jsonDecode($score_data, true);
                        if (isset($scoreArray['seo_score']) && isset($scoreArray['readability_score'])) {
                            $contentAnalysis = Tools::getValue('ets_seo_content_analysis');
                            $contentAnalysis = $contentAnalysis ? Tools::jsonDecode($contentAnalysis, true) : array();
                            $this->saveScore('AdminCategories', $id_category, $scoreArray['seo_score'], $scoreArray['readability_score'], $contentAnalysis);
                        }
                    }
                }
                $this->updateSeoRating('category', $id_category);
            }
        }
    }

    public function updateSeoManufacturer($params)
    {
        if (
            (!$this->isNewTheme && Tools::getValue('controller', '') == 'AdminManufacturers') ||
            ($this->isNewTheme && isset($params['object']) && $params['object'] instanceof Manufacturer)
        ) {
            $manufacturer = $params['object'];
            if ($manufacturer instanceof Manufacturer) {

                $id_manufacturer = $manufacturer->id;
                $key_phrase = Tools::getValue('ets_seo_key_phrase', array());
                $minor_key_phrase = Tools::getValue('ets_seo_minor_keyphrase', array());
                $social_title = Tools::getValue('ets_seo_social_title', array());
                $social_desc = Tools::getValue('ets_seo_social_desc', array());
                $social_img = Tools::getValue('ets_seo_social_img', array());

                if ($key_phrase) {
                    foreach ($key_phrase as $id_lang => $value) {

                        $id_ets_seo_manufacturer = Db::getInstance()->getValue("SELECT id_ets_seo_manufacturer 
                                                        FROM `" . _DB_PREFIX_ . "ets_seo_manufacturer` WHERE `id_manufacturer` = " . (int)$id_manufacturer . " 
                                                        AND id_shop = " . (int)$this->context->shop->id . " 
                                                        AND id_lang = " . (int)$id_lang);
                        if ((int)$id_ets_seo_manufacturer) {
                            $seoManuf = new EtsSeoManufacturer($id_ets_seo_manufacturer);
                        } else {
                            $seoManuf = new EtsSeoManufacturer();
                        }
                        $seoManuf->id_manufacturer = $id_manufacturer;
                        $seoManuf->id_shop = $this->context->shop->id;
                        $seoManuf->id_lang = $id_lang;
                        $seoManuf->key_phrase = $value;
                        $seoManuf->minor_key_phrase = $this->getMinorKeyphrase($minor_key_phrase[$id_lang]);
                        $seoManuf->social_title = isset($social_title[$id_lang]) ? $social_title[$id_lang] : '';
                        $seoManuf->social_desc = isset($social_desc[$id_lang]) ? $social_desc[$id_lang] : '';
                        $seoManuf->social_img = isset($social_img[$id_lang]) ? $this->getSocialImage($social_img[$id_lang]) : '';

                        if ($advanced = Tools::getValue('ets_seo_advanced', array())) {
                            foreach ($advanced as $key => $val) {

                                if (isset($val[$id_lang])) {
                                    if (is_array($val[$id_lang])) {
                                        $val[$id_lang] = implode(',', $val[$id_lang]);
                                    }
                                    $seoManuf->{$key} = $val[$id_lang];
                                }
                            }
                        }
                        $seoManuf->save();
                    }

                    //Add score
                    if ($score_data = Tools::getValue('ets_seo_score_data')) {
                        $scoreArray = Tools::jsonDecode($score_data, true);
                        if (isset($scoreArray['seo_score']) && isset($scoreArray['readability_score'])) {
                            $contentAnalysis = Tools::getValue('ets_seo_content_analysis');
                            $contentAnalysis = $contentAnalysis ? Tools::jsonDecode($contentAnalysis, true) : array();
                            $this->saveScore('AdminManufacturers', $id_manufacturer, $scoreArray['seo_score'], $scoreArray['readability_score'], $contentAnalysis);
                        }
                    }
                }
                $this->updateSeoRating('manufacturer', $id_manufacturer);
            }
        }
    }

    public function updateSeoSupplier($params)
    {
        if (
            (!$this->isNewTheme && Tools::getValue('controller', '') == 'AdminSuppliers') ||
            ($this->isNewTheme && isset($params['object']) && $params['object'] instanceof Supplier)
        ) {
            $supplier = $params['object'];
            if ($supplier instanceof Supplier) {

                $id_supplier = $supplier->id;
                $key_phrase = Tools::getValue('ets_seo_key_phrase', array());
                $minor_key_phrase = Tools::getValue('ets_seo_minor_keyphrase', array());
                $social_title = Tools::getValue('ets_seo_social_title', array());
                $social_desc = Tools::getValue('ets_seo_social_desc', array());
                $social_img = Tools::getValue('ets_seo_social_img', array());

                if ($key_phrase) {
                    foreach ($key_phrase as $id_lang => $value) {

                        $id_ets_seo_supplier = Db::getInstance()->getValue("SELECT id_ets_seo_supplier 
                                                        FROM `" . _DB_PREFIX_ . "ets_seo_supplier` WHERE `id_supplier` = " . (int)$id_supplier . " 
                                                        AND id_shop = " . (int)$this->context->shop->id . " 
                                                        AND id_lang = " . (int)$id_lang);
                        if ((int)$id_ets_seo_supplier) {
                            $seoSupplier = new EtsSeoSupplier($id_ets_seo_supplier);
                        } else {
                            $seoSupplier = new EtsSeoSupplier();
                        }
                        $seoSupplier->id_supplier = $id_supplier;
                        $seoSupplier->id_shop = $this->context->shop->id;
                        $seoSupplier->id_lang = $id_lang;
                        $seoSupplier->key_phrase = $value;
                        $seoSupplier->minor_key_phrase = $this->getMinorKeyphrase($minor_key_phrase[$id_lang]);
                        $seoSupplier->social_title = isset($social_title[$id_lang]) ? $social_title[$id_lang] : '';
                        $seoSupplier->social_desc = isset($social_desc[$id_lang]) ? $social_desc[$id_lang] : '';
                        $seoSupplier->social_img = isset($social_img[$id_lang]) ? $this->getSocialImage($social_img[$id_lang]) : '';

                        if ($advanced = Tools::getValue('ets_seo_advanced', array())) {
                            foreach ($advanced as $key => $val) {
                                if (isset($val[$id_lang])) {
                                    if (is_array($val[$id_lang])) {
                                        $val[$id_lang] = implode(',', $val[$id_lang]);
                                    }
                                    $seoSupplier->{$key} = $val[$id_lang];
                                }
                            }
                        }
                        $seoSupplier->save();
                    }

                    //Add score
                    if ($score_data = Tools::getValue('ets_seo_score_data')) {
                        $scoreArray = Tools::jsonDecode($score_data, true);
                        if (isset($scoreArray['seo_score']) && isset($scoreArray['readability_score'])) {
                            $contentAnalysis = Tools::getValue('ets_seo_content_analysis');
                            $contentAnalysis = $contentAnalysis ? Tools::jsonDecode($contentAnalysis, true) : array();
                            $this->saveScore('AdminSuppliers', $id_supplier, $scoreArray['seo_score'], $scoreArray['readability_score'], $contentAnalysis);
                        }
                    }
                }
                $this->updateSeoRating('supplier', $id_supplier);
            }
        }
    }

    public function updateSeoCmsCategory($params)
    {
        if (
            (!$this->isNewTheme && Tools::getValue('controller', '') == 'AdminCmsContent') ||
            ($this->isNewTheme && isset($params['object']) && $params['object'] instanceof CMSCategory)
        ) {

            $cms = $params['object'];
            if ($cms instanceof CMSCategory) {
                $id_cms = $cms->id;
                $key_phrase = Tools::getValue('ets_seo_key_phrase', array());
                $minor_key_phrase = Tools::getValue('ets_seo_minor_keyphrase', array());
                $social_title = Tools::getValue('ets_seo_social_title', array());
                $social_desc = Tools::getValue('ets_seo_social_desc', array());
                $social_img = Tools::getValue('ets_seo_social_img', array());

                if ($key_phrase) {
                    foreach ($key_phrase as $id_lang => $value) {

                        $id_ets_seo_cms = Db::getInstance()->getValue("SELECT id_ets_seo_cms_category 
                                                        FROM `" . _DB_PREFIX_ . "ets_seo_cms_category` WHERE `id_cms_category` = " . (int)$id_cms . " 
                                                        AND id_shop = " . (int)$this->context->shop->id . " 
                                                        AND id_lang = " . (int)$id_lang);

                        if ((int)$id_ets_seo_cms) {
                            $seoCms = new EtsSeoCmsCategory($id_ets_seo_cms);
                        } else {
                            $seoCms = new EtsSeoCmsCategory();
                        }

                        $seoCms->id_cms_category = $id_cms;
                        $seoCms->id_shop = $this->context->shop->id;
                        $seoCms->id_lang = $id_lang;
                        $seoCms->key_phrase = $value;
                        $seoCms->minor_key_phrase =$this->getMinorKeyphrase($minor_key_phrase[$id_lang]);
                        $seoCms->social_title = isset($social_title[$id_lang]) ? $social_title[$id_lang] : null;
                        $seoCms->social_desc = isset($social_desc[$id_lang]) ? $social_desc[$id_lang] : null;
                        $seoCms->social_img = isset($social_img[$id_lang]) ? $this->getSocialImage($social_img[$id_lang]) : null;

                        if ($advanced = Tools::getValue('ets_seo_advanced', array())) {

                            foreach ($advanced as $key => $val) {
                                if (isset($val[$id_lang])) {
                                    if (is_array($val[$id_lang])) {
                                        $val[$id_lang] = implode(',', $val[$id_lang]);
                                    }
                                    $seoCms->{$key} = $val[$id_lang];
                                }

                            }
                        }
                        $seoCms->save();
                    }

                    //Add score
                    if ($score_data = Tools::getValue('ets_seo_score_data')) {
                        $scoreArray = Tools::jsonDecode($score_data, true);
                        if (isset($scoreArray['seo_score']) && isset($scoreArray['readability_score'])) {
                            $contentAnalysis = Tools::getValue('ets_seo_content_analysis');
                            $contentAnalysis = $contentAnalysis ? Tools::jsonDecode($contentAnalysis, true) : array();
                            $this->saveScore('AdminCmsContent', $id_cms, $scoreArray['seo_score'], $scoreArray['readability_score'], $contentAnalysis, true);
                        }
                    }
                }
                $this->updateSeoRating('cms_category', $id_cms);
            }
        }
    }

    public function updateSeoProduct($params)
    {
        if (isset($params['product'])) {

            $id_product = $params['product']->id;
            $id_shop = $this->context->shop->id;
            $key_phrase = Tools::getValue('ets_seo_key_phrase', array());
            $minor_key_phrase = Tools::getValue('ets_seo_minor_keyphrase', array());
            $social_title = Tools::getValue('ets_seo_social_title', array());
            $social_desc = Tools::getValue('ets_seo_social_desc', array());
            $social_img = Tools::getValue('ets_seo_social_img', array());

            if ($key_phrase) {
                foreach ($key_phrase as $id_lang => $value) {

                    $id_ets_seo_product = Db::getInstance()->getValue("SELECT `id_ets_seo_product` 
                                                FROM `" . _DB_PREFIX_ . "ets_seo_product` 
                                                WHERE id_product = " . (int)$id_product . " AND id_shop = " . (int)$id_shop . " AND id_lang = " . (int)$id_lang);
                    if ($id_ets_seo_product) {
                        $seoProduct = new EtsSeoProduct($id_ets_seo_product);
                    } else {
                        $seoProduct = new EtsSeoProduct();
                    }

                    $seoProduct->id_product = $id_product;
                    $seoProduct->id_shop = $id_shop;
                    $seoProduct->id_lang = $id_lang;
                    $seoProduct->key_phrase = $value;
                    $seoProduct->minor_key_phrase = $this->getMinorKeyphrase($minor_key_phrase[$id_lang]);
                    $seoProduct->social_title = isset($social_title[$id_lang]) ? $social_title[$id_lang] : null;
                    $seoProduct->social_desc = isset($social_desc[$id_lang]) ? $social_desc[$id_lang] : null;

                    $seoProduct->social_img = isset($social_img[$id_lang]) && $social_img[$id_lang] ? $this->getSocialImage($social_img[$id_lang]) : null;

                    if ($advanced = Tools::getValue('ets_seo_advanced', array())) {

                        foreach ($advanced as $key => $val) {
                            if (isset($val[$id_lang]))
                                $seoProduct->{$key} = $val[$id_lang];
                        }
                    }

                    $seoProduct->save();
                    if ($score_data = Tools::getValue('ets_seo_score_data')) {
                        $scoreArray = Tools::jsonDecode($score_data, true);
                        if (isset($scoreArray['seo_score']) && isset($scoreArray['readability_score'])) {
                            $contentAnalysis = Tools::getValue('ets_seo_content_analysis');
                            $contentAnalysis = $contentAnalysis ? Tools::jsonDecode($contentAnalysis, true) : array();
                            $this->saveScore('AdminProducts', $id_product, $scoreArray['seo_score'], $scoreArray['readability_score'], $contentAnalysis);
                        }
                    }
                }
            }

            $this->updateSeoRating('product', $id_product);
        }
    }

    public function getRequestContainer()
    {
        if(!class_exists('\PrestaShop\PrestaShop\Adapter\SymfonyContainer'))
        {
            $kernel = null;
            try{
                $kernel = new AppKernel('prod', false);
                $kernel->boot();
                return $kernel->getContainer()->get('request_stack')->getCurrentRequest();
            }
            catch (Exception $ex){
                return null;
            }
        }
        $sfContainer = call_user_func(array('\PrestaShop\PrestaShop\Adapter\SymfonyContainer', 'getInstance'));

        if (null !== $sfContainer && null !== $sfContainer->get('request_stack')->getCurrentRequest()) {
            $request = $sfContainer->get('request_stack')->getCurrentRequest();
            return $request;
        }
        return null;
    }

    public function getSocialImage($path)
    {
        return basename($path);
    }

    public function saveScore($type, $id, $seo_scores, $readability_scores, $content_analysis = array(), $is_cms_category = false)
    {
        $languages = Language::getLanguages(false);
        $seo_score = array();
        $readability_score = array();
        $scoreAnalysis = array();
        if($content_analysis && is_array($content_analysis)){
            foreach ($content_analysis as $rules){
                foreach ($rules as $keyRule=>&$rule){
                    if(!Validate::isCleanHtml($rule['text'])){
                        $rule['text'] = '';
                    }
                }
                if(isset($rule)){
                    unset($rule);
                }
            }
        }
        foreach ($languages as $lang) {
            $scoreAnalysis[$lang['id_lang']] = array(
                'seo_score' => array(),
                'readability_score' => array(),
            );
            if (is_array($seo_scores)) {
                $seo_score[$lang['id_lang']] = 0;
                foreach ($seo_scores as $keyRule=>$rule) {

                    if (isset($rule[$lang['id_lang']])) {
                        $scoreAnalysis[$lang['id_lang']]['seo_score'][$keyRule] = $rule[$lang['id_lang']];
                        $seo_score[$lang['id_lang']] = $seo_score[$lang['id_lang']] + (int)$rule[$lang['id_lang']];
                    }
                }
                if(isset($rule))
                    unset($rule);
            }

            if (is_array($readability_scores)) {
                $readability_score[$lang['id_lang']] = 0;
                foreach ($readability_scores as $keyRule=>$rule) {
                    if (isset($rule[$lang['id_lang']])) {
                        $scoreAnalysis[$lang['id_lang']]['readability_score'][$keyRule] = $rule[$lang['id_lang']];
                        $readability_score[$lang['id_lang']] = $readability_score[$lang['id_lang']] + (int)$rule[$lang['id_lang']];
                    }
                }
            }

            if ($type == 'AdminProducts') {
                $exists = EtsSeoProduct::getSeoProduct($id, $this->context, (int)$lang['id_lang']);
                if ($exists) {
                    $seoProduct = new EtsSeoProduct((int)$exists['id_ets_seo_product']);
                    $seoProduct->seo_score = (int)$seo_score[$lang['id_lang']];
                    $seoProduct->readability_score = (int)$readability_score[$lang['id_lang']];
                    $seoProduct->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                    $seoProduct->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                    $seoProduct->update();
                } else {
                    $seoProduct = new EtsSeoProduct();
                    $seoProduct->id_shop = $this->context->shop->id;
                    $seoProduct->id_lang = (int)$lang['id_lang'];
                    $seoProduct->id_product = (int)$id;
                    $seoProduct->seo_score = (int)$seo_score[$lang['id_lang']];
                    $seoProduct->readability_score = (int)$readability_score[$lang['id_lang']];
                    $seoProduct->allow_search = 2;
                    $seoProduct->allow_flw_link = 1;
                    $seoProduct->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                    $seoProduct->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                    $seoProduct->save();
                }
            } elseif ($type == 'AdminCmsContent') {
                if ($is_cms_category) {
                    $exists = EtsSeoCmsCategory::getSeoCmsCategory($id, $this->context, (int)$lang['id_lang']);

                    if ($exists) {
                        $seoCMS = new EtsSeoCmsCategory((int)$exists['id_ets_seo_cms_category']);
                        $seoCMS->seo_score = (int)$seo_score[$lang['id_lang']];
                        $seoCMS->readability_score = (int)$readability_score[$lang['id_lang']];
                        $seoCMS->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                        $seoCMS->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                        $seoCMS->update();
                    } else {
                        $seoCMS = new EtsSeoCmsCategory();
                        $seoCMS->id_shop = $this->context->shop->id;
                        $seoCMS->id_lang = (int)$lang['id_lang'];
                        $seoCMS->id_cms_category = (int)$id;
                        $seoCMS->seo_score = (int)$seo_score[$lang['id_lang']];
                        $seoCMS->readability_score = (int)$readability_score[$lang['id_lang']];
                        $seoCMS->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                        $seoCMS->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                        $seoCMS->allow_search = 2;
                        $seoCMS->allow_flw_link = 1;
                        $seoCMS->save();

                    }
                } else {
                    $exists = EtsSeoCms::getSeoCms($id, $this->context, (int)$lang['id_lang']);

                    if ($exists) {
                        $seoCMS = new EtsSeoCms((int)$exists['id_ets_seo_cms']);
                        $seoCMS->seo_score = (int)$seo_score[$lang['id_lang']];
                        $seoCMS->readability_score = (int)$readability_score[$lang['id_lang']];
                        $seoCMS->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                        $seoCMS->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                        $seoCMS->update();
                    } else {
                        $seoCMS = new EtsSeoCms();
                        $seoCMS->id_shop = $this->context->shop->id;
                        $seoCMS->id_lang = (int)$lang['id_lang'];
                        $seoCMS->id_cms = (int)$id;
                        $seoCMS->seo_score = (int)$seo_score[$lang['id_lang']];
                        $seoCMS->readability_score = (int)$readability_score[$lang['id_lang']];
                        $seoCMS->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                        $seoCMS->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                        $seoCMS->allow_search = 2;
                        $seoCMS->allow_flw_link = 1;
                        $seoCMS->save();

                    }
                }

            } elseif ($type == 'AdminMeta') {
                $exists = EtsSeoMeta::getSeoMeta($id, $this->context, (int)$lang['id_lang']);
                if ($exists) {
                     $seoMeta = new EtsSeoMeta((int)$exists['id_ets_seo_meta']);
                     $seoMeta->seo_score = (int)$seo_score[$lang['id_lang']];
                     $seoMeta->readability_score = (int)$readability_score[$lang['id_lang']];
                     $seoMeta->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                     $seoMeta->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                     $seoMeta->save();
                } else {
                    $seoMeta = new EtsSeoMeta();
                    $seoMeta->id_shop = $this->context->shop->id;
                    $seoMeta->id_meta = (int)$id;
                    $seoMeta->id_lang = (int)$lang['id_lang'];
                    $seoMeta->seo_score = (int)$seo_score[$lang['id_lang']];
                    $seoMeta->readability_score = (int)$readability_score[$lang['id_lang']];
                    $seoMeta->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                    $seoMeta->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                    $seoMeta->allow_search = 1;
                    $seoMeta->allow_flw_link = 1;
                    $seoMeta->save();
                }
            } elseif ($type == 'AdminCategories') {
                $exists = EtsSeoCategory::getSeoCategory($id, $this->context, (int)$lang['id_lang']);

                if ($exists) {
                    $seoCategory = new EtsSeoCategory((int)$exists['id_ets_seo_category']);
                    $seoCategory->seo_score = (int)$seo_score[$lang['id_lang']];
                    $seoCategory->readability_score = (int)$readability_score[$lang['id_lang']];
                    $seoCategory->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                    $seoCategory->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                    $seoCategory->update();
                } else {
                    $seoCategory = new EtsSeoCategory();
                    $seoCategory->id_shop = $this->context->shop->id;
                    $seoCategory->id_lang = (int)$lang['id_lang'];
                    $seoCategory->id_category = (int)$id;
                    $seoCategory->seo_score = (int)$seo_score[$lang['id_lang']];
                    $seoCategory->readability_score = (int)$readability_score[$lang['id_lang']];
                    $seoCategory->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                    $seoCategory->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                    $seoCategory->allow_search = 2;
                    $seoCategory->allow_flw_link = 1;
                    $seoCategory->save();

                }
            } elseif ($type == 'AdminManufacturers') {

                $exists = EtsSeoManufacturer::getSeoManufacturer($id, $this->context, (int)$lang['id_lang']);

                if ($exists) {
                    $seoCategory = new EtsSeoManufacturer((int)$exists['id_ets_seo_manufacturer']);
                    $seoCategory->seo_score = (int)$seo_score[$lang['id_lang']];
                    $seoCategory->readability_score = (int)$readability_score[$lang['id_lang']];
                    $seoCategory->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                    $seoCategory->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                    $seoCategory->update();
                } else {
                    $seoCategory = new EtsSeoManufacturer();
                    $seoCategory->id_shop = $this->context->shop->id;
                    $seoCategory->id_lang = (int)$lang['id_lang'];
                    $seoCategory->id_manufacturer = (int)$id;
                    $seoCategory->seo_score = (int)$seo_score[$lang['id_lang']];
                    $seoCategory->readability_score = (int)$readability_score[$lang['id_lang']];
                    $seoCategory->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                    $seoCategory->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                    $seoCategory->allow_search = 2;
                    $seoCategory->allow_flw_link = 1;
                    $seoCategory->save();

                }
            } elseif ($type == 'AdminSuppliers') {
                $exists = EtsSeoSupplier::getSeoSupplier($id, $this->context, (int)$lang['id_lang']);

                if ($exists) {
                    $seoCategory = new EtsSeoSupplier((int)$exists['id_ets_seo_supplier']);
                    $seoCategory->seo_score = (int)$seo_score[$lang['id_lang']];
                    $seoCategory->readability_score = (int)$readability_score[$lang['id_lang']];
                    $seoCategory->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                    $seoCategory->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                    $seoCategory->update();
                } else {
                    $seoCategory = new EtsSeoSupplier();
                    $seoCategory->id_shop = $this->context->shop->id;
                    $seoCategory->id_lang = (int)$lang['id_lang'];
                    $seoCategory->id_supplier = (int)$id;
                    $seoCategory->seo_score = (int)$seo_score[$lang['id_lang']];
                    $seoCategory->readability_score = (int)$readability_score[$lang['id_lang']];
                    $seoCategory->score_analysis = Tools::jsonEncode($scoreAnalysis[$lang['id_lang']]);
                    $seoCategory->content_analysis = isset($content_analysis[$lang['id_lang']]) ? Tools::jsonEncode($content_analysis[$lang['id_lang']]) : null;
                    $seoCategory->allow_search = 2;
                    $seoCategory->allow_flw_link = 1;
                    $seoCategory->save();
                }
            }

        }
    }

    public function updateSeoRating($type, $id)
    {
        $sql = "SELECT id_ets_seo_rating 
              FROM `"._DB_PREFIX_."ets_seo_rating` 
              WHERE page_type = '".pSQL($type)."' 
              AND id_page = ".(int)$id." AND id_shop = ".(int)$this->context->shop->id;
        if((int)Tools::getValue('ets_seo_rating_enable') !== 1)
        {
            if($id_ets_seo_rating = (int)Db::getInstance()->getValue($sql))
            {
                $rating = new EtsSeoRating($id_ets_seo_rating);
                $rating->enable=0;
                $rating->save();
            }
            return;
        }
        $errors = $this->validateSeoRating();
        if($errors)
        {
            return;
        }

        if($id_ets_seo_rating = (int)Db::getInstance()->getValue($sql))
        {
            $rating = new EtsSeoRating($id_ets_seo_rating);
        }
        else{
            $rating = new EtsSeoRating();
        }
        $rating->page_type = $type;
        $rating->id_page = (int)$id;
        $rating->enable = (int)Tools::getValue('ets_seo_rating_enable');
        $rating->average_rating = (float)Tools::getValue('ets_seo_rating_average');
        if($bestRating = (float)Tools::getValue('ets_seo_rating_best'))
        {
            $rating->best_rating = (int)$bestRating;
        }
        else{
            $rating->best_rating = null;
        }
        if($worstRating = (float)Tools::getValue('ets_seo_rating_worst'))
        {
            $rating->worst_rating = (int)$worstRating;
        }
        $rating->rating_count = (int)Tools::getValue('ets_seo_rating_count');
        $rating->id_shop = (int)$this->context->shop->id;

        $rating->save();

    }

    public function  isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function getMinorKeyphrase($string){
        if($string && $this->isJson($string))
        {
            $data = json_decode($string, true);
            $minor = array();
            foreach ($data as $item)
            {
                $minor[] = $item['value'];
            }
            return implode(',', $minor);
        }

        return '';
    }

    public function validateSeoRating()
    {
        $errors = array();
        $seoDef = Ets_Seo_Define::getInstance();
        $trans = $seoDef->translateMessages();
        if((int)Tools::getValue('ets_seo_rating_enable') == 1)
        {
            $avgRating = Tools::getValue('ets_seo_rating_average');
            $bestRating = Tools::getValue('ets_seo_rating_best');
            $worstRating = Tools::getValue('ets_seo_rating_worst');
            $countRating = Tools::getValue('ets_seo_rating_count');
            if(!$avgRating)
            {
                $errors[] = $trans['avg_rating_required'];
            }
            elseif(!Validate::isUnsignedFloat($avgRating))
            {
                $errors[] = $trans['avg_rating_decimal'];
            }
            elseif((float)$avgRating <= 0 && (float)$avgRating > 5)
            {
                $errors[] = $trans['avg_rating_invalid'];;
            }
            else{
                if($bestRating) {
                    if (!Validate::isUnsignedInt($bestRating)) {
                        $errors[] = $trans['best_rating_integer'];
                    }
                    elseif((int)$bestRating > 5)
                    {
                        $errors[] = $trans['best_rating_invalid'];
                    }
                    elseif ((int)$bestRating < (float)$avgRating)
                    {
                        $errors[] = $trans['best_rating_greater_than_avg'];
                    }
                }

                if($worstRating) {
                    if (!Validate::isUnsignedInt($worstRating)) {
                        $errors[] = $trans['worst_rating_integer'];
                    }
                    elseif((int)$worstRating <= 0)
                    {
                        $errors[] = $trans['worst_rating_invalid'];
                    }
                    elseif ((int)$worstRating > (float)$avgRating)
                    {
                        $errors[] = $trans['worst_rating_less_than_avg'];
                    }
                }
            }
            if(!$countRating){
                $errors[] = $trans['rating_count_required'];
            }
            elseif(!Validate::isUnsignedInt($countRating))
            {
                $errors[] = $trans['rating_count_integer'];
            }
        }
        return $errors;
    }

    public static function validateLinkRewrite($type, $link_rewrites = array(), $id, $context = null)
    {
        if (!(int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')) {
            return false;
        }
        if(!$context)
        {
            $context = Context::getContext();
        }
        $table = '';
        $linkRewriteCol = 'link_rewrite';
        $idCol = '';
        $error = false;
        switch ($type){
            case 'product':
                $table = 'product_lang';
                $idCol = 'id_product';
                break;
            case 'category':
                $table = 'category_lang';
                $idCol = 'id_category';
                break;
            case 'cms':
                $table = 'cms_lang';
                $idCol = 'id_cms';
                break;
            case 'cms_category':
                $table = 'cms_category_lang';
                $idCol = 'id_cms_category';
                break;
            case 'meta':
                $table = 'meta_lang';
                $linkRewriteCol = 'url_rewrite';
                $idCol = 'id_meta';
                break;
        }
        if(!$table)
        {
            return $error;
        }
        $filterId = '';
        if ($id) {
            $filterId = " AND `" . (string)$idCol . "` !=" . (int)$id;
        }
        foreach ($link_rewrites as $id_lang => $link_rewrite) {
            $langCheck = Language::getLanguage($id_lang);
            if(!$langCheck || !(int)$langCheck['active'] || !$link_rewrite){
                continue;
            }
            $duplicate = Db::getInstance()->getValue(
                "SELECT *
                    FROM " . _DB_PREFIX_ . (string)$table . " 
                    WHERE `" . (string)$linkRewriteCol . "`='" . (string)$link_rewrite . "' AND id_lang=" . (int)$id_lang . " AND id_shop=" . (int)$context->shop->id . $filterId
            );
            if ($duplicate) {
                $error = $link_rewrite.' ('.Language::getIsoById($id_lang).')';
            }
            break;
        }
        return $error;

    }

    public static function checkLinkRewriteAjax($controller, $linkRewrites, $id = null, $isCmsCate = false)
    {
        $type = null;
        switch ($controller){
            case 'AdminProducts':
                $type = 'product';
                break;
        }
        if($type)
        {
            $dataLinks = array();
            foreach ($linkRewrites as $linkRewrite)
            {
                $dataLinks[$linkRewrite['id_lang']] = $linkRewrite['value'];
            }
            $error = self::validateLinkRewrite($type, $dataLinks, (int)$id);
            if($error)
            {
                die(Tools::jsonEncode(array(
                    'success' => false,
                    'error' => $error
                )));
            }
        }
        if($isCmsCate){
            //
        }
        die(Tools::jsonEncode(array(
            'success' => true,
            'error' => ''
        )));
    }

    public static function isMetaTemplateConfigured($controller, $is_cms_cate = false)
    {
        $title = '';
        $desc = '';
        switch ($controller)
        {
            case 'AdminProducts':
                $title =  'ETS_SEO_PROD_META_TILE';
                $desc =  'ETS_SEO_PROD_META_DESC';
                break;
            case 'AdminCategories':
                $title =  'ETS_SEO_CATEGORY_META_TILE';
                $desc =  'ETS_SEO_CATEGORY_META_DESC';
                break;
            case 'AdminCmsContent':
                if($is_cms_cate)
                {
                    $title =  'ETS_SEO_CMS_CATE_META_TILE';
                    $desc =  'ETS_SEO_CMS_CATE_META_DESC';
                }
                else{
                    $title = 'ETS_SEO_CMS_META_TILE';
                    $desc =  'ETS_SEO_CMS_META_DESC';
                }
                break;
            case 'AdminManufacturers':
                $title =  'ETS_SEO_MANUFACTURER_META_TITLE';
                $desc =  'ETS_SEO_MANUFACTURER_META_DESC';
                break;
            case 'AdminSuppliers':
                $title =  'ETS_SEO_SUPPLIER_META_TILE';
                $desc =  'ETS_SEO_SUPPLIER_META_DESC';
                break;
        }
        $languages = Language::getLanguages(false);
        $result = array();
        if($title)
        {
            foreach ($languages as $lang)
            {
                $result[$lang['id_lang']] = array(
                    'title' => Configuration::get($title, $lang['id_lang']),
                    'desc' => Configuration::get($desc, $lang['id_lang']),
                );
            }
        }
        return $result;
    }

    public static function getAnalysisScore($type = '', $id)
    {
        switch ($type)
        {
            case 'product':
                $data =  EtsSeoProduct::getSeoProduct($id);
                break;
            case 'category':
                $data =  EtsSeoCategory::getSeoCategory($id);
                break;
            case 'cms':
                $data =  EtsSeoCms::getSeoCms($id);
                break;
            case 'cms_category':
                $data =  EtsSeoCmsCategory::getSeoCmsCategory($id);
                break;
            case 'meta':
                $data =  EtsSeoMeta::getSeoMeta($id);
                break;
            case 'manufacturer':
                $data =  EtsSeoManufacturer::getSeoManufacturer($id);
                break;
            case 'supplier':
                $data =  EtsSeoSupplier::getSeoSupplier($id);
                break;
            default:
                $data =  array();
                break;
        }
        if(isset($data) && $data){
            $result = array();
            foreach ($data as $item){
                $result[$item['id_lang']] = $item;
            }
            return $result;
        }
        return array();
    }
}