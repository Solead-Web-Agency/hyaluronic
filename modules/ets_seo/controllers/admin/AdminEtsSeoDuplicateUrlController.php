<?php
/**
 * 2007-2021 ETS-Soft
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
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class AdminEtsSeoDuplicateUrlController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap  = true;
        parent::__construct();
        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }
    }

    public function renderList()
    {
        return $this->getDuplicateProduct()
            .$this->getDuplicateCategories()
            .$this->getDuplicateCMS()
            .$this->getDuplicateCMSCategory()
            .$this->getDuplicateMeta();
    }

    public function getDuplicateProduct()
    {
        $this->toolbar_title = $this->l('Products');
        $this->identifier = 'id_product';
        $this->table = 'product';
        $this->_join = "LEFT JOIN `"._DB_PREFIX_."product_lang` pl ON a.id_product = pl.id_product";
        $this->_select = "pl.`link_rewrite`,COUNT(pl.id_product) as count_id, MIN(pl.id_product) as minid, pl.`id_shop`, pl.`id_lang`";
        $this->_where = " AND pl.`id_shop` = ".(int)$this->context->shop->id. " AND pl.`link_rewrite` IS NOT NULL AND pl.`link_rewrite` != ''";
        $this->_group = " GROUP BY pl.id_lang, pl.link_rewrite";
        $this->_having = "COUNT(pl.id_product) > 1";
        $this->_orderBy = "minid";
        $this->_orderWay = "DESC";
        $this->actions = array('view');
        $this->list_simple_header = true;
        $this->fields_list = array(
            'link_rewrite' => array(
                'title'=> $this->l('Link rewrite'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'language' => array(
                'title'=> $this->l('Language'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'count_id' => array(
                'title'=> $this->l('Total link duplicate'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'links' => array(
                'title'=> $this->l('Name'),
                'align' => 'text-left',
                'float'=> true,
                'remove_onclick' => true
            ),
        );
        return parent::renderList();
    }

    public function getDuplicateCategories()
    {
        $this->toolbar_title = $this->l('Product categories');
        $this->identifier = 'id_category';
        $this->table = 'category';
        $this->_join = "LEFT JOIN `"._DB_PREFIX_."category_lang` cl ON a.id_category = cl.id_category";
        $this->_select = "cl.`link_rewrite`,COUNT(cl.id_category) as count_id, MIN(cl.id_category) as minid, cl.`id_shop`, cl.`id_lang`";
        $this->_where = " AND cl.`id_shop` = ".(int)$this->context->shop->id." AND cl.`link_rewrite` IS NOT NULL AND cl.`link_rewrite` != ''";
        $this->_group = " GROUP BY cl.id_lang, cl.link_rewrite";
        $this->_having = "COUNT(cl.id_category) > 1";
        $this->_orderBy = "minid";
        $this->_orderWay = "DESC";
        $this->actions = array('view');
        $this->list_simple_header = true;
        $this->fields_list = array(
            'link_rewrite' => array(
                'title'=> $this->l('Link rewrite'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'language' => array(
                'title'=> $this->l('Language'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'count_id' => array(
                'title'=> $this->l('Total link duplicate'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'links' => array(
                'title'=> $this->l('Name'),
                'align' => 'text-left',
                'float'=> true,
                'remove_onclick' => true
            ),
        );
        return parent::renderList();
    }

    public function  getDuplicateCMS()
    {
        $this->toolbar_title = $this->l('CMS (pages)');
        $this->identifier = 'id_cms';
        $this->table = 'cms';
        $this->_join = "LEFT JOIN `"._DB_PREFIX_."cms_lang` cl ON a.id_cms = cl.id_cms";
        $this->_select = "cl.`link_rewrite`,COUNT(cl.id_cms) as count_id, MIN(cl.id_cms) as minid, cl.`id_shop`, cl.`id_lang`, cl.id_cms";
        $this->_where = " AND cl.`id_shop` = ".(int)$this->context->shop->id." AND cl.`link_rewrite` IS NOT NULL AND cl.`link_rewrite` != ''";
        $this->_group = " GROUP BY cl.id_lang, cl.link_rewrite";
        $this->_having = "COUNT(cl.id_cms) > 1";
        $this->_orderBy = "minid";
        $this->_orderWay = "DESC";
        $this->actions = array('view');
        $this->list_simple_header = true;
        $this->fields_list = array(
            'link_rewrite' => array(
                'title'=> $this->l('Link rewrite'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'language' => array(
                'title'=> $this->l('Language'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'count_id' => array(
                'title'=> $this->l('Total link duplicate'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'links' => array(
                'title'=> $this->l('Name'),
                'align' => 'text-left',
                'float'=> true,
                'remove_onclick' => true
            ),
        );
        return parent::renderList();
    }

    public function getDuplicateCMSCategory()
    {
        $this->toolbar_title = $this->l('CMS categories');
        $this->identifier = 'id_cms_category';
        $this->table = 'cms_category';
        $this->_join = "INNER JOIN `"._DB_PREFIX_."cms_category_lang` cl ON a.id_cms_category = cl.id_cms_category";
        $this->_select = "cl.`link_rewrite`,COUNT(cl.id_cms_category) as count_id, MIN(cl.id_cms_category) as minid, cl.`id_shop`, cl.`id_lang`, cl.id_cms_category";
        $this->_where = " AND cl.`id_shop` = ".(int)$this->context->shop->id." AND cl.`link_rewrite` IS NOT NULL AND cl.`link_rewrite` != ''";
        $this->_group = " GROUP BY cl.id_lang, cl.link_rewrite";
        $this->_having = "COUNT(cl.id_cms_category) > 1";
        $this->_orderBy = "minid";
        $this->_orderWay = "DESC";
        $this->actions = array('view');
        $this->list_simple_header = true;
        $this->fields_list = array(
            'link_rewrite' => array(
                'title'=> $this->l('Link rewrite'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'language' => array(
                'title'=> $this->l('Language'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'count_id' => array(
                'title'=> $this->l('Total link duplicate'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'links' => array(
                'title'=> $this->l('Name'),
                'align' => 'text-left',
                'float'=> true,
                'remove_onclick' => true
            ),
        );
        return parent::renderList();
    }

    public function getDuplicateMeta()
    {
        $this->toolbar_title = $this->l('Other pages');
        $this->identifier = 'id_meta';
        $this->table = 'meta';
        $this->_join = "INNER JOIN `"._DB_PREFIX_."meta_lang` ml ON a.id_meta = ml.id_meta";
        $this->_select = "ml.`url_rewrite`,COUNT(ml.id_meta) as count_id, MIN(ml.id_meta) as minid, ml.`id_shop`, ml.`id_lang`, ml.id_meta";
        $this->_where = " AND ml.url_rewrite IS NOT NULL AND ml.url_rewrite != '' AND ml.`id_shop` = ".(int)$this->context->shop->id;
        $this->_group = " GROUP BY ml.id_lang, ml.url_rewrite";
        $this->_having = "COUNT(ml.id_meta) > 1";
        $this->_orderBy = "minid";
        $this->_orderWay = "DESC";
        $this->actions = array('view');
        $this->list_simple_header = true;
        $this->fields_list = array(
            'url_rewrite' => array(
                'title'=> $this->l('Link rewrite'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'language' => array(
                'title'=> $this->l('Language'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'count_id' => array(
                'title'=> $this->l('Total link duplicate'),
                'align' => 'text-center',
                'remove_onclick' => true
            ),
            'links' => array(
                'title'=> $this->l('Name'),
                'align' => 'text-left',
                'float'=> true,
                'remove_onclick' => true
            ),
        );
        return parent::renderList();
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        if($this->table == 'product')
        {
            foreach ($this->_list as $key=>&$item)
            {
                $item['language'] = Language::getIsoById((int)$item['id_lang']);
                if(!$item['language']){
                    unset($this->_list[$key]);
                    continue;
                }
                $duplicate = Db::getInstance()->executeS("SELECT id_product, name FROM `"._DB_PREFIX_."product_lang` WHERE link_rewrite='".(string)$item['link_rewrite']."' AND id_lang = '".(int)$item['id_lang']."'");
                $links = '';
                foreach ($duplicate as $data)
                {
                    $links .= $this->module->getLinkDuplicate('product', array('id' => $data['id_product'], 'title'=> $data['name']));
                }
                $item['links'] = $links;
            }

            if(isset($item)){
                unset($item);
            }
        }
        elseif($this->table == 'category')
        {
            foreach ($this->_list as $key=>&$item)
            {
                $item['language'] = Language::getIsoById((int)$item['id_lang']);
                if(!$item['language']){
                    unset($this->_list[$key]);
                    continue;
                }
                $duplicate = Db::getInstance()->executeS("SELECT id_category, name FROM `"._DB_PREFIX_."category_lang` WHERE link_rewrite='".(string)$item['link_rewrite']."' AND id_lang = '".(int)$item['id_lang']."'");
                $links = '';
                foreach ($duplicate as $data)
                {
                    $links .= $this->module->getLinkDuplicate('category', array('id' => $data['id_category'], 'title'=> $data['name']));
                }
                $item['links'] = $links;
            }
            if(isset($item)){
                unset($item);
            }
        }
        elseif($this->table == 'cms')
        {
            foreach ($this->_list as $key=> &$item)
            {
                $item['language'] = Language::getIsoById((int)$item['id_lang']);
                if(!$item['language']){
                    unset($this->_list[$key]);
                    continue;
                }
                $duplicate = Db::getInstance()->executeS("SELECT id_cms, meta_title FROM `"._DB_PREFIX_."cms_lang` WHERE link_rewrite='".(string)$item['link_rewrite']."' AND id_lang = '".(int)$item['id_lang']."'");
                $links = '';
                foreach ($duplicate as $data)
                {
                    $links .= $this->module->getLinkDuplicate('cms', array('id' => $data['id_cms'], 'title'=> $data['meta_title']));
                }
                $item['links'] = $links;
            }
            if(isset($item)){
                unset($item);
            }
        }
        elseif($this->table == 'cms_category')
        {
            foreach ($this->_list as $key=>&$item)
            {
                $item['language'] = Language::getIsoById((int)$item['id_lang']);
                if(!$item['language']){
                    unset($this->_list[$key]);
                    continue;
                }
                $duplicate = Db::getInstance()->executeS("SELECT id_cms_category, name FROM `"._DB_PREFIX_."cms_category_lang` WHERE link_rewrite='".(string)$item['link_rewrite']."' AND id_lang = '".(int)$item['id_lang']."'");
                $links = '';
                foreach ($duplicate as $data)
                {
                    $links .= $this->module->getLinkDuplicate('cms_category', array('id' => $data['id_cms_category'], 'title'=> $data['name']));
                }
                $item['links'] = $links;
            }
            if(isset($item)){
                unset($item);
            }
        }
        elseif($this->table == 'meta')
        {
            foreach ($this->_list as $key=>&$item)
            {
                $item['language'] = Language::getIsoById((int)$item['id_lang']);
                if(!$item['language']){
                    unset($this->_list[$key]);
                    continue;
                }
                $duplicate = Db::getInstance()->executeS("SELECT id_meta, title FROM `"._DB_PREFIX_."meta_lang` WHERE url_rewrite='".(string)$item['url_rewrite']."' AND id_lang = '".(int)$item['id_lang']."'");
                $links = '';
                foreach ($duplicate as $data)
                {
                    $links .= $this->module->getLinkDuplicate('meta', array('id' => $data['id_meta'], 'title'=> $data['title']));
                }
                $item['links'] = $links;
            }
            if(isset($item)){
                unset($item);
            }
        }
    }

}