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
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorFactory;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorInterface;
use Nukium\GLS\Common\Service\ShipIt\Routine\ShipItErrorRoutine;
use Nukium\GLS\Common\Service\ShipIt\ShipItComponentInterface;
use Nukium\GLS\Common\Value\GlsErrorValue;

class LabelErrorComponent implements ShipItComponentInterface
{
    private static $instance = null;

    protected $translator;

    protected $shipItErrorRoutine;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                TranslatorFactory::getInstance(),
                ShipItErrorRoutine::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        TranslatorInterface $translator,
        ShipItErrorRoutine $shipItErrorRoutine
    ) {
        $this->translator = $translator;
        $this->shipItErrorRoutine = $shipItErrorRoutine;
    }

    public function adaptEventBefore($event)
    {
        return $event;
    }

    public function adaptEventAfter($event)
    {
        $response = $event['response'];

        if (
            $response['status'] < 400 ||
            $response['status'] >= 500 ||
            $response['status'] === 401
        ) {
            return $event;
        }

        $isContactError = $this->shipItErrorRoutine->isInvalidField(
            $response,
            GlsErrorValue::ARG_CONTACT_ID
        );
        if ($isContactError) {
            throw new GlsApiException($this->translator->trans(
                'The contact number is invalid. Check the data entered in "GLS Account".'
            ));
        }

        $isParcelShopError = $this->shipItErrorRoutine->isInvalidType(
            $response,
            GlsErrorValue::TYPE_INVALID_PARCELSHOP
        );
        if ($isParcelShopError) {
            throw new GlsApiException($this->translator->trans(
                'The relay point selected by the customer is no longer available.'
            ));
        }

        $isNotAvailableShipment = $this->shipItErrorRoutine->isInvalidType(
            $response,
            GlsErrorValue::TYPE_NOT_AVAILABLE_SHIPMENT
        );
        if ($isNotAvailableShipment) {
            throw new GlsApiException($this->translator->trans(
                'Unable to generate a label for this order. Please contact your GLS sales.'
            ));
        }

        $isEmailError = $this->shipItErrorRoutine->isInvalidField(
            $response,
            GlsErrorValue::ARG_EMAIL
        );
        $isRelaisEmailError = $this->shipItErrorRoutine->isInvalidField(
            $response,
            GlsErrorValue::ARG_RELAIS_EMAIL
        );
        if (
            $isEmailError ||
            $isRelaisEmailError
        ) {
            throw new GlsApiException($this->translator->trans(
                'The email associated to the order is invalid or missing.'
            ));
        }

        $isAddressRequired = $this->shipItErrorRoutine->isRequiredField(
            $response,
            GlsErrorValue::ARG_ADDRESS
        );
        if ($isAddressRequired) {
            throw new GlsApiException($this->translator->trans(
                'The order does not have a delivery address.'
            ));
        }

        $isRefError = $this->shipItErrorRoutine->isInvalidField(
            $response,
            GlsErrorValue::ARG_REFERENCE
        );
        if ($isRefError) {
            throw new GlsApiException($this->translator->trans(
                'Invalid additional reference.'
            ));
        }

        $isWeightTooLow = $this->shipItErrorRoutine->isInvalidField(
            $response,
            GlsErrorValue::ARG_WEIGHT_TOO_LOW
        );
        if ($isWeightTooLow) {
            throw new GlsApiException($this->translator->trans(
                'The weight entered is too low.'
            ));
        }

        $isWeightTooHigh = $this->shipItErrorRoutine->isInvalidField(
            $response,
            GlsErrorValue::ARG_WEIGHT_TOO_HIGH
        );
        if ($isWeightTooHigh) {
            throw new GlsApiException($this->translator->trans(
                'The weight entered is too high.'
            ));
        }

        $isZipcodeInvalid1 = $this->shipItErrorRoutine->isInvalidField(
            $response,
            GlsErrorValue::ARG_INVALID_ZIPCODE1
        );
        $isZipcodeInvalid2 = $this->shipItErrorRoutine->isInvalidField(
            $response,
            GlsErrorValue::ARG_INVALID_ZIPCODE2
        );
        if ($isZipcodeInvalid1 || $isZipcodeInvalid2) {
            throw new GlsApiException($this->translator->trans(
                'The postal code is invalid.'
            ));
        }

        return $event;
    }
}
