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

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once 'classes/RouteTools.php';
include_once 'classes/RouteappFee.php';
include_once 'classes/RouteappShipment.php';
include_once 'classes/RouteTax.php';
include_once 'classes/RouteSetup.php';
include_once 'classes/RouteWidget.php';
include_once 'classes/RouteMerchant.php';
include_once 'classes/RouteQuote.php';
include_once 'classes/RouteHelper.php';
include_once 'classes/RouteOrder.php';
include_once 'classes/RouteShipment.php';
include_once 'classes/RouteThankYouPageAsset.php';

class Route extends Module
{
    const PREFIX = 'route_';

    /**
     * List of hooks used in this Module
     */
    public $hooks = [
        'displayBeforeCarrier',
        'displayAdminOrder',
        'actionOrderStatusPostUpdate',
        'actionOrderStatusUpdate',
        'header',
        'actionFrontControllerSetMedia',
        'actionBeforeCartUpdateQty',
        'displayReassurance',
        'displayInvoiceLegalFreeText',
    ];

    /**
     * Route constructor.
     */
    public function __construct()
    {
        $this->name = 'route';
        $this->bootstrap = true;
        $this->tab = 'shipping_logistics';
        //For how to upgrade versions, see https://devdocs.prestashop.com/1.7/modules/creation/enabling-auto-update/
        $this->version = '1.0.8';
        $this->author = 'Route';
        $this->module_key = '7fab3861205c4188d9d6df5d67a35832';

        parent::__construct();

        $this->displayName = $this->l('Route');
        $this->description =
            $this->l('Route has developed a PrestaShop Addon that adds secure shipping insurance to your orders.');

        if (!Configuration::get(RouteSetup::ROUTE_MERCHANT_ID) ||
            !Configuration::get(RouteSetup::ROUTE_SECRET_TOKEN_OPTION)) {
            $this->warning = $this->l('Login credentials not set!');
        }

        $this->ps_versions_compliancy = [
            'min' => '1.7.3.0',
            'max' => _PS_VERSION_,
        ];
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        RouteSetup::init();

        return parent::install()
            && $this->setupDb()
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('actionObjectOrderAddAfter')
            && $this->registerHook('displayPDFInvoice')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayAfterCarrier')
            && $this->registerHook('actionPDFInvoiceRender')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('actionEmailAddAfterContent')
            && $this->registerHook('actionGetExtraMailTemplateVars')
            && $this->registerHook('displayAdminAfterHeader')
            && $this->registerHook('displayAdminOrder')
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('actionOrderStatusPostUpdate')
            && $this->registerHook('actionAdminOrdersTrackingNumberUpdate');
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        $this->removeConfiguration();

        return parent::uninstall()
            && $this->removeDb();
    }

    /**
     * Remove configuration
     *
     * @return bool
     */
    public function removeConfiguration()
    {
        Configuration::deleteByName(RouteSetup::ROUTE_PUBLIC_TOKEN_OPTION);
        Configuration::deleteByName(RouteSetup::ROUTE_SECRET_TOKEN_OPTION);
        Configuration::deleteByName(RouteSetup::ACTIVATION_LINK);
        Configuration::deleteByName(RouteSetup::REGISTRATION_STEP);
        Configuration::deleteByName(RouteSetup::FAILED_REGISTRATION);
        Configuration::deleteByName(RouteSetup::ROUTE_MERCHANT_ID);
        Configuration::deleteByName(RouteTax::ROUTE_TAX_CLASSES);
        Configuration::deleteByName(RouteTax::ROUTE_TAX_ENABLED);

        return true;
    }

