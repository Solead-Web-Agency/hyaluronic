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

class MigrationProMigratedData extends ObjectModel
{
    const TYPE_TAX = 't';
    const TYPE_TAXES = 't';
    const TYPE_TAXRULESGROUP = 'trg';
    const TYPE_TAXRULE = 'tr';
    const TYPE_COUNTRY = 'co';
    const TYPE_STATE = 'st';
    const TYPE_CATEGORY = 'c';
    const TYPE_CATEGORIES = 'c';
    const TYPE_CARRIER = 'crr';
    const TYPE_CARRIERS = 'crr';
    const TYPE_PRODUCT = 'p';
    const TYPE_PRODUCTS = 'p';
    const TYPE_ATTACHMENT = 'atc';
    const TYPE_PRODUCTDOWNLOAD = 'prd';
    const TYPE_SPECIFICPRICERULE = 'spr';
    const TYPE_CATALOG_PRICE_RULES = 'spr';
    const TYPE_SPECIFICPRICERULECONDITIONGROUP = 'spg';
    const TYPE_SPECIFICPRICERULECONDITION = 'spc';
    const TYPE_ATTRIBUTEGROUP = 'ag';
    const TYPE_ATTRIBUTE = 'a';
    const TYPE_COMBINATION = 'com'; //PRODUCT_ATTRIBUTE
    const TYPE_SUPPLIER = 's';
    const TYPE_MANUFACTURER = 'm';
    const TYPE_MANUFACTURERS = 'm';
    const TYPE_SPECIFICPRICE = 'sp';
    const TYPE_IMAGE = 'i';
    const TYPE_FEATURE = 'f';
    const TYPE_FEATUREVALUE = 'fv';
    const TYPE_CUSTOMIZATIONFIELD = 'cf';
    const TYPE_TAG = 'tag';
    const TYPE_CUSTOMER = 'cus';
    const TYPE_CUSTOMERS = 'cus';
    const TYPE_CUSTOMERTHREAD = 'ct';
    const TYPE_CUSTOMERMESSAGE = 'cm';
    const TYPE_CART = 'car';
    const TYPE_EMPLOYEE = 'e';
    const TYPE_EMPLOYEES = 'e';
    const TYPE_ADDRESS = 'adr';
    const TYPE_ORDER = 'o';
    const TYPE_ORDERS = 'o';
    const TYPE_ORDERDETAIL = 'od';
    const TYPE_ORDERRETURN = 'ort';
    const TYPE_ORDERHISTORY = 'oh';
    const TYPE_ORDERSLIP = 'osp';
    const TYPE_ORDERINVOICE = 'oi';
    const TYPE_ORDERCARRIER = 'oc';
    const TYPE_ORDERCARTRULE = 'ocr';
    const TYPE_ORDERPAYMENT = 'op';
    const TYPE_ORDERMESSAGE = 'om';
    const TYPE_MESSAGE = 'mes';
    const TYPE_STOCKAVAILABLE = 'sa';
    const TYPE_PRODUCTSUPPLIER = 'ps';
    const TYPE_CMS = 'cms';
    const TYPE_CMSROLE = 'cro';
    const TYPE_CMSCATEGORY = 'ctg';
    const TYPE_CMSBLOCK = 'cbl';
    const TYPE_CARTRULE = 'cr';
    const TYPE_CART_RULES = 'cr';
    const TYPE_CARTRULEPRODUCTRULEGROUP = 'cpg';
    const TYPE_CARTRULEPRODUCTRULE = 'cpr';
    const TYPE_META = 'met';
    const TYPE_SEO = 'met';
    const TYPE_ZONE = 'zn';
    const TYPE_DELIVERY = 'dlv';
    const TYPE_RANGEPRICE = 'rn';
    const TYPE_RANGEWEIGHT = 'rw';

    public $id_data;
    public $entity_type;
    public $source_id;
    public $local_id;

    public static $definition = array(
        'table' => 'migrationpro_migrated_data',
        'primary' => 'id_data',
        'fields' => array(
            'entity_type' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'source_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'local_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true)
        ),
    );

    public static function import($entity_type, $source_id, $local_id)
    {
        $entity_type = constant("self::TYPE_" . Tools::strtoupper($entity_type));
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'migrationpro_migrated_data SET entity_type=\'' . pSQL($entity_type) . '\', source_id=' . (int)$source_id . ', local_id=' . (int)$local_id . ' ON DUPLICATE KEY UPDATE local_id=' . (int)$local_id;

        return Db::getInstance()->execute($sql);
    }

    public static function getLocalID($entity_type, $sourceID)
    {
        $entity_type = constant("self::TYPE_" . Tools::strtoupper(pSQL($entity_type)));

        $sql = 'SELECT local_id FROM ' . _DB_PREFIX_ . 'migrationpro_migrated_data WHERE entity_type=\'' . pSQL($entity_type) . '\' AND source_id=' . (int)$sourceID . ';';

        return Db::getInstance()->getValue($sql);
    }

    public static function getLastId($entity_type)
    {
        $entity_type = constant("self::TYPE_" . Tools::strtoupper(pSQL($entity_type)));

        $sql = 'SELECT source_id FROM ' . _DB_PREFIX_ . 'migrationpro_migrated_data WHERE entity_type=\'' . pSQL($entity_type) . '\'  ORDER BY source_id DESC';

        return Db::getInstance()->getValue($sql);
    }
}
