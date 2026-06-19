<?php
/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require dirname(__FILE__).'/vendor/autoload.php';

class RG_LocationDetection extends Module
{
    public $tags = array(
        '(COUNTRY_NAME)',
        '(COUNTRY_FLAG_URL)',
        '(REDIRECTION_URL)',
    );

    protected $display = 'globalForm';
    protected $current_id = null;

    public function __construct()
    {
        $this->name = 'rg_locationdetection';
        $this->tab = 'i18n_localization';
        $this->version = '2.6.0';
        $this->author = 'Rolige';
        $this->author_link = 'https://www.rolige.com/';
        $this->addons_author_link = 'https://addons.prestashop.com/en/2_community-developer?contributor=99052';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->secure_key = Tools::hash('sk'.$this->name);
        $this->public_key = Tools::hash('pk'.$this->name);

        parent::__construct();

        $this->displayName = $this->l('Advanced Location Detection');
        $this->description = $this->l('Detect the country and language of your visitors and configure redirects, default customer language, currency and carrier, besides other actions.');
    }

    public function __get($name)
    {
        if ($name == '_cookie') {
            return $this->_cookie = RgLdCookie::get();
        }
    }

    public function install()
    {
        include $this->local_path.'sql/install.php';

        return parent::install() &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayTop') &&
            $this->registerHook('actionCarrierUpdate') &&
            $this->registerHook('actionDispatcher') &&
            $this->insDefaultIp() &&
            $this->insDefaultCrawler() &&
            $this->delCacheIndex();
    }

