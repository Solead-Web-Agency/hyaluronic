<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ZendeskAddon\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Contact;
use Order;
use Product;
use ZendeskClasslib\Extensions\ProcessLogger\ProcessLoggerHandler;
use ZendeskClasslib\Hook\AbstractHook;

class CustomerHook extends AbstractHook
{
    const AVAILABLE_HOOKS = [
        'actionObjectCustomerMessageAddAfter',
    ];

    public function actionObjectCustomerMessageAddAfter($params)
    {
        if (\Tools::getValue('visibility') == '1' || \Dispatcher::getInstance()->getController() == 'AdminOrders'
            || \Dispatcher::getInstance()->getController() == 'validation') {
            if ((bool) \Tools::getValue('visibility')) {
                ProcessLoggerHandler::logInfo('Visibility is true', CustomerHook::class, null, 'actionObjectCustomerMessageAddAfter');
            }

            return true;
        }

        $api = new \ZendeskApi();
        $lang = \Context::getContext()->language->iso_code;

        $type_array = [1 => 'problem', 2 => 'question'];

        $customer_message = $params['object'];
        $customer_thread = new \CustomerThread((int) $customer_message->id_customer_thread);

        if ($customer_thread->id_customer) {
            $customer = new \Customer((int) $customer_thread->id_customer);
            $requester_name = $customer->firstname . ' ' . $customer->lastname;
        } else {
            $requester_name = $customer_thread->email;
        }

        $id_product = \Tools::getValue('id_product'); // get id_product product
        $contact = new \Contact((int) $customer_thread->id_contact);
        $carrier = new \Carrier($params['cart']->id_carrier);

        $selectedProduct = \Product::getProductName($id_product);
        $currentOrder = \Order::getUniqReferenceOf($customer_thread->id_order);

        $data = [];
        $data['type'] = $type_array[2];
        $data['status'] = 'new';
        // $data['tags'] = array('contact');

        if (!empty($contact->name[(int) $customer_thread->id_lang])) { /* Case  1 : When the subject isn't empty */
            $data['subject'] = $contact->name[(int) $customer_thread->id_lang];
        } elseif (!empty($id_product)) {  /* Case 2 :  when we add a message to an order in the order details tab in the history of my orders */
            $trads = [
                'order' => [
                    'en' => 'Order',
                    'fr' => 'Commande',
                ],
            ];
            $data['subject'] = $trads['order'][$lang] . ' n°' . $currentOrder . ' - ' . $selectedProduct;
        } else { /* Case 3 : when we add a message into the cart */
            $trads = [
                'order' => [
                    'en' => 'Order',
                    'fr' => 'Commande',
                ],
            ];
            $data['subject'] = $trads['order'][$lang] . ' n°' . $currentOrder;
        }
        $data['comment']['body'] = $customer_message->message;
        $data['requester'] = [
            'name' => $requester_name,
            'email' => $customer_thread->email,
        ];
        $data['via'] = [
            'channel' => 'web',
        ];
        $data['external_id'] = (int) $customer_message->id;
        if ($customer_message->file_name != '') {
            $ret = $api->uploadFile($customer_message->file_name, _PS_UPLOAD_DIR_ . $customer_message->file_name);
            if (!isset($ret->error)) {
                $data['comment']['uploads'] = $ret->upload->token;
            }
        }

        $idBrand = \Configuration::get(\Zendesk::ID_BRAND, null, null, (int) \Context::getContext()->shop->id, -1);

        if ($idBrand !== false && (int) $idBrand > 0) {
            $data['brand_id'] = (int) $idBrand;
        }

        // If we have an order reference, set it in the ticket
        if (isset($customer_thread->id_order) && \Configuration::get('ZENDESK_ORDER_ID_FIELD_ID')) {
            $order = new \Order($customer_thread->id_order);
            if (\Validate::isLoadedObject($order)) {
                $data['custom_fields'] = [
                    [
                        'id' => \Configuration::get('ZENDESK_ORDER_ID_FIELD_ID'),
                        'value' => $order->reference,
                    ],
                ];
            }
        }
        $api->addTicket($data);
    }
}
