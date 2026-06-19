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

require_once(dirname(__FILE__) . '/../amazon.php');
require_once(dirname(__FILE__) . '/amazon.logger.class.php');

abstract class AmazonReport extends Amazon
{
    const STEP_CREATE_REPORT = 'create_report';
    const STEP_CONFIRM_REPORT = 'confirm_report';
    const STEP_FETCH_REPORT = 'fetch_report';
    const STEP_PROCESS_REPORT = 'process_report';

    const CONFIRM_REPORT_STATUS_FAILED = 'confirm_failed';
    const CONFIRM_REPORT_STATUS_STAND_BY = 'confirm_stand_by';
    const CONFIRM_REPORT_STATUS_SUCCESS = 'confirm_success';

    // This may cause overlap, 2 reports uses the same file.
    // todo: Find a way to reuse file when many actions use same report
    const FILE_NAME_ACTIVE_LISTINGS_DATA = 'active_listings_data';
    const FILE_NAME_PRODUCT_LISTINGS_DATA = 'product_listings_data';
    const FILE_NAME_OPEN_LISTINGS_DATA = 'open_listings_data';
    const FILE_NAME_FBA_SHIPMENTS = 'fba_shipments_general';
    const FILE_NAME_FBA_LISTINGS_DATA = 'fba_listings_data';
    const FILE_NAME_VCS_DATA = 'vat_invoice_data_report';
    const EXPIRE = 18000; //5 hours

    protected $token;
    protected $amzMarketplace;
    /** @var mixed|null country iso in if cron, int if realtime. todo: Use $amzMarketplace instead */
    protected $amzLang;

    // todo: This should be non-static
    public static $errors = array();
    public static $messages = array();

    public $storagePath = null;
    protected $storageDir = 'reports';
    public $file_inventory = null;

    // Run order
    protected $step;
    protected $stepData;
    protected $nextStep;
    protected $nextStepData = array();
    protected $autoStartOver = true;
    protected $realtimeLatency; // Waiting for report to be ready after created
    protected $lastRequestTime; // The time of last report request

    /** @var AmazonLogger */
    protected $logger;
    /** @var string|array */
    protected $logChannel;
    /** @var AmazonSPConnectorPSMkp */
    protected $spMkp;

    protected $debugFilePrefix = 'amazon.report.class';

    public function getErrors()
    {
        return self::$errors;
    }

    public function getMessages()
    {
        return self::$messages;
    }

    public function __construct($token, $amzMarketplace, $amzLang = null)
    {
        $this->token = $token;
        $this->amzMarketplace = $amzMarketplace;
        $this->amzLang = $amzLang;
        $this->logger = new AmazonLogger($this->logChannel ?: AmazonLogger::CHANNEL_REPORT);
        parent::__construct();
    }

    abstract protected function reportType();

    abstract protected function inventoryType();

    public function initialize()
    {
        return $this->initInventory();
    }

    public function handleReportSteps($allTime = true)
    {
        $this->resolveCurrentStepInfo();
        // todo: Revert $lastRequestTime when the whole flow does not success
        $startTime = $allTime ? null : $this->lastRequestTime;

        switch ($this->step) {
            default:
                $reportId = $this->spCreateReport($this->reportType(), $startTime);
//                $reportId = '76281019264';
                if ($reportId) {
                    $this->nextStepData = array('report_id' => $reportId);
                    $this->lastRequestTime = time();
                    $this->resolveNextStepInfo();
                    $this->addLatency();
                }
                break;
            case self::STEP_CONFIRM_REPORT:
                $reportId = $this->stepData['report_id'];
                if (!$reportId) {
                    self::$errors[] = 'Invalid request: No report ID provided!';
                    $this->autoStartOverIfNeeded();
                    break;
                }
                $confirmReport = $this->spConfirmReport($reportId);
                switch ($confirmReport['status']) {
                    case self::CONFIRM_REPORT_STATUS_STAND_BY:
                        $this->nextStepData = $confirmReport['data'];
                        $this->resolveNextStepInfo(self::STEP_CONFIRM_REPORT);
                        $this->addLatency();
                        break;
                    case self::CONFIRM_REPORT_STATUS_SUCCESS:
                        $this->nextStepData = $confirmReport['data'];
                        $this->resolveNextStepInfo();
                        break;
                    default:
                        $this->autoStartOverIfNeeded();
                        break;
                }
                break;
            case self::STEP_FETCH_REPORT:
                $reportDocumentId = $this->stepData['resource_id'];
                if (!$reportDocumentId) {
                    self::$errors[] = 'Invalid request: No report resource ID provided!';
                    $this->autoStartOverIfNeeded();
                } else {
                    if ($this->spFetchReportDocument($reportDocumentId)) {
                        $this->resolveNextStepInfo();
                    } else {
                        $this->autoStartOverIfNeeded();
                    }
                }
                break;
            case self::STEP_PROCESS_REPORT:
                $processReportResult = $this->processReport();
                $this->afterProcessReport($processReportResult);
                $this->autoStartOverIfNeeded();
                break;
        }
    }

