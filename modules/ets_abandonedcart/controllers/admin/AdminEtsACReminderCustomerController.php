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
if (!class_exists('AdminEtsACReminderEmailController'))
    require_once(dirname(__FILE__) . '/AdminEtsACReminderEmailController.php');

class AdminEtsACReminderCustomerController extends AdminEtsACReminderEmailController
{
    public function __construct()
    {
        $this->type = 'customer';
        parent::__construct();

        $this->tpl_folder = 'common/';
    }

    public function getFieldsForm()
    {
        return [
            'hidden_reminder_id' => [
                'name' => 'hidden_reminder_id',
                'type' => 'hidden',
                'label' => $this->l('Reminder', 'AdminEtsACReminderCustomerController'),
                'default_value' => (int)Tools::getValue($this->identifier),
            ],
            'id_ets_abancart_email_template' => array(
                'name' => 'id_ets_abancart_email_template',
                'label' => $this->l('Email templates', 'AdminEtsACReminderCustomerController'),
                'type' => 'hidden',
            ),
            'title' => array(
                'name' => 'title',
                'label' => $this->l('Subject', 'AdminEtsACReminderCustomerController'),
                'type' => 'text',
                'lang' => true,
                'required' => true,
                'validate' => 'isMailSubject',
                'form_group_class' => 'abancart form_message isMailSubject required'
            ),
            'content' => array(
                'name' => 'content',
                'label' => $this->l('Email content', 'AdminEtsACReminderCustomerController'),
                'type' => 'textarea',
                'autoload_rte' => true,
                'lang' => true,
                'required' => true,
                'desc_type' => $this->type,
                'validate' => 'isCleanHtml',
                'form_group_class' => 'abancart content form_message isCleanHtml required'
            ),
            'email_timing_option' => [
                'name' => 'email_timing_option',
                'type' => 'hidden',
                'label' => $this->l('Campaign type', 'AdminEtsACReminderCustomerController'),
                'default_value' => $this->campaign->object->email_timing_option,
            ]
        ];
    }

    public function getConfirmInformationForm()
    {
        $options = [
            [
                'id_option' => 0,
                'name' => $this->l('Draft', 'AdminEtsACReminderEmailController'),
                'class' => 'enabled_no',
                'p' => $this->l('Save without sending email', 'AdminEtsACReminderEmailController')
            ],
        ];

        $isSchedule = $this->campaign->object->email_timing_option == EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME;
        $desc_send_email_now = $this->l('Save and send email immediately', 'AdminEtsACReminderEmailController');

        if ($this->campaign->object->email_timing_option == EtsAbancartReminder::CUSTOMER_EMAIL_SEND_RUN_NOW) {
            $options[] = [
                'id_option' => 3,
                'name' => $this->l('Finished', 'AdminEtsACReminderEmailController'),
                'class' => 'enabled_yes',
                'p' => $desc_send_email_now

            ];
        } else {
            $options[] = [
                'id_option' => 1,
                'name' => $isSchedule ? $this->l('Pending', 'AdminEtsACReminderEmailController') : $this->l('Running', 'AdminEtsACReminderEmailController'),
                'class' => 'enabled_yes',
                'p' => $isSchedule ? $this->l('Save and send email based on your scheduled time', 'AdminEtsACReminderEmailController') : $desc_send_email_now
            ];
        }

        //send_email_now
        return array(
            'enabled' => array(
                'type' => 'radios',
                'name' => 'enabled',
                'label' => $isSchedule ? $this->l('Send your scheduled email?', 'AdminEtsACReminderEmailController') : $this->l('Send email now?', 'AdminEtsACReminderEmailController'),
                'default' => 0,
                'options' => array(
                    'query' => $options,
                    'id' => 'id_option',
                    'name' => 'name',
                ),
                'form_group_class' => 'abancart form_confirm_information'
            )
        );
    }

    public function ajaxProcessRenderForm()
    {
        if ($this->access('edit')) {
            /** @var EtsAbancartReminder $object */
            $object = $this->loadObject(true);
            $emailTimingOption = null;
            if (($idCampaign = (int)Tools::getValue('id_ets_abancart_campaign')) && (Tools::isSubmit('addets_abancart_reminder') || Tools::isSubmit('updateets_abancart_reminder') || Tools::isSubmit('viewets_abancart_campaign'))) {
                $campaign = new EtsAbancartCampaign($idCampaign);
                $emailTimingOption = $campaign->campaign_type == 'customer' ? $campaign->email_timing_option : null;
            }
            $menuSteps = EtsAbancartReminderForm::getInstance()->getReminderSteps();
            if (isset($campaign) && in_array($campaign->email_timing_option, array(EtsAbancartReminder::CUSTOMER_EMAIL_SEND_RUN_NOW))) {
                unset($menuSteps['frequency']);
            }
            $this->tpl_form_vars = array(
                'email_templates' => EtsAbancartEmailTemplate::getTemplates(null, 'customer', null, $this->context),
                'lead_forms' => EtsAbancartForm::getAllForms(false, true),
                'maxSizeUpload' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'baseUri' => __PS_BASE_URI__,
                'field_types' => EtsAbancartField::getInstance()->getFieldType(),
                'module_dir' => _PS_MODULE_DIR_ . $this->module->name,
                'is17Ac' => $this->module->is17,
                'menus' => $menuSteps,
                'image_url' => $this->context->shop->getBaseURL(true) . 'img/' . $this->module->name . '/img/',
                'short_codes' => EtsAbancartDefines::getInstance()->getFields('short_codes'),
                //'send_email_now' => $object->send_email_now,
                'enabled' => $object->enabled,
                'emailTimingOption' => $emailTimingOption
            );
            $this->toJson(array(
                'html' => $this->renderForm(),
            ));
        }
    }
}