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

abstract class BaseTabMEP
{
    const CHANGE_FOR_PRODUCT = 0;
    const CHANGE_FOR_COMBINATION = 1;

    public $ids_shop = null;
    public $sql_shop = null;
    public $context;

    protected $combinations;

    protected $products;
    protected $errors = array();

    public function __construct()
    {
        $this->ids_shop = MassEditTools::getShopIds();
        $this->sql_shop = MassEditTools::getSqlShop();
        $this->context = Context::getContext();
    }

    protected function handleRequest()
    {
        $this->combinations = $this->getCombinationsFromRequest();
        $this->products = $this->getProductsFromRequest();
    }

    public static $positions = array(
        CategoryTabMEP::class => '1',
        PriceTabMEP::class => '2',
        ActiveTabMEP::class => '4',
        ManufacturerTabMEP::class => '5',
        AccessoriesTabMEP::class => '6',
        SupplierTabMEP::class => '7',
        DiscountTabMEP::class => '8',
        FeaturesTabMEP::class => '9',
        DeliveryTabMEP::class => '10',
        ImageTabMEP::class => '11',
        DescriptionTabMEP::class => '12',
        RuleCombinationTabMEP::class => '13',
        AttachmentTabMEP::class => '14',
        AdvancedStockManagementTabMEP::class => '15',
        QuantityTabMEP::class => '16',
        MetaTabMEP::class => '17',
        ReferenceTabMEP::class => '18',
        CreateproductsTabMEP::class => '19',
        CustomizationTabMEP::class => '20',
//        CarrierTabMEP::class => '21',

    );

    public $result = array();

    public function apply()
    {
        $this->result = array();
        $t = TransModMEP::getInstance();
        $this->handleRequest();
        if ($this->checkOptionForCombination()) {
            if (count($this->combinations) && $this->checkBeforeChange()) {
                $this->result = $this->applyChangeForCombinations($this->combinations);
                $this->applyChangeBoth($this->products, $this->getCombinationsIdsFromRequest());
                foreach (array_keys($this->combinations) as $id) {
                    $this->addToReIndexSearch((int)$id);
                }
                $this->updateDateUpdProducts(array_keys($this->combinations));
            } else {
                LoggerMEP::getInstance()->error($t->l('No combinations', __FILE__));
            }
        } else {
            if (count($this->products)) {
                if ($this->checkBeforeChange()) {
                    $this->result = $this->applyChangeForProducts($this->products);
                    $this->applyChangeBoth($this->products, $this->getCombinationsIdsFromRequest());
                    foreach ($this->products as $id) {
                        $this->addToReIndexSearch((int)$id);
                    }
                    $this->updateDateUpdProducts($this->products);
                }
            } else {
                LoggerMEP::getInstance()->error($t->l('No products', __FILE__));
            }
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return array();
        } else {
            $this->reindexSearch();
            return $this->result;
        }
    }

    abstract public function applyChangeForProducts($products);

    abstract public function applyChangeForCombinations($products);

    abstract public function applyChangeBoth($products, $combinations);

    abstract public function getTitle();

    public function assignVariables()
    {
        $variable_features = Feature::getFeatures($this->context->language->id);
        foreach ($variable_features as &$variable_feature) {
            $variable_feature['values'] = FeatureValue::getFeatureValuesWithLang(
                $this->context->language->id,
                (int)$variable_feature['id_feature'],
                true
            );
        }

        $variables = array(
            'languages' => ToolsModuleMEP::getLanguages(false),
            'default_form_language' => $this->context->language->id,
            'variables' => array(
                'currency' => $this->context->currency,
                'static' => array(
                    '{name}' => $this->l('name product'),
                    '{price}' => $this->l('price final'),
                    '{manufacturer}' => $this->l('manufacturer'),
                    '{category}' => $this->l('default category'),
                    '{reference}' => $this->l('product reference'),
                ),
                'features' => $variable_features,
            ),
            'tab_name' => ToolsModuleMEP::toCamelCase($this->getTabName(), true)
        );
        return $variables;
    }

    public function renderTabForm()
    {
        SmartyMEP::registerSmartyFunctions();
        return ToolsModuleMEP::fetchTemplate(
            'admin/mass_edit_product/helpers/form/tabs/' . $this->getTabName() . '.tpl',
            $this->assignVariables()
        );
    }

    public function checkAvailable()
    {
        return true;
    }

    public function checkBeforeChange()
    {
        return true;
    }

    public function checkOptionForCombination()
    {
        return false;
    }

