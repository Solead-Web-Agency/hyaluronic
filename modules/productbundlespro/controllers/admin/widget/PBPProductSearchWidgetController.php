<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

class PBPProductSearchWidgetController
{
    public $id;

    private $sibling;

    private $module_folder = 'productbundlespro';

    /**
     * PBPProductSearchWidgetController constructor.
     * @param $id
     * @param $sibling
     */
    public function __construct($id, $sibling)
    {
        $this->id = $id;
        $this->sibling = $sibling;
    }

    /**
     * @return mixed
     */
    public function render($selected_products = array())
    {
        Context::getContext()->smarty->assign(array(
            'id' => $this->id,
            'selected_products' => $selected_products
        ));
        return $this->sibling->display(_PS_MODULE_DIR_ . $this->module_folder, 'views/templates/admin/widget/pbpproductsearchwidget.tpl');
    }

    public function getSearchResults($product_name, $id_lang)
    {
        $sql = new DbQuery();
        $sql->select('DISTINCT(pl.id_product), name, reference');
        $sql->from('product_lang', 'pl');
        $sql->innerJoin('product', 'p', 'pl.id_product = p.id_product AND pl.id_lang = ' . (int)$id_lang);
        $sql->where('pl.name LIKE "%' . pSQL($product_name) . '%" OR p.reference LIKE "%' . pSQL($product_name) . '%" OR p.id_product = ' . (int)$product_name);
        $results = Db::getInstance()->executeS($sql);
        return $results;
    }

    public function processSearch()
    {
        return $this->getSearchResults(Tools::getValue('search_string'), Context::getContext()->language->id);
    }

    public function route()
    {
        switch (Tools::getValue('action')) {
            case 'processsearch':
                return $this->processSearch();
        }
    }
}
