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

class CedWishSupportModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if (!Tools::getIsset('secure_key')
            || Tools::getValue('secure_key') != Configuration::get('CED_WISH_MERCHANT_ID')
        ) {
            die('Secure key does not matched');
        }
        try {
            if (Tools::getIsset('order')) {
                $db = Db::getInstance();
                $sql = "SELECT * FROM `" . _DB_PREFIX_ . "cedwish_order` ";
                $filename = "wish_order_report-" . Configuration::get('CED_WISH_MERCHANT_ID') . "-";
                if (Tools::getIsset('start_date') && Tools::getValue('start_date')) {
                    $filename .= Tools::getValue('start_date');
                    $sql .= "WHERE 
                    released_at >= '" . pSQL(date('Y-m-d', strtotime(Tools::getValue('start_date')))) . "'";
                } else {
                    die('Start Date Required.');
                }

                if (Tools::getIsset('end_date') && Tools::getIsset('end_date')) {
                    $filename .= '-' . Tools::getValue('end_date');
                    $sql .= " AND 
                    released_at <= '" . pSQL(date('Y-m-d', strtotime(Tools::getValue('end_date')))) . "'";
                } else {
                    $filename .= '-' . date('Y-m-d');
                    $sql .= " AND released_at <= '" . pSQL(date('Y-m-d')) . "'";
                }
                $filename .= '.csv';

                $results = $db->executeS($sql);

                if (!empty($results)) {
                    $f = fopen('php://memory', 'w');
                    $header = array(
                        'order_id',
                        'variant_id',
                        'order_time',
                        'sku',
                        'product_id',
                        'quantity',
                        'shipping',
                        'shipping_cost',
                        'price',
                        'cost',
                        'order_total',
                        'state',
                        'transaction_id',
                        'product_name',
                        'currency_code'
                    );
                    fputcsv($f, $header);
                    foreach ($results as $line) {
                        $order_data = @json_decode(Tools::getDescriptionClean($line['wish_order']), true);
                        if (is_array($order_data) && !empty($order_data)) {
                            $row = array(
                                'order_id' => $order_data['id'],
                                'variant_id' => $order_data['product_information']['variation_id'],
                                'order_time' => $order_data['released_at'],
                                'sku' => $order_data['product_information']['sku'],
                                'product_id' => $order_data['product_information']['id'],
                                'quantity' =>
                                    $order_data['order_payment']['general_payment_details']['product_quantity'],
                                'shipping' =>
                                    $order_data['order_payment']['general_payment_details']
                                    ['product_shipping_price']['amount'],
                                'shipping_cost' =>
                                    $order_data['order_payment']['general_payment_details']
                                    ['shipping_merchant_payment']['amount'],
                                'price' =>
                                    $order_data['order_payment']['general_payment_details']
                                    ['product_price']['amount'],
                                'cost' =>
                                    $order_data['order_payment']['general_payment_details']
                                    ['product_merchant_payment']['amount'],
                                'order_total' =>
                                    $order_data['order_payment']['general_payment_details']['payment_total']['amount'],
                                'state' => $order_data['state'],
                                'transaction_id' => $order_data['transaction_id'],
                                'product_name' => $order_data['product_information']['name'],
                                'currency_code' =>
                                    $order_data['order_payment']['general_payment_details']
                                    ['payment_total']['currency_code']

                            );
                            fputcsv($f, $row);
                        }
                    }
                    fseek($f, 0);
                    header('Content-Type: application/csv');
                    header('Content-Disposition: attachment; filename="' . $filename . '";');
                    fpassthru($f);
                }
                exit;
            } elseif (Tools::getIsset('product')) {
                $query = new DbQuery();
                $query->select(
                    'SUM(product_quantity*product_price) as sale,product_name,cl.name as category_name'
                );
                $query->from('order_detail', 'od');
                $query->join('JOIN ' . _DB_PREFIX_ . 'product p ON (od.product_id=p.id_product)');
                $query->join('JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (cl.id_category=p.id_category_default)');
                $query->where(
                    'id_order IN 
                    (SELECT store_order_id FROM ' . _DB_PREFIX_ . 'cedwish_order WHERE store_order_id > 0)'
                );
                $query->groupBy('product_id');
                $query->orderBy('sale DESC');
                $query->limit('10');

                $results = Db::getInstance()->executeS($query);

                $filename = "top_wish_products-" . Configuration::get('CED_WISH_MERCHANT_ID') . ".csv";
                if (!empty($results)) {
                    $f = fopen('php://memory', 'w');
                    $header = array(
                        'product_name',
                        'sale',
                        'category_name',
                    );
                    fputcsv($f, $header);
                    foreach ($results as $line) {
                        $row = array();
                        foreach ($header as $head) {
                            $row[$head] = $line[$head];
                        }
                        fputcsv($f, $row);
                    }
                    fseek($f, 0);
                    header('Content-Type: application/csv');
                    header('Content-Disposition: attachment; filename="' . $filename . '";');
                    fpassthru($f);
                }
                exit;
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
        die('Executed Successfully.');
    }
}
