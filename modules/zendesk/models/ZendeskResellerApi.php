<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . 'zendesk/models/ZendeskCoreApi.php';

class ZendeskResellerApi extends ZendeskCoreApi
{
    private $errors = [];

    protected $type = 'reseller';
    protected $domain = 'signup';

    /**
     * Verify if subdomain is available
     *
     * @param string $subdomain Subdomain name
     *
     * @return stdClass|false
     */
    public function verifySubdomainAvailability($subdomain)
    {
        if (!ZendeskCoreApi::isSubDomain($subdomain)) {
            return false;
        }

        $params = [
            'item' => 'accounts/available.json?subdomain=' . $subdomain,
            'action' => 'GET',
        ];

        return $this->sendRequest($params);
    }

    /**
     * Create a trial account
     *
     * @param array $owner Owner
     * @param array $address Address
     * @param array $account Account
     *
     * @return stdClass|false
     */
    public function createTrialAccount($owner = [], $address = [], $account = [], $language = 'en-us')
    {
        if (!is_array($owner)) {
            return false;
        } elseif (!is_array($address)) {
            return false;
        } elseif (!is_array($account)) {
            return false;
        }

        // $account['help_desk_size'] = 'Large group'; // Recommended by API

        $params = [
            'item' => 'accounts',
            'action' => 'POST',
            'data' => [
                'owner' => $owner,
                'address' => $address,
                'account' => $account,
                'language' => $language,
                'partner' => [
                    'name' => 'PrestaShop',
                    'url' => 'http://www.prestashop.com/',
                ],
            ],
        ];

        return $this->sendRequest($params);
    }
}
