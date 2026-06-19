<?php

class AdminProductsExportController extends ModuleAdminController
{

  public function __construct()
  {
    parent::__construct();
    if (Tools::getValue('secure_key') !== false) {
      if( Tools::getValue('secure_key') == Configuration::getGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_TASKS_KEY') ){
        $this->_automaticExport();
        die;
      }
      else{
        die('Invalid secure_key');
      }
    }
  }

  private function _automaticExport()
  {
    $this->_sendCallback();
    ob_start();

    $this->_runTasks();

    ob_end_clean();
  }

  private function _runTasks()
  {
    $id_shop = (int)Tools::getValue('id_shop');
    $id_shop_group = (int)Tools::getValue('id_shop_group');

    $sql = '
      SELECT * 
      FROM ' . _DB_PREFIX_ . 'productsexport_tasks as t
      WHERE id_shop = ' . $id_shop . '
      AND id_shop_group = ' . $id_shop_group . '
      AND active = 1
    ';

    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if( $res ){
      foreach( $res as $task ){
        if ($this->_shouldBeExecuted($task) == true) {
          $this->_updateTaskStatus($task['id_task'], $task['one_shot']);
          $this->_runTask($task);
          $this->_updateTaskStatus($task['id_task'], $task['one_shot'], true);
        }
      }
    }
  }

  private function _shouldBeExecuted($task)
  {
    $hour = ($task['hour'] == -1) ? date('H') : $task['hour'];
    $day = ($task['day'] == -1) ? date('d') : $task['day'];
    $month = ($task['month'] == -1) ? date('m') : $task['month'];
    $day_of_week = ($task['day_of_week'] == -1) ? date('D') : date('D', strtotime('Sunday +' . $task['day_of_week'] . ' days'));

    $day = date('Y').'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
    $execution = $day_of_week.' '.$day.' '.str_pad($hour, 2, '0', STR_PAD_LEFT);
    $now = date('D Y-m-d H');

    return !(bool)strcmp($now, $execution);
  }
  
  private function _updateTaskStatus( $idTask, $oneShot, $finish = false )
  {
    if( Module::getInstanceByName('exportproducts')->checkExportRunning() ){
      return false;
    }
    if( !$finish ){
      $data = array(
        'last_start'  => time(),
        'last_finish' => ''
      );
      if( $oneShot ){
        $data['active'] = 0;
      }

      Db::getInstance(_PS_USE_SQL_SLAVE_)->update('productsexport_tasks', $data, "id_task=$idTask");
    }
    else{
      $data = array(
        'last_finish' => time()
      );

      Db::getInstance(_PS_USE_SQL_SLAVE_)->update('productsexport_tasks', $data, "id_task=$idTask");
    }
  }

  private function _runTask( $task )
  {
    $automaticLink = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/exportproducts/automatic_export.php?settings='.$task['export_settings'].'&id_shop_group='.Tools::getValue('id_shop_group').'&id_shop='.Tools::getValue('id_shop').'&id_lang='.Context::getContext()->language->id.'&secure_key='.Configuration::getGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_TASKS_KEY').'&id_task='.$task['id_task'];
    $stream_context = stream_context_create(array('http' => array('timeout' => 3600, 'max_redirects' => 99999)));
    Tools::file_get_contents($automaticLink, false, $stream_context, 3600);
  }

  private function _sendCallback()
  {
    ignore_user_abort(true);
    set_time_limit(0);

    ob_start();
    echo 'Tasks run';
    header('Connection: close');
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
    ob_flush();
    flush();

    if (function_exists('fastcgi_finish_request')) {
      fastcgi_finish_request();
    }
  }

  public function ajaxProcessSubscribe()
  {
    try{
      $json = array();
      $url = 'https://myprestamodules.com/modules/mpm_newsletters/send.php?newsletter=true&ajax=true&email='.pSQL(Tools::getValue('email'));

      $res = Tools::file_get_contents($url);
      die($res);
    }
    catch(Exception $e){
      $json['error'] = $e->getMessage();
      die(Tools::jsonEncode($json));
    }
  }

  public function ajaxProcessCheckVersion()
  {
    try{
      $json = array();
      $url = 'https://myprestamodules.com/modules/mpm_newsletters/send.php?get_module_version=true&ajax=true&module=37';

      $res = Tools::file_get_contents($url);

      if( $res ){
        $version = Tools::jsonDecode($res);
        $version = $version->module_version;
        Configuration::updateGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_VERSION', $version);
      }

      die($res);
    }
    catch(Exception $e){
      $json['error'] = $e->getMessage();
      die(Tools::jsonEncode($json));
    }
  }

}