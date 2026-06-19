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
require_once 'RouteSentry.php';
require_once 'RouteTax.php';

class RouteSetup
{
    const PLATFORM_ID = 'prestashop';
    const REGISTRATION_FIXED = '0';
    const REGISTRATION_STEP_USER_LOGIN_SUCCESS = '1-200';
    const FAILED_REGISTRATION_STEP_USER = '1';
    const FAILED_REGISTRATION_STEP_USER_DUPLICATED = '1-409';
    const FAILED_REGISTRATION_STEP_USER_LOGIN_FAILED = '1-401';
    const FAILED_REGISTRATION_STEP_USER_ACTIVATION = '2';
    const FAILED_REGISTRATION_STEP_MERCHANT = '3';
    const FAILED_REGISTRATION_STEP_MERCHANT_DUPLICATED = '3-409';

    const FAILED_REGISTRATION = 'routeapp_failed_registration';
    const SECRET_TOKEN_OPTION = 'routeapp_secret_token';
    const PUBLIC_TOKEN_OPTION = 'routeapp_public_token';
    const USER_ID = 'routeapp_user_id';
    const REGISTRATION_STEP = 'routeapp_route_registration_step';
    const DASHBOARD_PROD = 'https://dashboard.route.com/login?redirect=onboarding';
    const DASHBOARD_STAGE = 'https://dashboard-stage.route.com/login?redirect=onboarding';
    const REDIRECTED = '2';
    const MODULE_INSTALLED = 'true';
    const ACTIVATION_LINK = 'route_activation_link';

    const ROUTE_SECRET_TOKEN_OPTION = 'PS_ROUTE_SECRET_TOKEN_OPTION';
    const ROUTE_PUBLIC_TOKEN_OPTION = 'PS_ROUTE_PUBLIC_TOKEN_OPTION';
    const ROUTE_MERCHANT_ID = 'PS_ROUTE_MERCHANT_ID';

    const ROUTE_FRONTEND_CART_SUBTOTAL_SHIPPING = 'PS_ROUTE_FRONTEND_CART_SUBTOTAL_SHIPPING';
    const ROUTE_FRONTEND_DEFAULT_CART_SUBTOTAL_SHIPPING = '#cart-subtotal-shipping';
    const ROUTE_FRONTEND_TOTAL_TAX_EXCLUDE = 'PS_ROUTE_FRONTEND_TOTAL_TAX_EXCLUDE';
    const ROUTE_FRONTEND_DEFAULT_TOTAL_TAX_EXCLUDE = '#js-checkout-summary > div.card-block.cart-summary-totals > div:nth-child(1) > span.value';
    const ROUTE_FRONTEND_TOTAL_TAX_INCLUDE = 'PS_ROUTE_FRONTEND_TOTAL_TAX_INCLUDE';
    const ROUTE_FRONTEND_DEFAULT_TOTAL_TAX_INCLUDE = '#js-checkout-summary > div.card-block.cart-summary-totals > div.cart-summary-line.cart-total > span.value';
    const ROUTE_FRONTEND_TAXES = 'PS_ROUTE_FRONTEND_TAXES';
    const ROUTE_FRONTEND_DEFAULT_TAXES = '#js-checkout-summary > div.card-block.cart-summary-totals > div:nth-child(3) > span.value.sub';
    const ROUTE_FRONTEND_MINIMUM_FRACTION_DIGITS = 'PS_ROUTE_FRONTEND_MINIMUM_FRACTION_DIGITS';
    const ROUTE_FRONTEND_DEFAULT_MINIMUM_FRACTION_DIGITS = 2;
    const ROUTE_FRONTEND_MAXIMUM_FRACTION_DIGITS = 'PS_ROUTE_FRONTEND_MAXIMUM_FRACTION_DIGITS';
    const ROUTE_FRONTEND_DEFAULT_MAXIMUM_FRACTION_DIGITS = 2;

