<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @author    Carts Guru <prestashop@carts.guru>
 * @copyright Since 2017 Carts Guru
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
abstract class CGAbstractSales extends CGAbstractBase
{
    const THIRTY_MINUTES = 1800; // In seconds

    protected $imageTypes;

    protected static $cacheTiming = [];

    public function __construct($context = null)
    {
        parent::__construct($context);
        $this->imageTypes = $this->getProductImageTypes();
    }

    /**
     * Get Items from an order or cart.
     *
     * @param Order|Quote $object
     * @param bool $isSync
     *
     * @return array
     */
    protected function getItemsData($object, $isSync = false)
    {
        $data = [];
        foreach ($this->getItems($object) as $item) {
            $product = new Product($item['id_product'], false, $object->id_lang);

            $itemData = $this->getItemData($item, $object->id_lang);

            $idPA = $itemData['idProductAttribute'];

            if (version_compare(_PS_VERSION_, '1.6.0', '>=')) {
                $url = $this->link->getProductLink($product, null, null, null, $object->id_lang, $object->id_shop, $idPA, false, false, false, [], !$isSync);
            } else {
                $url = $this->link->getProductLink($product);
            }

            $imageUrl = $this->getProductImageUrl($product, $idPA);

            $firstAndLastCat = $this->getFirstAndLastCategories($product->id_category_default, $object->id_lang);

            // @TODO : extended custom fields
            $custom = null;

            $row = [
                'id' => (string) $product->id,
                'label' => $itemData['label'],
                'quantity' => $itemData['quantity'],
                'totalET' => $itemData['totalET'],
                'totalATI' => $itemData['totalATI'],
                'url' => $url,
                'imageUrl' => $imageUrl,
                'universe' => $firstAndLastCat['universe'],
                'category' => $firstAndLastCat['category'],
                'custom' => $custom
            ];

            if ($this->helper->isProductCatalogEnabled()) {
                $categories = $this->getCategories($product, $object->id_lang);
                if (isset($categories)) {
                    $row['categories'] = $categories;
                }
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get Items from an order or cart.
     *
     * @param object $object
     *
     * @return array
     */
    protected function getItems($object)
    {
        throw new Exception("Method 'getItems' not declared.");
    }

    /**
     * Get Items from an order or cart.
     *
     * @param array $item
     * @param int $idLang
     *
     * @return array
     */
    protected function getItemData($item, $idLang)
    {
        throw new Exception("Method 'getItems' not declared.");
    }

    /**
     * Get Product Image Url.
     *
     * @TODO: Revise in the future for automatizate
     *
     * @param Product $product
     *
     * @return string
     */
    private function getProductImageUrl($product, $idProductAttribute)
    {
        $bestImage = null;
        if ((int) $idProductAttribute) {
            $bestImage = Db::getInstance()->getRow(
                'SELECT pai.id_image FROM `' . _DB_PREFIX_ . 'product_attribute_image` pai
                    LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON i.`id_image` = pai.`id_image`
                    WHERE pai.`id_product_attribute` = ' . (int) $idProductAttribute .
                ' ORDER BY i.`cover` DESC, i.`position` ASC '
            );
        }

        if (!$bestImage) {
            $bestImage = Db::getInstance()->getRow(
                'SELECT i.`id_image` FROM `' . _DB_PREFIX_ . 'image` i
                    WHERE i.`id_product` = ' . (int) $product->id . ' AND i.`cover` = 1
                ORDER BY i.`cover` DESC, i.`position` ASC '
            );
        }

        if ($bestImage && (int) $bestImage['id_image']) {
            foreach ($this->imageTypes as $type) {
                $ids = $product->id . '-' . $bestImage['id_image'];

                return $this->link->getImageLink($product->link_rewrite, $ids, $type);
            }
        }

        return '';
    }

    private function getProductImageTypes()
    {
        $min = CartsGuru\CartsGuru\Shared\Constants::$minThumbSize;

        $imageTypes = [];
        foreach (array_reverse(ImageType::getImagesTypes(null, true)) as $type) {
            if (isset($type['height']) && isset($type['width']) && $type['products'] &&
                ($type['width'] >= $min) && ($type['height'] >= $min)) {
                $imageTypes[] = $type['name'];
            }
        }

        return $imageTypes;
    }

    private function getFirstAndLastCategories($idCategoryDefault, $idLang)
    {
        $category = new Category($idCategoryDefault, $idLang);

        $universeName = '';
        $categoryName = '';
        if ($category && (int) $category->id) {
            $categoryName = $category->name;
            $categories = $category->getParentsCategories($idLang);
            if (count($categories) > 0) {
                $universeName = $categories[count($categories) - 1]['name'];
                foreach ($categories as $cat) {
                    // prefer level depth 2 for universe 0 : root, 1 : accueil
                    if ((int) $cat['level_depth'] == 2) {
                        $universeName = $cat['name'];
                    }
                }
            }
        }

        return [
            'category' => $categoryName,
            'universe' => $universeName,
        ];
    }

    private static function getAllCategories($idLang)
    {
        // Get all store active categories
        $orderBy = 'ORDER BY c.`level_depth` DESC, category_shop.`position` DESC';
        $rawCategories = Category::getCategories($idLang, true, true, '', $orderBy);

        // Get only fields 'id', 'label' and ''id_parent' from all categories
        return array_reduce($rawCategories, function ($carry, $item) {
            foreach ($item as $subItem) {
                foreach ($subItem as $cat) {
                    $carry[] = [
                        'id' => $cat['id_category'],
                        'label' => $cat['name'],
                        'id_parent' => $cat['id_parent'],
                    ];
                }
            }

            return $carry;
        });
    }

    private function getCategories($product, $idLang)
    {
        $cacheId = 'CartsGuru::getAllCategories' . md5((int) $idLang);

        if (!Cache::isStored($cacheId) ||
            (strtotime('now') - self::$cacheTiming[$cacheId]) > self::THIRTY_MINUTES) {
            $allCategories = self::getAllCategories($idLang);
            Cache::store($cacheId, $allCategories);
            self::$cacheTiming[$cacheId] = strtotime('now');
        } else {
            $allCategories = Cache::retrieve($cacheId);
        }

        // Get selected product categories list
        $selectedCategories = $product->getCategories($product->id, $idLang);

        // Intersect selected list and all categories list
        $categories = array_filter($allCategories, function ($elem) use (&$selectedCategories) {
            // Exists selected category and not Root (parent equals 0)
            if (in_array($elem['id'], $selectedCategories) && (0 != $elem['id_parent'])) {
                // If a category has a parent category, add it to the selected list
                $selectedCategories[] = $elem['id_parent'];

                return true;
            }

            return false;
        });

        // Delete 'id_parent' field
        array_walk($categories, function (&$v) {
            unset($v['id_parent']);
        });

        return array_values($categories);
    }
}
