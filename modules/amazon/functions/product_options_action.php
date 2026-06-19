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
/* Is ajax/cron file */
require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../amazon.php');

require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.logger.class.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonProductOptionsJSON extends Amazon
{
    private $cr;

    private $auth;
    private $region;

    public function __construct()
    {
        parent::__construct();

        $this->cr = nl2br(Amazon::LF);

        AmazonContext::restore($this->context);

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    public function doIt()
    {
        if (!AmazonTools::checkToken(Tools::getValue('amazon_token'))) {
            die(Tools::displayError($this->l('Wrong Token')));
        }

        $callback = Tools::getValue('callback');

        if (empty($callback) || $callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $version = Tools::getValue('version');

        if (version_compare($version, '4', '>=')) {
            ob_start();
        }

        $langs = Tools::getValue('amazon_option_lang');

        if (!is_array($langs) && is_numeric($langs)) {
            $lang = (int)$langs;
            $langs = array($lang);
        }

        $id_product = (int)Tools::getValue('id_product');
        $id_product_attribute = null;
        $id_lang = (int)Tools::getValue('id_lang');
        $complex_id_product = Tools::getValue('complex_id_product');

        if (strpos($complex_id_product, '_') !== false) {
            $split_combination = explode('_', $complex_id_product);
            $id_product_attribute = (int)$split_combination[1];
        } elseif (is_numeric(trim($complex_id_product))) {
            $id_product_attribute = null;
        }

        if (empty($id_product) || !is_numeric($id_product)) {
            $action = null;
        } else {
            $product = new Product($id_product);

            if (Validate::isLoadedObject($product)) {
                $id_category = $product->id_category_default;
                $id_manufacturer = $product->id_manufacturer;
                $id_supplier = $product->id_supplier;
            }
            $action = AmazonTools::getValue('action');
        }

        if ($amazon_action = AmazonTools::getValue('amz-action-' . $id_lang)) {
            $amazon_action = in_array($amazon_action, array(
                    Amazon::ADD,
                    Amazon::REMOVE,
                    Amazon::UPDATE
                )) ? $amazon_action : Amazon::UPDATE;

            if (!AmazonProduct::marketplaceActionSet($amazon_action, $id_product, null, null, $id_lang)) {
                $pass = false;
            }
        }

        switch (Tools::getValue('action')) {
            case 'delete-product-option':
                $json = json_encode(array(
                    'error' => !AmazonProduct::marketplaceOptionDelete($id_product, $id_product_attribute, $id_lang)
                ));

                die($callback.'('.$json.')');
                break;
            case 'get-v4': // Set Product Option
                $pass = true;
                $message = null;


                if ($pass) {
                    $result = AmazonProduct::getProductOptionsV4($id_product, $id_product_attribute, $id_lang);

                    if (!(is_array($result) && count($result) && array_key_exists('asin1', reset($result)))) {
                        $product_options = array();
                        $pass = false;
                    } else {
                        $product_options = reset($result1);
                    }
                }

                $json = json_encode(array('error' => !$pass, $message, 'product_options' => $product_options));

                die($callback.'('.$json.')');
                break;

            case 'set': // Set Product Opton
                $pass = true;

                foreach ($langs as $val) {
                    $id_lang = (int)$val;
                    $disable = (bool)Tools::getValue('amz-disable-'.(int)$id_lang) ? 1 : 0;
                    $force = (bool)Tools::getValue('amz-force-'.(int)$id_lang) ? 1 : 0;
                    $price = (float)str_replace(',', '.', Tools::getValue('amz-price-'.(int)$id_lang));
                    $text = Tools::getValue('amz-text-'.(int)$id_lang);
                    $nopexport = (bool)Tools::getValue('amz-nopexport-'.(int)$id_lang);
                    $noqexport = (bool)Tools::getValue('amz-noqexport-'.(int)$id_lang);
                    $fba = (bool)Tools::getValue('amz-fba-'.(int)$id_lang);
                    $fba_value = (float)str_replace(',', '.', Tools::getValue('amz-fbavalue-'.(int)$id_lang));
                    $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);
                    $asin1 = Tools::getValue('amz-asin-'.(int)$id_lang);
                    $asin2 = Tools::getValue('amz-asin-2-'.(int)$id_lang);
                    $asin3 = Tools::getValue('amz-asin-3-'.(int)$id_lang);
                    $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                    $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);
                    $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);
                    $description = Tools::getValue('amz-description-'.(int)$id_lang);
                    $title = Tools::getValue('amz-title-'.(int)$id_lang);
                    $transparency_code = Tools::getValue('amz-transparencycode-'.(int)$id_lang);
                    $product_tax_override = Tools::getValue('amz-product_tax_override-'.(int)$id_lang);

                    $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);
                    $repricing_min = (float)str_replace(',', '.', Tools::getValue('amz-repricing_min-'.(int)$id_lang));
                    $repricing_max = (float)str_replace(',', '.', Tools::getValue('amz-repricing_max-'.(int)$id_lang));

                    if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                        // compatibility

                        $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, Amazon::LENGTH_BULLET_POINT) : null;
                        $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, Amazon::LENGTH_BULLET_POINT) : null;
                        $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, Amazon::LENGTH_BULLET_POINT) : null;
                        $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, Amazon::LENGTH_BULLET_POINT) : null;
                        $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, Amazon::LENGTH_BULLET_POINT) : null;
                    } else {
                        foreach (array(
                                     'bullet_point1',
                                     'bullet_point2',
                                     'bullet_point3',
                                     'bullet_point4',
                                     'bullet_point5'
                                 ) as $bullet_point) {
                            ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                        }
                    }

                    $gift_wrap = (bool)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                    $gift_message = (bool)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                    $brand = Tools::getValue('amz-brand-'.(int)$id_lang);
                    $manufacturer = Tools::getValue('amz-manufacturer-'.(int)$id_lang);
                    
                    $shipping = str_replace(',', '.', $shipping);

                    if (is_numeric($shipping) && $shipping == 0) {
                        $shipping = (float)0;
                    } elseif (empty($shipping)) {
                        $shipping = null;
                    } else {
                        $shipping = (float)$shipping;
                    }

                    $options = array(
                        'force' => $force,
                        'nopexport' => $nopexport,
                        'noqexport' => $noqexport,
                        'fba' => $fba,
                        'fba_value' => $fba_value,
                        'latency' => $latency,
                        'disable' => $disable,
                        'price' => $price,
                        'asin1' => $asin1,
                        'asin2' => $asin2,
                        'asin3' => $asin3,
                        'text' => $text,
                        'bullet_point1' => $bullet_point1,
                        'bullet_point2' => $bullet_point2,
                        'bullet_point3' => $bullet_point3,
                        'bullet_point4' => $bullet_point4,
                        'bullet_point5' => $bullet_point5,
                        'shipping' => $shipping,
                        'shipping_type' => $shipping_type,
                        'gift_wrap' => $gift_wrap,
                        'gift_message' => $gift_message,
                        'browsenode' => $browsenode,
                        'repricing_min' => $repricing_min,
                        'repricing_max' => $repricing_max,
                        'shipping_group' => $shipping_group,
                        'alternative_title' => $title,
                        'alternative_description' => $description,
                        'transparencycode' => $transparency_code,
                        'brand' => $brand,
                        'manufacturer' => $manufacturer,
                        'product_tax_override' => $product_tax_override
                    );

                    if (!AmazonProduct::setProductOptions($id_product, $id_lang, $options, $id_product_attribute)) {
                        $pass = false;
                    }
                }
                break;

            case 'propagate-action-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                $action = null;
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    $action = Tools::getValue('amz-action-'.(int)$id_lang);
                    $action = in_array($action, array(
                            Amazon::ADD,
                            Amazon::REMOVE,
                            Amazon::UPDATE
                        )) ? $action : Amazon::UPDATE;

                    break; // once ok
                }
                if ($action) {
                    if (!AmazonProduct::propagateProductActionToCategory($id_product, $id_category, pSQL($action))) {
                        $pass = false;
                    }
                }
                break;

            case 'propagate-action-shop':
                $pass = true;
                $action = null;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $action = Tools::getValue('amz-action-'.(int)$id_lang);
                    $action = in_array($action, array(
                            Amazon::ADD,
                            Amazon::REMOVE,
                            Amazon::UPDATE
                        )) ? $action : Amazon::UPDATE;

                    break;
                }

                if ($action) {
                    if (!AmazonProduct::propagateProductActionToShop($id_product, pSQL($action))) {
                        $pass = false;
                    }
                }
                break;

            case 'propagate-action-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    echo $id_manufacturer;
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $action = Tools::getValue('amz-action-'.(int)$id_lang);
                        $action = in_array($action, array(
                                Amazon::ADD,
                                Amazon::REMOVE,
                                Amazon::UPDATE
                            )) ? $action : Amazon::UPDATE;

                        break;
                    }

                    if ($action) {
                        if (!AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, $action)) {
                            $pass = false;
                        }
                    }
                }
                break;

            case 'propagate-action-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $action = Tools::getValue('amz-action-'.(int)$id_lang);
                    $action = in_array($action, array(
                            Amazon::ADD,
                            Amazon::REMOVE,
                            Amazon::UPDATE
                        )) ? $action : Amazon::UPDATE;

                    break;
                }
                if ($action) {
                    if (!AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, $action)) {
                        $pass = false;
                    }
                }
                break;


            case 'propagate-text-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $text = Tools::getValue('amz-text-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'text', $text)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;
            case 'propagate-text-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $text = Tools::getValue('amz-text-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'text', $text)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-text-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $text = Tools::getValue('amz-text-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'text', $text)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-text-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $text = Tools::getValue('amz-text-'.(int)$id_lang);

                    $options = array('text' => $text);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'text', pSQL($text))) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;


            case 'propagate-bulletpoint-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                    break;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                            // compatibility

                            $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, 500) : null;
                            $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, 500) : null;
                            $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, 500) : null;
                            $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, 500) : null;
                            $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, 500) : null;
                        } else {
                            foreach (array(
                                         'bullet_point1',
                                         'bullet_point2',
                                         'bullet_point3',
                                         'bullet_point4',
                                         'bullet_point5'
                                     ) as $bullet_point) {
                                ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                            }
                        }


                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point1', $bullet_point1)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point2', $bullet_point2)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point3', $bullet_point3)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point4', $bullet_point4)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point5', $bullet_point5)) {
                            $pass = false;
                        }

                        AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                    }
                }
                break;

            case 'propagate-bulletpoint-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                        // compatibility

                        $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, 500) : null;
                        $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, 500) : null;
                        $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, 500) : null;
                        $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, 500) : null;
                        $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, 500) : null;
                    } else {
                        foreach (array(
                                     'bullet_point1',
                                     'bullet_point2',
                                     'bullet_point3',
                                     'bullet_point4',
                                     'bullet_point5'
                                 ) as $bullet_point) {
                            ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                        }
                    }
                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point1', $bullet_point1)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point2', $bullet_point2)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point3', $bullet_point3)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point4', $bullet_point4)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point5', $bullet_point5)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-bulletpoint-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                        // compatibility

                        $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, 500) : null;
                        $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, 500) : null;
                        $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, 500) : null;
                        $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, 500) : null;
                        $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, 500) : null;
                    } else {
                        foreach (array(
                                     'bullet_point1',
                                     'bullet_point2',
                                     'bullet_point3',
                                     'bullet_point4',
                                     'bullet_point5'
                                 ) as $bullet_point) {
                            ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                        }
                    }

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point1', $bullet_point1)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point2', $bullet_point2)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point3', $bullet_point3)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point4', $bullet_point4)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point5', $bullet_point5)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-bulletpoint-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                        // compatibility

                        $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, 500) : null;
                        $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, 500) : null;
                        $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, 500) : null;
                        $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, 500) : null;
                        $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, 500) : null;
                    } else {
                        foreach (array(
                                     'bullet_point1',
                                     'bullet_point2',
                                     'bullet_point3',
                                     'bullet_point4',
                                     'bullet_point5'
                                 ) as $bullet_point) {
                            ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                        }
                    }

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point1', $bullet_point1)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point2', $bullet_point2)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point3', $bullet_point3)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point4', $bullet_point4)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point5', $bullet_point5)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-fba-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba = Tools::getValue('amz-fba-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'fba', $fba)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-fba-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba = Tools::getValue('amz-fba-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'fba', $fba)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-fba-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $fba = (int)Tools::getValue('amz-fba-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'fba', $fba)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }
                break;

            case 'propagate-fba-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba = (int)Tools::getValue('amz-fba-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'fba', $fba)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-fbavalue-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba_value = Tools::getValue('amz-fbavalue-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'fba_value', $fba_value)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-fbavalue-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba_value = Tools::getValue('amz-fbavalue-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'fba_value', $fba_value)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-fbavalue-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fbavalue = (int)Tools::getValue('amz-fbavalue-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'fba_value', $fbavalue)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-fbavalue-supplier':
                $pass = true;

                if (!$id_supplier) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $fbavalue = (int)Tools::getValue('amz-fbavalue-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'fba_value', $fbavalue)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                }
                break;

            case 'propagate-latency-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'latency', $latency)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-latency-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'latency', $latency)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-latency-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'latency', $latency)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }

                break;

            case 'propagate-latency-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'latency', $latency)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-browsenode-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'browsenode', $browsenode)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-browsenode-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'browsenode', $browsenode)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-browsenode-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'browsenode', $browsenode)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }

                break;

            case 'propagate-browsenode-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'browsenode', $browsenode)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;


            case 'propagate-shipping-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                    $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);

                    $shipping = str_replace(',', '.', $shipping);

                    if (is_numeric($shipping) && $shipping == 0) {
                        $shipping = (float)0;
                    } elseif (empty($shipping)) {
                        $shipping = null;
                    } else {
                        $shipping = (float)$shipping;
                    }

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, $shipping)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'shipping_type', $shipping_type)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-shipping-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                    $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);

                    $shipping = str_replace(',', '.', $shipping);

                    if (is_numeric($shipping) && $shipping == 0) {
                        $shipping = (float)0;
                    } elseif (empty($shipping)) {
                        $shipping = null;
                    } else {
                        $shipping = (float)$shipping;
                    }

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'shipping', $shipping)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'shipping_type', $shipping_type)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-shipping-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                        $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);

                        $shipping = str_replace(',', '.', $shipping);

                        if (is_numeric($shipping) && $shipping == 0) {
                            $shipping = (float)0;
                        } elseif (empty($shipping)) {
                            $shipping = null;
                        } else {
                            $shipping = (float)$shipping;
                        }

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'shipping', $shipping)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'shipping_type', $shipping_type)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }
                break;

            case 'propagate-shipping-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                        $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);

                        $shipping = str_replace(',', '.', $shipping);

                        if (is_numeric($shipping) && $shipping == 0) {
                            $shipping = (float)0;
                        } elseif (empty($shipping)) {
                            $shipping = null;
                        } else {
                            $shipping = (float)$shipping;
                        }

                        if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'shipping', $shipping)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'shipping_type', $shipping_type)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                }
                break;

            case 'propagate-disable-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $disable = (int)Tools::getValue('amz-disable-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'disable', $disable)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-disable-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $disable = (int)Tools::getValue('amz-disable-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'disable', $disable)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-disable-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $disable = (int)Tools::getValue('amz-disable-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'disable', $disable)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            // Force
            //
            case 'propagate-force-cat': // Propagate product option force
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $force = (int)Tools::getValue('amz-force-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'force', $force)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-force-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $force = (int)Tools::getValue('amz-force-'.(int)$id_lang);

                    $options = array('force' => $force);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'force', $force)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-force-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $force = (int)Tools::getValue('amz-force-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'force', $force)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }
                break;

            case 'propagate-force-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $force = (int)Tools::getValue('amz-force-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'force', $force)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-gift-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    $gift_wrap = (int)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                    $gift_message = (int)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'gift_wrap', $gift_wrap)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'gift_message', $gift_message)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-gift-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $gift_wrap = (int)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                        $gift_message = (int)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'gift_wrap', $gift_wrap)) {
                            $pass = false;
                        }
                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'gift_message', $gift_message)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-gift-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $gift_wrap = (int)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                    $gift_message = (int)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'gift_wrap', $gift_wrap)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'gift_message', $gift_message)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-gift-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $gift_wrap = (int)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                    $gift_message = (int)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'gift_wrap', $gift_wrap)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'gift_message', $gift_message)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            // shipping_group
            //
            case 'propagate-shipping_group-cat': // Propagate product option shipping_group
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'shipping_group', $shipping_group)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-shipping_group-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);

                    $options = array('shipping_group' => $shipping_group);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'shipping_group', $shipping_group)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-shipping_group-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'shipping_group', $shipping_group)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }
                break;

            case 'propagate-shipping_group-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'shipping_group', $shipping_group)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;
            
            // 2022-09-23: Tran removed action `ean2asin`, which uses deprecate MWS getASIN (ListMatchingProducts)

            case 'asin-probe':
                exit(json_encode($this->asinProbe(
                    Tools::getValue('id_product'),
                    Tools::getValue('id_product_attribute'),
                    Tools::getValue('id_lang'),
                    Tools::getValue('ean13'),
                    Tools::getValue('upc')
                )));

            case 'update-field':
                $field = Tools::getValue('field');
                $value = Tools::getValue('value');
                $pass = false;

                switch ($field) {
                    case 'ean13':
                        if (!is_numeric($value)) {
                            $json = json_encode(array('error' => true, 'output' => $this->l('The format is wrong!')));
                            die($callback.'('.$json.')');
                        }
                    //TODO: DO NOT BREAK HERE

                    case 'upc':
                        if (Tools::strlen($value) && !is_numeric($value)) {
                            die;
                        }
                    //TODO: DO NOT BREAK HERE

                    case 'reference':
                        $sql = null;

                        if ($id_product_attribute) {
                            $sql = 'UPDATE `'._DB_PREFIX_.'product_attribute` set `'.pSQL($field).'` = "'.pSQL($value).'" WHERE `id_product`='.(int)$id_product.' and `id_product_attribute` = '.(int)$id_product_attribute;
                        } elseif ($id_product) {
                            $sql = 'UPDATE `'._DB_PREFIX_.'product` set `'.pSQL($field).'` = "'.pSQL($value).'" WHERE `id_product`='.(int)$id_product;
                        }

                        if ($sql) {
                            if (Db::getInstance()->execute($sql)) {
                                $pass = true;
                            }
                        }
                        break;
                    case 'asin':
                        AmazonProduct::updateProductOptions($id_product, $id_lang, 'asin1', $value, $id_product_attribute);
                }
                break;

            case 'propagate-transparencycode-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $transparency_code = Tools::getValue('amz-transparencycode-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'transparencycode', $transparency_code)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-transparencycode-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $transparency_code = Tools::getValue('amz-transparencycode-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'transparencycode', $transparency_code)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-transparencycode-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $transparency_code = Tools::getValue('amz-transparencycode-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'transparencycode', $transparency_code)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }

                break;

            case 'propagate-transparencycode-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $transparency_code = Tools::getValue('amz-transparencycode-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'transparencycode', $transparency_code)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;
            
            //Brand
            case 'propagate-brand-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    $brand = Tools::getValue('amz-brand-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'brand', $brand)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-brand-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $brand = Tools::getValue('amz-brand-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'brand', $brand)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-brand-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $brand = Tools::getValue('amz-brand-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'brand', $brand)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-brand-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $brand = Tools::getValue('amz-brand-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'brand', $brand)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;
            
            //Manufacturer            
            case 'propagate-manufacturer-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    $manufacturer = Tools::getValue('amz-manufacturer-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'manufacturer', $manufacturer)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-manufacturer-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $manufacturer = Tools::getValue('amz-manufacturer-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'manufacturer', $manufacturer)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-manufacturer-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $manufacturer = Tools::getValue('amz-manufacturer-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'manufacturer', $manufacturer)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-manufacturer-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $manufacturer = Tools::getValue('amz-manufacturer-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'manufacturer', $manufacturer)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;
            //Product Tax Override
            case 'propagate-product_tax_override-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    $product_tax_override = Tools::getValue('amz-product_tax_override-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'product_tax_override', $product_tax_override)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-product_tax_override-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $product_tax_override = Tools::getValue('amz-product_tax_override-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'product_tax_override', $product_tax_override)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-product_tax_override-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $product_tax_override = Tools::getValue('amz-product_tax_override-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'product_tax_override', $product_tax_override)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-product_tax_override-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $product_tax_override = Tools::getValue('amz-product_tax_override-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'product_tax_override', $product_tax_override)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;
            default:
                $pass = false;
                break;
        }
        if (version_compare($version, '4', '<')) {
            if ($pass) {
                echo $this->l('Parameters successfully saved');
            } else {
                printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
            }
        }

        if (version_compare($version, '4', '>=')) {
            $output = ob_get_clean();

            $json = json_encode(array('error' => !$pass, 'output' => $output));

            die($callback.'('.$json.')');
        }
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if(!$lang)
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }

    /**
     * @param AmazonSellerPartnerResponse $searchResponse
     * @return string
     */
    private function asinProbed($searchResponse, $id_product, $id_product_attribute, $id_lang)
    {
        /** @var AmazonSPDefItemSearchResults $searchResult */
        $searchResult = $searchResponse->getStructuredPayload();
        if ($searchResult->hasItems()) {
            $asin = $searchResult->getFirstItem()->asin;
            AmazonProduct::updateProductOptions($id_product, $id_lang, 'asin1', $asin, $id_product_attribute);
            return $asin;
        }
        
        return '';
    }

    /**
     * @return array
     */
    public function asinProbe($id_product, $id_product_attribute, $id_lang, $ean13, $upc)
    {
        $lang2Mkp = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_LANG_TO_AMZ_MKP_ID);
        if (!isset($lang2Mkp[$id_lang])) {
            return array('success' => false, 'reason' => 'Invalid marketplace');
        }
        $marketplaceId = trim($lang2Mkp[$id_lang]);
        if (!$ean13 && !$upc) {
            return array('success' => false, 'reason' => 'Empty both EAN and UPC!');
        }

        $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($marketplaceId);
        $catalogApi = new AmazonSPAPICatalog(
            $spMkp,
            $spMkp->getMarketplaceId(),
            new AmazonLogger(AmazonLogger::CHANNEL_SP_API_CATALOG),
            $this->moduleFeatures->dev_mode
        );

        $fallbackResponse = array('success' => false, 'reason' => 'Not found');
        foreach (array(AmazonSPAPICatalog::IDENTIFIERS_TYPE_EAN => $ean13, AmazonSPAPICatalog::IDENTIFIERS_TYPE_UPC => $upc) as $type => $searchBy) {
            if ($searchBy) {
                $searchResult = $catalogApi->apiSearch(array($searchBy), $type);
                if ($searchResult->hasError()) {
                    $fallbackResponse = array('success' => false, 'reason' => $searchResult->getErrorMsg());
                } else {
                    $asin = $this->asinProbed($searchResult, $id_product, $id_product_attribute, $id_lang);
                    return array('success' => (bool)$asin, 'asin' => $asin, 'reason' => 'Not found');
                }
            }
        }

        return $fallbackResponse;
    }

    public function initAmazon($id_lang)
    {
        // Amazon Europe overidding
        //
        $amazon_features = Amazon::getAmazonFeatures();
        $amazonEurope = $amazon_features['amazon_europe'];

        $marketPlaceMaster = AmazonConfiguration::get('MASTER');
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
       
        $amazonCurrency = AmazonConfiguration::get('CURRENCY');

        // Currencies
        //
        $currencies = AmazonConfiguration::get('CURRENCY');

        if ((int)$amazonEurope) {
            foreach ($marketPlaceRegion as $language_id => $region) {
                // Identify the Master Marketplace
                //
                if ($marketPlaceMaster == $marketPlaceRegion[$language_id]) {
                    $mp_id_lang = $language_id;   // Default Platform - Language

                    $amazon = AmazonTools::selectPlatform($mp_id_lang, Amazon::$debug_mode);
                    
                    $this->auth = $amazon['auth'];
                }
                $this->params[$currencies[$language_id]] = array();
                $this->params[$currencies[$language_id]]['Currency'] = $amazonCurrency[$language_id];
                $this->params[$currencies[$language_id]]['Country'] = $marketPlaceMaster;

                // 2021-01-29: Tran removed unused $this->platforms, that cause undefined variable $marketPlaceIds
            }

            if (!isset($mp_id_lang)) {
                die($this->l('Amazon Europe : missing Master Platform configuration'));
            }
        } else {
            $amazon = AmazonTools::selectPlatform($id_lang, Amazon::$debug_mode);
            
            $this->auth =  $amazon['auth'];

            // 2021-01-29: Tran removed unused $this->platforms, that cause undefined variable $marketPlaceIds

            $this->params[$currencies[$id_lang]]['Currency'] = $amazonCurrency[$id_lang];
            $this->params[$currencies[$id_lang]]['Country'] = $marketPlaceRegion[$id_lang];
        }
        $this->region = $this->params[$currencies[$id_lang]];
    }
}

$apoJSON = new AmazonProductOptionsJSON();
$apoJSON->doIt();
