<?php
/**
 * 2010-2023 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2023 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class DatabaseTableConnector
{
    const KEY = 'BLMOD_XML_FEED_CUSTOM_FIELDS';

    public function save($feedId)
    {
        $customFieldsFromDatabase = $this->getAllValues();

        if (empty($customFieldsFromDatabase)) {
            $customFieldsFromDatabase = [];
        }

        $customFieldsFromDatabase[$feedId]['name'] = Tools::getValue('custom_field_name');
        $customFieldsFromDatabase[$feedId]['column_connector'] = Tools::getValue('table_column_connector');
        $customFieldsFromDatabase[$feedId]['column_value'] = Tools::getValue('table_column_value');

        Configuration::updateValue(self::KEY, htmlspecialchars(json_encode($customFieldsFromDatabase), ENT_QUOTES));
    }

    public function get($feedId)
    {
        $value = $this->getAllValues();

        if (empty($value[$feedId])) {
            return [
                'name' => [
                    0 => '',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                ],
                'column_connector' => [
                    0 => '',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                ],
                'column_value' => [
                    0 => '',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                ],
            ];
        }

        return $value[$feedId];
    }

    protected function getAllValues()
    {
        return json_decode(htmlspecialchars_decode(Configuration::get(self::KEY)), true);;
    }
}
