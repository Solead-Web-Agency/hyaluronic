<?php
/**
* 2007-2018 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

require_once(_PS_MODULE_DIR_.'deliveryorderautoupdate/classes/status.php');
require_once(_PS_MODULE_DIR_.'deliveryorderautoupdate/classes/connectors/Carrier.php');

class TrackingModel
{
    const EMAIL_SENT = 1;
    const DEFAULT_LANG = 'EN';
    public function __construct($method = 0)
    {
        $this->method = $method;
        $this->context = Context::getContext();
        $this->webxml_crr = $this->getStatuses();
        $this->status = 0;
        $this->id_tracking_history = 0;
        $this->sent = false;
        $this->admin_report = false;
        $this->helloshop_report = false;
    }
    public function setLang($order)
    {
        $this->id_lang = emailHelper::checkexistEmail($order['id_lang']);
        $this->lang = Db::getInstance()->getRow(
            'SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$this->id_lang
        );
        $this->iso_code = Tools::strtoupper($this->lang['iso_code']);
    }
    public function getCronTaskOrders()
    {
        $carrier_exclude = pSQL(Configuration::get('HL_TRACKING_CARRIER_EXCLUDE'));
        $status_exclude = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_EXCLUDE'));
        $id_shop = $this->context->shop->id;
        $date = pSQL(Configuration::get('DELIVERY_ORDER_DATE'));
        $id_carrier = Tools::getValue('carrier', 0);
        $orders = Db::getInstance()->executeS(
            'SELECT DISTINCT o.id_order,oc.id_order_carrier,o.id_lang, o.reference,
            o.current_state as id_order_state,oc.tracking_number,
            hlc.name as carrier, c2.id_carrier, c2.name as carrier_name,
            hlc.id, hlc.method, hlc.url, c2.url as tracking_url, osl.name as current_state
            FROM '._DB_PREFIX_.'order_carrier oc
            INNER JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
            INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state
            INNER JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
            LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference = c2.id_reference AND c2.deleted=0
            INNER JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c2.id_reference=hlcm.id_carrier_ps
            INNER JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
            LEFT JOIN '._DB_PREFIX_.'order_state_lang osl
            ON o.current_state = osl.id_order_state AND osl.id_lang='.(int)$this->context->language->id.'
            LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status cs ON oc.id_order_carrier=cs.id_order_carrier
            LEFT JOIN '._DB_PREFIX_.'hl_tracking_disable td ON oc.id_order_carrier=td.id_order_carrier
            WHERE '.($id_shop ? ' o.id_shop='.$id_shop.' AND ' : '')
            .' os.shipped=1'.
            ($id_carrier?' AND hlcm.id_carrier_hl = '.$id_carrier:'').
            ($carrier_exclude?' AND c2.id_reference NOT IN ('.$carrier_exclude.')':'').
            ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').
            ' '.($date ? ' AND oc.date_add >="'.$date.'" ' : '' ).
            ' AND oc.tracking_number <> ""
            AND (cs.id_status NOT IN(1,5) OR cs.id_status is null) AND td.id_order_carrier is null
             ORDER BY o.id_order'
        );
        return $orders;
    }
    public function getStatuses()
    {
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        return json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
    }
    private function removeUTF8BOM($text)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
    public function handleTrackingNumber($number)
    {
        $arr = explode(',', trim($number));
        if (count($arr) > 1) {
            return $arr[count($arr)-1];
        } else {
            return $number;
        }
    }
    public function updateReturn($return, $status)
    {
        $sql = "UPDATE `"._DB_PREFIX_."hl_tracking_return`
        SET shipping_status = '".$status->module_shipping_status_code."', status_date = '".
        $status->carrier_shipping_status_date."' WHERE id_return = ".$return['id_return'];
        Db::getInstance()->execute($sql);
    }
    public function trackReturn($order)
    {
        $order['tracking_number'] = $this->handleTrackingNumber($order['shipping_number']);
        $shopContext = $this->context->cookie->shopContext;
        $helper = new EmailHelper();
        $id_shop = (int)$helper->getIdShop($shopContext, $this->context->shop->id);
        $this->order = $order;
        $order['id_order_carrier'] = $order['id_return'];
        $connector_path = _PS_MODULE_DIR_.'deliveryorderautoupdate/classes/connectors/Carrier'.$order['hl_carrier'].'.php';
        if (file_exists($connector_path)) {
            include_once($connector_path);
            $carrier_name = 'Carrier'.$order['hl_carrier'];
        } else {
            $carrier_name = 'deliveryorderautoupdate\Carrier';
        }
        $carrier = new $carrier_name($order);
        $status = $carrier->track();
        // if (isset($status->result)) {
        //     $status = $status->result;
        // } else {
        //     $status = new stdClass();
        //     $status->shipment_ref = $order['id_return'];
        //     $status->carrier_server_status_code = '';
        //     $status->carrier_shipping_status_date = Date('Y-m-d H:i:s');
        //     $status->carrier_server_success = 'false';
        //     $status->carrier_shipping_status_code = 0;
        //     $status->carrier_shipping_status_text = null;
        //     $status->carrier_server_status_text = '';
        //     $status->module_shipping_status_code = 100;
        // }
        return $status;
    }
    public function track($order)
    {
        $order['tracking_number'] = $this->handleTrackingNumber($order['tracking_number']);
        $shopContext = $this->context->cookie->shopContext;
        $helper = new EmailHelper();
        $id_shop = (int)$helper->getIdShop($shopContext, $this->context->shop->id);
        $this->order = $order;
        $this->setLang($order);
        $connector_path = _PS_MODULE_DIR_.'deliveryorderautoupdate/classes/connectors/Carrier'.$order['id'].'.php';
        if (file_exists($connector_path)) {
            include_once($connector_path);
            $carrier_name = 'Carrier'.$order['id'];
        } else {
            $carrier_name = 'deliveryorderautoupdate\Carrier';
        }
        $carrier = new $carrier_name($order);
        $status = $carrier->track();
        $webxml_crr = $this->webxml_crr;
        foreach ($webxml_crr->status as $step) {
            if ($step->id_status == $status->module_shipping_status_code) {
                $this->status = $step;
            }
        }
        if (Configuration::get('HL_TRACKING_REPORT_ERROR') &&
            (!$status->module_shipping_status_code || $status->module_shipping_status_code == 100)
        ) {
            $this->report($order, $status);
        }
        return $status;
    }
    public function report($order, $status)
    {
        $this->helloshop_report |= $this->addReport($order, $status, 2);
        if (!$this->helloshop_report) {
            return;
        }
        $module = Module::getInstanceByName('deliveryorderautoupdate');
        $connector = $order['id'];
        $module_shipping_status = $status->module_shipping_status_code;
        $shipping_number = $order['tracking_number'];
        $postcode = '';
        $status_list = '';
        if (isset($status->all_events) && is_array($status->all_events) && count($status->all_events)) {
            foreach ($status->all_events as $event) {
                $status_list .= '<carrier_shipping_status>'.(isset($event->event_code)?$event->event_code:'').'</carrier_shipping_status>';
                $status_list .= '<carrier_shipping_description>'.(isset($event->event_description)?$event->event_description:'').'</carrier_shipping_description>';
            }
        } else {
            $status_list .= '<carrier_shipping_status>'.$status->carrier_shipping_status_code.
            '</carrier_shipping_status>';
            $status_list .= '<carrier_shipping_description>'.
            $status->carrier_shipping_status_text.'</carrier_shipping_description>';
        }
        $module_version = $module->version;
        $prestashop_version = _PS_VERSION_;
        $tracking_method = $this->method;
        $shop_url = EmailHelper::getUrl();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://helloshop.com/apis/modules/trackingcenter/error_report.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'<?xml version="1.0" encoding="UTF-8" ?>
            <root>
            <connector>'.$connector.'</connector>
            <module_shipping_status>'.$module_shipping_status.'</module_shipping_status>
            <shipping_number>'.$shipping_number.'</shipping_number>
            <postcode>'.$postcode.'</postcode>
            <status_list>'.$status_list.'</status_list>
            <module_version>'.$module_version.'</module_version>
            <prestashop_version>'.$prestashop_version.'</prestashop_version>
            <tracking_method>'.$tracking_method.'</tracking_method>
            <shop_url>'.$shop_url.'</shop_url>
            </root>',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/xml',
            ),
        ));
        curl_exec($curl);
    }
    public function getLastStatusTrack()
    {
        return $this->status;
    }
    public function getLastEventCode()
    {
        return $this->lastEventCode;
    }
    public function getLastTrackingHistoryId()
    {
        return $this->id_tracking_history;
    }
    public function getStatusList()
    {
        $statuses = array();
        $webxml_crr = $this->webxml_crr;
        foreach ($webxml_crr->status as $s) {
            $statuses[$s->id_status] = $s;
        }
        return $statuses;
    }
    public function isMailSent()
    {
        return $this->sent;
    }
    public function insertTrackHistory($order, $status)
    {
        $event_code = 'null';
        $webxml_crr = $this->webxml_crr;
        foreach ($webxml_crr->status as $step) {
            if ($step->id_status == $status->module_shipping_status_code) {
                $this->status = $step;
                $iso_code_stt = Tools::strtoupper($this->context->language->iso_code);
                if (!isset($step->$iso_code_stt)) {
                    $iso_code_stt = self::DEFAULT_LANG;
                }
                $event_code = $status->module_shipping_status_code.'_'.$step->$iso_code_stt;
            }
        }
        $server_success = $status->carrier_server_success === true || $status->carrier_server_success == 'true' ? 1 : 0;
        $this->lastEventCode = $event_code;
        $shipping_status_text = $status->carrier_shipping_status_code.'_'.$status->carrier_shipping_status_text;
        $carrier_server_status_text = ($status->carrier_server_status_code ?
            $status->carrier_server_status_code.'_'.$status->carrier_server_status_text :
            $shipping_status_text
        );
        $status_date = ($status->carrier_shipping_status_date ?
            date("Y-m-d H:i:s", strtotime($status->carrier_shipping_status_date)) :
            Date('Y-m-d H:i:s')
        );
        $email_sent = $this->shouldSendEmail($order, $status);
        Db::getInstance()->execute(
            "INSERT INTO `"._DB_PREFIX_."hl_tracking_history`
            (`id_order`, `id_order_carrier`, `method`, `hl_carrier`, `event_code`, `success_response`,
            `carrier_response`, `date_add`, `step_date`, `email_sent`)
            VALUES (".(int)$order['id_order'].", ".(int)$order['id_order_carrier'].", '".$this->method.
            "', '".(int)$order['id']."', '".
            pSQL($event_code)."', '".pSQL($server_success)."', '".pSQL($carrier_server_status_text).
            "', '".pSQL(Date('Y-m-d H:i:s'))."', '".pSQL($status_date)."', '".(int)$email_sent."')"
        );
        $this->id_tracking_history = Db::getInstance()->Insert_ID();
        $this->insertCurrentStatus($status);
        $this->insertEvents($status);
        return $this->id_tracking_history;
    }

    public function shouldSendEmail($order, $status)
    {
        $shipping_status_code = isset($status->module_shipping_status_code)?$status->module_shipping_status_code:0;
        $check_email_sent = Db::getInstance()->getValue(
            'SELECT COUNT(*)
            FROM '._DB_PREFIX_.'hl_tracking_email
            WHERE id_order_carrier='.(int)$order['id_order_carrier'].' AND shipping_status ='.$shipping_status_code
        );
        $code_mail_config = trim(Configuration::get('DELIVERY_EVENT_CODE_MAIL'));
        if ($code_mail_config != '') {
            $code_mail = explode(',', $code_mail_config);
        } else {
            $code_mail = array();
        }
        if (in_array($shipping_status_code, $code_mail) && $check_email_sent < 1) {
            $email_sent = 1;
        } else {
            $email_sent = 0;
        }
        return $email_sent;
    }
    public function getClientEmail($order)
    {
        return Db::getInstance()->getRow(
            'SELECT email, o.id_shop, CONCAT(c.firstname," ",c.lastname) as customer_name,
            date(o.date_add) as date_add,oc.tracking_number, o.reference, c.firstname, c.lastname
            FROM '._DB_PREFIX_.'customer c
            INNER JOIN '._DB_PREFIX_.'orders o ON o.id_customer = c.id_customer
            INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
            WHERE o.id_order='.(int)$order['id_order']
        );
    }
    public function sendMail($order, $status, $force_send = false)
    {
        $langs = count(Language::getLanguages());
        if (!$force_send) {
            $email_sent = $this->shouldSendEmail($order, $status);
        } else {
            $email_sent = true;
        }
        $client_email = $this->getClientEmail($order);
        $lastStatusTrack = $this->getLastStatusTrack();
        $iso_code = $this->iso_code;
        $stt1 = isset($lastStatusTrack->$iso_code)?$lastStatusTrack->$iso_code:$lastStatusTrack->EN;
        $html = EmailHelper::displayOrderDetail($client_email['reference'], $this->id_lang);
        if ($client_email['reference']) {
            $meta = Meta::getMetaByPage('module-deliveryorderautoupdate-orders', $this->id_lang);
            $link_tracking = emailHelper::getUrl()
            .($langs > 1?$this->lang['iso_code'].'/':'').$meta['url_rewrite'].'?order_reference='
            .$client_email['reference'];
        } else {
            $link_tracking = '#';
        }
        $this->params = array(
            '{current_status}' => $stt1,
            '{background}' => $lastStatusTrack->color,
            '{order_reference}' => $order['reference'],
            '{customer_name}' => $client_email['customer_name'],
            '{firstname}' => $client_email['firstname'],
            '{lastname}' => $client_email['lastname'],
            '{date}' => $client_email['date_add'],
            '{body_content}' => $html,
            '{link_tracking}' => $link_tracking,
            '{courier}' => $order['carrier'],
            '{track_link}' => $link_tracking
        );
        if ($email_sent) {
            $sent = Db::getInstance()->insert('hl_tracking_email', array(
                'id_order_carrier'      => (int)$order['id_order_carrier'],
                'id_tracking_history' => $this->getLastTrackingHistoryId(),
                'date_sent'     => date('Y-m-d H:i:s'),
                'email_status'  => self::EMAIL_SENT,
                'shipping_status' => (int)$status->module_shipping_status_code
            ));
            if ($sent) {
                $id_email = Db::getInstance()->Insert_ID();
                $this->params['{track_link}'] .= '&id_email='.$id_email;
                if (Configuration::get('HL_TRACKING_EMAIL_SUBJECT') == 'fixed') {
                    $subject = Configuration::get('DELIVERY_EMAIL_SUBJECT', $this->id_lang);
                } else {
                    $subject = emailHelper::getSubjectById($order['id_order_carrier'], $this->id_lang);
                }
                $subject = $subject?$subject:'NO_SUBJECT';
                $mail = Mail::Send(
                    $this->id_lang,
                    'tracking',
                    $subject,
                    $this->params,
                    $client_email['email'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    _PS_ROOT_DIR_.'/modules/deliveryorderautoupdate/mails/',
                    false,
                    ($client_email['id_shop'] ? $client_email['id_shop'] : 1),
                    null
                );
                return $mail;
            }
        }
        return false;
    }
    public function addReport($order, $status, $recipient)
    {
        $o = new Order($order['id_order']);
        if (isset($status->all_events) && is_array($status->all_events) && count($status->all_events)) {
            foreach ($status->all_events as $event) {
                Db::getInstance()->insert('hl_tracking_sent_report', array(
                    'id_order_carrier'      => (int)$o->id_carrier,
                    'id_connector' => (int)$order['id'],
                    'id_status'     => pSQL($event['id_status']),
                    'carrier_status_code'  => pSQL($event['event_code']),
                    'recipient' => $recipient,
                ), false, true, Db::INSERT_IGNORE);
            }
            return Db::getInstance()->Insert_ID();
        }
        return 0;
    }
    public function getNotifyContent($order, $status)
    {
        $statuses = array();
        foreach ($this->webxml_crr->status as $s) {
            $statuses[$s->id_status] = $s;
        }
        $DELIVERY_EVENT_CODE_MAIL_ADMIN = trim(Configuration::get('DELIVERY_EVENT_CODE_MAIL_ADMIN'));
        $code_mail_admin = $DELIVERY_EVENT_CODE_MAIL_ADMIN != ''?explode(',', $DELIVERY_EVENT_CODE_MAIL_ADMIN):array();
        if (in_array($status->module_shipping_status_code, $code_mail_admin)) {
            $this->admin_report |= $this->addReport($order, $status, 1);
            $tpl = $this->context->smarty->createTemplate(
                _PS_MODULE_DIR_.'deliveryorderautoupdate/views/templates/hook/notify-order.tpl'
            );
            $tpl->assign(array(
                'order' => $order,
                'status' => $status,
                'url' => emailHelper::getUrl(),
                'statuses' => $statuses,
                'iso_code' => Tools::strtoupper($this->context->language->iso_code),
            ));
            return $tpl->fetch();
        }
        return '';
    }
    public function notifyAdmin($content)
    {
        if (!$content || !$this->admin_report) {
            return;
        }
        $this->params['{body_content}'] = $content;
        $admin_emails = explode(',', Configuration::get('DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS'));
        foreach ($admin_emails as $admin_email) {
            if (Validate::isEmail($admin_email)) {
               Mail::Send(
                    $this->context->language->id,
                    'notify',
                    'Tracking Center - Cron task notification',
                    $this->params,
                    $admin_email,
                    null,
                    null,
                    null,
                    null,
                    null,
                    _PS_ROOT_DIR_.'/modules/deliveryorderautoupdate/mails/',
                    false,
                    ($this->context->shop->id ? $this->context->shop->id : 1),
                    null
                );
            }
        }
    }
    public function updateOrder($order, $status)
    {
        setlocale(LC_NUMERIC, 'en_US');
        $statuses = json_Decode(Configuration::get('DELIVERY_ORDER_STATUS_TO'), true);
        if (isset($statuses[$status->module_shipping_status_code]) &&
            $statuses[$status->module_shipping_status_code] > -1) {
            $status_update = $statuses[$status->module_shipping_status_code];
            $order_state = new OrderState($status_update);
            $history = new OrderHistory();
            $use_existings_payment = false;
            if (!$order->hasInvoice()) {
                $use_existings_payment = true;
            }
            $history->id_order = (int)$order->id;
            $history->id_employee = 0;
            if ($order_state->id != $order->current_state) {
                $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);
                $history->addWithemail();
                if (Validate::isDateFormat($status->carrier_shipping_status_date)) {
                    $history->date_add = $status->carrier_shipping_status_date;
                }
                //  else {
                //     print_r($status);
                // }
                $history->save();
            }
        }
        $this->updateOrderCarrier($status);
    }
    public function updateOrderCarrier($status)
    {
        if (isset($status->all_events) && is_array($status->all_events) && count($status->all_events)) {
            foreach ($status->all_events as $event) {
                if (isset($this->order) && $event['id_status'] == 4 && Validate::isDateFormat($event['event_date'])) {
                    $orderCarrier = new OrderCarrier($this->order['id_order_carrier']);
                    $orderCarrier->date_add = $event['event_date'];
                    $orderCarrier->update();
                    break;
                }
            }
        }
    }
    public static function getTrackSteps($id_order_carrier)
    {
        $step = Db::getInstance()->getRow(
            'SELECT oc.id_order_carrier,o.date_add AS "order", oc.date_add AS "shipped",
            te.date AS "current",te.id_status AS "current_status"
            FROM '._DB_PREFIX_.'order_carrier oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
            LEFT JOIN '._DB_PREFIX_.'hl_tracking_events te ON oc.id_order_carrier=te.id_order_carrier
            WHERE te.id_status NOT IN(0,13,100,101,102,103,104,105,106,107)
            AND oc.id_order_carrier='.$id_order_carrier.' ORDER BY te.id_tracking_events DESC'
        );
        return $step;
    }
    public static function getTrackEvents($id_order_carrier)
    {
        $history_left = Db::getInstance()->executeS(
            'SELECT oc.id_order_carrier, te.event_description,te.id_status,
            oc.id_carrier,te.date FROM '._DB_PREFIX_.'order_carrier oc
            inner join `'._DB_PREFIX_.'hl_tracking_events` te ON oc.id_order_carrier=te.id_order_carrier
            WHERE oc.id_order_carrier='.$id_order_carrier.' ORDER BY te.id_tracking_events DESC'
        );
        return $history_left;
    }
    public static function deleteEvents($id_order_carrier)
    {
        $sql = "DELETE FROM `"._DB_PREFIX_."hl_tracking_events` WHERE id_order_carrier = '{$id_order_carrier}'";
        return Db::getInstance()->query($sql);
    }
    public function insertEvents($status)
    {
        $sql = "DELETE FROM `"._DB_PREFIX_."hl_tracking_events` WHERE id_order_carrier = '{$status->shipment_ref}'";
        Db::getInstance()->query($sql);
        if (isset($status->all_events) && is_array($status->all_events) && count($status->all_events)) {
            foreach ($status->all_events as $event) {
                $sql = "INSERT IGNORE INTO `"._DB_PREFIX_."hl_tracking_events`
                (`id_order_carrier`, `id_status`, `event_description`, `date`)
                VALUES ('{$status->shipment_ref}', '{$event['id_status']}', '".
                pSQL($event['event_description'])."', '{$event['event_date']}')";
                Db::getInstance()->query($sql);
            }
        }
    }
    public static function deleteCurrentStatus($id_order_carrier)
    {
        $sql = "DELETE FROM `"._DB_PREFIX_."hl_tracking_current_status` WHERE id_order_carrier = '{$id_order_carrier}'";
        return Db::getInstance()->query($sql);
    }
    public function insertCurrentStatus($status)
    {
        $sql = "DELETE FROM `"._DB_PREFIX_."hl_tracking_current_status`
        WHERE id_order_carrier = '{$status->shipment_ref}';";
        Db::getInstance()->execute($sql);
        $sql = "INSERT INTO "._DB_PREFIX_."hl_tracking_current_status (id_order_carrier, id_status, date)
        VALUES ({$status->shipment_ref}, '{$status->module_shipping_status_code}', '{$status->carrier_shipping_status_date}')";
        return Db::getInstance()->query($sql);
    }
    public static function searchIdStatus($id_carrier, $code, $match = false)
    {
        if ($match) {
            $sql = 'SELECT id_status FROM `'._DB_PREFIX_.'hl_tracking_status_matching` WHERE id_connector='.$id_carrier.' AND code = "'.$code.'"';
        } else {
            $sql = 'SELECT id_status FROM `'._DB_PREFIX_.'hl_tracking_status_matching` WHERE id_connector='.$id_carrier.' AND LOCATE(code, "'.$code.'") > 0';
        }
        $id_status = Db::getInstance()->getValue($sql);
        return $id_status === false?0:$id_status;
    }
    public static function getIdStatusByCarrierCode($webxml_crr, $status, $string_compare = true, $default = 0)
    {
        $status = (string)$status;
        $id_status = $default;
        if ($string_compare) {
            foreach ($webxml_crr->step as $step) {
                if (!isset($step->statuscode)) {
                    continue;
                }
                $statuscode = $step->statuscode;
                if (is_array($statuscode)) {
                    if (in_array($status, $statuscode)) {
                        $id_status = $step->id_status;
                        break;
                    }
                } else {
                    if (is_object($statuscode)) {
                        $statuscode = (array)$statuscode;
                        $statuscode = implode('', $statuscode);
                    }
                    if (strcmp($status, (string)$statuscode) === 0) {
                        $id_status = $step->id_status;
                        break;
                    }
                }
            }
        } else {
            foreach ($webxml_crr->step as $step) {
                if (!isset($step->statuscode)) {
                    continue;
                }
                $statuscode = $step->statuscode;
                if (is_array($statuscode)) {
                    foreach ($statuscode as $code) {
                        if (is_object($code)) {
                            continue;
                        }
                        if (strpos($status, $code) !== false) {
                            $id_status = $step->id_status;
                            break;
                        }
                    }
                } else {
                    if (is_object($statuscode)) {
                        continue;
                    }
                    if (strpos($status, $statuscode) !== false) {
                        $id_status = $step->id_status;
                        break;
                    }
                }
            }
        }
        return $id_status;
    }
    public function updateStatus()
    {
        $lastCheck = Configuration::getGlobalValue('HL_TRACKING_LAST_STATUSCODE_CHECK');
        $version = Configuration::getGlobalValue('HL_TRACKING_STATUSCODE_VERSION');
        $today = date('Y-m-d');
        $rs = array(
            'success' => false,
            'count' => 0
        );
        $url = 'https://helloshop.com/status_code.xml';
        if (!$this->checkExists($url)) {
            $rs['success'] = false;
            $rs['err'] = 'update not available';
            die(json_Encode($rs));
        }
        // if ($lastCheck != $today) {
            Configuration::updateGlobalValue('HL_TRACKING_LAST_STATUSCODE_CHECK', $today, 0, 0);
            $xml = simplexml_load_file($url);
            $newversion = (string)$xml->database->attributes()->name;
            if ($version != $newversion) {
                $json = json_decode(json_encode($xml), true);
                $table = $json['database']['table'];
                Db::getInstance()->execute('TRUNCATE TABLE '._DB_PREFIX_.'hl_tracking_status_matching');
                foreach ($table as $row) {
                    try {
                        $sql = 'INSERT INTO '._DB_PREFIX_.'hl_tracking_status_matching VALUES ('.$row['column'][0].', '.$row['column'][1].', "'.addslashes($row['column'][2]).'")';
                        Db::getInstance()->execute($sql);
                        $rs['count'] += 1;
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                }
                $rs['success'] = true;
                Configuration::updateGlobalValue('HL_TRACKING_STATUSCODE_VERSION', $newversion, 0, 0);
            }
        // }
        return $rs;
    }
    public function checkExists($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);
        return $status;
    }
}
