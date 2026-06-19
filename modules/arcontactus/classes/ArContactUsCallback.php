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

class ArContactUsCallback extends ObjectModel
{
    const TABLE_NAME = 'arcontactus_callback';
    
    public $id;
    public $id_user;
    public $phone;
    public $name;
    public $email;
    public $referer;
    public $created_at;
    public $updated_at;
    public $status;
    public $comment;
    public $id_shop;

    const STATUS_NEW = 0;
    const STATUS_DONE = 1;
    const STATUS_IGNORE = 2;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'id_callback',
        'multilang' => false,
        'fields' => array(
            'id_user' =>            array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'phone' =>              array('type' => self::TYPE_STRING),
            'name' =>               array('type' => self::TYPE_STRING),
            'referer' =>            array('type' => self::TYPE_STRING),
            'email' =>              array('type' => self::TYPE_STRING),
            'created_at' =>         array('type' => self::TYPE_STRING),
            'updated_at' =>         array('type' => self::TYPE_STRING),
            'status' =>             array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'comment' =>            array('type' => self::TYPE_STRING),
            'id_shop' =>            array('type' => self::TYPE_INT),
        ),
    );
    
    public static function getAll($params)
    {
        $pageSize = isset($params['selected_pagination'])? $params['selected_pagination'] : 50;
        $page = isset($params['page'])? $params['page'] - 1 : 0;
        $offset = isset($params['page'])? $pageSize * $page : 0;
        $sql = new DbQuery();
        $sql->from(self::TABLE_NAME, 't');
        $sql->orderBy('created_at DESC');
        $filters = isset($params['filter'])? $params['filter'] : array();
        $where = self::processFilters($filters, array(
            't.id_shop = ' . (int)Context::getContext()->shop->id
        ));
        $sql->where($where);
        $sql->limit($pageSize, $offset);
        $res = Db::getInstance()->executeS($sql);
        
        return $res;
    }
    
    public static function addCallback($id_user, $phone, $name, $referer, $email)
    {
        $model = new self();
        $model->id_user = (int)$id_user;
        $model->phone = pSQL($phone);
        $model->name = pSQL($name);
        $model->referer = pSQL($referer);
        $model->email = pSQL($email);
        $model->created_at = date('Y-m-d H:i:s');
        $model->id_shop = (int)Context::getContext()->shop->id;
        $model->save();
        return $model;
    }
    
    public static function getCount($params = array())
    {
        $query = new DbQuery();
        $query->from(self::TABLE_NAME, 't');
        $query->select('COUNT(1) c');
        $filters = isset($params['filter'])? $params['filter'] : array();
        $where = self::processFilters($filters, array(
            't.id_shop = ' . (int)Context::getContext()->shop->id
        ));
        $query->where($where);
        $res = Db::getInstance()->getRow($query);
        return $res['c'];
    }
    
    public static function processFilters($params, $initParams = array())
    {
        $where = $initParams;
        $model = new self();
        foreach ($params as $k => $value) {
            if (property_exists($model, $k) && $value != '') {
                if ($k == 'id') {
                    $k = 'id_callback';
                }
                
                if ($k == 'phone') {
                    if (strpos($value, '%') !== false) {
                        $where[] = "t.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "t.`" . $k . "` LIKE '%" . pSQL($value) . "%'";
                    }
                } elseif ($k == 'checked') {
                    if ($value) {
                        $where[] = "t.`" . $k . "` = '" . pSQL($value) . "'";
                    } else {
                        $where[] = "(t.`" . $k . "` IS NULL OR sp.`" . $k . "` = 0)";
                    }
                } else {
                    if (strpos($value, '%') !== false) {
                        $where[] = "t.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "t.`" . $k . "` = '" . pSQL($value) . "'";
                    }
                }
            }
        }
        return implode(' AND ', $where);
    }
}
