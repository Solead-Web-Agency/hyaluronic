<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CedWishBatch extends ObjectModel
{
    public static $definition = array(
        'table' => 'cedwish_batch',
        'primary' => 'id_cedwish_batch',
        'multilang' => false,
        'fields' => array(
            'id_cedwish_batch' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'since' => array('type' => self::TYPE_DATE, 'db_type' => 'date'),
            'wish_sort' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'warehouse_name' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'wish_limit' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'show_rejected' => array('type' => self::TYPE_BOOL, 'db_type' => 'text'),
            'job_id' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'download_link' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'start_run_time' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'status' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'created_at' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'error' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
        ),
    );
    public $id_cedwish_batch;
    public $since;
    public $wish_sort;
    public $wish_limit;
    public $show_rejected;
    public $warehouse_name;
    public $job_id;
    public $download_link;
    public $start_run_time;
    public $end_run_time;
    public $status;
    public $created_at;
    public $error;
    protected $batch_file_dir = _PS_MODULE_DIR_ . 'cedwish/feeds/batches/';

    public function __construct($id_cedwish_batch = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id_cedwish_batch, $idLang, $idShop);
    }

    public function updateBatchStatus($job_id, $data)
    {
        $data_to_update = array();
        $data['download_link'] = json_encode($data['file_urls']);
        foreach (array_keys(self::$definition['fields']) as $field) {
            if (isset($data[trim($field)])) {
                $data_to_update[trim($field)] = $data[trim($field)];
            }
        }

        if (isset($data_to_update['download_link']) && $data_to_update['download_link']) {
            if (!is_dir($this->batch_file_dir)) {
                mkdir($this->batch_file_dir, '0777', true);
            }
            if (!empty($data['file_urls'])) {
                $data_to_update['download_link'] = array();
                foreach ($data['file_urls'] as $k => $file_url) {
                    $filename = $this->batch_file_dir . $job_id . '-' . $k . '.csv';
                    file_put_contents($filename, Tools::file_get_contents($file_url));
                    $data_to_update['download_link'][] = $filename;
                }
                $data_to_update['download_link'] = json_encode($data_to_update['download_link']);
            }
        }
        $data_to_update['created_at'] = Tools::substr($data_to_update['created_at'], 0, 19);
        $data_to_update['created_at'] = str_replace("T", " ", $data_to_update['created_at']);
        return Db::getInstance()->update(
            'cedwish_batch',
            $data_to_update,
            'job_id LIKE "' . pSQL($job_id) . '"'
        );
    }

    public function process($job_id)
    {
        $db = Db::getInstance();
        if (!is_dir($this->batch_file_dir)) {
            mkdir($this->batch_file_dir, '0777', true);
        }
        $item_count = 0;
        $response = array();
        $download_links = Db::getInstance()->getValue(
            "SELECT `download_link` FROM `" . _DB_PREFIX_ . "cedwish_batch` WHERE job_id='" . pSQL($job_id) . "'"
        );

        if ($download_links && @json_decode($download_links, true)) {
            $download_links = @json_decode($download_links, true);
            $language = new Language((int)Configuration::get('CED_WISH_LANG_ID'));
            $iso_code = '';
            if ($language && $language->iso_code) {
                $iso_code = $language->iso_code;
            }
            foreach ($download_links as $download_link) {
                if (file_exists($download_link)) {
                    try {
                        $data = Tools::file_get_contents($download_link);
                        $data = explode("\n", $data);
                        if (!empty($data)) {
                            $data = array_filter($data);
                            foreach ($data as $row) {
                                if ($row) {
                                    $row = json_decode($row, true);
                                }
                                if (isset($row['variations']) && !empty($row['variations'])) {
                                    foreach ($row['variations'] as $variation) {
                                        $item_sku = isset($variation['sku']) ? $variation['sku'] : '';
                                        $item_status = isset($variation['status']) ? $variation['status'] : '';
                                        $item_variation_id = isset($variation['id']) ? $variation['id'] : '';
                                        $item_parentSku = isset($row['parent_sku']) ? $row['parent_sku'] : '';
                                        $item_product_id = isset($variation['product_id'])
                                            ? $variation['product_id'] : '';

                                        $item_parentSku = str_replace(
                                            "_" . $iso_code,
                                            "",
                                            $item_parentSku
                                        );
                                        $item_sku = str_replace("_" . $iso_code, "", $item_sku);
                                        if (!$item_sku) {
                                            $item_sku = $item_parentSku;
                                        }
                                        $result = $db->getRow(
                                            "SELECT id_product,id_product_attribute FROM 
                                            `" . _DB_PREFIX_ . "product_attribute` 
                                            WHERE reference LIKE '" . pSQL($item_sku) . "' 
                                            OR ean13 LIKE '" . pSQL($item_sku) . "'  
                                            OR upc LIKE '" . pSQL($item_sku) . "'"
                                        );

                                        if ($result && isset($result['id_product']) && $result['id_product']) {
                                            $exist = $db->getValue(
                                                "SELECT id_cedwish_product FROM `" . _DB_PREFIX_ . "cedwish_product` 
                                                WHERE id_product = '" . (int)$result['id_product'] . "' AND 
                                                id_product_attribute = '" . (int)$result['id_product_attribute'] . "'"
                                            );
                                            if ($exist) {
                                                $db->Execute(
                                                    "UPDATE `" . _DB_PREFIX_ . "cedwish_product` SET 
                                                    status = '" . pSQL($item_status) . "',
                                                    marketplace_id = '" . pSQL($item_product_id) . "',
                                                    variation_id = '" . pSQL($item_variation_id) . "',
                                                    id_product_attribute = 
                                                    '" . (int)$result['id_product_attribute'] . "',
                                                    id_product = '" . (int)$result['id_product'] . "'
                                                    WHERE id_cedwish_product = '" . (int)$exist . "'"
                                                );
                                            } else {
                                                $db->Execute(
                                                    "INSERT INTO `" . _DB_PREFIX_ . "cedwish_product` SET 
                                                    status = '" . pSQL($item_status) . "',
                                                    marketplace_id = '" . pSQL($item_product_id) . "',
                                                    variation_id = '" . pSQL($item_variation_id) . "',
                                                    id_product_attribute 
                                                    = '" . (int)$result['id_product_attribute'] . "',
                                                    id_product = '" . (int)$result['id_product'] . "'"
                                                );
                                            }
                                            $item_count++;
                                        } else {
                                            $result = $db->getRow(
                                                "SELECT id_product FROM `" . _DB_PREFIX_ . "product` 
                                                WHERE reference LIKE '" . pSQL($item_sku) . "' 
                                                OR ean13 LIKE '" . pSQL($item_sku) . "' 
                                                OR upc LIKE '" . pSQL($item_sku) . "'"
                                            );

                                            if ($result && isset($result['id_product']) && $result['id_product']) {
                                                $exist = $db->getValue(
                                                    "SELECT id_cedwish_product FROM 
                                                    `" . _DB_PREFIX_ . "cedwish_product` 
                                                    WHERE id_product = '" . (int)$result['id_product'] . "' 
                                                    AND id_product_attribute = '0'"
                                                );
                                                if ($exist) {
                                                    $db->Execute(
                                                        "UPDATE `" . _DB_PREFIX_ . "cedwish_product` SET 
                                                        status = '" . pSQL($item_status) . "',
                                                        marketplace_id = '" . pSQL($item_product_id) . "',
                                                        variation_id = '" . pSQL($item_variation_id) . "',
                                                        id_product_attribute = '0',
                                                        id_product = '" . (int)$result['id_product'] . "'
                                                        WHERE id_cedwish_product = '" . (int)$exist . "'"
                                                    );
                                                } else {
                                                    $db->Execute(
                                                        "INSERT INTO `" . _DB_PREFIX_ . "cedwish_product` SET 
                                                        status = '" . pSQL($item_status) . "',
                                                        marketplace_id = '" . pSQL($item_product_id) . "',
                                                        variation_id = '" . pSQL($item_variation_id) . "',
                                                        id_product_attribute = '0',
                                                        id_product = '" . (int)$result['id_product'] . "'"
                                                    );
                                                }
                                                $item_count++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $response['error'][] = $e->getMessage();
                    }
                    $response['success'][] = $item_count . ' Item(s) in batch processed ';
                } else {
                    $response['error'][] = 'No response From Wish.';
                }
            }
        }
        return $response;
    }

    public function delete()
    {
        parent::delete();
    }
}
