<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2023
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once(dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/Core.php');
class ApiGetPsordersStats extends Core
{
    public function getData()
    {
        $conversionRate = $this->getStatsValues();
        
        $this->response['response'] = array(
            'status' => 'success',
            'refined_data' => $conversionRate
        );

        return $this->fetchJSONResponse();
    }
    
    public function getStatsValues()
    {
        $currency = Currency::getDefaultCurrency();
        $tooltip = null;
        $value = false;
        switch (Tools::getValue('kpi')) {
            case 'conversion_rate':
                $visitors = AdminStatsController::getVisits(
                    true,
                    date('Y-m-d', strtotime('-31 day')),
                    date('Y-m-d', strtotime('-1 day')),
                    false /*'day'*/
                );
                $orders = AdminStatsController::getOrders(
                    date('Y-m-d', strtotime('-31 day')),
                    date('Y-m-d', strtotime('-1 day')),
                    false /*'day'*/
                );

                $visits_sum = $visitors;
                $orders_sum = $orders;
                if ($visits_sum) {
                    $value = round(100 * $orders_sum / $visits_sum, 2);
                } elseif ($orders_sum) {
                    $value = '&infin;';
                } else {
                    $value = 0;
                }
                $value .= '%';

                ConfigurationKPI::updateValue('CONVERSION_RATE', $value);
                ConfigurationKPI::updateValue(
                    'CONVERSION_RATE_EXPIRE',
                    strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')))
                );

                break;

            case 'abandoned_cart':
                $value = AdminStatsController::getAbandonedCarts(
                    date('Y-m-d H:i:s', strtotime('-2 day')),
                    date('Y-m-d H:i:s', strtotime('-1 day'))
                );
                ConfigurationKPI::updateValue('ABANDONED_CARTS', $value);
                ConfigurationKPI::updateValue('ABANDONED_CARTS_EXPIRE', strtotime('+1 hour'));

                break;

            case 'installed_modules':
                $value = AdminStatsController::getInstalledModules();
                ConfigurationKPI::updateValue('INSTALLED_MODULES', $value);
                ConfigurationKPI::updateValue('INSTALLED_MODULES_EXPIRE', strtotime('+2 min'));

                break;

            case 'disabled_modules':
                $value = AdminStatsController::getDisabledModules();
                ConfigurationKPI::updateValue('DISABLED_MODULES', $value);
                ConfigurationKPI::updateValue('DISABLED_MODULES_EXPIRE', strtotime('+2 min'));

                break;

            case 'update_modules':
                $value = AdminStatsController::getModulesToUpdate();
                ConfigurationKPI::updateValue('UPDATE_MODULES', $value);
                ConfigurationKPI::updateValue('UPDATE_MODULES_EXPIRE', strtotime('+2 min'));

                break;

            case 'percent_product_stock':
                $value = AdminStatsController::getPercentProductStock();
                ConfigurationKPI::updateValue('PERCENT_PRODUCT_STOCK', $value);
                ConfigurationKPI::updateValue('PERCENT_PRODUCT_STOCK_EXPIRE', strtotime('+4 hour'));

                break;

            case 'percent_product_out_of_stock':
                $value = AdminStatsController::getPercentProductOutOfStock();
                $tooltip = $value;
                ConfigurationKPI::updateValue('PERCENT_PRODUCT_OUT_OF_STOCK', $value);
                ConfigurationKPI::updateValue('PERCENT_PRODUCT_OUT_OF_STOCK_EXPIRE', strtotime('+4 hour'));

                break;

            case 'product_avg_gross_margin':
                $value = AdminStatsController::getProductAverageGrossMargin();
                $tooltip = $value;
                ConfigurationKPI::updateValue('PRODUCT_AVG_GROSS_MARGIN', $value);
                ConfigurationKPI::updateValue('PRODUCT_AVG_GROSS_MARGIN_EXPIRE', strtotime('+6 hour'));

                break;

            case 'disabled_categories':
                $value = AdminStatsController::getDisabledCategories();
                ConfigurationKPI::updateValue('DISABLED_CATEGORIES', $value);
                ConfigurationKPI::updateValue('DISABLED_CATEGORIES_EXPIRE', strtotime('+2 hour'));

                break;

            case 'disabled_products':
                $value = round(
                        100 * AdminStatsController::getDisabledProducts() / AdminStatsController::getTotalProducts(),
                        2
                    ) . '%';
                $tooltip = $value;
                ConfigurationKPI::updateValue('DISABLED_PRODUCTS', $value);
                ConfigurationKPI::updateValue('DISABLED_PRODUCTS_EXPIRE', strtotime('+2 hour'));

                break;

            case '8020_sales_catalog':
                $value = AdminStatsController::get8020SalesCatalog(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));
                $tooltip = $value;
                $value = $value;
                ConfigurationKPI::updateValue('8020_SALES_CATALOG', $value);
                ConfigurationKPI::updateValue('8020_SALES_CATALOG_EXPIRE', strtotime('+12 hour'));

                break;

            case 'empty_categories':
                $value = AdminStatsController::getEmptyCategories();
                ConfigurationKPI::updateValue('EMPTY_CATEGORIES', $value);
                ConfigurationKPI::updateValue('EMPTY_CATEGORIES_EXPIRE', strtotime('+2 hour'));

                break;

            case 'customer_main_gender':
                $value = AdminStatsController::getCustomerMainGender();

                if ($value === false) {
                    $value = 'No customers';
                } elseif ($value['type'] == 'female') {
                    $value = $value;
                } elseif ($value['type'] == 'male') {
                    $value = $$value;
                } else {
                    $value = $value;
                }

                ConfigurationKPI::updateValue('CUSTOMER_MAIN_GENDER', [$this->context->language->id => $value]);
                ConfigurationKPI::updateValue(
                    'CUSTOMER_MAIN_GENDER_EXPIRE',
                    [$this->context->language->id => strtotime('+1 day')]
                );

                break;

            case 'avg_customer_age':
                $value = $value;
                ConfigurationKPI::updateValue('AVG_CUSTOMER_AGE', [$this->context->language->id => $value]);
                ConfigurationKPI::updateValue(
                    'AVG_CUSTOMER_AGE_EXPIRE',
                    [$this->context->language->id => strtotime('+1 day')]
                );

                break;

            case 'pending_messages':
                $value = (int) AdminStatsController::getPendingMessages();
                ConfigurationKPI::updateValue('PENDING_MESSAGES', $value);
                ConfigurationKPI::updateValue('PENDING_MESSAGES_EXPIRE', strtotime('+5 min'));

                break;

            case 'avg_msg_response_time':
                $value =AdminStatsController::getAverageMessageResponseTime(
                    date('Y-m-d', strtotime('-31 day')),
                    date('Y-m-d', strtotime('-1 day'))
                );
                ConfigurationKPI::updateValue('AVG_MSG_RESPONSE_TIME', $value);
                ConfigurationKPI::updateValue('AVG_MSG_RESPONSE_TIME_EXPIRE', strtotime('+4 hour'));

                break;

            case 'messages_per_thread':
                $value = round(
                    AdminStatsController::getMessagesPerThread(
                        date('Y-m-d', strtotime('-31 day')),
                        date('Y-m-d', strtotime('-1 day'))
                    ),
                    1
                );
                ConfigurationKPI::updateValue('MESSAGES_PER_THREAD', $value);
                ConfigurationKPI::updateValue('MESSAGES_PER_THREAD_EXPIRE', strtotime('+12 hour'));

                break;

            case 'newsletter_registrations':
                $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
                $moduleManager = $moduleManagerBuilder->build();

                $value = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                    '
                SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . 'customer`
                WHERE newsletter = 1
                ' . Shop::addSqlRestriction(Shop::SHARE_ORDER)
                );
                if ($moduleManager->isInstalled('ps_emailsubscription')) {
                    $value += Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        '
                    SELECT COUNT(*)
                    FROM `' . _DB_PREFIX_ . 'emailsubscription`
                    WHERE active = 1
                    ' . Shop::addSqlRestriction(Shop::SHARE_ORDER)
                    );
                }

                ConfigurationKPI::updateValue('NEWSLETTER_REGISTRATIONS', $value);
                ConfigurationKPI::updateValue('NEWSLETTER_REGISTRATIONS_EXPIRE', strtotime('+6 hour'));

                break;

            case 'enabled_languages':
                $value = Language::countActiveLanguages();
                ConfigurationKPI::updateValue('ENABLED_LANGUAGES', $value);
                ConfigurationKPI::updateValue('ENABLED_LANGUAGES_EXPIRE', strtotime('+1 min'));

                break;

            case 'frontoffice_translations':
                $themes = (new ThemeManagerBuilder($this->context, Db::getInstance()))
                    ->buildRepository()
                    ->getList();
                $languages = Language::getLanguages();
                $total = $translated = 0;
                foreach ($themes as $theme) {
                    /* @var Theme $theme */
                    foreach ($languages as $language) {
                        $kpi_key = substr(strtoupper($theme->getName() . '_' . $language['iso_code']), 0, 16);
                        $total += ConfigurationKPI::get('TRANSLATE_TOTAL_' . $kpi_key);
                        $translated += ConfigurationKPI::get('TRANSLATE_DONE_' . $kpi_key);
                    }
                }
                $value = 0;
                if ($translated) {
                    $value = round(100 * $translated / $total, 1);
                }
                $value .= '%';
                ConfigurationKPI::updateValue('FRONTOFFICE_TRANSLATIONS', $value);
                ConfigurationKPI::updateValue('FRONTOFFICE_TRANSLATIONS_EXPIRE', strtotime('+2 min'));

                break;

            case 'main_country':
                if (!($row = AdminStatsController::getMainCountry(
                    date('Y-m-d', strtotime('-30 day')),
                    date('Y-m-d')
                ))
                ) {
                    $value = 'No orders';
                } else {
                    $country = new Country($row['id_country'], $this->context->language->id);
                    $value = $country;
                }

                ConfigurationKPI::updateValue('MAIN_COUNTRY', [$this->context->language->id => $value]);
                ConfigurationKPI::updateValue(
                    'MAIN_COUNTRY_EXPIRE',
                    [$this->context->language->id => strtotime('+1 day')]
                );

                break;

            case 'orders_per_customer':
                $value = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                    '
                SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . 'customer` c
                WHERE c.active = 1
                ' . Shop::addSqlRestriction()
                );
                if ($value) {
                    $orders = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        '
                    SELECT COUNT(*)
                    FROM `' . _DB_PREFIX_ . 'orders` o
                    WHERE o.valid = 1
                    ' . Shop::addSqlRestriction()
                    );
                    $value = round($orders / $value, 2);
                }

                ConfigurationKPI::updateValue('ORDERS_PER_CUSTOMER', $value);
                ConfigurationKPI::updateValue('ORDERS_PER_CUSTOMER_EXPIRE', strtotime('+1 day'));

                break;

            case 'average_order_value':
                $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                    '
                SELECT
                    COUNT(`id_order`) AS orders,
                    SUM(`total_paid_tax_excl` / `conversion_rate`) AS total_paid_tax_excl
                FROM `' . _DB_PREFIX_ . 'orders`
                WHERE `invoice_date` BETWEEN "' . pSQL(date('Y-m-d', strtotime('-31 day'))) . ' 00:00:00" AND "' . pSQL(
                        date('Y-m-d', strtotime('-1 day'))
                    ) . ' 23:59:59"
                ' . Shop::addSqlRestriction()
                );
                $value = $this->context->getCurrentLocale()->formatPrice(
                    $row['orders'] ? $row['total_paid_tax_excl'] / $row['orders'] : 0,
                    $currency->iso_code
                );
                ConfigurationKPI::updateValue('AVG_ORDER_VALUE', $value);
                ConfigurationKPI::updateValue(
                    'AVG_ORDER_VALUE_EXPIRE',
                    strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')))
                );

                break;

            case 'netprofit_visit':
                $date_from = date('Y-m-d', strtotime('-31 day'));
                $date_to = date('Y-m-d', strtotime('-1 day'));

                $total_visitors = AdminStatsController::getVisits(false, $date_from, $date_to);
                $net_profits = AdminStatsController::getTotalSales($date_from, $date_to);
                $net_profits -= AdminStatsController::getExpenses($date_from, $date_to);
                $net_profits -= AdminStatsController::getPurchases($date_from, $date_to);

                if ($total_visitors) {
                    $value = $this->context->getCurrentLocale()->formatPrice($net_profits / $total_visitors, $currency->iso_code);
                } elseif ($net_profits) {
                    $value = '&infin;';
                } else {
                    $value = $this->context->getCurrentLocale()->formatPrice(0, $currency->iso_code);
                }

                ConfigurationKPI::updateValue('NETPROFIT_VISIT', $value);
                ConfigurationKPI::updateValue(
                    'NETPROFIT_VISIT_EXPIRE',
                    strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')))
                );

                break;

            case 'products_per_category':
                $products = AdminStatsController::getTotalProducts();
                $categories = AdminStatsController::getTotalCategories();
                $value = round($products / $categories);
                ConfigurationKPI::updateValue('PRODUCTS_PER_CATEGORY', $value);
                ConfigurationKPI::updateValue('PRODUCTS_PER_CATEGORY_EXPIRE', strtotime('+1 hour'));

                break;

            case 'top_category':
                if (!($id_category = AdminStatsController::getBestCategory(
                    date('Y-m-d', strtotime('-1 month')),
                    date('Y-m-d')
                ))) {
                    $value = $value;
                } else {
                    $category = new Category($id_category, $this->context->language->id);
                    $value = $category->name;
                }

                ConfigurationKPI::updateValue('TOP_CATEGORY', [$this->context->language->id => $value]);
                ConfigurationKPI::updateValue(
                    'TOP_CATEGORY_EXPIRE',
                    [$this->context->language->id => strtotime('+1 day')]
                );

                break;

            case 'shopping_cart_total':
                $cartId = Tools::getValue('cartId');
                $cart = new Cart((int) $cartId);
                if (Validate::isLoadedObject($cart)) {
                    $value = $this->context->getCurrentLocale()->formatPrice(
                        $cart->getCartTotalPrice(),
                        Currency::getIsoCodeById((int) $cart->id_currency)
                    );
                }
                break;
        }
        if ($value !== false) {
            return [
                'value' => $value,
                'tooltip' => $tooltip,
            ];
        }
        return 'has_errors';
    }


}
