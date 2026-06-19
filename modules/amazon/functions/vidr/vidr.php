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
require_once(dirname(__FILE__) . '/../wrapper.php');
require_once(dirname(__FILE__) . '/vidr_get_shipment.php');
require_once(dirname(__FILE__) . '/vidr_update_address.php');
require_once(dirname(__FILE__) . '/vidr_upload_vat_invoice.php');
require_once(dirname(__FILE__) . '/../../classes/amazon.logger.class.php');
if (!defined('_PS_VERSION_')) { exit; }

/**
 * Class AmazonFunctionVIDRCronScript
 * VCS all cron job entries
 * Possible query params:
 * - General
 *  + mainAction: get | upload | update. Action to execution regardless run_order
 * - Upload VAT invoice
 *  + demo: not upload to Amazon, mimic the response only.
 *  + limitUpload: number of upload requests (pass all check and do a real upload invoice)
 *  + mpOrderId: Run for particular order
 * - Get shipment
 *  + testXRowsOnly: Fetch x number of shipment only
 * - Update buyer VAT number / billing address for orders
 *  + mpOrderId: Run for particular order
 */
class AmazonFunctionVIDRCronScript extends AmazonFunctionWrapper
{
    protected $function = 'vidr_script';

    /** @var AmazonLogger */
    public $log;

    /**
     * This setting wil decide which action to be executed in this cron attempt.
     */
    const VIDR_CRON_RUN_ORDER_ALL_MKP_4_6_54 = 'VIDR_CRON_RUN_ORDER_ALL_MARKETPLACES_4_6_54';

    const RUN_ACTION_GET_SHIPMENT = 'GET_SHIPMENT';
    const RUN_ACTION_UPLOAD_VAT_INVOICE = 'UPLOAD_VAT_INVOICE';
    const RUN_ACTION_UPDATE_VAT_NUMBER = 'UPDATE_VAT_NUMBER';

    public function __construct()
    {
        $cronToken = AmazonTools::getValue('cron_token');
        parent::__construct($cronToken);
    }

    public function runVIDRCronScript()
    {
        $this->preCheckRequest();

        $cronToken = $this->cronToken;
        $amzMarketplace = $this->spConnector->getMarketplaceId();

        $action = $this->whatActionForThisTime();
        $this->pd(sprintf('VIDR action: %s', $action));
        $this->pd(sprintf('Marketplace: %s', $amzMarketplace));
        $this->separate();

        switch ($action) {
            case self::RUN_ACTION_GET_SHIPMENT:
                $this->log = new AmazonLogger(array(AmazonLogger::CHANNEL_VCS, AmazonLogger::SUB_VCS_GET));

                $testXRowsOnly = AmazonTools::getValue('testXRowsOnly');
                $getShipment = new AmazonFunctionVIDRGetShipment($cronToken, $amzMarketplace, $testXRowsOnly);
                $getShipment->runGetVIDRShipments();
                break;
            case self::RUN_ACTION_UPLOAD_VAT_INVOICE:
                $this->log = new AmazonLogger(array(AmazonLogger::CHANNEL_VCS, AmazonLogger::SUB_VCS_UPLOAD));

                $mpOrderId = AmazonTools::getValue('mpOrderId');
                $isDemo = AmazonTools::getValue('demo', false);
                $limitUpload = AmazonTools::getValue('limitUpload', false);
                if (false !== $limitUpload) {
                    $sendInvoice = new AmazonFunctionVIDRUploadVATInvoice($cronToken, $mpOrderId, $isDemo, true, (int)$limitUpload);
                } else {
                    $sendInvoice = new AmazonFunctionVIDRUploadVATInvoice($cronToken, $mpOrderId, $isDemo);
                }
                $sendInvoice->bulkUpload();
                break;
            case self::RUN_ACTION_UPDATE_VAT_NUMBER:
                $mpOrderId = AmazonTools::getValue('mpOrderId');
                $updateVATNumber = new AmazonFunctionVIDRUpdateAddress($cronToken, $mpOrderId);
                $updateVATNumber->updateCustomerAddress();
                break;
            default:
                $this->pd('Unknown action');
                break;
        }
    }

    protected function whatActionForThisTime()
    {
        $getShipment = self::RUN_ACTION_GET_SHIPMENT;
        $uploadInvoice = self::RUN_ACTION_UPLOAD_VAT_INVOICE;
        $updateVatNumber = self::RUN_ACTION_UPDATE_VAT_NUMBER;

        // Override action from parameter
        $mainAction = AmazonTools::getValue('mainAction', false);
        if ($mainAction) {
            switch ($mainAction) {
                case 'get':
                    return $getShipment;
                case 'upload':
                    return $uploadInvoice;
                case 'update':
                    return $updateVatNumber;
                default:
                    $this->pd('Override main action not valid, use Run Order instead');
                    break;
            }
        }

        // Use run order
        $spMkp = $this->spConnector->getMarketplaceId();
        $settingKey = self::VIDR_CRON_RUN_ORDER_ALL_MKP_4_6_54;
        $runOrder = AmazonConfiguration::get($settingKey);

        if (!$runOrder || !is_array($runOrder)) {
            // Run order not found, this is the first time, initialize it
            $thisTimeAction = $getShipment; // Setup get_shipment for next time
            $newRunOrder = array(
                // Double upload action because it's heaviest process
                $spMkp => array($updateVatNumber, $uploadInvoice, $uploadInvoice, $getShipment)
            );
        } elseif (!isset($runOrder[$spMkp]) || !$runOrder[$spMkp] || !is_array($runOrder[$spMkp]) || !count($runOrder[$spMkp])) {
            // Run order of this marketplace not exist, initialize it
            $thisTimeAction = $getShipment;
            $runOrder[$spMkp] = array($updateVatNumber, $uploadInvoice, $uploadInvoice, $getShipment);
            $newRunOrder = $runOrder;
        } else {
            $thisTimeAction = array_shift($runOrder[$spMkp]);
            $runOrder[$spMkp][] = $thisTimeAction;  // Queue this time action to tail
            $newRunOrder = $runOrder;
        }

        AmazonConfiguration::updateValue($settingKey, $newRunOrder);

        return $thisTimeAction;
    }
}

$amazonVIDRCron = new AmazonFunctionVIDRCronScript();
$amazonVIDRCron->runVIDRCronScript();
$debugContent = $amazonVIDRCron->getDebugContent();
echo $debugContent;
if ($amazonVIDRCron->log) {
    $amazonVIDRCron->log->debug($debugContent);
}
