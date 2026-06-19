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
require_once(dirname(__FILE__).'/../functions/AmazonFunctionFBAStock.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonFBAStockSynch extends AmazonFunctionFBAStock
{
    public function manageStocks()
    {
        $this->initialize();

        $days = (int)AmazonTools::getValue('days', 1);
        $page_limit = (int)AmazonTools::getValue('limit');
        $id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');
        $id_warehouse = $id_warehouse ?: null;

        $spConnector = $this->spConnector;
        $id_lang = $spConnector->getPSLanguage();
        $inventoryApi = new AmazonSPAPIGetInventorySummaries(
            $spConnector, $spConnector->getMarketplaceId(),
            $page_limit ? '' : date('Y-m-d', time() - (86400 * $days)),
            array(), $this->logger
        );
        $inventory = $inventoryApi->apiGetAll($page_limit ?: 20);
        foreach ($inventory as $item) {
            if ($item instanceof AmazonSellerPartnerResponse) {
                self::$warnings[] = $item->getErrorMsg();
                break;
            }
            
            if ($item instanceof AmazonSPDefInventorySummary) {
                $SKU = $item->sellerSku;
                $product = new AmazonProduct($SKU, false, $id_lang, 'reference', $this->context->shop->id);

                if (!Validate::isLoadedObject($product)) {
                    AmazonFBAStockSynch::$warnings[] = sprintf('%s - %s(%s)', $this->l('Unable to find product'), $product->name, $SKU);
                    continue;
                }
                if (!($options = AmazonProduct::getProductOptions($product->id, $id_lang, $product->id_product_attribute))) {
                    continue;
                }

                if ($item->getFulfillableQuantity() == 0) {
                    // Became out of stock
                    if ($options['fba']) {
                        // Turns Product to MFN for all targets marketplaces
                        AmazonProduct::updateProductOptions($product->id, $id_lang, 'fba', false, $product->id_product_attribute, false);
                    }
                    // Log the event
                    AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $product->id);
                    $message = sprintf('%s(%s) - %s', $product->name, $SKU, $this->l('Product became out of stock'));
                } else {
                    // FBA - In Stock
                    if (!$options['fba']) {
                        // Turns Product to AFN for all targets marketplaces
                        AmazonProduct::updateProductOptions($product->id, $id_lang, 'fba', true, $product->id_product_attribute, false);
                    }
                    // Log the event
                    AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $product->id);
                    $message = sprintf('%s(%s) - %s', $product->name, $SKU, $this->l('Product in stock (FBA)'));
                }

                $product_quantity = Product::getRealQuantity($product->id, $product->id_product_attribute, $id_warehouse, $this->context->shop->id);
                $product_quantity_fba = $item->getFulfillableQuantity();
                if ($product_quantity < 0) {
                    $product_quantity = 0;
                }

                if ($product_quantity > $product_quantity_fba) {
                    $delta = ($product_quantity - $product_quantity_fba) * -1;
                } else {
                    $delta = $product_quantity_fba - $product_quantity;
                }

                if ($delta == 0) {
                    AmazonFBAStockSynch::$log[] = sprintf('%s - %s (%d)', $message, $this->l('Stock already up to date'), $product_quantity);
                } elseif (StockAvailable::updateQuantity($product->id, $product->id_product_attribute, $delta, $this->context->shop->id)) {
                    AmazonFBAStockSynch::$log[] = sprintf('%s - %s (%d/%d)', $message, $this->l('Stock Updated'), $product_quantity, $delta);
                } else {
                    AmazonFBAStockSynch::$log[] = $message.' - '.$this->l('Stock Update FAILED');
                }
            }
        }

        $this->ed(self::$log, self::$warnings);

        $this->notifyTheResult($id_lang);
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if(!$lang)
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }
}

$amazonFBAStockSynch = new AmazonFBAStockSynch();
$amazonFBAStockSynch->manageStocks();
