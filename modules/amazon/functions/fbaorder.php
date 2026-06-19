<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
/* Is ajax/cron file */
require_once(dirname(__FILE__) . '/AmazonFunction.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.multichannel.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.carrier.class.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonFBAOrder extends AmazonFunction
{
    protected $logChannel = AmazonLogger::CHANNEL_FBA_MCF;

    public static $errors         = array();
    public static $log            = array();
    private $amazon_id_lang = null;

    public function __construct()
    {
        parent::__construct();

        if (!$this->functionAuthorization()) {
            die('Wrong Token');
        }

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $employee = null;
            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                die($this->l('Wrong Employee, please save the module configuration'));
            }

            $id_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');

            $group = new Group($id_group);

            if (!Validate::isLoadedObject($group)) {
                $id_group = null;
            }

            if (!$id_group || !is_numeric($id_group)) {
                $id_group = Configuration::get('PS_CUSTOMER_GROUP');
            }

            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = $id_group;
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
    }

    public function dispatch()
    {
        $result = false;
        $action = AmazonTools::getValue('action');
        switch ($action) {
            case 'list':
                $this->listFulfillmentOrder();
                break;
            case 'create':
                $result = $this->createFulfillmentOrder();
                break;
            case 'info':
            case 'cancel':
                $idOrder = (int)AmazonTools::getValue('id_order');
                if (!$idOrder) {
                    $this->ed('Missing mandatory parameter, id_order');
                } else {
                    if ($action == 'info') {
                        $result = $this->getFulfillmentOrder($idOrder);
                    } else {
                        $result = $this->cancelFulfillmentOrder($idOrder);
                    }                    
                }
                break;
            case 'status':
            default:
                // Forget the response, this is cronjob
                $this->FulfillmentOrderStatuses();
                break;
        }

        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(array(
                'error' => true,
                'response' => 'Process ends without success',
                'output' => '',
                'errors' => AmazonMultiChannel::$errors
            ));
        }
    }

    public function listFulfillmentOrder()
    {
        $orders = AmazonMultiChannel::orderList(Tools::getValue('days', 30));
        CommonTools::d($orders);
    }

    public function createFulfillmentOrder()
    {
        $this->Init();

        if (!($id_order = (int)AmazonTools::getValue('id_order'))) {
            print('Missing mandatory parameter, id_order');
            return false;
        }

        if (!(AmazonMultiChannel::isEligible($id_order))) {
            $this->elc('createFulfillmentOrder() is not eligible');
            return false;
        }

        $amazonMultiChannelOrder = new AmazonMultiChannel($id_order);
//        $this->elc('Multichannel Order', get_object_vars($amazonMultiChannelOrder));

        if (!Validate::isLoadedObject($amazonMultiChannelOrder)) {
            $this->elc('createFulfillmentOrder(): Validate::isLoadedObject() returned false');
            return false;
        }

        // Not already ordered, shipped or canceled
        if (AmazonTools::strlen($amazonMultiChannelOrder->marketPlaceChannelStatus)) {
            $this->elc('createFulfillmentOrder() has already a FBA/Multichannel state');
            return false;
        }

        $creationResult = $amazonMultiChannelOrder->createFulfillmentOrder($this->amazon_id_lang, $this->spConnector, $this->logger);
        $this->elc('Errors if any:', $amazonMultiChannelOrder->getErrors());

        return array(
            'error' => !$creationResult,
            'response' => '',
            'output' => '',
            'errors' => self::fix_encoding($amazonMultiChannelOrder->getErrors())
        );
    }

    public static function fix_encoding($to_fix)
    {
        if (is_array($to_fix) && count($to_fix)) {
            foreach ($to_fix as $key => $item) {
                if (!mb_check_encoding($item, 'UTF-8')) {
                    $to_fix[$key] = mb_convert_encoding($item, "UTF-8");
                }
            }
        } elseif (is_string($to_fix)) {
            $to_fix = mb_convert_encoding($to_fix, "UTF-8");
        }

        return ($to_fix);
    }

    public function Init()
    {
        if (!$this->functionAuthorization()) {
            die('Wrong token');
        }
        $spConnector = $this->initSpConnector();
        if (!$spConnector || !$spConnector->isAuthenticated()) {
            die('Missing region / marketplace!');
        }
        $this->amazon_id_lang = $spConnector->getPSLanguage();
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if(!$lang)
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }

    public function getFulfillmentOrder($id_order)
    {
        $this->Init();

        $amazonMultiChannelOrder = new AmazonMultiChannel($id_order);
        $orderInfo = array();
        $error = false;

        $result = $amazonMultiChannelOrder->getFulfillmentOrder($this->spConnector);
        if (!$result) {
            $error = true;
        } else {
            $orderInfo = array(
                'ReceivedDateTime' => AmazonTools::displayDate(
                    date('Y-m-d H:i:s', strtotime($result->fulfillmentOrder->receivedDate)),
                    $this->amazon_id_lang, true
                ),
                'StatusUpdatedDateTime' => AmazonTools::displayDate(
                    date('Y-m-d H:i:s', strtotime($result->fulfillmentOrder->statusUpdatedDate)),
                    $this->amazon_id_lang, true
                ),
                'ShippingSpeedCategory' => $result->fulfillmentOrder->shippingSpeedCategory,
                'FulfillmentMethod' => '', // No property available
                'FulfillmentOrderStatus' => $result->fulfillmentOrder->fulfillmentOrderStatus,
                'DisplayableOrderId' => $result->fulfillmentOrder->displayableOrderId,
                'Items' => count($result->fulfillmentOrderItems),
                'EstimatedShipDateTime' => $result->hasItems() ?
                    AmazonTools::displayDate(
                        date('Y-m-d', strtotime($result->getFirstItem()->estimatedShipDate))
                    ) : '',
                'EstimatedArrivalDateTime' => $result->hasItems() ?
                    AmazonTools::displayDate(
                        date('Y-m-d', strtotime($result->getFirstItem()->estimatedArrivalDate))
                    ) : '',
            );

            $amazonMultiChannelOrder->updateMpChannel(
                $result->fulfillmentOrder->fulfillmentOrderStatus,
                AmazonMultiChannel::AMAZON_FBA_MULTICHANNEL
            );
        }

        return array(
            'error' => $error,
            'info' => $orderInfo,
            'error_message' => 'Failed to get order!',
            'output' => '',
            'errors' => AmazonMultiChannel::$errors
        );
    }

    public function cancelFulfillmentOrder($id_order)
    {
        $this->Init();

        $amazonMultiChannelOrder = new AmazonMultiChannel($id_order);
        $error = false;
        $errorMessage = null;

        if (!$amazonMultiChannelOrder->cancelFulfillmentOrder($this->spConnector)) {
            $error = true;
            $errorMessage = 'Failed to cancel order!';
        } else {
            $history = new AmazonOrderHistory();
            $history->id_order = (int)$id_order;
            $history->id_employee = (int)Configuration::get('AMAZON_EMPLOYEE');
            $history->changeIdOrderState(Configuration::get('PS_OS_CANCELED'), $history->id_order, true);
            $history->addWithOutEmail(true);
        }

        return array(
            'error' => $error,
            'error_message' => $errorMessage,
            'output' => '',
            'errors' => AmazonMultiChannel::$errors
        );
    }

    /**
     * @return array|false
     */
    public function FulfillmentOrderStatuses()
    {
        $order_statuses = OrderState::getOrderStates($this->id_lang);
        $paid_states = array();

        if (is_array($order_statuses) && count($order_statuses)) {
            foreach ($order_statuses as $order_status) {
                if ($order_status['paid']) {
                    $paid_states[] = (int)$order_status['id_order_state'];
                }
            }
        }

        $pending_state = (int)Configuration::get('AMAZON_FBA_MULTICHANNEL_STATE');
        $sent_state = (int)Configuration::get('AMAZON_FBA_MULTICHANNEL_SENT');
        $done_state = (int)Configuration::get('AMAZON_FBA_MULTICHANNEL_DONE');
        $id_employee = (int)Configuration::get('AMAZON_EMPLOYEE');

        if (!$pending_state) {
            $this->elc('FulfillmentOrderStatuses() Order State is not yet configured');
            return false;
        }

        $this->Init();

        $amazonMultiChannel = new AmazonMultiChannel();

        $paid_state1 = Configuration::get('PS_OS_PAYMENT');
        $paid_state2 = Configuration::get('PS_OS_WS_PAYMENT');
        $statuses = array_merge($paid_states, array($pending_state, $sent_state, $paid_state1, $paid_state2));

        $result = AmazonMultiChannel::ordersByStatus(
            $statuses,
            AmazonTools::getValue('days', 7),
            AmazonTools::getValue('id_order', null)
        );

        if (!$result || !is_array($result) || !count($result)) {
            $this->elc('FulfillmentOrderStatuses() ordersByStatus returned nothing');
            return false;
        }
        $this->elc('Orders Returned:', $result);

        $dateStart = gmdate('Y-m-d\TH:i:s\Z', strtotime($result[0]['date_add']));

        // Merge as array('id_order' => ..)
        //
        $orders = array();

        foreach ($result as $entry) {
            $orders[$entry['id_order'] . '-' . $entry['reference']] = $entry;
        }

        $ffOrders = $amazonMultiChannel->ListAllFulfillmentOrders($dateStart, $this->spConnector);
        if (!$ffOrders || !is_array($ffOrders)) {
            $this->elc('FulfillmentOrderStatuses() ListAllFulfillmentOrders returns nothing');
            return false;
        }
        $this->elc('ListAllFulfillmentOrders:', $ffOrders);

        // Merge orders informations
        foreach ($ffOrders as $ffOrder) {
            $orders[$ffOrder->sellerFulfillmentOrderId]['FulfillmentOrderStatus'] = $ffOrder->fulfillmentOrderStatus;
            $orders[$ffOrder->sellerFulfillmentOrderId]['StatusUpdatedDateTime'] = $ffOrder->statusUpdatedDate;
        }
        $this->elc('Orders Merged:', $orders);

        foreach ($orders as $order) {
            if (!isset($order['id_order'])) {
                continue;
            }
            $id_order = (int)$order['id_order'];

            if (!isset($order['FulfillmentOrderStatus'])) {
                // not listed
                $this->elc('FulfillmentOrderStatus - unlisted order ID', $id_order);
                continue;
            }

            $amazonMultiChannel = new AmazonMultiChannel($id_order);
            if (!Validate::isLoadedObject($amazonMultiChannel)) {
                $this->elc('FulfillmentOrderStatuses() unable to load order id:', $id_order);
                continue;
            }

            if ($order['mp_channel_status'] != $order['FulfillmentOrderStatus']) {
                $amazonMultiChannel->updateMpChannel($order['FulfillmentOrderStatus'], AmazonMultiChannel::AMAZON_FBA_MULTICHANNEL);
                $orders[$id_order]['NewFulfillmentOrderStatus'] = $order['FulfillmentOrderStatus'];
            }

            $amazon_fba_order_state = Tools::strtolower($order['FulfillmentOrderStatus']);
            switch ($amazon_fba_order_state) {
                case AmazonMultiChannel::AMAZON_FBA_STATUS_PROCESSING:
                case AmazonMultiChannel::AMAZON_FBA_STATUS_COMPLETE:
                case AmazonMultiChannel::AMAZON_FBA_STATUS_COMPLETEPARTIALLED:
                case AmazonMultiChannel::AMAZON_FBA_STATUS_RECEIVED:
                    $result = $amazonMultiChannel->getFulfillmentOrder($this->spConnector);
                    $this->elc('GetFulfillmentOrder', $result);

                    if ($result->hasShipmentNotShipped()) {
                        $this->elc('GetFulfillmentOrder: Ignoring pending order');
                        break;
                    }

                    // Update tracking number, carrier
                    $trackingNumber = $result->getAnyExistedShipmentPackageTrackingNumber();
                    $carrierCode = $result->getAnyExistedShipmentPackageCarrierCode();
                    if ($carrierCode && $trackingNumber) {
                        $this->elc("Carrier: $carrierCode Tracking: $trackingNumber");
                        if (!($id_carrier_fba = AmazonCarrier::FBACarrier($carrierCode))) {
                            $id_carrier_fba = AmazonCarrier::FBACarrierCreate($carrierCode);
                            $this->elc("id_carrier_fba: $id_carrier_fba");
                            if (!$id_carrier_fba) {
                                $this->ed("FulfillmentOrderStatuses() unable add carrier: $carrierCode");
                                break;
                            }
                        }
//                        $previous_tracking_number = AmazonOrder::getShippingNumber($amazonMultiChannel);
                        AmazonCarrier::updateTrackingNumber($id_order, $id_carrier_fba, $trackingNumber, Amazon::$debug_mode);
                    }

                    // Process state
                    if ($result->hasShipment()) {
                        $has_shipped = false;
                        $order_history_list = $amazonMultiChannel->getHistory($this->id_lang);
                        if (is_array($order_history_list) && count($order_history_list)) {
                            foreach ($order_history_list as $order_history_item) {
                                if ((int)$order_history_item['id_order_state'] && (int)$order_history_item['id_order_state'] == $sent_state) {
                                    $has_shipped = true;
                                }
                            }
                            if (Tools::strlen($trackingNumber)) {
                                $has_shipped = true;
                            }

                            $this->elc('OrderHistory - List:', $order_history_list, 'OrderHistory - Has Shipped:', $has_shipped ? 'True' : 'False');
                        }


                        if (!$has_shipped && !$result->hasShipmentNotShipped()) {
                            $new_state = $sent_state;
                        } else {
                            $new_state = $has_shipped && $amazon_fba_order_state == AmazonMultiChannel::AMAZON_FBA_STATUS_COMPLETE && (int)$done_state ? (int)$done_state : (int)$sent_state;
                        }

                        // Prevent to switch the status to delivered earlier
                        $arrival_date_time1 = $result->getAnyExistedShipmentEstimatedArrival();
                        $arrival_date_time2 = $result->getAnyExistedShipmentPackageEstimatedArrival();
                        $this->elc(
                            "current_state: $amazonMultiChannel->current_state",
                            "new_state: $new_state",
                            "arrival_date_time1: " . date('c', $arrival_date_time1),
                            "arrival_date_time2: " . date('c', $arrival_date_time2)
                        );

                        if (($arrival_date_time1 || $arrival_date_time2) && $new_state == $done_state && max($arrival_date_time1, $arrival_date_time2) > time()) {
                            $new_state = (int)$sent_state;
                            $this->elc('Switch to sent state as order is not supposed to be arrived');
                        }
                        if (is_array($order_history_list) && count($order_history_list)) {
                            $last_history = reset($order_history_list);
                            if ((int)$last_history['id_order_state'] != (int)$new_state) {
                                $amazonMultiChannel->current_state = null;
                            }
                        }
                        
                        if ($amazonMultiChannel->current_state == $new_state) {
                            CommonTools::p(sprintf('FulfillmentOrderStatuses() order has already the same state: %d', $new_state));
                            break;
                        }

                        $this->elc(sprintf("Switching to state: %s", $new_state));
                        $amazonMultiChannel->addToHistory($id_employee, $new_state);
                        $amazonMultiChannel->current_state = $new_state;
                        $amazonMultiChannel->update();

                        $orders[$id_order]['id_order_state'] = $new_state;
                    }
                    break;
                default:
                    $this->elc("Status ignored: $amazon_fba_order_state");
            }
        }

        return $orders;
    }
}

$amazonFBAOrder = new AmazonFBAOrder();
$amazonFBAOrder->dispatch();
