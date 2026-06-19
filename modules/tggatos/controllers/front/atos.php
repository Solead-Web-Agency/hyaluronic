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

class Atos extends PaymentModule
{
	public $bin_dir;

	protected $js_path = null;
	protected $css_path = null;

	protected static $lang_cache;

	private $html;

	private $bank_array = array(
		'elysnet' => 'HSBC / CCF',
		'mercanet' => 'BNP Paribas',
		'sogenactif' => 'Société Générale',
		'etransactions' => 'Crédit Agricole',
		'webaffaires' => 'Crédit du Nord / Kolb',
		'sherlocks' => 'Crédit Lyonnais',
		'cyberplus' => 'Banque Populaire / SMC',
		'scelliusnet' => 'Banque Postale',
		'citelis' => 'Crédit Mutuel'
	);

	private $bank_paylib = array(
		'mercanet',
		'sogenactif',
		'scelliusnet',
	);

	public function __construct()
	{
		$this->name = 'atos';
		$this->version = '2.9.24';

		$this->tab = 'payments_gateways';
		$this->page = basename(__FILE__, '.php');
		$this->bootstrap = true;
		$this->author = 'PrestaShop';
		$this->module_key = 'f6d0744cc640221a199f05fca47476e0';

		parent::__construct();

		if (!Configuration::get('ATOS_BIN_DIR'))
			Configuration::updateValue('ATOS_BIN_DIR', dirname(__FILE__).'/bin/');
		elseif(Configuration::get('ATOS_BIN_DIR') != dirname(__FILE__).'/bin/')
			Configuration::updateValue('ATOS_BIN_DIR', dirname(__FILE__).'/bin/');

		$this->bin_dir = Configuration::get('ATOS_BIN_DIR');

		$this->js_path = $this->_path.'js/';
		$this->css_path = $this->_path.'css/';
		if (version_compare(_PS_VERSION_, '1.6', '<'))
			$this->getLang();

		$this->displayName = $this->l('Atos');
		$this->description = $this->l('This payment module for banks using ATOS allows your customers to pay by Credit Card');
	}

	public function install()
	{
		if (parent::install() === false
			|| $this->registerHook('orderConfirmation') === false
			|| $this->registerHook('payment') === false
			|| $this->registerHook('header') === false)
			return false;

		if (!Configuration::get('ATOS_CAPTURE_MODE'))
			Configuration::updateValue('ATOS_CAPTURE_MODE', 1);

		if (!Configuration::get('ATOS_CAPTURE_DAY'))
			Configuration::updateValue('ATOS_CAPTURE_DAY', 0);

		if (!Configuration::get('ATOS_CAPTURE_MODE'))
			Configuration::updateValue('ATOS_CAPTURE_MODE', 1);

		if (!Configuration::get('ATOS_NOTIFICATION_EMAIL'))
			Configuration::updateValue('ATOS_NOTIFICATION_EMAIL', Configuration::get('PS_SHOP_EMAIL'));

		if (!Configuration::get('ATOS_ALLOW_CUSTOM'))
			Configuration::updateValue('ATOS_ALLOW_CUSTOM', 0);

		$prems = substr(sprintf('%o', fileperms(dirname(__FILE__))), -4);
		if ($prems != '0777')
			chmod(dirname(__FILE__), octdec(0777));

		return true;
	}

	public function hookOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return;