    public static function init()
    {
        if (!self::isFreshNewInstallation()) {
            return;
        }
        self::createUser();
        self::setTaxes();
    }

    public static function setTaxes()
    {
        $id_shop = (int) Shop::getContextShopID();

        $sql = new DbQuery();
        $sql->select('id_tax_rules_group, COUNT(*) AS num_with_tax_group');
        $sql->from('carrier_tax_rules_group_shop');
        $sql->where("id_shop = {$id_shop}");
        $sql->groupBy('id_tax_rules_group');
        $sql->orderBy('num_with_tax_group DESC');
        $sql->limit(1);

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        if (count($res) > 0) {
            $routeTaxClass = $res[0]['id_tax_rules_group'];
            RouteTax::setTaxClasses($routeTaxClass);
            RouteTax::setRouteTaxEnabled(true);
        }
    }

    public static function isFreshNewInstallation()
    {
        return !self::hasValidMerchantTokens() &&
          !self::registrationStep();
    }

    public static function getRouteApi($token)
    {
        return new RouteApi($token, self::getEnvironment());
    }

    public static function routeappCreateUser($user)
    {
        $routeApi = self::getRouteApi(null);

        return $routeApi->post('/users', $user, true);
    }

    public static function routeappCreateMerchant($merchant)
    {
        $token = self::getSecretToken();
        $routeApi = self::getRouteApi($token);

        return $routeApi->post('/merchants', $merchant, true);
    }

    /**
     * Prepare user data, call user create request
     * and handle response
     *
     * @return bool
     */
    public static function createUser()
    {
        $response = self::routeappCreateUser(self::getUserData());

        if ($response['status_code'] !== 200 && $response['status_code'] !== 201) {
            $registrationCode = $response['status_code'] === 409 ?
                    self::FAILED_REGISTRATION_STEP_USER_DUPLICATED :
                    self::FAILED_REGISTRATION_STEP_USER;

            self::setRegistrationFailedAs($registrationCode);
            $extraData = [
                'params' => self::getUserData(),
                'method' => 'POST',
                'endpoint' => 'users',
            ];
            RouteSentry::track('error', 'Registration failed - ' . $registrationCode, debug_backtrace(), $extraData);

            return false;
        }

        if (self::registerUser($response)) {
            self::setRegistrationFailedAs(self::REGISTRATION_FIXED);

            return true;
        }
    }

    /**
     * @param $data
     *
     * @return array
     */
    private static function getUserData()
    {
        $userData = [];
        $userData['name'] = self::getCurrentName();
        $userData['password'] = self::generateTempPass();
        $userData['platform_id'] = self::PLATFORM_ID;
        $userData['phone'] = '';
        $userData['primary_email'] = self::getCurrentEmail();

        return $userData;
    }

    private static function generateTempPass()
    {
        return 'pass' . Tools::substr(hash('sha256', rand()), 0, 10);
    }

    /**
     * @param $response
     * @param $isActive
     *
     * @return bool
     */
    public static function registerUser($response, $isActive = false)
    {
        $response = $response['body'];
        self::setSecretKey($response['token']);

        if (!$isActive) {
            self::activeAccount();
        }

        return self::createMerchant();
    }

    /**
     * Activate User Account
     *
     * @return bool
     */
    public static function activeAccount()
    {
        $token = self::getSecretToken();
        $routeApi = self::getRouteApi($token);
        $response = $routeApi->post('/activate_account', ['email' => self::getCurrentEmail()], true);
        if (!$response || !in_array($response['status_code'], [200, 201])) {
            self::setRegistrationFailedAs(self::FAILED_REGISTRATION_STEP_USER_ACTIVATION);
            $extraData = [
                'params' => self::getCurrentEmail(),
                'method' => 'POST',
                'endpoint' => 'activate_account',
            ];
            RouteSentry::track('error', 'Failed to activate account', debug_backtrace(), $extraData);

            return false;
        }
        $response = $response['body'];
        self::setActivationLink($response['set_password_url']);

        self::setRegistrationFailedAs(self::REGISTRATION_FIXED);

        return true;
    }

