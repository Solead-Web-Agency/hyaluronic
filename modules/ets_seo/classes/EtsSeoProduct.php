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

if (!defined('_PS_VERSION_')) {
    exit();
}

class EtsSeoProduct extends ObjectModel
{
     /**
     * @var int
     */
    public $id_ets_seo_product;
     /**
     * @var int
     */
    public $id_product;

     /**
     * @var int
     */
    public $id_shop;

     /**
     * @var int
     */
    public $id_lang;

     /**
     * @var string
     */
    public $key_phrase;

    /**
     * @var string
     */
    public $minor_key_phrase;
    
     /**
     * @var int
     */
    public $allow_search;

     /**
     * @var int
     */
    public $allow_flw_link;

     /**
     * @var string
     */
    public $meta_robots_adv;
    
     /**
     * @var string
     */
    public $meta_keywords;

     /**
     * @var string
     */
    public $canonical_url;

     /**
     * @var int
     */
    public $seo_score;

     /**
     * @var int
     */
    public $readability_score;

    /**
     * @var string
     */
    public $score_analysis;

    /**
     * @var string
     */
    public $content_analysis;

    /**
     * @var string
     */
    public $social_title;

     /**
     * @var string
     */
    public $social_desc;

     /**
     * @var string
     */
    public $social_img;

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'ets_seo_product',
        'primary' => 'id_ets_seo_product',
        'multilang_shop' => false,
        'fields' => array(
            'id_ets_seo_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_lang' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'key_phrase' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'minor_key_phrase' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'allow_search' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'allow_flw_link' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'meta_robots_adv' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'meta_keywords' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'canonical_url' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'seo_score' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'readability_score' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'score_analysis' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'allow_null' => true
            ),
            'content_analysis' => array(
                'type' => self::TYPE_HTML,
                //'validate' => 'isCleanHtml',
                'allow_null' => true
            ),
            'social_title' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'social_desc' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'social_img' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            
        )
    );

    public static function getSeoProduct($id_product, $context = null, $id_lang = null){
        if(!$context){
            $context = Context::getContext();
        }

        if($id_lang){
            
            return Db::getInstance()->getRow("SELECT * 
                                        FROM `"._DB_PREFIX_."ets_seo_product` 
                                        WHERE id_product = ".(int)$id_product." AND id_shop = ".(int)$context->shop->id." AND id_lang = ".(int)$id_lang);

        }
       
        return Db::getInstance()->executeS("SELECT * 
                                        FROM `"._DB_PREFIX_."ets_seo_product` 
                                        WHERE id_product = ".(int)$id_product." AND id_shop = ".(int)$context->shop->id);

    }

    public static function getProductAttributeName($id_product_attribute, $context = null)
    {
        if(!$context)
        {
            $context = Context::getContext();
        }
        $sql = 'SELECT a.id_attribute,al.name,agl.name as group_name FROM `'._DB_PREFIX_.'attribute` a
            INNER JOIN `'._DB_PREFIX_.'attribute_shop` attribute_shop ON (a.id_attribute= attribute_shop.id_attribute AND attribute_shop.id_shop="'.(int)$context->shop->id.'")
            INNER JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (a.id_attribute=pac.id_attribute)
            LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.id_attribute=al.id_attribute AND al.id_lang="'.(int)$context->language->id.'")
            LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (a.id_attribute_group= agl.id_attribute_group AND agl.id_lang="'.(int)$context->language->id.'")
            WHERE pac.id_product_attribute ="'.(int)$id_product_attribute.'"
        ';
        $attributes = Db::getInstance()->executeS($sql);
        $name_attribute ='';
        if($attributes)
        {
            foreach($attributes as $attribute)
            {
                $name_attribute .= $attribute['group_name'].' '.$attribute['name'].' - ';
            }
        }
        return trim($name_attribute,'- ');
    }

    public static function getCommentProductData($id_product)
    {
        if (Module::isInstalled('productcomments') && Module::isEnabled('productcomments')) {
            $data = Db::getInstance()->getRow("SELECT COUNT(*) as total_rating, SUM(grade) as total_review FROM `"._DB_PREFIX_."product_comment` pc WHERE id_product = ".(int)$id_product);
            if($data)
            {
                $rating_count = (int)$data['total_rating'];
                $total_review = (int)$data['total_review'];
                return array(
                    'avg_rating' => $rating_count ? $total_review / $rating_count : 0,
                    'rating_count' => $rating_count
                );
            }
        }
        return array(
            'avg_rating' => 0,
            'rating_count' => 0
        );
    }

    public static function getMetaKeywords($id_product, $format_idlang = false)
    {
        $results =  Db::getInstance()->executeS("SELECT meta_keywords, id_lang FROM `"._DB_PREFIX_."ets_seo_product` WHERE id_product = ".(int)$id_product);

        if($format_idlang && $results)
        {
            $data = array();
            foreach ($results as $item)
            {
                $data[$item['id_lang']] = $item;
            }
            return $data;
        }
        return $results;
    }

}