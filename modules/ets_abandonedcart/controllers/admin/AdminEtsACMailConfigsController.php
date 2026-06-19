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

require_once(dirname(__FILE__) . '/AdminEtsACOptionsController.php');

class AdminEtsACMailConfigsController extends AdminEtsACOptionsController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        $btnText = $this->module->getTextTrans('Send test mail');
        $this->fields_options = array(
            'title' => $this->l('Mail configuration', 'AdminEtsACMailConfigsController'),
            'fields' => $this->def->getMailConfigs(),
            'icon' => '',
            'submit' => array(
                'title' => $this->l('Save', 'AdminEtsACMailConfigsController'),
            ),
            'buttons' => array(
                'send_test_mail' => array(
                    'title' => $btnText,
                    'icon' => 'process-icon-envelope',
                    'id' => 'ets_abancart_send_mail_test',
                    'name' => 'sendTestMail',
                )
            ),
        );
    }

    public function getConfigs($api = '')
    {
        $configs = array();
        $mail_options = $this->def->getMailOptions();
        $options = $api && isset($mail_options[$api]) && $mail_options[$api] ? $mail_options[$api] : $mail_options;

        if ($options) {
            foreach ($options as $option) {
                if (isset($option['id_option']) && trim($option['id_option']) !== '' && trim($option['id_option']) != 'default') {
                    foreach ($this->fields_options['fields'] as $key => $field) {
                        if (!empty($field['multi']) && (empty($field['api']) && empty($option['api']) || !empty($field['api']) && !empty($option['api']))) {
                            $api_mail_type = Tools::strtolower(trim($option['id_option']));
                            $newKey = $key . '_' . Tools::strtoupper($api_mail_type);
                            // Value:
                            if (Tools::isSubmit('submitOptionsconfiguration')) {
                                $configs[$newKey] = trim(($val = Tools::getValue($newKey))) && Validate::isCleanHtml($val) ? $val : '';
                            } else {
                                $configs[$newKey] = ($configVal = Configuration::get($newKey)) ? $configVal : Configuration::get($key);
                            }

                            // Check default configs:
                            $defaults = isset($field['defaults']) && is_array($field['defaults']) && count($field['defaults']) > 0;
                            // Placeholder:
                            if (isset($field['placeholders']) && is_array($field['placeholders']) && count($field['placeholders']) > 0 && isset($field['placeholders'][$api_mail_type]) && trim($field['placeholders'][$api_mail_type]) !== '') {
                                $configs[$newKey . '_PLACEHOLDER'] = $field['placeholders'][$api_mail_type];
                                // Default:
                                if (!$defaults) {
                                    $configs[$newKey . '_DEFAULT'] = $field['placeholders'][$api_mail_type];
                                }
                            } elseif ($defaults || isset($field['default']) && trim($field['default']) !== '') {
                                $configs[$newKey . '_DEFAULT'] = isset($field['defaults'][$api_mail_type]) && trim($field['defaults'][$api_mail_type]) !== '' ? $field['defaults'][$api_mail_type] : (isset($field['default']) && trim($field['default']) !== '' ? $field['default'] : '');
                            }
                        }
                    }
                }
            }
        }

        return $configs;
    }

    public function renderJs()
    {
        $this->context->smarty->assign(array(
            'configuration' => $this->getConfigs(),
        ));
        return $this->createTemplate('head.tpl')->fetch() . parent::renderJs();
    }

    public function ajaxProcessSendTestMail()
    {
        if ($this->access('edit')) {
            $email = trim(Tools::getValue('email'));
            if ($email == '') {
                $this->errors[] = $this->l('Email is required', 'AdminEtsACMailConfigsController');
            } elseif (!Validate::isEmail($email)) {
                $this->errors[] = $this->l('Error: invalid email address', 'AdminEtsACMailConfigsController');
            } else {
                #Notice: Email content may have  special characters and Validate::cleanHtml can not detect right
                $content = ($content = Tools::getValue('email_content')) ? $content : '';
                $subject = ($subject = Tools::getValue('email_subject')) && Validate::isCleanHtml($subject) ? $subject : '';
                $get_error = [];
                if (!EtsAbancartMail::send(
                    (int)$this->context->language->id,
                    'test_mail',
                    $subject ? $subject : $this->l('Test message -- AbandonedCart', 'AdminEtsACMailConfigsController'),
                    array(
                        '{context}' => $content ? $content : $this->l('This is a test message. Your server is now configured to send email', 'AdminEtsACMailConfigsController')
                    ),
                    $email,
                    'Test Message', null, null, null, null,
                    $this->module->getLocalPath() . 'mails/', false,
                    $this->context->shop->id,
                    null, null, null, $get_error
                )) {
                    $this->errors[] = $this->l('Error: Please check your configuration', 'AdminEtsACMailConfigsController') . '. ' . (is_array($get_error) && count($get_error) > 0 ? implode(PHP_EOL, $get_error) : $get_error);
                }
            }
        } else
            $this->errors[] = $this->l('Permission denied', 'AdminEtsACMailConfigsController');
        $hasError = count($this->errors) ? 1 : 0;
        $this->toJson([
            'errors' => $hasError ? Tools::nl2br(implode(PHP_EOL, $this->errors)) : false,
            'msg' => !$hasError ? $this->l('A test email has been sent to the email address you provided.', 'AdminEtsACMailConfigsController') : ''
        ]);
    }
}