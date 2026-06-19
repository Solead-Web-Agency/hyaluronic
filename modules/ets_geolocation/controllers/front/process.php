<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Ets_geolocationProcessModuleFrontController
 *
 * @property \Ets_geolocation $module
 * @property \Context|\ContextCore $context
 *
 * @mixin \ModuleFrontControllerCore
 */
class Ets_geolocationProcessModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $this->template = $this->module->is17 ? 'module:ets_geolocation/views/templates/front/popup.tpl' : 'popup16.tpl';
    }

    /**
     * @return \EtsGeoConfigService
     */
    public function getConfigService()
    {
        return $this->module->getConfigService();
    }

    public function initContent()
    {
        if (_PS_MODE_DEV_) {
            @error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_WARNING);
        }
        parent::initContent();
        if ($this->getConfigService()->isBotAndIgnored()) {
            exit;
        }
        $msg = $this->module->l('Geolocation is disable or unavailable', 'process');
        if (!(int) Configuration::get('PS_GEOLOCATION_ENABLED') || !$this->module->isGeoLiteCityAvailable()) {
            exit($msg);
        }
        try {
            $userIdCountry = $this->module->detectUserCountry(true);
        } catch (PrestaShopDatabaseException $e) {
            exit($e->getMessage());
        } catch (PrestaShopException $e) {
            exit($msg);
        }
        $geo_rule = Geo_rules::getRulesByIdCountry($userIdCountry);
        if ($this->module->checkThisCountryEnabled($userIdCountry) && $geo_rule) {
            if (isset($geo_rule['block_user']) && $geo_rule['block_user']) {
                exit(json_encode([
                    'link_block' => $this->context->link->getModuleLink($this->module->name, 'block', [], Tools::usingSecureMode()),
                ]));
            } elseif (isset($geo_rule['url_redirect']) && $geo_rule['url_redirect']) {
                exit(json_encode([
                    'link_block' => $geo_rule['url_redirect'],
                ]));
            } elseif (isset($geo_rule['disable_geo']) && $geo_rule['disable_geo']) {
                exit($msg);
            }
        }
        $this->setTemplate($this->template);
        if (Tools::isSubmit('geo_auto_processing') && !$this->getConfigService()->isGeoAutoCountryProcessed()
            && (!$this->getConfigService()->isWorkingOnHomePageOnly() || $this->context->cookie->page_controller == 'index')
        ) {
            $this->visited();
            if ($this->getConfigService()->isGeoRedirected()) {
                exit(json_encode(['html' => false]));
            }

            // for auto set currency and language
            $idCurrency = $idLang = null;
            $city = $this->module->detectAddressFromIp($this->module->getRemoteAddr());
            $detectedByGeo = $city && isset($city->country->isoCode);
            // set shipping tax.
            $this->module->detectedAddress($detectedByGeo ? $city->country->isoCode : $this->context->cookie->iso_code_country, $detectedByGeo);

            if ($detectedByGeo) {
                $this->getConfigService()->setCookieCountryIso($city->country->isoCode);
            }
            if ($detectedByGeo && $this->getConfigService()->isAutoSetLang() && Language::isMultiLanguageActivated()) {
                if ($lang = EtsGeoCountryHelper::getLangByIso($city->country->isoCode)) {
                    $idLang = $lang->id;
                }
            }
            if ($detectedByGeo && $this->getConfigService()->isAutoSetCurrency() && Currency::isMultiCurrencyActivated()) {
                if ($geoCurrency = EtsGeoCountryHelper::getIdCurrencyByIso($city->country->isoCode)) {
                    $idCurrency = $geoCurrency;
                } elseif (isset($this->context->country->id_currency) && (int) $this->context->country->id_currency) {
                    $idCurrency = (int) $this->context->country->id_currency;
                }
            }
            $is_hide_popup = !$this->getConfigService()->isShowConfirmPopup();
            $msg = '';
            $json = [];
            $url_params = [];
            $detectedCountryObj = $detectedByGeo ? new \Country(EtsGeoCountryHelper::getIdByIso($city->country->isoCode), $idLang) : $this->context->country;
            $argument = [
                //                'country_name' => is_array($this->context->country->name) ? $this->context->country->name[$this->context->language->id] : $this->context->country->name,
                'country_name' => is_array($detectedCountryObj->name) ? $detectedCountryObj->name[$this->context->language->id] : $detectedCountryObj->name,
                'geo_hide_notification' => $is_hide_popup,
            ];
            if ($idCurrency || $idLang) {
                if ((is_int($idLang) && $idLang != $this->context->language->id) && (is_int($idCurrency) && $idCurrency != $this->context->currency->id)) {
                    if ($is_hide_popup) {
                        $this->context->cookie->id_currency = $idCurrency;
                        $this->context->cookie->id_lang = $idLang;
                        $json['link_reload'] = $this->pregLink($this->context->cookie->id_lang);
                    } else {
                        $url_params = ['lang_id' => $idLang, 'currency_id' => $idCurrency];
                    }
                    $msg = $this->getConfigService()->getMessage($is_hide_popup ? 'setting' : 'confirm', $this->context->language->id);
                } elseif (is_int($idLang) && $idLang != $this->context->language->id) {
                    if ($is_hide_popup) {
                        $this->context->cookie->id_lang = $idLang;
                        $json['link_reload'] = $this->pregLink($this->context->cookie->id_lang);
                    } else {
                        $url_params = ['lang_id' => $idLang, 'currency_id' => $this->context->currency->id];
                    }
                    $msg = $this->getConfigService()->getMessage($is_hide_popup ? 'setting' : 'language', $this->context->language->id);
                } elseif (is_int($idCurrency) && $idCurrency != $this->context->currency->id) {
                    if ($is_hide_popup) {
                        $this->context->cookie->id_currency = $idCurrency;
                        $json['link_reload'] = $this->context->language->language_code;
                    } else {
                        $url_params = ['currency_id' => $idCurrency, 'lang_id' => $this->context->language->id];
                    }
                    $msg = $this->getConfigService()->getMessage($is_hide_popup ? 'setting' : 'currency', $this->context->language->id);
                }
            }
            $this->getConfigService()->setGeoAutoCountryProcessed(true);
            if ($url_params) {
                $argument['btn_link'] = $this->context->link->getModuleLink('ets_geolocation', 'process', $url_params, Tools::usingSecureMode());
            }
            $msg = $this->replaceShortCode($msg, $idLang, $idCurrency, $detectedCountryObj);
            $argument['msg'] = $msg;
            $json['html'] = $this->displayPopup($argument);
            exit(json_encode($json));
        }
        if (Tools::isSubmit('geo_confirm')) {
            if ($id_lang = Tools::getValue('lang_id')) {
                $this->context->cookie->id_lang = $id_lang;
            }
            if ($id_currency = Tools::getValue('currency_id')) {
                $this->context->cookie->id_currency = $id_currency;
            }
            $this->context->cookie->ets_geocountryloaded = 1;
            $this->context->cookie->ets_geo_manual_select_country = 1;
            $this->visited();
            exit(json_encode([
                'link_reload' => $this->pregLink($this->context->cookie->id_lang ?: Configuration::get('PS_LANG_DEFAULT')),
            ]));
        }
        if (Tools::isSubmit('choose_country')) {
            $content_choose = Configuration::get('ETS_GEO_CHOOSE_MSG', $this->context->language->id);
            $list_countries = $this->module->ets_getCountriesNotBlock($this->context->language->id, true, false, false);
            foreach ($list_countries as &$country) {
                $iso_code = $country['iso_code'];
                $id_lang_temp = $this->module->getGeoIDLang((int) $country['id_country']);
                if ($id_lang_temp) {
                    if (file_exists(_PS_TMP_IMG_DIR_ . 'lang_mini_' . $id_lang_temp . '_' . $this->context->shop->id . '.jpg')) {
                        $country['icon_image'] = _PS_TMP_IMG_ . 'lang_mini_' . $id_lang_temp . '_' . $this->context->shop->id . '.jpg';
                    } elseif (file_exists(_PS_IMG_DIR_ . 'l/' . $id_lang_temp . '.jpg')) {
                        $country['icon_image'] = _PS_IMG_ . 'l/' . $id_lang_temp . '.jpg';
                    }
                } else {
                    if (file_exists($this->module->getLocalPath() . 'views/img/flag/' . Tools::strtolower($iso_code) . '.jpg')) {
                        $country['icon_image'] = $this->module->getPathUri() . 'views/img/flag/' . Tools::strtolower($iso_code) . '.jpg';
                    } else {
                        $country['icon_image'] = $this->module->getPathUri() . 'views/img/flag/no_country.jpg';
                    }
                }
                unset($country);
            }
            $url_cart_page = $this->context->link->getPageLink('my-account', Configuration::get('PS_SSL_ENABLED'), $this->context->language->id);
            $this->context->smarty->assign([
                'content_choose' => $content_choose,
                'list_country' => $list_countries,
                'current_country_id' => (isset($this->context->country->id) && $this->context->country->id && $this->context->country->active ? $this->context->country->id : Configuration::get('PS_COUNTRY_DEFAULT')),
                'url_cart_page' => $url_cart_page,
            ]);
            $content_pop_choose = $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/front/popup_choose.tpl');
            exit(json_encode([
                'content_pop_choose' => $content_pop_choose,
            ]));
        }
        if (Tools::isSubmit('geo_loaded')) {
            $this->context->cookie->ets_geocountryloaded = 1;
            $this->visited();
            exit('Geo is loaded');
        }
        if (Tools::isSubmit('geo_selected_country')
            && ($id_country = Tools::getValue('country_id'))
            && ($country = new Country($id_country))
            && $country->id
            && $country->active) {
            $geo_rule = Geo_rules::getRulesByIdCountry($id_country);
            if ($geo_rule) {
                if (isset($geo_rule['block_user']) && $geo_rule['block_user']) {
                    exit(json_encode([
                        'link_block' => $this->context->link->getModuleLink($this->module->name, 'block', [], Tools::usingSecureMode()),
                    ]));
                }
                if (isset($geo_rule['url_redirect']) && $geo_rule['url_redirect']) {
                    exit(json_encode([
                        'link_block' => $geo_rule['url_redirect'],
                    ]));
                }
            }
            $this->context->cookie->iso_code_country = $country->iso_code;
            $this->module->detectedAddress($this->context->cookie->iso_code_country);
            if ($country->id_currency) {
                $this->context->cookie->id_currency = $country->id_currency;
            } elseif ($geoIdCurrency = (int) EtsGeoCountryHelper::getIdCurrencyByIso($country->iso_code)) {
                $this->context->cookie->id_currency = $geoIdCurrency;
            }
            $this->context->cookie->id_lang = ($geoLang = \EtsGeoCountryHelper::getLangByIso($country->iso_code)) ? $geoLang->id : (int) Configuration::get('PS_LANG_DEFAULT');
            if ($geo_rule) {
                if (isset($geo_rule['currency_to_set']) && (int) $geo_rule['currency_to_set']) {
                    $this->context->cookie->id_currency = (int) $geo_rule['currency_to_set'];
                }
                if (isset($geo_rule['lang_to_set']) && (int) $geo_rule['lang_to_set']) {
                    $this->context->cookie->id_lang = (int) $geo_rule['lang_to_set'];
                }
            }
            $this->getConfigService()->setManualSelectCountry(true);
            exit(json_encode([
                'link_reload' => $this->pregLink($this->context->cookie->id_lang),
                'success' => true,
            ]));
        }
        exit('exit');
    }

    public function visited()
    {
        $currentIp = $this->module->getRemoteAddr();
        // Add visited.
        if (Geo_visit::checkIpVisitToDay($currentIp)) {
            return;
        }
        $geo_visit_day = new Geo_visit_day();
        $geo_visit_day->deleteOtherDay();
        $geo_visit_day->ip_visit = $currentIp;
        $geo_visit_day->add();
        $idCountry = $this->context->country->id;
        $city = $this->module->detectAddressFromIp($currentIp);
        if ($city && isset($city->country->isoCode)) {
            $idCountry = EtsGeoCountryHelper::getIdByIso($city->country->isoCode);
        }
        if ($check_country = Geo_visit::getCountryVisitToDay($idCountry)) {
            $geo_visit = new Geo_visit($idCountry);
        } else {
            $geo_visit = new Geo_visit();
            $geo_visit->id_country = $idCountry;
            $geo_visit->visit = 1;
        }
        $geo_visit->last_ip = $currentIp;
        $geo_visit->last_visit_time = date('Y-m-d H:i:s');
        if ($check_country) {
            $geo_visit->update_custom();
        } else {
            $geo_visit->add();
        }
    }

    public function pregLink($id_lang)
    {
        $language = new Language($id_lang);

        return $language->iso_code;
    }

    public function displayPopup($argument = [])
    {
        if (!isset($argument['msg']) || !$argument['msg']) {
            return false;
        }
        $this->context->smarty->assign($argument);

        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/front/popup.tpl');
    }

    /**
     * @param string $msg
     * @param int $id_lang
     * @param int $id_currency
     * @param \Country|null $detectedCountry
     *
     * @return string|string[]|null
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function replaceShortCode($msg, $id_lang, $id_currency, $detectedCountry = null)
    {
        if (!$detectedCountry) {
            $detectedCountry = $this->context->country;
        }
        if (!$msg) {
            return $msg;
        }
        $language = new Language($id_lang ?: Configuration::get('PS_LANG_DEFAULT'));
        $currency = new Currency($id_currency);
        $msg = str_replace(
            ['[detected_country]', '[detected_language]', '[detected_currency]', '[current_language]', '[current_currency]'],
            [is_array($detectedCountry->name) ? $detectedCountry->name[$language->id] : $detectedCountry->name, $language->name, Tools::ucfirst($currency->name) . ' (' . $currency->iso_code . ')', $this->context->language->name, Tools::ucfirst($this->context->currency->name) . ' (' . $this->context->currency->iso_code . ')'],
            $msg
        );

        return $msg;
    }
}
