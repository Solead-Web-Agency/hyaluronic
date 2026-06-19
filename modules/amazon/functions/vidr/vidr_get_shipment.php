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
require_once(dirname(__FILE__) . '/../../classes/amazon.vidr_shipment.class.php');
require_once(dirname(__FILE__) . '/../../classes/amazon.vidr_shipment_order_mapping.php');
require_once(dirname(__FILE__) . '/../../includes/amazon.admin_configure.php');
require_once dirname(__FILE__) . '/../../includes/amazon.report.vcs.php';
if (!defined('_PS_VERSION_')) { exit; }

/**
 * Class AmazonFunctionGetVIDRShipment
 */
class AmazonFunctionVIDRGetShipment extends AmazonFunctionWrapper
{
    protected $amzMarketplace;

    public function __construct(
        $cronToken,
        $amzMarketplace,
        $testXRowsOnly,
        $debug = false
    )
    {
        parent::__construct($cronToken);
        $this->amzMarketplace = $amzMarketplace;
        $this->testXRowsOnly = $testXRowsOnly;
        $this->debug = $debug;
    }

    public function runGetVIDRShipments()
    {
        if (!$this->preCheckModuleSetting()) {
            return;
        }

        // preCheckRequest & resolveNecessaryParameters are resolved in report constructor, duplicated
        $amzReport = new AmazonVCSReport($this->cronToken, $this->amzMarketplace, '', $this->testXRowsOnly, $this->debug);
        if (!$amzReport->initialize()) {
            return;
        }

        // By default, report contains only pending records within 90 days, no need to specify date range
        // todo: Despite above document statement, the real data also includes orders older than 90 days. Should add date range
        $amzReport->handleReportSteps();
        foreach ($amzReport->getErrors() as $error) {
            $this->pd($error);
        }
        foreach ($amzReport->getMessages() as $message) {
            $this->pd($message);
        }
    }

    protected function preCheckModuleSetting()
    {
        $moduleFeatures = $this->amazon_features;
        $expertMode = isset($moduleFeatures, $moduleFeatures['expert_mode']) && (bool)($moduleFeatures['expert_mode']);
        $settingEnable = $expertMode && (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_VCS_ENABLED);
        if (!$settingEnable) {
            $this->pd('VIDR is not enable');
            return false;
        }

        return true;
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if (!$lang) {
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));
        }

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }
}
