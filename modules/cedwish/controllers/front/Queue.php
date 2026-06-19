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
 * @package   CedBol
 */

require_once _PS_MODULE_DIR_ . 'cedwish/classes/helper.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/queue.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/order.php';

class CedWishQueueModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if (!Tools::getIsset('secure_key')
            || Tools::getValue('secure_key') != Configuration::get('CED_WISH_CRON_SECURE_KEY')
        ) {
            die('Secure key does not matched');
        }

        try {
            $queue_info = CedWishQueue::getQueue();
            if (!empty($queue_info)
                && isset($queue_info['id_cedwish_queue'])
                && isset($queue_info['queue_type'])
                && isset($queue_info['queued_items'])
            ) {
                $id_CED_WISH_queue = $queue_info['id_cedwish_queue'];
                $queue_type = $queue_info['queue_type'];
                $queued_items = json_decode(stripcslashes($queue_info['queued_items']), true);
                echo '<pre>';
                print_r($queue_info);
                $product = new CedWishProduct();
                if ($id_CED_WISH_queue && !empty($queued_items)) {
                    switch ($queue_type) {
                        case 'product':
                        case 'upload':
                            $response = $product->upload($queued_items);
                            var_export($response, true);
                            break;

                        case 'price':
                            $response = $product->updateProductStockAndPrice($queued_items, 'price');
                            var_export($response, true);
                            break;

                        case 'update':
                            $response = $product->massUpdate($queued_items);
                            var_export($response, true);
                            break;

                        case 'shipment':
                            $order = new CedWishOrder();
                            $response = $order->shipOrder($queued_items);
                            var_export($response, true);
                            break;
                    }
                }
                CedWishQueue::deleteQueue((int)$id_CED_WISH_queue);
            }
            Configuration::updateValue('CED_WISH_QUEUE_CRON_LAST_EXECUTION', date("Y-m-d H:i:s"));
            die('Cron Executed Successfully.');
        } catch (Exception $e) {
            CedWishHelper::addLog(
                $e->getMessage(),
                FileLogger::ERROR
            );
            die('Please See Log(s).');
        }
    }
}
