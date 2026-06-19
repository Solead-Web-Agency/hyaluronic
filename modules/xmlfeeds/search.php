<?php
/**
 * 2010-2023 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2023 Bl Modules
 * @license
 */

$_POST = [];
$_FILES = [];
$_GET['module'] = 'xmlfeeds';

require(dirname(__FILE__).'/../../config/config.inc.php');
require(dirname(__FILE__).'/../../modules/xmlfeeds/XmlFeedsTools.php');

class SearchApiXml extends ModuleFrontController
{
    private $limit = 50;
    private $wordLength = 100;
    private $langId = 1;
    private $search_type = '';
    private $s = '';

    public function __construct()
    {
        $this->module = 'xmlfeeds';

        parent::__construct();
    }

    public function init()
    {
        $this->s = htmlspecialchars(Tools::getValue('s'), ENT_QUOTES);

        if (empty($this->s)) {
            die();
        }

        $this->langId = (int)(Configuration::get('PS_LANG_DEFAULT'));
        $this->search_type = htmlspecialchars(Tools::getValue('s_t'), ENT_QUOTES);
        $selectedProducts = trim(Tools::getValue('s_p'), ',');
        $whereSelected = '';
        $moduleImgPath = '../modules/xmlfeeds/views/img/';
        $where = 'l.id_product = "'.(int)$this->s.'"';

        if (!empty($selectedProducts) && $selectedProducts != 'undefined') {
            $idList = explode(',', $selectedProducts);
            $idListInt = [];

            foreach ($idList as $i) {
                $i = (int)$i;

                if (empty($i)) {
                    continue;
                }

                $idListInt[] = $i;
            }

            $whereSelected = ' AND l.id_product NOT IN ('.pSQL(implode(',', $idListInt)).')';
        }

        if ($this->search_type != 'search_id') {
            $this->search_type = 'search_name';
            $where = 'l.name LIKE "%'.pSQL($this->s).'%"';
        }

        $products = $this->getProducts($where, $whereSelected);

        $this->context->smarty->assign([
            'products' => $products,
            'moduleImgPath' => $moduleImgPath,
            'totalProducts' => count($products),
            'limit' => $this->limit,
        ]);

        echo $this->context->smarty->fetch(_PS_MODULE_DIR_.'xmlfeeds/views/templates/admin/page/searchApi.tpl');
    }

    private function getProducts($where, $whereSelected)
    {
        return $this->productTransformer(Db::getInstance()->ExecuteS('SELECT DISTINCT(l.id_product), l.name, 
            cl.name AS cat_name, i.id_image
            FROM '._DB_PREFIX_.'product_lang l
            LEFT JOIN '._DB_PREFIX_.'product p ON
            l.id_product = p.id_product
            LEFT JOIN '._DB_PREFIX_.'category_lang cl ON
            (p.id_category_default = cl.id_category AND cl.id_lang = "'.(int)$this->langId.'")
            LEFT JOIN `'._DB_PREFIX_.'image` i ON
            (p.id_product = i.id_product AND i.`cover`= "1")
            WHERE '.$where.' AND l.id_lang = "'.(int)$this->langId.'"'.$whereSelected.'
            GROUP BY l.id_product
            ORDER BY l.name ASC
            LIMIT '.(int)$this->limit));
    }

    private function highlight($needle, $haystack)
    {
        $container = 'span';
        $style = 'class';
        $ind = stripos($haystack, $needle);
        $len = Tools::strlen($needle);

        if ($ind !== false) {
            return Tools::substr($haystack, 0, $ind) . '<'.$container.' '.$style.'="find_word">' . Tools::substr($haystack, $ind, $len) . '</'.$container.'>' . $this->highlight($needle, Tools::substr($haystack, $ind + $len));
        }

        return $haystack;
    }

    private function productTransformer($products = array())
    {
        if (empty($products)) {
            return $products;
        }

        $imageClassName = (!class_exists('ImageCore', false) || _PS_VERSION_ > '1.5.3') ?  'Image' : 'ImageCore';
        $imgType = XmlFeedsTools::getImageType();

        foreach ($products as $k => $p) {
            $cat_name = '';

            if (Tools::strlen($p['name']) > $this->wordLength) {
                $products[$k]['name'] = Tools::substr($p['name'], 0, $this->wordLength) . '...';
            }

            if ($this->search_type == 'search_name') {
                $p['name'] = $this->highlight($this->s, $p['name']);
            }

            if (!empty($p['cat_name'])) {
                $cat_name = ', ' . $p['cat_name'];
            }

            $imageClass = new $imageClassName($p['id_image']);
            $name = $imageClass->getExistingImgPath();
            $url = _PS_BASE_URL_._THEME_PROD_DIR_.$name.$imgType;

            if (!file_exists(_PS_PROD_IMG_DIR_.$name.$imgType)) {
                $url = _PS_BASE_URL_._THEME_PROD_DIR_.$name.'.jpg';
            }

            $products[$k]['img_url'] = $url;
            $products[$k]['cat_name'] = $cat_name;
        }

        return $products;
    }
}

$searchApiXml = new SearchApiXml();
$searchApiXml->init();
