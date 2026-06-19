<?php
/**
* 2007-2023 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
/* Check to security tocken */
$tokens = Configuration::getMultiShopValues('DELIVERY_TOKEN');
$tokens[0] = Configuration::getGlobalValue('DELIVERY_TOKEN');
$id_shop = array_search(Tools::getValue('token'), $tokens);

if ($id_shop === false ||
    !Module::isInstalled('deliveryorderautoupdate')
) {
    die('Bad token');
}
Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
include(dirname(__FILE__).'/classes/trackingmodel.php');
include(dirname(__FILE__).'/deliveryorderautoupdate.php');
require_once(dirname(__FILE__).'/classes/emailHelper.php');
date_default_timezone_set('Europe/Paris');
$context = Context::getContext();
$idorders_ = 0;
$querycurrent_state = '('.pSQL(Configuration::get('DELIVERY_ORDER_STATUS_FROM')).')';
$trackingmodel = new TrackingModel(3);
$updateStatus = $trackingmodel->updateStatus();
$tracking_number = $trackingmodel->getCronTaskOrders();
$orders = array();
$email_content = '';
foreach ($tracking_number as $key => $number) {
    $order = new Order((int)$number['id_order']);
    $orders[$key]['id_order'] = $number['id_order'];
    $orders[$key]['current_state'] = $number['current_state'];
    $orders[$key]['carrier'] = $number['carrier'];
    $id_lang = emailHelper::checkexistEmail($number['id_lang']);
    $lang = Db::getInstance()->getRow('SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$id_lang);
    $iso_code = Tools::strtoupper($lang['iso_code']);
    if (($number['method'] == 0) && ($number['tracking_number'])) {
        $status = $trackingmodel->track($number);
        $id_tracking_history = $trackingmodel->insertTrackHistory($number, $status);
        $lastStatusTrack = $trackingmodel->getLastStatusTrack();
        $trackingmodel->sendMail($number, $status);
        $email_content .= $trackingmodel->getNotifyContent($number, $status);
        if ($number['id_order']) {
            $trackingmodel->updateOrder($order, $status);
        }
        $idorders_++;
        $event_ = Db::getInstance()->getRow(
            "SELECT event_code, date_add, step_date, carrier_response
            FROM "._DB_PREFIX_."hl_tracking_history
            WHERE id_order=".(int)$number['id_order']."
            ORDER BY id DESC"
        );

        $event_code = $event_['event_code'];

        $orders[$key]['track_number'] = $number['tracking_number'];
        $orders[$key]['last_result'] = $event_['carrier_response'];
        $orders[$key]['event_code'] = $event_['event_code'].'_'.
        (isset($lastStatusTrack->$iso_code)?$lastStatusTrack->$iso_code:$lastStatusTrack->EN).'-'.$event_['step_date'];
    } else {
        $orders[$key]['track_number'] = '';
        $orders[$key]['last_result'] = 'The Shipment is not found.';
        $orders[$key]['event_code'] = '-';
    }
}
$trackingmodel->notifyAdmin($email_content);

$context->smarty->assign(array(
    'idorders_' => $idorders_,
    'response' => (int)Configuration::get('HL_TRACKING_CRON_RESPONSE'),
    'orders' => $orders,
));
$context->smarty->display(dirname(__FILE__).'/views/templates/hook/cron.tpl');
