<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

require_once(dirname(__FILE__) . '/AdminEtsACOptionsController.php');

class AdminEtsACConfigsController extends AdminEtsACOptionsController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        $this->fields_options = array(
            'title' => $this->l('Automation', 'AdminEtsACConfigsController'),
            'fields' => $this->def->getFields('configs'),
            'icon' => '',
            'submit' => array(
                'title' => $this->l('Save', 'AdminEtsACConfigsController'),
            ),
            'name' => 'leave_website'
        );
    }

    public function renderOptions()
    {
        $this->context->smarty->assign(array(
            'menuTab' => array(
                'config' => array(
                    'name' => $this->l('Configuration', 'AdminEtsACConfigsController'),
                    'icon' => '',
                ),
                'log' => array(
                    'name' => $this->l('Cronjob log', 'AdminEtsACConfigsController'),
                    'icon' => '',
                ),
            ),
            'cronjobLog' => @file_exists(($file = _PS_CACHE_DIR_ . '/' . $this->module->name . '/cronjob.log')) ? Tools::file_get_contents($file) : '',
        ));

        return parent::renderOptions();
    }

    protected function ajaxProcessClearLog()
    {
        if (@file_exists(($file = _PS_CACHE_DIR_ . '/' . $this->module->name . '/cronjob.log'))) {
            if (!@unlink($file)) {
                $this->errors[] = $this->l('Cannot clear cronjob log. Check permission file.', 'AdminEtsACConfigsController');
            }
        } else
            $this->errors[] = $this->l('Cronjob log is cleaned', 'AdminEtsACConfigsController');
        $hasError = count($this->errors) > 0 ? true : false;

        $this->toJson(array(
            'errors' => $hasError,
            'msg' => $hasError ? implode(PHP_EOL, $this->errors) : $this->l('Clear cronjob log successfully', 'AdminEtsACConfigsController'),
        ));
    }

    protected function ajaxProcessCronjobExecute()
    {
        EtsAbancartTools::getInstance()->runCronjob($this->context->shop->id, true);
    }

}