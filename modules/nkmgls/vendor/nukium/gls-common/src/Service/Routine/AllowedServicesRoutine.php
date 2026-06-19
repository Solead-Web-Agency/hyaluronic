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

namespace Nukium\GLS\Common\Service\Routine;

use Nukium\GLS\Common\DTO\Adapter\Address;
use Nukium\GLS\Common\DTO\Adapter\Carrier;
use Nukium\GLS\Common\DTO\GLS\AllowedServices;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigFactory;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigInterface;
use Nukium\GLS\Common\Service\Adapter\Shop\ShopFactory;
use Nukium\GLS\Common\Service\Adapter\Shop\ShopInterface;
use Nukium\GLS\Common\Service\DataLoader\AllowedServicesLoader;
use Nukium\GLS\Common\Value\GlsValue;

class AllowedServicesRoutine
{
    private static $instance = null;

    protected $config;

    protected $shop;

    protected $allowedServicesLoader;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                ConfigFactory::getInstance(),
                ShopFactory::getInstance(),
                AllowedServicesLoader::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        ConfigInterface $config,
        ShopInterface $shop,
        AllowedServicesLoader $allowedServicesLoader
    ) {
        $this->config = $config;
        $this->shop = $shop;
        $this->allowedServicesLoader = $allowedServicesLoader;
    }

    public function isAllowedCarrier(
        Carrier $carrier,
        Address $destination
    ) {
        try {
            if (!$carrier->getIsGls()) {
                return true;
            }

            $carrierCode = $carrier->getCode();
            $destinationCountryCode = $destination->getCountryCode();

            if ($destination->getIgnoreRestriction()) {
                return true;
            }

            if ((int) $this->config->get('GLS_IS_USING_SHIPIT_API') === 0) {
                if (
                    $carrierCode === GlsValue::GLS_RELAIS &&
                    $destinationCountryCode !== 'FR'
                ) {
                    return false;
                }

                return true;
            }

            if (
                $carrierCode === GlsValue::GLS_AVANT_13H &&
                $destinationCountryCode !== 'FR'
            ) {
                return false;
            }

            if (!$this->checkConstraints($destination)) {
                return false;
            }

            $allowedServices = $this->getAllowedServices($destination);

            return
                $this->isAllowedProduct($allowedServices, $carrier) &&
                $this->isAllowedService($allowedServices, $carrier)
            ;
        } catch (\Exception $e) {
            return true;
        }
    }

    protected function isAllowedProduct(
        AllowedServices $allowedServices,
        Carrier $carrier
    ) {
        $carrierCode = $carrier->getCode();
        $products = $allowedServices->getProducts();

        switch ($carrierCode) {
            case GlsValue::GLS_CHEZ_VOUS:
                return
                    in_array(GlsValue::PRODUCT_PARCEL, $products, true) ||
                    in_array(GlsValue::PRODUCT_EXPRESS, $products, true)
                ;

            default:
                return in_array(
                    GlsValue::LABEL_PRODUCTS[$carrierCode],
                    $products
                );
        }
    }

    protected function isAllowedService(
        AllowedServices $allowedServices,
        Carrier $carrier
    ) {
        $carrierCode = $carrier->getCode();

        switch ($carrierCode) {
            case GlsValue::GLS_CHEZ_VOUS:
            case GlsValue::GLS_AVANT_13H:
                return true;

            default:
                return in_array(
                    GlsValue::LABEL_SERVICES[$carrierCode],
                    $allowedServices->getServices()
                );
        }
    }

    protected function getAllowedServices(Address $destination)
    {
        $storeData = $this->shop->getAddress();
        $numContact = $this->config->get('GLS_API_CONTACT_ID');

        return $this->allowedServicesLoader->getAllowedServices([
            'Source' => [
                'ZIPCode' => $storeData->getZipCode(),
                'CountryCode' => $storeData->getCountryCode(),
            ],
            'Destination' => [
                'ZIPCode' => $destination->getZipCode(),
                'CountryCode' => $destination->getCountryCode(),
            ],
            'ContactID' => $numContact,
        ]);
    }

    protected function checkConstraints(Address $destination)
    {
        $storeData = $this->shop->getAddress();

        $storeZipcode = $storeData->getZipCode();
        $storeCountryCode = $storeData->getCountryCode();
        $destinationZipcode = $destination->getZipCode();
        $destinationCountryCode = $destination->getCountryCode();

        if (
            empty($storeZipcode) ||
            empty($storeCountryCode) ||
            empty($destinationZipcode) ||
            empty($destinationCountryCode)
        ) {
            return false;
        }

        return true;
    }
}
