<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }

class AmazonDataAdjustment
{
    public $setting_module_prefix;

    public function __construct($setting_module_prefix = null)
    {
        $this->setting_module_prefix = $setting_module_prefix;
    }

    public function parseDataToJson($is_module_configuration = false)
    {
        $table = $is_module_configuration ? _DB_PREFIX_ . 'amazon_configuration' : _DB_PREFIX_ . 'configuration';
        if ($is_module_configuration && !AmazonTools::tableExists($table)) {
            return false;
        }
        $sql = 'SELECT * FROM `' . $table . '`';
        if (!$is_module_configuration) {
            $sql .= ' WHERE `name` LIKE "' . pSQL($this->setting_module_prefix) . '%"';
        }

        $results = Db::getInstance()->ExecuteS($sql);
        if (!empty($results)) {
            $targetName = $this->setting_module_prefix . AmazonConstant::CONFIG_IS_PARSE_JSON;
            $isParseJson = $is_module_configuration ? (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_IS_PARSE_JSON) : (bool)Configuration::get($targetName);

            if (!$isParseJson) {
                $parse_json_success = 0;
                try {
                    foreach ($results as $result) {
                        if ($this->isParseJson($result['value'])) {
                            continue;
                        }
                        if (!$this->isSerialized($result['value'])) {
                            $decodeBase64 = base64_decode($result['value']);
                            if ($this->isSerialized($decodeBase64)) {
                                $this->convertSerializedToJson($result['name'], $decodeBase64, true, $result['id_shop'], $result['id_shop_group']); // Convert to Json and save
                            }
                        } else {
                            $this->convertSerializedToJson($result['name'], $result['value'], false, $result['id_shop'], $result['id_shop_group'], $is_module_configuration); // Convert to Json and save
                        }
                    }
                    $parse_json_success = 1;
                } catch (\Exception $e) {
                    // failed $parse_json_success = 0;
                }
                $this->updateConfigValue($targetName, $parse_json_success, $is_module_configuration);
            } else {
                $this->checkConvertFailed();
            }
        }
        return true;
    }

    /**
     * Todo: Check and remove in the future
     * @param $configuration_key
     * @return void
     * @throws PrestaShopDatabaseException
     */
    private function checkConvertFailed() {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'amazon_configuration' . '`';
        $sql .= ' WHERE `name` IN ("AMAZON_SESSION_OFFERS", "AMAZON_BATCH_ACKNOWLEDGE", "AMAZON_OI_CRON_FAILED_LIST", "AMAZON_OI_CRON_FAILED_LIST", "AMAZON_BATCH_OFFERS_CRON", "AMAZON_BATCH_OFFERS", "AMAZON_SESSION_PRODUCTS", "AMAZON_BATCH_PRODUCTS", "SESSION_STATUS", "BATCH_STATUS", "BATCH_CANCEL", "BATCH_ORDER_REPORT", "AMAZON_SESSION_STATUS")';
        $results = Db::getInstance()->ExecuteS($sql);

        foreach ($results as $config) {
            if ($this->isParseJson($config['value'])) {
                continue;
            }
            $newValue = str_replace("\\", '', $config['value']);
            if($config['name'] == "AMAZON_BATCH_OFFERS_CRON") {
                $temp = '';
                for ($i = 0; $i < strlen($newValue); $i++) {
                    if ($newValue[$i] === '"') {
                        $temp .= substr($newValue, $i);
                        break;
                    }
                }
                $newValue = $temp;
            }
            if (substr($newValue, 0, 1) === '"' && substr($newValue, -1) === '"') {
                $newValue = substr($newValue, 1, -1);
            }
            $this->convertSerializedToJson($config['name'], $newValue, false, $config['id_shop'], $config['id_shop_group'], true);
        }
    }

    /**
     * @param $nameConfig
     * @param $value
     * @param $isEncodeBase64
     * @param $is_module_configuration
     * @return void
     */
    private function convertSerializedToJson($nameConfig, $value, $isEncodeBase64 = false, $shopID = null, $shopGroupID = null, $is_module_configuration = false)
    {
        $data = Tools::unSerialize($value, true);
        $parseJson = $isEncodeBase64 ? base64_encode(json_encode($data)) : $data;
        $this->updateConfigValue($nameConfig, $parseJson, $is_module_configuration, $shopID, $shopGroupID);
    }

    /**
     * @param $name
     * @param $value
     * @param $is_module_configuration
     * @param $shopID
     * @param $shopGroupID
     * @return void
     */
    private function updateConfigValue($name, $value, $is_module_configuration = false, $shopID = null, $shopGroupID = null)
    {
        if ($is_module_configuration) {
            $name = str_replace("AMAZON_", "", $name);
            AmazonConfiguration::updateValue($name, $value, false, $shopGroupID, $shopID);
        } else {
            $value = json_encode($value);
            Configuration::updateValue($name, $value, false, $shopGroupID, $shopID);
        }
    }

    /**
     * @param $data
     * @param $strict
     * @return bool
     */
    private function isSerialized($data, $strict = true)
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' === $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (!str_contains($data, '"')) {
                    return false;
                }
            // Or else fall through.
            case 'a':
            case 'O':
            case 'E':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }
        return false;
    }

    private function isParseJson($data)
    {
        if ($data == 'true' || $data == 'false' || !is_string($data)) {
            return true;
        }

        switch (mb_substr($data, 0, 1)) {
            case '{':
            case '[':
                return true;
        }
        return false;
    }

    /**
     * Replace old call serialize
     * @param $value
     * @return string
     */
    public static function serialize($value)
    {
        return json_encode($value);
    }

    /**
     * Replace old call unSerialize
     * @param $value
     * @param $associative
     * @return mixed
     */
    public static function unSerialize($value, $associative = null)
    {
        return !is_array($value) ? json_decode($value, $associative) : $value;
    }
}