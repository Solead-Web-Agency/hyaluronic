<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    SeQura Tech <prestashop@sequra.com>
 * @copyright Since 2013 SeQura WorldWide SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopSequra;

use PrestaShop\Module\PrestashopSequra\Configuration\PaymentMethodsSettings;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PreQualifier
{
    protected static $MODULE_NAME = 'sequra';
    protected static $SERVICE_COMPATIBLE = true;
    /**
     * @var \Cart
     */
    private $cart;
    /**
     * @var \Sequra|false
     */
    private $module;

    public function __construct($cart)
    {
        $this->cart = $cart;
        $this->module = \Module::getInstanceByName(static::$MODULE_NAME);
    }

    public function passes()
    {
        return
            static::available($this->module)
            && $this->isCartEligible()
            && $this->priceWithinRange()
            && static::availableForIP();
    }

    public static function canDisplayWidgetInProductPage($id_product)
    {
        $logger = new Logger();

        if (!$id_product) {
            $logger->logInfo("Product not eligible for widget. Bad ID:$id_product", __FUNCTION__, __CLASS__);

            return false;
        }
        if (!self::availableForIP()) {
            return false;
        }

        $sq_product_extra = new ProductExtra($id_product);
        if ($sq_product_extra->getProductIsBanned()) {
            $logger->logInfo("Product ID:$id_product is banned", __FUNCTION__, __CLASS__);

            return false;
        }
        if (!\Configuration::get('SEQURA_FOR_SERVICES')
            && $sq_product_extra->getProductIsVirtual()
        ) {
            $logger->logInfo("Product ID:$id_product not eligible for widget. Is virtual", __FUNCTION__, __CLASS__);

            return false;
        }

        return true;
    }

    public static function isPriceWithinMethodRange($method, $price, $check_min = true)
    {
        $max = $method['max_amount'] / 100;
        $min = $method['min_amount'] / 100;
        $too_much = is_numeric($max) && $max > 0 && $price > $max;
        $too_low = (is_numeric($min) && $min > 0 && $price < $min) && $check_min;

        return !$too_much && !$too_low;
    }

    public static function isPriceWithinRange($price, $check_min = true)
    {
        $ret = array_filter(
            PaymentMethodsSettings::getMerchantPaymentMethods(false, self::getCountryCode()),
            function ($method) use ($price, $check_min) {
                return
                    self::isPriceWithinMethodRange(
                        $method,
                        $price,
                        $check_min && PaymentMethodsSettings::getFamilyFor($method) != 'PARTPAYMENT'
                    );
            }
        );

        $is_not_empty = count($ret) > 0;
        if (!$is_not_empty) {
            $logger = new Logger();
            $logger->logInfo("Price: $price not within range.", __FUNCTION__, __CLASS__);
        }

        return $is_not_empty;
    }

    public static function isDateInRange($method)
    {
        $to_date = isset($method['ends_at']) ? strtotime($method['ends_at']) : 0;
        $from_date = isset($method['starts_at']) ? strtotime($method['starts_at']) : 0;

        return (!$from_date || time() >= $from_date)
            && (!$to_date || time() <= $to_date);
    }

    private static function getCountryCode()
    {
        $cart = \Context::getContext()->cart;
        if ($cart && $cart->id_address_delivery) {
            $address = new \Address($cart->id_address_delivery);

            return \Country::getIsoById($address->id_country);
        } elseif ($cart && $cart->id_address_delivery) {
            $address = new \Address($cart->id_address_delivery);

            return \Country::getIsoById($address->id_country);
        }

        return strtoupper(substr(\Context::getContext()->language->iso_code, -2));
    }

    public static function canShowBanner($key)
    {
        $show_banner = \ConfigurationCore::get($key, null, null, null, 0);

        return $show_banner && self::canDisplayInfo();
    }

    public static function canDisplayInfo($price = null)
    {
        // For this plugin widgets are added on page footer (footer.tpl)
        return false;
    }

    public static function available($module)
    {
        if ($module && \Module::isInstalled(static::$MODULE_NAME)) {
            $available = true;
            if (\Configuration::get('SEQURA_FOR_SERVICES')) {
                $available = static::$SERVICE_COMPATIBLE;
            }
            if (method_exists('Module', 'isEnabled')) {
                if (\Module::isEnabled(static::$MODULE_NAME)) {
                    return $available;
                }
            } else {
                if ($module->active) {
                    return $available;
                }
            }
        }

        $logger = new Logger();
        $logger->logInfo('Module is not installed or enabled', __FUNCTION__, __CLASS__);

        return false;
    }

    public static function availableForIP()
    {
        $allowed_ips = preg_split('/[\s*,]/', \Configuration::get('SEQURA_ALLOW_IP'), -1, PREG_SPLIT_NO_EMPTY);

        $is_allowed = empty($allowed_ips) || in_array($_SERVER['REMOTE_ADDR'], $allowed_ips) || isset($_COOKIE['SEQURA_INTEGRATOR']);

        if (!$is_allowed) {
            $logger = new Logger();
            $logger->logInfo('IP not allowed', __FUNCTION__, __CLASS__);
        }

        return $is_allowed;
    }

    public function priceWithinRange()
    {
        $price = $this->cart->getOrderTotal();

        return static::isPriceWithinRange($price);
    }

    private function isCartEligible()
    {
        $banned_products = array_filter(
            $this->cart->getProducts(),
            function ($cart_item) {
                $sq_product_extra = new ProductExtra($cart_item['id_product']);

                return $sq_product_extra->getProductIsBanned();
            }
        );

        $is_eligible = count($banned_products) == 0;
        if (!$is_eligible) {
            $logger = new Logger();
            $logger->logInfo('Cart not eligible for widget. Banned products', __FUNCTION__, __CLASS__);
        }

        return $is_eligible;
    }

    public function allowedCountry()
    {
        $address = new \Address((int) $this->cart->id_address_delivery);
        $country = new \Country((int) $address->id_country);

        return in_array($country->iso_code, $this->module->getCountries());
    }
}
