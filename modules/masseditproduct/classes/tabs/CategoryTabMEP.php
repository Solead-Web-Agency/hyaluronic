<?php
/**
 * 2007-2020 PrestaShop
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
 * @copyright 2012-2019 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class CategoryTabMEP extends BaseTabMEP
{
    const ACTION_WITH_CATEGORY_ADD = 0;
    const ACTION_WITH_CATEGORY_DELETE = 1;

    public $action_with_category;
    public $category_default;
    public $remove_old_categories;
    public $categories;
    public $object_category_default;

    public function __construct()
    {
        parent::__construct();
        $category = Tools::getValue('category');
        $this->action_with_category = (int)Tools::getValue('action_with_category', 0);
        $this->category_default = (int)Tools::getValue('id_category_default');
        $this->remove_old_categories = (int)Tools::getValue('remove_old_categories');
        $this->categories = (
            is_array($category) && count($category)
            ? array_map('intval', $category) : array()
        );
        $this->object_category_default = new Category(
            $this->category_default,
            $this->context->language->id
        );
    }

    public function applyChangeBoth($products, $combinations)
    {
    }

    public function applyChangeForProducts($products)
    {
        $return_products = array();

        if ($this->action_with_category === self::ACTION_WITH_CATEGORY_ADD) {
            if ($this->remove_old_categories && $this->action_with_category === self::ACTION_WITH_CATEGORY_ADD) {
                Db::getInstance()->delete(
                    'category_product',
                    ' id_product IN('.pSQL(implode(',', $products)).')'
                );
            }

            $category_product_data = array();
            foreach ($this->categories as $cat) {
                $sql = 'SELECT MAX(`position`) FROM `'._DB_PREFIX_.'category_product` WHERE `id_category` = '.(int)$cat;
                $max_position = Db::getInstance()->getValue($sql);
                foreach ($products as $key => $id_product) {
                    $position = $max_position + $key + 1;
                    $category_product_data[] = array(
                        'id_product' => (int)$id_product,
                        'id_category' => (int)$cat,
                        'position' => $position
                    );
                }
            }

            if ($this->category_default) {
                Db::getInstance()->update(
                    'product',
                    array('id_category_default' => (int)$this->category_default),
                    ' id_product IN('.pSQL(implode(',', $products)).')'
                );

                Db::getInstance()->update(
                    'product_shop',
                    array(
                        'id_category_default' => (int)$this->category_default,
                    ),
                    ' id_product IN('.pSQL(implode(',', $products)).')'
                    .(Shop::isFeatureActive() && $this->sql_shop ? ' AND id_shop '.pSQL($this->sql_shop) : '')
                );

                $key = isset($key) ? $key : 0;
                foreach ($products as $id_product) {
                    $category_product_data[] = array(
                        'id_product' => (int)$id_product,
                        'id_category' => (int)$this->category_default,
                        'position' => $key++
                    );
                }
            }

            Db::getInstance()->insert(
                'category_product',
                $category_product_data,
                false,
                true,
                Db::INSERT_IGNORE
            );

            if ($this->category_default) {
                foreach ($products as $product) {
                    $return_products[$product['id']] = $this->object_category_default->name;
                }
            }
        }

        if ($this->action_with_category === self::ACTION_WITH_CATEGORY_DELETE) {
            if (count($products) && count($this->categories)) {
                foreach ($products as $id_product) {
                    Db::getInstance()->execute(
                        'DELETE FROM `'._DB_PREFIX_.'category_product` WHERE  `id_product` = '.(int)$id_product.'
						AND `id_category` IN('.implode(',', array_map('intval', $this->categories)).')
						AND NOT (SELECT COUNT(ps.`id_category_default`)
						FROM `'._DB_PREFIX_.'product_shop` ps WHERE
						ps.`id_shop`  = 1
						 AND ps.`id_category_default` = `'._DB_PREFIX_.'category_product`.`id_category`
						AND ps.`id_product` = `'._DB_PREFIX_.'category_product`.`id_product`)'
                    );
                }
            }
        }

        foreach ($this->categories as $id_category) {
            $sql = 'SELECT `id_product`, `position` FROM `'._DB_PREFIX_.'category_product` 
            WHERE `id_category` = '.(int)$id_category.' ORDER BY `position` ASC';

            $sort_list = array();
            foreach (Db::getInstance()->executeS($sql) as $row) {
                $sort_list[$row['id_product']] = $row['position'];
            }
            $this->sortProductIdList($sort_list, array('filter_category' => $id_category));
        }

        return array(
            'products' => $return_products,
        );
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function checkBeforeChange()
    {
        if ($this->action_with_category === self::ACTION_WITH_CATEGORY_ADD) {
            if ($this->remove_old_categories && !$this->category_default) {
                LoggerMEP::getInstance()->error($this->l('Please select category default on!'));
            }

            if ($this->category_default) {
                if (!Validate::isLoadedObject($this->object_category_default)) {
                    LoggerMEP::getInstance()->error($this->l('Category default not exists'));
                }
            }

            if (LoggerMEP::getInstance()->hasError()) {
                return false;
            }
        }

        return true;
    }

    public function getTitle()
    {
        return $this->l('Category');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $variables['categories'] = Category::getCategories($this->context->language->id, false);
        $variables['simple_categories'] = Category::getSimpleCategories($this->context->language->id);
        return $variables;
    }

    public function sortProductIdList(array $productList, $filterParams)
    {
        if (!isset($filterParams['filter_category'])) {
            throw new \Exception('Cannot sort when filterParams does not contains \'filter_category\'.', 5010);
        }
        foreach ($filterParams as $k => $v) {
            if ($v == '' || strpos($k, 'filter_') !== 0) {
                continue;
            }
            if ($k == 'filter_category') {
                continue;
            }
            throw new \Exception('Cannot sort when filterParams contains other filter than \'filter_category\'.', 5010);
        }

        $categoryId = $filterParams['filter_category'];

        $maxPosition = max(array_values($productList));
        $sortedPositions = array_values($productList);
        sort($sortedPositions); // new positions to update

        // avoid '0', starts with '1', so shift right (+1)
        if ($sortedPositions[1] === 0) {
            foreach ($sortedPositions as $k => $v) {
                $sortedPositions[$k] = $v + 1;
            }
        }

        // combine old positions with new position in an array
        $combinedOldNewPositions = array_combine(array_values($productList), $sortedPositions);
        ksort($combinedOldNewPositions); // (keys: old positions starting at '1', values: new positions)
        $positionsMatcher = array_replace(array_pad(array(), $maxPosition, 0), $combinedOldNewPositions);
        // pad holes with 0
        array_shift($positionsMatcher);// shift because [0] is not used in MySQL FIELD()
        $fields = implode(',', $positionsMatcher);

        // update current pages.
        $updatePositions = 'UPDATE `'._DB_PREFIX_.'category_product` cp
            INNER JOIN `'._DB_PREFIX_.'product` p ON (cp.`id_product` = p.`id_product`)
            '.Shop::addSqlAssociation('product', 'p').'
            SET cp.`position` = ELT(cp.`position`, '.$fields.'),
                p.`date_upd` = "'.date('Y-m-d H:i:s').'",
                product_shop.`date_upd` = "'.date('Y-m-d H:i:s').'"
            WHERE cp.`id_category` = '.(int)$categoryId.' AND cp.`id_product` 
            IN ('.implode(',', array_map('intval', array_keys($productList))).')';

        Db::getInstance()->execute($updatePositions);

        // Fixes duplicates on all pages
        Db::getInstance()->query('SET @i := 0');
        $selectPositions = 'UPDATE`'._DB_PREFIX_.'category_product` cp
            SET cp.`position` = (SELECT @i := @i + 1)
            WHERE cp.`id_category` = '.(int)$categoryId.'
            ORDER BY cp.`id_product` 
            NOT IN ('.implode(',', array_map('intval', array_keys($productList))).'), cp.`position` ASC';
        Db::getInstance()->execute($selectPositions);

        return true;
    }
}
