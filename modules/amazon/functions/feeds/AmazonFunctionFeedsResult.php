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
require_once(dirname(__FILE__) . '/AmazonFunctionFeeds.php');
require_once(dirname(__FILE__) . '/../../classes/amazon.batch.class.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonFunctionFeedsResult extends AmazonFunctionFeeds
{
    protected $logChannel = array(AmazonLogger::CHANNEL_SP_API_FEEDS, AmazonLogger::CHANNEL_SP_API_FEED_RESULT);
    protected $type;

    public function __construct($feedId, $type)
    {
        $isXml = $type != AmazonBatch::TYPE_VAT_INVOICE_UPLOAD;
        parent::__construct($feedId, $isXml);

        $this->type = $type;
    }

    public function dispatch()
    {
        if (!$this->functionAuthorization()) {
            return array('status' => false, 'msg' => 'Wrong token!');
        }
        $spConnector = $this->spConnector;
        if (!$spConnector || !$spConnector->isAuthenticated()) {
            return array('status' => false, 'msg' => 'You are not authorized!');
        }

        try {
            $feedResult = $this->getFeedResultById($this->feedId);
        } catch (AmazonAPIFeedsException $apiException) {
            return array('status' => false, 'msg' => $apiException->getMessage());
        } catch (Exception $exception) {
            return array('status' => false, 'msg' => $exception->getMessage());
        }

        $resultTitle = sprintf('Feed - %s: %s %s', AmazonTools::ucfirst($this->type), $this->l('ID'), $this->feedId);
        $resultContent = $feedResult instanceof SimpleXMLElement
            ? $this->displayGetFeedSubmissionResult($feedResult, $resultTitle) : nl2br($feedResult);

        return array(
            'status' => true,
            'tpl' => $resultContent,
        );
    }

    protected function displayGetFeedSubmissionResult($obj, $title)
    {
        if (isset($obj->Message, $obj->Message->ProcessingReport, $obj->Message->ProcessingReport->Result, $obj->Message->ProcessingReport->Result->ResultCode)
            && ($obj->Message->ProcessingReport->Result->ResultCode == 'Warning' || $obj->Message->ProcessingReport->Result->ResultCode == 'Error')) {

            $errors = '';
            if (is_object($obj->Message->ProcessingReport->Result)) {
                foreach ($obj->Message->ProcessingReport->Result as $result) {
                    $additionalInfo = isset($result->AdditionalInfo->SKU)
                        ? sprintf('SKU: %s - ', $result->AdditionalInfo->SKU) : '';
                    $errors .= nl2br(sprintf(
                        "Message: %d %s %d: %s%s" . Amazon::LF . Amazon::LF,
                        $result->MessageID,
                        $result->ResultCode, $result->ResultMessageCode,
                        $additionalInfo,
                        $result->ResultDescription
                    ));
                }
            } else {
                $additionalInfo = isset($obj->AdditionalInfo->SKU)
                    ? sprintf('SKU: %s - ', $obj->AdditionalInfo->SKU) : '';
                $errors .= nl2br(sprintf(
                    "Message: %d %s %d: %s%s" . Amazon::LF . Amazon::LF,
                    $obj->MessageID,
                    $obj->Message->ProcessingReport->Result->ResultCode,
                    $obj->Message->ProcessingReport->Result->ResultMessageCode,
                    $additionalInfo,
                    $obj->Message->ProcessingReport->Result->ResultDescription
                ));
            }
        } elseif (isset($obj->Message, $obj->Message->ProcessingReport, $obj->Message->ProcessingReport->StatusCode)
            && $obj->Message->ProcessingReport->StatusCode == 'Complete') {
            $errors = '';
        } else {
            $errors = nl2br(print_r($obj, true));
        }

        $this->smarty->assign(array(
            'feedSubmissionResultTitle' => $title,
            'feedSubmissionResultText1' => $this->l('Entry'),
            'feedSubmissionResultText2' => $this->l('Result'),
            'feedSubmissionResultText3' => $this->l('Entries Processed'),
            'feedSubmissionResultText4' => $this->l('Entries processed successfully'),
            'feedSubmissionResultText5' => $this->l('Entries with Error'),
            'feedSubmissionResultText6' => $this->l('Entries with Warning'),
            'feedSubmissionResultText7' => $this->l('Messages Logs'),
            'feedSubmissionResultText8' => $this->l('Error'),
            'feedSubmissionResultText9' => $this->l('*** This is not an error *** Please DO NOT contact the support :'),
            'feedSubmissionResultText10' => $this->l('Amazon is currently processing your request, please wait a while, the report will be available soon.'),
            'feedSubmissionResultTextError' => $errors,
        ));

        if (isset($obj->Message) && isset($obj->Message->ProcessingReport->Summary->ProcessingSummary)) {
            $summary = $obj->Message->ProcessingReport->Summary->ProcessingSummary;
            $this->smarty->assign(array(
                'feedSubmissionResultSummary' => $summary
            ));
        } elseif (isset($obj->Message) && isset($obj->Message->ProcessingReport->ProcessingSummary)) {
            $summary = $obj->Message->ProcessingReport->ProcessingSummary;
            $this->smarty->assign(array(
                'feedSubmissionResultSummary' => $summary
            ));
        } elseif (isset($obj->Error)) {
            $this->smarty->assign(array(
                'feedSubmissionResultObject' => $obj,
                'feedSubmissionResultIsError' => true,
                'feedSubmissionResultTextError' => $obj->Error->Message,
            ));

            // Display an interpretation of this usual case
            //
            if ((string)$obj->Error->Code == 'FeedProcessingResultNotReady') {
                $this->smarty->assign(array(
                    'feedSubmissionResultIsNotReady' => true
                ));
            }
        } else {
            $this->smarty->assign(array(
                'feedSubmissionResultObjectText' => nl2br(print_r($obj, true))
            ));
        }

        return $this->display(
            $this->path . 'amazon.php',
            'views/templates/admin/functions/feed_submission_result.tpl'
        );
    }
}
