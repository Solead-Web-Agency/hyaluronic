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

class AmazonFBAManager extends AmazonFunctionFBAStock
{
    public function manageStocks()
    {
        $this->initialize();

        $forceUpdate = (bool)AmazonTools::getValue('force', false);
        $anticipate = (bool)AmazonTools::getValue('anticipate', false);
        $days = AmazonTools::getValue('days', 1);
        $page_limit = (int)AmazonTools::getValue('limit');
        $ignore_fba_value = (bool)AmazonTools::getValue('ignore-fba-value', false);

        $this->ed(
            'Parameters:',
            sprintf("Days - '%s'", $days),
            sprintf("Anticipate - '%s'", $anticipate ? 'true' : 'false'),
            sprintf("Ignore FBA Value - '%s'", $ignore_fba_value ? 'true' : 'false'),
            sprintf("Force Update - '%s'", $forceUpdate ? 'true' : 'false')
        );

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
                $sku = $item->sellerSku;
                $product = new AmazonProduct($sku, false, $id_lang, 'reference', $this->context->shop->id);
                if (!Validate::isLoadedObject($product)) {
                    AmazonFBAManager::$warnings[] = sprintf('%s - %s(%s)', $this->l('Unable to find product'), $product->name, $sku);
                    continue;
                }

                $this->elc('Product - ' . $product->name, get_object_vars($product));

                if ($anticipate && $item->totalQuantity && $item->totalQuantity > $item->getFulfillableQuantity()) {
                    $quantityConsidered = $item->totalQuantity;
                } else {
                    $quantityConsidered = $item->getFulfillableQuantity();
                }

                $this->ed(
                    "Reference: " . $sku,
                    "Quantity Considered: " . (int)$quantityConsidered,
                    "Ignore FBA Value - " . $ignore_fba_value ? 'true' : 'false',
                    'Force Update - ' . $forceUpdate ? 'true' : 'false'
                );

                if ($quantityConsidered == 0) {
                    // Became out of stock

                    // Product is set as FBA
                    //
                    if ($forceUpdate) {
                        // Turns Product to MFN for all targets marketplaces
                        // Log the event
                        AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $product->id);
                        AmazonFBAManager::$log[] = sprintf('%s(%s) - %s', $product->name, $sku, $this->l('Product became out of stock, switching to MFN'));
                    }
                } else {
                    // FBA - In Stock
                    if ($product->id_product_attribute) {
                        $options = AmazonProduct::getProductOptions($product->id, $id_lang, $product->id_product_attribute);
                    } else {
                        $options = AmazonProduct::getProductOptions($product->id, $id_lang);
                    }
                    if (!is_array($options) && !count($options) && !max($options)) {
                        $options = AmazonProduct::getDefaultOptions();
                    }

                    // Product is not set as FBA, but Amazon have it in stock
                    if ((!$options['fba'] || $forceUpdate) && (is_numeric($options['fba_value']) || $ignore_fba_value)) {
                        // Turns Product to AFN for all targets marketplaces
                        // ignore fba value if sets
                        AmazonProduct::updateProductOptions($product->id, $id_lang, 'fba', 1, $product->id_product_attribute, false);
                        $this->elc('Product Options:', $options);

                        // Log the event
                        AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $product->id);
                        AmazonFBAManager::$log[] = sprintf('%s(%s) - %s', $product->name, $sku, $this->l('Product in stock (FBA), switching to AFN'));
                    } elseif (!$options['fba'] && (!$options['fba_value'] || !$ignore_fba_value)) {
                        // ignore fba value if sets
                        AmazonProduct::updateProductOptions($product->id, $id_lang, 'fba', 0, $product->id_product_attribute, false);
                        $this->elc('Product Options:', $options);

                        // Log the event
                        AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $product->id);
                        AmazonFBAManager::$log[] = sprintf('%s(%s) - %s', $product->name, $sku, $this->l('Product in stock (FBA), but not valued for FBA, switching to MFN'));
                    }
                }
            }
        }

        $this->ed(AmazonFBAManager::$log, AmazonFBAManager::$warnings);

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

$amazonFBAManager = new AmazonFBAManager();
$amazonFBAManager->manageStocks();
