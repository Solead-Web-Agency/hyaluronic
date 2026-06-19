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
if (!defined('_PS_VERSION_')) { exit; }

class AmazonOrderMkpShippingServices
{
    const STEP_GET_SHIPPING_SERVICES = 1;
    const STEP_CREATE_SHIPMENT = 2;

    protected $process_step;
    // decoded data straight from DB
    protected $shipping_services;
    /** @var array */
    protected $errors = array();

    /** @var AmazonSPDefGetEligibleShipmentServicesRequest */
    protected $step1Request;
    /** @var AmazonSPDefGetEligibleShipmentServicesResult */
    protected $step1Response;
    /** @var AmazonSPDefPrimeShipment */
    protected $step2Response;
    protected $step2LabelFileName;

    protected $initialized = false;

    public function __construct($idOrder)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . Amazon::TABLE_MARKETPLACE_SHIPPING_SERVICE . '` WHERE `id_order` = ' . (int)$idOrder;
        $result = Db::getInstance()->getRow($sql);

        if ($result) {
            $this->initialized = true;
            $this->process_step = (int)$result['process_step'];
            $shippingServices = json_decode($result['shipping_services']);
            if ($shippingServices) {
                $this->shipping_services = $shippingServices;
            }
            $errors = json_decode($result['errors'], true);
            if ($errors) {
                $this->errors = $errors;
            }
        }
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function stepDoneGetEligibleShippingServices()
    {
        return $this->process_step === self::STEP_GET_SHIPPING_SERVICES;
    }

    public function stepDoneCreateShipment()
    {
        return $this->process_step === self::STEP_CREATE_SHIPMENT;
    }

    public function getDataEligibleExchangeRequest()
    {
        return $this->readEligibleServices()->step1Request;
    }

    public function getDataEligibleExchangeResponse()
    {
        return $this->readEligibleServices()->step1Response;
    }

    public function getCreatedShipmentId()
    {
        $response = $this->readCreatedShipment()->step2Response;
        if ($response) {
            return $response->ShipmentId;
        }

        return null;
    }

    public function getLabelFileName()
    {
        return $this->readCreatedShipment()->step2LabelFileName;
    }

    protected function readEligibleServices()
    {
        if ($this->stepDoneGetEligibleShippingServices()) {
            if (isset($this->shipping_services->request, $this->shipping_services->response)) {
                $request = $this->shipping_services->request;
                $response = $this->shipping_services->response;
                if ($request instanceof AmazonSPDefGetEligibleShipmentServicesRequest) {
                    $this->step1Request = $request;
                } elseif ($request) {
                    $this->step1Request = new AmazonSPDefGetEligibleShipmentServicesRequest($request);
                }

                if ($response instanceof AmazonSPDefGetEligibleShipmentServicesResult) {
                    $this->step1Response = $response;
                } elseif ($response) {
                    $this->step1Response = new AmazonSPDefGetEligibleShipmentServicesResult($response);
                }
            }
        }

        return $this;
    }

    protected function readCreatedShipment()
    {
        if ($this->stepDoneCreateShipment()) {
            if (isset($this->shipping_services->response)) {
                $response = $this->shipping_services->response;
                if ($response instanceof AmazonSPDefPrimeShipment) {
                    $this->step2Response = $response;
                } elseif ($response) {
                    $this->step2Response = new AmazonSPDefPrimeShipment($response);
                }
            }
            if (isset($this->shipping_services->label) && $this->shipping_services->label) {
                $this->step2LabelFileName = $this->shipping_services->label;
            }
        }

        return $this;
    }

    public function getDataAvailableCarriers()
    {
        $carriers = array();
        if ($this->stepDoneGetEligibleShippingServices()) {
            foreach ($this->readEligibleServices()->step1Response->ShippingServiceList as $service) {
                $carriers[$service->ShippingServiceId] = array(
                    'carrier_name' => $service->CarrierName,
                    'carrier_rate' => $service->Rate->Amount,
                    'carrier_currency' => $service->Rate->CurrencyCode,
                    'label_formats' => $service->AvailableLabelFormats,
                );
            }
        }

        return $carriers;
    }

    public function paramsForTemplate($url)
    {
        return array(
            'step' => $this->process_step,
            'has_error' => !empty($this->errors),
            'errors' => $this->errors,
            // Step 1 result
            'carriers' => $this->getDataAvailableCarriers(),
            // Step 2 result
            'shipment_id' => $this->getCreatedShipmentId(),
            'label_url' => $url . $this->getLabelFileName(),
        );
    }
}