    private function addLatency()
    {
        if (is_int($this->realtimeLatency) && $this->realtimeLatency > 1) {
            sleep($this->realtimeLatency);
        }
    }

    private function autoStartOverIfNeeded()
    {
        if ($this->autoStartOver) {
            $this->resolveNextStepInfo(self::STEP_CREATE_REPORT);
        }
    }

    // To be overrode. todo: Use AmazonReportParser at raw parse (1st step)
    abstract protected function processReport();

    protected function reportOptions()
    {
        return null;
    }

    protected function afterProcessReport($processResult)
    {
        if (empty($processResult)) {
            self::$messages[] = sprintf($this->l('Process Report Finish - No report found'), $processResult);
        }
    }

    protected function initInventory()
    {
        $this->storagePath = $this->path . $this->storageDir . '/';
        $fileid = floor((time() % (86400 * 365)) / self::EXPIRE);
        $this->file_inventory = sprintf(
            '%s%s_%s_%s.raw',
            $this->storagePath,
            $this->inventoryType(),
            $this->spMkp->getMarketplaceId(),
            $fileid
        );

        return true;
    }

    protected function spCreateReport($reportType, $startTime = null)
    {
        $this->logger->debug('createReport request', array(
            'mkp' => $this->spMkp->getMarketplaceId(),
            'report_type' => $reportType,
            'start_time' => $startTime,
            'report_options' => $this->reportOptions(),
        ));
        $createReportApi = new AmazonSPAPIReportsCreation(
            $this->spMkp,
            $this->spMkp->getMarketplaceId(),
            $reportType,
            $startTime,
            $this->reportOptions(),
            $this->moduleFeatures->dev_mode
        );
        $response = $createReportApi->apiCreate();
        $this->logger->debug(
            'createReport response',
            array('msg' => $response->getErrorMsg(), 'raw' => $response->getRawResponse())
        );

        if ($response->hasError()) {
            self::$errors[] = 'Failed to request report from Amazon';
            $this->ed('Failed to request report', $response->getErrorMsg(), $response->getRawResponse());
            return '';
        }

        $reportId = $response->getStructuredPayload()->reportId;
        self::$messages[] = sprintf($this->l('Report has been requested (%s), please wait a while'), $reportId);

        return $reportId;
    }

    protected function spConfirmReport($reportId)
    {
        $this->logger->debug('getReport request', array('report_id' => $reportId));
        $getReportApi = new AmazonSPAPIReportsGet(
            $this->spMkp,
            $reportId,
            $this->moduleFeatures->dev_mode
        );
        $response = $getReportApi->apiGet();
        $this->logger->debug(
            'getReport response',
            array('msg' => $response->getErrorMsg(), 'raw' => $response->getRawResponse())
        );

        if ($response->hasError()) {
            self::$errors[] = "Failed to get report from Amazon. Report ID: $reportId";
            $this->ed('Failed to get report', $response->getErrorMsg(), $response->getRawResponse());
            return array('status' => self::CONFIRM_REPORT_STATUS_FAILED, 'data' => array());
        }

        /** @var AmazonSPDefReport $report */
        $report = $response->getStructuredPayload();
        if (!$report->isProcessEnded()) {
            self::$messages[] = $this->l('Waiting a while for the report to be ready for download');
            return array('status' => self::CONFIRM_REPORT_STATUS_STAND_BY, 'data' => array('report_id' => $reportId));
        }

        if (!$report->isDone()) {
            self::$errors[] = "The report process ended unexpectedly. Report document ID: $report->reportDocumentId, report status: $report->processingStatus";
            return array('status' => self::CONFIRM_REPORT_STATUS_FAILED, 'data' => array());
        }

        if (!$report->reportDocumentId) {
            self::$errors[] = "Report is ready. But Amazon doesn't show where to download";
            return array('status' => self::CONFIRM_REPORT_STATUS_FAILED, 'data' => array());
        }

        $reportDocumentId = $report->reportDocumentId;
        self::$messages[] = sprintf('%s (%s)', $this->l('Downloading Report ID'), $reportDocumentId);

        return array('status' => self::CONFIRM_REPORT_STATUS_SUCCESS, 'data' => array('resource_id' => $reportDocumentId));
    }

