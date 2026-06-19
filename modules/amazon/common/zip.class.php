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
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) { exit; }
abstract class CommonZip
{
    public static $debug_mode = false;
    /**
     * @param $zipfile
     * @param $from
     *
     * @return bool
     */
    public function createZip($zipfile, $from)
    {
        $pclzip = _PS_ROOT_DIR_.'/tools/pclzip/pclzip.lib.php';

        if (method_exists('ZipArchive', 'addFile')) {
            return ($this->createZipWithZipArchive($zipfile, $from));
        } elseif (file_exists($pclzip)) {
            require_once(_PS_ROOT_DIR_.'/tools/pclzip/pclzip.lib.php');
            return ($this->createZipWithPclZip($zipfile, $from));
        } else {
            Tools::displayError(sprintf(
                '%s(%s): Not any existing method to zip the file',
                basename(__FILE__),
                __LINE__
            ));
            return (false);
        }
    }

    /**
     * @param $zipfile
     * @param $from
     *
     * @return bool
     */
    private function createZipWithPclZip($zipfile, $from)
    {
        if (self::$debug_mode) {
            printf('Creating Zip File with PclZip: %s', $zipfile);
        }

        if (file_exists($filename = $zipfile)) {
            if (!unlink($filename)) {
                if (self::$debug_mode) {
                    Tools::displayError(sprintf(
                        '%s(%s): '.$this->l('Unable to remove: %s'),
                        basename(__FILE__),
                        __LINE__,
                        $zipfile
                    ));
                }

                return (false);
            }
        }
        if (self::$debug_mode) {
            CommonTools::p(getcwd());
            CommonTools::p($from);
        }

        $zip = new PclZip($zipfile);
        $result = $zip->add($from);
        $pass = true;

        if (is_array($result) && count($result) == count($from)) {
            foreach ($result as $diag) {
                if (is_array($diag) && array_key_exists('status', $diag) && $diag['status'] == 'ok') {
                    $pass &=  true;
                } else {
                    $pass &= false;
                }
                if (self::$debug_mode) {
                    CommonTools::p($diag);
                }
            }
        } else {
            if (self::$debug_mode) {
                CommonTools::p($result);
            }
        }

        return($pass);
    }

    /**
     * @param $zipfile
     * @param $from
     *
     * @return bool
     */
    protected function createZipWithZipArchive($zipfile, $from)
    {
        if (self::$debug_mode) {
            printf('Creating Zip File: %s', $zipfile);
        }

        if (file_exists($filename = $zipfile)) {
            if (!unlink($filename)) {
                if (self::$debug_mode) {
                    Tools::displayError(sprintf(
                        '%s(%s): '.$this->l('Unable to remove: %s'),
                        basename(__FILE__),
                        __LINE__,
                        $zipfile
                    ));
                }

                return (false);
            }
        }
        $zip = new ZipArchive();

        if (!$zip->open($filename, ZIPARCHIVE::CREATE)) {
            if (self::$debug_mode) {
                Tools::displayError(sprintf(
                    '%s(%s): '.$this->l('Unable to open zip for writing: %s'),
                    basename(__FILE__),
                    __LINE__,
                    $zipfile
                ));
            }

            return (false);
        }

        foreach ($from as $key => $origin) {
            if (self::$debug_mode) {
                printf('Trying to add: %s to %s'."\n<br>", $origin, $filename);
            }

            if (!$zip->addFile($origin)) {
                if (self::$debug_mode) {
                    Tools::displayError(sprintf(
                        '%s(%s): '.$this->l('Unable to add to zip: %s'),
                        basename(__FILE__),
                        __LINE__,
                        $origin
                    ));
                }

                return (false);
            } else {
                if (self::$debug_mode) {
                    printf('Added: %s'."\n<br>", $origin);
                }
            }
        }
        $zip->close();

        return (true);
    }
}
