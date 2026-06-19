<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class Ets_abandonedcartUnsubscribeModuleFrontController extends ModuleFrontController
{
    public $_errors = array();

    public function __construct()
    {
        parent::__construct();
        $this->template = ($this->module->is17 ? 'module:' . $this->module->name . '/views/templates/front/' : '') . 'unsubscribe' . ($this->module->is17 ? '' : '-16') . '.tpl';
    }

    public function initContent()
    {
        parent::initContent();
        $assigns = array();
        $email = urldecode(Tools::getValue('email'));
        if (!($verify = urldecode(Tools::getValue('verify')))) {
            $this->_errors[] = $this->module->l('Verification is required.', 'unsubscribe');
        } elseif ($this->module->encrypt($email) !== $verify) {
            $this->_errors[] = $this->module->l('Invalid verification.', 'unsubscribe');
        } elseif (!$email) {
            $this->_errors[] = $this->module->l('Email is required.', 'unsubscribe');
        } elseif (!Validate::isEmail($email)) {
            $this->_errors[] = $this->module->l('Email is invalid.', 'unsubscribe');
        } elseif (!$this->unsubscribe($email)) {
            $this->_errors[] = $this->module->l('An error occurred while attempting to unsubscribe.', 'unsubscribe');
        }
        if (!$this->_errors)
            $assigns['msg'] = $this->module->l('Unsubscription successful.', 'unsubscribe');
        else
            $assigns['errors'] = $this->_errors;
        $assigns['unsubscribe'] = $this->module->getLocalPath() . 'views/templates/front/_unsubscribe.tpl';
        $this->context->smarty->assign($assigns);
        $this->setTemplate($this->template);
    }

    private function unsubscribe($email)
    {
        if (!($id = Customer::customerExists($email, true)) || !EtsAbancartUnsubscribers::setCustomerUnsubscribe($id)) {
            return false;
        }
        return true;
    }
}
