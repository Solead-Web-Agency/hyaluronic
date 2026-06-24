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

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

use Vivawallet\VivawalletPhp\Api\OrderClient;
use Vivawallet\VivawalletPhp\Application;
use Vivawalletsmartcheckout\Helpers\Config;
use Vivawalletsmartcheckout\Helpers\Database;
use Vivawalletsmartcheckout\Helpers\Order as VivawalletsmartcheckoutOrder;
use Vivawalletsmartcheckout\Loggers\Logger;

class VivaWalletSmartCheckoutCreateOrderModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $amount = (float) $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $currencyCode = $this->context->currency->iso_code;
        $environment = $this->module->getEnvironment();

        $vivaPaymentsOrderCode = $this->getVivaPaymentsOrder(
            (int) $this->context->cart->id,
            $amount,
            $currencyCode,
            $environment
        );
        if (!empty($vivaPaymentsOrderCode)) {
            Database::insertOrder(
                [
                    'cart_id' => (int) $this->context->cart->id,
                    'vivawallet_order_code' => $vivaPaymentsOrderCode,
                    'client_id' => Config::getFromDatabase("app.form.fields.$environment.client_id"),
                    'currency' => $currencyCode,
                    'amount' => $amount,
                    'is_demo' => (bool) Config::getFromDatabase('app.form.fields.demo_mode'),
                    'date_add' => date('Y-m-d H:i:s'),
                    'gmt_date_add' => gmdate('Y-m-d H:i:s'),
                ]
            );
        }

        $brand_color = Shop::isFeatureActive() ? Database::getBrandColorByShopId((int) $this->context->cart->id_shop) : Config::getFromDatabase('app.form.fields.brand_color');

        Tools::redirect(
            !empty($vivaPaymentsOrderCode)
                ? Application::getSmartCheckoutUrl(
                    [
                        'ref' => $vivaPaymentsOrderCode,
                        'color' => !empty($brand_color) ? str_replace('#', '', $brand_color) : '',
                    ],
                    $environment
                )
                : VivawalletsmartcheckoutOrder::getOrderFailureLink()
        );
        exit;
    }

    /**
     * Get viva payments order
     *
     * @param int $cartId id of cart
     * @param float $amount amount of payment
     * @param string $currencyCode currency code
     * @param string $environment environment
     *
     * @return string
     */
    private function getVivaPaymentsOrder(
        int $cartId,
        float $amount,
        string $currencyCode,
        string $environment
    ): string {
        $vivaPaymentsOrderClient = new OrderClient($this->module->getBearerAuthentication());
        $countryCode = !empty($this->context->country->iso_code) ? $this->context->country->iso_code : null;
        if (empty($countryCode)
            && isset($this->context->cart->id_address_invoice)
            && $this->context->cart->id_address_invoice > 0
        ) {
            $invoiceAddress = new Address($this->context->cart->id_address_invoice);
            $countryCode = Country::getIsoById($invoiceAddress->id_country);
        }
        $shopName = Shop::isFeatureActive() ? Context::getContext()->shop->name : Configuration::get('PS_SHOP_NAME');
        $arguments = [
            (int) number_format($amount, 2, '', ''),
            $currencyCode,
            [
                'sourceCode' => Config::getFromDatabase("app.form.fields.$environment.source"),
                'payment' => [
                    'maxInstallments' => $this->getMaxInstallments($amount),
                    'dynamicDescriptor' => Config::getFromDatabase('app.form.fields.dynamic_descriptor'),
                    'preauth' => Config::getFromDatabase('app.form.fields.transaction_type') ? true : false,
                ],
                'customer' => array_filter(
                    [
                        'email' => $this->context->customer->email,
                        'fullName' => "{$this->context->customer->firstname} {$this->context->customer->lastname}",
                        'requestLang' => Application::SUPPORTED_LANGUAGES[$this->context->language->iso_code] ?? null,
                        'countryCode' => !empty($countryCode) ? $countryCode : null,
                    ]
                ),
                'messages' => [
                    'customer' => !empty($shopName) ? $shopName : '',
                    'merchant' => "Prestashop Cart Id: $cartId",
                    'tags' => ['prestashop-smart', _PS_VERSION_, $this->module->version],
                ],
            ],
        ];
        $vivaPaymentsOrderResponse = $vivaPaymentsOrderClient->createOrder(...$arguments);
        $vivaPaymentsOrderCode = '';
        if ($vivaPaymentsOrderResponse->isSuccessful() && !empty($vivaPaymentsOrderResponse->getBody()->orderCode)) {
            $vivaPaymentsOrderCode = (string) $vivaPaymentsOrderResponse->getBody()->orderCode;
        } elseif ($vivaPaymentsOrderResponse->getError()) {
            Logger::log(
                [
                    'call' => 'createOrder',
                    'arguments' => $arguments,
                    'response' => $vivaPaymentsOrderResponse->all(),
                ],
                'vivaPayments'
            );
        }

        return $vivaPaymentsOrderCode;
    }

    /**
     * Get max installments
     *
     * @param $amount
     *
     * @return int
     */
    private function getMaxInstallments($amount): int
    {
        $maxInstallments = 1;
        try {
            $installmentsLogic = Config::getFromDatabase('app.form.fields.installments');
            $country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
            $installmentsAllowed = ('GR' === $country->iso_code);
            if ($installmentsAllowed && !empty($installmentsLogic)) {
                $installmentsLogicParts = array_map('trim', explode(',', $installmentsLogic));
                foreach ($installmentsLogicParts as $installmentsLogicPart) {
                    $installmentOptions = array_map('trim', explode(':', $installmentsLogicPart));
                    $installmentsAmount = $installmentOptions[0];
                    $installmentsTerm = $installmentOptions[1];
                    if ($amount >= $installmentsAmount && $installmentsTerm > $maxInstallments) {
                        $maxInstallments = (int) $installmentsTerm;
                    }
                }
            }
        } catch (PrestaShopException $exception) {
            Logger::log(
                ['exception' => $exception->getMessage(), 'category' => 'getMaxInstallments'],
                'generalException'
            );
        }

        return $maxInstallments;
    }
}
