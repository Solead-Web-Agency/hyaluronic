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
require_once(dirname(__FILE__) . '/AmazonFunctionWithFeeds.php');
require_once(dirname(__FILE__) . '/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.order.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.carrier.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.batch.class.php');
require_once(dirname(__FILE__) . '/../common/order.class.php');
require_once dirname(__FILE__) . '/../includes/amazon/amazon.message.fulfillment.php';
require_once(dirname(__FILE__) . '/../classes/AmazonFeedsSending.php');
if (!defined('_PS_VERSION_')) { exit; }

/**
 * Class AmazonBulkMode
 *
 * Possible query params:
 * - period: Extend the look back days to collect orders, default = DEFAULT_PERIOD_IN_DAYS
 * - force: deprecated
 */
class AmazonBulkModeWith extends AmazonFunctionWithFeeds
{
    const DEFAULT_PERIOD_IN_DAYS = 15;

    protected $logChannel = AmazonLogger::CHANNEL_ORDER_UPDATE_STATUS;

    protected $unknownCarriers = array();

    // Runtime variables
    protected $prPeriod;
    protected $prForce = false;
    protected $psSentState;

    public function __construct()
    {
        parent::__construct();

        $this->prPeriod = max(self::DEFAULT_PERIOD_IN_DAYS, (int)AmazonTools::getValue('period', 1));
        $this->prForce = (bool)AmazonTools::getValue('force');
        $this->psSentState = (int)AmazonConfiguration::get(AmazonConstant::CONFIG_OU_SENT_STATE);
    }

    public function bulkUpdate()
    {
        $timeStart = time();
        if (!$this->functionAuthorization()) {
            die('Wrong Token');
        }
        $spConnector = $this->initSpConnector();
        if (!$spConnector || !$spConnector->isAuthenticated()) {
            die('Missing region / marketplace!');
        }

        $order_state = new OrderState($this->psSentState, $this->id_lang);
        if (!Validate::isLoadedObject($order_state)) {
            die(sprintf('%s(%d): Wrong id order state', basename(__FILE__), __LINE__));
        }

        $this->ed(
            sprintf('Updating order statuses for: Amazon %s', implode(', ', $this->spConnector->getMarketplaces())),
            sprintf('Current time zone: %s', date_default_timezone_get()),
            sprintf("- Period: %s days. ", $this->prPeriod),
            sprintf("- State: %s (%d). ", $order_state->name, $this->psSentState)
        );

        // Fetch Orders
        $orders = $this->getOrders();
        if (!$orders) {
            $this->ed($this->l('No Orders - exiting normally'));
            $this->separate();
            return;
        }

        $this->ed('Order List:');
        foreach ($orders as $order) {
            $this->ed(sprintf('id_order: %d amazon order: %s id_lang: %d id_carrier: %d shipping_number: %s date: %s', $order['id_order'], $order['mp_order_id'], $order['id_lang'], $order['id_carrier'], $order['shipping_number'], $order['date_add']));
        }

        $this->ed('Preparing shipping list');
        $fulfillmentMessagesByMkpIds = $this->buildFulfillmentMessages($orders);

        $this->reportUnknownCarriers();

        if (!count($fulfillmentMessagesByMkpIds)) {
            $this->ed($this->l('No Orders - exiting normally'));
            return;
        }

        $this->ed(sprintf($this->l('%s Orders'), count($fulfillmentMessagesByMkpIds)));

        foreach ($fulfillmentMessagesByMkpIds as $mkpId => $fulfillmentMessages) {
            $fSending = AmazonFeedsSending::submitFeedOrderFulfillment(
                $this->spConnector, $fulfillmentMessages, $mkpId,
                AmazonBatches::TYPE_ORDER_CANCELLATION, $timeStart, $this->logger
            );
            $feedContent = $fSending->getFeedContent();
            $feedLog = array(AmazonLogger::CHANNEL_ORDER_UPDATE_STATUS, AmazonLogger::SUB_OU_FULFILLMENT);
            $this->ed($this->saveSentFeed($feedLog, $feedContent, $fSending->getSubmissionFeedId(), 'OrderFulfillment'));
            if ($fSending->getSentResponse()->hasError()) {
                $this->ed($fSending->getSentResponse()->getErrorMsg());
            } else {
                foreach ($fSending->getAdditionalData()['ps_order_ids'] as $psOrderId) {
                    AmazonOrder::updateMarketplaceStatus($psOrderId, AmazonOrder::CHECKED);
                }
            }
        }

        $this->separate();
    }

