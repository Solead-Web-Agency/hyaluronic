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
abstract class AmazonSPAPIReports extends AmazonSellerPartnerAPI
{
    const REPORT_TYPE_INVENTORY_ALL_LISTING = 'GET_MERCHANT_LISTINGS_ALL_DATA'; // details
    const REPORT_TYPE_INVENTORY_LISTING_LITE = 'GET_MERCHANT_LISTINGS_DATA_LITE'; // details lite
    const REPORT_TYPE_INVENTORY_LISTING = 'GET_MERCHANT_LISTINGS_ALL_DATA'; // summary
    const REPORT_TYPE_FBA_SHIPMENT_GENERAL = 'GET_AMAZON_FULFILLED_SHIPMENTS_DATA_GENERAL';
    const REPORT_TYPE_FBA_INVENTORY = 'GET_AFN_INVENTORY_DATA';
    const REPORT_TYPE_VCS = 'GET_FLAT_FILE_VAT_INVOICE_DATA_REPORT';
    const REPORT_TYPE_AFN_INVENTORY_DATA = 'GET_AFN_INVENTORY_DATA';
    const REPORT_TYPE_GET_FBA_MYI_ALL_INVENTORY_DATA = 'GET_FBA_MYI_ALL_INVENTORY_DATA';
    const REPORT_TYPE_GET_SELLER_PERFORMANCE = 'GET_V1_SELLER_PERFORMANCE_REPORT';
    const REPORT_TYPE_GET_XML_BROWSE_TREE_DATA = 'GET_XML_BROWSE_TREE_DATA';

    protected $reportType;

    public function isValid()
    {
        return true;
    }
}
