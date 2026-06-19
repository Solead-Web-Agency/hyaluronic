<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @order  Ced
 * @package   CedFruugo
 */

include_once _PS_MODULE_DIR_ . 'cedwish/classes/order.php';
include_once _PS_MODULE_DIR_ . 'cedwish/classes/api.php';

class AdminCedWishOrderController extends ModuleAdminController
{
    public $statuses_array = array();

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'cedwish_order';
        $this->identifier = 'id_cedwish_order';
        $this->_orderBy = 'id_cedwish_order';
        $this->_orderWay = 'DESC';
        $this->className = 'CedWishOrder';
        $this->list_no_link = true;

        $this->addRowAction('view');
        $this->addRowAction('ship');
        $this->addRowAction('cancel');
        $this->addRowAction('refund');
        $this->addRowAction('sync');
        $this->addRowAction('delete');

        parent::__construct();
        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->_select = 'osl.`name` AS `osname`, os.`color` ';

        $this->_join = '
        LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (a.`store_order_id` = o.`id_order`)
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = o.`current_state`)
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` 
        AND osl.`id_lang` = ' . (int)$this->context->language->id . ')';

        $this->bulk_actions = array(
            'sync' => array(
                'text' => $this->l('Sync Status'),
                'icon' => 'icon-refresh',
            ),
            'ship' => array(
                'text' => $this->l('Ship'),
                'icon' => 'icon-truck',
                'confirm' => $this->l('Are you sure, you want to make shipment.')
            ),
            'cancel' => array(
                'text' => $this->l('Cancel'),
                'confirm' => $this->l('Are you sure, you want to cancel the order.'),
                'icon' => 'icon-refresh'
            ),
            'refund' => array(
                'text' => $this->l('Refund'),
                'confirm' => $this->l('Are you sure, you want to refund this order.'),
                'icon' => 'icon-refresh'
            ),
            'delete' => array(
                'text' => $this->l('Delete'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Are you sure, you want to remove this order.')
            ),
        );

        $this->fields_list = array(
            'id_cedwish_order' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'store_order_id' => array(
                'title' => $this->l('Order ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'viewStoreOrder',
            ),
            'marketplace_order_id' => array(
                'title' => $this->l('Marketplace Order ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'released_at' => array(
                'title' => $this->l('Released At'),
                'align' => 'text-center',
                'class' => 'fixed-width-lg',
            ),
            'state' => array(
                'title' => $this->l('Status'),
                'align' => 'text-center',
                'class' => 'fixed-width-sx',
            ),
            'osname' => array(
                'title' => $this->l('Store Order Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname'
            ),
            'wish_order' => array(
                'title' => $this->l('Error'),
                'class' => 'fixed-width-xs',
                'align' => 'text-center',
                'callback' => 'viewOrderButton',
            ),
        );

        if (Tools::getIsset('fetch_order')) {
            $api = new CedWishApi();
            $orders = $api->getPendingOrders();
            if (isset($orders['code']) && ($orders['code']==0) && !empty($orders['data'])) {
                $mpOrder = new CedWishOrder();
                $result = $mpOrder->createOrder($orders['data']);
                if (isset($result['success']) && $result['success']) {
                    $this->confirmations[] = $result['success'] . $this->l(' Orders Fetched Successfully.');
                }
                if (isset($result['error']) && $result['error'] && ($result['error'] != $result['success'])) {
                    $this->confirmations[]
                        = ($result['error'] - $result['success']) . $this->l(' Orders Fetched With Error.');
                }
                if (($result['error']==0) && ($result['error']==0)) {
                    $this->confirmations[] = $this->l('No new order found');
                }
            } elseif (isset($orders['message']) && $orders['message']) {
                $this->errors[] = $orders['message'];
            } else {
                $this->errors[] = $this->l('No orders from Marketplace  .');
            }
        }
    }

    /**
     * Display Sync action link
     * @param string $token the token to add to the link
     * @param int $id the identifier to add to the link
     * @return string
     * @throws SmartyException
     */
    public function displaySyncLink($token = null, $id = 0)
    {
        if (!array_key_exists('Sync Status', self::$cache_lang)) {
            self::$cache_lang['Sync Status'] = ('Sync Status');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&id_wish_order=' . $id .
                '&sync&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Sync Status'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/order/list/list_action_sync.tpl'
        );
    }

    /**
     * Display Cancel action link
     * @param string $token the token to add to the link
     * @param int $id the identifier to add to the link
     * @return string
     * @throws SmartyException
     */
    public function displayCancelLink($token = null, $id = 0)
    {
        if (!array_key_exists('Cancel', self::$cache_lang)) {
            self::$cache_lang['Cancel'] = ('Cancel');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&id_wish_order=' . $id .
                '&cancel&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Cancel'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/order/list/list_action_cancel.tpl'
        );
    }

    /**
     * Display Refund action link
     * @param string $token the token to add to the link
     * @param int $id the identifier to add to the link
     * @return string
     * @throws SmartyException
     */
    public function displayRefundLink($token = null, $id = 0)
    {
        if (!array_key_exists('Refund', self::$cache_lang)) {
            self::$cache_lang['Refund'] = ('Refund');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&id_wish_order=' . $id .
                '&refund&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Refund'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/order/list/list_action_refund.tpl'
        );
    }

    public function initContent()
    {
        parent::initContent();
    }
    /**
     * Display Ship action link
     * @param string $token the token to add to the link
     * @param int $id the identifier to add to the link
     * @return string
     * @throws SmartyException
     */
    public function displayShipLink($token = null, $id = 0)
    {
        if (!array_key_exists('Ship', self::$cache_lang)) {
            self::$cache_lang['Ship'] = ('Ship');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&id_wish_order=' . $id .
                '&ship&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Ship'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/order/list/list_action_ship.tpl'
        );
    }

    public function viewOrderButton($wish_order, $data)
    {
        if (!empty($wish_order)) {
            $data['wish_error'] = $data['order_error'];
            $data['wish_order_id'] = $data['id_cedwish_order'];
            $this->context->smarty->assign(
                $data
            );
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/order/list/view_and_reimport.tpl'
            );
        }
    }

    public function viewStoreOrder($store_order_id, $data)
    {
        if ($store_order_id) {
            $data['store_order_id'] = $store_order_id;
            if (version_compare(_PS_VERSION_, '1.6', '>')) {
                $data['store_order_url'] = $this->context->link->getAdminLink(
                    'AdminOrders',
                    true
                ) . '&vieworder=true&id_order=' . $store_order_id;
            } else {
                $route_params = array();
                if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
                    $route_params = array(
                        'route' => 'admin_orders_view',
                        'orderId' => $store_order_id
                    );
                }
                $data['store_order_url'] = $this->context->link->getAdminLink(
                    'AdminOrders',
                    true,
                    $route_params,
                    array(
                        'orderId' => $store_order_id,
                        'vieworder' => true,
                        'id_order' => $store_order_id
                    )
                );
            }
            $this->context->smarty->assign(
                $data
            );
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/order/list/view_store_order.tpl'
            );
        }
        return $store_order_id;
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['fetch_order'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedWishOrder') . '&fetch_order',
                'desc' => $this->l('Fetch Orders'),
                'icon' => 'process-icon-download'
            );
            $this->page_header_toolbar_btn['fetch_order_by_id'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedWishOrder') . '&addcedwish_order',
                'desc' => $this->l('Fetch Orders By ID'),
                'icon' => 'process-icon-download'
            );
        } else {
            $this->page_header_toolbar_btn['back_to_order'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedWishOrder'),
                'desc' => $this->l('Back To List'),
                'icon' => 'process-icon-back'
            );
        }
        return parent::initPageHeaderToolbar();
    }

    public function renderForm()
    {
        $this->context->smarty->assign(array(
            'wish_order_fetch_token' => $this->token,
        ));
        $form = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ .'cedwish/views/templates/admin/order/form/search_import_order.tpl'
        );
        return $form.parent::renderForm();
    }

    public function ajaxProcessSearchImportOrder()
    {
        $orderId = Tools::getValue('orderId');
        $response = array();
        if ($orderId) {
            $api = new CedWishApi();
            $order = $api->getOrder(array($orderId));
            if (isset($order['code']) && ($order['code']==0) && !empty($order['data'])) {
                $order = $order['data'];
                $result = (new CedWishOrder())->createOrder(array($order));
                if (isset($result['response'][0])) {
                    $response = array(
                        'success' => true,
                        'message' => $result['response'][0],
                    );
                } else {
                    $response = array(
                        'success' => true,
                        'message' => $result[0],
                    );
                }
            } else {
                $response = $order;
            }
        } else {
            $response = array(
                'success' => false,
                'message' => $this->l('Please Enter order id'),
            );
        }
        die(
            json_encode(
                $response
            )
        );
    }

    public function postProcess()
    {
        $response = array();
        $order = new CedWishOrder();
        $id_wish_order = Tools::getValue('id_wish_order');
        if (Tools::isSubmit('cancel') || Tools::isSubmit('refund')) {
            $response = $order->cancelOrder(
                array(
                    $id_wish_order
                )
            );
        }
        if (Tools::isSubmit('ship')) {
            $response = $order->shipOrder(
                array(
                    $id_wish_order
                )
            );
        }
        if (Tools::isSubmit('sync')) {
            $response = $order->syncStatus(
                array(
                    $id_wish_order
                )
            );
        }
        if (!empty($response)) {
            if (isset($response['error']) && !empty($response['error'])) {
                $this->errors[] = implode(", ", $response['error']);
            }
            if (isset($response['success']) && !empty($response['success'])) {
                $this->confirmations[] = implode(", ", $response['success']);
            }
        }
        parent::postProcess();
    }

    public function renderList()
    {
        $this->context->smarty->assign(
            array(
                'wish_order_token' => Tools::getAdminTokenLite('AdminCedWishOrder')
            )
        );
        $list_actions =  $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/order/list/list_actions.tpl'
        );
        return $list_actions.parent::renderList();
    }

    public function renderKpis()
    {
        $kpis = array();
        $time = time();
        $helper = new HelperKpi();
        $helper->id = 'box-total-order';
        $helper->icon = 'icon-list';
        $helper->color = 'color4';
        $helper->title = $this->l('Orders(s)');
        $helper->subtitle = $this->l('Total Orders');
        if (ConfigurationKPI::get('WISH_TOTAL_ORDERS') !== false) {
            $helper->value = ConfigurationKPI::get('WISH_TOTAL_ORDERS');
        }
        $helper->source = $this->context->link->getAdminLink('AdminCedWishOrder')
            . '&ajax=1&action=getKpi&kpi=wish_order_total';
        $helper->refresh = (bool) (ConfigurationKPI::get('WISH_TOTAL_ORDERS_EXPIRE') < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-total-mapped';
        $helper->icon = 'icon-truck';
        $helper->color = 'color3';
        $helper->title = $this->l('Shipped Orders');
        $helper->subtitle = $this->l('Total Shipped Orders');
        if (ConfigurationKPI::get('WISH_TOTAL_SHIPPED_ORDERS') !== false) {
            $helper->value = ConfigurationKPI::get('WISH_TOTAL_SHIPPED_ORDERS');
        }
        $helper->source = $this->context->link->getAdminLink('AdminCedWishOrder')
            . '&ajax=1&action=getKpi&kpi=wish_order_shipped_total';
        $helper->refresh = (bool) (ConfigurationKPI::get('WISH_TOTAL_SHIPPED_ORDERS_EXPIRE') < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-total-facebook';
        $helper->icon = 'icon-money';
        $helper->color = 'color1';
        $helper->title = $this->l('Refunded Orders');
        $helper->subtitle = $this->l('Total Refunded Orders');
        if (ConfigurationKPI::get('WISH_TOTAL_REFUNDED_ORDERS') !== false) {
            $helper->value = ConfigurationKPI::get('WISH_TOTAL_REFUNDED_ORDERS');
        }
        $helper->source = $this->context->link->getAdminLink('AdminCedWishOrder')
            . '&ajax=1&action=getKpi&kpi=wish_order_refunded_total';
        $helper->refresh = (bool) (ConfigurationKPI::get('WISH_TOTAL_REFUNDED_ORDERS_EXPIRE') < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-total-empty-mapped';
        $helper->icon = 'icon-exchange';
        $helper->color = 'color2';
        $helper->title = $this->l('Failed Orders');
        $helper->subtitle = $this->l('Fetch But not created on store');
        if (ConfigurationKPI::get('WISH_TOTAL_FAILED_ORDERS') !== false) {
            $helper->value = ConfigurationKPI::get('WISH_TOTAL_FAILED_ORDERS');
        }
        $helper->source = $this->context->link->getAdminLink('AdminCedWishOrder')
            . '&ajax=1&action=getKpi&kpi=wish_order_failed_total';
        $helper->refresh = (bool) (ConfigurationKPI::get('WISH_TOTAL_FAILED_ORDERS_EXPIRE') < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpiRow();
        $helper->kpis = $kpis;

        return $helper->generate();
    }

    public function renderView()
    {
        $order = $this->loadObject();
        try {
            $store_order = new Order((int)$order->store_order_id);
            $this->context->smarty->assign(
                array(
                    'store_order_id' => $order->store_order_id,
                    'id_cedwish_order' => $order->id,
                    'marketplace_order_id' => $order->marketplace_order_id,
                    'wish_error' => $order->order_error,
                    'released_at' => $order->released_at,
                    'state' => $order->state,
                    'carrier_selected' => CedWishHelper::getWishMappedCarrier($store_order->id_carrier),
                    'country_selected' => Configuration::get('CED_WISH_ORIGIN_COUNTRY'),
                    'shippingCountries' => CedWishHelper::getShippableCountries(),
                    'shippingCarriers' => CedWishHelper::getCarriers(),
                    'wish_order_token' => Tools::getAdminTokenLite('AdminCedWishOrder'),
                    'wish_order' => json_decode(Tools::getDescriptionClean($order->wish_order), true),
                )
            );
            $view = $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/order/form/view.tpl'
            );
            return $view.parent::renderView();
        } catch (PrestaShopDatabaseException $e) {
        } catch (PrestaShopException $e) {
        }
    }

    public function processBulkSync()
    {
        if (!empty($this->boxes)) {
            $order = new CedWishOrder();
            $result = $order->syncStatus($this->boxes);
            if (isset($result['error']) && !empty($result['error'])) {
                foreach ($result['error'] as $error) {
                    $this->errors[] = $error;
                }
            }

            if (isset($result['success']) && !empty($result['success'])) {
                foreach ($result['success'] as $success) {
                    $this->confirmations[] = $success;
                }
            }
        } else {
            $this->errors[] = $this->l('Please Select Orders to sync status');
        }
    }

    public function processBulkCancel()
    {
        if (!empty($this->boxes)) {
            $order = new CedWishOrder();
            $result = $order->cancelOrder($this->boxes);
            if (isset($result['error']) && !empty($result['error'])) {
                foreach ($result['error'] as $error) {
                    $this->errors[] = $error;
                }
            }

            if (isset($result['success']) && !empty($result['success'])) {
                foreach ($result['success'] as $success) {
                    $this->confirmations[] = $success;
                }
            }
        } else {
            $this->errors[] = $this->l('Please Select Orders to sync status');
        }
    }

    public function processBulkRefund()
    {
        if (!empty($this->boxes)) {
            $order = new CedWishOrder();
            $result = $order->cancelOrder($this->boxes);
            if (isset($result['error']) && !empty($result['error'])) {
                foreach ($result['error'] as $error) {
                    $this->errors[] = $error;
                }
            }

            if (isset($result['success']) && !empty($result['success'])) {
                foreach ($result['success'] as $success) {
                    $this->confirmations[] = $success;
                }
            }
        } else {
            $this->errors[] = $this->l('Please Select Orders to sync status');
        }
    }

    public function processBulkShip()
    {
        if (!empty($this->boxes)) {
            $order = new CedWishOrder();
            $result = $order->shipOrder($this->boxes);
            if (isset($result['error']) && !empty($result['error'])) {
                foreach ($result['error'] as $error) {
                    if ($error) {
                        $this->errors[] = $error;
                    }
                }
            }

            if (isset($result['success']) && !empty($result['success'])) {
                foreach ($result['success'] as $success) {
                    $this->confirmations[] = $success;
                }
            }
        } else {
            $this->errors[] = $this->l('Please Select Orders to Ship');
        }
    }

    public function ajaxProcessGetOrderDetails()
    {
        $wish_order_id = Tools::getValue('wish_order_id', 0);
        $order = new CedWishOrder((int)$wish_order_id);
        if ($order && $order->id_cedwish_order) {
            $wish_order = json_decode(Tools::getDescriptionClean($order->wish_order), true);
            die(
                json_encode(
                    array(
                        'success' => true,
                        'message' => $wish_order
                    )
                )
            );
        }
        die(
            json_encode(
                array(
                    'success' => false,
                    'message' => $this->l('Failed to get order details')
                )
            )
        );
    }

    public function ajaxProcessResubmitFeed()
    {
        $response = "json data is incorrect";
        if (Tools::getIsset('feed_content')
            && Tools::getIsset('wish_order_id')
            && Tools::getValue('wish_order_id')
            && Tools::getValue('feed_content')
        ) {
            $wish_order_id = Tools::getValue('wish_order_id');
            $order = Tools::getValue('feed_content');
            $db = Db::getInstance();
            try {
                $orderData = json_decode($order, true);
                if ($orderData === null && json_last_error() !== JSON_ERROR_NONE) {
                    $response = "json data is incorrect";
                } else {
                    if ($wish_order_id
                        && !empty($orderData)
                    ) {
                        $wishOrder = new CedwishOrder();
                        $prestashopOrderId = $wishOrder->createPrestashopOrder($orderData);
                        if ($prestashopOrderId) {
                            $db->update(
                                'cedwish_order',
                                array(
                                    'store_order_id' => (int)$prestashopOrderId,
                                    'wish_order' => pSQL(json_encode($orderData)),
                                    'order_error' => '',
                                ),
                                'id_cedwish_order = ' . (int)$wish_order_id
                            );
                            $response = 'Wish Order With Id ' . $wish_order_id .
                                ' Imported Successfully.';
                            $response = array('success' => true, 'message' => $response);
                        } else {
                            $response = 'Failed to Create Wish Order Please check Error on order grid';
                            $response = array('success' => false, 'message' => $response);
                        }
                    } else {
                        $response = 'Wish Order Already created.';
                        $response = array('success' => false, 'message' => $response);
                    }
                }
            } catch (Exception $e) {
                $response = $e->getMessage();
                $response = array('success' => false, 'message' => $response);
            }
        }
        die(json_encode($response));
    }

    public function ajaxProcessCreateShipment()
    {
        if (Tools::getIsset('shipping_provider')
            && Tools::getIsset('wish_order_id')
            && Tools::getValue('wish_order_id')
            && Tools::getValue('origin_country')
            && Tools::getValue('id_cedwish_order')
            && Tools::getValue('shipping_provider')
            && Tools::getValue('tracking_number')
        ) {
            $wish_order_id = Tools::getValue('wish_order_id');
            $ship_note = Tools::getValue('ship_note');
            $id_cedwish_order = Tools::getValue('id_cedwish_order');
            $origin_country = Tools::getValue('origin_country');
            $shipping_provider = Tools::getValue('shipping_provider');
            $tracking_number = Tools::getValue('tracking_number');
            try {
                $params = array(
                    'origin_country' => $origin_country,
                    'shipping_provider' => $shipping_provider,
                    'tracking_number' => $tracking_number
                );
                if ($ship_note) {
                    $params['ship_note'] = $ship_note;
                }
                $wishOrder = new CedWishApi();
                $order = $wishOrder->makeShipment(
                    $wish_order_id,
                    $params
                );
                if (!empty($order) && isset($order['code']) && ($order['code'] == 0)) {
                    $order = $order['data'];
                    Db::getInstance()->update(
                        'cedwish_order',
                        array(
                            'wish_order' => pSQL(json_encode($order)),
                            'order_error' => '',
                            'state' => pSQL($order['state'])
                        ),
                        'id_cedwish_order = ' . (int)$id_cedwish_order
                    );
                    $response = 'Wish Order With Id ' . $wish_order_id . ' Shipped Successfully.';
                    $response = array('success' => true, 'message' => $response);
                } elseif (isset($order['message']) && $order['message']) {
                    $response = $order['message'];
                    Db::getInstance()->update(
                        'cedwish_order',
                        array(
                            'order_error' => pSQL($response)
                        ),
                        'id_cedwish_order = ' . (int)$wish_order_id
                    );
                    $response = array('success' => false, 'message' => $response);
                } else {
                    $response = 'Failed to ship order.';
                    Db::getInstance()->update(
                        'cedwish_order',
                        array(
                            'order_error' => pSQL($response)
                        ),
                        'id_cedwish_order = ' . (int)$wish_order_id
                    );
                    $response = array('success' => false, 'message' => $response);
                }
            } catch (Exception $e) {
                $response = $e->getMessage();
                $response = array('success' => false, 'message' => $response);
            }
        } else {
            $response = array('success' => false, 'message' => "Please fill all required data");
        }
        die(json_encode($response));
    }

    public function displayAjaxGetKpi()
    {
        $tooltip = null;
        switch (Tools::getValue('kpi')) {
            case 'wish_order_total':
                $value = Db::getInstance()->getValue("SELECT count(*) FROM `" . _DB_PREFIX_ . "cedwish_order`");
                ConfigurationKPI::updateValue('WISH_TOTAL_ORDERS', $value);
                ConfigurationKPI::updateValue(
                    'WISH_TOTAL_ORDERS_EXPIRE',
                    strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')))
                );

                break;

            case 'wish_order_shipped_total':
                $value = Db::getInstance()->getValue(
                    "SELECT count(*) FROM `" . _DB_PREFIX_ . "cedwish_order` WHERE state = 'SHIPPED'"
                );
                ConfigurationKPI::updateValue('WISH_TOTAL_SHIPPED_ORDERS', $value);
                ConfigurationKPI::updateValue('WISH_TOTAL_SHIPPED_ORDERS_EXPIRE', strtotime('+1 hour'));

                break;

            case 'wish_order_refunded_total':
                $value =  Db::getInstance()->getValue(
                    "SELECT COUNT(DISTINCT id_cedwish_order) FROM `" . _DB_PREFIX_ . "cedwish_order` WHERE 
                    state = 'REFUNDED'"
                );
                ConfigurationKPI::updateValue('WISH_TOTAL_REFUNDED_ORDERS', $value);
                ConfigurationKPI::updateValue('WISH_TOTAL_REFUNDED_ORDERS_EXPIRE', strtotime('+1 hour'));

                break;

            case 'wish_order_failed_total':
                $value = Db::getInstance()->getValue(
                    "SELECT COUNT(*) FROM " . _DB_PREFIX_ . "cedwish_order WHERE store_order_id=0"
                );
                ConfigurationKPI::updateValue('WISH_TOTAL_FAILED_ORDERS', $value);
                ConfigurationKPI::updateValue('WISH_TOTAL_FAILED_ORDERS_EXPIRE', strtotime('+1 hour'));

                break;

            default:
                $value = false;
        }
        if ($value !== false) {
            $array = ['value' => $value, 'tooltip' => $tooltip];
            die(json_encode($array));
        }
        die(json_encode(['has_errors' => true]));
    }
}
