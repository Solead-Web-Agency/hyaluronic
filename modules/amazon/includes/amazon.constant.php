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
class AmazonConstant extends AmazonSellerPartnerConstant
{
    const SP_API_ENABLE = true;
    const SP_API_RETURN_QUERY_NAME = 'sp-api-auth'; // custom query param to determine SP API Auth status

    const PLATFORM_EGYPT = 'eg';

    const MERCHANT_ID = 'MERCHANT_ID';

    const TABLE_MARKETPLACE_PRODUCT_ACTION = 'marketplace_product_action';
    const TABLE_MARKETPLACE_PRODUCT_OPTION = 'marketplace_product_option';
    const TABLE_MKP_ORDERS = 'marketplace_orders';
    const TABLE_MKP_ORDER_DETAILS = 'marketplace_order_items';
    const TABLE_AMZ_STATES = 'amazon_states';

    // License ID
    const CONFIG_PS_ORDER_ID = 'PS_ORDER_ID';

    // Global configuration
    const PS_CONTEXT_DATA = 'AMAZON_CONTEXT_DATA';
    const PS_CONTEXT_4_10 = 'AMAZON_CONTEXT_DATA_4_10';

    const GB_CONFIG_INSTANT_TOKEN = 'AMAZON_INSTANT_TOKEN';
    const CONFIG_PS_LANG_ACTIVE = 'ACTIVE';
    const CONFIG_PS_LANG_TO_AMZ_MKP_ID = 'MARKETPLACE_ID';
    const CONFIG_LANG_TO_REGION = 'REGION';
    const CONFIG_TAXES = 'TAXES';
    
    // Legacy configuration, need to migrate if needed
    const CONFIG_TOGGLE_FBA_MULTICHANNEL = 'AMAZON_FBA_MULTICHANNEL';
    const CONFIG_TOGGLE_FBA_MULTICHANNEL_AUTO = 'AMAZON_FBA_MULTICHANNEL_AUTO';

    // Order import
    const CONFIG_OI_TAXES_COMPLY_EU_VAT_RULES = 'OI_TAXES_COMPLY_EU_VAT_RULES';
    const CONFIG_OI_TAXES_ON_BUSINESS_ORDERS = 'TAXES_ON_BUSINESS_ORDERS';
    const CONFIG_OI_TAXES_FORCE_RECALCULATION = 'OI_FORCE_VAT_RECALCULATION';
    const CONFIG_OI_ALLOW_STOCK_ZERO = 'OI_ALLOW_STOCK_ZERO';
    const CONFIG_OI_UNCHECK_PRODUCT_STATUS = 'OI_UNCHECK_PRODUCT_STATUS';
    const CONFIG_OI_CARRIER_PICKUP_POINT = 'OI_CARRIER_PICKUP_POINT';
    const CONFIG_OI_FORMAT_PHONE_FT_ZERO = 'OI_FORMAT_PHONE_FT_ZERO';
    const CONFIG_OI_FORMAT_PHONE_REMOVE_SPACING = 'OI_FORMAT_PHONE_REMOVE_SPACING';
    const CONFIG_OI_REFERENCE_FORMAT = 'OI_REFERENCE_FORMAT';

    // Order fulfillment
    const CONFIG_OU_SENT_STATE = 'SENT_STATE';

    // todo: Migrate to dedicated table
    const CONFIG_LEGACY_PRE_ORDER = 'AMAZON_PREORDER';
    const CONFIG_PRE_ORDER = 'PREORDER';
    const CONFIG_IMPORT_BY_ID = 'IMPORT_BY_ID';

    /**
     * todo: Move from Amazon
     * const ORDER_STATE_STANDARD = 'STD';
     * const ORDER_STATE_BUSINESS = 'BUSINESS';
     * const ORDER_STATE_PREORDER = 'PRE';
     * const ORDER_STATE_PRIMEORDER = 'PRIME';
     */
    const ORDER_INCOMING_TYPE_PREORDER = 'PRE';
    const ORDER_INCOMING_TYPE_FBA = 'FBA';
    const ORDER_INCOMING_TYPE_PRIME = 'PRIME';
    const ORDER_INCOMING_TYPE_BUSINESS = 'BUSINESS';
    const ORDER_INCOMING_TYPE_STANDARD = 'STD';
    const ORDER_INCOMING_TYPE_STANDARD_UNSHIPPED = 'STD_UNSHIPPED';
    const ORDER_SENT_TYPE = 'sent_state';
    const ORDER_CANCELED_TYPE = 'canceled_state';
    /**
     * [
     *   [
     *      [attr => [FBA, Prime, Business]],
     *      [state] => int
     *   ],
     *   ...
     * ]
     */
    const CONFIG_ORDER_STATES_INCOMING_OF_ORDER_ATTRS_COMBINATION = 'OS_INCOMING_OF_ORDER_ATTRS_COMBINATION';

    const CONFIG_CARRIER_MAPPING_OUTGOING = 'CARRIER_OUTGOING';

    const LENGTH_TITLE = 500;
    const LENGTH_BULLET_POINT = 500;
    const LENGTH_DESCRIPTION = 2000;

    const CONFIG_VCS_ENABLED = 'VIDR_ENABLED';
    const CONFIG_VCS_SEND_INVOICE = 'VIDR_SEND_INVOICE';  // Some customers just want to access VCS db, but not upload any invoice
    const CONFIG_VCS_UPDATE_CUSTOMER_VAT_NUMBER = 'VIDR_UPDATE_CUSTOMER_VAT_NUMBER';  // Use data of VCS to update customer VAT number (in address of imported order)
    const CONFIG_VCS_UPDATE_BILLING_ADDRESS = 'VIDR_UPDATE_BILLING_ADDRESS';   // Update entire billing address by VCS data

