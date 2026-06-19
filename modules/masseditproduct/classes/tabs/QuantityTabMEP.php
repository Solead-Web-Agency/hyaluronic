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

class QuantityTabMEP extends BaseTabMEP
{
    const CHANGE_TYPE_QUANTITY = 'quantity';
    const CHANGE_TYPE_WAREHOUSE = 'warehouse';

    public $quantity;
    public $action_quantity;
    public $change_type;
    public $language;
    public $available_now;
    public $available_later;
    public $change_available_date;
    public $available_date;
    public $out_of_stock;
    public $minimal_quantity;
    public $id_warehouse;
    public $action_warehouse;
    public $warehouse;
    public $stock_manager;
    public $check_access_field_quantity;

    public function __construct()
    {
        parent::__construct();
        $this->quantity = (int)Tools::getValue('quantity', 0);
        $this->action_quantity = (int)Tools::getValue('action_quantity');
        $this->change_type = Tools::getValue('change_type');
        $this->language = Tools::getValue('language');
        $this->language2 = Tools::getValue('language2');
        $this->available_now = Tools::getValue('available_now');
        $this->available_later = Tools::getValue('available_later');
        $this->change_available_date = (int)Tools::getValue('change_available_date');
        $this->available_date = Tools::getValue('available_date');
        $this->out_of_stock = Tools::getValue('out_of_stock');
        $this->minimal_quantity = Tools::getValue('minimal_quantity');
        $this->id_warehouse = (int)Tools::getValue('warehouse');
        $this->action_warehouse = (int)Tools::getValue('action_warehouse');
        $this->warehouse = new Warehouse($this->id_warehouse);
        $this->stock_manager = new StockManager();
        $this->check_access_field_quantity = $this->checkAccessField('quantity');
    }

    public function checkBeforeChange()
    {
        if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $this->change_type = self::CHANGE_TYPE_QUANTITY;
        }

