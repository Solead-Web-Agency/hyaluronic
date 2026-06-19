<?php

require_once _PS_MODULE_DIR_ . 'qov/qov.php';

class AdminPreviewOrderController extends ModuleAdminController
{

    public function ajaxProcess()
    {
        $qov = new qov();
        $context = new Context();
        $getContext = $context->getContext();

        if (Tools::isSubmit('secure_key') && Tools::getValue('secure_key') == $qov->secure_key && (int)Tools::getValue('id_order', 'false') != 'false')
        {
            $order = new Order((int)Tools::getValue('id_order'));
            $details = $qov->getOrderDetails((int)Tools::getValue('id_order'));
            echo "<tr class=\"QuickOrderDetails DetailsOrder" . (int)Tools::getValue('id_order') . "\"><td colspan=\"12\">$details</td></tr>";
        }

        if (Tools::isSubmit('secure_key') && Tools::getValue('secure_key') == $qov->secure_key && (int)Tools::getValue('ido', 'false') != 'false' && Tools::getValue('tracking', 'false') != 'false')
        {
            $order = new Order((int)Tools::getValue('ido'));
            $order->shipping_number = (string)Tools::getValue('tracking');
            $order->setWsShippingNumber((string)Tools::getValue('tracking'));
            $order->update();
            $customer = new Customer($order->id_customer);
            //SHIPPING
            $id_order_carrier = Db::getInstance()->getValue('SELECT `id_order_carrier` FROM `' . _DB_PREFIX_ . 'order_carrier` WHERE `id_order` = ' . (int)Tools::getValue('ido') . '');
            $order_carrier = new OrderCarrier($id_order_carrier);
            $carrier = new Carrier($order_carrier->id_carrier);
            $templateVars = array(
                '{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{id_order}' => $order->id,
                '{shipping_number}' => $order->shipping_number,
                '{order_name}' => $order->getUniqReference()
            );
            if (Configuration::get('QOV_TRACKING_EMAIL') == 1)
            {
                if (@Mail::Send((int)$order->id_lang, 'in_transit', Mail::l('Package in transit', (int)$order->id_lang), $templateVars, $customer->email, $customer->firstname . ' ' . $customer->lastname, null, null, null, null, _PS_MAIL_DIR_, true, (int)$order->id_shop))
                {

                    Hook::exec('actionAdminOrdersTrackingNumberUpdate', array(
                        'order' => $order,
                        'customer' => $customer,
                        'carrier' => $carrier
                    ), null, false, true, false, $order->id_shop);
                }
            }

            if ($order->shipping_number != '')
            {
                $tracking = str_replace('@', $order->shipping_number, $carrier->url);
            }
            else
            {
                $tracking = false;
            }
            echo $tracking;
        }

        if (Tools::isSubmit('secure_key') && Tools::getValue('secure_key') == $qov->secure_key && (int)Tools::getValue('idorder', 'false') != 'false' && (int)Tools::getValue('ostatus', 'false') != 'false')
        {
            $order = new Order((int)Tools::getValue('idorder'));
            $current_state = new OrderState($order->current_state, $getContext->language->id);
            $history = new OrderHistory();
            $history->id_order = (int)Tools::getValue('idorder');
            $history->id_employee = (int)Tools::getValue('id_employee');
            $history->changeIdOrderState((int)Tools::getValue('ostatus'), $order);
            $history->addWithemail();
            $employee = new Employee($history->id_employee);

            foreach (OrderState::getOrderStates($getContext->language->id) as $key => $value)
            {
                if ($value['id_order_state'] == (int)Tools::getValue('ostatus'))
                {
                    echo '
            <tr>
                <td><img src="../img/os/' . $value['id_order_state'] . '.gif" width="16" height="16" /></td>
                <td>' . $value['name'] . '<span class="hidden qov_old_status_of_order">' . $current_state->name . '</span><span class="qov_last_status_of_order hidden label color_field" style="background-color:' . $value['color'] . '; color: ' . (Tools::getBrightness($value['color']) < 128 ? "white" : "#383838") . '">' . $value['name'] . '</td>
                <td>' . $employee->firstname . ' ' . $employee->lastname . '</td>
                <td>' . Tools::displayDate($history->date_add, null, true) . '</td>
            </tr>';
                }
            }
        }
    }
}