    const CONFIG_CRON_PARAMS = 'CRON_PARAMS';

    // orders importing order status params
    const OI_ORDER_STATUS_ALL = 'All';
    const OI_ORDER_STATUS_PENDING = 'Pending';
    const OI_ORDER_STATUS_UNSHIPPED = 'Unshipped';
    const OI_ORDER_STATUS_PARTIALLY_SHIPPED = 'PartiallyShipped';
    const OI_ORDER_STATUS_SHIPPED = 'Shipped';

    // Use ps_configuration, json type to overcome unicode and break lines
    const IMPORT_ORDERS_CRON_FAILED_LIST = 'AMAZON_OI_CRON_FAILED_LIST';

    const CONFIG_PRODUCT_UPDATE_CONDITION_IGNORE = 'PU_CONDITION_IGNORE';

    // FBA
    const FBA_MC_CURRENCY = 'FBA_MULTICHANNEL_CURRENCY';   // Currency use while sending external order to Amazon, use from selectPlatforms() if leave empty

    const CONFIG_GET_BY_DIRECT_SQL = 'AMAZON_CONFIG_GET_BY_DIRECT_SQL';

    // Filters
    const FILTER_STATUS = 'STATUS_FILTER';

    const PE_DISCOUNT = 'SPECIALS';

    const CONFIG_MARKETPLACE_COLUMNS = 'EXCLUDED_MARKETPLACE_COLUMNS';

    // All prime settings are combined into 1 configuration
    const CONFIG_PRIME_SETTINGS = 'PRIME';

    const CONFIG_PRIME_CARRIER = 'PRIME_CARRIER';
    const CONFIG_AMZ_PRIME_CARRIER = 'AMAZON_PRIME_CARRIER';
    const CONFIG_AMZ_NOT_DECREASE_STOCK = 'FBA_NOT_DECREASE_STOCK_FOR_SHIPPED';

    // ISO codes for each marketplace
    const AMAZON_BE_ISO = 'be';
    const AMAZON_ZA_ISO = 'za';

    // Number of days to clear logs
    const CONFIG_AMZ_DISABLE_LOGGING = 'DISABLE_LOGGING';
    const CONFIG_AMZ_CLEAR_LOGS_DAY = 'CLEAR_LOGS_DAY';

    // Active use different seller account for marketplace setting
    const CONFIG_AMZ_EU_SEPARATE_SELLER_ACC = 'EU_SEPARATE_SELLER_ACC';

    // Marketplace group setting
    const CUSTOMER_GROUP_NONE_BUSINESS_INCL_VAT = 'none_business_incl_vat';
    const CUSTOMER_GROUP_BUSINESS_VAT_INCL = 'business_vat_incl';
    const CUSTOMER_GROUP_BUSINESS_OUTSIDE_COUNTRY_VAT_EXCL = 'business_outside_country_vat_excl';
    const CONFIG_MKP_CUSTOMER_GROUP = 'MKP_CUSTOMER_GROUP';

    const CONFIG_MKP_INCOMING_CARRIERS = 'MKP_INCOMING_CARRIERS';

    // Product export
    const CONFIG_PE_DELETE_PRODUCTS = 'DELETE_PRODUCTS';

    // Shipping
    const SHIPPING_ALL_CONFIG = 'SHIPPING';

    // VCS
    const CONFIG_MKP_IT_VCS_ALLOW_UPLOAD_NON_BIZ_INVOICE = 'MKP_IT_VCS_UPLOAD_NON_BIZ_INV';
    const CONFIG_AMZ_VCS_CLEAN_UP = 'VCS_CLEAN_UP';

    const CONFIG_MKP_ALTERNATIVE_LANGS = 'ALTERNATIVE_LANGUAGES';
    const CONFIG_INACTIVE_LANGUAGES = 'AMAZON_INACTIVE_LANGUAGES';
    const CONFIG_DISABLE_SPECIAL_FEATURES = 'DISABLE_SPECIAL_FEATURES';
    const CONFIG_PRODUCT_COUNTRY_VAT = 'PRODUCT_COUNTRY_VAT';
    const CONFIG_PRODUCT_COUNTRY_VAT_RATE = 'PRODUCT_COUNTRY_VAT_RATE';

    // FBA
    const FBA_STOCK_BEHAVIOUR_SWITCH = 1;
    const FBA_STOCK_BEHAVIOUR_SYNC = 2;

    // Hooks
    const CONFIG_LIST_HOOKS = 'HOOKS';

    // Images
    const CONFIG_ALTERNATE_PRODUCT_IMAGE = 'ALTERNATE_PRODUCT_IMAGE';

    // Mapping tab settings
    const CONFIG_MAPPING_FEATURE_CUSTOM = 'MAPPING_FEATURE_CUSTOM';
    const CONFIG_ALTERNATE_PRODUCT_IMAGE_SRC = 'ALTERNATE_PRODUCT_IMAGE_SRC';

    //Price rules conversion
    const CONFIG_PRICE_RULE_AFTER_CONVERSION = 'APPLY_PRICE_RULE_AFTER_CONVERSION';

    /**
     * Master Marketplace.
     * This value is not used anymore (AmazonProductOptionsJSON::initAmazon, AmazonTools::selectPlatforms).
     * Add: Use this value for the default mkp selected.
     */
    const CONFIG_MASTER_MARKETPLACE = 'MASTER';

    // Convert data from serialize to json
    const CONFIG_IS_PARSE_JSON = 'IS_PARSE_JSON';


    const REPORT_TITLE_SKU = 'SKU';
    const REPORT_TITLE_ASIN1 = 'ASIN1';
    const REPORT_TITLE_QUANTITY = 'QUANTITY';
}
