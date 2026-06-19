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
require_once(dirname(__FILE__).'/AmazonFunctionWithFeeds.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_cancel.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_item.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.mail.logger.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.orderhistory.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.batch.class.php');
require_once(dirname(__FILE__) . '/../classes/AmazonFeedsSending.php');
if (!defined('_PS_VERSION_')) { exit; }

/**
 * Possible query params:
 * - lookBack: Extend the look back days to collect PS orders, default = DEFAULT_LOOK_BACK_DAYS
 */
class AmazonBulkCancelMode extends AmazonFunctionWithFeeds
{
    const DEFAULT_LOOK_BACK_DAYS = 3;

    protected $prLookBack;
    protected $logChannel = AmazonLogger::CHANNEL_ORDER_CANCELLATION;

    public function __construct()
    {
        parent::__construct();

        $this->prLookBack = max(self::DEFAULT_LOOK_BACK_DAYS, (int)AmazonTools::getValue('lookBack', 1));
        $this->emailNotification = (bool)Configuration::get('AMAZON_EMAIL');
    }

    public function dispatch()
    {
        if (!$this->functionAuthorization()) {
            die('Wrong token');
        }

        $action = AmazonTools::getValue('action');
        $this->elc('Dispatch action:', $action);
        switch ($action) {
            case 'cancel':
                $this->switchToCancel();
                break;
            default:
                $this->bulkCancel();
                break;
        }
    }

    public function switchToCancel()
    {
        //  Check Access Tokens
        //
        $error = false;
        $status = Tools::getValue('cancel_status');

        $callback = Tools::getValue('callback');
        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $id_order = (int)Tools::getValue('id_order');
        $reason = (int)Tools::getValue('reason');

        if (!$status) {
            die(Tools::displayError('Missing status'));
        }
        if (!$id_order) {
            die(Tools::displayError('Missing id_order'));
        }
        if (!$reason && $status == AmazonOrder::PROCESS_CANCEL) {
            die(Tools::displayError('Missing reason'));
        }

        $order = new AmazonOrder($id_order);

        if (!Validate::isLoadedObject($order)) {
            die(Tools::displayError('Unable to load order id:'.$id_order));
        }

        $message = null;

        switch ($status) {
            case AmazonOrder::PROCESS_CANCEL:
                $order_cancel = new AmazonOrderCancel();
                if (!$result = $order_cancel->changeOrderStatus($id_order, $status, $reason)) {
                    $message = $this->l('Unable to change the status');
                    $error = true;
                } else {
                    $message = $this->l('Order cancellation has been successfully scheduled');
                }
                break;
            case AmazonOrder::REVERT_CANCEL:
                $order_cancel = new AmazonOrderCancel();
                if (!$result = $order_cancel->changeOrderStatus($id_order, $status)) {
                    $message = $this->l('Unable to change the status');
                    $error = true;
                } else {
                    $message = $this->l('Order cancellation has been suspended');
                }
                break;
        }
        
        $json = json_encode(array(
            'error' => !$result || $error,
            'response' => $this->l('Nothing to do'),
            'result' => $result ? $message : ob_get_clean(),
        ));
        die((string)$callback.'('.$json.')');
    }

