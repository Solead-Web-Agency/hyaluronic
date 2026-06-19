<?php
/**
* ProQuality (c) All rights reserved.
*
* DISCLAIMER
*
* Do not edit, modify or copy this file.
* If you wish to customize it, contact us at addons4prestashop@gmail.com.
*
* @author    Andrei Cimpean (ProQuality) <addons4prestashop@gmail.com>
* @copyright 2015-2016 ProQuality
* @license   Do not edit, modify or copy this file
*/

if (!defined('_PS_VERSION_'))
	exit;

require_once dirname(__FILE__).'/config/config.inc.php';

class Pqshippingcostsbasedonzipcodes extends Module
{
	public $ps_version;
	public $context;
	public $hook1;
	public $hook2;
	public $hook3;
	public $module_token;
	public $countries_token;
	public $zones_token;
	public $ajax_token;
	public $employee;
	public $id_product;
	public $url;
	const SCBOZ_TOKEN = 'pqshippingcostsbasedonzipcodes';
	
	protected $config_form = false;

	public function dev()
	{
		#return false;
	}

	public static function getToken()
	{
		return Tools::hash(self::SCBOZ_TOKEN);
	}

	public static function isTokenValid($token)
	{
		#d(self::getToken());
		return (self::getToken() === $token);
	}
	
