<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_3_0_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $lang_from = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
    Configuration::updateValue('AT_LANG_FROM', $lang_from);
    $module_obj->defineAPI();
    $module_obj->api->install();
    $module_obj->repeatingTranslations('install');
    if ($yandex_api_key = Configuration::get('Y_API_KEY')) {
        $date_array = explode('-', gmdate('j-n-Y'));
        $chars_day = (int)$module_obj->db->getValue('
            SELECT characters FROM '._DB_PREFIX_.'at_stats
            WHERE day = '.(int)$date_array[0].' AND month = '.(int)$date_array[1].'
            AND year = '.(int)$date_array[2].'
        ');
        $chars_month = (int)$module_obj->db->getValue('
            SELECT SUM(characters) FROM '._DB_PREFIX_.'at_stats
            WHERE month = '.(int)$date_array[1].' AND year = '.(int)$date_array[2].'
        ');
        $yandex_provider_data = array(
            'provider' => 'YandexTranslate',
            'credentials' => Tools::jsonEncode(array('api_key' => $yandex_api_key)),
            'selected' => 1,
            'stats' => Tools::jsonEncode($module_obj->api->validateStats(array(
                'd' => array($date_array[0], $chars_day),
                'm' => array($date_array[1], $chars_month)
            ))),
        );
        $module_obj->api->addProvider($yandex_provider_data);
    }
    Configuration::deleteByName('Y_API_KEY');
    $module_obj->db->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'at_stats');
    return true;
}
