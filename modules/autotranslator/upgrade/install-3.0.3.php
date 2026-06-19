<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_3_0_3($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $module_obj->defineAPI();
    $deepl_row = $module_obj->db->getRow('
        SELECT * FROM '.pSQL($module_obj->api->db_table).'
        WHERE provider = \'DeeplTranslate\'
    ');
    if (is_array($deepl_row) && !empty($deepl_row['credentials'])) {
        $credentials = Tools::jsonDecode($deepl_row['credentials'], true);
        $credentials['plan'] = 2; // in previous version only paid plan was available
        $deepl_row['credentials'] = Tools::jsonEncode($credentials);
        $module_obj->db->execute('
            REPLACE INTO '.pSQL($module_obj->api->db_table).'
            VALUES (\''.implode('\', \'', array_map('pSQL', $deepl_row)).'\')
        ');
    }
    return true;
}
