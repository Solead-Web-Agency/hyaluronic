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
require_once(dirname(__FILE__) . '/AdminEtsACController.php');

class AdminEtsACLeadsController extends AdminEtsACController
{
    public $lead_form_inputs = array();
    public $lead_fields = array();
    public $thankyou_page_fields = array();

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        $this->table = 'ets_abancart_form';
        $this->_select = 'fl.*, SUM(IF(fs.id_ets_abancart_form, 1,0)) as total_lead';
        $this->_join = ' 
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_form_lang` fl ON fl.id_ets_abancart_form = a.id_ets_abancart_form AND fl.id_lang=' . (int)$this->context->language->id . ' 
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_form_submit` fs ON fs.id_ets_abancart_form = a.id_ets_abancart_form';
        $this->_where = ' AND a.id_shop=' . (int)$this->context->shop->id;
        $this->_group = ' GROUP BY a.id_ets_abancart_form';
        $this->_orderBy = 'id_ets_abancart_form';
        $this->_orderWay = 'DESC';
        $this->identifier = 'id_ets_abancart_form';
        $this->list_id = $this->table;
        $this->className = 'EtsAbancartForm';
        $this->actions = array('view', 'edit', 'duplicate', 'delete');
        $this->fields_list = array(
            'id_ets_abancart_form' => array(
                'title' => $this->l('ID', 'AdminEtsACLeadsController'),
                'type' => 'int',
                'filter_key' => 'a!id_ets_abancart_form'
            ),
            'name' => array(
                'title' => $this->l('Title', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'float' => true,
                'remove_onclick' => true,
                'filter_key' => 'fl!name',
                'class' => 'title'
            ),
            'description' => array(
                'title' => $this->l('Description', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'filter_key' => 'fl!description',
                'class' => 'lead_description',
                'float' => 1
            ),
            'enable' => array(
                'title' => $this->l('Active', 'AdminEtsACLeadsController'),
                'type' => 'bool',
                'active' => 'status',
                'filter_key' => 'a!enable',
                'class' => 'center',
                'remove_onclick' => true
            ),
            'total_lead' => array(
                'title' => $this->l('Total lead', 'AdminEtsACLeadsController'),
                'type' => 'int',
                'havingFilter' => true,
                'class' => 'center',
                'remove_onclick' => true
            ),
        );

        $this->lead_fields = array(
            'name' => array(
                'label' => $this->l('Title', 'AdminEtsACLeadsController'),
                'name' => 'name',
                'lang' => true,
                'required' => true,
                'validate' => 'isString'
            ),
            'description' => array(
                'label' => $this->l('Description', 'AdminEtsACLeadsController'),
                'name' => 'description',
                'lang' => true,
                'validate' => 'isString'
            ),
            'type' => array(
                'label' => $this->l('Type', 'AdminEtsACLeadsController'),
                'name' => 'type',
                'required' => true,
                'validate' => 'isInt'
            ),
            'placeholder' => array(
                'label' => $this->l('Place holder', 'AdminEtsACLeadsController'),
                'name' => 'placeholder',
                'lang' => true,
                'validate' => 'isString'
            ),
            'content' => array(
                'label' => $this->l('Content', 'AdminEtsACLeadsController'),
                'name' => 'content',
                'lang' => true,
                'required' => true,
                'validate' => 'isString'
            ),
            'is_contact_name' => array(
                'label' => $this->l('Is contact name', 'AdminEtsACLeadsController'),
                'name' => 'required',
            ),
            'is_contact_email' => array(
                'label' => $this->l('Is contact email', 'AdminEtsACLeadsController'),
                'name' => 'required',
            ),
            'required' => array(
                'label' => $this->l('Required', 'AdminEtsACLeadsController'),
                'name' => 'required',
            ),
            'display_column' => array(
                'label' => $this->l('Display column', 'AdminEtsACLeadsController'),
                'name' => 'display_column',
            ),
            'enable' => array(
                'label' => $this->l('Enable', 'AdminEtsACLeadsController'),
                'name' => 'enable',
            ),
        );
        $this->context->smarty->assign(array(
            'idLangDefault' => Configuration::get('PS_LANG_DEFAULT'),
            'baseLinkLeadForm' => $this->context->shop->getBaseURL(true),
            'languages' => Language::getLanguages(false),
            'formItem' => Tools::isSubmit('updateets_abancart_form') && ($idForm = (int)Tools::getValue('id_ets_abancart_form')) ? new EtsAbancartForm($idForm) : null
        ));
        $this->lead_form_inputs = array(
            'name' => array(
                'name' => 'name',
                'label' => $this->l('Form title', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'class' => 'ets-ac-form-title',
                'lang' => true,
                'col' => 5,
                'required' => true,
                'validate' => 'isString',
                'form_group_class' => ''
            ),
            'alias' => array(
                'name' => 'alias',
                'label' => $this->l('Friendly URL', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'lang' => true,
                'class' => 'ets-ac-form-alias',
                'col' => 5,
                'required' => true,
                'validate' => 'isString',
                'form_group_class' => '',
                'desc' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/desc_form_item.tpl'),
            ),
            'description' => array(
                'name' => 'description',
                'label' => $this->l('Description', 'AdminEtsACLeadsController'),
                'type' => 'textarea',
                'lang' => true,
                'col' => 5,
                'autoload_rte' => true,
                'validate' => 'isString',
                'form_group_class' => ''
            ),
            'btn_title' => array(
                'name' => 'btn_title',
                'label' => $this->l('Button label ', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'lang' => true,
                'col' => 5,
                'required' => true,
                'validate' => 'isString',
                'form_group_class' => ''
            ),
            'btn_bg_color' => array(
                'name' => 'btn_bg_color',
                'label' => $this->l('Button color', 'AdminEtsACLeadsController'),
                'type' => 'color',
                'default' => '#0099ff',
                'required' => true,
                'validate' => 'isColor',
                'form_group_class' => ''
            ),
            'btn_bg_hover_color' => array(
                'name' => 'btn_bg_hover_color',
                'label' => $this->l('Button hover color', 'AdminEtsACLeadsController'),
                'type' => 'color',
                'default' => '#006bb3',
                'required' => true,
                'validate' => 'isColor',
                'form_group_class' => ''
            ),
            'btn_text_color' => array(
                'name' => 'btn_text_color',
                'label' => $this->l('Button text color', 'AdminEtsACLeadsController'),
                'type' => 'color',
                'default' => '#ffffff',
                'required' => true,
                'validate' => 'isColor',
                'form_group_class' => ''
            ),
            'btn_text_hover_color' => array(
                'name' => 'btn_text_hover_color',
                'label' => $this->l('Button text hover color', 'AdminEtsACLeadsController'),
                'type' => 'color',
                'default' => '#ffffff',
                'required' => true,
                'validate' => 'isColor',
                'form_group_class' => ''
            ),
            'enable_captcha' => array(
                'name' => 'enable_captcha',
                'label' => $this->l('Use captcha', 'AdminEtsACLeadsController'),
                'type' => 'switch',
                'values' => array(
                    array(
                        'label' => $this->l('Yes', 'AdminEtsACLeadsController'),
                        'value' => 1
                    ),
                    array(
                        'label' => $this->l('No', 'AdminEtsACLeadsController'),
                        'value' => 0
                    ),
                ),
                'form_group_class' => ''
            ),
            'captcha_type' => array(
                'name' => 'captcha_type',
                'label' => $this->l('Captcha type', 'AdminEtsACLeadsController'),
                'type' => 'select',
                'form_group_class' => 'ets_ac_lead_captcha_item',
                'options' => array(
                    'id' => 'id',
                    'name' => 'name',
                    'query' => array(
                        array(
                            'name' => $this->l('reCaptcha v2', 'AdminEtsACLeadsController'),
                            'id' => 'v2',
                        ),
                        array(
                            'name' => $this->l('reCaptcha v3', 'AdminEtsACLeadsController'),
                            'id' => 'v3',
                        )
                    )
                ),
                'form_group_class' => ' ets_ac_lead_captcha_item'
            ),
            'captcha_site_key_v2' => array(
                'name' => 'captcha_site_key_v2',
                'label' => $this->l('Site key', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'default' => '',
                'required' => true,
                'validate' => 'isString',
                'col' => 4,
                'form_group_class' => ' ets_ac_lead_captcha_item ets_ac_lead_captcha_item_type ets_ac_lead_captcha_item_type_v2',
            ),
            'captcha_secret_key_v2' => array(
                'name' => 'captcha_secret_key_v2',
                'label' => $this->l('Secret key', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'default' => '',
                'required' => true,
                'validate' => 'isString',
                'col' => 4,
                'form_group_class' => ' ets_ac_lead_captcha_item ets_ac_lead_captcha_item_type ets_ac_lead_captcha_item_type_v2',
                'desc' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/ets_abandonedcart/views/templates/hook/desc_captcha.tpl')
            ),
            'captcha_site_key_v3' => array(
                'name' => 'captcha_site_key_v3',
                'label' => $this->l('Site key', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'default' => '',
                'required' => true,
                'validate' => 'isString',
                'col' => 4,
                'form_group_class' => ' ets_ac_lead_captcha_item ets_ac_lead_captcha_item_type ets_ac_lead_captcha_item_type_v3',
            ),
            'captcha_secret_key_v3' => array(
                'name' => 'captcha_secret_key_v3',
                'label' => $this->l('Secret key', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'default' => '',
                'required' => true,
                'validate' => 'isString',
                'col' => 4,
                'form_group_class' => ' ets_ac_lead_captcha_item ets_ac_lead_captcha_item_type ets_ac_lead_captcha_item_type_v3',
                'desc' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/ets_abandonedcart/views/templates/hook/desc_captcha.tpl')
            ),

            'disable_captcha_lic' => array(
                'name' => 'disable_captcha_lic',
                'label' => $this->l('Disable captcha for logged in customer', 'AdminEtsACLeadsController'),
                'type' => 'switch',
                'form_group_class' => 'ets_ac_lead_captcha_item',
                'values' => array(
                    array(
                        'label' => $this->l('Yes', 'AdminEtsACLeadsController'),
                        'value' => 1
                    ),
                    array(
                        'label' => $this->l('No', 'AdminEtsACLeadsController'),
                        'value' => 0
                    ),
                ),
                'form_group_class' => ' ets_ac_lead_captcha_item'
            ),
            'enable' => array(
                'name' => 'enable',
                'label' => $this->l('Enable', 'AdminEtsACLeadsController'),
                'type' => 'switch',
                'values' => array(
                    array(
                        'label' => $this->l('Yes', 'AdminEtsACLeadsController'),
                        'value' => 1
                    ),
                    array(
                        'label' => $this->l('No', 'AdminEtsACLeadsController'),
                        'value' => 0
                    ),
                ),
                'form_group_class' => ''
            ),

            'field_list' => array(
                'name' => 'field_list',
                'type' => 'field_list',
            ),
        );

        $this->thankyou_page_fields = array(
            'display_thankyou_page' => array(
                'name' => 'display_thankyou_page',
                'label' => $this->l('Display "Thank you" page after form submission', 'AdminEtsACLeadsController'),
                'type' => 'switch',
                'values' => array(
                    array(
                        'label' => $this->l('Yes', 'AdminEtsACLeadsController'),
                        'value' => 1
                    ),
                    array(
                        'label' => $this->l('No', 'AdminEtsACLeadsController'),
                        'value' => 0
                    ),
                ),
                'class' => 'on_thankyou_page',
                'form_group_class' => ''
            ),
            'thankyou_page_title' => array(
                'name' => 'thankyou_page_title',
                'label' => $this->l('Title', 'AdminEtsACLeadsController'),
                'label_validate' => $this->l('Title of thank you page', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'required' => true,
                'lang' => true,
                'col' => 5,
                'class' => 'on_thankyou_page',
                'form_group_class' => 'ets_ac_tab_lead_tp_option_item'
            ),
            'thankyou_page_alias' => array(
                'name' => 'thankyou_page_alias',
                'label' => $this->l('Alias', 'AdminEtsACLeadsController'),
                'type' => 'text',
                'lang' => true,
                'col' => 5,
                'class' => 'on_thankyou_page',
                'form_group_class' => 'ets_ac_tab_lead_tp_option_item',
                'desc' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/desc_tp_item.tpl'),
            ),
            'thankyou_page_content' => array(
                'name' => 'thankyou_page_content',
                'label' => $this->l('Content', 'AdminEtsACLeadsController'),
                'label_validate' => $this->l('Content of thank you page', 'AdminEtsACLeadsController'),
                'type' => 'textarea',
                'required' => true,
                'lang' => true,
                'col' => 5,
                'autoload_rte' => true,
                'class' => 'on_thankyou_page',
                'form_group_class' => 'ets_ac_tab_lead_tp_option_item'
            ),
        );
    }


    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => ($idForm = (int)Tools::getValue('id_ets_abancart_form')) ? sprintf($this->l('Edit lead form %s', 'AdminEtsACLeadsController'), '#' . $idForm) : $this->l('Add new lead form', 'AdminEtsACLeadsController'),
            ),
            'input' => array_merge($this->lead_form_inputs, $this->thankyou_page_fields),
            'submit' => array(
                'title' => $this->l('Save', 'AdminEtsACLeadsController'),
                'name' => 'submitSaveLeadConfigs'
            )
        );
        $formItem = isset($idForm) && $idForm ? new EtsAbancartForm($idForm, $this->context->language->id) : null;
        $this->context->smarty->assign(array(
            'leadFormTitle' => $formItem ? $formItem->name : '',
        ));
        $this->tpl_form_vars = array(
            'field_types' => EtsAbancartField::getInstance()->getFieldType(),
            'content_field_list' => $this->getConfigFieldsList(),
        );
        $this->content = parent::renderForm() . $this->getEmptyField();
    }

    public function getConfigFieldsList()
    {
        $idForm = (int)Tools::getValue('id_ets_abancart_form');
        $this->context->smarty->assign(array(
            'fields' => $idForm ? EtsAbancartField::getAllFields(false, $idForm) : array(),
            'languages' => Language::getLanguages(false),
            'default_lang' => Configuration::get('PS_LANG_DEFAULT'),
            'field_types' => EtsAbancartField::getInstance()->getFieldType(),
            'idForm' => $idForm
        ));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/ets_abandonedcart/views/templates/hook/field/field_list.tpl');
    }

    public function getEmptyField()
    {
        $this->context->smarty->assign(array(
            'languages' => Language::getLanguages(false),
            'default_lang' => Configuration::get('PS_LANG_DEFAULT'),
        ));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/ets_abandonedcart/views/templates/hook/field/empty_item.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitSaveLeadConfigs')) {
            $this->validateLeadForm(array_merge($this->lead_form_inputs, $this->thankyou_page_fields));
            $this->validateLeadFields($this->lead_fields);
            if (!$this->errors) {
                if (($formItem = $this->saveLeadForm()) && $formItem->id) {
                    $this->saveLeadFields($formItem->id);
                }
                $this->confirmations[] = $this->l('Configuration updated successfully', 'AdminEtsACLeadsController');
            }
        }
        parent::postProcess();

        if (Tools::isSubmit('etsAcDeleteLeadField')) {
            $idField = (int)Tools::getValue('id_field');
            if ($idField) {
                $field = new EtsAbancartField($idField);
                if ($field && $field->id && $field->delete()) {
                    die(Tools::jsonEncode(array(
                        'success' => true,
                        'message' => $this->l('Deleted successfully', 'AdminEtsACLeadsController')
                    )));
                } else {
                    die(Tools::jsonEncode(array(
                        'success' => false,
                        'message' => $this->l('Field does not exist', 'AdminEtsACLeadsController')
                    )));
                }
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'message' => $this->l('No field to delete', 'AdminEtsACLeadsController')
            )));
        }
        if (Tools::isSubmit('downloadFile')) {
            $idFieldValue = (int)Tools::getValue('idFieldValue');
            $fieldValue = new EtsAbancartFieldValue($idFieldValue);
            if ($fieldValue && $fieldValue->id && $fieldValue->value) {
                $fieldItem = new EtsAbancartField($fieldValue->id_ets_abancart_field);
                if ($fieldItem->type == EtsAbancartField::FIELD_TYPE_FILE && file_exists(_PS_DOWNLOAD_DIR_ . $this->module->name . '/' . $fieldValue->value)) {
                    $this->downloadFile($fieldValue->value, $fieldValue->file_name);
                }
            }
            die($this->l('File does not exist', 'AdminEtsACLeadsController'));
        }

        if (Tools::isSubmit('etsAcSortFormField')) {
            $idForm = (int)Tools::getValue('idForm');
            $sortData = Tools::getValue('sortData');

            //Validate array
            if (!is_array($sortData)) {
                $sortData = array();
                foreach ($sortData as $k => $i) {
                    if (!Validate::isCleanHtml($i)) {
                        unset($sortData[$k]);
                    }
                }
            }
            if ($idForm && ($formItem = new EtsAbancartForm($idForm)) && $formItem->id && $sortData && is_array($sortData)) {
                foreach ($sortData as $k => $item) {
                    $field = new EtsAbancartField((int)$item);
                    if ($field) {
                        $field->position = $k + 1;
                        $field->update();
                    }
                }
                die(Tools::jsonEncode(array(
                    'success' => true,
                    'message' => $this->l('Updated successfully', 'AdminEtsACLeadsController')
                )));
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'message' => $this->l('Update failed', 'AdminEtsACLeadsController')
            )));
        }

        if (isset($this->context->cookie->ets_ac_duplicate_form_success) && $this->context->cookie->ets_ac_duplicate_form_success) {
            $this->confirmations[] = $this->context->cookie->ets_ac_duplicate_form_success;
            $this->context->cookie->ets_ac_duplicate_form_success = null;
        }
        if (isset($this->context->cookie->ets_ac_duplicate_form_error) && $this->context->cookie->ets_ac_duplicate_form_error) {
            $this->errors[] = $this->context->cookie->ets_ac_duplicate_form_error;
            $this->context->cookie->ets_ac_duplicate_form_error = null;
        }
        if (Tools::isSubmit('duplicate_form')) {
            $idForm = (int)Tools::getValue('id_ets_abancart_form');
            if ($idForm && ($formItem = new EtsAbancartForm($idForm)) && $formItem->id) {
                $formItem->id = null;
                $formItem->is_init = 0;
                $languages = Language::getLanguages(false);
                $defaultLang = Configuration::get('PS_DEFAULT_LANG');
                foreach ($languages as $lang) {
                    $formItem->alias[$lang['id_lang']] = isset($formItem->alias[$lang['id_lang']]) ? $formItem->alias[$lang['id_lang']] . '-' . (EtsAbancartForm::getMaxId() + 1) : $formItem->alias[$defaultLang] . '-' . (EtsAbancartForm::getMaxId() + 1);
                    $formItem->thankyou_page_alias[$lang['id_lang']] = isset($formItem->thankyou_page_alias[$lang['id_lang']]) ? $formItem->thankyou_page_alias[$lang['id_lang']] . '-' . (EtsAbancartForm::getMaxId() + 1) : $formItem->display_thankyou_page[$defaultLang] . '-' . (EtsAbancartForm::getMaxId() + 1);
                }
                if ($formItem->add()) {
                    $fields = EtsAbancartField::getAllFields(false, $idForm, $this->context->language->id);
                    if ($fields) {
                        foreach ($fields as $field) {
                            $f = new EtsAbancartField($field['id_ets_abancart_field']);
                            $f->id = null;
                            $f->id_ets_abancart_form = $formItem->id;
                            $f->add();
                        }
                    }
                    $this->context->cookie->ets_ac_duplicate_form_success = $this->l('A form duplicated successfully', 'AdminEtsACLeadsController');
                    Tools::redirect($this->context->link->getAdminLink('AdminEtsACLeads'));
                }
            }
            $this->context->cookie->ets_ac_duplicate_form_error = $this->l('Cannot duplicate form', 'AdminEtsACLeadsController');
        }

        if (Tools::isSubmit('exportLeadForm')) {
            $idForm = (int)Tools::getValue('id_ets_abancart_form');
            if ($idForm && ($formItem = new EtsAbancartForm($idForm)) && $formItem->id) {
                $this->exportLeadForm($idForm);
            }
        }
    }

    public function validateLeadFields()
    {
        $fields = (int)Tools::getValue('lead_field');
        if (!$fields || !is_array($fields)) {
            return;
        }
        $idLangDefault = Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);
        $optionType = array(EtsAbancartField::FIELD_TYPE_CHECKBOX, EtsAbancartField::FIELD_TYPE_RADIO, EtsAbancartField::FIELD_TYPE_SELECT);
        foreach ($fields as $field) {
            foreach ($this->lead_fields as $config) {
                if (isset($config['lang']) && $config['lang']) {
                    $valDefault = isset($field[$config['name']][$idLangDefault]) && ($valDefault = $field[$config['name']][$idLangDefault]) && Validate::isCleanHtml($valDefault) ? $valDefault : '';
                    if (isset($config['required']) && $config['required'] && !Tools::strlen($valDefault)) {
                        $type = $field['type'];
                        if (($config['name'] == 'content' && in_array($type, $optionType)) || $config['name'] !== 'content') {
                            $this->errors[] = sprintf($this->l('%s is required', 'AdminEtsACLeadsController'), isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                        }
                    }
                    foreach ($languages as $lang) {
                        $val = isset($field[$config['name']][$lang['id_lang']]) && ($val = $field[$config['name']][$lang['id_lang']]) && Validate::isCleanHtml($val) ? $val : '';
                        if (isset($config['validate']) && $config['validate'] && Tools::strlen($val) && !Validate::{$config['validate']}($val)) {
                            $this->errors[] = sprintf($this->l('"%s" %s is invalid', 'AdminEtsACLeadsController'), $lang['iso_code'], isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                        }
                    }
                } else {
                    $val = isset($field[$config['name']]) && ($val = $field[$config['name']]) && Validate::isCleanHtml($val) ? $val : '';
                    if (isset($config['required']) && $config['required'] && !Tools::strlen($val)) {
                        $this->errors[] = sprintf($this->l('%s is required', 'AdminEtsACLeadsController'), isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                    } elseif (isset($config['validate']) && $config['validate'] && Tools::strlen($val) && !Validate::{$config['validate']}($val)) {
                        $this->errors[] = sprintf($this->l('%s is invalid', 'AdminEtsACLeadsController'), isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                    }
                }
            }
        }
    }

    public function validateLeadForm($configs)
    {
        $idLangDefault = Configuration::get('PS_LANG_DEFAULT');
        $idForm = (int)Tools::getValue('id_ets_abancart_form');
        $languages = Language::getLanguages(false);
        $captchaV2Items = array('captcha_site_key_v2', 'captcha_secret_key_v2');
        $captchaV3Items = array('captcha_site_key_v3', 'captcha_secret_key_v3');
        $enableCaptcha = (int)Tools::getValue('enable_captcha');
        $captchaType = ($captchaType = Tools::getValue('captcha_type')) && Validate::isCleanHtml($captchaType) ? $captchaType : '';
        $enableThankyouPage = (int)Tools::getValue('display_thankyou_page');
        $thankyouPageOptions = array('thankyou_page_title', 'thankyou_page_alias', 'thankyou_page_content');
        foreach ($configs as $config) {
            if (isset($config['lang']) && $config['lang']) {
                $valDefault = ($valDefault = Tools::getValue($config['name'] . '_' . $idLangDefault)) && Validate::isCleanHtml($valDefault, true) ? $valDefault : '';
                if (isset($config['required']) && $config['required'] && !Tools::strlen($valDefault)) {
                    if (!in_array($config['name'], $thankyouPageOptions) || (in_array($config['name'], $thankyouPageOptions) && $enableThankyouPage))
                        $this->errors[] = sprintf($this->l('%s is required', 'AdminEtsACLeadsController'), isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                }
                foreach ($languages as $lang) {

                    $val = ($val = Tools::getValue($config['name'] . '_' . $lang['id_lang'])) && Validate::isCleanHtml($val, true) ? trim($val) : '';
                    if (isset($config['validate']) && $config['validate'] && Tools::strlen($val)) {
                        if ($config['validate'] === 'isColor' && !preg_match('/^#[a-f0-9]{3,6}$/', $val)) {
                            $this->errors[] = sprintf($this->l('%s is not a valid color', 'AdminEtsACLeadsController'), isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                        } elseif ($config['validate'] !== 'isColor' && !Validate::{$config['validate']}($val)) {
                            $this->errors[] = sprintf($this->l('"%s" %s is invalid', 'AdminEtsACLeadsController'), $lang['iso_code'], isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                        }
                    }
                    if ($config['name'] == 'alias' && $val && EtsAbancartForm::getFormByAlias($val, $lang['id_lang'], false, $idForm ? array($idForm) : array())) {
                        $this->errors[] = sprintf($this->l('"%s" This alias has been used in another form. Please use another alias', 'AdminEtsACLeadsController'), $val);
                    } elseif ($config['name'] == 'thankyou_page_alias' && $val && EtsAbancartForm::getThankyouPageByAlias($val, $lang['id_lang'], false, false, $idForm ? array($idForm) : array())) {
                        $this->errors[] = sprintf($this->l('"%s" Thank you page alias has been used in another form. Please use another alias', 'AdminEtsACLeadsController'), $lang['iso_code']);
                    }
                }
            } else {
                $val = ($val = Tools::getValue($config['name'])) && Validate::isCleanHtml($val) ? $val : '';
                if (isset($config['required']) && $config['required'] && !Tools::strlen($val)) {
                    if ($enableCaptcha && (($captchaType == 'v2' && in_array($config['name'], $captchaV2Items)) || ($captchaType == 'v3' && in_array($config['name'], $captchaV3Items)))) {
                        $this->errors[] = sprintf($this->l('%s is required', 'AdminEtsACLeadsController'), isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                    } elseif (!$enableCaptcha && !in_array($config['name'], array_merge($captchaV2Items, $captchaV3Items))) {
                        $this->errors[] = sprintf($this->l('%s is required', 'AdminEtsACLeadsController'), isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                    }
                } elseif (isset($config['validate']) && $config['validate'] && Tools::strlen($val)) {
                    if ($config['validate'] === 'isColor' && !preg_match('/^#[a-f0-9]{3,6}$/', $val)) {
                        $this->errors[] = sprintf($this->l('%s is not valid color', 'AdminEtsACLeadsController'), isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                    } elseif ($config['validate'] !== 'isColor' && !Validate::{$config['validate']}($val)) {
                        $this->errors[] = sprintf($this->l('%s is invalid', 'AdminEtsACLeadsController'), isset($config['label_validate']) ? $config['label_validate'] : $config['label']);
                    }
                }
            }
        }
    }

    public function saveLeadForm()
    {
        $idLangDefault = Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);
        $idForm = (int)Tools::getValue('id_ets_abancart_form');
        if ($idForm)
            $formItem = new EtsAbancartForm($idForm);
        else
            $formItem = new EtsAbancartForm();
        $configs = array_merge($this->lead_form_inputs, $this->thankyou_page_fields);
        foreach ($configs as $config) {
            if (isset($config['lang']) && $config['lang']) {
                $valLang = array();
                $valDefault = ($valDefault = Tools::getValue($config['name'] . '_' . $idLangDefault)) && Validate::isCleanHtml($valDefault) ? $valDefault : '';
                foreach ($languages as $lang) {
                    $val = ($val = Tools::getValue($config['name'] . '_' . $lang['id_lang'])) && Validate::isCleanHtml($val) ? $val : '';
                    if (!$val) {
                        $val = $valDefault;
                    }
                    $valLang[$lang['id_lang']] = $val;
                }
                $formItem->{$config['name']} = $valLang;
            } else {
                $val = ($val = Tools::getValue($config['name'])) && Validate::isCleanHtml($val) ? $val : '';
                $formItem->{$config['name']} = $val;
            }
        }
        $formItem->id_shop = $this->context->shop->id;
        if ($idForm)
            return $formItem->update() ? $formItem : null;
        return $formItem->add() ? $formItem : null;
    }

    public function saveLeadFields($idForm)
    {
        $fields = Tools::getValue('lead_field');
        if (!$fields || !is_array($fields)) {
            return;
        }
        $idLangDefault = Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);
        foreach ($fields as $id => $field) {
            if (EtsAbancartField::getFieldById($id)) {
                $fieldItem = new EtsAbancartField($id);
            } else {
                $fieldItem = new EtsAbancartField();
            }
            foreach ($this->lead_fields as $config) {
                if (isset($config['lang']) && $config['lang']) {
                    $valDefault = isset($field[$config['name']][$idLangDefault]) && ($valDefault = $field[$config['name']][$idLangDefault]) && Validate::isCleanHtml($valDefault) ? $valDefault : '';
                    $valLang = array();
                    foreach ($languages as $lang) {
                        $val = isset($field[$config['name']][$lang['id_lang']]) && ($val = $field[$config['name']][$lang['id_lang']]) && Validate::isCleanHtml($val) ? $val : '';
                        $valLang[$lang['id_lang']] = $val ?: $valDefault;
                    }
                    $fieldItem->{$config['name']} = $valLang;
                } else {
                    $val = isset($field[$config['name']]) && ($val = $field[$config['name']]) && Validate::isCleanHtml($val) ? $val : '';
                    $fieldItem->{$config['name']} = $val;
                }
            }
            if ($fieldItem->id) {
                $fieldItem->update();
            } else {
                $fieldItem->id_ets_abancart_form = $idForm;
                $fieldItem->position = (int)EtsAbancartField::getMaxPosition() + 1;
                $fieldItem->add();
            }
        }
    }

    public function ajaxProcessStatus()
    {
        $id = (int)Tools::getValue('id_ets_abancart_form');
        if ($id) {
            $formItem = new EtsAbancartForm($id);
            if ($formItem && $formItem->id) {
                $formItem->enable = !$formItem->enable;
                if (!$formItem->update()) {
                    $this->errors[] = $this->l('Cannot update this item', 'AdminEtsACLeadsController');
                }
            } else {
                $this->errors[] = $this->l('Form does not exist', 'AdminEtsACLeadsController');
            }
            $this->toJson(array(
                'hasError' => $this->errors ? true : false,
                'enabled' => $formItem->enable,
                'msg' => $this->errors ? $this->module->displayError($this->errors) : $this->l('Update status successfully', 'AdminEtsACLeadsController'),
            ));
        }
        $this->errors[] = $this->l('No item to update', 'AdminEtsACLeadsController');
        $this->toJson(array(
            'hasError' => true,
            'enabled' => $formItem->enable,
            'msg' => $this->errors ? $this->module->displayError($this->errors) : $this->l('Update status successfully', 'AdminEtsACLeadsController'),
        ));
    }

    public function renderView()
    {
        $idForm = (int)Tools::getValue('id_ets_abancart_form');
        $form = EtsAbancartForm::getFormById($idForm, true);
        if ($form) {
            $limit = 50;
            $total = EtsAbancartFormSubmit::getTotalFormSubmit($idForm);
            $page = (int)Tools::getValue('page');
            if (!$page) {
                $page = 1;
            }
            $totalPage = ceil($total / $limit);
            if ($total && $page > $total) {
                $page = $totalPage;
            }
            $start = ($page - 1) * $limit;
            $fieldValues = EtsAbancartFormSubmit::getFormSubmitData($idForm, $start, $limit);

            $this->context->smarty->assign(array(
                'leadForm' => $form,
                'fieldValues' => $fieldValues,
                'totalPage' => $totalPage,
                'totalForm' => $total,
                'currentPage' => $page,
                'linkList' => $this->context->link->getAdminLink('AdminEtsACLeads'),
                'linkPage' => $this->context->link->getAdminLink('AdminEtsACLeads') . '&viewets_abancart_form=&id_ets_abancart_form=' . $idForm,
                'limit' => $limit,
                'leadFormTitle' => $form['name'],
                'fieldTypes' => EtsAbancartField::getInstance()->getFieldType(),
                'linkDownloadFile' => $this->context->link->getAdminLink('AdminEtsACLeads') . '&downloadFile=1',
                'linkExportLeadForm' => $this->context->link->getAdminLink('AdminEtsACLeads') . '&exportLeadForm=1&id_ets_abancart_form=' . (int)$idForm,
            ));
        }
        return parent::renderView();
    }

    public function downloadFile($fileName, $displayName = null)
    {
        if (!$displayName) {
            $displayName = $fileName;
        }
        if ($fileName && file_exists(_PS_DOWNLOAD_DIR_ . $this->module->name . '/' . $fileName)) {
            $filepath = _PS_DOWNLOAD_DIR_ . $this->module->name . '/' . $fileName;
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $displayName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            flush(); // Flush system output buffer
            readfile($filepath);
            exit();
        }
        die($this->l('File does not exist', 'AdminEtsACLeadsController'));
    }

    public function displayDuplicateLink($token, $id)
    {
        if (!isset(self::$cache_lang['duplicate'])) {
            self::$cache_lang['duplicate'] = $this->l('Duplicate', 'AdminEtsACLeadsController');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&duplicate_form&' . $this->identifier . '=' . $id .
                '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['duplicate'],
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/link_duplicate_form.tpl');
    }

    public function displayDeleteLink($token, $id)
    {
        if (($object = new EtsAbancartForm($id)) && !$object->is_init) {
            if (!isset(self::$cache_lang['deleteleadform'])) {
                self::$cache_lang['deleteleadform'] = $this->l('Delete', 'AdminEtsACLeadsController');
            }
            $this->context->smarty->assign(array(
                'href' => self::$currentIndex .
                    '&' . $this->identifier . '=' . $id .
                    '&delete' . $this->table . '&token=' . ($token != null ? $token : $this->token),
                'action' => self::$cache_lang['deleteleadform'],
                'confirm' => $this->l('Delete selected items?', 'AdminEtsACLeadsController'),
            ));

            return $this->context->smarty->fetch('helpers/list/list_action_delete.tpl');
        }
    }

    public function exportLeadForm($idForm)
    {
        $formSubmits = EtsAbancartFormSubmit::getFormSubmitData($idForm, 0, 50);
        $csv = "";
        $filename = 'lead_form_' . $idForm . ".csv";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-type: application/x-msdownload");
        if ($formSubmits) {
            $leadForm = EtsAbancartForm::getFormById($idForm);
            if ($leadForm && isset($leadForm['fields'])) {
                $titles = array($this->l('ID', 'AdminEtsACLeadsController'), $this->l('Date', 'AdminEtsACLeadsController'));
                foreach ($leadForm['fields'] as $field) {
                    $titles[] = $field['name'];
                }
                $csv .= join("\t", $titles) . "\r\n";
            }
            $fieldTypes = EtsAbancartField::getInstance()->getFieldType();
            foreach ($formSubmits as $fs) {
                $dataItem = array($fs['id_ets_abancart_form_submit'], $fs['date_add']);
                if ($fs['field_values']) {
                    foreach ($leadForm['fields'] as $field) {
                        $foundCol = false;
                        foreach ($fs['field_values'] as $item) {
                            if ($field['display_column'] && $item['id_ets_abancart_field'] == $field['id_ets_abancart_field']) {
                                $foundCol = true;
                                if ($fieldTypes['file']['key'] == $field['type']) {
                                    if ($item['file_name'])
                                        $dataItem[] = $item['file_name'];
                                    else
                                        $dataItem[] = $item['value'];

                                } else
                                    $dataItem[] = $item['value'];
                            }
                        }
                        if (!$foundCol) {
                            $dataItem[] = "";
                        }
                    }
                    $csv .= join("\t", $dataItem) . "\r\n";
                }
            }
        }
        $csv = chr(255) . chr(254) . mb_convert_encoding($csv, "UTF-16LE", "UTF-8");
        echo $csv;
        exit;
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        foreach ($this->_list as &$item) {
            $item['description'] = strip_tags($item['description']);
            if (Tools::strlen($item['description']) > 200) {
                $item['description'] = Tools::substr($item['description'], 0, 200) . '...';
            }
            $this->context->smarty->assign(array(
                'leadFormName' => $item['name'],
                'leadFormUrl' => EtsAbancartForm::getLeadFormUrl(null, $item['alias']),
            ));
            $item['name'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/lead_form_link_item.tpl');
        }

        if (isset($item)) {
            unset($item);
        }
    }
}