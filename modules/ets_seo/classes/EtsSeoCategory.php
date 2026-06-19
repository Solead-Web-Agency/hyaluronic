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

class EtsSeoCategory extends ObjectModel
{
     /**
     * @var int
     */
    public $id_ets_seo_category;
     /**
     * @var int
     */
    public $id_category;

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
        'table' => 'ets_seo_category',
        'primary' => 'id_ets_seo_category',
        'multilang_shop' => false,
        'fields' => array(
            'id_ets_seo_category' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_category' => array(
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

    public static function getSeoCategory($id_category, $context = null, $id_lang = null){
        if(!$context){
            $context = Context::getContext();
        }

        if($id_lang){
            return Db::getInstance()->getRow("SELECT * 
                                        FROM `"._DB_PREFIX_."ets_seo_category` 
                                        WHERE id_category = ".(int)$id_category." AND id_shop = ".(int)$context->shop->id." AND id_lang = ".(int)$id_lang);

        }
       
        return Db::getInstance()->executeS("SELECT * 
                                        FROM `"._DB_PREFIX_."ets_seo_category` 
                                        WHERE id_category = ".(int)$id_category." AND id_shop = ".(int)$context->shop->id);

    }

    public static function getCategoriesWithoutRoot(){
        $roots = Category::getRootCategories();
        $category = array();
        $context = Context::getContext();
        foreach ($roots as $root)
        {
            $category = array_merge($category, self::getNestedCategories($root['id_category'], $context->language->id));
        }
        return $category;
    }

    public static function getNestedCategories(
        $idRootCategory = null,
        $idLang = false,
        $active = true,
        $groups = null,
        $useShopRestriction = true,
        $sqlFilter = '',
        $orderBy = '',
        $limit = ''
    ) {
        if (isset($idRootCategory) && !Validate::isInt($idRootCategory)) {
            die(Tools::displayError());
        }

        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        if (isset($groups) && Group::isFeatureActive() && !is_array($groups)) {
            $groups = (array) $groups;
        }

        $cacheId = 'EtsSeoCategory::getNestedCategories_' . md5(
                (int) $idRootCategory .
                (int) $idLang .
                (int) $active .
                (int) $useShopRestriction .
                (isset($groups) && Group::isFeatureActive() ? implode('', $groups) : '') .
                (isset($sqlFilter) ? $sqlFilter : '') .
                (isset($orderBy) ? $orderBy : '') .
                (isset($limit) ? $limit : '')
            );

        if (!Cache::isStored($cacheId)) {
            $result = Db::getInstance()->executeS(
                '
				SELECT c.*, cl.*
				FROM `' . _DB_PREFIX_ . 'category` c
				' . ($useShopRestriction ? Shop::addSqlAssociation('category', 'c') : '') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . '
				' . (isset($groups) && Group::isFeatureActive() ? 'LEFT JOIN `' . _DB_PREFIX_ . 'category_group` cg ON c.`id_category` = cg.`id_category`' : '') . '
				' . (isset($idRootCategory) ? 'RIGHT JOIN `' . _DB_PREFIX_ . 'category` c2 ON c2.`id_category` = ' . (int) $idRootCategory . ' AND c.`nleft` > c2.`nleft` AND c.`nright` < c2.`nright`' : '') . '
				WHERE 1 ' . $sqlFilter . ' ' . ($idLang ? 'AND `id_lang` = ' . (int) $idLang : '') . '
				' . ($active ? ' AND c.`active` = 1' : '') . '
				' . (isset($groups) && Group::isFeatureActive() ? ' AND cg.`id_group` IN (' . implode(',', array_map('intval', $groups)) . ')' : '') . '
				' . (!$idLang || (isset($groups) && Group::isFeatureActive()) ? ' GROUP BY c.`id_category`' : '') . '
				' . ($orderBy != '' ? $orderBy : ' ORDER BY c.`level_depth` ASC') . '
				' . ($orderBy == '' && $useShopRestriction ? ', category_shop.`position` ASC' : '') . '
				' . ($limit != '' ? $limit : '')
            );
            Cache::store($cacheId, $result);
        } else {
            $result = Cache::retrieve($cacheId);
        }

        return $result;
    }

}