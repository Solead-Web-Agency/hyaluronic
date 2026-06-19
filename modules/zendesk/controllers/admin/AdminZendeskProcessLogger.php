<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . 'zendesk/vendor/autoload.php';

use ZendeskAddon\Logger\ApiLogger;
use ZendeskClasslib\Extensions\ProcessLogger\Controllers\Admin\AdminProcessLoggerController;
use ZendeskClasslib\Extensions\ProcessLogger\ProcessLoggerExtension;

class AdminZendeskProcessLoggerController extends AdminProcessLoggerController
{
    private $logPath;

    public function __construct()
    {
        parent::__construct();

        $this->fields_options['processLogger']['fields'][Zendesk::IS_FILE_LOGGER_ACTIVE] = [
            'title' => $this->module->l(
                'Activate Log files',
                'AdminProcessLoggerController'
            ),
            'hint' => $this->module->l(
                'Add all requests to log files',
                'AdminProcessLoggerController'
            ),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => 'bool',
        ];

        $this->logPath = _PS_MODULE_DIR_ . $this->module->name . '/logs/';
    }

    public function initContent()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->page_header_toolbar_btn['zendesk_config'] = [
                'href' => $this->context->link->getAdminLink('AdminZendeskConfiguration'),
                'desc' => $this->module->l('Configuration'),
                'icon' => 'process-icon-cogs',
            ];
            $this->page_header_toolbar_btn['zendesk_logs'] = [
                'href' => $this->context->link->getAdminLink('AdminZendeskProcessLogger'),
                'desc' => $this->module->l('Logs'),
                'icon' => 'process-icon-terminal',
            ];
            $this->page_header_toolbar_btn['zendesk_invoices'] = [
                'href' => $this->context->link->getAdminLink('AdminZendeskPrestaShopInvoices'),
                'desc' => 'PrestaShop ' . $this->module->l('Invoices'),
                'icon' => 'process-icon-envelope',
            ];
        }

        parent::initContent();

        if (Tools::isSubmit('submitDeleteOldLogs')) {
            ApiLogger::getInstance()->deleteLogFilesOld((int) Tools::getValue('remove_from_days'));
        }

        $isLoggerFileActive = Configuration::getGlobalValue(Zendesk::IS_FILE_LOGGER_ACTIVE);

        if ($isLoggerFileActive !== false) {
            $this->showLogFiles();
        }
    }

    public function saveConfiguration()
    {
        $shops[] = 0;
        $idShop = Context::getContext()->shop->id;
        $isLoggerActive = Tools::getValue(Zendesk::IS_FILE_LOGGER_ACTIVE);

        $saveForEveryShops = (bool) Tools::getValue('zendesk_processlogger_multishop_processLogger');

        foreach (Shop::getShops(true, null, true) as $key => $idShop) {
            if ($saveForEveryShops === false && (int) $idShop !== Context::getContext()->shop->id) {
                continue;
            }

            $this->saveItemIfSubmitted(ProcessLoggerExtension::QUIET_MODE, $idShop);
            $this->saveItemIfSubmitted(ProcessLoggerExtension::ERASING_DISABLED, $idShop);
            $this->saveItemIfSubmitted(ProcessLoggerExtension::ERASING_DAYSMAX, $idShop, false);

            $this->confirmations[] = $this->module->l(
                'Log parameters are successfully updated!',
                'AdminProcessLoggerController'
            );

            $loggerFileState = Configuration::get(Zendesk::IS_FILE_LOGGER_ACTIVE, null, null, $idShop, false);

            if ($loggerFileState !== $isLoggerActive) {
                Configuration::updateGlobalValue(Zendesk::IS_FILE_LOGGER_ACTIVE, (bool) $isLoggerActive);

                $infoActivation = 'Logger file ';
                $infoActivation .= (bool) $isLoggerActive === true ? 'enabled ' : 'disabled ';
                $infoActivation .= date('Y-m-d H:i:s') . ' by ';
                $infoActivation .= $this->context->employee->firstname . ' ' . $this->context->employee->lastname;
                $infoActivation .= ' (id ' . $this->context->employee->id . ' on shop ' . $idShop . ')';
                $this->confirmations[] = $infoActivation;
            }
        }
    }

    private function showLogFiles()
    {
        $aMonths = scandir($this->logPath);
        $logsFilesFull = [];
        foreach ($aMonths as $aMonth) {
            if ($aMonth !== '.' && $aMonth !== '..') {
                $logsFilesFull[$aMonth] = glob($this->logPath . $aMonth . '/*.log');
            }
        }
        $logsFiles = empty($logsFilesFull) === false ? $this->onlyFileNames($logsFilesFull) : [];
        $this->context->smarty->assign([
            'logs_files' => $logsFiles,
            'logs_url' => $this->context->link->getAdminLink(
                'AdminZendeskProcessLogger',
                true
            ),
        ]);

        $content = $this->context->smarty->getTemplateVars('content');

        $fileName = Tools::getValue('display_file');
        if ($fileName !== false) {
            $fileToDisplay = $this->logPath . $fileName;
            if ($this->checkSecurityFile($fileName) === true) {
                $this->context->smarty->assign([
                    'logfile_content' => Tools::file_get_contents($fileToDisplay),
                    'logfile_name' => $fileName,
                ]);
            }
        }
        $this->context->smarty->assign(
            'ajax_remove_old_logs',
            $this->context->link->getAdminLink('AdminZendeskProcessLogger')
        );

        $contentLogs = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/logs.tpl'
        );

        $this->context->smarty->assign([
            'content' => $content . $contentLogs,
        ]);
    }

    private function onlyFileNames($logsFilesFull)
    {
        $logsFiles = [];
        foreach ($logsFilesFull as $aMonth => $fileList) {
            foreach ($fileList as $aFile) {
                $filePath = explode('/', $aFile);
                $logsFiles[$aMonth][] = @end($filePath);
            }
        }

        return $logsFiles;
    }

    private function checkSecurityFile($fileName)
    {
        if (strpos($fileName, '.log') === false) {
            $this->context->controller->errors[] = $this->module->l(
                'File extension other that log is forbidden.'
            );

            return false;
        }

        if (strpos($fileName, '../') !== false) {
            $this->context->controller->errors[] = $this->module->l('Directory in log file is forbidden.');

            return false;
        }

        if (file_exists($this->logPath . $fileName) === false) {
            $this->context->controller->errors[] = $this->module->l('Log file do not exists.');

            return false;
        }

        return true;
    }

    /**
     * Save field if submitted
     *
     * @param string $item - key of field to check
     * @param int $idShop - shop to save in
     */
    private function saveItemIfSubmitted($item, $idShop, $isBoolean = true)
    {
        if (Tools::isSubmit($item)) {
            $value = Tools::getValue($item);

            Configuration::updateValue(
                $item,
                $isBoolean ? (bool) $value : (int) $value,
                false,
                null,
                $idShop
            );
        }
    }
}
