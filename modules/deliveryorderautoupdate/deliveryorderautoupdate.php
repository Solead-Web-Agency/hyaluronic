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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/classes/emailHelper.php');
require_once(dirname(__FILE__).'/classes/returns.php');
require_once(dirname(__FILE__).'/classes/trackingmodel.php');
class Deliveryorderautoupdate extends Module
{
    private $html = '';
    const DEFAULT_LANG = 'EN';
    public function __construct()
    {
        $this->name = 'deliveryorderautoupdate';
        $this->tab = 'front_office_features';
        $this->version = '2.6.5';
        $this->author = 'Helloshop';
        $this->secure_key = Tools::hash($this->name);
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->tracking_page = $this->l('Go To Tracking Page');
        $this->last_step = $this->l('Last Step');
        $this->url = EmailHelper::getUrl();
        $this->method = array("Button", "Robot", "Push", "Cron", "Force");

        parent::__construct();

        $this->displayName = $this->l('Tracking Center');
        $this->description = $this->l(
            'Tracks your parcels and updates order status automatically according shipping status.'
        );
        $this->module_key = '4cd44e72caa1f30e6d283a1acc652741';
        $this->v17 = preg_match_all('/^1.7/', _PS_VERSION_);
        $this->label = array(
            'not_track' => $this->l('Not tracked yet'),
            'not_delivered' => $this->l('Not delivered'),
        );
    }
    public function install()
    {
        if (parent::install() &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayOrderDetail')
        ) {
            include(dirname(__FILE__).'/install/install.php');
            $this->addMeta('module-deliveryorderautoupdate-orders');
            $install_demo = new InstallDemo();
            $install_demo->insertData();
            $this->addTab('Tracking Center', 14, 'AdmindeliveryorderautoupdateDashboard');
            $this->addTab('Tracking Ajax', -1, 'AdmindeliveryorderautoupdateAjax');
            Configuration::updateGlobalValue('HL_TC_INSTALL_DATE', date('Y-m-d H:i:s'));
            Configuration::updateGlobalValue("HL_TRACKING_EMAIL_SUBJECT", 'status');
            Configuration::updateGlobalValue("DELIVERY_ORDER_NOTIFI_EMAIL", '');
            Configuration::updateGlobalValue("DELIVERY_TOKEN", Tools::getAdminTokenLite('AdminModules'));
            Configuration::updateGlobalValue("HL_TRACKING_REPORT_ERROR", 1);
            Configuration::updateGlobalValue("order_show_status", 1);
            Configuration::updateGlobalValue("HL_ORDER_TRACKING_BLOCK", 1);
            Configuration::updateGlobalValue("HL_CUSTOMER_SHIPPING_STEP", 1);
            $today = date('Y-m-d');
            $month = strtotime(date("Y-m-d", strtotime($today)) . " - 30 day");
            $month = date("Y-m-d", $month);
            // $month = strftime("%Y-%m-%d", $month);
            Configuration::updateGlobalValue("DELIVERY_ORDER_DATE", $month);
            $this->installLangEmail();
            return true;
        }
        return false;
    }

