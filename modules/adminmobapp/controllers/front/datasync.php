<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2024
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMobAppDataSyncModuleFrontController extends ModuleFrontController
{
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_NOT_FOUND = 404;
    private $response;
    public function init()
    {
        parent::init();

        $action = Tools::getValue('action');
        $token = Tools::getValue('token');
        $auths = Configuration::get('ADMINMOBAPP_SHOP_FCM');

        $action = filter_var($action, FILTER_SANITIZE_STRING);
        $token = filter_var($token, FILTER_SANITIZE_STRING);

        $base_url = Tools::getShopDomainSsl(true).__PS_BASE_URI__;

        if ($action === 'invoice' && hash_equals($auths, $token)) {
            $this->getInvoice();
            exit();
        }

        if ($action === 'delivery' && hash_equals($auths, $token)) {
            $this->getDelivery();
            exit();
        }
        
        try {
            $authenticate = $this->getBearerToken();
            if (!$authenticate) {
                throw new Exception('Unauthorized Token Not Found', self::HTTP_UNAUTHORIZED);
            }

            $keyIsValid = $this->authorization($authenticate);
            if (!$keyIsValid) {
                throw new Exception('Invalid Auth Token', self::HTTP_UNAUTHORIZED);
            }

            $resource = Tools::getValue('resource');
            if (empty($resource)) {
                throw new Exception('Resource not specified', self::HTTP_BAD_REQUEST);
            }

            if (!preg_match('/^[a-zA-Z0-9]+$/', $resource)) {
                throw new Exception('Invalid resource name', self::HTTP_BAD_REQUEST);
            }

            //$class = 'Api' . Tools::ucfirst($resource);
            $class = Tools::ucfirst($resource);
            $folderName = Tools::substr($class, 3, 8);
            
            $moduleControllerDir = _PS_MODULE_DIR_ . 'adminmobapp/services/' . $folderName . '/';
            $currentUrl = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            
            $class = 'Api'.$class;
            if (!file_exists($moduleControllerDir . $class . '.php')) {
                throw new Exception('Class Not Found', self::HTTP_NOT_FOUND);
            }

            require_once $moduleControllerDir . $class . '.php';
            $requestClass = Tools::ucwords($class);
            $request = new $requestClass($currentUrl);
            $response = $request->target();

            header('Content-Type: application/json');
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode());
            echo json_encode(['error' => $e->getMessage()]);
            // Log errors for debugging
            error_log($e->getMessage(), 0);
        }

        exit;
    }

    private function authorization($authenticate)
    {
        $key = Configuration::get('ADMINMOBAPP_AUTH_TOKEN', true);
        if (!is_array($key)) {
            $key = [$key];
        }
        return in_array($authenticate, $key);
    }

    private function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    private function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getInvoice()
    {
        $id_order = (int) Tools::getValue('id_order');
        $id_lang = (int) Tools::getValue('id_lang');
        $language = new Language($id_lang);
        $this->context->language = $language;
        if (!$id_order || !Validate::isUnsignedId($id_order)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Invalid or missing order ID'
            );
            return $this->fetchJSONResponse();
        }

        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Order not found'
            );
            return $this->fetchJSONResponse();
        }

        if (!$order->hasInvoice()) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'No invoice available for this order'
            );
            return $this->fetchJSONResponse();
        }

        $orderInvoices = $order->getInvoicesCollection();

        if (count($orderInvoices) === 0) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'No invoice found for this order'
            );
            return $this->fetchJSONResponse();
        }

        $pdf = new PDF($orderInvoices, PDF::TEMPLATE_INVOICE, $this->context->smarty);
        $pdf->render();
        exit;
    }

    public function getDelivery()
    {
        $id_order = (int) Tools::getValue('id_order');
        $id_lang = (int) Tools::getValue('id_lang');
        $language = new Language($id_lang);
        $this->context->language = $language;
        if (!$id_order || !Validate::isUnsignedId($id_order)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Invalid or missing order ID'
            );
            return $this->fetchJSONResponse();
        }

        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Order not found'
            );
            return $this->fetchJSONResponse();
        }

        $orderDeliver = $order->getDeliverySlipsCollection();
        if (empty($orderDeliver)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'No invoice found for this order'
            );
            return $this->fetchJSONResponse();
        }

        $pdf = new PDF($orderDeliver, PDF::TEMPLATE_DELIVERY_SLIP, Context::getContext()->smarty);
        $pdf->render();
        exit;
    }
}
