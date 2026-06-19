<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
/* Is ajax/cron file */
require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../amazon.php');
require_once(dirname(__FILE__).'/../classes/AmzOrder.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_item.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.logger.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.product.class.php');
if (!defined('_PS_VERSION_')) { exit; }

/**
 * Class AmazonMerchantFulfillment
 * Use to handle ajax calls from order details.
 * Todo: Prime - Consider transform to controller
 */
class AmazonMerchantFulfillment extends Amazon
{
    const SP_API_SANDBOX = false;

    public static $get_shipping_service = 'get-shipping-service';   // Get for old / new order, which does not have any fulfillment data
    public static $create_shipment = 'create-shipment';             // Step 2, create from chosen [ShippingServiceId]
    public static $get_shipment = 'get-shipment';                   // Get exist shipment, already created

    protected $primeConfig = array();

    protected $errors = array();

    protected $debugFilePrefix = 'fulfillment';

    /** @var AmazonLogger */
    protected $log;
    
    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    public function dispatch($action, $id_order, $mkpId)
    {
        $this->log = new AmazonLogger(AmazonLogger::CHANNEL_PRIME);

        if (!$action || !$id_order || !$mkpId) {
            echo json_encode(array('success' => false, 'reason' => array('Invalid request!')));
            return;
        }

        $token = AmazonTools::getValue('instant_token');
        if (!$token || $token != Configuration::get(AmazonConstant::GB_CONFIG_INSTANT_TOKEN, null, 0, 0)) {
            echo json_encode(array('success' => false, 'reason' => array($this->l('Wrong token'))));
            return;
        }

        $amzOrder = new AmzOrder($id_order);
        if (!Validate::isLoadedObject($amzOrder)) {
            echo json_encode(array('success' => false, 'reason' => array($this->l('Unable to load order object'))));
            return;
        }

        if ($action == self::$get_shipping_service) {
            $getResult = $this->spGetShippingServices($amzOrder, $mkpId);
            $result = $this->stepDispatch($amzOrder, $getResult, AmazonOrderMkpShippingServices::STEP_GET_SHIPPING_SERVICES);
        } elseif ($action == self::$create_shipment) {
            $shipmentServiceId = AmazonTools::getValue('shipment_service_id');
            $format = AmazonTools::getValue('shipment_service_format');
            $createResult = $this->spCreateShipment($amzOrder, $mkpId, $shipmentServiceId, $format);
            $result = $this->stepDispatch($amzOrder, $createResult, AmazonOrderMkpShippingServices::STEP_CREATE_SHIPMENT);
        } else {
            $shipmentId = AmazonTools::getValue('shipment_id');
            $labelFile = $this->spGetShipment($amzOrder, $mkpId, $shipmentId);
            $result = !$labelFile
                ? array('success' => false, 'reason' => $this->errors)
                : array('success' => true, 'url' => $this->buildSpPrimeLabelUrl() . $labelFile);
        }

        echo json_encode($result);
    }

    /**
     * @param AmzOrder $amazonOrder
     * @return array
     */
    protected function stepDispatch($amazonOrder, $apiResult, $step)
    {
        if (!$apiResult || !is_array($apiResult) || !isset($apiResult['request'], $apiResult['response'])) {
            return array('success' => false, 'reason' => $this->errors);
        } else {
            AmazonOrder::updOrderMerchantFulfillment(
                $amazonOrder->id,
                $amazonOrder->amzInfo()->mp_order_id,
                json_encode($apiResult),
                $step
            );

            $primeParams = $this->buildSpPrimeParams($amazonOrder->id);
            // Return template prime.tpl
            $this->context->smarty->assign(array('prime' => $primeParams));
            $template = $this->context->smarty->fetch($this->path . 'views/templates/admin/admin_order/prime.tpl');
            return array('success' => true, 'template' => $template);
        }
    }

