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

if (!class_exists('AdminEtsACCampaignController'))
    require_once(dirname(__FILE__) . '/AdminEtsACCampaignController.php');
if (!class_exists('AdminEtsACFormController'))
    require_once(dirname(__FILE__) . '/AdminEtsACFormController.php');

class AdminEtsACReminderEmailController extends AdminEtsACFormController
{
    /**
     * @var AdminEtsACCampaignController
     */
    public $campaign;
    public $type = 'email';
    public $currentLink;
    public $toolbar_btn = array();
    public $id_campaign = 0;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'ets_abancart_reminder';
        $this->className = 'EtsAbancartReminder';
        $this->list_id = $this->table;

        $this->show_form_cancel_button = false;
        $this->lang = true;
        $this->_redirect = false;
        $this->list_no_link = true;
        $this->_orderBy = 'time_sec';
        $this->_orderWay = 'ASC';

        $this->addRowAction('edit');
        $this->addRowAction('viewtracking');
        $this->addRowAction('delete');

        parent::__construct();
        $this->tpl_folder = 'common/';
        $this->override_folder = 'common/';
        $this->base_tpl_view = 'email_campaign_view.tpl';
        $displayTime = EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL || EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER;
        $this->_select = '
            (86400*IFNULL(a.day, 0) + 3600*IFNULL(a.hour, 0) + 60*IFNULL(a.minute, 0) + IFNULL(a.second, 0)) as `time_sec`
            , (@rank:=@rank + ' . (isset($this->context->cookie->{$this->list_id . '_start'}) && $this->context->cookie->{$this->list_id . '_start'} ? (int)$this->context->cookie->{$this->list_id . '_start'} : 0) . ' + 1) as `index`
            , SUM(IF(ac.campaign_type!=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' AND ac.campaign_type!=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER) . '\', dt.number_of_displayed, at.total_execute_times)) as `execute_times`
            , SUM(IF(at.delivered=1, 1, NULL)) as `success`
            , SUM(IF(at.delivered=0, 1, NULL)) as `failed`
            , SUM(IF(at.read > 0, 1, NULL)) as `read`
            , qu.`queue`
            , SUM(IF(ic.id_ets_abancart_reminder > 0, 1, 0)) as `total_index`
            , SUM(IF(at.id_ets_abancart_reminder > 0, 1, 0)) as `total_tracking`
            , ac.campaign_type
            , "--" as status
        ';

        if ($displayTime) {
            $this->_select .= '';
        }

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_tracking` at ON (at.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` dt ON (dt.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = a.id_ets_abancart_campaign)
            LEFT JOIN (
                SELECT SUM(IF(ic2.id_ets_abancart_reminder > 0, 1, 0)) as `total_index`, ic2.id_ets_abancart_reminder
                FROM `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ic2
                GROUP BY ic2.id_ets_abancart_reminder
            ) ic ON (ic.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
            LEFT JOIN ( 
                SELECT SUM(IF(qu2.id_ets_abancart_reminder > 0, 1, NULL)) as `queue`, qu2.id_ets_abancart_reminder
                FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` qu2 
                GROUP BY qu2.id_ets_abancart_reminder
            ) qu ON (qu.id_ets_abancart_reminder = at.id_ets_abancart_reminder)
            , (SELECT @rank:=0) y
        ';

        $this->_where = 'AND a.deleted = 0';
        $this->_group = 'GROUP BY a.id_ets_abancart_reminder';

        $this->campaign = new AdminEtsACCampaignController($this->type);
        $this->campaign->tabAccess = $this->tabAccess;
        $this->context->controller = $this;
        $this->id_campaign = (int)Tools::getValue('id_ets_abancart_campaign');
        $this->campaign->object = new EtsAbancartCampaign($this->id_campaign);
        if ($this->id_campaign) {
            $this->_where .= ' AND a.' . pSQL($this->campaign->identifier) . '=' . (int)$this->id_campaign;
        }

        $this->fields_list = $this->getFieldsList();
    }

    public function displayExecuteTimes($execute_times, $tr)
    {
        return $this->campaign->displayExecuteTimes($execute_times, $tr);
    }

    public function displayReminderExecuteTimes($execute_times, $tr)
    {
        return $this->displayExecuteTimes($execute_times, $tr);
    }

    public function getFieldsList()
    {
        if (!isset($this->campaign->object))
            return [];

        $displayTime = trim($this->type) == EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL || trim($this->type) == EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER;

        return array_merge(
            [
                'index' => array(
                    'title' => $this->l('Order', 'AdminEtsACReminderEmailController'),
                    'type' => 'int',
                    'search' => false,
                    'orderby' => false,
                    'class' => 'fixed-width-xs center',
                    'callback' => 'printReminder',
                ),
                'id_ets_abancart_reminder' => array(
                    'title' => $this->l('ID', 'AdminEtsACReminderEmailController'),
                    'type' => 'int',
                    'class' => 'fixed-width-xs center',
                    'filter_key' => 'a!id_ets_abancart_reminder',
                ),
                'title' => array(
                    'title' => $displayTime ? $this->l('Email subject', 'AdminEtsACReminderEmailController') : $this->l('Title', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'filter_key' => 'b!title',
                ),
                'execute_times' => array(
                    'title' => $displayTime ? $this->l('Execute times', 'AdminEtsACReminderEmailController') : $this->l('Display times', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'havingFilter' => true,
                    'search' => false,
                    'class' => 'fixed-width-lg',
                    'callback' => 'displayReminderExecuteTimes',
                ),
            ],
            $this->getListFrequency(),
            [
                'discount_option' => array(
                    'title' => $this->l('Discount', 'AdminEtsACReminderEmailController'),
                    'type' => 'select',
                    'list' => array(
                        'no' => $this->l('No discount', 'AdminEtsACReminderEmailController'),
                        'fixed' => $this->l('Fixed discount code', 'AdminEtsACReminderEmailController'),
                        'auto' => $this->l('Generate discount code automatically', 'AdminEtsACReminderEmailController'),
                    ),
                    'filter_key' => 'a!discount_option',
                    'class' => 'fixed-width-lg',
                    'callback' => 'discountOption',
                ),
                'enabled' => array(
                    'title' => $this->l('Status', 'AdminEtsACReminderEmailController'),
                    'type' => 'select',
                    'list' => EtsAbancartReminderForm::getInstance()->getReminderStatusOptions($this->campaign->object->email_timing_option, true),
                    'orderby' => false,
                    'filter_key' => 'a!enabled',
                    'class' => 'fixed-width-lg',
                    'callback' => 'displayReminderStatus',
                    'remove_onclick' => true
                ),
            ]
        );
    }

    public function displayReminderStatus($enabled, $tr)
    {
        $campaignStatus = 0;
        if (!$this->campaign->object->enabled)
            $campaignStatus = EtsAbancartCampaign::CAMPAIGN_STATUS_DISABLED;
        elseif (trim($this->campaign->object->available_to) !== '' && strtotime($this->campaign->object->available_to) < time())
            $campaignStatus = EtsAbancartCampaign::CAMPAIGN_STATUS_EXPIRED;
        elseif (trim($this->campaign->object->available_from) !== '' && strtotime($this->campaign->object->available_from) > time())
            $campaignStatus = EtsAbancartCampaign::CAMPAIGN_STATUS_A_WAITING;
        $this->context->smarty->assign([
            'enabled' => $enabled,
            'campaignStatus' => $campaignStatus,
            'email_timing_option' => $this->campaign->object->email_timing_option,
            'href' => self::$currentIndex . '&id_ets_abancart_campaign=' . $this->campaign->object->id . '&id_ets_abancart_reminder=' . (int)$tr['id_ets_abancart_reminder'] . '&action=reminderStatus&status' . $this->list_id . '&token=' . $this->token,
        ]);

        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/common/status.tpl');
    }

    static $delay_popup_based_on;

    public function getListFrequency()
    {
        $fields_list = [];
        if (!isset($this->campaign->object))
            return $fields_list;
        switch ($this->type) {
            case 'email':
            case 'customer':
                if ($this->campaign->object->email_timing_option == EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME) {
                    $fields_list += [
                        'schedule_time' => array(
                            'title' => $this->l('Schedule time', 'AdminEtsACReminderEmailController'),
                            'type' => 'datetime',
                            'filter_key' => 'a!schedule_time',
                            'class' => 'fixed-width-lg',
                        ),
                    ];
                } elseif ($this->campaign->object->email_timing_option != EtsAbancartReminder::CUSTOMER_EMAIL_SEND_RUN_NOW) {
                    $fields_list += [
                        'day' => array(
                            'title' => $this->l('Day(s)', 'AdminEtsACReminderEmailController'),
                            'type' => 'text',
                            'filter_key' => 'a!day',
                            'class' => 'center',
                            'form_group_class' => 'width_200'
                        ),
                        'hour' => array(
                            'title' => $this->l('Hour(s)', 'AdminEtsACReminderEmailController'),
                            'type' => 'text',
                            'filter_key' => 'a!hour',
                            'class' => 'center',
                            'form_group_class' => 'width_200'
                        ),
                    ];
                }
                break;
            case 'bar':
            case 'browser':
            case 'popup':
                if (!in_array(trim($this->type), [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER]) && $this->campaign->object->has_product_in_cart == EtsAbancartCampaign::HAS_SHOPPING_CART_YES) {
                    if (!self::$delay_popup_based_on) {
                        self::$delay_popup_based_on = [
                            EtsAbancartReminder::DELAY_PAGE_LOAD => $this->l('Page loading action', 'AdminEtsACReminderEmailController'),
                            EtsAbancartReminder::DELAY_CART_CREATION_TIME => $this->l('Cart creation time', 'AdminEtsACReminderEmailController'),
                        ];
                    }
                    $fields_list['delay_popup_based_on'] = array(
                        'title' => $this->l('Delay display based on', 'AdminEtsACReminderEmailController'),
                        'type' => 'select',
                        'list' => self::$delay_popup_based_on,
                        'filter_key' => 'a!delay_popup_based_on',
                        'class' => 'center',
                        'form_group_class' => 'width_200',
                        'callback' => 'displayDelayPopupBasedOn'
                    );
                }
                $fields_list['minute'] = array(
                    'title' => $this->l('Delay minute(s)', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'filter_key' => 'a!minute',
                    'class' => 'center',
                    'form_group_class' => 'width_200'
                );
                $fields_list['second'] = array(
                    'title' => $this->l('Delay second(s)', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'filter_key' => 'a!second',
                    'class' => 'center',
                    'form_group_class' => 'width_200'
                );
                if (!in_array(trim($this->type), [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER, EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER])) {
                    $fields_list['redisplay'] = [
                        'title' => $this->l('Redisplay after (min(s))', 'AdminEtsACReminderEmailController'),
                        'type' => 'text',
                        'filter_key' => 'a!redisplay',
                        'class' => 'center',
                        'form_group_class' => 'width_200'
                    ];
                }
                break;
        }
        return $fields_list;
    }

    public function displayDelayPopupBasedOn($delay_popup_based_on)
    {
        return isset(self::$delay_popup_based_on[$delay_popup_based_on]) ? self::$delay_popup_based_on[$delay_popup_based_on] : null;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme); // TODO: Change the autogenerated stub
        $this->addJS(array(
            _PS_JS_DIR_ . 'admin/tinymce.inc.js',
            _PS_JS_DIR_ . 'tiny_mce/tiny_mce.js',
            _PS_JS_DIR_ . 'jquery/plugins/autocomplete/jquery.autocomplete.js',
            $this->mPath . 'views/js/chart.admin.js',
        ));
        $this->addJqueryPlugin('colorpicker');
    }

    public function init()
    {
        $this->campaign->init();
        parent::init();

        $this->currentLink = self::$currentIndex;

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Reminders', 'AdminEtsACReminderEmailController'),
            ),
            'submit' => array(
                'title' => $this->l('Save', 'AdminEtsACReminderEmailController'),
            ),
            'input' => $this->getFields(),
        );

    }

    public function getFieldsForm()
    {
        return [
            'hidden_reminder_id' => [
                'name' => 'hidden_reminder_id',
                'type' => 'hidden',
                'label' => $this->l('Reminder', 'AdminEtsACReminderEmailController'),
                'default_value' => (int)Tools::getValue($this->identifier),
            ],
            'id_ets_abancart_email_template' => array(
                'name' => 'id_ets_abancart_email_template',
                'label' => $this->l('Email templates', 'AdminEtsACReminderEmailController'),
                'type' => 'hidden',
                'default' => 0,
            ),
            'title' => array(
                'name' => 'title',
                'label' => $this->l('Subject', 'AdminEtsACReminderEmailController'),
                'type' => 'text',
                'lang' => true,
                'required' => true,
                'validate' => 'isMailSubject',
                'form_group_class' => 'abancart form_message required isMailSubject'
            ),
            'content' => array(
                'name' => 'content',
                'label' => $this->l('Email content', 'AdminEtsACReminderEmailController'),
                'type' => 'textarea',
                'autoload_rte' => true,
                'lang' => true,
                'required' => true,
                'desc_type' => $this->type,
                'validate' => 'isCleanHtml',
                'form_group_class' => 'abancart content form_message isCleanHtml required'
            ),
        ];
    }

    public function getFields()
    {
        return array_merge(
            [
                'id_ets_abancart_campaign' => array(
                    'name' => 'id_ets_abancart_campaign',
                    'label' => $this->l('Campaign ID', 'AdminEtsACReminderEmailController'),
                    'type' => 'hidden',
                    'default_value' => $this->campaign->object->id,
                ),
            ],
            $this->getConfigFrequency(),
            $this->getConfigCartRule(),
            $this->getFieldsForm(),
            $this->getConfirmInformationForm()
        );
    }

    public function getConfirmInformationForm()
    {
        switch ($this->type) {
            case 'popup':
                $p = [
                    $this->l('Save without displaying popup', 'AdminEtsACReminderEmailController'),
                    $this->l('Save and display the popup immediately', 'AdminEtsACReminderEmailController')
                ];
                break;
            case 'bar':
                $p = [
                    $this->l('Save without displaying highlight bar', 'AdminEtsACReminderEmailController'),
                    $this->l('Save and display the highlight bar immediately', 'AdminEtsACReminderEmailController')
                ];
                break;
            case 'browser':
                $p = [
                    $this->l('Save without displaying web push notification', 'AdminEtsACReminderEmailController'),
                    $this->l('Save and display the web push notification immediately', 'AdminEtsACReminderEmailController')
                ];
                break;
            default:
                $p = [
                    $this->l('Save without sending email', 'AdminEtsACReminderEmailController'),
                    $this->l('Save and send email immediately', 'AdminEtsACReminderEmailController')
                ];
                break;
        }
        return array(
            'enabled' => array(
                'type' => 'radios',
                'name' => 'enabled',
                'label' => $this->type == 'email' ? $this->l('Send email now?', 'AdminEtsACReminderEmailController') : $this->l('Status', 'AdminEtsACReminderEmailController'),
                'default' => 0,
                'options' => array(
                    'query' => array(
                        array(
                            'id_option' => EtsAbancartReminder::REMINDER_STATUS_DRAFT,
                            'name' => $this->l('Draft', 'AdminEtsACReminderEmailController'),
                            'class' => 'enabled_no',
                            'p' => $p[0]
                        ),
                        array(
                            'id_option' => EtsAbancartReminder::REMINDER_STATUS_RUNNING,
                            'name' => $this->l('Running', 'AdminEtsACReminderEmailController'),
                            'class' => 'enabled_yes',
                            'p' => $p[1]
                        ),
                    ),
                    'id' => 'id_option',
                    'name' => 'name',
                ),
                'default_value' => EtsAbancartReminder::REMINDER_STATUS_RUNNING,
                'form_group_class' => 'abancart form_confirm_information enabled'
            )
        );
    }

    public function getConfigFrequency()
    {
        $fields = [];
        switch ($this->type) {
            case 'email':
            case 'customer':
                $sendRepeatOptions = array(EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION, EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN);
                $values = array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Yes', 'AdminEtsACReminderEmailController')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('No', 'AdminEtsACReminderEmailController')
                    ),
                );
                $fields += [
                    'label_email_timing_option' => array(
                        'name' => 'label_email_timing_option',
                        'label' => '',
                        'type' => 'text',
                        'form_group_class' => 'abancart form_frequency ets_ac_label_email_timing_option',
                        'list_title' => array(
                            'register' => $this->l('How long after registering?', 'AdminEtsACReminderEmailController'),
                            'order' => $this->l('How long after completing order?', 'AdminEtsACReminderEmailController'),
                            'last_login' => $this->l('How long since the last visit time?', 'AdminEtsACReminderEmailController'),
                            'register_newsletter' => $this->l('How long after registering newsletter?', 'AdminEtsACReminderEmailController'),
                        )
                    ),
                    'day' => array(
                        'name' => 'day',
                        'label' => $this->l('Days', 'AdminEtsACReminderEmailController'),
                        'col' => '6',
                        'type' => 'text',
                        'validate' => 'isUnsignedFloat',
                        'suffix' => $this->l('day(s)', 'AdminEtsACReminderEmailController'),
                        'form_group_class' => 'abancart form_frequency width_200 isUnsignedFloat ets_ac_customer_email_register_order',
                    ),
                    'hour' => array(
                        'name' => 'hour',
                        'label' => $this->l('Hours', 'AdminEtsACReminderEmailController'),
                        'col' => '6',
                        'type' => 'text',
                        'validate' => 'isUnsignedFloat',
                        'suffix' => $this->l('hour(s)', 'AdminEtsACReminderEmailController'),
                        'form_group_class' => 'abancart form_frequency width_200 isUnsignedFloat ets_ac_customer_email_register_order',
                    ),
                ];

                if ($this->campaign->object) {
                    if (in_array($this->campaign->object->email_timing_option, $sendRepeatOptions)) {
                        $isOrderTiming = $this->campaign->object->email_timing_option == EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION;
                        $fields['send_repeat_email'] = array(
                            'name' => 'send_repeat_email',
                            'label' => $isOrderTiming ? $this->l('Send repeat email for every order', 'AdminEtsACReminderEmailController') : $this->l('Send repeat email for every time customer visit', 'AdminEtsACReminderEmailController'),
                            'type' => 'switch',
                            'default_value' => 0,
                            'values' => $values,
                            'form_group_class' => 'abancart form_frequency ets_ac_send_repeat_email',
                            'desc' => $isOrderTiming ? $this->l('If this option is disabled, email will be sent for the first order only', 'AdminEtsACReminderEmailController') : $this->l('If this option is disabled, email will be sent for the first time customer visit only', 'AdminEtsACReminderEmailController'),
                        );
                    }
                    switch ($this->campaign->object->email_timing_option) {
                        case EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_REGISTRATION:
                            $fields['day']['desc'] = $this->l('After X day(s) since the customer registered a new account, the reminder email will be sent. You can enter decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                            $fields['hour']['desc'] = $this->l('After X hour(s) since the customer registered a new account, the reminder email will be sent. You can enter decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                            break;
                        case EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION:
                            $fields['day']['desc'] = $this->l('After X day(s) since the order is completed, the reminder email will be sent. You can enter decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                            $fields['hour']['desc'] = $this->l('After X hour(s) since the order is completed, the reminder email will be sent. You can enter decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                            break;
                        case EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER:
                            $fields['day']['desc'] = $this->l('After X day(s) since a customer subscribed to the newsletter, the reminder email will be sent. You can enter decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                            $fields['hour']['desc'] = $this->l('After X hour(s) since a customer subscribed to the newsletter, the reminder email will be sent. You can enter decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                            break;
                        case EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN:
                            $fields['day']['desc'] = $this->l('After X day(s) since a customer\'s last visit time, the reminder email will be sent. You can enter decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                            $fields['hour']['desc'] = $this->l('After X hour(s) since a customer\'s last visit time, the reminder email will be sent. You can enter decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                            break;
                    }
                }
                $fields['customer_email_schedule_time'] = array(
                    'name' => 'customer_email_schedule_time',
                    'label' => $this->l('Schedule time', 'AdminEtsACReminderEmailController'),
                    'type' => 'datetime',
                    'col' => 4,
                    'to' => 'customer_email_schedule_time',
                    'form_group_class' => 'abancart form_frequency ets_ac_customer_email_schedule_time',
                );
                break;
            case 'bar':
            case 'browser':
            case 'popup':
                if (!in_array(trim($this->type), [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER]) && $this->campaign->object->has_product_in_cart == EtsAbancartCampaign::HAS_SHOPPING_CART_YES) {
                    $fields['delay_popup_based_on'] = array(
                        'name' => 'delay_popup_based_on',
                        'label' => $this->l('Delay display based on', 'AdminEtsACReminderEmailController'),
                        'col' => '6',
                        'type' => 'radio',
                        'values' => [
                            [
                                'id' => 'page_load',
                                'value' => 0,
                                'label' => $this->l('Page loading action'),
                            ],
                            [
                                'id' => 'cart_creation_time',
                                'value' => 1,
                                'label' => $this->l('Cart creation time'),
                            ],
                        ],
                        'desc' => $this->l('The delay display time will be calculated when a customer reloads a web page or adds a product into shopping cart.', 'AdminEtsACReminderEmailController'),
                        'form_group_class' => 'abancart form_frequency width_200 isUnsignedFloat'
                    );
                    if ($this->type == EtsAbancartCampaign::CAMPAIGN_TYPE_BAR) {
                        $fields['delay_popup_based_on']['desc'] = $this->l('The delay display time will be calculated when a customer reloads a web page or adds a product into shopping cart.', 'AdminEtsACReminderEmailController');
                    } elseif ($this->type == EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER) {
                        $fields['delay_popup_based_on']['desc'] = $this->l('The delay display time will be calculated when a customer reloads a web page or adds a product into shopping cart.', 'AdminEtsACReminderEmailController');
                    }
                }
                $fields['minute'] = array(
                    'name' => 'minute',
                    'label' => $this->l('Delay minutes', 'AdminEtsACReminderEmailController'),
                    'col' => '6',
                    'type' => 'text',
                    'validate' => 'isUnsignedFloat',
                    'suffix' => $this->l('min(s)', 'AdminEtsACReminderEmailController'),
                    'form_group_class' => 'abancart form_frequency width_200 isUnsignedFloat',
                    'desc' => $this->l('After X minute(s) since a shopping cart became abandoned, the popup will be displayed. Accept decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController')
                );
                $fields['second'] = array(
                    'name' => 'second',
                    'label' => $this->l('Delay seconds', 'AdminEtsACReminderEmailController'),
                    'col' => '6',
                    'type' => 'text',
                    'validate' => 'isUnsignedInt',
                    'suffix' => $this->l('second(s)', 'AdminEtsACReminderEmailController'),
                    'form_group_class' => 'abancart form_frequency width_200 isUnsignedInt',
                    'desc' => $this->l('After X second(s) since a shopping cart became abandoned, the popup will be displayed. Accept integer values such as 15, 30, 60, etc.', 'AdminEtsACReminderEmailController')
                );
                if ($this->type == EtsAbancartCampaign::CAMPAIGN_TYPE_BAR) {
                    $fields['minute']['desc'] = $this->l('After X minute(s) since a shopping cart became abandoned, the highlight bar will be displayed. Accept decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                    $fields['second']['desc'] = $this->l('After X second(s) since a shopping cart became abandoned, the highlight bar will be displayed. Accept integer values such as 15, 30, 60, etc.', 'AdminEtsACReminderEmailController');
                } elseif ($this->type == EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER) {
                    $fields['minute']['desc'] = $this->l('After X minute(s) since a shopping cart became abandoned, the web push notification will be displayed. Accept decimal values such as 12, 4.5, etc.', 'AdminEtsACReminderEmailController');
                    $fields['second']['desc'] = $this->l('After X second(s) since a shopping cart became abandoned, the web push notification will be displayed. Accept integer values such as 15, 30, 60, etc.', 'AdminEtsACReminderEmailController');
                }
                if (!in_array(trim($this->type), [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER, EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER])) {
                    $fields['redisplay'] = array(
                        'name' => 'redisplay',
                        'label' => $this->l('Redisplay after (min(s))', 'AdminEtsACReminderEmailController'),
                        'col' => '6',
                        'type' => 'text',
                        'suffix' => $this->l('min(s)', 'AdminEtsACReminderEmailController'),
                        'validate' => 'isUnsignedFloat',
                        'desc' => $this->l('After X minute(s) since a customer closed the reminder popup or reloaded web page or added a product into shopping cart, the popup will be displayed again. If you set redisplay time to "0", the popup will not be displayed again.', 'AdminEtsACReminderEmailController'),
                        'form_group_class' => 'abancart form_frequency width_200 isUnsignedFloat'
                    );
                    if ($this->type == EtsAbancartCampaign::CAMPAIGN_TYPE_BAR) {
                        $fields['redisplay']['desc'] = $this->l('After X minute(s) since a customer closed the highlight bar or reloaded web page or added a product into shopping cart, the highlight bar will be displayed again. If you set redisplay time to "0", the highlight bar will not be displayed again.', 'AdminEtsACReminderEmailController');
                    }
                }
                break;
        }
        if ($this->type == 'email') {
            $extraCustomerConfigs = array('email_timing_option', 'label_email_timing_option', 'send_repeat_email', 'customer_email_schedule_time');
            foreach ($extraCustomerConfigs as $item) {
                if (isset($fields[$item])) {
                    unset($fields[$item]);
                }
            }
        }
        return $fields;
    }

    public function getConfigCartRule()
    {
        $discount_options = array(
            array(
                'id_option' => 'no',
                'name' => $this->l('No discount', 'AdminEtsACReminderEmailController')
            ),
            array(
                'id_option' => 'fixed',
                'name' => $this->l('Fixed discount code', 'AdminEtsACReminderEmailController'),
                'cart_rule_link' => $this->context->link->getAdminLink('AdminCartRules')
            ),
        );
        $fields = [];
        if ($this->campaign->object->campaign_type == EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL ||
            $this->campaign->object->campaign_type == EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER && $this->campaign->object->email_timing_option !== EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER ||
            $this->campaign->object->has_product_in_cart == EtsAbancartCampaign::HAS_SHOPPING_CART_YES
        ) {
            $discount_options[] = array(
                'id_option' => 'auto',
                'name' => $this->l('Generate discount code automatically', 'AdminEtsACReminderEmailController')
            );
            $fields = [
                'quantity' => array(
                    'name' => 'quantity',
                    'label' => $this->l('Total available', 'AdminEtsACReminderEmailController'),
                    'hint' => $this->l('The cart rule will be applied to the first', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'col' => '2',
                    'validate' => 'isCleanHtml',
                    'form_group_class' => 'abancart form_discount discount_option auto ets_ac_discount_qty',
                    'default_value' => 1,
                ),
                'quantity_per_user' => array(
                    'name' => 'quantity_per_user',
                    'label' => $this->l('Total available for each user', 'AdminEtsACReminderEmailController'),
                    'hint' => $this->l('A customer will only be able to use the cart rule', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'col' => '2',
                    'validate' => 'isCleanHtml',
                    'form_group_class' => 'abancart form_discount discount_option auto ets_ac_discount_qty',
                    'default_value' => 1,
                ),
                'discount_code' => array(
                    'name' => 'discount_code',
                    'label' => $this->l('Discount code', 'AdminEtsACReminderEmailController'),
                    'hint' => $this->l('Discount code', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'col' => '2',
                    'required' => true,
                    'validate' => 'isCleanHtml',
                    'form_group_class' => 'abancart form_discount discount_option fixed isCleanHtml required',
                ),
                'free_shipping' => array(
                    'name' => 'free_shipping',
                    'label' => $this->l('Free shipping', 'AdminEtsACReminderEmailController'),
                    'type' => 'switch',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'AdminEtsACReminderEmailController')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'AdminEtsACReminderEmailController')
                        ),
                    ),
                    'form_group_class' => 'abancart form_discount discount_option auto is_parent2',
                ),
                'apply_discount' => array(
                    'name' => 'apply_discount',
                    'label' => $this->l('Apply a discount', 'AdminEtsACReminderEmailController'),
                    'type' => 'radios',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'percent',
                                'name' => $this->l('Percentage (%)', 'AdminEtsACReminderEmailController')
                            ),
                            array(
                                'id_option' => 'amount',
                                'name' => $this->l('Amount', 'AdminEtsACReminderEmailController')
                            ),
                            array(
                                'id_option' => 'off',
                                'name' => $this->l('None', 'AdminEtsACReminderEmailController')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default_value' => 'off',
                    'form_group_class' => 'abancart form_discount discount_option auto apply_discount is_parent2',
                ),
                'reduction_amount' => array(
                    'name' => 'reduction_amount',
                    'label' => $this->l('Amount', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'default_value' => '0',
                    'col' => '4',
                    'currencies' => Currency::getCurrencies(),
                    'tax' => array(
                        array(
                            'id_option' => 0,
                            'name' => $this->l('Tax excluded', 'AdminEtsACReminderEmailController')
                        ),
                        array(
                            'id_option' => 1,
                            'name' => $this->l('Tax included', 'AdminEtsACReminderEmailController')
                        ),
                    ),
                    'required' => true,
                    'validate' => 'isUnsignedFloat',
                    'form_group_class' => 'abancart form_discount discount_option auto apply_discount amount isUnsignedFloat required',
                ),
                'discount_name' => array(
                    'name' => 'discount_name',
                    'label' => $this->l('Discount name', 'AdminEtsACReminderEmailController'),
                    'hint' => $this->l('This will be displayed in the cart summary, as well as on the invoice.', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'lang' => true,
                    'required' => true,
                    'col' => 6,
                    'validate' => 'isCleanHtml',
                    'form_group_class' => 'abancart form_discount discount_option auto isCleanHtml required'
                ),
                'reduction_percent' => array(
                    'name' => 'reduction_percent',
                    'label' => $this->l('Discount percentage', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'suffix' => '%',
                    'col' => '2',
                    'required' => true,
                    'validate' => 'isPercentage',
                    'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent isPercentage required',
                ),
                'apply_discount_to' => array(
                    'name' => 'apply_discount_to',
                    'label' => $this->l('Apply a discount to', 'AdminEtsACReminderEmailController'),
                    'type' => 'radios',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'order',
                                'name' => $this->l('Order (without shipping)', 'AdminEtsACReminderEmailController'),
                            ),
                            array(
                                'id_option' => 'specific',
                                'name' => $this->l('Specific product', 'AdminEtsACReminderEmailController')
                            ),
                            array(
                                'id_option' => 'cheapest',
                                'name' => $this->l('Cheapest product', 'AdminEtsACReminderEmailController')
                            ),
                            array(
                                'id_option' => 'selection',
                                'name' => $this->l('Selected product(s)', 'AdminEtsACReminderEmailController')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default_value' => 'order',
                    'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent amount ets_ac_apply_discount',
                ),
                'reduction_product' => array(
                    'name' => 'reduction_product',
                    'label' => $this->l('Product', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'col' => '2',
                    'specific_product' => true,
                    'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent amount ets_ac_specific_product_group',
                ),
                'selected_product' => array(
                    'name' => 'selected_product',
                    'label' => $this->l('Search product', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'col' => '2',
                    'search_product' => true,
                    'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent ets_ac_selected_product_group',
                ),
                'reduction_exclude_special' => array(
                    'name' => 'reduction_exclude_special',
                    'label' => $this->l('Exclude discounted products', 'AdminEtsACReminderEmailController'),
                    'type' => 'switch',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'AdminEtsACReminderEmailController')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'AdminEtsACReminderEmailController')
                        ),
                    ),
                    'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent',
                ),
                'free_gift' => array(
                    'name' => 'free_gift',
                    'label' => $this->l('Send a free gift', 'AdminEtsACReminderEmailController'),
                    'type' => 'switch',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'AdminEtsACReminderEmailController')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'AdminEtsACReminderEmailController')
                        ),
                    ),
                    'form_group_class' => 'abancart form_discount discount_option auto',
                ),
                'product_gift' => array(
                    'name' => 'product_gift',
                    'label' => $this->l('Search a product', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'suffix' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'ets_abandonedcart/views/templates/hook/icon_search.tpl'),
                    'col' => '2',
                    'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent amount off ets_ac_gift_product_filter_group',
                ),
                'id_currency' => array(
                    'name' => 'id_currency',
                    'label' => $this->l('Currency ID', 'AdminEtsACReminderEmailController'),
                    'type' => 'select',
                    'options' => array(
                        'query' => Currency::getCurrencies(),
                        'id' => 'id_currency',
                        'name' => 'name',
                    ),
                    'default_value' => $this->context->currency->id,
                    'form_group_class' => 'abancart form_discount'
                ),
                'reduction_tax' => array(
                    'name' => 'reduction_tax',
                    'label' => $this->l(''),
                    'type' => 'select',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 0,
                                'name' => $this->l('Tax excluded', 'AdminEtsACReminderEmailController')
                            ),
                            array(
                                'id_option' => 1,
                                'name' => $this->l('Tax included', 'AdminEtsACReminderEmailController')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default_value' => '0',
                    'form_group_class' => 'abancart form_discount'
                ),
                'apply_discount_in' => array(
                    'name' => 'apply_discount_in',
                    'label' => $this->l('Discount availability', 'AdminEtsACReminderEmailController'),
                    'hint' => $this->l('After you create a discount, you can define additional availability settings to determine when and through which sales methods the discount is applicable', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'required' => 'true',
                    'suffix' => $this->l('days', 'AdminEtsACReminderEmailController'),
                    'validate' => 'isUnsignedInt',
                    'col' => '2',
                    'default_value' => '1',
                    'form_group_class' => 'abancart form_discount discount_option auto apply_discount is_parent2 isUnsignedInt required',
                ),
                'allow_multi_discount' => array(
                    'name' => 'allow_multi_discount',
                    'label' => $this->l('Can use with other voucher in the same shopping cart?', 'AdminEtsACReminderEmailController'),
                    'type' => 'switch',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'enable_multi_discount_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'AdminEtsACReminderEmailController')
                        ),
                        array(
                            'id' => 'enable_multi_discount_off',
                            'value' => 0,
                            'label' => $this->l('No', 'AdminEtsACReminderEmailController')
                        ),
                    ),
                    'form_group_class' => 'abancart form_discount discount_option fixed auto ets_ac_discount_qty',
                )
            ];
        } else {
            $fields = [
                'discount_code' => array(
                    'name' => 'discount_code',
                    'label' => $this->l('Discount code', 'AdminEtsACReminderEmailController'),
                    'hint' => $this->l('Discount code', 'AdminEtsACReminderEmailController'),
                    'type' => 'text',
                    'col' => '2',
                    'required' => true,
                    'validate' => 'isCleanHtml',
                    'form_group_class' => 'abancart form_discount discount_option fixed isCleanHtml required',
                ),
            ];
        }
        $fields = array_merge(
            [
                'discount_option' => array(
                    'name' => 'discount_option',
                    'label' => $this->l('Discount options', 'AdminEtsACReminderEmailController'),
                    'type' => 'radios',
                    'options' => array(
                        'query' => $discount_options,
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default_value' => 'no',
                    'form_group_class' => 'abancart form_discount discount_option is_parent1',
                )
            ],
            $fields
        );
        switch ($this->type) {
            case 'bar':
            case 'popup':
                $fields['enable_count_down_clock'] = array(
                    'name' => 'enable_count_down_clock',
                    'label' => $this->l('Enable discount countdown clock', 'AdminEtsACReminderEmailController'),
                    'type' => 'switch',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'AdminEtsACReminderEmailController')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'AdminEtsACReminderEmailController')
                        ),
                    ),
                    'form_group_class' => 'abancart form_discount discount_option fixed auto'
                );
                break;
        }

        return $fields;
    }

    public function setHelperDisplay(Helper $helper)
    {
        parent::setHelperDisplay($helper);

        $this->helper->currentIndex = $this->currentLink . ($this->id_campaign ? '&' . $this->campaign->identifier . '=' . (int)$this->id_campaign : '');
    }

    public function initToolbarTitle()
    {
        $this->toolbar_title = $this->l('Reminders', 'AdminEtsACReminderEmailController');
    }

    public function initToolbar()
    {
        parent::initToolbar();

        $this->toolbar_btn['new'] = array(
            'href' => self::$currentIndex . '&add' . $this->table . ($this->id_campaign ? '&' . $this->campaign->identifier . '=' . (int)$this->id_campaign : '') . '&token=' . $this->token . '&campaign_type=' . $this->type,
            'desc' => $this->l('Add new', 'AdminEtsACReminderEmailController'),
        );
    }

    public function initProcess()
    {
        parent::initProcess();

        if (((Tools::isSubmit('submitAdd' . $this->campaign->table) || Tools::isSubmit('submitAdd' . $this->campaign->table . 'AndStay')) && count($this->campaign->errors))
            || Tools::isSubmit('update' . $this->campaign->table)
            || Tools::isSubmit('add' . $this->campaign->table)
        ) {
            if (Tools::isSubmit('update' . $this->campaign->table))
                $this->display = 'edit_campaign';
            elseif (Tools::isSubmit('add' . $this->campaign->table))
                $this->display = 'add_campaign';
        } elseif (Tools::isSubmit('submitAdd' . $this->table) || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
            if ($this->id_object) {
                if ($this->access('edit')) {
                    $this->action = 'save';
                } else {
                    $this->errors[] = $this->l('You do not have permission to edit this.', 'AdminEtsACReminderEmailController');
                }
            } else {
                if ($this->access('add')) {
                    $this->action = 'save';
                } else {
                    $this->errors[] = $this->l('You do not have permission to add this.', 'AdminEtsACReminderEmailController');
                }
            }
        }
        if (!$this->display) {
            $this->display = 'list';
        }
    }

    public function initContent()
    {
        $this->getLanguages();
        $this->initToolbar();
        $this->initTabModuleList();
        $this->initPageHeaderToolbar();

        $this->campaign->token = $this->token;
        if ($this->display == 'list') {
            if (Tools::isSubmit('view' . $this->campaign->table)) {
                $this->content .= $this->renderView();
            } else {
                $this->campaign->toolbar_btn['new'] = array(
                    'href' => self::$currentIndex . '&add' . $this->campaign->table . '&token=' . $this->token,
                    'desc' => $this->l('Add new campaign', 'AdminEtsACReminderEmailController'),
                );
                $this->content .= $this->campaign->renderList();
            }

        } elseif ($this->display == 'edit_campaign' || $this->display == 'add_campaign') {
            $this->campaign->tpl_form_vars += [
                'nb_reminders' => (int)EtsAbancartCampaign::nbReminders($this->id_campaign),
                'href' => self::$currentIndex . '&add' . $this->table . '&id_ets_abancart_campaign=' . $this->id_campaign . '&token=' . $this->token,
                'id_campaign' => $this->id_campaign
            ];
            $this->content .= $this->campaign->renderForm();

            if ($this->campaign->object->id) {
                $this->content .= $this->initBlockList();
                if (isset($this->campaign->object->name[$this->default_form_language]) && $this->campaign->object->name[$this->default_form_language]) {
                    $this->page_header_toolbar_title = $this->l('Edit', 'AdminEtsACReminderEmailController') . ': ' . $this->campaign->object->name[$this->default_form_language];
                }
            } elseif ($this->display == 'add_campaign') {
                $this->page_header_toolbar_title = $this->l('Add new campaign', 'AdminEtsACReminderEmailController');
            }
        } else if ($this->display == 'add' || $this->display == 'edit') {
            $this->content .= $this->renderForm();
        }

        // Title page.
        if (null !== $this->page_header_toolbar_title)
            $this->context->smarty->assign(array(
                'title' => $this->page_header_toolbar_title
            ));

        // Admin content.
        $this->renderAdmin();
    }

    public function initBlockList()
    {
        $this->context->smarty->assign(array(
            'content' => $this->renderList()
        ));

        return $this->createTemplate('block.tpl')->fetch();
    }

    public function renderForm()
    {
        $this->loadObject(true);
        return parent::renderForm();
    }

    public function postProcess()
    {
        // Process Campaign.
        $this->campaign->postProcess();
        parent::postProcess();

        if (Tools::getValue('action') == 'initChart') {
            $times = ($times = Tools::getValue('time_series')) && Validate::isCleanHtml($times) ? $times : '';
            $reminder_filter = ($reminder_filter = Tools::getValue('reminder_filter')) && Validate::isCleanHtml($reminder_filter) ? $reminder_filter : '';
            $id_campaign = (int)Tools::getValue('id_campaign');
            $chartData = EtsAbancartReminderForm::getInstance()->getLineChartCampaign($times, $id_campaign, 0, 0, 0, 0, $reminder_filter, $this->errors);
            $has_error = count($this->errors) > 0;
            die(json_encode([
                'errors' => $has_error ? implode(PHP_EOL, $this->errors) : false,
                'line_chart' => $chartData
            ]));
        }

        if (Tools::isSubmit('exportCampaignTracking')) {
            $id_campaign = (int)Tools::getValue('id_ets_abancart_campaign');
            $filterTime = ($filterTime = Tools::getValue('filter_time')) && Validate::isCleanHtml($filterTime) ? $filterTime : '';
            $timeFrom = ($timeFrom = Tools::getValue('time_range_from')) && Validate::isDate($timeFrom) ? $timeFrom : '';
            $timeTo = ($timeTo = Tools::getValue('time_range_to')) && Validate::isDate($timeTo) ? $timeTo : '';
            EtsAbancartReminderForm::getInstance()->exportEmailSentToCsv($id_campaign, $filterTime, $timeFrom, $timeTo);
            die('1');
        }
        if (isset($this->campaign->errors) && is_array($this->campaign->errors)) {
            $this->errors = array_merge($this->errors, $this->campaign->errors);
        }
        if ((Tools::isSubmit('submitAdd' . $this->campaign->table . 'AndStay') || Tools::isSubmit('delete' . $this->campaign->table)) && isset($this->campaign->redirect_after) && $this->campaign->redirect_after) {
            $this->redirect_after = $this->campaign->redirect_after . '&token=' . $this->token;
        }
        if (Tools::isSubmit('validateStepForm')) {
            $errors = $this->validateStepForm();
            $has_error = count($errors) > 0;
            die(json_encode([
                'success' => !$has_error,
                'message' => $has_error ? $this->module->displayError($errors) : ''
            ]));
        }
    }

    public function ajaxProcessReminderStatus()
    {
        if (Tools::isSubmit('status' . $this->list_id)) {

            /** @var EtsAbancartReminder $object */
            $object = $this->loadObject();

            if (Validate::isLoadedObject($object)) {
                if (property_exists($object, 'enabled'))
                    $object->enabled = (int)Tools::getValue('enabled');
                if (!$object->update())
                    $this->errors[] = $this->l('An error occurred while updating the status.', 'AdminEtsACReminderEmailController');
            } else
                $this->errors[] = $this->l('An error occurred while updating the status for an object.', 'AdminEtsACReminderEmailController');

            $hasError = count($this->errors) > 0;
            $this->toJson(array(
                'hasError' => $hasError,
                'list' => $this->renderList(),
                'msg' => $hasError ? $this->module->displayError($this->errors) : $this->l('Update status successfully', 'AdminEtsACReminderEmailController'),
            ));
        }
    }

    public function processDelete()
    {
        /** @var EtsAbancartReminder $object */
        $object = $this->loadObject();

        if (Validate::isLoadedObject($object)) {
            if ($object->delete()) {
                $this->redirect_after = self::$currentIndex . '&conf=1&token=' . $this->token;
            }
        } else
            $this->errors[] = $this->l('An error occurred while deleting the object.', 'AdminEtsACReminderEmailController');

        return $object;
    }

    public function ajaxProcessRenderForm()
    {
        if ($this->access('edit')) {

            $this->tpl_form_vars = array(
                'email_templates' => EtsAbancartEmailTemplate::getTemplates(null, 'email', null, $this->context),
                'lead_forms' => EtsAbancartForm::getAllForms(false, true),
                'maxSizeUpload' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'baseUri' => __PS_BASE_URI__,
                'field_types' => EtsAbancartField::getInstance()->getFieldType(),
                'module_dir' => _PS_MODULE_DIR_ . $this->module->name,
                'is17Ac' => $this->module->is17,
                'menus' => EtsAbancartReminderForm::getInstance()->getReminderSteps(),
                'image_url' => $this->context->shop->getBaseURL(true) . 'img/' . $this->module->name . '/img/',
                'short_codes' => EtsAbancartDefines::getInstance()->getFields('short_codes'),
            );
            $this->toJson(array(
                'html' => $this->renderForm(),
            ));
        }
    }

    public function getFieldsValue($obj)
    {
        if ($obj instanceof EtsAbancartReminder) {

            parent::getFieldsValue($obj);

            if (!$obj->id) {
                $languages = Language::getLanguages(false);
                foreach ($this->fields_form as $fieldset) {
                    if (isset($fieldset['form']['input'])) {
                        foreach ($fieldset['form']['input'] as $input) {
                            if ((!isset($this->fields_value[$input['name']]) || !$this->fields_value[$input['name']]) && isset($input['default'])) {
                                if (isset($input['lang']) && $input['lang']) {
                                    $default = array();
                                    foreach ($languages as $lang) {
                                        $default[$lang['id_lang']] = $input['default'];
                                    }
                                    $this->fields_value[$input['name']] = $default;
                                } else {
                                    $this->fields_value[$input['name']] = $input['default'];
                                }
                            }
                        }
                    }
                }
            }

            if ($obj->reduction_product == 0)
                $this->fields_value['apply_discount_to'] = 'order';
            elseif ($obj->reduction_product == -1)
                $this->fields_value['apply_discount_to'] = 'cheapest';
            elseif ($obj->reduction_product == -2) {
                $this->fields_value['apply_discount_to'] = 'selection';
                $this->fields_value['selected_product_list'] = array();
                if ($obj->selected_product) {
                    $ids = explode(',', $obj->selected_product);
                    $this->fields_value['selected_product_list'] = EtsAbancartReminderForm::getInstance()->displayListProduct('selected_product', $ids, 'ets-ac-products-list-selected_product');
                }
            } elseif ($obj->reduction_product > 0) {
                $this->fields_value['apply_discount_to'] = 'specific';
                $p = new Product($obj->reduction_product, false, $this->context->language->id);
                $this->fields_value['specific_product_name'] = $p->name;
                $this->fields_value['specific_product_item'] = EtsAbancartReminderForm::getInstance()->displayListProduct('specific_product_item', array($p->id), 'ets-ac-products-list-reduction_product');
            }
            if ($obj->gift_product && ($product = new Product($obj->gift_product, false, $this->context->language->id)) && $product->id) {
                $this->fields_value['free_gift'] = 1;
                $this->fields_value['gift_product'] = $obj->gift_product;
                $this->fields_value['gift_product_attribute'] = $obj->gift_product_attribute ?: '';
                $productName = $product->name;
                if ($obj->gift_product_attribute && ($attrs = $product->getAttributeCombinationsById($obj->gift_product_attribute, $this->context->language->id))) {
                    foreach ($attrs as $item) {
                        $productName .= ' ' . $item['group_name'] . ' ' . $item['attribute_name'];
                    }
                }
                $this->fields_value['gift_product_name'] = $productName;
                $this->fields_value['gift_product_item'] = EtsAbancartReminderForm::getInstance()->displayListProduct('gift_product_item', array($obj->gift_product), 'ets-ac-products-list-product_gift', $productName);
            } else {
                $this->fields_value['free_gift'] = 0;
            }
            if ($obj->schedule_time) {
                $this->fields_value['customer_email_schedule_time'] = $obj->schedule_time;
            }

            return $this->fields_value;
        }
        return parent::getFieldsValue($obj);
    }

    public function ajaxProcessRenderList()
    {
        if ($this->access('edit')) {
            // Process list filtering
            if ($this->filter && $this->action != 'reset_filters') {
                $this->processFilter();
            }
            $this->toJson(array(
                'html' => $this->initBlockList()
            ));
        }
    }

    public function ajaxProcessSaveData()
    {
        if ($this->access('edit')) {
            $this->processSave();
            $jsonData = array(
                'errors' => ($hasError = count($this->errors) ? true : false) ? $this->module->displayError($this->errors) : false,
            );
            if (!$hasError) {
                $this->initToolbar();
                $jsonData = array_merge($jsonData, array(
                    'msg' => $this->l('Saved', 'AdminEtsACReminderEmailController'),
                    'html' => $this->renderList(),
                    'nb_reminders' => (int)EtsAbancartCampaign::nbReminders($this->id_campaign),
                ));
            }
            $this->toJson($jsonData);
        }
    }

    public function ajaxProcessDelete()
    {
        if ($this->access('edit')) {
            $this->processDelete();
            $hasError = count($this->errors) ? true : false;
            $jsonData = array(
                'errors' => $hasError ? $this->module->displayError($this->errors) : false,
            );
            if (!$hasError)
                $jsonData = array_merge($jsonData, array(
                    'msg' => $this->l('Deleted', 'AdminEtsACReminderEmailController'),
                    'html' => $this->renderList(),
                    'nb_reminders' => (int)EtsAbancartCampaign::nbReminders($this->id_campaign),
                ));
            $this->toJson($jsonData);
        }
    }

    public function ajaxProcessSelectTemplate()
    {
        if ($this->access('edit')) {
            $object = new EtsAbancartEmailTemplate((int)Tools::getValue('id_ets_abancart_email_template'));
            $subject = EtsAbancartEmailTemplate::getSubject($object->id);
            $languages = Language::getLanguages(false);

            $mailContent = $mailSubject = array();
            $idLangDefault = Configuration::get('PS_LANG_DEFAULT');
            $mailDirDefault = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . ($object->folder_name ?: $object->id) . '/' . $object->temp_path[$idLangDefault];

            foreach ($languages as $lang) {
                $mailDir = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . ($object->folder_name ?: $object->id) . '/' . $object->temp_path[$lang['id_lang']];
                if (file_exists($mailDir)) {
                    $mailContent[$lang['id_lang']] = EtsAbancartEmailTemplate::getBodyMailTemplate($mailDir, $object);
                } elseif (file_exists($mailDirDefault)) {
                    $mailContent[$lang['id_lang']] = EtsAbancartEmailTemplate::getBodyMailTemplate($mailDirDefault, $object);
                } else {
                    $mailContent[$lang['id_lang']] = '';
                }
                if (isset($subject[$lang['iso_code']])) {
                    $mailSubject[$lang['id_lang']] = $subject[$lang['iso_code']];
                } else
                    $mailSubject[$lang['id_lang']] = $subject['en'];
            }

            $this->toJson(array(
                'html' => $object->id > 0 ? $mailContent : '',
                'subject' => $mailSubject
            ));
        }
    }

    public function ajaxProcessViewTracking()
    {
        if ($this->access('edit')) {
            $this->loadObject(true);
            $trackings = EtsAbancartTracking::reminderLogs($this->object->id);

            $this->context->smarty->assign(array(
                'TRACKINGs' => $trackings,
                'TYPE' => $this->type,
            ));

            $this->toJson(array(
                'html' => $this->createTemplate('tracking.tpl')->fetch()
            ));
        }
    }

    public function validateRules($class_name = false)
    {
        parent::validateRules($class_name);
        if (!$this->errors) {
            $campaignObj = new EtsAbancartCampaign((int)Tools::getValue($this->campaign->identifier));

            $day = trim($day = Tools::getValue('day')) != '' && Validate::isUnsignedFloat($day) ? $day : 0;
            $hour = trim($hour = Tools::getValue('hour')) != '' && Validate::isUnsignedFloat($hour) ? $hour : 0;
            $min = trim($min = Tools::getValue('minute')) != '' && Validate::isUnsignedFloat($min) ? $min : 0;
            $sec = trim($sec = Tools::getValue('second')) != '' && Validate::isUnsignedInt($sec) ? $sec : 0;

            $query = '
                SELECT ' . pSQL($this->identifier) . ' FROM ' . _DB_PREFIX_ . pSQL($this->table) . '
                WHERE `enabled` = 1
                    AND `deleted` = 0
                    AND `day`' . ($day ? '=' . $day : ' is NULL') . '
                    AND `hour`' . ($hour ? '=' . $hour : ' is NULL') . '
                    AND `minute`' . ($min ? '=' . $min : ' is NULL ') . '
                    AND `second`' . ($sec ? '=' . $sec : ' is NULL ') . '
                    ' . ($campaignObj->id > 0 ? 'AND ' . pSQL($this->campaign->identifier) . ' =' . (int)$campaignObj->id : '') . '
                    ' . ($this->id_object ? 'AND ' . pSQL($this->identifier) . ' !=' . (int)$this->id_object : '') . '
            ';
            if (EtsAbancartTools::doSqlFilter($query)) {
                $this->errors[] = $this->l('The same frequency has existed in another reminder', 'AdminEtsACReminderEmailController');
            } else {
                $discount_option = trim(Tools::getValue('discount_option'));
                if ($discount_option == 'no' || $discount_option == 'auto' && !in_array($campaignObj->campaign_type, [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER]) && (int)$campaignObj->has_product_in_cart !== EtsAbancartCampaign::HAS_SHOPPING_CART_YES) {
                    $languages = Language::getLanguages(false);
                    if ($languages) {
                        foreach ($languages as $l) {
                            $content = trim(Tools::getValue('content_' . $l['id_lang']));
                            if ($content !== '' && preg_match('/\[discount_(?:code|from|to)|reduction|money_saved|total_payment_after_discount|button_add_discount|show_discount_box|discount_count_down_clock\]/i', $content)) {
                                $this->errors[] = sprintf($this->l('Discount code that appeared in reminder content in %s is invalid. Please remove the discount code and the related contents.', 'AdminEtsACReminderEmailController'), $l['name']);
                            }
                        }
                    }
                }
            }
        }
    }

    public function discountOption($value, $tpl_vars)
    {
        $campaign = new EtsAbancartCampaign((int)$tpl_vars['id_ets_abancart_campaign']);
        $currency = new Currency((int)$tpl_vars['id_currency'], $this->context->language->id);
        $tpl_vars = array_merge($tpl_vars, array(
            'campaign_type' => $campaign->campaign_type,
            'value' => $value,
            'currency' => $currency
        ));
        $this->context->smarty->assign($tpl_vars);
        return $this->createTemplate('discount.tpl')->fetch();
    }

    public function printReminder($value)
    {
        $this->context->smarty->assign(array(
            'value' => $value,
            'badge' => 'danger-hover',
        ));
        return $this->createTemplate('badge.tpl')->fetch();
    }

    public function displayViewTrackingLink($token, $id)
    {
        if (!isset(self::$cache_lang['viewtracking'])) {
            self::$cache_lang['viewtracking'] = $this->l('View tracking', 'AdminEtsACReminderEmailController');
        }

        $this->context->smarty->assign(array(
            'href' => $this->currentLink . (($parentId = (int)Tools::getValue($this->campaign->identifier)) ? '&' . $this->campaign->identifier . '=' . (int)$parentId : '') . '&viewtracking&' . $this->identifier . '=' . $id . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['viewtracking'],
        ));

        return $this->createTemplate('helpers/list/list_action_view_tracking.tpl')->fetch();
    }

    public function displayEditLink($token, $id)
    {
        if (!isset($this->campaign->object) || $this->campaign->object->id < 1 || $id < 1)
            return false;
        $reminder = new EtsAbancartReminder($id);
        if ($reminder->id > 0 && $reminder->enabled != EtsAbancartReminder::REMINDER_STATUS_FINISHED) {
            if (!isset(self::$cache_lang['edit'])) {
                self::$cache_lang['edit'] = $this->l('Edit', 'AdminEtsACReminderEmailController');
            }

            $this->context->smarty->assign(array(
                'href' => $this->currentLink . '&update' . $this->table . (($parentId = (int)Tools::getValue($this->campaign->identifier)) ? '&' . $this->campaign->identifier . '=' . $parentId : '') . '&' . $this->identifier . '=' . $id . '&token=' . ($token != null ? $token : $this->token),
                'action' => self::$cache_lang['edit'],
            ));

            return $this->createTemplate('helpers/list/list_action_edit.tpl')->fetch();
        }
    }

    public function renderView()
    {
        $times_series = array(
            'all' => array(
                'label' => $this->l('All', 'AdminEtsACReminderEmailController'),
            ),
            'this_year' => array(
                'label' => $this->l('This year', 'AdminEtsACReminderEmailController'),
                'default' => 1,
            ),
            'last_year' => array(
                'label' => $this->l('Last year', 'AdminEtsACReminderEmailController'),
            ),
            'this_month' => array(
                'label' => $this->l('This month', 'AdminEtsACReminderEmailController'),
            ),
            'last_month' => array(
                'label' => $this->l('Last month', 'AdminEtsACReminderEmailController'),
            ),
            'today' => array(
                'label' => $this->l('Today', 'AdminEtsACReminderEmailController'),
            ),
            'yesterday' => array(
                'label' => $this->l('Yesterday', 'AdminEtsACReminderEmailController'),
            ),
            'time_range' => array(
                'label' => $this->l('Time range', 'AdminEtsACReminderEmailController'),
            ),
        );
        $id_campaign = (int)Tools::getValue('id_ets_abancart_campaign');
        $campaign = new EtsAbancartCampaign($id_campaign, $this->context->language->id);
        $controller = ($controller = Tools::getValue('controller')) && Validate::isCleanHtml($controller) ? $controller : '';
        $this->tpl_view_vars = array(
            'campaign' => $campaign,
            'campaign_groups' => EtsAbancartCampaign::getCampaignGroup($campaign->id, $this->context->language->id),
            'is_all_country' => $campaign->is_all_country,
            'campaign_countries' => $campaign->is_all_country ? array() : EtsAbancartCampaign::getCampaignCountries($campaign->id, $campaign->is_all_country),
            'is_all_lang' => $campaign->is_all_lang,
            'campaign_languages' => $campaign->is_all_lang ? array() : EtsAbancartCampaign::getCampaignLanguages($campaign->id, $campaign->is_all_lang),
            'time_series' => $times_series,
            'line_chart' => EtsAbancartReminderForm::getInstance()->getLineChartCampaign('this_year', $id_campaign, 0, 0, 0, 0, $this->type == EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL ? 'recovered_carts' : ($this->type == EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER ? 'email_sent' : 'all_reminders')),
            'table_reminder' => $this->getReminders(),
            'emailSendOption' => EtsAbancartReminderForm::getInstance()->getCustomerEmailSendOptions(),
            'countReminder' => EtsAbancartReminder::getTotalReminder($id_campaign),
            'linkAddReminder' => $this->context->link->getAdminLink($controller) . '&id_ets_abancart_campaign=' . $id_campaign . '&addets_abancart_campaign',
            'linkEditCampaign' => $this->context->link->getAdminLink($controller) . '&id_ets_abancart_campaign=' . $id_campaign . '&updateets_abancart_campaign',
            'linkSubmitExport' => $this->context->link->getAdminLink('AdminEtsACReminderEmail') . '&id_ets_abancart_campaign=' . $id_campaign . '&exportCampaignTracking=1',
            'purchasedProducts' => $campaign->campaign_type == 'customer' && $campaign->has_placed_orders != 'no' && $campaign->purchased_product ? EtsAbancartReminderForm::getInstance()->displayListProduct('ets_ac_purchased_product', explode(',', $campaign->purchased_product), 'ets_ac_purchased_product', '', false) : '',
            'notPurchasedProducts' => $campaign->campaign_type == 'customer' && $campaign->has_placed_orders != 'no' && $campaign->not_purchased_product ? EtsAbancartReminderForm::getInstance()->displayListProduct('ets_ac_not_purchased_product', explode(',', $campaign->not_purchased_product), 'ets_ac_not_purchased_product', '', false) : '',
            'reminders' => EtsAbancartReminder::getReminders($id_campaign, $this->context),
        );

        if ($campaign->campaign_type == 'customer' || $campaign->campaign_type == 'email')
            $this->tpl_view_vars['last_email_sent'] = EtsAbancartCampaign::getEmailSent($id_campaign, 10, null, null, $this->context);

        $this->context->smarty->assign(array(
            'campaignName' => $campaign->name
        ));

        return parent::renderView();
    }

    public function getReminders()
    {
        return parent::renderList();
    }

    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);
        if (Tools::getValue('action') == 'saveData' && $table == 'ets_abancart_reminder' && $object instanceof EtsAbancartReminder) {
            $adt = ($adt = Tools::getValue('apply_discount_to')) && Validate::isCleanHtml($adt) ? $adt : '';
            if ($adt != 'selection') {
                $object->selected_product = null;
            }
            if ($adt == 'order') {
                $object->reduction_product = 0;
            } elseif ($adt == 'specific') {
                $object->reduction_product = (int)Tools::getValue('reduction_product');

            } elseif ($adt == 'cheapest') {
                $object->reduction_product = -1;
            } elseif ($adt == 'selection') {
                $object->reduction_product = -2;
                if (($selectedProducts = Tools::getValue('selected_product')) && is_array($selectedProducts)) {
                    $products = array_map('intval', $selectedProducts);
                    $object->selected_product = implode(',', $products);
                } else
                    $object->selected_product = null;
            }
            $freeGift = (int)Tools::getValue('free_gift');
            if (!$freeGift) {
                $object->gift_product = 0;
                $object->gift_product_attribute = 0;
            }
            $campaign = new EtsAbancartCampaign((int)Tools::getValue('id_ets_abancart_campaign'));
            $sendEmailRepeat = (int)Tools::getValue('send_repeat_email');
            $scheduleTime = ($scheduleTime = Tools::getValue('customer_email_schedule_time')) && Validate::isCleanHtml($scheduleTime) ? $scheduleTime : '';
            $object->schedule_time = null;

            switch ($campaign->email_timing_option) {
                case EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN:
                case EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION:
                    $object->send_repeat_email = $sendEmailRepeat;
                    break;
                case EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME:
                    $object->schedule_time = date('Y-m-d H:i:s', strtotime($scheduleTime));
                    break;
            }
            $languages = Language::getLanguages(false);
            $emailTemplate = new EtsAbancartEmailTemplate($object->id_ets_abancart_email_template);
            $folderName = $emailTemplate->folder_name ?: $emailTemplate->id;
            $mailPath = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $folderName;
            $cache = $mailPath . '/email_editing.ets';
            $commentCode = $mailPath . '/key_editing.json';
            $contentCommentCode = file_exists($commentCode) ? Tools::file_get_contents($commentCode) : '';
            $commentCodeJson = $contentCommentCode ? Tools::jsonDecode($contentCommentCode, true) : array();
            foreach ($languages as $lang) {
                foreach ($commentCodeJson as $kc => $code) {
                    $object->content[$lang['id_lang']] = str_replace('<!--%--comment' . $kc . '--%-->', $code, $object->content[$lang['id_lang']]);
                }
            }
            if (file_exists($cache)) {
                unlink($cache);
            }
            if (file_exists($commentCode)) {
                unlink($commentCode);
            }
        }
    }

    public function validateStepForm()
    {
        $errors = array();
        $request = Tools::getAllValues();
        $campaign = new EtsAbancartCampaign((int)Tools::getValue('id_ets_abancart_campaign'));

        if (!isset($campaign->email_timing_option) || in_array((int)$campaign->email_timing_option, array(EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_REGISTRATION, EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION, EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN, EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER))) {
            if (isset($request['day']) || isset($request['hour']) || isset($request['minute']) || isset($request['minute']) || isset($request['second'])) {
                /*if ((!isset($request['day']) || !$request['day']) && (!isset($request['hour']) || !$request['hour']) && (!isset($request['minute']) || !$request['minute']) && (!isset($request['second']) || !$request['second'])) {
                    $errors[] = $this->l('Timing is required', 'AdminEtsACReminderEmailController');
                } else*/
                if (isset($request['day']) && $request['day'] && !Validate::isUnsignedFloat($request['day'])) {
                    $errors[] = $this->l('Day value is invalid', 'AdminEtsACReminderEmailController');
                } elseif (isset($request['hour']) && $request['hour'] && !Validate::isUnsignedFloat($request['hour'])) {
                    $errors[] = $this->l('Hour value is invalid', 'AdminEtsACReminderEmailController');
                } elseif (isset($request['minute']) && $request['minute'] && !Validate::isUnsignedFloat($request['minute'])) {
                    $errors[] = $this->l('Minute value is invalid', 'AdminEtsACReminderEmailController');
                } elseif (isset($request['second']) && $request['second'] && !Validate::isUnsignedFloat($request['second'])) {
                    $errors[] = $this->l('Second value is invalid', 'AdminEtsACReminderEmailController');
                }
            }
        }

        if (isset($campaign->email_timing_option) && (int)$campaign->email_timing_option == EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME) {
            if (!isset($request['customer_email_schedule_time']) || !$request['customer_email_schedule_time']) {
                $errors[] = $this->l('Schedule time is required', 'AdminEtsACReminderEmailController');
            } elseif (!Validate::isDate($request['customer_email_schedule_time']) || strtotime($request['customer_email_schedule_time']) < time()) {
                $errors[] = $this->l('Schedule time is invalid', 'AdminEtsACReminderEmailController');
            }
        }
        $idLangDefault = Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);
        if (!$errors && isset($request['discount_option'])) {
            if ($request['discount_option'] == 'auto') {
                if (!isset($request['discount_name_' . $idLangDefault]) || !$request['discount_name_' . $idLangDefault]) {
                    $errors[] = $this->l('Discount name is required', 'AdminEtsACReminderEmailController');
                }
            }

            if ($request['discount_option'] == 'fixed') {
                if (!isset($request['discount_code']) || !$request['discount_code']) {
                    $errors[] = $this->l('Discount code is required', 'AdminEtsACReminderEmailController');
                } elseif (!Validate::isCleanHtml($request['discount_code'])) {
                    $errors[] = $this->l('Discount code is invalid', 'AdminEtsACReminderEmailController');
                } elseif (!CartRule::getIdByCode(trim($request['discount_code']))) {
                    $errors[] = $this->l('Discount code does not exist', 'AdminEtsACReminderEmailController');
                }
            } elseif ($request['discount_option'] == 'auto') {
                if (!isset($request['quantity']) || !Tools::strlen($request['quantity'])) {
                    $errors[] = $this->l('Total available is required', 'AdminEtsACReminderEmailController');
                } elseif (!Validate::isUnsignedInt($request['quantity']) || (float)$request['quantity'] <= 0) {
                    $errors[] = $this->l('Total available is invalid', 'AdminEtsACReminderEmailController');
                }
                if (!isset($request['quantity_per_user']) || !Tools::strlen($request['quantity_per_user'])) {
                    $errors[] = $this->l('Total available for each user is required', 'AdminEtsACReminderEmailController');
                } elseif (!Validate::isUnsignedInt($request['quantity_per_user']) || (int)$request['quantity_per_user'] <= 0) {
                    $errors[] = $this->l('Total available for each user is invalid', 'AdminEtsACReminderEmailController');
                }
                if (!isset($request['apply_discount_in']) || !Tools::strlen($request['apply_discount_in'])) {
                    $errors[] = $this->l('Discount availability required', 'AdminEtsACReminderEmailController');
                } elseif (!Validate::isUnsignedInt($request['apply_discount_in']) || (int)$request['apply_discount_in'] <= 0) {
                    $errors[] = $this->l('Discount availability is invalid', 'AdminEtsACReminderEmailController');
                }
                if (isset($request['apply_discount'])) {
                    if ($request['apply_discount'] == 'percent') {
                        if (!isset($request['reduction_percent']) || !Tools::strlen($request['reduction_percent'])) {
                            $errors[] = $this->l('Discount percent is required', 'AdminEtsACReminderEmailController');
                        } elseif (!Validate::isUnsignedFloat($request['reduction_percent']) || (float)$request['reduction_percent'] <= 0) {
                            $errors[] = $this->l('Discount percent is invalid', 'AdminEtsACReminderEmailController');
                        }
                        if (isset($request['apply_discount_to']) && $request['apply_discount_to'] == 'selection'
                            && (!isset($request['selected_product']) || !$request['selected_product'])) {
                            $errors[] = $this->l('Select products for apply discount is required', 'AdminEtsACReminderEmailController');
                        }
                    } elseif ($request['apply_discount'] == 'amount') {
                        if (!isset($request['reduction_amount']) || !Tools::strlen($request['reduction_amount'])) {
                            $errors[] = $this->l('Discount amount is required', 'AdminEtsACReminderEmailController');
                        } elseif (!Validate::isUnsignedFloat($request['reduction_amount']) || (float)$request['reduction_amount'] <= 0) {
                            $errors[] = $this->l('Discount amount is invalid', 'AdminEtsACReminderEmailController');
                        }
                    }
                    if ($request['apply_discount'] == 'percent' || $request['apply_discount'] == 'amount') {
                        if (isset($request['apply_discount_to']) && $request['apply_discount_to'] == 'specific'
                            && (!isset($request['reduction_product']) || !$request['reduction_product'])) {
                            $errors[] = $this->l('Specific product for apply discount is required', 'AdminEtsACReminderEmailController');
                        }
                    }

                    if (isset($request['free_gift']) && (int)$request['free_gift'] && (!isset($request['gift_product']) || !(int)$request['gift_product'])) {
                        $errors[] = $this->l('Product to send free gift is required', 'AdminEtsACReminderEmailController');
                    }
                }

                if (isset($request['apply_discount_in']) && Tools::strlen($request['apply_discount_in']) && !Validate::isUnsignedInt($request['apply_discount_in'])) {
                    $errors[] = $this->l('Discount availability is invalid', 'AdminEtsACReminderEmailController');
                }
            }
        }

        if (isset($request['title_' . $idLangDefault]) && !$request['title_' . $idLangDefault]) {
            if ($campaign->campaign_type == 'popup')
                $errors[] = $this->l('Title is required', 'AdminEtsACReminderEmailController');
            else
                $errors[] = $this->l('Email subject is required', 'AdminEtsACReminderEmailController');
        } else {
            foreach ($languages as $lang) {
                if (isset($request['title_' . $lang['id_lang']]) && !Validate::isString($request['title_' . $lang['id_lang']])) {
                    if ($campaign->campaign_type == 'popup')
                        $errors[] = sprintf($this->l('Title in "%s" is invalid', 'AdminEtsACReminderEmailController'), $lang['iso_code']);
                    else
                        $errors[] = sprintf($this->l('Email subject in "%s" is invalid', 'AdminEtsACReminderEmailController'), $lang['iso_code']);
                } elseif (isset($request['title_' . $lang['id_lang']]) && Tools::strlen($request['title_' . $lang['id_lang']]) > 100) {
                    if ($campaign->campaign_type == 'popup')
                        $errors[] = sprintf($this->l('Title in "%s" is too long. Maximum length: %s characters', 'AdminEtsACReminderEmailController'), $lang['iso_code'], 100);
                    else
                        $errors[] = sprintf($this->l('Email subject in in "%s" is too long. Maximum length: %s characters', 'AdminEtsACReminderEmailController'), $lang['iso_code'], 100);
                }
            }
        }
        if (isset($request['content_' . $idLangDefault]) && !$request['content_' . $idLangDefault]) {
            $errors[] = $this->l('Email content is required', 'AdminEtsACReminderEmailController');
        } else {
            foreach ($languages as $lang) {
                if (isset($request['content_' . $lang['id_lang']]) && !Validate::isCleanHtml($request['content_' . $lang['id_lang']])) {
                    $errors[] = sprintf($this->l('Email content in "%s" is invalid', 'AdminEtsACReminderEmailController'), $lang['iso_code']);
                }
            }
        }
        return $errors;
    }

}