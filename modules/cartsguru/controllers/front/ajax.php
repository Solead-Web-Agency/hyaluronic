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
 * Ajax Controller for retrieve data with tracker
 */
class CartsGuruAjaxModuleFrontController extends ModuleFrontController
{
    private $helper;

    private $tracker;

    public function __construct()
    {
        parent::__construct();
        $this->helper = new CGHelper();
        $this->tracker = new CGTracker($this->helper, $this->context);
    }

    public function init()
    {
        parent::init();
        header('X-Robots-Tag: noindex, nofollow', true);
    }

    public function initContent()
    {
        parent::initContent();
        $this->ajax = true;
    }

    /**
     * Respond a json.
     *
     * @return string
     *
     * @throws LogicException
     */
    public function displayAjax()
    {
        if (!$this->isTokenValid()) {
            exit('Token is not valid, hack stop');
        }

        if ($this->errors) {
            exit(json_encode(['hasError' => true, 'errors' => $this->errors]));
        }

        if ('getTracker' == Tools::getValue('method')) {
            $this->tracker->loadData();
            $data = $this->tracker->getData();

            ob_end_clean();
            header('Content-Type: application/json');

            $module = Module::getInstanceByName('cartsguru');
            $code = $this->context->smarty->fetch($module->views_url . '/templates/hook/tracker.tpl', $data);
            $this->ajaxRender(json_encode([
                'tracker' => $code,
            ]));
        }

        return true;
    }

    /**
     * @param string|null $value
     * @param string|null $controller
     * @param string|null $method
     *
     * @throws PrestaShopException
     */
    protected function ajaxRender($value = null, $controller = null, $method = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            parent::ajaxRender($value, $controller, $method);

            return;
        }

        if ($controller === null) {
            $controller = get_class($this);
        }

        if ($method === null) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $method = $bt[1]['function'];
        }

        /* @deprecated deprecated since 1.6.1.1 */
        Hook::exec('actionAjaxDieBefore', ['controller' => $controller, 'method' => $method, 'value' => $value]);

        /*
         * @deprecated deprecated since 1.6.1.1
         * use 'actionAjaxDie'.$controller.$method.'Before' instead
         */
        Hook::exec('actionBeforeAjaxDie' . $controller . $method, ['value' => $value]);
        Hook::exec('actionAjaxDie' . $controller . $method . 'Before', ['value' => $value]);
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

        echo $value;
    }
}
