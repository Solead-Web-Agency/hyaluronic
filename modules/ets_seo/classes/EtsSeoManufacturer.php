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

class EtsSeoManufacturer extends ObjectModel
{
     /**
     * @var int
     */
    public $id_ets_seo_manufacturer;
     /**
     * @var int
     */
    public $id_manufacturer;

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
        'table' => 'ets_seo_manufacturer',
        'primary' => 'id_ets_seo_manufacturer',
        'multilang_shop' => false,
        'fields' => array(
            'id_ets_seo_manufacturer' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_manufacturer' => array(
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
                //'validate' => 'isString',
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

    public static function getSeoManufacturer($id_manufacturer, $context = null, $id_lang = null){
        if(!$context){
            $context = Context::getContext();
        }

        if($id_lang){
            
            return Db::getInstance()->getRow("SELECT * 
                                        FROM `"._DB_PREFIX_."ets_seo_manufacturer` 
                                        WHERE id_manufacturer = ".(int)$id_manufacturer." AND id_shop = ".(int)$context->shop->id." AND id_lang = ".(int)$id_lang);

        }
       
        return Db::getInstance()->executeS("SELECT * 
                                        FROM `"._DB_PREFIX_."ets_seo_manufacturer` 
                                        WHERE id_manufacturer = ".(int)$id_manufacturer." AND id_shop = ".(int)$context->shop->id);

    }


}