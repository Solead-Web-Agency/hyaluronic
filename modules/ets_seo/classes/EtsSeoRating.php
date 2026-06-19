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

class EtsSeoRating extends ObjectModel
{
    /**
     * @var int
     */
    public $id_ets_seo_rating;
    /**
     * @var string
     */
    public $page_type;

    /**
     * @var int
     */
    public $id_page;

    /**
     * @var int
     */
    public $enable;

    /**
     * @var float
     */
    public $average_rating;
    /**
     * @var float
     */
    public $best_rating;

    /**
     * @var float
     */
    public $worst_rating;

    /**
     * @var int
     */
    public $rating_count;

    /**
     * @var int
     */
    public $id_shop;


    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'ets_seo_rating',
        'primary' => 'id_ets_seo_rating',
        'multilang_shop' => false,
        'fields' => array(
            'page_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'id_page' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'enable' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'average_rating' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isUnsignedFloat'
            ),
            'best_rating' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isUnsignedFloat',
                'allow_null' => true
            ),
            'worst_rating' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isUnsignedFloat',
                'allow_null' => true
            ),
            'rating_count' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
        )
    );

    public static function getRating($type, $id, $context = null){

        if($type && (int)$id)
        {
            if(!$context)
            {
                $context = Context::getContext();
            }
            return Db::getInstance()->getRow("SELECT * FROM `"._DB_PREFIX_."ets_seo_rating` WHERE page_type = '".pSQL($type)."' AND id_page = ".(int)$id." AND id_shop = ".(int)$context->shop->id);
        }
        return false;
    }

    public static function getRatingConfig($type, $id)
    {
        $ratingConfig = Configuration::get('ETS_SEO_RATING_PAGES') ? explode(',', Configuration::get('ETS_SEO_RATING_PAGES')) : array();
        if(!in_array($type, $ratingConfig)){
            return null;
        }
        $dataRating = null;
        if((int)$id)
        {
            switch ($type)
            {
                case 'product':
                    $dataRating = self::getRating('product', $id);
                    break;
                case 'cms':
                    $dataRating = self::getRating('cms', $id);
                    break;
                case 'meta':
                    $dataRating = self::getRating('meta', $id);
                    break;
                case 'category':
                    $dataRating = self::getRating('category', $id);
                    break;
                case 'cms_category':
                    $dataRating = self::getRating('cms_category', $id);
                    break;
                case 'manufacturer':
                    $dataRating = self::getRating('manufacturer', $id);
                    break;
                case 'supplier':
                    $dataRating = self::getRating('supplier', $id);
                    break;
            }
        }

        $ratingSeo = array(
            'avg_rating' => '',
            'best_rating' => '',
            'worst_rating' => '',
            'rating_count' => '',
        );

        $enableRating = false;
        if($dataRating && (int)$dataRating['enable'] == 1)
        {
            $ratingSeo = array(
                'avg_rating' => (float)$dataRating['average_rating'],
                'best_rating' => (int)$dataRating['best_rating'],
                'worst_rating' => (int)$dataRating['worst_rating'],
                'rating_count' => (int)$dataRating['rating_count'],
            );
            $enableRating = true;
        }
        elseif($dataRating && (int)$dataRating['enable'] == 0)
        {
            $ratingSeo = array(
                'avg_rating' => (float)$dataRating['average_rating'],
                'best_rating' => (int)$dataRating['best_rating'],
                'worst_rating' => (int)$dataRating['worst_rating'],
                'rating_count' => (int)$dataRating['rating_count'],
            );
            $enableRating = false;
        }
        return $enableRating ? $ratingSeo : null;
    }
}