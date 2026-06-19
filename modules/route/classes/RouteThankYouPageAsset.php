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

class RouteThankYouPageAsset extends RouteApi
{
    /**
     * Route Analytics endpoint
     *
     * @var string
     */
    const CDN_ROUTE_ANALYTICS = 'https://cdn.routeapp.io/route-analytics/route-analytics.js';

    public function __construct($token = '', $env = '', $handler = null)
    {
        if (isset($handler)) {
            $this->handler = $handler;
        } else {
            $this->handler = new RouteApi($token, $env);
        }
    }

    /**
     * Get asset settings
     *
     * @param $apiHost
     *
     * @return mixed|json string
     */
    public function getAssetSettings($apiHost)
    {
        try {
            $assetSettingsResponse = $this->handler->get("/asset-settings/$apiHost", null, true);
            if (isset($assetSettingsResponse['body']) && !empty($assetSettingsResponse['body'])) {
                return $assetSettingsResponse['body'];
            }
        } catch (Exception $e) {
            $extraData = [
                'shop' => $apiHost,
            ];
            RouteSentry::track('error', 'RouteAPI Asset Settings request error', debug_backtrace(), $extraData);
        }

        return false;
    }

    /**
     * Get Thank You Page asset output
     *
     * @return string Output widget HTML
     */
    public function render()
    {
        $output = '';
        $site = RouteSetup::getShopDomain();
        $assetSettingsResponse = $this->getAssetSettings($site);

        if ($assetSettingsResponse) {
            if (isset($assetSettingsResponse['asset_settings']['asset_live'])) {
                $raw_html = $assetSettingsResponse['asset_settings']['asset_content']['raw_html'];
                $css = $assetSettingsResponse['asset_settings']['asset_content']['css_url'];
                $js = isset($assetSettingsResponse['asset_settings']['asset_content']['js_url']) ? $assetSettingsResponse['asset_settings']['asset_content']['js_url'] : '';
                $output = [];
                if (!empty($js)) {
                    $output[] = '<script type="text/javascript" src="' . $js . '"></script>';
                }
                $output[] = '<link rel="stylesheet" href="' . $css . '" type="text/css" />';
                $output[] = '<script type="text/javascript" src="' . self::CDN_ROUTE_ANALYTICS . '"></script>';
                $output[] = '<div>' . $raw_html . '</div>';
                $output[] = '<div>&nbsp;</div><div>&nbsp;</div><div>&nbsp;</div>';
                $output = implode('', $output);
            }
        }

        return $output;
    }
}
