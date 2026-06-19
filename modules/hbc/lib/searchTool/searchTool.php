<?PHP
/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA PL MILOSZ MYSZCZUK VATEU: PL9730945634
 * @copyright 2010-2019 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * Search Tool
 * version 1.3.0
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */

class searchToolhbc extends hbc
{
    public $addon;
    public $name;
    public $tab;
    public $context;

    public static function jsonEncode($data, $options = 0, $depth = 512)
    {
        return json_encode($data, $options, $depth);
    }

    public function __construct($addon = null, $tab = null)
    {
        $this->tab = $tab;
        $this->addon = $addon;
        $this->name = $addon;
        $this->context = Context::getContext();
        if (Tools::getValue('searchType', 'false') != 'false' && Tools::getValue('ajax') == 1) {
            if (Tools::getValue('searchType') == 'manufacturer') {
                echo self::jsonencode($this->searchForID('manufacturer', 'name', trim(Tools::getValue('q')), false));
                die();
            } elseif (Tools::getValue('searchType') == 'product') {
                echo self::jsonencode($this->searchForID('product_lang', 'name', trim(Tools::getValue('q')), true));
                die();
            } elseif (Tools::getValue('searchType') == 'category') {
                echo self::jsonencode($this->searchForID('category_lang', 'name', trim(Tools::getValue('q')), true));
                die();
            } elseif (Tools::getValue('searchType') == 'supplier') {
                echo self::jsonencode($this->searchForID('supplier', 'name', trim(Tools::getValue('q')), false));
                die();
            } elseif (Tools::getValue('searchType') == 'cms_category') {
                echo self::jsonencode($this->searchForID('cms_category_lang', 'name', trim(Tools::getValue('q')), true));
                die();
            } elseif (Tools::getValue('searchType') == 'cms') {
                echo self::jsonencode($this->searchForID('cms_lang', 'meta_title', trim(Tools::getValue('q')), true));
                die();
            } elseif (Tools::getValue('searchType') == 'customer') {
                echo self::jsonencode($this->searchForID('customer', array('email', 'firstname', 'lastname'), trim(Tools::getValue('q')), true));
                die();
            } elseif (Tools::getValue('searchType') == 'group') {
                echo self::jsonencode($this->searchForID('group_lang', 'name', trim(Tools::getValue('q')), false));
                die();
            } elseif (Tools::getValue('searchType') == 'feature_value') {
                $result = $this->searchForID('feature_value_lang', 'value', trim(Tools::getValue('q')), false);
                if (is_array($result)) {
                    if (count($result) > 0) {
                        foreach ($result AS $k => $v) {
                            $fv = new FeatureValue($v['id_feature_value'], Context::getContext()->language->id);
                            $f = new Feature($fv->id_feature, Context::getContext()->language->id);
                            if ($f != false) {
                                $result[$k]['feature_name'] = $f->name;
                                $result[$k]['value'] = $result[$k]['value'] . $this->productsFound('feature', $v['id_feature_value']);
                            }
                        }
                    }
                }
                echo self::jsonencode($result);
                die();
            } elseif (Tools::getValue('searchType') == 'attribute_value') {
                $result = $this->searchForID('attribute_lang', 'name', trim(Tools::getValue('q')), false);
                if (is_array($result)) {
                    if (count($result) > 0) {
                        foreach ($result AS $k => $v) {
                            $attr = new Attribute($v['id_attribute'], Context::getContext()->language->id);
                            $attrg = new AttributeGroup($attr->id_attribute_group, Context::getContext()->language->id);
                            if ($attrg != false) {
                                $result[$k]['attribute_name'] = $attrg->public_name;
                                $result[$k]['name'] = $result[$k]['name'] . (Tools::getValue('showCounter') == true ? $this->productsFound('attribute', $v['id_attribute']) : '');
                            }
                        }
                    }
                }
                echo self::jsonencode($result);
                die();
            }
        } elseif (Tools::getValue('getCombinations') == 1 && Tools::getValue('searchByID', 'false') != 'false' && Tools::getValue('ajax') == 1) {
            echo $this->returnCombinations(Tools::getValue('searchByID'), Tools::getValue('combinationsClass'));
        }
    }

    public function initTool()
    {
        $this->context->smarty->assign('SearchToolLink', $this->context->link->getAdminLink('AdminModules', false) . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&ajax=1&module_name=' . $this->name);
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->addon . '/lib/searchTool/views/scripts.tpl');
    }

