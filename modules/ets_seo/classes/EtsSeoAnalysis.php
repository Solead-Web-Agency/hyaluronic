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

class EtsSeoAnalysis
{
    public static $instance = null;
    protected $context;

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new EtsSeoAnalysis();
        }

        return self::$instance;
    }

    public function analysisPages($pages = array())
    {
        $limit = 10;
        $pageType = $pages[0];

        $defaultData = array(
            'id' => 0,
            'id_lang' => 0,
            'name' => null,
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'description' => null,
            'description_short' => null,
        );
        switch ($pageType){
            case 'product':
                $datas = Db::getInstance()->executeS("SELECT a.id_product as `id`, a.id_lang as id_lang, a.name as name, a.meta_title, a.meta_description, a.description, a.description_short
                        FROM `"._DB_PREFIX_."product_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_product` b ON (a.id_product = b.id_product AND a.id_lang = b.id_lang)
                        WHERE b.id_product IS NULL LIMIT ".(int)$limit);
                break;
            case 'category':
                $datas = Db::getInstance()->executeS("SELECT a.id_category as `id`, a.id_lang as id_lang, a.name as name, a.meta_title, a.meta_description, a.description, '' as description_short
                        FROM `"._DB_PREFIX_."category_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_category` b ON (a.id_category = b.id_category AND a.id_lang = b.id_lang)
                        WHERE b.id_category IS NULL LIMIT ".(int)$limit);
                break;
            case 'cms':
                $datas = Db::getInstance()->executeS("SELECT a.id_cms as `id`, a.id_lang as id_lang, a.meta_title as `name`, a.head_seo_title as meta_title, a.meta_description, a.content as description, '' as description_short
                        FROM `"._DB_PREFIX_."cms_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_cms` b ON (a.id_cms = b.id_cms AND a.id_lang = b.id_lang)
                        WHERE b.id_cms IS NULL LIMIT ".(int)$limit);
                break;
            case 'cms_category':
                $datas = Db::getInstance()->executeS("SELECT a.id_cms_category as `id`, a.id_lang as id_lang, a.name, a.meta_title, a.meta_description, a.description, '' as description_short
                        FROM `"._DB_PREFIX_."cms_category_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_cms_category` b ON (a.id_cms_category = b.id_cms_category AND a.id_lang = b.id_lang)
                        WHERE b.id_cms_category IS NULL LIMIT ".(int)$limit);
                break;
            case 'manufacturer':
                $datas = Db::getInstance()->executeS("SELECT a.id_manufacturer as `id`, a.id_lang as id_lang, m.name, a.meta_title, a.meta_description, a.description, a.short_description as description_short
                        FROM `"._DB_PREFIX_."manufacturer_lang` a
                        LEFT JOIN `"._DB_PREFIX_."manufacturer` m ON m.id_manufacturer = a.id_manufacturer
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_manufacturer` b ON (a.id_manufacturer = b.id_manufacturer AND a.id_lang = b.id_lang)
                        WHERE b.id_manufacturer IS NULL LIMIT ".(int)$limit);
                break;
            case 'supplier':
                $datas = Db::getInstance()->executeS("SELECT a.id_supplier as `id`, a.id_lang as id_lang, s.name, a.meta_title, a.meta_description, a.description, '' as description_short
                        FROM `"._DB_PREFIX_."supplier_lang` a
                        LEFT JOIN `"._DB_PREFIX_."supplier` s ON a.id_supplier = s.id_supplier
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_supplier` b ON (a.id_supplier = b.id_supplier AND a.id_lang = b.id_lang)
                        WHERE b.id_supplier IS NULL LIMIT ".(int)$limit);
                break;
            case 'meta':
                $datas = Db::getInstance()->executeS("SELECT a.id_meta as `id`, a.id_lang as id_lang, a.title as `name`, '' as meta_title, '' as meta_description, a.description, '' as description_short
                        FROM `"._DB_PREFIX_."meta_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_meta` b ON (a.id_meta = b.id_meta AND a.id_lang = b.id_lang)
                        WHERE b.id_meta IS NULL LIMIT ".(int)$limit);
                break;
            default:
                $datas = array();
        }

        if(!$datas && count($pages) > 1){
            if (($keyPage = array_search($pageType, $pages)) !== false) {
                array_splice($pages, $keyPage, 1);
                return $this->analysisPages($pages);
            }
        }
        if($datas){
            $results = array();
            foreach ($datas as $item){
                $results[] = array_merge($defaultData, $item);
            }
            $datas = $results;
        }
        return array(
            'data' => $datas,
            'page_type' => $pageType,
            'stop' => !$datas || !$pages ? 1 : 0
        );
    }

    public function updateDataAnalysis($pageType, $scoreData)
    {
        $tblName = '';
        $idCol = '';
        switch ($pageType){
            case 'product':
                $tblName = 'ets_seo_product';
                $idCol = 'id_product';
                break;
            case 'category':
                $tblName = 'ets_seo_category';
                $idCol = 'id_category';
                break;
            case 'cms':
                $tblName = 'ets_seo_cms';
                $idCol = 'id_cms';
                break;
            case 'cms_category':
                $tblName = 'ets_seo_cms_category';
                $idCol = 'id_cms_category';
                break;
            case 'manufacturer':
                $tblName = 'ets_seo_manufacturer';
                $idCol = 'id_manufacturer';
                break;
            case 'supplier':
                $tblName = 'ets_seo_supplier';
                $idCol = 'id_supplier';
                break;
            case 'meta':
                $tblName = 'ets_seo_meta';
                $idCol = 'id_meta';
                break;
        }

        if($tblName && $idCol){
            foreach ($scoreData as $scoreItem){
                Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_.$tblName."` (`".$idCol."`, `id_lang`, `id_shop`, `seo_score`, `readability_score`, `allow_flw_link`, `allow_search`) 
                                            VALUES(".(int)$scoreItem['id'].",".(int)$scoreItem['id_lang'].", ".(int)$this->context->shop->id.", ".(int)$scoreItem['score']['seo'].",".(int)$scoreItem['score']['readability'].",1, 2)");

            }
            return true;
        }
        return false;
    }
}