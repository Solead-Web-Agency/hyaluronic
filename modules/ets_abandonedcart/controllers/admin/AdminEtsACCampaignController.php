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

require_once(dirname(__FILE__) . '/AdminEtsACFormController.php');

class AdminEtsACCampaignController extends AdminEtsACFormController
{
    // Properties.
    private $type;
    public $reminderType = array();
    /**
     * @var EtsAbancartCampaign
     */
    public $object;
    public $redirect_after;
    public $controllerList = null;
    public $isViewCampaign = false;

    static $countries_array;
    static $languages_array;

    public function __construct($type = null)
    {
        $this->bootstrap = true;
        $this->isViewCampaign = Tools::getIsset('viewets_abancart_campaign');
        $this->table = 'ets_abancart_campaign';
        $this->className = 'EtsAbancartCampaign';
        $this->list_id = $this->table;
        $this->show_form_cancel_button = false;
        $this->lang = true;
        $this->list_no_link = false;
        $this->_orderWay = 'DESC';
        $this->_redirect = false;
        parent::__construct();

        if (!Tools::isSubmit('view' . $this->table))
            $this->addRowAction('view');
        $this->addRowAction('edit');
        if (Tools::isSubmit('view' . $this->table))
            $this->addRowAction('viewtracking');
        $this->addRowAction('delete');

        $this->tpl_folder = 'common/';
        $this->override_folder = 'common/';
        $this->base_tpl_view = 'email_campaign_view.tpl';
        $this->type = $type;
        $hasDisplayTimes = trim($this->type) !== '' && !in_array($this->type, [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER]);

        $this->_select = '
            ROUND(a.min_total_cart, 2) `min_total_cart`
            , ROUND(a.max_total_cart, 2) `max_total_cart` 
            , b.`name`
            , COUNT(DISTINCT IF(ar.deleted=0, ar.id_ets_abancart_reminder, NULL)) as reminders
            , SUM(IF(a.campaign_type!=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' AND a.campaign_type!=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER) . '\', IF(dt.id_ets_abancart_reminder > 0, dt.number_of_displayed, NULL), IF(at.id_ets_abancart_reminder > 0, at.total_execute_times, NULL))) as `execute_times`
            , SUM(IF(at.id_ets_abancart_reminder > 0 AND at.delivered > 0, 1, NULL)) as `success`
            , SUM(IF(at.id_ets_abancart_reminder > 0 AND at.delivered <= 0, 1, NULL)) as `failed`
            , SUM(IF(at.id_ets_abancart_reminder > 0 AND at.read > 0, 1, NULL)) as `read`
            , qu.`queue` as `queue`
            , a.is_all_country
            , a.is_all_lang
            , IF(cc.id_country is NOT NULL, 1, 0) as `unknown_country`
            , SUM(o.total_paid_tax_incl) as `total_paid_tax_incl`
            , COUNT(o.id_order) as `nb_order`
            , o.id_currency
            , IF(a.is_all_country, 0, IF(cc.id_country is NOT NULL, -1, cc2.`ids_country`)) `countries`
            , IF(a.is_all_lang, 0, awl.`ids_language`) `languages`
        ';

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_campaign = a.id_ets_abancart_campaign)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_tracking` at ON (at.id_ets_abancart_reminder = ar.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` dt ON (dt.id_ets_abancart_reminder = ar.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign_country` cc ON (a.id_ets_abancart_campaign = cc.id_ets_abancart_campaign AND cc.id_country=-1)
            LEFT JOIN (
                SELECT cc2.id_ets_abancart_campaign, GROUP_CONCAT(cc2.id_country SEPARATOR \',\') `ids_country` 
                FROM `' . _DB_PREFIX_ . 'ets_abancart_campaign_country` cc2 
                GROUP BY cc2.id_ets_abancart_campaign
            ) cc2 ON (a.id_ets_abancart_campaign = cc2.id_ets_abancart_campaign)
            LEFT JOIN (
                SELECT awl.id_ets_abancart_campaign, GROUP_CONCAT(awl.id_lang SEPARATOR \',\') `ids_language` 
                FROM `' . _DB_PREFIX_ . 'ets_abancart_campaign_with_lang` awl
                GROUP BY awl.id_ets_abancart_campaign 
            ) awl ON (a.id_ets_abancart_campaign = awl.id_ets_abancart_campaign)
            LEFT JOIN ( 
                SELECT SUM(IF(qu2.id_ets_abancart_reminder > 0, 1, NULL)) as `queue`, qu2.id_ets_abancart_reminder
                FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` qu2 
                GROUP BY qu2.id_ets_abancart_reminder
            ) qu ON (qu.id_ets_abancart_reminder = at.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'cart` cart ON (cart.id_cart = at.id_cart)
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = cart.id_cart)
        ';

        $this->_where = 'AND a.deleted = 0 AND a.id_shop=' . (int)$this->context->shop->id;

        $this->_group = 'GROUP BY a.id_ets_abancart_campaign';

        if ($this->type)
            $this->_where .= ' AND a.campaign_type = \'' . pSQL($this->type) . '\'';

        if (!$this->reminderType && ($cTypes = EtsAbancartDefines::getInstance()->getFields('sub_menus'))) {
            foreach ($cTypes as $key => $value) {
                if (!isset($value['object']) || $value['object']) {
                    $this->reminderType[$key] = $value['label'];
                }
            }
        }

        self::$countries_array = [
            0 => $this->l('All', 'AdminEtsACCampaignController'),
            -1 => $this->l('Unknown', 'AdminEtsACCampaignController'),
        ];
        $countries = Country::getCountries($this->context->language->id, true);
        if ($countries) {
            foreach ($countries as $country) {
                self::$countries_array[$country['id_country']] = $country['name'];
            }
        }
        self::$languages_array = [
            0 => $this->l('All', 'AdminEtsACCampaignController'),
        ];
        $languages = Language::getLanguages(false);
        if ($languages) {
            foreach ($languages as $language) {
                self::$languages_array[$language['id_lang']] = $language['name'];
            }
        }
        $this->fields_list = array_merge(
            [
                'id_ets_abancart_campaign' => array(
                    'title' => $this->l('ID', 'AdminEtsACCampaignController'),
                    'type' => 'int',
                    'filter_key' => 'a!id_ets_abancart_campaign',
                    'class' => 'fixed-width-xs center',
                ),
                'name' => array(
                    'title' => $this->l('Name', 'AdminEtsACCampaignController'),
                    'type' => 'text',
                    'filter_key' => 'b!name',
                ),
            ],
            (!$this->type ? [
                'campaign_type' => array(
                    'title' => $this->l('Reminder type', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'class' => 'center',
                    'list' => $this->reminderType,
                    'filter_key' => 'a!campaign_type',
                    'callback' => 'campaignType',
                )
            ] : []),
            [
                'reminders' => array(
                    'title' => $this->l('Reminder(s)', 'AdminEtsACCampaignController'),
                    'type' => 'text',
                    'havingFilter' => true,
                    'class' => 'center',
                    'callback' => 'printReminder',
                ),
                'execute_times' => array(
                    'title' => $hasDisplayTimes ? $this->l('Display times', 'AdminEtsACCampaignController') : $this->l('Execute times', 'AdminEtsACCampaignController'),
                    'type' => 'text',
                    'havingFilter' => true,
                    'search' => false,
                    'class' => 'fixed-width-lg center',
                    'callback' => 'displayExecuteTimes',
                )
            ],
            (!$hasDisplayTimes ? [
                'total_paid_tax_incl' => array(
                    'title' => $this->l('Recovered carts', 'AdminEtsACReminderEmailController'),
                    'align' => 'text-right',
                    'type' => 'price',
                    'currency' => true,
                    'callback' => 'displayRecoveredCarts',
                    'class' => 'fixed-width-lg',
                ),
            ] : []),
            ($this->type != 'customer' ? [
                'min_total_cart' => array(
                    'title' => $this->l('From total cart value', 'AdminEtsACCampaignController'),
                    'type' => 'text',
                    'filter_key' => 'a!min_total_cart',
                    'class' => 'text-center',
                ),
                'max_total_cart' => array(
                    'title' => $this->l('To total cart value', 'AdminEtsACCampaignController'),
                    'type' => 'text',
                    'filter_key' => 'a!max_total_cart',
                    'class' => 'text-center',
                ),
            ] : [
                'last_order_from' => array(
                    'title' => $this->l('Last order from', 'AdminEtsACCampaignController'),
                    'type' => 'date',
                    'filter_key' => 'a!last_order_from',
                    'class' => 'text-center'
                ),
                'last_order_to' => array(
                    'title' => $this->l('Last order to', 'AdminEtsACCampaignController'),
                    'type' => 'date',
                    'filter_key' => 'a!last_order_to',
                    'class' => 'text-center'
                ),
            ]),
            [
                'available_from' => array(
                    'title' => $this->l('Available from', 'AdminEtsACCampaignController'),
                    'type' => 'date',
                    'filter_key' => 'a!available_from',
                    'class' => 'text-center'
                ),
                'available_to' => array(
                    'title' => $this->l('Available to', 'AdminEtsACCampaignController'),
                    'type' => 'date',
                    'filter_key' => 'a!available_to',
                    'class' => 'text-center'
                ),
                'countries' => array(
                    'title' => $this->l('Countries', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'list' => self::$countries_array,
                    'class' => 'text-center',
                    'filter_key' => 'countries',
                    'orderby' => false,
                    'havingFilter' => true,
                    'callback' => 'displayCountries',
                    'callback_object' => $this,
                ),
                'languages' => array(
                    'title' => $this->l('Languages', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'list' => self::$languages_array,
                    'class' => 'text-center',
                    'filter_key' => 'languages',
                    'orderby' => false,
                    'havingFilter' => true,
                    'callback' => 'displayLanguages',
                    'callback_object' => $this,
                ),
                'enabled' => array(
                    'title' => $this->l('Active', 'AdminEtsACCampaignController'),
                    'type' => 'bool',
                    'active' => 'status',
                    'class' => 'center',
                    'filter_key' => 'a!enabled',
                    'remove_onclick' => true
                ),
            ]
        );
    }

    public function processFilter()
    {
        parent::processFilter();

        if ($this->_filterHaving)
            $this->_filterHaving = preg_replace('/(\`(countries|languages)\`)\s*=\s*\'(-?\d+)\'/', '$1 REGEXP \'^($3,([0-9,]+)|([0-9,]+),$3,([0-9,]+)|([0-9,]+),$3|$3)$\'', $this->_filterHaving);
    }

    public function displayCountries($countries)
    {
        $countries = explode(',', $countries);
        $c = [];
        foreach ($countries as $country) {
            $c[] = self::$countries_array[$country];
        }
        return implode(',', $c);
    }

    public function displayLanguages($languages)
    {
        $languages = explode(',', $languages);
        $l = [];
        foreach ($languages as $language) {
            $l[] = self::$languages_array[$language];
        }
        return implode(',', $l);
    }

    public function displayExecuteTimes($execute_times, $tr)
    {
        if (!$execute_times)
            return 0;
        if ((isset($tr['campaign_type']) && ($tr['campaign_type'] == 'email' || $tr['campaign_type'] == 'customer')) || ($this->type == 'email' || $this->type == 'customer')) {
            $stats = [];
            if (isset($tr['success']) && (int)$tr['success'] > 0) {
                $stats[] = $this->l('Success', 'AdminEtsACCampaignController') . ': ' . (int)$tr['success'];
            }
            if (isset($tr['failed']) && (int)$tr['failed'] > 0) {
                $stats[] = $this->l('Failed', 'AdminEtsACCampaignController') . ': ' . (int)$tr['failed'];
            }
            if (isset($tr['queue']) && (int)$tr['queue'] > 0) {
                $stats[] = $this->l('Queue', 'AdminEtsACCampaignController') . ': ' . (int)$tr['queue'];
            }
            if (isset($tr['read']) && (int)$tr['read'] > 0) {
                $stats[] = $this->l('Read', 'AdminEtsACCampaignController') . ': ' . (int)$tr['read'];
            }
            $execute_times .= ($stats ? ' (' . implode(' | ', $stats) . ')' : '');
        }
        $this->context->smarty->assign(array(
            'execute_times_item' => $execute_times,
            'link_to_tracking' => $this->context->link->getAdminLink('AdminEtsAC' . ($this->type == 'email' || $this->type == 'customer' ? '' : 'Display') . 'Tracking') . '&id_ets_abancart_campaign=' . $tr['id_ets_abancart_campaign'] . (isset($tr['id_ets_abancart_reminder']) ? '&id_ets_abancart_reminder=' . $tr['id_ets_abancart_reminder'] : ''),
        ));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/execute_times_item.tpl');
    }

    public function initToolbar()
    {
        parent::initToolbar(); // TODO: Change the autogenerated stub
        if (null == $this->type)
            unset($this->toolbar_btn['new']);
    }

    public function getFieldsForm()
    {
        $countries = Country::getCountries($this->context->language->id, true);
        $countries_array = [
            [
                'id_country' => '0',
                'name' => $this->l('All', 'AdminEtsACCampaignController')
            ],
            [
                'id_country' => '-1',
                'name' => $this->l('Unknown', 'AdminEtsACCampaignController')
            ],
        ];
        if ($countries) {
            foreach ($countries as $country) {
                $countries_array[] = [
                    'id_country' => (int)$country['id_country'],
                    'name' => trim($country['name'])
                ];
            }
        }
        $languages = Language::getLanguages(true);
        $languages_array = [
            [
                'id_lang' => '0',
                'name' => $this->l('All', 'AdminEtsACCampaignController')
            ]
        ];
        if ($languages) {
            foreach ($languages as $language) {
                $languages_array[] = [
                    'id_lang' => (int)$language['id_lang'],
                    'name' => trim($language['name'])
                ];
            }
        }
        $groups = Group::getGroups($this->context->language->id, $this->context->shop->id);
        $groups_array = [];
        if (is_array($groups) && count($groups) > 0) {
            foreach ($groups as $group) {
                if ($this->type == 'email') {
                    if ((int)$group['id_group'] !== (int)Configuration::get('PS_UNIDENTIFIED_GROUP')) {
                        $groups_array[] = [
                            'id_group' => (int)$group['id_group'],
                            'name' => $group['name']
                        ];
                    }
                } else {
                    $groups_array[] = [
                        'id_group' => (int)$group['id_group'],
                        'name' => $group['name']
                    ];
                }
            }
        }
        $formFields = array_merge(
            [
                'name' => array(
                    'name' => 'name',
                    'label' => $this->l('Name', 'AdminEtsACCampaignController'),
                    'lang' => true,
                    'required' => true,
                    'type' => 'text',
                    'col' => 9,
                    'class' => 'col_name',
                    'form_group_class' => 'group_colname',
                    'validate' => 'isString'
                ),
                'available_from' => array(
                    'name' => 'available_from',
                    'label' => $this->l('Available', 'AdminEtsACCampaignController'),
                    'type' => 'date',
                    'desc' => $this->l('Leave blank for unlimited time', 'AdminEtsACCampaignController'),
                    'to' => 'available_to',
                    'validate' => 'isDate',
                ),
                'available_to' => array(
                    'name' => 'available_to',
                    'label' => $this->l('To', 'AdminEtsACCampaignController'),
                    'type' => 'date',
                    'validate' => 'isDate',
                ),
                'email_timing_option' => array(
                    'name' => 'email_timing_option',
                    'label' => $this->l('When to send email?', 'AdminEtsACCampaignController'),
                    'type' => 'radios',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_REGISTRATION,
                                'name' => $this->l('After customer registration', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION,
                                'name' => $this->l('After order completion', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME,
                                'name' => $this->l('Schedule time (All registered customers)', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_RUN_NOW,
                                'name' => $this->l('Run now (All registered customers)', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER,
                                'name' => $this->l('After subscribing newsletter', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN,
                                'name' => $this->l('Last visit time', 'AdminEtsACCampaignController')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default_value' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_REGISTRATION,
                ),
            ],
            trim($this->type) !== 'customer' ? [
                'customer_group' => array(
                    'name' => 'customer_group',
                    'label' => $this->l('Applicable user group', 'AdminEtsACCampaignController'),
                    'type' => 'abancart_group',
                    'values' => array(
                        'query' => $groups_array,
                        'id' => 'id_group',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Select user group to apply this reminder campaign', 'AdminEtsACCampaignController'),
                    'default_value' => 'all',
                    'form_group_class' => 'customer_group',
                ),
            ] : [],
            [
                'has_product_in_cart' => array(
                    'name' => 'has_product_in_cart',
                    'label' => $this->l('Has product in shopping cart?', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'options' => array(
                        'id' => 'id',
                        'name' => 'name',
                        'query' => array(
                            array(
                                'id' => 1,
                                'name' => $this->l('Yes', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id' => 0,
                                'name' => $this->l('No', 'AdminEtsACCampaignController')
                            ), array(

                                'id' => 2,
                                'name' => $this->l('Both', 'AdminEtsACCampaignController')
                            ),
                        )
                    ),
                    'default_value' => 2,
                    'form_group_class' => 'has_product_in_cart',
                ),
            ],
            trim($this->type) === 'customer' ? [
                'has_placed_orders' => array(
                    'name' => 'has_placed_orders',
                    'label' => $this->l('Has placed orders?', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'all',
                                'name' => $this->l('All', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => 'yes',
                                'name' => $this->l('Yes', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => 'no',
                                'name' => $this->l('No', 'AdminEtsACCampaignController')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'objects' => 'customer',
                    'default_value' => 'all',
                    'form_group_class' => 'has_placed_orders',
                ),
                'min_total_order' => array(
                    'name' => 'min_total_order',
                    'label' => $this->l('Total order value', 'AdminEtsACCampaignController'),
                    'prefix' => $this->l('From', 'AdminEtsACCampaignController'),
                    'suffix' => $this->context->currency->iso_code,
                    'type' => 'text',
                    'form_group_class' => 'min_total_order',
                    'to' => 'max_total_order',
                    'validate' => 'isUnsignedFloat',
                ),
                'max_total_order' => array(
                    'name' => 'max_total_order',
                    'label' => $this->l('Total order value', 'AdminEtsACCampaignController'),
                    'prefix' => $this->l('To', 'AdminEtsACCampaignController'),
                    'suffix' => $this->context->currency->iso_code,
                    'type' => 'text',
                    'validate' => 'isUnsignedFloat',
                ),
            ] : [
                'min_total_cart' => array(
                    'name' => 'min_total_cart',
                    'label' => $this->l('Total cart value', 'AdminEtsACCampaignController'),
                    'prefix' => $this->l('From', 'AdminEtsACCampaignController'),
                    'suffix' => $this->context->currency->iso_code,
                    'type' => 'text',
                    'to' => 'max_total_cart',
                    'validate' => 'isUnsignedFloat',
                    'form_group_class' => 'ets_ac_minmax_total_cart'
                ),
                'max_total_cart' => array(
                    'name' => 'max_total_cart',
                    'label' => $this->l('Total cart value', 'AdminEtsACCampaignController'),
                    'prefix' => $this->l('To', 'AdminEtsACCampaignController'),
                    'suffix' => $this->context->currency->iso_code,
                    'type' => 'text',
                    'validate' => 'isUnsignedFloat',
                    'form_group_class' => 'ets_ac_minmax_total_cart'
                ),
                'has_applied_voucher' => array(
                    'name' => 'has_applied_voucher',
                    'label' => $this->l('Cart has applied a voucher code?', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'yes',
                                'name' => $this->l('Yes', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => 'no',
                                'name' => $this->l('No', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => 'both',
                                'name' => $this->l('Both', 'AdminEtsACCampaignController')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default_value' => 'both',
                ),
            ],
            [
                'last_order_from' => array(
                    'name' => 'last_order_from',
                    'label' => $this->l('Last order from', 'AdminEtsACCampaignController'),
                    'prefix' => $this->l('From', 'AdminEtsACCampaignController'),
                    'type' => 'date',
                    'desc' => $this->l('Leave blank for unlimited time', 'AdminEtsACCampaignController'),
                    'to' => 'last_order_to',
                    'form_group_class' => 'last_order_from',
                    'validate' => 'isDate',
                ),
                'last_order_to' => array(
                    'name' => 'last_order_to',
                    'label' => $this->l('Last order to', 'AdminEtsACCampaignController'),
                    'prefix' => $this->l('To', 'AdminEtsACCampaignController'),
                    'type' => 'date',
                    'validate' => 'isDate',
                ),
                'purchased_product' => array(
                    'name' => 'purchased_product',
                    'label' => $this->l('Has purchased product', 'AdminEtsACCampaignController'),
                    'col' => 3,
                    'type' => 'text',
                    'form_group_class' => 'purchased_product',
                    'validate' => 'isCleanHtml',
                ),
                'not_purchased_product' => array(
                    'name' => 'not_purchased_product',
                    'label' => $this->l('Has not purchased product', 'AdminEtsACCampaignController'),
                    'col' => 3,
                    'type' => 'text',
                    'form_group_class' => 'not_purchased_product',
                    'validate' => 'isCleanHtml',
                ),
                'countries' => array(
                    'name' => 'countries',
                    'label' => $this->l('Countries', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'required' => true,
                    'multiple' => true,
                    'options' => array(
                        'query' => $countries_array,
                        'id' => 'id_country',
                        'name' => 'name'
                    ),
                    'default_value' => [0],
                    'form_group_class' => 'countries'
                ),
                'languages' => array(
                    'name' => 'languages',
                    'label' => $this->l('Languages', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'required' => true,
                    'multiple' => true,
                    'options' => array(
                        'query' => $languages_array,
                        'id' => 'id_lang',
                        'name' => 'name'
                    ),
                    'default_value' => [0],
                ),
            ],
            $this->type == 'email' ? array(
                'newsletter' => array(
                    'name' => 'newsletter',
                    'label' => $this->l('Customers have subscribed to receive newsletter?', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 1,
                                'name' => $this->l('Yes', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => 0,
                                'name' => $this->l('No', 'AdminEtsACCampaignController')
                            ),
                            array(
                                'id_option' => 2,
                                'name' => $this->l('Both', 'AdminEtsACCampaignController')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default_value' => 2,
                )
            ) : array(),
            array(
                'enabled' => array(
                    'name' => 'enabled',
                    'label' => $this->l('Enabled', 'AdminEtsACCampaignController'),
                    'type' => 'switch',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'AdminEtsACCampaignController')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'AdminEtsACCampaignController')
                        ),
                    ),
                    'default_value' => 1,
                )
            )
        );
        if ($this->type == 'email' || $this->type == 'customer') {
            unset($formFields['has_product_in_cart']);
        }
        if ($this->type != 'customer') {
            unset($formFields['email_timing_option']);
        }
        return $formFields;
    }

    public function initProcess()
    {
        parent::initProcess();

        $this->toolbar_title = (isset($this->reminderType[$this->type]) ? $this->reminderType[$this->type] : '') . ' ' . ($this->display == 'edit' || $this->display == 'add' ? $this->l('campaign', 'AdminEtsACCampaignController') . (($id_campaign = (int)Tools::getValue('id_ets_abancart_campaign')) ? ' #' . $id_campaign : '') : $this->l('campaigns', 'AdminEtsACCampaignController'));
        if ($this->type && ($this->display == 'add' || $this->display == 'edit')) {
            $this->toolbar_title = ($this->display == 'add' ? $this->l('Add', 'AdminEtsACCampaignController') : '') . ($this->display == 'edit' ? $this->l('Edit', 'AdminEtsACCampaignController') : '') . ' ' . $this->toolbar_title;
            $this->fields_form = array(
                'legend' => array(
                    'title' => $this->toolbar_title,
                    //'icon' => 'icon-group',
                ),
                'input' => $this->getFieldsForm()
            );
        }
    }

    public function renderForm()
    {
        if (!$this->loadObject(true)) {
            //return;
        }

        // Multi Shops.
        if (isset($this->context->shop->id) && $this->context->shop->id) {
            $this->fields_form['input'][] = array(
                'type' => 'hidden',
                'label' => $this->l('Shop ID', 'AdminEtsACCampaignController'),
                'name' => 'id_shop',
                'default_value' => $this->context->shop->id,
            );
        }

        // Campaign Type.
        $this->fields_form['input'][] = array(
            'name' => 'campaign_type',
            'type' => 'hidden',
            'label' => $this->l('Shop ID', 'AdminEtsACCampaignController'),
            'default_value' => $this->type,
        );

        // Buttons.
        $this->fields_form['buttons'] = array(
            'back' => array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'title' => $this->l('Back to list', 'AdminEtsACCampaignController'),
                'icon' => 'process-icon-back',
                'class' => 'ets_abancart_process_back',
            ),
            'save-and-stay' => array(
                'title' => $this->l('Save', 'AdminEtsACCampaignController'),
                'name' => 'submitAdd' . $this->table . 'AndStay',
                'type' => 'submit',
                'class' => 'btn btn-default pull-right',
                'icon' => 'process-icon-save',
            ),
        );

        // Redirect.
        if ($this->display == 'edit' || $this->display == 'add') {
            self::$currentIndex .= ($this->id_object ? '&' . $this->identifier . '=' . $this->id_object : '')
                . ($this->display == 'add' ? '&add' . $this->list_id : '')
                . ($this->display == 'edit' ? '&update' . $this->list_id : '');
        }
        return parent::renderForm();
    }

    public function getFieldsValue($obj)
    {
        parent::getFieldsValue($obj);

        if (is_array($this->fields_form) && count($this->fields_form) > 0) {
            foreach ($this->fields_form as $fieldset) {
                if (isset($fieldset['form']['input']) && is_array($fieldset['form']['input']) && count($fieldset['form']['input']) > 0) {
                    foreach ($fieldset['form']['input'] as $key => $field) {
                        if (isset($field['multiple']) && $field['multiple']) {
                            $this->fields_value[$key . '[]'] = $this->fields_value[$key];
                        }
                    }
                }
            }
        }

        return $this->fields_value;
    }

    public function processAdd()
    {
        if ($this->object = parent::processAdd()) {
            // Save and stay on same form
            if (empty($this->redirect_after) && $this->redirect_after !== false && Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
                $this->redirect_after = self::$currentIndex . '&' . $this->identifier . '=' . $this->object->id . '&conf=3&view' . $this->table;
            }
        }

        return $this->object;
    }

    public function processUpdate()
    {
        if ($object = parent::processUpdate()) {
            if (Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
                $this->redirect_after = self::$currentIndex . '&' . $this->identifier . '=' . $object->id . '&conf=4&view' . $this->table . '&token=' . $this->token;
            }
        }

        return $object;
    }

    public function processDelete()
    {
        if (Validate::isLoadedObject($object = $this->loadObject())) {
            if ($object->delete()) {
                $this->redirect_after = self::$currentIndex . '&conf=1&token=' . $this->token;
            }
        } else
            $this->errors[] = $this->l('An error occurred while deleting the object.', 'AdminEtsACCampaignController');

        return $object;
    }

    public function ajaxProcessStatus()
    {
        if (Tools::isSubmit('status' . $this->table)) {
            $object = $this->loadObject();

            if (Validate::isLoadedObject($object)) {
                if (property_exists($object, 'enabled')) {
                    $object->enabled = !(int)$object->enabled;
                }
                if (!$object->update()) {
                    $this->errors[] = $this->l('An error occurred while updating the status.', 'AdminEtsACCampaignController');
                }
            } else {
                $this->errors[] = $this->l('An error occurred while updating the status for an object.', 'AdminEtsACCampaignController');
            }
            $hasError = count($this->errors) > 0;
            $this->toJson(array(
                'hasError' => $hasError,
                'enabled' => $object->enabled,
                'msg' => $hasError ? $this->module->displayError($this->errors) : $this->l('Update status successfully', 'AdminEtsACCampaignController'),
            ));
        }
    }

    public function ajaxProcessSearchProduct()
    {
        $q = ($q = Tools::getValue('q')) && Validate::isCleanHtml($q) ? $q : '';
        $excludeIds = Tools::getValue('excludeIds');
        $excludePackItself = Tools::getValue('packItself');
        $excludeVirtuals = (int)Tools::getValue('excludeVirtuals') ? 1 : 0;
        $exclude_packs = (int)Tools::getValue('exclude_packs') ? 1 : 0;

        if (!$q ||
            !Validate::isCleanHtml($q) ||
            (trim($excludeIds) !== '' && $excludeIds !== 'NaN' && ($excludeIds = explode(',', $excludeIds)) && !Validate::isArrayWithIds($excludeIds)) ||
            (trim($excludePackItself) !== '' && !Validate::isUnsignedInt($excludePackItself))
        ) {
            die;
        }
        if (($products = EtsAbancartCampaign::findProducts($q, $excludeIds, $excludePackItself, $excludeVirtuals, $exclude_packs)) && is_array($products) && count($products) > 0) {
            foreach ($products as $item) {

                if (Tools::isSubmit('getAttribute')) {
                    $attrs = (new Product($item['id_product']))->getAttributeCombinations($this->context->language->id);

                    if ($attrs) {
                        $combinations = array();
                        foreach ($attrs as $attr) {
                            if (!isset($combinations[$attr['id_product_attribute']])) {
                                $combinations[$attr['id_product_attribute']] = $attr['group_name'] . ': ' . $attr['attribute_name'];
                            } else {
                                $combinations[$attr['id_product_attribute']] .= ' - ' . $attr['group_name'] . ': ' . $attr['attribute_name'];
                            }
                        }
                        foreach ($combinations as $id_attr => $combination) {
                            $product = array(
                                'id' => (int)($item['id_product']),
                                'name' => $item['name'],
                                'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                                'image' => str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($item['link_rewrite'], $item['id_image'], EtsAbancartTools::getImageType('home'))),
                                'link' => $this->context->link->getProductLink((int)$item['id_product'], $item['link_rewrite'], null, null, $this->context->language->id),
                                'id_product_attribute' => $id_attr,
                                'attribute_name' => $combination,
                            );
                            echo implode('|', $product) . PHP_EOL;
                        }
                    } else {
                        $product = array(
                            'id' => (int)($item['id_product']),
                            'name' => $item['name'],
                            'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                            'image' => str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($item['link_rewrite'], $item['id_image'], EtsAbancartTools::getImageType('home'))),
                            'link' => $this->context->link->getProductLink((int)$item['id_product'], $item['link_rewrite'], null, null, $this->context->language->id),
                            'id_product_attribute' => 0,
                            'attribute_name' => '',
                        );
                        echo implode('|', $product) . PHP_EOL;
                    }
                } else {
                    $product = array(
                        'id' => (int)($item['id_product']),
                        'name' => $item['name'],
                        'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                        'image' => str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($item['link_rewrite'], $item['id_image'], EtsAbancartTools::getImageType('home'))),
                        'link' => $this->context->link->getProductLink((int)$item['id_product'], $item['link_rewrite'], null, null, $this->context->language->id)
                    );
                    echo implode('|', $product) . PHP_EOL;
                }
            }
        }
        die;
    }

    public function validateRules($class_name = false)
    {
        parent::validateRules($class_name);

        $countries = ($countries = Tools::getValue('countries')) && is_array($countries) ? array_map('intval', $countries) : array();
        $languages = ($languages = Tools::getValue('languages')) && is_array($languages) ? array_map('intval', $languages) : array();
        $has_applied_voucher = ($has_applied_voucher = Tools::getValue('has_applied_voucher')) && Validate::isCleanHtml($has_applied_voucher) ? $has_applied_voucher : '';
        $has_placed_orders = ($has_placed_orders = Tools::getValue('has_placed_orders')) && Validate::isCleanHtml($has_placed_orders) ? $has_placed_orders : '';
        $last_order_from = ($last_order_from = Tools::getValue('last_order_from')) && Validate::isDate($last_order_from) ? $last_order_from : '';
        $last_order_to = ($last_order_to = Tools::getValue('last_order_to')) && Validate::isDate($last_order_to) ? $last_order_to : '';
        $purchased_product = ($purchased_product = Tools::getValue('purchased_product')) && Validate::isCleanHtml($purchased_product) ? $purchased_product : '';
        $not_purchased_product = ($not_purchased_product = Tools::getValue('not_purchased_product')) && Validate::isCleanHtml($not_purchased_product) ? $not_purchased_product : '';
        $newsletter = (int)Tools::getValue('newsletter');
        $email_timing_option = ($email_timing_option = Tools::getValue('email_timing_option')) && Validate::isCleanHtml($email_timing_option) ? $email_timing_option : '';
        $has_product_in_cart = Tools::getValue('has_product_in_cart');
        if (!Validate::isUnsignedInt($has_product_in_cart))
            $has_product_in_cart = 0;
        $sql = '
                SELECT ' . pSQL($this->identifier) . ',is_all_country,is_all_lang FROM ' . _DB_PREFIX_ . $this->table . ' c
                WHERE `enabled` = 1
                    AND `deleted` = 0
	                AND `available_from`' . (($available_from = Tools::getValue('available_from')) != '' && Validate::isDate($available_from) ? ' = "' . $available_from . '"' : ' is NULL ') . '
	                AND `available_to`' . (($available_to = Tools::getValue('available_to')) != '' && Validate::isDate($available_to) ? ' = "' . $available_to . '"' : ' is NULL ') . '
	                ' . ($this->type != 'customer' ? '
	                    AND `min_total_cart`' . (($min_total_cart = Tools::getValue('min_total_cart')) != '' && Validate::isFloat($min_total_cart) ? ' = ' . (float)$min_total_cart : ' is NULL ') . '
	                    AND `max_total_cart`' . (($max_total_cart = Tools::getValue('max_total_cart')) != '' && Validate::isFloat($max_total_cart) ? ' = ' . (float)$max_total_cart : ' is NULL ') . '' : ' AND email_timing_option = \'' . $email_timing_option . '\'  AND has_placed_orders = \'' . (($has_placed_orders = trim(Tools::getValue('has_placed_orders'))) && Validate::isCleanHtml($has_placed_orders) ? pSQL($has_placed_orders) : '') . '\''
            ) . '
                    AND campaign_type =\'' . pSQL($this->type) . '\' 
                    ' . ($this->type !== 'customer' ? ' AND has_applied_voucher="' . pSQL($has_applied_voucher) . '"' : ' AND has_placed_orders="' . pSQL($has_placed_orders) . '" AND last_order_from' . ($last_order_from ? '="' . $last_order_from . '"' : ' IS NULL') . ' AND last_order_to' . ($last_order_to ? '="' . $last_order_to . '"' : ' IS NULL')) . ' 
                    ' . ($this->type == 'email' ? ' AND newsletter=' . (int)$newsletter : '') . ' 
                    ' . ($this->type == 'customer' ? ' AND purchased_product' . ($purchased_product ? '="' . pSQL($purchased_product) . '"' : ' IS NULL') . ' AND not_purchased_product' . ($not_purchased_product ? '="' . pSQL($not_purchased_product) . '"' : ' IS NULL') : '') . ' 
                    ' . (in_array(0, $countries) ? 'AND is_all_country=1' : '') . ' 
                    ' . (in_array(0, $languages) ? 'AND is_all_lang=1' : '') . '
                    ' . ($this->id_object ? ' AND ' . pSQL($this->identifier) . ' !=' . (int)$this->id_object : '') . '
                    ' . (!in_array($this->type, [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER]) ? ' AND has_product_in_cart = ' . (int)$has_product_in_cart : '') . '
                    AND c.id_shop = ' . (int)$this->context->shop->id . '
            ';
        if (!$this->errors && ($campaigns = EtsAbancartTools::doSqlFilter($sql))) {
            $isTheSame = false;

            foreach ($campaigns as $campaign) {
                $isSameLang = false;
                $isSameCountry = false;
                if ((int)$campaign['is_all_country'] && (int)$campaign['is_all_lang']) {
                    if (in_array(0, $countries) && in_array(0, $languages)) {
                        $isTheSame = true;
                    }
                    break;
                }
                $listCountries = $countries;
                $listLangs = $languages;
                $k1 = array_search(0, $listCountries);
                if ($k1 !== false) {
                    unset($listCountries[$k1]);
                }
                $k2 = array_search(0, $listLangs);
                if ($k2 !== false) {
                    unset($listLangs[$k2]);
                }
                if (!(int)$campaign['is_all_country']) {
                    $idsCountry = EtsAbancartCampaign::getCountryIdsOfCampaign($campaign['id_ets_abancart_campaign']);
                    if ($idsCountry) {
                        $c1 = 0;
                        foreach ($idsCountry as $c) {
                            if (in_array((int)$c['id_country'], $listCountries)) {
                                $c1++;
                            }
                        }
                        if ($c1 == count($listCountries)) {
                            $isSameCountry = true;
                        }
                    }
                }
                if (!(int)$campaign['is_all_lang']) {
                    $idsLang = EtsAbancartCampaign::getLangIdsOfCampaign($campaign['id_ets_abancart_campaign']);
                    if ($idsLang) {
                        $c2 = 0;
                        foreach ($idsLang as $l) {
                            if (in_array((int)$l['id_lang'], $listLangs)) {
                                $c2++;
                            }
                        }
                        if ($c2 == count($listLangs)) {
                            $isSameLang = true;
                        }
                    }
                }
                if ($isSameLang && $isSameCountry) {
                    $isTheSame = true;
                    break;
                }

            }
            if ($isTheSame)
                $this->errors[] = $this->l('Another campaign is using the same condition.', 'AdminEtsACCampaignController');
        }
        $id = (int)Tools::getValue($this->identifier);
        if (count($this->errors) < 1
            && $id > 0
            && ($campaignObj = new EtsAbancartCampaign($id))
            && !in_array($campaignObj->campaign_type, [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER])
            && (int)$has_product_in_cart !== EtsAbancartCampaign::HAS_SHOPPING_CART_YES
            && EtsAbancartReminder::hasVoucherInReminder($campaignObj->id) > 0
        ) {
            $this->errors[] = $this->l('Discount code in reminder content is invalid. Please remove the discount code and the related contents.', 'AdminEtsACCampaignController');
        }
    }

    public function customValidate($key, $input)
    {
        if ($key == 'available_from' && trim(($from = Tools::getValue($key))) != '' && !Validate::isDate($from)) {
            $this->errors[] = $this->l('"Available from" is invalid', 'AdminEtsACCampaignController');
        } elseif ($key == 'available_to' && trim(($to = Tools::getValue($key))) != '' && !Validate::isDate($to)) {
            $this->errors[] = $this->l('"Available to" is invalid', 'AdminEtsACCampaignController');
        } elseif ($key == 'available_from' && trim(($from = Tools::getValue($key))) != '' && trim(($to = Tools::getValue('available_to'))) != '' && Validate::isCleanHtml($to) && Validate::isCleanHtml($from) && strtotime($to) < strtotime($from)) {
            $this->errors[] = $input['label'] . ' ' . $this->l('"From" must be less than or equal with "To"', 'AdminEtsACCampaignController');
        } elseif ($key == 'min_total_cart' && trim(($min = Tools::getValue($key))) != '' && !Validate::isUnsignedFloat($min)) {
            $this->errors[] = $input['label'] . ' ' . $this->l('"from" is invalid', 'AdminEtsACCampaignController');
        } elseif ($key == 'max_total_cart' && trim(($max = Tools::getValue($key))) != '' && !Validate::isUnsignedFloat($max)) {
            $this->errors[] = $input['label'] . ' ' . $this->l('"to" is invalid', 'AdminEtsACCampaignController');
        } elseif ($key == 'min_total_cart' && trim(($min = Tools::getValue($key))) != '' && trim(($max = Tools::getValue('max_total_cart'))) != '' && (float)$max < (float)$min) {
            $this->errors[] = $input['label'] . ' ' . $this->l('"Total cart value from" must be less than or equal with "Total cart value to"', 'AdminEtsACCampaignController');
        }
        return parent::customValidate($key, $input);
    }

    public function campaignType($value)
    {
        return isset($this->reminderType[$value]) && $this->reminderType[$value] ? $this->reminderType[$value] : '--';
    }

    public function printReminder($value)
    {
        $this->context->smarty->assign(array(
            'value' => $value,
            'badge' => 'danger-hover',
        ));
        return $this->createTemplate('badge.tpl')->fetch();
    }

    public function displayEditLink($token, $id)
    {
        if (!isset(self::$cache_lang['edit'])) {
            self::$cache_lang['edit'] = $this->l('Edit', 'AdminEtsACCampaignController');
        }
        $object = new EtsAbancartCampaign($id);
        $this->context->smarty->assign(array(
            'action' => self::$cache_lang['edit'],
            'href' => $this->context->link->getAdminLink(Ets_abandonedcart::$slugTab . 'Reminder' . Tools::ucfirst($object->campaign_type), $token) . '&' . $this->identifier . '=' . $id . '&update' . $this->table,
        ));
        return $this->context->smarty->fetch('helpers/list/list_action_edit.tpl');
    }

    public function displayDeleteLink($token, $id)
    {
        if ($this->className == 'EtsAbancartReminder' || $this->isViewCampaign) {
            if (!isset(self::$cache_lang['delete'])) {
                self::$cache_lang['delete'] = $this->l('Delete', 'AdminEtsACCampaignController');
            }
            $reminder = new EtsAbancartReminder($id);
            $campaign = new EtsAbancartCampaign($reminder->id_ets_abancart_campaign);
            $controller = Ets_abandonedcart::$slugTab . 'Reminder' . Tools::ucfirst($campaign->campaign_type);
            $this->context->smarty->assign(array(
                'href' => 'index.php?controller=' . $controller . '&token=' . Tools::getAdminTokenLite($controller) . '&id_ets_abancart_reminder=' . $id . '&deleteets_abancart_reminder',
                'action' => self::$cache_lang['delete'],
                'token' => $token,
                'msg_confirm' => $this->l('Do you want to delete selected item(s)?', 'AdminEtsACCampaignController'),
            ));
            return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/' . $this->override_folder . 'helpers/list/list_action_delete.tpl');
        }
        if (($object = new $this->className($id)) && $object->campaign_type) {
            if (!isset(self::$cache_lang['delete'])) {
                self::$cache_lang['delete'] = $this->l('Delete', 'AdminEtsACCampaignController');
            }
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminLink(Ets_abandonedcart::$slugTab . 'Reminder' . Tools::ucfirst($object->campaign_type)) . '&' . $this->identifier . '=' . $id . '&delete' . $this->table,
                'action' => self::$cache_lang['delete'],
                'token' => $token,
                'msg_confirm' => $this->l('Do you want to delete selected item(s)?', 'AdminEtsACCampaignController'),
            ));
            return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/' . $this->override_folder . 'helpers/list/list_action_delete.tpl');
        }
    }

    public function renderView()
    {
        $times_series = array(
            'all' => array(
                'label' => $this->l('All', 'AdminEtsACCampaignController'),
            ),
            'this_year' => array(
                'label' => $this->l('This year', 'AdminEtsACCampaignController'),
                'default' => 1,
            ),
            'last_year' => array(
                'label' => $this->l('Last year', 'AdminEtsACCampaignController'),
            ),
            'this_month' => array(
                'label' => $this->l('This month', 'AdminEtsACCampaignController'),
            ),
            'last_month' => array(
                'label' => $this->l('Last month', 'AdminEtsACCampaignController'),
            ),
            'today' => array(
                'label' => $this->l('Today', 'AdminEtsACCampaignController'),
            ),
            'yesterday' => array(
                'label' => $this->l('Yesterday', 'AdminEtsACCampaignController'),
            ),
            'time_range' => array(
                'label' => $this->l('Time range', 'AdminEtsACCampaignController'),
            ),
        );

        $id_campaign = (int)Tools::getValue('id_ets_abancart_campaign');
        $campaign = new EtsAbancartCampaign($id_campaign, $this->context->language->id);
        $controller = ($controller = Tools::getValue('controller')) && Validate::isCleanHtml($controller) ? $controller : '';
        switch ($campaign->campaign_type) {
            case 'email':
                $controller = 'AdminEtsACReminderEmail';
                break;
            case 'customer':
                $controller = 'AdminEtsACReminderCustomer';
                break;
            case 'popup':
                $controller = 'AdminEtsACReminderPopup';
                break;
            case 'bar':
                $controller = 'AdminEtsACReminderBar';
                break;
            case 'leave':
                $controller = 'AdminEtsACReminderLeave';
                break;
            case 'browser':
                $controller = 'AdminEtsACReminderBrowser';
                break;
        }

        $this->controllerList = $controller;
        $this->tpl_view_vars = array(
            'campaign' => $campaign,
            'campaign_groups' => EtsAbancartCampaign::getCampaignGroup($campaign->id, $this->context->language->id),
            'is_all_country' => $campaign->is_all_country,
            'campaign_countries' => $campaign->is_all_country ? array() : EtsAbancartCampaign::getCampaignCountries($campaign->id, $campaign->is_all_country),
            'is_all_lang' => $campaign->is_all_lang,
            'campaign_languages' => $campaign->is_all_lang ? array() : EtsAbancartCampaign::getCampaignLanguages($campaign->id, $campaign->is_all_lang),
            'time_series' => $times_series,
            'line_chart' => EtsAbancartReminderForm::getInstance()->getLineChartCampaign('this_year', $id_campaign),
            'last_email_sent' => EtsAbancartCampaign::getEmailSent($id_campaign, 10, null, null, $this->context),
            'table_reminder' => $this->getReminders($id_campaign),
            'countReminder' => EtsAbancartReminder::getTotalReminder($id_campaign),
            'emailSendOption' => EtsAbancartReminderForm::getInstance()->getCustomerEmailSendOptions(),
            'linkAddReminder' => 'index.php?controller=' . $controller . '&token=' . Tools::getAdminTokenLite($controller) . '&id_ets_abancart_campaign=' . $id_campaign . '&addets_abancart_campaign',
            'linkEditCampaign' => $this->context->link->getAdminLink($controller) . '&id_ets_abancart_campaign=' . $id_campaign . '&updateets_abancart_campaign',
            'linkSubmitExport' => $this->context->link->getAdminLink('AdminEtsACReminderEmail') . '&id_ets_abancart_campaign=' . $id_campaign . '&exportCampaignTracking=1',
            'purchasedProducts' => $campaign->campaign_type == 'customer' && $campaign->has_placed_orders != 'no' && $campaign->purchased_product ? EtsAbancartReminderForm::getInstance()->displayListProduct('ets_ac_purchased_product', explode(',', $campaign->purchased_product), 'ets_ac_purchased_product', '', false) : '',
            'notPurchasedProducts' => $campaign->campaign_type == 'customer' && $campaign->has_placed_orders != 'no' && $campaign->not_purchased_product ? EtsAbancartReminderForm::getInstance()->displayListProduct('ets_ac_not_purchased_product', explode(',', $campaign->not_purchased_product), 'ets_ac_not_purchased_product', '', false) : '',
        );
        $this->context->smarty->assign(array(
            'campaignName' => $campaign->name
        ));
        $this->meta_title = '';
        return parent::renderView();
    }

    public function getReminders($id_campaign)
    {
        $campaign = new EtsAbancartCampaign($id_campaign, $this->context->language->id);
        $this->toolbar_title = $this->l('Reminders', 'AdminEtsACCampaignController');
        $this->table = 'ets_abancart_reminder';
        $this->list_id = $this->table;
        $this->toolbar_btn['new'] = array(
            'href' => 'index.php?controller=' . $this->controllerList . '&add' . $this->table
                . '&id_ets_abancart_campaign=' . (int)$id_campaign
                . '&token=' . Tools::getAdminTokenLite($this->controllerList),
            'desc' => $this->l('Add new', 'AdminEtsACCampaignController'),
        );
        $this->_select = '
            (86400*IFNULL(a.day, 0) + 3600*IFNULL(a.hour, 0) + 60*IFNULL(a.minute, 0) + IFNULL(a.second, 0)) as `time_sec`
            , (@rank:=@rank + ' . (isset($this->context->cookie->{$this->list_id . '_start'}) && $this->context->cookie->{'ets_abancart_reminder' . '_start'} ? (int)$this->context->cookie->{'ets_abancart_reminder' . '_start'} : 0) . ' + 1) as `index`
            , SUM(at.total_execute_times) as `execute_times`
            , SUM(IF(at.delivered=1, 1, NULL)) as `success`
            , SUM(IF(at.delivered=0, 1, NULL)) as `failed`
            , SUM(IF(at.`read` > 0, 1, NULL)) as `read`
            , qu.`queue`
            , SUM(IF(ic.id_ets_abancart_reminder > 0, 1, 0)) as `total_index`
            , SUM(IF(at.id_ets_abancart_reminder > 0, 1, 0)) as `total_tracking`
            , ac.campaign_type
        ';

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_tracking` at ON (at.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = a.id_ets_abancart_campaign)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ic ON (ic.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
            LEFT JOIN ( 
                SELECT SUM(IF(qu2.id_ets_abancart_reminder > 0, 1, NULL)) as `queue`, qu2.id_ets_abancart_reminder
                FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` qu2 
                GROUP BY qu2.id_ets_abancart_reminder
            ) qu ON (qu.id_ets_abancart_reminder = at.id_ets_abancart_reminder), (SELECT @rank:=0) y
        ';
        $this->_group = 'GROUP BY a.id_ets_abancart_reminder';
        $this->_where = ' AND a.id_ets_abancart_campaign=' . (int)$id_campaign;
        $this->identifier = 'id_ets_abancart_reminder';
        $this->className = 'EtsAbancartReminder';
        $this->fields_list = $this->getFieldsList($campaign->campaign_type);
        $this->actions = [];
        $this->addRowAction('edit');
        $this->addRowAction('viewtracking');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function getFieldsList($type)
    {
        return array_merge(
            [
                'index' => array(
                    'title' => $this->l('Order', 'AdminEtsACCampaignController'),
                    'type' => 'int',
                    'search' => false,
                    'orderby' => false,
                    'class' => 'fixed-width-xs center',
                    'callback' => 'printReminder',
                ),
                'id_ets_abancart_reminder' => array(
                    'title' => $this->l('ID', 'AdminEtsACCampaignController'),
                    'type' => 'int',
                    'class' => 'fixed-width-xs center',
                    'filter_key' => 'a!id_ets_abancart_reminder',
                ),
                'title' => array(
                    'title' => (trim($type) !== 'email' || trim($type) !== 'customer' ? $this->l('Email subject', 'AdminEtsACCampaignController') : $this->l('Title', 'AdminEtsACCampaignController')),
                    'type' => 'text',
                    'filter_key' => 'b!title',
                ),
                'execute_times' => array(
                    'title' => $this->l('Execute times', 'AdminEtsACCampaignController'),
                    'type' => 'text',
                    'havingFilter' => true,
                    'search' => false,
                    'class' => 'fixed-width-lg center',
                    'callback' => 'displayReminderExecuteTimes',
                ),
            ],
            $this->getListFrequency($type),
            [
                'discount_option' => array(
                    'title' => $this->l('Discount', 'AdminEtsACCampaignController'),
                    'type' => 'select',
                    'list' => array(
                        'no' => $this->l('No discount', 'AdminEtsACCampaignController'),
                        'fixed' => $this->l('Fixed discount code', 'AdminEtsACCampaignController'),
                        'auto' => $this->l('Generate discount code automatically', 'AdminEtsACCampaignController'),
                    ),
                    'filter_key' => 'a!discount_option',
                    'callback' => 'discountOption',
                ),
                'enabled' => array(
                    'title' => $this->l('Active', 'AdminEtsACCampaignController'),
                    'type' => 'bool',
                    'active' => 'status',
                    'filter_key' => 'a!enabled',
                    'class' => 'center',
                    'remove_onclick' => true
                ),
            ],
            $type == 'customer' ? array(
                'status' => array(
                    'title' => $this->l('Status', 'AdminEtsACCampaignController'),
                    'type' => 'text',
                    'search' => false,
                    'orderby' => false,
                    'float' => true,
                    'class' => 'status',
                    'remove_onclick' => true
                ),
            ) : array()
        );
    }

    public function getListFrequency($type)
    {
        $fields_list = [];
        switch ($type) {
            case 'email':
            case 'customer':
                $fields_list += [
                    'day' => array(
                        'title' => $this->l('Day(s)', 'AdminEtsACCampaignController'),
                        'type' => 'text',
                        'filter_key' => 'a!day',
                        'class' => 'center',
                        'form_group_class' => 'width_200'
                    ),
                    'hour' => array(
                        'title' => $this->l('Hour(s)', 'AdminEtsACCampaignController'),
                        'type' => 'text',
                        'filter_key' => 'a!hour',
                        'class' => 'center',
                        'form_group_class' => 'width_200'
                    ),
                ];
                break;
            case 'bar':
            case 'browser':
            case 'popup':
                $fields_list += [
                    'minute' => array(
                        'title' => $this->l('Minute(s)', 'AdminEtsACCampaignController'),
                        'type' => 'text',
                        'filter_key' => 'a!minute',
                        'class' => 'center',
                        'form_group_class' => 'width_200'
                    ),
                    'second' => array(
                        'title' => $this->l('Second(s)', 'AdminEtsACCampaignController'),
                        'type' => 'text',
                        'filter_key' => 'a!second',
                        'class' => 'center',
                        'form_group_class' => 'width_200'
                    ),
                ];
                if (trim($type) === 'popup') {
                    $fields_list += [
                        'redisplay' => array(
                            'title' => $this->l('Redisplay after (min(s))', 'AdminEtsACCampaignController'),
                            'type' => 'text',
                            'filter_key' => 'a!redisplay',
                            'class' => 'center',
                            'form_group_class' => 'width_200'
                        ),
                    ];
                }
                break;
        }
        return $fields_list;
    }

    public function displayReminderExecuteTimes($execute_times, $tr)
    {
        return $this->displayExecuteTimes($execute_times, $tr);
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

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS(array(
            _PS_JS_DIR_ . 'admin/tinymce.inc.js',
            _PS_JS_DIR_ . 'tiny_mce/tiny_mce.js',
            _PS_JS_DIR_ . 'jquery/plugins/autocomplete/jquery.autocomplete.js',
            $this->mPath . 'views/js/chart.admin.js',
        ));
        $this->addJqueryPlugin('colorpicker');
    }

    public function displayViewTrackingLink($token, $id)
    {
        if (!isset(self::$cache_lang['viewtracking'])) {
            self::$cache_lang['viewtracking'] = $this->l('View tracking', 'AdminEtsACCampaignController');
        }
        $campaign = new AdminEtsACCampaignController($this->type);
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . (($parentId = (int)Tools::getValue($campaign->identifier)) ? '&' . $campaign->identifier . '=' . (int)$parentId : '') . '&viewtracking&' . $this->identifier . '=' . $id . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['viewtracking'],
        ));

        return $this->createTemplate('helpers/list/list_action_view_tracking.tpl')->fetch();
    }

    public function ajaxProcessViewTracking()
    {
        if ($this->access('edit')) {
            $this->loadObject(true);
            $trackings = EtsAbancartTracking::reminderLogs((int)Tools::getValue('id_ets_abancart_reminder'));
            $this->context->smarty->assign(array(
                'TRACKINGs' => $trackings,
                'TYPE' => $this->type,
            ));
            $this->toJson(array(
                'html' => $this->createTemplate('tracking.tpl')->fetch()
            ));
        }
    }

    public function displayViewLink($token, $id)
    {
        if (!isset(self::$cache_lang['view'])) {
            self::$cache_lang['view'] = $this->l('View', 'AdminEtsACCampaignController');
        }

        $campaign = new EtsAbancartCampaign($id);
        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminLink(Ets_abandonedcart::$slugTab . 'Reminder' . Tools::ucfirst($campaign->campaign_type), $token) . '&view' . $this->table . '&' . $this->identifier . '=' . $campaign->id,
            'action' => self::$cache_lang['view'],
        ));

        return $this->createTemplate('helpers/list/list_action_view.tpl')->fetch();
    }
}