    protected function spFetchReportDocument($reportDocumentId)
    {
        $this->logger->debug(
            'getReportDocument request',
            array('doc_id' => $reportDocumentId, 'report_type' => $this->reportType())
        );
        $reportDocApi = new AmazonSPAPIReportsDocumentGet(
            $this->spMkp,
            $reportDocumentId,
            $this->reportType(),
            $this->moduleFeatures->dev_mode
        );
        $response = $reportDocApi->apiFetch();
        $this->logger->debug('getReportDocument response', array('raw' => $response->getRawResponse()));

        if ($response->hasError()) {
            self::$errors[] = "Failed to fetch report content from Amazon. Report document ID: $reportDocumentId";
            $this->ed('Failed to fetch report content.', $response->getErrorMsg(), $response->getRawResponse());
            return false;
        }

        if (!AmazonTools::isDirWriteable($this->storagePath)) {
            $error = sprintf('"%s" %s', $this->storagePath, $this->l('is not a writable directory, please check directory permissions'));
            self::$errors[] = $error;
            $this->ed($error);
            return false;
        }

        $reportContent = $this->decodeReport($response->getStructuredPayload());
        if (file_put_contents($this->file_inventory, $reportContent) === false) {
            self::$errors[] = "Unable to write to output file: $this->file_inventory";
            $this->ed("Unable to write to output file: $this->file_inventory");
            return false;
        }

        self::$messages[] = "Processing the report ($reportDocumentId)";

        return true;
    }

    /**
     * @param AmazonSPDefProxyReportDocument $reportDocument
     */
    protected function decodeReport($reportDocument)
    {
        // todo: Consider handling expired report document
        $rawContents = $this->curl_get_file_contents($reportDocument->resource_url);
        $contents = $reportDocument->compression_algo === "GZIP" ? gzdecode($rawContents) : $rawContents;
        switch ($reportDocument->content_type) {
            // All current reports are TAB, add other cases if use
            case AmazonSPDefProxyReportDocument::CONTENT_TYPE_TAB:
                return preg_replace("/(\t|^)\"([^\"]+?)(\t|\n)/", "$1\"\"$2$3", $contents);
            default:
                return $contents;
        }
    }

    /**
     * Replace file_get_contents with CURL
     * Fixed: disabled in the server configuration by allow_url_fopen=0
     * @param $URL
     * @return bool|string
     */
    protected function curl_get_file_contents($URL)
    {
        if (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1'))) {
            return file_get_contents($URL);
        } else {
            $c = curl_init();
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_URL, $URL);
            $contents = curl_exec($c);
            curl_close($c);

            if ($contents)
                return $contents;
            else
                return false;
        }
    }

    abstract protected function resolveCurrentStepInfo();

    abstract protected function afterResolveNextStep();

    protected function resolveNextStepInfo($forceStep = '')
    {
        $runOrder = array(self::STEP_CREATE_REPORT, self::STEP_CONFIRM_REPORT, self::STEP_FETCH_REPORT, self::STEP_PROCESS_REPORT);

        if ($forceStep && in_array($forceStep, $runOrder)) {
            $nextRun = $forceStep;
        } elseif ($this->step && in_array($this->step, $runOrder)) {
            do {
                $step = array_shift($runOrder);
                $runOrder[] = $step;
            } while ($step != $this->step);
            $nextRun = $runOrder[0];
        } else {
            $this->nextStep = '';
            $this->afterResolveNextStep();
            return;
        }

        $this->nextStep = $nextRun;
        $this->afterResolveNextStep();
    }

    public function debugXML($xml)
    {
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return AmazonTools::pre(array(htmlspecialchars($dom->saveXML())), true);
    }

    protected function ed()
    {
        $debug = $this->dbt(func_get_args());
        $this->logger->info($debug);
        if (Amazon::$debug_mode) {
            echo $debug;
        }
    }

    protected function er()
    {
        $debug = $this->dbt(func_get_args());
        $this->logger->info($debug);
        echo $debug;
    }
}
