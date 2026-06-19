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

require_once(dirname(__FILE__).'../../autoload.php');

class SdevAtosPaymentMethod extends SdevAtosObjectModel
{
    public $id;
    public $id_contract;
    public $name;
    public $method;
    public $authentication_key;
    public $settlement_mode;
    public $settlement_mode_version;
    public $payment_options;
    public $is_enabled;
    public $min_amount;
    public $max_amount;
    public $has_3d_secure_from;
    public $cashing_mode;
    public $first_cashing_percentage;
    public $delay;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => SdevModule::LNAME.'_payment_method',
        'primary' => 'id_payment_method',
        'multishop' => true,
        'fields' => array(
            'id_contract' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255, 'required' => true),
            'method' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255, 'required' => true),
            'authentication_key' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'settlement_mode' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 20),
            'settlement_mode_version' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'size' => 3),
            'payment_options' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'is_enabled' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'min_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'required' => true),
            'max_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'required' => true),
            'has_3d_secure_from' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'required' => true),
            'cashing_mode' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'first_cashing_percentage' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'delay' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        )
    );

    public static $unique = array(array('id_contract', 'method'));

    const PAYMENT1XCB = 'payment1xcb';
    const PAYMENT2XCB = 'payment2xcb';
    const PAYMENT3XCB = 'payment3xcb';
    const UNEUROCOM = 'unEuroCom';
    const AMEXEA = 'amexEa';
    const COFIDIS3X = 'cofidis3x';
    const COFIDIS4X = 'cofidis4x';
    const COFINOGA = 'cofinoga';
    const COFINOGA3XCB = 'cofinoga3xcb';
    const FRANFINANCE3XCB = 'franfinance3xcb';
    const FRANFINANCE4XCB = 'franfinance4xcb';
    const FACILYPAY = 'facilypay';
    const FACILYPAY3X = 'facilypay3x';
    const FACILYPAY4X = 'facilypay4x';
    const PAYPAL = 'paypal';

    /**
     * Delete by a contract ID.
     *
     * @param int $id_contract - Contract ID.
     * @return bool
     * @throws Exception
     */
    public static function deleteByIdContract($id_contract)
    {
        try {
            if (is_int($id_contract) || is_numeric($id_contract)) {
                if ($id_contract) {
                    return (bool)Db::getInstance()->delete(pSQL(self::$definition['table']), '`id_contract` = '.(int)$id_contract);
                }
                return false;
            }
            throw new Exception('The id_contract must be an integer, '.gettype($id_contract).' given !');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Gets the SIPS version associated to the payment method.
     *
     * @param int $paymentMethodId The payment method's ID.
     * @return bool|false|string|null
     */
    public static function getSipsVersion($paymentMethodId)
    {
        return Db::getInstance()->getValue(
            (new DbQuery())
                ->select('sips_version')
                ->from('sdevatos_contract', 'sc')
                ->leftJoin('sdevatos_payment_method', 'spm', 'spm.id_contract = sc.id_contract')
                ->where('id_payment_method = '.(int)$paymentMethodId)
        );
    }
}
