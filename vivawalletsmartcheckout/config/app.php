<?php
/**
 * Copyright since 2007 Viva Wallet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@vivawallet.com so we can send you a copy immediately.
 *
 * @author    Viva Wallet <support@vivawallet.com>
 * @copyright Since 2007 Viva Wallet
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

return [
    'required' => [
        'php' => [
            'extensions' => ['curl', 'json'],
            'version' => '7.1',
        ],
    ],
    'prestashop' => [
        'abbreviation' => 'PR',
        'name' => 'PrestaShop',
    ],
    'module' => [
        'attributes' => [
            'name' => 'vivawalletsmartcheckout',
            'bootstrap' => true,
            'ps_versions_range_support' => ['min' => '9.0.0', 'max' => _PS_VERSION_],
        ],
        'enabled' => 'VIVAWALLET_SMART_CHECKOUT_ENABLED',
        'ssl_protected' => true,
        'auto_create_webhook' => true,
    ],
    'admin_tabs' => [
        'refund' => [
            'class_name' => 'AdminVivawalletsmartcheckoutRefund',
            'name' => [
                'en' => 'Refund',
            ],
        ],
        'capture' => [
            'class_name' => 'AdminVivawalletsmartcheckoutCapture',
            'name' => [
                'en' => 'Capture',
            ],
        ],
    ],
    'hooks' => [
        'displayHeader',
        'displayPaymentReturn',
        'paymentOptions',
        'payment',
        'displayPaymentEU',
        'displayAdminOrderLeft',
        'displayAdminOrderMain',
        'actionAdminControllerSetMedia',
        'actionGetAdminOrderButtons',
    ],
    'order' => [
        'states' => [
            'pending' => [
                'field' => 'VIVAWALLET_SMART_CHECKOUT_OS_PENDING',
                'name' => 'Viva Wallet payment on hold',
                'options' => [
                    'paid' => false,
                    'color' => '#ffc978',
                    'logable' => 0,
                ],
            ],
            'partially_refund' => [
                'field' => 'VIVAWALLET_SMART_CHECKOUT_OS_PARTIALLY_REFUNDED',
                'name' => 'Viva Wallet payment partially refunded',
            ],
            'awaiting_payment' => [
                'field' => 'VIVAWALLET_SMART_CHECKOUT_OS_AWAITING_PAYMENT',
                'name' => 'Awaiting Viva payment',
                'options' => [
                    'paid' => false,
                    'color' => '#34209E',
                    'logable' => 0,
                ],
            ],
            'preauthorization' => [
                'field' => 'VIVAWALLET_SMART_CHECKOUT_OS_PREAUTHORIZATION',
                'name' => 'Viva preauthorization payment',
                'options' => [
                    'paid' => false,
                    'color' => '#FFA500',
                ],
            ],
        ],
    ],
    'form' => [
        'fields' => [
            'demo' => [
                'client_id' => 'VIVAWALLET_SMART_CHECKOUT_DEMO_CLIENT_ID',
                'client_secret' => 'VIVAWALLET_SMART_CHECKOUT_DEMO_CLIENT_SECRET',
                'source' => 'VIVAWALLET_SMART_CHECKOUT_DEMO_SOURCE',
            ],
            'live' => [
                'client_id' => 'VIVAWALLET_SMART_CHECKOUT_LIVE_CLIENT_ID',
                'client_secret' => 'VIVAWALLET_SMART_CHECKOUT_LIVE_CLIENT_SECRET',
                'source' => 'VIVAWALLET_SMART_CHECKOUT_LIVE_SOURCE',
            ],
            'demo_mode' => 'VIVAWALLET_SMART_CHECKOUT_DEMO_MODE',
            'title' => 'VIVAWALLET_SMART_CHECKOUT_TITLE',
            'description' => 'VIVAWALLET_SMART_CHECKOUT_DESCRIPTION',
            'preferred_status' => 'VIVAWALLET_SMART_CHECKOUT_PREFERRED_ORDER_STATE',
            'installments' => 'VIVAWALLET_SMART_CHECKOUT_INSTALLMENTS',
            'brand_color' => 'VIVAWALLET_SMART_CHECKOUT_BRAND_COLOR',
            'dynamic_descriptor' => 'VIVAWALLET_SMART_CHECKOUT_DYNAMIC_DESCRIPTOR',
            'transaction_type' => 'VIVAWALLET_SMART_CHECKOUT_TRANSACTION_TYPE',
        ],
        'state_options' => [
            'PS_OS_PAYMENT',
            'PS_OS_PREPARATION',
            'PS_OS_SHIPPING',
            'PS_OS_DELIVERED',
        ],
        'merchant_id' => 'VIVAWALLET_SMART_CHECKOUT_MID',
    ],
    'translation' => [
        'iso_codes' => [
            'bg',
            'cs',
            'da',
            'de',
            'el',
            'en',
            'es',
            'fi',
            'fr',
            'hr',
            'hu',
            'it',
            'nl',
            'pl',
            'pt',
            'ro',
            'sv',
        ],
        'default' => 'en',
    ],
    'viva_payments' => [
        'grant_type' => 'client_credentials',
        'scope' => implode(' ', [
            'urn:viva:payments:core:api:acquiring',
            'urn:viva:payments:core:api:acquiring:transactions',
            'urn:viva:payments:core:api:redirectcheckout',
            'urn:viva:payments:core:api:plugins',
            'urn:viva:payments:core:api:plugins:prestashop',
        ]),
        'token_expiration_time' => 1200,
        'max_webhook_tries' => 3,
        'webhook_events' => [
            'success' => 1796,
            'failure' => 1798,
        ],
        'channel_id' => 'A45C1059-C048-471A-95AE-8F5FF92C16F0',
    ],
    'database' => [
        'tables' => [
            'orders' => 'vivawallet_smart_checkout_orders',
            'transactions' => 'vivawallet_smart_checkout_transactions',
            'transaction_types' => 'vivawallet_smart_checkout_transaction_types',
        ],
    ],
    'logger' => [
        'severity' => 3,
    ],
    'url' => [
        'developer_portal' => 'https://developer.vivawallet.com/plugins/prestashop-smart-checkout',
        'merchant_account' => 'https://members.vivawallet.com/en/signin',
        'register_account' => 'https://app.vivawallet.com/register/?lang=en',
    ],
];