    public static function setSecretKey($token)
    {
        Configuration::updateValue(self::ROUTE_SECRET_TOKEN_OPTION, $token);
    }

    public static function setMerchantId($merchantId)
    {
        Configuration::updateValue(self::ROUTE_MERCHANT_ID, $merchantId);
    }

    public static function setPublicKey($token)
    {
        Configuration::updateValue(self::ROUTE_PUBLIC_TOKEN_OPTION, $token);
    }

    private static function setActivationLink($activation_link)
    {
        Configuration::updateValue(self::ACTIVATION_LINK, $activation_link);
    }

    /**
     * @return WP_User
     */
    public static function getCurrentUser()
    {
        return self::getContext()->employee;
    }

    private static function getContext()
    {
        if (class_exists('Context')) {
            return Context::getContext();
        } else {
            $context = new StdClass();
            $context->smarty = $GLOBALS['smarty'];
            $context->cookie = $GLOBALS['cookie'];
            $context->employee = $GLOBALS['employee'];
        }
    }

    /**
     * @return string
     */
    private static function getCurrentEmail()
    {
        $user = self::getCurrentUser();

        return $user->email;
    }

    /**
     * @return string
     */
    private static function getCurrentName()
    {
        $user = self::getCurrentUser();

        return $user->firstname;
    }

    public static function hasValidMerchantTokens()
    {
        if (self::hasMerchantTokens()) {
            $token = self::getSecretToken();
            $routeApi = self::getRouteApi($token);
            $response = $routeApi->get('/merchants', null, true);

            return $response['status_code'] == 200 && count($response['body']) == 1;
        }

        return false;
    }

    public static function hasMerchantTokens()
    {
        return !empty(self::getPublicToken()) &&
          !empty(self::getSecretToken());
    }

    public static function setAsInstalled()
    {
        Configuration::updateValue(self::REGISTRATION_STEP, self::MODULE_INSTALLED);
    }

    public static function setRegistrationFailedAs($step)
    {
        Configuration::updateValue(self::FAILED_REGISTRATION, $step);
    }

    public static function getPublicToken()
    {
        return Configuration::get(self::ROUTE_PUBLIC_TOKEN_OPTION);
    }

    public static function getMerchantId()
    {
        return Configuration::get(self::ROUTE_MERCHANT_ID);
    }

    public static function getSecretToken()
    {
        return Configuration::get(self::ROUTE_SECRET_TOKEN_OPTION);
    }

    public static function getFrontendCartSubtotalShipping()
    {
        return Configuration::get(self::ROUTE_FRONTEND_CART_SUBTOTAL_SHIPPING) ?
            Configuration::get(self::ROUTE_FRONTEND_CART_SUBTOTAL_SHIPPING) :
            self::ROUTE_FRONTEND_DEFAULT_CART_SUBTOTAL_SHIPPING;
    }

    public static function setFrontendCartSubtotalShipping($value)
    {
        Configuration::updateValue(self::ROUTE_FRONTEND_CART_SUBTOTAL_SHIPPING, $value);
    }

    public static function getFrontendTotalTaxExclude()
    {
        return Configuration::get(self::ROUTE_FRONTEND_TOTAL_TAX_EXCLUDE) ?
            Configuration::get(self::ROUTE_FRONTEND_TOTAL_TAX_EXCLUDE) :
            self::ROUTE_FRONTEND_DEFAULT_TOTAL_TAX_EXCLUDE;
    }

    public static function setFrontendTotalTaxExclude($value)
    {
        Configuration::updateValue(self::ROUTE_FRONTEND_TOTAL_TAX_EXCLUDE, $value);
    }

    public static function getFrontendTotalTaxInclude()
    {
        return Configuration::get(self::ROUTE_FRONTEND_TOTAL_TAX_INCLUDE) ?
            Configuration::get(self::ROUTE_FRONTEND_TOTAL_TAX_INCLUDE) :
            self::ROUTE_FRONTEND_DEFAULT_TOTAL_TAX_INCLUDE;
    }

