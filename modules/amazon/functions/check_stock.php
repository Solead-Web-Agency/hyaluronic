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
require_once(dirname(__FILE__) . '/env.php');
require_once(dirname(__FILE__) . '/../classes/reports/amazon.report.cronjob.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.mail.logger.class.php');
if (!defined('_PS_VERSION_')) { exit; }

/**
 * Cronjob fix stock discrepancies
 */
class AmazonAutoUpdate extends AmazonReportCronjob
{
    public $amazon_id_lang = null;
    public $id_shop = 1;
    public $id_warehouse = 1;

    private $fixTheDiscrepancies = false;
    private $titleTranslations = array(
                                    AmazonConstant::REPORT_TITLE_SKU=>array(
                                        'seller-sku', 'sku-vendeur', 'SKU venditore', 'SKU del vendedor'
                                    ),
                                    AmazonConstant::REPORT_TITLE_ASIN1=>array(
                                        'asin1','ASIN 1'
                                    ),
                                    AmazonConstant::REPORT_TITLE_QUANTITY=>array(
                                        'quantity','quantit', 'Quantit', 'Cantidad', 'quantité', 'Quantità'
                                    ),
                                    );

    protected function reportType()
    {
        return AmazonSPAPIReports::REPORT_TYPE_INVENTORY_ALL_LISTING;
    }

    protected function inventoryType()
    {
        return self::FILE_NAME_OPEN_LISTINGS_DATA;
    }

    protected function runOrderConfigKey()
    {
        return 'CJ_CHECK_STOCK_REQUEST';
    }

    public function dispatch()
    {
        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        if (!$this->initialize()) {
            return;
        }

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            if (AmazonConfiguration::shopIsFeatureActive()) {
                $this->id_shop = (int)$this->context->shop->id;
            } else {
                $this->id_shop = 1;
            }
            $this->id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');
        }

        // To resolve amazon_id_lang. todo: Use marketplace ID instead of PS language
        $region = $this->spMkp->getIso();
        $marketPlaceRegion = AmazonConfiguration::get(AmazonConstant::CONFIG_LANG_TO_REGION);
        if (!is_array($marketPlaceRegion) || !count($marketPlaceRegion)) {
            die('Module is not configured yet');
        }
        $marketLang2Region = array_flip($marketPlaceRegion);
        if (!isset($marketLang2Region[$region]) || !$marketLang2Region[$region]) {
            die('No selected language, nothing to do...');
        }
        $this->amazon_id_lang = $marketLang2Region[$region];
        $this->fixTheDiscrepancies = (bool)Tools::getValue('fix');

