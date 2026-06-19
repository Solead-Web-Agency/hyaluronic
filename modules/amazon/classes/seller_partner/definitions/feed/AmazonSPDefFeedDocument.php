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

/**
 * Real response has this structure:
 * payload: {
 *   compression_algo: string
 *   resource_url: string
 * }
 */
if (class_exists(Module::class)) {
    if (!defined('_PS_VERSION_')) {
        exit;
    }
}
class AmazonSPDefFeedDocument extends AmazonSPDefObject
{
    const COMPRESSION_ALG_GZIP = 'GZIP';

    // Required
    public $feedDocumentId; // Not exist in real payload result
    public $url;    // doc
    public $resource_url;   // actual

    // Optional
    public $compressionAlgorithm;   // doc
    public $compression_algo;   // actual

    public function getResourceUrl()
    {
        return $this->resource_url ? $this->resource_url : ($this->url ? $this->url : '');
    }

    public function getCompressionAlgorithm()
    {
        return $this->compression_algo ? $this->compression_algo : ($this->compressionAlgorithm ? $this->compressionAlgorithm : '');
    }

    public function gzipCompression()
    {
        return $this->getCompressionAlgorithm() === self::COMPRESSION_ALG_GZIP;
    }
}
