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

require_once(dirname(__FILE__) . '/../classes/amazon.configuration.class.php');

class AmazonShop extends Shop
{
    public static function setShop($shop)
    {
        self::$context_id_shop = $shop->id;
        self::$context_id_shop_group = $shop->id_shop_group;
        self::$context = self::CONTEXT_SHOP;
    }
}

/**
 * PHP5.6 does not allow to access empty property.
 * $a = (object)['a' => 1, 'b' => 2]; $c = ''; $a->$c = 3; ---> Fatal error: Cannot access empty property
 * In case of non-multistore, `$contextKey` is empty, therefore can not assign it to the module context.
 * Read / write module context by array access instead (json_decode array associate)
 */
class AmazonContext
{
    /**
     * Restore shop context for ajax scripts
     * @param $context
     * @param null $shop
     * @param bool|false $debug
     * @return bool
     */
    public static function restore(&$context, $shop = null, $debug = false)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (!Shop::isFeatureActive()) {
                $context = Context::getContext();
                if (!property_exists($context, 'controller') || !is_object($context->controller)) {
                    $context->controller = new FrontController();
                }

                return true;
            }

            $context_key = $shop instanceof Shop ? self::getKey($shop) : Tools::getValue('context_key');
            $storedContexts = json_decode(
                AmazonTools::decode(
                    AmazonConfiguration::getGlobalValue(AmazonConstant::PS_CONTEXT_4_10)
                ),
                true    // Explanation in the class declaration
            );

            if (!$storedContexts || !is_string($context_key)
                || !isset($storedContexts[$context_key])
                || !$storedContexts[$context_key] || !is_array($storedContexts[$context_key])) {
                if ($debug) {
                    printf('%s(#%d): Wrong context, please configure your module first', basename(__FILE__), __LINE__);
                }

                return false;
            }

            $idEmployee = $storedContexts[$context_key]['id_employee'];
            $idCurrency = $storedContexts[$context_key]['id_currency'];
            $idCountry = $storedContexts[$context_key]['id_country'];
            $idLanguage = $storedContexts[$context_key]['id_language'];
            $idShop = (int)$storedContexts[$context_key]['id_shop'];

            $context->employee = new Employee($idEmployee);
            $context->currency = new Currency($idCurrency);
            $context->country = new Country($idCountry);
            $context->language = new Language($idLanguage);
            $context->controller = isset($storedContexts[$context_key]['controller']) && is_array($storedContexts[$context_key]['controller'])
                ? (object)$storedContexts[$context_key]['controller']
                : new FrontController();
            if ($idShop) {
                $context->shop = new Shop($idShop);
            }

            AmazonShop::setShop($context->shop);
        }

        return true;
    }

    /**
     * Generate an unique key to store the context
     * @param $shop
     * @return null|string
     */
    public static function getKey($shop)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return (null);
        }

        if (!Shop::isFeatureActive()) {
            return (null);
        }

        if (!$shop instanceof Shop && !$shop instanceof StdClass) {
            return (null);
        }

        $id_shop = (int)$shop->id;
        $id_shop_group = (int)$shop->id_shop_group;

        $context_key = dechex(crc32(sprintf('%02d_%02d', $id_shop, $id_shop_group))); // create a short key

        return ($context_key);
    }

    /**
     * Save store context
     * @param Context $context
     * @param null $employee
     * @param bool|false $debug
     * @return bool
     */
    public static function save($context, $employee = null, $debug = false)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $contextKey = self::getKey($context->shop);
            $amazonContexts = json_decode(
                AmazonTools::decode(
                    AmazonConfiguration::getGlobalValue(AmazonConstant::PS_CONTEXT_4_10)
                ),
                true    // Explanation in the class declaration
            );

            if (!$amazonContexts) {
                $amazonContexts = array();
            }

            // save the whole controller instance instead of its ID
            // because PS 1.6 has problem creating AdminModulesController when restoring context
            // https://common-services-force.monday.com/boards/1971464818/pulses/1989896654
            $amazonContexts[$contextKey] = array(
                'id_shop' => $context->shop->id,
                'id_currency' => $context->currency->id,
                'id_country' => $context->country->id,
                'id_language' => $context->language->id,
                'id_employee' => Validate::isLoadedObject($employee) ? $employee->id : $context->employee->id,
                'controller' => $context->controller,
            );

            return (AmazonConfiguration::updateGlobalValue(
                AmazonConstant::PS_CONTEXT_4_10,
                AmazonTools::encode(
                    json_encode($amazonContexts)
                )
            ));
        }

        return true;
    }
}