    /**
     * @param AmzOrder $amzOrder
     * @return array|false
     */
    public function spGetShippingServices($amzOrder, $mkpId)
    {
        $this->primeConfig = AmazonConfiguration::get(AmazonConstant::CONFIG_PRIME_SETTINGS);

        // Check the existence
        $shippingServices = $amzOrder->amzInfo()->getShippingServices();
        $existShipmentId = $shippingServices->getCreatedShipmentId();
        if ($existShipmentId) {
            $this->errors[] = "The order already have a shipment id: $existShipmentId";
            return false;
        }

        // Build request
        $psDimensionUnit = $this->unit_dimensions();
        if (!$psDimensionUnit) {
            $this->errors[] = sprintf($this->l('%s/%d: Amazon couldn\'t match this dimension unit: %s, Allow values: inches or centimeters'), basename(__FILE__), __LINE__, Configuration::get('PS_DIMENSION_UNIT'));
            return false;
        }
        $psWeightUnit = $this->unit_weight();
        if (!$psWeightUnit) {
            $this->errors[] = sprintf($this->l('%s/%d: Amazon couldn\'t match this weight unit: %s, Allow values: oz (for ounces) or g (for grams)'), basename(__FILE__), __LINE__, Configuration::get('PS_WEIGHT_UNIT'));
            return false;
        }

        $order_items = AmazonOrderItem::getAllByMpOrderIds($amzOrder->amzInfo()->mp_order_id);
        $items = array();
        $packageDimensionHeight = null;
        $packageDimensionLength = null;
        $packageDimensionWidth = null;
        $packageDimensionUnit = null;
        $weightValue = null;
        $weightUnit = null;
        foreach ($order_items as $order_item) {
            if (!isset($order_item['order_item_id']) || !$order_item['order_item_id']) {
                $this->errors[] = sprintf('%s/%d: %s [Order ID %s]', basename(__FILE__), __LINE__, $this->l('Missing order item id'), $amzOrder->id);
                return false;
            }
            if (!isset($order_item['id_product']) || !$order_item['id_product']) {
                $this->errors[] = sprintf('%s/%d: %s [Order ID %s]', basename(__FILE__), __LINE__, $this->l('Missing id_product, order id item:').$order_item['order_item_id'], $amzOrder->id);
                return false;
            }

            $id_product = (int) $order_item['id_product'];
            $idProductAttribute = $order_item['id_product_attribute'];
            $product = new Product($id_product);
            if (!Validate::isLoadedObject($product)) {
                $this->errors[] = sprintf('%s/%d: %s [Product ID %s]', basename(__FILE__), __LINE__, $this->l('Unable to load product object'), $id_product);
                return false;
            }

            // get product dimension
            if ($product->height > 0) {
                $packageDimensionHeight = (float) $product->height;
            }
            if ($product->depth > 0) {
                $packageDimensionLength = (float) $product->depth;
            }
            if ($product->width > 0) {
                $packageDimensionWidth = (float) $product->width;
            }
            if ($product->height > 0 && $product->depth > 0 && $product->width > 0) {
                $packageDimensionUnit = $psDimensionUnit;
            }

            // get product weight
            if($product->weight > 0) {
                $weightUnit = $psWeightUnit;
                $weightValue = $product->weight;
                if ($psWeightUnit == 'kg') { // kg to g
                    $weightUnit = 'g';
                    $weightValue = $product->weight * 1000;
                }
            }

            $amzProductOptions = AmazonProduct::getProductOptions($id_product, $amzOrder->id_lang, $idProductAttribute);

            // Items
            $items[] = array(
                'OrderItemId'=> $order_item['order_item_id'],
                'Quantity'=> $order_item['quantity'],
                'TransparencyCodeList' => $amzProductOptions ? array($amzProductOptions['transparencycode']) : array(),
            );
        }

        $request = new AmazonSPDefGetEligibleShipmentServicesRequest(array(
            'ShipmentRequestDetails' => array(
                'AmazonOrderId' => $amzOrder->amzInfo()->mp_order_id,
                'ShipFromAddress' => $this->getShipFromAddress($amzOrder->id_shop),
                'ShippingServiceOptions' => array(
                    'DeliveryExperience' => isset($this->primeConfig['delivery_experience'])
                        ? $this->primeConfig['delivery_experience']
                        : array_keys(AmazonSPDefShippingServiceOptions::$deliveryExpEnum)[0],
                    'CarrierWillPickUp' => isset($this->primeConfig['carrier_will_pickup'])
                        && (bool)$this->primeConfig['carrier_will_pickup'],
                ),
                'PackageDimensions' => array(
                    'Unit' => $packageDimensionUnit,
                    'Length' => $packageDimensionLength,
                    'Width' => $packageDimensionWidth,
                    'Height' => $packageDimensionHeight,
                ),
                'Weight' => array(
                    'Unit' => $weightUnit,
                    'Value' => $weightValue,
                ),
                'ItemList' => $items,
            ),
        ));
        if (!$request->validate()) {
            foreach ($request->getValidationErrors() as $errorKey => $errorMsg) {
                $this->errors[] = "`$errorKey` $errorMsg";
            }
            return false;
        }

        // Call API
        $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($mkpId);
        $primeApi = new AmazonSPAPIGetEligibleShipmentServices(
            $spMkp,
            $request,
            $this->log,
            $this->moduleFeatures->dev_mode,
            self::SP_API_SANDBOX
        );
        $primeResponse = $primeApi->apiGet();
        $this->log->debug('getShippingService() response', array(
            'id_order' => $amzOrder->id, 'mkp' => $mkpId,
            'errors' => $this->errors, 'response' => $primeResponse->getRawResponse())
        );
        if ($primeResponse->hasError()) {
            $this->errors[] = $primeResponse->getErrorMsg();
            return false;
        }
        
        return array('request' => $request, 'response' => $primeResponse->getStructuredPayload());
    }