        $this->autoUpdate();
    }

    public function autoUpdate()
    {
        $this->handleReportSteps();

        foreach (self::$errors as $error) {
            $this->logger->error($error);
            AmazonTools::p($error);
        }
        foreach (self::$messages as $msg) {
            $this->logger->info($msg);
            AmazonTools::p($msg);
        }
    }

    protected function processReport()
    {
        $fix_asin = (bool)Tools::getValue('fix-asin');
        $this->ed('processInventory()');

        require_once dirname(__FILE__) . '/../classes/reports/AmazonReportParser.php';
        $reportParser = new AmazonReportParser($this->file_inventory);
        $lines = $reportParser->parseReport();
        if (!$lines) {
            self::$errors = array_merge(self::$errors, $reportParser->getDebug());
            return array();
        }

        $this->ed(sprintf('Inventory: %s products', count($lines)));

        $amazonItems = array();

        foreach ($lines as $line) {
            if (count($line) < 4) {
                continue;
            }

            $SKU = $this->getSKUValue($line);
            $ASIN = $this->getASIN1Value($line);
            $Qty = $this->getQuantityValue($line);

            if (!$SKU || $Qty <= 0 || ($fix_asin && !$ASIN)) {
                continue;
            }

            if (($fix_asin && AmazonTools::validateSKU($SKU) && AmazonTools::validateASIN($ASIN)) || (!$fix_asin && AmazonTools::validateSKU($SKU))) {
                $amazonItems[$SKU] = $Qty;
            } else {
                $this->ed(sprintf('processInventory - %s "%s/%s"', $this->l('Wrong ASIN or SKU'), $SKU, $ASIN));
            }
        }

        if (!is_array($amazonItems) || !count($amazonItems)) {
            $this->ed('processInventory - Inventory is empty !');
        }

        $quantity_mismatch = array();

        foreach ($amazonItems as $SKU => $AmazonQty) {
            $productCheckArray = AmazonProduct::checkProduct($SKU, $this->id_shop, $this->id_lang);
            $productCheck = $productCheckArray['count'];

            if ($productCheck == 0) {
                $this->ed(sprintf('processInventory - SKU/Reference not found in your database. Please check existence of this product: "%s"', $SKU));
                continue;
            } elseif ($productCheck > 1) {
                $this->ed(sprintf('processInventory - Unable to import duplicate product "%s" - Please remove the duplicate product in your database.', $SKU));
                continue;
            }
            $product = new AmazonProduct($SKU, false, $this->amazon_id_lang, 'reference', $this->id_shop);

            if (!Validate::isLoadedObject($product)) {
                continue;
            }
            $id_product = $product->id;
            $id_product_attribute = $product->id_product_attribute;
            $quantity = 0;

            $product_options = AmazonProduct::getProductOptions($id_product, $this->id_lang, $id_product_attribute);
            $combination_options = array();

            if ($product->id_product_attribute) {
                $combination_options = AmazonProduct::getProductOptions($id_product, $this->id_lang, $id_product_attribute);
            }

            if (count($combination_options)) {
                $options = &$combination_options;
            } else {
                $options = &$product_options;
            }

            if (isset($options['fba']) && (bool)$options['fba']) {
                continue;
            }
            if (isset($options['disable']) && (bool)$options['disable']) {
                continue;
            }

            if (isset($options['force']) && (bool)$options['force']) {
                $quantity = 999;
            }

            if (!$quantity) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $quantity = Product::getQuantity((int)$id_product, $id_product_attribute ? $id_product_attribute : null);
                } else {
                    $quantity = Product::getRealQuantity($id_product, $id_product_attribute ? $id_product_attribute : null, $this->id_warehouse, $this->id_shop);
                }
            }

            if ($fix_asin) {
                AmazonProduct::updateProductOptions($id_product, $this->amazon_id_lang, 'asin1', $ASIN, $id_product_attribute);
            }

            if ($AmazonQty <= 0 && $quantity <= 0) {
                continue;
            }

            if ($AmazonQty != $quantity) {
                $quantity_mismatch[$SKU] = array('amazon' => $AmazonQty, 'prestashop' => $quantity);

                if ($this->fixTheDiscrepancies) {
                    AmazonProduct::marketplaceActionSet($quantity <= 0 ? Amazon::REMOVE : Amazon::UPDATE, $id_product);
                }
            }
        }

        if (count($quantity_mismatch)) {
            $report = null;
            $report .= 'Mismatching quantities report:' . self::LF;
            $report .= sprintf('%-40s %-12s %-12s' . self::LF, 'SKU', 'Prestashop', 'Amazon');

            foreach ($quantity_mismatch as $SKU => $report_array) {
                $report .= sprintf('%-40s %-12s %-12s' . self::LF, $SKU, $report_array['prestashop'], $report_array['amazon']);
            }

            if (Tools::getValue('fix')) {
                $report .= count($quantity_mismatch) . ' unconsistencies automatically fixed' . self::LF;
            }
            if ((bool)Configuration::get('AMAZON_EMAIL')) {
                AmazonMailLogger::message($report);
            }

            $this->er($report);
        } else {
            $this->er('No Mismatch');
        }

        return (true);
    }

    private function getSKUValue($line){
        $str = '';

        foreach($this->titleTranslations[AmazonConstant::REPORT_TITLE_SKU] as $label){
            if(isset($line[$label])){
                $str = $line[$label];
                break;
            }
        }
        
        return $str;
    }

    private function getASIN1Value($line){
        $str = '';

        foreach($this->titleTranslations[AmazonConstant::REPORT_TITLE_ASIN1] as $label){
            if(isset($line[$label])){
                $str = trim($line[$label]);
                break;
            }
        }
        
        return $str;

    }

    private function getQuantityValue($line){
        $qty = 0;

        foreach($this->titleTranslations[AmazonConstant::REPORT_TITLE_QUANTITY] as $label){
            if(isset($line[$label])){
                $qty = (int) $line[$label];
                break;
            }
        }
        
        return $qty;

    }

}

$amazonAutoUpdate = new AmazonAutoUpdate(
    Tools::getValue('cron_token'),
    Tools::getValue('mkp'),
    Tools::getValue('lang')
);
$amazonAutoUpdate->dispatch();
