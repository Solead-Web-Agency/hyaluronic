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
class AmazonSPDefGetInventorySummariesResponse extends AmazonSPDefObject
{
    // All are optional
    /** @var AmazonSPDefGetInventorySummariesResult */
    public $payload;
    /** @var AmazonSPDefPagination */
    public $pagination;
    /** @var AmazonSPDefError[] */
    public $errors;

    protected static $complexChildren = array(
        'payload' => AmazonSPDefGetInventorySummariesResult::class,
        'pagination' => AmazonSPDefPagination::class,
    );

    protected static $listOfComplexChildren = array(
        'errors' => AmazonSPDefError::class,
    );

    /**
     * @return AmazonSPDefInventorySummary[]
     */
    public function getResult()
    {
        if ($this->payload && $this->payload->inventorySummaries) {
            return $this->payload->inventorySummaries;
        }

        return array();
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return isset($this->pagination) && isset($this->pagination->nextToken) && !empty($this->pagination->nextToken);
    }

    /**
     * @return string
     */
    public function getNextToken()
    {
        return $this->hasNext() ? $this->pagination->nextToken : '';
    }
}
