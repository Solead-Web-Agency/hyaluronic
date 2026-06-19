<?php
/**
 * 2010-2023 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2023 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AvailabilityLabel
{
    protected $settings = [];
    protected $isAvailableWhenOutOfStock = false;

    /**
     * @param array $settings
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param bool $isAvailableWhenOutOfStock
     */
    public function setIsAvailableWhenOutOfStock($isAvailableWhenOutOfStock)
    {
        $this->isAvailableWhenOutOfStock = $isAvailableWhenOutOfStock;
    }

    /**
     * @return bool
     */
    public function isAvailableWhenOutOfStock()
    {
        return $this->isAvailableWhenOutOfStock;
    }

    public function getStatus($product, $productQty)
    {
        $settings = $this->getSettings();

        $inStock = !empty($settings['label_in_stock_text']) ? $settings['label_in_stock_text'] : $product->available_now;
        $inStock = !empty($inStock) ? $inStock : $settings['configurationLang']['PS_LABEL_IN_STOCK_PRODUCTS'];

        $outOfStockAllowed = !empty($settings['label_out_of_stock_text']) ? $settings['label_out_of_stock_text'] : $product->available_later;
        $outOfStockAllowed = !empty($outOfStockAllowed) ? $outOfStockAllowed : $settings['configurationLang']['PS_LABEL_OOS_PRODUCTS_BOA'];

        $outOfStockDenied = !empty($settings['label_on_demand_stock_text']) ? $settings['label_on_demand_stock_text'] : $settings['configurationLang']['PS_LABEL_OOS_PRODUCTS_BOD'];

        if ($product->available_for_order != 1 && $product->online_only != 1) {
            return $outOfStockDenied;
        }

        if ($productQty > 0) {
            return $inStock;
        }

        if ($productQty == 0 && $this->isAvailableWhenOutOfStock()) {
            return $outOfStockAllowed;
        }

        if ($productQty == 0 && !$this->isAvailableWhenOutOfStock()) {
            return $outOfStockDenied;
        }

        return '';
    }
}
