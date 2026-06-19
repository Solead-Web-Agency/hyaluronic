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

abstract class AdminEtsACOptionsController extends AdminEtsACController
{
    public $def;

    public function __construct()
    {
        parent::__construct();

        $this->def = EtsAbancartDefines::getInstance();
    }

    public function renderOptions()
    {
        if (!$this->fields_options)
            return $this->content;
        // Reminder configs.
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => isset($this->fields_options['title']) ? $this->fields_options['title'] : '',
                    //'icon' => 'icon-AdminAdmin'
                ),
                'input' => array(),
                'submit' => array(
                    'title' => $this->l('Save', 'AdminEtsACOptionsController'),
                ),
            ),
        );
        if (isset($this->fields_options['buttons']) && $this->fields_options['buttons']) {
            $fields_form['form']['buttons'] = $this->fields_options['buttons'];
        }
        if (isset($this->fields_options['fields']) && count($this->fields_options['fields']) > 0) {
            foreach ($this->fields_options['fields'] as $key => $config) {
                $fields = $config;
                $fields['name'] = $key;
                $fields['values'] = isset($config['values']) ? $config['values'] : ($config['type'] == 'switch' ? array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Yes', 'AdminEtsACOptionsController')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('No', 'AdminEtsACOptionsController')
                    )
                ) : false);
                if ($config['type'] == 'select' && !empty($fields['multiple']) && stripos($fields['name'], '[]') === false)
                    $fields['name'] .= '[]';
                $fields_form['form']['input'][] = $fields;
            }
        }

        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        // Helper Form.
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this->module;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitOptions' . $this->table;
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->override_folder = '/';

        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code
            ),
            'fields_value' => $this->getConfigFieldsValue($helper->submit_action),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'controller_name' => preg_replace('#' . Ets_abandonedcart::$slugTab . '#', '', $this->controller_name),

        );
        if (Tools::strpos($this->controller_name, 'ReminderLeave') !== false) {
            $helper->tpl_vars['short_codes'] = EtsAbancartDefines::getInstance()->getFields('short_codes');
        }
        if (Tools::strpos($this->controller_name, 'Configs') !== false) {
            $helper->tpl_vars['url'] = $this->context->link->getAdminLink('AdminEtsACConfigs') . '&secure=' . Configuration::getGlobalValue('ETS_ABANCART_SECURE_TOKEN');
            $helper->tpl_vars['path'] = '* * * * * ' . (defined('PHP_BINDIR') && PHP_BINDIR && is_string(PHP_BINDIR) ? PHP_BINDIR . '/' : '') . 'php ' . _PS_MODULE_DIR_ . $this->module->name . '/cronjob.php secure=' . Configuration::getGlobalValue('ETS_ABANCART_SECURE_TOKEN');
        }
        /*if (Tools::strpos($this->controller_name, 'MailConfigs') !== -1) {
            $helper->tpl_vars['action'] = self::$currentIndex . '&token=' . $this->token;
        }*/
        $this->content .= $helper->generateForm(array($fields_form));
    }

    protected function getConfigFieldsValue($submit_action)
    {
        $fields = array();
        if (isset($this->fields_options['fields']) && $this->fields_options['fields']) {
            $languages = Language::getLanguages(false);

            // isSubmit.
            if (Tools::isSubmit($submit_action)) {
                foreach ($this->fields_options['fields'] as $key => $config) {
                    if (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $fields[$key][$l['id_lang']] = ($valLang = Tools::getValue($key . '_' . $l['id_lang'])) && Validate::isCleanHtml($valLang) ? $valLang : (isset($config['default']) ? $config['default'] : '');
                        }
                    } elseif ($config['type'] == 'select' && isset($config['multiple']) && $config['multiple']) {
                        $valArray = ($valArray = Tools::getValue($key)) && is_array($valArray) ? $valArray : array();
                        foreach ($valArray as $ki => $vi) {
                            if (!is_array($vi) && !Validate::isCleanHtml($vi)) {
                                unset($valArray[$ki]);
                            }
                        }
                        $fields[$key . ($config['type'] == 'select' ? '[]' : '')] = $valArray;
                    } elseif ($config['type'] == 'switch') {
                        $fields[$key] = Tools::getValue($key) ? 1 : 0;
                    } else {
                        $fields[$key] = ($valData = Tools::getValue($key)) && (is_array($valData) || Validate::isCleanHtml($valData)) ? $valData : (isset($config['default']) ? $config['default'] : '');
                    }
                    if ($key == 'ETS_ABANCART_REDUCTION_PRODUCT' && ($idProductReaction = (int)Tools::getValue('ETS_ABANCART_REDUCTION_PRODUCT'))) {
                        $pReduction = new Product($idProductReaction, false, $this->context->language->id);
                        $fields['specific_product_name'] = $pReduction->name;
                        $fields['specific_product_item'] = EtsAbancartReminderForm::getInstance()->displayListProduct('', array($pReduction->id), 'ets-ac-products-list-ETS_ABANCART_REDUCTION_PRODUCT');
                    } elseif ($key == 'ETS_ABANCART_SELECTED_PRODUCT') {
                        $fields['ETS_ABANCART_SELECTED_PRODUCT'] = array();
                        $ids = Tools::getValue('ETS_ABANCART_SELECTED_PRODUCT');
                        if (!$ids || !is_array($ids)) {
                            $ids = array();
                        }
                        if ($ids)
                            $ids = array_map('intval', $ids);
                        if ($ids) {
                            $fields['selected_product_list'] = EtsAbancartReminderForm::getInstance()->displayListProduct('ETS_ABANCART_SELECTED_PRODUCT', $ids, 'ets-ac-products-list-ETS_ABANCART_SELECTED_PRODUCT');
                        }
                    } elseif ($key == 'ETS_ABANCART_PRODUCT_GIFT' && ($idP = (int)Tools::getValue('ETS_ABANCART_GIFT_PRODUCT'))) {
                        $idA = (int)Tools::getValue('ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE');
                        $pGift = new Product($idP, false, $this->context->language->id);
                        $productName = $pGift->name;
                        if ($idA && ($attrs = $pGift->getAttributeCombinationsById($idA, $this->context->language->id))) {
                            foreach ($attrs as $item) {
                                $productName .= ' ' . $item['group_name'] . ' ' . $item['attribute_name'];
                            }
                        }
                        $fields['ETS_ABANCART_GIFT_PRODUCT'] = $idP;
                        $fields['ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE'] = $idA;
                        $fields['gift_product_name'] = $productName;
                        $fields['gift_product_item'] = EtsAbancartReminderForm::getInstance()->displayListProduct('gift_product_item', array($idP), 'ets-ac-products-list-product_gift', $productName);
                    }
                }
            } else {
                // Get Configs.
                foreach ($this->fields_options['fields'] as $key => $config) {
                    $global = !empty($config['global']) ? 1 : 0;
                    if (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $fields[$key][$l['id_lang']] = $this->getFields($key, $global, $l['id_lang']);
                        }
                    } elseif ($config['type'] == 'select' && isset($config['multiple']) && $config['multiple']) {
                        $fields[$key . ($config['type'] == 'select' ? '[]' : '')] = ($result = $this->getFields($key, $global)) != '' ? explode(',', $result) : array();
                    } else
                        $fields[$key] = $this->getFields($key, $global);
                    if ($key == 'ETS_ABANCART_REDUCTION_PRODUCT' && ($idProductReaction = (int)Configuration::get('ETS_ABANCART_REDUCTION_PRODUCT'))) {
                        $pReduction = new Product($idProductReaction, false, $this->context->language->id);
                        $this->fields_value['specific_product_name'] = $pReduction->name;
                        $fields['specific_product_item'] = EtsAbancartReminderForm::getInstance()->displayListProduct('', array($pReduction->id), 'ets-ac-products-list-ETS_ABANCART_REDUCTION_PRODUCT');
                    } elseif ($key == 'ETS_ABANCART_SELECTED_PRODUCT') {
                        $fields['ETS_ABANCART_SELECTED_PRODUCT'] = array();
                        $ids = Configuration::get('ETS_ABANCART_SELECTED_PRODUCT');
                        $ids = $ids ? explode(',', $ids) : array();
                        if ($ids)
                            $ids = array_map('intval', $ids);
                        if ($ids) {
                            $fields['selected_product_list'] = EtsAbancartReminderForm::getInstance()->displayListProduct('ETS_ABANCART_SELECTED_PRODUCT', $ids, 'ets-ac-products-list-ETS_ABANCART_SELECTED_PRODUCT');
                        }
                    } elseif ($key == 'ETS_ABANCART_PRODUCT_GIFT' && ($idP = (int)Configuration::get('ETS_ABANCART_GIFT_PRODUCT'))) {
                        $idA = (int)Configuration::get('ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE');
                        $pGift = new Product($idP, false, $this->context->language->id);
                        $productName = $pGift->name;
                        if ($idA && ($attrs = $pGift->getAttributeCombinationsById($idA, $this->context->language->id))) {
                            foreach ($attrs as $item) {
                                $productName .= ' ' . $item['group_name'] . ' ' . $item['attribute_name'];
                            }
                        }
                        $fields['ETS_ABANCART_GIFT_PRODUCT'] = $idP;
                        $fields['ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE'] = $idA;
                        $fields['gift_product_name'] = $productName;
                        $fields['gift_product_item'] = EtsAbancartReminderForm::getInstance()->displayListProduct('gift_product_item', array($idP), 'ets-ac-products-list-product_gift', $productName);
                    }
                }
            }
        }

        return $fields;
    }

    protected function getFields($key, $global = false, $idLang = null)
    {
        return $global ? Configuration::getGlobalValue($key, $idLang) : Configuration::get($key, $idLang);
    }

    protected function setFields($key, $values, $global = false, $html = false)
    {
        return $global ? Configuration::updateGlobalValue($key, $values, $html) : Configuration::updateValue($key, $values, $html);
    }

    protected function processUpdateOptions()
    {
        $api = trim(Tools::getValue('ETS_ABANCART_MAIL_SERVICE'));
        if (!$this->fields_options ||
            !isset($this->fields_options['fields']) ||
            !$this->fields_options['fields'] ||
            !trim($api) !== '' && !Validate::isCleanHtml($api)
        ) {
            return false;
        }
        $configs = $this->fields_options['fields'];
        $mail_options = ($options = $this->def->getMailOptions()) && isset($options[$api]) && $options[$api] ? $options[$api] : [];
        $languages = Language::getLanguages(false);
        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        // Validate Fields.
        $discountOption = Tools::isSubmit('submitOptionsconfiguration') ? Tools::getValue('ETS_ABANCART_DISCOUNT_OPTION') : Configuration::get('ETS_ABANCART_DISCOUNT_OPTION');
        $discountOption = Validate::isCleanHtml($discountOption) ? $discountOption : '';
        if ($configs) {
            foreach ($configs as $key => $config) {

                if (trim($key) !== 'ETS_ABANCART_MAIL_SERVICE' && $api !== 'default' && $mail_options) {
                    if ((!empty($mail_options['api']) && empty($config['api']))
                        || (empty($mail_options['api']) && !empty($config['api']))
                        || (!empty($config['object']) && !in_array($api, explode(',', $config['object'])))) {
                        continue;
                    }
                    if (isset($config['form_group_class']) && !in_array('trans', explode(',', $config['form_group_class']))) {
                        $key .= '_' . Tools::strtoupper($api);
                    }
                } elseif ($api === 'default' && (!isset($config['form_group_class']) || !in_array('trans', explode(',', $config['form_group_class'])))) {
                    continue;
                }

                if (isset($config['lang']) && $config['lang']) {
                    if (isset($config['required']) && $config['required'] && $config['type'] != 'switch' && trim(Tools::getValue($key . '_' . $id_lang_default) == '')) {
                        if ($key == 'ETS_ABANCART_DISCOUNT_NAME' && $discountOption == 'auto') {
                            $this->errors[] = $config['label'] . ' ' . $this->l('is required', 'AdminEtsACOptionsController');
                        } elseif ($key != 'ETS_ABANCART_DISCOUNT_NAME') {
                            $this->errors[] = $config['label'] . ' ' . $this->l('is required', 'AdminEtsACOptionsController');
                        }
                    }
                } else {
                    if (isset($config['required']) && $config['required'] && $config['type'] != 'switch' && $this->requiredFields($key)) {
                        if (!in_array($key, array('ETS_ABANCART_QUANTITY', 'ETS_ABANCART_QUANTITY_PER_USER'))) {
                            $this->errors[] = $config['label'] . ' ' . $this->l('is required', 'AdminEtsACOptionsController');
                        }
                    } elseif (isset($config['validate']) && $this->validateFields($key, $config)) {
                        $validate = $config['validate'];
                        if (!Validate::$validate(trim(Tools::getValue($key)))) {
                            $this->errors[] = $config['label'] . ' ' . $this->l('is invalid', 'AdminEtsACOptionsController');
                        }
                        unset($validate);
                    } elseif ($key == 'ETS_ABANCART_SECURE_TOKEN' && Tools::strlen(Tools::getValue($key)) > 10) {
                        $this->errors[] = $this->l('Maximum cronjob secure token length is 10 characters', 'AdminEtsACOptionsController');
                    } else if ($key == 'ETS_ABANCART_DISCOUNT_CODE' && ($code = Tools::getValue($key)) && trim(Tools::getValue('ETS_ABANCART_DISCOUNT_OPTION')) === 'fixed' && (!Validate::isCleanHtml($code) || !CartRule::cartRuleExists($code))) {
                        $this->errors[] = $config['label'] . ' ' . $this->l('is invalid', 'AdminEtsACOptionsController');
                    } elseif (!is_array(Tools::getValue($key)) && !Validate::isCleanHtml(trim(Tools::getValue($key)))) {
                        $this->errors[] = $config['label'] . ' ' . $this->l('is invalid', 'AdminEtsACOptionsController');
                    }
                }
                if ($discountOption == 'auto') {
                    if ($key == 'ETS_ABANCART_QUANTITY' || $key == 'ETS_ABANCART_QUANTITY_PER_USER') {
                        $qty = Tools::getValue($key);
                        if (!Tools::strlen($qty)) {
                            $this->errors[] = $config['label'] . ' ' . $this->l('is required', 'AdminEtsACOptionsController');
                        } elseif (!Validate::isUnsignedInt($qty) || (int)$qty < 1) {
                            $this->errors[] = $config['label'] . ' ' . $this->l('is invalid', 'AdminEtsACOptionsController');
                        }
                    }
                    $discountApply = ($discountApply = Tools::getValue('ETS_ABANCART_APPLY_DISCOUNT')) && Validate::isCleanHtml($discountApply) ? $discountApply : '';
                    $discountApplyTo = ($discountApplyTo = Tools::getValue('ETS_ABANCART_APPLY_DISCOUNT_TO')) && Validate::isCleanHtml($discountApplyTo) ? $discountApplyTo : '';
                    if ($key == 'ETS_ABANCART_REDUCTION_PRODUCT') {
                        $reductionProduct = Tools::getValue('ETS_ABANCART_REDUCTION_PRODUCT');
                        if ($discountApplyTo == 'specific') {
                            if (!Tools::strlen($reductionProduct)) {
                                $this->errors[] = $config['label'] . ' ' . $this->l('is required', 'AdminEtsACOptionsController');
                            } elseif (!Validate::isUnsignedInt($reductionProduct) || !($p = new Product($reductionProduct)) || !$p->id) {
                                $this->errors[] = $config['label'] . ' ' . $this->l('is invalid', 'AdminEtsACOptionsController');
                            }
                        }
                    } elseif ($key == 'ETS_ABANCART_SELECTED_PRODUCT') {
                        $selectedProducts = Tools::getValue('ETS_ABANCART_SELECTED_PRODUCT');
                        if ($selectedProducts && is_array($selectedProducts)) {
                            $selectedProducts = array_map('intval', $selectedProducts);
                        }
                        if ($discountApplyTo == 'selection' && $discountApply == 'percent') {
                            if (!$selectedProducts) {
                                $this->errors[] = $config['label'] . ' ' . $this->l('is required', 'AdminEtsACOptionsController');
                            } elseif ($selectedProducts && count($selectedProducts) == 1 && $selectedProducts[0] == 0) {
                                $this->errors[] = $config['label'] . ' ' . $this->l('is invalid', 'AdminEtsACOptionsController');
                            }
                        }
                    } elseif ($key == 'ETS_ABANCART_PRODUCT_GIFT') {
                        $enableGift = (int)Tools::getValue('ETS_ABANCART_SEND_A_GIFT');
                        $giftP = (int)Tools::getValue('ETS_ABANCART_GIFT_PRODUCT');

                        if ($enableGift) {
                            if (!$giftP) {
                                $this->errors[] = $config['label'] . ' ' . $this->l('is required', 'AdminEtsACOptionsController');
                            } elseif (!($p = new Product($giftP)) || !$p->id) {
                                $this->errors[] = $config['label'] . ' ' . $this->l('is invalid', 'AdminEtsACOptionsController');
                            }
                        }
                    }
                }

            }

            if (($shopping_cart = (int)Tools::getValue('ETS_ABANCART_SAVE_SHOPPING_CART')) && (int)Tools::getValue('ETS_ABANCART_MINUTES') > 60) {
                $this->errors[] = $this->l('Minute(s) is range from 0 to 60 or empty.', 'AdminEtsACOptionsController');
            }
            if ($shopping_cart && (int)Tools::getValue('ETS_ABANCART_SECONDS') > 60) {
                $this->errors[] = $this->l('Second(s) is range from 0 to 60 or empty.', 'AdminEtsACOptionsController');
            }
        }
        if (!$this->errors) {

            if ($configs) {
                foreach ($configs as $key => $config) {
                    $global = !empty($config['global']) ? 1 : 0;
                    if ($key != 'ETS_ABANCART_MAIL_SERVICE' && $api !== 'default' && $mail_options) {
                        if (!empty($mail_options['api']) && empty($config['api'])
                            || empty($mail_options['api']) && !empty($config['api'])
                            || !empty($config['object']) && !in_array($api, explode(',', $config['object']))) {
                            continue;
                        }
                        if (isset($config['form_group_class']) && !in_array('trans', explode(',', $config['form_group_class']))) {
                            $key .= '_' . Tools::strtoupper($api);
                        }
                    } elseif ($api === 'default' && isset($config['form_group_class']) && !in_array('trans', explode(',', $config['form_group_class']))) {
                        if ($key == 'ETS_ABANCART_MAIL_SERVICE') {
                            $this->setFields($key, $api, $global, true);
                        }
                        continue;
                    }
                    // Configs.
                    if (isset($config['lang']) && $config['lang']) {
                        $values = array();
                        foreach ($languages as $lang) {
                            if ($config['type'] == 'switch')
                                $values[$lang['id_lang']] = (int)trim(Tools::getValue($key . '_' . $lang['id_lang'])) ? 1 : 0;
                            else
                                $values[$lang['id_lang']] = trim(Tools::getValue($key . '_' . $lang['id_lang'])) ? trim(Tools::getValue($key . '_' . $lang['id_lang'])) : trim(Tools::getValue($key . '_' . $id_lang_default));
                        }
                        $this->setFields($key, $values, $global, true);
                    } else {
                        if ($config['type'] == 'switch') {
                            $this->setFields($key, (int)trim(Tools::getValue($key)) ? 1 : 0, $global, true);
                        } elseif ($config['type'] == 'select' && isset($config['multiple']) && $config['multiple']) {
                            $this->setFields($key, implode(',', Tools::getValue($key, array())), $global, true);
                        } elseif ($key == 'ETS_ABANCART_SELECTED_PRODUCT') {
                            $this->setFields($key, implode(',', Tools::getValue($key, array())), $global, true);
                        } else
                            $this->setFields($key, trim(Tools::getValue($key)), $global, true);
                        if ($key == 'ETS_ABANCART_PRODUCT_GIFT') {
                            $this->setFields('ETS_ABANCART_GIFT_PRODUCT', (int)Tools::getValue('ETS_ABANCART_GIFT_PRODUCT'), $global, true);
                            $this->setFields('ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE', (int)Tools::getValue('ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE'), $global, true);
                        }
                    }
                }
                if (Tools::getValue('controller') == 'AdminEtsACReminderLeave') {
                    $this->setFields('ETS_ABANCART_LEAVE_TIME_UPDATE', time(), 0, false);
                }
            }
        }

        if (empty($this->errors)) {
            $this->confirmations[] = $this->_conf[6];
        }
    }

    protected function requiredFields($key)
    {
        $discount_option = trim(Tools::getValue('ETS_ABANCART_DISCOUNT_OPTION'));
        $apply_discount = trim(Tools::getValue('ETS_ABANCART_APPLY_DISCOUNT'));

        switch (trim($key)) {
            case 'ETS_ABANCART_DISCOUNT_CODE':
                return ($discount_option === 'fixed' && !trim(Tools::getValue($key)));
            case 'ETS_ABANCART_REDUCTION_PERCENT':
                return ($discount_option === 'auto' && $apply_discount === 'percent' && !trim(Tools::getValue($key)));
            case 'ETS_ABANCART_APPLY_DISCOUNT_IN':
                return ($discount_option === 'auto' && !trim(Tools::getValue($key)));
            case 'ETS_ABANCART_REDUCTION_AMOUNT':
                return ($discount_option === 'auto' && $apply_discount === 'amount' && !trim(Tools::getValue($key)));
            default:
                return (trim(Tools::getValue($key, '')) == '');
        }

        return false;
    }

    protected function validateFields($key, $config)
    {
        $result = Tools::getValue($key) != '';
        $discount_option = trim(Tools::getValue('ETS_ABANCART_DISCOUNT_OPTION'));
        $apply_discount = trim(Tools::getValue('ETS_ABANCART_APPLY_DISCOUNT'));

        switch ($key) {
            // Configs.
            case 'ETS_ABANCART_REDUCTION_AMOUNT':
                $result = ($apply_discount === 'amount' && $discount_option === 'auto');
                break;
            case 'ETS_ABANCART_REDUCTION_PERCENT':
                $result = ($apply_discount === 'percent' && $discount_option === 'auto');
                break;
            case 'ETS_ABANCART_APPLY_DISCOUNT_IN':
                $result = ($discount_option === 'auto');
                break;
            case 'ETS_ABANCART_HOURS':
            case 'ETS_ABANCART_MINUTES':
            case 'ETS_ABANCART_SECONDS':
                $result = (int)Tools::getValue('ETS_ABANCART_SAVE_SHOPPING_CART') && Tools::getValue($key) != '';
                break;
        }
        return $result && method_exists('Validate', $config['validate']);
    }
}