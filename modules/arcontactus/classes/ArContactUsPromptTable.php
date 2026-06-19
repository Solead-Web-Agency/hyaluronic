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

class ArContactUsPromptTable extends ObjectModel
{
    const TABLE_NAME = 'arcontactus_prompt';
    
    public $id;
    public $position;
    public $status;
    public $id_shop;
    
    public $message;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'id_prompt',
        'multilang' => true,
        'fields' => array(
            'position' =>           array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'status' =>             array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_shop' =>            array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            
            /* Lang fields */
            'message' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => false
            )
        ),
    );
    
    public static function getAll($id_lang, $activeOnly = false, $id_shop = null)
    {
        $sql = new DbQuery();
        $sql->join('LEFT JOIN ' . _DB_PREFIX_ .  self::TABLE_NAME . '_lang l ON l.id_prompt = t.id_prompt');
        $sql->from(self::TABLE_NAME, 't');
        if ($activeOnly) {
            $where = 'l.id_lang = ' . (int)$id_lang . ' AND t.status = 1';
        } else {
            $where = 'l.id_lang = ' . (int)$id_lang;
        }
        if ($id_shop !== null) {
            $where .= ' AND id_shop IN (0,' . (int)$id_shop . ')';
        }
        $sql->where($where);
        $sql->orderBy('position ASC');
        return Db::getInstance()->executeS($sql);
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
        if ($errors && $error_return) {
            return $errors;
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
