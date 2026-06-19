<?php
	define('_PS_MODE_DEV_', false);
include_once(dirname(__FILE__).'/../../config/config.inc.php');

  if ( Tools::getValue('phpinfo') ){
    phpinfo();
    die;
  }

if( !Tools::getValue('ajax') ){
  header('HTTP/1.0 403 Forbidden');
  echo 'You are forbidden!';  die;
}

  if( !(int)Configuration::get('PS_SHOP_ENABLE') ){
    if (!in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('PS_MAINTENANCE_IP')))) {
      if( !Configuration::get('PS_MAINTENANCE_IP') ){
        Configuration::updateValue('PS_MAINTENANCE_IP', Tools::getRemoteAddr() );
      }
      else{
        Configuration::updateValue('PS_MAINTENANCE_IP', Configuration::get('PS_MAINTENANCE_IP') . ',' . Tools::getRemoteAddr());
      }
    }
  }

include_once(dirname(__FILE__).'/../../init.php');

try {
  ini_set('memory_limit', '-1');
  ini_set('max_execution_time', "0");
  @ini_set('display_errors', 'off');

  $write_fd = fopen('error.log', 'w');
  fwrite($write_fd, " ");
  fclose($write_fd);
  ini_set("log_errors", 1);
  ini_set("error_log", "error.log");

  $json = array();

  if( Tools::getValue('add_all_visible_products') !== false){
      $name_config = 'GOMAKOIL_PRODUCTS_CHECKED';
      $config = Tools::unserialize(Configuration::get($name_config,'' ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));

      if( !$config ){
          $config = array();
      }

      $product_ids = trim(Tools::getValue('product_ids'), ', ');

      if (empty($product_ids)) {
          $config = array();
      } else {
          $config = explode(',', $product_ids);
      }

      $products = serialize($config);
      Configuration::updateValue($name_config, $products, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
  }

  if( Tools::getValue('add_product') !== false){
    $name_config = 'GOMAKOIL_PRODUCTS_CHECKED';
    $config = Tools::unserialize(Configuration::get($name_config,'' ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));

    if( !$config ){
      $config = array();
    }

    if (!empty(Tools::getValue('id_product'))) {
        if (!in_array( Tools::getValue('id_product'), $config)){
            array_push($config, Tools::getValue('id_product'));
        } else{
            $key = array_search(Tools::getValue('id_product'), $config);
            if ($key !== false)
            {
                unset($config[$key]);
            }
        }
    }

    $products =serialize($config);
    Configuration::updateValue($name_config, $products, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
  }

  if( Tools::getValue('add_manufacturer') !== false){
    $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED';
    $config = Tools::unserialize(Configuration::get($name_config,'' ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
    if( !$config ){
      $config = array();
    }
    if (!in_array( Tools::getValue('id_manufacturer'), $config)){
      array_push($config, Tools::getValue('id_manufacturer'));
    }
    else{
      $key = array_search(Tools::getValue('id_manufacturer'), $config);
      if ($key !== false)
      {
        unset($config[$key]);
      }
    }
    $config = serialize($config);
    Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
  }

  if( Tools::getValue('add_supplier') !== false){
    $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED';
    $config = Tools::unserialize(Configuration::get($name_config,'' ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
    if( !$config ){
      $config = array();
    }
    if (!in_array( Tools::getValue('id_supplier'), $config)){
      array_push($config, Tools::getValue('id_supplier'));
    }
    else{
      $key = array_search(Tools::getValue('id_supplier'), $config);
      if ($key !== false)
      {
        unset($config[$key]);
      }
    }
    $config = serialize($config);
    Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
  }

  if( Tools::getValue('search_product') !== false){

    $json['products'] = Module::getInstanceByName('exportproducts')->searchProducts(Tools::getValue('search_product'), Tools::getValue('id_shop'), Tools::getValue('id_lang'));
  }

  if( Tools::getValue('search_manufacturer') !== false){
    $json['manufacturers'] = Module::getInstanceByName('exportproducts')->searchManufacturers(Tools::getValue('search_manufacturer'));
  }

  if( Tools::getValue('search_supplier') !== false){
    $json['suppliers'] = Module::getInstanceByName('exportproducts')->searchSuppliers(Tools::getValue('search_supplier'));
  }

  if( Tools::getValue('show_checked_products') !== false){
    $json['products'] = Module::getInstanceByName('exportproducts')->showCheckedProducts(Tools::getValue('id_shop'), Tools::getValue('id_lang'));
  }

  if( Tools::getValue('show_checked_manufacturers') !== false){
    $json['manufacturers'] = Module::getInstanceByName('exportproducts')->showCheckedManufacturers();
  }

  if( Tools::getValue('show_checked_suppliers') !== false){
    $json['suppliers'] = Module::getInstanceByName('exportproducts')->showCheckedSuppliers();
  }

  if( Tools::getValue('show_all_products') !== false){
    $json['products'] = Module::getInstanceByName('exportproducts')->showAllProducts(Tools::getValue('id_shop'), Tools::getValue('id_lang'));
  }

  if( Tools::getValue('show_all_manufacturers') !== false){
    $json['manufacturers'] = Module::getInstanceByName('exportproducts')->showAllManufacturers();
  }

  if( Tools::getValue('show_all_suppliers') !== false){
    $json['suppliers'] = Module::getInstanceByName('exportproducts')->showAllSuppliers();
  }

  if( Tools::getValue('export') !== false){
    if( !Tools::getValue('page_limit') ){
      if( Module::getInstanceByName('exportproducts')->checkExportRunning() ){
       throw new Exception(Module::getInstanceByName('exportproducts')->l('Other export is running now. Please wait until it will finish.', 'send'));
      }
    }

    $error_list = array();
    $name_file = false;

    if(!Tools::getValue('field')){
      $error_list[] = array('tab' => 'filter_fields', 'field' => false, 'msg' => Module::getInstanceByName('exportproducts')->l('Please select field for export', 'send'));
    }

    if( Tools::getValue('name_export_file') && !Tools::getValue('name_file') ){
      $error_list[] = array('tab' => 'export', 'field' => 'name_file', 'msg' => Module::getInstanceByName('exportproducts')->l('Please set name export file', 'send'));
    }

    if(Tools::getValue('name_export_file') && Tools::getValue('name_file')){
      $name_file = Tools::getValue('name_file');
    }

    if( Tools::getValue('price_value') !== '' && !Tools::getValue('selection_type_price') ){
      $error_list[] = array('tab' => 'filter_products', 'field' => 'selection_type_price', 'msg' => Module::getInstanceByName('exportproducts')->l('Please select sign inequality', 'send'));
    }

    if( Tools::getValue('price_value') !== '' && !Validate::isFloat( Tools::getValue('price_value')) ){
      $error_list[] = array('tab' => 'filter_products', 'field' => 'price_value',  'msg' => Module::getInstanceByName('exportproducts')->l('Please enter valid price value', 'send'));
    }

    if( Tools::getValue('quantity_value') !== '' && !Tools::getValue('selection_type_quantity') ){
      $error_list[] = array('tab' => 'filter_products', 'field' => 'selection_type_quantity',  'msg' => Module::getInstanceByName('exportproducts')->l('Please select sign inequality', 'send'));
    }

    if( Tools::getValue('quantity_value') !== '' && !Validate::isInt( Tools::getValue('quantity_value')) ){
      $error_list[] = array('tab' => 'filter_products', 'field' => 'quantity_value', 'msg' => Module::getInstanceByName('exportproducts')->l('Please enter valid quantity value', 'send'));
    }

    if(!$error_list){
      Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', '', false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      if( Tools::getValue('categories') ){
        Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', serialize(Tools::getValue('categories')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      }
      if( Tools::getValue('field') ){
        Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', serialize(Tools::getValue('field')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      }

      $more_settings = array(
        'active_products'           => Tools::getValue('active_products'),
        'inactive_products'         => Tools::getValue('inactive_products'),
        'display_headers'           => Tools::getValue('display_headers'),
        'strip_tags'                => Tools::getValue('strip_tags'),
        'round_value'               => Tools::getValue('round_value'),
        'decoration_price'          => Tools::getValue('decoration_price'),
        'separator_decimal_points'  => Tools::getValue('separator_decimal_points'),
        'currency'                  => Tools::getValue('currency'),
        'orderby'                   => Tools::getValue('orderby'),
        'orderway'                  => Tools::getValue('orderway'),
        'settings'                  => Tools::getValue('last_id'),
        'automatic'                 => Tools::getValue('automatic'),
        'not_exported'              => Tools::getValue('not_exported'),
        'ean_products'              => Tools::getValue('ean_products'),
        'delimiter_val'             => Tools::getValue('delimiter_val'),
        'seperatop_val'             => Tools::getValue('seperatop_val'),
        'feed_target'               => Tools::getValue('feed_target'),
        'ftp_server'                => Tools::getValue('ftp_server'),
        'ftp_user'                  => Tools::getValue('ftp_user'),
        'ftp_password'              => Tools::getValue('ftp_password'),
        'ftp_folder_path'           => Tools::getValue('ftp_folder_path'),
        'specific_prices_products'  => Tools::getValue('specific_prices_products'),
        'price_products'            => array(
          'price_value'          => Tools::getValue('price_value'),
          'selection_type_price' => Tools::getValue('selection_type_price')
        ),
        'quantity_products'         => array(
          'quantity_value'          => Tools::getValue('quantity_value'),
          'selection_type_quantity' => Tools::getValue('selection_type_quantity')
        ),
        'selection_type_visibility' => Tools::getValue('selection_type_visibility'),
        'selection_type_condition'  => Tools::getValue('selection_type_condition'),
        'extra_fields'              => Tools::getValue('extra_fields'),
        'edited_xml_names'              => Tools::getValue('edited_xml_names'),
      );

      include_once('export.php');
      $export = new exportProduct( Tools::getValue('id_shop'), Tools::getValue('id_lang'), Tools::getValue('format_file'), Tools::getValue('separate'), $more_settings, $name_file );
      $fileName = $export->exportProducts( Tools::getValue('page_limit') );
      if( is_int($fileName) ){
        $json['page_limit'] = $fileName;
      }
      else{
        if( Tools::getValue('feed_target') == 'ftp' ){
          $json['success'] = Module::getInstanceByName('exportproducts')->l('Exported file successfully uploaded on your FTP Server!', 'send');
        }
        else{
          $json['file'] = $fileName;
          $json['success'] = Module::getInstanceByName('exportproducts')->l('Data successfully saved!', 'send');
          $json['module_url'] = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/exportproducts/';  
        }
      }
    }
    else{
      $json['error_list'] = $error_list;
    }

  }

  if ( Tools::getValue('returnCount') == true){

    $versionDateId = Configuration::getIdByName('GOMAKOIL_PRODUCTS_EXPORT_VERSION', 0 ,0);
    $needUpdate = false;
    if( $versionDateId ){
      $versionConf = new Configuration($versionDateId);
      if(( time()-strtotime($versionConf->date_upd) ) > ( 10*24*3600 ) ){
        $needUpdate = true;
      }
    }

    if( !$versionDateId || $needUpdate ){
      $url = 'https://myprestamodules.com/modules/mpm_newsletters/send.php?get_module_version=true&ajax=true&module=37';

      $res = Tools::file_get_contents($url);

      if( $res ){
        $version = Tools::jsonDecode($res);
        $version = $version->module_version;
        Configuration::updateGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_VERSION', $version);
      }

      if( $versionDateId ){
        $versionConf->date_upd = date('Y-m-d H:i:s');
        $versionConf->update();
      }
    }

    $productsCount = Configuration::getGlobalValue('EXPORT_PRODUCTS_COUNT');
    $currentExportedProducts = Configuration::getGlobalValue('EXPORT_PRODUCTS_CURRENT_COUNT');
    if( ((int)$currentExportedProducts) ){
      $json['export_notification'] = Module::getInstanceByName('exportproducts')->l('Successfully exported ' . $currentExportedProducts . ' from ' . $productsCount . ' items', 'send');
    }
    else{
      $json['export_notification'] = Configuration::getGlobalValue('EXPORT_PRODUCTS_CURRENT_COUNT');
    }
  }

  if( Tools::getValue('saveSettings') !== false){
    $error_list = array();
    if(!Tools::getValue('field')){
      $error_list[] = array('tab' => 'filter_fields', 'field' => false, 'msg' => Module::getInstanceByName('exportproducts')->l('Please select field for export', 'send'));
    }

    if( !Tools::getValue('save_setting') ){
      $error_list[] = array('tab' => 'new_settings', 'field' => 'save_setting', 'msg' => Module::getInstanceByName('exportproducts')->l('Please enter settings name!', 'send'));
    }
    if( Tools::getValue('automatic') && !Tools::getValue('notification_emails') ){
//      $error_list[] = array('tab' => 'automatic_export', 'field' => 'notification_emails', 'msg' => Module::getInstanceByName('exportproducts')->l('Please enter at least one email for Automatic Products Export Notification', 'send'));
    }

    if( Tools::getValue('name_export_file') && !Tools::getValue('name_file') ){
      $error_list[] = array('tab' => 'export', 'field' => 'name_file', 'msg' => Module::getInstanceByName('exportproducts')->l('Please set name export file', 'send'));
    }

    if( Tools::getValue('price_value') !== '' && !Tools::getValue('selection_type_price') ){
      $error_list[] = array('tab' => 'filter_products', 'field' => 'selection_type_price', 'msg' => Module::getInstanceByName('exportproducts')->l('Please select sign inequality', 'send'));
    }

    if( Tools::getValue('price_value') !== '' && !Validate::isFloat( Tools::getValue('price_value')) ){
      $error_list[] = array('tab' => 'filter_products', 'field' => 'price_value',  'msg' => Module::getInstanceByName('exportproducts')->l('Please enter valid price value', 'send'));
    }

    if( Tools::getValue('quantity_value') !== '' && !Tools::getValue('selection_type_quantity') ){
      $error_list[] = array('tab' => 'filter_products', 'field' => 'selection_type_quantity',  'msg' => Module::getInstanceByName('exportproducts')->l('Please select sign inequality', 'send'));
    }

    if( Tools::getValue('quantity_value') !== '' && !Validate::isInt( Tools::getValue('quantity_value')) ){
      $error_list[] = array('tab' => 'filter_products', 'field' => 'quantity_value', 'msg' => Module::getInstanceByName('exportproducts')->l('Please enter valid quantity value', 'send'));
    }

    if(!$error_list) {
      $name_config = 'GOMAKOIL_NAME_SETTING_' . Tools::getValue('last_id');
      $config = Configuration::get($name_config, '', Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      if ($config && $config !== Tools::getValue('save_setting')) {
        $all_setting = Tools::unserialize(Configuration::get('GOMAKOIL_ALL_SETTINGS', '', Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
        if ($all_setting) {
          $all_setting = max($all_setting);
          $id = $all_setting + 1;
        }
      }
      else {
        $id = Tools::getValue('last_id');
      }

      $automaticSettings = array(
        'automatic' => Tools::getValue('automatic'),
        'notification_emails' => Tools::getValue('notification_emails'),
      );

      $priceSettings = array(
        'price_value' => Tools::getValue('price_value'),
        'selection_type_price' => Tools::getValue('selection_type_price'),
      );

      $quantitySettings = array(
        'quantity_value' => Tools::getValue('quantity_value'),
        'selection_type_quantity' => Tools::getValue('selection_type_quantity'),
      );

      Configuration::updateValue('GOMAKOIL_PRODUCTS_VISIBILITY_' . $id, serialize(Tools::getValue('selection_type_visibility')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_PRODUCTS_CONDITION_' . $id, serialize(Tools::getValue('selection_type_condition')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_PRODUCTS_PRICE_' . $id, serialize($priceSettings), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_PRODUCTS_QUANTITY_' . $id, serialize($quantitySettings), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_PRODUCTS_AUTOMATIC_EXPORT_' . $id, serialize($automaticSettings), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_SEPARATE_SETTING_EX_' . $id, Tools::getValue('separate'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_NAME_SETTING_' . $id, Tools::getValue('save_setting'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_DISPLAY_HEADERS_' . $id, Tools::getValue('display_headers'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_ORDER_BY_' . $id, Tools::getValue('orderby'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_ORDER_WAY_' . $id, Tools::getValue('orderway'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_DESIMAL_POINTS_' . $id, Tools::getValue('round_value'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_STRIP_TAGS_' . $id, Tools::getValue('strip_tags'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_NOT_EXPORDED_' . $id, Tools::getValue('not_exported'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_DECORATION_PRICE_' . $id, Tools::getValue('decoration_price'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_DECIMAL_PRICE_' . $id, Tools::getValue('separator_decimal_points'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_CURRENCY_' . $id, Tools::getValue('currency'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_ACTIVE_PRODUCTS_SETTING_' . $id, Tools::getValue('active_products'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_INACTIVE_PRODUCTS_SETTING_' . $id, Tools::getValue('inactive_products'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_EAN_PRODUCTS_SETTING_' . $id, Tools::getValue('ean_products'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_SPECIFIC_PRICES_PRODUCTS_SETTING_' . $id, Tools::getValue('specific_prices_products'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_SHOW_NAME_FILE_' . $id, Tools::getValue('name_export_file'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_NAME_FILE_' . $id, Tools::getValue('name_file'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_CSV_DELIMITER_' . $id, Tools::getValue('delimiter_val'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_CSV_SEPERATOR_' . $id, Tools::getValue('seperatop_val'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      
      Configuration::updateValue('GOMAKOIL_FEED_TARGET_' . $id, Tools::getValue('feed_target'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_FTP_SERVER_' . $id, Tools::getValue('ftp_server'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_FTP_USER_' . $id, Tools::getValue('ftp_user'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_FTP_PASSWORD_' . $id, Tools::getValue('ftp_password'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_FTP_FOLDER_PATH_' . $id, Tools::getValue('ftp_folder_path'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $config = Configuration::get('GOMAKOIL_PRODUCTS_CHECKED', '', Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $name_config = 'GOMAKOIL_PRODUCTS_CHECKED_' . $id;
      Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      $config = Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED', '', Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED_' . $id;
      Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      $config = Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED', '', Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED_' . $id;
      Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $name_config = 'GOMAKOIL_CATEGORIES_CHECKED_' . $id;
      Configuration::updateValue($name_config, serialize(Tools::getValue('categories')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $name_config = 'GOMAKOIL_FIELDS_CHECKED_' . $id;
      Configuration::updateValue($name_config, serialize(Tools::getValue('field')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $name_config = 'GOMAKOIL_EXTRA_FIELDS_' . $id;
      Configuration::updateValue($name_config, serialize(Tools::getValue('extra_fields')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $name_config = 'GOMAKOIL_EDITED_XML_NAMES_' . $id;
      Configuration::updateValue($name_config, serialize(Tools::getValue('edited_xml_names')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $name_config = 'GOMAKOIL_LANG_CHECKED_' . $id;
      Configuration::updateValue($name_config, Tools::getValue('id_lang'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $name_config = 'GOMAKOIL_TYPE_FILE_' . $id;
      Configuration::updateValue($name_config, Tools::getValue('format_file'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      $settings = array();
      $settings = Tools::unserialize(Configuration::get('GOMAKOIL_ALL_SETTINGS', '', Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));

      if ($settings) {
        if (!in_array($id, $settings)) {
          $settings[] = $id;
          $settings = serialize($settings);
          Configuration::updateValue('GOMAKOIL_ALL_SETTINGS', $settings, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        }
      }
      else {
        $settings[] = $id;
        $settings = serialize($settings);
        Configuration::updateValue('GOMAKOIL_ALL_SETTINGS', $settings, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

      }

      $automatic = Tools::getValue('automatic');
      $not_exported = Tools::getValue('not_exported');
      if (isset($automatic) && $automatic && isset($not_exported) && $not_exported) {
        Db::getInstance()->delete('exported_products', 'id_setting=' . (int)$id);
      }

      $json['id'] = $id;
    }
    else{
      $json['error_list'] = $error_list;
    }
  }

  if( Tools::getValue('removeSetting') !== false){
    $id = Tools::getValue('id');
    Db::getInstance()->delete('exported_products', 'id_setting='.(int)$id);
    Configuration::deleteByName('GOMAKOIL_PRODUCTS_CHECKED_'.$id);
    Configuration::deleteByName('GOMAKOIL_MANUFACTURERS_CHECKED_'.$id);
    Configuration::deleteByName('GOMAKOIL_SUPPLIERS_CHECKED_'.$id);
    Configuration::deleteByName('GOMAKOIL_CATEGORIES_CHECKED_'.$id);
    Configuration::deleteByName('GOMAKOIL_FIELDS_CHECKED_'.$id);
    Configuration::deleteByName('GOMAKOIL_EXTRA_FIELDS_'.$id);
    Configuration::deleteByName('GOMAKOIL_EDITED_XML_NAMES_'.$id);
    Configuration::deleteByName('GOMAKOIL_LANG_CHECKED_'.$id);
    Configuration::deleteByName('GOMAKOIL_TYPE_FILE_'.$id);
    Configuration::deleteByName('GOMAKOIL_NAME_SETTING_'.$id);
    Configuration::deleteByName('GOMAKOIL_SEPARATE_SETTING_EX_'.$id);
    Configuration::deleteByName('GOMAKOIL_PRODUCTS_AUTOMATIC_EXPORT_'.$id);
    Configuration::deleteByName('GOMAKOIL_DISPLAY_HEADERS_'.$id);
    Configuration::deleteByName('GOMAKOIL_DESIMAL_POINTS_'.$id);
    Configuration::deleteByName('GOMAKOIL_CURRENCY_'.$id);
    Configuration::deleteByName('GOMAKOIL_ORDER_BY_'.$id);
    Configuration::deleteByName('GOMAKOIL_ORDER_WAY_'.$id);
    Configuration::deleteByName('GOMAKOIL_NOT_EXPORDED_'.$id);
    Configuration::deleteByName('GOMAKOIL_DECORATION_PRICE_'.$id);
    Configuration::deleteByName('GOMAKOIL_DECIMAL_PRICE_'.$id);
    Configuration::deleteByName('GOMAKOIL_STRIP_TAGS_'.$id);
    Configuration::deleteByName('GOMAKOIL_ACTIVE_PRODUCTS_SETTING_'.$id);
    Configuration::deleteByName('GOMAKOIL_INACTIVE_PRODUCTS_SETTING_'.$id);
    Configuration::deleteByName('GOMAKOIL_STOK_PRODUCTS_SETTING_'.$id);
    Configuration::deleteByName('GOMAKOIL_EAN_PRODUCTS_SETTING_'.$id);
    Configuration::deleteByName('GOMAKOIL_SPECIFIC_PRICES_PRODUCTS_SETTING_'.$id);
    Configuration::deleteByName('GOMAKOIL_PRODUCTS_PRICE_'.$id);
    Configuration::deleteByName('GOMAKOIL_PRODUCTS_QUANTITY_'.$id);
    Configuration::deleteByName('GOMAKOIL_PRODUCTS_VISIBILITY_'.$id);
    Configuration::deleteByName('GOMAKOIL_PRODUCTS_CONDITION_'.$id);
    Configuration::deleteByName('GOMAKOIL_SHOW_NAME_FILE_'.$id);
    Configuration::deleteByName('GOMAKOIL_NAME_FILE_'.$id);
    Configuration::deleteByName('GOMAKOIL_CSV_DELIMITER_'.$id);
    Configuration::deleteByName('GOMAKOIL_CSV_SEPERATOR_'.$id);
    
    Configuration::deleteByName('GOMAKOIL_FEED_TARGET_'.$id);
    Configuration::deleteByName('GOMAKOIL_FTP_SERVER_'.$id);
    Configuration::deleteByName('GOMAKOIL_FTP_USER_'.$id);
    Configuration::deleteByName('GOMAKOIL_FTP_PASSWORD_'.$id);
    Configuration::deleteByName('GOMAKOIL_FTP_FOLDER_PATH_'.$id);

    $settings = array();
    $settings = Tools::unserialize(Configuration::get('GOMAKOIL_ALL_SETTINGS', '' ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
    if(in_array($id, $settings)){
      $key = array_search($id, $settings);
      unset ($settings[$key]);
      $settings =serialize($settings);
      Configuration::updateValue('GOMAKOIL_ALL_SETTINGS', $settings, false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
    }
    $json['success'] = true;
  }
  echo Tools::jsonEncode($json);
}
catch( Exception $e ){
  $json['error'] = $e->getMessage();
  echo Tools::jsonEncode($json);
}