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

class AdminEtsACReminderPopupController extends AdminEtsACReminderEmailController
{
    public function __construct()
    {
        $this->type = 'popup';
        parent::__construct();
    }

    public function getFieldsForm()
    {
        $fields = [
            'hidden_reminder_id' => [
                'name' => 'hidden_reminder_id',
                'type' => 'hidden',
                'label' => $this->l('Reminder', 'AdminEtsACReminderPopupController'),
                'default_value' => (int)Tools::getValue($this->identifier),
            ],
            'title' => array(
                'name' => 'title',
                'label' => $this->l('Title', 'AdminEtsACReminderPopupController'),
                'type' => 'text',
                'lang' => true,
                'required' => true,
                'validate' => 'isCleanHtml',
                'form_group_class' => 'abancart form_message isCleanHtml required ets_ac_config_popup_content'
            ),
            'content' => array(
                'name' => 'content',
                'label' => $this->l('Content', 'AdminEtsACReminderPopupController'),
                'type' => 'textarea',
                'autoload_rte' => true,
                'lang' => true,
                'required' => true,
                'desc_type' => $this->type,
                'validate' => 'isCleanHtml',
                'form_group_class' => 'abancart content form_message isCleanHtml required ets_ac_config_popup_content'
            ),
            'default_content' => array(
                'name' => 'default_content',
                'label' => '',
                'type' => 'default_content',
                'has_discount' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_popup_reminder_discount.tpl'),
                'no_discount' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_popup_reminder_nodiscount.tpl'),
                'no_product_in_cart' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_popup_no_product_in_cart.tpl'),
                'title_no_product_in_cart' => 'Your popup title',
            ),
            'header_bg' => array(
                'name' => 'header_bg',
                'label' => $this->l('Header background', 'AdminEtsACReminderPopupController'),
                'type' => 'color',
                'validate' => 'isCleanHtml',
                'default' => '#03a9f3',
                'selector_change' => '.ets_abancart_preview_title',
                'attr_change' => 'background-color',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'header_text_color' => array(
                'name' => 'header_text_color',
                'label' => $this->l('Header text color', 'AdminEtsACReminderPopupController'),
                'type' => 'color',
                'validate' => 'isCleanHtml',
                'default' => '#ffffff',
                'selector_change' => '.ets_abancart_preview_title',
                'attr_change' => 'color',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'header_height' => array(
                'name' => 'header_height',
                'label' => $this->l('Header height', 'AdminEtsACReminderPopupController'),
                'type' => 'range',
                'min' => 50,
                'max' => 150,
                'default' => '60',
                'unit' => $this->l('px', 'AdminEtsACReminderPopupController'),
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview_title',
                'attr_change' => 'height',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'header_font_size' => array(
                'name' => 'header_font_size',
                'label' => $this->l('Header font size', 'AdminEtsACReminderPopupController'),
                'type' => 'range',
                'min' => 10,
                'max' => 50,
                'validate' => 'isCleanHtml',
                'default' => '24',
                'unit' => $this->l('px', 'AdminEtsACReminderPopupController'),
                'selector_change' => '.ets_abancart_preview_title',
                'attr_change' => 'font-size',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'popup_width' => array(
                'name' => 'popup_width',
                'label' => $this->l('Width', 'AdminEtsACReminderPopupController'),
                'type' => 'range',
                'min' => 200,
                'max' => 1200,
                'default' => '820',
                'unit' => $this->l('px', 'AdminEtsACReminderPopupController'),
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'width',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'popup_height' => array(
                'name' => 'popup_height',
                'label' => $this->l('Height', 'AdminEtsACReminderPopupController'),
                'type' => 'range',
                'min' => 200,
                'max' => 1200,
                'default' => '640',
                'unit' => $this->l('px', 'AdminEtsACReminderPopupController'),
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'height',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'border_radius' => array(
                'name' => 'border_radius',
                'label' => $this->l('Border radius', 'AdminEtsACReminderPopupController'),
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'default' => '10',
                'unit' => $this->l('px', 'AdminEtsACReminderPopupController'),
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'border-radius',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),

            'border_width' => array(
                'name' => 'border_width',
                'label' => $this->l('Border width', 'AdminEtsACReminderPopupController'),
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'default' => '0',
                'unit' => $this->l('px', 'AdminEtsACReminderPopupController'),
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'border-width',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'border_color' => array(
                'name' => 'border_color',
                'label' => $this->l('Border color', 'AdminEtsACReminderPopupController'),
                'type' => 'color',
                'validate' => 'isCleanHtml',
                'default' => '#ffffff',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'border-color',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'popup_body_bg' => array(
                'name' => 'popup_body_bg',
                'label' => $this->l('Body background', 'AdminEtsACReminderPopupController'),
                'type' => 'color',
                'default' => '#ffffff',
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview',
                'attr_change' => 'background-color',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'font_size' => array(
                'name' => 'font_size',
                'label' => $this->l('Text font size', 'AdminEtsACReminderPopupController'),
                'type' => 'range',
                'min' => 10,
                'max' => 50,
                'validate' => 'isCleanHtml',
                'default' => '13',
                'unit' => $this->l('px', 'AdminEtsACReminderPopupController'),
                'selector_change' => '.ets_abancart_preview_content_view .ets_abancart_preview p,.ets_abancart_preview_content_view .ets_abancart_preview a, .ets_abancart_preview_content_view .ets_abancart_preview p,.ets_abancart_preview_content_view .ets_abancart_preview div',
                'attr_change' => 'font-size',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'close_btn_color' => array(
                'name' => 'close_btn_color',
                'label' => $this->l('Close button color', 'AdminEtsACReminderPopupController'),
                'type' => 'color',
                'validate' => 'isCleanHtml',
                'default' => '#ffffff',
                'selector_change' => '.ets_abancart_preview_title',
                'attr_change' => 'background-color',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'overlay_bg' => array(
                'name' => 'overlay_bg',
                'label' => $this->l('Overlay background color', 'AdminEtsACReminderPopupController'),
                'type' => 'color',
                'validate' => 'isCleanHtml',
                'default' => '#333333',
                'selector_change' => '.ets_abancart_preview_info.popup',
                'attr_change' => 'background-color',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'overlay_bg_opacity' => array(
                'name' => 'overlay_bg_opacity',
                'label' => $this->l('Overlay background opacity', 'AdminEtsACReminderPopupController'),
                'type' => 'range',
                'min' => 0,
                'max' => 1,
                'step' => 0.01,
                'validate' => 'isFloat',
                'default' => 0.8,
                'selector_change' => '.ets_abancart_preview_info.popup',
                'attr_change' => 'background-color',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'padding' => array(
                'name' => 'padding',
                'label' => $this->l('Padding', 'AdminEtsACReminderPopupController'),
                'type' => 'range',
                'min' => 0,
                'max' => 150,
                'unit' => $this->l('px', 'AdminEtsACReminderPopupController'),
                'validate' => 'isCleanHtml',
                'default' => '30',
                'selector_change' => '.ets_abancart_preview',
                'attr_change' => 'padding',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'vertical_align' => array(
                'name' => 'vertical_align',
                'label' => $this->l('Vertical alignment', 'AdminEtsACReminderPopupController'),
                'type' => 'select',
                'validate' => 'isCleanHtml',
                'default' => 'center',
                'options' => array(
                    'name' => 'name',
                    'id' => 'id',
                    'query' => array(
                        array(
                            'name' => $this->l('Left', 'AdminEtsACReminderPopupController'),
                            'id' => 'left'
                        ),
                        array(
                            'name' => $this->l('Center', 'AdminEtsACReminderPopupController'),
                            'id' => 'center'
                        ),
                        array(
                            'name' => $this->l('Right', 'AdminEtsACReminderPopupController'),
                            'id' => 'right'
                        ),
                    )
                ),
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
        ];

        if (isset($this->campaign->object) && $this->campaign->object->id > 0) {
            $fields['has_shopping_cart'] = [
                'name' => 'has_shopping_cart',
                'type' => 'hidden',
                'label' => $this->l('Has shopping cart', 'AdminEtsACReminderPopupController'),
                'default_value' => $this->campaign->object->has_product_in_cart == EtsAbancartCampaign::HAS_SHOPPING_CART_YES ? 1 : 0,
            ];
        }

        return $fields;
    }

    public function ajaxProcessRenderForm()
    {
        if ($this->access('edit')) {
            $menus = EtsAbancartReminderForm::getInstance()->getReminderSteps();
            unset($menus['select_template']);
            $this->tpl_form_vars = array(
                'menus' => $menus,
                'lead_forms' => EtsAbancartForm::getAllForms(false, true),
                'maxSizeUpload' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'baseUri' => __PS_BASE_URI__,
                'field_types' => EtsAbancartField::getInstance()->getFieldType(),
                'module_dir' => _PS_MODULE_DIR_ . $this->module->name,
                'is17Ac' => $this->module->is17,
                'short_codes' => EtsAbancartDefines::getInstance()->getFields('short_codes'),
            );
            $this->toJson(array(
                'html' => $this->renderForm(),
            ));
        }
    }
}