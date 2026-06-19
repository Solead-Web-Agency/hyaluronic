<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 20.01.16
 * Time: 15:38
 */

//if (!defined('_PS_ADMIN_DIR_')) {
//  define('_PS_ADMIN_DIR_', getcwd());
//}
define('_PS_MODE_DEV_', false);
include(dirname(__FILE__).'/../../config/config.inc.php');
// Context::getContext()->controller = 'AdminController';

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

include(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/export.php');

try{
  checkConfig();
  if (Tools::getValue('secure_key')) {
    $secureKey = md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME'));
    if ( ( $secureKey === Tools::getValue('secure_key')) || ( Tools::getValue('secure_key') == Configuration::getGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_TASKS_KEY') ) ) {
      if( !Tools::getValue('id_lang') ){
        throw new Exception('id_lang is Empty');
      }
      if( !Tools::getValue('id_shop') ){
        throw new Exception('id_shop is Empty');
      }

      if( !Tools::getValue('id_shop_group') ){
        throw new Exception('id_shop_group is Empty');
      }

      if( !Tools::getValue('limit') ){
        if( Module::getInstanceByName('exportproducts')->checkExportRunning() ){
          Module::getInstanceByName('exportproducts')->updateProgress(Module::getInstanceByName('exportproducts')->l('Other export is running now.'));
          throw new Exception(Module::getInstanceByName('exportproducts')->l('Other export is running now. Please wait until it will finish.', 'send'));
        }
      }

      $automatic = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_AUTOMATIC_EXPORT_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')));
      if( !$automatic['automatic'] ){
        throw new Exception(Module::getInstanceByName('exportproducts')->l('You must activate the automatic products export', 'automatic_export'));
      }

      $name_file = false;
      $more_settings = array(
        'active_products' => Configuration::get('GOMAKOIL_ACTIVE_PRODUCTS_SETTING_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'inactive_products' => Configuration::get('GOMAKOIL_INACTIVE_PRODUCTS_SETTING_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'display_headers' => Configuration::get('GOMAKOIL_DISPLAY_HEADERS_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'orderby' => Configuration::get('GOMAKOIL_ORDER_BY_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'orderway' => Configuration::get('GOMAKOIL_ORDER_WAY_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'round_value' => Configuration::get('GOMAKOIL_DESIMAL_POINTS_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'strip_tags' => Configuration::get('GOMAKOIL_STRIP_TAGS_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'decoration_price' => Configuration::get('GOMAKOIL_DECORATION_PRICE_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'separator_decimal_points' => Configuration::get('GOMAKOIL_DECIMAL_PRICE_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'currency' => Configuration::get('GOMAKOIL_CURRENCY_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'settings' => Tools::getValue('settings'),
        'automatic' => $automatic['automatic'],
        'not_exported' => Configuration::get('GOMAKOIL_NOT_EXPORDED_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'ean_products' => Configuration::get('GOMAKOIL_EAN_PRODUCTS_SETTING_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'delimiter_val' => Configuration::get('GOMAKOIL_CSV_DELIMITER_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'seperatop_val' => Configuration::get('GOMAKOIL_CSV_SEPERATOR_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'specific_prices_products' => Configuration::get('GOMAKOIL_SPECIFIC_PRICES_PRODUCTS_SETTING_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'feed_target' => Configuration::get('GOMAKOIL_FEED_TARGET_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'ftp_server' => Configuration::get('GOMAKOIL_FTP_SERVER_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'ftp_user' => Configuration::get('GOMAKOIL_FTP_USER_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'ftp_password' => Configuration::get('GOMAKOIL_FTP_PASSWORD_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'ftp_folder_path' => Configuration::get('GOMAKOIL_FTP_FOLDER_PATH_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')),
        'price_products' => Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_PRICE_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'))),
        'quantity_products' => Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_QUANTITY_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'))),
        'selection_type_visibility' =>Tools::unserialize( Configuration::get('GOMAKOIL_PRODUCTS_VISIBILITY_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'))),
        'selection_type_condition' => Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_CONDITION_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'))),
        'extra_fields'              => Tools::unserialize(Configuration::get('GOMAKOIL_EXTRA_FIELDS_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'))),
        'edited_xml_names'    => Tools::unserialize(Configuration::get('GOMAKOIL_EDITED_XML_NAMES_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'))),
      );

      $current = Configuration::get('GOMAKOIL_PRODUCTS_CHECKED_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_PRODUCTS_CHECKED', $current, false, Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));

      $current = Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_MANUFACTURERS_CHECKED', $current, false, Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));

      $current = Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_SUPPLIERS_CHECKED', $current, false, Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));

      $current = Configuration::get('GOMAKOIL_CATEGORIES_CHECKED_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', $current, false, Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));

      $current = Configuration::get('GOMAKOIL_FIELDS_CHECKED_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', $current, false, Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));

      $current = Configuration::get('GOMAKOIL_LANG_CHECKED_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));
      Configuration::updateValue('GOMAKOIL_LANG_CHECKED', $current, false, Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));

      $formatFile = Configuration::get('GOMAKOIL_TYPE_FILE_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));
      $separate = Configuration::get('GOMAKOIL_SEPARATE_SETTING_EX_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));
      $show = Configuration::get('GOMAKOIL_SHOW_NAME_FILE_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));
      $name = Configuration::get('GOMAKOIL_NAME_FILE_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));

      if($show && $name){
        $name_file = $name;
      }


      $export = new exportProduct( Tools::getValue('id_shop'), Tools::getValue('id_lang'), $formatFile, $separate,$more_settings, $name_file );
      $link = $export->exportProducts(Tools::getValue('limit',0));

      if( is_int($link) ){
        Tools::redirect(Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/exportproducts/automatic_export.php?settings='.Tools::getValue('settings').'&id_shop='.Tools::getValue('id_shop').'&id_shop_group='.Tools::getValue('id_shop_group').'&id_lang='.Tools::getValue('id_lang').'&secure_key='.Tools::getValue('secure_key').'&limit='.$link.'&id_task='.Tools::getValue('id_task'));
        die;
      }

      sendEmail(false, $link);
      echo Module::getInstanceByName('exportproducts')->l('Export Report sent on your email (if you set up it in settings)!','automatic_export');
    }
    else{
      echo (Module::getInstanceByName('exportproducts')->l('Secure key is wrong','automatic_export'));
      die;
    }
  }
  else{
    echo (Module::getInstanceByName('exportproducts')->l('Secure key is wrong','automatic_export'));
    die;
  }
}
catch( Exception $e ){
  sendEmail($e->getMessage());

  echo '<strong>Error: </strong>' . $e->getMessage();
}

function checkConfig()
{
  $config = (Configuration::get('GOMAKOIL_FIELDS_CHECKED_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')));
  if( !$config ){
    echo Module::getInstanceByName('exportproducts')->l('Export Settings does not exists!', 'automatic_export');
    die;
  }
}

function sendEmail( $error = false, $link = false )
{
  $config = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_AUTOMATIC_EXPORT_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop')));
  $emails = $config['notification_emails'];
  $emails = trim($emails);
  if( !$emails ){
    return false;
  }
  $emails = explode("\n", $emails);


  $feedTarget = Configuration::get('GOMAKOIL_FEED_TARGET_'.Tools::getValue('settings'), '' ,Tools::getValue('id_shop_group'), Tools::getValue('id_shop'));

  foreach ($emails as $users_email){
    $users_email = trim($users_email);
    $mailMessage = '';
    $mailMessage .= '<div style="width: 50%; min-width: 160px;margin: 0 auto;margin-top: 40px;margin-bottom: 40px;border: 1px solid #dadada;border-radius: 6px;    ">';
    $mailMessage .= '<div style="padding: 20px;border-bottom: 1px solid #dadada;font-size: 20px;text-align: center;
        border-radius: 6px 6px 0px 0px;
        background-image: -ms-linear-gradient(top, #FFFFFF 0%, #FFFFFF 20%, #FCFCFC 40%, #FAFAFA 60%, #FAFAFA 80%, #EDEDED 100%);
        background-image: -moz-linear-gradient(top, #FFFFFF 0%, #FFFFFF 20%, #FCFCFC 40%, #FAFAFA 60%, #FAFAFA 80%, #EDEDED 100%);
        background-image: -o-linear-gradient(top, #FFFFFF 0%, #FFFFFF 20%, #FCFCFC 40%, #FAFAFA 60%, #FAFAFA 80%, #EDEDED 100%);
        background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #FFFFFF), color-stop(20, #FFFFFF), color-stop(40, #FCFCFC), color-stop(60, #FAFAFA), color-stop(80, #FAFAFA), color-stop(100, #EDEDED));
        background-image: -webkit-linear-gradient(top, #FFFFFF 0%, #FFFFFF 20%, #FCFCFC 40%, #FAFAFA 60%, #FAFAFA 80%, #EDEDED 100%);
        background-image: linear-gradient(to bottom, #FFFFFF 0%, #FFFFFF 20%, #FCFCFC 40%, #FAFAFA 60%, #FAFAFA 80%, #EDEDED 100%);">'.Module::getInstanceByName('exportproducts')->l('Products Export Report', 'automatic_export').'</div><div style="padding: 30px;font-size: 14px;">';
    if( $error ){
      $mailMessage .= '<div style="margin-bottom: 10px;"><div style="margin: 2px 10px 2px 0px;color: red"><strong>'.Module::getInstanceByName('exportproducts')->l('Error:', 'automatic_export'). '</strong> ' . $error . '</div><div style="clear: both;"></div></div>';
    }
    if( $feedTarget == 'ftp' ){
      $mailMessage .= '<div style="margin-bottom: 10px;"><div style="float: left;width: 100px;margin: 2px 10px 2px 0px;"><strong>'.Module::getInstanceByName('exportproducts')->l('Export date:', 'automatic_export').'</strong></div><div style="float: left;margin-top: 2px;"> ' . date('d/m/Y G:i:s') .'</div><div style="clear: both;"></div></div>';
      $mailMessage .= '<div style="margin-bottom: 10px;"><div style="float: left;margin: 2px 10px 2px 0px;"><strong>'.Module::getInstanceByName('exportproducts')->l('Exported file successfully uploaded on your FTP Server!', 'automatic_export').'</strong></div><div style="clear: both;"></div></div>';
    }
    else{
      if( $link ){
        $mailMessage .= '<div style="margin-bottom: 10px;"><div style="float: left;width: 100px;margin: 2px 10px 2px 0px;"><strong>'.Module::getInstanceByName('exportproducts')->l('Export date:', 'automatic_export').'</strong></div><div style="float: left;margin-top: 2px;"> ' . date('d/m/Y G:i:s') .'</div><div style="clear: both;"></div></div>';
        $mailMessage .= '<div style="margin-bottom: 10px;"><div style="float: left;width: 100px;margin: 2px 10px 2px 0px;"><strong>'.Module::getInstanceByName('exportproducts')->l('Exported file:', 'automatic_export').'</strong></div><div style="float: left;margin-top: 2px;"> <a style="color: #00aff0;" href="' . $link . '">' . $link . '</a></div><div style="clear: both;"></div></div>';
      }
    }

    $mailMessage .= '<div style="clear: both;display: block !important;"></div><div style="clear: both;display: block !important;"></div><div style="clear: both; display: block !important;">';
    $template_vars = array('{content}' => $mailMessage);
    $mail = Mail::Send(
      Configuration::get('PS_LANG_DEFAULT'),
      'notification',
      Module::getInstanceByName('exportproducts')->l('Products Export Report', 'automatic_export'),
      $template_vars,
      "$users_email",
      NULL,
      Tools::getValue('email') ? Tools::getValue('email') : NULL,
      Tools::getValue('fio') ? Tools::getValue('fio') : NULL,
      NULL,
      NULL,
      dirname(__FILE__).'/mails/');
    if( !$mail ){
      echo Module::getInstanceByName('exportproducts')->l('Can\'t send email. Please contact us!', 'automatic_export');
      die;
    }
  }
}