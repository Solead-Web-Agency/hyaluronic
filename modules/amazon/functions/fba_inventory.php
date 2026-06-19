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
require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../classes/reports/amazon.report.realtime.class.php');
if (!defined('_PS_VERSION_')) { exit; }

/**
 * Sync FBA inventory
 */
class AmazonShippingConfig extends AmazonReportRealTime
{
    protected $logChannel = AmazonLogger::CHANNEL_FBA_INVENTORY;

    protected $fba_stock_behaviour;
    protected $amazon_id_lang;

    protected function reportType()
    {
        return AmazonSPAPIReports::REPORT_TYPE_FBA_INVENTORY;
    }

    protected function inventoryType()
    {
        return self::FILE_NAME_FBA_LISTINGS_DATA;
    }

    public function __construct()
    {
        parent::__construct(Tools::getValue('instant_token'), '', Tools::getValue('amazon_lang'));

        AmazonContext::restore($this->context);

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }
    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if(!$lang)
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }

    public function dispatch()
    {
        ob_start();

        $this->fba_stock_behaviour = Tools::getValue('fba_stock_behaviour');
        $this->amazon_id_lang = Tools::getValue('amazon_lang');

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
            printf('processReport()'.nl2br(Amazon::LF));
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
            CommonTools::p(sprintf('Inventory: %s products', count($lines)));
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

        $columns = explode("\t", AmazonTools::noAccents(str_replace(' ', '-', Tools::strtolower(utf8_encode($header)))));
        $columns = array_map('trim', $columns);

        $seller_sku_idx = $this->getColumIndex($columns, array('seller-sku'));
        $asin_idx = $this->getColumIndex($columns, array('asin'));
        $condition_code_idx = $this->getColumIndex($columns, array('warehouse-condition-code'));
        $quantity_idx = $this->getColumIndex($columns, array('quantity-available'));
        $columns_count = count($columns);

        $count = 0;
        $fba_entries = array();

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            if ($count++ < 1) {
                continue;
            }

            $result = explode("\t", $line);

            if (count($result) < $columns_count) {
                continue;
            }

            $seller_sku = $result[$seller_sku_idx];
            $asin = $result[$asin_idx];
            $condition_code = $result[$condition_code_idx];
            $quantity = (int)$result[$quantity_idx];

            $fba_entries[$seller_sku] = array('sku' => $seller_sku, 'asin' => $asin, 'condition_code' => $condition_code, 'quantity' => $quantity);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%-30s%-16s%-16s%-4s'.Amazon::LF, 'SKU', 'ASIN', 'Condition Code', 'Quantity'));
            foreach ($fba_entries as $entry) {
                CommonTools::p(sprintf('%-30s%-16s%-16s%-4s'.Amazon::LF, $entry['sku'], $entry['asin'], $entry['condition_code'], $entry['quantity']));
            }
        }

        if (!is_array($fba_entries) || !count($fba_entries)) {
            $error = sprintf('%s - %s(#%d): %s - Empty FBA listing', date('c'), basename(__FILE__), __LINE__, __FUNCTION__);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$error");
            }
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Processed Items: %s', print_r($fba_entries, true)));
        }

        return ($fba_entries);
    }

    protected function afterProcessReport($fba_entries)
    {
        $id_lang = $this->context->language->id;
        $id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');
        $id_warehouse = $id_warehouse ? $id_warehouse : null;
        $updated = 0;
        $switched = 0;
        $log = true;

        if (is_array($fba_entries) && count($fba_entries)) {
            foreach ($fba_entries as $fba_entry) {
                $SKU = $fba_entry['sku'];
                $quantity = $fba_entry['quantity'];

                if (!AmazonTools::validateSKU($SKU)) {
                    $error = sprintf('%s: "%s"', $this->l('Invalid SKU'), $SKU);
                    $debug = sprintf('%s - %s(#%d): %s - %s (%s)', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }
                    continue;
                }

                $product = new AmazonProduct($SKU, false, $id_lang);

                if (!Validate::isLoadedObject($product)) {
                    $error = sprintf('%s - %s(%s)', $this->l('Unable to find product'), $product->name, $SKU);
                    $debug = sprintf('%s - %s(#%d): %s - %s (%s)', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }
                    continue;
                }
                $id_product = (int)$product->id;
                $id_product_attribute = (int)$product->id_product_attribute ? (int)$product->id_product_attribute : null;

                $options = AmazonProduct::getProductOptions($id_product, $id_lang, $id_product_attribute);

                if (is_array($options) && isset($options['disable']) && (bool)$options['disable']) {
                    $disabled = true;
                } else {
                    $disabled = false;
                }

                if (is_array($options) && isset($options['fba']) && (bool)$options['fba']) {
                    $fba = true;
                } else {
                    $fba = false;
                }
                if (!$quantity || $disabled) {
                    // Became out of stock

                    if ($fba) {
                        // Turns Product to MFN for all targets marketplaces
                        AmazonProduct::updateProductOptions($id_product, $this->amazon_id_lang, 'fba', false, $id_product_attribute);
                        $switched++;
                    }
                } elseif ($quantity && !$disabled) {
                    if (!$fba) {
                        // Turns Product to AFN for all targets marketplaces
                        AmazonProduct::updateProductOptions($id_product, $this->amazon_id_lang, 'fba', true, $id_product_attribute);
                        $switched++;
                    }
                }

                $product_quantity = Product::getRealQuantity($id_product, $id_product_attribute, $id_warehouse, $this->context->shop->id);
                $product_quantity_fba = $quantity;

                if ($product_quantity < 0) {
                    $product_quantity = 0;
                }

                if ($product_quantity > $product_quantity_fba) {
                    $delta = ($product_quantity - $product_quantity_fba) * -1;
                } else {
                    $delta = $product_quantity_fba - $product_quantity;
                }

                if ($delta == 0) {
                    if ($log) {
                        $message = sprintf('%s - %s (%d)', $SKU, $this->l('Stock already up to date'), $product_quantity);
                    }
                } elseif (StockAvailable::updateQuantity($id_product, $id_product_attribute, $delta, $this->context->shop->id)) {
                    if ($log) {
                        $message = sprintf('%s - %s (%d/%d)', $SKU, $this->l('Stock Updated'), $product_quantity, $delta);
                    }
                    $updated++;
                } else {
                    $message = ' - '.$this->l('Stock Update FAILED');
                }
                self::$messages[] = $message;

                if (count(self::$messages) > 100) {
                    $log = false;
                    self::$messages[] = $this->l('More than 100 SKU have been logged, next messages will be ignored, but the action will be performed and summarized at the end');
                }

                // Log the event
                AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $id_product);
            }
            self::$messages[] = $message = sprintf('%d %s - %d %s', is_array($switched) ? count($switched) : 0, $this->l('FBA offers switched'), $updated, $this->l('Stock movements'));

            $debug = sprintf('%s - %s(#%d): %s - %s', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $message);

            if (Amazon::$debug_mode) {
                CommonTools::p($debug);
            }
            if ($updated || $switched) {
                if ($this->fba_stock_behaviour == AmazonConstant::FBA_STOCK_BEHAVIOUR_SYNC) {
                    Configuration::updateValue('AMAZON_FBA_STOCK_BEHAVIOUR', AmazonConstant::FBA_STOCK_BEHAVIOUR_SYNC);
                }
            }
        } else {
            $error = $this->l('FBA inventory is empty');
            $debug = sprintf('%s - %s(#%d): %s - %s (%s)', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p($debug);
            }
        }
    }

    private function getColumIndex($columns, $keys)
    {
        $array_keys = array_intersect($columns, $keys);

        // Header, display to the user he doesn't have merchant shipping group
        if (!is_array($array_keys) || !count($array_keys)) {
            $error = sprintf('%s: %s', $this->l('Missing Column'), print_r($keys, true));
            $debug = sprintf('%s - %s(#%d): %s - %s', date('c'), basename(__FILE__), __LINE__, __FUNCTION__, $error);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }
        $columns_keys = array_flip($columns);

        $result = $columns_keys[reset($array_keys)];

        if (!is_numeric($result)) {
            return(false);
        } else {
            return($result);
        }
    }
}

$amazonShippingConfig = new AmazonShippingConfig();
$amazonShippingConfig->dispatch();