    /**
     * @var AmzOrder $amzOrder
     * @return array|false
     */
    public function spCreateShipment($amzOrder, $mkpId, $shippingServiceId, $format)
    {
        // Validation
        if (!$shippingServiceId) {
            $this->errors[] = sprintf('%s [%s] - (%s/%d)', $this->l('Please select the Shipping Service Id'), $amzOrder->id, basename(__FILE__), __LINE__);
            return false;
        }
        if (!$format) {
            $this->errors[] = sprintf('%s [%s] - (%s/%d)', $this->l('Please select the Label Format'), $amzOrder->id, basename(__FILE__), __LINE__);
            return false;
        }
        $shippingServices = $amzOrder->amzInfo()->getShippingServices();
        if (!$shippingServices->isInitialized() || !$shippingServices->stepDoneGetEligibleShippingServices()) {
            $this->errors[] = 'Empty list of eligible shipping services. You should request them first!';
            return false;
        }
        $existShipmentId = $shippingServices->getCreatedShipmentId();
        if ($existShipmentId) {
            $this->errors[] = "The order already have a shipment id: $existShipmentId";
            return false;
        }

        // Use exchange data from previous step to create shipment
        $exchangeRequest = $shippingServices->getDataEligibleExchangeRequest();
        $exchangeResponse = $shippingServices->getDataEligibleExchangeResponse();
        $availableCarriers = array_keys($shippingServices->getDataAvailableCarriers());

        if (!$exchangeRequest || !$exchangeResponse) {
            $this->errors[] = 'Failed to load the eligible shipping services, please start over!';
            return false;
        }
        if (!in_array($shippingServiceId, $availableCarriers)) {
            $this->errors[] = 'The selected shipping service is not available!';
            return false;
        }

        // Set selected format, build request
        $exchangeRequest->ShipmentRequestDetails->ShippingServiceOptions->LabelFormat = $format;
        $request = new AmazonSPDefCreateShipmentRequest((object)array(
            'ShipmentRequestDetails' => $exchangeRequest->ShipmentRequestDetails,
            'ShippingServiceId' => $shippingServiceId,
        ));
        if (!$request->validate()) {
            foreach ($request->getValidationErrors() as $errorKey => $errorMsg) {
                $this->errors[] = "`$errorKey` $errorMsg";
            }
            return false;
        }

        // Call API
        $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($mkpId);
        $primeApi = new AmazonSPAPICreateShipment(
            $spMkp,
            $request,
            $this->moduleFeatures->dev_mode,
            self::SP_API_SANDBOX
        );
        $this->log->debug('createShipment()', array('request' => $request));
        $primeResponse = $primeApi->apiCreate();
        $this->log->debug('createShipment() response', array(
            'id_order' => $amzOrder->id,
            'errors' => $this->errors, 'response' => $primeResponse->getRawResponse(),
        ));
        if ($primeResponse->hasError()) {
            $this->errors[] = $primeResponse->getErrorMsg();
            return false;
        }

        // Generate the label
        $labelUrl = $this->generateLabel(
            $primeResponse->getStructuredPayload(),
            $amzOrder->amzInfo()->mp_order_id
        );

        return array('request' => $request, 'response' => $primeResponse->getStructuredPayload(), 'label' => $labelUrl);
    }

