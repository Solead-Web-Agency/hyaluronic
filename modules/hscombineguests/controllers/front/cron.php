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

class HsCombineGuestsCronModuleFrontController extends ModuleFrontController
{
    /**
     * @var bool
     */
    protected $display_header = false;

    /**
     * @var bool
     */
    protected $display_footer = false;

    public function postProcess()
    {
        if (Tools::hash($this->module->name) != Tools::getValue('token')) {
            exit;
        }
        if (!Configuration::get('HSCG_CRON_JOB')) {
            exit;
        }
        $id_guests = HsCGCustomer::getIdCustomersSameEmail();
        if (empty($id_guests)) {
            exit;
        }
        if (Module::isInstalled($this->module->name) && Module::isEnabled($this->module->name)) {
            $this->processCombineGuestToCustomer($id_guests);
        } else {
            exit;
        }
    }

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
