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

if (!defined('_PS_VERSION_')) {
    exit;
}

class AmazonAdminConfigPostProcess
{
    /** @var Amazon */
    public $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function postProcess()
    {
        // Dedicated configuration
        $booleanSettings = array(
            array('form_name' => 'parameter_vidr', 'db_key' => AmazonConstant::CONFIG_VCS_ENABLED),
            array('form_name' => 'parameter_vidr_send_invoice', 'db_key' => AmazonConstant::CONFIG_VCS_SEND_INVOICE),
            array('form_name' => 'parameter_vidr_update_customer_vat_number', 'db_key' => AmazonConstant::CONFIG_VCS_UPDATE_CUSTOMER_VAT_NUMBER),
            array('form_name' => 'parameter_vidr_update_billing_address', 'db_key' => AmazonConstant::CONFIG_VCS_UPDATE_BILLING_ADDRESS),
            array('form_name' => 'taxes_comply_eu_vat_rules', 'db_key' => AmazonConstant::CONFIG_OI_TAXES_COMPLY_EU_VAT_RULES),
            array('form_name' => 'taxes_on_business_orders', 'db_key' => AmazonConstant::CONFIG_OI_TAXES_ON_BUSINESS_ORDERS),
            array('form_name' => 'force_vat_recalculation', 'db_key' => AmazonConstant::CONFIG_OI_TAXES_FORCE_RECALCULATION),
            array('form_name' => 'product_update_condition_ignore', 'db_key' => AmazonConstant::CONFIG_PRODUCT_UPDATE_CONDITION_IGNORE),
        );
        foreach ($booleanSettings as $booleanSetting) {
            $value = (bool)AmazonTools::getValue($booleanSetting['form_name']);
            AmazonConfiguration::updateValue($booleanSetting['db_key'], $value);
        }

        // Array configuration
        $arraySettings = array(
            array('form_name' => 'prime_config', 'db_key' => AmazonConstant::CONFIG_PRIME_SETTINGS),
        );
        foreach ($arraySettings as $arraySetting) {
            $value = (array)AmazonTools::getValue($arraySetting['form_name']);
            AmazonConfiguration::updateValue($arraySetting['db_key'], $value);
        }

        // String / number configuration
        $stringSettings = array(
            array('form_name' => 'ps_order_id', 'db_key' => AmazonConstant::CONFIG_PS_ORDER_ID),
        );
        foreach ($stringSettings as $stringSetting) {
            $value = AmazonTools::getValue($stringSetting['form_name']);
            AmazonConfiguration::updateValue($stringSetting['db_key'], $value);
        }

        /* START Override authorize */
        $is_override_auths        = Tools::getValue('is_override_auth');
        $override_mkp_ids         = Tools::getValue('override_mkp_id');
        $override_seller_ids      = Tools::getValue('override_seller_id');
        $override_refresh_tokens  = Tools::getValue('override_refresh_token');

        foreach (AmazonTools::languages() as $language) {
            $id_lang = $language['id_lang'];

            /*Override authorize */
            $isUpdateAuth         = isset($is_override_auths[$id_lang]) ? $is_override_auths[$id_lang] : '';
            $overrideMkpId        = isset($override_mkp_ids[$id_lang]) ? $override_mkp_ids[$id_lang] : '';
            $overrideSellerId     = isset($override_seller_ids[$id_lang]) ? $override_seller_ids[$id_lang] : '';
            $overrideRefreshToken = isset($override_refresh_tokens[$id_lang]) ? $override_refresh_tokens[$id_lang] : '';
            if ($isUpdateAuth) {
                AmazonSPAuthPS::updateAuthValues($overrideSellerId, $overrideRefreshToken, $overrideMkpId);
            }
        }
        /* END Override authorize */
        
        $this->mkpCustomerGroup();

        // todo: Continue to migrate carrier mapping outgoing. Currently, always save new config, prepare to transform
        // todo: Possible to validate input mapping
        $this->module->migrationManager->migrateDuringSaveConfiguration();
    }

    private function mkpCustomerGroup() {
        /* Marketplace customer groups */
        $mkp_groups = (array)Tools::getValue('mkp_group');

        foreach (AmazonTools::languages() as $language) {
            $id_lang = $language['id_lang'];
            /* START: Marketplace customer groups */
            if (isset($mkp_groups[$id_lang])) {
                $mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_NONE_BUSINESS_INCL_VAT] = isset($mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_NONE_BUSINESS_INCL_VAT]) ? $mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_NONE_BUSINESS_INCL_VAT] : '';
                $mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_BUSINESS_VAT_INCL] = isset($mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_BUSINESS_VAT_INCL]) ? $mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_BUSINESS_VAT_INCL] : '';
                $mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_BUSINESS_OUTSIDE_COUNTRY_VAT_EXCL] = isset($mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_BUSINESS_OUTSIDE_COUNTRY_VAT_EXCL]) ? $mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_BUSINESS_OUTSIDE_COUNTRY_VAT_EXCL] : '';
            } else {
                $mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_NONE_BUSINESS_INCL_VAT] = '';
                $mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_BUSINESS_VAT_INCL] = '';
                $mkp_groups[$id_lang][AmazonConstant::CUSTOMER_GROUP_BUSINESS_OUTSIDE_COUNTRY_VAT_EXCL] = '';
            }
            /* END: Marketplace customer groups */
        }

        /* Marketplace customer groups */
        AmazonConfiguration::updateValue(AmazonConstant::CONFIG_MKP_CUSTOMER_GROUP, $mkp_groups);
    }
}