    /**
     * @param AmzOrder $amzOrder
     * @return false|string
     */
    protected function spGetShipment($amzOrder, $mkpId, $shipmentId)
    {
        $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($mkpId);
        $primeApi = new AmazonSPAPIGetShipment(
            $spMkp,
            $shipmentId,
            $this->moduleFeatures->dev_mode,
            self::SP_API_SANDBOX
        );
        $this->log->debug('getShipment()', array('order_id' => $amzOrder->id, 'mkp' => $mkpId, 'request' => $shipmentId));
        $primeResponse = $primeApi->apiGet();
        $this->log->debug('getShipment() response', array(
            'id_order' => $amzOrder->id,
            'mkp' => $mkpId,
            'errors' => $this->errors,
            'request' => $shipmentId,
        ));
        
        if ($primeResponse->hasError()) {
            $this->errors[] = $primeResponse->getErrorMsg();
            return false;
        }

        return $this->generateLabel(
            $primeResponse->getStructuredPayload(),
            $amzOrder->amzInfo()->mp_order_id
        );
    }

    public function unit_dimensions()
    {
        // Package Dimensions Unit values: inches or centimeters
        $dimensionUnit = AmazonTools::strtolower(preg_replace('/[^A-Za-z]/', '', Configuration::get('PS_DIMENSION_UNIT')));

        switch ($dimensionUnit) {
            case 'cm':
            case 'centimeter':
            case 'centimeters':
                return 'centimeters';
            case 'in':
            case 'inch':
            case 'inches':
                return 'inches';
        }
        
        return false;
    }

    public function unit_weight()
    {
       // Weight Unit values: oz (for ounces) or g (for grams)
        $weightUnit = AmazonTools::strtolower(preg_replace('/[^A-Za-z]/', '', Configuration::get('PS_WEIGHT_UNIT')));

        switch ($weightUnit) {
            case 'oz ':
            case 'ounce':
            case 'ounces':
                return 'oz';
            case 'g':
            case 'gram':
            case 'grams':
                return 'g';
            case 'kg':
            case 'kilogram':
            case 'kilograms':
                return 'kg';
        }

        return false;
    }
    
    /**
     * To extract document data from a compressed file.
     * - Decode the Base64-encoded string.
     * - Save the decoded string with a “.gzip” extension.
     * - Extract the PDF, PNG, or ZPL file from the GZIP file.
     * @param AmazonSPDefPrimeShipment $shipment
     * @param string $mpOrderId
     * @return false|string
     */
    public function generateLabel($shipment, $mpOrderId)
    {
        // Parse format
        if ($shipment->Label->LabelFormat) {
            $fileFormat = AmazonTools::strtolower($shipment->Label->LabelFormat);
        } else {
            switch (AmazonTools::strtolower($shipment->Label->FileContents->FileType)) {
                case 'application/pdf' :
                    $fileFormat = 'pdf';
                    break;
                case 'application/zpl' :
                    $fileFormat = 'zpl';
                    break;
                default :
                    $fileFormat = 'png';
                    break;
            }
        }

        ini_set("zlib.output_compression", 1); 
        
        // File path
        $file_path = dirname(__FILE__).'/../labels/';
        
        if (!is_dir($file_path)) {
            if (!@mkdir($file_path)) {
                $this->pdd('Unable to create directory: ' . $file_path, __LINE__, true);
                $this->log->error('Unable to create directory: ' . $file_path);
                $this->errors[] = $this->l('Unable to create directory') . ': ' . ($file_path);
                return (false);
            }
            if (!@chmod($file_path, 0777)) {
                $this->pdd('Unable to set permission on directory: ' . $file_path, __LINE__, true);
                $this->log->error('Unable to set permission on directory: ' . $file_path);
            }
        }
        
        // File name
        $file_name = "label-$mpOrderId.$fileFormat";

        // 1. Decode the Base64-encoded string.
        $content = base64_decode($shipment->Label->FileContents->Contents); //TODO: Validation: Use to evaluate base64 encoded values, required

        // 2. Save the decoded string with a ".gzip" extension.
        if(!empty($content)) {
            if(file_exists($file_path . $file_name)) { // remove old file
                unlink($file_path . $file_name);
            } 
            
            // 3. Extract the PDF, PNG, or ZPL file from the GZIP file.
            $fp = fopen($file_path . $file_name.'.gz', "w");
            fwrite($fp, $content);
            fclose($fp);
            
            exec("gzip -d ".$file_path . $file_name.".gz"); //TODO: Validation: Use to compress Amazon assets
            $url = $file_name;
            $this->pdd('Label file path: ' . $url, __LINE__, true);
            $this->log->success('Generate Label successfully.', array('url' => $url));
            return $url;
        } else {
            $this->log->error(
                'Generate Label failed! Invalid source content',
                array('decoded' => $content, 'source' => $shipment->Label->FileContents->Contents
            ));
            $this->errors[] = $this->l('Generate Label failed!') .' ' . $this->l('Invalid content');
            return (false);
        }
    }