    public function bulkCancel()
    {
        $spConnector = $this->initSpConnector();
        if (!$spConnector || !$spConnector->isAuthenticated()) {
            die('Missing region / marketplace!');
        }

        $timeStart = time();

        // Orders States
        $id_canceled_state = AmazonConfiguration::get('CANCELED_STATE');
        $this->bulkCancelPreValidate($id_canceled_state);

        // Send canceled orders to the marketplace
        $to_cancel = $this->bulkCancelGetOrders();
        $this->cancelOrdersApi($to_cancel, $timeStart);

        // Get canceled orders from the marketplace
        $spApiOrders = new AmazonSPAPIOrders($this->spConnector);
        $createdAfterDate = gmdate('Y-m-d\TH:i:s\Z', strtotime(date('c', strtotime('now - 1 week'))));
        $createdBeforeDate = gmdate('Y-m-d\TH:i:s\Z', strtotime(date('c', strtotime('now - 15 min'))));
        $this->elc('Date range: ', $createdAfterDate, $createdBeforeDate);
        $canceledOrders = $spApiOrders->apiListAll(
            $createdAfterDate,
            $createdBeforeDate,
            null,
            null,
            AmazonSPDefOrder::STATUS_CANCELED,
            array(
                AmazonSPDefOrder::FULFILLMENT_CHANNEL_AMAZON,
                AmazonSPDefOrder::FULFILLMENT_CHANNEL_SELLER,
            )
        );
        $this->elc('Canceled Orders from Amazon: ', $canceledOrders);

        foreach ($canceledOrders as $canceledOrder) {
            if (!$canceledOrder instanceof AmazonSPDefOrder) {
                continue;
            }

            $amazon_order_id = $canceledOrder->AmazonOrderId;
            $this->elc('Start cancel order ID: ' . $amazon_order_id);

            if (!($id_order = AmazonOrder::checkByMpId($amazon_order_id))) {
                $this->elc("Unable to find order: $amazon_order_id");
                continue;
            }

            $order = new Order($id_order);
            if (!Validate::isLoadedObject($order)) {
                $this->elc("Unable to load order: $id_order");
                continue;
            }

            $order_history = $order->getHistory($order->id_lang);
            $cancelable = true;
            $canceled = false;

            if (is_array($order_history) && count($order_history)) {
                foreach ($order_history as $key => $order_state) {
                    if ((int)$id_canceled_state && (int)$order_state['id_order_state'] == (int)$id_canceled_state && $key == 0) {
                        $canceled = true;
                    }
                    if (isset($order_state['shipped']) && $order_state['shipped']) {
                        $cancelable = false;
                    }
                    if (isset($order_state['deleted']) && $order_state['deleted']) {
                        $cancelable = false;
                    }
                }
            }

            $do_cancel = false;
            $alert = '';

            if (!$canceled && $cancelable) {
                $do_cancel = true;
                $alert = $this->l('Canceling Order');
            } elseif (!$canceled && !$cancelable) {
                $alert = $this->l('Warning: unable to cancel this order');
            } elseif ($canceled) {
                continue;
            }
            if ($alert && Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('Alert: %s', $alert));
                CommonTools::p(sprintf('Do cancel: %s', $do_cancel ? 'Yes' : 'No'));
            }

            if ($do_cancel) {
                $this->addToHistory($id_order, $id_canceled_state);

                if ($this->emailNotification) {
                    $this->emailContent .= date('c').self::LF .
                        sprintf('%s', $this->l('Canceled Order')).self::LF .
                        sprintf('Amazon Order ID : %s', $amazon_order_id).self::LF .
                        sprintf('Prestashop Order ID : %s', $id_order).self::LF .
                        sprintf('Ordered on : %s', $canceledOrder->PurchaseDate).self::LF .
                        sprintf('From : %s', $canceledOrder->SalesChannel).self::LF .
                        ($alert ? (sprintf('Action : %s', $alert).self::LF) : '');
                }
            }
        }

        if ($this->emailNotification) {
            AmazonMailLogger::message($this->emailContent);
        }
        $this->elc(
            sprintf('Notify by email: %s. ', $this->emailNotification ? 'Yes' : 'No'),
            'Email message:',
            $this->emailContent
        );

