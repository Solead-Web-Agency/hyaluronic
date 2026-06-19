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

include_once dirname(__FILE__).'/ArContactUsAbstract.php';
include_once dirname(__FILE__).'/ArContactUsTools.php';

class ArContactUsTable extends ObjectModel
{
    const TYPE_LINK = 0;
    const TYPE_INTEGRATION = 1;
    const TYPE_JS = 2;
    const TYPE_CALLBACK = 3;
    
    const TABLE_NAME = 'arcontactus';
    
    public $id;
    public $icon;
    public $type;
    public $display;
    public $link;
    public $js;
    public $integration;
    public $color;
    public $position;
    public $status;
    public $registered_only;
    public $target;
    public $product_page;
    
    public $always;
    public $d1;
    public $d2;
    public $d3;
    public $d4;
    public $d5;
    public $d6;
    public $d7;
    public $time_from;
    public $time_to;
    public $id_shop;
    public $data;
    
    public $title;
    public $subtitle;
    


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'id_contactus',
        'multilang' => true,
        'fields' => array(
            'icon' =>               array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'link' =>               array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'type' =>               array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'display' =>            array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'registered_only' =>    array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'js' =>                 array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'color' =>              array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'position' =>           array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'status' =>             array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'target' =>             array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'product_page' =>       array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'always' =>             array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'd1' =>                 array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'd2' =>                 array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'd3' =>                 array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'd4' =>                 array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'd5' =>                 array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'd6' =>                 array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'd7' =>                 array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'time_from' =>          array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'time_to' =>            array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'integration' =>        array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'id_shop' =>            array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'data' =>               array('type' => self::TYPE_HTML),
            
            /* Lang fields */
            'title' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'required' => false,
                'size' => 255
            ),
            'subtitle' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'required' => false,
                'size' => 255
            )
        ),
    );
    
    public static function getDisplayClass($display)
    {
        if ($display == 2) { // desktop only
            return 'ar-desktop-only';
        } elseif ($display == 3) { // mobile only
            return 'ar-mobile-only';
        }
    }
    
    public static function getLink($link)
    {
        $linkObject = Context::getContext()->link;
        if ($link == '{contact}') {
            return $linkObject->getPageLink('contact');
        }
        if ($link == '{callback}') {
            return null;
        }
        return $link;
    }
    
    public static function getIcon($name, $data = array())
    {
        if (!is_array($data)) {
            $data = ArContactUsTools::jsonDecode($data, true);
        }
        if (isset($data['icon_type']) && $data['icon_type'] == 'fa') {
            return $data['icon_svg'];
        }
        if (isset($data['icon_type']) && $data['icon_type'] == 'uploaded') {
            $module = Module::getInstanceByName('arcontactus');
            return '<img src="' . $module->getUploadsUrl() . $data['icon_img'] . '" />';
        }
        if ($name) {
            return ArContactUsAbstract::getIcon($name);
        }
        return null;
    }
    
    public static function getAll($id_lang, $activeOnly = false, $day = false, $time = false, $logged = null, $product_page = false, $id_shop = null)
    {
        $sql = new DbQuery();
        $sql->join('LEFT JOIN ' . _DB_PREFIX_ .  self::TABLE_NAME . '_lang l ON l.id_contactus = t.id_contactus');
        $sql->from(self::TABLE_NAME, 't');
        $where = array('l.id_lang = ' . (int)$id_lang);
        if ($activeOnly) {
            $where[] = 't.status = 1';
        }
        if ($product_page) {
            $where[] = 't.product_page = 1';
        }
        if ($day !== false) {
            $dayField = 'd' . (int)$day;
            $where[] = '(always = 1 OR ' . $dayField . ' = 1)';
            if ($time) {
                $where[] = "((time_from <= '" . $time . "' AND time_" . "to >= '" . $time . "') OR always = 1)";
            }
        }
        if ($logged !== null) {
            if ($logged) {
                $where[] = '(registered_only IN (0, 1) OR registered_only IS NULL)';
            } else {
                $where[] = '(registered_only IN (0, 2) OR registered_only IS NULL)';
            }
        }
        if ($id_shop !== null) {
            $where[] = 'id_shop IN (0,' . (int)$id_shop . ')';
        }
        $sql->where(implode(' AND ', $where));
        $sql->orderBy('position ASC');
        $res = Db::getInstance()->executeS($sql);
        
        foreach ($res as $k => $row) {
            $res[$k]['icon_content'] = self::getIcon($row['icon'], $row['data']);
            $res[$k]['url'] = self::getLink($row['link']);
            $res[$k]['js'] = Tools::stripslashes($row['js']);
        }
        return $res;
    }
    
    /**
     * Checks if object field values are valid before database interaction
     *
     * @param bool $die
     * @param bool $error_return
     *
     * @return bool|string True, false or error message.
     * @throws PrestaShopException
     */
    public function validateFields($die = true, $error_return = false)
    {
        $errors = array();
        foreach ($this->def['fields'] as $field => $data) {
            if (!empty($data['lang'])) {
                continue;
            }

            if (is_array($this->update_fields) && empty($this->update_fields[$field]) && isset($this->def['fields'][$field]['shop']) && $this->def['fields'][$field]['shop']) {
                continue;
            }

            $message = $this->validateField($field, $this->$field, null, array(), true);
            if ($message !== true) {
                if ($die) {
                    throw new PrestaShopException($message);
                }
                $errors[$field] = $message;
            }
        }
        if (!$this->always) {
            if (!$this->validateTime($this->time_from)) {
                $errors['time_from'] = 'Wrong time';
            }
            if (!$this->validateTime($this->time_to)) {
                $errors['time_to'] = 'Wrong time';
            }
            if (!$this->validateInterval()) {
                $errors['time_from'] = 'Wrong time interval. Time in first field might be less then time in second field';
            }
        }
        if ($errors && $error_return) {
            return $errors;
        }
        return true;
    }
    
    public function validateInterval()
    {
        return $this->timeToSeconds($this->time_from) < $this->timeToSeconds($this->time_to);
    }
    
    public function timeToSeconds($value)
    {
        $data = explode(':', $value);
        if (count($data) != 3) {
            return false;
        }
        $d1 = (int)$data[0];
        $d2 = (int)$data[1];
        $d3 = (int)$data[2];
        return ($d1 * 3600) + ($d2 * 60) + $d3;
    }
    
    public function validateTime($value)
    {
        $data = explode(':', $value);
        if (count($data) != 3) {
            return false;
        }
        $d1 = (int)$data[0];
        $d2 = (int)$data[1];
        $d3 = (int)$data[2];
        if ($d1 < 0 || $d1 > 23) {
            return false;
        }
        if ($d2 < 0 || $d2 > 59) {
            return false;
        }
        if ($d3 < 0 || $d3 > 59) {
            return false;
        }
        return true;
    }
    
    public static function getLastPosition()
    {
        $sql = new DbQuery();
        $sql->select('position');
        $sql->from(self::TABLE_NAME);
        $sql->orderBy('position DESC');
        return Db::getInstance()->getValue($sql);
    }
}