    private function getShipFromAddress($id_shop)
    {
        $primeConfig = $this->primeConfig;
        // Can not merge country to $address_components, because we need CountryCode (differ from other components)
        $id_shop_country = Configuration::get('PS_SHOP_COUNTRY_ID', null, null, $id_shop);
        $fallback_country = new Country($id_shop_country);
        $country_code = isset($primeConfig['country']) && $primeConfig['country'] ? $primeConfig['country'] : $fallback_country->iso_code;
        $address = array('CountryCode' => AmazonTools::strtoupper($country_code));

        $address_components = array(
            array('ak' => 'Name',         'pk' => 'shop_name', 'ps' => 'PS_SHOP_NAME',  'r' => 1),
            array('ak' => 'AddressLine1', 'pk' => 'address1',  'ps' => 'PS_SHOP_ADDR1', 'r' => 1),
            array('ak' => 'AddressLine2', 'pk' => 'address2',  'ps' => 'PS_SHOP_ADDR2', 'r' => 0),
            array('ak' => 'City',         'pk' => 'city',      'ps' => 'PS_SHOP_CITY',  'r' => 1, 'l' => 30),
            array('ak' => 'PostalCode',   'pk' => 'postcode',  'ps' => 'PS_SHOP_CODE',  'r' => 1),
            array('ak' => 'Email',        'pk' => 'email',     'ps' => 'PS_SHOP_EMAIL', 'r' => 1),
            array('ak' => 'Phone',        'pk' => 'phone',     'ps' => 'PS_SHOP_PHONE', 'r' => 1),
            // Optional, don't need that much details
//            array('ak' => 'AddressLine3', 'pk' => 'address3',  'ps' => 'PS_SHOP_ADDR3', 'r' => 0),
//            array('ak' => 'DistrictOrCounty',       'pk' => 'dis_ct',    'ps' => 'PS_SHOP_COUNTRY', 'r' => 0),
//            array('ak' => 'StateOrProvinceCode',    'pk' => 'st_prov',   'ps' => 'PS_SHOP_CODE', 'r' => 0),
        );
        foreach ($address_components as $component) {
            $amazon_key = $component['ak'];
            $prime_key = $component['pk'];
            $ps_default_key = $component['ps'];
            $value = isset($primeConfig[$prime_key]) && $primeConfig[$prime_key]
                ? $primeConfig[$prime_key] : Configuration::get($ps_default_key, null, null, $id_shop);
            // Truncate component if it has length limit
            if (isset($component['l']) && $component['l'] > 0) {
                $value = AmazonTools::substr($value, 0, $component['l']);
            }
            if ($component['r'] || $value) {
                $address[$amazon_key] = $value;
            }
        }

        return $address;
    }
}

$action = Tools::getValue('action');
$idOrder = Tools::getValue('id_order');
$mkpId = Tools::getValue('marketplace_id');
if ($action && $idOrder) {
    $amazonMerchantFulfillment = new AmazonMerchantFulfillment();
    $amazonMerchantFulfillment->dispatch($action, $idOrder, $mkpId);
}