        if (!$this->change_type) {
            LoggerMEP::getInstance()->error($this->l('Please, set option "Management quantity in"'));
        }

        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && !Validate::isLoadedObject($this->warehouse)
            && $this->change_type == 'warehouse') {
            LoggerMEP::getInstance()->error($this->l('Selected warehouse not found!'));
        }

        if ($this->checkAccessField('available_date') && !Validate::isDateFormat($this->available_date)) {
            LoggerMEP::getInstance()->error($this->l('Available date invalid!'));
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function applyChangeForCombinations($products)
    {
        $return_combinations = array();
        $id_shop = null;
        foreach ($products as $id_product => $combinations) {
            foreach ($combinations as $id_pa) {
                if (!$this->check_access_field_quantity) {
                    continue;
                }

                if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') || $this->change_type == 'quantity') {
                    $return_combinations[$id_pa] = MassEditTools::setQuantity(
                        (int)$id_product,
                        $id_pa,
                        $this->quantity,
                        $this->action_quantity,
                        $id_shop
                    );
                } else {
                    if (Warehouse::getProductLocation($id_product, $id_pa, $this->warehouse->id) === false) {
                        Warehouse::setProductLocation($id_product, $id_pa, $this->warehouse->id, '');
                    }

                    if ($this->action_warehouse) {
                        $this->stock_manager->addProduct(
                            (int)$id_product,
                            $id_pa,
                            $this->warehouse,
                            $this->quantity,
                            0,
                            Product::getPriceStatic(
                                (int)$id_product,
                                false,
                                $id_pa,
                                6,
                                null,
                                false,
                                false
                            )
                        );
                    } else {
                        $quantity_warehouse = $this->stock_manager->getProductRealQuantities(
                            (int)$id_product,
                            $id_pa,
                            $this->warehouse->id
                        );
                        $quantity = min($quantity_warehouse, $this->quantity);
                        $this->stock_manager->removeProduct(
                            (int)$id_product,
                            $id_pa,
                            $this->warehouse,
                            $quantity,
                            0
                        );
                    }

                        StockAvailable::synchronize((int)$id_product);
                        $return_combinations[$id_pa] = Product::getQuantity((int)$id_product, $id_pa);
                }
            }
        }

        return array(
            'combinations' => $return_combinations,
        );
    }

    public function applyChangeForProducts($products)
    {
        $return_products = array();
        foreach ($products as $id_product) {
            if (count(MassEditTools::getShopIds())) {
                foreach (MassEditTools::getShopIds() as $id_shop) {
                    if (!$this->check_access_field_quantity) {
                        continue;
                    }

                    if (!Product::usesAdvancedStockManagement((int)$id_product)
                        && $this->change_type == self::CHANGE_TYPE_QUANTITY) {
                        $return_products[(int)$id_product] = MassEditTools::setQuantity(
                            (int)$id_product,
                            0,
                            $this->quantity,
                            $this->action_quantity,
                            $id_shop
                        );
                    }

                    if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')
                        && Product::usesAdvancedStockManagement((int)$id_product)
                        && $this->change_type == self::CHANGE_TYPE_WAREHOUSE) {
                        if (Warehouse::getProductLocation($id_product, 0, $this->warehouse->id) === false) {
                            Warehouse::setProductLocation($id_product, 0, $this->warehouse->id, '');
                        }

                        if ($this->action_warehouse) {
                            $this->stock_manager->addProduct(
                                (int)$id_product,
                                0,
                                $this->warehouse,
                                $this->quantity,
                                0,
                                Product::getPriceStatic(
                                    (int)$id_product,
                                    false,
                                    null,
                                    6,
                                    null,
                                    false,
                                    false
                                )
                            );
                        } else {
                            $quantity_warehouse = $this->stock_manager->getProductRealQuantities(
                                (int)$id_product,
                                0,
                                $this->warehouse->id
                            );
                            $quantity = min($quantity_warehouse, $this->quantity);

                            $this->stock_manager->removeProduct(
                                (int)$id_product,
                                0,
                                $this->warehouse,
                                $quantity,
                                0
                            );
                        }

                        StockAvailable::synchronize((int)$id_product);
                        $return_products[(int)$id_product] = Product::getQuantity((int)$id_product);
                    }
                }
            }
        }

        return array(
            'products' => $return_products
        );
    }

    public function applyChangeBoth($products, $combinations)
    {
        foreach ($products as $id_product) {
            $data_for_update = array();
            $data_for_update2 = array();
//            if ($this->checkAccessField('available_now')) {
//                $data_for_update['available_now'] = addslashes(pSQL($this->available_now));
//            }
//            if ($this->checkAccessField('available_later')) {
//                $data_for_update['available_later'] = addslashes(pSQL($this->available_later));
//            }

            if ($this->checkAccessField('available_date')) {
                if ($this->change_available_date) {
                    if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
                        Db::getInstance()->update(
                            'product_attribute',
                            array('available_date' => $this->available_date),
                            ' id_product = ' . (int)$id_product
                        );
                    }

                    Db::getInstance()->update(
                        'product_attribute_shop',
                        array('available_date' => $this->available_date),
                        ' id_product = ' . (int)$id_product
                        . (Shop::isFeatureActive() && $this->sql_shop ? ' AND id_shop ' . $this->sql_shop : '')
                    );
                } else {
                    HelperDbMEP::updateObjectFieldByClass(
                        'Product',
                        'available_date',
                        (int)$id_product,
                        $this->available_date
                    );
                }
            }

            if ($this->checkAccessField('out_of_stock')) {
                StockAvailable::setProductOutOfStock((int)$id_product, $this->out_of_stock);
            }

            if ($this->checkAccessField('available_now')) {
                $data_for_update['available_now'] = addslashes(pSQL($this->available_now));
            }
            if ($this->checkAccessField('available_later')) {
                $data_for_update2['available_later'] = addslashes(pSQL($this->available_later));
                Db::getInstance()->update(
                    'product_lang',
                    $data_for_update2,
                    ' id_product = ' . (int)$id_product
                    . ($this->language2 ? ' AND id_lang = ' . (int)$this->language2 : '')
                    . ' ' . (Shop::isFeatureActive() && $this->sql_shop ? ' AND id_shop ' . $this->sql_shop : '')
                );
            }

            if (count($data_for_update)) {
                Db::getInstance()->update(
                    'product_lang',
                    $data_for_update,
                    ' id_product = ' . (int)$id_product
                    . ($this->language ? ' AND id_lang = ' . (int)$this->language : '')
                    . ' ' . (Shop::isFeatureActive() && $this->sql_shop ? ' AND id_shop ' . $this->sql_shop : '')
                );
            }
        }

        if ($this->checkAccessField('minimal_quantity')) {
            if ($this->checkOptionForCombination()) {
                $table = 'product_attribute_shop';
                $field = '`id_product_attribute`';
                $ids = $combinations;
            } else {
                $table = 'product_shop';
                $field = '`id_product`';
                $ids = $products;
            }
            foreach ($ids as $id) {
                Db::getInstance()->update(
                    $table,
                    array(
                        'minimal_quantity' => MassEditTools::getMinimalQuantityForUpdate(
                            $id,
                            $this->minimal_quantity,
                            $table,
                            $this->action_quantity
                        ),
                    ),
                    $field . ' = ' . (int)$id
                );
            }
        }
    }

    public function checkOptionForCombination()
    {
        $change_for = (int)Tools::getValue('change_for');
        return $change_for == self::CHANGE_FOR_COMBINATION;
    }

    public function getTitle()
    {
        return $this->l('Quantity');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $variables['advanced_stock_management'] = (int)Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');
        $variables['warehouses'] = Warehouse::getWarehouses();
        $variables['pack_stock_type'] = Configuration::get('PS_PACK_STOCK_TYPE');
        $variables['token_preferences'] = Tools::getAdminTokenLite('AdminPPreferences');

        return $variables;
    }
}
