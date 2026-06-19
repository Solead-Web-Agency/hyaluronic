<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @author    Carts Guru <prestashop@carts.guru>
 * @copyright Since 2017 Carts Guru
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

/**
 * Integration controller - Handle CGApi Requests
 */
class CartsGuruAdminModuleFrontController extends ModuleFrontController
{
    const CARTS_GURU_API_CALL_REQUEST = 'admin';

    private $helper;

    private $behaviors = [];

    public function __construct()
    {
        $this->helper = new CGHelper();

        $this->behaviors = [
            'delete-coupons' => new DeleteCoupons(),
            'delete-hooks' => new DeleteHooks(),
            'delete-scripts' => new DeleteScripts(),
            'get-contacts' => new GetContacts(),
            'get-coupons' => new GetCoupons(),
            'get-hooks' => new GetHooks(),
            'get-orders' => new GetOrders(),
            'get-scripts' => new GetScripts(),
            'post-coupons' => new PostCoupons(),
            'post-hooks' => new PostHooks(),
            'post-scripts' => new PostScripts(),
        ];

        parent::__construct();
    }

    public function display()
    {
        echo 'Carts Guru - Admin';

        return true;
    }

    public function initContent()
    {
        Module::getInstanceByName('cartsguru')->log('Admin Controller');

        $this->ajax = true;

        parent::initContent();

        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $auth = Tools::getValue('auth');
            $resource = Tools::getValue('resource');

            $this->isValidCallMethod($method);
            $this->isValidAuth($auth);
            $behavior = $this->getBehavior($method, $resource);

            switch ($method) {
                case 'GET':
                case 'DELETE':
                    $context = $this->getContext();
                    break;
                case 'POST':
                    $json = Tools::file_get_contents('php://input');
                    $context = json_decode($json);
                    break;
                default:
                    $context = null;
            }

            $behavior->execute($context);
        } catch (LogicException $e) {
            $message = $e->getMessage();

            Module::getInstanceByName('cartsguru')->log($message, FileLogger::ERROR);

            $result = [];
            $result['errors'] = [];
            $result['errors'][] = $message;

            switch ($message) {
                case 'Bad auth.':
                    header('HTTP/1.1 401 Unauthorized');
                    header('Status: 401 Unauthorized');
                    break;
                default:
                    header('HTTP/1.1 403 Forbidden');
                    header('Status: 403 Forbidden');
                    break;
            }

            $this->errors[] = $message;
        }
    }

    public function displayAjax()
    {
    }

    /**
     * Check if the method is accepted.
     *
     * @param $method string
     *
     * @return true
     *
     * @throws LogicException
     */
    private function isValidCallMethod($method)
    {
        if (!in_array($method, ['GET', 'POST', 'DELETE'])) {
            throw new LogicException("Request method doesn\'t exists.");
        }

        return true;
    }

    /**
     * Check if the authentification is correct.
     *
     * @param $auth string
     *
     * @return true
     *
     * @throws LogicException
     */
    private function isValidAuth($auth)
    {
        Module::getInstanceByName('cartsguru')->log('Auth: ' . $auth);

        $idShopGroup = (int) Shop::getContextShopGroupID();
        $idShop = (int) Shop::getContextShopID();

        $siteId = Configuration::get('CARTS_GURU_SETTINGS_SITE_ID', null, $idShopGroup, $idShop);
        $authKey = Configuration::get('CARTS_GURU_SETTINGS_AUTH_KEY', null, $idShopGroup, $idShop);
        $calcAuth = $this->helper->getHmac($siteId, $authKey);

        if ($calcAuth != $auth) {
            throw new LogicException('Bad auth.');
        }

        return true;
    }

    /**
     * Get a behavior about all the possibilities.
     *
     * @param $method string
     * @param $resource string
     *
     * @return true
     *
     * @throws LogicException
     */
    private function getBehavior($method, $resource)
    {
        $action = Tools::strtolower($method) . '-' . $resource;

        Module::getInstanceByName('cartsguru')->log('Action: ' . $action);

        $behavior = null;
        if (array_key_exists($action, $this->behaviors)) {
            $behavior = $this->behaviors[$action];
        }

        if (null === $behavior) {
            throw new LogicException("Behavior doesn\'t exists.");
        }

        return $behavior;
    }

    /**
     * Fixed incompatibility with versions < 1.6.1.0
     *
     * @return mixed
     */
    private function getContext()
    {
        if (version_compare(_PS_VERSION_, '1.6.1.0', '>=')) {
            return Tools::getAllValues();
        } else {
            return $_POST + $_GET;
        }
    }
}
