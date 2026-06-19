<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

require_once('TranslationProvider.php');

class TranslationAPI
{
    public function __construct($module)
    {
        $this->module = $module;
        $this->db = $module->db;
        $this->db_table = _DB_PREFIX_.'at_api';
        $this->classes_dir = _PS_MODULE_DIR_.$this->module->name.'/classes/';
        $this->processed_chars_num = 0;
    }

    public function install()
    {
        return $this->db->execute('
            CREATE TABLE IF NOT EXISTS '.pSQL($this->db_table).' ('.
            $this->getProviderColumns().', PRIMARY KEY (id)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8
        ');
    }

    public function getProviderColumns($return_keys = false)
    {
        $columns = array(
            'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
            'provider' => 'varchar(32) NOT NULL',
            'credentials' => 'text NOT NULL',
            'selected' => 'tinyint(1) NOT NULL DEFAULT 0',
            'stats' => 'text NOT NULL',
        );
        if ($return_keys) {
            $columns = array_keys($columns);
        } else {
            foreach ($columns as $name => &$value) {
                $value = $name.' '.$value;
            }
            $columns = implode(', ', $columns);
        }
        return $columns;
    }

    public function uninstall()
    {
        return $this->db->execute('DROP TABLE IF EXISTS '.pSQL($this->db_table));
    }

    public function getAvailableProviders($only_keys = false)
    {
        $ret = array();
        foreach (glob($this->classes_dir.'*Translate.php') as $file) {
            $provider_name = basename($file, '.php');
            if ($provider = $this->getProvider($provider_name)) {
                $ret[$provider_name] = $provider;
            }
        }
        return $only_keys ? array_keys($ret) : $ret;
    }

    public function saveData($provider_name, $data)
    {
        $provider = $this->getProvider($provider_name, $data);
        if ($provider && $this->validateCredentials($provider)) {
            $data = Tools::jsonEncode($data);
            return $this->db->execute('
                UPDATE '.pSQL($this->db_table).' SET selected = 0;
                UPDATE '.pSQL($this->db_table).' SET selected = 1, credentials = \''.pSQL($data).'\'
                WHERE provider = \''.pSQL($provider_name).'\'
            ');
        }
    }

    public function getSavedData($provider_name)
    {
        return $this->db->getRow('
            SELECT * FROM '.pSQL($this->db_table).'
            WHERE provider = \''.pSQL($provider_name).'\'
        ');
    }

    public function validateCredentials($provider)
    {
        return $this->translate('Hello', 'en', 'ru', $provider, true);
    }

    public function translate($content, $from, $to, $provider = null, $validation_process = false)
    {
        if ($content) {
            $provider = $provider ?: $this->getSelectedProvider();
            $translation_result = $provider->translate($content, $from, $to);
            $this->not_processed = $translation_result['not_processed'];
            $translation = $translation_result['result'];
            if ($provider->errors) {
                foreach ($provider->errors as &$e) {
                    $text = $provider->data['name'].' | ';
                    if (!$validation_process) {
                        $text .= $from.'→'.$to.' | ';
                    }
                    $e = $text.$e;
                }
                $this->module->throwError($provider->errors);
            }
            $this->updateStats($provider, $provider->processed_chars_num);
        } else {
            $translation = $content;
        }
        return $translation;
    }

    public function updateStats($provider, $char_count)
    {
        $stats = $this->validateStats($provider->saved_data['stats']);
        foreach ($stats as &$s_value) {
            $s_value[1] += $char_count;
        }
        $this->processed_chars_num += $char_count;
        return $this->db->execute('
            UPDATE '.pSQL($this->db_table).' SET stats = \''.pSQL(Tools::jsonEncode($stats)).'\'
            WHERE provider = \''.pSQL($provider->saved_data['provider']).'\'
        ');
    }

    public function validateStats($stats_data = array())
    {
        if (is_string($stats_data)) {
            $stats_data = Tools::jsonDecode($stats_data, true);
        }
        $date_array = explode('-', gmdate('j-n'));
        $stats = array('d' => array($date_array[0], 0), 'm' => array($date_array[1], 0));
        foreach ($stats as $key => $st) {
            if (isset($stats_data[$key]) && isset($stats_data[$key][0]) &&
                isset($stats_data[$key][1]) && $stats_data[$key][0] == $st[0]) {
                $stats[$key][1] = $stats_data[$key][1];
            }
        }
        return $stats;
    }

    public function getSimplifiedStatsData($provider = null)
    {
        $provider = $provider ?: $this->getSelectedProvider();
        $stats = $this->validateStats($provider->saved_data['stats']);
        return array('day' => $stats['d'][1], 'month' => $stats['m'][1]);
    }

    public function getProvider($provider_name, $custom_credentials = false)
    {
        $file = $this->classes_dir.$provider_name.'.php';
        if (file_exists($file)) {
            require_once($file);
            if (class_exists($provider_name)) {
                if (!$saved_data = $this->getSavedData($provider_name)) {
                    $saved_data = $this->addProvider(array('provider' => $provider_name));
                }
                if ($custom_credentials) {
                    $saved_data['credentials'] = $custom_credentials;
                }
                if (is_string($saved_data['credentials'])) {
                    $saved_data['credentials'] = Tools::jsonDecode($saved_data['credentials'], true);
                }
                $saved_data['stats'] = $this->validateStats($saved_data['stats']);
                return new $provider_name($saved_data);
            }
        }
    }

    public function getSelectedProvider()
    {
        return $this->getProvider($this->db->getValue('
            SELECT provider FROM '.pSQL($this->db_table).' ORDER BY selected DESC
        '));
    }

    public function addProvider($forced_values = array())
    {
        $data = array_fill_keys($this->getProviderColumns(true), '');
        $data['stats'] = Tools::jsonEncode($this->validateStats());
        foreach ($forced_values as $key => $value) {
            if (isset($data[$key])) {
                $data[$key] = pSQL($value);
            }
        }
        $this->db->execute('
            REPLACE INTO '.pSQL($this->db_table).' VALUES (\''.implode('\', \'', $data).'\')
        ');
        return $data;
    }
}
