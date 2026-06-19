<?php
/**
* 2007-2023 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

// require_once(_PS_MODULE_DIR_.'deliveryorderautoupdate/deliveryorderautoupdate.php');
// require_once(_PS_MODULE_DIR_.'deliveryorderautoupdate/classes/trackingmodel.php');
class ReturnOrder extends EmailHelper
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->itemsPerPage = 20;
    }

    public function getReturnFilterCondition($filter)
    {
        $filter_where = '';
        if (!$filter) {
            return $filter_where;
        }
        $condition = array();
        if (isset($filter['id_return']) && $filter['id_return']) {
            $condition[] = "tr.id_return LIKE '%{$filter['id_return']}%'";
        }
        if (isset($filter['id_order']) && $filter['id_order']) {
            $condition[] = "tr.id_order LIKE '%{$filter['id_order']}%'";
        }
        if (isset($filter['id_order_return']) && $filter['id_order_return']) {
            if ($filter['id_order_return'] === 'null') {
                $condition[] = "ors.id_order_return_state IS NULL";
            } else {
                $condition[] = "ors.id_order_return_state LIKE '%{$filter['id_order_return']}%'";
            }
        }
        if (isset($filter['customer']) && $filter['customer']) {
            $condition[] = "CONCAT(cu.firstname,' ',cu.lastname) LIKE '%{$filter['customer']}%'";
        }
        if (isset($filter['connector']) && $filter['connector']) {
            $condition[] = "tr.id_connector = {$filter['connector']}";
        }
        if (isset($filter['tracking_number']) && $filter['tracking_number']) {
            $condition[] = "tr.shipping_number LIKE '%{$filter['tracking_number']}%'";
        }
        if (isset($filter['status']) && $filter['status'] != '' && $filter['status'] != 'show_all') {
            switch ($filter['status']) {
                case 'no_delivery':
                    $condition[] = "(tr.shipping_status <> 1 OR tr.shipping_status is null)";
                    break;
                case 'delivery':
                    $condition[] = "tr.shipping_status = 1";
                    break;
                case 'no_tracking':
                    $condition[] = "tr.shipping_status is null";
                    break;
                default:
                    $condition[] = "tr.shipping_status = {$filter['status']}";
                    break;
            }
        }
        if (count($condition)) {
            $filter_where = implode(' AND ', $condition);
        }
        return $filter_where;
    }
    public function listReturn($page = 0, $filter = null)
    {
        $itemsPerPage = $this->itemsPerPage;
        $offset = $page*$itemsPerPage;
        $where = $this->getReturnFilterCondition($filter);
        $select = "SELECT tr.`id_return`,tr.id_order, tr.id_order_return,ort.date_add, ors.color as state_color,
        orsl.name as state_name, CONCAT(cu.firstname,' ',cu.lastname) AS customer,o.reference,o.date_add,
        tr.id_connector, tr.shipping_number as track_number,tr.`shipping_status`, tr.`status_date`";
        $select_count = "SELECT COUNT(tr.id_return)";
        $sql ="
        FROM `"._DB_PREFIX_."hl_tracking_return` tr
        LEFT JOIN "._DB_PREFIX_."order_return ort ON tr.id_order_return=ort.id_order_return
        LEFT JOIN "._DB_PREFIX_."order_return_state ors ON ort.state=ors.id_order_return_state
        LEFT JOIN "._DB_PREFIX_."order_return_state_lang orsl ON ors.id_order_return_state=orsl.id_order_return_state
        AND orsl.id_lang=".$this->context->language->id."
        LEFT JOIN "._DB_PREFIX_."orders o ON tr.id_order=o.id_order
        LEFT JOIN "._DB_PREFIX_."customer cu ON o.id_customer=cu.id_customer".($where?' WHERE '.$where:'')."
        ORDER BY tr.id_return DESC";
        $limit = " LIMIT ".$offset.', '.$itemsPerPage;
        $orders = Db::getInstance()->executeS($select.$sql.$limit);
        $total = Db::getInstance()->getValue($select_count.$sql);

        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/carriers.xml';
        $xml_carrier = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $file_status = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $xml_status = json_decode(
            json_encode(simplexml_load_file($file_status, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $url = self::getUrl();
        $trackingmodel = new TrackingModel();
        $context = Context::getContext();
        $shopContext = $context->cookie->shopContext;
        $id_shop = (int)$this->getIdShop($shopContext, $context->shop->id);
        $lang = 'EN';
        $context = Context::getContext();
        $v17 = preg_match_all('/^1.7/', _PS_VERSION_);
        foreach ($orders as $key => $order) {
            $event = $order['shipping_status'];
            if ($event != null) {
                foreach ($xml_status->status as $step) {
                    if ($event == $step->id_status) {
                        $iso_code = Tools::strtoupper($context->language->iso_code);
                        if (!isset($step->$iso_code)) {
                            $iso_code = $lang;
                        }
                        $iso_code = $step->$iso_code;
                        break;
                    }
                }
                $orders[$key]['event_code'] = $iso_code ? $iso_code : $event;
                $orders[$key]['code'] = $step->id_status;
                $orders[$key]['event_date'] = $order['status_date'];
                $orders[$key]['step_date'] = date(
                    $context->language->date_format_full,
                    strtotime($order['status_date'])
                );
            }
            $connector = array_filter($xml_carrier->carrier, function ($c) use ($order) {
                return $c->id_carrier == $order['id_connector'];
            });
            $orders[$key]['json_server'] = $context->link->getAdminLink('AdmindeliveryorderautoupdateAjax').'&ajax=1&action=viewResponse&track=return&shipment_ref='.$order['id_return'];
            if (count($connector)) {
                $connector = current($connector);
                $orders[$key]['connector_name'] = $connector->name;
            } else {
                $orders[$key]['connector_name'] = '';
            }
            $orders[$key]['method'] = 0;
            $orders[$key]['disable'] = false;
            $orders[$key]['id_order_carrier'] = '';
            $orders[$key]['id'] = '';
            if ($v17) {
                $link = $this->context->link;
                $orders[$key]['url'] = $this->context->link->getAdminLink(
                    'AdminOrders',
                    true,
                    [],
                    array('id_order' => $order['id_order'], 'vieworder' => 1)
                );
            } else {
                $orders[$key]['url'] = Dispatcher::getInstance()->createUrl(
                    'AdminOrders',
                    $this->context->language->id,
                    array(
                        'token' => Tools::getAdminTokenLite('AdminOrders'),
                        'id_order' => $order['id_order'],
                        'vieworder' => 1,
                    ),
                    false
                );
            }
            if ($order['state_color']) {
                $orders[$key]['brightness'] = $this->calculateBrightNess($order['state_color']);
            }
        }
        return array(
            'orders' => $orders,
            'total' => $total,
        );
    }
    public static function getReturnByOrder($id_order)
    {
        $lang = 'en';
        $context = Context::getContext();
        $iso_code = $context->language->iso_code;
        $trackingmodel = new TrackingModel();
        $url = self::getUrl();
        $shopContext = $context->cookie->shopContext;
        $id_shop = (int)(new self)->getIdShop($shopContext, $context->shop->id);

        $sql ="SELECT tr.`id_return`,tr.id_order, tr.id_order_return,ort.date_add, ors.color as state_color,
        orsl.name as state_name,o.reference,
        tr.id_connector, tr.shipping_number as track_number,tr.`shipping_status`, tr.`status_date`
        FROM `"._DB_PREFIX_."hl_tracking_return` tr
        LEFT JOIN "._DB_PREFIX_."order_return ort ON tr.id_order_return=ort.id_order_return
        LEFT JOIN "._DB_PREFIX_."order_return_state ors ON ort.state=ors.id_order_return_state
        LEFT JOIN "._DB_PREFIX_."order_return_state_lang orsl ON ors.id_order_return_state=orsl.id_order_return_state
        AND orsl.id_lang=".$context->language->id."
        LEFT JOIN "._DB_PREFIX_."orders o ON tr.id_order=o.id_order
        WHERE tr.id_order = {$id_order}
        ORDER BY tr.id_return DESC";
        $returns = Db::getInstance()->executeS($sql);
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/carriers.xml';
        $xml_carrier = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $file_status = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $xml_status = json_decode(
            json_encode(simplexml_load_file($file_status, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        foreach ($returns as $key => $return) {
            $event = $return['shipping_status'];
            if ($event != null) {
                foreach ($xml_status->status as $step) {
                    if ($event == $step->id_status) {
                        $iso_code = Tools::strtoupper($context->language->iso_code);
                        if (!isset($step->$iso_code)) {
                            $iso_code = $lang;
                        }
                        $iso_code = $step->$iso_code;
                        break;
                    }
                }
                $returns[$key]['code'] = $step->id_status;
                $returns[$key]['event_code'] = $iso_code ? $iso_code : $event;
                $returns[$key]['event_date'] = $return['status_date'];
                $returns[$key]['step_date'] = date(
                    $context->language->date_format_full,
                    strtotime($return['status_date'])
                );
            }
            $connector = array_filter($xml_carrier->carrier, function ($c) use ($return) {
                return $c->id_carrier == $return['id_connector'];
            });
            if (count($connector)) {
                $connector = current($connector);
                $returns[$key]['json_server'] = $url.'modules/deliveryorderautoupdate/webservices/'
                .$return['id_connector'].'/Carrier'
                .$return['id_connector'].'.php?shipment_ref='
                .$return['id_return'].'&parcel_number='
                .$trackingmodel->handleTrackingNumber($return['track_number'])
                .'&token='.Configuration::get('DELIVERY_TOKEN')
                .'&order_ref='
                .$return['reference'].'&id_shop='.$id_shop.'&devmode=1';
                $returns[$key]['connector_name'] = $connector->name;
            } else {
                $returns[$key]['json_server'] = '#';
                $returns[$key]['connector_name'] = '';
            }
        }
        return $returns;
    }
}
