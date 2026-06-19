<?php
/**
 * 2007-2022 Olivier CLEMENCE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to Olivier CLEMENCEso we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Olivier CLEMENCE to newer
 * versions in the future. If you wish to customize Olivier CLEMENCE for your
 * needs please refer to Olivier CLEMENCE for more information.
 *
 * @author    Olivier CLEMENCE
 * @copyright 2007-2022 Olivier CLEMENCE
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Olivier CLEMENCE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OpartIndexNow extends Module
{
    private $html = '';
 

    public function __construct()
    {
        $this->name          = 'opartindexnow';
        $this->tab           = 'seo';
        $this->version       = '1.2.5';
        $this->author        = 'Réussir mon ecommerce';
		$this->module_key    = "90a2dba23fa0f50dea6716290a039120";

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IndexNow for prestashop');
        $this->description = $this->l('Index your pages faster with technology Indexnow');

        $this->confirmUninstall = $this->l('Are you sure you wan\'t uninstal this module ?');
        $this->selected_element = 1;

    }



    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        Configuration::updateValue('INDEXNOW_LIMITSEND',30);
        include(dirname(__FILE__) . '/sql/install.php');
        return parent::install()
        && $this->registerHook('actionObjectProductDeleteBefore')
        && $this->registerHook('actionProductDelete')
        && $this->registerHook('actionProductSave')
        && $this->registerHook('actionObjectCategoryDeleteBefore')
        && $this->registerHook('actionCategoryDelete')
        && $this->registerHook('actionCategoryUpdate')
        && $this->registerHook('actionCategoryAdd')
        && $this->registerHook('actionObjectSupplierUpdateAfter')
        && $this->registerHook('actionObjectSupplierDeleteAfter')
        && $this->registerHook('actionObjectSupplierDeleteBefore')
        && $this->registerHook('actionObjectSupplierAddAfter')
        && $this->registerHook('actionObjectCmsDeleteAfter')
        && $this->registerHook('actionObjectCmsDeleteBefore')
        && $this->registerHook('actionObjectCmsUpdateAfter')
        && $this->registerHook('actionObjectCmsAddAfter')
         && $this->registerHook('actionObjectManufacturerDeleteBefore')
        && $this->registerHook('actionObjectManufacturerDeleteAfter')
        && $this->registerHook('actionObjectManufacturerAddAfter')
        && $this->registerHook('actionObjectManufacturerUpdateAfter')
        && $this->registerHook('displayAdminAfterHeader');
    }


      public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');
         if(Configuration::get('OPARTINDEXNOW_KEY')){

            unlink(_PS_CORE_DIR_ . "/".Configuration::get('OPARTINDEXNOW_KEY').'.txt');
           Configuration::deleteByName('OPARTINDEXNOW_KEY');
           Configuration::deleteByName('INDEXNOW_LIMITLOGS');
            Configuration::deleteByName('INDEXNOW_NOINDEX_ARGS');
            Configuration::deleteByName('INDEXNOW_VISIBILITY_ARGS');
            Configuration::deleteByName('INDEXNOW_LIMITSEND');

          
        }

        return parent::uninstall();
    }


    private function countCategories(){
        $sql = 'SELECT COUNT(id_category) FROM `' . _DB_PREFIX_ . 'opartindexnow_categories`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }



      private function getCategories(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $id_category = false,
        $excluded = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                c.id_category,
                c.active, cl.name,
                cl.link_rewrite,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'category` c
                ' . Shop::addSqlAssociation('category', 'c') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON (
                    c.`id_category` = cl.`id_category` '
                    . Shop::addSqlRestrictionOnLang('cl') . '
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_categories` oin
                ON (
                    oin.`id_category` = c.`id_category` '
                    . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = cl.id_lang
                )
            WHERE cl.`id_lang` = ' . (int) $id_lang
                . ($id_category ? ' AND c.`id_parent` = ' . (int) $id_category : '')
                . ($excluded ? ' AND oin.`exclude` = 1' : '')
                . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
                . ($active ? ' AND c.`active` = 1' : '')
                . ($notactive ? ' AND c.`active` = 0' : '')
                . ($status ? ' AND oin.`status` > 0' : '') .'

            ORDER BY `' . pSQL($order_by) . '` ' . pSQL($order_way)
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');

        $result = array();
        $result['rows']      = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }

    private function getProducts(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $id_category = false,
        $excluded = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                p.id_product,
                product_shop.active,
                pl.name,
                pl.link_rewrite,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'product` p
            ' . Shop::addSqlAssociation('product', 'p') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON (
                    p.`id_product` = pl.`id_product` '
                    . Shop::addSqlRestrictionOnLang('pl') . '
                )'
            . ($id_category ? ' LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c
                ON (
                    c.`id_product` = p.`id_product`
                )' : '') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_products` oin
                ON (
                    oin.`id_product` = p.`id_product`
                    ' . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = pl.id_lang
                )
            WHERE pl.`id_lang` = ' . (int) $id_lang
                . ($id_category ? ' AND c.`id_category` = ' . (int) $id_category : '')
                . ($excluded ? ' AND oin.`exclude` = 1' : '')
                . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
                . ($active ? ' AND p.`active` = 1' : '')
                . ($notactive ? ' AND p.`active` = 0' : '')
                . ($status ? ' AND oin.`status` > 0' : '').'
            ORDER BY `' . pSQL($order_by) . '` ' . pSQL($order_way)
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');

        $result = array();
        $result['rows']      = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT FOUND_ROWS() AS totalItem');



        return $result;
    }


       private function countProducts(){
        $sql = 'SELECT COUNT(id_product) FROM `' . _DB_PREFIX_ . 'opartindexnow_products`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    private function getSuppliers(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $exclude = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                s.id_supplier,
                s.active,
                s.name,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'supplier` s
            ' . Shop::addSqlAssociation('supplier', 's') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'supplier_lang` sl
                ON (
                    s.`id_supplier` = sl.`id_supplier`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_suppliers` oin
                ON (
                    oin.`id_supplier` = s.`id_supplier`
                    ' . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = sl.id_lang
                )
            WHERE sl.`id_lang` = ' . (int) $id_lang
                . ($exclude ? ' AND oin.`exclude` = 1' : '')
                . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
                . ($active ? ' AND s.`active` = 1' : '')
                . ($notactive ? ' AND s.`active` = 0' : '')
                . ($status ? ' AND oin.`status` > 0' : '') . '
            ORDER BY `' . pSQL($order_by) . '` ' . pSQL($order_way)
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');

        $result = array();
        $result['rows']      = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }

       private function countSuppliers(){
        $sql = 'SELECT COUNT(id_supplier) FROM `' . _DB_PREFIX_ . 'opartindexnow_suppliers`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }


    private function getCms(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $exclude = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false
    ) {
        if (version_compare(_PS_VERSION_, '1.6.0.11') <= 0) {
            $sql =
                'SELECT
                    SQL_CALC_FOUND_ROWS
                    c.id_cms,
                    c.active,
                    c.indexation,
                    cl.meta_title as name,
                    cl.link_rewrite,
                    oin.exclude,
                    oin.status,
                    oin.date_upd,
                    oin.type
                FROM `' . _DB_PREFIX_ . 'cms` c
                ' . Shop::addSqlAssociation('cms', 'c') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'cms_lang` cl
                    ON (
                        c.`id_cms` = cl.`id_cms`
                    )
                LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_cms` oin
                    ON (
                        oin.`id_cms` = c.`id_cms`
                        ' . Shop::addSqlRestriction(false, 'oin') . '
                        AND oin.id_lang = ' . (int) $id_lang . '
                    )
                WHERE cl.`id_lang` = ' . (int) $id_lang
                    . ($exclude ? ' AND oin.`exclude` = 1' : '')
                    . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
                    . ($active ? ' AND c.`active` = 1' : '')
                    . ($notactive ? ' AND c.`active` = 0' : '')
                    . ($status ? ' AND oin.`status` > 0' : '') .'
                GROUP BY c.id_cms
                ORDER BY `' . pSQL($order_by) . '` ' . pSQL($order_way)
                . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');
        } else {
            $sql =
                'SELECT
                    SQL_CALC_FOUND_ROWS
                    c.id_cms,
                    c.active,
                    c.indexation,
                    cl.meta_title as name,
                    cl.link_rewrite,
                    oin.exclude,
                    oin.status,
                    oin.date_upd,
                    oin.type
                FROM `' . _DB_PREFIX_ . 'cms` c
                ' . Shop::addSqlAssociation('cms', 'c') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'cms_lang` cl
                    ON (
                        c.`id_cms` = cl.`id_cms`
                        ' . Shop::addSqlRestrictionOnLang('cl') . '
                    )
                LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_cms` oin
                    ON (
                        oin.`id_cms` = c.`id_cms`
                        ' . Shop::addSqlRestriction(false, 'oin') . '
                        AND oin.id_lang = ' . (int) $id_lang . '
                    )
                WHERE cl.`id_lang` = ' . (int) $id_lang
                    . ($exclude ? ' AND oin.`exclude` = 1' : '')
                     . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
                     . ($active ? ' AND c.`active` = 1' : '')
                    . ($notactive ? ' AND c.`active` = 0' : '')                     
                    . ($status ? ' AND oin.`status` > 0' : '').'
                GROUP BY c.id_cms
                ORDER BY `' . pSQL($order_by) . '` ' . pSQL($order_way)
                . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');
        }

        $result = array();
        $result['rows']      = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }

    private function countCms(){
        $sql = 'SELECT COUNT(id_cms) FROM `' . _DB_PREFIX_ . 'opartindexnow_cms`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }




    private function getManufacturers(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $exclude = null,
        $noexcluded = null,
        $active = null,
        $notactive = null,
        $status = false
    ) {
        $sql =
            'SELECT
                SQL_CALC_FOUND_ROWS
                m.id_manufacturer,
                m.active,
                m.name,
                oin.exclude,
                oin.status,
                oin.date_upd,
                oin.type
            FROM `' . _DB_PREFIX_ . 'manufacturer` m
            ' . Shop::addSqlAssociation('manufacturer', 'm') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer_lang` ml
                ON (
                    m.`id_manufacturer` = ml.`id_manufacturer`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'opartindexnow_manufacturers` oin
                ON (
                    oin.`id_manufacturer` = m.`id_manufacturer`
                    ' . Shop::addSqlRestriction(false, 'oin') . '
                    AND oin.id_lang = ' . (int) $id_lang . '
                )
            WHERE ml.id_lang = ' . (int) $id_lang
                . ($exclude ? ' AND oin.`exclude` = 1' : '')
                 . ($noexcluded ? ' AND oin.`exclude` = 0' : '')
                  . ($active ? ' AND m.`active` = 1' : '')
                 . ($notactive ? ' AND m.`active` = 0' : '')
                  . ($status ? ' AND oin.`status` > 0' : '') .'
            ORDER BY `' . pSQL($order_by) . '` ' . pSQL($order_way)
            . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');

        $result = array();
        $result['rows']      = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT FOUND_ROWS() AS totalItem');

        return $result;
    }


    private function countManufacturers(){
        $sql = 'SELECT COUNT(id_manufacturer) FROM `' . _DB_PREFIX_ . 'opartindexnow_manufacturers`';

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }



    public function getUrlSubmitted(){

        if(Configuration::get('INDEXNOW_LIMITLOGS') > 0){

            $count = Db::getInstance()->getValue('SELECT COUNT(id_log) FROM `'._DB_PREFIX_.'opartindexnow_logs`');

            if($count > Configuration::get('INDEXNOW_LIMITLOGS')){
                $nombre = $count - Configuration::get('INDEXNOW_LIMITLOGS');
                Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'opartindexnow_logs` ORDER BY id_log ASC LIMIT '.$nombre);
            }
        }

       // Modif multiboutique
        if (Shop::isFeatureActive() &&  Shop::getContext() !=4){
            $base_url = $this->context->link->getBaseLink($this->context->shop->id);
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` WHERE URL LIKE "%'.$base_url.'%" ORDER BY date_upd DESC';
        }
        else{
           $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` ORDER BY date_upd DESC';
 
        }


        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);



    }


    private function getLog($id){


        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` WHERE id_log = '.$id;

        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);


    }


    private function getLastlog(){


        $sql = 'SELECT status FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` ORDER BY id_log DESC';


        return  Db::getInstance()->getValue($sql);


    }


    private function checkUrlgetLogs($url){

        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` WHERE url = "'.$url.'" and date_upd like "'.date("Y-m-d").'%"' ;
         return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public function countSend($date){

        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` WHERE  date_upd like "'.$date.'%"' ;
         return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

     public function lastSend(){

        $sql = 'SELECT date_upd FROM `' . _DB_PREFIX_ . 'opartindexnow_logs` ORDER BY id_log DESC' ;
         return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }





    //function to send on API indexNow
    public function SendIndexNow($listitem, $items,$types){

        $key =  Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id);
        $reponse = [];



        if($key){
        foreach ($types as $type) {
            if($type == 'category'){
                foreach ($listitem as $item) {
                    if(empty($items[$item])){
                        $linkcms = $this->context->link->getCategoryLink($item);
                        $ch = curl_init('https://api.indexnow.org/indexnow?url='.$linkcms.'&key='.$key);
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                        curl_exec($ch);
                        $status = curl_getinfo($ch);
                        $reponse[$item] = $status['http_code'];
                    }               
                    
                }
            }

            if($type == 'product'){
                foreach ($listitem as $item) {
                   if(empty($items[$item])){
                        $linkcms = $this->context->link->getProductLink($item);
                        $ch = curl_init('https://api.indexnow.org/indexnow?url='.$linkcms.'&key='.$key);
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                        curl_exec($ch);
                        $status = curl_getinfo($ch);
                        $reponse[$item] = $status['http_code'];
                    }               
                    
                }
            }


             if($type == 'supplier'){
                foreach ($listitem as $item) {
                   if(empty($items[$item])){
                        $linkcms = $this->context->link->getSupplierLink($item);
                        $ch = curl_init('https://api.indexnow.org/indexnow?url='.$linkcms.'&key='.$key);
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                        curl_exec($ch);
                        $status = curl_getinfo($ch);
                        $reponse[$item] = $status['http_code'];
                    }               
                    
                }
            }


             if($type == 'cms'){
                foreach ($listitem as $item) {
                    if(empty($items[$item])){
                        $linkcms = $this->context->link->getCMSLink($item);
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                        $ch = curl_init('https://api.indexnow.org/indexnow?url='.$linkcms.'&key='.$key);
                        curl_exec($ch);
                        $status = curl_getinfo($ch);
                        $reponse[$item] = $status['http_code'];    
                    }
                                               
                }
            }


            if($type == 'manufacturer'){
                foreach ($listitem as $item) {
                    if(empty($items[$item])){
                        $linkcms = $this->context->link->getManufacturerLink($item);
                        $ch = curl_init('https://api.indexnow.org/indexnow?url='.$linkcms.'&key='.$key);
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                        curl_exec($ch);
                        $status = curl_getinfo($ch);
                        $reponse[$item] = $status['http_code'];
                    }             
                    
                }
            }
        }

         return $reponse;
            
        }
       

    }

    public function ResendItem($item){
         $key =  Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id);
         if($key){
            $ch = curl_init('https://api.indexnow.org/indexnow?url='.$item['url'].'&key='.$key);
            curl_exec($ch);
            $status = curl_getinfo($ch);
            $reponse = $status['http_code'];

            $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                            (name, status, url,date_upd)
                        VALUES (
                            "' . $item['name'] . '",
                            ' .     $reponse . ',
                            "' .  $item['url']. '",
                            "' .date("Y-m-d H:i:s"). '")';

                $result = Db::getInstance()->Execute($sql);

        }
    }


    public function postProcess(){

        $id_lang = $this->context->language->id;

        //enregistrer les configurations
        if(Tools::isSubmit('indexnowsendconfig')){
            if(Tools::getValue('limitsend') > 0){
               Configuration::updateValue('INDEXNOW_LIMITSEND',Tools::getValue('limitsend')); 
            }
            else{

                $erreur = $this->l('The daily send limit must be greater than 0');

                $this->context->smarty->assign('erreur',$erreur);

            }
            if(Tools::getValue('limitlogs')){
                Configuration::updateValue('INDEXNOW_LIMITLOGS',Tools::getValue('limitlogs'));
            }
            Configuration::updateValue('INDEXNOW_NOINDEX_ARGS',Tools::getValue('INDEXNOW_NOINDEX_ARGS'));
             Configuration::updateValue('INDEXNOW_VISIBILITY_ARGS',Tools::getValue('INDEXNOW_VISIBILITY_ARGS'));
            //si oui, alors on met les urls en noindex en exclusion
            if(Tools::getValue('INDEXNOW_NOINDEX_ARGS') == 1){
                $this->getItemsnoIndex();
            }

             if(Tools::getValue('INDEXNOW_VISIBILITY_ARGS') == 1){
                $this->getItemsVisibilty();
            }

            $confirmation = $this->l('The data has been saved');

            $this->context->smarty->assign('confirmation',$confirmation);
        }



        //vider la list
        if (Tools::getValue('deletelist')){

            db::getInstance()->execute('TRUNCATE TABLE '._DB_PREFIX_.'opartindexnow_logs');


        }


        //formulaire exclusion
        if (Tools::getValue('listItem')){

            $excludeValue  = Tools::getValue('NoIndexNow');
            $listItem = Tools::getValue('listItem');
            $type = [];


            $sql           = '';
             if (Tools::getIsset('oesfp_element_type')) {
                $this->selected_element = Tools::getValue('oesfp_element_type');
            }


             if (empty($listItem) || count($listItem) == 0) {
                return false;
            }



            if ($this->selected_element == 1) {
                $tableName = 'categories';
                $fieldName = 'id_category';
                $type[]  = 'category';
            }

            if ($this->selected_element == 2) {
                $tableName = 'products';
                $fieldName = 'id_product';
                $type[]  = 'product';
            }

              if ($this->selected_element == 3) {
                $tableName = 'suppliers';
                $fieldName = 'id_supplier';
                $type[]  = 'supplier';
            }

            if ($this->selected_element == 4) {
                $tableName = 'cms';
                $fieldName = 'id_cms';
                $type[]  = 'cms';
            }


            if ($this->selected_element == 5) {
                $tableName = 'manufacturers';
                $fieldName = 'id_manufacturer';
                $type[] = 'manufacturer';
            }


            foreach ($listItem as $id_item) {
                 $isExclude  = (isset($excludeValue[$id_item])) ? 1 : 0;


                    $sql =
                        'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_' . $tableName . '
                            (' . $fieldName . ', id_lang, id_shop, exclude,status,date_upd)
                        VALUES (
                            ' . (int) $id_item . ',
                            ' . (int) $id_lang . ',
                            ' . (int) $this->context->shop->id . ',
                            ' . (int) $isExclude . ',
                            0,
                            "' . date("Y-m-d H:i:s") . '"
                        ) ON DUPLICATE KEY UPDATE
                            exclude = ' . (int) $isExclude.'';

                         if (!db::getInstance()->execute($sql)) {
                                $this->post_error[] = sprintf(
                                    $this->l('An error occured while updating the %s configuration for %s = %s'),
                                    $tableName,
                                    $fieldName,
                                    $id_item
                                );
                            }

                }

            }

        //formulaire de renvoie
        if(Tools::getValue('SendIndexNow')){
            $ids  = Tools::getValue('SendIndexNow');
            foreach ($ids as  $id) {
                $log = $this->getLog($id);
                $this->ResendItem($log);

            }

            $module_obj = new OpartIndexnow();
            $redirection = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$module_obj->name.'&tab_module='.$module_obj->tab.'&module_name='.$module_obj->name.'&token='
            .Tools::getAdminTokenLite('AdminModules').'&action=soumission';
            Tools::redirect($redirection);
            
            
        }
       

    }

    public function getItemsnoIndex(){

        $elements = [];
        $i = 0;

        $elements['products'] = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'opartnoindex_products');
        $elements['categories'] = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'opartnoindex_categories');
        $elements['suppliers'] = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'opartnoindex_suppliers');
        $elements['cms'] = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'cms');
        foreach ($elements['cms'] as $cms) {
            $elements['cms'][$i]['id_lang'] = $this->context->language->id;
            $elements['cms'][$i]['id_shop'] = $this->context->shop->id;
                if($cms['indexation'] == 0){
                    $elements['cms'][$i]['no_index'] = 1;
                }
                else{
                    $elements['cms'][$i]['no_index'] = 0; 
                }
                $i++;
        }

        $elements['manufacturers'] = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'opartnoindex_manufacturers');



        foreach ($elements as $key => $element) {
            foreach ($element as $value) {
               switch ($key) {
                   case 'products':
                       $fieldName = 'id_product';
                       break;
                    case 'categories':
                       $fieldName = 'id_category';
                       break;
                    case 'suppliers':
                       $fieldName = 'id_supplier';
                       break;
                    case 'cms':
                       $fieldName = 'id_cms';
                       break;
                    case 'manufacturers':
                       $fieldName = 'id_manufacturer';
                       break;
                   
               }


                   $sql =
                        'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_' . $key . '
                            (' . $fieldName . ', id_lang, id_shop, exclude,status,date_upd)
                        VALUES (
                            ' . (int) $value[$fieldName] . ',
                            ' . (int) $value['id_lang'] . ',
                            ' . (int) $value['id_shop'] . ',
                            ' . (int) $value['no_index'] . ',
                            0,
                            "' . date("Y-m-d H:i:s") . '"
                        ) ON DUPLICATE KEY UPDATE
                            exclude = ' . (int) $value['id_lang'].'';


                         if (!db::getInstance()->execute($sql)) {
                                $this->post_error[] = sprintf(
                                    $this->l('An error occured while updating the %s configuration for %s = %s'),
                                    $key,
                                    $fieldName,
                                    $value[$fieldName]
                                );
                            }

                }
            }
          

    }

    public function getItemsVisibilty(){
        $products = db::getInstance()->executeS('SELECT id_product,id_shop FROM '._DB_PREFIX_.'product_shop  WHERE visibility = "none"');

        foreach ($products as $product) {
                $sql =
                        'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_products
                            (id_product, id_lang, id_shop, exclude,status,date_upd)
                        VALUES (
                            ' . (int) $product['id_product'] . ',
                            ' . (int) $this->context->language->id . ',
                            ' . (int) $product['id_shop'] . ',
                            1,
                            0,
                            "' . date("Y-m-d H:i:s") . '"
                        ) ON DUPLICATE KEY UPDATE
                            exclude = 1';



                         if (!db::getInstance()->execute($sql)) {
                                $this->post_error[] = sprintf(
                                    $this->l('An error occured while updating the %s configuration for %s = %s'),
                                    'opartindexnow_products',
                                    'id_product',
                                    $product['id_product']
                                );
                            }
        }
    }


     public function getContent()
    {

        $this->context->controller->addJS($this->_path.'views/js/back.js');
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
         $smarty  = $this->context->smarty;


         if (Tools::isSubmit('submit_exclusion') || Tools::isSubmit('submit_url') || Tools::getValue('deletelist') == 1 || Tools::isSubmit('indexnowsendconfig')) {

            $this->postProcess();
        }


         //genère la clé
        //page exclusion 
        if(Tools::getValue('action') == "key"){



        if(Tools::getValue('generatekey')){

            $string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-';
               $var_size = Tools::strlen($string);
               $key = "";
                for( $x = 0; $x < 128; $x++ ) {  
                    $key .= $string[ rand( 0, $var_size - 1 ) ];  
                }

                Configuration::UpdateValue('OPARTINDEXNOW_KEY',$key);

                $content = "";
                $fp = fopen(_PS_CORE_DIR_ . "/".$key.".txt","wb"); 
                fwrite($fp,$content);
                fclose($fp);
              
            }

            //supp la clé
            if(Tools::getValue('deletekey')){

                unlink(_PS_CORE_DIR_ . "/".Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id).'.txt');
               Configuration::deleteByName('OPARTINDEXNOW_KEY');

              
            }

             $smarty->assign('tabkey', 'tabkey');
        }


        $key = Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id);
        $admin_module_url =
            'index.php?controller=AdminModules&configure='
            . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');


        //page exclusion 
        if(Tools::getValue('action') == "exclusion" || Tools::getValue('oesfp_element_type')){
            //Tools::dieObject('test2');
            // Get item number form filter
            if (Tools::getIsset('item_number') && !empty(Tools::getValue('item_number'))) {
                $item_number = Tools::getValue('item_number');
            } else {
                $item_number = 25;
            }



              if (Tools::getIsset('oesfp_element_type')) {
                $this->selected_element = Tools::getValue('oesfp_element_type');
            }

            //filter
            $start                = 0;
            $limit                = $item_number;
            $selected_page        = 1;
            $id_selected_category = false;
            $excluded         = false;
            $noexcluded        = false;
            $active         = false;
            $notactive        = false;

            //check current page
            if (Tools::getIsset('selected_page') && Tools::getValue('selected_page') != 0) {
                $selected_page = Tools::getValue('selected_page');

            }

            //Tools::dieObject(Tools::getValue('selected_category'));

                if (Tools::getIsset('selected_category')) {
                    $id_selected_category =
                        (Tools::getValue('selected_category') == '-1') ? false : Tools::getValue('selected_category');
                }

                if (Tools::getIsset('selected_status')) {
                    if(Tools::getValue('selected_status') == 1){
                        $excluded = 1;
                    }
                    elseif(Tools::getValue('selected_status') == 0){
                        $noexcluded = 1;
                    }
                    
                }

                if (Tools::getIsset('selected_active')) {
                    if(Tools::getValue('selected_active') == 1){
                        $active = 1;
                    }
                    elseif(Tools::getValue('selected_active') == 0){
                        $notactive = 1;
                    }
                    
                }



  


            $start = ($selected_page - 1) * $limit;
            $tree                    = $this->getCategoryTree($this->context->language->id);
            $select_category_options = $this->nested2select($tree, $id_selected_category, '');



              //load category
            if ($this->selected_element == 1) {
                $nb = $this->countCategories();
                if($nb == 0 &&  $noexcluded == 1){
                     $res = $this->getCategories(
                    $this->context->language->id,
                    $start,
                    $limit,
                    'id_category',
                    'ASC',
                    $id_selected_category
                    );
                }
                else{

                     $res = $this->getCategories(
                    $this->context->language->id,
                    $start,
                    $limit,
                    'id_category',
                    'ASC',
                    $id_selected_category,
                    $excluded,
                    $noexcluded,
                     $active,
                    $notactive
                );


                }
                
                $categories = $res['rows'];
                $linkClass  = new Link;

                foreach ($categories as &$category) {
                    $category['link'] = $linkClass->getCategoryLink($category['id_category']);
                }

                $smarty->assign('categories', $categories);
            }

             //load product list
            if ($this->selected_element == 2) {

                $nb = $this->countProducts();
                if($nb == 0 &&  $noexcluded == 1){
                     $res = $this->getProducts(
                    $this->context->language->id,
                    $start,
                    $limit,
                    'id_product',
                    'ASC',
                    $id_selected_category
                    );

                }
                else{
                    $res = $this->getProducts(
                        $this->context->language->id,
                        $start,
                        $limit,
                        'id_product',
                        'ASC',
                        $id_selected_category,
                        $excluded,
                        $noexcluded,
                        $active,
                        $notactive
                    );
                }



                $products  = $res['rows'];

                
                $linkClass = new Link;

                foreach ($products as &$product) {
                    $product['id_cover']   = Product::getCover($product['id_product']);
                    $product['cover_link'] = $linkClass->getImageLink(
                        $product['link_rewrite'],
                        $product['id_cover']['id_image']
                    );
                    $product['link']       = $linkClass->getProductLink($product['id_product']);
                }

                $smarty->assign('products', $products);
            }


             //load supplier list
            if ($this->selected_element == 3) {
                $nb = $this->countSuppliers();
                if($nb == 0 &&  $noexcluded == 1){
                     $res = $this->getSuppliers(
                    $this->context->language->id,
                    $start,
                    $limit,
                    'id_supplier',
                    'ASC'
                    );
                }
                else{
                     $res = $this->getSuppliers(
                    $this->context->language->id,
                    $start,
                    $limit,
                    'id_supplier',
                    'ASC',
                    $excluded,
                    $noexcluded,
                    $active,
                    $notactive
                    );
                }
               


                $suppliers = $res['rows'];
                $linkClass = new Link;

                foreach ($suppliers as &$supplier) {
                    $supplier['link'] = $linkClass->getSupplierLink($supplier['id_supplier']);
                }

                $smarty->assign('suppliers', $suppliers);
            }


             //load cms list
            if ($this->selected_element == 4) {
                $nb = $this->countCms();
                if($nb == 0  &&  $noexcluded == 1){
                    $res = $this->getCms(
                    $this->context->language->id,
                    $start,
                    $limit,
                    'id_cms',
                    'ASC'
                    );
                }
                else{
                     $res = $this->getCms(
                    $this->context->language->id,
                    $start,
                    $limit,
                    'id_cms',
                    'ASC',
                    $excluded,
                    $noexcluded,
                    $active,
                    $notactive
                );


                }
               

                $cmss      = $res['rows'];
                $linkClass = new Link;

                foreach ($cmss as &$cms) {
                    $cms['no_index'] = ($cms['indexation'] == 0) ? 1 : 0;
                    $cms['link']     = $linkClass->getCMSLink($cms['id_cms'], $cms['link_rewrite']);
                }

                $smarty->assign('cmss', $cmss);
            }


            //load manufacturer list
            if ($this->selected_element == 5) {
                $nb = $this->countManufacturers();
                if($nb == 0  &&  $noexcluded == 1){

                      $res = $this->getManufacturers(
                    $this->context->language->id,
                    $start,
                    $limit,
                    'id_manufacturer',
                    'ASC'
                    );
                }
                else{
                     $res = $this->getManufacturers(
                   $this->context->language->id,
                    $start,
                    $limit,
                    'id_manufacturer',
                    'ASC',
                    $excluded,
                    $noexcluded,
                    $active,
                    $notactive
                    );

                }

                $manufacturers = $res['rows'];
                $linkClass     = new Link;

                foreach ($manufacturers as &$manufacturer) {
                    $manufacturer['link'] = $linkClass->getManufacturerLink($manufacturer['id_manufacturer']);
                }

                $smarty->assign('manufacturers', $manufacturers);
            }


            $smarty->assign(array(
            'item_number' => $item_number,
            'limit' => $limit,
            'selected_page' => $selected_page,
            'select_category_options' => $select_category_options,
            'noexclude_only' => $noexcluded,
            'exclude_only' => $excluded,
            'active' => $active,
            'notactive' => $notactive,
            'exclusion' => 'exclusion',
            'totalItem' => $res['totalItem'][0]['totalItem'],
            'id_selected_category' => $id_selected_category
            

            ));
        }


        //load Urls submitted
        if(Tools::getValue('action') == "soumission"){

            $urlssubmitted = $this->getUrlSubmitted();
            $smarty->assign('urlssubmitted', $urlssubmitted);

        }

          if(Tools::getValue('action') == "help"){

            $smarty->assign('help', true);

        }

        if(empty(Tools::getValue('action')) && empty(Tools::getValue('oesfp_element_type'))){

            $smarty->assign('tabkey', 'tabkey');

        }

         //recupère les valeurs de configuration
        if(Configuration::get('INDEXNOW_LIMITLOGS')){
            $smarty->assign('limitlogs',Configuration::get('INDEXNOW_LIMITLOGS'));
        }


        if(Configuration::get('INDEXNOW_VISIBILITY_ARGS')){
            $smarty->assign('INDEXNOW_VISIBILITY_ARGS',Configuration::get('INDEXNOW_VISIBILITY_ARGS'));
        }

        if(Module::getInstanceByName('opartnoindex') && Module::getInstanceByName('opartnoindex')->active == 1){
           $smarty->assign(array(
            'noindex'=>1,
            'INDEXNOW_NOINDEX_ARGS'=> Configuration::get('INDEXNOW_NOINDEX_ARGS')
            ));


        }

         $moduleUrl = $this->context->link->getModuleLink('opartindexnow', 'cron');

      
        $smarty->assign(array(
            'module_local_path' => $this->local_path,
            'admin_module_url' => $admin_module_url,
            'key' => $key,
            'selected_element'=>$this->selected_element,
            'limitsend' => Configuration::get('INDEXNOW_LIMITSEND'),
            'urlcron' => $moduleUrl . '?token=' . Tools::substr(Tools::hash('opartindexnow/cron'), 0, 10) . '&id_shop=' . $this->context->shop->id,
        ));

        $this->html = '';
        $this->html .= $this->display(__FILE__, 'views/templates/admin/configure.tpl');

 

        return $this->html;
    }


     private function getItemExclude($id, $table, $key){

            $sql = 'SELECT exclude FROM '._DB_PREFIX_.'opartindexnow_'.$table.' WHERE id_shop = '.$this->context->shop->id.' AND id_lang = '.$this->context->language->id.' AND '.$key.' = '.$id;

            $exclude = db::getInstance()->getvalue($sql);

            return $exclude;

    }

    public function getListsWainting($senday){

        $limit = Configuration::get('INDEXNOW_LIMITSEND') - $senday;

        $result = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'opartindexnow_waiting LIMIT 0,'.$limit);

        return $result;
    }


        public function hookActionProductSave($params){

            if (!isset($params['product'])) {
                return;
            }

            $id_product = (int) $params['product']->id;
            $exclude = $this->getItemExclude($id_product, 'products', 'id_product');

            $listitem = array($id_product);
            $type = array('product');

            $url = $this->context->link->getProductLink($id_product);
            $nb = $this->checkUrlgetLogs($url);
            

            if($exclude != 1 && $params['product']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                $sendday = $this->countSend(date("Y-m-d"));

                 $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                    $reponse  = (isset($reponse[$id_product])) ? $reponse[$id_product] : 403;




                    $sql =
                            'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_products
                                (id_product, id_lang, id_shop,status,date_upd)
                            VALUES (
                                ' . (int) $id_product . ',
                                ' . (int) $this->context->language->id . ',
                                ' . (int) $this->context->shop->id . ',
                                ' .  $reponse . ',
                                "' . date("Y-m-d H:i:s") . '"
                            ) ON DUPLICATE KEY UPDATE
                                status = ' . (int) $reponse.',
                                date_upd = "' .date("Y-m-d H:i:s").'"';
                    
                     $result = Db::getInstance()->Execute($sql);

                     $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['product']->name[$this->context->language->id] . '",
                                ' .     $reponse . ',
                                "' .  $this->context->link->getProductLink($id_product). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);
                }
                else{

                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . $params['product']->name[$this->context->language->id] . '",
                                "product",
                                '.$id_product.',
                                "' .  $this->context->link->getProductLink($id_product). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);

                }


            }

        }

        public function hookactionObjectProductDeleteBefore($params){


             if (!isset($params['object'])) {
                return;
            }

            $id_product= (int) $params['object']->id;
            $exclude = $this->getItemExclude($id_product, 'products', 'id_product');

            $url = $this->context->link->getProductLink($id_product);
            $nb = $this->checkUrlgetLogs($url);

            

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id)  && $nb < 1){
                Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE',$this->context->link->getProductLink($params['object']->id));

            }
        }



        public function hookActionProductDelete($params)
        {
            if (!isset($params['product'])) {
                return;
            }

            $id_product = (int) $params['product']->id;
            $exclude = $this->getItemExclude($id_product, 'products', 'id_product');

            $url = $this->context->link->getProductLink($id_product);
            $nb = $this->checkUrlgetLogs($url);

            $listitem = array($id_product);
            $type = array('product');

            if($exclude != 1 && $params['product']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                $sendday = $this->countSend(date("Y-m-d"));
                 $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                    $reponse  = (isset($reponse[$id_product])) ? $reponse[$id_product] : 403;

                    
                     Db::getInstance()->delete('opartindexnow_products',
                    '`id_product` = ' . (int) $id_product . ' AND `id_shop`=' . (int) $this->context->shop->id
                    );



                       $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['product']->name[$this->context->language->id] . '",
                                ' .     $reponse . ',
                                "' . Configuration::get('OPARTINDEXNOW_URL_DELETE'). '",
                                "' .date("Y-m-d H:i:s"). '")';


                    $result = Db::getInstance()->Execute($sql);
                }
                else{

                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                            "' . $params['product']->name[$this->context->language->id] . '",
                                "product",
                                '.$id_product.',
                                "' .  $this->context->link->getProductLink($id_product). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);

                }



            }

           
        }


        public function hookActionCategoryUpdate($params){


            if (!isset($params['category'])) {
                return;
            }

            $id_category = (int) $params['category']->id;
            $exclude = $this->getItemExclude($id_category, 'categories', 'id_category');

            $listitem = array($id_category);
            $type = array('category');


            $url = $this->context->link->getCategoryLink($id_category);
            $nb = $this->checkUrlgetLogs($url);
            


            if($exclude != 1 && $params['category']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1){
                $sendday = $this->countSend(date("Y-m-d"));
                  $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                     $reponse  = (isset($reponse[$id_category])) ? $reponse[$id_category] : 403;



                    $sql =
                            'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_categories
                                (id_category, id_lang, id_shop,status,date_upd)
                            VALUES (
                                ' . (int) $id_category . ',
                                ' . (int) $this->context->language->id . ',
                                ' . (int) $this->context->shop->id . ',
                                ' .  $reponse . ',
                                "' . date("Y-m-d H:i:s") . '"
                            ) ON DUPLICATE KEY UPDATE
                                status = ' . (int) $reponse.',
                                date_upd = "' .date("Y-m-d H:i:s").'"';
                    
                     $result = Db::getInstance()->Execute($sql);


                      $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['category']->name[$this->context->language->id] . '",
                                ' .     $reponse . ',
                                "' .  $this->context->link->getCategoryLink($id_category). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);

                 }
                 else{
                     $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . $params['category']->name[$this->context->language->id] . '",
                                "category",
                                '.$id_category.',
                                "' .  $this->context->link->getCategoryLink($id_category). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);
 
                 }




            }

        }

        public function hookActionCategoryAdd($params){
            return $this->hookActionCategoryUpdate($params);
        }

        public function hookactionObjectCategoryDeleteBefore($params){

            
             if (!isset($params['object'])) {
                return;
            }

            $id_category= (int) $params['object']->id;
            $exclude = $this->getItemExclude($id_category, 'categories', 'id_category');

            $url = $this->context->link->getCategoryLink($id_category);
            $nb = $this->checkUrlgetLogs($url);

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1){
                Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE',$this->context->link->getCategoryLink($params['object']->id));

            }
        }
        



        public function hookActionCategoryDelete($params)
        {

            if (!isset($params['category'])) {
                return;
            }

            $id_category= (int) $params['category']->id;
            $exclude = $this->getItemExclude($id_category, 'categories', 'id_category');

            $url = $this->context->link->getCategoryLink($id_category);
            $nb = $this->checkUrlgetLogs($url);

            $listitem = array($id_category);
            $type = array('category');


            if($exclude != 1 && $params['category']->active == 1  && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                $sendday = $this->countSend(date("Y-m-d"));
                   $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                     $reponse  = (isset($reponse[$id_category])) ? $reponse[$id_category] : 403;
                     Db::getInstance()->delete('opartindexnow_categories',
                    '`id_category` = ' . (int) $id_category . ' AND `id_shop`=' . (int) $this->context->shop->id
                    );


                       $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['category']->name[$this->context->language->id] . '",
                                ' .     $reponse . ',
                                "' . Configuration::get('OPARTINDEXNOW_URL_DELETE'). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);
                }
                else{

                       $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . $params['category']->name[$this->context->language->id] . '",
                                "category",
                                '.$id_category.',
                                "' .  $this->context->link->getCategoryLink($id_category). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);
                }

            }

           
        }

          public function hookActionObjectSupplierUpdateAfter($params){


            if (!isset($params['object'])) {
                return;
            }

            $id_supplier = (int) $params['object']->id;
            $exclude = $this->getItemExclude($id_supplier, 'suppliers', 'id_supplier');

            $url = $this->context->link->getSupplierLink($id_supplier);
            $nb = $this->checkUrlgetLogs($url);

            $listitem = array($id_supplier);
            $type = array('supplier');

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                $sendday = $this->countSend(date("Y-m-d"));
                 $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                    $reponse  = (isset($reponse[$id_supplier])) ? $reponse[$id_supplier] : 403;



                    $sql =
                            'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_suppliers
                                (id_supplier, id_lang, id_shop,status,date_upd)
                            VALUES (
                                ' . (int) $id_supplier . ',
                                ' . (int) $this->context->language->id . ',
                                ' . (int) $this->context->shop->id . ',
                                ' .  $reponse . ',
                                "' . date("Y-m-d H:i:s") . '"
                            ) ON DUPLICATE KEY UPDATE
                                status = ' . (int) $reponse.',
                                date_upd = "' .date("Y-m-d H:i:s").'"';
                    
                     $result = Db::getInstance()->Execute($sql);


                      $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['object']->name . '",
                                ' .     $reponse . ',
                                "' .  $this->context->link->getSupplierLink($params['object']->id). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);
                }
                else{

                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . $params['object']->name . '",
                                "supplier",
                                '.$id_supplier.',
                                "' .  $this->context->link->getSupplierLink($params['object']->id). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);


                }

            }

        }


        public function hookactionObjectSupplierDeleteBefore($params){


            if (!isset($params['object'])) {
                return;
            }

            $id_supplier = (int) $params['object']->id;
            $exclude = $this->getItemExclude($id_supplier, 'suppliers', 'id_supplier');

            $url = $this->context->link->getSupplierLink($id_supplier);
            $nb = $this->checkUrlgetLogs($url);

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1){
                Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE',$this->context->link->getSupplierLink($params['object']->id));

            }
        }
        



        public function hookActionObjectSupplierDeleteAfter($params)
        {
            if (!isset($params['object'])) {
                return;
            }

            $id_supplier = (int) $params['object']->id;
            $exclude = $this->getItemExclude($id_supplier, 'suppliers', 'id_supplier');

            $url = $this->context->link->getSupplierLink($id_supplier);
            $nb = $this->checkUrlgetLogs($url);

            $listitem = array($id_supplier);
            $type = array('supplier');

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                $sendday = $this->countSend(date("Y-m-d"));
                 $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                    $reponse  = (isset($reponse[$id_supplier])) ? $reponse[$id_supplier] : 403;
                    
                     Db::getInstance()->delete('opartindexnow_suppliers',
                    '`id_supplier` = ' . (int) $id_supplier . ' AND `id_shop`=' . (int) $this->context->shop->id
                    );


                     $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['object']->name . '",
                                ' .     $reponse . ',
                                "' . Configuration::get('OPARTINDEXNOW_URL_DELETE'). '",
                                "' .date("Y-m-d H:i:s"). '")';


                    $result = Db::getInstance()->Execute($sql);
                }
                else{

                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . $params['object']->name . '",
                                "supplier",
                                '.$id_supplier.',
                                "' .  $this->context->link->getSupplierLink($params['object']->id). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);
                }

            }

           
        }

        public function hookactionObjectSupplierAddAfter($params){
            return $this->hookActionObjectSupplierUpdateAfter($params);
        }




          public function hookActionObjectCmsUpdateAfter($params){

            if (!isset($params['object'])) {
                return;
            }


            $id_cms = (int) $params['object']->id;

            $exclude = $this->getItemExclude($id_cms, 'cms', 'id_cms');

            $url = $this->context->link->getCmsLink($id_cms);
            $nb = $this->checkUrlgetLogs($url);


            $listitem = array($id_cms);
            $type = array('cms');

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                $sendday = $this->countSend(date("Y-m-d"));
                $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                     $reponse  = (isset($reponse[$id_cms])) ? $reponse[$id_cms] : 403;



                    $sql =
                            'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_cms
                                (id_cms, id_lang, id_shop,status,date_upd)
                            VALUES (
                                ' . (int) $id_cms . ',
                                ' . (int) $this->context->language->id . ',
                                ' . (int) $this->context->shop->id . ',
                                ' .  $reponse . ',
                                "' . date("Y-m-d H:i:s") . '"
                            ) ON DUPLICATE KEY UPDATE
                                status = ' . (int) $reponse.',
                                date_upd = "' .date("Y-m-d H:i:s").'"';
                    
                     $result = Db::getInstance()->Execute($sql);


                       $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['object']->meta_title[$this->context->language->id] . '",
                                ' .     $reponse . ',
                                "' .  $this->context->link->getCmsLink($params['object']->id). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);
                }
                else{

                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . $params['object']->meta_title[$this->context->language->id] . '",
                                "cms",
                                '.$id_cms.',
                                "' .  $this->context->link->getCmsLink($params['object']->id). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);

                }

            }

        }



        public function hookactionObjectCmsDeleteBefore($params){


            if (!isset($params['object'])) {
                return;
            }

            $id_cms = (int) $params['object']->id;

            $exclude = $this->getItemExclude($id_cms, 'cms', 'id_cms');

            $url = $this->context->link->getCmsLink($id_cms);
            $nb = $this->checkUrlgetLogs($url);

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE',$this->context->link->getCmsLink($params['object']->id));

            }
        }
        



        public function hookactionObjectCmsDeleteAfter($params)
        {
            if (!isset($params['object'])) {
                return;
            }

            $id_cms = (int) $params['object']->id;
            $exclude = $this->getItemExclude($id_cms, 'cms', 'id_cms');

            $url = $this->context->link->getCmsLink($id_cms);
            $nb = $this->checkUrlgetLogs($url);

            $listitem = array($id_cms);
            $type = array('cms');

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                $sendday = $this->countSend(date("Y-m-d"));
                  $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                                     $reponse  = (isset($reponse[$id_cms])) ? $reponse[$id_cms] : 403;

                    
                     Db::getInstance()->delete('opartindexnow_cms',
                    '`id_cms` = ' . (int) $id_cms . ' AND `id_shop`=' . (int) $this->context->shop->id
                    );


                     $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['object']->meta_title[$this->context->language->id]. '",
                                ' .     $reponse . ',
                                "' . Configuration::get('OPARTINDEXNOW_URL_DELETE'). '",
                                "' .date("Y-m-d H:i:s"). '")';


                $result = Db::getInstance()->Execute($sql);

                }
                else{

                     $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . $params['object']->meta_title[$this->context->language->id] . '",
                                "cms",
                                '.$id_cms.',
                                "' .  $this->context->link->getCmsLink($params['object']->id). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);

                }


            }

           
        }

        public function hookactionObjectCmsAddAfter($params){
            return $this->hookActionObjectCmsUpdateAfter($params);
        }


           public function hookactionObjectManufacturerUpdateAfter($params){

            if (!isset($params['object'])) {
                return;
            }

            $id_manufacturer = (int) $params['object']->id;
            $exclude = $this->getItemExclude($id_manufacturer, 'manufacturers', 'id_manufacturer');

            $url = $this->context->link->getManufacturerLink($id_manufacturer);
            $nb = $this->checkUrlgetLogs($url);

            $listitem = array($id_manufacturer);
            $type = array('manufacturer');

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                $sendday = $this->countSend(date("Y-m-d"));
                  $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }
                

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                    $reponse  = (isset($reponse[$id_manufacturer])) ? $reponse[$id_manufacturer] : 403;



                    $sql =
                            'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_manufacturers
                                (id_manufacturer, id_lang, id_shop,status,date_upd)
                            VALUES (
                                ' . (int) $id_manufacturer . ',
                                ' . (int) $this->context->language->id . ',
                                ' . (int) $this->context->shop->id . ',
                                ' .  $reponse. ',
                                "' . date("Y-m-d H:i:s") . '"
                            ) ON DUPLICATE KEY UPDATE
                                status = ' . (int) $reponse.',
                                date_upd = "' .date("Y-m-d H:i:s").'"';
                    
                     $result = Db::getInstance()->Execute($sql);

                      $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['object']->name . '",
                                ' .     $reponse . ',
                                "' .  $this->context->link->getManufacturerLink($params['object']->id). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);

                }
                else{

                     $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . $params['object']->name . '",
                                "manufacturer",
                                '.$id_manufacturer.',
                                "' .  $this->context->link->getManufacturerLink($params['object']->id). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);

                }

            }

        }


        public function hookactionObjectManufacturerDeleteBefore($params){


             if (!isset($params['object'])) {
                return;
            }

            $id_manufacturer = (int) $params['object']->id;
            $exclude = $this->getItemExclude($id_manufacturer, 'manufacturers', 'id_manufacturer');

            $url = $this->context->link->getManufacturerLink($id_manufacturer);
            $nb = $this->checkUrlgetLogs($url);

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1){
                Configuration::UpdateValue('OPARTINDEXNOW_URL_DELETE',$this->context->link->getManufacturerLink($params['object']->id));

            }
        }



        public function hookactionObjectManufacturerDeleteAfter($params)
        {
            if (!isset($params['object'])) {
                return;
            }

            $id_manufacturer = (int) $params['object']->id;
            $exclude = $this->getItemExclude($id_manufacturer, 'manufacturers', 'id_manufacturer');

            $url = $this->context->link->getManufacturerLink($id_manufacturer);
            $nb = $this->checkUrlgetLogs($url);

            $listitem = array($id_manufacturer);
            $type = array('manufacturer');

            if($exclude != 1 && $params['object']->active == 1 && Configuration::get('OPARTINDEXNOW_KEY',null,null,$this->context->shop->id) && $nb < 1 ){
                $sendday = $this->countSend(date("Y-m-d"));
                 $lastping = new DateTime($this->lastSend());
                
                if($sendday > 0){
                    $now =  new DateTime('now');
                    $interval = $lastping->diff($now);
                    $minute = $interval->format('%i');
                }
                else{
                    $minute = 2;
                }

                if($sendday < Configuration::get('INDEXNOW_LIMITSEND') && $minute > 1){
                    $items = [];
                    $reponse = $this->SendIndexNow($listitem, $items,$type);
                    $reponse  = (isset($reponse[$id_manufacturer])) ? $reponse[$id_manufacturer] : 403;
                    
                     Db::getInstance()->delete('opartindexnow_manufacturers',
                    '`id_manufacturer` = ' . (int) $id_manufacturer . ' AND `id_shop`=' . (int) $this->context->shop->id
                    );


                      $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_logs
                                (name, status, url,date_upd)
                            VALUES (
                                "' . $params['object']->name . '",
                                ' .     $reponse . ',
                                "' . Configuration::get('OPARTINDEXNOW_URL_DELETE'). '",
                                "' .date("Y-m-d H:i:s"). '")';


                    $result = Db::getInstance()->Execute($sql);
                }
                else{

                     $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartindexnow_waiting
                                (name,type, id, url,date_upd)
                            VALUES (
                                "' . $params['object']->name . '",
                                "manufacturer",
                                '.$id_manufacturer.',
                                "' .  $this->context->link->getManufacturerLink($params['object']->id). '",
                                "' .date("Y-m-d H:i:s"). '")';

                    $result = Db::getInstance()->Execute($sql);
                }

            }

           
        }

        public function hookactionObjectManufacturerAddAfter($params){
            return $this->hookactionObjectManufacturerUpdateAfter($params);
        }

        public function hookdisplayAdminAfterHeader(){
            if(!Configuration::get('OPARTINDEXNOW_KEY')){
                $module_obj = new OpartIndexnow();
                $redirection = $this->context->link->getAdminLink('AdminModules', false)
                .'&configure='.$module_obj->name.'&tab_module='.$module_obj->tab.'&module_name='.$module_obj->name.'&token='
                .Tools::getAdminTokenLite('AdminModules');
                $this->smarty->assign('redirection',$redirection);
                return $this->display(__FILE__, 'notif.tpl');
            }
            if($this->active && $this->getLastlog() == "403"){
                $module_obj = new OpartIndexnow();
                $redirection = $this->context->link->getAdminLink('AdminModules', false)
                .'&configure='.$module_obj->name.'&tab_module='.$module_obj->tab.'&module_name='.$module_obj->name.'&token='
                .Tools::getAdminTokenLite('AdminModules');
                $this->smarty->assign('redirection',$redirection);
                 return $this->display(__FILE__, 'notifstatus.tpl');
            }
        }





    public function getCategoryTree($id_lang)
    {
        $sql = 'SELECT c.id_category, c.id_parent, cl.name
            FROM `' . _DB_PREFIX_ . 'category` c
            ' . Shop::addSqlAssociation('category', 'c') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . '
            WHERE 1 ' . ($id_lang ? 'AND `id_lang` = ' . (int) $id_lang : '');

        $result  = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $dataset = array();
        foreach ($result as $value) {
            $dataset[$value['id_category']] = $value;

            if (!is_numeric($value['id_parent']) || $value['id_parent'] == 0) {
                $dataset[$value['id_category']]['id_parent'] = null;
            }
        }

        $tree = array();
        foreach ($dataset as $id => &$node) {
            if ($node['id_parent'] === null || $node['id_parent'] == '' || !is_numeric($node['id_parent'])) {
                $tree[$id] =& $node;
            } else {
                if (!isset($dataset[$node['id_parent']]['children'])) {
                    $dataset[$node['id_parent']]['children'] = array();
                }

                $dataset[$node['id_parent']]['children'][$id] =& $node;
            }
        }

        return $tree;
    }


    public function nested2select($data, $selected_category, $spaces = '')
    {
        $result = array();
        if (sizeof($data) > 0) {
            foreach ($data as $entry) {
                $selected = ($entry['id_category'] == $selected_category) ? 'selected="selected"' : '';
                $child =
                    (isset($entry['children'])) ? $this->nested2select(
                        $entry['children'],
                        $selected_category,
                        $spaces . '&nbsp;'
                    ) : '';
                $result[] = sprintf(
                    '<option value="%s" ' . $selected . '>' . $spaces . '%s </option>%s',
                    $entry['id_category'],
                    $entry['name'],
                    $child
                );
            }
        }

        return implode($result);
    }


}