    public static function setFrontendTotalTaxInclude($value)
    {
        Configuration::updateValue(self::ROUTE_FRONTEND_TOTAL_TAX_INCLUDE, $value);
    }

    public static function getFrontendTaxes()
    {
        return Configuration::get(self::ROUTE_FRONTEND_TAXES) ?
            Configuration::get(self::ROUTE_FRONTEND_TAXES) :
            self::ROUTE_FRONTEND_DEFAULT_TAXES;
    }

    public static function setFrontendTaxes($value)
    {
        Configuration::updateValue(self::ROUTE_FRONTEND_TAXES, $value);
    }

    public static function getFrontendMinimumFractionDigits()
    {
        return Configuration::get(self::ROUTE_FRONTEND_MINIMUM_FRACTION_DIGITS) ?
            Configuration::get(self::ROUTE_FRONTEND_MINIMUM_FRACTION_DIGITS) :
            self::ROUTE_FRONTEND_DEFAULT_MINIMUM_FRACTION_DIGITS;
    }

    public static function setFrontendMinimumFractionDigits($value)
    {
        Configuration::updateValue(self::ROUTE_FRONTEND_MINIMUM_FRACTION_DIGITS, $value);
    }

    public static function getFrontendMaximumFractionDigits()
    {
        return Configuration::get(self::ROUTE_FRONTEND_MAXIMUM_FRACTION_DIGITS) ?
            Configuration::get(self::ROUTE_FRONTEND_MAXIMUM_FRACTION_DIGITS) :
            self::ROUTE_FRONTEND_DEFAULT_MAXIMUM_FRACTION_DIGITS;
    }

    public static function setFrontendMaximumFractionDigits($value)
    {
        Configuration::updateValue(self::ROUTE_FRONTEND_MAXIMUM_FRACTION_DIGITS, $value);
    }

    public static function registrationStep()
    {
        return Configuration::get(self::REGISTRATION_STEP);
    }

    /**
     * Return the current environment
     *
     * @return string
     */
    public static function getEnvironment()
    {
        return getenv('APP_ENV') === 'stage' ? 'stage' : 'production';
    }

    public static function isInstalled()
    {
        return self::registrationStep() === self::MODULE_INSTALLED;
    }

