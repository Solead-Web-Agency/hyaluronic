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
require_once(dirname(__FILE__) . '/../amazon.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonFunction extends Amazon
{
    /** @var AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion */
    protected $spConnector;

    protected $_debug = false;

    protected $logChannel;
    /** @var AmazonLogger */
    protected $logger;

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        if (Amazon::$debug_mode || AmazonTools::getValue('debug')) {
            $this->_debug = true;
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        if ($this->logChannel) {
            $this->logger = new AmazonLogger($this->logChannel);
        }
    }

    protected function functionAuthorization()
    {
        $cronToken = AmazonTools::getValue('cron_token', AmazonTools::getValue('amazon_token'));
        $instantToken = AmazonTools::getValue('instant_token');
        if (AmazonTools::checkToken($cronToken)
            || $instantToken == Configuration::get(AmazonConstant::GB_CONFIG_INSTANT_TOKEN, null, 0, 0)) {
            return true;
        }

        return false;
    }

    /**
     * @return AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion
     */
    protected function initSpConnector()
    {
        $spMkp = AmazonTools::getValue('sp_mkp');
        $spRegion = AmazonTools::getValue('sp_region');
        if ($spMkp) {
            $this->spConnector = AmazonSPConnectorPSMkp::initFromMarketplace($spMkp);
        } elseif ($spRegion) {
            $this->spConnector = new AmazonSPConnectorPSRegion($spRegion);
        }

        return $this->spConnector;
    }

    /**
     * Log debug and print on debug mode (by condition)
     */
    protected function elc()
    {
        $toLog = $this->dbt(func_get_args());
        $this->logger->debug($toLog);
        if ($this->_debug) {
            echo $toLog;
        }
    }

    /**
     * Log debug and print
     */
    protected function ed()
    {
        $toLog = $this->dbt(func_get_args());
        $this->logger->debug($toLog);
        echo $toLog;

        return $this;   // Chaining function call if needed 
    }

    /**
     * Log debug only
     */
    protected function ld()
    {
        $toLog = $this->dbt(func_get_args());
        $this->logger->debug($toLog);
    }
}
