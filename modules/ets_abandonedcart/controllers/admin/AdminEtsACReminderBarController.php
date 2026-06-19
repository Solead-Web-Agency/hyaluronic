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

class AdminEtsACReminderBarController extends AdminEtsACReminderEmailController
{
    public function __construct()
    {
        $this->type = 'bar';
        parent::__construct();
    }

    public function getFieldsForm()
    {
        $fields = [
            'hidden_reminder_id' => [
                'name' => 'hidden_reminder_id',
                'type' => 'hidden',
                'label' => $this->l('Reminder', 'AdminEtsACReminderBarController'),
                'default_value' => (int)Tools::getValue($this->identifier),
            ],
            'title' => array(
                'name' => 'title',
                'label' => $this->l('Title', 'AdminEtsACReminderBarController'),
                'type' => 'text',
                'lang' => true,
                'required' => true,
                'validate' => 'isCleanHtml',
                'form_group_class' => 'abancart form_message isCleanHtml required ets_ac_config_popup_content'
            ),
            'content' => array(
                'name' => 'content',
                'label' => $this->l('Content', 'AdminEtsACReminderBarController'),
                'type' => 'textarea',
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
                'has_discount' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_highlightbar_discount.tpl'),
                'no_discount' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_highlightbar_nodiscount.tpl'),
                'no_product_in_cart' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/default/default_highlightbar_no_product_in_cart.tpl'),
                'title_no_product_in_cart' => 'Your highlight bar title',
            ),
            'text_color' => array(
                'name' => 'text_color',
                'label' => $this->l('Text color', 'AdminEtsACReminderBarController'),
                'type' => 'color',
                'validate' => 'isColor',
                'default' => '#ffffff',
                'form_group_class' => 'abancart form_message isColor ets_ac_config_popup_item'
            ),
            'background_color' => array(
                'name' => 'background_color',
                'label' => $this->l('Background color', 'AdminEtsACReminderBarController'),
                'type' => 'color',
                'validate' => 'isColor',
                'default' => '#ff514e',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'background-color',
                'form_group_class' => 'abancart form_message isColor ets_ac_config_popup_item'
            ),
            'popup_width' => array(
                'name' => 'popup_width',
                'label' => $this->l('Width', 'AdminEtsACReminderBarController'),
                'type' => 'range',
                'min' => 200,
                'max' => 5000,
                'default' => '1170',
                'unit' => $this->l('px', 'AdminEtsACReminderBarController'),
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'width',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'popup_height' => array(
                'name' => 'popup_height',
                'label' => $this->l('Height', 'AdminEtsACReminderBarController'),
                'type' => 'range',
                'min' => 50,
                'max' => 500,
                'default' => '70',
                'unit' => $this->l('px', 'AdminEtsACReminderBarController'),
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'height',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'border_radius' => array(
                'name' => 'border_radius',
                'label' => $this->l('Border radius', 'AdminEtsACReminderBarController'),
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'default' => '0',
                'unit' => $this->l('px', 'AdminEtsACReminderBarController'),
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'border-radius',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),

            'border_width' => array(
                'name' => 'border_width',
                'label' => $this->l('Border width', 'AdminEtsACReminderBarController'),
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'default' => '0',
                'unit' => $this->l('px', 'AdminEtsACReminderBarController'),
                'validate' => 'isCleanHtml',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'border-width',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'border_color' => array(
                'name' => 'border_color',
                'label' => $this->l('Border color', 'AdminEtsACReminderBarController'),
                'type' => 'color',
                'validate' => 'isCleanHtml',
                'default' => '#ffffff',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'border-color',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'font_size' => array(
                'name' => 'font_size',
                'label' => $this->l('Text font size', 'AdminEtsACReminderBarController'),
                'type' => 'range',
                'min' => 10,
                'max' => 50,
                'validate' => 'isCleanHtml',
                'default' => '13',
                'unit' => $this->l('px', 'AdminEtsACReminderBarController'),
                'selector_change' => '.ets_abancart_preview_content_view .ets_abancart_preview,.ets_abancart_preview_content_view .ets_abancart_preview a,.ets_abancart_preview_content_view .ets_abancart_preview p',
                'attr_change' => 'font-size',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'close_btn_color' => array(
                'name' => 'close_btn_color',
                'label' => $this->l('Close button color', 'AdminEtsACReminderBarController'),
                'type' => 'color',
                'validate' => 'isCleanHtml',
                'default' => '#dddddd',
                'selector_change' => '.ets_abancart_preview_content_view',
                'attr_change' => 'background-color',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'padding' => array(
                'name' => 'padding',
                'label' => $this->l('Padding', 'AdminEtsACReminderBarController'),
                'type' => 'range',
                'min' => 0,
                'max' => 150,
                'unit' => $this->l('px', 'AdminEtsACReminderBarController'),
                'validate' => 'isCleanHtml',
                'default' => '10',
                'selector_change' => '.ets_abancart_preview',
                'attr_change' => 'padding',
                'form_group_class' => 'abancart content form_message ets_ac_config_popup_item'
            ),
            'vertical_align' => array(
                'name' => 'vertical_align',
                'label' => $this->l('Vertical alignment', 'AdminEtsACReminderBarController'),
                'type' => 'select',
                'validate' => 'isCleanHtml',
                'default' => 'center',
                'options' => array(
                    'name' => 'name',
                    'id' => 'id',
                    'query' => array(
                        array(
                            'name' => $this->l('Left', 'AdminEtsACReminderBarController'),
                            'id' => 'left'
                        ),
                        array(
                            'name' => $this->l('Center', 'AdminEtsACReminderBarController'),
                            'id' => 'center'
                        ),
                        array(
                            'name' => $this->l('Right', 'AdminEtsACReminderBarController'),
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
                'label' => $this->l('Has shopping cart', 'AdminEtsACReminderBarController'),
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
                'short_codes' => EtsAbancartDefines::getInstance()->getFields('short_codes'),
                'lead_forms' => EtsAbancartForm::getAllForms(false, true),
                'maxSizeUpload' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'baseUri' => __PS_BASE_URI__,
                'field_types' => EtsAbancartField::getInstance()->getFieldType(),
                'module_dir' => _PS_MODULE_DIR_ . $this->module->name,
                'is17Ac' => $this->module->is17,
                'image_url' => $this->context->shop->getBaseURL(true) . 'img/' . $this->module->name . '/img/',
            );

            $this->toJson(array(
                'html' => $this->renderForm(),
            ));
        }
    }
}