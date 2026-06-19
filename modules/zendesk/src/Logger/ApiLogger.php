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

namespace ZendeskAddon\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ApiLogger
{
    public $logname;

    const MAX_LOG_FILE_SIZE = 2000000000;

    private static $instance;

    private $stream;

    public $module;

    /**
     * @var Logger
     */
    private $logger;

    /** @var bool */
    private $fileLoggerActivated = false;

    final protected function __construct()
    {
        $this->module = \Module::getInstanceByName('zendesk');
        $date = date('Ymd');
        $hashOfDay = md5(_COOKIE_KEY_ . $date);
        $this->logname = $this->module->name . '-' . $date . '-' . $hashOfDay . '.log';
        $this->fileLoggerActivated = \Configuration::getGlobalValue(\Zendesk::IS_FILE_LOGGER_ACTIVE);
        $this->build();
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize a singleton.');
    }

    protected function build()
    {
        $logDir = _PS_MODULE_DIR_ . $this->module->name . '/logs/' . date('Ym');
        if (is_dir($logDir) === false) {
            mkdir($logDir, 0755);
            @copy(_PS_MODULE_DIR_ . $this->module->name . '/index.php', $logDir . '/index.php');
            @copy(_PS_MODULE_DIR_ . $this->module->name . '/logs/.htaccess', $logDir . '/.htaccess');
        }

        $logFile = $logDir . '/' . $this->logname;
        if (file_exists($logFile)) {
            $fileSize = filesize($logFile);
            if ($fileSize > self::MAX_LOG_FILE_SIZE) {
                unlink($logFile);
            }
        } else {
            try {
                $this->deleteLogFilesOld();
            } catch (\Exception $ex) {
                if (is_dir($logDir)) {
                    $this->stream = fopen($logDir . '/' . $this->logname, 'a+');
                    $this->logger = new Logger($this->module->name, [new StreamHandler($this->stream)]);
                    $this->logger->info('Error while deleting old logs ' . $ex->getMessage());
                }
            }
        }

        if ($this->fileLoggerActivated === false) {
            return true;
        }
        $this->stream = fopen($logFile, 'a+');
        $this->logger = new Logger($this->module->name, [new StreamHandler($this->stream)]);
    }

    public function log($object, $data, $type = 'Error', $isObject = false)
    {
        if ($this->fileLoggerActivated === false) {
            return true;
        }

        $logData = $data;
        if ($isObject === true) {
            $logData = json_encode($data);
        }

        $this->logger->info($this->getClass($object) . ' - ' . $type . ' - Data: ' . $logData);
    }

    private function getClass($object)
    {
        return str_replace('ModuleFrontController', '', (new \ReflectionClass($object))->getShortName());
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function __destruct()
    {
        if (empty($this->stream)) {
            return;
        }
        fclose($this->stream);
    }

    /**
     * Delete log files older than days specified (default = 60 days)
     *
     * @param int $deleteFromDays Number of days to delete - 0 = all days
     */
    public function deleteLogFilesOld($deleteFromDays = 60)
    {
        $logDir = _PS_MODULE_DIR_ . $this->module->name . '/logs/';
        $currentMonthDir = _PS_MODULE_DIR_ . $this->module->name . '/logs/' . date('Ym');
        $previousLogDirs = scandir($logDir);
        $origin = new \DateTimeImmutable('now');
        foreach ($previousLogDirs as $oneLogFolder) {
            if (in_array($oneLogFolder, ['.', '..']) === true || is_dir($logDir . $oneLogFolder) === false) {
                continue;
            }
            $filesWereDeleted = false;
            $logFiles = scandir($logDir . $oneLogFolder);
            foreach ($logFiles as $oneFileLog) {
                if (in_array($oneFileLog, ['.', '..', '.htaccess', 'index.php']) === true) {
                    continue;
                }
                $fileNameExploded = explode('-', str_replace('.log', '', $oneFileLog));
                $dateFile = \sprintf(
                    '%s-%s-%s',
                    substr($fileNameExploded[1], 0, 4),
                    substr($fileNameExploded[1], 4, 2),
                    substr($fileNameExploded[1], 6, 2)
                );
                if (count($fileNameExploded) !== 3 || \mb_strlen($fileNameExploded[1]) !== 8) {
                    continue;
                }
                $target = new \DateTimeImmutable($dateFile);
                $interval = $origin->diff($target);
                if ((int) $interval->format('%a') >= (int) $deleteFromDays) {
                    $filesWereDeleted = true;
                    @unlink($logDir . $oneLogFolder . '/' . $oneFileLog);
                }
            }
            if ($filesWereDeleted === false) {
                if (count($logFiles) <= 3) {
                    foreach ($logFiles as $oneFileLog) {
                        if (in_array($oneFileLog, ['.', '..']) === true) {
                            continue;
                        }
                        @unlink($logDir . $oneLogFolder . '/' . $oneFileLog);
                    }
                    if ($logDir . $oneLogFolder !== $currentMonthDir) {
                        @rmdir($logDir . $oneLogFolder);
                    }
                }
            }
        }
        foreach ($previousLogDirs as $oneLogFolder) {
            if (in_array($oneLogFolder, ['.', '..', date('Ym')]) === true || is_dir($logDir . $oneLogFolder) === false) {
                continue;
            }
            if ($oneLogFolder === $currentMonthDir) {
                continue;
            }
            $filesWereDeleted = false;
            $logFiles = scandir($logDir . $oneLogFolder);
            if (count($logFiles) <= 3) {
                foreach ($logFiles as $oneFileLog) {
                    if (in_array($oneFileLog, ['.', '..']) === true) {
                        continue;
                    }
                    @unlink($logDir . $oneLogFolder . '/' . $oneFileLog);
                }
                @rmdir($logDir . $oneLogFolder);
            }
        }
    }
}
