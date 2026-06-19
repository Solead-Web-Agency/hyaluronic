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

require_once(_PS_MODULE_DIR_.'deliveryorderautoupdate/deliveryorderautoupdate.php');
require_once(_PS_MODULE_DIR_.'deliveryorderautoupdate/classes/trackingmodel.php');
class EmailHelper extends Module
{
    const DEFAULT_LANG = 'EN';
    public static $email_status = array("", "Sent", "", "Clicked");

    public static function getSubjectById($id_order_carrier, $id_lang)
    {
        $iso_code_lwr = Language::getIsoById($id_lang);
        $iso_code = Tools::strtoupper($iso_code_lwr);
        $_LANGMAIL = array();
        if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$iso_code_lwr.'/lang.php')) {
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$iso_code_lwr.'/lang.php');
        } else {
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/en/lang.php');
        }
        $module = Module::getInstanceByName('deliveryorderautoupdate');
        $status = $module->getStatus($id_order_carrier);
        $list = $module->getStatusList();
        if ($status && isset($list[$status['event_code']])) {
            $status['label'] = isset($list[$status['event_code']]->{$iso_code})
            ?$list[$status['event_code']]->{$iso_code}:$list[$status['event_code']]->EN;
            $subject = $_LANGMAIL['current_status'].$status['label'];
        } else {
            $subject = $module->l('No shipping status yet');
        }
        return $subject;
    }
    public function returnEmailTpl($id_order, $lang)
    {
        $shop_name = Db::getInstance()->getRow(
            'SELECT name FROM '._DB_PREFIX_.'shop WHERE id_shop='.(int)$this->context->shop->id
        );
        $lang_id = Db::getInstance()->getRow('SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$lang);
        $lang_iso_code = $lang_id['iso_code'];
        $idShop = Context::getContext()->shop->id;
        if (false !== Configuration::get('PS_LOGO_MAIL') &&
            file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_MAIL', null, null, $idShop))
        ) {
            $logo = Configuration::get('PS_LOGO_MAIL', null, null, $idShop);
        } elseif (file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $idShop))) {
            $logo = Configuration::get('PS_LOGO', null, null, $idShop);
        }
        if ($id_order) {
            $client_email = Db::getInstance()->getRow(
                'SELECT email, c.firstname, c.lastname, CONCAT(c.firstname," ",c.lastname) as customer_name,
                date(o.date_add) as date_add, o.reference
                FROM '._DB_PREFIX_.'customer c
                INNER JOIN '._DB_PREFIX_.'orders o ON o.id_customer = c.id_customer
                WHERE o.id_order='.(int)$id_order
            );
            $html = self::displayOrderDetail($client_email['reference'], $lang);
            $params = array(
                '{firstname}' => $client_email['firstname'],
                '{lastname}' => $client_email['lastname'],
                '{body_content}' => $html,
                '{shop_logo}' => $this->getUrl().'img/'.$logo,
                '{shop_url}' => $this->getUrl(),
                '{shop_name}' => $shop_name['name'],
            );
        } else {
            $params = array(
                '{shop_logo}' => $this->getUrl().'img/'.$logo,
                '{shop_url}' => $this->getUrl(),
                '{shop_name}' => $shop_name['name']
            );
        }
        $name = 'tracking';
        $handle = fopen(_PS_MODULE_DIR_."deliveryorderautoupdate/mails/".$lang_iso_code."/".$name.".html", "r");
        $contents = fread(
            $handle,
            filesize(_PS_MODULE_DIR_."deliveryorderautoupdate/mails/".$lang_iso_code."/".$name.".html")
        );
        fclose($handle);
        foreach ($params as $key => $pr) {
            $contents = str_replace($key, $pr, $contents);
        }

        return $contents;
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
    public static function getBase()
    {
        $force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
        $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';

        if (isset($force_ssl) && $force_ssl) {
            return $protocol_link.Tools::getShopDomainSsl();
        } else {
            return _PS_BASE_URL_;
        }
    }
    public function getEvent($id_order_carrier)
    {
        $event_code = Db::getInstance()->getRow(
            'SELECT event_code, step_date, date_add
            FROM '._DB_PREFIX_.'hl_tracking_history
            WHERE id_order_carrier='.(int)$id_order_carrier
            .' ORDER BY id DESC'
        );
        $event = $event_code['event_code'];
        $values = array();
        $values['event'] = $event;
        $values['date_add'] = $event_code['date_add'];
        $values['step_date'] = $event_code['step_date'];
        return $values;
    }
    public function getIdShop($shopContext, $id_sp)
    {
        if ((strpos($shopContext, 'g') !== false) && ($shopContext)) {
            $split = explode('-', $shopContext);
            $id_shop = Db::getInstance()->getRow(
                'SELECT GROUP_CONCAT(id_shop) as id FROM '._DB_PREFIX_.'shop Where id_shop_group='.(int)$split[1]
            );
            $id_shop = $id_shop['id'];
        } elseif (strpos($shopContext, 's') !== false) {
            $id_shop = $id_sp;
        } else {
            $id_shop = null;
        }
        return $id_shop;
    }
    public static function checkexistEmail($id_lang)
    {
        $sql = 'SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$id_lang;
        $iso_code = Db::getInstance()->getRow($sql);
        if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$iso_code['iso_code'].'/tracking.html')) {
            return $id_lang;
        }
        return Configuration::get("PS_LANG_DEFAULT");
    }
    public static function displayOrderDetail($id, $id_lang = 1, $show_all_status = false)
    {
        $context = Context::getContext();
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $lang_iso_code = Language::getIsoById($id_lang);
        $locale = 0;
        $_LANGMAIL = array();
        if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$lang_iso_code.'/lang.php')) {
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$lang_iso_code.'/lang.php');
            $locale = $_LANGMAIL['locale'];
        } else {
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/en/lang.php');
        }
        setlocale(LC_TIME, $locale);
        if ($id) {
            $orders = Db::getInstance()->executeS(
                'SELECT o.reference, a.city as address1, c2.name, hlc.id as id_connector, oc.tracking_number,
                c2.id_carrier, c2.url, oc.date_add, oc.id_order_carrier
                FROM `'._DB_PREFIX_.'orders` o
                LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery = a.id_address
                INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order = oc.id_order
                LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
                LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference=c2.id_reference AND c2.deleted = 0
                LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
                LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
                WHERE o.reference LIKE "'.$id.'"
                ORDER BY oc.id_order_carrier ASC'
            );
            foreach ($orders as &$order) {
                if ($show_all_status) {
                    $history_left = Db::getInstance()->executeS(
                        'SELECT hl.id,hl.date_add, hl.step_date as stepdate, hl.hl_carrier,
                        hl.success_response, hl.carrier_response,
                        hl.event_code,hl.id_order, hl.email_sent, o.reference, o.date_add as order_created
                        FROM `'._DB_PREFIX_.'hl_tracking_history` hl
                        INNER JOIN (
                        SELECT event_code, MAX(id) as id
                        FROM `'._DB_PREFIX_.'hl_tracking_history`
                        where id_order_carrier = '.$order['id_order_carrier'].'
                        GROUP BY event_code
                        ) lastth ON hl.id = lastth.id
                        INNER JOIN '._DB_PREFIX_.'order_carrier oc ON hl.id_order_carrier=oc.id_order_carrier
                        INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = oc.id_order
                        WHERE hl.id_order_carrier = '.$order['id_order_carrier'].'
                        ORDER BY hl.date_add DESC'
                    );
                } else {
                    $history_left = Db::getInstance()->executeS(
                        'SELECT hl.id,hl.date_add, hl.step_date as stepdate, hl.hl_carrier,
                        hl.success_response, hl.carrier_response,
                        hl.event_code,hl.id_order, hl.email_sent, o.reference, o.date_add as order_created
                        FROM `'._DB_PREFIX_.'hl_tracking_history` hl
                        INNER JOIN (
                        SELECT event_code, MAX(id) as id
                        FROM `'._DB_PREFIX_.'hl_tracking_history`
                        where id_order_carrier = '.$order['id_order_carrier'].'
                        GROUP BY event_code
                        ) lastth ON hl.id = lastth.id
                        INNER JOIN '._DB_PREFIX_.'order_carrier oc ON hl.id_order_carrier=oc.id_order_carrier
                        INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = oc.id_order
                        WHERE hl.id_order_carrier = '.$order['id_order_carrier'].
                        ' AND hl.event_code IN (1,2,3,4,5,6,7,8,9)
                        ORDER BY hl.date_add DESC'
                    );
                }
                foreach ($history_left as &$hs_) {
                    $hs_['result'] = null;
                    $time = strtotime($hs_['stepdate']);
                    $hs_['step_date'] = date('jS F Y', $time);
                    // $hs_['step_date'] = strftime('%e %B %G', $time);
                    $hs_['step_time'] = date('H:i:s', $time);
                    foreach ($webxml_crr->status as $step) {
                        if ($step->id_status == $hs_['event_code']) {
                            $iso_code = Tools::strtoupper($lang_iso_code);
                            if (!isset($step->$iso_code)) {
                                $iso_code = self::DEFAULT_LANG;
                            }
                            $hs_['result'] = $hs_['event_code'].'_'.$step->$iso_code;
                        }
                    }
                }
                $order['history_left'] = $history_left;
                $time = strtotime($order['date_add']);
                $order['created_date'] = date('jS F Y', $time);
                // $order['created_date'] = strftime('%e %B %G', $time);
                $order['created_time'] = date('H:i:s', $time);
            }
            $statuses = array();
            foreach ($webxml_crr->status as $status) {
                $statuses[$status->id_status] = $status;
            }
            $svg = 'modules/deliveryorderautoupdate/views/img/svg/';
            $tpl = $context->smarty->createTemplate(
                _PS_MODULE_DIR_.'deliveryorderautoupdate/views/templates/hook/email-steplist.tpl'
            );
            $tpl->assign(array(
                'orderArr' => $orders,
                'statuses' => $statuses,
                'image' => EmailHelper::getBase()._THEME_SHIP_DIR_,//EmailHelper::getUrl().$image,
                'svg' => EmailHelper::getUrl().$svg,
                'url_root' => self::getUrl(),
                'lang' => $_LANGMAIL,
                'reference' => $id
            ));
            return $tpl->fetch();
        }
        return '';
    }
    private function toStatusCond($filter, $field)
    {
        if ($filter['status'] == '999') {
            $str = "{$field} is null";
        } elseif ($filter['status'] == 'not_delivered') {
            $str = "({$field} NOT IN(1,5) OR $field is NULL)";
        } else {
            $str = "{$field} = {$filter['status']}";
        }
        return $str;
    }
    public function getFilterCondition($filter)
    {
        $filter_where = '';
        $condition = array();
        if (isset($filter['id_order_carrier']) && $filter['id_order_carrier']) {
            $condition[] = "oc.id_order_carrier = {$filter['id_order_carrier']}";
        } else {
            if (isset($filter['status']) && $filter['status'] != '') {
                $condition[] = $this->toStatusCond($filter, 'tcs.id_status');
            }

            if (isset($filter['carrier']) && !empty($filter['carrier'])) {
                $condition[] = "c2.id_reference = {$filter['carrier']}";
            }
            if (isset($filter['date'])) {
                $cond = $this->getDateQuery($filter['date'], 'oc.date_add');
                if ($cond) {
                    $condition[] = $cond;
                }
            }
            if (isset($filter['transit_range'])) {
                $condition[] = "IF (
                        tcs.date is not null,
                        DATEDIFF(tcs.date,oc.date_add),
                        DATEDIFF( current_date,oc.date_add)
                ) BETWEEN {$filter['transit_range']['start']} AND {$filter['transit_range']['end']}";
            }
        }
        if (count($condition)) {
            $filter_where = 'AND '. implode(' AND ', $condition);
        }
        return $filter_where;
    }
    public function getSortFilter($filter)
    {
        $orderBy = '';
        if (isset($filter['sort']) && isset($filter['sort']['orderBy']) && isset($filter['sort']['orderWay'])) {
            switch ($filter['sort']['orderBy']) {
                case 'order':
                    $field = 'o.id_order';
                    break;
                case 'reference':
                    $field = 'o.reference';
                    break;
                case 'customer':
                    $orderBy = "ORDER BY cus.firstname {$filter['sort']['orderWay']}, cus.lastname {$filter['sort']['orderWay']}";
                    break;
                case 'carrier':
                    $field = 'id_carrier';
                    break;
                case 'connector':
                    $field = 'hlc.id';
                    break;
                case 'tracking_number':
                    $field = 'oc.tracking_number';
                    break;
                case 'status':
                    $field = 'tcs.id_status';
                    break;
                default:
                    $field = 'o.id_order';
                    break;
            }
            if (empty($orderBy)) {
                $orderBy = "ORDER BY {$field} {$filter['sort']['orderWay']}";
            }
        } else {
            $orderBy = "ORDER BY o.id_order DESC, oc.id_order_carrier ASC";
        }
        return $orderBy;
    }
    private function getDateQuery($date, $field)
    {
        $condition = '';
        switch ($date["key"]) {
            case "prevMonth":
                $condition = "MONTH({$field}) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
                $condition = "YEAR({$field}) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
                break;
            case "prevYear":
                $condition = "YEAR({$field}) = YEAR(CURRENT_DATE()) - 1";
                break;
            case "month":
                $condition = "MONTH({$field}) = MONTH(CURRENT_DATE())";
                $condition = "YEAR({$field}) = YEAR(CURRENT_DATE())";
                break;
            case "year":
                $condition = "YEAR({$field}) = YEAR(CURRENT_DATE())";
                break;
            case "range":
                $condition = "{$field} BETWEEN \"{$date["range"]["start"]} 00:00:00\" AND \"{$date["range"]["end"]} 23:59:59\"";
                break;
        }
        return $condition;
    }
    public function getIssueFilterCondition($filter)
    {
        $filter_where = '';
        $condition = array();
        $id_shop = (int)$this->getIdShop($this->context->cookie->shopContext, $this->context->shop->id);
        if ($id_shop) {
            $condition[] = "o.id_shop = {$id_shop}";
        }
        $condition[] = "tis.date =(SELECT tis.date FROM "._DB_PREFIX_."hl_tracking_issue_status tis
        WHERE ti.id_issue=tis.id_issue ORDER BY tis.date DESC LIMIT 1)";
        if ($filter) {
            if (isset($filter['id_issue']) && $filter['id_issue']) {
                $condition[] = "ti.id_issue LIKE '%{$filter['id_issue']}%'";
            }
            if (isset($filter['id_order']) && $filter['id_order']) {
                $condition[] = "oc.id_order LIKE '%{$filter['id_order']}%'";
            }
            if (isset($filter['service']) && $filter['service']) {
                $condition[] = "c2.id_carrier = {$filter['service']}";
            }
            if (isset($filter['customer']) && $filter['customer']) {
                $condition[] = "CONCAT(cu.firstname,' ',cu.lastname) LIKE '%{$filter['customer']}%'";
            }
            if (isset($filter['issue_type']) && $filter['issue_type']) {
                $condition[] = "ti.issue_type = {$filter['issue_type']}";
            }
            if (isset($filter['status']) && $filter['status'] != '' && $filter['status'] != 'show_all') {
                $condition[] = "tis.status = {$filter['status']}";
            }
        }
        if (count($condition)) {
            $filter_where = implode(' AND ', $condition);
        }
        return $filter_where;
    }
    public static function getIssueByShipment($id_order_carrier)
    {
        $lang = 'en';
        $context = Context::getContext();
        $iso_code = $context->language->iso_code;
        $sql ="SELECT *
        FROM `"._DB_PREFIX_."hl_tracking_issue` di
        WHERE di.id_order_carrier = {$id_order_carrier}";
        $issue = Db::getInstance()->getRow($sql);
        $path_issue = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/xml/issues.xml';
        $xml_issue = json_decode(
            json_encode(simplexml_load_file($path_issue, 'SimpleXMLElement', LIBXML_NOCDATA)),
            true
        );
        if ($issue) {
            $issue['issue'] = '';
            foreach ($xml_issue['issues']['issue'] as $i) {
                if ($issue['issue_type'] == $i['id_issue']) {
                    if (isset($i[$iso_code])) {
                        $issue['issue'] = $i[$iso_code];
                    } else {
                        $issue['issue'] = $i[$lang];
                    }
                    break;
                }
            }
        }

        return $issue;
    }
    public static function getIssueById($id_issue)
    {
        $lang = 'en';
        $context = Context::getContext();
        $iso_code = $context->language->iso_code;
        $sql ="SELECT *
        FROM `"._DB_PREFIX_."hl_tracking_issue` di
        WHERE di.id_issue = {$id_issue}";
        $issue = Db::getInstance()->getRow($sql);
        $path_issue = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/xml/issues.xml';
        $xml_issue = json_decode(
            json_encode(simplexml_load_file($path_issue, 'SimpleXMLElement', LIBXML_NOCDATA)),
            true
        );
        if ($issue) {
            $issue['issue'] = '';
            foreach ($xml_issue['issues']['issue'] as $i) {
                if ($issue['issue_type'] == $i['id_issue']) {
                    if (isset($i[$iso_code])) {
                        $issue['issue'] = $i[$iso_code];
                    } else {
                        $issue['issue'] = $i[$lang];
                    }
                    break;
                }
            }
        }

        return $issue;
    }
    public function listIssue($page = 0, $filter = null)
    {
        $itemsPerPage = 20;
        $offset = $page*$itemsPerPage;
        $where = $this->getIssueFilterCondition($filter);

        $sql ="SELECT ti.id_issue, ti.issue_type, tis.status as id_status ,tis.date as issue_date,
        CONCAT(c2.id_carrier,'_',c2.name) as service, c2.id_carrier, oc.tracking_number, oc.id_order,
        CONCAT(cu.firstname,' ',cu.lastname) AS customer, tcs.id_status as event_code, tcs.date
        FROM `"._DB_PREFIX_."hl_tracking_issue` ti
        LEFT JOIN "._DB_PREFIX_."order_carrier oc ON ti.id_order_carrier=oc.id_order_carrier
        LEFT JOIN "._DB_PREFIX_."carrier c ON oc.id_carrier=c.id_carrier
        LEFT JOIN "._DB_PREFIX_."carrier c2 ON c.id_reference=c2.id_reference AND c2.deleted=0
        LEFT JOIN "._DB_PREFIX_."orders o ON oc.id_order=o.id_order
        LEFT JOIN "._DB_PREFIX_."customer cu ON o.id_customer=cu.id_customer
        LEFT JOIN "._DB_PREFIX_."hl_tracking_current_status tcs ON ti.id_order_carrier=tcs.id_order_carrier
        LEFT JOIN "._DB_PREFIX_."hl_tracking_issue_status tis ON ti.id_issue=tis.id_issue".
        ($where?' WHERE '.$where:'');
        $limit = " LIMIT ".$offset.', '.$itemsPerPage;
        $orders = Db::getInstance()->executeS($sql.$limit);
        $sql = "SELECT COUNT(*) FROM "._DB_PREFIX_."hl_tracking_issue";
        $total = Db::getInstance()->getValue($sql);

        $path_issue = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/xml/issues.xml';
        $xml_issue = json_decode(
            json_encode(simplexml_load_file($path_issue, 'SimpleXMLElement', LIBXML_NOCDATA)),
            true
        );
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $lang = 'en';
        $context = Context::getContext();
        $iso_code = $context->language->iso_code;
        foreach ($orders as $key => $order) {
            foreach ($xml_issue['issues']['issue'] as $issue) {
                if ($order['issue_type'] == $issue['id_issue']) {
                    if (isset($issue[$iso_code])) {
                        $orders[$key]['issue'] = $issue[$iso_code];
                    } else {
                        $orders[$key]['issue'] = $issue[$lang];
                    }
                    break;
                }
            }
            foreach ($xml_issue['statuses']['status'] as $status) {
                if ($order['id_status'] == $status['id_status']) {
                    if (isset($status[$iso_code])) {
                        $orders[$key]['status'] = $status[$iso_code];
                    } else {
                        $orders[$key]['status'] = $status[$lang];
                    }
                    break;
                }
            }
            $event = $order['event_code'];
            $iso_code = '_';
            foreach ($webxml_crr->status as $step) {
                if ($event == $step->id_status) {
                    $iso_code = Tools::strtoupper($context->language->iso_code);
                    if (!isset($step->$iso_code)) {
                        $iso_code = $lang;
                    }
                    $iso_code = $step->id_status.'_'.$step->$iso_code;
                    break;
                }
            }
            $orders[$key]['event_code'] = $iso_code ? $iso_code : $event;
            $orders[$key]['code'] = $step->id_status;
            $orders[$key]['event_date'] = $order['date'];
            if ($step->id_status == 999) {
                $orders[$key]['step_date'] = '';
            } else {
                $orders[$key]['step_date'] = date(
                    $context->language->date_format_full,
                    strtotime($order['date'])
                );
            }
        }
        return array(
            'orders' => $orders,
            'total' => $total,
        );
    }
    public function calculateBrightNess($hex)
    {
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        $brightness = ($r*299 + $g*587 + $b*114)/1000;
        return $brightness;
    }
    public function getDefaultStatus($webxml_crr)
    {
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        $defaultStatus = array_map(function($s) {
            return $s->id_status;
        }, $statuses);
        $defaultStatus = array_filter($defaultStatus, function($s) {
            return $s != 1;
        });
        return $defaultStatus;
    }
    public function getOverview($filter)
    {
        $context = Context::getContext();
        $shopContext = $context->cookie->shopContext;
        $id_shop = (int)$this->getIdShop($shopContext, $context->shop->id);
        $dateqr = '';
        $cqr = '';
        $sqr = '';
        $oqr = '';
        if (!empty($filter['id_order_carrier'])) {
            $oqr = "oc.id_order = {$filter['id_order_carrier']}";
        } else {
            if (isset($filter['date'])) {
                $dateqr = $this->getDateQuery($filter['date'], 'oc.date_add');
            }
            if (isset($filter['carrier']) && !empty($filter['carrier'])) {
                $cqr = "c.id_reference = {$filter['carrier']}";
            }
            if (isset($filter['status']) && $filter['status'] != '') {
                $sqr = $this->toStatusCond($filter, 'tcs.id_status');
            }
        }
        $carrier_exclude = pSQL(Configuration::get('HL_TRACKING_CARRIER_EXCLUDE'));
        $status_exclude = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_EXCLUDE'));
        $sql = 'SELECT COUNT(oc.id_order_carrier) as total, (COUNT(oc.id_order_carrier)-COUNT(tcs.id_order_carrier)) AS "without_tracking"
		FROM '._DB_PREFIX_.'order_carrier oc
        LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
        LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
		INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state
		LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier=tcs.id_order_carrier
        WHERE o.id_shop='.$id_shop.' AND os.shipped=1'.
        ($oqr?' AND '.$oqr:'').
        ($sqr?' AND '.$sqr:'').
        ($cqr?' AND '.$cqr:'').
        ($dateqr?' AND '.$dateqr:'').
        ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').
        ($carrier_exclude?' AND c.id_reference NOT IN ('.$carrier_exclude.')':'');
        $rs = Db::getInstance()->getRow($sql);
        $total = $rs['total'];
        $without_tracking = $rs['without_tracking'];
        return array(
            'total' => $total,
            'without_tracking' => $without_tracking,
            'time' => $this->getTime($filter)
        );
    }
    public function getDeliveryStatus($filter)
    {
        $module = Module::getInstanceByName('deliveryorderautoupdate');
        $dateqr2 = '';
        $cqr = '';
        $oqr = '';
        if (!empty($filter['id_order_carrier'])) {
            $oqr = "oc.id_order = {$filter['id_order_carrier']}";
        } else {
            if (isset($filter['date'])) {
                $dateqr2 = $this->getDateQuery($filter['date'], 'oc.date_add');
            }
            if (isset($filter['carrier']) && !empty($filter['carrier'])) {
                $cqr = "c.id_reference = {$filter['carrier']}";
            }
        }
        $carrier_exclude = pSQL(Configuration::get('HL_TRACKING_CARRIER_EXCLUDE'));
        $status_exclude = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_EXCLUDE'));
        $sql = 'SELECT COUNT(oc.id_order_carrier) AS shipments, "1" AS id_status
        FROM '._DB_PREFIX_.'order_carrier oc
        LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier=tcs.id_order_carrier
        INNER JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
        INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state'
        .($dateqr2?' WHERE os.shipped=1 AND '.$dateqr2:'').
        ' AND tcs.id_status = 1'.
        ($cqr?' AND '.$cqr:'').
        ($oqr?' AND '.$oqr:'').
        ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').
        ($carrier_exclude?' AND c.id_reference NOT IN ('.$carrier_exclude.')':'').'
        GROUP BY id_status
        UNION
        SELECT COUNT(oc.id_order_carrier) AS shipments, "not_delivered" AS id_status
        FROM  '._DB_PREFIX_.'order_carrier oc
        LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier=tcs.id_order_carrier
        INNER JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
        INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state'
        .($dateqr2?' WHERE os.shipped=1 AND '.$dateqr2:'').
        ' AND (tcs.id_status NOT IN(1,5) OR tcs.id_status is null)'.
        ($cqr?' AND '.$cqr:'').
        ($oqr?' AND '.$oqr:'').
        ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').
        ($carrier_exclude?' AND c.id_reference NOT IN ('.$carrier_exclude.')':'').'
        UNION
        SELECT COUNT(oc.id_order_carrier) AS shipments, tcs.id_status AS id_status
        FROM  '._DB_PREFIX_.'order_carrier oc
        LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier=tcs.id_order_carrier
        INNER JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
        INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state'
        .($dateqr2?' WHERE os.shipped=1 AND '.$dateqr2:'').
        ($cqr?' AND '.$cqr:'').
        ($oqr?' AND '.$oqr:'').
        ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').
        ($carrier_exclude?' AND c.id_reference NOT IN ('.$carrier_exclude.')':'').'
        AND tcs.id_status<>1
        GROUP BY id_status
		UNION
        SELECT COUNT(oc.id_order_carrier) AS shipments, "999" AS id_status
        FROM  '._DB_PREFIX_.'order_carrier oc
        LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier=tcs.id_order_carrier
        INNER JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
        INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state'
        .($dateqr2?' WHERE os.shipped=1 AND '.$dateqr2:'').
        ($cqr?' AND '.$cqr:'').
        ($oqr?' AND '.$oqr:'').
        ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').
        ($carrier_exclude?' AND c.id_reference NOT IN ('.$carrier_exclude.')':'').'
        AND tcs.id_status is null';
        $details = Db::getInstance()->executeS($sql);
        $context = Context::getContext();
        $iso_code = Tools::strtoupper($context->language->iso_code);
        $webxml_crr = self::getStatusXML();
        $statuses = $this->getDefaultStatus($webxml_crr);
        foreach ($details as &$detail) {
            foreach ($webxml_crr->status as $step) {
                if (is_null($detail['id_status'])) {
                    $detail['shipments'] = $without_tracking;
                    $detail['color'] = '#000';
                    $detail['label'] = $module->label['not_track'];
                    continue;
                }
                if ($detail['id_status'] == 'not_delivered') {
                    $detail['color'] = '#000';
                    $detail['label'] = $module->label['not_delivered'];
                    continue;
                }
                if ($detail['id_status'] == $step->id_status) {
                    if (!isset($step->{$iso_code})) {
                        $iso_code = 'EN';
                    }
                    $detail['color'] = $step->color;
                    $detail['label'] = $step->{$iso_code};
                    break;
                }
            }
        }
        return $details;
    }
    public function getTime($filter)
    {
        $dateqr = '';
        if (isset($filter['date'])) {
            $dateqr = $this->getDateQuery($filter['date'], 'oc.date_add');
        }
        $sql = 'SELECT FLOOR(SUM(HOUR(TIMEDIFF(oc.date_add,tcs.date))) / 24), CONCAT(FLOOR(SUM(HOUR(TIMEDIFF(oc.date_add,tcs.date))) / 24), " days ",MOD(SUM(HOUR(TIMEDIFF(oc.date_add,tcs.date))), 24), " hours ",SUM(MINUTE(TIMEDIFF(oc.date_add,tcs.date))), " min " ) AS transit_time,
        CONCAT(FLOOR(SUM(HOUR(TIMEDIFF(o.date_add,oc.date_add))) / 24), " DAYS ",MOD(SUM(HOUR(TIMEDIFF(o.date_add,oc.date_add))), 24), " hours ",SUM(MINUTE(TIMEDIFF(o.date_add,oc.date_add))), " min " ) AS preparation_time,
        CONCAT(FLOOR(SUM(HOUR(TIMEDIFF(o.date_add,tcs.date))) / 24), " DAYS ",MOD(SUM(HOUR(TIMEDIFF(o.date_add,tcs.date))), 24), " hours ",SUM(MINUTE(TIMEDIFF(o.date_add,tcs.date))), " min " ) AS total_time
        FROM '._DB_PREFIX_.'order_carrier oc
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier=tcs.id_order_carrier
        LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
        INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state
        INNER JOIN '._DB_PREFIX_.'configuration conf ON conf.name LIKE "DELIVERY_ORDER_STATUS_FROM"
        WHERE os.shipped=1'
        .($dateqr?' AND '.$dateqr:'');
        $time = Db::getInstance()->getRow($sql);
        foreach ($time as &$value) {
            $value = $this->toDelayTime(floor((float)$value));
        }
        return $time;
    }
    public function getCarrierStat($filter)
    {
        $dateqr = '';
        $sqr = '';
        $oqr = '';
        if (!empty($filter['id_order_carrier'])) {
            $oqr = "oc.id_order = {$filter['id_order_carrier']}";
        } else {
            if (isset($filter['date'])) {
                $dateqr = $this->getDateQuery($filter['date'], 'oc.date_add');
            }
            if (isset($filter['status']) && $filter['status'] != '') {
                $sqr = $this->toStatusCond($filter, 'cs.id_status');
            }
        }
        $carrier_exclude = Configuration::get('HL_TRACKING_CARRIER_EXCLUDE');
		$status_exclude = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_EXCLUDE'));
        $sql = 'SELECT COUNT(oc.id_order_carrier) AS shipments, c2.id_reference, c2.name
        FROM  '._DB_PREFIX_.'order_carrier oc
		LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
		INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state
        LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
        LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference=c2.id_reference AND c2.deleted=0
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status cs ON oc.id_order_carrier=cs.id_order_carrier
        WHERE os.shipped=1'.
        ($oqr?' AND '.$oqr:'').
        ($sqr?' AND '.$sqr:'').
        ($dateqr?' AND '.$dateqr:'').
        ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').
        ($carrier_exclude?' AND c2.id_reference NOT IN ('.$carrier_exclude.')':'').'
        GROUP BY c2.id_reference
        ORDER BY shipments DESC';
        $carriers = Db::getInstance()->executeS($sql);
        return $carriers;
    }
    public function listOrder($page = 0, $filter = null, $lang = 'EN')
    {
        $url = self::getUrl();
        $module = Module::getInstanceByName('deliveryorderautoupdate');
        $trackingmodel = new TrackingModel();
        $itemsPerPage = 100;
        $offset = $page*$itemsPerPage;
        $carrier_exclude = pSQL(Configuration::get('HL_TRACKING_CARRIER_EXCLUDE'));
        $status_exclude = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_EXCLUDE'));
        $context = Context::getContext();
        $shopContext = $context->cookie->shopContext;
        $id_shop = (int)$this->getIdShop($shopContext, $context->shop->id);
        $date_ = pSQL(Configuration::get('DELIVERY_ORDER_DATE'));
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $filter_where = $this->getFilterCondition($filter);
        $filter_order = $this->getSortFilter($filter);
        if (isset($filter['id_order_carrier']) && $filter['id_order_carrier']) {
            $where = ($id_shop ? 'o.id_shop IN ('.(int)$id_shop.') AND ' : '')."oc.id_order={$filter['id_order_carrier']}";
        } else {
            $where = ($id_shop ? 'o.id_shop IN ('.(int)$id_shop.') AND ' : '').' os.shipped=1'.
            ($carrier_exclude?' AND c2.id_reference NOT IN ('.$carrier_exclude.')':'').
            ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').
            ' '.$filter_where.' '.
            $filter_order.'
            LIMIT '.$offset.', '.$itemsPerPage;
        }
        $sql = 'SELECT oc.id_order, oc.id_order_carrier,
        oc.tracking_number, c2.active, hlc.name,
        hlc.id, hlc.method, c2.url as tracking_url, o.reference, cus.firstname,
        cus.lastname, c2.name as carrier_name, oc.id_carrier as id_carrier,
        c2.id_reference as carrier_reference, CASE WHEN tcs.id_status is null THEN "999" ELSE tcs.id_status END AS event_code,
        tcs.date, tdis.id_order_carrier as disable,
        IF (
            tcs.date is not null,
            TIMESTAMPDIFF(MINUTE, oc.date_add, tcs.date),
            IF (
                tcs.date is not null,
                TIMESTAMPDIFF(MINUTE, oc.date_add, tcs.date),
                TIMESTAMPDIFF(MINUTE, oc.date_add, NOW())
            )
        ) as transit_time, ti.id_issue
        FROM '._DB_PREFIX_.'order_carrier oc
        INNER JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
		INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_disable tdis ON oc.id_order_carrier = tdis.id_order_carrier
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_issue ti ON oc.id_order_carrier = ti.id_order_carrier
        LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
        LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference = c2.id_reference AND c2.deleted=0
        LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c2.id_reference=hlcm.id_carrier_ps
        LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
        LEFT JOIN '._DB_PREFIX_.'customer cus ON o.id_customer = cus.id_customer
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier = tcs.id_order_carrier
        WHERE '.$where;
        $tracking_number = Db::getInstance()->executeS($sql);
        $orders = array();
        foreach ($tracking_number as $key => $number) {
            $orders[$key] = $number;
            if ($number['method'] == 0) {
                // $event = $this->getEvent($number['id_order_carrier']);
                $event = $number['event_code'];
                $iso_code = '_';
                foreach ($webxml_crr->status as $step) {
                    if ($event == $step->id_status) {
                        $iso_code = Tools::strtoupper($context->language->iso_code);
                        if (!isset($step->$iso_code)) {
                            $iso_code = $lang;
                        }
                        $iso_code = $step->id_status.'_'.$step->$iso_code;
                        break;
                    }
                }
                $orders[$key]['event_code'] = $iso_code ? $iso_code : $event;
                $orders[$key]['code'] = $step->id_status;
                $orders[$key]['event_date'] = $number['date'];
                if ($step->id_status == 999) {
                    $orders[$key]['step_date'] = '';
                } else {
                    $orders[$key]['step_date'] = date(
                        $context->language->date_format_full,
                        strtotime($number['date'])
                    );
                }
            }

            $check_email_sent = array();
            $orders[$key]['last_result'] = '';//$event_code['carrier_response'];
            $orders[$key]['success_response'] = '';//$event_code['success_response'];
            $orders[$key]['email_sent'] = count($check_email_sent);
            $id_order = Db::getInstance()->getRow(
                "SELECT o.id_order, o.reference, osl.name as current_state_name, o.current_state, os.color
                FROM "._DB_PREFIX_."orders o
                LEFT JOIN "._DB_PREFIX_."order_state_lang osl ON o.current_state = osl.id_order_state
                AND osl.id_lang=".(int)$context->language->id."
                LEFT JOIN "._DB_PREFIX_."order_state os ON o.current_state = os.id_order_state
                WHERE o.id_order=".(int)$number['id_order']
            );

            $orders[$key]['current_state'] = $id_order['current_state_name'];
            $orders[$key]['current_state_id'] = $id_order['current_state'];
            $orders[$key]['current_state_color'] = $id_order['color'];
            $orders[$key]['carrier'] = $number['name'];
            $orders[$key]['track_number'] = $number['tracking_number'];
            $orders[$key]['json_carrier'] = $url.'modules/deliveryorderautoupdate/webservices/'
            .$number['id'].'/Carrier'.$number['id'].'.php?shipment_ref='
            .$number['id_order_carrier'].'&parcel_number='.
            $trackingmodel->handleTrackingNumber($number['tracking_number'])
            .'&order_ref='.$number['reference'].'&id_shop='.$id_shop;
            // $orders[$key]['json_server'] = $url.'modules/deliveryorderautoupdate/webservices/'
            // .$number['id'].'/Carrier'
            // .$number['id'].'.php?shipment_ref='
            // .$number['id_order_carrier'].'&parcel_number='
            // .$trackingmodel->handleTrackingNumber($number['tracking_number'])
            // .'&token='.Configuration::get('DELIVERY_TOKEN')
            // .'&order_ref='
            // .$number['reference'].'&id_shop='.$id_shop.'&devmode=1';
            $orders[$key]['json_server'] = $context->link->getAdminLink('AdmindeliveryorderautoupdateAjax').'&ajax=1&action=viewResponse&shipment_ref='.$number['id_order_carrier'];
            if (strpos($number['tracking_url'], '@') !== false) {
                $orders[$key]['tracking_url'] = str_replace('@', $number['tracking_number'], $number['tracking_url']);
            } elseif ($number['tracking_url']) {
                $orders[$key]['tracking_url'] = $number['tracking_url'].$number['tracking_number'];
            } else {
                $orders[$key]['tracking_url'] = null;
            }
            $orders[$key]['transit_time'] = $this->toDelayTime($number['transit_time']);
            if ($module->v17) {
                $orders[$key]['url'] = $this->context->link->getAdminLink(
                    'AdminOrders',
                    true,
                    [],
                    array('id_order' => $number['id_order'], 'vieworder' => 1)
                );
            } else {
                $orders[$key]['url'] = Dispatcher::getInstance()->createUrl(
                    'AdminOrders',
                    $this->context->language->id,
                    array(
                        'token' => Tools::getAdminTokenLite('AdminOrders'),
                        'id_order' => $number['id_order'],
                        'vieworder' => 1,
                    ),
                    false
                );
            }
        }
        return $orders;
    }
    public function getTotalOrder($filter = null)
    {
        $carrier_exclude = pSQL(Configuration::get('HL_TRACKING_CARRIER_EXCLUDE'));
        $status_exclude = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_EXCLUDE'));
        $context = Context::getContext();
        $shopContext = $context->cookie->shopContext;
        $id_shop = (int)$this->getIdShop($shopContext, $context->shop->id);
        $date_ = pSQL(Configuration::get('DELIVERY_ORDER_DATE'));
        $filter_where = $this->getFilterCondition($filter);
        $filter_order = $this->getSortFilter($filter);

        if (isset($filter['id_order_carrier']) && $filter['id_order_carrier']) {
            $where = ($id_shop ? 'o.id_shop IN ('.(int)$id_shop.') AND ' : '')."oc.id_order={$filter['id_order_carrier']}";
        } else {
            $where = ($id_shop ? 'o.id_shop IN ('.(int)$id_shop.') AND ' : '').' os.shipped=1'.
            ($carrier_exclude?' AND c2.id_reference NOT IN ('.$carrier_exclude.')':'').
            ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').
            ' '.$filter_where.' '.
            $filter_order;
        }
        $sql = 'SELECT oc.id_order, oc.id_order_carrier,
        oc.tracking_number, c2.active, hlc.name,
        hlc.id, hlc.method, c2.url as tracking_url, o.reference, cus.firstname,
        cus.lastname, c2.name as carrier_name, oc.id_carrier as id_carrier,
        c2.id_reference as carrier_reference, CASE WHEN tcs.id_status is null THEN "999" ELSE tcs.id_status END AS event_code,
        tcs.date, tdis.id_order_carrier as disable,
        IF (tcs.date is not null,
			TIMESTAMPDIFF(MINUTE, oc.date_add, tcs.date),
			TIMESTAMPDIFF(MINUTE, oc.date_add, NOW())
        ) as transit_time
        FROM '._DB_PREFIX_.'order_carrier oc
        INNER JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
        INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_disable tdis ON oc.id_order_carrier = tdis.id_order_carrier
        LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
        LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference = c2.id_reference AND c2.deleted=0
        LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c2.id_reference=hlcm.id_carrier_ps
        LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
        LEFT JOIN '._DB_PREFIX_.'customer cus ON o.id_customer = cus.id_customer
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier = tcs.id_order_carrier
        WHERE '.$where;
        $total = Db::getInstance()->executeS($sql);
        return count($total);
    }
    public function toDelayTime($delayMinute)
    {
        $str = '';
        if ($delayMinute >= 60) {
            $hour = floor($delayMinute/60);
            $minute = $delayMinute%60;
            if ($hour > 24) {
                $day = floor($hour/24);
                $hour = $hour%24;
            } else {
                $day = 0;
            }
            $str = ($day?$day:'')."d {$hour}h {$minute}m";
        } else {
            $str = "{$delayMinute}m";
        }
        return $str;
    }
    public function getTimeAvg($orders, $total = null, $count = null)
    {
        $avg = array(
            'preparation' => 0,
            'transit' => 0,
            'total' => 0,
        );
        if ($total == null) {
            $total = array(
                'preparation' => 0,
                'transit' => 0,
                'total' => 0,
            );
        }
        if ($count == null) {
            $count = array(
                'preparation' => 0,
                'transit' => 0,
                'total' => 0,
            );
        }
        foreach ($orders as $order) {
            if ($order['preparation'] > 0) {
                $total['preparation'] += $order['preparation'];
                $count['preparation']++;
            }
            if ($order['transit_time'] > 0) {
                $total['transit'] += $order['transit_time'];
                $count['transit']++;
            }
            if ($order['delay'] > 0) {
                $total['total'] += $order['delay'];
                $count['total']++;
            }
        }
        foreach ($total as $key => $value) {
            if ($count[$key]) {
                $avg[$key] = $this->toDelayTime(floor($value/$count[$key]));
            }
        }
        return array(
            'total' => $total,
            'count' => $count,
            'avg' => $avg
        );
    }
    public static function getEmailListByIdOrderCarrier($id_order_carrier)
    {
        $prefix = _DB_PREFIX_;
        return Db::getInstance()->executeS(
            "SELECT e.id, oc.id_order, e.id_tracking_history, e.date_sent, e.email_status,
            h.step_date, h.hl_carrier, e.shipping_status, c.firstname, c.lastname
            FROM {$prefix}hl_tracking_email e
            LEFT JOIN {$prefix}order_carrier oc ON e.id_order_carrier = oc.id_order_carrier
            LEFT JOIN {$prefix}orders o ON oc.id_order = o.id_order
            LEFT JOIN {$prefix}customer c ON o.id_customer = c.id_customer
            LEFT JOIN {$prefix}hl_tracking_history h ON e.id_tracking_history = h.id
            WHERE e.id_order_carrier = {$id_order_carrier}
            ORDER BY e.id DESC"
        );
    }
    public static function displayDate($time, $locale)
    {
        $date = '';
        switch ($locale) {
            case 'en_US':
                $date = date('jS F Y', $time);
                break;
            default:
                $format = new IntlDateFormatter($locale, IntlDateFormatter::NONE,
                  IntlDateFormatter::NONE, NULL, NULL, "dd MMMM yyyy");
                $date = datefmt_format($format, $time);
                // $date = strftime('%e %B %G', $time);
                break;
        }
        return $date;
    }
    public static function getIssueHistory($id_issue)
    {
        $context = Context::getContext();
        $sql = "SELECT * FROM "._DB_PREFIX_."hl_tracking_issue_status WHERE id_issue = {$id_issue} ORDER BY date DESC";
        $histories = Db::getInstance()->executeS($sql);
        $path_issue = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/xml/issues.xml';
        $xml_issue = json_decode(
            json_encode(simplexml_load_file($path_issue, 'SimpleXMLElement', LIBXML_NOCDATA)),
            true
        );
        $iso_code = $context->language->iso_code;
        foreach ($histories as $key => $history) {
            foreach ($xml_issue['statuses']['status'] as $status) {
                if ($status['id_status'] == $history['status']) {
                    $histories[$key]['status_name'] = isset($status[$iso_code])?$status[$iso_code]:$status['en'];
                    break;
                }
            }
        }
        return $histories;
    }
    public static function getShipmentById($id_order_carrier)
    {
        $shipment = Db::getInstance()->getRow(
            'SELECT oc.id_order_carrier, oc.tracking_number as track_number, c.name as carrier, c.id_carrier,
            c.id_reference, hlc.id as id_connector, hlc.name as connector, o.reference, c.url as tracking_url,
            oc.date_add, tcs.id_status as event_code, tcs.date, tdis.id_order_carrier as disable, hlc.id,
            COUNT(di.id_issue) as issue
            FROM `'._DB_PREFIX_.'order_carrier` oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order = o.id_order
            LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
            LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON hlcm.id_carrier_ps=c.id_reference
            LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc ON hlcm.id_carrier_hl=hlc.id
            LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier = tcs.id_order_carrier
            LEFT JOIN '._DB_PREFIX_.'hl_tracking_disable tdis ON oc.id_order_carrier = tdis.id_order_carrier
            LEFT JOIN '._DB_PREFIX_.'hl_tracking_issue di ON oc.id_order_carrier = di.id_order_carrier
            WHERE oc.id_order_carrier='.$id_order_carrier
        );
        $context = Context::getContext();
        $trackingmodel = new TrackingModel();
        $shopContext = $context->cookie->shopContext;
        $id_shop = (new self)->getIdShop($shopContext, $context->shop->id);
        $event = $shipment['event_code'];
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $iso_code = 'EN';
        $webxml_crr = self::getStatusXML();
        foreach ($webxml_crr->status as $step) {
            if ($event == $step->id_status) {
                $iso_code = Tools::strtoupper($context->language->iso_code);
                if (!isset($step->{$iso_code})) {
                    $iso_code = $lang;
                }
                break;
            }
        }
        $shipment['event_code'] = $step->{$iso_code};
        $shipment['code'] = $step->id_status;
        $shipment['event_date'] = $shipment['date'];
        $shipment['step_date'] = date(
            $context->language->date_format_full,
            strtotime($shipment['date'])
        );
        $shipment['json_server'] = self::getUrl().'modules/deliveryorderautoupdate/webservices/'
        .$shipment['id'].'/Carrier'
        .$shipment['id'].'_sv_response.php?shipment_ref='
        .$shipment['id_order_carrier'].'&parcel_number='
        .$trackingmodel->handleTrackingNumber($shipment['track_number'])
        .'&token='.Configuration::get('DELIVERY_TOKEN')
        .'&order_ref='
        .$shipment['reference'].'&id_shop='.$id_shop;
        if (strpos($shipment['tracking_url'], '@') !== false) {
            $shipment['tracking_url'] = str_replace('@', $shipment['track_number'], $shipment['tracking_url']);
        } elseif ($shipment['tracking_url']) {
            $shipment['tracking_url'] = $shipment['tracking_url'].$shipment['track_number'];
        } else {
            $shipment['tracking_url'] = null;
        }
        return $shipment;
    }
    public static function getStatusXML()
    {
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        return $webxml_crr;
    }
    public static function convertDate($date)
    {
        $date_format = str_replace('d', 'DD', $date);
        $date_format = str_replace('m', 'MM', $date_format);
        $date_format = str_replace('Y', 'YYYY', $date_format);
        $date_format = str_replace('n', 'M', $date_format);
        $date_format = str_replace('j', 'D', $date_format);
        $date_format = str_replace('y', 'YY', $date_format);
        return $date_format;
    }
    public function updateCheck()
    {
        $lastCheck = Configuration::getGlobalValue('HL_TRACKING_LAST_STATUSCODE_CHECK');
        $version = Configuration::getGlobalValue('HL_TRACKING_STATUSCODE_VERSION');
        $today = date('Y-m-d');
        $module = Module::getInstanceByName('deliveryorderautoupdate');
        $rs = array('update' => false);
        $rs['url'] = $module->returnFrontUrl().'modules/deliveryorderautoupdate/status_update.php?token='
        .Configuration::get("DELIVERY_TOKEN");
        // if ($lastCheck != $today) {
            $xml = simplexml_load_file('https://helloshop.com/status_code.xml');
            $newversion = (string)$xml->database->attributes()->name;
            if ($version !== $newversion) {
                $rs['update'] = true;
                $rs['old'] = $version;
                $rs['new'] = $newversion;
            }
        // }
        return $rs;
    }
    public function getSliderValue($filter)
    {
        $dateqr = '';
        $cqr = '';
        $sqr = '';
        $oqr = '';
        if (!empty($filter['id_order_carrier'])) {
            $oqr = "oc.id_order = {$filter['id_order_carrier']}";
        } else {
            if (isset($filter['date'])) {
                $dateqr = $this->getDateQuery($filter['date'], 'oc.date_add');
            }
            if (isset($filter['carrier']) && !empty($filter['carrier'])) {
                $cqr = "c.id_reference = {$filter['carrier']}";
            }
            if (isset($filter['status']) && $filter['status'] != '') {
                $sqr = $this->toStatusCond($filter, 'tcs.id_status');
            }
        }
        $carrier_exclude = pSQL(Configuration::get('HL_TRACKING_CARRIER_EXCLUDE'));
        $status_exclude = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_EXCLUDE'));
        $sql = 'SELECT MIN(a.transit_time) as min, MAX(a.transit_time) as max
        FROM (SELECT DISTINCT IF(
                        tcs.date is not null,
                        DATEDIFF(tcs.date,oc.date_add),
                        DATEDIFF( current_date,oc.date_add)) AS transit_time
        FROM '._DB_PREFIX_.'order_carrier oc
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status tcs ON oc.id_order_carrier=tcs.id_order_carrier
        LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
        INNER JOIN '._DB_PREFIX_.'order_state os ON o.current_state=os.id_order_state
        WHERE os.shipped=1'.
        ($oqr?' AND '.$oqr:'').
        ($sqr?' AND '.$sqr:'').
        ($cqr?' AND '.$cqr:'').
        ($dateqr?' AND '.$dateqr:'').
        ($status_exclude?' AND o.current_state NOT IN ('.$status_exclude.')':'').'
        GROUP BY oc.id_order_carrier) as a';
        return Db::getInstance()->getRow($sql);
    }
}
