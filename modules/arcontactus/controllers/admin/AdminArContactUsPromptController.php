<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

include_once dirname(__FILE__).'/../../classes/ArContactUsPromptTable.php';
include_once dirname(__FILE__).'/../../classes/ArContactUsTools.php';

class AdminArContactUsPromptController extends ModuleAdminController
{
    public function __construct()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();
        $this->meta_title = $this->l('Contact us');
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }
    
    public function ajaxProcessSave()
    {
        $data = Tools::getValue('data');
        $id = Tools::getValue('id');
        $title = array();
        $errors = array();
        if (!$id) {
            $model = new ArContactUsPromptTable();
            $model->status = 1;
            $model->position = ArContactUsPromptTable::getLastPosition() + 1;
        } else {
            $model = new ArContactUsPromptTable($id);
        }
        foreach ($data as $param) {
            if (Tools::strpos($param['name'], 'message') !== false) {
                $lang = str_replace('message_', '', $param['name']);
                $title[$lang] = stripcslashes(str_replace('\n', "\n", pSQL($param['value'], true)));
                if (Tools::isEmpty($title[$lang])) {
                    $language = new Language($lang);
                    $errors['message'][] = array(
                        'id_lang' => $lang,
                        'error' => sprintf($this->module->l('Field "Message" is required for language "%s"'), $language->iso_code)
                    );
                }
            }
            if ($param['name'] == 'id_shop') {
                $model->id_shop = (int)$param['value'];
            }
        }
        $model->message = $title;
        
        $modelErrors = $model->validateFields(false, true);
        if ($modelErrors !== true) {
            $errors = array_merge($errors, $modelErrors);
        }
        if ($errors) {
            die(ArContactUsTools::jsonEncode(array(
                'success' => 0,
                'errors' => $errors
            )));
        }
        $model->save();
        $this->module->clearCache();
        die(ArContactUsTools::jsonEncode(array(
            'success' => 1,
            'errors' => $errors,
            'model' => $model
        )));
    }
    
    public function ajaxProcessReload()
    {
        die(ArContactUsTools::jsonEncode(array(
            'content' => $this->module->renderPromptTable()
        )));
    }
    
    public function ajaxProcessReorder()
    {
        $data = Tools::getValue('data');
        foreach ($data as $item) {
            $k = explode('_', $item);
            Db::getInstance()->update(ArContactUsPromptTable::TABLE_NAME, array(
                'position' => (int)$k[1]
            ), 'id_prompt = ' . (int)$k[0]);
        }
        $this->module->clearCache();
        die(ArContactUsTools::jsonEncode(array()));
    }
    
    public function ajaxProcessSwitch()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsPromptTable($id);
        $model->status = !$model->status;
        $model->save();
        $this->module->clearCache();
        die(ArContactUsTools::jsonEncode(array(
            'active' => (int)$model->status
        )));
    }
    
    public function ajaxProcessEdit()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsPromptTable($id);
        die(ArContactUsTools::jsonEncode($model));
    }
    
    public function ajaxProcessDelete()
    {
        $id = Tools::getValue('id');
        $model = new ArContactUsPromptTable($id);
        die(ArContactUsTools::jsonEncode(array(
            'success' => $model->delete()
        )));
    }
}