    private function getOrders()
    {
        $psLangIds = $this->spConnector->getActivePSLanguages();
        $psLangIds = implode(',', $psLangIds);

        return AmazonOrder::getMarketplaceOrdersStatesByIdLang(
            $psLangIds,
            $this->psSentState,
            $this->prPeriod, $this->prForce,
            $this->_debug
        );
    }

    /**
     * Group fulfillment by marketplace
     * @param array $orders
     * @return AmazonFulfillmentMessage[][]
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function buildFulfillmentMessages($orders)
    {
        $fulfillmentMessages = array();

        foreach ($orders as $order) {
            $id_lang = $order['id_lang'];
            $psIdOrder = $order['id_order'];
            $amzIdOrder = $order['mp_order_id'];
            $mkpId = isset($order['marketplace_id']) ? $order['marketplace_id'] : '';
            if (!$mkpId) {
                $this->ed('Missing marketplace ID', $psIdOrder, $amzIdOrder);
                continue;
            }

            $amazonCarrier = AmazonCarrier::getAmazonCarrierByPsIdCarrier($order['id_carrier'], $id_lang);

            if (!AmazonTools::strlen($amazonCarrier['carrier'])) {
                $this->addUnknownCarrier($id_lang, $order['id_carrier']);
                continue;
            }

            $shippingNumber = $this->checkShippingNumber($order['shipping_number'], $psIdOrder);
            $amzOrder = AmazonOrder::getByOrderId($psIdOrder);
            // Shipping service in order takes higher priority, then in carrier outgoing mapping
            $shippingMethod = $amzOrder['shipping_services'];
            if (!$shippingMethod) {
                $shippingMethod = $amazonCarrier['shipping_service'];
            }

            // todo: Maybe pass whole mapping is more elegant
            $fulfillmentMessage = new AmazonFulfillmentMessage(
                $amzIdOrder, $psIdOrder,
                $amazonCarrier['carrier'], $shippingNumber, $shippingMethod,
                strtotime($order['date_add'])
            );
            if (!$fulfillmentMessage->hasCarrier()) {
                $this->ed('Missing carrier', $amzIdOrder);
                continue;
            }
            $fulfillmentMessages[$mkpId][$amzIdOrder] = $fulfillmentMessage;
            $this->ed($fulfillmentMessage->toString());
        }

        return $fulfillmentMessages;
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if (!$lang)
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }

    protected function addUnknownCarrier($idLang, $idCarrier)
    {
        if (!isset($this->unknownCarriers[$idLang])) {
            $this->unknownCarriers[$idLang] = array();
        }
        $this->unknownCarriers[$idLang][$idCarrier] = $idCarrier;
    }

    protected function reportUnknownCarriers()
    {
        if (count($this->unknownCarriers)) {
            $this->ed('Unknown carriers:');

            foreach ($this->unknownCarriers as $id_lang => $unknown_carrier) {
                foreach ($unknown_carrier as $id_carrier) {
                    $carrier = new Carrier($id_carrier);
                    $this->ed(sprintf(
                        '%s %s (%s) - %d',
                        $this->l('Carrier not found, please configure your carriers associations for:'),
                        isset($carrier->name) ? $carrier->name : $id_carrier,
                        Language::getIsoById($id_lang),
                        $id_carrier
                    ));
                }
            }
        }
    }

    /**
     * @param $queryShippingNumber
     * @param $psIdOrder
     * @return int|null
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function checkShippingNumber($queryShippingNumber, $psIdOrder)
    {
        if (empty($queryShippingNumber)) {
            $psOrder = new AmazonOrder($psIdOrder);
            $tracking = $psOrder->getWsShippingNumber();
            $this->ed('Trying to resolve shipping number if not found. Order id: %d, shipping number: %s', $psIdOrder, $tracking);

            return $tracking;
        }

        return $queryShippingNumber;
    }

    protected function separate()
    {
        $this->ed(str_repeat('-', 80));
    }
}

$amazonBulkMode = new AmazonBulkModeWith;
$amazonBulkMode->bulkUpdate();
