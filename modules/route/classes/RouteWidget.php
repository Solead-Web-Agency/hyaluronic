<?php
/**
 * A Route Prestashop Extension that adds secure shipping
 * protection to your orders
 *
 * Php version 7.0^
 *
 * @author    Route Development Team <dev@routeapp.io>
 * @copyright 2019 Route App Inc. Copyright (c) https://www.routeapp.io/
 * @license   https://www.routeapp.io/merchant-terms-of-use  Proprietary License
 */

require_once 'RouteApi.php';

class RouteWidget extends RouteApi
{
    /**
     * Route Widget (Opt-out/Opt-in) endpoint
     *
     * @var string
     */
    const CDN_ROUTE_WIDGET = 'https://cdn.routeapp.io/route-widget/stable/route-widget-stable.min.js';

    /**
     * Route Widget (Opt-out/Opt-in) endpoint
     *
     * @var string
     */
    const CDN_ROUTE_WIDGET_BASE = 'https://cdn.routeapp.io';

    /*
     * Route Widget supported i18n languages
     *
     * @var array
     * */
    const CDN_ROUTE_WIDGET_I18N = [
        'en-US',
        'pt-BR',
        'pt-PT',
        'fr-FR',
        'fr-CA',
        'sv-SE',
        'ru-RU',
        'no-NO',
        'it-IT',
        'fi-FI',
        'es-ES',
        'de-DE',
        'pl-PL',
    ];

    /**
     * Route Full Coverage Widget endpoint
     *
     * @var string
     */
    const CDN_ROUTE_FULL_COVERAGE_WIDGET =
        'https://cdn.routeapp.io/route-full-coverage-widget/stable/full-coverage-widget-stable.min.js';

    /**
     * Upper Merchant Coverage Limit
     *
     * @var float
     */
    const DEFAULT_MAX_USD_SUBTOTAL_ALLOWED = 5000;
    /**
     * Lower Merchant Coverage Limit
     *
     * @var float
     */
    const DEFAULT_MIN_USD_SUBTOTAL_ALLOWED = 0;

    private $merchant;

    public function __construct($token = '', $env = '', $handler = null)
    {
        if (isset($handler)) {
            $this->handler = $handler;
        } else {
            $this->handler = new RouteApi($token, $env);
        }

        if (empty($this->merchant)) {
            $routeApi = new RouteMerchant($token, $env);
            $this->merchant = $routeApi->getMerchant();
        }
    }

    /**
     * Check if Route Plus settings is activated
     *
     * @since    1.0.0
     *
     * @return bool
     */
    public function isFullCoverage()
    {
        return $this->hasRoutePlus()
            && isset($this->merchant['merchant_preferences']['merchant_supplied_insurance'])
            && $this->merchant['merchant_preferences']['merchant_supplied_insurance'];
    }

    /**
     * Check if Merchant Preferences is Opt In
     *
     * @since    1.0.0
     *
     * @return bool
     */
    public function isOptIn()
    {
        return $this->hasRoutePlus()
            && isset($this->merchant['merchant_preferences']['opt_in'])
            && $this->merchant['merchant_preferences']['opt_in'] == true;
    }

    /**
     * Check if Merchant Preferences is Opt Out
     *
     * @since    1.0.0
     *
     * @return bool
     */
    public function isOptOut()
    {
        return $this->hasRoutePlus()
            && isset($this->merchant['merchant_preferences']['opt_out'])
            && $this->merchant['merchant_preferences']['opt_out'] == true && !$this->isOptIn();
    }

    /**
     * Check if Merchant has Route Plus enabled
     *
     * @since    1.0.0
     *
     * @return bool
     */
    public function hasRoutePlus()
    {
        return isset($this->merchant['has_route_plus']) && $this->merchant['has_route_plus'];
    }

