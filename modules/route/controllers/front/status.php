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

class RouteStatusModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $publicToken = $this->encodeToken(RouteSetup::getPublicToken());
        $secretKey = $this->encodeToken(RouteSetup::getSecretToken());

        $json = [
            'version' => (string) Module::getInstanceByName('route')->version,
            'public_token' => $publicToken,
            'secret_key' => $secretKey,
            'date' => (new \DateTime()),
            'is_taxable' => (string) Tools::getValue('options_routeTaxEnabled'),
            'tax_class' => (string) Tools::getValue('routeTaxClass'),
        ];

        die(Tools::jsonEncode($json));
    }

    private function encodeToken($token)
    {
        return Tools::substr($token, 0, 5) . '...' . Tools::substr($token, -5);
    }

    public function setMedia()
    {
        parent::setMedia();
    }
}
