<?php
 /**
 * NOTICE OF LICENSE 
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    MigrationPro
 * @copyright Copyright (c) 2012-2023 MigrationPro
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @package   MigrationPro: OpenCart to PrestaShop Migrate tool
 */

class EntityTypeMapper
{
    public static function getEntityTypeNameByAlias($alias)
    {
        $entityTypesAndAliases = self::entityTypes();

        if (array_key_exists($alias, $entityTypesAndAliases)) {
            return $entityTypesAndAliases[$alias];
        }

        return 'Common';
    }

    private static function entityTypes()
    {
        $entityTypeAliasesAndNames = array(
            't'=>'Tax',
            'trg'=>'Tax Rules Group',
            'tr'=>'Tax Rule',
            'co'=>'Country',
            'st'=>'State',
            'c'=>'Category',
            'crr'=>'Carrier',
            'p'=>'Product',
            'atc'=>'Attachment',
            'prd'=>'Product Download',
            'spr'=>'Specific Price Rule',
            'spg'=> 'Specific Price Rule Condition Group',
            'spc'=>'Specific Price Rule Condition',
            'ag'=>'Attribute Group',
            'a'=>'Attribute',
            'com'=>'Combination',
            's'=>'Supplier',
            'm'=>'Manufacturer',
            'sp'=>'Specific Price',
            'i'=>'Image',
            'f'=>'Feature',
            'fv'=>'Feature Value',
            'cf'=>'Customization Field',
            'tag'=>'Tag',
            'cus'=>'Customer',
            'ct'=>'Customer Thread',
            'cm'=>'Customer Message',
            'car'=>'Cart',
            'e'=>'Employee',
            'adr'=>'Address',
            'o'=>'Order',
            'od'=>'Order Detail',
            'ort'=>'Order Return',
            'oh'=>'Order History',
            'osp'=>'Order Slip',
            'oi'=>'Order Invoice',
            'oc'=>'Order Carrier',
            'ocr'=>'Order Cart Rule',
            'op'=>'Order Payment',
            'om'=>'Order Message',
            'mes'=>'Message',
            'sa'=> 'Stock Available',
            'ps' => 'Product Supplier',
            'cms'=> 'CMS',
            'cro' => 'CMS Role',
            'ctg' => 'CMS Category',
            'cbl' => 'CMS Block',
            'cr' => 'Cart Rule',
            'cpg' => 'Cart Rule Product Rule Group',
            'cpr' => 'Cart Rule Product Rule',
            'met' => 'Meta',
            'war' => 'Warehouse',
            'stk' => 'Stock',
            'wpl' => 'Ware House Location',
            'zn' => 'Zone',
            'dlv' => 'Delivery',
            'rn' => 'Range Price',
            'rw' => 'Range Weight',
        );

        return $entityTypeAliasesAndNames;
    }
}
