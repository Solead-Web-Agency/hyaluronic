<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2023
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class Core extends ModuleFrontController
{
    public $response;
    public $log_var;
    public function __construct()
    {
        //$this->setLog();
        $this->context = Context::getContext();
        $id_language = Configuration::get('PS_LANG_DEFAULT');
        $this->context->cookie->id_language = $id_language;
        $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        $this->context->cookie->id_currency = $id_currency;
        $id_country = Configuration::get('PS_COUNTRY_DEFAULT');
        $this->context->cookie->id_country = $id_country;
    }

    public function initContext()
    {
        $this->context = Context::getContext();
    }
    
    public function target()
    {
        $time = microtime(true);
        $response = $this->getData();

        return array(
            'type' => 'json',
            'content' => $response,
            'header' => array(
                'Access-Time: ' . time(),
                'Content-Type: text/json',
                'Powered-By: PrestaShop Fmm Webservice API',
                'Execution-Time: ' . round($time - $_SERVER["REQUEST_TIME_FLOAT"], 4)
            )
        );
    }

    public function setLog()
    {
        return true;
    }
    
    public function writeLog($error = '')
    {
        $error = $error;
        return true;
    }

    protected function fetchJSONResponse()
    {
        $response = array();
        if (!empty($this->response)) {
            $response = $this->response;
        }
        header('Content-Type: application/json');
        return $response;
    }

    abstract public function getData();
}
