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
class AmazonSPDefItemIdentifier extends AmazonSPDefObject
{
    // All are required
    public $identifierType; // string
    public $identifier;     // string

    public function getSKU()
    {
        if ($this->isTypeSKU()) {
            return $this->identifier;
        }

        return '';
    }

    public function getEAN()
    {
        if ($this->isTypeEAN()) {
            return $this->identifier;
        }

        return '';
    }

    public function getUPC()
    {
        if ($this->isTypeUPC()) {
            return $this->identifier;
        }

        return '';
    }

    public function getISBN()
    {
        if ($this->isTypeISBN()) {
            return $this->identifier;
        }

        return '';
    }

    public function getASIN()
    {
        if ($this->isTypeASIN()) {
            return $this->identifier;
        }

        return '';
    }

    public function getGTIN()
    {
        if ($this->isTypeGTIN()) {
            return $this->identifier;
        }

        return '';
    }

    public function getJAN()
    {
        if ($this->isTypeJAN()) {
            return $this->identifier;
        }

        return '';
    }

    public function getMINSAN()
    {
        if ($this->isTypeMINSAN()) {
            return $this->identifier;
        }

        return '';
    }

    protected function isTypeSKU()
    {
        return strtoupper($this->identifierType) == 'SKU';
    }

    protected function isTypeEAN()
    {
        return strtoupper($this->identifierType) == 'EAN';
    }

    protected function isTypeUPC()
    {
        return strtoupper($this->identifierType) == 'UPC';
    }

    protected function isTypeISBN()
    {
        return strtoupper($this->identifierType) == 'ISBN';
    }

    protected function isTypeASIN()
    {
        return strtoupper($this->identifierType) == 'ASIN';
    }

    protected function isTypeGTIN()
    {
        return strtoupper($this->identifierType) == 'GTIN';
    }

    protected function isTypeJAN()
    {
        return strtoupper($this->identifierType) == 'JAN';
    }

    protected function isTypeMINSAN()
    {
        return strtoupper($this->identifierType) == 'MINSAN';
    }
}
