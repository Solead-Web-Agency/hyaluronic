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
require_once(dirname(__FILE__) . '/../functions/env.php');
require_once(dirname(__FILE__) . '/../classes/amazon.tools.class.php');
require_once(dirname(__FILE__) . '/../classes/reports/amazon.report.realtime.class.php');
if (!defined('_PS_VERSION_')) { exit; }

/**
 * Download shipping templates
 */
class AmazonShippingConfig extends AmazonReportRealTime
{
    public static $offers = array();
    public static $process = false;
    public static $end = false;

    protected $debugFilePrefix = 'download_reports';

    protected function reportType()
    {
        return AmazonSPAPIReports::REPORT_TYPE_INVENTORY_ALL_LISTING;
    }

    protected function inventoryType()
    {
        return self::FILE_NAME_ACTIVE_LISTINGS_DATA;
    }

    public function __construct()
    {
        parent::__construct(Tools::getValue('instant_token'), '', Tools::getValue('amazon_lang'));

        ob_start();
        AmazonContext::restore($this->context);
        ob_get_clean();
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if (!$lang)
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }

    public function dispatch()
    {
        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
        $token = Tools::getValue('instant_token');

        if (!$token || $token != Configuration::get(AmazonConstant::GB_CONFIG_INSTANT_TOKEN, null, 0, 0)) {
            print 'Wrong token';
            die;
        }

        $this->getShippingGroupNames();
    }

    public function getShippingGroupNames()
    {
        ob_start();

        $continue = false;
        $nextStep = '';
        $carrierData = array();

        if ($this->initialize()) {
            $this->handleReportSteps();
            $nextStep = $this->nextStep;
            $continue = (bool)$nextStep;
            $carrierData = $this->nextStepData;
        }

        echo json_encode(array(
            'error' => (bool)count(self::$errors),
            'errors' => self::$errors,
            'message' => (bool)count(self::$messages),
            'messages' => self::$messages,
            'continue' => $continue,
            'step' => $nextStep,
            'data' => $carrierData,
            'debug' => Amazon::$debug_mode,
            'output' => ob_get_clean()
        ));
    }

    protected function processReport()
    {
        if (Amazon::$debug_mode) {
            printf('processReport()' . nl2br(Amazon::LF));
        }

        if (($result = AmazonTools::fileGetContents($this->file_inventory)) === false) {
            $error = $this->l('Unable to read input file');
            $debug = sprintf('%s - %s(#%d): %s - %s (%s)', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        if ($result == null or empty($result)) {
            $error = $this->l('Inventory is empty !');
            $debug = sprintf('%s - %s(#%d): %s - %s (%s)', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        $lines = explode(Amazon::LF, $result);

        if (!is_array($lines) || !count($lines)) {
            $error = $this->l('Inventory is empty !');
            $debug = sprintf('%s - %s(#%d): %s - %s (%s)', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(str_repeat('-', 160));
            CommonTools::p(sprintf('Inventory: %s products' . nl2br(Amazon::LF), count($lines)));
        }

        $header = reset($lines);

        if (!Tools::strlen($header)) {
            $error = $this->l('No header, file might be corrupted');
            $debug = sprintf('%s - %s(#%d): %s - %s', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $error);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        $columns = explode("\t", AmazonTools::noAccents(Tools::strtolower(utf8_encode($header))));
        $to_search = array('merchant-shipping-group', 'gruppo-spedizione-venditore', 'groupe-expedition-vendeur', 'haendlerversandgruppe');
        $array_keys = array_intersect($columns, $to_search);

        // Header, display to the user he doesn't have merchant shipping group
        if (!is_array($array_keys) || !count($array_keys)) {
            $error = $this->l('No merchant shipping groups detected, it seems your are not using shipping templates');
            $debug = sprintf(
                '%s - %s(#%d): %s - %s - %s',
                date('c'),
                basename(__FILE__),
                __LINE__,
                __FUNCTION__,
                $error,
                print_r($columns));
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }
        $columns_keys = array_flip($columns);
        $merchant_shipping_group_key = $columns_keys[reset($array_keys)];

        $count = 0;
        $group_names = array();
        $matching_errors = array();
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            if ($count++ < 1) {
                continue;
            }

            $result = explode("\t", $line);

            if (count($result) < $merchant_shipping_group_key + 1) {
                continue;
            }
            $merchant_group_name = utf8_encode($result[$merchant_shipping_group_key]);

            if (!preg_match('/^[\w\s]*$/iu', $merchant_group_name)) {
                if (isset($matching_errors[$merchant_shipping_group_key])) {
                    continue;
                } else {
                    $matching_errors[$merchant_shipping_group_key] = true;
                }
                $error = sprintf(
                    $this->l('Invalid shipping group name: "%s", shipping group names must only containing text'),
                    $merchant_group_name
                );
                self::$errors[] = $error;

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf(
                        '%s - %s(#%d): %s - %s',
                        date('c'),
                        basename(__FILE__),
                        __LINE__,
                        __FUNCTION__,
                        $error
                    ));
                }
                continue;
            }
            $group_key = AmazonTools::toKey($result[$merchant_shipping_group_key]);
            $group_names[$group_key] = $merchant_group_name;
        }

        if (!is_array($group_names) || !count($group_names)) {
            $error = sprintf('%s - %s(#%d): %s - Not any group name found', date('c'), basename(__FILE__), __LINE__, __FUNCTION__);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$error");
            }
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Processed Items: %s', print_r($group_names, true)));
        }

        return ($group_names);
    }

    protected function afterProcessReport($group_names)
    {
        if (is_array($group_names) && count($group_names)) {
            $configured_group_names = AmazonConfiguration::get('shipping_groups');

            if (is_array($configured_group_names) && count($configured_group_names)) {
                $configured_group_names[$this->spMkp->getIso()] = $group_names; // Legacy support. todo: Move to marketplace below
                $configured_group_names[$this->spMkp->getMarketplaceId()] = $group_names;
            } else {
                $configured_group_names = array(
                    $this->spMkp->getIso() => $group_names, // Legacy support. todo: Move to marketplace below
                    $this->spMkp->getMarketplaceId() => $group_names,
                );
            }
            AmazonConfiguration::updateValue('shipping_groups', $configured_group_names);

            self::$messages[] = $message = sprintf('%d / %s', count($group_names), $this->l('shipping groups have been retrieve with success'));
            $debug = sprintf('%s - %s(#%d): %s - %s', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $message);

            if (Amazon::$debug_mode) {
                CommonTools::p($debug);
            }
        } else {
            $error = $this->l('Not any existing shipping groups have been found from the inventory');
            $debug = sprintf('%s - %s(#%d): %s - %s (%s)', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p($debug);
            }
        }
    }
}

$amazonShippingConfig = new AmazonShippingConfig();
$amazonShippingConfig->dispatch();
