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
 * @copyright 2012-2017 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class PackRowSeoSa
{
    public static $errors = array();

    public static function getPackDataForMore(Product $product)
    {
        $context = Context::getContext();
        $pack_row = array();
        $pack_row['can_pack'] = !$product->getWsCombinations() && !$product->is_virtual;
        if (Tools::getValue('namePackItems')) {
            $input_pack_items = Tools::getValue('inputPackItems');
            $input_namepack_items = Tools::getValue('namePackItems');
            $pack_items = self::getPackItems();
        } else {
            $product->packItems = Pack::getItems($product->id, $context->language->id);
            $pack_items = self::getPackItems($product);
            $input_namepack_items = '';
            $input_pack_items = '';
            foreach ($pack_items as $pack_item) {
                $input_pack_items .= $pack_item['pack_quantity'] . 'x' . $pack_item['id'] .
                    'x' . $pack_item['id_product_attribute'] . '-';
                $input_namepack_items .= $pack_item['pack_quantity'] . ' x ' . $pack_item['name'] . '¤';
            }
        }

        $pack_row['input_pack_items'] = $input_pack_items;
        $pack_row['input_namepack_items'] = $input_namepack_items;
        $pack_row['pack_items'] = $pack_items;

        return $pack_row;
    }

    public static function getPackItems(Product $product)
    {
        $pack_items = array();
        $i = 0;
        foreach ($product->packItems as $pack_item) {
            $pack_items[$i]['id'] = $pack_item->id;
            $pack_items[$i]['pack_quantity'] = $pack_item->pack_quantity;
            $pack_items[$i]['name'] = $pack_item->name;
            $pack_items[$i]['reference'] = $pack_item->reference;
            $pack_items[$i]['id_product_attribute'] = isset($pack_item->id_pack_product_attribute)
            && $pack_item->id_pack_product_attribute ? $pack_item->id_pack_product_attribute : 0;
            $cover = $pack_item->id_pack_product_attribute ? Product::getCombinationImageById(
                $pack_item->id_pack_product_attribute,
                Context::getContext()->language->id
            ) : Product::getCover($pack_item->id);
            $link = Context::getContext()->link;
            $type = 'home' . '_default';
            $pack_items[$i]['image'] = $link->getImageLink($pack_item->link_rewrite, $cover['id_image'], $type);
            // @todo: don't rely on 'home_default'
            //$path_to_image = _PS_IMG_DIR_.'p/'.Image::getImgFolderStatic($cover['id_image']).
            //(int)$cover['id_image'].'.jpg';
            //$pack_items[$i]['image'] = ImageManager::thumbnail(
            //$path_to_image,
            // 'pack_mini_'.$pack_item->id.'_'.$this->context->shop->id.'.jpg',
            // 120
            //);
            $i++;
        }
        return $pack_items;
    }

    public static function updatePackItems(Product $product)
    {
        Pack::deleteItems($product->id);
        // lines format: QTY x ID-QTY x ID
        if (Tools::getValue('inputPackItems')) {
            $product->setDefaultAttribute(0);//reset cache_default_attribute
            $items = Tools::getValue('inputPackItems');
            $lines = array_unique(explode('-', $items));

            // lines is an array of string with format : QTYxIDxID_PRODUCT_ATTRIBUTE
            if (count($lines)) {
                foreach ($lines as $line) {
                    if (!empty($line)) {
                        $item_id_attribute = 0;
                        count($array = explode('x', $line)) == 3 ? list(
                            $qty,
                            $item_id,
                            $item_id_attribute
                            ) = $array : list($qty, $item_id) = $array;
                        if ($qty > 0 && isset($item_id)) {
                            if (Pack::isPack((int)$item_id)) {
                                self::$errors[] = 'You can\'t add product packs into a pack';
                                return false;
                            } elseif (!Pack::addItem(
                                (int)$product->id,
                                (int)$item_id,
                                (int)$qty,
                                (int)$item_id_attribute
                            )
                            ) {
                                self::$errors[] = 'An error occurred while attempting to add products to the pack.';
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
}
