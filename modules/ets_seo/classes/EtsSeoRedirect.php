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

class EtsSeoRedirect extends ObjectModel
{
    /**
     * @var int
     */
    public $id_ets_seo_redirect;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $target;

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var int
     */
    public $id_shop;

    public static $definition = array(
        'table' => 'ets_seo_redirect',
        'primary' => 'id_ets_seo_redirect',
        'multilang_shop' => false,
        'fields' => array(
            'id_ets_seo_redirect' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'url' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'target' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'
            ),
            
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            
        )
    );

    public static function getTypeUrlRedirect($current_url, $context = null, $active = false)
    {
        if(!$context)
        {
            $context = Context::getContext();
        }
        $url = trim(str_replace($context->shop->getBaseURL(true, false).__PS_BASE_URI__, '', $current_url));
        $where_active = $active ? " AND active = 1" : '';
        if(strpos($url,'/*') !== false){
            return  Db::getInstance()->getRow("SELECT `type`, `target` FROM `"._DB_PREFIX_."ets_seo_redirect` WHERE '".pSQL($url)."' REGEXP (REPLACE(`url`, '*', '(.*)')) ".pSQL($where_active)." AND `id_shop`=".(int)$context->shop->id);
        }
        return  Db::getInstance()->getRow("SELECT `type`, `target` FROM `"._DB_PREFIX_."ets_seo_redirect` WHERE `url` = '".pSQL($url)."'".pSQL($where_active)." AND `id_shop`=".(int)$context->shop->id);
    }
}