    protected function getCombinationsFromRequest()
    {
        $combinations = Tools::getValue('combinations');
        $tmp_combinations = array();
        if (is_array($combinations) && count($combinations)) {
            foreach ($combinations as $combination) {
                $combination = explode('_', $combination);
                if (!array_key_exists((int)$combination[0], $tmp_combinations)) {
                    $tmp_combinations[(int)$combination[0]] = array();
                }
                $tmp_combinations[(int)$combination[0]][] = (int)$combination[1];
            }
        }
        $combinations = $tmp_combinations;

        return $combinations;
    }

    public function getCombinationsIdsFromRequest()
    {
        $products = $this->getCombinationsFromRequest();
        $combinations = array();
        foreach ($products as $c) {
            $combinations = array_merge($combinations, $c);
        }
        return $combinations;
    }

    protected function getProductsFromRequest()
    {
        $products = Tools::getValue('products');
        $ids_product = array();
        if (is_array($products) && count($products)) {
            foreach ($products as $product) {
                $ids_product[] = (int)$product['id'];
            }
        }

        return $ids_product;
    }

    public static $disabled = null;
    public static $enabled = null;

    public function cacheDisabled()
    {
        if (is_null(self::$disabled)) {
            $disabled = Tools::getValue('disabled');
            self::$disabled = (is_array($disabled) && count($disabled) ? $disabled : array());
        }
    }

    // fix enabled TODO
    public function cacheEnabled()
    {
        if (is_null(self::$enabled)) {
            $enabled = Tools::getValue('enabled_feature_fix');
            self::$enabled = (is_array($enabled) && count($enabled) ? $enabled : array());
        }
    }

    public function checkAccessField($field)
    {
        $this->cacheDisabled();

        if (in_array($field, self::$disabled)) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getEnabledFeatures()
    {
        $this->cacheEnabled();

        return self::$enabled;
    }

    public $trigger_update_date_upd = false;

    public function updateDateUpdProducts($ids, $date_upd = null)
    {
        if (!Tools::getValue('change_date_upd')) {
            return false;
        }
        if ($this->trigger_update_date_upd) {
            return false;
        }

        if (is_null($date_upd)) {
            $date_upd = date('Y-m-d H:i:s');
        }

        Db::getInstance()->update(
            'product',
            array('date_upd' => $date_upd),
            ' id_product IN(' . pSQL(implode(',', array_map('intval', $ids))) . ')'
        );

        Db::getInstance()->update(
            'product_shop',
            array(
                'date_upd' => $date_upd,
            ),
            ' id_product IN(' . pSQL(implode(',', array_map('intval', $ids))) . ')'
            . (Shop::isFeatureActive() && $this->sql_shop ? ' AND id_shop ' . pSQL($this->sql_shop) : '')
        );
        $this->trigger_update_date_upd = true;
    }

    public function updateDateUpdProduct($id, $date_upd = null)
    {
        if (!Tools::getValue('change_date_upd')) {
            return false;
        }
        if (is_null($date_upd)) {
            $date_upd = date('Y-m-d H:i:s');
        }
        MassEditTools::updateObjectField('Product', 'date_upd', $id, $date_upd);
    }

    public $reindex_products = array();

    public function addToReIndexSearch($ids_product)
    {
        if ((int)Tools::getValue('reindex_products')) {
            if (is_array($ids_product) && count($ids_product)) {
                $this->reindex_products = array_merge($this->reindex_products, $ids_product);
            } else {
                $this->reindex_products[] = $ids_product;
            }

            $this->reindex_products = array_unique($this->reindex_products);
        }
    }

    public function reindexSearch()
    {
        if ((int)Tools::getValue('reindex_products')) {
            $this->reindexProducts($this->reindex_products);
        }
        if (is_array($this->reindex_products) && count($this->reindex_products)) {
            SpecificPriceRule::applyAllRules($this->reindex_products);
        }
    }

    /**
     * @param $ids_product
     * @throws PrestaShopException
     */
    public function reindexProducts($ids_product)
    {
        if (is_array($ids_product) && count($ids_product)) {
            foreach ($ids_product as $id_product) {
                Search::indexation(false, (int)$id_product);
                Hook::exec('actionIndexProduct', array('product' => $id_product));
            }
        }
    }

    public function getIntvalArrayRequest($name)
    {
        $var = Tools::getValue($name);
        if (!is_array($var)) {
            return false;
        }
        foreach ($var as &$item) {
            $item = (int)$item;
        }
        return $var;
    }

    public function getAttributes()
    {
        return array();
    }

    public function getPosition()
    {
        $class_name = get_class($this);
        return (isset(self::$positions[$class_name]) ? self::$positions[$class_name] : 90000);
    }

    public function getTabName()
    {
        $class_name = get_class($this);
        $tab = str_replace('TabMEP', '', $class_name);
        return Tools::toUnderscoreCase($tab);
    }

    public function l($string)
    {
        return TransModMEP::getInstance()->l($string, Tools::strtolower(get_class($this)));
    }
}
