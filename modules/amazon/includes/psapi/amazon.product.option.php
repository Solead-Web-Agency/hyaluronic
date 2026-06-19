<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }
class AmazonProductOption extends ObjectModel
{
    
    public $id_mpo_webservice;
    public $id_product;
    public $id_lang;
    public $id_product_attribute;
    public $force;
    public $nopexport;
    public $noqexport;
    public $fba;
    public $fba_value;
    public $latency;
    public $disable;
    public $price;
    public $asin1;
    public $asin2;
    public $asin3;
    public $text;
    public $bullet_point1;
    public $bullet_point2;
    public $bullet_point3;
    public $bullet_point4;
    public $bullet_point5;
    public $shipping;
    public $shipping_type;
    public $gift_wrap;
    public $gift_message;
    public $browsenode;
    public $repricing_min;
    public $repricing_max;
    public $repricing_gap;
    public $shipping_group;
    public $alternative_title;
    public $alternative_description;
    public $id_shop;
    public $transparencycode;
    public $product_tax_override;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => AmazonConstant::TABLE_MARKETPLACE_PRODUCT_OPTION,
        'primary' => 'id_mpo_webservice',
        'multilang' => false,
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'required' => true),
            'id_lang' => array('type' => self::TYPE_INT, 'required' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'required' => true),
            'force' => array('type' => self::TYPE_INT),
            'nopexport' => array('type' => self::TYPE_INT),
            'noqexport' => array('type' => self::TYPE_INT),
            'fba' => array('type' => self::TYPE_INT),
            'fba_value' => array('type' => self::TYPE_FLOAT),
            'latency' => array('type' => self::TYPE_INT),
            'disable' => array('type' => self::TYPE_INT),
            'price' => array('type' => self::TYPE_FLOAT),
            'asin1' => array('type' => self::TYPE_STRING, 'size' => 16),
            'asin2' => array('type' => self::TYPE_STRING, 'size' => 16),
            'asin3' => array('type' => self::TYPE_STRING, 'size' => 16),
            'text' => array('type' => self::TYPE_STRING, 'size' => 256),
            'bullet_point1' => array('type' => self::TYPE_STRING, 'size' => AmazonConstant::LENGTH_BULLET_POINT),
            'bullet_point2' => array('type' => self::TYPE_STRING, 'size' => AmazonConstant::LENGTH_BULLET_POINT),
            'bullet_point3' => array('type' => self::TYPE_STRING, 'size' => AmazonConstant::LENGTH_BULLET_POINT),
            'bullet_point4' => array('type' => self::TYPE_STRING, 'size' => AmazonConstant::LENGTH_BULLET_POINT),
            'bullet_point5' => array('type' => self::TYPE_STRING, 'size' => AmazonConstant::LENGTH_BULLET_POINT),
            'shipping' => array('type' => self::TYPE_FLOAT),
            'shipping_type' => array('type' => self::TYPE_INT),
            'gift_wrap' => array('type' => self::TYPE_INT),
            'gift_message' => array('type' => self::TYPE_INT),
            'browsenode' => array('type' => self::TYPE_STRING, 'size' => 16),
            'repricing_min' => array('type' => self::TYPE_FLOAT),
            'repricing_max' => array('type' => self::TYPE_FLOAT),
            'repricing_gap' => array('type' => self::TYPE_FLOAT),
            'shipping_group' => array('type' => self::TYPE_STRING, 'size' => 32),
            'alternative_title' => array('type' => self::TYPE_STRING, 'size' => 255),
            'alternative_description' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'transparencycode' => array('type' => self::TYPE_STRING, 'size' => 64),
            'product_tax_override' => array('type' => self::TYPE_STRING, 'size' => 64),
        )
    );

    protected $webserviceParameters = array(
      'objectNodeName' => 'marketplace_product_option',
      'objectsNodeName' => 'marketplace_product_options',
      'fields' => array(
          'id_product' => array('required' => true),
          'id_lang' => array('required' => true),
          'id_product_attribute' => array('required' => true),
          'force' => array(),
          'nopexport' => array(),
          'noqexport' => array(),
          'fba' => array(),
          'fba_value' => array(),
          'latency' => array(),
          'disable' => array(),
          'price' => array(),
          'asin1' => array(),
          'asin2' => array(),
          'asin3' => array(),
          'text' => array(),
          'bullet_point1' => array(),
          'bullet_point2' => array(),
          'bullet_point3' => array(),
          'bullet_point4' => array(),
          'bullet_point5' => array(),
          'shipping' => array(),
          'shipping_type' => array(),
          'gift_wrap' => array(),
          'gift_message' => array(),
          'browsenode' => array(),
          'repricing_min' => array(),
          'repricing_max' => array(),
          'repricing_gap' => array(),
          'shipping_group' => array(),
          'alternative_title' => array(),
          'alternative_description' => array(),
          'id_shop' => array(),
          'transparencycode' => array(),
          'product_tax_override' => array(),
        )
    );
}
