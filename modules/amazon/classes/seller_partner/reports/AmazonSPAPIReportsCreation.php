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

class AmazonSPAPIReportsCreation extends AmazonSPAPIReports
{
    private $marketplaceIds;
    private $dataStartTime;
    private $dataEndTime;
    private $reportOptions;

    public function __construct(
        $connector,
        $marketplaceIds,
        $reportType,
        $dataStartTime = null,
        $reportOptions = null,
        $devMode = false,
        $isSandBox = false
    )
    {
        parent::__construct($connector, self::API_TYPE_REPORTS_CREATE, null, $devMode, $isSandBox);
        $this->reportType = $reportType;
        if (is_string($marketplaceIds)) {
            $marketplaceIds = array($marketplaceIds);
        }
        $this->marketplaceIds = $marketplaceIds;
        if (is_int($dataStartTime)) {
            $dataStartTime = date('c', $dataStartTime);
        }
        $this->dataStartTime = $dataStartTime;
        $this->reportOptions = $reportOptions;
    }

    public function apiCreate()
    {
        if ($this->isValid()) {
            return $this->doRequest([
                'marketplace_ids' => $this->marketplaceIds,
                'report_type' => $this->reportType,
                'data_start_time' => $this->dataStartTime,
                'report_options' => $this->reportOptions,
            ]);
        }

        return AmazonSellerPartnerResponse::badRequest(400, 'Incorrect report request!');
    }

    public function parsePayload($payload)
    {
        return new AmazonSPDefCreateReportResponse($payload);
    }
}