		if ($params['objOrder']->valid || $params['objOrder']->current_state == _PS_OS_PAYMENT_)
			$this->context->smarty->assign(array('status' => 'ok', 'id_order' => $params['objOrder']->id));
		else
			$this->context->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'views/templates/hook/hookorderconfirmation.tpl');
	}

	public function postProcess()
	{
		// Step 1
		if (Tools::isSubmit('submitAtosID'))
		{
			Configuration::updateValue('ATOS_MERCHANT_ID', trim(Tools::getValue('ATOS_MERCHANT_ID')));
			return 1;
		}

		// Step 2
		if (Tools::isSubmit('submitAtosCertif') && isset($_FILES['ATOS_CERTIF']) && !$_FILES['ATOS_CERTIF']['error'])
		{
			if (!Configuration::get('ATOS_MERCHANT_ID'))
				$this->html .= $this->displayError($this->l('Please to fill the MERCHANT ID before uploading certificate'));
			else
			{
				if (move_uploaded_file($_FILES['ATOS_CERTIF']['tmp_name'], dirname(__FILE__).'/certif.fr.'.Configuration::get('ATOS_MERCHANT_ID')))
				{
					$this->html .= $this->displayConfirmation($this->l('Certificate updated'));

					$atoscode = $this->findCertificatSetup(dirname(__FILE__).'/certif.fr.'.Configuration::get('ATOS_MERCHANT_ID'));
					if($atoscode)
						$this->updateBank($atoscode);
				}
				else
					$this->html .= $this->displayError($this->l('Error in copying the certificat'));
			}
			return 2;
		}

		// Step 3
		if (Tools::isSubmit('submitAtosBank'))
		{
			$atoscode = '';
			foreach ($this->bank_array as $key => $value)
			{
				if ($key === pSQL(Tools::getValue('atos_bank'.$key)))
				{
					$atoscode = $key;
					break;
				}
			}
			unset($key, $value);

			$id_merchant = Configuration::get('ATOS_MERCHANT_ID');

			if (!empty($atoscode) && !empty($id_merchant))
				$this->updateBank($atoscode);

			else
				$this->html .= $this->displayError($this->l('Please to fill the MERCHANT ID before selecting your bank'));

			return 3;
		}

		// Step 4 Optional
		if (Tools::isSubmit('submitAtosOptions'))
		{
			$amex = (int)Tools::getValue('ATOS_ALLOW_AMEX');
			$paylib = (int)Tools::getValue('ATOS_ALLOW_PAYLIB');
			$custom = (int)Tools::getValue('ATOS_ALLOW_CUSTOM');
			$capture_mode = (int)Tools::getValue('ATOS_CAPTURE_MODE');
			$capture_day = (int)Tools::getValue('ATOS_CAPTURE_DAY');

			Configuration::updateValue('ATOS_ALLOW_AMEX', $amex);
			Configuration::updateValue('ATOS_ALLOW_PAYLIB', $paylib);
			Configuration::updateValue('ATOS_ALLOW_CUSTOM', $custom);
			Configuration::updateValue('ATOS_CAPTURE_MODE', $capture_mode);
			Configuration::updateValue('ATOS_CAPTURE_DAY', $capture_day);
			Configuration::updateValue('ATOS_REDIRECT', (int)Tools::getValue('ATOS_REDIRECT'));
			Configuration::updateValue('ATOS_ERROR_BEHAVIOR', (int)Tools::getValue('ATOS_ERROR_BEHAVIOR'));

			$email = Tools::getValue('ATOS_NOTIFICATION_EMAIL');
			if (empty($email) || Validate::isEmail($email))
				Configuration::updateValue('ATOS_NOTIFICATION_EMAIL', $email);
			else
				$this->html .= $this->displayError($this->l('please specify a valid e-mail address or nothing at all'));
			$this->html .= $this->displayConfirmation($this->l('Settings updated'));

			// Restore default parmcom
			$this->restoreParmcom();

			// Edit the parmcom
			$this->editParmcom();

			return 4;
		}
	}

	/**
	 * Find the bank setup by using the certificat
	 */
	public function findCertificatSetup($file_name)
	{
		$regex = '#[a-zA-Z_]+[,][0-9\/]+[,][a-zA-Z0-9]+[,][a-zA-Z0-9]+[,][a-zA-Z0-9]+#';
		$content = Tools::file_get_contents($file_name);

		if (!$content)
			return false;

		if (!preg_match_all($regex, $content, $matches) || !$matches[0][0])
			return false;

		$list = explode(',', strtolower($matches[0][0]));

		if (!array_key_exists($list[3], $this->bank_array))
			return false;

		return $list[3];
	}

	/**
	 * Update Bank setup
	 */
	public function updateBank($atoscode)
	{
		// Restore default parmcom
		$this->restoreParmcom();

		Configuration::updateValue('ATOS_BANK', trim($atoscode));

		$pathfile_content = 'DEBUG!NO!'."\n".'D_LOGO!'.__PS_BASE_URI__.'modules/atos/img/logos/!'."\n".
			'F_CERTIFICATE!'.dirname(__FILE__).'/certif!'."\n".'F_PARAM!'.dirname(__FILE__).'/parmcom!'."\n".
			'F_DEFAULT!'.dirname(__FILE__).'/parmcom.'.$atoscode.'!';

		if (!file_put_contents(dirname(__FILE__).'/pathfile', $pathfile_content))
			$this->html .= $this->displayError($this->l('Impossible to create pathfile'));

		if (!file_put_contents(dirname(__FILE__).'/parmcom.'.Configuration::get('ATOS_MERCHANT_ID'), "\nADVERT!logo.jpg!\n"))
			$this->html .= $this->displayError($this->l('Impossible to create parmcom'));

		$this->html .= $this->displayConfirmation($this->l('Your bank have been selected'));

		$this->editParmcom();
	}
	public function editParmcom()
	{
		$amex = (int)Configuration::get('ATOS_ALLOW_AMEX');
		$paylib = (int)Configuration::get('ATOS_ALLOW_PAYLIB');
		$bank = Configuration::get('ATOS_BANK');
		$parcomedit = dirname(__FILE__).'/parmcom.'.$bank;

		$string = 'PAYMENT_MEANS!CB,2,VISA,2,MASTERCARD,2!';
		if ($amex === 1 && $paylib === 1 && in_array($bank, $this->bank_paylib))
		{
			$data = Tools::file_get_contents($parcomedit);
			$data = str_replace($string, 'PAYMENT_MEANS!CB,2,VISA,2,MASTERCARD,2,AMEX,2,PAYLIB,2!', $data);
			file_put_contents($parcomedit, $data);
		}
		elseif ($paylib === 1 && in_array($bank, $this->bank_paylib))
		{
			$data = Tools::file_get_contents($parcomedit);
			$data = str_replace($string, 'PAYMENT_MEANS!CB,2,VISA,2,MASTERCARD,2,PAYLIB,2!', $data);
			file_put_contents($parcomedit, $data);
		}
		elseif ($amex === 1 || (!in_array($bank, $this->bank_paylib) && $amex !== 0))
		{
			$data = Tools::file_get_contents($parcomedit);
			$data = str_replace($string, 'PAYMENT_MEANS!CB,2,VISA,2,MASTERCARD,2,AMEX,2!', $data);
			file_put_contents($parcomedit, $data);
		}
		unset($this->bank_paylib);
	}

	public function restoreParmcom()
	{
		$bank = Configuration::get('ATOS_BANK');
		$parcomedit = dirname(__FILE__).'/parmcom.'.$bank;
		$string = 'PAYMENT_MEANS!CB,2,VISA,2,MASTERCARD,2!';

		$data = Tools::file_get_contents($parcomedit);

		$old = 'PAYMENT_MEANS!CB,2,VISA,2,MASTERCARD,2,AMEX,2,PAYLIB,2!';
		$data = str_replace($old, $string, $data);

		$old = 'PAYMENT_MEANS!CB,2,VISA,2,MASTERCARD,2,AMEX,2!';
		$data = str_replace($old, $string, $data);

		$old = 'PAYMENT_MEANS!CB,2,VISA,2,MASTERCARD,2,PAYLIB,2!';
		$data = str_replace($old, $string, $data);

		file_put_contents($parcomedit, $data);
	}

	public function getProperBin()
	{
		$file_list = Tools::scandir($this->bin_dir, '');
		$ignore_list = array('.', '..', 'index.php', 'request.exe', 'response.exe');
		$handle = array_diff($file_list, $ignore_list);
		foreach ($handle as $file)
		{
			$is_response = stristr($file, 'response');
			if($is_response !== false)
			{
				$result = $this->simpleExec($this->bin_dir.$file);
				if (!empty($result))
				{
					if ($file == 'response')
						break;

					$suffix = str_replace('response', '', $file);
					$this->renameBin($suffix);
				}
				unset($result);
			}
		}
		unset($handle, $file, $ignore_list, $file_list);
	}

	public function renameBin($suffix)
	{
		if (file_exists($this->bin_dir.'request'.$suffix))
		{
			rename($this->bin_dir.'request', $this->bin_dir.'request_old');
			rename($this->bin_dir.'request'.$suffix, $this->bin_dir.'request');
		}
		if (file_exists($this->bin_dir.'response'.$suffix))
		{
			rename($this->bin_dir.'response', $this->bin_dir.'response_old');
			rename($this->bin_dir.'response'.$suffix, $this->bin_dir.'response');
		}
	}

	public function simpleExec($cmd)
	{
		exec($cmd, $output);
		return ($output);
	}

	public function getContent()
	{
		// Submit process
		$is_submit = $this->postProcess();
		if (!Configuration::get('ATOS_CAPTURE_MODE'))
			Configuration::updateValue('ATOS_CAPTURE_MODE', 1);
		// Check configuration
		$exec = is_callable('exec');
		$safe_mode = ini_get('safe_mode');
		$bank = Configuration::get('ATOS_BANK');
		$amex = (int)Configuration::get('ATOS_ALLOW_AMEX');
		$capture_mode = (int)Configuration::get('ATOS_CAPTURE_MODE');
		$capture_day = (int)Configuration::get('ATOS_CAPTURE_DAY');

		$paylib = (int)Configuration::get('ATOS_ALLOW_PAYLIB');
		$custom = (int)Configuration::get('ATOS_ALLOW_CUSTOM');
		$redirect = (int)Configuration::get('ATOS_REDIRECT');
		$id_merchant = Configuration::get('ATOS_MERCHANT_ID');
		$shop_enable = (int)Configuration::get('PS_SHOP_ENABLE');
		$behavior = (int)Configuration::get('ATOS_ERROR_BEHAVIOR');
		$is_win = (Tools::strtoupper(Tools::substr(PHP_OS, 0, 3)) === 'WIN');
		$notification = Configuration::get('ATOS_NOTIFICATION_EMAIL');
		$response = is_executable($this->bin_dir.'response'.(((int)$is_win === 1) ? '.exe' : ''));
		$requests = is_executable($this->bin_dir.'request'.(((int)$is_win === 1) ? '.exe' : ''));

		// Binary auto configuration
		if ($exec && (int)$is_win === 0)
		{
			$result = $this->simpleExec($this->bin_dir.'response');
			if (empty($result))
				$this->getProperBin();
			unset($result);
		}

		// We load asset
		$this->loadAsset();

		if (version_compare(_PS_VERSION_, '1.6', '<'))
		{
			// Clean the code use tpl file for html
			$tab = '&tab_module='.$this->tab;
			$token_mod = '&token='.Tools::getAdminTokenLite('AdminModules');
			$token_pos = '&token='.Tools::getAdminTokenLite('AdminModulesPositions');
			$token_trad = '&token='.Tools::getAdminTokenLite('AdminTranslations');

			$this->context->smarty->assign(array(
				'module_active' => (bool)$this->active,
				'module_trad' => 'index.php?controller=AdminTranslations'.$token_trad.'&type=modules&lang=',
				'module_hook' => 'index.php?controller=AdminModulesPositions'.$token_pos.'&show_modules='.$this->id,
				'module_back' => 'index.php?controller=AdminModules'.$token_mod.$tab.'&module_name='.$this->name,
				'module_form' => 'index.php?controller=AdminModules&configure='.$this->name.$token_mod.$tab.'&module_name='.$this->name,
				'module_reset' => 'index.php?controller=AdminModules'.$token_mod.'&module_name='.$this->name.'&reset'.$tab,
			));
			// Clean memory
			unset($tab, $token_mod, $token_pos, $token_trad);
		}

		/* Language for documentation in back-office */
		$lang = 'FR';

		$this->context->smarty->assign(array(
			'exec'=> $exec,
			'bank'=> $bank,
			'amex'=> $amex,
			'is_win'=> $is_win,
			'paylib'=> $paylib,
			'custom'=> $custom,
			'request'=> $requests,
			'behavior'=> $behavior,
			'response'=> $response,
			'redirect'=> $redirect,
			'is_submit'=> $is_submit,
			'safe_mode'=> $safe_mode,
			'bin_dir'=> $this->bin_dir,
			'shop_enable'=> $shop_enable,
			'id_merchant'=> $id_merchant,
			'capture_day'=> $capture_day,
			'capture_mode'=> $capture_mode,
			'bank_list'=> $this->bank_array,
			'notification'=> $notification,
			'form_uri' => $_SERVER['REQUEST_URI'],
			'certificat' => (int)file_exists(dirname(__FILE__).'/certif.fr.'.$id_merchant),
			'pathfile' => (int)file_exists(dirname(__FILE__).'/pathfile'),
			'parmcom' => (int)file_exists(dirname(__FILE__).'/parmcom.'.$id_merchant),

			'html'=> $this->html,
			'module_name' => $this->name,
			'module_version' => $this->version,
			'debug_mode' => (int)_PS_MODE_DEV_,
			'lang_select' => self::$lang_cache,
			'module_display' => $this->displayName,
			'multishop' => (int)Shop::isFeatureActive(),
			'guide_link1' => 'docs/atos_guide_integration_'.$lang.'.pdf',
			'guide_link2' => 'docs/atos_guide_personnalisation_'.$lang.'.pdf',
			'ps_version' => (bool)version_compare(_PS_VERSION_, '1.6', '>'),
		));

		unset($this->bank_array);

		return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
	}

	private function setSilentURL()
	{
		return Tools::getShopDomain(true);
	}

	private function getAtosForm($cart)
	{
		$lang = new Language((int)$cart->id_lang);
		$customer = new Customer((int)$cart->id_customer);
		$currency = new Currency((int)$cart->id_currency);
		$custom = (int)Configuration::get('ATOS_ALLOW_CUSTOM');

		$ps_url = Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
		$ps_url .= __PS_BASE_URI__;

		$ps_silent_url = $this->setSilentURL() . __PS_BASE_URI__;

		$ipn_page = $ps_silent_url.'modules/'.$this->name.'/validation.php';
		$cancel_page = $ps_url.'modules/'.$this->name.'/atos_return.php';
		if (version_compare(_PS_VERSION_, '1.5', '>'))
		{
			$back_url = $ps_url.'index.php?controller=order&step=3&';
			$return_page = $ps_url.'index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->id.'&key='.$customer->secure_key.'&';
		}
		else
		{
			$back_url = $ps_url.'order.php?step=3&';
			$return_page = $ps_url.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$this->id.'&key='.$customer->secure_key.'&';
		}
		$redirect = Configuration::get('ATOS_REDIRECT') ? ' data=NO_RESPONSE_PAGE_POST="'.$return_page.'"' : '';

		// for 1.3 compatibility
		if (!isset($currency->iso_code_num) || $currency->iso_code_num == '')
		{
			$array_currency_iso_num = array(
				'DKK'	=> 208,
				'EUR'	=> 978,
				'USD'	=> 840,
				'GBP'	=> 826,
				'SEK'	=> 752,
				'AUD'	=> 036,
				'CAD'	=> 124,
				'ISK'	=> 352,
				'JPY'	=> 392,
				'NZD'	=> 554,
				'NOK'	=> 578,
				'CHF'	=> 756,
				'TRY'	=> 949,
			);
			$currency_num = $array_currency_iso_num[$currency->iso_code];
		}
		else
			$currency_num = $currency->iso_code_num;

		$return = ' normal_return_url="'.$return_page.'"';
		$cancel = ' cancel_return_url="'.$cancel_page.'"';
		$auto = ' automatic_response_url="'.$ipn_page.'"';
		$ip = ' customer_ip_address='.Tools::substr($_SERVER['REMOTE_ADDR'], 0, 16);

		$capture_day = (int)Configuration::get('ATOS_CAPTURE_DAY');
		$capture_mode = (int)Configuration::get('ATOS_CAPTURE_MODE') ? 'AUTHOR_CAPTURE' : 'VALIDATION';

		$custom_tpl = '';
		if ($custom === 1)
			$custom_tpl = ' templatefile=custom_tpl';

		$parm = 'merchant_id='.Configuration::get('ATOS_MERCHANT_ID').' language='.$lang->iso_code.
		' customer_id='.(int)$cart->id_customer.
		' caddie='.(int)$cart->id.
		' merchant_country=fr amount='.(int)round(sprintf('%f', $cart->getOrderTotal() * 100)).
		' currency_code='.$currency_num.
		' capture_day='.$capture_day.
		' capture_mode='.$capture_mode.
		' pathfile="'.dirname(__FILE__).'/pathfile" '.$return.$cancel.$auto.$ip.$redirect.$custom_tpl;

		$is_win = (Tools::strtoupper(Tools::substr(PHP_OS, 0, 3)) === 'WIN');
		if (!$result = exec($this->bin_dir.'request'.(((int)$is_win === 1) ? '.exe' : '').' '.$parm))
			return $this->l('Atos error: can\'t execute binary');

		unset($cart, $lang, $customer, $currency);

		$result_array = explode('!', $result);
		if ($result_array[1] == -1)
			return $this->l('Atos error:').' '.$result_array[2];
		elseif (!isset($result_array[3]))
			return $this->l('Atos error: can\'t execute request');
		return $result_array[3];
	}

	public function hookPayment($params)
	{
		if ($params['cart']->getOrderTotal() < 1.00)
			return $this->display(__FILE__, 'payment.tpl');

		$this->context->smarty->assign('atos', $this->getAtosForm($params['cart']));
		return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
	}

	private function getLang()
	{
		if (self::$lang_cache == null && !is_array(self::$lang_cache))
		{
			self::$lang_cache = array();
			if ($languages = Language::getLanguages())
			{
				foreach ($languages as $row)
				{
					$exprow = explode(' (', $row['name']);
					$subtitle = (isset($exprow[1]) ? trim(Tools::substr($exprow[1], 0, -1)) : '');
					self::$lang_cache[$row['iso_code']] = array (
						'title' => trim($exprow[0]),
						'subtitle' => $subtitle
					);
				}
				/* Clean memory */
				unset($row, $exprow, $subtitle, $languages);
			}
		}
	}

	/**
	* Loads asset resources
	*/
	public function loadAsset()
	{
		$css_compatibility = $js_compatibility = array();

		// Load CSS
		$css = array(
			$this->css_path.'font-awesome.min.css',
			$this->css_path.'bootstrap-select.min.css',
			$this->css_path.'bootstrap-responsive.min.css',
			$this->css_path.$this->name.'.css',
		);
		if (version_compare(_PS_VERSION_, '1.6', '<'))
		{
			$css_compatibility = array(
				$this->css_path.'bootstrap.min.css',
				$this->css_path.'bootstrap.extend.css',
				$this->css_path.'font-awesome.min.css',
			);
			$css = array_merge($css_compatibility, $css);
		}
		$this->context->controller->addCSS($css, 'all');

		// Load JS
		$jss = array(
			$this->js_path.'bootstrap-select.min.js',
			$this->js_path.$this->name.'.js'
		);

		if (version_compare(_PS_VERSION_, '1.6', '<'))
		{
			$js_compatibility = array(
				$this->js_path.'bootstrap.min.js'
			);
			$jss = array_merge($jss, $js_compatibility);
		}
		$this->context->controller->addJS($jss);
		// Clean memory
		unset($jss, $css, $js_compatibility, $css_compatibility);
	}
}
			