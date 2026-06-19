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
 * @author    SeQura Tech <prestashop@sequra.com>
 * @copyright Since 2013 SeQura WorldWide SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
use PrestaShop\Module\PrestashopSequra\Logger;
use PrestaShop\Module\PrestashopSequra\Reporter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SequraTriggerreportModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * Logger
     *
     * @var Logger
     * */
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->logger = new Logger();
    }

    public function initContent()
    {
        $this->submitDailyReport();
        $error = Configuration::get('SEQURA_REPORT_ERROR');
        if ('' == $error) {
            exit('ok');
        }
        $this->logger->logError('Failed to send the DR: ' . $error, __FUNCTION__, __CLASS__);
        http_response_code(599);
        exit('ko');
    }

    private function submitDailyReport()
    {
        if ($_SERVER['HTTP_USER_AGENT'] == 'sequra-cron') {
            $this->detachBrowser();
        }

        // This would run in background if UA- sequra-cron
        return (new Reporter($this->module))->submitDailyReport();
    }

    private function detachBrowser()
    {
        ob_start();

        // tell PHP to ignore if the browsers closes connection
        @ignore_user_abort(true);
        // check it worked
        $defer = @ignore_user_abort();
        // according to the docs, in some cases on IIS+CGI
        // ignore_user_abort does not work
        // If so, just abort.
        if (!$defer) {
            $this->logger->logError('Webserver does not support ignore_user_abort()', __FUNCTION__, __CLASS__);
            throw new RuntimeException('Webserver does not support ignore_user_abort()');
        }

        // remove the buffer, even nested ones
        while (ob_get_level()) {
            ob_end_clean();
        }
        /* close the frigging connection with the browser, and help IE understand the message */
        ob_start();
        header('Content-Type: text/plain');
        header('Content-Length: 0');
        header("Content-Encoding: none\r\n");
        header('Connection: close');
        // we need all three, in this precise order
        @flush();
        @ob_end_flush();
        @ob_flush();
    }
}