    /**
     * Get onboarding content
     *
     * @param false $bypassSetUpCheck
     *
     * @return string
     */
    public function getContent($bypassSetUpCheck = false)
    {
        $shopContext = Shop::getContext();
        if ($shopContext != Shop::CONTEXT_SHOP) {
            $this->context->smarty->assign('failureStep', '4');

            return $this->fetch('module:route/views/templates/admin/OnboardingError.tpl');
        }
        $output = null;
        if (RouteSetup::isInstalled() || $bypassSetUpCheck) {
            if (Tools::isSubmit('submit' . $this->name)) {
                $merchantId = (string) Tools::getValue('merchantId');
                $publicToken = (string) Tools::getValue('publicToken');
                $secretToken = (string) Tools::getValue('secretToken');
                $routeTaxClass = (string) Tools::getValue('routeTaxClass');
                $routeTaxEnabled = (string) Tools::getValue('options_routeTaxEnabled');
                $frontendCartSubtotalShipping = (string) Tools::getValue('frontend-cart-subtotal-shipping');
                $frontendTotalTaxExclude = (string) Tools::getValue('frontend-total-tax-exclude');
                $frontendTotalTaxInclude = (string) Tools::getValue('frontend-total-tax-include');
                $frontendTaxes = (string) Tools::getValue('frontend-taxes');
                $frontendMinimumFractionDigits = (int) Tools::getValue('frontend-minimum-fraction-digits');
                $frontendMaximumFractionDigits = (int) Tools::getValue('frontend-maximum-fraction-digits');

                if (!$publicToken || empty($publicToken)) {
                    $output .= $this->displayError($this->l('Invalid public token value'));
                } elseif (!$secretToken ||
                    empty($secretToken)
                ) {
                    $output .= $this->displayError($this->l('Invalid secret token value'));
                } elseif (!$merchantId ||
                    empty($merchantId)
                ) {
                    $output .= $this->displayError($this->l('Invalid Merchant Id value'));
                } else {
                    RouteSetup::setMerchantId($merchantId);
                    RouteSetup::setPublicKey($publicToken);
                    RouteSetup::setSecretKey($secretToken);
                    RouteTax::setTaxClasses($routeTaxClass);
                    RouteTax::setRouteTaxEnabled($routeTaxEnabled);
                    RouteSetup::setFrontendCartSubtotalShipping($frontendCartSubtotalShipping);
                    RouteSetup::setFrontendTotalTaxExclude($frontendTotalTaxExclude);
                    RouteSetup::setFrontendTotalTaxInclude($frontendTotalTaxInclude);
                    RouteSetup::setFrontendTaxes($frontendTaxes);
                    RouteSetup::setFrontendMinimumFractionDigits($frontendMinimumFractionDigits);
                    RouteSetup::setFrontendMaximumFractionDigits($frontendMaximumFractionDigits);

                    RouteSetup::setRegistrationFailedAs(RouteSetup::REGISTRATION_STEP_USER_LOGIN_SUCCESS);
                    RouteSetup::setAsInstalled();
                    $output .= $this->displayConfirmation($this->l('Settings updated'));
                }
            }

            return $output . $this->displaySettingsForm();
        } else {
            if (RouteSetup::getRegistrationFailedAs() == RouteSetup::FAILED_REGISTRATION_STEP_USER_DUPLICATED) {
                //display settings form instead login form
                return $this->getContent(true);
            }
            $registrationStatus = RouteSetup::getRegistrationFailedAs();

            if (isset($registrationStatus) && $registrationStatus === '0') {
                RouteSetup::setAsInstalled();
                $link = RouteSetup::getActivationLink();

                return $link == '' ? '' : '<iframe src="' . $link . '" style="width:100%;height:740px;"></iframe>';
            }
            $failureStep = Tools::substr(RouteSetup::getRegistrationFailedAs(), 0, 1);
            if ($failureStep == '') {
                RouteSetup::init();

                return $this->getContent();
            } else {
                $this->context->smarty->assign('failureStep', $failureStep);

                return $this->fetch('module:route/views/templates/admin/OnboardingError.tpl');
            }
        }
    }

    /**
     * Display login form
     *
     * @return string
     */
    public function displayLoginForm()
    {
        // Get default language
//        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm = [];
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Username'),
                    'name' => 'userName',
                    'size' => 20,
                    'required' => true,
                ],
                [
                    'type' => 'password',
                    'label' => $this->l('Password'),
                    'name' => 'password',
                    'size' => 20,
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Login'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
//        $helper->default_form_language = $defaultLang;
//        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ],
        ];

        // Load current values
        $user = RouteSetup::getCurrentUser();
        $helper->fields_value['userName'] = $user->email;
        $helper->fields_value['password'] = '';