    public static function hasUserLoginSucceed()
    {
        return self::getRegistrationFailedAs() == self::REGISTRATION_STEP_USER_LOGIN_SUCCESS;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public static function getMerchantData()
    {
        $merchantData = [];
        $merchantData['platform_id'] = self::PLATFORM_ID;
        $merchantData['source'] = self::PLATFORM_ID;
        $merchantData['store_domain'] = self::getShopDomain();
        $merchantData['store_name'] = self::getBlogName();
        $merchantData['deal_size_order_count'] = self::getCalculateDealSize();
        $merchantData['country'] = self::getStoreCountry();
        $merchantData['currency'] = self::getCurrency();
        $merchantData['status'] = 'Active';

        return $merchantData;
    }

    private static function getBlogName()
    {
        return Configuration::get('PS_SHOP_NAME');
    }

    private static function getStoreCountry()
    {
        $context = self::getContext();

        return $context->country->iso_code;
    }

    public static function getCurrency()
    {
        global $currency;

        return isset($currency->iso_code) ? $currency->iso_code : 'USD';
    }

    public static function getCurrencySymbol()
    {
        global $currency;

        return $currency->symbol;
    }

    public static function getLanguageLocale()
    {
        global $language;

        return $language->locale;
    }

    public static function getShopDomain()
    {
        return Configuration::get('PS_SHOP_DOMAIN');
    }

    /**
     * Prepare merchant data, call merchant create request
     * and handle response
     *
     * @return bool
     */
    public static function createMerchant()
    {
        $response = null;

        $merchant_data = self::getMerchantData();
        $response = self::routeappCreateMerchant($merchant_data);

        if ($response['status_code'] !== 200 && $response['status_code'] !== 201 && $response['status_code'] !== 409) {
            self::setRegistrationFailedAs(
                $response['status_code'] === 409 ?
                    self::FAILED_REGISTRATION_STEP_MERCHANT_DUPLICATED :
                    self::FAILED_REGISTRATION_STEP_MERCHANT
            );
            RouteSentry::track('error', 'Failed to create merchant', debug_backtrace(), [
                'response' => $response,
            ]);

            return false;
        }

        if (!empty($response)) {
            $response = isset($response['body']) ? $response['body'] : '';

            self::setPublicKey(isset($response['public_api_key']) ? $response['public_api_key'] : '');
            self::setSecretKey(isset($response['prod_api_secret']) ? $response['prod_api_secret'] : '');
            self::setMerchantId(isset($response['id']) ? $response['id'] : '');
        }

        return true;
    }

    public static function getActivationLink()
    {
        return Configuration::get(self::ACTIVATION_LINK);
    }

    public static function getRegistrationFailedAs()
    {
        return Configuration::get(self::FAILED_REGISTRATION);
    }

    /**
     * @param $response
     *
     * @return bool
     */
    public static function merchantCanBeUpdated($response)
    {
        return (isset($response->platform_id) && Tools::strtolower($response->platform_id) == 'email') ||
            (isset($response->status) && Tools::strtolower($response->status) != 'active');
    }

    /**
     * Prepare user data, call user create request
     * and handle response
     *
     * @return bool
     */
    public static function registerUserLogin($username, $password)
    {
        $response = self::routeappUserLogin($username, $password);

        if ($response['status_code'] !== 200) {
            self::setRegistrationFailedAs(self::FAILED_REGISTRATION_STEP_USER_LOGIN_FAILED);
            $extraData = [
                'params' => ['username' => $username, 'password' => 'XXXXX'],
                'method' => 'POST',
                'endpoint' => 'login',
            ];
            RouteSentry::track('error', 'Failed to register user login', debug_backtrace(), $extraData);

            return false;
        }

        if (self::registerUser($response, true)) {
            self::setRegistrationFailedAs(self::REGISTRATION_STEP_USER_LOGIN_SUCCESS);
            self::setAsInstalled();

            return true;
        }
    }

    /**
     * Create user at Route
     *
     * @param $username
     * @param $password
     *
     * @return array|bool|mixed|object
     */
    public static function routeappUserLogin($username, $password)
    {
        $token = self::getSecretToken();
        $routeApi = self::getRouteApi($token);
        $data = [
            'username' => $username,
            'password' => $password,
        ];

        return $routeApi->post('/login', $data, true);
    }

    public static function getRouteRedirect()
    {
        if (self::hasUserLoginSucceed()) {
            return self::getRouteDashboardLink();
        }

        return self::getActivationLink();
    }

    public static function getRouteDashboardLink()
    {
        return self::getEnvironment() === 'stage' ? self::DASHBOARD_STAGE : self::DASHBOARD_PROD;
    }

    /**
     * Returns the total of orders from the past 30 days
     *
     * @see It needs to return a string because our Route API doesn't support parsing 0 as integer in
     * deal_size_order_count. If we send 0 it will convert the merchant deal_size_order_count field to
     * be null instead of 0.
     *
     * @return string
     */
    public static function getCalculateDealSize()
    {
        $ordersCollection = self::getOrdersFromPastDays(30);

        return (string) $ordersCollection[0]['total'];
    }

    /**
     * Get a list of orders from the past days
     *
     * @param int $days
     *
     * @return array
     */
    public static function getOrdersFromPastDays($days = 30)
    {
        $days = (int) $days;
        $sql = new DbQuery();
        $sql->select('COUNT(id_order) AS total');
        $sql->from('orders');
        $sql->where("date_add BETWEEN CURDATE() - INTERVAL {$days} DAY AND CURDATE()");

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
    }
}
