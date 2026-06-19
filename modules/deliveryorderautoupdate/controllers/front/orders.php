<?php
/**
* 2007-2023 PrestaShop
*
* Tracking Center
*
*  @author    Tofel Dahhaoui <modules@helloshop.com>
*  @copyright 2007-2018 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: https://helloshop.com
*/

class DeliveryorderautoupdateOrdersModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::initContent()
     */
    const DEFAULT_LANG = 'EN';
    public function initContent()
    {
        parent::initContent();
        $id = pSQL(Tools::getValue('order_reference'));
        if (Tools::isSubmit('id_email')) {
            $id_email = Tools::getValue('id_email');
            $order = Order::getByReference($id)->getFirst();
            if ($order) {
                $id_order = $order->id;
                Db::getInstance()->update('hl_tracking_email', array(
                    'email_status' => 3,
                ), "id = {$id_email} AND id_order = {$id_order}");
            }
        }
        if ($id) {
            $orders = Db::getInstance()->executeS(
                'SELECT o.reference, a.city as address1, c2.name, hlc.id as id_connector, oc.tracking_number,
                c2.id_carrier, c.url, oc.id_order_carrier, oc.date_add
                FROM `'._DB_PREFIX_.'orders` o
                LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery = a.id_address
                INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order = oc.id_order
                LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
                LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference=c2.id_reference AND c2.deleted=0
                LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
                LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
                WHERE o.reference LIKE "'.$id.'"
                ORDER BY oc.id_order_carrier ASC'
            );
            $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
            $webxml_crr = json_decode(
                json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
            );
            $locale = 0;
            if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.
                $this->context->language->iso_code.'/lang.php')) {
                $_LANGMAIL = array();
                include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.
                    $this->context->language->iso_code.'/lang.php');
                $locale = $_LANGMAIL['locale'];
            }
            $statuses = array();
            foreach ($webxml_crr->status as $status) {
                $statuses[$status->id_status] = $status;
            }
            setlocale(LC_TIME, $locale);
            foreach ($orders as &$order) {
                if (strpos($order['url'], '@') !== false) {
                    $order['url'] = str_replace('@', $order['tracking_number'], $order['url']);
                } elseif ($order['url']) {
                    $order['url'] = $order['url'].$order['url'];
                } else {
                    $order['url'] = null;
                }
                $history_left = TrackingModel::getTrackEvents($order['id_order_carrier']);
                $steps = TrackingModel::getTrackSteps($order['id_order_carrier']);
                foreach ($webxml_crr->status as $step) {
                    if ($step->id_status == $steps['current_status']) {
                        $iso_code = Tools::strtoupper($this->context->language->iso_code);
                        if (!isset($step->$iso_code)) {
                            $iso_code = self::DEFAULT_LANG;
                        }
                        $steps['status_text'] = $step->$iso_code;
                    }
                }
                foreach ($history_left as &$hs_) {
                    $hs_['result'] = null;
                    $time = strtotime($hs_['date']);
                    $hs_['step_date'] = EmailHelper::displayDate($time, $locale);
                    // $hs_['step_date'] = date('F jS Y', $time);
                    $hs_['step_time'] = date('H:i:s', $time);
                    foreach ($webxml_crr->status as $step) {
                        if ($step->id_status == $hs_['id_status']) {
                            $iso_code = Tools::strtoupper($this->context->language->iso_code);
                            if (!isset($step->$iso_code)) {
                                $iso_code = self::DEFAULT_LANG;
                            }
                            $hs_['status_text'] = $step->$iso_code;
                        }
                    }
                }
                $order['history_left'] = $history_left;
                $order['steps'] = $steps;
                $time = strtotime($order['date_add']);
                $order['created_date'] = EmailHelper::displayDate($time, $locale);
                $order['created_time'] = date('H:i:s', $time);
            }
            // $image = 'modules/deliveryorderautoupdate/views/img/logos/';
            $this->context->smarty->assign(array(
                'orderArr' => $orders,
                'statuses' => $statuses,
                'url_root' => $this->getUrl(),
                'image' => _THEME_SHIP_DIR_,
                'reference' => $id,
                'date_format_full' => $this->context->language->date_format_full,
                'date_format_lite' => $this->context->language->date_format_lite,
                'svg' => $this->getUrl().'modules/deliveryorderautoupdate/views/img/svg/',
            ));
        } else {
            $this->context->smarty->assign(array(
                'history_left' => null
            ));
        }
        if (preg_match_all('/^1.6/', _PS_VERSION_)) {
            $this->setTemplate('16/Orders.tpl');
        } else {
            $this->setTemplate('module:deliveryorderautoupdate/views/templates/front/17/Orders.tpl');
        }
    }
    public static function getUrl()
    {
        $force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
        $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';

        if (isset($force_ssl) && $force_ssl) {
            return $protocol_link.Tools::getShopDomainSsl().__PS_BASE_URI__;
        } else {
            return _PS_BASE_URL_.__PS_BASE_URI__;
        }
    }
    public function setMedia()
    {
        parent::setMedia();
        $url = EmailHelper::getUrl();
        $this->addJS($url.'modules/deliveryorderautoupdate/views/js/accordion.js');
        $this->addCSS($url.'modules/deliveryorderautoupdate/views/css/accordion.css');
    }
}
