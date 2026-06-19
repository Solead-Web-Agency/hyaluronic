<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminThumbGenController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function postProcess()
    {
        $result = array('status' => 'error');
        $errors = array(
            'key' => $this->module->notifications['validation']['key'],
            'thumbnail_generation' => $this->module->notifications['thumbnail_generation']['error'],
        );

        if (Tools::getValue('secureKey') == $this->module->secure_key) {
            switch (Tools::getValue('action')) {
                case 'generateThumbnails':
                    $progress = $this->module->generateThumbnails(
                        Tools::getValue('imageGroup'),
                        Tools::getValue('imageTypes'),
                        Tools::getValue('mode')
                    );

                    if (is_numeric($progress['percent'])) {
                        $result = $progress;
                        $result['status'] = 'success';
                    } else {
                        $result['message'] = $errors['thumbnail_generation'];
                    }

                    break;

                case 'saveChecked':
                    $ids = Tools::getValue('imageTypes');
                    $value = array();

                    if (!empty($ids) && is_array($ids)) {
                        $value = array_map('intval', $ids);
                    }

                    $this->module->updateSettings('image_types', $value);

                    break;

                case 'saveValue':
                    $name = Tools::getValue('name', false);
                    $value = Tools::getValue('value', false);

                    if (in_array($name, array('chunk'))) {
                        switch ($name) {
                            case 'chunk':
                                if (!Validate::isUnsignedInt($value) || $value > 10) {
                                    $value = 10;
                                }

                                break;

                            default:
                                $name = $value = null;
                                break;
                        }

                        $this->module->updateSettings($name, $value);
                        $result['status'] = 'success';
                    }

                    break;

                case 'downloadLog':
                    header('Content-Type: text/plain');
                    header('Content-Disposition: attachment; filename='.$this->module->log_name);
                    header('Pragma: no-cache');
                    readfile($this->module->log_path);
                    exit();

                case 'clearLog':
                    if ($this->module->clearLog()) {
                        $result['status'] = 'success';
                    }

                    break;

                case 'showLog':
                    $log = $this->module->displayLog();

                    if (!empty($log)) {
                        $result['status'] = 'success';
                        $result['message'] = $log;
                    }

                    break;

                default:
                    break;
            }
        } else {
            $result['message'] = $errors['key'];
        }

        header('Content-Type: application/json');
        exit(Tools::jsonEncode($result));
    }
}