	public function __construct()
	{
		$this->name                   = 'pqshippingcostsbasedonzipcodes';
		$this->tab                    = 'shipping_logistics';
		$this->version                = '3.0.1';
		$this->author                 = 'ProQuality';
		$this->module_key             = 'b882c4efabc82f6ad9270c9b4aea84e3';
		$this->author_address		  = '0x79A346Cb657578a98e464200DFF789eE447A3e2a';
		$this->need_instance          = 0;
		$this->ps_versions_compliancy = array(
			'min' => '1.4',
			'max' => '1.7'
		);
		
		/**
		 * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
		 */
		$this->bootstrap = false;
		
		parent::__construct();
		
		$this->displayName = $this->l('Shipping Costs Based On Zipcodes Pro');

		$this->description = $this->l('Apply different shipping costs based on postal codes of your customers.');
		
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		
		$this->ps_version = (Tools::substr(_PS_VERSION_, 0, 3));

		$this->id_lang = (int)$this->context->language->id;

		$this->iso_lang = Language::getIsoById($this->id_lang);
		
		$this->id_product = '19959';

		if ($this->ps_version == '1.4')
		{
			$this->hook1 = 'backOfficeHeader';
			//$this->hook2 = 'header';
			
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');			
		}
		else
		{
			$this->hook1 = 'displayBackOfficeHeader';
			//$this->hook2 = 'displayHeader';
		}
		
		$this->context->employee = new Employee($this->context->cookie->id_employee);
		#$this->setCookie(array('id_employee' => $this->context->cookie->id_employee));
		#$this->setCookie(array('employee' => $this->objectToArray($this->context->employee)));

		$this->ajax_token = $this->getToken();
		$this->module_token = Tools::getAdminToken( 'AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)$this->context->employee->id);
		$this->countries_token = Tools::getAdminToken( 'AdminCountries'.(int)Tab::getIdFromClassName('AdminCountries').(int)$this->context->employee->id);
		$this->zones_token = Tools::getAdminToken( 'AdminZones'.(int)Tab::getIdFromClassName('AdminZones').(int)$this->context->employee->id);

		$this->dev();
		
	}
	
	/**
	 * Don't forget to create update methods if needed:
	 * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
	 */
	public function install()
	{
		$sql_array = array();
		$sql_execution = array();
		// creating table livechatpro_archive
		$sql_array[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name."_conditions` (
		`id_condition` INT(11) NOT NULL AUTO_INCREMENT,
		`id_country` INT(11) NULL DEFAULT NULL,
		`id_zone` INT(11) NULL DEFAULT NULL,
		`filter` ENUM('range','equal','starts') NULL DEFAULT NULL,
		`zipcode_min` TINYTEXT NULL,
		`zipcode_max` TINYTEXT NULL,
		`add_date` DATETIME NULL DEFAULT NULL,
		`upd_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`id_condition`),
		INDEX `id_country` (`id_country`),
		INDEX `id_zone` (`id_zone`)
	)
	COLLATE='utf8_general_ci'
	ENGINE=InnoDB
	AUTO_INCREMENT=7;";		

		#$sql_array[] = 'INSERT INTO `'._DB_PREFIX_.$this->name."_conditions` (`id_condition`, `id_country`, `id_zone`, `zipcodes_range`, `zipcode_min`, `zipcode_max`, `add_date`, `upd_date`) VALUES (1, 8, 1, 'Y', '100', '100', '2015-06-12 11:15:04', '2015-06-12 17:37:44');";

		foreach ($sql_array as $sql)
			$sql_execution[] = Db::getInstance()->Execute($sql);
		
		return parent::install() 
			&& $this->registerHook($this->hook1) 
			//&& $this->registerHook($this->hook2) 
			&& Configuration::updateValue(Tools::strtoupper($this->name).'_SETTINGS', serialize(array(
				'name' => $this->name,
				'ajax_token' => $this->ajax_token,
				'module_token' => $this->module_token,
				)))
			&& (!in_array(false, $sql_execution)); 
	}
	
	public function uninstall()
	{
		$sql_array = array();
		$sql_execution = array();

		$sql_array[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_conditions`;';

		foreach ($sql_array as $sql)
			$sql_execution[] = Db::getInstance()->Execute($sql);

		#$configuration_array = unserialize(Configuration::get(Tools::strtoupper($this->name).'_SETTINGS'));
		
		return parent::uninstall() && Configuration::deleteByName($this->name);
	}
	
	
	
	
	public function getContent()
	{
		$output = null;
		
		if (Tools::isSubmit('submit'.$this->name))
		{
			#$configuration_array = unserialize(Configuration::get(Tools::strtoupper($this->name).'_SETTINGS'));
			
			if (!$this->name || empty($this->name) || !Validate::isGenericName($this->name))
				$output .= $this->displayError($this->l('Invalid Configuration value'));
			else
			{
				Configuration::updateValue(Tools::strtoupper($this->name).'_SETTINGS', serialize(array(
					'name' => $this->name,
					'ajax_token' => $this->ajax_token,
					'module_token' => $this->module_token,
				)));
				
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		
		return $output.$this->displayForm();
	}
	
	
	
	
	public function displayForm()
	{
		$this->assignModuleVars();
		
		$employee = $this->objectToArray($this->context->employee);
		
		$country = new Country();
		$countries = $country->getCountries($this->context->language->id, true);

		$zone = new Zone();
		$zones = $zone->getZones();


		$this->context->smarty->assign(array(
			'employee' => $employee,
			'countries' => $countries,
			'zones' => $zones,
			'rss_code' => '',
			'media' => ''
		));
		
		return $this->display(dirname(__FILE__), '/views/templates/admin/index.tpl');
		
	}



	public function checkIfEmployeeIsSuperAdmin()
	{
		$result = Db::getInstance()->executeS('SELECT COUNT(*) FROM `'._DB_PREFIX_.'employee` e WHERE e.id_employee = "'.(int)$this->context->cookie->id_employee.'" AND e.id_profile = "1"');

		$res = $result[0]['COUNT(*)'];

		if (empty($res))
			return false;
		else
			return true;
	}
	
	
	public function assignModuleVars()
	{
		$module_dir                = dirname(__FILE__);
		$module_templates_back_dir = dirname(__FILE__).'/views/templates/admin/';
		#$module_templates_front_dir = dirname(__FILE__).'/views/templates/front/';
		$module_js_dir             = dirname(__FILE__).'/views/js/';
		
		#$input_value = $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/label_templates/default.tpl');
		if (Tools::substr(_PS_VERSION_, 0, 3) == '1.4')
			$module_link = 'index.php?tab=AdminModules&configure='.$this->name.'&token='.$this->module_token.'&tab_module=front_office_features&module_name='.$this->name;
		else
			$module_link = 'index.php?controller=AdminModules&configure='.$this->name.'&token='.$this->module_token.'&module_name='.$this->name;

		$doc_iso = file_exists(_PS_MODULE_DIR_.$this->name.'/readme_'.$this->iso_lang.'.pdf') ? $this->iso_lang : 'en';
		$module_url = $this->http_or_https().Tools::getShopDomain().$this->_path;

		$country = new Country();
		$countries = $country->getCountries($this->context->language->id, true);
		
		$country_ids = $country_names = $zone_ids = $zone_names = array();

		foreach ($countries as $value)
		{
			$country_ids[$value['country']] = $value['id_country'];
			$country_names[$value['id_country']] = $value['country'];
		}
		#d($country_ids);
		$zone = new Zone();
		$zones = $zone->getZones();

		foreach ($zones as $value)
		{
			$zone_ids[$value['name']] = $value['id_zone'];
			$zone_names[$value['id_zone']] = $value['name'];
		}

		$this->context->smarty->assign(array(
			'dev_modules_link' => 'http://addons.prestashop.com/'.$this->iso_lang.'/93_proquality',
			'support_link' => 'http://addons.prestashop.com/'.$this->iso_lang.'/contact-community.php?id_product='.$this->id_product,
			'doc_link' => '../modules/'.$this->name.'/readme_'.$doc_iso.'.pdf',
			'video_link' => '',			
			'countries' => Tools::jsonEncode($this->arrayToObject($country_names)),
			'zones' => Tools::jsonEncode($this->arrayToObject($zone_names)),
			'countries_ids' => Tools::jsonEncode($this->arrayToObject($country_ids)),
			'zones_ids' => Tools::jsonEncode($this->arrayToObject($zone_ids)),
			'module_version' => $this->version,
			'ps_version' => $this->ps_version,
			'module_name' => $this->name,
			'employee_is_superadmin' => ($this->checkIfEmployeeIsSuperAdmin() == true) ? 'Y' : 'N',
			'module_path' => $this->_path,
			'module_url' => $module_url,
			'module_dir' => $module_dir,
			'module_token' => $this->module_token,
			'countries_token' => $this->countries_token,
			'zones_token' => $this->zones_token,
			'ajax_token' => $this->ajax_token,
			'module_link' => $module_link,
			'module_templates_back_dir' => $module_templates_back_dir,
			'module_js_dir' => $module_js_dir,
			'db_prefix' => _DB_PREFIX_,
			'scboz_current_url' => $this->http_or_https()."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
		));
		
	}


	public function http_or_https()
	{
		return Tools::getProtocol(Tools::usingSecureMode()); 
	}


	public function hookBackOfficeHeader($params)
	{
		if (Tools::isSubmit('configure') && Tools::getValue('configure') == $this->name)
		{
			$this->assignModuleVars();

			$media = '';
			
			if ($this->ps_version == '1.4')
			{
				//jquery
				$media .= '<script type="text/javascript">$.noConflict(true);</script>';
				$media .= '<script src="'.$this->_path.'views/js/jquery/jquery-1.7.2.js"></script>';

				//module media
				//js
				$media .= '<script src="'.$this->_path.'views/js/jquery/ui/jquery-ui-1.10.4.custom.min.js"></script>';
				$media .= '<script src="'.$this->_path.'libraries/uploadify/jquery.uploadifive.min.js"></script>';
				$media .= '<script src="'.$this->_path.'libraries/datatables/datatables.min.js"></script>';
				#$media .= '<script src="'.$this->_path.'libraries/datatables/RowGrouping-1.2.9/dataTables.rowGrouping.js"></script>';
				$media .= '<script type="text/javascript">'.$this->context->smarty->fetch(dirname(__FILE__).'/views/js/assigned_vars.js').'</script>
							<script type="text/javascript">'.$this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/translations.js.tpl').'</script>
							<script src="'.$this->_path.'views/js/back.js"></script>
							<script src="'.$this->_path.'views/js/global.functions.js"></script>';
				//css
				$media .= '<link rel="stylesheet" type="text/css" href="'.$this->_path.'views/css/jquery_themes/base/jquery.ui.all.css">';
				$media .= '<link rel="stylesheet" type="text/css" href="'.$this->_path.'libraries/uploadify/uploadifive.css">';
				$media .= '<link rel="stylesheet" type="text/css" href="'.$this->_path.'views/css/back.css">';
				$media .= '<link rel="stylesheet" type="text/css" href="'.$this->_path.'libraries/datatables/datatables.min.css">';
				$media .= '<link rel="stylesheet" type="text/css" href="'.$this->_path.'views/css/fontawesome/font-awesome.min.css">';
				$media .= '
				<style>
					.ui-tabs .ui-tabs-nav li a { line-height: 30px !important; padding: 0px 10px 0px 10px !important; margin: 0px !important; }
					.ui-icon .ui-icon-close { background-image:url(../img/uploadify-cancel.png) !important; }
					.ui-dialog-buttonpane { display: block !important; }
					.ui-dialog-buttonset { display: block !important; }
				</style>';
			}
			else
			{
				//add jquery
				$this->context->controller->addJquery();
				
				//load jquery ui plugins
				$this->context->controller->addJqueryUI('ui.tabs');
				$this->context->controller->addJqueryUI('ui.dialog');
				
				//module media
				//js
				$this->context->controller->addJS($this->_path.'views/js/jquery.storageapi.plugin.min.js');
				$this->context->controller->addJS($this->_path.'libraries/uploadify/jquery.uploadifive.min.js');
				$this->context->controller->addJS($this->_path.'libraries/datatables/datatables.min.js');
				#$this->context->controller->addJS($this->_path.'libraries/datatables/RowGrouping-1.2.9/dataTables.rowGrouping.js');				
				$media .= '
				<script type="text/javascript">'.$this->context->smarty->fetch(dirname(__FILE__).'/views/js/assigned_vars.js').'</script>
            	<script type="text/javascript">'.$this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/translations.js.tpl').'</script>';
				$this->context->controller->addJS($this->_path.'views/js/back.js');
				$this->context->controller->addJS($this->_path.'views/js/global.functions.js');

				//css
				$this->context->controller->addCSS($this->_path.'views/css/back.css', 'all');
				$this->context->controller->addCSS($this->_path.'views/css/jquery_themes/bootstrap/jquery.ui.theme.css', 'all');
				$this->context->controller->addCSS($this->_path.'libraries/uploadify/uploadifive.css', 'all');
				$this->context->controller->addCSS($this->_path.'libraries/datatables/datatables.min.css', 'all');
				$this->context->controller->addCSS($this->_path.'views/css/fontawesome/font-awesome.min.css', 'all');
				
				if ($this->ps_version == '1.6')
					$this->context->controller->addJS($this->_path.'views/js/fixwidth.js');
			}

			return $media;

		} // end configure
		
		
		
	}
	
	
	public function isDemo()
	{
		$is_demo = (stristr($_SERVER['SERVER_NAME'], 'prestashopaddonsmodules.com') || stristr($_SERVER['SERVER_NAME'], '4prestashop.com')) ? true : false;
		#$is_demo = ( stristr($_SERVER['SERVER_NAME'], 'prestashopaddonsmodules.com') || stristr($_SERVER['SERVER_NAME'], '4prestashop.com') ) ? false : true;
		
		return $is_demo;
	}

	public function getCondition($data)
	{
		$result = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.$this->name.'_conditions` WHERE id_condition = "'.(int)$data['id_condition'].'"');
		
		if ($result) 
			return $result[0];
		else
			return false;
	}

	public function addCondition($data)
	{
		if ($this->isDemo() == true)
			return false;

		$country = new Country($data['id_country']);
		if ($country->id_zone == $data['id_zone'])
			return false;

		$sql = 'INSERT INTO '._DB_PREFIX_.$this->name.'_conditions 
				(
					id_country,
					id_zone,
					filter,
					zipcode_min,
					zipcode_max,
					add_date
				) 
				VALUES 
				(
					"'.pSQL($data['id_country']).'",
					"'.pSQL($data['id_zone']).'",
					"'.pSQL($data['filter']).'",
					"'.pSQL($data['zipcode_min']).'",
					"'.pSQL($data['zipcode_max']).'",
					DATE_FORMAT(NOW(),\'%Y-%m-%d %H:%i:%s\')
				)';

		Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);

		return true;
	}	


	public function validateDataTablesInfo($data = '')
	{
		if ($this->isDemo() == true)
			return 'ERROR_DEMO_MODE';

		if (!empty($data['id_country']))
		{
			$country = new Country($data['id_country']);
			if ($country->id_zone == $data['id_country'])
				return 'ERROR_SAME_ZONE';
		}

		return true;
	}


	public function fillGridDataTables($type, $data = '')
	{
		$db = '';		

		include('libraries/datatables/PHP/DataTables.php');

			if ($type == 'conditions')
			{
				return DataTables\Editor::inst( $db, _DB_PREFIX_.$this->name.'_conditions', 'id_condition')->fields(
						DataTables\Editor\Field::inst( _DB_PREFIX_.$this->name.'_conditions.id_country' )->validator( 'Validate::notEmpty' )/*->getFormatter( 
							function ( $val, $data, $opts ) { // from server for client
									$country_details = Db::getInstance()->executeS('SELECT name FROM `'._DB_PREFIX_.'country_lang` cl WHERE cl.id_country = "'.pSQL($val).'" ORDER BY cl.id_country ASC LIMIT 1');
									return $country_details[0]['name'];
								} )->setFormatter( 
							function ( $val, $data, $opts ) { // from client for server
									$country_details = Db::getInstance()->executeS('SELECT id_country FROM `'._DB_PREFIX_.'country_lang` cl WHERE cl.name = "'.pSQL($val).'" ORDER BY cl.id_country ASC LIMIT 1');
									return $country_details[0]['id_country'];
								} )*/,
						DataTables\Editor\Field::inst( _DB_PREFIX_.$this->name.'_conditions.id_zone' )->validator( 'Validate::notEmpty' )/*->getFormatter( 
							function ( $val, $data, $opts ) { // from server for client
									$zone_details = Db::getInstance()->executeS('SELECT name FROM `'._DB_PREFIX_.'zone` z WHERE z.id_zone = "'.pSQL($val).'"');
									return $zone_details[0]['name'];
								} )->setFormatter( 
							function ( $val, $data, $opts ) { // from client for server
									$zone_details = Db::getInstance()->executeS('SELECT id_zone FROM `'._DB_PREFIX_.'zone` z WHERE z.name = "'.pSQL($val).'"');
									return $zone_details[0]['id_zone'];
								} )*/,
						DataTables\Editor\Field::inst( _DB_PREFIX_.$this->name.'_conditions.filter' )->validator( 'Validate::notEmpty' ),
						DataTables\Editor\Field::inst( _DB_PREFIX_.$this->name.'_conditions.zipcode_min' )->validator( 'Validate::notEmpty' ),
						DataTables\Editor\Field::inst( _DB_PREFIX_.$this->name.'_conditions.zipcode_max' )->validator( 'Validate::notEmpty' ),
						DataTables\Editor\Field::inst( _DB_PREFIX_.$this->name.'_conditions.upd_date' )->validator( 'Validate::dateFormat', array('format'  => DataTables\Editor\Format::DATE_ISO_8601,'message' => 'Please enter a date in the format yyyy-mm-dd') )->getFormatter( 'Format::date_sql_to_format', DataTables\Editor\Format::DATE_ISO_8601 )->setFormatter( 'Format::date_format_to_sql', DataTables\Editor\Format::DATE_ISO_8601 )
					)->process( $data )->json();
			}
	}


	public function getConditions()
	{
		return Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.$this->name.'_conditions`');
	}
	



	/**
	 * Convert object to array.
	 */
	public function objectToArray($obj)
	{
		if (is_array($obj) || is_object($obj))
		{
			$result = array();

			foreach ($obj as $key => $value)
				$result[$key] = $this->objectToArray($value);

			return $result;
		}
		return $obj;
	}

	

	public function arrayToObject($array) 
	{
		$obj = new stdClass;

		foreach ($array as $k => $v) 
		{
			if (Tools::strlen($k)) 
			{
				if (is_array($v)) 
					$obj->{$k} = $this->arrayToObject($v); //RECURSION
				else 
					$obj->{$k} = $v;
			}
		}
		return $obj;
	} 
	

}