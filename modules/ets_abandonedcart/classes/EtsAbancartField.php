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

class EtsAbancartField extends ObjectModel
{
    public $id_ets_abancart_form;
    public $type;
    public $position;
    public $required;
    public $enable;
    public $display_column;
    public $is_contact_name;
    public $is_contact_email;

    //Multi lang
    public $name;
    public $description;
    public $content;
    public $placeholder;

    public static $instance = null;

    const FIELD_TYPE_TEXT = 1;
    const FIELD_TYPE_EMAIL = 2;
    const FIELD_TYPE_PHONE = 3;
    const FIELD_TYPE_NUMBER = 4;
    const FIELD_TYPE_TEXTAREA = 5;
    const FIELD_TYPE_RADIO = 6;
    const FIELD_TYPE_CHECKBOX = 7;
    const FIELD_TYPE_SELECT = 8;
    const FIELD_TYPE_FILE= 9;
    const FIELD_TYPE_DATE= 10;
    const FIELD_TYPE_DATE_TIME= 11;

    public static $definition = array(
        'table' => 'ets_abancart_field',
        'primary' => 'id_ets_abancart_field',
        'multilang' => true,
        'fields' => array(
            'id_ets_abancart_form' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'type' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'required' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'enable' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'display_column' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'is_contact_name' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'is_contact_email' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            //Multi lang
            'name' => array('type' => self::TYPE_STRING, 'lang'=>true, 'validate' => 'isString'),
            'description' => array('type' => self::TYPE_STRING,'lang'=>true, 'validate' => 'isString'),
            'content' => array('type' => self::TYPE_STRING,'lang'=>true, 'validate' => 'isString'),
            'placeholder' => array('type' => self::TYPE_STRING,'lang'=>true, 'validate' => 'isString'),
        )
    );

    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new EtsAbancartField();
        }

        return self::$instance;
    }

    public function l($string)
    {
        return Translate::getModuleTranslation('ets_abandonedcart', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }

    public function delete()
    {
        $idField = $this->id;
        $fieldType = $this->type;
        if(parent::delete()){
            if($fieldType == EtsAbancartField::FIELD_TYPE_FILE){
                $fieldValues = Db::getInstance()->executeS("SELECT `value` FROM `"._DB_PREFIX_."ets_abancart_field_value` WHERE id_ets_abancart_field=".(int)$idField." AND `value` IS NOT NULL AND `value` != ''");
                if($fieldValues){
                    foreach ($fieldValues as $item){
                        if(file_exists(_PS_DOWNLOAD_DIR_.'ets_abandonedcart/'.$item['value'])){
                            unlink(_PS_DOWNLOAD_DIR_.'ets_abandonedcart/'.$item['value']);
                        }
                    }
                }
            }
            return Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."ets_abancart_field_value` WHERE id_ets_abancart_field=".(int)$idField);
        }
        return false;
    }

    public static function getAllFields($active = false, $idForm=null, $idLang = null)
    {
        $where = "";
        if($active){
            $where .= " AND f.enable=1";
        }
        if($idForm){
            $where .= " AND f.id_ets_abancart_form=".(int)$idForm;
        }
        if($idLang){
            $where .= " AND fl.id_lang=".(int)$idLang;
        }
        $fields = Db::getInstance()->executeS("
            SELECT f.*, fl.* FROM `"._DB_PREFIX_."ets_abancart_field` f 
            LEFT JOIN `"._DB_PREFIX_."ets_abancart_field_lang` fl ON fl.id_ets_abancart_field = f.id_ets_abancart_field WHERE 1 ".(string)$where." ORDER BY `position` ASC");
        $results = array();
        if($fields){
            foreach ($fields as $field){
                if($idLang){
                    $field['id'] = $field['id_ets_abancart_field'];
                    $field['options'] = EtsAbancartField::generateOptions($field['content'], $field['type']);
                    $results[] = $field;
                }
                else{
                    if(!isset($results[$field['id_ets_abancart_field']])){
                        $results[$field['id_ets_abancart_field']] = array(
                            'id_ets_abancart_field' => $field['id_ets_abancart_field'],
                            'id' => $field['id_ets_abancart_field'],
                            'id_ets_abancart_form' => $field['id_ets_abancart_form'],
                            'type' => $field['type'],
                            'position' => (int)$field['position'],
                            'required' => (int)$field['required'],
                            'display_column' => (int)$field['display_column'],
                            'enable' => (int)$field['enable'],
                            'is_contact_name' => (int)$field['is_contact_name'],
                            'is_contact_email' => (int)$field['is_contact_email'],
                            'name' => array($field['id_lang'] => $field['name']),
                            'description' => array($field['id_lang'] => $field['description']),
                            'content' => array($field['id_lang'] => $field['content']),
                            'placeholder' => array($field['id_lang'] => $field['placeholder']),
                        );
                    }
                    else{
                        $results[$field['id_ets_abancart_field']]['name'][$field['id_lang']] = $field['name'];
                        $results[$field['id_ets_abancart_field']]['description'][$field['id_lang']] = $field['description'];
                        $results[$field['id_ets_abancart_field']]['content'][$field['id_lang']] = $field['content'];
                        $results[$field['id_ets_abancart_field']]['placeholder'][$field['id_lang']] = $field['placeholder'];
                        $results[$field['id_ets_abancart_field']]['options'][$field['id_lang']] = EtsAbancartField::generateOptions($field['content'], $field['type']);
                    }
                }
            }
        }
        return $results;
    }

    public function getFieldType()
    {
        return array(
            'text' => array(
                'key' => EtsAbancartField::FIELD_TYPE_TEXT,
                'title' => $this->l('Text'),
            ),
            'email' => array(
                'key' => EtsAbancartField::FIELD_TYPE_EMAIL,
                'title' => $this->l('Email'),
            ),
            'phone' => array(
                'key' => EtsAbancartField::FIELD_TYPE_PHONE,
                'title' => $this->l('Phone number'),
            ),
            'number' => array(
                'key' => EtsAbancartField::FIELD_TYPE_NUMBER,
                'title' => $this->l('Number'),
            ),
            'textarea' => array(
                'key' => EtsAbancartField::FIELD_TYPE_TEXTAREA,
                'title' => $this->l('Textarea'),
            ),
            'radio' => array(
                'key' => EtsAbancartField::FIELD_TYPE_RADIO,
                'title' => $this->l('Radio'),
            ),
            'checkbox' => array(
                'key' => EtsAbancartField::FIELD_TYPE_CHECKBOX,
                'title' => $this->l('Checkbox'),
            ),
            'select' => array(
                'key' => EtsAbancartField::FIELD_TYPE_SELECT,
                'title' => $this->l('Select'),
            ),
            'file' => array(
                'key' => EtsAbancartField::FIELD_TYPE_FILE,
                'title' => $this->l('File upload'),
            ),
            'date' => array(
                'key' => EtsAbancartField::FIELD_TYPE_DATE,
                'title' => $this->l('Date'),
            ),
            'datetime' => array(
                'key' => EtsAbancartField::FIELD_TYPE_DATE_TIME,
                'title' => $this->l('Date time'),
            ),
        );
    }

    public static function getFieldById($id)
    {
        return Db::getInstance()->getRow("
                SELECT * FROM `"._DB_PREFIX_."ets_abancart_field` f 
                LEFT JOIN `"._DB_PREFIX_."ets_abancart_field_lang` fl ON f.id_ets_abancart_field=fl.id_ets_abancart_field AND fl.id_lang=".(int)Context::getContext()->language->id." 
                WHERE f.id_ets_abancart_field=".(int)$id);
    }

    public static function getMaxPosition()
    {
        return Db::getInstance()->getValue("
                SELECT MAX(`position`) as max_pos FROM `"._DB_PREFIX_."ets_abancart_field`");
    }

    public static function generateOptions($content, $type)
    {
        if(!$content){
            return array();
        }
        $rows = explode("\n", $content);
        $options = array();
        $hasDefault = false;
        foreach ($rows as $row){
            $row = str_replace("\r", "", $row);
            if(!$row){
                continue;
            }
            $items = explode(':',$row);
            $opItem = array('default'=> 0, 'label' => '', 'value'=> '');
            if(count($items) >1 && trim(Tools::strtolower($items[count($items)-1])) == 'default'){
                if(!$hasDefault || ($hasDefault && $type == EtsAbancartField::FIELD_TYPE_CHECKBOX)) {
                    $opItem['default'] = 1;
                    $hasDefault = true;
                }
                $value = array();
                foreach ($items as $k=>$item){
                    if($k < count($items)-1)
                        $value[] = $item;
                }
                $textValue = implode(':', $value);
            }
            else{
                $textValue = $row;
            }

            $labelValue = explode('|', $textValue);
            if(count($labelValue) > 1){
                $opItem['label'] = $labelValue[0];
                $opItem['value'] = $labelValue[1];
            }
            else{
                $opItem['label'] = $textValue;
                $opItem['value'] = $textValue;
            }
            $options[] = $opItem;
        }

        return $options;
    }

}