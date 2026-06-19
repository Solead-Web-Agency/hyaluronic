<?php
/**
* 2007-2018 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2018 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

ob_start();
require_once(dirname(__FILE__).'/../../classes/trackingmodel.php');
require_once(dirname(__FILE__).'/../../deliveryorderautoupdate.php');
require_once(dirname(__FILE__).'/../../classes/emailHelper.php');
require_once(dirname(__FILE__).'/../../classes/returns.php');
require_once(dirname(__FILE__).'/../../classes/importreturnshipment.php');
require_once(dirname(__FILE__).'/../../classes/connectors/Carrier.php');

define('ITEM_PER_PAGE', 20);
class AdmindeliveryorderautoupdateAjaxController extends ModuleAdminController
{
    const EMAIL_SENT = 1;
    const DEFAULT_LANG = 'EN';
    public function __construct()
    {
        $this->name = 'deliveryorderautoupdate';
        $this->tab = 'front_office_features';
        $this->bootstrap = true;
        $this->lang = true;
        $this->context = Context::getContext();
        $this->secure_key = Tools::hash($this->name);
        parent::__construct();
        $this->url = EmailHelper::getUrl();
        $helper = new EmailHelper();
        $shopContext = Context::getContext()->cookie->shopContext;
        $this->id_shop = (int)$helper->getIdShop($shopContext, $this->context->shop->id);
        $this->method = array("Button", "Robot", "Push", "Cron", "Force");
        $this->email_status = array("", "Sent", "", "Clicked");
        $this->v17 = preg_match_all('/^1.7/', _PS_VERSION_);
        $this->tabAccess['view'] = '1';
        $this->itemsPerPage = 50;
    }
    public function ajaxProcessCarriers()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != Configuration::get('DELIVERY_TOKEN')) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $carrier = array();
        $bg_color = array();
        $helper = new EmailHelper();
        $id = Tools::getValue("id");
        $date_ = pSQL(Configuration::get('DELIVERY_ORDER_DATE'));
        $tpl = $this->createTemplate('message_info.tpl');
        $link = new Link();
        $tpl->assign(array(
            'url' => $this->url,
            'admin_url' => $link->getAdminLink('AdmindeliveryorderautoupdateAjax'),
            'config_url' => $link->getAdminLink('AdminModules')
            .'&configure=deliveryorderautoupdate&module_name=deliveryorderautoupdate',
        ));
        $querycurrent_state = '('.pSQL(Configuration::get('DELIVERY_ORDER_STATUS_FROM')).')';
        $carrier = Db::getInstance()->executeS(
            'SELECT hlc.name
            FROM '._DB_PREFIX_.'orders o
            INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
			INNER JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
            INNER JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
            INNER JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
            WHERE o.id_shop='.(int)$this->context->shop->id.' AND  o.current_state IN '
            .$querycurrent_state.($id ? ' AND hlc.id='.(int)$id : '').' '
            .($date_ ? ' AND o.date_add >="'.pSQL($date_).'" ' : '' ).'
            GROUP BY hlc.id'
        );

        $tracking_number = Db::getInstance()->executeS(
            'SELECT DISTINCT o.id_order,oc.tracking_number, c.active, hlc.name,
            hlc.id, hlc.method, hlc.url, c.url as tracking_url
            FROM '._DB_PREFIX_.'orders o
            INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
			INNER JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
            INNER JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
            INNER JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id

            WHERE o.id_shop='.(int)$this->context->shop->id.' AND  o.current_state IN '
            .$querycurrent_state.($id ? ' AND hlc.id='.(int)$id : '').' '
            .($date_ ? ' AND o.date_add >="'.pSQL($date_).'" ' : '' ).'
            GROUP BY o.id_order ORDER BY o.id_order DESC'
        );

        $orders = array();

        date_default_timezone_set('Europe/Paris');
        header('Content-Type: text/html; charset=utf-8');

        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        foreach ($tracking_number as $key => $number) {
            $event_historycode = Db::getInstance()->getRow(
                'SELECT event_code, date_add, carrier_response, success_response
                FROM '._DB_PREFIX_.'hl_tracking_history
                WHERE id_order='.(int)$number['id_order'].' ORDER BY id DESC'
            );
            if ($number['method'] == 0) {
                // $url_modulecarrier = Db::getInstance()->getRow(
                //     "SELECT url FROM "._DB_PREFIX_."hl_carrier WHERE id=".(int)$number['id']
                // );

                $event = $helper->getEvent($number['id_order']);
                $event_code = $event['event'];
                $orders[$key]['event_date'] = $event['date_add'];
            }

            $orders[$key]['last_result'] = $event_historycode['carrier_response'];
            $orders[$key]['success_response'] = $event_historycode['success_response'];
            $orders[$key]['track_number'] = $number['tracking_number'];
            foreach ($webxml_crr->status as $step) {
                if (strpos($event_code, $step->id_status) !== false) {
                    $iso_code = Tools::strtoupper($this->context->language->iso_code);
                    if (!isset($step->$iso_code)) {
                        $iso_code = self::DEFAULT_LANG;
                    }
                    $iso_code = $step->id_status.'_'.$step->$iso_code;
                    break;
                }
            }
            $orders[$key]['event_code'] = $iso_code ? $iso_code : $event_code;
            $id_order = Db::getInstance()->getRow(
                "SELECT o.id_order, o.reference, osl.name as current_state_name, o.current_state, os.color
                FROM "._DB_PREFIX_."orders o
                LEFT JOIN "._DB_PREFIX_."order_state_lang osl ON o.current_state = osl.id_order_state
                AND osl.id_lang=".(int)$this->context->language->id."
                LEFT JOIN "._DB_PREFIX_."order_state os ON o.current_state = os.id_order_state
                WHERE o.id_shop=".(int)$this->context->shop->id."
                AND  o.id_order=".(int)$number['id_order']." AND o.current_state IN ".$querycurrent_state
            );

            $orders[$key]['current_state'] = $id_order['current_state_name'];
            $orders[$key]['current_state_id'] = $id_order['current_state'];
            $orders[$key]['current_state_color'] = $id_order['color'];
            $orders[$key]['id_order'] = $number['id_order'];
            $orders[$key]['carrier'] = $number['name'];
            $orders[$key]['method'] = $number['method'];
            if (strpos($number['tracking_url'], '@') !== false) {
                $orders[$key]['tracking_url'] = str_replace('@', $number['tracking_number'], $number['tracking_url']);
            } else {
                $orders[$key]['tracking_url'] = $number['tracking_url'].$number['tracking_number'];
            }
        }

        $status = Db::getInstance()->executeS(
            'SELECT name
            FROM '._DB_PREFIX_.'order_state_lang
            WHERE id_lang = '.(int)$this->context->language->id.'
            AND id_order_state IN '.$querycurrent_state.'
            GROUP BY id_order_state'
        );


        $bg_color['vail_number'] = $webxml_crr->status[4]->color;
        $bg_color['warning'] = $webxml_crr->status[2]->color;
        $bg_color['enable'] = $webxml_crr->status[1]->color;
        $bg_color['default'] = $webxml_crr->status[0]->color;
        $bg_color['take_deli'] = $webxml_crr->status[3]->color;
        $bg_color['returned_shipper'] = $webxml_crr->status[7]->color;
        $bg_color['out_delivery'] = $webxml_crr->status[8]->color;
        $bg_color['delivered_shop'] = $webxml_crr->status[6]->color;

        $tpl->assign(array(
            'orders' => $orders,
            'id' => $id,
            'status' => $status,
            'bg_color' => $bg_color,
            'carrier' => $carrier,
            'url' => $this->url,
            'l' => Tools::getValue('l'),
            'onlyshow' => Tools::getValue('onlyshow'),
            'token' => Tools::getValue('token'),
            'admin_url' => $link->getAdminLink('AdmindeliveryorderautoupdateAjax'),
            'config_url' => $link->getAdminLink('AdminModules')
            .'&configure=deliveryorderautoupdate&module_name=deliveryorderautoupdate',
            'total_order' => null,
            'error_message' => null
        ));

        echo $tpl->fetch();
    }
    public function ajaxProcessAllCountry()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != Configuration::get('DELIVERY_TOKEN')) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $l = Tools::getValue('l');
        if ($l == 0) {
            // module list carriers exist on carriers.xml
            $id_search = Tools::getValue('id');
            $carrier_search = Tools::getValue('carrier');
            $country_search = Tools::getValue('country');
            $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/carriers.xml';

            $carrier_xml = array();
            $country_xml = array();
            $webxml_crr = json_decode(
                json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
            );
            usort($webxml_crr->carrier, function ($a, $b) {
                return strcasecmp($a->name, $b->name);
            });
            if (isset($webxml_crr->carrier->id_carrier) && $webxml_crr->carrier->id_carrier) {
                if (is_array($webxml_crr->carrier->country)) {
                    foreach ($webxml_crr->carrier->country as $cnn) {
                        if (!in_array($cnn, $country_xml)) {
                            $country_xml[] = $cnn;
                        }
                    }
                    $country_vl = implode(',', $webxml_crr->carrier->country);
                    $country_vl1 = $webxml_crr->carrier->country[0];
                } else {
                    if (!in_array($webxml_crr->carrier->country, $country_xml)) {
                        $country_xml[] = $webxml_crr->carrier->country;
                    }
                    $country_vl = $webxml_crr->carrier->country;
                    $country_vl1 = $webxml_crr->carrier->country;
                }
                $country_filter = ($country_search ? $country_search : $country_vl1);
                if ((strpos($country_vl, $country_filter) !== false) &&
                    ((($id_search) && ($id_search == $webxml_crr->carrier->id_carrier)) || (!$id_search)) &&
                    (
                        (
                            ($carrier_search) &&
                            strpos(
                                Tools::strtolower($webxml_crr->carrier->name),
                                Tools::strtolower($carrier_search)
                            ) !== false
                        ) || (!$carrier_search)
                    )
                ) {
                    $carrier_xml[0]['id'] = $webxml_crr->carrier->id_carrier;
                    $carrier_xml[0]['name'] = $webxml_crr->carrier->name;
                    $country = Db::getInstance()->getRow(
                        "SELECT name
                        FROM "._DB_PREFIX_."country_lang cl
                        INNER JOIN "._DB_PREFIX_."country c ON c.id_country = cl.id_country AND cl.id_lang="
                        .(int)$this->context->language->id." WHERE c.iso_code LIKE '".pSQL($country_filter)."'"
                    );

                    $carrier_xml[0]['country'] = $country['name'];
                    $carrier_xml[0]['url'] = $webxml_crr->carrier->submodule->url_webservice;
                    $url_webservice = _PS_ROOT_DIR_.$webxml_crr->carrier->submodule->url_webservice;
                    $check_crr = Db::getInstance()->getRow(
                        "SELECT id, url FROM "._DB_PREFIX_."hl_carrier WHERE id=".(int)$webxml_crr->carrier->id_carrier
                    );
                    $url_freeservice = _PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'
                    .$webxml_crr->carrier->id_carrier.'/Carrier'.$webxml_crr->carrier->id_carrier.'.php';

                    // add this variable to validate on dev mod
                    $carrier_xml[0]['add_connector'] = 0;
                    $carrier_xml[0]['add_cr'] = 0;
                    // end
                    $url_logo_carrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/img/logos/'
                    .$webxml_crr->carrier->id_carrier.'.jpg';
                    if (file_exists($url_logo_carrier)) {
                        $carrier_xml[0]['logo'] = 1;
                    } else {
                        $carrier_xml[0]['logo'] = 0;
                    }
                    if (isset($check_crr['id']) === false) {
                        $carrier_xml[0]['add_cr'] = 1;
                    } elseif ((file_exists($url_webservice) || (file_exists($url_freeservice))) &&
                        (isset($check_crr['id']))
                    ) {
                        $carrier_xml[0]['add_cr'] = 2;
                        if (file_exists($url_freeservice) &&
                            (isset($check_crr['url'])) &&
                            (!empty($check_crr['url']))
                        ) {
                            $carrier_xml[0]['add_connector'] = 3;
                        } elseif (file_exists($url_webservice) && (empty($check_crr['url']))) {
                            $carrier_xml[0]['add_connector'] = 4;
                        } elseif ((!file_exists($url_webservice)) && (!file_exists($url_freeservice))) {
                            $carrier_xml[0]['add_connector'] = 5;
                        }
                    }
                }
            } else {
                foreach ($webxml_crr->carrier as $key => $xml_crr) {
                    if (is_array($xml_crr->country)) {
                        foreach ($xml_crr->country as $cnn) {
                            if (!in_array($cnn, $country_xml)) {
                                $country_xml[] = $cnn;
                            }
                        }
                        $country_vl = implode(',', array_map(function ($c) {
                            return "'{$c}'";
                        }, $xml_crr->country));
                        $country_vl1 = $xml_crr->country[0];
                    } else {
                        if (!in_array($xml_crr->country, $country_xml) &&
                            Tools::strtolower($xml_crr->country) != 'all') {
                            $country_xml[] = $xml_crr->country;
                        }
                        $country_vl = "'{$xml_crr->country}'";
                        $country_vl1 = $xml_crr->country;
                    }
                    $country_filter = ($country_search ? $country_search : $country_vl1);
                    if ((strpos($country_vl, $country_filter) !== false) &&
                        ((($id_search) && ($id_search == $xml_crr->id_carrier)) || (!$id_search)) &&
                        (
                            (
                                ($carrier_search) &&
                                strpos(Tools::strtolower($xml_crr->name), Tools::strtolower($carrier_search)) !== false
                            ) || (!$carrier_search)
                        )
                    ) {
                        $carrier_xml[$key]['id'] = $xml_crr->id_carrier;
                        $carrier_xml[$key]['name'] = $xml_crr->name;
                        $carrier_xml[$key]['detail'] = isset($xml_crr->detail)?$xml_crr->detail:'';
                        if (Tools::strtolower($country_vl1) == 'all') {
                            $carrier_xml[$key]['country'] = $this->module->l('Global');
                            $carrier_xml[$key]['iso_code'] = 'ALL';
                        } else {
                            $country = Db::getInstance()->executeS(
                                "SELECT name, iso_code
                                FROM "._DB_PREFIX_."country_lang cl
                                INNER JOIN "._DB_PREFIX_."country c ON c.id_country = cl.id_country
                                AND cl.id_lang=".(int)$this->context->language->id."
                                WHERE c.iso_code IN (".$country_vl.")"
                            );
                            $carrier_xml[$key]['country'] = implode(', ', array_map(function ($c) {
                                return $c['name'];
                            }, $country));
                            $carrier_xml[$key]['iso_code'] = implode(', ', array_map(function ($c) {
                                return $c['iso_code'];
                            }, $country));
                        }
                        // $carrier_xml[$key]['url'] = $xml_crr->submodule->url_webservice;
                        // $url_webservice = _PS_ROOT_DIR_.$xml_crr->submodule->url_webservice;
                        $check_crr = Db::getInstance()->getRow(
                            "SELECT id, url FROM "._DB_PREFIX_."hl_carrier WHERE id=".(int)$xml_crr->id_carrier
                        );
                        $url_freeservice = _PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'
                        .$xml_crr->id_carrier.'/Carrier'.$xml_crr->id_carrier.'.php';
                        // add this variable to validate on dev mod
                        $carrier_xml[$key]['add_connector'] = 0;
                        $carrier_xml[$key]['add_cr'] = 0;
                        // end
                        $url_logo_carrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/img/logos/'
                        .$xml_crr->id_carrier.'.jpg';
                        if (file_exists($url_logo_carrier)) {
                            $carrier_xml[$key]['logo'] = 1;
                        } else {
                            $carrier_xml[$key]['logo'] = 0;
                        }
                        if (isset($check_crr['id']) === false) {
                            $carrier_xml[$key]['add_cr'] = 1;
                        } else {
                            $carrier_xml[$key]['add_cr'] = 2;
                        }
                    }

                    $credentials_embed =_PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'
                    .$xml_crr->id_carrier.'/credentials'.$xml_crr->id_carrier.'.xml';
                    $credentials = json_decode(
                        json_encode(@simplexml_load_file($credentials_embed, 'SimpleXMLElement', LIBXML_NOCDATA))
                    );
                    $carrier_xml[$key]['hasCredential'] = isset($credentials->credential) &&
                    !empty($credentials->credential);

                    if (isset($credentials->credential->credname)) {
                        $credname = Configuration::get($credentials->credential->credname) === false;
                        if ($credname) {
                            $carrier_xml[$key]['hasCredential'] = false;
                        }
                    } elseif (isset($credentials->credential)) {
                        foreach ($credentials->credential as $crr) {
                            $credname = Configuration::get($crr->credname) === false;
                            if ($credname) {
                                $carrier_xml[$key]['hasCredential'] = false;
                            }
                        }
                    }
                }
            }
            $country = array(array('id' => 'ALL', 'name' => $this->module->l('Global')));
            foreach ($country_xml as $key => $cnn) {
                $ct_name = Db::getInstance()->getRow(
                    "SELECT name
                    FROM "._DB_PREFIX_."country_lang cl
                    INNER JOIN "._DB_PREFIX_."country c ON c.id_country = cl.id_country
                    AND cl.id_lang=".(int)$this->context->language->id."
                    WHERE c.iso_code LIKE '".pSQL($cnn)."'"
                );
                $country[] = array(
                    'id' => $cnn,
                    'name' => $ct_name['name'],
                );
            }
            usort($country, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            $tpl = $this->createTemplate('ajax_allcountry.tpl');
            $tpl->assign(array(
                'carrier_xml' => $carrier_xml,
                'url_root' => $this->url,
                'country_xml' => $country,
                'id_search' => $id_search,
                'carrier_search' => $carrier_search,
                'country_search' => $country_search,
                'url_ajax' => Tools::getValue('url_ajax'),
            ));
            echo $tpl->fetch();
        } elseif ($l == 1) {
            $id = Tools::getValue('id');
            $name = Tools::getValue('name');
            $url = Tools::getValue('url');
            $result = Db::getInstance()->insert(
                'hl_carrier',
                array(
                    'id' => (int)$id,
                    'name' => pSQL($name),
                    'url' => pSQL($url),
                    'active' => 0,
                    'method' => 0
                )
            );
            die(json_encode($result));
        // } elseif ($l == 2) {
            // reload my couriers
            // $order_carrier = Db::getInstance()->executeS("SELECT hc.* FROM "._DB_PREFIX_."hl_carrier hc");
            // foreach ($order_carrier as &$cr) {
            //     $url_freeservice = _PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'
            //     .$cr['id'].'/Carrier'.$cr['id'].'.php';
            //     $url_logo_carrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/img/logos/'.$cr['id'].'.jpg';
            //     if (file_exists($url_logo_carrier)) {
            //         $cr['logo'] = 1;
            //     } else {
            //         $cr['logo'] = 0;
            //     }
            //     if (file_exists($url_freeservice) && $cr['url']) {
            //         $cr['connector'] = 1;
            //     } else {
            //         $cr['connector'] = 0;
            //     }
            // }
            // $tpl = $this->createTemplate('ajax_mycouriers.tpl');
            // $tpl->assign(array(
            //     'link' => $this->context->link,
            //     'order_carrier' => $order_carrier,
            //     'js_link' => $this->url.'modules/deliveryorderautoupdate/views/js/module_conf.js',
            //     'url_root' => $this->url,
            //     'url_ajax' => Tools::getValue('url_ajax')
            // ));
            // echo $tpl->fetch();
        // } elseif ($l == 3) {
        //     $id = (int)Tools::getValue('id');
        //     $carrier = pSQL(Tools::getValue('carrier'));
        //     $status = (int)Tools::getValue('status');
        //     $method = (int)Tools::getValue('method');

        //     $where = ($id ? ' AND hc.id = '.(int)$id : '');
        //     $where .= ($carrier ? ' AND hc.name Like "'.$carrier.'%"' : '');
        //     $where .= ($status ? ' AND hc.active = '.($status == 2 ? 0 : 1) : '');
        //     $where .= ($method ? ' AND hc.method = '.$method : '');
        //     $order_carrier = Db::getInstance()->executeS(
        //         "SELECT hc.*
        //         FROM "._DB_PREFIX_."hl_carrier hc"
        //         .($where ? ' WHERE '.Tools::substr($where, 4) : '')
        //     );
        //     foreach ($order_carrier as &$cr) {
        //         $url_logo_carrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/img/logos/'.$cr['id'].'.jpg';
        //         if (file_exists($url_logo_carrier)) {
        //             $cr['logo'] = 1;
        //         } else {
        //             $cr['logo'] = 0;
        //         }
        //     }
        //     $tpl = $this->createTemplate('ajax_mycouriers.tpl');
        //     $tpl->assign(array(
        //         'link' => $this->context->link,
        //         'order_carrier' => $order_carrier,
        //         'url_root' => $this->url,
        //         'url_ajax' => Tools::getValue('url_ajax')
        //     ));
        //     echo $tpl->fetch();
        } elseif ($l == 4) {
            // general tab
            $cron_url = Deliveryorderautoupdate::returnFrontUrl()
            .'modules/deliveryorderautoupdate/cron_status.php?token='
            .Tools::getAdminTokenLite('AdminModules');

            $order_export = Db::getInstance()->executeS(
                "SELECT id_order_state as id, name
                FROM "._DB_PREFIX_."order_state_lang
                WHERE id_lang = ".(int)$this->context->language->id
            );

            $connectors = Db::getInstance()->executeS(
                'SELECT id, name FROM '._DB_PREFIX_.'hl_carrier WHERE active=1 ORDER BY id DESC'
            );

            $tpl = $this->createTemplate('ajax_general.tpl');
            $tpl->assign(array(
                'link' => $this->context->link,
                'date_requestorder' => Configuration::get('DELIVERY_ORDER_DATE'),
                'order_export' => $order_export,
                'export_from' => Configuration::get('DELIVERY_ORDER_STATUS_FROM'),
                'event_code_mail' => Configuration::get('DELIVERY_EVENT_CODE_MAIL'),
                'order_status_mailupdate' => Configuration::get('HL_TRACKING_STATUS_1'),
                'connectors' => $connectors,
                'cron_url' => $cron_url,
                'url_root' => $this->url,
                'url_ajax' => Tools::getValue('url_ajax')
            ));
            echo $tpl->fetch();
        } elseif ($l == 5) {
            $id = (int)Tools::getValue('id');
            $deleteCre = Tools::getValue('deleteCre') == 'true'?true:false;
            if ($id) {
                Db::getInstance()->delete('hl_carrier', 'id='.$id);
                Db::getInstance()->delete('hl_carrier_matching', 'id_carrier_hl='.$id);

                if ($deleteCre) {
                    $credentials_embed =_PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'
                    .$id.'/credentials'.$id.'.xml';
                    $credentials = json_decode(
                        json_encode(@simplexml_load_file($credentials_embed, 'SimpleXMLElement', LIBXML_NOCDATA))
                    );
                    if (isset($credentials->credential->credname)) {
                        Configuration::deleteByName($credentials->credential->credname);
                    } else {
                        foreach ($credentials->credential as $crr) {
                            Configuration::deleteByName($crr->credname);
                        }
                    }
                }
            }
        }
    }
    public function sendEmail($id_order_carrier)
    {
        $trackingmodel = new TrackingModel();
        $order = $this->getOrder($id_order_carrier);
        $status = $trackingmodel->track($order);
        $email_sent = $trackingmodel->sendMail($order, $status, true);
        return $email_sent;
    }
    private function getOrder($id_order_carrier)
    {
        $order = Db::getInstance()->getRow(
            'SELECT DISTINCT o.id_order, oc.id_order_carrier,o.id_shop,o.id_lang,
            o.reference, oc.tracking_number,
            hlc.name as carrier, hlc.id, hlc.method, hlc.url, c.url as tracking_url
            FROM '._DB_PREFIX_.'orders o
            INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
            INNER JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
            INNER JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
            INNER JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
            WHERE oc.id_order_carrier='.$id_order_carrier
        );
        return $order;
    }
    private function getReturn($id_return)
    {
        $return = Db::getInstance()->getRow(
            'SELECT tr.*, tr.id_return as id_order_carrier, tr.shipping_number as tracking_number, tr.id_connector as hl_carrier, o.reference FROM '._DB_PREFIX_.'hl_tracking_return tr
            LEFT JOIN '._DB_PREFIX_.'orders o ON tr.id_order=o.id_order
            where id_return = '.(int)$id_return
        );
        return $return;
    }
    // public function sendEmail($id_order_carrier)
    // {
    //     $insert = Db::getInstance()->insert('hl_tracking_email', array(
    //         'id_order_carrier'      => (int)$id_order_carrier,
    //         'date_sent'     => date('Y-m-d H:i:s'),
    //         'email_status'  => self::EMAIL_SENT,
    //     ));
    //     if (!$insert) {
    //         return false;
    //     }
    //     $id_email = Db::getInstance()->Insert_ID();
    //     $id_shop = (Configuration::get("PS_MULTISHOP_FEATURE_ACTIVE") ? $this->context->shop->id : null);
    //     $langs = count(Language::getLanguages());
    //     $client_email = Db::getInstance()->getRow(
    //         'SELECT email, o.id_shop, o.reference, CONCAT(c.firstname," ",c.lastname) as customer_name,
    //         date(o.date_add) as date_add, o.shipping_number,oc.tracking_number,
    //         o.reference, hlc.name, o.id_lang as language, c.firstname, c.lastname, oc.id_order
    //         FROM '._DB_PREFIX_.'customer c
    //         INNER JOIN '._DB_PREFIX_.'orders o ON o.id_customer = c.id_customer
    //         INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
    //         LEFT JOIN '._DB_PREFIX_.'carrier ca ON oc.id_carrier = ca.id_carrier
    //         INNER JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON ca.id_reference=hlcm.id_carrier_ps
    //         INNER JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
    //         WHERE oc.id_order_carrier='.(int)$id_order_carrier
    //     );
    //     $language = $client_email['language'];
    //     $status_tracks = Db::getInstance()->executeS(
    //         'SELECT MIN(step_date) as date_,event_code, carrier_response, email_sent, step_date
    //         FROM `'._DB_PREFIX_.'hl_tracking_history`
    //         WHERE id_order_carrier='.(int)$id_order_carrier.' AND event_code NOT IN(0)
    //         GROUP BY id_order_carrier,event_code
    //         ORDER BY id DESC'
    //     );
    //     $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
    //     $webxml_crr = json_decode(
    //         json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
    //     );
    //     $webxml_statuses = array();
    //     foreach ($webxml_crr->status as $step) {
    //         $webxml_statuses[$step->id_status] = $step;
    //     }
    //     $lang_id = Db::getInstance()->getRow(
    //         'SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$language
    //     );
    //     $lang_iso_code = $lang_id['iso_code'];
    //     $iso_code = Tools::strtoupper($lang_iso_code);
    //     $courier = $client_email['name'];
    //     $stt1 = isset($webxml_statuses[$status_tracks[0]['event_code']]->$iso_code)?
    //     $webxml_statuses[$status_tracks[0]['event_code']]->$iso_code:
    //     $webxml_statuses[$status_tracks[0]['event_code']]->EN;
    //     $html = EmailHelper::displayOrderDetail($client_email['reference'], $language);
    //     $meta = Meta::getMetaByPage('module-deliveryorderautoupdate-orders', $language);
    //     if ($client_email['reference']) {
    //         $link_tracking = $this->url
    //         .($langs > 1?$lang_id['iso_code'].'/':'').$meta['url_rewrite'].'?order_reference='
    //         .$client_email['reference'].'&id_email='.$id_email;
    //     } else {
    //         $link_tracking = 'no order has been found';
    //     }

    //     $params = array(
    //         '{current_status}' => $stt1,
    //         '{background}' => $webxml_statuses[$status_tracks[0]['event_code']]->color,
    //         '{order_reference}' => $client_email['reference'],
    //         '{customer_name}' => $client_email['customer_name'],
    //         '{firstname}' => $client_email['firstname'],
    //         '{lastname}' => $client_email['lastname'],
    //         '{date}' => $client_email['date_add'],
    //         '{body_content}' => $html,
    //         '{link_tracking}' => $link_tracking,
    //         '{courier}' => $courier,
    //         '{track_link}' => $link_tracking
    //     );
    //     if (Configuration::get('HL_TRACKING_EMAIL_SUBJECT') == 'fixed') {
    //         $subject = Configuration::get('DELIVERY_EMAIL_SUBJECT', $language);
    //     } else {
    //         $subject = EmailHelper::getSubjectById($id_order_carrier, $language);
    //     }
    //     $subject = $subject?$subject:'NO_SUBJECT';
    //     $id_lang = $language;
    //     $rs = Mail::Send(
    //         $id_lang,
    //         'tracking',
    //         $subject,
    //         $params,
    //         $client_email['email'],
    //         null,
    //         null,
    //         null,
    //         null,
    //         null,
    //         _PS_ROOT_DIR_.'/modules/deliveryorderautoupdate/mails/',
    //         false,
    //         ($client_email['id_shop'] ? $client_email['id_shop'] : $id_shop),
    //         null
    //     );
    //     return $rs;
    // }
    public function ajaxProcessBulkEditCarrier()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_orders = Tools::getValue('id_orders');
        $ids = pSQL(implode(', ', $id_orders));
        $id_carrier = (int)Tools::getValue('id_carrier');
        $sql = "UPDATE `"._DB_PREFIX_."order_carrier` SET `id_carrier`={$id_carrier} WHERE id_order_carrier IN({$ids})";
        // $sql2 = "UPDATE "._DB_PREFIX_."orders ors SET ors.id_carrier={$id_carrier} WHERE ors.id_order IN({$ids})";
        try {
            $rs['success'] = Db::getInstance()->execute($sql);
        } catch (Exception $e) {
            $rs['success'] = false;
            $rs['error'] = $e->getMessage();
        }
        die(json_encode($rs));
    }
    public function ajaxProcessSendBulk()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_orders = Tools::getValue('id_orders');
        foreach ($id_orders as $id_order) {
            $this->sendEmail($id_order);
        }
    }
    public function ajaxProcessSendMail()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order = Tools::getValue('id_order');
        $rs['success'] = $this->sendEmail($id_order);
        die(json_encode($rs));
    }
    public function ajaxProcessResend()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order = Tools::getValue('id_order');
        $rs['success'] = $this->sendEmail($id_order);
        die(json_encode($rs));
    }
    public function ajaxProcessTrackReturn()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $trackingmodel = new TrackingModel();
        $id_return = (int)Tools::getValue("id_return");
        date_default_timezone_set('Europe/Paris');
        $tracking_number = $this->getReturn($id_return);
        $status = $trackingmodel->trackReturn($tracking_number);
        $trackingmodel->updateReturn($tracking_number, $status);
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $statuses = array();
        foreach ($webxml_crr->status as $s) {
            $statuses[$s->id_status] = $s;
        }
        $tpl = $this->createTemplate('ajax_trackreturn.tpl');
        $tpl->assign(array(
            'status' => $status,
            'url_root' => $this->url,
            'statuses' => $statuses,
            'lang' => Tools::strtoupper($this->context->language->iso_code)
        ));
        $data = array();
        $data['last_result_carrier'] = $tpl->fetch();
        die(json_encode($data));
    }
    public function ajaxProcessDeleteReturn()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        try {
            $ids = Tools::getValue('ids');
            $rs['success'] = ImportReturnShipment::deleteReturn($ids);
        } catch (Exception $e) {
            $rs['success'] = false;
            $rs['msg'] = $e->getMessage();
        }
        die(json_encode($rs));
    }
    public function ajaxProcessCheckModule()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        try {
            $module = Tools::getValue('module_name');
            $import = new ImportReturnShipment();
            switch ($module) {
                case 'colissimo':
                    $tableExist = $import->checkColissimo();
                    if ($tableExist) {
                        $rs['success'] = true;
                        $rs['msg'] = $import->countrowsColissimo(). ' return shipments will be imported';
                    } else {
                        $rs['success'] = false;
                        $rs['msg'] = $this->module->l('Module is not installed');
                    }
                    break;

                default:
                    $rs['success'] = false;
                    $rs['msg'] = 'module not valid';
                    break;
            }
        } catch (Exception $e) {
            $rs['success'] = false;
            $rs['msg'] = $e->getMessage();
        }
        die(json_encode($rs));
    }
    public function ajaxProcessImportReturn()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        try {
            $id_connector = (int)Tools::getValue('id_connector');
            $module = Tools::getValue('module_name');
            $import = new ImportReturnShipment();
            switch ($module) {
                case 'colissimo':
                    $rs['success'] = $import->importColissimo($id_connector);
                    break;

                default:
                    $rs['success'] = false;
                    $rs['msg'] = 'module not valid';
                    break;
            }
        } catch (Exception $e) {
            $rs['success'] = false;
            $rs['msg'] = $e->getMessage();
        }
        die(json_encode($rs));
    }
    public function ajaxProcessUpdateCarriers()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $trackingmodel = new TrackingModel();
        // $langs = count(Language::getLanguages());
        $id_order_carrier = (int)Tools::getValue("orders");
        $link = new Link();
        $orders = array();
        $shipping_status_text = null;
        $server_success = 0;
        $email_sent = false;
        date_default_timezone_set('Europe/Paris');
        header('Content-Type: text/html; charset=utf-8');
        // $date_ = pSQL(Configuration::get('DELIVERY_ORDER_DATE'));
        $delivery_order_status_from = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_FROM'));
        // $querycurrent_state = '('.pSQL(Configuration::get('DELIVERY_ORDER_STATUS_FROM')).')';

        $tracking_number = $this->getOrder($id_order_carrier);
        $id_order = $tracking_number['id_order'];
        $order = new Order((int)$id_order);


        $order_ = Db::getInstance()->getRow(
            "SELECT o.id_order, o.reference, os.color, osl.id_order_state, osl.name, o.id_lang
            FROM "._DB_PREFIX_."orders o
            INNER JOIN "._DB_PREFIX_."order_carrier oc ON o.id_order=oc.id_order
            INNER JOIN "._DB_PREFIX_."order_state_lang osl ON o.current_state = osl.id_order_state
            AND osl.id_lang=".(int)$this->context->language->id."
            INNER JOIN "._DB_PREFIX_."order_state os ON o.current_state = os.id_order_state
            WHERE oc.id_order=".(int)$id_order_carrier.
            ($delivery_order_status_from?" AND o.current_state IN ({$$querycurrent_state})":'')
        );

        $orders['id_order'] = $order_['id_order'];
        $orders['current_state_color'] = $order_['color'];
        $orders['iconstatus'] = 0;
        $orders['result'] = 5;

        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $orders['event_time'] = null;
        if ($tracking_number) {
            if (strpos($tracking_number['tracking_url'], '@') !== false) {
                $orders['tracking_url'] = str_replace(
                    '@',
                    $tracking_number['tracking_number'],
                    $tracking_number['tracking_url']
                );
            } else {
                $orders['tracking_url'] = $tracking_number['tracking_url'].$tracking_number['tracking_number'];
            }
            if ($tracking_number['method'] == 0) {
                    // $id_lang = EmailHelper::checkexistEmail($tracking_number['id_lang']);
                $status = $trackingmodel->track($tracking_number);
                $trackingmodel->insertTrackHistory($tracking_number, $status);
                $email_sent = $trackingmodel->sendMail($tracking_number, $status);
                if ($id_order) {
                    $trackingmodel->updateOrder($order, $status);
                }
                $orders['carrier'] = $tracking_number['id'];
                $orders['status'] = $status->carrier_server_status_code ?
                $status->carrier_server_status_code : $status->carrier_shipping_status_code;
                $orders['result'] = $trackingmodel->getLastEventCode();
                $orders['iconstatus'] = 1;
            }
        }
        $event_date = Db::getInstance()->getRow(
            'SELECT date_add, step_date
            FROM '._DB_PREFIX_.'hl_tracking_history
            WHERE id_order_carrier='.(int)$id_order_carrier.'
            ORDER BY id DESC'
        );
        $orders['event_date'] = $event_date['date_add'];
        $orders['step_date'] = date($this->context->language->date_format_full, strtotime($event_date['step_date']));
        $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/jquery.counter.js', 'all');
        $statuses = array();
        foreach ($webxml_crr->status as $s) {
            $statuses[$s->id_status] = $s;
        }
        $tpl = $this->createTemplate('ajax_statuscarrier.tpl');
        $tpl->assign(array(
            'order' => $orders,
            'id_carrier' => $tracking_number['id'],
            'admin_url' => $link->getAdminLink('AdmindeliveryorderautoupdateAjax'),
            'url_root' => $this->url,
            'carrier_stt' => Tools::getValue('carrier_stt'),
            'statuses' => $statuses,
            'email_sent' => $email_sent,
        ));
        $status_carrier = $this->createTemplate('ajax_statusorders.tpl');
        $status_carrier->assign(array(
            'order' => $shipping_status_text,
            // 'carrier_server_status_text' => isset($carrier_server_status_text)?$carrier_server_status_text:'',
            'server_success' => $server_success,
        ));
        $data = array();
        $data['last_result_carrier'] = $tpl->fetch();
        $data['status_carrier'] = $status_carrier->fetch();
        $data['email_sent'] = $email_sent;
        die(json_encode($data));
    }
    public function ajaxProcessUpdateCarrier()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $trackingmodel = new TrackingModel();
        $id_carrier = (int)Tools::getValue("id_carrier");
        $id_order = (int)Tools::getValue("id_order");
        $id_order_carrier = (int)Tools::getValue("id_order_carrier");
        if (!Validate::isUnsignedId($id_carrier)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_carrier';
            die(json_encode($rs));
        }
        if (!Validate::isUnsignedId($id_order_carrier)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_order_carrier';
            die(json_encode($rs));
        }
        $prefix = _DB_PREFIX_;
        $sql = "UPDATE {$prefix}order_carrier oc
        SET oc.id_carrier={$id_carrier} WHERE oc.id_order_carrier={$id_order_carrier}";
        $sql2 = "UPDATE {$prefix}orders ors SET ors.id_carrier={$id_carrier} WHERE ors.id_order={$id_order}";
        $rs['success'] = Db::getInstance()->execute($sql);
        $rs['success2'] = Db::getInstance()->execute($sql2);
        $rs['id_carrier'] = $id_carrier;

        if ($rs['success']) {
            $sql = "SELECT o.id_order, oc.id_order_carrier, o.reference, hlc.id, c.id_reference,
            hlc.name, c.url as tracking_url, oc.tracking_number, c.name as carrier_name
            FROM {$prefix}orders o
            INNER JOIN {$prefix}order_carrier oc ON o.id_order=oc.id_order
            LEFT JOIN {$prefix}carrier c ON oc.id_carrier = c.id_carrier
            LEFT JOIN {$prefix}hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
            LEFT JOIN {$prefix}hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
            WHERE oc.id_order_carrier = {$id_order_carrier}";
            $order = Db::getInstance()->getRow($sql);

            if (strpos($order['tracking_url'], '@') !== false) {
                $order['tracking_url'] = str_replace('@', $order['tracking_number'], $order['tracking_url']);
            } elseif ($order['tracking_url']) {
                $order['tracking_url'] = $order['tracking_url'].$order['tracking_number'];
            } else {
                $order['tracking_url'] = null;
            }
            $rs['json_server'] = $this->url.'modules/deliveryorderautoupdate/webservices/'
            .$order['id'].'/Carrier'.$order['id'].'.php?shipment_ref='
            .$order['id_order_carrier'].'&parcel_number='.
            $trackingmodel->handleTrackingNumber($order['tracking_number'])
            .'&token='.Configuration::get('DELIVERY_TOKEN')
            .'&order_ref='.$order['reference'].'&id_shop='.$this->id_shop.'&devmode=1';

            $rs['json_carrier'] = $this->url.'modules/deliveryorderautoupdate/webservices/'
            .$order['id'].'/Carrier'.$order['id'].'.php?shipment_ref='
            .$order['id_order_carrier'].'&parcel_number='.
            $trackingmodel->handleTrackingNumber($order['tracking_number'])
            .'&order_ref='.$order['reference'].'&id_shop='.$this->id_shop.'&devmode=0';
            $rs['data'] = $order;
            $rs['carrier_name'] = $order['carrier_name'];
        }
        die(json_encode($rs));
    }
    public function ajaxProcessGetConnector()
    {
        $connector_carrier = Db::getInstance()->executeS(
            'SELECT CONCAT(id, "_",name) as connector FROM '._DB_PREFIX_.'hl_carrier ORDER BY id'
        );
        die(json_encode($connector_carrier));
    }
    public function ajaxProcessUpdateConnector()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_connector = (int)Tools::getValue("id_connector");
        $id_order_carrier = (int)Tools::getValue("id_order_carrier");
        $id_carrier = (int)Tools::getValue("id_carrier");
        if (!Validate::isUnsignedId($id_connector)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_connector';
            die(json_encode($rs));
        }
        if (!Validate::isUnsignedId($id_order_carrier)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_order_carrier';
            die(json_encode($rs));
        }
        if (!Validate::isUnsignedId($id_carrier)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_carrier';
            die(json_encode($rs));
        }
        $id_carrier_hl = Db::getInstance()->getRow(
            'SELECT id_carrier_hl
            FROM '._DB_PREFIX_.'hl_carrier_matching
            WHERE `id_carrier_ps`='.$id_carrier
        );
        if ($id_carrier_hl && $id_carrier_hl['id_carrier_hl']>-1) {
            if ($id_connector) {
                $rs['success'] = Db::getInstance()->execute(
                    'UPDATE `'._DB_PREFIX_.'hl_carrier_matching`
                    SET `id_carrier_hl`='.$id_connector.'
                    WHERE `id_carrier_ps`='.$id_carrier
                );
            } else {
                $rs['success'] = Db::getInstance()->execute(
                    'DELETE FROM '._DB_PREFIX_.'hl_carrier_matching
                    WHERE id_carrier_ps ='. $id_carrier
                );
            }
        } else {
            $rs['success'] = Db::getInstance()->insert(
                'hl_carrier_matching',
                array(
                    'id_carrier_hl' => $id_connector,
                    'id_carrier_ps' => $id_carrier
                )
            );
        }
        if ($rs['success']) {
            $number = Db::getInstance()->getRow(
                'SELECT DISTINCT o.id_order,oc.tracking_number, hlc.id, o.reference, hlc.name
                FROM '._DB_PREFIX_.'orders o
                INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
				LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
                LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
                LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
                WHERE oc.id_order_carrier = '.$id_order_carrier
            );
            $rs['tracking_number'] = $number['tracking_number'];
            $trackingmodel = new TrackingModel();
            $tracking_number = $trackingmodel->handleTrackingNumber($number['tracking_number']);

            $rs['json_carrier'] = $this->url.'modules/deliveryorderautoupdate/webservices/'
            .$number['id'].'/Carrier'.$number['id'].
            '.php?shipment_ref={$id_order_carrier}&parcel_number='.$tracking_number.
            '&order_ref={$reference}&&id_shop='.$this->id_shop.'&devmode=0';
            $rs['id_connector'] = $number['id'];
            if ($number['id']) {
                $rs['connector_name'] = $number['name'];
            } else {
                $rs['connector_name'] = $this->module->l('No connector');
            }
        }

        die(json_encode($rs));
    }
    public function ajaxProcessUpdateConnectorReturn()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_connector = (int)Tools::getValue("id_connector");
        $id_return = (int)Tools::getValue("id_return");
        if (!Validate::isUnsignedId($id_connector)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_connector';
            die(json_encode($rs));
        }
        if (!Validate::isUnsignedId($id_return)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_order_carrier';
            die(json_encode($rs));
        }
        $rs['success'] = Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'hl_tracking_return`
            SET `id_connector`='.$id_connector.'
            WHERE `id_return`='.$id_return
        );

        if ($rs['success']) {
            $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/carriers.xml';
            $xml_carrier = json_decode(
                json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
            );
            $return = Db::getInstance()->getRow(
                "SELECT *, o.reference FROM "._DB_PREFIX_."hl_tracking_return tr
                LEFT JOIN "._DB_PREFIX_."orders o ON tr.id_order = o.id_order
                WHERE tr.id_return = ".$id_return
            );
            $rs['json_server'] = $this->url.'modules/deliveryorderautoupdate/webservices/'
            .$id_connector.'/Carrier'.$id_connector.'.php?shipment_ref='.$id_return.
            '&parcel_number='.$return['shipping_number']
            .'&token='.Configuration::get('DELIVERY_TOKEN')
            .'&order_ref='.$return['reference'].'&id_shop='.$this->id_shop.'&devmode=1';

            $connector = array_filter($xml_carrier->carrier, function ($c) use ($id_connector) {
                return $c->id_carrier == $id_connector;
            });

            if (count($connector)) {
                $connector = current($connector);
                $rs['connector_name'] = $connector->name;
                $rs['id_connector'] = $connector->id_carrier;
            }
        }

        die(json_encode($rs));
    }
    public function ajaxProcessCheckIssue()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order_carrier = (int)Tools::getValue("id_order_carrier");
        $issue = EmailHelper::getIssueByShipment($id_order_carrier);
        if ($issue) {
            $issue['histories'] = EmailHelper::getIssueHistory($issue['id_issue']);
        }
        $tpl = $this->createTemplate('../../hook/issuelist.tpl');
        $this->context->smarty->assign(array(
            'issue' => $issue,
            'date_format_full' => $this->context->language->date_format_full,
        ));
        $rs['html'] = $tpl->fetch();
        die(json_encode($rs));
    }
    public function ajaxProcessCreateIssue()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order_carrier = (int)Tools::getValue("id_order_carrier");
        $issue_type = (int)Tools::getValue("issue_type");
        $detail = Tools::getValue("detail");
        if (!Validate::isUnsignedId($id_order_carrier)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_order_carrier';
            die(json_encode($rs));
        }
        $check = (int)Db::getInstance()->getValue(
            "SELECT COUNT(*) FROM "._DB_PREFIX_."hl_tracking_issue WHERE id_order_carrier = {$id_order_carrier}"
        );
        if ($check) {
            $rs['success'] = false;
            $rs['msg'] = $this->module->l('issue exists');
        } else {
            $rs['success'] = Db::getInstance()->insert(
                'hl_tracking_issue',
                array(
                    'id_order_carrier' => $id_order_carrier,
                    'issue_type' => $issue_type
                ),
                false,
                true,
                Db::ON_DUPLICATE_KEY
            );
            if ($rs['success']) {
                $id_issue = Db::getInstance()->Insert_ID();
                Db::getInstance()->insert(
                    'hl_tracking_issue_status',
                    array(
                        'id_issue' => (int)$id_issue,
                        'status' => 1,
                        'date' => date('Y-m-d H:i:s'),
                        'detail' => $detail
                    )
                );
                $issue = EmailHelper::getIssueByShipment($id_order_carrier);
                if ($issue) {
                    $issue['histories'] = EmailHelper::getIssueHistory($issue['id_issue']);
                }
                $tpl = $this->createTemplate('../../hook/issuelist.tpl');
                $this->context->smarty->assign(array(
                    'issue' => $issue,
                    'date_format_full' => $this->context->language->date_format_full,
                ));
                $rs['html'] = $tpl->fetch();
            }
        }


        die(json_encode($rs));
    }
    public function ajaxProcessUpdateIssue()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_issue = (int)Tools::getValue("id_issue");
        $issue_type = (int)Tools::getValue("issue_type");
        if (!Validate::isUnsignedId($id_issue)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_issue';
            die(json_encode($rs));
        }
        $rs['success'] = Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'hl_tracking_issue`
            SET `issue_type`='.$issue_type.'
            WHERE `id_issue`='.$id_issue
        );

        if ($rs['success']) {
            $path_issue = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/xml/issues.xml';
            $xml_issue = json_decode(
                json_encode(simplexml_load_file($path_issue, 'SimpleXMLElement', LIBXML_NOCDATA)),
                true
            );
            $rs['issue_name'] = '';
            $iso_code = $this->context->language->iso_code;
            foreach ($xml_issue['issues']['issue'] as $issue) {
                if ($issue['id_issue'] == $issue_type) {
                    $rs['issue_name'] = isset($issue[$iso_code])?$issue[$iso_code]:$issue['en'];
                    break;
                }
            }
            $rs['issue_type'] = $issue_type;
        }

        die(json_encode($rs));
    }
    public function ajaxProcessUpdateIssueStatus()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_issue = (int)Tools::getValue("id_issue");
        $id_status = (int)Tools::getValue("id_status");
        $detail = Tools::getValue("detail");
        if (!Validate::isUnsignedId($id_issue)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_issue';
            die(json_encode($rs));
        }
        $date= date('Y-m-d H:i:s');
        $rs['success'] = Db::getInstance()->insert(
            "hl_tracking_issue_status",
            array(
                "id_issue"=> $id_issue,
                "status"=> $id_status,
                "detail"=> $detail,
                'date' => $date
            )
        );

        if ($rs['success']) {
            $path_issue = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/xml/issues.xml';
            $xml_issue = json_decode(
                json_encode(simplexml_load_file($path_issue, 'SimpleXMLElement', LIBXML_NOCDATA)),
                true
            );
            $rs['status_name'] = '';
            $iso_code = $this->context->language->iso_code;
            foreach ($xml_issue['statuses']['status'] as $status) {
                if ($status['id_status'] == $id_status) {
                    $rs['status_name'] = isset($status[$iso_code])?$status[$iso_code]:$status['en'];
                    break;
                }
            }
            $rs['id_issue_status'] = $id_status;
            $rs['issue_status_date'] = $date;
            $tpl = $this->createTemplate('../../hook/issue-row.tpl');
            $tpl->assign(array(
                'histories' => array(array(
                    'date' => $date,
                    'status_name' => $rs['status_name'],
                    'detail' => $detail
                )),
                'date_format_full' =>  $this->context->language->date_format_full
            ));
            $rs['html'] = $tpl->fetch();
        }

        die(json_encode($rs));
    }
    public function ajaxProcessLoadIssueHistory()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_issue = (int)Tools::getValue("id_issue");
        $issue = EmailHelper::getIssueById($id_issue);
        if ($issue) {
            $issue['histories'] = EmailHelper::getIssueHistory($issue['id_issue']);
        }
        $tpl = $this->createTemplate('../../hook/issuelist.tpl');
        $this->context->smarty->assign(array(
            'issue' => $issue,
            'date_format_full' => $this->context->language->date_format_full,
            'hideEdit' => true
        ));
        $rs['html'] = $tpl->fetch();
        die(json_encode($rs));
    }
    public function ajaxProcessUpdateBulkConnectorReturn()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        try {
            $id_connector = (int)Tools::getValue("id_connector");
            $id_return = Tools::getValue("ids_return");
            if (!Validate::isUnsignedId($id_connector)) {
                $rs['success'] = false;
                $rs['msg'] = 'Invalid id_connector';
                die(json_encode($rs));
            }
            $rs['success'] = ImportReturnShipment::updateConnector($id_connector, $id_return);
        } catch (Exception $e) {
            $rs['success'] = false;
            $rs['msg'] = $e->getMessage();
        }
        die(json_encode($rs));
    }

    public function ajaxProcessUpdateTrackingNumber()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $trackingmodel = new TrackingModel();
        $id_order = (int)Tools::getValue("id_order");
        $id_order_carrier = (int)Tools::getValue("id_order_carrier");
        $tracking_number = pSQL(trim(Tools::getValue("tracking_number")));
        if (!Validate::isUnsignedId($id_order_carrier)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_order_carrier';
            die(json_encode($rs));
        }
        $prefix = _DB_PREFIX_;
        $sql = "UPDATE {$prefix}order_carrier oc
        SET oc.tracking_number='{$tracking_number}'
        WHERE oc.id_order_carrier={$id_order_carrier}";
        $rs['success'] = Db::getInstance()->execute($sql);
        if ($rs['success']) {
            $number = Db::getInstance()->getRow(
                'SELECT DISTINCT o.id_order, oc.id_order_carrier ,oc.tracking_number,
                hlc.id, o.reference, c.url as tracking_url
                FROM '._DB_PREFIX_.'orders o
                INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
                LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
                LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
                LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
                WHERE oc.id_order_carrier = '.$id_order_carrier
            );
            $rs['json_server'] = $this->url.'modules/deliveryorderautoupdate/webservices/'
            .$number['id'].'/Carrier'.$number['id'].'.php?shipment_ref='
            .$number['id_order_carrier'].'&parcel_number='.
            $trackingmodel->handleTrackingNumber($number['tracking_number'])
            .'&token='.Configuration::get('DELIVERY_TOKEN')
            .'&order_ref='
            .$number['reference'].'&id_shop='.$this->id_shop.'&devmode=1';

            if (strpos($number['tracking_url'], '@') !== false) {
                $rs['tracking_url'] = str_replace('@', $number['tracking_number'], $number['tracking_url']);
            } else {
                $rs['tracking_url'] = $number['tracking_url'].$number['tracking_number'];
            }
        }
        die(json_encode($rs));
    }
    public function ajaxProcessUpdateTrackingNumberReturn()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_return = (int)Tools::getValue("id_return");
        $tracking_number = pSQL(trim(Tools::getValue("tracking_number")));
        if (!Validate::isUnsignedId($id_return)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_return';
            die(json_encode($rs));
        }
        $prefix = _DB_PREFIX_;
        $sql = "UPDATE {$prefix}hl_tracking_return
        SET shipping_number='{$tracking_number}'
        WHERE id_return={$id_return}";
        $rs['success'] = Db::getInstance()->execute($sql);
        if ($rs['success']) {
            $return = Db::getInstance()->getRow(
                "SELECT *, o.reference FROM "._DB_PREFIX_."hl_tracking_return tr
                LEFT JOIN "._DB_PREFIX_."orders o ON tr.id_order = o.id_order
                WHERE tr.id_return = ".$id_return
            );
            $rs['json_server'] = $this->url.'modules/deliveryorderautoupdate/webservices/'
            .$return['id_connector'].'/Carrier'.$return['id_connector'].'.php?shipment_ref='.$id_return.
            '&parcel_number='.$return['shipping_number']
            .'&token='.Configuration::get('DELIVERY_TOKEN')
            .'&order_ref='.$return['reference'].'&id_shop='.$this->id_shop.'&devmode=1';
        }
        die(json_encode($rs));
    }
    public function ajaxProcessUpdateShipmentDate()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order_carrier = (int)Tools::getValue("id_order_carrier");
        $date = Tools::getValue('shipment_date');
        if (!Validate::isDateFormat($date)) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid date format';
            die(json_encode($rs));
        }
        $orderCarrier = new OrderCarrier($id_order_carrier);
        $orderCarrier->date_add = $date;
        $orderCarrier->update();
        die(json_encode($rs));
    }
    public function ajaxProcessViewStep()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        // $parcel_number = Tools::getValue("parcel_number");
        $id_order_carrier = (int)Tools::getValue("id_order_carrier");
        $steps = TrackingModel::getTrackSteps($id_order_carrier);
        // $history_left = TrackingModel::getTrackEvents($id_order_carrier);
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        foreach ($webxml_crr->status as $step) {
            if ($step->id_status == $steps['current_status']) {
                $iso_code = Tools::strtoupper($this->context->language->iso_code);
                if (!isset($step->$iso_code)) {
                    $iso_code = self::DEFAULT_LANG;
                }
                $steps['status_text'] = $step->$iso_code;
            }
        }

        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        $tpl = $this->createTemplate('../../hook/steplist.tpl');
        $context = Context::getContext();
        $tpl->assign(array(
            'steps' => $steps,
            'statuses' => $statuses,
            'url_root' => $this->url,
            'date_format_full' => $context->language->date_format_full,
            'date_format_lite' => $context->language->date_format_lite,
        ));
        echo $tpl->fetch();
    }
    public function ajaxProcessViewEvent()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order_carrier = (int)Tools::getValue("id_order_carrier");
        $events = TrackingModel::getTrackEvents($id_order_carrier);

        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        $tpl = $this->createTemplate('../../hook/event-list.tpl');
        $tpl->assign(array(
            'events' => $events,
            'showStatus' => true,
            'show_header' => true,
            'statuses' => $statuses,
            'url_root' => $this->url,
            'date_format_full' => $this->context->language->date_format_full,
            'date_format_lite' => $this->context->language->date_format_lite,
        ));
        echo $tpl->fetch();
    }
    public function ajaxProcessTrackHistory()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        // $parcel_number = Tools::getValue("parcel_number");
        $id_order_carrier = Tools::getValue("id_order_carrier");
        $page = (int)Tools::getValue('page');
        $total = Db::getInstance()->getValue(
            'SELECT COUNT(*) as total
            FROM `'._DB_PREFIX_.'hl_tracking_history` hl
            WHERE hl.id_order_carrier='.(int)$id_order_carrier
        );
        $itemsPerPage = Tools::getValue('itemsPerPage', $this->itemsPerPage);
        $totalPage = ceil($total/$itemsPerPage);
        if ($page >= $totalPage) {
            $tpl = $this->createTemplate('ajax_empty.tpl');
            $rs['html'] = $tpl->fetch();
            $rs['total'] = $totalPage;
            die(json_Encode($rs));
        }
        $offset = $page*$itemsPerPage;
        $limit = $itemsPerPage;
        $iso_code = Tools::strtoupper($this->context->language->iso_code);
        $history = Db::getInstance()->executeS(
            'SELECT hl.id,hl.date_add, hl.step_date, hl.hl_carrier, hl.success_response, hl.carrier_response,
            hl.event_code,hl.id_order,
            hl.method as carrier, hl.email_sent
            FROM `'._DB_PREFIX_.'hl_tracking_history` hl
            WHERE hl.id_order_carrier='.(int)$id_order_carrier.'
            ORDER BY hl.id DESC
            LIMIT '.$offset.', '.$limit
        );
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $shipping = array();
        foreach ($webxml_crr->status as $k => $step) {
            $shipping[$k] = isset($step->$iso_code)?$step->$iso_code:$step->{self::DEFAULT_LANG};
        }
        $tpl = $this->createTemplate('ajax_trackinghistory.tpl');
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        foreach ($history as &$hs) {
            $hs['step_date'] = date($this->context->language->date_format_full, strtotime($hs['step_date']));
            $hs['date_add'] = date($this->context->language->date_format_full, strtotime($hs['date_add']));
            if (isset($statuses[$hs['event_code']])) {
                $hs['shipping_status'] = isset($statuses[$hs['event_code']]->$iso_code)?
                $statuses[$hs['event_code']]->$iso_code:$statuses[$hs['event_code']]->EN;
            }
        }
        $tpl->assign(array(
            'history' => $history,
            'statuses' => $statuses,
            'shipping' => $shipping,
            'url_root' => $this->url,
            'methods' => $this->method,
        ));
        $rs['html'] = $tpl->fetch();
        $rs['total'] = $totalPage;
        die(json_Encode($rs));
    }
    public function ajaxProcessEmailHistory()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $prefix = _DB_PREFIX_;
        // $where = '';
        if (Tools::isSubmit('id_order_carrier') && Tools::getValue('id_order_carrier') > 0) {
            $id_order_carrier = (int)Tools::getValue('id_order_carrier');
            // $where = "WHERE e.id_order = {$id_order}";
            $page = (int)Tools::getValue('page');
            $total = Db::getInstance()->getValue(
                "SELECT COUNT(*) as total
                FROM {$prefix}hl_tracking_email e
                LEFT JOIN {$prefix}order_carrier oc ON e.id_order_carrier = oc.id_order_carrier
                LEFT JOIN {$prefix}orders o ON oc.id_order = o.id_order
                LEFT JOIN {$prefix}customer c ON o.id_customer = c.id_customer
                LEFT JOIN {$prefix}hl_tracking_history h ON e.id_tracking_history = h.id
                WHERE e.id_order_carrier = {$id_order_carrier}"
            );
            $totalPage = ceil($total/$this->itemsPerPage);
            if ($page >= $totalPage) {
                $tpl = $this->createTemplate('ajax_empty.tpl');
                $rs['html'] = $tpl->fetch();
                $rs['total'] = $totalPage;
                die(json_Encode($rs));
            }
            $offset = $page*$this->itemsPerPage;
            $limit = $this->itemsPerPage;
            $sql = "SELECT e.id, oc.id_order, e.id_tracking_history, e.date_sent, e.email_status,
            h.step_date, h.hl_carrier, e.shipping_status, c.firstname, c.lastname
            FROM {$prefix}hl_tracking_email e
            LEFT JOIN {$prefix}order_carrier oc ON e.id_order_carrier = oc.id_order_carrier
            LEFT JOIN {$prefix}orders o ON oc.id_order = o.id_order
            LEFT JOIN {$prefix}customer c ON o.id_customer = c.id_customer
            LEFT JOIN {$prefix}hl_tracking_history h ON e.id_tracking_history = h.id
            WHERE e.id_order_carrier = {$id_order_carrier}
            ORDER BY e.id DESC
            LIMIT {$offset}, {$limit}";
        } else {
            $page = (int)Tools::getValue('page');
            $total = Db::getInstance()->getValue(
                "SELECT COUNT(*) as total
                FROM {$prefix}hl_tracking_email e
                LEFT JOIN {$prefix}order_carrier oc ON e.id_order_carrier = oc.id_order_carrier
                LEFT JOIN {$prefix}orders o ON oc.id_order = o.id_order
                LEFT JOIN {$prefix}customer c ON o.id_customer = c.id_customer
                LEFT JOIN {$prefix}hl_tracking_history h ON e.id_tracking_history = h.id"
            );
            $totalPage = ceil($total/$this->itemsPerPage);
            if ($page >= $totalPage) {
                $tpl = $this->createTemplate('ajax_empty.tpl');
                $rs['html'] = $tpl->fetch();
                $rs['total'] = $totalPage;
                die(json_Encode($rs));
            }
            $offset = $page*$this->itemsPerPage;
            $limit = $this->itemsPerPage;
            $sql = "SELECT e.id, oc.id_order, e.id_tracking_history, e.date_sent, e.email_status,
            h.step_date, h.hl_carrier, e.shipping_status, c.firstname, c.lastname
            FROM {$prefix}hl_tracking_email e
            LEFT JOIN {$prefix}order_carrier oc ON e.id_order_carrier = oc.id_order_carrier
            LEFT JOIN {$prefix}orders o ON oc.id_order = o.id_order
            LEFT JOIN {$prefix}customer c ON o.id_customer = c.id_customer
            LEFT JOIN {$prefix}hl_tracking_history h ON e.id_tracking_history = h.id
            ORDER BY e.id DESC
            LIMIT {$offset}, {$limit}";
        }
        $emails = Db::getInstance()->executeS($sql);
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        $iso_code = Tools::strtoupper($this->context->language->iso_code);
        foreach ($emails as &$email) {
            $email['date_sent'] = date($this->context->language->date_format_full, strtotime($email['date_sent']));
            if (isset($statuses[$email['shipping_status']])) {
                $email['status_text'] = isset($statuses[$email['shipping_status']]->$iso_code)?
                $statuses[$email['shipping_status']]->$iso_code:$statuses[$email['shipping_status']]->EN;
            }
        }
        $tpl = $this->createTemplate('ajax_emailhistory.tpl');
        $tpl->assign(array(
            'emails' => $emails,
            // 'methods' => $this->method,
            'email_statuses' => $this->email_status,
            'url_root' => $this->url,
            'statuses' => $statuses,
        ));
        $rs['html'] = $tpl->fetch();
        $rs['total'] = $totalPage;
        die(json_Encode($rs));
    }
    public function ajaxProcessDashboard()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $l = Tools::getValue("l");
        $step = (int)Tools::getValue("step");
        if ($l == 0) {
            $parcel_number = Tools::getValue("parcel_number");
            $tab_active = Tools::getValue("tab_active");
            $id_order = Tools::getValue("id_order");
            $history = Db::getInstance()->executeS(
                'SELECT hl.id,hl.date_add, hl.step_date, hl.hl_carrier, hl.success_response, hl.carrier_response,
                hl.event_code,hl.id_order, hl.method as carrier, hl.email_sent
                FROM `'._DB_PREFIX_.'hl_tracking_history` hl
                INNER JOIN '._DB_PREFIX_.'orders o ON (o.id_order = hl.id_order)
                INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
                WHERE oc.tracking_number="'.pSQL($parcel_number).'"
                ORDER BY hl.id DESC'
            );

            $history_left = Db::getInstance()->executeS(
                'SELECT hl.id,hl.date_add, hl.step_date, hl.hl_carrier, hl.success_response, hl.carrier_response,
                hl.event_code,o.id_order, hl.method as carrier, hl.email_sent
                FROM `'._DB_PREFIX_.'hl_tracking_history` hl
                INNER JOIN '._DB_PREFIX_.'orders o ON (o.id_order = hl.id_order)
                INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
                WHERE oc.tracking_number="'.pSQL($parcel_number).'" AND hl.event_code NOT IN (0)
                GROUP BY hl.event_code
                ORDER BY hl.id DESC'
            );

            $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
            $webxml_crr = json_decode(
                json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
            );
            foreach ($history as &$hs) {
                $hs['result'] = null;
                foreach ($webxml_crr->status as $step) {
                    if ($step->id_status == $hs['event_code']) {
                        $iso_code = Tools::strtoupper($this->context->language->iso_code);
                        if (!isset($step->$iso_code)) {
                            $iso_code = self::DEFAULT_LANG;
                        }
                        $hs['result'] = $hs['event_code'].'_'.$step->$iso_code;
                    }
                }
            }
            foreach ($history_left as &$hs_) {
                $hs_['result'] = null;
                foreach ($webxml_crr->status as $step) {
                    if ($step->id_status == $hs_['event_code']) {
                        $iso_code = Tools::strtoupper($this->context->language->iso_code);
                        if (!isset($step->$iso_code)) {
                            $iso_code = self::DEFAULT_LANG;
                        }
                        $hs_['result'] = $hs_['event_code'].'_'.$step->$iso_code;
                    }
                }
            }
            $shipping = array();
            foreach ($webxml_crr->status as $k => $step) {
                $iso_code = Tools::strtoupper($this->context->language->iso_code);
                if (!isset($step->$iso_code)) {
                    $iso_code = self::DEFAULT_LANG;
                }
                $shipping[$k]['name'] = $step->$iso_code;
            }
            $bg_color = array();
            $bg_color['vail_number'] = $webxml_crr->status[4]->color;
            $bg_color['warning'] = $webxml_crr->status[2]->color;
            $bg_color['enable'] = $webxml_crr->status[1]->color;
            $bg_color['default'] = $webxml_crr->status[0]->color;
            $bg_color['take_deli'] = $webxml_crr->status[3]->color;
            $bg_color['returned_shipper'] = $webxml_crr->status[7]->color;
            $bg_color['out_delivery'] = $webxml_crr->status[8]->color;
            $bg_color['delivered_shop'] = $webxml_crr->status[6]->color;
            $tpl = $this->createTemplate('ajax_order_shipment.tpl');
            $tpl->assign(array(
                'history' => $history,
                'history_left' => $history_left,
                'bg_color' => $bg_color,
                'tab_active' => $tab_active,
                'shipping' => $shipping,
                'id_order' => $id_order,
                'url_root' => $this->url,
                'methods' => $this->method,
            ));
            echo $tpl->fetch();
        } elseif ($l == 1) {
            // show left and right panel in order detail backend
            $id_order = (int)Tools::getValue('id_order');
            $history = Db::getInstance()->executeS(
                'SELECT id,date_add, step_date, hl_carrier, success_response, carrier_response,event_code,id_order,
                method as carrier, email_sent
                FROM `'._DB_PREFIX_.'hl_tracking_history`
                WHERE id_order='.(int)Tools::getValue('id_order')./*' AND event_code NOT IN (0)*/
                ' ORDER BY id DESC'
            );
            $history_left = Db::getInstance()->executeS(
                'SELECT id,date_add, step_date, hl_carrier, success_response, carrier_response,event_code,id_order,
                method as carrier, email_sent
                FROM `'._DB_PREFIX_.'hl_tracking_history`
                WHERE id_order='.(int)Tools::getValue('id_order')./*' AND event_code NOT IN (0)*/
                ' GROUP BY event_code
                ORDER BY id DESC'
            );
            $id_carrier = Db::getInstance()->getRow(
                'SELECT hlc.id
                FROM '._DB_PREFIX_.'hl_carrier hlc
                INNER JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON hlcm.id_carrier_hl=hlc.id
				INNER JOIN '._DB_PREFIX_.'carrier c ON hlcm.id_carrier_ps=c.id_reference
                INNER JOIN '._DB_PREFIX_.'orders o ON o.id_carrier=c.id_carrier
                WHERE o.id_order='.(int)Tools::getValue('id_order')
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
            setlocale(LC_TIME, $locale);
            foreach ($history as &$hs) {
                $hs['result'] = null;
                foreach ($webxml_crr->status as $step) {
                    if ($step->id_status == $hs['event_code']) {
                        $iso_code = Tools::strtoupper($this->context->language->iso_code);
                        if (!isset($step->$iso_code)) {
                            $iso_code = self::DEFAULT_LANG;
                        }
                        $hs['result'] = $hs['event_code'].'_'.$step->$iso_code;
                    }
                }
            }
            foreach ($history_left as &$hs_) {
                $hs_['result'] = null;
                $time = strtotime($hs_['step_date']);
                $hs_['step_date'] = '';
                $hs_['step_time'] = date('H:i:s', $time);
                foreach ($webxml_crr->status as $step) {
                    if ($step->id_status == $hs_['event_code']) {
                        $iso_code = Tools::strtoupper($this->context->language->iso_code);
                        if (!isset($step->$iso_code)) {
                            $iso_code = self::DEFAULT_LANG;
                        }
                        $hs_['result'] = $hs_['event_code'].'_'.$step->$iso_code;
                    }
                }
            }
            $iso_code = Tools::strtoupper($this->context->language->iso_code);
            $shipping = array_map(function ($step) use ($iso_code) {
                return isset($step->$iso_code)?$step->$iso_code:$step->{self::DEFAULT_LANG};
            }, $webxml_crr->status);
            $statuses = array();
            foreach ($webxml_crr->status as $status) {
                $statuses[$status->id_status] = $status;
            }
            foreach ($history as &$hs) {
                $hs['step_date'] = date($this->context->language->date_format_full, strtotime($hs['step_date']));
                $hs['date_add'] = date($this->context->language->date_format_full, strtotime($hs['date_add']));
                if (isset($statuses[$hs['event_code']])) {
                    $hs['shipping_status'] = isset($statuses[$hs['event_code']]->$iso_code)?
                    $statuses[$hs['event_code']]->$iso_code:$statuses[$hs['event_code']]->EN;
                }
            }
            $link = new Link();

            $prefix = _DB_PREFIX_;
            $emails = Db::getInstance()->executeS(
                "SELECT e.id, e.id_order, e.id_tracking_history, e.date_sent, e.email_status,
                h.step_date, h.hl_carrier, e.shipping_status, c.firstname, c.lastname
                FROM {$prefix}hl_tracking_email e
                LEFT JOIN {$prefix}orders o ON e.id_order = o.id_order
                LEFT JOIN {$prefix}customer c ON o.id_customer = c.id_customer
                LEFT JOIN {$prefix}hl_tracking_history h ON e.id_tracking_history = h.id
                WHERE e.id_order = {$id_order}
                ORDER BY e.id DESC"
            );
            foreach ($emails as &$email) {
                $email['date_sent'] = date($this->context->language->date_format_full, strtotime($email['date_sent']));
                if (isset($statuses[$email['shipping_status']])) {
                    $email['status_text'] = isset($statuses[$email['shipping_status']]->$iso_code)?
                    $statuses[$email['shipping_status']]->$iso_code:$statuses[$email['shipping_status']]->EN;
                }
            }
            $tpl = $this->createTemplate('displayAdminOrder.tpl');
            $tpl->assign(array(
                'history' => $history,
            'emails' => $emails,
                'history_left' => $history_left,
                'id_carrier' => $id_carrier['id'],
                'ajaxdel_url' => $link->getAdminLink('AdmindeliveryorderautoupdateAjax'),
                'id_order' => Tools::getValue('id_order'),
                'url_root' => $this->url,
                'statuses' => $statuses,
                'shipping' => $shipping,
                'methods' => $this->method,
            ));
            echo $tpl->fetch();
        } elseif ($l == 2) {
            // show history tab in tracking center
            $iso_code = Tools::strtoupper($this->context->language->iso_code);

            $total = Db::getInstance()->getValue(
                'SELECT COUNT(*) as total
                FROM `'._DB_PREFIX_.'hl_tracking_history`'
            );
            $totalPage = ceil($total/$this->itemsPerPage);
            if ($step >= $totalPage) {
                $tpl = $this->createTemplate('ajax_empty.tpl');
                $rs['html'] = $tpl->fetch();
                $rs['total'] = $totalPage;
                die(json_Encode($rs));
            }
            $step_ = $step*$this->itemsPerPage;
            $history = Db::getInstance()->executeS(
                'SELECT id,date_add, step_date, hl_carrier, success_response, carrier_response,event_code,id_order,
                method as carrier, email_sent
                FROM `'._DB_PREFIX_.'hl_tracking_history`
                ORDER BY id DESC LIMIT '.(int)$step_.', '.$this->itemsPerPage
            );
            $history_total = Db::getInstance()->getRow('SELECT count(id) as total
                        FROM `'._DB_PREFIX_.'hl_tracking_history`');
            $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
            $webxml_crr = json_decode(
                json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
            );
            $statuses = array();
            foreach ($webxml_crr->status as $status) {
                $statuses[$status->id_status] = $status;
            }
            foreach ($history as &$hs) {
                $carrier_name = Db::getInstance()->getRow(
                    'SELECT CONCAT(id,"_",name) as carrier_name
                    FROM '._DB_PREFIX_.'hl_carrier
                    WHERE id='.(int)$hs['hl_carrier']
                );
                $hs['carrier_name'] = $carrier_name['carrier_name'];
                $hs['step_date'] = date($this->context->language->date_format_full, strtotime($hs['step_date']));
                $hs['date_add'] = date($this->context->language->date_format_full, strtotime($hs['date_add']));
                $hs['result'] = null;
                if (isset($statuses[$hs['event_code']])) {
                    $hs['shipping_status'] = isset($statuses[$hs['event_code']]->$iso_code)?
                    $statuses[$hs['event_code']]->$iso_code:$statuses[$hs['event_code']]->EN;
                }
            }
            $tpl = $this->createTemplate('ajax_dashboardhistory.tpl');
            $tpl->assign(array(
                'history_total' => $history_total['total'],
                'statuses' => $statuses,
                'history' => $history,
                'history_now' => $step_+50,
                'url_root' => $this->url,
                'methods' => $this->method,
            ));
            $rs['html'] = $tpl->fetch();
            $rs['total'] = $totalPage;
            die(json_Encode($rs));
        }
    }
    public function ajaxProcessSaveCarrier()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != Configuration::get('DELIVERY_TOKEN')) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        // $update = (int)Tools::getValue("edit");
        // $active = ((bool)Tools::getValue("active") ? 1 : 0);
        // $methor = (int)Tools::getValue("methor");
        $rs = array('credential' => false);
        $carrier_emb = (Tools::getValue("carrier_emb"));
        // $carrier_sub = (Tools::getValue("carrier_sub"));
        // if ($update) {
        //     $id = (int)Tools::getValue("id");
        //     $id ? Configuration::updateValue('hl_tr_carrier'.$id.'_id_modulecarrier', $id) : null;

        //     Db::getInstance()->update("hl_carrier", array("method"=> $methor, "active"=> $active), 'id='.$id);
        // } else {
        //     Db::getInstance()->insert("hl_carrier", array("method"=> $methor, "active"=> $active));
        //     $id = Db::getInstance()->getRow('SELECT id FROM '._DB_PREFIX_.'hl_carrier ORDER BY id DESC');
        //     $id = (int)$id['id'];
        // }
        if (isset($carrier_emb) && ($carrier_emb)) {
            foreach ($carrier_emb as $emb) {
                Configuration::updateValue($emb['name'], $emb['val']);
                $check = Db::getInstance()->getRow(
                    'SELECT value
                    FROM '._DB_PREFIX_.'configuration
                    WHERE id_shop='.(int)$this->context->shop->id.' AND name Like "'.pSQL($emb['name']).'"'
                );
                if ($check['value']) {
                    Db::getInstance()->update(
                        'configuration',
                        array('value' => pSQL($emb['val'])),
                        'name Like "'.pSQL($emb['name']).'" AND id_shop='.(int)$this->context->shop->id
                    );
                } else {
                    Db::getInstance()->insert(
                        'configuration',
                        array(
                            'value' => pSQL($emb['val']),
                            'name' => pSQL($emb['name']),
                            'id_shop' => (int)$this->context->shop->id
                        )
                    );
                }
                $rs['credential'] |= ($emb['val'] != '');
            }
        }
        die(json_Encode($rs));
    }
    public function ajaxProcessEditCarrier()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != Configuration::get('DELIVERY_TOKEN')) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        // $module_webcarrier = array();
        $embedded_carrier = array();
        // $id_listmodulecrr = ',';
        // $key = 0;
        if (Tools::getValue('id_carrier')) {
            $id_carrier = (int)Tools::getValue('id_carrier');
            // $Dlv_update = new Deliveryorderautoupdate();
            // $carrier = $Dlv_update->getCarrier($id_carrier);

            $url_freeservice = _PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'
            .$id_carrier.'/Carrier'.$id_carrier.'.php';
            if (file_exists($url_freeservice)) {
                $embedded_carrier['emb'] = 1;
                // $url = Db::getInstance()->getRow(
                //     'SELECT url FROM '._DB_PREFIX_.'hl_carrier WHERE id='.(int)$id_carrier
                // );
                $embedded_carrier['checked'] = 1;
                $embedded_carrier['url'] = $this->url
                .'deliveryorderautoupdate/webservices/'.$id_carrier.'/Carrier'.$id_carrier.'.php';
            } else {
                $embedded_carrier['checked'] = 2;
            }
        }
        $order_listcarrier = Db::getInstance()->executeS(
            "SELECT DISTINCT o.id_carrier as id,c.name
            FROM "._DB_PREFIX_."orders o
            INNER JOIN "._DB_PREFIX_."carrier c ON o.id_carrier=c.id_carrier
            WHERE c.active = 1 AND o.date_add > '".pSQL(Configuration::get("DELIVERY_ORDER_DATE"))."'
            ORDER BY c.name"
        );

        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/carriers.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $carrier = array_filter($webxml_crr->carrier, function ($c) use ($id_carrier) {
            return $c->id_carrier == $id_carrier;
        });
        if (count($carrier)) {
            $carrier = current($carrier);
        }

        $webxml_crr = $carrier->credentials;
        $carrier_emb_conf = array();
        $number = 0;
        if (isset($webxml_crr) && ($webxml_crr)) {
            $carrier_emb_conf[0]['infor'] = isset($webxml_crr->info)?$webxml_crr->info:'';
            if (isset($webxml_crr->credential->credname)) {
                $carrier_emb_conf[$number]['label'] = $webxml_crr->credential->credpublicname;
                $carrier_emb_conf[$number]['name'] = $webxml_crr->credential->credname;
                $carrier_emb_conf[$number]['val'] = Configuration::get($webxml_crr->credential->credname);
            } else {
                foreach ($webxml_crr as $crr) {
                    $isarray = is_array($crr);
                    if ($isarray) {
                        foreach ($crr as $crr_val) {
                            $carrier_emb_conf[$number]['label'] = $crr_val->credpublicname;
                            $carrier_emb_conf[$number]['name'] = $crr_val->credname;
                            $carrier_emb_conf[$number]['val'] = Configuration::get($crr_val->credname);
                            $number++;
                        }
                    }
                }
            }
        }
        // $webxml_crr = json_decode(
        //     json_encode(@simplexml_load_file($credentials_sub, 'SimpleXMLElement', LIBXML_NOCDATA))
        // );
        // $carrier_sub_conf = array();
        // $number = 0;
        // if (isset($webxml_crr) && ($webxml_crr)) {
        //     if ($webxml_crr->credential->credname) {
        //         $carrier_sub_conf[$number]['label'] = $webxml_crr->credential->credpublicname;
        //         $carrier_sub_conf[$number]['name'] = $webxml_crr->credential->credname;
        //         $carrier_sub_conf[$number]['val'] = Configuration::get($webxml_crr->credential->credname);
        //     } else {
        //         foreach ($webxml_crr as $crr) {
        //             foreach ($crr as $crr_val) {
        //                 $carrier_sub_conf[$number]['label'] = $crr_val->credpublicname;
        //                 $carrier_sub_conf[$number]['name'] = $crr_val->credname;
        //                 $carrier_sub_conf[$number]['val'] = Configuration::get($crr_val->credname);
        //                 $number++;
        //             }
        //         }
        //     }
        // }
        $push_exist = 0;
        if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'
            .$id_carrier.'/push'.$id_carrier.'.php')
        ) {
            $push_exist = 1;
        }

        $tpl = $this->createTemplate('edit_confcarrier.tpl');
        $tpl->assign(array(
            'carrier' => $carrier,
            'logo' => $this->url.'modules/deliveryorderautoupdate/views/img/logos/'.$id_carrier.'.jpg',
            'embedded_carrier' => $embedded_carrier,
            'carrier_emb_conf' => $carrier_emb_conf,
            // 'carrier_sub_conf' => $carrier_sub_conf,
            'id_carriername' => $id_carrier,
            'push_exist' => $push_exist,
            'hl_tr_carrier' => Configuration::get('hl_tr_carrier'.$id_carrier.'_url'),
            'token' => Tools::getAdminTokenLite('AdminModules'),
            'order_listcarrier' => $order_listcarrier,
            'url_backend' => $this->context->link->getAdminLink('AdminModules', true)
                .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name,
            'url_root' => $this->url,
            'module_dir' => _PS_MODULE_DIR_,
        ));

        echo $tpl->fetch();
    }
    public function ajaxProcessSaveSubject()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != Configuration::get('DELIVERY_TOKEN')) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $subject = Tools::getValue('subject');
        $lang_id = Tools::getValue('lang');
        $value = array();
        $value[$lang_id] = $subject;
        $rs['success'] = Configuration::updateValue('DELIVERY_EMAIL_SUBJECT', $value);
        echo json_Encode($rs);
    }
    public function getSubjectById($id_order, $id_lang)
    {
        $iso_code_lwr = Language::getIsoById($id_lang);
        $iso_code = Tools::strtoupper($iso_code_lwr);
        $_LANGMAIL = array();
        if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$iso_code_lwr.'/lang.php')) {
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$iso_code_lwr.'/lang.php');
        } else {
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/en/lang.php');
        }
        $id_order_carrier = 0;
        if ($id_order) {
            $id_order_carrier =  Db::getInstance()->getValue(
                'SELECT MAX(id_order_carrier)
                FROM `'._DB_PREFIX_.'order_carrier`
                WHERE id_order='.$id_order
            );
        }
        $status = $this->module->getStatus($id_order_carrier);
        $list = $this->module->getStatusList();
        if ($status && isset($list[$status['event_code']])) {
            $status['label'] = isset($list[$status['event_code']]->{$iso_code})
            ?$list[$status['event_code']]->{$iso_code}:$list[$status['event_code']]->EN;
            $subject = $_LANGMAIL['current_status'].$status['label'];
        } else {
            $subject = $this->module->l('No shipping status yet');
        }
        return $subject;
    }
    public function ajaxProcessChangeSubjectType()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != Configuration::get('DELIVERY_TOKEN')) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order = Tools::getValue('id_order');
        $id_lang = Tools::getValue('lang');
        $subjectType = Tools::getValue('subjectType');
        if ($subjectType == 'fixed') {
            $subject = Configuration::get('DELIVERY_EMAIL_SUBJECT', $id_lang);
        } else {
            $subject = $this->getSubjectById($id_order, $id_lang);
        }
        $rs['success'] = Configuration::updateValue('HL_TRACKING_EMAIL_SUBJECT', $subjectType);
        $rs['subject'] = $subject;

        echo json_Encode($rs);
    }
    public function ajaxProcessTrackConfig()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != Configuration::get('DELIVERY_TOKEN')) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $langs = count(Language::getLanguages());
        $l = Tools::getValue('l');
        $id_shop = $this->context->shop->id;
        if ($l == 1) {
            // send test email on config
            $email = Tools::getValue('email');
            $language = Tools::getValue('language');
            $id_order = Tools::getValue('id_order');

            $client_email = Db::getInstance()->getRow(
                'SELECT email, o.id_shop, o.reference, CONCAT(c.firstname," ",c.lastname) as customer_name,
                date(o.date_add) as date_add, oc.tracking_number, o.reference,
                hlc.name, o.id_lang as language, c.firstname, c.lastname
                FROM '._DB_PREFIX_.'customer c
                INNER JOIN '._DB_PREFIX_.'orders o ON o.id_customer = c.id_customer
                INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
				LEFT JOIN '._DB_PREFIX_.'carrier ca ON oc.id_carrier = ca.id_carrier
                LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON ca.id_reference=hlcm.id_carrier_ps
                LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
                WHERE o.id_order='.(int)$id_order
            );
            // $language = $client_email['language'];
            $status_tracks = Db::getInstance()->executeS(
                'SELECT MIN(step_date) as date_,event_code, carrier_response, email_sent, step_date
                FROM `'._DB_PREFIX_.'hl_tracking_history`
                WHERE id_order='.(int)$id_order.' AND event_code NOT IN(0)
                GROUP BY id_order,event_code
                ORDER BY id DESC'
            );

            $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
            $webxml_crr = json_decode(
                json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
            );
            $webxml_statuses = array();
            foreach ($webxml_crr->status as $step) {
                $webxml_statuses[$step->id_status] = $step;
            }
            $lang_id = Db::getInstance()->getRow(
                'SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$language
            );
            $lang_iso_code = $lang_id['iso_code'];
            $iso_code = Tools::strtoupper($lang_iso_code);
            $courier = $client_email['name'];
            if (count($status_tracks)) {
                $stt1 = isset($webxml_statuses[$status_tracks[0]['event_code']]->$iso_code)?
                $webxml_statuses[$status_tracks[0]['event_code']]->$iso_code:
                $webxml_statuses[$status_tracks[0]['event_code']]->EN;
                $background = $webxml_statuses[$status_tracks[0]['event_code']]->color;
            } else {
                $stt1 = 'No shipping status';
                $background = '#fff';
            }
            $html = EmailHelper::displayOrderDetail($client_email['reference'], $language);
            $meta = Meta::getMetaByPage('module-deliveryorderautoupdate-orders', $language);
            if ($client_email['reference']) {
                $link_tracking = $this->url
                .($langs > 1?$lang_id['iso_code'].'/':'').$meta['url_rewrite'].'?order_reference='
                .$client_email['reference'];
            } else {
                $link_tracking = 'no order has been found';
            }

            $params = array(
                '{current_status}' => $stt1,
                '{background}' => $background,
                '{order_reference}' => $client_email['reference'],
                '{customer_name}' => $client_email['customer_name'],
                '{firstname}' => $client_email['firstname'],
                '{lastname}' => $client_email['lastname'],
                '{date}' => $client_email['date_add'],
                '{body_content}' => $html,
                '{link_tracking}' => $link_tracking,
                '{courier}' => $courier,
                '{track_link}' => $link_tracking
            );
            if (Configuration::get('HL_TRACKING_EMAIL_SUBJECT') == 'fixed') {
                $subject = Configuration::get('DELIVERY_EMAIL_SUBJECT', $language);
            } else {
                $subject = $this->getSubjectById($id_order, $language);
            }
            $subject = $subject?$subject:'NO_SUBJECT';
            $id_lang = EmailHelper::checkexistEmail($language);
            Mail::Send(
                $id_lang,
                'tracking',
                $subject,
                $params,
                $email,
                null,
                null,
                null,
                null,
                null,
                _PS_ROOT_DIR_.'/modules/deliveryorderautoupdate/mails/',
                false,
                ($client_email['id_shop'] ? $client_email['id_shop'] : $id_shop),
                null
            );
        } elseif ($l == 2) {
            // show email demo on config
            $name = 'tracking';
            $id_order = Tools::getValue('id_order');
            $order = new Order($id_order);
            $email = new EmailHelper();
            $contents = $email->returnEmailTpl($id_order, Tools::getValue('lang'));
            $lang_id = (int)Tools::getValue('lang');
            $lang = Db::getInstance()->getValue(
                'SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE id_lang='.$lang_id
            );
            $subjectType = Configuration::get('HL_TRACKING_EMAIL_SUBJECT');
            if ($subjectType == 'fixed') {
                $subject_email = Configuration::get('DELIVERY_EMAIL_SUBJECT', $lang_id);
                $subject_email = $subject_email?$subject_email:'NO_SUBJECT';
            } else {
                $subject_email = $this->getSubjectById($id_order, $lang_id);
            }
            $langs = count(Language::getLanguages());
            $meta = Meta::getMetaByPage('module-deliveryorderautoupdate-orders', $lang_id);
            $link_tracking = $this->url
            .($langs > 1?$lang.'/':'').$meta['url_rewrite'].'?order_reference='
            .$order->reference;
            $contents = str_replace('{track_link}', $link_tracking, $contents);
            $tpl = $this->createTemplate('email_template.tpl');
            $tpl->assign(array(
                'html_email' => $contents,
                'subject_email' => $subject_email,
                'lang' => $lang,
                'name' => $name,
                'subjectType' => $subjectType,
            ));
            echo $tpl->fetch();
        } elseif ($l == 3) {
            $connector_selected = explode("_", Tools::getValue('connector_selected'));
            $id_carrier_hl = Db::getInstance()->getRow(
                'SELECT id_carrier_hl
                FROM '._DB_PREFIX_.'hl_carrier_matching
                WHERE `id_carrier_ps`='.(int)Tools::getValue('id_carrier')
            );
            if ($id_carrier_hl['id_carrier_hl']) {
                Db::getInstance()->execute(
                    'UPDATE `'._DB_PREFIX_.'hl_carrier_matching` SET `id_carrier_hl`='
                    .(int)$connector_selected[0].' WHERE `id_carrier_ps`='.(int)Tools::getValue('id_carrier')
                );
            } else {
                Db::getInstance()->insert(
                    'hl_carrier_matching',
                    array(
                        'id_carrier_hl' => (int)$connector_selected[0],
                        'id_carrier_ps' => (int)Tools::getValue('id_carrier')
                    )
                );
            }
        }
    }
    public function ajaxProcessGetOrderTracks()
    {
        $rs = array();
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $prefix = _DB_PREFIX_;
        $ids = Tools::getValue('ids');
        $in_cond = implode(', ', $ids);
        // $rs = array();
        $sql = "SELECT a.*, oc.id_order FROM {$prefix}hl_tracking_current_status a
                INNER JOIN {$prefix}order_carrier oc ON a.id_order_carrier = oc.id_order_carrier
                WHERE oc.id_order IN ({$in_cond})";
        $orders = Db::getInstance()->executeS($sql);
        if (!is_array($orders)) {
            $orders = array();
        }
        $tracks = array();

        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $locale = 0;
        if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.
            $this->context->language->iso_code.'/lang.php')) {
            $_LANGMAIL = array();
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$this->context->language->iso_code.'/lang.php');
            $locale = $_LANGMAIL['locale'];
        }
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        foreach ($orders as &$hs_) {
            $hs_['result'] = null;
            $time = strtotime($hs_['date']);
            $hs_['step_date'] = $formatter->format($time);
            $hs_['step_time'] = date('H:i:s', $time);
            foreach ($webxml_crr->status as $step) {
                if ($step->id_status == $hs_['id_status']) {
                    $iso_code = Tools::strtoupper($this->context->language->iso_code);
                    if (!isset($step->$iso_code)) {
                        $iso_code = self::DEFAULT_LANG;
                    }
                    $hs_['result'] = $step->$iso_code;
                }
            }
        }
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        $image = 'modules/deliveryorderautoupdate/views/img/logos/';
        foreach ($orders as $order) {
            $tpl = $this->createTemplate('admin_order_track.tpl');
            $tpl->assign(array(
                'order' => $order,
                'statuses' => $statuses,
                'image' => EmailHelper::getUrl().$image,
                'url_root' => $this->url,
            ));
            if (!isset($tracks[$order['id_order']])) {
                $tracks[$order['id_order']] = array(
                    'id_order' => $order['id_order'],
                    'count' => 1,
                    'track' => $tpl->fetch(),
                );
            } elseif ($tracks[$order['id_order']]['count'] < 4) {
                $tracks[$order['id_order']]['track'] = $tpl->fetch().$tracks[$order['id_order']]['track'];
                $tracks[$order['id_order']]['count'] += 1;
            }
        }
        $tracks = array_values($tracks);
        die(json_Encode($tracks));
    }
    public function ajaxProcessGetServerIP()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $rs['ip'] = Tools::getRemoteAddr();
        die(json_Encode($rs));
    }
    public function ajaxProcessClearData()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $type = Tools::getValue('type');
        $step = (int)Tools::getValue('step', 0);
        $email = (int)Tools::getValue('email', 0);

        switch ($type) {
            case 'all':
                $cond = '';
                break;
            case 'week':
                $cond = '< NOW() - INTERVAL 1 WEEK';
                break;
            case 'month':
                $cond = '< NOW() - INTERVAL 1 MONTH';
                break;
        }
        $rs['cond'] = $cond;
        if ($step) {
            $rs['step'] = Db::getInstance()->delete('hl_tracking_history', $cond?('date_add '.$cond):'');
        }
        if ($email) {
            $rs['email'] = Db::getInstance()->delete('hl_tracking_email', $cond?('date_sent '.$cond):'');
        }
        die(json_Encode($rs));
    }
    public function ajaxProcessLoadMore()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $itemsPerPage = $this->itemsPerPage;
        $filter = Tools::getValue('filter');
        $page = (int)Tools::getValue('page') + 1;
        $helper = new EmailHelper();
        $orders = $helper->listOrder($page, $filter);
        $total = $helper->getTotalOrder($filter);
        $rs['p'] = $page;
        $rs['pages_nb'] = ceil($total/$itemsPerPage)-1;
        $rs['html'] = $this->renderListOrder($orders);
        die(json_Encode($rs));
    }
    public function ajaxProcessLoadMoreReturn()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $filter = Tools::getValue('filter');
        $page = (int)Tools::getValue('page') + 1;
        $helper = new ReturnOrder();
        $list = $helper->listReturn($page, $filter);
        $itemsPerPage = $helper->itemsPerPage;
        $orders = $list['orders'];
        $total = $list['total'];
        $rs['p'] = $page;
        $rs['count'] = count($orders);
        $rs['total'] = $total;
        $rs['pages_nb'] = ceil($total/$itemsPerPage)-1;
        $rs['html'] = $this->renderListOrder($orders, 'return.tpl');
        die(json_Encode($rs));
    }
    private function renderListOrder($orders, $tpl = 'loadmore.tpl')
    {
        $prefix = _DB_PREFIX_;
        $context = Context::getContext();
        $helper = new EmailHelper();
        $carrier_2 = Db::getInstance()->executeS(
            "SELECT id_carrier, name
            FROM {$prefix}carrier
            WHERE deleted = 0 ORDER BY active DESC, name ASC "
        );
        $allCarriers = Db::getInstance()->executeS(
            "SELECT id_carrier, name
            FROM {$prefix}carrier"
        );
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        $carrier = Db::getInstance()->executeS('SELECT id, name FROM '._DB_PREFIX_.'hl_carrier ORDER BY id');
        $tpl = $this->createTemplate($tpl);
        $link = new Link();
        $url = $helper->getUrl();
        $langs = count(Language::getLanguages());
        $emp_lang = Language::getIsoById($context->employee->id_lang);
        $id_shop = (int)$context->shop->id;

        if ($this->v17) {
            foreach ($orders as &$order) {
                $order['url'] = $link->getAdminLink(
                    'AdminOrders',
                    true,
                    [],
                    array('id_order' => $order['id_order'], 'vieworder' => 1)
                );
            }
        } else {
            foreach ($orders as &$order) {
                $order['url'] = Dispatcher::getInstance()->createUrl(
                    'AdminOrders',
                    $context->language->id,
                    array(
                        'token' => Tools::getAdminTokenLite('AdminOrders'),
                        'id_order' => $order['id_order'],
                        'vieworder' => 1,
                    ),
                    false
                );
            }
        }
        $meta = Meta::getMetaByPage('module-deliveryorderautoupdate-orders', $context->employee->id_lang);
        $tpl->assign(array(
            'front_url' => Deliveryorderautoupdate::returnFrontUrl().($langs > 1?$emp_lang.'/':'').$meta['url_rewrite'],
            'orders' => $orders,
            'carrier_2' => $carrier_2,
            'allCarriers' => $allCarriers,
            'carrier' => $carrier,
            'statuses' => $statuses,
            'url' => $url,
            'id_shop' => $id_shop,
            'link' => $link,
        ));
        return $tpl->fetch();
    }
    public function ajaxProcessFilter()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $page = 0;
        $itemsPerPage = $this->itemsPerPage;
        $filter = Tools::getValue('filter');
        $changeOverview = (int)Tools::getValue('changeOverview');
        $helper = new EmailHelper();
        switch ($changeOverview) {
            case 1:
                $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
                $webxml_crr = json_decode(
                    json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
                );
                $carriers = $helper->getCarrierStat($filter);
                $deliveryStatus = $helper->getDeliveryStatus($filter);
                $overview = $helper->getOverview($filter);
                $rs['overview'] = $this->renderOverview(array(
                    'overview' => $overview,
                    'carrierStat' => $carriers,
                    'deliveryStatus' => $deliveryStatus,
                    'slider' => $helper->getSliderValue($filter)
                ));
                break;
            case 2:
                $deliveryStatus = $helper->getDeliveryStatus($filter);
                if (!empty($filter['status'])) {
                    $c = array_filter($deliveryStatus, function($status) use ($filter) {
                        return $status['id_status'] == $filter['status'];
                    });
                    if (!count($c)) {
                        $filter['status'] = '';
                    }
                }
                $overview = $helper->getOverview($filter);
                $tpl = $this->createTemplate('delivery_status.tpl');
                $tpl->assign(array(
                    'overview' => $overview,
                    'deliveryStatus' => $deliveryStatus,
                    'selectedStatus' => $filter['status']
                ));
                $rs['delivery_status'] = $tpl->fetch();
                break;
            case 3:
                $carriers = $helper->getCarrierStat($filter);
                if (!empty($filter['carrier'])) {
                    $c = array_filter($carriers, function($carrier) use ($filter) {
                        return $carrier['id_reference'] == $filter['carrier'];
                    });
                    if (!count($c)) {
                        $filter['carrier'] = '';
                    }
                }
                // $filter['carriers'] = array_map(function($carrier) {
                //     return $carrier['id_reference'];
                // }, $carriers);
                $overview = $helper->getOverview($filter);
                $tpl = $this->createTemplate('carriers.tpl');
                $tpl->assign(array(
                    'overview' => $overview,
                    'carrierStat' => $carriers,
                    'selectedCarrier' => $filter['carrier']
                ));
                $rs['carriers'] = $tpl->fetch();
                break;
        }
        $orders = $helper->listOrder($page, $filter);
        $total = $helper->getTotalOrder($filter);
        $rs['p'] = $page;
        $rs['pages_nb'] = ceil($total/$itemsPerPage)-1;
        $rs['html'] = $this->renderListOrder($orders);
        if (!isset($overview)) {
            $overview = $helper->getOverview($filter);
        }
        $rs['total'] = $overview['total'];
        die(json_Encode($rs));
    }
    public function renderOverview($params)
    {
        $tpl = $this->createTemplate('overview.tpl');
        $tpl->assign($params);
        return $tpl->fetch();
    }
    public function ajaxProcessFilterReturn()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $page = 0;
        $filter = Tools::getValue('filter');
        $helper = new ReturnOrder();
        $list = $helper->listReturn($page, $filter);
        $itemsPerPage = $helper->itemsPerPage;
        $orders = $list['orders'];
        $total = $list['total'];
        $rs['p'] = $page;
        $rs['count'] = count($orders);
        $rs['total'] = $total;
        $rs['pages_nb'] = ceil($total/$itemsPerPage)-1;
        $rs['html'] = $this->renderListOrder($orders, 'return.tpl');
        die(json_Encode($rs));
    }
    public function ajaxProcessFilterIssue()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $page = 0;
        $itemsPerPage = $this->itemsPerPage;
        $filter = Tools::getValue('filter');
        $helper = new EmailHelper();
        $list = $helper->listIssue($page, $filter);
        $orders = $list['orders'];
        $total = $list['total'];
        $rs['p'] = $page;
        $rs['pages_nb'] = ceil($total/$itemsPerPage)-1;
        $rs['html'] = $this->renderListOrder($orders, 'issue.tpl');
        die(json_Encode($rs));
    }
    public function ajaxProcessLoadTab()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $page = 0;
        $itemsPerPage = $this->itemsPerPage;
        $tab = Tools::getValue('id');
        switch ($tab) {
            case 'pick_ui_delivered':
                $helper = new EmailHelper();
                $filter = array('date' => array('key' => 'month'));
                $list = $helper->listDelayOrder($page, $filter);
                $orders = $list['orders'];
                $total = $list['total'];
                $rs['p'] = $page;
                $rs['total'] = $total;
                $rs['pages_nb'] = ceil($total/$itemsPerPage)-1;
                $rs['html'] = $this->renderListOrder($orders, 'delay.tpl');
                $rs += $helper->getTimeAvg($orders);
                break;
            case 'pick_ui_return':
                $helper = new ReturnOrder();
                $list = $helper->listReturn($page, array('status' => 'no_delivery'));
                $itemsPerPage = $helper->itemsPerPage;
                $orders = $list['orders'];
                $total = $list['total'];
                $rs['p'] = $page;
                $rs['total'] = $total;
                $rs['pages_nb'] = ceil($total/$itemsPerPage)-1;
                $rs['html'] = $this->renderListOrder($orders, 'return.tpl');
                break;
            case 'pick_ui_issue':
                $helper = new EmailHelper();
                $list = $helper->listIssue($page);
                $orders = $list['orders'];
                $total = $list['total'];
                $rs['p'] = $page;
                $rs['total'] = $total;
                $rs['pages_nb'] = ceil($total/$itemsPerPage)-1;
                $rs['html'] = $this->renderListOrder($orders, 'issue.tpl');
                break;
        }
        die(json_Encode($rs));
    }
    public function ajaxProcessDelayFilter()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $page = Tools::getValue('page', 0);
        $helper = new EmailHelper();
        $filter = array();
        if (Tools::isSubmit('key')) {
            $key = Tools::getValue('key');
            $range = Tools::getValue('range');
            $filter['date'] = array(
                'key' => $key,
                'range' => $range,
            );
        }
        if (Tools::isSubmit('filter')) {
            $filter = array_merge($filter, Tools::getValue('filter'));
        }
        $list = $helper->listDelayOrder($page, $filter);
        $orders = $list['orders'];
        $total = $list['total'];
        $rs['p'] = $page;
        $rs['pages_nb'] = ceil($total/ITEM_PER_PAGE)-1;
        $rs['html'] = $this->renderListOrder($orders, 'delay.tpl');
        $total = Tools::getValue('total', null);
        $count = Tools::getValue('count', null);
        $rs += $helper->getTimeAvg($orders, $total, $count);
        die(json_Encode($rs));
    }
    public function ajaxProcessForce()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order_carrier = Tools::getValue('id_order_carrier');
        $order = Db::getInstance()->getRow(
            'SELECT DISTINCT o.id_order,oc.id_order_carrier,o.id_shop,o.id_lang,
            o.reference, oc.tracking_number,
            hlc.name as carrier, hlc.id, hlc.method, hlc.url, c.url as tracking_url
            FROM '._DB_PREFIX_.'orders o
            INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
            INNER JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
            INNER JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
            INNER JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
            WHERE oc.id_order_carrier='.(int)$id_order_carrier
        );
        $id_status = Tools::getValue('id_status');
        $send_mail = Tools::getValue('send_mail');
        $date = DateTime::createFromFormat('d/m/Y', Tools::getValue('date'));
        $id_order = $order['id_order'];
        $status = new stdClass();
        $status->shipment_ref = $id_order_carrier;
        $status->carrier_server_status_code = '';
        $status->carrier_shipping_status_date = $date->format('Y-m-d H:i:s');
        $status->carrier_server_success = true;
        $status->carrier_shipping_status_code = $id_status;
        $status->carrier_shipping_status_text = 'force';
        $status->carrier_server_status_text = 'force';
        $status->module_shipping_status_code = $id_status;

        $trackingmodel = new TrackingModel(4);
        $trackingmodel->setLang($order);
        $rs['force'] = $trackingmodel->insertTrackHistory($order, $status);
        $email_sent = false;
        if ($send_mail) {
            $email_sent = $trackingmodel->sendMail($order, $status, true);
        }
        if ($id_order) {
            $trackingmodel->updateOrder(new Order($id_order), $status);
        }
        $statuses = $trackingmodel->getStatusList();
        $tracking_history = Db::getInstance()->getRow(
            'SELECT step_date, date_add
            FROM '._DB_PREFIX_.'hl_tracking_history
            WHERE id_order='.(int)$id_order.' ORDER BY id DESC'
        );
        $order['step_date'] = $tracking_history['step_date'];
        $iso_code_stt = Tools::strtoupper($this->context->language->iso_code);
        $order['result'] = $id_status.'_'.$statuses[$id_status]->$iso_code_stt;
        $tpl = $this->createTemplate('ajax_statuscarrier.tpl');
        $tpl->assign(array(
            'order' => $order,
            'url_root' => $this->url,
            'statuses' => $statuses,
            'email_sent' => $email_sent,
        ));
        $rs['last_status_result'] = $tpl->fetch();
        die(json_Encode($rs));
    }
    public function ajaxProcessForceReturn()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_return = Tools::getValue('id_return');
        $id_status = Tools::getValue('id_status');
        $return = Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'hl_tracking_return
            where id_return = '.(int)$id_return
        );
        $status = new stdClass();
        $status->shipment_ref = $id_return;
        $status->carrier_server_status_code = '';
        $status->carrier_shipping_status_date = Date('Y-m-d H:i:s');
        $status->carrier_server_success = true;
        $status->carrier_shipping_status_code = $id_status;
        $status->carrier_shipping_status_text = 'force';
        $status->carrier_server_status_text = 'force';
        $status->module_shipping_status_code = $id_status;

        $trackingmodel = new TrackingModel(4);
        $trackingmodel->updateReturn($return, $status);
        $statuses = $trackingmodel->getStatusList();
        $tpl = $this->createTemplate('ajax_trackreturn.tpl');
        $tpl->assign(array(
            'status' => $status,
            'url_root' => $this->url,
            'statuses' => $statuses,
            'lang' => Tools::strtoupper($this->context->language->iso_code)
        ));
        $rs['last_status_result'] = $tpl->fetch();
        die(json_Encode($rs));
    }
    public function ajaxProcessDisable()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order_carrier = (int)Tools::getValue('id_order_carrier');
        $disabled = (int)Tools::getValue('disabled');
        if ($disabled) {
            $rs['success'] = Db::getInstance()->insert(
                'hl_tracking_disable',
                array(
                    'id_order_carrier' => (int)$id_order_carrier,
                ),
                false,
                true,
                Db::INSERT_IGNORE
            );
        } else {
            $rs['success'] = Db::getInstance()->delete('hl_tracking_disable', 'id_order_carrier='.$id_order_carrier);
        }
        die(json_Encode($rs));
    }
    public function ajaxProcessAddShipment()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order = (int)Tools::getValue('id_order');
        $id_carrier = (int)Tools::getValue('id_carrier');
        $tracking_number = Tools::getValue('shipping_number');
        $orderCarrier = new OrderCarrier();
        $orderCarrier->id_order = $id_order;
        $orderCarrier->id_carrier = $id_carrier;
        $orderCarrier->tracking_number = $tracking_number;
        $rs['success'] = $orderCarrier->add();
        $rs['id_order_carrier'] = $orderCarrier->id;
        $shipment = EmailHelper::getShipmentById($orderCarrier->id);
        $webxml_crr = EmailHelper::getStatusXML();
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        $tpl = $this->createTemplate('../../hook/shipment-row.tpl');
        $this->context->smarty->assign(array(
            'shipments' => array($shipment),
            'url' => EmailHelper::getUrl(),
            'v16' => (int)version_compare(_PS_VERSION_, '1.7', '<'),
            'statuses' => $statuses
        ));
        $rs['html'] = $tpl->fetch();
        die(json_Encode($rs));
    }
    public function ajaxProcessAddReturn()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order = (int)Tools::getValue('id_order');
        $id_order_return = (int)Tools::getValue('id_order_return');
        $id_connector = (int)Tools::getValue('id_connector');
        $date = date('Y-m-d H:i:s');
        if (!$id_connector) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid id_connector';
            die(json_encode($rs));
        }
        $tracking_number = Tools::getValue('shipping_number');
        // $order_return = new OrderReturn($id_order_return);
        try {
            $rs['success'] = Db::getInstance()->execute(
                'INSERT INTO '._DB_PREFIX_."hl_tracking_return
                (`id_order`, `id_order_return`, `id_connector`, `shipping_number`, `date_add`)
                VALUES ({$id_order}, {$id_order_return}, {$id_connector}, '{$tracking_number}', '{$date}')"
            );
            $returns = ReturnOrder::getReturnByOrder($id_order);
            $webxml_crr = EmailHelper::getStatusXML();
            $statuses = array();
            foreach ($webxml_crr->status as $status) {
                $statuses[$status->id_status] = $status;
            }
            $tpl = $this->createTemplate('../../hook/returnlist.tpl');
            $helper = new EmailHelper();
            $this->context->smarty->assign(array(
                'returns' => $returns,
                'url' => $helper->getUrl(),
                'statuses' => $statuses
            ));
            $rs['html'] = $tpl->fetch();
        } catch (Exception $e) {
            $rs['success'] = false;
            $rs['msg'] = $e->getMessage();
        }
        die(json_Encode($rs));
    }
    public function ajaxProcessSplitShipment()
    {
        $rs = array('success' => true, 'shipments' => array());
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order_carrier = (int)Tools::getValue('id_order_carrier');
        $orderCarrier = new OrderCarrier($id_order_carrier);
        $tracking_number = $orderCarrier->tracking_number;
        $tracking_split = explode(',', $tracking_number);
        $firstShipment = true;
        $shipments = array();
        $webxml_crr = EmailHelper::getStatusXML();
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        foreach ($tracking_split as $tracking) {
            $tracking = trim($tracking);
            if ($tracking) {
                if ($firstShipment) {
                    $orderCarrier->tracking_number = $tracking;
                    $firstShipment = false;
                    $rs['success'] &= $orderCarrier->update();
                    $shipment = EmailHelper::getShipmentById($orderCarrier->id);
                } else {
                    $newOrderCarrier = clone($orderCarrier);
                    $newOrderCarrier->id_order_carrier = 0;
                    $newOrderCarrier->id = 0;
                    $newOrderCarrier->tracking_number = $tracking;
                    $rs['success'] &= $newOrderCarrier->add();
                    $rs['shipments'][] = $newOrderCarrier;
                    $shipment = EmailHelper::getShipmentById($newOrderCarrier->id);
                }
                if (!$rs['success']) {
                    $rs['err'] = "split order with tracking {$tracking} failed";
                    die(json_Encode($rs));
                } else {
                    $shipments[] = $shipment;
                }
            }
        }
        $tpl = $this->createTemplate('../../hook/shipment-row.tpl');
        $this->context->smarty->assign(array(
            'shipments' => array_reverse($shipments),
            'url' => EmailHelper::getUrl(),
            'v16' => (int)version_compare(_PS_VERSION_, '1.7', '<'),
            'statuses' => $statuses
        ));
        $rs['html'] = $tpl->fetch();
        // $deleteOldOrder = $orderCarrier->delete();
        // if (!$deleteOldOrder) {
        //     $rs['success'] = false;
        //     $rs['err'] = "delete order_carrier {$id_order_carrier} failed";
        //     die(json_Encode($rs));
        // }
        // $sql = "DELETE FROM `"._DB_PREFIX_."hl_tracking_history` WHERE id_order_carrier={$id_order_carrier}";
        // $deleteOldTrack = Db::getInstance()->execute($sql);
        // if (!$deleteOldTrack) {
        //     $rs['success'] = false;
        //     $rs['err'] = "delete tracking failed";
        //     die(json_Encode($rs));
        // }
        die(json_Encode($rs));
    }
    public function ajaxProcessDeleteShipment()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $id_order_carrier = (int)Tools::getValue('id_order_carrier');
        TrackingModel::deleteCurrentStatus($id_order_carrier);
        TrackingModel::deleteEvents($id_order_carrier);
        $orderCarrier = new OrderCarrier($id_order_carrier);
        $rs['success'] = $orderCarrier->delete();
        die(json_Encode($rs));
    }
    public function ajaxProcessChangeShipment()
    {
        $rs = array('success' => true);
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $id_order_carrier = (int)Tools::getValue('id_order_carrier', 0);
        $history = Db::getInstance()->executeS(
            'SELECT id,date_add, step_date, hl_carrier, success_response, carrier_response,event_code,id_order,
            method as carrier, email_sent
            FROM `'._DB_PREFIX_.'hl_tracking_history`
            WHERE id_order_carrier='.$id_order_carrier.'
            ORDER BY id DESC'
        );
        $shipment = Db::getInstance()->getRow(
            'SELECT oc.id_order_carrier, oc.tracking_number, c.name, c.id_carrier,
            c.id_reference, hlc.id as id_connector, hlc.name as connector, c.url as tracking_url,
            oc.date_add
            FROM `'._DB_PREFIX_.'order_carrier` oc
            LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
            LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON hlcm.id_carrier_ps=c.id_reference
            LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc ON hlcm.id_carrier_hl=hlc.id
            WHERE oc.id_order_carrier='.$id_order_carrier
        );
        if (strpos($shipment['tracking_url'], '@') !== false) {
            $shipment['tracking_url'] = str_replace('@', $shipment['tracking_number'], $shipment['tracking_url']);
        } elseif ($shipment['tracking_url']) {
            $shipment['tracking_url'] = $shipment['tracking_url'].$shipment['tracking_number'];
        } else {
            $shipment['tracking_url'] = null;
        }
        $steps = TrackingModel::getTrackSteps($id_order_carrier);
        foreach ($webxml_crr->status as $step) {
            if ($step->id_status == $steps['current_status']) {
                $iso_code = Tools::strtoupper($this->context->language->iso_code);
                if (!isset($step->$iso_code)) {
                    $iso_code = self::DEFAULT_LANG;
                }
                $steps['status_text'] = $step->$iso_code;
            }
        }
        $history_left = TrackingModel::getTrackEvents($id_order_carrier);
        $emails = EmailHelper::getEmailListByIdOrderCarrier($id_order_carrier);
        $issue = EmailHelper::getIssueByShipment($id_order_carrier);
        if ($issue) {
            $issue['histories'] = EmailHelper::getIssueHistory($issue['id_issue']);
        }
        $orderCarrier = new OrderCarrier($id_order_carrier);
        $id_order = $orderCarrier->id_order;
        $returns = ReturnOrder::getReturnByOrder($id_order);
        $locale = 0;
        if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.
            $this->context->language->iso_code.'/lang.php')) {
            $_LANGMAIL = array();
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$this->context->language->iso_code.'/lang.php');
            $locale = $_LANGMAIL['locale'];
        }
        setlocale(LC_TIME, $locale);
        foreach ($history as &$hs) {
            $hs['result'] = null;
            foreach ($webxml_crr->status as $step) {
                if ($step->id_status == $hs['event_code']) {
                    $iso_code = Tools::strtoupper($this->context->language->iso_code);
                    if (!isset($step->$iso_code)) {
                        $iso_code = self::DEFAULT_LANG;
                    }
                    $hs['result'] = $hs['event_code'].'_'.$step->$iso_code;
                }
            }
        }
        foreach ($history_left as &$hs_) {
            $hs_['result'] = null;
            $time = strtotime($hs_['date']);
            $hs_['step_date'] = '';
            $hs_['step_time'] = date('H:i:s', $time);
            foreach ($webxml_crr->status as $step) {
                if ($step->id_status == $hs_['id_status']) {
                    $iso_code = Tools::strtoupper($this->context->language->iso_code);
                    if (!isset($step->$iso_code)) {
                        $iso_code = self::DEFAULT_LANG;
                    }
                    $hs_['result'] = $hs_['id_status'].'_'.$step->$iso_code;
                }
            }
        }
        $iso_code = Tools::strtoupper($this->context->language->iso_code);
        $shipping = array_map(function ($step) use ($iso_code) {
            return isset($step->$iso_code)?$step->$iso_code:$step->EN;
        }, $webxml_crr->status);
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        foreach ($history as &$hs) {
            $hs['step_date'] = date($this->context->language->date_format_full, strtotime($hs['step_date']));
            $hs['date_add'] = date($this->context->language->date_format_full, strtotime($hs['date_add']));
            if (isset($statuses[$hs['event_code']])) {
                $hs['shipping_status'] = isset($statuses[$hs['event_code']]->$iso_code)?
                $statuses[$hs['event_code']]->$iso_code:$statuses[$hs['event_code']]->EN;
            }
        }
        foreach ($emails as &$email) {
            $email['date_sent'] = date($this->context->language->date_format_full, strtotime($email['date_sent']));
            if (isset($statuses[$email['shipping_status']])) {
                $email['status_text'] = isset($statuses[$email['shipping_status']]->$iso_code)?
                $statuses[$email['shipping_status']]->$iso_code:$statuses[$email['shipping_status']]->EN;
            }
        }
        $tpl = $this->createTemplate('../../hook/tracking-box.tpl');
        $this->context->smarty->assign(array(
            'steps' => $steps,
            'history' => $history,
            'statuses' => $statuses,
            'issue' => $issue,
            'returns' => $returns,
            'url_root' => $this->url,
            'shipping' => $shipping,
            'emails' => $emails,
            'history_left' => $history_left,
            'email_statuses' => $this->email_status,
            'date_format_full' => $this->context->language->date_format_full,
            'date_format_lite' => $this->context->language->date_format_lite,
            'methods' => $this->method,
        ));
        $rs['html'] = $tpl->fetch();
        $rs['shipment'] = $shipment;
        die(json_Encode($rs));
    }
    public function ajaxProcessSearchOrder()
    {
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $q = Tools::getValue('q');
        $context = Context::getContext();
        $sql = "SELECT oc.id_order_carrier, o.id_order, o.reference, CONCAT(cu.lastname,' ',cu.firstname) AS customer, GROUP_CONCAT(oc.tracking_number) AS tracking_number
        FROM "._DB_PREFIX_."order_carrier oc
        LEFT JOIN "._DB_PREFIX_."orders o ON oc.id_order=o.id_order
        LEFT JOIN "._DB_PREFIX_."customer cu ON o.id_customer=cu.id_customer
        WHERE oc.id_order LIKE '{$q}%' OR o.reference LIKE '{$q}%' OR cu.lastname LIKE '{$q}%' OR oc.tracking_number LIKE '{$q}%'
		GROUP BY oc.id_order";
        $orders = Db::getInstance()->executeS($sql);
        die(json_Encode($orders));
    }
    public function ajaxProcessUpdateXML()
    {
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $trackingmodel = new TrackingModel();
        $helper = new EmailHelper();
        $rs = array('success' => false);
        $check = $helper->updateCheck();
        if ($check['update']) {
            $rs = $trackingmodel->updateStatus();
        }
        die(json_Encode($rs));
    }
    public function ajaxProcessCheckUpdate()
    {
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->secure_key) {
            $rs['success'] = false;
            $rs['msg'] = 'Invalid key';
            die(json_encode($rs));
        }
        $trackingmodel = new TrackingModel();
        $helper = new EmailHelper();
        $rs = array('success' => false);
        $check = $helper->updateCheck();
        if ($check['update']) {
            $rs = $trackingmodel->updateStatus();
        }
        die(json_Encode($rs));
    }
    public function ajaxProcessViewResponse()
    {
        $id_shipment = Tools::getValue('shipment_ref');
        $devmode = (int)Tools::getValue('devmode');
        $track = Tools::getValue('track', 'order');
        if ($track == 'return') {
            $order = $this->getReturn($id_shipment);
            $connector = $order['hl_carrier'];
        } else {
            $order = $this->getOrder($id_shipment);
            $connector = $order['id'];
        }
        $connector_path = _PS_MODULE_DIR_.'deliveryorderautoupdate/classes/connectors/Carrier'.$connector.'.php';
        if (file_exists($connector_path)) {
            include_once($connector_path);
            $carrier_name = 'Carrier'.$connector;
        } else {
            $carrier_name = 'deliveryorderautoupdate\Carrier';
        }
        $carrier = new $carrier_name($order);
        if ($devmode) {
            $carrier->print();
        } else {
            $status = $carrier->track();
            die(json_Encode($status));
        }
        die();
    }
}
