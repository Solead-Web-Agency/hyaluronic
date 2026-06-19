<?php
/**
 * The file is controller. Do not modify the file if you want to upgrade the module in future
 * 
 * @author    Globo Jsc <contact@globosoftware.net>
 * @copyright 2020 Globo., Jsc
 * @license   please read license in file license.txt
 * @link	     http://www.globosoftware.net
 */

class GupselloffersModel extends ObjectModel
{
    public $id_g_upsellrule;
    public $showin_product = 0;
    public $showin_cart = 0;
    public $showin_cart_popup = 0;
    public $showin_home = 0;
    public $showin_collection = 0;
    public $qty = 1;
    public $apply_discount = 0;
    public $type_discount = 0;
    public $amount_discount;
    public $id_currency_discount;
    public $tax_discount;
    public $display_product = 'all_product';
    public $display_cateids;
    public $position;
    public $remove_product;
    public $remove_product_upsell;
    public $display_customqty;
    public $type_price;
    public $minimum_amount;
    public $maximum_amount;
    public $product_ids;
    public $product_combin_ids;
    public $product_displayids;
    public $product_displaycombin_ids;
    public $dateadd;
    public $dateup;
    public $name;
    public $title;
    public $description;
    public $button_popupname;
    public $showoffers = 0;
    public static $definition = array(
        'table' => 'g_upsellrule',
        'primary' => 'id_g_upsellrule',
        'multilang' => true,
        'fields' => array(
            'showin_product' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'showin_cart' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'showin_cart_popup' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'showin_home' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'showin_collection' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'qty'=> array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'apply_discount' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'type_discount' => array( 'type' => self::TYPE_STRING, 'validate' => 'isString'),
            'amount_discount'=> array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'id_currency_discount'=> array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'showoffers'=> array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'tax_discount'=> array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'display_product'=> array( 'type' => self::TYPE_STRING, 'validate' => 'isString'),
            'display_cateids' => array( 'type' => self::TYPE_STRING, 'validate' => 'isString'),
            'position' => array('type' => self::TYPE_INT),
            'remove_product' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'remove_product_upsell' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'display_customqty' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'type_price' =>  array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'minimum_amount' => array( 'type' => self::TYPE_STRING, 'validate' => 'isString'),
            'maximum_amount' => array( 'type' => self::TYPE_STRING, 'validate' => 'isString'),
            'product_ids' => array( 'type' => self::TYPE_STRING, 'validate' => 'isString'),
            'product_combin_ids' => array( 'type' => self::TYPE_STRING, 'validate' => 'isString'),
            'product_displayids' => array( 'type' => self::TYPE_STRING, 'validate' => 'isString'),
            'product_displaycombin_ids' => array( 'type' => self::TYPE_STRING, 'validate' => 'isString'),
            'dateadd' => array( 'type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'dateup' => array( 'type' => self::TYPE_DATE, 'validate' => 'isDate'),
            /*lang ps_lang*/
            'name' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'size' => 255,
                'required' => true),
            'title' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'size' => 255,
                'required' => true),
            'description' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'size' => 255,),
            'button_popupname' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'size' => 255,),
        ),
    );
    public function __construct($id_g_upsellrule = null, $id_lang = null, $id_shop = null)
    {
        Shop::addTableAssociation('g_upsellrule', array('type' => 'shop'));
        parent::__construct($id_g_upsellrule, $id_lang, $id_shop);
        return true;
    }
    public static function Countlistupsell($id_lang = 0,  $id_shop = 0)
    {
        $sql = 'SELECT COUNT(*) FROM `'._DB_PREFIX_.'g_upsellrule` a 
        LEFT JOIN `'._DB_PREFIX_.'g_upsellrule_lang` b ON (a.`id_g_upsellrule` = b.`id_g_upsellrule`)
		LEFT JOIN `'._DB_PREFIX_.'g_upsellrule_shop` c ON (a.`id_g_upsellrule` = c.`id_g_upsellrule`)
        WHERE  b.`id_lang` = '.(int)$id_lang.' AND c.`id_shop` = '.(int)$id_shop;
        $number = Db::getInstance()->getValue($sql);
        return (int)$number;
    }    
    public static function listupsell($query_search = array(), $number_start, $number_end = 0, $id_lang = 0,  $id_shop = 0)
    {
        $query_search;
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'g_upsellrule` a 
        LEFT JOIN `'._DB_PREFIX_.'g_upsellrule_lang` b ON (a.`id_g_upsellrule` = b.`id_g_upsellrule`)
		LEFT JOIN `'._DB_PREFIX_.'g_upsellrule_shop` c ON (a.`id_g_upsellrule` = c.`id_g_upsellrule`)
        WHERE  b.`id_lang` = '.(int)$id_lang.' AND c.`id_shop` = '.(int)$id_shop;
        
        if ((int)$number_end >0) {
            $sql .= ' ORDER BY a.`position` ASC limit '. (int)$number_start . ','.(int)$number_end;
        } else
            $sql .= ' ORDER BY a.`position` ASC';
        $listupsells = Db::getInstance()->executeS($sql);
        return $listupsells;
    }
    public static function getUpselldisplay($showin='', $id=0, $id_lang, $id_shop)
    {
        $results = array();
        $id;
        if ($showin !='') {
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'g_upsellrule` up 
                    LEFT JOIN `'._DB_PREFIX_.'g_upsellrule_lang` uplang ON(uplang.`id_g_upsellrule` = up.`id_g_upsellrule`)
                    LEFT JOIN `'._DB_PREFIX_.'g_upsellrule_shop` upshop ON(up.`id_g_upsellrule` = upshop.`id_g_upsellrule`)
                    WHERE  uplang.`id_lang` ='.(int)$id_lang.' AND upshop.`id_shop` ='.(int)$id_shop;
            $sql .= ' AND up.`'.pSQL($showin).'` = 1';
            $sql .=' ORDER BY up.`position` ASC';
            $results = Db::getInstance()->executeS($sql);
            if($results){
                return $results;
            }
        }
        return $results;
    }
    public static function getProductcombinidupsell($id_g_upsellrule=0, $product_display='', $id_shop)
    {
        $sql = 'SELECT DISTINCT `id_g_upsellrule` FROM `'._DB_PREFIX_.'g_upsellproductcombin` 
        WHERE `id_g_upsellrule` = '.(int)$id_g_upsellrule.' AND `product_display`= "'.pSQL($product_display).'" AND `id_shop` = '.(int)$id_shop.'';
        $id = (int)Db::getInstance()->getValue($sql);
        return $id;
    }
    public static function getProductcombinidproduct($id_g_upsellrule=0, $product_display='', $id_shop)
    {
        $idold = array();
        $sql = 'SELECT DISTINCT `id_product` FROM `'._DB_PREFIX_.'g_upsellproductcombin` 
        WHERE `id_g_upsellrule` = '.(int)$id_g_upsellrule.' AND `product_display`= "'.pSQL($product_display).'" AND `id_shop` = '.(int)$id_shop.'';
        $ids = Db::getInstance()->executeS($sql);
        if ($ids) {
            foreach ($ids as $id) {
                $idold[] = $id['id_product'];
            }
        }
        return $idold;
    }
    public static function getProductmostpopular($id_g_upsellrule=0, $product_display='', $id_shop)
    {
        $sql = 'SELECT DISTINCT `id_product` FROM `'._DB_PREFIX_.'g_upsellproductcombin` 
        WHERE `id_g_upsellrule` = '.(int)$id_g_upsellrule.' AND `product_display`= "'.pSQL($product_display).'" AND `id_shop` = '.(int)$id_shop.' AND `mostpopular` = 1';
        $id = Db::getInstance()->getValue($sql);
        return $id;
    }
    public static function getProductcombin($id_g_upsellrule=0, $id_product='', $product_display='', $id_shop)
    {
        $resultolds = array();
        $sql = 'SELECT `id_combin` FROM `'._DB_PREFIX_.'g_upsellproductcombin` 
        WHERE `id_g_upsellrule` = '.(int)$id_g_upsellrule.' AND `id_product` IN('. pSql($id_product) .') AND `product_display`= "'.pSQL($product_display).'" AND `id_shop` = '.(int)$id_shop.'';
        $results = Db::getInstance()->executeS($sql);
        if ($results) {
            foreach($results as $result) {
                $resultolds[] = $result['id_combin'];
            }
        }
        return $resultolds;
    }
    public static function getProductcombininproductid($id_g_upsellrule=0, $id_product=0, $product_display='', $id_shop)
    {
        $resultolds = array();
        $sql = 'SELECT `id_combin` FROM `'._DB_PREFIX_.'g_upsellproductcombin` 
        WHERE `id_g_upsellrule` = '.(int)$id_g_upsellrule.' AND `id_product` = '.(int)$id_product.' AND `product_display`= "'.pSQL($product_display).'" AND `id_shop` = '.(int)$id_shop.'';
        $results = Db::getInstance()->executeS($sql);
        if ($results) {
            foreach($results as $result) {
                $resultolds[] = $result['id_combin'];
            }
        }
        return $resultolds;
    }
    public static function getProductcombininproductidAndidcombin($id_g_upsellrule=0, $id_product=0, $id_combin=0, $product_display='', $id_shop)
    {
        $sql = 'SELECT `id_g_upsellproductcombin` FROM `'._DB_PREFIX_.'g_upsellproductcombin` 
        WHERE `id_g_upsellrule` = '.(int)$id_g_upsellrule.' AND `id_product` = '.(int)$id_product.' AND `id_combin` ='.(int)$id_combin.' AND `product_display`= "'.pSQL($product_display).'" AND `id_shop` = '.(int)$id_shop.'';
        $id_g_upsellproductcombin = (int)Db::getInstance()->getValue($sql);
        return $id_g_upsellproductcombin;
    }
    public static function addProductcombin($id_g_upsellrule=0, $id_product=0, $id_combin=0, $product_display='', $id_shop, $mostpopular='0')
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'g_upsellproductcombin` (`id_g_upsellrule`,`id_product`,`id_combin`,`product_display`,`id_shop`, `mostpopular`) VALUES
        ('.(int)$id_g_upsellrule.','.(int)$id_product.','.(int)$id_combin.',"'.pSQL($product_display).'",'.(int)$id_shop.',"'.(int)$mostpopular.'")';
        return Db::getInstance()->execute($sql);
    }
    public static function deleteProductcombin($id_g_upsellrule=0, $product_display='', $id_shop)
    {
        $sql = 'DELETE FROM `'._DB_PREFIX_.'g_upsellproductcombin` WHERE
                `id_g_upsellrule` = '.(int)$id_g_upsellrule.' AND `product_display` ="'.pSQL($product_display).'" AND `id_shop` = '.(int)$id_shop;
        return Db::getInstance()->execute($sql);
    }
    public static function getMaxidUpsell($id_shop=0)
    {
        $sql = 'SELECT MAX(up.`id_g_upsellrule`) FROM `'._DB_PREFIX_.'g_upsellrule` up
        LEFT JOIN `'._DB_PREFIX_.'g_upsellrule_shop` upshop ON(up.`id_g_upsellrule` = upshop.`id_g_upsellrule`) 
        WHERE  upshop.`id_shop` = '.(int)$id_shop;
        return Db::getInstance()->getValue($sql);
    }
}