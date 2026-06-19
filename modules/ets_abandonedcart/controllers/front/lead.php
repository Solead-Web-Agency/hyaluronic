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

class Ets_abandonedcartLeadModuleFrontController extends ModuleFrontController
{
    public $allowFiles;
    public $idForm;
    public $formItem;
    public function __construct()
    {
        parent::__construct();
        $this->allowFiles = array('png', 'jpg', 'gif', 'zip', 'pdf');
        $this->formItem = null;
        $this->formFields = null;
        $this->idForm = null;
    }

    public function initContent()
    {
        parent::initContent();

        if($this->idForm && $this->formItem){
            $formItem = $this->formItem;
            $fields = EtsAbancartField::getAllFields(true, $formItem['id_ets_abancart_form'], $this->context->language->id);
            $this->formFields = $fields;
            $formItem['fields'] = $fields;
            $this->context->smarty->assign(array(
                'leadFormContent'=> $this->getFormContent($formItem),
                'moduleDir' => _PS_MODULE_DIR_.$this->module->name,
                'actionForm' => $this->context->link->getModuleLink($this->module->name, 'lead', array('url_alias'=> $formItem['alias'])),
                'path' => $this->module->getBreadCrumb(),
                'breadcrumb' => $this->module->is17 ? $this->module->getBreadCrumb() : false,
            ));

            if($this->module->is17)
                $this->setTemplate('module:'.$this->module->name.'/views/templates/front/lead_form.tpl');
            else
                $this->setTemplate('lead_form16.tpl');
        }
        else
            Tools::redirect('404');
    }

