<?php
/**
 * Combine guests for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(dirname(__FILE__) . '/../../models/HsCGCustomer.php');
require_once(dirname(__FILE__) . '/../../models/CombineModel.php');

class AdminCombineGuestController extends ModuleAdminController
{

    /**
     * Array data return when using ajax
     * @var array
     */
    protected $json_data = array(
        'success' => false,
        'message' => false,
        'data' => false
    );

    /**
     * Enable this only if you are combining customers instead of guests.
     * This is useful when, due to unknown reasons, there are duplicated customer records (is_guest = 0) in table Customer
     * @var boolean
     */
    protected $_combine_customers = false;

    /**
     * construct
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'customer';
        $this->identifier = 'id_customer';
        $this->list_no_link = true;
        $this->className = 'Customer';
        $this->lang = false;
        parent::__construct();
        $this->bulk_actions = array(
            'combine' => array(
                'text' => $this->module->i18n['combine_elected'],
                'confirm' => $this->module->i18n['combine_selected_items']
            )
        );
        // get all both guest + account have the same email
        $this->_select = '
                        GROUP_CONCAT(`is_guest`) AS `is_guest_string`,
                        GROUP_CONCAT(`id_customer`) AS `id_customer_string`,
                        COUNT(a.`id_customer`) AS `total`
                    ';

        if ($this->_combine_customers) {
            $this->_where = " AND a.`email` !='' AND a.`active` = 1 AND a.`is_guest` = 0";
            $this->_having = "`total` > 1"; // if there is only 1 customer, it's good. Nothing to do.
        } else {
            // combine guests (the default case of this module)
            $this->_where = " AND a.`email` !='' AND a.`active` = 1 AND a.`is_guest` = 1";
        }
        $this->_where .= Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'a');
        $this->_group = ' GROUP BY a.`email`';
        $this->fields_list['id_customer'] = array(
            'title' => $this->module->i18n['ids'],
            'align' => 'center',
            'callback' => 'getListGuestIds',
            'width' => 30
        );

        $this->fields_list['firstname'] = array(
            'title' => $this->module->i18n['first_name'],
            'filter_key' => 'a!firstname',
            'align' => 'center',
            'orderby' => true,
            'filter' => true,
            'search' => true
        );

        $this->fields_list['lastname'] = array(
            'title' => $this->module->i18n['last_name'],
            'filter_key' => 'a!lastname',
            'align' => 'center',
            'orderby' => true,
            'filter' => true,
            'search' => true
        );

        $this->fields_list['email'] = array(
            'title' => $this->module->i18n['email'],
            'align' => 'left',
            'filter_key' => 'a!email',
            'align' => 'center',
            'orderby' => true,
            'filter' => true,
            'search' => true
        );

        $this->fields_list['total'] = array(
            'title' => $this->module->i18n['total'],
            'width' => 35,
            'align' => 'center',
            'filter_key' => 'total',
            'orderby' => true,
            'filter' => true,
            'search' => false
        );

        $this->fields_list['is_guest_string'] = array(
            'title' => $this->module->i18n['has_customer_account'],
            'width' => 150,
            'align' => 'center',
            'callback' => 'checkGuestIsCustomer',
            'orderby' => false,
            'search' => false
        );
        
        $this->action = Tools::getValue('action');
        if ($this->module->isPrestashop16()) {
            $this->fields_options = array(
                'hs_combineguests_footer' => array(
                    'title' => $this->module->i18n['about_us'],
                    'fields' => $this->generateFooterModuleInformation()
                )
            );
        }
    }
    protected function generateFooterModuleInformation()
    {
        return array(
            'HS_COMBINEGUESTS_FOOTER' => array(
                'type' => 'hs_combineguests_footer',
                'is_prestashop16' => (int) $this->module->isPrestashop16(),
                'module_version' => $this->module->version,
                'document_url' => $this->module->getDocumentPath(),
                'module_author' => $this->module->author,
                'module_year' => '2013 - ' . date("Y"),
            ),
        );
    }

    /**
     * remove toolbar default
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
        unset($this->toolbar_btn['export']);
    }

    /**
     * fix 1.6 does not show check box when there is a guest's customer
     * @param Helper $helper
     */
    public function setHelperDisplay(Helper $helper)
    {
        parent::setHelperDisplay($helper);
        $helper->force_show_bulk_actions = true;
        $this->helper = $helper;
    }

    /**
     * Get all customer have the same email (only get id_customer)
     * @param int $id_customer
     * @return string id_customer (ex: 1,2,3)
     * */
    public function getListGuestIds($id_customer)
    {
        $sql = 'SELECT GROUP_CONCAT(`id_customer`) as `id_guests` FROM `' . _DB_PREFIX_ . 'customer`
		WHERE  `email` like (SELECT `email` from `' . _DB_PREFIX_ . 'customer` WHERE `id_customer` = ' . (int) $id_customer .')'. Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);
        $result = Db::getInstance()->getRow($sql);
        $customer_ids = null;
        if (!empty($result)) {
            $customer_ids = $result['id_guests'];
        }
        return $customer_ids;
    }

    /**
     * Check if guest exists as a customer
     * @param string $id_guest_string
     * @return string
     */
    public function checkGuestIsCustomer($id_guest_string)
    {
        $id_guest = explode(',', $id_guest_string);
        if (in_array('0', $id_guest)) {
            $exists_as_customer = 1;
        } else {
            $exists_as_customer = 0;
        }
        $this->context->smarty->assign(array(
            'exists_as_customer' => $exists_as_customer
        ));
        return $this->createTemplate('_guest_exists_customer.tpl')->fetch();
    }

    public function postProcess()
    {
        parent::postProcess();
        // process save setting Email
//        if (Tools::getValue('HSCUSTOMERNOTIFY') == 'Save') {
//            $hs_customer_notify = Tools::getValue('hs_customer_notify', 0);
//            if (Configuration::updateValue('HS_CUSTOMER_NOTIFY', $hs_customer_notify)) {
//                $this->confirmations[] = $this->module->i18n['the_setting_has_been_updated_successfully'];
//            }
//        }
        if (Tools::isSubmit('submitCombineGuestsSetting')) {
            foreach ($this->module->configuration_keys as $config_name => $config_validate) {
                $config_validate = $config_validate; // fix validator ps not use
                if (Validate::$config_validate(Tools::getValue($config_name))) {
                    Configuration::updateValue($config_name, Tools::getValue($config_name));
                }
            }
            $this->confirmations[] = $this->_conf[6];
        }
        if (Tools::isSubmit('submitBulkcombinecustomer')) {
            $id_guests = Tools::getValue('customerBox');
            if ($this->processCombineGuestToCustomer($id_guests)) {
                $this->confirmations[] = $this->module->i18n['combine_guests_to_customer_sucessfully'];
            } else {
                $this->errors[] = Tools::displayError('There is an error occured. Please try again!');
            }
        }
    }

    /**
     *
     * submit combine guests form page customer detail
     */
    public function processCombineGuests()
    {
        $current_customer_id = (int) Tools::getValue('current_customer_id');
        $selected_customer_ids = Tools::getValue('customers', array());
        if (!empty($selected_customer_ids) && !empty($current_customer_id)) {
            array_push($selected_customer_ids, $current_customer_id);
        } else {
            exit($this->module->i18n['errors']);
        }
        $combine_customer = false;
        //check there are more than one selected customers is customer  => If $selected_customer_ids contain a
        if ($this->checkExistMoreCustomer($selected_customer_ids)) {
            // get target customer
            $target_customer_id = HsCGCustomer::getTargetCustomerId($current_customer_id, $selected_customer_ids);
            // get id customer which resgister is customer
            $customer_ids = HsCGCustomer::getSelectedCustomers(array_diff($selected_customer_ids, array($target_customer_id)));
            // combine customer
            $combine_customer = $this->combineCustomers($target_customer_id, $customer_ids);
            $selected_customer_ids = array_diff($selected_customer_ids, $customer_ids);
        }

        // Selected customers don't include any guest
        if ($combine_customer && count($selected_customer_ids) === 1) {
            $password = $this->module->i18n['as_you_registered_in_our_shop'];
            $this->sendEmail($target_customer_id, $password);
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomers') . '&conf=4');
        } elseif (!count($this->errors)) {
            // selected customer  contain guests

            $guest_ids = array(implode(',', $selected_customer_ids));
            if (!empty($current_customer_id) && !empty($guest_ids) && $this->processCombineGuestToCustomer($guest_ids)) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomers') . '&conf=4');
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomers'));
            }
        } else {
            exit($this->module->i18n['customers_have_been_combined_fail']);
        }
    }

    /**
     * combine all customers to a customer
     * @param int $target_customer_id
     * @param array $customer_ids
     * @return boolean
     */
    protected function combineCustomers($target_customer_id, array $customer_ids)
    {
        $combine_model = new CombineModel();
        $id_currency = $this->context->currency->id;
        $id_group = Customer::getDefaultGroupId($target_customer_id);
        $id_country = Customer::getCurrentCountry($target_customer_id);

        if (!$combine_model->combineAddresses($customer_ids, $target_customer_id) || !$combine_model->combinePrivateNotes($customer_ids, $target_customer_id) || !$combine_model->combineCarts($customer_ids, $target_customer_id) || !$combine_model->combineCartRule($customer_ids, $target_customer_id, $id_group) || !$combine_model->combineCompareProduct($customer_ids, $target_customer_id) || !$combine_model->combineMessage($customer_ids, $target_customer_id) || !$combine_model->combineOrders($customer_ids, $target_customer_id) || !$combine_model->combineSpecificPrice($customer_ids, $target_customer_id, $id_currency, $id_country, $id_group)) {
            $this->errors[] = Tools::displayError('Customers have been combined fail.');
        }
        // combine all loyalty of guests to customer
        $dependency_module_name = 'loyalty';
        if (Module::isEnabled($dependency_module_name) && Module::isInstalled($dependency_module_name)) {
            if (!$combine_model->combineLoyalty($customer_ids, $target_customer_id)) {
                $this->errors[] = Tools::displayError('There is an error when convert guests loyalty. Please try again!');
            }
        }
        if (!count($this->errors)) {
            if (!$combine_model->deleteGuestsCustomers($customer_ids)) {
                $this->errors[] = Tools::displayError('Customers have been deleted fail.');
            }
        }
        return (!count($this->errors)) ? true : false;
    }

    /**
     * check all id customers which is selected it has more than one id customer register is customer
     * @param array $selected_customer_ids
     * @return boolean
     */
    protected function checkExistMoreCustomer(array $selected_customer_ids)
    {
        $flag = false;
        $customer_ids = HsCGCustomer::getSelectedCustomers($selected_customer_ids);
        if (!empty($customer_ids) && count($customer_ids) >= 2) {
            $flag = true;
        }
        return $flag;
    }

    /**
     * @desc get all selected guests
     * Foreach guest
     * +> Check if guest is exists as a customer
     * +> If yes, don't call function convert guest to customer. And return the id customer exists
     * +> If not, choose random a guest and update it to a customer, and then update another guest for that guest (just updated to a customer).
     * (Why: decrease the record in db, save time to create new Object customer and insert new, don't have to update many record + tables)
     * +>  Convert address
     * +> if have two addresses have the same alias? => Rename Alias
     * +> If not update address to customer converted
     * +> Convert cart
     * +> Convert Order
     * +> Convert Order History
     * End Foreach
     * @param array $id_guests
     * @return boolean
     * */
    public function processCombineGuestToCustomer($id_guests = array())
    {
        $combine_model = new CombineModel();
        $flag = true;
        if (!empty($id_guests)) {
            foreach ($id_guests as $id_guest_string) {
                // check if guest exists as a customer
                $guest_is_customer = $combine_model->checkGuestIsExistsCustomer($id_guest_string);
                // explode string id_guest to an array
                $id_guest_string = explode(',', $id_guest_string);
                // if guest exists as customer
                if (!empty($guest_is_customer)) {
                    $id_customer = $guest_is_customer['id_customer'];
                    $password = $this->module->i18n['as_you_registered_in_our_shop'];
                } else {
                    // get first guest and convert it to a customer
                    $id_customer = $id_guest_string[0];
                    // call function convert guests to customer. Clone everything from guest to new customer, but create a randon
                    // password and send an email to customer if the config send notification is enable.
                    $password = Tools::passwdGen();
                    if (!Validate::isPasswd($password)) {
                        $this->errors[] = Tools::displayError('Create customer\'s password fail. Please try again!');
                    }
                    if (!$combine_model->combineGuestToCustomer($id_customer, Tools::hash($password))) {
                        $this->errors[] = Tools::displayError('There is an error when convert guests to customer. Please try again!');
                    }
                }
                // remove guest exists a customer out of the id_guest_string
                $id_guests = array_diff($id_guest_string, array($id_customer));

                // we are only combine to address + cart + order.... if the account exist as a (customer + guest)
                if (!empty($id_guests) && !count($this->errors)) {
                    // convert guest's address to customer
                    if (!$combine_model->combineAddresses($id_guests, $id_customer)) {
                        $this->errors[] = Tools::displayError('There is an error when convert guests addresses. Please try again!');
                    }
                    if (!$combine_model->combinePrivateNotes($id_guests, $id_customer)) {
                        $this->errors[] = Tools::displayError('There is an error when convert guests private notes. Please try again!');
                    }
                    // convert guest's cart to customer
                    if (!$combine_model->combineCarts($id_guests, $id_customer)) {
                        $this->errors[] = Tools::displayError('There is an error when convert guests carts. Please try again!');
                    }

                    // convert guest's order to customer
                    if (!$combine_model->combineOrders($id_guests, $id_customer)) {
                        $this->errors[] = Tools::displayError('There is an error when convert guests orders. Please try again!');
                    }

                    // combine all message of guests to customer
                    if (!$combine_model->combineMessage($id_guests, $id_customer)) {
                        $this->errors[] = Tools::displayError('There is an error when convert guests message. Please try again!');
                    }

                    // combine all loyalty of guests to customer
                    $dependency_module_name = 'loyalty';
                    if (Module::isEnabled($dependency_module_name) && Module::isInstalled($dependency_module_name)) {
                        if (!$combine_model->combineLoyalty($id_guests, $id_customer)) {
                            $this->errors[] = Tools::displayError('There is an error when convert guests loyalty. Please try again!');
                        }
                    }
                    // delete all guests converted
                    if (!$combine_model->deleteGuestsCustomers($id_guests)) {
                        $this->errors[] = Tools::displayError('There is an error when delete guests. Please try again!');
                    }
                }
                if (!count($this->errors)) {
                    $this->sendEmail($id_customer, $password);
                }
            }
        }
        if (!count($this->errors)) {
            $flag = true;
        }
        return $flag;
    }

    /**
     * Override value of id_customer_string and total to show correct list account are both (customer + guest)
     * @param see AdminControlelr::getList()
     */
    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        foreach ($this->_list as &$list) {
            $list_id_customer = $this->getListGuestIds($list['id_customer']);
            $list['id_customer_string'] = $list_id_customer;
            $list['total'] = count(explode(',', $list_id_customer));
            $list['is_guest_string'] = $this->implodeGuestString($list_id_customer);
        }
    }

    /**
     * Implode array is_guest of a list customer to a string
     * @param varchar $list_id_customer
     * @return varchar id_guests (ex: 0,1,0)
     */
    public function implodeGuestString($list_id_customer)
    {
        $sql = 'SELECT GROUP_CONCAT(`is_guest`) as `id_guests` FROM `' . _DB_PREFIX_ . 'customer`
					WHERE `id_customer` IN (' . pSQL($list_id_customer) . ') ';
        $result = Db::getInstance()->getRow($sql);
        $guest_ids = null;
        if (!empty($result)) {
            $guest_ids = $result['id_guests'];
        }
        return $guest_ids;
    }

    /**
     * ajax search customer
     */
    public function ajaxProcessSearchCustomers()
    {
        $curent_customer_id = (int) Tools::getValue('current_customer_id');
        $customers = HsCGCustomer::searchGuestCustomers(pSQL(Tools::getValue('customer_search')), $curent_customer_id);
        $this->context->smarty->assign(array(
            'customers' => $this->getCustomers($customers),
            'curent_customer_id' => $curent_customer_id,
            'link' => $this->context->link->getAdminLink(hscombineguests::CLASS_CONTROLLER_COMBINE_GUEST),
        ));
        
        if ($this->module->isPrestashop176()) {
            $template = 'customer_search_176.tpl';
        } elseif ($this->module->isPrestashop1617()) {
            $template = 'customer_search_16_17.tpl';
        } else {
            $template = 'customer_search_15.tpl';
        }
        $this->json_data = array(
            'success' => true,
            'data' => $this->createTemplate($template)->fetch()
        );
        $this->content = Tools::jsonEnCode($this->json_data);
    }

    /**
     * add more info of customer
     * @param array $customers
     * @return array
     * Array
     *   (
     *       <pre>
     *       [0] => Array
     *           (
     *               [id_customer] => int
     *               [id_shop_group] => int
     *               [id_shop] => int
     *               [id_gender] => int
     *               [id_default_group] => int
     *               [id_lang] => int
     *               [id_risk] => int
     *               [company] => string
     *               [siret] => int
     *               [ape] => int
     *               [firstname] => string
     *               [lastname] => string
     *               [email] => string
     *               [passwd] => string
     *               [last_passwd_gen] => date
     *               [birthday] => date
     *               [newsletter] => boolean
     *               [ip_registration_newsletter] => int
     *               [newsletter_date_add] => date
     *               [optin] => boolean
     *               [website] => string
     *               [outstanding_allow_amount] => float
     *               [show_public_prices] => boolean
     *               [max_payment_days] => int
     *               [secure_key] => string
     *               [note] => string
     *               [active] => boolean
     *               [is_guest] => boolean
     *               [deleted] => boolean
     *               [date_add] => date
     *               [date_upd] => date
     *               [sex] => string
     *               [last_visit] => date
     *               [age] => int
     *               [language] => string
     *           )
     *      [1]=> Array()
     * ..............
     * </pre>
     */
    protected function getCustomers($customers)
    {
        if (!empty($customers)) {
            foreach ($customers as &$customer) {
                $obj_customer = new Customer($customer['id_customer']);
                if (Validate::isLoadedObject($obj_customer)) {
                    if (version_compare(_PS_VERSION_, '1.6') == 1) {
                        $gender = new Gender($obj_customer->id_gender, $this->context->language->id);
                    } else {
                        $gender = new Gender($this->context->language->id);
                    }
                    if (Validate::isLoadedObject($gender)) {
                        $customer['sex'] = !empty($gender) ? $gender->name : $this->module->i18n['unknown'];
                    } else {
                        $customer['sex'] = $this->module->i18n['unknown'];
                    }

                    $customer_stats = $obj_customer->getStats();

                    $customer['last_visit'] = ($customer_stats['last_visit']) ? $customer_stats['last_visit'] : $this->module->i18n['never'];
                    $customer['age'] = $customer_stats['age'];

                    $language = new Language($customer['id_lang']);
                    if (Validate::isLoadedObject($language)) {
                        $customer['language'] = $language->name;
                    } else {
                        $customer['language'] = $this->module->i18n['unknown'];
                    }
                }
            }
        }
        return $customers;
    }

    /**
     *
     * @param int $id_customer
     * @param string $password
     */
    protected function sendEmail($id_customer, $password)
    {
        // send mail if send notifications option is enable
        if (Configuration::get('HS_CUSTOMER_NOTIFY')) {
            $customer = new Customer($id_customer);
            if (Validate::isLoadedObject($customer)) {
                $id_lang = (int) $customer->id_lang;
                $template_vars = array(
                    '{site_name}' => Configuration::get('PS_SHOP_NAME'),
                    '{firstname}' => $customer->firstname,
                    '{email}' => $customer->email,
                    '{password}' => $password,
                    '{forgot_password_url}' => $this->context->link->getPageLink('password', true)
                );
                Mail::Send($id_lang, 'mail_confirm', Mail::l($this->module->i18n['just_combined_your_accounts'], $id_lang), $template_vars, $customer->email, null, Configuration::get('PS_SHOP_EMAIL'), Configuration::get('PS_SHOP_NAME'), null, null, _PS_MODULE_DIR_ . 'hscombineguests/mails/');
            }
        }
    }
}
