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

require_once _PS_MODULE_DIR_ . 'cedwish/classes/order.php';

class CedWishOrderModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if (!Tools::getIsset('secure_key')
            || Tools::getValue('secure_key') != Configuration::get('CED_WISH_CRON_SECURE_KEY')
        ) {
            die('Secure key does not matched');
        }
        try {
            $api = new CedWishApi();
            $orders = $api->getPendingOrders();
            if (isset($orders['code']) && ($orders['code']==0) && !empty($orders['data'])) {
                $order = new CedWishOrder();
                $order->createOrder($orders['data']);
            } else {
                echo '<pre>';
                print_r($orders);
            }
            Configuration::updateValue('CED_WISH_ORDER_CRON_LAST_EXECUTION', date("Y-m-d H:i:s"));
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