    /**
     * @since    1.0.0
     *
     * @return bool
     */
    public function selected()
    {
        if (Context::getContext()->cookie->__isset('route_insurace_selected')) {
            if (Context::getContext()->cookie->route_insurace_selected == '') {
                return $this->isOptOut();
            }

            return RouteTools::isTrue(Context::getContext()->cookie->route_insurace_selected);
        }

        return $this->isOptOut();
    }

    /**
     * @since    1.0.0
     *
     * @return string
     */
    public function getRouteWidgetUrl()
    {
        $locale = RouteSetup::getLanguageLocale();

        return $this->isFullCoverage() ? $this->getRouteFulLCoverageWidgeti18n($locale) :
            $this->getRouteWidgeti18n($locale);
    }

    /**
     * Return the Route Widget CDN URL based on the locale
     *
     * @since    1.0.5
     *
     * @return string
     */
    public function getRouteWidgeti18n($language = 'en-US')
    {
        if (!in_array($language, self::CDN_ROUTE_WIDGET_I18N)) {
            return self::CDN_ROUTE_WIDGET;
        }

        return sprintf('%s/route-widget/i18n/route-widget.%s.min.js', self::CDN_ROUTE_WIDGET_BASE, $language);
    }

    /**
     * Return the Full Coverage Widget CDN URL based on the locale
     *
     * @since    1.0.6
     *
     * @return string
     */
    public function getRouteFulLCoverageWidgeti18n($language = 'en-US')
    {
        if (!in_array($language, self::CDN_ROUTE_WIDGET_I18N)) {
            return self::CDN_ROUTE_FULL_COVERAGE_WIDGET;
        }

        return sprintf('%s/route-full-coverage-widget/i18n/route-full-coverage-widget.%s.min.js', self::CDN_ROUTE_WIDGET_BASE, $language);
    }

    /**
     * @since    1.0.0
     *
     * @return string
     */
    public function inMerchantCoverageLimit($subtotal, $currency)
    {
        try {
            if (!empty($subtotal)) {
                $routeQuote = new RouteQuote(
                    RouteSetup::getSecretToken(),
                    RouteSetup::getEnvironment()
                );
                $quoteResponse = $routeQuote->getQuote($subtotal, $currency);

                if ($quoteResponse['status_code'] === 200) {
                    $quote = $quoteResponse['body'];
                    $coverageUpperLimit = (float) $quote['coverage_upper_limit'];
                    $coverageLowerLimit = (float) $quote['coverage_lower_limit'];
                    $maxUsdSubtotal = isset($coverageUpperLimit)
                        && $coverageUpperLimit > 0 ? $coverageUpperLimit : self::DEFAULT_MAX_USD_SUBTOTAL_ALLOWED;
                    $minUsdSubtotal = isset($coverageLowerLimit)
                        && $coverageLowerLimit > 0 ? $coverageLowerLimit : self::DEFAULT_MIN_USD_SUBTOTAL_ALLOWED;
                    $subtotalUsd = (float) $quote['subtotal_usd'];

                    return ($minUsdSubtotal < $subtotalUsd) && ($subtotalUsd < $maxUsdSubtotal);
                }
            }
        } catch (Exception $e) {
            $extraData = [
                'params' => [
                    'subtotal' => $subtotal,
                    'currency' => $currency,
                ],
                'method' => 'GET',
                'endpoint' => 'quote',
            ];
            RouteSentry::track('error', 'Error getting the merchant coverage limit', debug_backtrace(), $extraData);
        }

        return false;
    }

    public function getQuote($subtotal, $currency)
    {
        if (!empty($subtotal)) {
            $routeQuote = new RouteQuote(
                RouteSetup::getSecretToken(),
                RouteSetup::getEnvironment()
            );
            $quoteResponse = $routeQuote->getQuote($subtotal, $currency);

            if ($quoteResponse['status_code'] === 200) {
                return $quoteResponse['body']['insurance_price'];
            }
        }

        return 0;
    }
}
