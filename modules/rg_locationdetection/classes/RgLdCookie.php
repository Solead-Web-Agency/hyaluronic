<?php
/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

class RgLdCookie
{
    private static $cookie = array();

    /**
     * Own cookie of the module
     *
     * @param int $lifetime [Seconds]
     *
     * @return object [Cookie by `id_shop`]
     */
    public static function get($lifetime = null)
    {
        $name = 'rgld-ps-'.(int)Context::getContext()->shop->id;

        if (!isset(self::$cookie[$name])) {
            if ($lifetime === null) {
                $lifetime = (int)Configuration::get('RGLD_COOKIE');
            }

            $expire = (int)$lifetime;
            $expire = $expire ? time() + (60 * 60 * $expire) : $expire;
            $module = Module::getInstanceByName('rg_locationdetection');
            $dev_mode = false;

            if (Tools::getValue('dev_mode') && Tools::getValue('secure_key') == $module->secure_key) {
                $dev_mode = true;
                $expire = time();
            }

            $force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
            $domains = null;

            if (Context::getContext()->shop->domain != Context::getContext()->shop->domain_ssl) {
                $domains = array(Context::getContext()->shop->domain_ssl, Context::getContext()->shop->domain);
            }

            self::$cookie[$name] = new Cookie(
                $name,
                Context::getContext()->shop->virtual_uri,
                $expire,
                $domains,
                false,
                $force_ssl
            );

            foreach (self::initList() as $key => $value) {
                if ($dev_mode || !isset(self::$cookie[$name]->{$key})) {
                    self::$cookie[$name]->{$key} = $value;
                }
            }
        }

        return self::$cookie[$name];
    }

    private static function initList()
    {
        return array(
            'detect_location' => 'init',
            'ip_address' => false,
            'lang_currency_switched_once' => false,
            'ps_id_country' => false,
            'ps_country_contains_states' => false,
            'ps_country_active' => false,
            'id_country' => false,
            'country_iso_code' => false,
            'country_name' => false,
            'id_state' => false,
            'state_name' => false,
            'city_name' => false,
            'id_lang' => false,
            'id_currency' => false,
            'id_carrier' => false,
            'flag_url' => false,
            'redirection_id' => false,
            'redirection_qty' => false,
            'redirection_count' => 0,
            'redirection_url' => false,
            'redirection_show_popup' => false,
            'redirection_popup_count' => 1,
            'redirection_popup' => false,
            'infobar_id' => false,
            'infobar_show' => false,
        );
    }
}