    public function productsFound($type, $id)
    {
        if ($type == 'attribute') {
            $result = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pac INNER JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas ON (pas.id_product_attribute = pac.id_product_attribute) WHERE pac.id_attribute="' . $id . '" GROUP BY pas.id_product');
            if (isset($result[0]['id_product'])) {
                return ' | ' . $this->l('Products found:') . ' ' . count($result);
            } else {
                return '';
            }
        }

        if ($type == 'feature') {
            $result = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'feature_product` fp WHERE fp.id_feature_value="'.$id.'" ');
            if (isset($result[0]['id_feature_value'])) {
                return ' | ' . $this->l('Products found:') . ' ' . count($result);
            } else {
                return '';
            }
        }
    }

    public function searchTool($type, $resultInput, $replacementType = 'replace', $returnBox = false, $object = false, $combinations = false, $combination_input = '', $selected_combination = false, $showCounter = false)
    {
        $array = array();
        if ($returnBox == true) {
            if ($object != false) {
                $objectClass = ucfirst($type);
                if ($objectClass == 'Cms_category') {
                    $objectClass = 'CMSCategory';
                } elseif ($objectClass == 'Cms') {
                    $objectClass = 'CMS';
                } elseif ($objectClass == 'Feature_value') {
                    $objectClass = 'FeatureValue';
                }
                if (class_exists($objectClass)) {
                    $object_exploded = explode(',', $object);
                    foreach ($object_exploded AS $object_item) {
                        if ($type == 'product') {
                            $object_to_display = new $objectClass($object_item, false, $this->context->language->id);
                        } elseif ($type == 'feature_value') {
                            $object_to_display = new FeatureValue($object_item, $this->context->language->id);
                            $f = new Feature($object_to_display->id_feature, $this->context->language->id);
                            $object_to_display->value = $f->name . ': ' . $object_to_display->value;
                        } elseif ($type == 'attribute_value') {
                            $object_to_display = new Attribute($object_item, $this->context->language->id);
                            $ag = new Feature($object_to_display->id_attribute_group, $this->context->language->id);
                            $object_to_display->value = $ag->public_name . ': ' . $object_to_display->name;
                        } else {
                            $object_to_display = new $objectClass($object_item, $this->context->language->id);
                        }
                        $array[] = '<div class="' . $type . $resultInput . $object_item . '"><span class="btn btn-default" onclick="SearchToolRemoveItem(\'' . $type . $resultInput . $object_item . '\',\'' . $type . '\',\'' . $resultInput . '\',\'' . $object_item . '\');"><i class="icon-remove"></i></span> #' . $object_to_display->id . ' ' . (isset($object_to_display->firstname) ? $object_to_display->firstname . ' ' . $object_to_display->lastname . ' ' . $object_to_display->email : (isset($object_to_display->name) ? $object_to_display->name : (isset($object_to_display->meta_title) ? $object_to_display->meta_title : (isset($object_to_display->value) ? $object_to_display->value : '')))) . '</div>';
                    }
                }
            }
            return '<div class="' . $resultInput . '_' . $type . 'sBox">' . implode('', $array) . '</div>' . ($type == 'product' ? (isset($object_to_display->id) ? (($combinations == true) ? (($selected_combination != false) ? $this->returnCombinations($object_to_display->id, $combination_input, $selected_combination) : '') : '') : '') : '');
        }
        return '<input style="width: 80px; font-size: 10px; margin: 0px; height: 17px;" type="text" placeholder="' . $this->l('Search') . '" id="searchTool_' . $type . '" data-replacementtype="' . $replacementType . '" data-resultinput="' . $resultInput . '" data-type="' . $type . '" data-toggle="tooltip" data-showCounter="' . (bool)$showCounter . '" data-original-title="' . $this->l('Search for name of: ') . $type . '" class="label-tooltip searchToolInput searchTool_' . $type . '"/>';
    }

    public function searchForID($table, $field, $term, $shop = false)
    {
        $result = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . $table . '` WHERE ' . (is_array($field) ? $this->returnArrayFields($field, $term) : ($field . " LIKE '%" . psql($term) . "%' ")) . ($shop != false ? 'AND id_shop="' . $shop . '"' : '') . ' GROUP BY id_' . str_replace('_lang', '', $table));
        return $result;
    }

    public function returnArrayFields($field, $term)
    {
        $return = array();
        foreach ($field AS $f) {
            $return[] = $f . " LIKE '%" . psql($term) . "%' ";
        }
        return implode("OR ", $return);
    }

    public function returnCombinations($id = 0, $class = 'class', $preselected = false)
    {
        $product = new Product($id, false, $this->context->language->id);
        $combinations = $product->getAttributeCombinations($this->context->language->id);
        $combinations_array = array();

        if (count($combinations) > 0) {
            foreach ($combinations AS $key => $combination) {
                $cb = new Combination($combination['id_product_attribute'], $this->context->language->id);
                $cb_name = $cb->getAttributesName($this->context->language->id);
                $combination_name = '';
                if (count($cb_name) > 0) {
                    foreach ($cb_name AS $cb_name) {
                        $combination_name .= $cb_name['name'] . ' ';
                    }
                }
                $combinations_array[$combination['id_product_attribute']] = "<div class='form-control-static margin-form '><div class='btn btn-default " . $class . "Buttons " . (($preselected != false) ? (($preselected == $combination['id_product_attribute']) ? 'active' : '') : '') . "' onclick=\"$('." . $class . "Buttons').removeClass('active'); $(this).addClass('active'); $('#" . $class . "').val(" . $combination['id_product_attribute'] . ")\" >" . $this->l('select') . "</div> " . $combination_name . " " . ($combination['reference'] != '' ? '(' . $combination['reference'] . ')' : '') . "</div>";
            }
            return '<div class="panel ' . $class . 'selectedCombinations" style="margin-top:20px"><h3>' . $this->l('Select combination') . '</h3>' . implode('', $combinations_array) . '</div>';
        }
    }
}