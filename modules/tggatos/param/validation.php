<?php
/**
* 2007-2014 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
if (version_compare(_PS_VERSION_, '1.5', '<'))
	include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/atos.php');

if (!Tools::getValue('DATA'))
	throw new Exception('error in atos module: data is required');
else
{
	$datas = Tools::getValue('DATA');
	$error_behavior = (int)Configuration::get('ATOS_ERROR_BEHAVIOR');

	$atos = new Atos();
	$is_win = (Tools::strtoupper(Tools::substr(PHP_OS, 0, 3)) === 'WIN');
	$exec = $atos->bin_dir.'response'.(((int)$is_win === 1) ? '.exe' : '');
	$result = exec($exec.' pathfile='.dirname(__FILE__).'/pathfile message='.preg_replace('#[^a-z0-9]#Ui', '', $datas));
	$result_array = explode('!', $result);

	if (!count($result_array) || !isset($result_array[3]) || !isset($result_array[6]))
	{
		Mail::Send(Configuration::get('PS_LANG_DEFAULT'), 'notification',
		$atos->l('Atos notification'),
		array('message' => $atos->l('error in atos payment module: can\'t execute request')),
		Configuration::get('ATOS_NOTIFICATION_EMAIL'),
		null, null, null, null, null,
		dirname(__FILE__).'/mails/');
	}
	elseif ($result_array[1] == -1)
	{
		Mail::Send(Configuration::get('PS_LANG_DEFAULT'), 'notification',
		$atos->l('Atos notification'),
		array('message' => $atos->l('error in atos payment module:').' '.$result_array[2]),
		Configuration::get('ATOS_NOTIFICATION_EMAIL'),
		null, null, null, null, null,
		dirname(__FILE__).'/mails/');
	}
	else
	{
		$total_paid = ($result_array[5] / 100);
		$message = $atos->l('Transaction ID:').' '.$result_array[6].'<br />'."\n".
		$atos->l('Payment mean:').' '.$result_array[7].'<br />'."\n".
		$atos->l('Payment has began at:').' '.$result_array[8].'<br />'."\n".
		$atos->l('Payment received at:').' '.$result_array[10].' '.$result_array[9].'<br />'."\n".
		$atos->l('Authorization ID:').' '.$result_array[13].'<br />'."\n".
		$atos->l('Currency:').' '.$result_array[14].'<br />'."\n".
		$atos->l('Customer IP address:').' '.$result_array[29].'<br />'."\n".
		$atos->l('Cart ID:').' '.$result_array[22].'<br />'."\n\n".
		$atos->l('Atos Real Paid:').' '.$total_paid.'<br />'."\n\n".
		$atos->l('Atos Version:').' '.$atos->version.'<br />'."\n";
		$order_state = _PS_OS_PAYMENT_;

		/* Checking whether merchant ID is OK */
		$merchant_id = Configuration::get('ATOS_MERCHANT_ID');
		if ($result_array[3] != $merchant_id)
		{
			$order_state = _PS_OS_ERROR_;
			$msg = ' ('.$result_array[3].' '.$atos->l('should be').' '.$merchant_id.')';
			$message .= '<span style="color: red;">'.$atos->l('Merchant ID is not valid').$msg.'</span>'."\n";
		}

		/* Checking for currency */
		if ($order_state == _PS_OS_PAYMENT_)
		{
			$cart = new Cart((int)$result_array[22]);
			$currencies = array(1 => '978');
			if (isset($currencies[$cart->id_currency]))
			{
				if ($currencies[$cart->id_currency] != Tools::strtoupper($result_array[14]))
				{
					$order_state = _PS_OS_ERROR_;
					$message .= '<span style="color: red;">'.$atos->l('Currency is not the right one (should be ').$currencies[$cart->id_currency].')</span>'."\n";
				}
			}
		}

		/* Checking for bank code response */
		if ($order_state == _PS_OS_PAYMENT_)
		{
			$response_code = (int)$result_array[11];
			switch ($response_code)
			{
				case 3:
					$message .= '<span style="color: red;">'.$atos->l('Merchand ID is not valid').'</span>'."\n";
					$order_state = _PS_OS_ERROR_;
				break;
				case 5:
					$message .= '<span style="color: red;">'.$atos->l('Bank has rejected payment').'</span>'."\n";
					$order_state = _PS_OS_ERROR_;
				break;
				case 12:
				case 17:
					die;
				case 30:
					$message .= '<span style="color: red;">'.$atos->l('Format error').'</span>'."\n";
					$order_state = _PS_OS_ERROR_;
				break;
				case 34:
					$message .= '<span style="color: red;">'.$atos->l('Bank said that transaction might be fraudulous').'</span>'."\n";
					$order_state = _PS_OS_ERROR_;
				break;
				case 75:
					$message .= '<span style="color: red;">'.$atos->l('Customer has exceeded max tries for its card number').'</span>'."\n";
					$order_state = _PS_OS_ERROR_;
				break;
				case 90:
					$message .= '<span style="color: red;">'.$atos->l('Bank server was unavailable').'</span>'."\n";
					$order_state = _PS_OS_ERROR_;
				break;
			}
		}

		$customer = new Customer((int)$cart->id_customer);

		if ($order_state == _PS_OS_PAYMENT_ || $error_behavior == '0')
		{
			$total_paid = ($result_array[5] / 100);
			$atos->validateOrder((int)$result_array[22], $order_state, $total_paid, $atos->displayName, $message, array(), null, false, $customer->secure_key);

			if (version_compare(_PS_VERSION_, '1.5.0.0') >= '0')
			{
				$amount = ($result_array[5] / 100);

				$order_id = Order::getOrderByCartId((int)$result_array[22]);
				$order = new Order((int)$order_id);
				if ($order_state == _PS_OS_PAYMENT_)
				{
					$order->valid = 1;
					$order->save();
				}

				$id_order_payment = Db::getInstance()->getValue('SELECT id_order_payment
				FROM `'._DB_PREFIX_.'order_payment`
				WHERE `order_reference` LIKE \'%'.pSQL($order->reference).'%\'');

				if ($id_order_payment == false)
					$order->addOrderPayment($amount, null, $result_array[6]);
				else
				{
					$order_payment = new OrderPayment((int)$id_order_payment);
					$order_payment->transaction_id = $result_array[6];
					$order_payment->save();
				}
			}
		}
		elseif ($error_behavior == 1)
		{
			Mail::Send(Configuration::get('PS_LANG_DEFAULT'), 'notification',
			$atos->l('Atos notification'),
			array('message' => 'Order: '.$result_array[22].' / '.$message),
			Configuration::get('ATOS_NOTIFICATION_EMAIL'),
			null, null, null, null, null,
			dirname(__FILE__).'/mails/');
		}
	}
}