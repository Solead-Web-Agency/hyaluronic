<?php
/**
 * 2007-2020 ETS-Soft
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
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2020 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class AdminEtsTransConfigController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function renderOptions()
    {
        return $this->module->renderConfig();
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Tools::isSubmit('saveEtstransSettings')) {
            $errors = array();
            $languages = Language::getLanguages(false);
            $api = new EtsTransApi();
            $fields = $this->module->getFieldForm();

            if((int)Tools::getValue('ETS_TRANS_AUTO_SETTING_ENABLED')){
                $fields['ETS_TRANS_LANG_SOURCE']['required'] = true;
                $fields['ETS_TRANS_LANG_TARGET']['required'] = true;
                $fields['ETS_TRANS_FILED_TRANS']['required'] = true;
            }
            $contextualWordsConfig = array('ETS_TRANS_CONTEXT_WORDS', 'ETS_TRANS_PAGE_APPEND_CONTEXT_WORD');
            foreach ($fields as $field) {
                $fieldVal = Tools::getValue($field['name']);
                if(isset($field['lang']) && $field['lang'] && isset($field['required']) && $field['required']){
                    if(!Tools::getValue($field['name'] . '_' . Configuration::get('PS_LANG_DEFAULT')))
                        $errors[] = isset($field['error_message']['required']) ? $field['error_message']['required'] : $field['name'].' '.$this->l('is required');
                    elseif(isset($field['validate']) && $field['validate']){
                        foreach ($languages as $lang){
                            if(($langVal = Tools::getValue($field['name'] . '_' . $lang['id_lang'])) && !Validate::{$field['validate']}($langVal)){
                                $errors[] = isset($field['error_message']['validate']) ? '"'.$lang['iso_code'].'" '.$field['error_message']['validate'] : '"'.$lang['iso_code'].'" '.$field['name'].' '.$this->l('is invalid');
                            }
                        }
                    }
                }
                elseif (!$fieldVal && isset($field['required']) && $field['required']) {
                    if(!in_array($field['name'], $contextualWordsConfig) || (in_array($field['name'], $contextualWordsConfig) && (int)Tools::getValue('ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD')))
                        $errors[] = $field['error_message']['required'];
                }
                elseif ($fieldVal && isset($field['validate']) && $field['validate']) {
                    if (!Validate::{$field['validate']}($fieldVal)) {
                        $errors[] = $field['error_message']['validate'];
                    }
                }
                elseif($field['name'] == 'ETS_TRANS_MAX_WORD_APPEND_CONTEXT_WORD' && Tools::getValue('ETS_TRANS_MAX_WORD_APPEND_CONTEXT_WORD') == '0'){
                    $errors[] =$this->l('The "Append contextual words when original text to translate fewer than (or equals)" must be greater than zero');
                }
                if($fieldVal && isset($field['isApiKey']) && $field['isApiKey'] && !$api->validateApiKey($fieldVal)){
                    $errors[] = $field['error_message']['isApiKey'];
                }
            }
            if($errors){
                $this->errors = $errors;
            }
            else{
                $langDefault = Configuration::get('PS_LANG_DEFAULT');
                foreach ($fields as $field){
                    $fieldVal = Tools::getValue($field['name']);
                    if (isset($field['lang']) && $field['lang']) {
                        $value = array();
                        foreach ($languages as $lang) {
                            $value[$lang['id_lang']] = ($langValue = Tools::getValue($field['name'] . '_' . $lang['id_lang'])) ? $langValue : Tools::getValue($field['name'] . '_' . $langDefault);
                        }
                        Configuration::updateGlobalValue($field['name'], $value);
                        Configuration::updateValue($field['name'], $value);
                    }
                    else {
                        if(is_array($fieldVal)){
                            $fieldVal = implode(',', $fieldVal);
                        }
                        Configuration::updateGlobalValue($field['name'], $fieldVal);
                        Configuration::updateValue($field['name'], $fieldVal);
                    }
                }
                $this->confirmations = array($this->l('Configuration saved'));
            }
        }

    }
}