        return $helper->generateForm($fieldsForm);
    }

    /**
     * Display settings form
     *
     * @return string
     */
    public function displaySettingsForm()
    {
        // Init Fields form array
        $fieldsForm = [];
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Merchant ID'),
                    'name' => 'merchantId',
                    'size' => 20,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Public token'),
                    'name' => 'publicToken',
                    'size' => 20,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Secret token'),
                    'name' => 'secretToken',
                    'size' => 20,
                    'required' => true,
                ],
                [
                    'type' => 'checkbox',
                    'label' => $this->l('Is Route Fee taxable'),
                    'name' => 'options',
                    'values' => [
                        'query' => [
                            [
                                'id' => 'routeTaxEnabled',
                                'name' => $this->l('When enabled, Route fee will be taxable'),
                                'val' => '1',
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Tax Class'),
                    'name' => 'routeTaxClass',
                    'required' => false,
                    'options' => [
                        'query' => RouteTax::getIdTaxRulesGroup(),
                        'id' => 'value',
                        'name' => 'name',
                    ],
                ],
            ],
        ];
        $fieldsForm[1]['form'] = [
            'legend' => [
                'title' => $this->l('Advanced Settings (Frontend Theme)'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Cart Subtotal Shipping'),
                    'desc' => $this->l('Selector for cart subtotal element on checkout'),
                    'name' => 'frontend-cart-subtotal-shipping',
                    'size' => 200,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Total tax exclude'),
                    'desc' => $this->l('Selector for total tax exclude element on checkout'),
                    'name' => 'frontend-total-tax-exclude',
                    'size' => 200,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Total tax include'),
                    'desc' => $this->l('Selector for total tax include element on checkout'),
                    'name' => 'frontend-total-tax-include',
                    'size' => 200,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Taxes line'),
                    'desc' => $this->l('Selector for taxes line element on checkout'),
                    'name' => 'frontend-taxes',
                    'size' => 200,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Minimum Fraction Digits'),
                    'desc' => $this->l('Minimum amount of decimals that will be show on frontend'),
                    'name' => 'frontend-minimum-fraction-digits',
                    'size' => 2,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Maximum Fraction Digits'),
                    'desc' => $this->l('Maximum amount of decimals that will be show on frontend'),
                    'name' => 'frontend-maximum-fraction-digits',
                    'size' => 2,
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ],
            'buttons' => [
                [
                    'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'title' => $this->trans('Back to list', [], 'Admin.Actions'),
                    'icon' => 'process-icon-back',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        // Language
//        $helper->default_form_language = $defaultLang;
//        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ],
        ];

        // Load current values
        $helper->fields_value['merchantId'] = RouteSetup::getMerchantId();
        $helper->fields_value['publicToken'] = RouteSetup::getPublicToken();
        $helper->fields_value['secretToken'] = RouteSetup::getSecretToken();
        $helper->fields_value['routeTaxClass'] = RouteTax::getTaxClasses();
        $helper->fields_value['options_routeTaxEnabled'] = RouteTax::getRouteTaxEnabled();
        $helper->fields_value['frontend-cart-subtotal-shipping'] = RouteSetup::getFrontendCartSubtotalShipping();
        $helper->fields_value['frontend-total-tax-exclude'] = RouteSetup::getFrontendTotalTaxExclude();
        $helper->fields_value['frontend-total-tax-include'] = RouteSetup::getFrontendTotalTaxInclude();
        $helper->fields_value['frontend-taxes'] = RouteSetup::getFrontendTaxes();
        $helper->fields_value['frontend-minimum-fraction-digits'] = RouteSetup::getFrontendMinimumFractionDigits();
        $helper->fields_value['frontend-maximum-fraction-digits'] = RouteSetup::getFrontendMaximumFractionDigits();

        return $helper->generateForm($fieldsForm);
    }

    /**
     * Action when order changes status
     *
     * @param $params
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        if (!RouteSetup::isInstalled()) {
            return;
        }

        $status = $params['newOrderStatus']->name;
        $order = RouteHelper::buildRouteOrder((int) $params['id_order']);
        $routeApi = new RouteOrder(
            RouteSetup::getSecretToken(),
            RouteSetup::getEnvironment()
        );

        if ($status === 'Canceled' || $status == 'Payment error' || $status == 'Refunded') {
            $response = $routeApi->cancelOrder($order['source_order_id']);
        } else {
            $response = $routeApi->upsertOrder($order['source_order_id'], $order);
        }
        $accepted_response_statuses = [200, 201];
        if (isset($response['status_code']) && in_array($response['status_code'], $accepted_response_statuses)) {
            try {
                $routeapp_fee = RouteappFee::getByOrderId($order['source_order_number']);
                if ($routeapp_fee) {
                    $routeapp_fee->processed = 1;
                    $routeapp_fee->update();
                } else {
                    $routeapp_fee = new RouteappFee();
                    $routeapp_fee->id_order = (int) $params['id_order'];
                    $routeapp_fee->processed = 1;
                    $routeapp_fee->save();
                }
            } catch (PrestaShopDatabaseException | PrestaShopException $e) {
                RouteSentry::track(
                    'error',
                    'Error trying to mark fee as processed: ' . $e->getMessage(),
                    debug_backtrace(),
                    ['orderId' => $order['source_order_id']]
                );
            }
        }
    }

    /**
     * Action when there is a tracking number update
     *
     * @param $params
     */
    public function hookActionAdminOrdersTrackingNumberUpdate($params)
    {
        if (!RouteSetup::isInstalled()) {
            return;
        }
        $routeApiShipment = new RouteShipment(
            RouteSetup::getSecretToken(),
            RouteSetup::getEnvironment()
        );
        $routeApiOrder = new RouteOrder(
            RouteSetup::getSecretToken(),
            RouteSetup::getEnvironment()
        );

        $shipment = RouteHelper::buildRouteShipment($params['order'], $params['carrier']);
        $shippingNumber = $params['order']->shipping_number;

        //check if we don't have previous shipment record for this order
        $routeShipment = RouteappShipment::getByOrderId((int) $params['order']->id);
        if ($routeShipment && $routeShipment->id) {
            $orderResponse = $routeApiOrder->getOrder($params['order']->reference);
            if (isset($orderResponse['id'])) {
                // cancel all existing shipments for this order, except the new one
                // we will only be upserting latest shipment
                // since PS only allows one shipment per order
                $routeApiShipment->cancelShipment($orderResponse['id'], $params['order']->reference, $shippingNumber);
            }
            //record from custom table
            $routeShipment->delete();
        }

        //create shipment record on custom table
        $routeShipment = new RouteappShipment();
        $routeShipment->id_order = (int) $params['order']->id;
        $routeShipment->id_order_carrier = (int) $params['carrier']->id;
        $routeShipment->tracking_number = $shippingNumber;
        $routeShipment->add();

        //check if order was processed
        $sendShipment = true;
        $routeappFee = RouteappFee::getByOrderId((int) $params['order']->id);
        if (!$routeappFee || $routeappFee->processed == 0) {
            //check on Route API side
            $orderResponse = $routeApiOrder->getOrder($params['order']->reference);
            if (!isset($orderResponse['id'])) {
                // order not found on Route side
                $sendShipment = false;
            } else {
                //update route fee record on custom table
                if (isset($routeappFee->id)) {
                    $routeappFee->processed = 1;
                    $routeappFee->update();
                }
            }
        }

        if ($sendShipment) {
            $response = false;
            if (!isset($shippingNumber) || $shippingNumber == '') {
                $response = $routeApiShipment->cancelShipment($orderResponse['id'], $params['order']->reference);
            } else {
                $response = $routeApiShipment->upsertShipment($shipment['tracking_number'], $shipment, $params['order']->reference);
            }
            $accepted_response_statuses = [200, 201];
            if (isset($response['status_code']) && in_array($response['status_code'], $accepted_response_statuses)) {
                try {
                    $routeShipment->processed = 1;
                    $routeShipment->update();
                } catch (PrestaShopDatabaseException | PrestaShopException $e) {
                    RouteSentry::track(
                        'error',
                        'Error trying to mark shipment as processed: ' . $e->getMessage(),
                        debug_backtrace(),
                        ['orderId' => $routeShipment->id_order]
                    );
                }
            }
        }
    }

    /**
     * Action to show Route on admin orders
     *
     * @param $params
     *
     * @return string
     *
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    public function hookDisplayAdminOrder($params)
    {
        $id_order = $params['id_order'];
        $routeapp_fee = RouteappFee::getByOrderId($id_order);
        if ($routeapp_fee) {
            $display_fee = Tools::displayPrice($routeapp_fee->fee_amount);

            return "<script>
                var replaceInner = function(element, inner) {
                    if(element.hasChildNodes()) {
                        element.removeAttribute('id');
                        replaceInner(element.firstChild, inner);
                    }
                    else {
                        element.parentNode.innerHTML = inner;
                    }
                };
                var getTaxElement = function() {
                    var orderTaxesTotal = document.getElementById('orderTaxesTotal');
                    if (orderTaxesTotal) {
                        return orderTaxesTotal.parentElement;
                    }
                    var totalTaxes = document.getElementById('total_taxes');
                    return totalTaxes;
                };
                window.addEventListener('load', function() {
                    var taxEl = getTaxElement();
                    var routeEl = taxEl.cloneNode(true);
                    var children = routeEl.children;
                    replaceInner(children[0], 'Route Shipping Protection');
                    replaceInner(children[1], '" . $display_fee . "');
                    taxEl.parentElement.insertBefore(routeEl, taxEl);
                });
            </script>";
        }
    }

    /**
     * Action to insert Route on email content
     *
     * @param $params
     */
    public function hookActionEmailAddAfterContent($params)
    {
        if ($params['template'] == 'order_conf') {
            $dom = new DOMDocument();
            $dom->loadHTML($params['template_html']); //Load up the html

            $xpath = new DOMXPath($dom);
            $summaryRows = $xpath->query('//tr[contains(@class,"order_summary")]');
            $refRow = $summaryRows->item(3);
            $routeRow = $refRow->cloneNode(true);

            $firstChild = $routeRow->firstChild;
            $firstChild->textContent = 'Route Shipping Protection';
            $firstChild->nextSibling->nextSibling->textContent = '{routeapp_fee}';
            $refRow->insertBefore($routeRow);

            $params['template_html'] = $dom->saveHtml();
        }
    }

    /**
     * Action to extend email template vars
     *
     * @param $params
     *
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    public function hookActionGetExtraMailTemplateVars($params)
    {
        if ($params['template'] == 'order_conf') {
            if (isset($params['template_vars']['{id_order}'])) {
                $id_order = $params['template_vars']['{id_order}'];
                $routeapp_fee = RouteappFee::getByOrderId($id_order);
                if ($routeapp_fee) {
                    $display_fee = Tools::displayPrice($routeapp_fee->fee_amount);
                    $params['extra_template_vars']['{routeapp_fee}'] = $display_fee;
                } else {
                    $params['extra_template_vars']['{routeapp_fee}'] = 'N/A';
                }
            }
        }
    }

    /**
     * Action to add route fee on admin order grid
     *
     * @param $params
     *
     * @return string
     *
     * @throws PrestaShopException
     */
    public function hookDisplayAdminAfterHeader($params)
    {
        if ('AdminOrders' === Tools::getValue('controller')) {
            $url = $this->context->link->getAdminLink('AdminRouteapp');

            return "<script>
                let getRouteappFees = async function(order_ids) {
                    let result = await $.ajax({
                      type: 'GET',
                      url: '" . $url . "',
                      data: {
                          order_ids: order_ids,
                          action: 'getOrders',
                          ajax: true
                      },
                      success: function (data) {
                        return data;
                      }
                    });
                    return result;
                };
                
                let addRouteFeeCol = function(row, routefee) {
                    var routeCell = row.cells[6];
                    if (routefee)
                        routeCell.innerHTML = routefee;
                    else
                        routeCell.innerHTML = '-';
                };
                
                let getOrderIdFromRow = function(row) {
                    return row.cells[1].innerText
                };
                
                var getOrderTable = function() {
                   var orderGridTable = document.getElementById('order_grid_table');
                   if (orderGridTable) {
                       return orderGridTable;
                   }
                   var tableOrder = document.getElementById('table-order');
                   if (tableOrder) {
                       return tableOrder;
                   }
                };
                var populateBlankRows = function(rows) {
                    for(row of rows) {
                        var newCell = row.insertCell(6);
                        newCell.innerHTML = '-';
                    }
                }

                window.addEventListener('load', function() {
                    let orderTable = getOrderTable();
                    if(orderTable) {
                        let headerRows = orderTable.tHead.rows;
                        let headerRow = headerRows[0];
                        let routeCol = headerRow.cells[6].cloneNode(true);   
                        routeCol.innerHTML = 'Route Shipping';
                        headerRow.insertBefore(routeCol, headerRow.cells[6]);
                        
                        let routeSearchCol = headerRows[1].insertCell(6);
                        routeSearchCol.innerHTML = '-';
                        routeSearchCol.outerHTML = '<th class=\"text-center\">-</th>';
                        routeSearchCol.style.textAlign='center';
                        
                        let body = orderTable.tBodies[0];
                        let rows = body.rows;
                        populateBlankRows(rows);

                        let order_ids = []
                        for (row of rows) {
                            order_ids.push(getOrderIdFromRow(row));
                        }
                        getRouteappFees(order_ids).then((rawRes) => {
                            let orderFeeMap = JSON.parse(rawRes);
                            for(row of rows) {   
                                let order_id = getOrderIdFromRow(row);
                                addRouteFeeCol(row, orderFeeMap[order_id]);
                            }
                        });
                    }
                });
            </script>";
        }
    }

    public function hookDisplayHeader($params)
    {
//        echo 'Can be useful for debugging';
    }

    /**
     * Action to add route on invoice pdf
     *
     * @param $params
     *
     * @return string
     *
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    public function hookDisplayPDFInvoice($params)
    {
        $order_invoice = $params['object'];
        $routeapp_fee = RouteappFee::getByOrderId($order_invoice->id_order);
        $result = '';
        if ($routeapp_fee) {
            $display_fee = Tools::displayPrice($routeapp_fee->fee_amount);

            $result = $result . '<div>*Shipping Costs includes '
                . $display_fee
                . ' for Route Shipping Protection.';

            if ($routeapp_fee->tax_amount > 0.0) {
                $display_tax = Tools::displayPrice($routeapp_fee->tax_amount);
                $result = $result . '<br/>Shipping tax includes '
                    . $display_tax
                    . ' for Route Shipping Protection.</div>';
            } else {
                $result = $result . '</div>';
            }
        }

        return $result;
    }

    /**
     * Action to insert route on invoice pdf
     *
     * @param $params
     */
    public function hookActionPDFInvoiceRender($params)
    {
        foreach ($params['order_invoice_list'] as $order_invoice) {
            $routeapp_fee = RouteappFee::getByOrderId($order_invoice->id_order);
            if ($routeapp_fee) {
                $order_invoice->total_shipping_tax_excl += $routeapp_fee->fee_amount;
                $order_invoice->total_shipping_tax_incl += $routeapp_fee->fee_amount + $routeapp_fee->tax_amount;
            }
        }
    }

    /**
     * Action to show route widget after carrier selection
     *
     * @param $params
     *
     * @return false|string
     *
     * @throws Exception
     */
    public function hookDisplayAfterCarrier($params)
    {
        $widget = new RouteWidget(
            RouteSetup::getSecretToken(),
            RouteSetup::getEnvironment()
        );
        $cart = $this->context->cart;
        $selected = json_encode($widget->selected());
        $currency = Context::getContext()->currency->getDefaultCurrency()->iso_code;
        $subtotal = $cart->getOrderTotal(false, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING, null, null, false);
        $routeTax = RouteTax::getRouteTax($cart->id_address_invoice, $subtotal, $currency);
        $this->context->smarty->assign('subtotal', $subtotal);
        $this->context->smarty->assign('selected', $selected);
        $this->context->smarty->assign('widget', $widget->getRouteWidgetUrl());
        $this->context->smarty->assign('token', RouteSetup::getPublicToken());
        $this->context->smarty->assign('environment', RouteSetup::getEnvironment());
        $this->context->smarty->assign('currency', $currency);
        $this->context->smarty->assign('routeTax', $routeTax);
        $this->context->smarty->assign('frontendCartSubtotalShipping', RouteSetup::getFrontendCartSubtotalShipping());
        $this->context->smarty->assign('frontendTotalTaxExclude', RouteSetup::getFrontendTotalTaxExclude());
        $this->context->smarty->assign('frontendTotalTaxInclude', RouteSetup::getFrontendTotalTaxInclude());
        $this->context->smarty->assign('frontendTaxes', RouteSetup::getFrontendTaxes());
        $this->context->smarty->assign('frontendMinimumFractionDigits', RouteSetup::getFrontendMinimumFractionDigits());
        $this->context->smarty->assign('frontendMaximumFractionDigits', RouteSetup::getFrontendMaximumFractionDigits());
        $this->context->smarty->assign('currencySymbol', Currency::getDefaultCurrency()->symbol);

        $this->context->smarty->assign(
            'routeController',
            Context::getContext()->link->getModuleLink('route', 'route', [], true)
        );

        if ($cart && $widget->hasRoutePlus() && $widget->inMerchantCoverageLimit($subtotal, $currency)) {
            return $this->fetch('module:route/views/templates/hook/shortcode.tpl');
        }

        return false;
    }

    /**
     * Action to show thank you page asset
     *
     * @param $params
     *
     * @return false|string
     *
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'];
        $routeapp_fee = RouteappFee::getByOrderId($order->id);
        $thankPageAsset = new RouteThankYouPageAsset(
            null,
            RouteSetup::getEnvironment()
        );

        if ($routeapp_fee) {
            $route_fee = Tools::displayPrice($routeapp_fee->fee_amount);
            $this->context->smarty->assign('route_fee', $route_fee);
            $this->context->smarty->assign('order_id', $order->id);
            $this->context->smarty->assign('route_thankyou_page_asset', $thankPageAsset->render());

            return $this->display(__FILE__, 'RouteOrderConfirmation.tpl');
        }
    }

    /**
     * Action to calculate route after an order was added
     *
     * @param $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionObjectOrderAddAfter($params)
    {
        $selected = $this->context->cookie->route_insurace_selected;

        if ($selected == 'true') {
            $fee_amount = $this->context->cookie->route_insurace_price;
            $order = $params['object'];
            $fee_tax_amount = RouteTax::calculateTax($fee_amount, $order->id_address_invoice);
            $order->total_paid += $fee_amount + $fee_tax_amount;
            $order->total_paid_tax_incl += $fee_amount + $fee_tax_amount;
            $order->total_paid_tax_excl += $fee_amount;
            $order->update();

            $routeapp_fee = new RouteappFee();
            $routeapp_fee->id_order = (int) $order->id;
            $routeapp_fee->fee_amount = (float) $fee_amount;
            $routeapp_fee->tax_amount = (float) $fee_tax_amount;
            $routeapp_fee->tax_class = RouteTax::getTaxClasses();
            $routeapp_fee->add();
        }
    }

    /**
     * Grab Route module routes
     *
     * @param $params
     *
     * @return array[]
     */
    public function hookModuleRoutes($params)
    {
        return [
            'route-module' => [
                'rule' => 'route/login',
                'keywords' => [],
                'controller' => 'login',
                'params' => [
                    'fc' => 'module',
                    'module' => 'route',
                ],
            ],
        ];
    }

    /**
     * Include custom js on checkout page
     *
     * @param $params
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        // Only on checkout page
        if ('order' === $this->context->controller->php_self) {
            $this->context->controller->registerJavascript(
                'tools',
                'js/tools.js',
                ['position' => 'bottom', 'priority' => 100]
            );
        }
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * Create custom table to store Route Fee
     *
     * @return bool
     */
    public function setupDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'routeapp_fee`( 
            `id_routeapp_fee` INT(11) NOT NULL AUTO_INCREMENT ,
            `id_order` INT(11) NOT NULL UNIQUE, 
            `fee_amount` DECIMAL( 20, 6 ) NOT NULL,
            `tax_amount` DECIMAL( 20, 6 ) NULL,
            `tax_class` VARCHAR(50) NULL,
            `processed` TINYINT(1) DEFAULT 0,
        PRIMARY KEY (`id_routeapp_fee`),
        INDEX `ID_ORDER_IND` (`id_order`));';
        Db::getInstance()->execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'routeapp_shipment`( 
            `id_routeapp_shipment` INT(11) NOT NULL AUTO_INCREMENT ,
            `id_order` INT(11) NOT NULL UNIQUE, 
            `id_order_carrier` INT(11) NOT NULL, 
            `tracking_number` VARCHAR(50) NULL,
            `processed` TINYINT(1) DEFAULT 0,
        PRIMARY KEY (`id_routeapp_shipment`),
        INDEX `ID_ORDER_IND` (`id_order`));';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Drop custom table
     *
     * @return bool
     */
    public function removeDb()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'routeapp_fee`;';

        return Db::getInstance()->execute($sql);
    }

    public function renderForm()
    {
    }
}