    private function addMeta($page)
    {
        $theme_meta_value = array();
        $result = Db::getInstance()->getValue('SELECT * FROM '._DB_PREFIX_.'meta WHERE page="'.pSQL($page).'"');
        if ((int)$result > 0) {
            return true;
        }
        $_meta = new MetaCore();
        $_meta->page = $page;
        $_meta->configurable = 1;
        $langs = Language::getLanguages(false);
        foreach ($langs as $l) {
            $iso_code_lwr = Language::getIsoById($l['id_lang']);
            $_LANGMAIL = array();
            if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$iso_code_lwr.'/lang.php')) {
                include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$iso_code_lwr.'/lang.php');
            } else {
                include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/en/lang.php');
            }
            $_meta->title[$l['id_lang']] = $_LANGMAIL['title'];
            $_meta->url_rewrite[$l['id_lang']] = $_LANGMAIL['url_rewrite'];
        }

        $_meta->add();
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $themes = Theme::getThemes();

            if ((int)$_meta->id > 0) {
                foreach ($themes as $theme) {
                    $theme_meta_value[] = array(
                        'id_theme' => (int)$theme->id,
                        'id_meta' => (int)$_meta->id,
                        'left_column' => (int)$theme->default_left_column,
                        'right_column' => (int)$theme->default_right_column
                    );
                }
                if (count($theme_meta_value) > 0) {
                    return Db::getInstance()->insert('theme_meta', $theme_meta_value);
                }
            } else {
                return false;
            }
        }
        return true;
    }
    public function uninstall()
    {
        if (parent::uninstall()) {
            $this->removeTab('AdmindeliveryorderautoupdateDashboard');
            $this->removeTab('AdmindeliveryorderautoupdateAjax');
            $this->deleteMeta('module-deliveryorderautoupdate-orders');
            $this->deleteTables();
            Configuration::deleteByName('DELIVERY_ORDER_DATE');
            Configuration::deleteByName('DELIVERY_ORDER_STATUS_FROM');
            Configuration::deleteByName('DELIVERY_ORDER_CRON');
            Configuration::deleteByName('DELIVERY_ORDER_NOTIFI_EMAIL');
            Configuration::deleteByName('DELIVERY_TOKEN');
            Configuration::deleteByName('DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS');
            Configuration::deleteByName('DELIVERY_EVENT_CODE_MAIL_ADMIN');
            Configuration::deleteByName('HL_TRACKING_EMAIL_SUBJECT');
            Configuration::deleteByName('updatestatusversion');
            return true;
        }
        return false;
    }
    public function installLangEmail()
    {
        $backups = glob(dirname(__FILE__).'/mails/*/lang.txt');
        if (($backups) && (is_array($backups))) {
            foreach ($backups as $item) {
                $handle = fopen($item, "r");
                $contents = fread($handle, filesize($item));
                fclose($handle);
                $link_mail = explode('deliveryorderautoupdate', $item);
                $url_mail = glob(str_replace('.txt', '.php', _PS_ROOT_DIR_.'/themes/*/'.$link_mail[1]));
                foreach ($url_mail as $url) {
                    if (file_exists($url)) {
                        $Filedata = Tools::file_get_contents($url);
                        $data_source =  htmlentities($Filedata);
                        $position = (strrpos($data_source, '$_LANGMAIL['));
                        $str = Tools::substr($data_source, 0, $position) . $contents
                        . Tools::substr($data_source, $position);
                        $handle = fopen($url, "w");
                        $str = str_replace('&lt;', '<', $str);
                        $str = str_replace('&amp;gt;', '>', $str);
                        $str = str_replace('&gt;', '>', $str);
                        fwrite($handle, $str);
                        fclose($handle);
                    }
                }
            }
        }
    }
    private function addTab($title, $parent_id = 10, $class = 0)
    {
        @copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'t/'.$class.'.gif');
        $_tab = new Tab();
        $id_parent = Db::getInstance()->getRow(
            "SELECT id_parent FROM "._DB_PREFIX_."tab WHERE class_name like 'AdminOrders'"
        );
        $_tab->class_name = $class;
        $_tab->module = $this->name;
        $_tab->id_parent = $id_parent['id_parent'];
        $_tab->position = 12;
        $langs = Language::getLanguages(false);
        foreach ($langs as $l) {
            $_tab->name[$l['id_lang']] = $title;
        }
        if ($parent_id == -1) {
            $_tab->id_parent = -1;
            $_tab->add();
        } else {
            $_tab->add(true, false);
        }
        return $_tab->id;
    }
    private function deleteMeta($page)
    {
        $id = Db::getInstance()->getValue("SELECT id_meta FROM "._DB_PREFIX_."meta WHERE page='{$page}'");
        if ($id) {
            $meta = new Meta($id);
            $meta->delete();
        }
    }
    private function removeTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();
            return true;
        }
        return false;
    }
    public function getContent()
    {
        $this->html = null;
        if (Tools::isSubmit('submitItem')) {
            $notifi_email = Tools::getValue("notifi_email");
            $event_code_mail = (
                Tools::getValue('event_code_mail') ? implode(',', Tools::getValue('event_code_mail')) : ''
            );
            $DELIVERY_EVENT_CODE_MAIL_ADMIN = (
                Tools::getValue('DELIVERY_EVENT_CODE_MAIL_ADMIN') ?
                implode(',', Tools::getValue('DELIVERY_EVENT_CODE_MAIL_ADMIN')) : ''
            );
            $DELIVERY_ORDER_STATUS_TO = Tools::getValue('DELIVERY_ORDER_STATUS_TO');
            Configuration::updateValue('DELIVERY_ORDER_STATUS_TO', json_Encode($DELIVERY_ORDER_STATUS_TO));
            $status_from = (Tools::getValue('status_from') ? implode(',', Tools::getValue('status_from')) : '');
            $status_exclude = (Tools::getValue('status_exclude') ?
            implode(',', Tools::getValue('status_exclude')) : '');
            $carrier_exclude = (Tools::getValue('carrier_exclude') ?
            implode(',', Tools::getValue('carrier_exclude')) : '');
            Configuration::updateValue("order_show_status", Tools::getValue("order_show_status"));
            Configuration::updateValue("HL_TRACKING_REPORT_ERROR", Tools::getValue("HL_TRACKING_REPORT_ERROR"));
            Configuration::updateValue("DELIVERY_ORDER_DATE", Tools::getValue('date_requestorder'));
            Configuration::updateValue("DELIVERY_ORDER_STATUS_FROM", $status_from);
            Configuration::updateValue("DELIVERY_ORDER_STATUS_EXCLUDE", $status_exclude);
            Configuration::updateValue("HL_TRACKING_CARRIER_EXCLUDE", $carrier_exclude);
            Configuration::updateValue("DELIVERY_EVENT_CODE_MAIL", $event_code_mail);
            Configuration::updateValue("DELIVERY_EVENT_CODE_MAIL_ADMIN", $DELIVERY_EVENT_CODE_MAIL_ADMIN);
            Configuration::updateValue(
                "DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS",
                Tools::getValue('DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS')
            );
            Configuration::updateValue(
                "DELIVERY_ORDER_NOTIFI_EMAIL",
                ($notifi_email ? implode(",", $notifi_email) : null)
            );
            Configuration::updateValue("DELIVERY_ORDER_CRON", Tools::getValue('cron_url'));
            Configuration::updateValue("HL_TRACKING_CRON_RESPONSE", Tools::getValue('HL_TRACKING_CRON_RESPONSE'));
            Configuration::updateValue("HL_ORDER_TRACKING_BLOCK", Tools::getValue('HL_ORDER_TRACKING_BLOCK'));
            Configuration::updateValue("HL_CUSTOMER_SHIPPING_STEP", Tools::getValue('HL_CUSTOMER_SHIPPING_STEP'));
            Configuration::updateValue("HL_TRACKING_STATUS_1", Tools::getValue('order_status_mailupdate'));
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminModules', true).'&conf=6&configure='
                .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
            );
        } else {
            $this->html .= $this->renderAddForm();
        }
        return $this->html;
    }
    protected function deleteTables()
    {
        $Db =  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_carrier`;');
        $Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_carrier_matching`;');
      //  $Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_current_status`;');
		$Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_disable`;');
        $Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_email`;');
      //  $Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_events`;');
        $Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_history`;');
      //  $Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_issue`;');
	 //   $Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_issue_status`;');
		$Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_return`;');
		$Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_sent_report`;');
		$Db .=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_tracking_status_matching`;');
        return $Db;
    }

    public function renderAddForm()
    {
        $fields_form = array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'delivery_order',
                        'name' => 'delivery_name',
                        'form_group_class' => ((int)version_compare(_PS_VERSION_, '1.7', '<'))?'v16':'v17'
                    ),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
        Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='
        .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $statuses = array();
        $iso_code = Tools::strtoupper($this->context->language->iso_code);
        foreach ($webxml_crr->status as $status) {
            $status->name = isset($status->$iso_code)?$status->$iso_code:$status->EN;
            $statuses[$status->id_status] = $status;
        }
        $orderStates = OrderState::getOrderStates($this->context->language->id);
        $helper->tpl_vars = array(
            'link' => $this->context->link,
            'fields_value' => $this->getAddFieldsValues(),
            'statuses' => $statuses,
            'orderStates' => $orderStates,
            'status_img' => $this->url.'modules/deliveryorderautoupdate/views/img/steps/'
        );
        $helper->override_folder = '/';
        return $helper->generateForm(array($fields_form));
    }
    public function getAddFieldsValues()
    {
        $fields = array();
        $url_root = $this->url;
        $cron_url = $this->returnFrontUrl().'modules/deliveryorderautoupdate/cron_status.php?token='
        .Configuration::get("DELIVERY_TOKEN");
        $order_export = Db::getInstance()->executeS(
            "SELECT id_order_state as id, name
            FROM "._DB_PREFIX_."order_state_lang
            WHERE id_lang = ".(int)$this->context->language->id
        );

        $order_carrier = Db::getInstance()->executeS("SELECT hc.* FROM "._DB_PREFIX_."hl_carrier hc");
        foreach ($order_carrier as &$cr) {
            $url_logo_carrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/img/logos/'.$cr['id'].'.jpg';
            $url_freeservice = _PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'
                .$cr['id'].'/Carrier'.$cr['id'].'.php';
            if (file_exists($url_logo_carrier)) {
                $cr['logo'] = 1;
            } else {
                $cr['logo'] = 0;
            }
            if (file_exists($url_freeservice) && $cr['url']) {
                $cr['connector'] = 1;
            } else {
                $cr['connector'] = 0;
            }
        }
        $connectors = Db::getInstance()->executeS(
            'SELECT id, name FROM '._DB_PREFIX_.'hl_carrier ORDER BY id DESC'
        );
        $order_ids = Db::getInstance()->executeS(
            'SELECT o.id_order
            FROM '._DB_PREFIX_.'orders o
            ORDER BY o.id_order DESC
            LIMIT 20'
        );
        $lang_name = Db::getInstance()->getRow(
            'SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE id_lang = '.(int)$this->context->language->id
        );
        $stt_carrier_val = Db::getInstance()->executeS(
            "SELECT CONCAT(hcm.id_carrier_ps,'_',c.name) as carrier,'is linked to',COUNT(id_carrier_ps) as carrier_ps,
            'tracking carriers : ',GROUP_CONCAT(CONCAT(hc.id,'_',hc.name)) as carrier_list
            FROM "._DB_PREFIX_."hl_carrier_matching hcm
            INNER JOIN "._DB_PREFIX_."carrier c ON c.id_reference=hcm.id_carrier_ps
            INNER JOIN "._DB_PREFIX_."hl_carrier hc ON hcm.id_carrier_hl=hc.id
            GROUP BY hcm.id_carrier_ps
            HAVING COUNT(hcm.id_carrier_ps)>1"
        );
        $lang_name = $lang_name['iso_code'];
        $document = Tools::file_get_contents(
            dirname(__FILE__)."/guide/guide_update_status_".($lang_name ? $lang_name : 'fr').".txt"
        );
        $lines = explode("\n", $document);
        $guide = $this->url.'modules/deliveryorderautoupdate/guide/';
        foreach ($lines as $newline) {
            $guide = $guide.$newline.'';
        }
        $carrier_matching = Db::getInstance()->executeS(
            'SELECT c.id_carrier, c.name, c.active,
            COUNT(oc.id_order_carrier) as link,CONCAT(cm.id_carrier_hl, "_",hc.name) as connector
            FROM '._DB_PREFIX_.'carrier c
            LEFT JOIN '._DB_PREFIX_.'order_carrier oc ON c.id_carrier=oc.id_carrier
            LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching cm ON c.id_reference=cm.id_carrier_ps
            LEFT JOIN '._DB_PREFIX_.'hl_carrier hc ON cm.id_carrier_hl=hc.id
            GROUP BY oc.id_carrier
            ORDER BY c.id_carrier'
        );
        $connector_carrier = Db::getInstance()->executeS(
            'SELECT CONCAT(id, "_",name) as connector FROM '._DB_PREFIX_.'hl_carrier ORDER BY id'
        );
        $carriers = Db::getInstance()->executeS(
            'SELECT id_reference, name FROM '._DB_PREFIX_.'carrier where deleted = 0 ORDER BY name'
        );
        $fields['order_ids'] = $order_ids;
        $fields['carrier_matching'] = $carrier_matching;
        $fields['connector_carrier'] = $connector_carrier;
        $fields['order_export'] = $order_export;
        $fields['carriers'] = $carriers;
        $fields['order_carrier'] = $order_carrier;
        $fields['stt_carrier_val'] = $stt_carrier_val;
        $fields['order_show_status'] = Configuration::get("order_show_status");
        $fields['HL_TRACKING_REPORT_ERROR'] = Configuration::get("HL_TRACKING_REPORT_ERROR");
        $fields['date_requestorder'] = Configuration::get('DELIVERY_ORDER_DATE');
        $fields['export_from'] = Configuration::get('DELIVERY_ORDER_STATUS_FROM');
        $fields['status_exclude'] = Configuration::get('DELIVERY_ORDER_STATUS_EXCLUDE');
        $fields['carrier_exclude'] = Configuration::get('HL_TRACKING_CARRIER_EXCLUDE');
        $fields['notifi_email'] = Configuration::get('DELIVERY_ORDER_NOTIFI_EMAIL');
        $fields['event_code_mail'] = explode(',', Configuration::get('DELIVERY_EVENT_CODE_MAIL'));
        $fields['DELIVERY_EVENT_CODE_MAIL_ADMIN'] = explode(',', Configuration::get('DELIVERY_EVENT_CODE_MAIL_ADMIN'));
        $fields['DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS'] = Configuration::get('DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS');
        $fields['order_status_mailupdate'] = Configuration::get('HL_TRACKING_STATUS_1');
        $fields['response_type'] = (int)Configuration::get('HL_TRACKING_CRON_RESPONSE');
        $fields['HL_ORDER_TRACKING_BLOCK'] = (int)Configuration::get('HL_ORDER_TRACKING_BLOCK');
        $fields['HL_CUSTOMER_SHIPPING_STEP'] = (int)Configuration::get('HL_CUSTOMER_SHIPPING_STEP');
        $fields['DELIVERY_ORDER_STATUS_TO'] = json_Decode(Configuration::get('DELIVERY_ORDER_STATUS_TO'), true);
        $fields['cron_url'] = $cron_url;
        $fields['connectors'] = $connectors;
        $fields['guide'] = $guide;
        $fields['language'] = Language::getLanguages(true);
        $fields['token'] = Configuration::get('DELIVERY_TOKEN');
        $fields['url_root'] = $url_root;
        $fields['admin_dashboard'] = $this->context->link->getAdminLink('AdmindeliveryorderautoupdateDashboard', true);
        $fields['url_ajax'] = $this->context->link->getAdminLink('AdmindeliveryorderautoupdateAjax', true);
        return $fields;
    }
    public function compareDelivery($url_page, $delivery)
    {
        $txt_str = 0;
        $text_link = explode(",", $delivery['text']);
        foreach ($text_link as $txt) {
            if (strpos($url_page, $txt) ||
                (strpos($url_page, mb_convert_encoding($txt, "UTF-8", "Windows-1252"))) ||
                (mb_strpos(mb_convert_encoding($url_page, 'utf-8', 'ISO-8859-15'), $txt))
            ) {
                $txt_str = 1;
            }
        }
        return $txt_str;
    }
    public static function returnFrontUrl()
    {
        $force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
        $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
        $shop_url = Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'shop_url WHERE id_shop = '.(int)Context::getContext()->shop->id
        );
        if (isset($force_ssl) && $force_ssl) {
            return $protocol_link.$shop_url['domain_ssl'].$shop_url['physical_uri'].$shop_url['virtual_uri'];
        } else {
            return $protocol_link.$shop_url['domain'].$shop_url['physical_uri'].$shop_url['virtual_uri'];
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $html = '';
        $this->context->controller->addJquery();
        $this->context->controller->addJqueryUI('ui.dialog');
        $this->context->controller->addJqueryPlugin('chosen');
        $this->context->controller->addJqueryPlugin('tagify');
        if (Tools::getValue('controller') == 'AdminOrders' &&
            !Tools::isSubmit('id_order') &&
            Configuration::get('order_show_status')
        ) {
            $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/adminorder.js');
            $this->context->smarty->assign(array(
                    'delivery_token' => Configuration::get('DELIVERY_TOKEN'),
                    'link' => $this->context->link,
                    'secure_key' => $this->secure_key,
            ));
            $html .= $this->display(__FILE__, '/views/templates/hook/defines.tpl');
        }
        if (!version_compare(_PS_VERSION_, '1.7', '<') && Tools::getValue('controller') == 'AdminOrders') {
            $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/admin.js');
            $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/swal.js');
            $this->context->controller->addCSS(
                $this->url.'modules/deliveryorderautoupdate/views/css/hookDisplayAdminOrder17.css',
                'all'
            );
            $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/dateinput.css');
        }
        if (Tools::getValue('controller') == 'AdminOrders') {
            $this->context->controller->addCSS(
                $this->url.'modules/deliveryorderautoupdate/views/css/stepBackoffice.css'
            );
        }
        if (Tools::getValue('configure') == 'deliveryorderautoupdate') {
            $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/module_conf.js');
        }
        if (Tools::getValue('configure') == 'deliveryorderautoupdate') {
            $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/style.css', 'all');
        }
        return $html;
    }
    private function addHeaderMedia()
    {
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/style.css', 'all');
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/accordion.css');
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/step.css');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/accordion.js');
    }
    public function hookHeader()
    {
        if (method_exists('Tools', 'getAllValues')) {
            $page = Tools::getAllValues();
        } else {
            $page = $_POST + $_GET;
        }
        if ((isset($page['module']) &&
            $page['module'] == 'deliveryorderautoupdate' &&
            $page['controller'] == 'orders') ||
            $page['controller'] == 'history' ||
            $page['controller'] == 'orderdetail') {
            $this->addHeaderMedia();
        }
    }
    public function hookDisplayOrderDetail($params)
    {
        if (!Configuration::get('HL_CUSTOMER_SHIPPING_STEP')) {
            return '';
        }
        $reference = $params['order']->reference;
        $orderArr = Db::getInstance()->executeS(
            'SELECT o.reference, a.city as address1, c2.name, hlc.id as id_connector, oc.tracking_number,
            c2.id_carrier, c.url, oc.id_order_carrier, oc.date_add
            FROM `'._DB_PREFIX_.'orders` o
            LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery = a.id_address
            LEFT JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order = oc.id_order
            LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
            LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference=c2.id_reference AND c2.active=1 AND c2.deleted = 0
            LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
            LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
            WHERE o.reference LIKE "'.$reference.'"
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
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$this->context->language->iso_code.'/lang.php');
            $locale = $_LANGMAIL['locale'];
        }
        setlocale(LC_TIME, $locale);
        foreach ($orderArr as &$order) {
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
                $hs_['step_date'] = date("Y-m-d", $time);
                // $hs_['step_date'] = strftime('%e %B %G', $time);
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
            $order['created_date'] = date("Y-m-d", $time);
            // $order['created_date'] = strftime('%e %B %G', $time);
            $order['created_time'] = date('H:i:s', $time);
        }
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        $this->context->smarty->assign(array(
            'orderArr' => $orderArr,
            'statuses' => $statuses,
            'image' => _THEME_SHIP_DIR_,
            'svg' => EmailHelper::getUrl().'modules/deliveryorderautoupdate/views/img/svg/',
            'url_root' => EmailHelper::getUrl(),
            'date_format_full' => $this->context->language->date_format_full,
            'date_format_lite' => $this->context->language->date_format_lite,
            'reference' => $reference,
        ));
        return ($this->display(__FILE__, 'views/templates/front/16/Orders.tpl'));
    }
    public function hookDisplayAdminOrder()
    {
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/admin.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/swal.js');
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/dateinput.css');
        if (!Configuration::get('HL_ORDER_TRACKING_BLOCK')) {
            return '';
        }
        $helper = new EmailHelper();
        $trackingmodel = new TrackingModel();
        $shopContext = $this->context->cookie->shopContext;
        $id_shop = (int)$helper->getIdShop($shopContext, $this->context->shop->id);
        $id_order = (int)Tools::getValue('id_order');
        $shipments = Db::getInstance()->executeS(
            'SELECT oc.id_order_carrier, oc.tracking_number as track_number, c.name as carrier, c.id_carrier, c.id_reference,
            hlc.id as id_connector, hlc.name as connector, o.reference, c.url as tracking_url,
            oc.date_add, th.id_status as event_code, th.date, tdis.id_order_carrier as disable, hlc.id,
            COUNT(di.id_issue) as issue
            FROM `'._DB_PREFIX_.'order_carrier` oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order = o.id_order
            LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier = c.id_carrier
            LEFT JOIN '._DB_PREFIX_.'hl_carrier_matching hlcm ON hlcm.id_carrier_ps=c.id_reference
            LEFT JOIN '._DB_PREFIX_.'hl_carrier hlc ON hlcm.id_carrier_hl=hlc.id
            LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status th ON oc.id_order_carrier = th.id_order_carrier
            LEFT JOIN '._DB_PREFIX_.'hl_tracking_disable tdis ON oc.id_order_carrier = tdis.id_order_carrier
            LEFT JOIN '._DB_PREFIX_.'hl_tracking_issue di ON oc.id_order_carrier = di.id_order_carrier
            WHERE oc.id_order='.$id_order.'
            GROUP BY oc.id_order_carrier
            ORDER BY id_order_carrier DESC'
        );
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $path_issue = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/xml/issues.xml';
        $xml_issue = json_decode(
            json_encode(simplexml_load_file($path_issue, 'SimpleXMLElement', LIBXML_NOCDATA)),
            true
        );
        foreach ($shipments as $key => $shipment) {
            $event = $shipment['event_code'];
            $shipments[$key]['code'] = '';
            $iso_code = self::DEFAULT_LANG;
            foreach ($webxml_crr->status as $step) {
                if ($event == $step->id_status) {
                    $iso_code = Tools::strtoupper($this->context->language->iso_code);
                    if (!isset($step->{$iso_code})) {
                        $iso_code = self::DEFAULT_LANG;
                    }
                    $shipments[$key]['event_code'] = $step->{$iso_code};
                    $shipments[$key]['code'] = $step->id_status;
                    break;
                }
            }
            $shipments[$key]['event_date'] = $shipment['date'];
            $shipments[$key]['step_date'] = date(
                $this->context->language->date_format_full,
                strtotime($shipment['date'])
            );
            $shipments[$key]['json_server'] = $this->url.'modules/deliveryorderautoupdate/webservices/'
            .$shipment['id'].'/Carrier'
            .$shipment['id'].'.php?shipment_ref='
            .$shipment['id_order_carrier'].'&parcel_number='
            .$trackingmodel->handleTrackingNumber($shipment['track_number'])
            .'&token='.Configuration::get('DELIVERY_TOKEN')
            .'&order_ref='
            .$shipment['reference'].'&id_shop='.$id_shop.'&devmode=1';
            if (strpos($shipment['tracking_url'], '@') !== false) {
                $shipments[$key]['tracking_url'] = str_replace('@', $shipment['track_number'], $shipment['tracking_url']);
            } elseif ($shipment['tracking_url']) {
                $shipments[$key]['tracking_url'] = $shipment['tracking_url'].$shipment['track_number'];
            } else {
                $shipments[$key]['tracking_url'] = null;
            }
        }
        $array_issue = array();
        $array_issue_status = array();
        $iso_code = $this->context->language->iso_code;
        foreach ($xml_issue['issues']['issue'] as $issue) {
            $array_issue[] = array(
                'id' => $issue['id_issue'],
                'name' => isset($issue[$iso_code])?$issue[$iso_code]:$issue[Tools::strtolower(self::DEFAULT_LANG)],
            );
        }
        foreach ($xml_issue['statuses']['status'] as $status) {
            $array_issue_status[] = array(
                'id' => $status['id_status'],
                'name' => isset($status[$iso_code])?$status[$iso_code]:$status[Tools::strtolower(self::DEFAULT_LANG)],
            );
        }
        $prefix = _DB_PREFIX_;
        if (!count($shipments)) {
            $shipment = false;
        } else {
            $shipment = $shipments[0];
            $returns = ReturnOrder::getReturnByOrder((int)Tools::getValue('id_order'));
        }
        $carriers = Db::getInstance()->executeS(
            "SELECT id_carrier, name
            FROM {$prefix}carrier
            WHERE deleted = 0 ORDER BY active DESC, name ASC "
        );
        $connectors = Db::getInstance()->executeS(
            'SELECT id as id_connector, name as connector FROM '._DB_PREFIX_.'hl_carrier ORDER BY id'
        );
        $requests = Db::getInstance()->executeS(
            'SELECT CONCAT("Return #", id_order_return," of ", Date(date_add)) as name, id_order_return
            FROM '._DB_PREFIX_.'order_return WHERE id_order = '.$id_order
        );

        $locale = 0;
        if (file_exists(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.
            $this->context->language->iso_code.'/lang.php')) {
            $_LANGMAIL = array();
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/'.$this->context->language->iso_code.'/lang.php');
            $locale = $_LANGMAIL['locale'];
        } else {
            include(_PS_MODULE_DIR_.'deliveryorderautoupdate/mails/en/lang.php');
        }
        setlocale(LC_TIME, $locale);
        $iso_code = Tools::strtoupper($this->context->language->iso_code);
        $shipping = array_map(function ($step) use ($iso_code) {
            return isset($step->$iso_code)?$step->$iso_code:$step->EN;
        }, $webxml_crr->status);
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        $link = new Link();
        $langs = count(Language::getLanguages());
        $emp_lang = Language::getIsoById($this->context->employee->id_lang);
        $meta = Meta::getMetaByPage('module-deliveryorderautoupdate-orders', $this->context->employee->id_lang);
        $this->context->smarty->assign(array(
            'returns' => $returns,
            'carriers' => $carriers,
            'connectors' => $connectors,
            'requests' => $requests,
            'ajaxdel_url' => $link->getAdminLink('AdmindeliveryorderautoupdateAjax'),
            'trackingpage_url' => $link->getAdminLink('AdmindeliveryorderautoupdateDashboard'),
            'id_order' => Tools::getValue('id_order'),
            'url_root' => $this->url,
            'secure_key' => $this->secure_key,
            'email_statuses' => EmailHelper::$email_status,
            'statuses' => $statuses,
            'shipping' => $shipping,
            'methods' => $this->method,
            'shipments' => $shipments,
            'shipment' => $shipment,
            'date_format_full' => $this->context->language->date_format_full,
            'date_format_lite' => $this->context->language->date_format_lite,
            'front_url' => Deliveryorderautoupdate::returnFrontUrl().($langs > 1?$emp_lang.'/':'').$meta['url_rewrite'],
            'issues' => $array_issue,
            'issue_status' => $array_issue_status,
            'v16' => (int)version_compare(_PS_VERSION_, '1.7', '<'),
            'url' => $this->url
        ));
        return ($this->display(__FILE__, 'views/templates/hook/displayAdminOrder.tpl'));
    }
    public function getStatus($id_order_carrier)
    {
        if (!$id_order_carrier) {
            return null;
        }

        $history_left = Db::getInstance()->getRow(
            'SELECT hl.id,hl.date_add, hl.step_date as stepdate, hl.hl_carrier, hl.success_response,
            hl.carrier_response, hl.event_code,hl.id_order, hl.email_sent, o.reference
            FROM `'._DB_PREFIX_.'hl_tracking_history` hl
            INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = hl.id_order
            INNER JOIN '._DB_PREFIX_.'order_carrier oc ON hl.id_order_carrier=oc.id_order_carrier
            WHERE oc.id_order_carrier = "'.$id_order_carrier.'" AND hl.event_code IN (1,2,3,4,5,6,7,8,9)
            ORDER BY hl.id DESC'
        );
        return $history_left;
    }
    public function getStatusList()
    {
        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        return $statuses;
    }
    public function getTranslation()
    {
        return array(
            'shipment_selected' => $this->l('shipment selected'),
            'shipments_selected' => $this->l('shipments selected')
        );
    }
}
