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
class AmazonSPDefProxyReportDocument extends AmazonSPDefObject
{
    const CONTENT_TYPE_CSV = 'text/csv';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_PDF = 'application/pdf';
    const CONTENT_TYPE_PLAIN = 'text/plain';
    const CONTENT_TYPE_TAB = 'text/tab-separated-values';
    const CONTENT_TYPE_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const CONTENT_TYPE_XML = 'text/xml';

    public $content_type;
    public $compression_algo;
    public $resource_url;
}
