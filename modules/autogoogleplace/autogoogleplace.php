<?php

/**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2022 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@malttt.com
 * Phone : +33.972535133
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AutoGooglePlace extends Module
{
    public function __construct()
    {
        $this->name = 'autogoogleplace';
        $this->tab = 'front_office_features';
        $this->bootstrap = true;
        $this->version = '1.8.06';
        $this->author = 'Agence Malttt';

        $this->lang = true;
        $this->need_instance = 0;
        $this->ps_version_compliancy['min'] = '1.5.2.0';
        $this->ps_version_compliancy['max'] = _PS_VERSION_;
        $this->module_key = 'a10a69130ef93bcf7d1be6f3e11a4a42';
        $this->author_address = '0xc1dcC59643a63D65e05F1F9d8e985dbb9CDe344f';
        $this->displayName = $this->l('Google Address Suggest');
        $this->description = $this->l('Give users an automatic suggestion and completion when they type their address !');

        // Module Configuration Menu
        $this->conf_tabs = array(
            'settings' => $this->l('Main Settings'),
            'adv' => $this->l('Advanced'),
        );

        $context = Context::getContext();
        $id_lang = isset($context->cookie->id_lang) ? (int) $context->cookie->id_lang : (int) Configuration::get('PS_LANG_DEFAULT');
        $countries = Country::getCountries($id_lang, true, false, false);
        $metas = Meta::getMetasByIdLang($id_lang);

        // Backward compatibility
        $links = Configuration::get('AUTOGOOGLEPLACE_ENABLED_LINKS');

        if (strpos($links, ',') !== false) {
            $links = str_replace(array(' ', ','), array('', '|'), $links);
            Configuration::updateValue('AUTOGOOGLEPLACE_ENABLED_LINKS', $links);
        }

        $disabled_countries = Configuration::get('AUTOGOOGLEPLACE_DISABLED_COUNTRIES');

        if (strpos($disabled_countries, ',') !== false) {
            $disabled_countries = str_replace(array(' ', ','), array('', '|'), $disabled_countries);
            Configuration::updateValue('AUTOGOOGLEPLACE_DISABLED_COUNTRIES', $disabled_countries);
        }

        $this->old_version = version_compare(_PS_VERSION_, "1.6.1.0", "<");

        if ($this->old_version) {

            $enabled_links = array(
                'name' => 'AUTOGOOGLEPLACE_ENABLED_LINKS',
                'type' => 'text',
                'tab' => 'settings',
                'label' => $this->l('Which page uses autocomplete ?'),
                'desc' => $this->l('Please add entries with | separation and no spaces, for example : "order|address|order-opc".') . ' ' . $this->l('Please note that you must register a module page in Preference > SEO & URLs if you want to use it here'),
            );
        } else {

            $enabled_links = array(
                'name' => 'AUTOGOOGLEPLACE_ENABLED_LINKS',
                'type' => 'select',
                'class' => 'chosen',
                'multiple' => true,
                'tab' => 'settings',
                'label' => $this->l('Which page uses autocomplete ?'),
                'desc' => $this->l('Please note that you must register a module page in Preference > SEO & URLs if you want to use it here'),
                'options' => array(
                    'query' => $metas,
                    'id' => 'id_meta',
                    'name' => 'page',
                )
            );
        }


        $this->fields_form = array(
            array(
                'name' => 'AUTOGOOGLEPLACE_KEY',
                'type' => 'text',
                'tab' => 'settings',
                'label' => $this->l('Google API Key'),
                'default' => '',
            ),
            $enabled_links,
            array(
                'name' => 'AUTOGOOGLEPLACE_DISABLED_COUNTRIES',
                'type' => 'select',
                'class' => 'chosen',
                'multiple' => true,
                'tab' => 'settings',
                'label' => $this->l('Limit address suggestions by countries (up to five) ?'),
                'desc' => $this->l('Do not add more than five or it won\'t work.'),
                'default' => '',
                'options' => array(
                    'query' => $countries,
                    'id' => 'iso_code',
                    'name' => 'name',
                )
            ),
            array(
                'name' => 'AUTOGOOGLEPLACE_LONG_ADDRESS',
                'type' => 'radio',
                'label' => $this->l('Use long address format'),
                'desc' => $this->l('Use "Avenue ABC" instead of "Av. ABC"'),
                'default' => '0',
                'tab' => 'settings',
                'values' => array(
                    array(
                        'id' => 'type_la_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_la_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'AUTOGOOGLEPLACE_ACTIVE_ADDRESS2',
                'type' => 'radio',
                'label' => $this->l('Complete also Address 2 line'),
                'desc' => $this->l(''),
                'default' => '0',
                'tab' => 'settings',
                'values' => array(
                    array(
                        'id' => 'type_aa_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_aa_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'AUTOGOOGLEPLACE_CUSTOM_CHECKOUT',
                'type' => 'radio',
                'label' => $this->l('Do you use a custom checkout module ?'),
                'default' => '0',
                'tab' => 'settings',
                'values' => array(
                    array(
                        'id' => 'type_cc_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_cc_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'AUTOGOOGLEPLACE_DISABLE_BACKOFFICE',
                'type' => 'radio',
                'label' => $this->l('Disable autocompletion in Back Office'),
                'default' => '0',
                'tab' => 'adv',
                'values' => array(
                    array(
                        'id' => 'type_db_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_db_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'AUTOGOOGLEPLACE_DISABLE_BROWSER_AUTOCOMPLETE',
                'type' => 'radio',
                'label' => $this->l('Disable browser autocomplete (Google Chrome)'),
                'default' => '0',
                'tab' => 'adv',
                'values' => array(
                    array(
                        'id' => 'type_dba_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_dba_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'AUTOGOOGLEPLACE_LOAD_MAPS',
                'type' => 'radio',
                'label' => $this->l('Force Google Maps HTML loading'),
                'desc' => $this->l('If nothing appears in address field.'),
                'default' => '0',
                'tab' => 'adv',
                'values' => array(
                    array(
                        'id' => 'type_lm_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_lm_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'AUTOGOOGLEPLACE_FORCE_15',
                'type' => 'radio',
                'label' => $this->l('Force 1.5 Theme mode (not recommanded) ?'),
                'desc' => $this->l('Can be usefull if your key is entered but it still does not work on old themes configuration'),
                'default' => '0',
                'tab' => 'adv',
                'values' => array(
                    array(
                        'id' => 'type_afo_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_afo_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
        );

        $this->hooks = array('displayHeader', 'displayBackOfficeTop');

        parent::__construct();
    }

    public function install()
    {
        if ($this->fields_form && count($this->fields_form) > 0) {
            foreach ($this->fields_form as $value) {
                if (isset($value['default']) && !empty($value['default'])) {
                    if (!Configuration::updateValue($value['name'], $value['default'])) {
                        return false;
                    }
                }
            }
        }

        if (!parent::install()) {
            return false;
        } else {
            if (isset($this->hooks) && !empty($this->hooks)) {
                foreach ($this->hooks as $v) {
                    if (!$this->registerHook($v)) {
                        return false;
                    }
                }
            }

            // Default configuration
            $default_pages = array('authentication', 'address', 'order', 'order-opc', 'identity');
            $ids = array();

            if (isset($this->context->cookie->id_lang)) {
                $metas = Meta::getMetasByIdLang((int) $this->context->cookie->id_lang);

                if (!empty($metas)) {
                    foreach ($metas as $meta) {
                        if (in_array($meta['page'], $default_pages)) {
                            $ids[] = $meta['id_meta'];
                        }
                    }
                }

                if (!empty($ids)) {
                    $implode = implode('|', $ids);
                    Configuration::updateValue('AUTOGOOGLEPLACE_ENABLED_LINKS', $implode);
                }
            }
        }

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        $render = $this->getPreform() . $this->renderForm();
        return $render;
    }

    public function getPreform()
    {
        $err_conf = $this->postProcess();

        $this->loadAutoCompletion();

        $context = Context::getContext();

        if (isset($context->shop) && Validate::isLoadedObject($context->shop)) {
            $shop_url = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) ? $context->shop->domain_ssl : $context->shop->domain;
        } else {
            $shop_url = 'www.domain.tld';
        }

        $secure_shop_url = $shop_url . '/*';

        $exp = explode('.', $shop_url);

        unset($exp[0]);

        $secure_domain_url = implode('.', $exp) . '/*';

        $this->context->smarty->assign(
            array(
                'key' => trim(Configuration::get('AUTOGOOGLEPLACE_KEY')),
                'secure_shop_url' => $secure_shop_url,
                'secure_domain_url' => $secure_domain_url,
                'err_conf' => $err_conf
            )
        );

        return $this->display(__FILE__, 'preform.tpl');
    }

    public function renderForm()
    {
        if ($this->fields_form == null) {
            return false;
        }

        $fields = $this->fields_form;

        $fields_form = array(
            'tinymce' => true,
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration for') . ' ' . $this->displayName,
                    'icon' => 'icon-cogs',
                ),
                'input' => $fields,
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        if (isset($this->conf_tabs)) {
            $fields_form['form']['tabs'] = $this->conf_tabs;
        }

        $this->context->controller->addjQueryPlugin('tagify', null, false);

        $helper = new HelperForm();
        $helper->show_toolbar = (isset($this->show_toolbar) ? $this->show_toolbar : false);

        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper->submit_action = 'submit' . Tools::ucfirst($this->name);
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldValues()
    {
        if ($this->fields_form == null) {
            return false;
        }

        $array = $this->fields_form;
        $languages = Language::getLanguages(false);

        $field_values = array();

        foreach ($array as $value) {
            if (isset($value['lang']) && $value['lang'] == true) {
                foreach ($languages as $lang) {
                    if (!isset($value['multiple']) || $value['multiple'] == false) {
                        $field_values[$value['name']][$lang['id_lang']] = Configuration::get($value['name'], $lang['id_lang']);
                    } else {
                        $v = Configuration::get($value['name'], $lang['id_lang']);

                        if (!empty($v)) {
                            $v = explode('|', $v);
                        } else {
                            $v = array();
                        }

                        $field_values[$value['name'] . '[]'][$lang['id_lang']] = $v;
                    }
                }
            } else {
                if (isset($value['multiple']) && $value['multiple'] == true) {
                    $v = Configuration::get($value['name']);

                    if (!empty($v)) {
                        $v = explode('|', $v);
                    } else {
                        $v = array();
                    }

                    $field_values[$value['name'] . '[]'] = $v;
                } else {
                    $field_values[$value['name']] = Configuration::get($value['name']);
                }
            }
        }

        return $field_values;
    }

    /**
     * Check new configuration values.
     *
     * @return bool
     */
    public function postProcess()
    {
        if (!isset($this->fields_form)) {
            return parent::postProcess();
        }

        $values = $this->fields_form;

        if (!($languages = Language::getLanguages(true))) {
            return false;
        }

        $err = array();

        if (Tools::isSubmit('submit' . Tools::ucfirst($this->name))) {
            foreach ($values as $value) {
                if (isset($value['lang']) && $value['lang'] == true) {
                    switch ($value['type']) {
                        case 'text':
                        case 'password':
                        case 'textarea':
                        case 'select':

                            $text = array();

                            foreach ($languages as $lang) {
                                $v = Tools::getValue($value['name'] . '_' . $lang['id_lang']);

                                if (isset($value['multiple']) && $value['multiple'] == true && !empty($v) && is_array($v)) {
                                    $v = implode('|', $v);
                                }

                                $text[$lang['id_lang']] = $v;
                            }

                            if (isset($value['validate']) && !empty($value['validate'])) {
                                foreach ($text as $k => $v) {
                                    if (empty($text[$k])) {
                                        continue;
                                    }

                                    if ($value['validate'] == 'isInt') {
                                        if (!Validate::isInt($text[$k])) {
                                            $err[] = $this->l('One of your numeric field is invalid.') . ' ("' . $text[$k] . '")';
                                            continue;
                                        } else {
                                            $text[$k] = (int) $text[$k];
                                        }
                                    } elseif ($value['validate'] == 'isFloat') {
                                        if (!Validate::isFloat($text[$k])) {
                                            $err[] = $this->l('One of your numeric field is invalid.') . ' ("' . $text[$k] . '")';
                                            continue;
                                        } else {
                                            $text[$k] = (float) $text[$k];
                                        }
                                    } elseif ($value['validate'] == 'isEmail') {
                                        if (!Validate::isEmail($text[$k])) {
                                            $err[] = $this->l('One of your email field is invalid.') . ' ("' . $text[$k] . '")';
                                            continue;
                                        }
                                    } elseif ($value['validate'] == 'isAddress') {
                                        if (!Validate::isAddress($text[$k])) {
                                            $err[] = $this->l('One of your address field is invalid.') . ' ("' . $text[$k] . '")';
                                            continue;
                                        }
                                    } elseif ($value['validate'] == 'isPostCode') {
                                        if (!Validate::isPostCode($text[$k])) {
                                            $err[] = $this->l('One of your post code field is invalid.') . ' ("' . $text[$k] . '")';
                                            continue;
                                        }
                                    } elseif ($value['validate'] == 'isPhoneNumber') {
                                        if (!Validate::isPhoneNumber($text[$k])) {
                                            $err[] = $this->l('One of your phone number field is invalid.') . ' ("' . $text[$k] . '")';
                                            continue;
                                        }
                                    } elseif ($value['validate'] == 'isHour') {
                                        if (strpos($text[$k], ':') == false) {
                                            $err[] = $this->l('One of your hour field is invalid.') . ' ("' . $text[$k] . '")';
                                            continue;
                                        }
                                    } elseif ($value['type'] == 'password' && empty($text[$k])) {
                                        // Nothing to do
                                        continue;
                                    }
                                }
                            }

                            Configuration::updateValue($value['name'], $text, (isset($value['html']) ? $value['html'] : false));

                            break;

                        default:

                            Configuration::updateValue($value['name'], Tools::getValue($value['name'] . '_' . $lang['id_lang']), (isset($value['html']) ? $value['html'] : false));

                            break;
                    }
                } else {
                    switch ($value['type']) {
                        case 'text':
                        case 'password':
                        case 'textarea':
                        case 'select':

                            $v = Tools::getValue($value['name']);

                            if (isset($value['validate']) && !empty($value['validate']) && !empty($v)) {
                                if ($value['validate'] == 'isInt') {
                                    if (!Validate::isInt($v)) {
                                        $err[] = $this->l('One of your numeric field is invalid.') . ' ("' . $v . '")';
                                        break;
                                    } else {
                                        $v = (int) $v;
                                    }
                                } elseif ($value['validate'] == 'isFloat') {
                                    if (!Validate::isFloat($v)) {
                                        $err[] = $this->l('One of your numeric field is invalid.') . ' ("' . $v . '")';
                                        break;
                                    } else {
                                        $v = (float) $v;
                                    }
                                } elseif ($value['validate'] == 'isEmail') {
                                    if (!Validate::isEmail($v)) {
                                        $err[] = $this->l('One of your email field is invalid.') . ' ("' . $v . '")';
                                        break;
                                    }
                                } elseif ($value['validate'] == 'isAddress') {
                                    if (!Validate::isAddress($v)) {
                                        $err[] = $this->l('One of your address field is invalid.') . ' ("' . $v . '")';
                                        break;
                                    }
                                } elseif ($value['validate'] == 'isPostCode') {
                                    if (!Validate::isPostCode($v)) {
                                        $err[] = $this->l('One of your post code field is invalid.') . ' ("' . $v . '")';
                                        break;
                                    }
                                } elseif ($value['validate'] == 'isPhoneNumber') {
                                    if (!Validate::isPhoneNumber($v)) {
                                        $err[] = $this->l('One of your phone number field is invalid.') . ' ("' . $v . '")';
                                        break;
                                    }
                                } elseif ($value['validate'] == 'isHour') {
                                    if (strpos($v, ':') == false) {
                                        $err[] = $this->l('One of your hour field is invalid.') . ' ("' . $v . '")';
                                        break;
                                    }
                                }
                            }

                            if ($value['type'] == 'password' && empty($v)) {
                                // Nothing to do
                                break;
                            }

                            if (isset($value['multiple']) && $value['multiple'] == true && !empty($v) && is_array($v)) {
                                $v = implode('|', $v);
                            }

                            Configuration::updateValue($value['name'], $v, (isset($value['html']) ? $value['html'] : false));

                            break;

                        default:

                            Configuration::updateValue($value['name'], Tools::getValue($value['name']), (isset($value['html']) ? $value['html'] : false));

                            break;
                    }
                }
            }

            if (!empty($err)) {
                return $err;
            } else {

                // Display update confirmation, if they are here it's ok, they passed all errors
                Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&conf=6');
            }
        }
    }

    public function getPageName()
    {
        $context = Context::getContext();
        $smarty = $context->smarty;

        if (!empty($smarty->tpl_vars['page_name']->value)) {
            $page_name = $smarty->tpl_vars['page_name']->value;
        } elseif (!empty($this->page_name)) {
            $page_name = $this->page_name;
        } elseif (!empty($this->php_self)) {
            $page_name = $this->php_self;
        } elseif (preg_match('#^' . preg_quote($context->shop->physical_uri, '#') . 'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m)) {
            $page_name = 'module-' . $m[1] . '-' . str_replace(array('.php', '/'), array('', '-'), $m[2]);
        } else {
            $page_name = Dispatcher::getInstance()->getController();
            $page_name = (preg_match('/^[0-9]/', $page_name) ? 'page_' . $page_name : $page_name);
        }

        return $page_name;
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeTop()
    {
        $disable_backoffice = (bool) Configuration::get('AUTOGOOGLEPLACE_DISABLE_BACKOFFICE');

        if ($disable_backoffice == true) {
            return;
        }

        $controller = Tools::getValue('controller');

        if (!empty($controller)) {
            $controller = Tools::strtolower($controller);
        }

        if (in_array($controller, array('adminaddresses'))) {
            $this->loadAutoCompletion();
        }
    }

    public function hookDisplayHeader($params)
    {
        $metas = Configuration::get('AUTOGOOGLEPLACE_ENABLED_LINKS');

        if (!empty($metas)) {
            $page_name = $this->getPageName();

            if ((bool) Configuration::get('AUTOGOOGLEPLACE_CUSTOM_CHECKOUT') == false) {
                if ($page_name == 'order' && isset($this->context->cart->id_address_delivery) > 0 && (int) $this->context->cart->id_address_delivery > 0 && Tools::getValue('newAddress') != 'delivery' && Tools::getValue('editAddress') != 'delivery') {
                    // Avoid loading on shipping order page.
                    return;
                }
            }

            $metas = str_replace('|', ',', $metas);

            if ($this->old_version) {

                $result = false;

                if (!empty($metas)) {

                    $entries = explode(',', $metas);

                    if (!empty($entries)) {

                        foreach ($entries as $entry) {
                            $entry = trim($entry);
                            if ($entry == $page_name) {
                                $result = true;
                                break;
                            }
                        }
                    }
                }


                if ($result) {
                    $this->loadAutoCompletion();
                }
            } else {

                $req = 'SELECT page FROM `' . _DB_PREFIX_ . 'meta` WHERE `id_meta` IN ( ' . pSQL($metas) . ') AND page="' . pSQL($page_name) . '"';

                $sql = Db::getInstance()->getRow($req);


                if (!empty($sql)) {
                    $this->loadAutoCompletion();
                }
            }
        }
    }

    private function loadAutoCompletion()
    {
        $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/' . $this->name . '.js');

        $countries_iso = array();
        $states_iso = array();

        $countries = Country::getCountries($this->context->cookie->id_lang, true, false, false);

        if (!empty($countries)) {
            foreach ($countries as $country) {
                $countries_iso[htmlspecialchars($country['iso_code'])] = (int) $country['id_country'];
            }
        }

        $states = State::getStates($this->context->cookie->id_lang, true);

        if (!empty($states)) {
            foreach ($states as $state) {
                $states_iso[htmlspecialchars($state['iso_code'])] = (int) $state['id_state'];
            }
        }

        $admin_logged = false;

        $cookie = new Cookie('psAdmin', '', (int) Configuration::get('PS_COOKIE_LIFETIME_BO'));

        if (isset($cookie->id_employee) && !empty($cookie->id_employee)) {
            $employee = new Employee((int) $cookie->id_employee);

            if (
                Validate::isLoadedObject($employee)
                &&  $employee->checkPassword((int)$cookie->id_employee, $cookie->passwd)
                && (!isset($cookie->remote_addr) || $cookie->remote_addr == ip2long(Tools::getRemoteAddr()) || !Configuration::get('PS_COOKIE_CHECKIP'))
            ) {
                $admin_logged = true;
            }
        }

        $excluded_countries_array = Configuration::get('AUTOGOOGLEPLACE_DISABLED_COUNTRIES');

        if (!empty($excluded_countries_array) && !empty($countries)) {
            $excluded_countries = explode('|', $excluded_countries_array);

            foreach ($excluded_countries as $key => $value) {
                $excluded_countries[$key] = trim($value);
            }
        } else {
            $excluded_countries = array();
        }

        $apikey = trim(Configuration::get('AUTOGOOGLEPLACE_KEY'));

        $load_mapsapi = (bool) Configuration::get('AUTOGOOGLEPLACE_LOAD_MAPS');

        $js_defs = array(
            'mapsapikey' => $apikey,
            'autogoogleplace_excluded_countries' => $excluded_countries,
            'autogoogleplace_countries' => $countries_iso,
            'autogoogleplace_states' => $states_iso,
            'autogoogleplace_admin_logged' => (int) $admin_logged,
            'autogoogleplace_long_address' => (int) Configuration::get('AUTOGOOGLEPLACE_LONG_ADDRESS'),
            'autogoogleplace_active_address2' => (int) Configuration::get('AUTOGOOGLEPLACE_ACTIVE_ADDRESS2'),
            'autogoogleplace_custom_checkout' => (int) Configuration::get('AUTOGOOGLEPLACE_CUSTOM_CHECKOUT'),
            'autogoogleplace_disable_browser_autocomplete' => (int) Configuration::get('AUTOGOOGLEPLACE_DISABLE_BROWSER_AUTOCOMPLETE')
        );

        if ((((bool) Configuration::get('AUTOGOOGLEPLACE_FORCE_15') == true || !class_exists('Media')) || version_compare(_PS_VERSION_, "1.6.0.2", "<")) && !in_array(Tools::getValue('ajax'), array('true', true, 1, "1"))) {

            echo "<script type='text/javascript'>";
            foreach ($js_defs as $key => $value) {
                echo 'var ' . $key . ' = "' . $value . '";';
            }
            echo "</script>";

            if ($load_mapsapi === true) {
                echo '<script defer async type="text/javascript" src="//maps.googleapis.com/maps/api/js?libraries=places&amp;key=' . $apikey . '"></script>';
            }
        } else {

            Media::addJsDef(
                $js_defs
            );

            if ($load_mapsapi === true) {
                $maps_url = '//maps.googleapis.com/maps/api/js?libraries=places&amp;key=' . $apikey;

                if (version_compare(_PS_VERSION_, '1.7', ">=") && method_exists($this->context->controller, 'registerJavascript')) {
                    $this->context->controller->registerJavascript('modules-autogoogleplace', $maps_url, array('position' => 'bottom', 'priority' => 160));
                } else {
                    $this->context->controller->addJS($maps_url);
                }
            }
        }
    }
}
