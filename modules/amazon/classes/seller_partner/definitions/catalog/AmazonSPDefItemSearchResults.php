<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (class_exists(Module::class)) {
    if (!defined('_PS_VERSION_')) {
        exit;
    }
}
class AmazonSPDefItemSearchResults extends AmazonSPDefObject
{
    // All required
    public $numberOfResults;    // integer
    /** @var AmazonSPDefPagination */
    public $pagination;
    /** @var AmazonSPDefRefinements */
    public $refinements;
    /** @var AmazonSPDefCatalogItem[] */
    public $items;

    protected static $complexChildren = array(
        'pagination' => AmazonSPDefPagination::class,
        'refinements' => AmazonSPDefRefinements::class,
    );

    protected static $listOfComplexChildren = array(
        'items' => AmazonSPDefCatalogItem::class,
    );

    public function hasItems()
    {
        return $this->numberOfResults && count($this->items);
    }

    /**
     * @return AmazonSPDefCatalogItem|null
     */
    public function getFirstItem()
    {
        return $this->hasItems() ? $this->items[0] : null;
    }
}
