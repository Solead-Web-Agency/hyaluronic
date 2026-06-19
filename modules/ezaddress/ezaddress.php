<?php
/**
 * Copyright EZMods
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites ( or projects ), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author EZ Mods
 * @copyright  EZ Mods
 * @license    Valid for 1 website ( or project ) for each purchase of license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class EZAddress extends Module
{
    public function __construct()
    {
        $this->name = 'ezaddress';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->module_key = '9807feee3378ef0e05f55daca8697460';
        $this->author = 'EZ Mods';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('EZ Address');
        $this->description = $this->l('Simplifies address form by hiding unnecessary fields and autofilling address fields based on autocomplete result.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install()
        && $this->registerHook('actionFrontControllerSetMedia')
        && $this->registerHook('additionalCustomerAddressFields')
        && $this->registerHook('displayHeader')
        && Configuration::updateValue('ezaddress_autohide', 1)
        && $this->messagesTrans();
    }

    public function uninstall()
    {
        return
            parent::uninstall()
            && Configuration::deleteByName('ezaddress_api')
            && Configuration::deleteByName('ezaddress_google_key')
            && Configuration::deleteByName('ezaddress_geoapify_key')
            && Configuration::deleteByName('ezaddress_autohide')
            && Configuration::deleteByName('ezaddress_autolang')
            && $this->removeMessagesTrans()
        ;
    }

    public function messagesTrans()
    {
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $iso = $lang['iso_code'];
            $id = $lang['id_lang'];
            if ($iso == 'cs') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Hledat adresu...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Omlouváme se, ale do zvolené země zatím nezasíláme.');
            } elseif ($iso == 'de') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Adresse suchen...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Leider versenden wir noch nicht in das ausgewählte Land.');
            } elseif ($iso == 'es') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Buscar una dirección...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Lamentablemente, aún no enviamos al país seleccionado.');
            } elseif ($iso == 'et') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Otsi aadressi...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Kahjuks me ei tarni hetkel valitud riiki.');
            } elseif ($iso == 'fr') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Rechercher une adresse...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Malheureusement, nous ne expédions pas encore vers le pays sélectionné.');
            } elseif ($iso == 'it') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Cerca un indirizzo...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Purtroppo non spediamo ancora nel paese selezionato.');
            } elseif ($iso == 'nl') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Zoek een adres...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Helaas verzenden we nog niet naar het geselecteerde land.');
            } elseif ($iso == 'pl') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Szukaj adresu...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Niestety nie wysyłamy jeszcze do wybranego kraju.');
            } elseif ($iso == 'pt') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Procurar um endereço...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Infelizmente, ainda não enviamos para o país selecionado.');
            } elseif ($iso == 'ru') {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Искать адрес...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'К сожалению, мы пока не доставляем в выбранную страну.');
            } else {
                Configuration::updateValue('ezaddress_placeholder_' . (int) $id, 'Search for an address...');
                Configuration::updateValue('ezaddress_google_error_' . (int) $id, 'Unfortunately we do not ship to selected country yet.');
            }
        }

        return true;
    }

    public function removeMessagesTrans()
    {
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $iso = $lang['iso_code'];
            $id = $lang['id_lang'];
            Configuration::deleteByName('ezaddress_placeholder_' . (int) $id);
            Configuration::deleteByName('ezaddress_google_error_' . (int) $id);
        }

        return true;
    }

    /**
     * This method handles the module's configuration page
     *
     * @return string The page's HTML content
     */
    public function getContent()
    {
        $output = '';
        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->name)) {
            // retrieve the value set by the user
            $ezaddress_api = (string) Tools::getValue('ezaddress_api');
            $ezaddress_google_key = (string) Tools::getValue('ezaddress_google_key');
            $ezaddress_geoapify_key = (string) Tools::getValue('ezaddress_geoapify_key');
            $ezaddress_autohide = (string) Tools::getValue('ezaddress_autohide');
            $ezaddress_autolang = (string) Tools::getValue('ezaddress_autolang');
            // Check if api key is provided for api selection
            if ($ezaddress_api == 'google' && empty($ezaddress_google_key) || $ezaddress_api == 'geoapify' && empty($ezaddress_geoapify_key)) {
                $output = $this->displayError($this->l('API key missing for:') . ' ' . $ezaddress_api);
            } else {
                Configuration::updateValue('ezaddress_api', $ezaddress_api);
                Configuration::updateValue('ezaddress_google_key', $ezaddress_google_key);
                Configuration::updateValue('ezaddress_geoapify_key', $ezaddress_geoapify_key);
                Configuration::updateValue('ezaddress_autohide', $ezaddress_autohide);
                Configuration::updateValue('ezaddress_autolang', $ezaddress_autolang);
                $languages = Language::getLanguages(false);
                foreach ($languages as $lang) {
                    Configuration::updateValue('ezaddress_placeholder_' . (int) $lang['id_lang'], (string) Tools::getValue('ezaddress_placeholder_' . (int) $lang['id_lang'], false));
                    Configuration::updateValue('ezaddress_google_error_' . (int) $lang['id_lang'], (string) Tools::getValue('ezaddress_google_error_' . (int) $lang['id_lang'], false));
                }
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        // display any message, then the form
        return $output . $this->displayForm();
    }

    /**
     * Builds the configuration form
     *
     * @return string HTML code
     */
    public function displayForm()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        // Setup form
        $setup_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Setup'),
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Autocomplete API'),
                        'name' => 'ezaddress_api',
                        'required' => true,
                        'desc' => $this->l('Select API service to use for address autocomplete, In-ADS works only for Estonia, no API key needed.'),
                        'options' => [
                            'query' => [
                                [
                                    'value' => 'google',
                                    'label' => 'Google',
                                ],
                                [
                                    'value' => 'geoapify',
                                    'label' => 'Geoapify',
                                ],
                                [
                                    'value' => 'inads',
                                    'label' => 'In-ADS(Estonia only)',
                                ],
                            ],
                            'id' => 'value',
                            'name' => 'label',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Google API key'),
                        'name' => 'ezaddress_google_key',
                        'size' => 20,
                        'required' => false,
                        'desc' => '<a href="' . __PS_BASE_URI__ . 'modules/' . $this->name . '/docs/readme_en.pdf" target="_blank">' . $this->l('See documentation on how to obtain API key.') . '</a>',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Geoapify API key'),
                        'name' => 'ezaddress_geoapify_key',
                        'size' => 20,
                        'required' => false,
                        'desc' => '<a href="' . __PS_BASE_URI__ . 'modules/' . $this->name . '/docs/readme_en.pdf" target="_blank">' . $this->l('See documentation on how to obtain API key.') . '</a>',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];
        // Options form
        $options_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Options'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Autocomplete input placeholder'),
                        'name' => 'ezaddress_placeholder',
                        'desc' => $this->l('Placeholder text for autocomplete input.'),
                        'lang' => true,
                        'size' => 20,
                        'required' => false,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Auto hide fields'),
                        'name' => 'ezaddress_autohide',
                        'default_value' => 1,
                        'desc' => $this->l('Simplifies address form by hiding unnessecary fields.'),
                        'values' => [
                            [
                                'id' => 'autohide_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'autohide_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Address in site language'),
                        'name' => 'ezaddress_autolang',
                        'default_value' => 0,
                        'desc' => $this->l('Shows addresses in selected site language, autofill for state input might not work correctly with geoapify.'),
                        'values' => [
                            [
                                'id' => 'autolang_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'autolang_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];
        // Google form
        $google_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Google Autocomplete settings'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Google error message'),
                        'name' => 'ezaddress_google_error',
                        'lang' => true,
                        'size' => 20,
                        'required' => false,
                        'desc' => $this->l('Error message to return when you do not ship to the country that user has selected.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];
        $helper = new HelperForm();
        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        foreach (Language::getLanguages(false) as $lang) {
            $helper->languages[] = [
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0),
            ];
        }
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;
        // Default language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        // Load current values into the form
        $helper->fields_value['ezaddress_api'] = Tools::getValue('ezaddress_api', Configuration::get('ezaddress_api'));
        $helper->fields_value['ezaddress_google_key'] = Tools::getValue('ezaddress_google_key', Configuration::get('ezaddress_google_key'));
        $helper->fields_value['ezaddress_geoapify_key'] = Tools::getValue('ezaddress_geoapify_key', Configuration::get('ezaddress_geoapify_key'));
        $helper->fields_value['ezaddress_autohide'] = Tools::getValue('ezaddress_autohide', Configuration::get('ezaddress_autohide'));
        $helper->fields_value['ezaddress_autolang'] = Tools::getValue('ezaddress_autolang', Configuration::get('ezaddress_autolang'));
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $helper->fields_value['ezaddress_placeholder'][$lang['id_lang']] = Tools::getValue('ezaddress_placeholder_' . (int) $lang['id_lang'], Configuration::get('ezaddress_placeholder_' . (int) $lang['id_lang']));
            $helper->fields_value['ezaddress_google_error'][$lang['id_lang']] = Tools::getValue('ezaddress_google_error_' . (int) $lang['id_lang'], Configuration::get('ezaddress_google_error_' . (int) $lang['id_lang']));
        }

        return $helper->generateForm([$setup_form, $options_form, $google_form]);
    }

    public function hookDisplayHeader()
    {
        $ezaddress_api = Tools::getValue('ezaddress_api', Configuration::get('ezaddress_api'));
        $ezaddress_autohide = Tools::getValue('ezaddress_autohide', Configuration::get('ezaddress_autohide'));
        $ezaddress_autolang = Tools::getValue('ezaddress_autolang', Configuration::get('ezaddress_autolang'));
        $api_key = '';
        if ($ezaddress_api == 'google') {
            $api_key = Tools::getValue('ezaddress_google_key', Configuration::get('ezaddress_google_key'));
        } elseif ($ezaddress_api == 'geoapify') {
            $api_key = Tools::getValue('ezaddress_geoapify_key', Configuration::get('ezaddress_geoapify_key'));
        }
        // return enabled countries
        $current_lang = $this->context->cookie->id_lang;
        $current_lang_iso = Language::getIsoById($current_lang);
        $enabled_countries = Country::getCountries($current_lang, true);
        $i = 0;
        foreach ($enabled_countries as $country) {
            $enabled_countries_array[$i]['id_country'] = $country['id_country'];
            $enabled_countries_array[$i]['iso_code'] = $country['iso_code'];
            if (!empty($country['states'])) {
                foreach ($country['states'] as $id => $state) {
                    $enabled_countries_array[$i]['states'][$id]['id_state'] = $state['id_state'];
                    $enabled_countries_array[$i]['states'][$id]['iso_code'] = $state['iso_code'];
                }
            }
            ++$i;
        }
        $enabled_countries_json = json_encode($enabled_countries_array);
        if ($ezaddress_api) {
            Media::addJsDef([
                'ezaddress_api' => $ezaddress_api,
                'ezaddress_api_key' => $api_key,
                'ezaddress_autohide' => $ezaddress_autohide,
                'ezaddress_autolang' => $ezaddress_autolang,
                'ezaddress_countries' => $enabled_countries_json,
                'ezaddress_lang' => $current_lang_iso,
                'ezaddress_google_error' => Tools::getValue('ezaddress_google_error_' . $current_lang, Configuration::get('ezaddress_google_error_' . $current_lang)),
            ]);
        }
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'ezaddress-style',
            'modules/' . $this->name . '/views/css/ezaddress.css',
            [
                'media' => 'all',
                'priority' => 1000,
            ]
        );
        $this->context->controller->registerJavascript(
            'ezaddress-javascript',
            'modules/' . $this->name . '/views/js/ezaddress.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );
    }

    public function hookAdditionalCustomerAddressFields($params)
    {
        $lang = $this->context->cookie->id_lang;
        $format = $params['fields'];
        $format['address1']->addAvailableValue(
            'placeholder',
            Tools::getValue('ezaddress_placeholder_' . $lang, Configuration::get('ezaddress_placeholder_' . $lang)),
        );
    }
}
