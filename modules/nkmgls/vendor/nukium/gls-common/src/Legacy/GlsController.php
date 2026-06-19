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

namespace Nukium\GLS\Common\Legacy;

use Nukium\GLS\Common\Service\Handler\Legacy\GlsControllerHandler;
use Nukium\GLS\Common\Value\GlsValue;

class GlsController
{
    protected $soap_location;

    protected $ws_login = '';

    protected $ws_pwd = '';

    protected $soap_client = null;

    public static function createInstance($_params = null)
    {
        $glsControllerHandler = GlsControllerHandler::getInstance();

        return $glsControllerHandler->create($_params);
    }

    public function __construct(
        $_params = null
    ) {
        $this->soap_location = GlsValue::LEGACY_RELAY_API;

        if (!is_null($_params)) {
            $this->ws_login = $_params['GLS_WSLOGIN'];
            $this->ws_pwd = $_params['GLS_WSPWD'];
        }
    }

    public function checkAuth()
    {
        return $this->searchRelay('34000');
    }

    public function searchRelay(
        $_cp,
        $_city = '',
        $_country = 'FR',
        $_street = '',
        $options = []
    ) {
        $soapclient = $this->getSoapClient();

        if (!$soapclient) {
            return null;
        }

        $credentials = ['UserName' => $this->ws_login, 'Password' => $this->ws_pwd];
        $address = ['ZipCode' => $_cp, 'Country' => $_country, 'City' => $_city, 'Name1' => '', 'Street1' => $_street];
        $params = ['Credentials' => $credentials, 'Address' => $address];

        try {
            $response = $soapclient->GetParcelShops($params);

            if (
                !empty($response)
                && isset($response->exitCode->ErrorCode)
                && ((int) $response->exitCode->ErrorCode == 998 || (int) $response->exitCode->ErrorCode == 999)
            ) {
                $address = ['ZipCode' => $_cp, 'Country' => $_country, 'City' => '', 'Name1' => '', 'Street1' => ''];
                $params = ['Credentials' => $credentials, 'Address' => $address];
                $response = $soapclient->GetParcelShops($params);
            }
        } catch (\SoapFault $e) {
            return null;
        }

        return $this->handleSearchRelay($response, $options);
    }

    public function getRelayDetail($_id)
    {
        $soapclient = $this->getSoapClient();

        if ($soapclient) {
            $credentials = ['UserName' => $this->ws_login, 'Password' => $this->ws_pwd];
            $params = ['ParcelShopId' => $_id, 'Credentials' => $credentials];

            try {
                $response = $soapclient->GetParcelShopById($params);
            } catch (\SoapFault $e) {
                return false;
            }

            return $this->handleRelayDetail($_id, $response);
        }

        return false;
    }

    protected function handleSearchRelay($response, $options)
    {
        if (empty($response)) {
            return null;
        }

        $count = 0;
        $onlyXl = (isset($options['only_xl']))
            ? $options['only_xl']
            : false
        ;

        $callback = function ($v) use (&$count, $onlyXl) {
            if (
                !property_exists($v, 'Parcelshop') ||
                !property_exists($v->Parcelshop, 'ParcelShopId') ||
                !property_exists($v->Parcelshop, 'Address') ||
                !property_exists($v->Parcelshop, 'GLSCoordinates') ||
                !property_exists($v->Parcelshop->Address, 'Name1') ||
                !property_exists($v->Parcelshop->Address, 'Street1') ||
                !property_exists($v->Parcelshop->Address, 'ZipCode') ||
                !property_exists($v->Parcelshop->Address, 'City') ||
                !property_exists($v->Parcelshop->GLSCoordinates, 'Latitude') ||
                !property_exists($v->Parcelshop->GLSCoordinates, 'Longitude')
            ) {
                return false;
            }

            $namePrefix = strtoupper(substr($v->Parcelshop->Address->Name1, -2));

            if ($onlyXl && $namePrefix !== GlsValue::RELAY_XL_PREFIX) {
                return false;
            }

            if ($count >= GlsValue::LIMIT_RELAY) {
                return false;
            }

            ++$count;

            return true;
        };

        if (
            isset($response->SearchResults) &&
            is_array($response->SearchResults)
        ) {
            $response->SearchResults = array_filter(
                $response->SearchResults,
                $callback
            );
            $response->SearchResults = array_values($response->SearchResults);
        }

        return $response;
    }

    protected function handleRelayDetail($_id, $response)
    {
        if (!empty($response)) {
            if ($response->ExitCode->ErrorCode == 0) {
                return [
                    'originalResponse' => $response,
                    'parcelShopById' => $_id,
                    'Name1' => $response->ParcelShop->Address->Name1,
                    'Street1' => $response->ParcelShop->Address->Street1,
                    'ZipCode' => $response->ParcelShop->Address->ZipCode,
                    'City' => $response->ParcelShop->Address->City,
                    'Country' => $response->ParcelShop->Address->Country,
                    'Phone' => $response->ParcelShop->Phone->Contact,
                    'Mobile' => $response->ParcelShop->Mobile->Contact,
                    'email' => $response->ParcelShop->Email,
                    'url' => $response->ParcelShop->URL,
                    'latitude' => $response->ParcelShop->GLSCoordinates->Latitude,
                    'longitude' => $response->ParcelShop->GLSCoordinates->Longitude,
                    'GLSWorkingDay' => $response->ParcelShop->GLSWorkingDay,
                    'Name2' => (!empty($response->ParcelShop->Address->Name2) ? $response->ParcelShop->Address->Name2 : ''),
                    'Name3' => (!empty($response->ParcelShop->Address->Name3) ? $response->ParcelShop->Address->Name3 : ''),
                    'ContactName' => (!empty($response->ParcelShop->Address->ContactName) ? $response->ParcelShop->Address->ContactName : ''),
                    'BlockNo1' => (!empty($response->ParcelShop->Address->BlockNo1) ? $response->ParcelShop->Address->BlockNo1 : ''),
                    'Street2' => (!empty($response->ParcelShop->Address->Street2) ? $response->ParcelShop->Address->Street2 : ''),
                    'BlockNo2' => (!empty($response->ParcelShop->Address->BlockNo2) ? $response->ParcelShop->Address->BlockNo2 : ''),
                    'Province' => (!empty($response->ParcelShop->Address->Province) ? $response->ParcelShop->Address->Province : ''),
                ];
            } else {
            }
        } else {
        }

        return false;
    }

    protected function getSoapClient()
    {
        if (!extension_loaded('soap')) {
            return false;
        }

        if (empty($this->soap_client)) {
            try {
                $this->soap_client = new \SoapClient(
                    $this->soap_location,
                    [
                        'trace' => 1,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'exceptions' => true,
                        'connection_timeout' => 15,
                    ]
                );

                $soap_client_connected = false;
                if (is_array($this->soap_client->__getFunctions())) {
                    foreach ($this->soap_client->__getFunctions() as $value) {
                        if (strpos($value, 'GetParcelShops') !== false) {
                            $soap_client_connected = true;
                            break;
                        }
                    }
                }

                if (!$soap_client_connected) {
                    return false;
                }
            } catch (\SoapFault $e) {
                return false;
            } catch (\Exception $e) {
                return false;
            }
        }

        return $this->soap_client;
    }
}
