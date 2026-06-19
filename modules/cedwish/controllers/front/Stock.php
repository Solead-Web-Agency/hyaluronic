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

class CedWishStockModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if (!Tools::getIsset('secure_key')
            || Tools::getValue('secure_key') != Configuration::get('CED_WISH_CRON_SECURE_KEY')
        ) {
            die('Secure key does not matched');
        }
        CedWishHelper::updateInfo();
        try {
            $queue_info = CedWishQueue::getQueueByType('stock', 500);
            if (!empty($queue_info)) {
                $products = array();
                foreach ($queue_info as $queue) {
                    $temp = json_decode(Tools::getDescriptionClean($queue['queued_items']), true);
                    if (!empty($temp)) {
                        $products = array_merge($products, $temp);
                        CedWishQueue::deleteQueue((int)$queue['id_cedwish_queue']);
                    }
                }
                if (!empty($products)) {
                    $products = array_filter($products);
                }
                $product = new CedWishProduct();
                $products = $product->updateProductStockAndPrice($products);
                echo '<pre>';
                print_r($products);
            }
            Configuration::updateValue('CED_WISH_STOCK_CRON_LAST_EXECUTION', date("Y-m-d H:i:s"));
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
