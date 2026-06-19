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
require_once(dirname(__FILE__) . '/AmazonFunctionWithFeeds.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonFunctionWrapper extends AmazonFunctionWithFeeds
{
    /** @var string Store all debug messages printed while running */
    public static $debugContent;

    protected $cr;
    protected $debug;

    /** @var string For debug message only. Children should overwrite this for easier debugging. To know which function create debug message */
    protected $function = 'function_wrapper';

    /** @var AmazonWebService */
    protected $api;

    protected $cronToken;
    protected $amazonLang;

    /**
     * Flags for testing report handler
     */
    protected $testXRowsOnly;

    public function __construct($cronToken)
    {
        parent::__construct();
        $this->cr = Amazon::LF;
        $this->debug = $this->_debug;
        $this->cronToken = $cronToken;
    }

    protected function preCheckRequest()
    {
        if (!$this->functionAuthorization()) {
            die('Wrong Token');
        }

        $spConnector = $this->initSpConnector();
        if (!$spConnector || !$spConnector->isAuthenticated()) {
            die($this->l('No selected language, nothing to do...'));
        }
        $this->amazonLang = AmazonVIDRShipment::getMarketplaceIso($spConnector->getMarketplaceId());

        return true;
    }

    /******************************************* Helper functions *****************************************************/

    /**
     * @param bool $debugModeOnly
     */
    protected function separate($debugModeOnly = false)
    {
        if (!$debugModeOnly || $this->debug) {
            self::$debugContent .= str_repeat('-', 160) . $this->cr;
        }
    }

    /**
     * Print debug message
     * @param $message
     * @param bool $debugModeOnly
     */
    protected function pd($message, $debugModeOnly = false)
    {
        if (!$debugModeOnly || $this->debug) {
            self::$debugContent .= AmazonTools::pre(array($message), true) . $this->cr;
        }
    }

    /**
     * Print debug message with file & line detail
     * @param $message
     * @param $line
     * @param bool $debugModeOnly
     */
    public function pdd($message, $line, $debugModeOnly = false)
    {
        if (!$debugModeOnly || $this->debug) {
            self::$debugContent .= AmazonTools::pre(array(
                    sprintf('%s(#%d): ', $this->function, $line),
                    $message
                ), true)
                . $this->cr;
        }
    }

    public function concatDebug($addition)
    {
        self::$debugContent .= $addition;
    }

    public function getDebugContent()
    {
        $content = self::$debugContent;
        self::$debugContent = '';
        return $content;
    }
}
