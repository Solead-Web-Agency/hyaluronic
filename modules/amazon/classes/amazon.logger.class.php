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
if (!defined('_PS_VERSION_')) { exit; }

require_once dirname(__FILE__) . '/seller_partner/IAmazonSellerPartnerLogger.php';

class AmazonLogger implements IAmazonSellerPartnerLogger
{
    const LIFE_TIME = 7776000;    // 90 * 24 * 3600;

    const CHANNEL_PRIME = 'prime';
    const CHANNEL_VCS = 'vcs';
    const CHANNEL_ORDER_LISTING = 'order_listing';
    const CHANNEL_ORDER_IMPORT = 'order_import';
    const CHANNEL_ORDER_UPDATE_STATUS = 'order_fulfillment';
    const CHANNEL_ORDER_CANCELLATION = 'order_cancellation';
    const CHANNEL_REPORT = 'report';
    const CHANNEL_ORDER_REPORT = 'order_report';
    const CHANNEL_PRODUCT_IMPORT = 'product_import';
    const CHANNEL_PRODUCT_EXPORT = 'product_export';
    const CHANNEL_FBA_INVENTORY = 'fba_inventory';
    const CHANNEL_FBA_MCF = 'fba_mcf';
    const CHANNEL_SP_API_AUTHORIZATION = 'sp_api_auth';
    const CHANNEL_SP_API_SELLERS = 'sp_api_sellers';
    const CHANNEL_SP_API_CATALOG = 'sp_api_catalog';

    const CHANNEL_SP_API_FEEDS = 'sp_api_feeds';
    const CHANNEL_SP_API_FEED_RESULT = 'sp_api_feed_result';
    const CHANNEL_SP_API_FBA = 'sp_api_fba';

    const SUB_VCS_UPLOAD = 'upload';
    const SUB_VCS_GET = 'get';
    const SUB_OI_ACK = 'acknowledge';
    const SUB_OU_FULFILLMENT = 'fulfillment';
    const SUB_PE_DELETION = 'deletion';
    const SUB_PE_IMAGES = 'images';
    const SUB_PE_PRICES = 'prices';
    const SUB_PE_OVERRIDES = 'overrides';
    const SUB_PE_INVENTORY = 'inventory';
    const SUB_PE_RELATIONSHIP = 'relationship';
    const SUB_PE_PRODUCT_DATA = 'creation';
    const SUB_FBA_INVENTORY = 'inventory';
    const SUB_FBA_CREATE_FULFILLMENT_ORDER = 'create_order';
    const SUB_FBA_GET_FULFILLMENT_ORDER = 'get_order';
    const SUB_FBA_GET_FULFILLMENT_ORDERS = 'get_orders';
    const SUB_FBA_CANCEL_FULFILLMENT_ORDER = 'cancel_order';

    protected $filePath;
    protected $disable = false; // Disable logging function

    public function clearOldLogs($baseOnFileName = false, $lifeTime = self::LIFE_TIME)
    {
        $recursiveIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(_PS_MODULE_DIR_ . 'amazon/logs', FilesystemIterator::SKIP_DOTS)
        );

        foreach ($recursiveIterator as $file) {
            $isLogFile = $file->isFile() && $file->getExtension() === 'log';
            /** @var SplFileObject $file */
            if (!$baseOnFileName && $isLogFile && (time() - $file->getMTime()) > $lifeTime) {
                unlink($file);
            }
            $fileTime = strtotime(substr($file->getFilename(), 0, 10));
            if ($baseOnFileName && $isLogFile && (time() - $fileTime) > $lifeTime) {
                unlink($file);
            }
        }
    }

    /**
     * Clear old logs with the number of days in the setting. Other cases use 90 days as the default.
     * @return void
     */
    protected function checkAndClearLogs()
    {
        $daySetting = (int)AmazonConfiguration::get(AmazonConstant::CONFIG_AMZ_CLEAR_LOGS_DAY);
        if ($daySetting !== 0) {
            $lifeTime = $daySetting * 24 * 3600;
            $this->clearOldLogs(true, $lifeTime);
        } else {
            $this->clearOldLogs(true);
        }

        // Clean up vcs files
        $this->checkAndCleanUpVCSFiles();
    }

    /**
     * AmazonLogger constructor.
     * @param string|array $locations
     */
    public function __construct($locations, $overrideFileName = '')
    {
        $logPath = $this->constructLogPath($locations);
        if ($overrideFileName) {
            $logPath .= "$overrideFileName.log";
        } else {
            $today = date('Y-m-d');
            $logPath .= "$today.log";
        }

        $this->filePath = $logPath;
        $this->disable = (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_AMZ_DISABLE_LOGGING);

        // Clear old logs every run any logs.
        $this->checkAndClearLogs();
    }

    public function debug($message, $context = array())
    {
        $this->log($message, self::DEBUG, $context);
    }

    public function info($message, $context = array())
    {
        $this->log($message, self::INFO, $context);
    }

    public function warn($message, $context = array())
    {
        $this->log($message, self::WARNING, $context);
    }

    public function error($message, $context = array())
    {
        $this->log($message, self::ERROR, $context);
    }

    public function success($message, $context = array())
    {
        $this->log($message, self::SUCCESS, $context);
    }

    protected function log($message, $level, $context)
    {
        if ($this->filePath && !$this->disable) {
            $content = $context ?
                sprintf("%s > %s > %s\n%s\n", date('H:i:s', time()), $level, $message, print_r($context, true)) :
                sprintf("%s > %s > %s\n", date('H:i:s', time()), $level, $message);
            @file_put_contents($this->filePath, $content, FILE_APPEND);
        }
    }

    protected function constructLogPath($locations)
    {
        if (is_string($locations)) {
            $locations = array($locations);
        }

        $logPath = dirname(dirname(__FILE__)) . "/logs/";
        foreach ($locations as $location) {
            $logPath .= "$location/";
        }

        return $logPath;
    }

    /**
     * Clear raw/pdf files
     * @param $type
     * @param $lifeTime
     * @param $path
     * @return void
     */
    public static function clearFilesByExt($type , $lifeTime, $path = 'amazon/vidr')
    {

        $recursiveIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(_PS_MODULE_DIR_ . $path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($recursiveIterator as $file) {
            $isValidFile = $file->isFile() && $file->getExtension() === $type;
            if ($isValidFile && (time() - $file->getMTime()) > $lifeTime) {
                unlink($file);
            }
        }
    }

    /**
     * Clean up the Raw/PDF files with the number of days in the setting.
     * @return void
     */
    protected function checkAndCleanUpVCSFiles()
    {
        /* Clear pdf files */
        $daySetting = (int)AmazonConfiguration::get(AmazonConstant::CONFIG_AMZ_VCS_CLEAN_UP);
        if ($daySetting !== 0) {
            $lifeTime = $daySetting * 24 * 3600;
            $this->clearFilesByExt('pdf' ,$lifeTime);
        }
        // Other case: If the value is 0, the files will be handled by the customer's own scripts.

        /* Clear raw files - after 5 days */
        $rawLifeTime = 5 * 24 * 3600;
        $this->clearFilesByExt('raw', $rawLifeTime); // VCS
        $this->clearFilesByExt('raw', $rawLifeTime, 'amazon/reports'); // Report (Active, FBA, Open, Product listing data...)
    }
}
