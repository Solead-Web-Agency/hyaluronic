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

class DescriptionTabMEP extends BaseTabMEP
{
    public function applyChangeBoth($products, $combinations)
    {
    }

    public function applyChangeForProducts($products)
    {
        $description = Tools::getValue('description');
        $description_short = Tools::getValue('description_short');
        $language = (int)Tools::getValue('language');
        $product_name = Tools::getValue('product_name');
        $location_description_short = Tools::getValue('location_description_short');
        $location_description = Tools::getValue('location_description');

        foreach ($products as $id_product) {
            if (!$language) {
                $languages = Language::getLanguages(true);
            } else {
                $languages = array(array('id_lang' => $language));
            }

            foreach ($languages as $lang) {
                $data_for_update = array();
                $product = new Product($id_product);

                $product->description[$lang['id_lang']] = $product->description[$lang['id_lang']];
                $product->description_short[$lang['id_lang']] = $product->description_short[$lang['id_lang']];

                if ($this->checkAccessField('description')) {
                    $this->addToReIndexSearch((int)$id_product);
                    $description_update = MassEditTools::renderMetaTag(
                        $description,
                        (int)$id_product,
                        $lang['id_lang']
                    );

                    switch ($location_description) {
                        case 1:
                            $data_for_update['description']=$description_update.$product->description[$lang['id_lang']];
                            break;
                        case 2:
                            $data_for_update['description']=$product->description[$lang['id_lang']].$description_update;
                            break;
                        default:
                            $data_for_update['description'] = $description_update;
                    }
                  //  $data_for_update['description'] = str_replace("'","`", $data_for_update['description']);
                }
                if ($this->checkAccessField('description_short')) {
                    $this->addToReIndexSearch((int)$id_product);
                    $description_short_update = MassEditTools::renderMetaTag(
                        $description_short,
                        (int)$id_product,
                        $lang['id_lang']
                    );

                    switch ($location_description_short) {
                        case 1:
                            $data_for_update['description_short'] =
                                $description_short_update.$product->description_short[$lang['id_lang']];
                            break;
                        case 2:
                            $data_for_update['description_short'] =
                                $product->description_short[$lang['id_lang']].$description_short_update;
                            break;
                        default:
                            $data_for_update['description_short'] = $description_short_update;
                    }
                }

                if ($this->checkAccessField('product_name')) {
                    $this->addToReIndexSearch((int)$id_product);
                    $product_name_update = MassEditTools::renderMetaTag(
                        $product_name,
                        (int)$id_product,
                        $lang['id_lang']
                    );
                    $data_for_update['name'] = addslashes($product_name_update);
                }

                if (count($data_for_update)) {
                    Db::getInstance()->update(
                        'product_lang',
                        $data_for_update,
                        ' id_product = '.(int)$id_product
                        .($lang['id_lang'] ? ' AND id_lang = '.(int)$lang['id_lang'] : '')
                        .' '.(Shop::isFeatureActive() && $this->sql_shop ? ' AND id_shop '.$this->sql_shop : '')
                    );
                }
            }
        }

        return array();
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function getTitle()
    {
        return $this->l('Description');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $variables['static_for_name'] = array(
            '{title}' => $this->l('title'),
            '{price}' => $this->l('price final'),
            '{manufacturer}' => $this->l('manufacturer'),
            '{category}' => $this->l('default category')
        );
        return $variables;
    }
}
