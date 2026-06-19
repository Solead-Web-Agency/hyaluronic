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

class FeaturesTabMEP extends BaseTabMEP
{
    public function applyChangeBoth($products, $combinations)
    {
    }

    public function applyChangeForProducts($products)
    {
        $error = array();
        $product_obj = new Product(null);
        $disabled = Tools::getValue('disabled');
        $delete_old = Tools::getValue('delete_old');
        $extended_features = Tools::getValue('extendedfeatures', array());
        $features = Feature::getFeatures(Context::getContext()->employee->id_lang);

        $multi_features = Tools::getIsset('form') && ($form = Tools::getValue('form')) && isset($form['features']);
        foreach ($products as $id_product) {
            if ($multi_features) {
                if (Validate::isLoadedObject($product = new Product($id_product))) {
                    if ((bool)$delete_old) {
                        $product->deleteFeatures();
                    }

                    // add new objects
                    $languages = Language::getLanguages(false);
                    $features = isset($form['features']) ? $form['features'] : array();
                    if (is_array($features)) {
                        foreach ($features as $key => $feature) {
                          //  dump($feature);
                            $delete_feature = Tools::getValue('delete_form_features_'.$key);
                            if ($delete_feature == 1 && empty($feature['value']) && !empty($feature['feature'])) {
                                self::deleteFeaturesId($product->id, $feature['feature']);
                            } elseif ($delete_feature == 1 && !empty($feature['value'])) {
                                self::deleteFeaturesvalueId($product->id, $feature['feature'], $feature['value']);
                            } else {
                                if (!empty($feature['value'])) {
                                    $product->addFeaturesToDB($feature['feature'], $feature['value']);
                                } elseif ($defaultValue = $this->checkFeaturesMulti($languages, $feature, $error)) {
                                    $idValue = $product->addFeaturesToDB($feature['feature'], 0, 1);
                                    foreach ($languages as $language) {
                                        $valueToAdd = (isset($feature['custom_value'][$language['id_lang']]))
                                            ? $feature['custom_value'][$language['id_lang']]
                                            : $defaultValue;
                                        $product->addFeaturesCustomToDB(
                                            $idValue,
                                            (int)$language['id_lang'],
                                            $valueToAdd
                                        );
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $error[] = $this->l('A product must be created before adding features.');
                }
                continue;
            }
            $product_obj->id = $id_product;
            $languages = Language::getLanguages(false);
            if (Validate::isLoadedObject($product_obj)) {
                $this->addToReIndexSearch((int)$product_obj->id);
                if (Module::isEnabled('seosaextendedfeatures')) {
                    foreach ($features as $feature) {
                        if ($disabled['feature'][$feature['id_feature']]) {
                            continue;
                        }
                        if ($delete_old['feature'][$feature['id_feature']]) {
                            MassEditTools::deleteFeatures($id_product, array($feature['id_feature']));
                        }

                        $feature_name = str_replace(array('[', ']', '(', ')', ' '), '_', $feature['name']);
                        if (!key_exists($feature_name, $extended_features)
                            && !key_exists($feature['name'], $extended_features)) {
                            continue;
                        }

                        $max_position = Db::getInstance()->getValue(
                            'SELECT MAX(position) FROM `' . _DB_PREFIX_ . 'feature_product` 
                            WHERE `id_feature` = ' . (int)$feature['id_feature']
                        );

                        if ($feature['name'] != $feature_name) {
                            foreach ($extended_features[$feature['name']] as $key => $value) {
                                if ($key == 'default') {
                                    foreach ($value as $k => $val) {
                                        $id_feature_value = str_replace('number:', '', $val);
                                        $position = $max_position + $k;
                                        $this->addFeatureValueToProduct(
                                            $id_product,
                                            $feature['id_feature'],
                                            $id_feature_value,
                                            $position
                                        );
                                    }
                                } elseif ($key == 'custom') {
                                    foreach ($value as $k => $val) {
                                        $feature_value = new FeatureValue();
                                        $feature_value->id_feature = $feature['id_feature'];
                                        $feature_value->custom = 1;
                                        $feature_value->value = array();
                                        foreach ($val as $iso => $v) {
                                            $id_lang = Db::getInstance()->getValue(
                                                'SELECT `id_lang` FROM `' . _DB_PREFIX_ . 'lang`
                                        WHERE `iso_code` = "' . pSQL($iso) . '"'
                                            );
                                            $feature_value->value[(string)$id_lang] = $v;
                                        }
                                        $feature_value->save();
                                        if (!$feature_value->id) {
                                            return false;
                                        }
                                        $position = $max_position + $k;
                                        $this->addFeatureValueToProduct(
                                            $id_product,
                                            $feature['id_feature'],
                                            $feature_value->id,
                                            $position
                                        );
                                    }
                                }
                            }
                            foreach ($extended_features[$feature_name] as $key => $value) {
                                if ($key == 'default') {
                                    foreach ($value as $k => $val) {
                                        $id_feature_value = str_replace('number:', '', $val);
                                        $position = $max_position + $k;
                                        $this->addFeatureValueToProduct(
                                            $id_product,
                                            $feature['id_feature'],
                                            $id_feature_value,
                                            $position
                                        );
                                    }
                                } elseif ($key == 'custom') {
                                    foreach ($value as $k => $val) {
                                        $feature_value = new FeatureValue();
                                        $feature_value->id_feature = $feature['id_feature'];
                                        $feature_value->custom = 1;
                                        $feature_value->value = array();
                                        foreach ($val as $iso => $v) {
                                            $id_lang = Db::getInstance()->getValue(
                                                'SELECT `id_lang` FROM `' . _DB_PREFIX_ . 'lang`
                                        WHERE `iso_code` = "' . pSQL($iso) . '"'
                                            );
                                            $feature_value->value[(string)$id_lang] = $v;
                                        }
                                        $feature_value->save();
                                        if (!$feature_value->id) {
                                            return false;
                                        }
                                        $position = $max_position + $k;
                                        $this->addFeatureValueToProduct(
                                            $id_product,
                                            $feature['id_feature'],
                                            $feature_value->id,
                                            $position
                                        );
                                    }
                                }
                            }
                        } else {
                            foreach ($extended_features[$feature_name] as $key => $value) {
                                if ($key == 'default') {
                                    foreach ($value as $k => $val) {
                                        $id_feature_value = str_replace('number:', '', $val);
                                        $position = $max_position + $k;
                                        $this->addFeatureValueToProduct(
                                            $id_product,
                                            $feature['id_feature'],
                                            $id_feature_value,
                                            $position
                                        );
                                    }
                                } elseif ($key == 'custom') {
                                    foreach ($value as $k => $val) {
                                        $feature_value = new FeatureValue();
                                        $feature_value->id_feature = $feature['id_feature'];
                                        $feature_value->custom = 1;
                                        $feature_value->value = array();
                                        foreach ($val as $iso => $v) {
                                            $id_lang = Db::getInstance()->getValue(
                                                'SELECT `id_lang` FROM `' . _DB_PREFIX_ . 'lang`
                                        WHERE `iso_code` = "' . pSQL($iso) . '"'
                                            );
                                            $feature_value->value[(string)$id_lang] = $v;
                                        }
                                        $feature_value->save();
                                        if (!$feature_value->id) {
                                            return false;
                                        }
                                        $position = $max_position + $k;
                                        $this->addFeatureValueToProduct(
                                            $id_product,
                                            $feature['id_feature'],
                                            $feature_value->id,
                                            $position
                                        );
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($delete_old) {
                        MassEditTools::deleteFeatures($product_obj->id, $this->getEnabledFeatures());
                    }

                    foreach ($_POST as $key => $val) {
                        if (preg_match('/^feature_([0-9]+)_value/i', $key, $match)) {
                            if (!in_array($match[1], $this->getEnabledFeatures())) {
                                continue;
                            }

                            if ($val) {
                                MassEditTools::deleteFeatures($product_obj->id, $this->getEnabledFeatures());
                                $product_obj->addFeaturesToDB($match[1], $val);
                            } else {
                                if ($default_value = $this->checkFeatures($languages, $match[1], $error)) {
                                    if (!array_key_exists($match[1], $this->check_features)) {
                                        $this->check_features[$match[1]] = $default_value;
                                    }
                                    $id_value = $product_obj->addFeaturesToDB($match[1], 0, 1);
                                    foreach ($languages as $language) {
                                        if ($cust = Tools::getValue(
                                            'custom_' . $match[1] . '_' . (int)$language['id_lang']
                                        )) {
                                            $product_obj->addFeaturesCustomToDB(
                                                $id_value,
                                                (int)$language['id_lang'],
                                                $cust
                                            );
                                        } else {
                                            $product_obj->addFeaturesCustomToDB(
                                                $id_value,
                                                (int)$language['id_lang'],
                                                $default_value
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return array();
    }

    public function applyChangeForCombinations($products)
    {
    }

    /**
     * @param $id_product
     * @param $id_feature
     * @param $id_feature_value
     * @param $max_position
     * @return bool
     */
    private function addFeatureValueToProduct($id_product, $id_feature, $id_feature_value, $position)
    {
        $id_product = (int)$id_product;
        $id_feature = (int)$id_feature;
        $id_feature_value = (int)$id_feature_value;
        $position = (int)$position;

        $data = array(
            'id_product' => $id_product,
            'id_feature' => $id_feature,
            'id_feature_value' => $id_feature_value,
            'position' => $position,
        );

        return Db::getInstance()->insert('feature_product', $data, false, true, Db::INSERT_IGNORE);
    }

    protected $check_features = array();

    protected function checkFeatures($languages, $feature_id, &$errors)
    {
        if (array_key_exists($feature_id, $this->check_features)) {
            return $this->check_features[$feature_id];
        }
        $rules = call_user_func(array('FeatureValue', 'getValidationRules'), 'FeatureValue');
        $feature = Feature::getFeature((int)Configuration::get('PS_LANG_DEFAULT'), $feature_id);
        $val = 0;
        foreach ($languages as $language) {
            if ($val = Tools::getValue('custom_' . $feature_id . '_' . $language['id_lang'])) {
                $current_language = new Language($language['id_lang']);
                if (Tools::strlen($val) > $rules['sizeLang']['value']) {
                    $errors[] = sprintf(
                        $this->l('The name for feature %1$s is too long in %2$s.'),
                        ' <b>' . $feature['name'] . '</b>',
                        $current_language->name
                    );
                } elseif (!call_user_func(array('Validate', $rules['validateLang']['value']), $val)) {
                    $errors[] = sprintf(
                        $this->l('A valid name required for feature. %1$s in %2$s.'),
                        ' <b>' . $feature['name'] . '</b>',
                        $current_language->name
                    );
                }
                if (count($this->errors)) {
                    return 0;
                }
                // Getting default language
                if ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT')) {
                    return $val;
                }
            }
        }

        return 0;
    }

    protected function checkFeaturesMulti($languages, $featureInfo, &$error)
    {
        $rules = call_user_func(array('FeatureValue', 'getValidationRules'), 'FeatureValue');
        $feature = Feature::getFeature((int) Configuration::get('PS_LANG_DEFAULT'), $featureInfo['feature']);
        foreach ($languages as $language) {
            if (isset($featureInfo['custom_value'][$language['id_lang']])) {
                $val = $featureInfo['custom_value'][$language['id_lang']];
                $current_language = new Language($language['id_lang']);
                if (Tools::strlen($val) > $rules['sizeLang']['value']) {
                    $error[] = sprintf(
                        $this->l('The name for feature %1$s is too long in %2$s.'),
                        ' <b>' . $feature['name'] . '</b>',
                        $current_language->name
                    );
                } elseif (!call_user_func(array('Validate', $rules['validateLang']['value']), $val)) {
                    $error[] = sprintf(
                        $this->l('A valid name required for feature. %1$s in %2$s.'),
                        ' <b>' . $feature['name'] . '</b>',
                        $current_language->name
                    );
                }
                if (count($error)) {
                    return 0;
                }
                // Getting default language
                if ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT')) {
                    return $val;
                }
            }
        }
        return 0;
    }

    public function getTitle()
    {
        return $this->l('Features');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
            $kernel = ${'GLOBALS'}['kernel'];
            $container = $kernel->getContainer();
            $product = new Product();
            $modelMapper = $container->get('prestashop.adapter.admin.model.product');
            $formBuilder = $container->get('form.factory')->createBuilder(
                Symfony\Component\Form\Extension\Core\Type\FormType::class,
                $modelMapper->getFormData($product),
                ['allow_extra_fields' => true]
            )
                ->add('features', Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                    'entry_type' => PrestaShopBundle\Form\Admin\Feature\ProductFeature::class,
                    'prototype' => true,
                    'allow_add' => true,
                    'allow_delete' => true,
                ]);

            $variables['form_multi_features'] = $container->get('twig')->render(
                '@PrestaShop/Admin/Product/ProductPage/Forms/form_feature.html.twig',
                [
                'form' => $formBuilder->getForm()->get('features')->createView()->vars['prototype']
                ]
            );
            $text = '<a class="btn tooltip-link delete pl-0 pr-0"><i class="material-icons">delete</i></a>';
            $variables['form_multi_features'] = str_replace($text, '<a class="btn tooltip-link delete pl-0 pr-0">
            <i class="material-icons">delete</i></a>
            <input type="checkbox" name="delete_form_features___name__">Del</input>
            </div></div>', $variables['form_multi_features']);
        }


        $features = MassEditTools::getFeatures($this->context->language->id, true, 1, true);
        $variables['features'] = $features;
        $variables['total_features'] = MassEditTools::getTotalFeatures();
        $variables['count_feature_view'] = MassEditTools::LIMIT_FEATURES;
        $variables['link'] = $this->context->link;
        $variables['feature_tab_html'] = Module::isEnabled('seosaextendedfeatures');
        return $variables;
    }

    public function deleteFeaturesId($id_product, $id_featurer)
    {
        $all_shops = Context::getContext()->shop->getContext() == Shop::CONTEXT_ALL ? true : false;
        // List products features
        $features = Db::getInstance()->executeS(
            '
            SELECT p.*, f.*
            FROM `' . _DB_PREFIX_ . 'feature_product` as p
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_value` as f ON (f.`id_feature_value` = p.`id_feature_value`)
            ' . (!$all_shops ?
                'LEFT JOIN `' . _DB_PREFIX_ . 'feature_shop` fs ON (f.`id_feature` = fs.`id_feature`)' : null) . '
            WHERE `id_product` = ' . (int) $id_product . ' AND `id_feature = ` . $id_featurer . '
            . (!$all_shops ? ' AND fs.`id_shop` = ' . (int) Context::getContext()->shop->id : '')
        );

        foreach ($features as $tab) {
            // Delete product custom features
            if ($tab['custom']) {
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'feature_value` 
                WHERE `id_feature_value` = ' . (int) $tab['id_feature_value']);
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'feature_value_lang` 
                WHERE `id_feature_value` = ' . (int) $tab['id_feature_value']);
            }
        }
        // Delete product features
        Db::getInstance()->execute('
            DELETE `' . _DB_PREFIX_ . 'feature_product` FROM `' . _DB_PREFIX_ . 'feature_product`
            WHERE `id_product` = ' . (int) $id_product . ' AND id_feature = ' . $id_featurer . (!$all_shops ? '
                AND `id_feature` IN (
                    SELECT `id_feature`
                    FROM `' . _DB_PREFIX_ . 'feature_shop`
                    WHERE `id_shop` = ' . (int) Context::getContext()->shop->id . '
                )' : ''));

        SpecificPriceRule::applyAllRules(array((int) $id_product));
    }

    public function deleteFeaturesvalueId($id_product, $id_featurer, $id_value)
    {
        $all_shops = Context::getContext()->shop->getContext() == Shop::CONTEXT_ALL ? true : false;
        // List products features
        $features = Db::getInstance()->executeS(
            '
            SELECT p.*, f.*
            FROM `' . _DB_PREFIX_ . 'feature_product` as p
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_value` as f ON (f.`id_feature_value` = p.`id_feature_value`)
            ' . (!$all_shops ?
                'LEFT JOIN `' . _DB_PREFIX_ . 'feature_shop` fs ON (f.`id_feature` = fs.`id_feature`)' : null) . '
            WHERE `id_product` = ' . (int) $id_product . ' 
            AND  id_feature = ' . $id_featurer .  'AND id_feature_value = ' . $id_value
            . (!$all_shops ? ' AND fs.`id_shop` = ' . (int) Context::getContext()->shop->id : '')
        );

        foreach ($features as $tab) {
            // Delete product custom features
            if ($tab['custom']) {
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'feature_value` 
                WHERE `id_feature_value` = ' . (int) $tab['id_feature_value']);
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'feature_value_lang` 
                WHERE `id_feature_value` = ' . (int) $tab['id_feature_value']);
            }
        }
        // Delete product features
        Db::getInstance()->execute('
            DELETE `' . _DB_PREFIX_ . 'feature_product` FROM `' . _DB_PREFIX_ . 'feature_product`
            WHERE `id_product` = ' . (int) $id_product . ' 
            AND id_feature = ' . $id_featurer . ' AND id_feature_value = ' . $id_value . (!$all_shops ? '
                AND `id_feature` IN (
                    SELECT `id_feature`
                    FROM `' . _DB_PREFIX_ . 'feature_shop`
                    WHERE `id_shop` = ' . (int) Context::getContext()->shop->id . '
                )' : ''));
    }
}
