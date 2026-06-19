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

namespace PrestaShop\Module\PrestashopSequra;

use FileLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Logger
{
    private const MAX_LOG_SIZE = 2 * 1024 * 1024; // 2MB
    /**
     * The path to the log file.
     *
     * @var string
     */
    private $log_file_path;

    /**
     * The logger object.
     *
     * @var \FileLogger
     */
    private $logger;

    public function __construct()
    {
        $this->log_file_path = _PS_MODULE_DIR_ . implode(DIRECTORY_SEPARATOR, ['sequra', 'sequra.log']);
        $this->logger = new \FileLogger(0);
    }

    /**
     * Make sure the log file exists and is writable.
     *
     * @return bool
     */
    private function setup()
    {
        if (!file_exists($this->log_file_path)) {
            $dir = dirname($this->log_file_path);
            if (!is_dir($dir) && file_exists($dir)) {
                error_log('Log file does not exist and parent /log directory is a file.');

                return false;
            }
            // make dir with the correct permissions.
            if (!file_exists($dir) && !mkdir($dir, 0755, true)) {
                error_log('Could not create log file directory.');

                return false;
            }

            // create the file.
            if (!touch($this->log_file_path)) {
                error_log('Could not create log file.');

                return false;
            }
        }

        if (!is_writable(dirname($this->log_file_path))) {
            return false;
        }

        // check if log file exceed 2MB and if so, clear it.
        if (filesize($this->log_file_path) >= self::MAX_LOG_SIZE) {
            try {
                $this->clearLog();

                return $this->setup();
            } catch (\Exception $e) {
                error_log($e->getMessage());

                return false;
            }
        }

        $this->logger->setFilename($this->log_file_path);

        return true;
    }

    /**
     * Get the content of the log file.
     *
     * @return string The content of the log file
     *
     * @throws \Exception If something goes wrong. The exception message will contain the error message.
     */
    public function getLogContent()
    {
        if (!$this->setup()) {
            throw new \Exception('Could not setup log file.');
        }

        $content = \Tools::file_get_contents($this->log_file_path);
        if (false === $content) {
            throw new \Exception('Could not read log file.');
        }

        return $content;
    }

    /**
     * Clear the log file.
     *
     * @throws \Exception If something goes wrong. The exception message will contain the error message.
     */
    public function clearLog()
    {
        if (file_exists($this->log_file_path) && !unlink($this->log_file_path)) {
            throw new \Exception('Could not clear log file.');
        }
        if (!$this->setup()) {
            throw new \Exception('Could not setup log file.');
        }
    }

    /**
     * Log a message with the severity "WARNING".
     *
     * @param string $message The message to log
     * @param string|null $func The method name
     * @param string|null $class The class name
     */
    public function logWarning($message, $func = null, $class = null)
    {
        $this->log($message, $func, $class, \FileLogger::WARNING);
    }

    /**
     * Log a message with the severity "INFO".
     *
     * @param string $message The message to log
     * @param string|null $func The method name
     * @param string|null $class The class name
     */
    public function logInfo($message, $func = null, $class = null)
    {
        $this->log($message, $func, $class, \FileLogger::INFO);
    }

    /**
     * Log a message with the severity "DEBUG".
     *
     * @param string $message The message to log
     * @param string|null $func The method name
     * @param string|null $class The class name
     */
    public function logDebug($message, $func = null, $class = null)
    {
        $this->log($message, $func, $class, \FileLogger::DEBUG);
    }

    /**
     * Log a message with the severity "ERROR".
     *
     * @param string $message The message to log
     * @param string|null $func The method name
     * @param string|null $class The class name
     */
    public function logError($message, $func = null, $class = null)
    {
        $this->log($message, $func, $class, \FileLogger::ERROR);
    }

    /**
     * Log a message with the severity "ERROR".
     *
     * @param string $message The message to log
     * @param string|null $func The method name
     * @param string|null $class The class name
     * @param int $level The log level. Use FileLogger::DEBUG, FileLogger::INFO, FileLogger::WARNING, FileLogger::ERROR
     */
    private function log($message, $func = null, $class = null, $level = \FileLogger::DEBUG)
    {
        if (!$this->isLogEnabled()) {
            return;
        }
        if ($this->setup()) {
            try {
                $this->logger->log($this->formatMsg($message, $func, $class), $level);
            } catch (\Exception $e) {
                error_log('Could not log message: ' . $e->getMessage());
            }
        }
    }

    /**
     * Check if the log is enabled.
     *
     * @return bool
     */
    private function isLogEnabled()
    {
        return \Configuration::get('SEQURA_ENABLE_DEBUG') === '1';
    }

    /**
     * Helper function to format the message.
     *
     * @param string $msg The message to log
     * @param string $func The method name
     * @param string $class The class name
     *
     * @return string
     */
    private function formatMsg($msg, $func = null, $class = null)
    {
        $message = '';

        if (!empty($func)) {
            $message .= $func . '()';
        }
        if (!empty($class)) {
            $message = $class . '::' . $message;
        }

        if (!empty($message)) {
            $message .= ' - ';
        }

        return $message . $msg;
    }
}
