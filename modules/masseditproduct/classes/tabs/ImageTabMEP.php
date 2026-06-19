<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    SeoSA <885588@bk.ru>
 * @copyright 2012-2020 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class ImageTabMEP extends BaseTabMEP
{
    public $response_images;
    public $delete_images;

    public function __construct()
    {
        parent::__construct();
        $this->response_images = Tools::getValue('responseImages');
        $this->delete_images = (int)Tools::getValue('delete_images');
    }

    public function applyChangeBoth($products, $combinations)
    {
        if ($this->checkAccessField('disable_image_caption')) {
            $in = implode(
                ',',
                array_map('intval', $products)
            );

            $sub_sql = 'SELECT `id_image` FROM `' . _DB_PREFIX_ . 'image_shop` 
            WHERE `id_product` IN(' . pSQL($in) . ') AND `id_shop` = ' . (int)$this->context->shop->id;

            if (Tools::getValue('delete_captions')) {
                $sql = 'UPDATE `'. _DB_PREFIX_ .'image_lang` SET `legend` = "" WHERE `id_image` IN('.pSQL($sub_sql).')';
                if (!Db::getInstance()->execute($sql)) {
                    LoggerMEP::getInstance()->error($this->l('Failed to remove caption'));
                }
            }

            $reg = '|{([^{}]{4,})}|u';

            foreach (Language::getLanguages(true, false, true) as $lang) {
                $legend = Tools::getValue('legend_' . $lang);

                $where_position = Tools::getValue('position')
                    ? ' AND `position` = ' . (int)Tools::getValue('position') : '';

                $sub_sql .= $where_position;
                $join = '';
                preg_match_all($reg, $legend, $matches);
                if (count($matches[1])) {
                    foreach ($matches[1] as $column) {
                        switch ($column) {
                            case 'name':
                                $legend = str_replace('{name}', '\', pl.name, \'', $legend);
                                $join .= ' JOIN `'. _DB_PREFIX_ .'product_lang` pl 
                                ON p.`id_product` = pl.`id_product` and pl.`id_lang` = ' . $lang .'';
                                break;
                            case 'category':
                                $legend = str_replace('{category}', '\', cl.name, \'', $legend);
                                $join .= ' JOIN `' . _DB_PREFIX_
                                    . 'category_lang` cl 
                                    ON p.`id_category_default` = cl.`id_category` and cl.`id_lang` = ' . $lang .'';
                                break;
                            case 'manufacturer':
                                $legend = str_replace('{manufacturer}', '\', m.name, \'', $legend);
                                $join .= ' JOIN `' . _DB_PREFIX_
                                    . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`';
                                break;
                        }
                    }
                }
                if ($legend && $legend[0] !== ',') {
                    $legend = '\'' . $legend;
                } else {
                    $legend = ltrim($legend, ', \'');
                }
                if ($legend && $legend[Tools::strlen($legend) - 3] !== ',') {
                    $legend = $legend . '\'';
                } else {
                    $legend = rtrim($legend, ', \'');
                }

                $column_legend = $legend ? 'CONCAT(' . $legend . ')' : '" "';
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'image_lang` il, 
                           (SELECT i.`id_image`, ' . $column_legend . ' as legend 
                             FROM `'. _DB_PREFIX_ .'product` p JOIN `'. _DB_PREFIX_ .'image` i 
                             ON p.`id_product` = i.`id_product` '. $join .'
                             WHERE p.`id_product` IN(' . pSQL($in) . ')' . $where_position . ') temp
                          SET il.`legend` = temp.`legend`
                          WHERE il.`id_image` = temp.`id_image` AND il.`id_lang` = ' . $lang;

                if (!Db::getInstance()->execute($sql)) {
                    LoggerMEP::getInstance()->error($this->l('Failed to change caption'));
                }
            }
        }
    }

    public function applyChangeForProducts($products)
    {
        if ($this->checkAccessField('disable_image')) {
            foreach ($products as $id_product) {
                $this->applyImages($id_product);
            }
            MassEditTools::clearTmpFolder();
        }

        return array();
    }

    public function applyChangeForCombinations($products)
    {
        if ($this->checkAccessField('disable_image')) {
            foreach ($products as $id_product => $combinations) {
                $this->applyImages($id_product, $combinations);
            }
            MassEditTools::clearTmpFolder();
        }

        return array();
    }

    public function checkBeforeChange()
    {
        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function checkOptionForCombination()
    {
        $change_for = (int)Tools::getValue('change_for');
        if ($change_for == self::CHANGE_FOR_COMBINATION) {
            return true;
        }
        return false;
    }

    public function applyImages($id_product, $combinations = null)
    {
        $types = ImageType::getImagesTypes('products');
        $product_obj = new Product((int)$id_product);
        if ($this->delete_images) {
            $product_obj->deleteImages();
        }

        if (is_array($this->response_images)) {
            $cover = $product_obj->getCoverWs();
            foreach ($this->response_images as $response_image) {
                if (array_key_exists('original', $response_image)) {
                    $image = new Image();
                    $image->id_product = (int)$id_product;
                    if (!$cover) {
                        $image->cover = 1;
                    }
                    if ($image->save()) {
                        if (!$cover) {
                            $cover = $image->id;
                        }

                        $image->createImgFolder();
                        call_user_func(
                            'copy',
                            MassEditTools::getPath() . $response_image['original'],
                            _PS_PROD_IMG_DIR_ . $image->getImgPath() . '.jpg'
                        );
                        foreach ($types as $type) {
                            if (array_key_exists($type['name'], $response_image)) {
                                call_user_func(
                                    'copy',
                                    MassEditTools::getPath() . $response_image[$type['name']],
                                    _PS_PROD_IMG_DIR_ . $image->getImgPath() . '-' . $type['name'] . '.jpg'
                                );
                            }
                        }

                        if (!is_null($combinations)) {
                            $product_attribute_image = array();
                            foreach ($combinations as $id_pa) {
                                $product_attribute_image[] = array(
                                    'id_image' => $image->id,
                                    'id_product_attribute' => (int)$id_pa,
                                );
                            }
                            Db::getInstance()->insert('product_attribute_image', $product_attribute_image);
                        }
                    }
                }
            }
        }
        MassEditTools::removeTmpImageProduct($product_obj->id);
    }

    public function getTitle()
    {
        return $this->l('Image');
    }
}
