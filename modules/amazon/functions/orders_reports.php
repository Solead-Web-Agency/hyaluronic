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
require_once(dirname(__FILE__) . '/env.php');
require_once(dirname(__FILE__) . '/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.support.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.remote_cart.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.order.class.php');
require_once(dirname(__FILE__) . '/../classes/amazon.address.class.php');
require_once(dirname(__FILE__) . '/../classes/reports/amazon.report.cronjob.class.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonOrdersReport extends AmazonReportCronjob
{
    protected $logChannel = AmazonLogger::CHANNEL_ORDER_REPORT;

    protected function reportType()
    {
        return AmazonSPAPIReports::REPORT_TYPE_FBA_SHIPMENT_GENERAL;
    }

    protected function inventoryType()
    {
        return self::FILE_NAME_FBA_SHIPMENTS;
    }

    protected function runOrderConfigKey()
    {
        return 'CJ_ORDERS_REPORTS_REQUEST';
    }

    public function dispatch()
    {
        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        if (!$this->amazon_features['orders_reports'] || !$this->initialize()) {
            return;
        }

        $this->manageReports();

        $this->pddE($this->getErrors());
        $this->pddE($this->getMessages());
    }

    /**
     * Get reports and import to db
     */
    protected function manageReports()
    {
        $this->pddE(sprintf('%s(#%d): - Order Report Request:', basename(__FILE__), __LINE__));

        $this->handleReportSteps();
    }

    protected function processReport()
    {
        if (!AmazonTools::fieldExistsR(_DB_PREFIX_ . AmazonDBManager::TABLE_MKP_ORDERS, 'fulfillment_center_id')) {
            self::$errors[] = sprintf('%s(#%d): processReportFulfilledShipments - fulfillment_center_id field does not exist in table %s! Please save your configuration again', basename(__FILE__), __LINE__, AmazonDBManager::TABLE_MKP_ORDERS);
            return;
        }

        $response = AmazonTools::fileGetContents($this->file_inventory);
        if (empty($response)) {
            self::$errors[] = sprintf('%s(#%d): processReportFulfilledShipments - Report is empty !', basename(__FILE__), __LINE__);
            return;
        }

        // get data by line, then by tab for each line
        // the first line is the header
        $lines = explode("\r\n", $response);

        if (!is_array($lines) || !count($lines)) {
            self::$errors[] = sprintf('%s(#%d): processReportFulfilledShipments - Report is empty !', basename(__FILE__), __LINE__);
            return;
        }
        $this->pddE(str_repeat('-', 160), sprintf('Orders: %s lines', count($lines) - 1));

        $header = array_shift($lines);
        $header = explode("\t", $header);
        $this->pddE(str_repeat('-', 160), sprintf('Header: %s', print_r($header, true)));

        $order_id_index = array_search('amazon-order-id', $header);
        $fulfillment_center_index = array_search('fulfillment-center-id', $header);

        foreach ($lines as $line) {
            $order_info = str_getcsv($line, "\t");
            $this->pddE('Order info: ' . print_r($order_info, true));

            if (count($header) !== count($order_info)) {
                $this->pddE(
                    sprintf('Length mismatch between header and line: %d - %d', count($header), count($order_info)),
                    $order_info
                );
            }

            Db::getInstance()->update(
                AmazonDBManager::TABLE_MKP_ORDERS,
                array(
                    'fulfillment_center_id' => pSQL($order_info[$fulfillment_center_index]),
                ),
                'mp_order_id = "' . pSQL($order_info[$order_id_index]) . '"',
                1,
                true
            );
        }
    }

    protected function pddE()
    {
        $args = func_get_args();
        $backTrace = debug_backtrace();
        $caller = array_shift($backTrace);
        $fileSegment = explode('/', $caller['file']);
        $file = array_pop($fileSegment);

        foreach ($args as $arg) {
            AmazonTools::pre(array(
                sprintf('%s(#%d): ', $file, $caller['line']),
                $arg
            ))
            . nl2br(Amazon::LF);
        }
    }
}

$amazonOrdersReport = new AmazonOrdersReport(
    Tools::getValue('cron_token', Tools::getValue('amazon_token')),
    Tools::getValue('mkp')
);
$amazonOrdersReport->dispatch();
