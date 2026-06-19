<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevAtos
 * Support by mail : contact@scaledev.fr
 */

use ScaleDEV\SdevAtos\SdevModule;
use ScaleDEV\SdevAtos\SdevConfiguration;

require_once(dirname(__FILE__).'../../autoload.php');

class SdevAtosContract extends SdevAtosObjectModel
{
    public $id;
    public $bank;
    public $sips_version;
    public $is_test_mode;
    public $exe_mode;
    public $exe_version;
    public $merchant_id;
    public $secrete_key;
    public $key_version;
    public $transaction_reference;
    public $transaction_ref_id;
    public $has_3d_secure;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => SdevModule::LNAME.'_contract',
        'primary' => 'id_contract',
        'multishop' => false,
        'fields' => array(
            'bank' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255, 'required' => true),
            'sips_version' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255, 'required' => true),
            'is_test_mode' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'exe_mode' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'exe_version' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'merchant_id' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 15, 'required' => true),
            'secrete_key' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'key_version' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 10),
            'transaction_reference' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255, 'required' => true),
            'transaction_ref_id' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 35),
            'has_3d_secure' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        )
    );

    public static $unique = 'merchant_id';

    /**
     * Get the certificate file name linked to a contract.
     *
     * @return bool|string
     */
    public function getCertificateFileName()
    {
        $PATHFILE_PATH = SdevConfiguration::get('PATHFILE_PATH');
        if (!$PATHFILE_PATH) {
            $PATHFILE_PATH = SdevModule::DIR.'param/';
        }
        if (is_dir($PATHFILE_PATH) && ($dir = opendir($PATHFILE_PATH))) {
            while (($file = readdir($dir)) !== false) {
                $exp = explode('.', $file);
                if (strstr($file, 'certif.') && array_pop($exp) == $this->merchant_id) {
                    return $file;
                }
            }
        }
        return false;
    }

    /**
     * Get the parmcom file name linked to a contract.
     *
     * @return bool|string
     */
    public function getParmcomFileName()
    {
        $PATHFILE_PATH = SdevConfiguration::get('PATHFILE_PATH');
        if (!$PATHFILE_PATH) {
            $PATHFILE_PATH = SdevModule::DIR.'param/';
        }
        if (is_dir($PATHFILE_PATH) && ($dir = opendir($PATHFILE_PATH))) {
            while (($file = readdir($dir)) !== false) {
                if ($file == 'parmcom.'.$this->merchant_id) {
                    return $file;
                }
            }
        }
        return false;
    }

    /**
     * Get y merhcant id
     *
     * @return bool|string
     */
    public static function getByMerchantId($merchant_id = null)
    {
        if ($merchant_id) {
            $contract_id = Db::getInstance()->getValue(
                "SELECT `id_contract`
                FROM `".pSQL(_DB_PREFIX_.static::$definition['table'])."`
                WHERE `merchant_id` = '".$merchant_id."'"
            );

            if ($contract_id) {
                return new SdevAtosContract((int)$contract_id);
            }
        }

        return false;
    }
}