    public function getFormContent($leadForm)
    {
        if($leadForm['fields']) {
            $dataPost = Tools::getValue('field');
            foreach ($leadForm['fields'] as &$item) {
               $val = isset($dataPost[$item['id_ets_abancart_field']]) ? $dataPost[$item['id_ets_abancart_field']] : '';
                if($val){
                    if(in_array($item['type'], array(EtsAbancartField::FIELD_TYPE_CHECKBOX, EtsAbancartField::FIELD_TYPE_SELECT, EtsAbancartField::FIELD_TYPE_RADIO))){

                        foreach ($item['options'] as &$op){

                            if(is_array($val) && $item['type'] == EtsAbancartField::FIELD_TYPE_CHECKBOX && in_array($op['value'],$val)){
                                $op['default'] = 1;
                            }
                            elseif(($item['type'] == EtsAbancartField::FIELD_TYPE_SELECT || $item['type'] == EtsAbancartField::FIELD_TYPE_RADIO) && $op['value'] == $val){
                                $op['default'] = 1;
                            }
                        }
                        if(isset($op)){
                            unset($op);
                        }
                    }
                    else {
                        $item['value'] = Validate::isCleanHtml($val) ? $val : '';
                    }
                }
            }
            if(isset($item)){
                unset($item);
            }
        }

        $this->context->smarty->assign(array(
            'lead_form' => $leadForm,
            'reminderType' => '',
            'field_types' => EtsAbancartField::getInstance()->getFieldType(),
            'formSubmit' => true,
        ));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->module->name.'/views/templates/hook/lead_form_short_code.tpl');
    }

    public function postProcess()
    {
        parent::postProcess();
        $isAjax = Tools::isSubmit('ajax');
        if(!$isAjax){
            $alias = Tools::getValue('url_alias');

            if($alias){
                $alias = Validate::isCleanHtml($alias) ? $alias : '';
                if($alias && ($formItem = EtsAbancartForm::getFormByAlias($alias,$this->context->language->id))){
                    $this->idForm = $formItem['id_ets_abancart_form'];
                    $this->formItem = $formItem;
                }
                else
                    Tools::redirect(404);
            }
        }
        if(Tools::isSubmit('submitEtsAcLeadForm') || Tools::isSubmit('submitEtsAcLeadFormPost')){

            if($isAjax)
                $idForm = (int)Tools::getValue('id_form');
            else
                $idForm = $this->idForm;
            $form = new EtsAbancartForm($idForm);
            $fieldsForm = EtsAbancartField::getAllFields(true, $idForm, $this->context->language->id);
            $fields = Tools::getValue('field', array());

            foreach ($fieldsForm as $item){
                $value = isset($fields[$item['id']]) ? $fields[$item['id']] : '';
                if(!is_array($value)){
                    $value = trim($value);
                }
                if($item['required'] && !$value){
                    $this->errors[] = sprintf($this->module->l('%s is required','lead'), $item['name']);
                }
                elseif(!is_array($value) && !Validate::isCleanHtml($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $item['name']);
                }
                elseif(is_array($value)){
                    foreach ($value as $i){
                        if(is_array($i) || !Validate::isCleanHtml($i)){
                            $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $item['name']);
                            break;
                        }
                    }
                }
                else{
                    $this->validateField('field', $item['id'],$item['name'], $item['type'], $item['content'], $value);
                }
            }
            $captchav3Response = ($captchav3Response = Tools::getValue('captcha_v3_response')) && Validate::isCleanHtml($captchav3Response) ? $captchav3Response : '';
            if($form->enable_captcha && $form->captcha_type == 'v3' && (!$form->disable_captcha_lic || ($form->disable_captcha_lic && !$this->context->customer->isLogged()))){
                if(!$captchav3Response)
                    $this->errors[] = $this->module->l('Captcha verification is not exists');
                else{
                    $responseCv3 = EtsAbancartTools::request('POST', 'https://www.google.com/recaptcha/api/siteverify', array('secret'=> $form->captcha_secret_key_v3, 'response'=> $captchav3Response, 'remoteip' => Tools::getRemoteAddr()));
                    if($responseCv3){
                        $responseCv3 = Tools::jsonDecode($responseCv3, true);
                        if(!isset($responseCv3['success']) || !$responseCv3['success']){
                            $this->errors[] = $this->module->l('Cannot verify captcha');
                        }
                    }
                }
            }

            if(!$this->errors){
                $idReminder = $isAjax ? (int)Tools::getValue('id_reminder') : (int)Tools::getValue('idReminder');
                $idCart = (int)Tools::getValue('id_cart');
                $formSubmit = new EtsAbancartFormSubmit();
                $formSubmit->id_ets_abancart_form = $idForm;
                $formSubmit->id_cart = $idCart;
                $formSubmit->id_customer = $this->context->customer->isLogged() ? $this->context->customer->id : 0;
                $formSubmit->id_ets_abancart_reminder = $idReminder > 0 ?: 0;
                $formSubmit->is_leaving_website = $idReminder == -1 ? 1 : 0;
                $formSubmit->id_lang = $this->context->language->id;
                $formSubmit->id_currency = $this->context->currency->id;
                $formSubmit->id_country = ($this->context->country ? $this->context->country->id : 0);
                if( $formSubmit->add(true)) {
                    foreach ($fieldsForm as $item) {
                        $value = isset($fields[$item['id']]) ? $fields[$item['id']] : '';
                        if (!is_array($value)) {
                            $value = trim($value);
                        }
                        $fileName = null;
                        if ($item['type'] == EtsAbancartField::FIELD_TYPE_FILE && isset($_FILES['field']) && isset($_FILES['field']['name'][$item['id']])) {
                            $value = $this->uploadFile($_FILES['field'], $item['id']);
                            $fileName = str_replace(array(' ', '(', ')'), array('', '', ''), $_FILES['field']['name'][$item['id']]);
                        }

                        $fieldValue = new EtsAbancartFieldValue();
                        $fieldValue->id_ets_abancart_form_submit = $formSubmit->id;
                        $fieldValue->id_ets_abancart_field = $item['id'];
                        $fieldValue->value = is_array($value) ? implode(', ', $value) : ($value ?: null);
                        $fieldValue->file_name = isset($fileName) ? $fileName : '';
                        $fieldValue->add();
                    }
                }

                if(Tools::isSubmit('ajax')){
                    $this->context = EtsAbancartForm::setLeadFormCookie($form->id, $this->context);
                    die(Tools::jsonEncode(array(
                        'success' => true,
                        'message' => $this->module->l('Submitted successfully', 'lead'),
                        'display_thankyou_page' => $form->display_thankyou_page ? true : false,
                        'thankyou' => $form->display_thankyou_page ? $this->getThankyouPage($form) : '',
                    )));
                }
                else{
                    if($form->display_thankyou_page){
                        Tools::redirect($this->context->link->getModuleLink($this->module->name, 'thank', array('url_alias'=> $form->thankyou_page_alias)));
                    }
                    else{
                        $this->success[] = $this->l('Submitted successfully');
                    }
                }
            }
            if(Tools::isSubmit('ajax')) {
                die(Tools::jsonEncode(array(
                    'success' => false,
                    'message' => $this->errors ?: array($this->module->l('Cannot submit form', 'lead')),
                )));
            }
        }
    }

    public function validateField($key, $id,$name, $type,$content, $value)
    {
        $options = array();
        if($content){
            $listOptions = EtsAbancartField::generateOptions($content, $type);
            if($listOptions){
                foreach ($listOptions as $i){
                    $options[] = $i['value'];
                }
            }
        }
        switch ($type){
            case EtsAbancartField::FIELD_TYPE_TEXT:
                if(!Validate::isString($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                }
                break;
            case EtsAbancartField::FIELD_TYPE_EMAIL:
                if(!Validate::isEmail($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                }
                break;
            case EtsAbancartField::FIELD_TYPE_PHONE:
                if(!Validate::isPhoneNumber($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                }
                break;
            case EtsAbancartField::FIELD_TYPE_NUMBER:
                if(!Validate::isFloat($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                }
                break;
            case EtsAbancartField::FIELD_TYPE_TEXTAREA:
                if(!Validate::isCleanHtml($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                }
                break;
            case EtsAbancartField::FIELD_TYPE_RADIO:
            case EtsAbancartField::FIELD_TYPE_SELECT:
                if(is_array($value) || !Validate::isCleanHtml($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                }
                break;
            case EtsAbancartField::FIELD_TYPE_CHECKBOX:
                if($value && !is_array($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                }
                elseif ($value){
                    foreach ($value as $item){
                        if(!is_array($item, $options)){
                            $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                            break;
                        }
                    }
                }
                break;
            case EtsAbancartField::FIELD_TYPE_FILE:
                if(isset($_FILES[$key]['name'][$id]) && $_FILES[$key]['name'][$id]){
                    $fileName = str_replace(array(' ', '(', ')'), array('','',''), $_FILES[$key]['name'][$id]);
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $maxSize = (float)Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1020*1024;
                    if(!Validate::isFileName($fileName)){
                        $this->errors[] = sprintf($this->module->l('"%s" file name is invalid','lead'), $name);
                    }
                    elseif(!in_array($ext, $this->allowFiles)){
                        $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                    }
                    elseif((float)$_FILES[$key]['size'][$id] > $maxSize){
                        $this->errors[] = sprintf($this->module->l('%s is too large','lead'), $name);
                    }
                }
                break;
            case EtsAbancartField::FIELD_TYPE_DATE:
                if($value && !Validate::isDate($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                }
                break;
            case EtsAbancartField::FIELD_TYPE_DATE_TIME:
               if($value && !Validate::isDateFormat($value)){
                    $this->errors[] = sprintf($this->module->l('%s is invalid','lead'), $name);
                }
                break;
        }
    }

    public function uploadFile($file, $key=null)
    {
        $fName = $key ? (isset($file['name'][$key]) ? $file['name'][$key] : null) : (isset($file['name']) ? $file['name'] : null);
        $fTmp = $key ? (isset($file['tmp_name'][$key]) ? $file['tmp_name'][$key] : null) : (isset($file['tmp_name']) ? $file['tmp_name'] : null);
        if(isset($file) && isset($fName) && $fName){
            $uploadDir = _PS_DOWNLOAD_DIR_.$this->module->name;
            if(!is_dir($uploadDir)){
                mkdir($uploadDir, 0755);
                @copy(dirname(__FILE__).'/index.php', $uploadDir.'/index.php');
            }
            $ext = pathinfo($fName, PATHINFO_EXTENSION);
            $fileName = 'file_field_'.microtime(true).rand(111,99999).'.'.$ext;
            if(move_uploaded_file($fTmp, $uploadDir.'/'.$fileName)){
                return $fileName;
            }
        }
        return false;
    }

    public function getThankyouPage(EtsAbancartForm $form)
    {
        if($form->display_thankyou_page){
            $this->context->smarty->assign(array(
                'tp_title' => is_array($form->thankyou_page_title) ? $form->thankyou_page_title[$this->context->language->id] : $form->thankyou_page_title,
                'tp_content' => is_array($form->thankyou_page_content) ? $form->thankyou_page_content[$this->context->language->id] : $form->thankyou_page_content,
            ));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->module->name.'/views/templates/front/thankyou_page_block.tpl');
        }
        return '';
    }

}