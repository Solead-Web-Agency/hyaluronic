<?php
/**
 *  Module made by Nukium
 *
 *  @author    Nukium
 *  @copyright 2023 Nukium SAS
 *  @license   All rights reserved
 *
 * ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
 * ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
 * ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
 * ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
 * ██   ████  ██████  ██   ██ ██  ██████  ██      ██
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Nukium\GLS\Common\Service\ShipIt\Adapter\Component;

use Nukium\GLS\Common\Exception\GlsApiException;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigFactory;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigInterface;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorFactory;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorInterface;
use Nukium\GLS\Common\Service\ShipIt\ShipItComponentInterface;
use Nukium\GLS\Common\Value\GlsValue;

class LabelComponent implements ShipItComponentInterface
{
    private static $instance = null;

    protected $config;

    protected $translator;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                ConfigFactory::getInstance(),
                TranslatorFactory::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        ConfigInterface $config,
        TranslatorInterface $translator
    ) {
        $this->config = $config;
        $this->translator = $translator;
    }

    public function adaptEventBefore($event)
    {
        $body = $event['body'];
        $carrierData = $this->adaptBodyCarrierData($body);

        $res = [
            'Shipment' => [
                'ShipmentReference' => $this->adaptBodyReferences($body),
                'Middleware' => $this->config->get('GLS_SHIPIT_MIDDLEWARE'),
                'Consignee' => [
                    'Address' => $this->adaptBodyAddress($body),
                ],
                'Shipper' => [
                    'ContactID' => $this->adaptBodyContactId($body),
                ],
                'ShipmentUnit' => $this->adaptBodyParcels($body),
            ],
            'PrintingOptions' => [
                'ReturnLabels' => [
                    'TemplateSet' => 'NONE',
                    'LabelFormat' => 'PDF',
                ],
            ],
        ];

        $res['Shipment']['Product'] = $carrierData['product'];

        if (isset($carrierData['service'])) {
            $res['Shipment']['Service'] = [];
            $res['Shipment']['Service'][] = $carrierData['service'];
        }

        if (isset($body['shipmentDate'])) {
            $res['Shipment']['ShippingDate'] = $body['shipmentDate'];
        }

        if (isset($body['incoterm'])) {
            $res['Shipment']['IncotermCode'] = $body['incoterm'];
        }

        $event['body'] = $res;

        return $event;
    }

    public function adaptEventAfter($event)
    {
        $data = $event['original_response']['content'];
        $result = $event['response']['content'];

        if (
            !isset($data->CreatedShipment) ||
            empty($data->CreatedShipment->ParcelData) ||
            empty($data->CreatedShipment->PrintData)
        ) {
            throw new GlsApiException($this->translator->trans(
                'An error occured , please contact technical support.'
            ));
        }

        $data = $data->CreatedShipment;
        $trackingData = $this->adaptResponseTracking($data);

        $result->labels = $this->adaptResponseLabels($data);
        $result->parcels = $trackingData['parcels'];
        $result->returns = [];
        $result->location = $trackingData['location'];

        return $event;
    }

    protected function adaptBodyReferences($body)
    {
        $res = [];

        foreach ($body['references'] as $e) {
            if (empty($e)) {
                continue;
            }

            $res[] = $e;
        }

        return $res;
    }

    protected function adaptBodyContactId($body)
    {
        $temp = explode(' ', $body['shipperId']);

        return $temp[1];
    }

    protected function adaptBodyAddress($body)
    {
        $address = $body['additional_temp_data']['customer_address'];

        $res = [
            'eMail' => $address['email'],
            'Name1' => $address['name1'],
            'Street' => $address['street1'],
            'ZIPCode' => $address['zipCode'],
            'City' => $address['city'],
            'CountryCode' => $address['country'],
        ];

        if (!empty($address['phone'])) {
            $res['FixedLinePhonenumber'] = $address['phone'];
        }

        if (!empty($address['mobile'])) {
            $res['MobilePhoneNumber'] = $address['mobile'];
        }

        if (
            isset($address['contact']) &&
            strlen($address['contact']) > 5
        ) {
            $res['ContactPerson'] = $address['contact'];
        }

        if (!empty($address['name2'])) {
            $res['Name2'] = $address['name2'];
        }

        if (!empty($address['name3'])) {
            $res['Name3'] = $address['name3'];
        }

        return $res;
    }

    protected function adaptBodyParcels($body)
    {
        $res = [];

        foreach ($body['parcels'] as $e) {
            $res[] = [
                'Weight' => $e['weight'],
            ];
        }

        return $res;
    }

    protected function adaptBodyCarrierData($body)
    {
        $labelProducts = GlsValue::LABEL_PRODUCTS;
        $labelServices = GlsValue::LABEL_SERVICES;

        $parcel = $body['parcels'][0];

        if (isset($parcel['services']) && !empty($parcel['services'])) {
            $service = $parcel['services'][0];

            $serviceInfo = null;
            if (isset($service['infos']) && !empty($service['infos'])) {
                $serviceInfo = $service['infos'][0];
            }

            if (
                $service['name'] === 'shopreturnservice' &&
                $serviceInfo !== null &&
                $serviceInfo['name'] === 'returnonly' &&
                $serviceInfo['value'] === 'Y'
            ) {
                return [
                    'product' => GlsValue::PRODUCT_PARCEL,
                    'service' => [
                        'ShopReturn' => [
                            'ServiceName' => 'service_shopreturn',
                            'NumberOfLabels' => 1,
                        ],
                    ],
                ];
            } elseif ($service['name'] === 'shopDeliveryService') {
                $parcelshopId = null;
                if ($serviceInfo !== null) {
                    $parcelshopId = $serviceInfo['value'];
                }

                if ($parcelshopId === null) {
                    throw new GlsApiException($this->translator->trans(
                        'No relay point has been selected.'
                    ));
                }

                return [
                    'product' => $labelProducts[GlsValue::GLS_RELAIS],
                    'service' => [
                        'ShopDelivery' => [
                            'ServiceName' => $labelServices[GlsValue::GLS_RELAIS],
                            'ParcelShopID' => $parcelshopId,
                        ],
                    ],
                ];
            } elseif ($service['name'] === 'flexDeliveryService') {
                return [
                    'product' => $labelProducts[GlsValue::GLS_CHEZ_VOUS_PLUS],
                    'service' => [
                        'Service' => [
                            'ServiceName' => $labelServices[GlsValue::GLS_CHEZ_VOUS_PLUS],
                        ],
                    ],
                ];
            } elseif ($service['name'] === 'express') {
                return [
                    'product' => $labelProducts[GlsValue::GLS_AVANT_13H],
                    'service' => [
                        'Service' => [
                            'ServiceName' => $labelServices[GlsValue::GLS_AVANT_13H],
                        ],
                    ],
                ];
            }
        }

        return [
            'product' => $labelProducts[GlsValue::GLS_CHEZ_VOUS],
        ];
    }

    protected function adaptResponseLabels($data)
    {
        $res = [];

        foreach ($data->PrintData as $e) {
            $res[] = $e->Data;
        }

        return $res;
    }

    protected function adaptResponseTracking($data)
    {
        $res = [
            'parcels' => [],
        ];

        $trackIds = [];

        foreach ($data->ParcelData as $e) {
            $resultElement = new \stdClass();
            $resultElement->trackId = $e->TrackID;
            $resultElement->parcelNumber = $e->ParcelNumber;
            $resultElement->location = GlsValue::TRACKING_URL . $e->TrackID;

            $res['parcels'][] = $resultElement;
            $trackIds[] = $e->TrackID;
        }

        $res['location'] = GlsValue::TRACKING_URL . implode(',', $trackIds);

        return $res;
    }
}
