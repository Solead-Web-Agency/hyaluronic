<?php
/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA PL MILOSZ MYSZCZUK VATEU: PL9730945634
 * @copyright 2010-2024 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */

class Shop extends ShopCore
{
    public static function returnUserCountry()
    {
        $_SERVER['REMOTE_ADDR'] = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
        $groups_array = explode(',', Configuration::get('HBC_EXCLUDE_GROUP'));
        if (is_array($groups_array)) {
            if (count($groups_array) > 0) {
                foreach (self::getCustomerGroups() AS $g => $k) {
                    if (in_array($g, $groups_array)) {
                        return false;
                    }
                }
            }
        }

        if (isset(Context::getContext()->customer->id)) {
            if (is_int(Context::getContext()->customer->id)) {
                if (Context::getContext()->customer->id > 0) {
                    $customers_array = explode(',', Configuration::get('HBC_EXCLUDE_CUSTOMER'));
                    if (is_array($customers_array)) {
                        if (count($customers_array) > 0) {
                            if (in_array(Context::getContext()->customer->id, $customers_array)) {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        if (in_array(Tools::getRemoteAddr(),explode(";",Configuration::get('PS_GEOLOCATION_WHITELIST')))) {
            return false;
        }
        
        $record = false;
        if ((!in_array(Tools::getRemoteAddr(), array('localhost', '1227.0.0.1')) && !in_array($_SERVER['SERVER_NAME'], array('localhost', '127.20.0.1'))) || Configuration::get('HBC_SIMULATE_ON') == true) {
            /* Check if Maxmind Database exists */
            if (@filemtime(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_)) {
                $reader = new GeoIp2\Database\Reader(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_);
                try {
                    $ip = Configuration::get('HBC_SIMULATE_IP');
                    $record = $reader->city((Configuration::get('HBC_SIMULATE_ON') ? (filter_var($ip, FILTER_VALIDATE_IP) ? $ip : Tools::getRemoteAddr()) : Tools::getRemoteAddr()));
                } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                    $record = null;
                }

                if (isset($record->country->isoCode)) {
                    return $record->country->isoCode;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function addSqlAssociation($table, $alias, $inner_join = true, $on = null, $force_not_default = false)
    {
        $table_alias = $table . '_shop';
        if (strpos($table, '.') !== false) {
            list($table_alias, $table) = explode('.', $table);
        }
        $asso_table = Shop::getAssoTable($table);
        if ($asso_table === false || $asso_table['type'] != 'shop') {
            return;
        }
        $sql = (($inner_join) ? ' INNER' : ' LEFT') . ' JOIN ' . _DB_PREFIX_ . $table . '_shop ' . $table_alias . '
		ON (' . $table_alias . '.id_' . $table . ' = ' . $alias . '.id_' . $table;
        if ((int)self::$context_id_shop) {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . (int)self::$context_id_shop;
        } elseif (Shop::checkIdShopDefault($table) && !$force_not_default) {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . $alias . '.id_shop_default';
        } else {
            $sql .= ' AND ' . $table_alias . '.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')';
        }
        $sql .= (($on) ? ' AND ' . $on : '');
        if (Module::isInstalled('hbc') && !defined('_PS_ADMIN_DIR_')) {
            if ($table == 'product' AND $alias == 'p') {
                $array_to_check = array();
                $context = Context::getContext();
                $country = self::returnUserCountry();
                if ($country != false) {
                    $array_to_check = array(Country::getByIso($country));
                    if (Configuration::get('HBC_DELIVERY') == 1) {
                        if (isset(Context::getContext()->cart->id_address_delivery)) {
                            if (Context::getContext()->cart->id_address_delivery){
                                $address = new Address(Context::getContext()->cart->id_address_delivery);
                                if (isset($address->id_country)) {
                                    $country_address = new Country($address->id_country);
                                    $array_to_check[] =Country::getByIso($country_address->iso_code);
                                }
                            }
                        }
                    }
                    if (1 == 1) {
                        $sql .= ' AND p.id_product NOT IN (SELECT id_product FROM ' . _DB_PREFIX_ . 'hbc hbc WHERE hbc.id_shop IN (' . (int)self::$context_id_shop . ') AND hbc.`country` IN (' . implode(',', $array_to_check) . '))';
                    }
                    unset($array_to_check);
                }
            }
        }
        $sql .= ')';
        return $sql;
    }

    public static function getCustomerGroups()
    {
        $customer_groups = array();
        if (isset(Context::getContext()->cart->id_customer)) {
            if (Context::getContext()->cart->id_customer == 0) {
                $customer_groups[1] = 1;
            } else {
                foreach (Customer::getGroupsStatic(Context::getContext()->cart->id_customer) as $group) {
                    $customer_groups[$group] = 1;
                }
            }
        } elseif (Context::getContext()->customer->is_guest == 1) {
            $customer_groups[1] = 2;
        } else {
            $customer_groups[1] = 1;
        }
        if (count($customer_groups) > 0) {
            return $customer_groups;
        } else {
            return false;
        }
    }
}