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

class EtsAbancartDefines extends EtsAbancartCore
{
    static $sub_menus;
    static $menus;
    static $instance;
    static $mail_options;

    static $short_codes;
    // Configs
    static $configs;
    static $mail_configs;
    static $other_configs;
    static $mail_trans;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new EtsAbancartDefines();
        }
        return self::$instance;
    }

    static $leave_configs;

    public function getLeaveConfigs($getQuery = false)
    {
        $queryForm = array();
        $forms = array();
        if ($getQuery) {
            $forms = EtsAbancartForm::getAllForms(true);
            $queryForm = array(
                array(
                    'id_option' => '',
                    'name' => $this->l('None', 'EtsAbancartDefines')
                )
            );

        }
        foreach ($forms as $form) {
            $queryForm[] = array(
                'id_option' => $form['id_ets_abancart_form'],
                'name' => $form['name']
            );
        }
        if (!self::$leave_configs) {
            self::$leave_configs = array(
                'ETS_ABANCART_HAS_PRODUCT_IN_CART' => array(
                    'name' => 'ETS_ABANCART_HAS_PRODUCT_IN_CART',
                    'label' => $this->l('Has product in shopping cart?', 'EtsAbancartDefines'),
                    'type' => 'select',
                    'options' => array(
                        'id' => 'id',
                        'name' => 'name',
                        'query' => array(
                            array(
                                'id' => 1,
                                'name' => $this->l('Yes', 'EtsAbancartDefines')
                            ),
                            array(
                                'id' => 0,
                                'name' => $this->l('No', 'EtsAbancartDefines')
                            ), array(

                                'id' => 2,
                                'name' => $this->l('Both', 'EtsAbancartDefines')
                            ),
                        )
                    ),
                    'default_value' => 2,
                    'form_group_class' => 'ets_ac_config_popup_content'
                ),
                'ETS_ABANCART_DISCOUNT_OPTION' => array(
                    'label' => $this->l('Discount options', 'EtsAbancartDefines'),
                    'type' => 'radios',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'no',
                                'name' => $this->l('No discount', 'EtsAbancartDefines')
                            ),
                            array(
                                'id_option' => 'fixed',
                                'name' => $this->l('Fixed discount code', 'EtsAbancartDefines'),
                                'cart_rule_link' => $this->context->link->getAdminLink('AdminCartRules')
                            ),
                            array(
                                'id_option' => 'auto',
                                'name' => $this->l('Generate discount code automatically', 'EtsAbancartDefines')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default' => 'no',
                    'form_group_class' => 'leave discount_option is_parent1 ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_QUANTITY' => array(
                    'label' => $this->l('Total available', 'EtsAbancartDefines'),
                    'hint' => $this->l('The cart rule will be applied to the first X users', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'col' => '4',
                    'required' => true,
                    'form_group_class' => 'leave discount_option auto ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_QUANTITY_PER_USER' => array(
                    'label' => $this->l('Total available for each user', 'EtsAbancartDefines'),
                    'hint' => $this->l('A customer will only be able to use this cart rule for X time(s).', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'col' => '4',
                    'required' => true,
                    'form_group_class' => 'leave discount_option auto ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_DISCOUNT_CODE' => array(
                    'label' => $this->l('Discount code', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'col' => '4',
                    'required' => true,
                    'form_group_class' => 'leave discount_option fixed ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_FREE_SHIPPING' => array(
                    'label' => $this->l('Free shipping', 'EtsAbancartDefines'),
                    'type' => 'switch',
                    'default' => 1,
                    'form_group_class' => 'leave discount_option auto ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_APPLY_DISCOUNT' => array(
                    'label' => $this->l('Apply a discount', 'EtsAbancartDefines'),
                    'type' => 'radios',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'percent',
                                'name' => $this->l('Percentage (%)', 'EtsAbancartDefines')
                            ),
                            array(
                                'id_option' => 'amount',
                                'name' => $this->l('Amount', 'EtsAbancartDefines')
                            ),
                            array(
                                'id_option' => 'off',
                                'name' => $this->l('None', 'EtsAbancartDefines')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default' => 'off',
                    'form_group_class' => 'leave discount_option auto ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_DISCOUNT_NAME' => array(
                    'label' => $this->l('Discount name', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'lang' => true,
                    'required' => true,
                    'form_group_class' => 'leave discount_option auto ets_ac_config_popup_content'
                ),

                'ETS_ABANCART_REDUCTION_PERCENT' => array(
                    'label' => $this->l('Discount percentage', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'suffix' => '%',
                    'col' => '4',
                    'required' => true,
                    'validate' => 'isUnsignedFloat',
                    'desc' => $this->l('Does not apply to the shipping costs', 'EtsAbancartDefines'),
                    'form_group_class' => 'leave discount_option auto apply_discount percent ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_REDUCTION_AMOUNT' => array(
                    'label' => $this->l('Amount', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'default' => '0',
                    'currencies' => Currency::getCurrencies(),
                    'tax' => array(
                        array(
                            'id_option' => 0,
                            'name' => $this->l('Tax excluded', 'EtsAbancartDefines')
                        ),
                        array(
                            'id_option' => 1,
                            'name' => $this->l('Tax included', 'EtsAbancartDefines')
                        ),
                    ),
                    'required' => true,
                    'validate' => 'isUnsignedFloat',
                    'form_group_class' => 'leave discount_option auto apply_discount amount ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_ID_CURRENCY' => array(
                    'label' => '',
                    'type' => 'select',
                    'options' => array(
                        'query' => Currency::getCurrencies(),
                        'id' => 'id_currency',
                        'name' => 'name',
                    ),
                    'default' => $this->context->currency->id,
                    'form_group_class' => 'leave ets_ac_config_popup_content'
                ),
                'ETS_ABANCART_REDUCTION_TAX' => array(
                    'label' => '',
                    'type' => 'select',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 0,
                                'name' => $this->l('Tax excluded', 'EtsAbancartDefines')
                            ),
                            array(
                                'id_option' => 1,
                                'name' => $this->l('Tax included', 'EtsAbancartDefines')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default' => '0',
                    'form_group_class' => 'leave ets_ac_config_popup_content'
                ),
                'ETS_ABANCART_APPLY_DISCOUNT_TO' => array(
                    'label' => $this->l('Apply a discount to', 'EtsAbancartDefines'),
                    'type' => 'radios',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'order',
                                'name' => $this->l('Order (without shipping)', 'EtsAbancartDefines')
                            ),
                            array(
                                'id_option' => 'specific',
                                'name' => $this->l('Specific product', 'EtsAbancartDefines'),
                            ),
                            array(
                                'id_option' => 'cheapest',
                                'name' => $this->l('Cheapest product', 'EtsAbancartDefines')
                            ),
                            array(
                                'id_option' => 'selection',
                                'name' => $this->l('Selected product(s)', 'EtsAbancartDefines')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default' => 'order',
                    'form_group_class' => 'leave discount_option auto apply_discount percent amount ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_REDUCTION_PRODUCT' => array(
                    'label' => $this->l('Products', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'default' => '',
                    'form_group_class' => 'leave discount_option auto apply_discount percent amount ets_ac_specific_product_group ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_SELECTED_PRODUCT' => array(
                    'label' => $this->l('Search product', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'default' => '',
                    'form_group_class' => 'leave discount_option auto apply_discount percent ets_ac_selected_product_group ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_REDUCTION_EXCLUDE_SPECIAL' => array(
                    'label' => $this->l('Exclude discounted products', 'EtsAbancartDefines'),
                    'type' => 'switch',
                    'default' => 0,
                    'form_group_class' => 'leave discount_option auto apply_discount percent ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_SEND_A_GIFT' => array(
                    'label' => $this->l('Send a free gift', 'EtsAbancartDefines'),
                    'type' => 'switch',
                    'default' => 0,
                    'form_group_class' => 'leave discount_option auto ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_PRODUCT_GIFT' => array(
                    'label' => $this->l('Search a product', 'EtsAbancartDefines'),
                    'type' => 'switch',
                    'default' => 0,
                    'form_group_class' => 'leave discount_option auto apply_discount percent amount off ets_ac_gift_product_filter_group ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_APPLY_DISCOUNT_IN' => array(
                    'label' => $this->l('Discount availability', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'required' => 'true',
                    'suffix' => $this->l('days', 'EtsAbancartDefines'),
                    'validate' => 'isUnsignedInt',
                    'col' => '4',
                    'default' => '1',
                    'form_group_class' => 'leave discount_option auto apply_discount is_parent2 ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_ENABLE_COUNTDOWN_CLOCK' => array(
                    'name' => 'ETS_ABANCART_ENABLE_COUNTDOWN_CLOCK',
                    'label' => $this->l('Enable discount countdown clock', 'EtsAbancartDefines'),
                    'type' => 'switch',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'EtsAbancartDefines')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'EtsAbancartDefines')
                        ),
                    ),
                    'form_group_class' => 'leave discount_option auto ets_ac_config_popup_content'
                ),
                'ETS_ABANCART_ALLOW_MULTI_DISCOUNT' => array(
                    'label' => $this->l('Can use with other voucher in the same shopping cart?', 'EtsAbancartDefines'),
                    'type' => 'switch',
                    'default' => 0,
                    'form_group_class' => 'leave discount_option auto fixed ets_ac_config_popup_content'
                ),
                'ETS_ABANCART_CONTENT' => array(
                    'label' => $this->l('Content', 'EtsAbancartDefines'),
                    'type' => 'textarea',
                    'lang' => true,
                    'autoload_rte' => true,
                    'required' => true,
                    'form_group_class' => 'leave ets_ac_config_popup_content',
                ),
                'ETS_ABANCART_POPUP_BG_COLOR' => array(
                    'name' => 'ETS_ABANCART_POPUP_BG_COLOR',
                    'label' => $this->l('Background color', 'EtsAbancartDefines'),
                    'type' => 'color',
                    'validate' => 'isColor',
                    'selector_change' => '.ets_abancart_preview',
                    'attr_change' => 'background-color',
                    'form_group_class' => 'leave isColor ets_ac_config_popup_item',
                    'default' => '#ffffff',
                ),
                'ETS_ABANCART_POPUP_WIDTH' => array(
                    'name' => 'ETS_ABANCART_POPUP_WIDTH',
                    'label' => $this->l('Width', 'EtsAbancartDefines'),
                    'type' => 'range',
                    'min' => 200,
                    'max' => 1200,
                    'default' => '640',
                    'unit' => $this->l('px', 'EtsAbancartDefines'),
                    'validate' => 'isCleanHtml',
                    'selector_change' => '.ets_abancart_preview',
                    'attr_change' => 'width',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_POPUP_HEIGHT' => array(
                    'name' => 'ETS_ABANCART_POPUP_HEIGHT',
                    'label' => $this->l('Height', 'EtsAbancartDefines'),
                    'type' => 'range',
                    'min' => 200,
                    'max' => 1200,
                    'default' => '500',
                    'unit' => $this->l('px', 'EtsAbancartDefines'),
                    'validate' => 'isCleanHtml',
                    'selector_change' => '.ets_abancart_preview',
                    'attr_change' => 'height',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_BORDER_RADIUS' => array(
                    'name' => 'ETS_ABANCART_BORDER_RADIUS',
                    'label' => $this->l('Border radius', 'EtsAbancartDefines'),
                    'type' => 'range',
                    'min' => 0,
                    'max' => 50,
                    'default' => '10',
                    'unit' => $this->l('px', 'EtsAbancartDefines'),
                    'validate' => 'isCleanHtml',
                    'selector_change' => '.ets_abancart_preview',
                    'attr_change' => 'border-radius',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),

                'ETS_ABANCART_BORDER_WIDTH' => array(
                    'name' => 'ETS_ABANCART_BORDER_WIDTH',
                    'label' => $this->l('Border width', 'EtsAbancartDefines'),
                    'type' => 'range',
                    'min' => 0,
                    'max' => 50,
                    'default' => '0',
                    'unit' => $this->l('px', 'EtsAbancartDefines'),
                    'validate' => 'isCleanHtml',
                    'selector_change' => '.ets_abancart_preview',
                    'attr_change' => 'border-width',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_BORDER_COLOR' => array(
                    'name' => 'ETS_ABANCART_BORDER_COLOR',
                    'label' => $this->l('Border color', 'EtsAbancartDefines'),
                    'type' => 'color',
                    'validate' => 'isCleanHtml',
                    'default' => '#ffffff',
                    'selector_change' => '.ets_abancart_preview',
                    'attr_change' => 'border-color',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_FONT_SIZE' => array(
                    'name' => 'ETS_ABANCART_FONT_SIZE',
                    'label' => $this->l('Text font size', 'EtsAbancartDefines'),
                    'type' => 'range',
                    'min' => 10,
                    'max' => 50,
                    'validate' => 'isCleanHtml',
                    'default' => '13',
                    'unit' => $this->l('px', 'EtsAbancartDefines'),
                    'selector_change' => 'p,a,div',
                    'attr_change' => 'font-size',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_CLOSE_BTN_COLOR' => array(
                    'name' => 'ETS_ABANCART_CLOSE_BTN_COLOR',
                    'label' => $this->l('Close button color', 'EtsAbancartDefines'),
                    'type' => 'color',
                    'validate' => 'isCleanHtml',
                    'default' => '#dddddd',
                    'selector_change' => '.ets_abancart_preview',
                    'attr_change' => 'background-color',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_PADDING' => array(
                    'name' => 'ETS_ABANCART_PADDING',
                    'label' => $this->l('Padding', 'EtsAbancartDefines'),
                    'type' => 'range',
                    'min' => 0,
                    'max' => 150,
                    'unit' => $this->l('px', 'EtsAbancartDefines'),
                    'validate' => 'isCleanHtml',
                    'default' => '10',
                    'selector_change' => '.ets_abancart_preview',
                    'attr_change' => 'padding',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_VERTICLE_ALIGN' => array(
                    'name' => 'ETS_ABANCART_VERTICLE_ALIGN',
                    'label' => $this->l('Vertical alignment', 'EtsAbancartDefines'),
                    'type' => 'select',
                    'validate' => 'isCleanHtml',
                    'default' => 'center',
                    'options' => array(
                        'name' => 'name',
                        'id' => 'id',
                        'query' => array(
                            array(
                                'name' => $this->l('Left', 'EtsAbancartDefines'),
                                'id' => 'left'
                            ),
                            array(
                                'name' => $this->l('Center', 'EtsAbancartDefines'),
                                'id' => 'center'
                            ),
                            array(
                                'name' => $this->l('Right', 'EtsAbancartDefines'),
                                'id' => 'right'
                            ),
                        )
                    ),
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_OVERLAY_BG' => array(
                    'name' => 'ETS_ABANCART_OVERLAY_BG',
                    'label' => $this->l('Overlay background color', 'EtsAbancartDefines'),
                    'type' => 'color',
                    'validate' => 'isColor',
                    'default' => '#333333',
                    'selector_change' => '.ets_abancart_preview_info',
                    'attr_change' => 'background-color',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_OVERLAY_BG_OPACITY' => array(
                    'name' => 'ETS_ABANCART_OVERLAY_BG_OPACITY',
                    'label' => $this->l('Overlay background opacity', 'EtsAbancartDefines'),
                    'type' => 'range',
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.01,
                    'validate' => 'isFloat',
                    'default' => 0.8,
                    'selector_change' => '.ets_abancart_preview_info',
                    'attr_change' => 'background-color',
                    'form_group_class' => 'leave ets_ac_config_popup_item'
                ),
                'ETS_ABANCART_LEAVE_WEBSITE_ENABLED' => array(
                    'label' => $this->l('Enabled', 'EtsAbancartDefines'),
                    'type' => 'switch',
                    'default' => 0,
                    'form_group_class' => 'leave ets_ac_config_popup_content',
                ),
                'default_content' => array(
                    'name' => 'default_content',
                    'label' => '',
                    'type' => 'default_content',
                    'has_discount' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_leave_web_reminder_discount.tpl'),
                    'no_discount' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_leave_web_reminder_nodiscount.tpl'),
                    'no_product_in_cart' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_leave_web_no_product_in_cart.tpl')
                )
            );
        }
        return self::$leave_configs;
    }

    static $browser_tab_configs;

    public function getBrowserTabConfigs()
    {
        if (!self::$browser_tab_configs) {
            self::$browser_tab_configs = array(
                'ETS_ABANCART_BROWSER_TAB_ENABLED' => array(
                    'label' => $this->l('Enabled', 'EtsAbancartDefines'),
                    'type' => 'switch',
                    'default' => 0,
                ),
                'ETS_ABANCART_TEXT_COLOR' => array(
                    'label' => $this->l('Text color', 'EtsAbancartDefines'),
                    'type' => 'color',
                    'default' => '#ffffff',
                    'form_group_class' => 'browser_tab text_color',
                ),
                'ETS_ABANCART_BACKGROUND_COLOR' => array(
                    'label' => $this->l('Background color', 'EtsAbancartDefines'),
                    'type' => 'color',
                    'default' => '#ff0000',
                    'form_group_class' => 'browser_tab background_color',
                ),
            );
        }
        return self::$browser_tab_configs;
    }

    public function getMailOptions()
    {
        if (!self::$mail_options) {
            self::$mail_options = array(
                'default' => array(
                    'id_option' => 'default',
                    'name' => $this->l('PrestaShop\'s mail', 'EtsAbancartDefines'),
                    'prestashop_mail_link' => $this->context->link->getAdminLink('AdminEmails', true, ($this->module->is17 ? ['route' => 'admin_emails_index'] : []))
                ),
                'hotmail' => array(
                    'id_option' => 'hotmail',
                    'name' => $this->l('Hotmail', 'EtsAbancartDefines')
                ),
                'gmail' => array(
                    'id_option' => 'gmail',
                    'name' => $this->l('Gmail', 'EtsAbancartDefines')
                ),
                'yahoomail' => array(
                    'id_option' => 'yahoomail',
                    'name' => $this->l('Yahoo mail', 'EtsAbancartDefines')
                ),
                'sendgrid' => array(
                    'id_option' => 'sendgrid',
                    'name' => $this->l('SendGrid', 'EtsAbancartDefines'),
                    'api' => true,
                ),
                'sendinblue' => array(
                    'id_option' => 'sendinblue',
                    'name' => $this->l('Sendinblue', 'EtsAbancartDefines'),
                    'api' => true,
                ),
                'mailjet' => array(
                    'id_option' => 'mailjet',
                    'name' => $this->l('Mailjet', 'EtsAbancartDefines'),
                    'api' => true,
                ),
                'custom' => array(
                    'id_option' => 'custom',
                    'name' => $this->l('Custom SMTP', 'EtsAbancartDefines'),
                ),
            );
        }
        return self::$mail_options;
    }

    public function getMailConfigs()
    {
        if (!self::$mail_configs) {
            self::$mail_configs = array(
                'ETS_ABANCART_MAIL_SERVICE' => array(
                    'label' => $this->l('Mail service', 'EtsAbancartDefines'),
                    'type' => 'radios',
                    'options' => array(
                        'query' => $this->getMailOptions(),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'default' => 'default',
                    'form_group_class' => '',
                ),
                'ETS_ABANCART_MAIL_DOMAIN' => array(
                    'label' => $this->l('Mail domain name', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'multi' => true,
                    'col' => '4',
                    'hint' => $this->l('Fully qualified domain name (keep this field empty if you don\'t know).', 'EtsAbancartDefines'),
                    'placeholders' => [
                        'hotmail' => 'office365.com',
                        'gmail' => 'gmail.com',
                        'yahoomail' => 'mail.yahoo.com',
                        'custom' => '',
                    ],
                    'form_group_class' => 'mail_service hotmail gmail yahoomail custom',
                ),
                'ETS_ABANCART_MAIL_SERVER' => array(
                    'label' => $this->l('SMTP server', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'multi' => true,
                    'col' => '4',
                    'required' => true,
                    'hint' => $this->l('IP address or server name (e.g. smtp.mydomain.com).', 'EtsAbancartDefines'),
                    'placeholders' => [
                        'hotmail' => 'outlook.office365.com',
                        'gmail' => 'smtp.gmail.com',
                        'yahoomail' => 'smtp.mail.yahoo.com',
                        'custom' => '',
                    ],
                    'form_group_class' => 'mail_service hotmail gmail yahoomail custom',
                ),
                'ETS_ABANCART_MAIL_USER' => array(
                    'label' => $this->l('SMTP username', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'multi' => true,
                    'col' => '4',
                    'required' => true,
                    'hint' => $this->l('Leave blank if not applicable.', 'EtsAbancartDefines'),
                    'placeholders' => [
                        'hotmail' => $this->l('Your hotmail address', 'EtsAbancartDefines'),
                        'gmail' => $this->l('Your gmail address', 'EtsAbancartDefines'),
                        'yahoomail' => $this->l('Your yahoo mail address', 'EtsAbancartDefines'),
                        'custom' => $this->l('Your email address', 'EtsAbancartDefines'),
                    ],
                    'form_group_class' => 'mail_service hotmail gmail yahoomail custom',
                ),
                'ETS_ABANCART_MAIL_PASSWD' => array(
                    'label' => $this->l('Your password', 'EtsAbancartDefines'),
                    'type' => 'password',
                    'multi' => true,
                    'required' => true,
                    'hint' => $this->l('Leave blank if not applicable.', 'EtsAbancartDefines'),
                    'placeholders' => [
                        'hotmail' => $this->l('Your hotmail password', 'EtsAbancartDefines'),
                        'gmail' => $this->l('Your gmail password', 'EtsAbancartDefines'),
                        'yahoomail' => $this->l('Your yahoo mail password', 'EtsAbancartDefines'),
                        'custom' => $this->l('Your custom mail password', 'EtsAbancartDefines'),
                    ],
                    'form_group_class' => 'mail_service hotmail gmail yahoomail custom',
                ),
                'ETS_ABANCART_MAIL_SMTP_ENCRYPTION' => array(
                    'label' => $this->l('Encryption', 'EtsAbancartDefines'),
                    'type' => 'select',
                    'multi' => true,
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'off',
                                'label' => $this->l('None', 'EtsAbancartDefines'),
                            ),
                            array(
                                'id_option' => 'tls',
                                'label' => $this->l('TLS', 'EtsAbancartDefines'),
                            ),
                            array(
                                'id_option' => 'ssl',
                                'label' => $this->l('SSL', 'EtsAbancartDefines'),
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'label',
                    ),
                    'col' => '4',
                    'defaults' => [
                        'hotmail' => 'tls',
                        'gmail' => 'tls',
                        'yahoomail' => 'ssl',
                        'custom' => 'ssl',
                    ],
                    'form_group_class' => 'mail_service hotmail gmail yahoomail custom',
                ),
                'ETS_ABANCART_MAIL_SMTP_PORT' => array(
                    'label' => $this->l('Port', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'multi' => true,
                    'validate' => 'isUnsignedInt',
                    'col' => '4',
                    'required' => true,
                    'hint' => $this->l('Port number to use.', 'EtsAbancartDefines'),
                    'placeholder' => '587',
                    'default' => '587',
                    'form_group_class' => 'mail_service hotmail gmail yahoomail custom',
                ),
                'ETS_ABANCART_MAIL_API_KEY' => array(
                    'label' => $this->l('API Key ID', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'api' => true,
                    'object' => 'sendgrid,sendinblue,mailjet',
                    'multi' => true,
                    'col' => '6',
                    'required' => true,
                    'form_group_class' => 'mail_service sendgrid sendinblue mailjet',
                ),
                'ETS_ABANCART_MAIL_SECRET_KEY' => array(
                    'label' => $this->l('API Secret Key', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'api' => true,
                    'multi' => true,
                    'object' => 'mailjet',
                    'col' => '6',
                    'required' => true,
                    'form_group_class' => 'mail_service mailjet',
                ),
            );
        }
        return self::$mail_configs;
    }

    public function getMailTrans()
    {
        if (!self::$mail_trans) {
            self::$mail_trans = array(
                'ETS_ABANCART_MAIL_UNSUBSCRIBE' => array(
                    'label' => $this->l('Unsubscribe text', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'lang' => true,
                    'required' => true,
                    'trans' => 'Unsubscribe',
                    'default' => $this->l('Unsubscribe', 'EtsAbancartDefines'),
                    'form_group_class' => 'trans',
                ),
                'ETS_ABANCART_MAIL_BUTTON_ADD_DISCOUNT' => array(
                    'label' => $this->l('Button add discount text', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'lang' => true,
                    'required' => true,
                    'default' => $this->l('Apply code and checkout', 'EtsAbancartDefines'),
                    'trans' => 'Apply code and checkout',
                    'form_group_class' => 'trans',
                ),
                'ETS_ABANCART_MAIL_HIGHLIGHT_BAR_BUTTON_ADD_DISCOUNT' => array(
                    'label' => $this->l('Highlight bar button add discount text', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'lang' => true,
                    'required' => true,
                    'default' => $this->l('Click here to checkout and get %s off', 'EtsAbancartDefines'),
                    'trans' => 'Click here to checkout and get %s off',
                    'form_group_class' => 'trans',
                ),
                'ETS_ABANCART_MAIL_SHOW_DISCOUNT_BOX' => array(
                    'label' => $this->l('Show discount box text', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'lang' => true,
                    'required' => true,
                    'default' => $this->l('Click to copy', 'EtsAbancartDefines'),
                    'trans' => 'Click to copy',
                    'form_group_class' => 'trans',
                ),
                'ETS_ABANCART_MAIL_CHECKOUT_BUTTON' => array(
                    'label' => $this->l('Checkout button text', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'lang' => true,
                    'required' => true,
                    'default' => $this->l('Go to checkout', 'EtsAbancartDefines'),
                    'trans' => 'Go to checkout',
                    'form_group_class' => 'trans',
                ),
                'ETS_ABANCART_MAIL_BUTTON_NO_THANKS' => array(
                    'label' => $this->l('"No, thanks" button text', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'lang' => true,
                    'required' => true,
                    'default' => $this->l('No, I don\'t like it . Thanks', 'EtsAbancartDefines'),
                    'trans' => 'No, I don\\\'t like it . Thanks',
                    'form_group_class' => 'trans',
                ),
                'ETS_ABANCART_MAIL_SHOP_BUTTON' => array(
                    'label' => $this->l('Shop button text', 'EtsAbancartDefines'),
                    'type' => 'text',
                    'lang' => true,
                    'required' => true,
                    'default' => $this->l('Go to shop', 'EtsAbancartDefines'),
                    'trans' => 'Go to shop',
                    'form_group_class' => 'trans',
                ),
            );
        }
        return self::$mail_trans;
    }

    public function getOtherConfigs()
    {
        if (!self::$other_configs) {
            self::$other_configs = array_merge([
                'ETS_ABANCART_AUTO_CLEAR_DISCOUNT' => array(
                    'type' => 'switch',
                    'label' => $this->l('Auto clear expired discount codes', 'EtsAbancartDefines'),
                    'global' => 1,
                    'default' => 1,
                ),
                'ETS_ABANCART_SAVE_SHOPPING_CART' => array(
                    'type' => 'switch',
                    'label' => $this->l('Allow customers to save their shopping cart?', 'EtsAbancartDefines'),
                    'default' => 0,
                ),
                'ETS_ABANCART_HOURS' => array(
                    'type' => 'text',
                    'label' => $this->l('Hour(s)', 'EtsAbancartDefines'),
                    'validate' => 'isUnsignedFloat',
                    'placeholder' => $this->l('Hour(s)', 'EtsAbancartDefines'),
                    'desc' => $this->l('Minutes and seconds are between 0 and 60 or empty.', 'EtsAbancartDefines'),
                    'form_group_class' => 'shopping_cart times'
                ),
                'ETS_ABANCART_MINUTES' => array(
                    'type' => 'text',
                    'label' => $this->l('Minute(s)', 'EtsAbancartDefines'),
                    'validate' => 'isUnsignedInt',
                    'placeholder' => $this->l('Minute(s)', 'EtsAbancartDefines'),
                    'form_group_class' => 'shopping_cart times'
                ),
                'ETS_ABANCART_SECONDS' => array(
                    'type' => 'text',
                    'label' => $this->l('Second(s)', 'EtsAbancartDefines'),
                    'validate' => 'isUnsignedInt',
                    'placeholder' => $this->l('Second(s)', 'EtsAbancartDefines'),
                    'form_group_class' => 'shopping_cart times'
                ),
                'ETS_ABANCART_ALLOW_NOTIFICATION' => array(
                    'type' => 'switch',
                    'label' => $this->l('Ask customers if they allow to display web push notification', 'EtsAbancartDefines'),
                    'default' => 0,
                ),
            ], $this->getMailTrans());
        }
        return self::$other_configs;
    }

    public function getMenus()
    {
        if (!self::$menus) {
            self::$menus = array(
                'dashboard' => array(
                    'label' => $this->l('Dashboard', 'EtsAbancartDefines'),
                    'origin' => 'Dashboard',
                    'icon' => 'dashboard',
                    'class' => 'Dashboard',
                ),
                'all' => array(
                    'label' => $this->l('Reminder campaigns', 'EtsAbancartDefines'),
                    'origin' => 'Reminder campaigns',
                    'entity' => 'campaign',
                    'icon' => 'campaign',
                    'sub_menus' => $this->getSubMenus(),
                    'class' => 'Campaign',
                ),
                'cart' => array(
                    'label' => $this->l('Abandoned carts', 'EtsAbancartDefines'),
                    'origin' => 'Abandoned carts',
                    'entity' => 'cart',
                    'icon' => 'cart',
                    'class' => 'Cart',
                ),
                'converted_carts' => array(
                    'label' => $this->l('Recovered carts', 'EtsAbancartDefines'),
                    'origin' => 'Recovered carts',
                    'icon' => 'converted_carts',
                    'class' => 'ConvertedCarts',
                ),
                'email_template' => array(
                    'label' => $this->l('Email templates', 'EtsAbancartDefines'),
                    'origin' => 'Email templates',
                    'entity' => 'email_template',
                    'icon' => 'template',
                    'class' => 'EmailTemplate',
                ),
                'tracking' => array(
                    'label' => $this->l('Campaign tracking', 'EtsAbancartDefines'),
                    'origin' => 'Campaign tracking',
                    'entity' => 'tracking',
                    'icon' => 'tracking',
                    'class' => 'Tracking',
                    'sub_menus' => [
                        'email_tracking' => [
                            'entity' => 'tracking',
                            'label' => $this->l('Email tracking', 'EtsAbancartDefines'),
                            'origin' => 'Email tracking',
                            'icon' => 'email_tracking',
                            'class' => 'EmailTracking',
                        ],
                        'display_tracking' => [
                            'entity' => 'display_tracking',
                            'label' => $this->l('Display tracking', 'EtsAbancartDefines'),
                            'origin' => 'Display tracking',
                            'icon' => 'display_tracking',
                            'class' => 'DisplayTracking'
                        ],
                        'discounts' => [
                            'entity' => 'discounts',
                            'label' => $this->l('Discounts', 'EtsAbancartDefines'),
                            'origin' => 'Discounts',
                            'icon' => 'discounts',
                            'class' => 'Discounts'
                        ],
                        'display_log' => [
                            'entity' => 'display_log',
                            'label' => $this->l('Display log', 'EtsAbancartDefines'),
                            'origin' => 'Display log',
                            'icon' => 'display_log',
                            'class' => 'DisplayLog'
                        ],
                    ]
                ),
                'mail_configs' => array(
                    'label' => $this->l('Mail configuration', 'EtsAbancartDefines'),
                    'origin' => 'Mail configuration',
                    'entity' => 'mail_configs',
                    'icon' => 'mail_configs',
                    'object' => 0,
                    'class' => 'MailConfigs',
                    'sub_menus' => [
                        'service' => [
                            'entity' => 'service',
                            'label' => $this->l('Mail service', 'EtsAbancartDefines'),
                            'origin' => 'Mail service',
                            'icon' => 'service',
                            'class' => 'MailServices',
                            'desc' => $this->l('SMTP services to send reminder emails', 'EtsAbancartDefines'),
                        ],
                        'queue' => [
                            'entity' => 'queue',
                            'label' => $this->l('Mail queue', 'EtsAbancartDefines'),
                            'origin' => 'Mail queue',
                            'icon' => 'queue',
                            'class' => 'MailQueue',
                            'desc' => $this->l('Emails that are going to be sent in next queue checks (via cronjob)', 'EtsAbancartDefines'),
                        ],
                        'indexed' => [
                            'entity' => 'indexed',
                            'label' => $this->l('Indexed carts', 'EtsAbancartDefines'),
                            'origin' => 'Indexed carts',
                            'icon' => 'index-queue',
                            'class' => 'IndexedCarts',
                            'desc' => $this->l('Shopping carts have been added to cart index table', 'EtsAbancartDefines'),
                        ],
                        'customer_indexed' => [
                            'entity' => 'customer_indexed',
                            'label' => $this->l('Indexed customers', 'EtsAbancartDefines'),
                            'origin' => 'Indexed customers',
                            'icon' => 'index-customer',
                            'class' => 'IndexedCustomers',
                            'desc' => $this->l('Customers have been added to customer index table', 'EtsAbancartDefines'),
                        ],
                        'unsubscribed' => [
                            'entity' => 'unsubscribed',
                            'label' => $this->l('Unsubscribed list', 'EtsAbancartDefines'),
                            'origin' => 'Unsubscribed list',
                            'icon' => 'unsubscribed',
                            'class' => 'Unsubscribed',
                            'desc' => $this->l('Customers unsubscribed from your mailing list', 'EtsAbancartDefines'),
                        ],
                        'mail_log' => [
                            'entity' => 'mail_log',
                            'label' => $this->l('Mail log', 'EtsAbancartDefines'),
                            'origin' => 'Mail log',
                            'icon' => 'mail-log',
                            'class' => 'MailLog',
                            'desc' => $this->l('Detailed log of mail sending process', 'EtsAbancartDefines'),
                        ]
                    ],
                ),
                'leads' => array(
                    'label' => $this->l('Leads', 'EtsAbancartDefines'),
                    'origin' => 'Leads',
                    'entity' => 'leads',
                    'icon' => 'leads',
                    'object' => 0,
                    'class' => 'Leads',
                ),
                'configs' => array(
                    'label' => $this->l('Automation', 'EtsAbancartDefines'),
                    'origin' => 'Automation',
                    'entity' => 'configs',
                    'icon' => 'configs',
                    'object' => 0,
                    'class' => 'Configs',
                ),
                'other_configs' => array(
                    'label' => $this->l('Other settings', 'EtsAbancartDefines'),
                    'origin' => 'Other settings',
                    'entity' => 'other_configs',
                    'icon' => 'other_configs',
                    'object' => 0,
                    'class' => 'OtherConfigs',
                ),

            );
        }
        return self::$menus;
    }

    public function getConfigs()
    {
        if (!self::$configs) {
            self::$configs = array(
                'ETS_ABANCART_CRONJOB_MAIL_LOG' => array(
                    'type' => 'switch',
                    'label' => $this->l('Enable mail log', 'EtsAbancartDefines'),
                    'global' => 1,
                    'tab' => 'config',
                    'default' => 1,
                    'form_group_class' => 'ets_abancart_cronjob',
                    'desc' => $this->l('Enable this option for testing purposes only', 'EtsAbancartDefines'),
                ),
                'ETS_ABANCART_CRONJOB_EMAILS' => array(
                    'type' => 'text',
                    'label' => $this->l('Mail queue step', 'EtsAbancartDefines') . ' (' . $this->l('Maximum number of email sent every time cronjob file run', 'EtsAbancartDefines') . ')',
                    'default' => 5,
                    'col' => 3,
                    'suffix' => $this->l('email(s)', 'EtsAbancartDefines'),
                    'required' => true,
                    'global' => 1,
                    'validate' => 'isUnsignedInt',
                    'tab' => 'config',
                    'form_group_class' => 'ets_abancart_cronjob',
                    'desc' => $this->l('Every time cronjob is run, it will check mail queue for the emails to be sent. Reduce this value if your server has limited timeout.', 'EtsAbancartDefines')
                ),
                'ETS_ABANCART_CRONJOB_MAX_TRY' => array(
                    'type' => 'text',
                    'label' => $this->l('Mail queue max-trying times', 'EtsAbancartDefines'),
                    'default' => 5,
                    'col' => 3,
                    'suffix' => $this->l('time(s)', 'EtsAbancartDefines'),
                    'required' => true,
                    'global' => 1,
                    'validate' => 'isUnsignedInt',
                    'tab' => 'config',
                    'form_group_class' => 'ets_abancart_cronjob',
                    'desc' => $this->l('The times to try to send an email again if it was failed! After that, the email will be deleted from queue.', 'EtsAbancartDefines'),
                ),
                'ETS_ABANCART_SECURE_TOKEN' => array(
                    'type' => 'text',
                    'label' => $this->l('Cronjob secure token', 'EtsAbancartDefines'),
                    'default' => Tools::passwdGen(10),
                    'col' => 3,
                    'global' => 1,
                    'required' => true,
                    'suffix' => $this->l('Generate', 'EtsAbancartDefines'),
                    'tab' => 'config',
                    'form_group_class' => 'ets_abancart_cronjob ets_abancart_secure_token',
                ),
                'ETS_ABANCART_SAVE_CRONJOB_LOG' => array(
                    'type' => 'switch',
                    'label' => $this->l('Save cronjob log', 'EtsAbancartDefines'),
                    'default' => 0,
                    'desc' => $this->l('Only recommended for debug purpose', 'EtsAbancartDefines'),
                    'tab' => 'log',
                    'global' => 1,
                    'form_group_class' => 'ets_abancart_cronjob',
                ),
                'ETS_ABANCART_CRONJOB_LOG' => array(
                    'type' => 'html',
                    'label' => $this->l('Cronjob log', 'EtsAbancartDefines'),
                    'default' => '',
                    'tab' => 'log',
                    'global' => 1,
                    'form_group_class' => 'ets_abancart_cronjob',
                ),
            );
        }
        return self::$configs;
    }

    public function getSubMenus()
    {
        if (!self::$sub_menus) {
            self::$sub_menus = array(
                'email' => array(
                    'entity' => 'campaign',
                    'label' => $this->l('Automated abandoned cart emails', 'EtsAbancartDefines'),
                    'origin' => 'Automated abandoned cart emails',
                    'icon' => 'email',
                    'link' => '&type=all',
                    'class' => 'ReminderEmail',
                    'desc' => $this->l('Send abandoned cart reminders via email', 'EtsAbancartDefines'),
                ),
                'customer' => array(
                    'entity' => 'campaign',
                    'label' => $this->l('Custom emails and newsletter', 'EtsAbancartDefines'),
                    'origin' => 'Custom emails and newsletter',
                    'icon' => 'customer',
                    'class' => 'ReminderCustomer',
                    'desc' => $this->l('Auto email marketing tool with custom reminder emails or newsletter', 'EtsAbancartDefines'),
                ),
                'popup' => array(
                    'entity' => 'campaign',
                    'label' => $this->l('Popup reminder', 'EtsAbancartDefines'),
                    'origin' => 'Popup reminder',
                    'icon' => 'popup',
                    'class' => 'ReminderPopup',
                    'desc' => $this->l('Display abandoned cart reminders via popup', 'EtsAbancartDefines'),
                ),
                'bar' => array(
                    'entity' => 'campaign',
                    'label' => $this->l('Highlight bar reminder', 'EtsAbancartDefines'),
                    'origin' => 'Highlight bar reminder',
                    'icon' => 'bar',
                    'class' => 'ReminderBar',
                    'desc' => $this->l('Display abandoned cart reminder message on top of web page', 'EtsAbancartDefines'),
                ),
                'browser' => array(
                    'entity' => 'campaign',
                    'label' => $this->l('Web push notification', 'EtsAbancartDefines'),
                    'origin' => 'Web push notification',
                    'icon' => 'browser',
                    'class' => 'ReminderBrowser',
                    'desc' => $this->l('Display abandoned cart reminder message via web push notification', 'EtsAbancartDefines'),
                ),
                'leave' => array(
                    'entity' => 'leave_configs',
                    'label' => $this->l('Leaving website reminder', 'EtsAbancartDefines'),
                    'origin' => 'Leaving website reminder',
                    'icon' => 'leave',
                    'object' => 0,
                    'class' => 'ReminderLeave',
                    'desc' => $this->l('Display abandoned cart reminder message when customer leaves website', 'EtsAbancartDefines'),
                ),
                'browser_tab' => array(
                    'entity' => 'browser_tab_configs',
                    'label' => $this->l('Browser tab notification', 'EtsAbancartDefines'),
                    'origin' => 'Browser tab notification',
                    'icon' => 'browser_tab',
                    'object' => 0,
                    'class' => 'ReminderBrowserTab',
                    'desc' => $this->l('Highlight the number of products in shopping cart', 'EtsAbancartDefines'),
                ),
            );
        }
        return self::$sub_menus;
    }

    public function getShortCodes()
    {
        if (!self::$short_codes) {
            self::$short_codes = array(
                'shop_name' => array(
                    'name' => $this->l('Shop name', 'EtsAbancartDefines'),
                    'group' => 'shop',
                ),
                'logo' => array(
                    'name' => $this->l('Logo', 'EtsAbancartDefines'),
                    'group' => 'shop',
                    'object' => 'email,cart,customer,popup,bar,leave,browser',
                ),
                'firstname' => array(
                    'name' => $this->l('First name', 'EtsAbancartDefines'),
                    'group' => 'customer',
                    'object' => 'email,cart,customer',
                ),
                'lastname' => array(
                    'name' => $this->l('Last name', 'EtsAbancartDefines'),
                    'object' => 'email,cart,customer',
                    'group' => 'customer',
                ),
                'discount_code' => array(
                    'name' => $this->l('Discount code', 'EtsAbancartDefines'),
                    'group' => 'discount',
                ),
                'discount_from' => array(
                    'name' => $this->l('Discount from', 'EtsAbancartDefines'),
                    'group' => 'discount',
                ),
                'discount_to' => array(
                    'name' => $this->l('Discount to', 'EtsAbancartDefines'),
                    'group' => 'discount',
                ),
                'reduction' => array(
                    'name' => $this->l('Reduction', 'EtsAbancartDefines'),
                    'group' => 'discount',
                ),
                'money_saved' => array(
                    'name' => $this->l('Money saved', 'EtsAbancartDefines'),
                    'group' => 'discount',
                    'object' => 'email,cart,popup,bar,leave',
                ),
                'product_list' => array(
                    'name' => $this->l('Product list', 'EtsAbancartDefines'),
                    'object' => 'email,cart,popup,leave',
                    'group' => 'product',
                ),
                'product_grid' => array(
                    'name' => $this->l('Product grid', 'EtsAbancartDefines'),
                    'object' => 'customer',
                    'group' => 'product',
                ),
                'total_cart' => array(
                    'name' => $this->l('Total cart', 'EtsAbancartDefines'),
                    'group' => 'product',
                    'object' => 'email,cart,popup,bar,leave',
                ),
                'total_products_cost' => array(
                    'name' => $this->l('Total product cost', 'EtsAbancartDefines'),
                    'group' => 'payment',
                    'object' => 'email,cart,popup,bar,leave',
                ),
                'total_shipping_cost' => array(
                    'name' => $this->l('Total shipping cost', 'EtsAbancartDefines'),
                    'group' => 'payment',
                    'object' => 'email,cart,popup,bar,leave',
                ),
                'total_tax' => array(
                    'name' => $this->l('Total tax', 'EtsAbancartDefines'),
                    'group' => 'payment',
                    'object' => 'email,cart,popup,bar,leave',
                ),
                'total_payment_after_discount' => array(
                    'name' => $this->l('Total payment after discount', 'EtsAbancartDefines'),
                    'group' => 'discount',
                    'object' => 'email,cart,popup,bar,leave',
                ),
                'checkout_button' => array(
                    'name' => $this->l('Checkout button', 'EtsAbancartDefines'),
                    'object' => 'email,cart,popup,leave',
                    'group' => 'button',
                ),
                'button_add_discount' => array(
                    'name' => $this->l('Add discount button', 'EtsAbancartDefines'),
                    'object' => 'popup,bar,leave',
                    'group' => 'discount',
                ),
                'show_discount_box' => array(
                    'name' => $this->l('Discount box', 'EtsAbancartDefines'),
                    'object' => 'popup,leave',
                    'group' => 'discount',
                ),
                'discount_count_down_clock' => array(
                    'name' => $this->l('Discount count down clock', 'EtsAbancartDefines'),
                    'object' => 'popup,bar,leave',
                    'group' => 'discount',
                ),
                'button_no_thanks' => array(
                    'name' => $this->l('"No, thanks" button', 'EtsAbancartDefines'),
                    'object' => 'popup,bar,leave',
                    'group' => 'button',
                ),
                'unsubscribe' => array(
                    'name' => $this->l('Unsubscribe link', 'EtsAbancartDefines'),
                    'object' => 'email,cart,customer',
                    'group' => 'button',
                ),
                'registration_date' => array(
                    'name' => $this->l('Registration date', 'EtsAbancartDefines'),
                    'object' => 'customer',
                    'group' => 'button',
                ),
                'last_order_id' => array(
                    'name' => $this->l('Last order ID', 'EtsAbancartDefines'),
                    'object' => 'customer',
                    'group' => 'button',
                ),
                'last_order_reference' => array(
                    'name' => $this->l('Last order reference', 'EtsAbancartDefines'),
                    'object' => 'customer',
                    'group' => 'button',
                ),
                'last_order_total' => array(
                    'name' => $this->l('Last order total', 'EtsAbancartDefines'),
                    'object' => 'customer',
                    'group' => 'button',
                ),
                'order_total' => array(
                    'name' => $this->l('Order total', 'EtsAbancartDefines'),
                    'object' => 'customer',
                    'group' => 'button',
                ),
                'last_time_login_date' => array(
                    'name' => $this->l('Last visit time', 'EtsAbancartDefines'),
                    'object' => 'customer',
                    'group' => 'button',
                ),
                'shop_button' => array(
                    'name' => $this->l('Shop button', 'EtsAbancartDefines'),
                    'object' => 'email,cart,popup,leave,bar,customer',
                    'group' => 'button',
                ),
                'lead_form' => array(
                    'name' => $this->l('Lead form', 'EtsAbancartDefines'),
                    'object' => 'email,cart,popup,leave,bar,customer',
                    'group' => 'button',
                ),
                'countdown_clock' => array(
                    'name' => $this->l('Count down clock', 'EtsAbancartDefines'),
                    'object' => 'cart,popup,bar,leave',
                    'group' => 'button',
                ),
                'custom_button' => array(
                    'name' => $this->l('Custom button', 'EtsAbancartDefines'),
                    'object' => 'email,cart,popup,leave,bar,customer',
                    'group' => 'button',
                ),
            );
        }
        return self::$short_codes;
    }

    public function getFields($fields)
    {
        switch (trim($fields)) {
            case 'short_codes':
                return $this->getShortCodes();
            case 'sub_menus':
                return $this->getSubMenus();
            case 'menus':
                return $this->getMenus();
            case 'configs':
                return $this->getConfigs();
            case 'other_configs':
                return $this->getOtherConfigs();
            case 'mail_configs':
                return $this->getMailConfigs();
            case 'leave_configs':
                return $this->getLeaveConfigs();
            case 'browser_tab_configs':
                return $this->getBrowserTabConfigs();
            case 'mail_trans':
                return $this->getMailTrans();
        }
        return array();
    }

    public function installDefaultConfig()
    {
        $configs = $this->getLeaveConfigs(false);
        $languages = Language::getLanguages(false);
        $shops = Shop::getShops(false);
        foreach ($configs as $key => $config) {
            if (isset($config['default_value']) && $config['default_value']) {
                if (isset($config['lang']) && $config['lang']) {
                    $value = array();
                    foreach ($languages as $lang) {
                        $value[$lang['id_lang']] = $config['default_value'];
                    }
                    Configuration::updateGlobalValue($key, $value);
                    foreach ($shops as $shop) {
                        Configuration::updateValue($key, $value, true, null, $shop['id_shop']);
                    }
                } else {
                    Configuration::updateGlobalValue($key, $config['default_value']);
                    foreach ($shops as $shop) {
                        Configuration::updateValue($key, $config['default_value'], true, null, $shop['id_shop']);
                    }
                }
            }
        }
        return true;
    }

    public function installDefaultLeadConfigs()
    {
        $languages = Language::getLanguages(false);
        $shops = Shop::getShops(true);
        foreach ($shops as $shop) {
            $form = new EtsAbancartForm();
            $form->id_shop = (int)$shop['id_shop'];
            $form->enable = 1;
            $form->btn_bg_color = '#0099ff';
            $form->btn_bg_hover_color = '#0066cc';
            $form->btn_text_color = '#ffffff';
            $form->btn_text_hover_color = '#ffffff';
            $form->enable = 1;
            $form->enable_captcha = 0;
            $form->captcha_type = 'v2';
            $form->display_thankyou_page = 1;
            $form->is_init = 1;
            $form->name = array();
            $form->description = array();
            $form->alias = array();
            $form->thankyou_page_title = array();
            $form->thankyou_page_alias = array();
            $form->thankyou_page_content = array();
            foreach ($languages as $lang) {
                $form->name[$lang['id_lang']] = $this->l('Default', 'EtsAbancartDefines');
                $form->description[$lang['id_lang']] = '';
                $form->alias[$lang['id_lang']] = 'default';
                $form->btn_title[$lang['id_lang']] = $this->l('Submit', 'EtsAbancartDefines');
                $form->thankyou_page_title[$lang['id_lang']] = $this->l('Thank you', 'EtsAbancartDefines');
                $form->thankyou_page_alias[$lang['id_lang']] = 'thanks';
                $form->thankyou_page_content[$lang['id_lang']] = $this->l('Many thanks to you', 'EtsAbancartDefines');
            }
            if ($form->add()) {
                $fields = array(
                    'name' => array(
                        'type' => EtsAbancartField::FIELD_TYPE_TEXT,
                        'title' => $this->l('Name', 'EtsAbancartDefines'),
                        'position' => 1
                    ),
                    'email' => array(
                        'type' => EtsAbancartField::FIELD_TYPE_EMAIL,
                        'title' => $this->l('Email', 'EtsAbancartDefines'),
                        'position' => 2
                    ),
                    'phone' => array(
                        'type' => EtsAbancartField::FIELD_TYPE_PHONE,
                        'title' => $this->l('Phone number', 'EtsAbancartDefines'),
                        'position' => 3
                    ),
                );
                foreach ($fields as $item) {
                    $field = new EtsAbancartField();
                    $field->id_ets_abancart_form = $form->id;
                    $field->enable = 1;
                    $field->type = $item['type'];
                    $field->position = $item['position'];
                    $field->required = 1;
                    $field->display_column = 1;
                    $field->is_contact_email = 1;
                    $field->is_contact_name = 1;
                    $field->name = array();
                    $field->description = array();
                    $field->content = array();
                    foreach ($languages as $lang) {
                        $field->name[$lang['id_lang']] = $item['title'];
                        $field->description[$lang['id_lang']] = '';
                        $field->content[$lang['id_lang']] = '';
                    }
                    $field->add();
                }
            }
        }

        return true;
    }

    public static function uninstallAllConfigs()
    {
        $configs = Db::getInstance()->executeS("SELECT `name` FROM `" . _DB_PREFIX_ . "configuration` WHERE `name` LIKE 'ETS_ABANCART_%'");
        if ($configs) {
            foreach ($configs as $config) {
                Configuration::deleteByName($config['name']);
            }
        }
        return true;
    }

    public function getTrans($idLang = 0)
    {
        if (!$idLang) {
            $idLang = (int)$this->context->language->id;
        }
        $configs = $this->getMailTrans();
        $assigns = [];
        if ($configs) {
            foreach ($configs as $key => $config) {
                $assigns[$key] = Configuration::get($key, $idLang);
            }
            unset($config);
        }
        return $assigns;
    }
}