        printf("Processed %d order(s)", count($to_cancel));
    }

    protected function bulkCancelGetOrders()
    {
        $order_cancel = new AmazonOrderCancel();
        $orders = $order_cancel->getOrders($this->preparePsIdLangForDbParam(), $this->prLookBack);

        $this->elc('Orders:', $orders);

        $to_cancel = array();
        if (is_array($orders) && count($orders)) {
            foreach ($orders as $order) {
                $id_order = (int)$order['id_order'];
                $mp_order_id = $order['mp_order_id'];
                $mkp = $order['marketplace_id'];

                if (!$id_order || !AmazonTools::strlen($mp_order_id)) {
                    continue;
                }
                
                $orderItemIds = AmazonOrderItem::getOrderItems($mp_order_id);
                $this->elc("Order items: ", $orderItemIds);

                $orderItems = array();
                if (is_array($orderItemIds) && count($orderItemIds)) {
                    foreach ($orderItemIds as $orderItemId) {
                        if (!AmazonTools::strlen($orderItemId)) {
                            continue;
                        }

                        $orderItem = new AmazonOrderItem($mp_order_id, $orderItemId);
                        $this->elc($orderItem);
                        if (AmazonTools::strlen($orderItem->order_item_id)) {
                            $orderItems[$orderItemId] = new AmazonSPFeedMessageOrderAcknowledgementCancelItem(
                                $orderItem->order_item_id, $orderItem->sku, $orderItem->reason
                            );
                            $this->elc('To cancel item: ', $orderItems[$orderItemId]);
                        }
                    }
                }

                $to_cancel[$mkp][$mp_order_id] = array(
                    'merchant_order_id' => $id_order,
                    'mp_order_id' => $mp_order_id,
                    'items' => $orderItems,
                );
            }
        }

        $this->elc('To cancel: ', $to_cancel);
        
        return $to_cancel;
    }
    
    protected function cancelOrdersApi($ordersByMarketplaces, $startTime)
    {
        foreach ($ordersByMarketplaces as $mkp => $orders) {
            $fSending = AmazonFeedsSending::submitFeedOrderCancel(
                $this->spConnector, $orders, $mkp,
                AmazonBatches::TYPE_ORDER_CANCELLATION, $startTime, $this->logger
            );
            $feedContent = $fSending->getFeedContent();
            $feedLog = array(AmazonLogger::CHANNEL_ORDER_CANCELLATION, AmazonLogger::SUB_OI_ACK);
            $this->logger->debug($this->saveSentFeed($feedLog, $feedContent, $fSending->getSubmissionFeedId(), 'OrderAcknowledge'));
            if ($fSending->getSentResponse()->hasError()) {
                $this->elc($fSending->getSentResponse()->getErrorMsg());
            } else {
                $this->updateOrdersAfterCancel($orders);
                $this->appendNotifyEmail($orders);
            }
        }
    }

    private function updateOrdersAfterCancel($orders)
    {
        foreach ($orders as $order) {
            $this->updateOrderAfterCancel($order['merchant_order_id']);
        }
    }
    
    private function updateOrderAfterCancel($psOrderId)
    {
        if ((int)$psOrderId) {
            $order = new AmazonOrder($psOrderId);

            if (Validate::isLoadedObject($order)) {
                $order->amazon_order_info->mp_status = AmazonOrder::CANCELED;
                $order->amazon_order_info->saveOrderInfo();
            }
        }
    }
    
    protected $emailNotification = false;
    protected $emailContent = '';
    
    private function appendNotifyEmail($orders)
    {
        if ($this->emailNotification) {
            foreach ($orders as $order) {
                $this->emailContent .=
                    date('c') . self::LF .
                    sprintf('%s', $this->l('Canceled Order')) . self::LF .
                    sprintf('Amazon Order ID : %s', $order['mp_order_id']) . self::LF .
                    sprintf('Prestashop Order ID : %s', $order['merchant_order_id']) . self::LF .
                    sprintf('Products : %s', is_array($order['items']) ? count($order['items']) : 0) . self::LF .
                    self::LF;
            }
        }
    }

    protected function bulkCancelPreValidate($id_canceled_state)
    {
        if (!$this->amazon_features['cancel_orders']) {
            die('Feature is not active, you can activate it in Features tab.');
        }

        $this->elc("ID Cancelled state: $id_canceled_state");
        if ($id_canceled_state) {
            $order_state = new OrderState($id_canceled_state);
            if (Validate::isLoadedObject($order_state)) {
                return;
            }
        }

        die('Please configure canceled order state in your module configuration first.');
    }

    /**
     * @return string
     */
    protected function preparePsIdLangForDbParam()
    {
        return implode(',', $this->spConnector->getActivePSLanguages());
    }

    private function addToHistory($id_order, $id_order_state)
    {
        $id_employee = Configuration::get('AMAZON_EMPLOYEE');
        // Add History
        $new_history = new AmazonOrderHistory();
        $new_history->id_order = (int)$id_order;
        $new_history->id_employee = (int)$id_employee ? (int)$id_employee : 1;
        $new_history->changeIdOrderState($id_order_state, $id_order);
        $new_history->addWithOutEmail(true);

        $this->elc('Adding to history: ', $new_history);
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if(!$lang)
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }
}

$amazonBulkMode = new AmazonBulkCancelMode();
$amazonBulkMode->dispatch();