    public function uninstall()
    {
        include $this->local_path.'sql/uninstall.php';

        $config_values = $this->getConfigGlobalFormValues();

        foreach (array_keys($config_values) as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        if (Tools::isSubmit('addRedirection') || Tools::isSubmit('updateRedirection')) {
            $this->display = 'redirectionForm';
        } elseif (Tools::isSubmit('addInfobar') || Tools::isSubmit('updateInfobar')) {
            $this->display = 'infobarForm';
        } elseif (Tools::isSubmit('addCountry') || Tools::isSubmit('updateCountry')) {
            $this->display = 'countryForm';
        }

        $output = '';
        $prefix = version_compare(_PS_VERSION_, '1.6.0.0', '<') ? '' : 'RG';

        /*
         * Check actions in the API form.
         */
        if (Tools::isSubmit('submitDownloadDB') || Tools::isSubmit('update_db')) {
            if (!$error = $this->postValidateApiForm()) {
                $val = $this->getConfigApiFormValues();

                foreach ($val as $k => $v) {
                    Configuration::updateValue($k, $v);
                }

                if (!$this->downloadDB()) {
                    $error = $this->l('Database could not be downloaded.').' '.$this->l('Please, check your API token and try again.');
                } else {
                    $output .= $this->displayConfirmation($this->l('Database downloaded successfully.'));
                }
            }
        /*
         * Check actions in the global form.
         */
        } elseif (Tools::isSubmit('submitGlobalForm')) {
            if (!$error = $this->postValidateGlobalForm()) {
                $val = $this->getConfigGlobalFormValues();

                foreach ($val as $k => $v) {
                    if (in_array($k, array('RGLD_EXCLUDE_IP', 'RGLD_EXCLUDE_AGENTS'))) {
                        Configuration::updateValue($k, str_replace("\n", ';', str_replace("\r", '', $v)));
                    } else {
                        Configuration::updateValue($k, $v);
                    }
                }

                $output .= $this->displayConfirmation($this->l('Configuration updated successfully.'));
            }
        /*
         * Check actions for redirections form.
         */
        } elseif (Tools::isSubmit('submitRedirectionForm')) {
            if (!$error = $this->postValidateRedirectionForm()) {
                $this->postProcessRedirectionForm();

                if ($this->current_id) {
                    $output .= $this->displayConfirmation($this->l('The redirection has been successfully updated.'));
                } else {
                    $output .= $this->displayConfirmation($this->l('The redirection has been successfully added.'));
                }
            }
        } elseif (Tools::isSubmit('statusRedirection')) {
            $redirection = new RgLdRedirection((int)Tools::getValue('id_redirection'));

            if (!$redirection->id || !$redirection->toggleStatus()) {
                $error = $this->l('An error occurred updating the redirection.');
            } else {
                $output .= $this->displayConfirmation($this->l('The redirection status has been successfully updated.'));
            }
        } elseif (Tools::isSubmit('deleteRedirection')) {
            $redirection = new RgLdRedirection((int)Tools::getValue('id_redirection'));

            if (!$redirection->id || !$redirection->delete()) {
                $error = $this->l('An error occurred deleting the redirection.');
            } else {
                $output .= $this->displayConfirmation($this->l('The redirection has been successfully deleted.'));
            }
        } elseif (Tools::isSubmit('submitBulkenableRedirection')) {
            RgLdRedirection::bulkEnable(Tools::getValue($prefix.'RedirectionBox'));
        } elseif (Tools::isSubmit('submitBulkdisableRedirection')) {
            RgLdRedirection::bulkDisable(Tools::getValue($prefix.'RedirectionBox'));
        } elseif (Tools::isSubmit('submitBulkdeleteRedirection')) {
            RgLdRedirection::bulkDelete(Tools::getValue($prefix.'RedirectionBox'));
        /*
         * Check actions for infobars.
         */
        } elseif (Tools::isSubmit('submitInfobarForm')) {
            if (!$error = $this->postValidateInfobarForm()) {
                $this->postProcessInfobarForm();

                if ($this->current_id) {
                    $output .= $this->displayConfirmation($this->l('The infobar has been successfully updated.'));
                } else {
                    $output .= $this->displayConfirmation($this->l('The infobar has been successfully added.'));
                }
            }
        } elseif (Tools::isSubmit('statusInfobar')) {
            $infobar = new RgLdInfobar((int)Tools::getValue('id_infobar'));

            if (!$infobar->id || !$infobar->toggleStatus()) {
                $error = $this->l('An error occurred updating the infobar.');
            } else {
                $output .= $this->displayConfirmation($this->l('The infobar status has been successfully updated.'));
            }
        } elseif (Tools::isSubmit('deleteInfobar')) {
            $infobar = new RgLdInfobar((int)Tools::getValue('id_infobar'));

            if (!$infobar->id || !$infobar->delete()) {
                $error = $this->l('An error occurred deleting the infobar.');
            } else {
                $output .= $this->displayConfirmation($this->l('The infobar has been successfully deleted.'));
            }
        } elseif (Tools::isSubmit('submitBulkenableInfobar')) {
            RgLdInfobar::bulkEnable(Tools::getValue($prefix.'InfobarBox'));
        } elseif (Tools::isSubmit('submitBulkdisableInfobar')) {
            RgLdInfobar::bulkDisable(Tools::getValue($prefix.'InfobarBox'));
        } elseif (Tools::isSubmit('submitBulkdeleteInfobar')) {
            RgLdInfobar::bulkDelete(Tools::getValue($prefix.'InfobarBox'));
        /*
         * Check actions for countries.
         */
        } elseif (Tools::isSubmit('submitCountryForm') && Tools::isSubmit('country_iso_code')) {
            if (!$error = $this->postValidateCountryForm()) {
                $this->postProcessCountryForm();

                if ($this->current_id) {
                    $output .= $this->displayConfirmation($this->l('The country has been successfully updated.'));
                } else {
                    $output .= $this->displayConfirmation($this->l('The country has been successfully added.'));
                }
            }
        } elseif (Tools::isSubmit('statusCountry')) {
            $country = new RgLdCountry((int)Tools::getValue('id_country'));

            if (!$country->id || !$country->toggleStatus()) {
                $error = $this->l('An error occurred updating the country.');
            } else {
                $output .= $this->displayConfirmation($this->l('The country status has been successfully updated.'));
            }
        } elseif (Tools::isSubmit('deleteCountry')) {
            $country = new RgLdCountry((int)Tools::getValue('id_country'));

            if (!$country->id || !$country->delete()) {
                $error = $this->l('An error occurred deleting the country.');
            } else {
                $output .= $this->displayConfirmation($this->l('The country has been successfully deleted.'));
            }
        } elseif (Tools::isSubmit('submitBulkenableCountry')) {
            RgLdCountry::bulkEnable(Tools::getValue($prefix.'CountryBox'));
        } elseif (Tools::isSubmit('submitBulkdisableCountry')) {
            RgLdCountry::bulkDisable(Tools::getValue($prefix.'CountryBox'));
        } elseif (Tools::isSubmit('submitBulkdeleteCountry')) {
            RgLdCountry::bulkDelete(Tools::getValue($prefix.'CountryBox'));
        }

        if (isset($error) && $error) {
            $output .= $this->displayError($error);
        }

        $this->context->smarty->assign($this->name, array(
            '_path' => $this->_path,
            'displayName' => $this->displayName,
            'description' => $this->description,
            'version' => $this->version,
            'author' => $this->author,
            'author_link' => isset($this->module_key) && $this->module_key ? $this->addons_author_link : $this->author_link,
        ));

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure-top.tpl');
        $output .= $this->{$this->display}();

        if ('globalForm' == $this->display) {
            if (Configuration::get('RGLD_TOKEN') && $this->getDatabases()) {
                if (Configuration::get('PS_DETECT_LANG')) {
                    $this->adminDisplayWarning($this->l('If you have problems with the language detection, we suggest you deactivate the "Set language from browser" option in "International > Localization".'));
                }

                if (Configuration::get('PS_DETECT_COUNTRY')) {
                    $this->adminDisplayWarning($this->l('If you have problems with the country detection, we suggest you deactivate the "Set default country from browser language" option in "International > Localization".'));
                }

                $output .= $this->redirectionList();
                $output .= $this->infobarList();
                $output .= $this->countryList();
            }
        }

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure-bottom.tpl');

        return $output;
    }

    public function hookActionDispatcher()
    {
        if (!isset($this->context->controller->controller_type) ||
            $this->context->controller->controller_type !== 'front'
        ) {
            return false;
        }

        if ($this->_cookie->detect_location == 'init' || $this->_cookie->detect_location) {
            $this->_cookie->detect_location = false;

            if (!$ip_address = $this->getIpAddress()) {
                return;
            }

            $location = $this->getIpDetails($ip_address);

            if (isset($location['countryCode']) &&
                $location['countryCode'] &&
                ('unavailable-field' != $location['countryCode'])
            ) {
                $country = RgLdCountry::getCountryByIsoCode(Tools::strtoupper($location['countryCode']), $this->context->language->id);

                if (!$country) {
                    return;
                }

                if (!$this->_cookie->lang_currency_switched_once) {
                    $this->_cookie->lang_currency_switched_once = true;
                    $switch_lang = false;
                    $id_lang = (int)$this->getNewIdLang($country);

                    if ($id_lang != $this->context->language->id) {
                        $country = RgLdCountry::getCountryByIsoCode(Tools::strtoupper($location['countryCode']), $id_lang);
                        $switch_lang = true;
                    }

                    $id_currency = (int)$this->getNewIdCurrency($country);

                    if ($id_currency != $this->context->cookie->id_currency) {
                        $this->context->cookie->id_currency = $id_currency;
                    }

                    $id_country = (int)Country::getByIso(Tools::strtoupper($location['countryCode']));

                    if ($id_country && $this->context->country->id != $id_country) {
                        $this->context->country = new Country($id_country, (int)$id_lang);
                        $this->context->cookie->iso_code_country = Tools::strtoupper($country->iso_code);
                    }

                    if ($switch_lang) {
                        $this->_cookie->detect_location = true;
                        Tools::redirect($this->context->link->getLanguageLink($id_lang));
                    }
                }

                $flag_url = false;

                if (@is_file($this->local_path.'views/img/flags/'.Tools::strtolower($location['countryCode']).'.png')) {
                    $flag_url = __PS_BASE_URI__.'modules/'.$this->name.'/views/img/flags/'.Tools::strtolower($location['countryCode']).'.png';
                }

                $ps_country = RgLdTools::getCountryByIso(Tools::strtoupper($location['countryCode']));
                $this->_cookie->ip_address = $ip_address;
                $this->_cookie->ps_id_country = (int)$ps_country['id_country'];
                $this->_cookie->ps_country_contains_states = (int)$ps_country['contains_states'];
                $this->_cookie->ps_country_active = (int)$ps_country['active'];
                $this->_cookie->id_country = (int)$country['id_country'];
                $this->_cookie->country_iso_code = Tools::strtoupper($location['countryCode']);
                $this->_cookie->country_name = $country['name'];
                $this->_cookie->id_state = $ps_country['contains_states']
                    ? (int)RgLdTools::searchState($location['regionName'], (int)$ps_country['id_country'])
                    : 0;
                $this->_cookie->state_name = $location['regionName'] && 'unavailable-field' != $location['regionName']
                    ? $location['regionName']
                    : false;
                $this->_cookie->city_name = $location['cityName'] && 'unavailable-field' != $location['cityName']
                    ? $location['cityName']
                    : false;
                $this->_cookie->id_lang = $this->context->language->id;
                $this->_cookie->id_currency = $this->context->cookie->id_currency;
                $this->_cookie->id_carrier = (int)$country['id_carrier'];
                $this->_cookie->flag_url = $flag_url;

                if (Configuration::get('RGLD_REDIRECTIONS') &&
                    ($id_redirection = RgLdRedirection::getIdRedirectionByCountryCode(Tools::strtoupper($location['countryCode'])))
                ) {
                    ++$this->_cookie->redirection_count;
                    $this->_cookie->redirection_id = (int)$id_redirection;
                    $this->initCookieRedirection((int)$id_redirection);

                    if ($this->_cookie->redirection_qty &&
                        ($this->_cookie->redirection_qty <= $this->_cookie->redirection_count)
                    ) {
                        $this->_cookie->detect_location = false;
                    } else {
                        $this->_cookie->detect_location = true;
                    }

                    if (!$this->_cookie->redirection_show_popup) {
                        Tools::redirect($this->_cookie->redirection_url);
                    }
                }

                if (Configuration::get('RGLD_INFOBARS') &&
                    ($id_infobar = RgLdInfobar::getIdInfobarByCountryCode(Tools::strtoupper($location['countryCode'])))
                ) {
                    $this->_cookie->infobar_id = (int)$id_infobar;
                    $this->_cookie->infobar_show = true;
                } else {
                    $this->_cookie->infobar_show = false;
                }
            }
        }

        if ($this->_cookie->id_lang &&
            ($this->_cookie->id_lang != $this->context->language->id)
        ) {
            $this->_cookie->id_lang = (int)$this->context->language->id;

            if ($this->_cookie->ld_id_country) {
                $country = new RgLdCountry($this->_cookie->ld_id_country);
                $this->_cookie->country_name = $country->name[(int)$this->context->language->id];
            }

            if (Configuration::get('RGLD_REDIRECTIONS') && $this->_cookie->redirection_popup) {
                $this->initCookieRedirection($this->_cookie->redirection_id);
            }
        }
    }

    public function hookActionCarrierUpdate($params)
    {
        $old_id_carrier = $params['id_carrier'];
        $new_id_carrier = $params['carrier']->id;

        return Db::getInstance()->update(RgLdCountry::$definition['table'], array('id_carrier' => (int)$new_id_carrier), 'id_carrier = '.(int)$old_id_carrier);
    }

    public function hookDisplayTop()
    {
        if ($this->_cookie->infobar_show) {
            $infobar = new RgLdInfobar((int)$this->_cookie->infobar_id, (int)$this->context->language->id);

            $vars = array();
            $vars['infobar_width'] = $infobar->width;
            $vars['infobar_height'] = $infobar->height;
            $vars['infobar_background'] = $infobar->background;
            $vars['infobar_border_size'] = $infobar->border_size;
            $vars['infobar_border_color'] = $infobar->border_color;
            $vars['infobar_close_button_color'] = $infobar->close_button_color;
            $vars['infobar_position'] = $infobar->position;
            $vars['infobar_static'] = $infobar->static;
            $vars['infobar_custom_css'] = $infobar->custom_css;

            $replace = array(
                $this->_cookie->country_name,
                $this->_cookie->flag_url,
                '',
            );
            $vars['infobar_content'] = RgLdTools::nl2br(RgLdTools::replaceTags(
                $this->tags,
                $replace,
                $infobar->content
            ));

            $this->context->smarty->assign(array(
                'rgld' => $vars,
                'rgld_css' => RgLdTools::generateCssInline('infobar', $vars),
            ));

            return $this->display(__FILE__, 'display_top.tpl');
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if ('AdminModules' == $this->context->controller->controller_name &&
            Tools::getValue('configure') == $this->name
        ) {
            if (method_exists($this->context->controller, 'addJquery')) {
                $this->context->controller->addJquery();
            }

            $this->context->controller->addCSS($this->_path.'views/css/back.css');
            $this->context->controller->addJS($this->_path.'views/js/back.js');
        }
    }

    public function hookDisplayHeader()
    {
        $page_name = false;

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') === true) {
            $page_name = $this->context->controller->getPageName();
        } elseif (method_exists($this->context->smarty, 'getTemplateVars')) {
            $page_name = $this->context->smarty->getTemplateVars('page_name');
        }

        $this->context->smarty->assign(array(
            'page_name' => $page_name,
            'rgld_public_key' => $this->public_key,
            'rgld_path' => $this->_path,
        ));

        if ($this->_cookie->redirection_show_popup) {
            if (!$this->_cookie->redirection_qty ||
                ($this->_cookie->redirection_qty && $this->_cookie->redirection_popup_count <= $this->_cookie->redirection_qty)
            ) {
                $redirection = new RgLdRedirection((int)$this->_cookie->redirection_id);

                if (Validate::isLoadedObject($redirection)) {
                    $replace = array(
                        $this->_cookie->country_name,
                        $this->_cookie->flag_url,
                        $this->_cookie->redirection_url,
                    );
                    $popup_content = RgLdTools::replaceTags(
                        $this->tags,
                        $replace,
                        RgLdTools::nl2br($redirection->popup[(int)$this->context->language->id])
                    );

                    if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
                        $this->context->smarty->assign('rgld_popup', $popup_content);
                    } else {
                        Media::addJsDef(array('rgld_popup' => $popup_content));
                    }

                    $this->context->controller->addCSS($this->_path.'views/libs/rgldbox/jquery.rgldbox.min.css');
                    $this->context->controller->addJS($this->_path.'views/libs/rgldbox/jquery.rgldbox.min.js');
                }
            }
        }

        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            if ($this->_cookie->infobar_show) {
                $infobar = new RgLdInfobar((int)$this->_cookie->infobar_id);

                $this->context->smarty->assign(array(
                    'rgld_infobar_position' => $infobar->position,
                ));
            }
        } else {
            Media::addJsDef(array(
                'ps16' => true,
                'page_name' => $page_name,
                'rgld_public_key' => $this->public_key,
                'rgld_path' => $this->_path,
            ));

            if ($this->_cookie->infobar_show) {
                $infobar = new RgLdInfobar((int)$this->_cookie->infobar_id);

                Media::addJsDef(array(
                    'rgld_infobar_position' => $infobar->position,
                ));
            }
        }

        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJquery();
        }

        $this->context->controller->addCSS($this->_path.'views/css/front.css');
        $this->context->controller->addJS($this->_path.'views/js/front.js');

        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            return $this->display(__FILE__, 'ps15Header.tpl');
        }
    }

    public function getIpDetails($ip_address)
    {
        $class_file = $this->local_path.'libraries/IP2Location.php';
        $database_file = $this->local_path.'libraries/databases/'.Configuration::get('RGLD_IP_DATABASE');

        if (!@file_exists($class_file) || !@is_file($database_file)) {
            return false;
        }

        require_once $class_file;

        if (!class_exists('IP2Location\Database')) {
            return false;
        }

        $db = new \IP2Location\Database($database_file, \IP2Location\Database::FILE_IO);

        return $db->lookup($ip_address, \IP2Location\Database::ALL);
    }

    /**
     * Form that will be displayed in top of the configuration.
     */
    protected function globalForm()
    {
        $databases = $this->getDatabases();

        if (Configuration::get('RGLD_TOKEN') && count($databases)) {
            $fields_form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Global Settings'),
                        'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'type' => 'textarea',
                            'label' => $this->l('Exclude list, IP addresses'),
                            'name' => 'RGLD_EXCLUDE_IP',
                            'rows' => 7,
                            'form_group_class' => 'no-autosize',
                            'desc' => $this->l('You can add many IP addresses as you need (one per line), the detection will be excluded for these IP addresses, this is needed for a good SEO.'),
                        ),
                        array(
                            'type' => 'textarea',
                            'label' => $this->l('Exclude list, User agents'),
                            'name' => 'RGLD_EXCLUDE_AGENTS',
                            'rows' => 7,
                            'form_group_class' => 'no-autosize',
                            'desc' => $this->l('You can add many User Agents as you need, you can use the wildcard "%" at start and end of the word to exclude all agents that match with this word (one per line) (eg. %google%, bingbot), the detection will be excluded for these user agents, this is suggested for a good SEO.'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('CloudFlare'),
                            'name' => 'RGLD_CLOUDFLARE',
                            'is_bool' => true,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled'),
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled'),
                                ),
                            ),
                            'desc' => $this->l('Turn on this option if you are using CloudFlare on this shop.'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Detect currency'),
                            'name' => 'RGLD_DETECT_CURRENCY',
                            'is_bool' => true,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled'),
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled'),
                                ),
                            ),
                            'desc' => $this->l('Set the currency based on the country of the customer, the country should be defined previously in the Countries list.'),
                        ),
                        array(
                            'type' => 'radio',
                            'label' => $this->l('Detect language'),
                            'name' => 'RGLD_DETECT_LANG',
                            'values' => $this->_detectLangList(),
                            'desc' => $this->l('The country should be defined previously in the Countries list (Only for the 2nd and 3rd option)'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Set the carrier'),
                            'name' => 'RGLD_DETECT_CARRIER',
                            'is_bool' => true,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled'),
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled'),
                                ),
                            ),
                            'desc' => $this->l('Set the carrier based on the country, the country with the carrier should be defined previously in the Countries list.'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Enable redirections'),
                            'name' => 'RGLD_REDIRECTIONS',
                            'is_bool' => true,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled'),
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled'),
                                ),
                            ),
                            'desc' => $this->l('When this option is deactivated, no redirection of the "Redirections List" will be executed.'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Enable infobars'),
                            'name' => 'RGLD_INFOBARS',
                            'is_bool' => true,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled'),
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled'),
                                ),
                            ),
                            'desc' => $this->l('When this option is deactivated, no infobar of the "Infobars List" will be displayed.'),
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('Lifetime of the cookie'),
                            'name' => 'RGLD_COOKIE',
                            'required' => true,
                            'class' => 'input fixed-width-md',
                            'size' => 10,
                            'suffix' => $this->l('Hrs'),
                            'desc' => $this->l('Enter the hours of life that will have the cookie, put to "0" to expire until the browser is closed.'),
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('API token'),
                            'name' => 'RGLD_TOKEN',
                            'required' => true,
                            'class' => 'input',
                            'desc' => $this->l('Enter the API token to download the default IP database.'),
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->l('IP Database'),
                            'name' => 'RGLD_IP_DATABASE',
                            'required' => true,
                            'class' => 'fixed-width-xxl',
                            'options' => array(
                                'query' => $databases,
                                'id' => 'file',
                                'name' => 'file',
                            ),
                            'desc' => $this->l('Select the database you want to use for detection.').'<br><strong>'.$this->l('NOTE:').'</strong> '.sprintf($this->l('This module use a free database from ip2location.com, if you need a better accuracy in detection, then you could think in buy the full database %s.'), '<a href="http://www.ip2location.com/databases/db3-ip-country-region-city" target="_blank" title="http://www.ip2location.com/databases/db3-ip-country-region-city">'.$this->l('here').'</a>').'<br>'.$this->l('The database should be uploaded to').': /modules/'.$this->name.'/libraries/databases/',
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                    'buttons' => array(
                        'update_db' => array(
                            'name' => 'update_db',
                            'type' => 'submit',
                            'title' => $this->l('Update Default Database'),
                            'class' => 'btn btn-default',
                            'icon' => 'process-icon-refresh',
                        ),
                    ),
                ),
            );
        } else {
            $fields_form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Global Settings'),
                        'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'type' => 'text',
                            'label' => $this->l('API token'),
                            'name' => 'RGLD_TOKEN',
                            'required' => true,
                            'class' => 'input',
                            'desc' => $this->l('Enter the API token to download the default IP database.').' '.$this->l('You can get it from your account at:').' https://lite.ip2location.com/login',
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                ),
            );
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = (Configuration::get('RGLD_TOKEN') && count($databases) ? 'submitGlobalForm' : 'submitDownloadDB');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigGlobalFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        if (in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))) {
            $ip_address = '127.0.0.1';
        } else {
            if (Configuration::get('RGLD_CLOUDFLARE')) {
                $_SERVER['REMOTE_ADDR'] = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
            }

            $ip_address = Tools::getRemoteAddr();
        }

        $this->backwardForm($fields_form, $helper);

        if (Configuration::get('RGLD_TOKEN') && count($databases)) {
            $this->context->smarty->assign(array(
                'dev_url' => $this->context->link->getPageLink('index', null, null, 'secure_key='.$this->secure_key.'&dev_mode=true&ip_address='.$ip_address),
                'cron_url' => Tools::getShopDomainSsl(true).$this->_path.'cron_update_db.php?secure_key='.$this->secure_key
                    .'&id_shop='.(int)Configuration::get('PS_SHOP_DEFAULT'),
            ));
        }

        return $helper->generateForm(array($fields_form));
    }

    /**
     * Values for the inputs in the global form.
     */
    protected function getConfigGlobalFormValues()
    {
        return array(
            ($name = 'RGLD_EXCLUDE_IP') => Tools::getValue($name, str_replace(';', "\n", Configuration::get($name))),
            ($name = 'RGLD_EXCLUDE_AGENTS') => Tools::getValue($name, str_replace(';', "\n", Configuration::get($name))),
            ($name = 'RGLD_CLOUDFLARE') => (int)(bool)Tools::getValue($name, Configuration::get($name)),
            ($name = 'RGLD_DETECT_CURRENCY') => (int)(bool)Tools::getValue($name, Configuration::get($name)),
            ($name = 'RGLD_DETECT_LANG') => (int)Tools::getValue($name, Configuration::get($name)),
            ($name = 'RGLD_DETECT_CARRIER') => (int)(bool)Tools::getValue($name, Configuration::get($name)),
            ($name = 'RGLD_REDIRECTIONS') => (int)(bool)Tools::getValue($name, Configuration::get($name)),
            ($name = 'RGLD_INFOBARS') => (int)(bool)Tools::getValue($name, Configuration::get($name)),
            ($name = 'RGLD_COOKIE') => abs((int)Tools::getValue($name, Configuration::get($name))),
            ($name = 'RGLD_TOKEN') => trim(Tools::getValue($name, Configuration::get($name))),
            ($name = 'RGLD_IP_DATABASE') => trim(Tools::getValue($name, Configuration::get($name))),
        );
    }

    /**
     * Validate global form data.
     */
    protected function postValidateGlobalForm()
    {
        $val = $this->getConfigGlobalFormValues();

        if (!Tools::isEmpty($val['RGLD_EXCLUDE_IP']) && !Validate::isCleanHtml($val['RGLD_EXCLUDE_IP'])) {
            return $this->l('Exclude list, IP addresses').': '.$this->l('Is invalid.');
        }

        if (!Tools::isEmpty($val['RGLD_EXCLUDE_AGENTS']) && !Validate::isCleanHtml($val['RGLD_EXCLUDE_AGENTS'])) {
            return $this->l('Exclude list, User agents').': '.$this->l('Is invalid.');
        }

        if (!in_array($val['RGLD_DETECT_LANG'], $this->_detectLangList(true))) {
            return $this->l('Detect language').': '.$this->l('You must select one from the list.');
        }

        if (!in_array($val['RGLD_IP_DATABASE'], $this->getDatabases(true))) {
            return $this->l('IP Database').': '.$this->l('You must select one from the list.');
        }

        return $this->postValidateApiForm();
    }

    /**
     * Values for the inputs in the API form.
     */
    protected function getConfigApiFormValues()
    {
        return array(
            ($name = 'RGLD_TOKEN') => trim(Tools::getValue($name, Configuration::get($name))),
        );
    }

    /**
     * Validate API form data.
     */
    protected function postValidateApiForm()
    {
        $val = $this->getConfigGlobalFormValues();

        if (Tools::isEmpty($val['RGLD_TOKEN'])) {
            return $this->l('API token').': '.$this->l('Cannot be empty.');
        }

        if (!Validate::isString($val['RGLD_TOKEN'])) {
            return $this->l('API token').': '.$this->l('Is invalid.');
        }

        return false;
    }

    /**
     * List that will be display all the redirections.
     */
    protected function redirectionList()
    {
        $fields_list = array(
            'id_redirection' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'r!id_redirection',
            ),
            'countries_string' => array(
                'title' => $this->l('Countries'),
                'filter_key' => 'cl!name',
            ),
            'url' => array(
                'title' => $this->l('URL'),
                'filter_key' => 'r!url',
            ),
            'quantity' => array(
                'title' => $this->l('Quantity'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'r!quantity',
            ),
            'full_path' => array(
                'title' => $this->l('Full Path'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'type' => 'select',
                'list' => array(0 => $this->l('No'), 1 => $this->l('Yes')),
                'filter_key' => 'r!full_path',
            ),
            'use_popup' => array(
                'title' => $this->l('Use Popup'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'type' => 'select',
                'list' => array(0 => $this->l('No'), 1 => $this->l('Yes')),
                'filter_key' => 'r!use_popup',
            ),
            'popup' => array(
                'title' => $this->l('Popup'),
                'filter_key' => 'rl!popup',
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'align' => 'center',
                'active' => 'status',
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'filter_key' => 'r!active',
            ),
        );

        $list = RgLdTools::getListFilters('RGRedirection', 'id_redirection', $fields_list);
        $content = RgLdRedirection::getRedirections($this->context->language->id, null, $list->where, $list->orderby, $list->orderway);

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->bulk_actions = array(
            'enable' => array('text' => $this->l('Enable selection'), 'icon' => 'icon-off text-success'),
            'disable' => array('text' => $this->l('Disable selection'), 'icon' => 'icon-off text-danger'),
            'delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?'), 'icon' => 'icon-trash'),
        );
        $helper->module = $this;
        $helper->listTotal = count($content);
        $helper->identifier = 'id_redirection';
        $helper->title = $this->l('Redirections List');
        $helper->table = 'Redirection';
        $helper->list_id = $list->id;
        $helper->orderBy = $list->orderby;
        $helper->orderWay = $list->orderway;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->toolbar_btn['new'] = array(
            'href' => $helper->currentIndex.'&addRedirection&token='.$helper->token,
            'desc' => $this->l('Add New Redirection'),
        );

        // Paginate the result.
        $content = RgLdTools::paginateList($content, $list->page, $list->pagination);

        return $helper->generateList($content, $fields_list);
    }

    /**
     * List that will be display all the infobars.
     */
    protected function infobarList()
    {
        $fields_list = array(
            'id_infobar' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'i!id_infobar',
            ),
            'countries_string' => array(
                'title' => $this->l('Countries'),
                'filter_key' => 'cl!name',
            ),
            'content' => array(
                'title' => $this->l('Content'),
                'filter_key' => 'il!content',
            ),
            'width' => array(
                'title' => $this->l('Width'),
                'class' => 'fixed-width-xs',
                'filter_key' => 'i!width',
            ),
            'height' => array(
                'title' => $this->l('Height'),
                'class' => 'fixed-width-xs',
                'filter_key' => 'i!height',
            ),
            'background' => array(
                'title' => $this->l('Background'),
                'color' => 'background',
                'filter_key' => 'i!background',
            ),
            'border_size' => array(
                'title' => $this->l('B. Size'),
                'class' => 'fixed-width-xs',
                'filter_key' => 'i!border_size',
            ),
            'border_color' => array(
                'title' => $this->l('B. Color'),
                'color' => 'border_color',
                'filter_key' => 'i!border_color',
            ),
            'close_button_color' => array(
                'title' => $this->l('Btn Color'),
                'color' => 'close_button_color',
                'filter_key' => 'i!close_button_color',
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'class' => 'fixed-width-xs',
                'filter_key' => 'i!position',
            ),
            'static' => array(
                'title' => $this->l('Static'),
                'class' => 'fixed-width-xs',
                'type' => 'select',
                'list' => array(0 => $this->l('No'), 1 => $this->l('Yes')),
                'filter_key' => 'i!static',
            ),
            'custom_css' => array(
                'title' => $this->l('Custom CSS'),
                'maxlength' => 200,
                'filter_key' => 'i!custom_css',
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'align' => 'center',
                'active' => 'status',
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'filter_key' => 'i!active',
            ),
        );

        $list = RgLdTools::getListFilters('RGInfobar', 'id_infobar', $fields_list);
        $content = RgLdInfobar::getInfobars($this->context->language->id, null, $list->where, $list->orderby, $list->orderway);

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->bulk_actions = array(
            'enable' => array('text' => $this->l('Enable selection'), 'icon' => 'icon-off text-success'),
            'disable' => array('text' => $this->l('Disable selection'), 'icon' => 'icon-off text-danger'),
            'delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?'), 'icon' => 'icon-trash'),
        );
        $helper->module = $this;
        $helper->listTotal = count($content);
        $helper->identifier = 'id_infobar';
        $helper->title = $this->l('Infobars List');
        $helper->table = 'Infobar';
        $helper->list_id = $list->id;
        $helper->orderBy = $list->orderby;
        $helper->orderWay = $list->orderway;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->toolbar_btn['new'] = array(
            'href' => $helper->currentIndex.'&addInfobar&token='.$helper->token,
            'desc' => $this->l('Add New Infobar'),
        );

        // Paginate the result.
        $content = RgLdTools::paginateList($content, $list->page, $list->pagination);

        $this->backwardList($fields_list);

        return $helper->generateList($content, $fields_list);
    }

    /**
     * List that will be display the countries.
     */
    protected function countryList()
    {
        $fields_list = array(
            'id_country' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'c!id_country',
            ),
            'country_iso_code' => array(
                'title' => $this->l('ISO Code'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'c!country_iso_code',
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'filter_key' => 'cl!name',
            ),
            'currency_iso_code' => array(
                'title' => $this->l('Currency ISO Code'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'c!currency_iso_code',
            ),
            'currency_name' => array(
                'title' => $this->l('Currency Name'),
                'filter_key' => 'c!currency_name',
            ),
            'lang_iso_code_first' => array(
                'title' => $this->l('Lang ISO Code'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'c!lang_iso_code_first',
            ),
            'lang_name_first' => array(
                'title' => $this->l('Lang Name'),
                'filter_key' => 'c!lang_name_first',
            ),
            'carrier_name' => array(
                'title' => $this->l('Carrier'),
                'filter_key' => 'carrier_name',
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'align' => 'center',
                'active' => 'status',
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'filter_key' => 'c!active',
            ),
        );

        $list = RgLdTools::getListFilters('RGCountry', 'id_country', $fields_list);
        $content = RgLdCountry::getCountries($this->context->language->id, null, $list->where, $list->orderby, $list->orderway);

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->bulk_actions = array(
            'enable' => array('text' => $this->l('Enable selection'), 'icon' => 'icon-off text-success'),
            'disable' => array('text' => $this->l('Disable selection'), 'icon' => 'icon-off text-danger'),
            'delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?'), 'icon' => 'icon-trash'),
        );
        $helper->module = $this;
        $helper->listTotal = count($content);
        $helper->identifier = 'id_country';
        $helper->title = $this->l('Countries List');
        $helper->table = 'Country';
        $helper->list_id = $list->id;
        $helper->orderBy = $list->orderby;
        $helper->orderWay = $list->orderway;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->toolbar_btn['new'] = array(
            'href' => $helper->currentIndex.'&addCountry&token='.$helper->token,
            'desc' => $this->l('Add New Country'),
        );

        // Paginate the result.
        $content = RgLdTools::paginateList($content, $list->page, $list->pagination);

        return $helper->generateList($content, $fields_list);
    }

    /**
     * Form to add/edit redirections.
     */
    protected function redirectionForm()
    {
        $id_redirection = (int)Tools::getValue('id_redirection');
        $content = new RgLdRedirection($id_redirection);

        if (Tools::isSubmit('submitRedirectionForm')) {
            $content = RgLdTools::changeItemsPost($_POST, array('popup'));
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Redirection'),
                    'icon' => 'icon-external-link',
                ),
                'input' => array(
                    array(
                        'type' => 'rg-multiple-checkbox',
                        'label' => $this->l('Countries'),
                        'name' => 'countries',
                        'required' => true,
                        'values' => RgLdRedirection::getAvailableCountries($this->context->language->id, $id_redirection),
                        'id_field' => 'id_country',
                        'name_field' => 'name',
                        'desc' => $this->l('Select the countries that will be redirected.'),
                        'hide_id_column' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('URL'),
                        'name' => 'url',
                        'size' => 70,
                        'required' => true,
                        'desc' => $this->l('Type the URL to which the visitor will be redirected, eg: http://www.example.com/'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Quantity of redirects'),
                        'name' => 'quantity',
                        'required' => true,
                        'class' => 'input fixed-width-md',
                        'size' => 10,
                        'desc' => $this->l('Enter the number of redirections that you want allow, use "0" to redirect all the time.'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use full path'),
                        'name' => 'full_path',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                        'desc' => $this->l('Redirect using the full path of the URL where it comes from.'),
                    ),
                    array(
                        'type' => 'html',
                        'name' => 'full_path_info',
                        'html_content' => $this->context->smarty->fetch($this->local_path.'views/templates/hook/_full_path_info.tpl'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show a popup'),
                        'name' => 'use_popup',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                        'desc' => $this->l('Display a popup giving the option to be redirected.'),
                    ),
                    array(
                        'type' => 'html',
                        'name' => 'popup_info',
                        'form_group_class' => 'redirection_popup',
                        'html_content' => $this->context->smarty->fetch($this->local_path.'views/templates/hook/_popup_info.tpl'),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => '',
                        'name' => 'popup',
                        'required' => true,
                        'form_group_class' => 'redirection_popup',
                        'autoload_rte' => true,
                        'lang' => true,
                        'desc' => $this->l('Content of the popup.'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                        'desc' => $this->l('Activate/deactivate this redirection.'),
                    ),
                ),
                'buttons' => array(
                    'cancelBlock' => array(
                        'title' => $this->l('Cancel'),
                        'href' => $this->context->link->getAdminLink('AdminModules', false)
                            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'icon' => 'process-icon-cancel',
                    ),
                    'submitRedirectionFormSaveAndStay' => array(
                        'type' => 'submit',
                        'title' => $this->l('Save and Stay'),
                        'name' => 'submitRedirectionFormSaveAndStay',
                        'icon' => 'process-icon-save',
                        'class' => 'pull-right',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRedirectionForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.($id_redirection ? '&id_redirection='.$id_redirection : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => (array)$content,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->backwardForm($fields_form, $helper, true);

        return $helper->generateForm(array($fields_form));
    }

    protected function postValidateRedirectionForm()
    {
        $this->display = 'redirectionForm';
        $this->current_id = (int)Tools::getValue('id_redirection') ?: null;
        $languages = $this->context->language->getLanguages(false);
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        if (!$tmp1 = Tools::getValue('countries')) {
            return $this->l('Countries').': '.$this->l('You must select at least one.');
        }

        if (count($tmp1) != count(array_intersect($tmp1, array_column(RgLdRedirection::getAvailableCountries($this->context->language->id, $this->current_id), 'id_country')))
        ) {
            return $this->l('Countries').': '.$this->l('You must select them from the list.');
        }

        if ((!$tmp2 = trim(Tools::getValue('url'))) || !Validate::isAbsoluteUrl($tmp2) || !RgLdTools::validateUrl($tmp2)) {
            return $this->l('URL').': '.$this->l('Is invalid.');
        }

        if (!Validate::isUnsignedInt(Tools::getValue('quantity'))) {
            return $this->l('Quantity of redirects').': '.$this->l('You must specify an integer value greater than or equal to "0".');
        }

        if (!Validate::isBool(Tools::getValue('full_path'))) {
            return $this->l('Use full path').': '.$this->l('Is invalid.');
        }

        if (!Validate::isBool($tmp3 = Tools::getValue('use_popup'))) {
            return $this->l('Show a popup').': '.$this->l('Is invalid.');
        }

        if (Tools::isEmpty(Tools::getValue('popup_'.$default_lang))) {
            if ($tmp3) {
                return $this->l('Show a popup').' > '.$this->l('Content').': '.$this->l('Can not be empty in the default language.');
            }
        }

        foreach ($languages as $lang) {
            if (!Validate::isCleanHtml(Tools::getValue('popup_'.$lang['id_lang']))) {
                if ($tmp3) {
                    return $this->l('Show a popup').' > '.$this->l('Content').' > '.$lang['name'].': '.$this->l('Is invalid.');
                } else {
                    $_POST['popup_'.$lang['id_lang']] = '';
                }
            }
        }

        if (!Validate::isBool(Tools::getValue('active'))) {
            return $this->l('Active').': '.$this->l('Is invalid.');
        }

        return false;
    }

    protected function postProcessRedirectionForm()
    {
        if (!Tools::isSubmit('submitRedirectionFormSaveAndStay')) {
            $this->display = 'globalForm';
        }

        $values = RgLdTools::changeItemsPost($_POST, array('popup'));
        $redirection = new RgLdRedirection($this->current_id);
        $redirection = RgLdTools::setObjectVars($redirection, $values);

        if ($this->current_id) {
            $redirection->update();
        } else {
            $redirection->add();

            if (Tools::isSubmit('submitRedirectionFormSaveAndStay')) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&updateRedirection&id_redirection='.(int)$redirection->id);
            }
        }
    }

    /**
     * Form to add/edit infobars.
     */
    protected function infobarForm()
    {
        $id_infobar = (int)Tools::getValue('id_infobar');
        $content = new RgLdInfobar($id_infobar);

        if (Tools::isSubmit('submitInfobarForm')) {
            $content = RgLdTools::changeItemsPost($_POST, array('content'));
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Infobar'),
                    'icon' => 'icon-exclamation-circle',
                ),
                'input' => array(
                    array(
                        'type' => 'rg-multiple-checkbox',
                        'label' => $this->l('Countries'),
                        'name' => 'countries',
                        'required' => true,
                        'values' => RgLdInfobar::getAvailableCountries($this->context->language->id, $id_infobar),
                        'id_field' => 'id_country',
                        'name_field' => 'name',
                        'desc' => $this->l('Select the country(s) where the infobar will be displayed.'),
                        'hide_id_column' => true,
                    ),
                    array(
                        'type' => 'html',
                        'name' => 'infobar_info',
                        'html_content' => '<strong>'.$this->l('Available tags in the infobar...').'</strong><br><code>(COUNTRY_NAME)</code> <-- '.$this->l('Country name detected.').'<br><code>(COUNTRY_FLAG_URL)</code> <-- '.$this->l('URL to the image with the flag of the country detected.').'<br><br><a href="#" class="load-demo-content">'.$this->l('Load a simple infobar demo in editor').'</a>',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Infobar content'),
                        'name' => 'content',
                        'required' => true,
                        'autoload_rte' => true,
                        'lang' => true,
                        'desc' => $this->l('Insert the content to be displayed in the infobar.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Width'),
                        'name' => 'width',
                        'class' => 'input fixed-width-md',
                        'desc' => $this->l('Insert the width of the infobar including the unit type, eg. 100%, 100px, 100em, etc. (Leave in blank to discard)'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Height'),
                        'name' => 'height',
                        'class' => 'input fixed-width-md',
                        'desc' => $this->l('Insert the height of the infobar including the unit type, eg. 100%, 100px, 100em, etc. (Leave in blank to discard)'),
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->l('Background'),
                        'name' => 'background',
                        'desc' => $this->l('Select the background color. (Leave in blank to discard)'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Border size'),
                        'name' => 'border_size',
                        'class' => 'input fixed-width-md',
                        'desc' => $this->l('Insert the border size of the infobar including the unit type, eg. 100%, 100px, 100em, etc. (Leave in blank to discard)'),
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->l('Border color'),
                        'name' => 'border_color',
                        'desc' => $this->l('Select the border color. (Leave in blank to discard)'),
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->l('Close button color'),
                        'name' => 'close_button_color',
                        'desc' => $this->l('Select the color of the close button. (Required when you use the Border size)'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Position'),
                        'name' => 'position',
                        'options' => array(
                            'query' => $this->_infoBarPositionList(),
                            'id' => 'position',
                            'name' => 'name',
                        ),
                        'desc' => $this->l('Select the position where the infobar will be displayed in the screen.'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Static position'),
                        'name' => 'static',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                        'desc' => $this->l('The infobar will be visible all the time although scroll the page.'),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Custom CSS'),
                        'name' => 'custom_css',
                        'rows' => 7,
                        'desc' => $this->l('Insert your custom CSS for this infobar. (Leave in blank to discard)'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                        'desc' => $this->l('Activate/deactivate this infobar.'),
                    ),
                ),
                'buttons' => array(
                    'cancelBlock' => array(
                        'title' => $this->l('Cancel'),
                        'href' => $this->context->link->getAdminLink('AdminModules', false)
                            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'icon' => 'process-icon-cancel',
                    ),
                    'submitInfobarFormSaveAndStay' => array(
                        'type' => 'submit',
                        'title' => $this->l('Save and Stay'),
                        'name' => 'submitInfobarFormSaveAndStay',
                        'icon' => 'process-icon-save',
                        'class' => 'pull-right',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitInfobarForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
            .($id_infobar ? '&id_infobar='.$id_infobar : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => (array)$content,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->backwardForm($fields_form, $helper, true);

        return $helper->generateForm(array($fields_form));
    }

    protected function postValidateInfobarForm()
    {
        $this->display = 'infobarForm';
        $this->current_id = (int)Tools::getValue('id_infobar') ?: null;
        $languages = $this->context->language->getLanguages(false);
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        if (!$tmp1 = Tools::getValue('countries')) {
            return $this->l('Countries').': '.$this->l('You must select at least one.');
        }

        if (count($tmp1) != count(array_intersect($tmp1, array_column(RgLdInfobar::getAvailableCountries($this->context->language->id, $this->current_id), 'id_country')))
        ) {
            return $this->l('Countries').': '.$this->l('You must select them from the list.');
        }

        if (Tools::isEmpty(Tools::getValue('content_'.$default_lang))) {
            return $this->l('Infobar content').': '.$this->l('Can not be empty in the default language.');
        }

        foreach ($languages as $lang) {
            if (!Validate::isCleanHtml(Tools::getValue('content_'.$lang['id_lang']))) {
                return $this->l('Infobar content').' > '.$lang['name'].': '.$this->l('Is invalid.');
            }
        }

        if (!Validate::isCleanHtml(Tools::getValue('width'))) {
            return $this->l('Width').': '.$this->l('Is invalid.');
        }

        if (!Validate::isCleanHtml(Tools::getValue('width'))) {
            return $this->l('Height').': '.$this->l('Is invalid.');
        }

        if (!Validate::isColor(Tools::getValue('background'))) {
            return $this->l('Background').': '.$this->l('Is invalid.');
        }

        if (!Validate::isCleanHtml(Tools::getValue('border_size'))) {
            return $this->l('Border size').': '.$this->l('Is invalid.');
        }

        if (!Validate::isColor(Tools::getValue('border_color'))) {
            return $this->l('Border color').': '.$this->l('Is invalid.');
        }

        if (!Tools::isEmpty(Tools::getValue('border_size')) && Tools::isEmpty(Tools::getValue('border_color'))) {
            return $this->l('Border color').': '.$this->l('Is required.');
        }

        if (!Validate::isColor(Tools::getValue('close_button_color'))) {
            return $this->l('Close button color').': '.$this->l('Is invalid.');
        }

        if (!in_array(Tools::getValue('position'), $this->_infoBarPositionList(true))) {
            return $this->l('Position').': '.$this->l('You must select one from the list.');
        }

        if (!Validate::isBool(Tools::getValue('static'))) {
            return $this->l('Static position').': '.$this->l('Is invalid.');
        }

        if (!Validate::isCleanHtml(Tools::getValue('custom_css'))) {
            return $this->l('Custom CSS').': '.$this->l('Is invalid.');
        }

        if (!Validate::isBool(Tools::getValue('active'))) {
            return $this->l('Active').': '.$this->l('Is invalid.');
        }

        return false;
    }

    protected function postProcessInfobarForm()
    {
        if (!Tools::isSubmit('submitInfobarFormSaveAndStay')) {
            $this->display = 'globalForm';
        }

        $values = RgLdTools::changeItemsPost($_POST, array('content'));
        $infobar = new RgLdInfobar($this->current_id);
        $infobar = RgLdTools::setObjectVars($infobar, $values);

        if ($this->current_id) {
            $infobar->update();
        } else {
            $infobar->add();

            if (Tools::isSubmit('submitInfobarFormSaveAndStay')) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&updateInfobar&id_infobar='.(int)$infobar->id);
            }
        }
    }

    /**
     * Form to add/edit countries.
     */
    protected function countryForm()
    {
        $id_country = (int)Tools::getValue('id_country');
        $content = new RgLdCountry($id_country);

        if (Tools::isSubmit('submitCountryForm')) {
            $content = RgLdTools::changeItemsPost($_POST, array('name'));
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Country'),
                    'icon' => 'icon-globe',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Country ISO Code'),
                        'name' => 'country_iso_code',
                        'required' => true,
                        'desc' => sprintf($this->l('You must enter the country ISO code in two-digit, more information at %s.'), '<a target="blank" href="http://en.wikipedia.org/wiki/ISO_3166-1#Officially_assigned_code_elements">wikipedia</a>'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Country name'),
                        'name' => 'name',
                        'lang' => true,
                        'required' => true,
                        'desc' => $this->l('Specify the name of the country.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Currency ISO Code'),
                        'name' => 'currency_iso_code',
                        'required' => true,
                        'desc' => sprintf($this->l('You must enter the currency ISO code in three-digits, more information at %s.'), '<a target="blank" href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes">wikipedia</a>'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Currency name'),
                        'name' => 'currency_name',
                        'required' => true,
                        'desc' => $this->l('Specify the name of the currency.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Language ISO Code (first option)'),
                        'name' => 'lang_iso_code_first',
                        'required' => true,
                        'desc' => sprintf($this->l('You must enter the language ISO code in two-digit, more information at %s.'), '<a target="blank" href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes">wikipedia</a>'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Language name (first option)'),
                        'name' => 'lang_name_first',
                        'required' => true,
                        'desc' => $this->l('Specify the name of the first language.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Language ISO Code (second option)'),
                        'name' => 'lang_iso_code_second',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Language name (second option)'),
                        'name' => 'lang_name_second',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Language ISO Code (third option)'),
                        'name' => 'lang_iso_code_third',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Language name (third option)'),
                        'name' => 'lang_name_third',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Carrier'),
                        'name' => 'id_carrier',
                        'class' => 'fixed-width-xxl',
                        'options' => array(
                            'query' => $this->_countryCarrierList(),
                            'id' => 'id_carrier',
                            'name' => 'name',
                        ),
                        'desc' => array(
                            $this->l('Select the carrier you want to be as default for this country.'),
                            '<strong>'.$this->l('IMPORTANT').'</strong>: '
                                .$this->l('You must ensure that the selected carrier is valid for this country.'),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                        'desc' => $this->l('If you disable this country, will be not available in the detection by IP.'),
                    ),
                ),
                'buttons' => array(
                    'cancelBlock' => array(
                        'title' => $this->l('Cancel'),
                        'href' => $this->context->link->getAdminLink('AdminModules', false)
                            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'icon' => 'process-icon-cancel',
                    ),
                    'submitCountryFormSaveAndStay' => array(
                        'type' => 'submit',
                        'title' => $this->l('Save and Stay'),
                        'name' => 'submitCountryFormSaveAndStay',
                        'icon' => 'process-icon-save',
                        'class' => 'pull-right',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCountryForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.($id_country ? '&id_country='.$id_country : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => (array)$content,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->backwardForm($fields_form, $helper, true);

        return $helper->generateForm(array($fields_form));
    }

    protected function postValidateCountryForm()
    {
        $this->display = 'countryForm';
        $this->current_id = (int)Tools::getValue('id_country') ?: null;
        $languages = $this->context->language->getLanguages(false);
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        if (!preg_match('/^[a-zA-Z]{2}$/', Tools::getValue('country_iso_code'))) {
            return $this->l('Country ISO Code').': '.$this->l('Is invalid.');
        }

        if (Tools::isEmpty(Tools::getValue('name_'.$default_lang))) {
            return $this->l('Country name').': '.$this->l('Can not be empty in the default language.');
        }

        foreach ($languages as $lang) {
            if (!Validate::isGenericName(Tools::getValue('name_'.$lang['id_lang']))) {
                return $this->l('Country name').' > '.$lang['name'].': '.$this->l('Is invalid.');
            }
        }

        if (!preg_match('/^[a-zA-Z]{3}$/', Tools::getValue('currency_iso_code'))) {
            return $this->l('Currency ISO Code').': '.$this->l('Is invalid.');
        }

        if (Tools::isEmpty($temp1 = Tools::getValue('currency_name')) || !Validate::isGenericName($temp1)) {
            return $this->l('Currency name').': '.$this->l('Is invalid.');
        }

        if (!preg_match('/^[a-zA-Z]{2}$/', Tools::getValue('lang_iso_code_first'))) {
            return $this->l('Language ISO Code (first option)').': '.$this->l('Is invalid.');
        }

        if (Tools::isEmpty($temp2 = Tools::getValue('lang_name_first')) || !Validate::isGenericName($temp2)) {
            return $this->l('Language name (first option)').': '.$this->l('Is invalid.');
        }

        if (!Tools::isEmpty($temp3 = Tools::getValue('lang_iso_code_second')) && !preg_match('/^[a-zA-Z]{2}$/', $temp3)) {
            return $this->l('Language ISO Code (second option)').': '.$this->l('Is invalid.');
        }

        if (!Tools::isEmpty($temp4 = Tools::getValue('lang_name_second')) && !Validate::isGenericName($temp4)) {
            return $this->l('Language name (second option)').': '.$this->l('Is invalid.');
        }

        if (!Tools::isEmpty($temp5 = Tools::getValue('lang_iso_code_third')) && !preg_match('/^[a-zA-Z]{2}$/', $temp5)) {
            return $this->l('Language ISO Code (third option)').': '.$this->l('Is invalid.');
        }

        if (!Tools::isEmpty($temp6 = Tools::getValue('lang_name_third')) && !Validate::isGenericName($temp6)) {
            return $this->l('Language name (third option)').': '.$this->l('Is invalid.');
        }

        if (!in_array(Tools::getValue('id_carrier'), $this->_countryCarrierList(true))) {
            return $this->l('Carrier').': '.$this->l('You must select one from the list.');
        }

        if (!Validate::isBool(Tools::getValue('active'))) {
            return $this->l('Active').': '.$this->l('Is invalid.');
        }

        return false;
    }

    protected function postProcessCountryForm()
    {
        if (!Tools::isSubmit('submitCountryFormSaveAndStay')) {
            $this->display = 'globalForm';
        }

        $values = RgLdTools::changeItemsPost($_POST, array('name'));
        $country = new RgLdCountry($this->current_id);
        $country = RgLdTools::setObjectVars($country, $values);

        if ($this->current_id) {
            $country->update();
        } else {
            $country->add();
        }
    }

    protected function getNewIdLang($country)
    {
        $id_lang = false;

        if ($detect_lang = (int)Configuration::get('RGLD_DETECT_LANG')) {
            if (1 == $detect_lang || 2 == $detect_lang) {
                if ($country['lang_iso_code_first'] &&
                    ($first = RgLdTools::getIdLangByIsoCode($country['lang_iso_code_first']))
                ) {
                    $id_lang = $first;
                } elseif ($country['lang_iso_code_second'] &&
                    ($second = RgLdTools::getIdLangByIsoCode($country['lang_iso_code_second']))
                ) {
                    $id_lang = $second;
                } elseif ($country['lang_iso_code_third'] &&
                    ($third = RgLdTools::getIdLangByIsoCode($country['lang_iso_code_third']))
                ) {
                    $id_lang = $third;
                }

                if (1 == $detect_lang && !$id_lang) {
                    $id_lang = RgLdTools::getIdLangByBrowser();
                }
            } elseif (3 == $detect_lang) {
                $id_lang = RgLdTools::getIdLangByBrowser();
            }
        }

        if (!$id_lang) {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }

        return $id_lang;
    }

    protected function getNewIdCurrency($country)
    {
        $id_currency = false;

        if ((int)Configuration::get('RGLD_DETECT_CURRENCY')) {
            if ($tmp_id_currency = Currency::getIdByIsoCode(Tools::strtoupper($country['currency_iso_code']))) {
                if ($currency = new Currency($tmp_id_currency)) {
                    if ($currency->active) {
                        $id_currency = $tmp_id_currency;
                    }
                }
            }
        }

        if (!$id_currency) {
            $id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        }

        return $id_currency;
    }

    protected function getIpAddress()
    {
        if (Configuration::get('RGLD_CLOUDFLARE')) {
            $_SERVER['REMOTE_ADDR'] = isset($_SERVER['HTTP_CF_CONNECTING_IP'])
                ? $_SERVER['HTTP_CF_CONNECTING_IP']
                : $_SERVER['REMOTE_ADDR'];
        }

        $dev_mode = false;

        if (Tools::getValue('dev_mode') &&
            ($secure_key = Tools::getValue('secure_key')) &&
            ($secure_key == $this->secure_key) &&
            ($ip_address = Tools::getValue('ip_address'))
        ) {
            $dev_mode = true;
        } else {
            $ip_address = Tools::getRemoteAddr();
        }

        if (!$ip_address ||
            (isset($_SERVER['HTTP_USER_AGENT']) && RgLdTools::isCrawlerListed($_SERVER['HTTP_USER_AGENT'])) ||
            (!$dev_mode && in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1')))
        ) {
            return false;
        }

        $exclude_ips = explode(';', Configuration::get('RGLD_EXCLUDE_IP'));

        if (is_array($exclude_ips) && end($exclude_ips)) {
            foreach ($exclude_ips as $ip) {
                if (preg_match('/^'.$ip.'.*/', $ip_address)) {
                    return false;
                }
            }
        }

        return $ip_address;
    }

    protected function getUrlFullPath($url)
    {
        $path = Tools::substr($_SERVER['REQUEST_URI'], Tools::strlen(__PS_BASE_URI__), Tools::strlen($_SERVER['REQUEST_URI']));

        if (preg_match('/^\//', $path)) {
            $path = Tools::substr($path, 1, Tools::strlen($path));
        }

        if (preg_match('/\/$/', $url)) {
            $final_url = $url.$path;
        } else {
            $final_url = $url.'/'.$path;
        }

        return $final_url;
    }

    protected function initCookieRedirection($id_redirection)
    {
        $redirection = new RgLdRedirection($id_redirection);

        if ($redirection->id) {
            $this->_cookie->redirection_url = $redirection->url;
            $this->_cookie->redirection_qty = $redirection->quantity;

            if ($redirection->full_path) {
                $this->_cookie->redirection_url = $this->getUrlFullPath($redirection->url);
            }

            $this->_cookie->redirection_show_popup = ($redirection->use_popup && $redirection->popup);
        }
    }

    private function backwardForm(&$fields_form, &$helper, $button_toolbar = false)
    {
        // PS 1.5 code.
        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $types = $fields_form['form']['input'];

            foreach ($types as $key => $type) {
                if ('switch' == $type['type']) {
                    $fields_form['form']['input'][$key]['type'] = 'radio';
                    $fields_form['form']['input'][$key]['class'] = 't';

                    foreach (array_keys($type['values']) as $k) {
                        $id = $fields_form['form']['input'][$key]['values'][$k]['id'];
                        $fields_form['form']['input'][$key]['values'][$k]['id'] = $type['name'].'_'.$id;
                    }
                } elseif ('radio' == $type['type']) {
                    $fields_form['form']['input'][$key]['br'] = true;
                    $fields_form['form']['input'][$key]['class'] = 'no-float';
                } elseif ('textarea' == $type['type'] && isset($type['lang']) && true == $type['lang']) {
                    $fields_form['form']['input'][$key]['cols'] = 40;
                    $fields_form['form']['input'][$key]['rows'] = 10;
                } elseif ('textarea' == $type['type'] && !isset($type['cols'])) {
                    $fields_form['form']['input'][$key]['cols'] = 80;
                } elseif ('color' == $type['type'] && !isset($type['size'])) {
                    $fields_form['form']['input'][$key]['size'] = 20;
                } elseif ('html' == $type['type'] && isset($type['html_content'])) {
                    $fields_form['form']['input'][$key]['desc'] = $type['html_content'];
                }
            }

            $fields_form['form']['legend']['image'] = $this->_path.'logo.gif';
            $fields_form['form']['submit']['class'] = 'button';

            if ($button_toolbar) {
                $helper->show_toolbar = true;
                $helper->toolbar_btn = array(
                    'back' => array(
                        'href' => $helper->currentIndex.'&token='.$helper->token,
                        'desc' => $this->l('Back'),
                    ),
                    'save' => array(
                        'desc' => $this->l('Save'),
                    ),
                );
            }
        }
    }

    private function backwardList(&$fields_list)
    {
        // PS 1.5 codes.
        if (version_compare(_PS_VERSION_, '1.5.5.0', '<')) {
            $list = $fields_list;

            foreach (array_keys($list) as $key) {
                if (isset($fields_list[$key]['color'])) {
                    unset($fields_list[$key]['color']);
                }
            }
        }
    }

    private function insDefaultIp()
    {
        return Configuration::updateValue('RGLD_EXCLUDE_IP', str_replace("\n", ';', str_replace("\r", '', RgLdTools::defaultIpListed())));
    }

    private function insDefaultCrawler()
    {
        return Configuration::updateValue('RGLD_EXCLUDE_AGENTS', str_replace("\n", ';', str_replace("\r", '', RgLdTools::defaultCrawlersListed())));
    }

    private function delCacheIndex()
    {
        if (file_exists(_PS_CACHE_DIR_.'class_index.php')) {
            @unlink(_PS_CACHE_DIR_.'class_index.php');
        }

        return true;
    }

    private function _detectLangList($only_values = false)
    {
        $return = array(
            array(
                'id' => 'type_no',
                'value' => 0,
                'label' => $this->l('No'),
            ),
            array(
                'id' => 'type_country_browser',
                'value' => 1,
                'label' => $this->l('Yes, based on the country, if doesn\'t exists, then detect by the browser (Recommended)'),
            ),
            array(
                'id' => 'type_country',
                'value' => 2,
                'label' => $this->l('Yes, based on the country'),
            ),
            array(
                'id' => 'type_browser',
                'value' => 3,
                'label' => $this->l('Yes, based on the browser language'),
            ),
        );

        if ($only_values) {
            $return = array_column($return, 'value');
        }

        return $return;
    }

    private function _infoBarPositionList($only_values = false)
    {
        $return = array(
            array('position' => 'top', 'name' => 'Top'),
            array('position' => 'bottom', 'name' => 'Bottom'),
        );

        if ($only_values) {
            $return = array_column($return, 'position');
        }

        return $return;
    }

    private function _countryCarrierList($only_values = false)
    {
        $return = array(array('id_carrier' => '0', 'name' => $this->l('-- None --')));
        $return = array_merge($return, (array)Carrier::getCarriers($this->context->language->id));

        if ($only_values) {
            $return = array_column($return, 'id_carrier');
        }

        return $return;
    }

    private function getDatabases($only_value = false)
    {
        $files = array();

        foreach (glob($this->local_path.'libraries/databases/*.BIN') as $file) {
            $files[] = array('file' => basename($file));
        }

        if ($only_value) {
            $files = array_column($files, 'file');
        }

        return $files;
    }

    public function downloadDB()
    {
        if (trim(Configuration::get('RGLD_TOKEN'))) {
            if (Tools::copy('http://www.ip2location.com/download/?file=DB3LITEBINIPV6&token='.Configuration::get('RGLD_TOKEN'), $this->local_path.'libraries/databases/DB3LITEBINIPV6.BIN.zip')) {
                if (Tools::ZipExtract($this->local_path.'libraries/databases/DB3LITEBINIPV6.BIN.zip', $this->local_path.'libraries/databases/')) {
                    foreach (glob($this->local_path.'libraries/databases/*.TXT') as $file) {
                        unlink($file);
                    }

                    return true;
                }

                @unlink($this->local_path.'libraries/databases/DB3LITEBINIPV6.BIN.zip');
            }
        }

        return false;
    }
}
