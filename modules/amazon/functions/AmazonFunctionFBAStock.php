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
/* Is ajax/cron file */
require_once(dirname(__FILE__) . '/../functions/AmazonFunction.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonFunctionFBAStock extends AmazonFunction
{
    public static $warnings = array();
    public static $log = array();

    protected $logChannel = AmazonLogger::CHANNEL_FBA_INVENTORY;

    public function __construct()
    {
        parent::__construct();

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $employee = null;
            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                die($this->l('Wrong Employee, please save the module configuration'));
            }

            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
    }

    protected function initialize()
    {
        if (!$this->functionAuthorization()) {
            die($this->l('Wrong Token'));
        }

        $spConnector = $this->initSpConnector();
        if (!$spConnector || !$spConnector->isAuthenticated()) {
            die('Missing region / marketplace!');
        }
    }

    protected function notifyTheResult($id_lang)
    {
        if (count(self::$warnings) || count(self::$log)) {
            $events = '';
            $errors = '';
            $mailSend = false;

            if (self::$log) {
                $events = $this->l('Events') . ": " . nl2br(Amazon::LF);
                $mailSend = (bool)Configuration::get('AMAZON_EMAIL');
                foreach (self::$log as $log) {
                    $events .= $log . nl2br(Amazon::LF);
                }
            }

            if (self::$warnings) {
                $errors = $this->l('Warnings') . ": " . nl2br(Amazon::LF);
                foreach (self::$warnings as $warning) {
                    $errors .= $warning . nl2br(Amazon::LF);
                }
            }

            if ($mailSend) {
                Mail::Send(
                    $id_lang, // id_lang
                    'fba_stocks', // template
                    $this->l('Amazon FBA: You have new events from your store'), // subject
                    array(
                        '{events}' => $events,
                        '{errors}' => $errors,
                    ), // templateVars
                    Configuration::get('PS_SHOP_EMAIL'), // to
                    null, // To Name
                    null, // From
                    null, // From Name
                    null, // Attachment
                    null, // SMTP
                    $this->path . 'mails/'
                );
            }
        }
    }
}
