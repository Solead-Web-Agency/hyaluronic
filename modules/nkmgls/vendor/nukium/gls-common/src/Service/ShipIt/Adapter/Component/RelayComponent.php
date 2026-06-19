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
use Nukium\GLS\Common\Service\ShipIt\ShipItComponentInterface;

class RelayComponent implements ShipItComponentInterface
{
    private static $instance = null;

    protected $translator;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                TranslatorFactory::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function adaptEventBefore($event)
    {
        return $event;
    }

    public function adaptEventAfter($event)
    {
        $data = $event['original_response']['content'];
        $result = $event['response']['content'];

        if (
            (!isset($data->ParcelShop) || empty($data->ParcelShop)) &&
            !isset($data->ParcelShopID)
        ) {
            throw new GlsApiException('', 998);
        }

        if (isset($data->ParcelShop)) {
            $result->SearchResults = $this->adaptSearch($data);
        } elseif (isset($data->ParcelShopID)) {
            $result->ParcelShop = $this->adaptDetail($data, $result);
        } else {
            throw new GlsApiException($this->translator->trans(
                'An error occured , please contact technical support.'
            ));
        }

        return $event;
    }

    protected function adaptSearch($data)
    {
        $result = [];

        foreach ($data->ParcelShop as $dataElement) {
            $resultElement = new \stdClass();
            $resultElement->Parcelshop = $this->adaptDetail($dataElement, $resultElement);
            $resultElement->AirLineDistance = $this->adaptAirlineDistance($dataElement, $resultElement);

            $result[] = $resultElement;
        }

        return $result;
    }

    protected function adaptDetail($dataElement)
    {
        $resultElement = new \stdClass();
        $resultElement->ParcelShopId = $dataElement->ParcelShopID;

        $this->adaptLocation($dataElement, $resultElement);
        $this->adaptAddress($dataElement, $resultElement);
        $this->adaptWordkingDay($dataElement, $resultElement);

        return $resultElement;
    }

    protected function adaptAirlineDistance($dataElement)
    {
        if (!isset($dataElement->AirlineDistance)) {
            return '';
        }

        return $dataElement->AirlineDistance;
    }

    protected function adaptLocation($dataElement, $resultElement)
    {
        $lat = 0;
        $lng = 0;

        if (
            isset($dataElement->Location, $dataElement->Location->Latitude, $dataElement->Location->Longitude)
        ) {
            $lat = $dataElement->Location->Latitude;
            $lng = $dataElement->Location->Longitude;
        }

        $resultElement->GLSCoordinates = new \stdClass();
        $resultElement->GLSCoordinates->Latitude = $lat;
        $resultElement->GLSCoordinates->Longitude = $lng;
    }

    protected function adaptAddress($dataElement, $resultElement)
    {
        $resultElement->Address = new \stdClass();
        $resultElement->Address->Name1 = '';
        $resultElement->Address->Name2 = '';
        $resultElement->Address->Name3 = '';
        $resultElement->Address->Street1 = '';
        $resultElement->Address->BlockNo1 = '';
        $resultElement->Address->Street2 = '';
        $resultElement->Address->BlockNo2 = '';
        $resultElement->Address->ZipCode = '';
        $resultElement->Address->City = '';
        $resultElement->Address->Province = '';
        $resultElement->Address->Country = '';

        $resultElement->Phone = new \stdClass();
        $resultElement->Phone->Contact = '';

        $resultElement->Mobile = new \stdClass();
        $resultElement->Mobile->Contact = '';

        $resultElement->Email = '';
        $resultElement->URL = '';

        if (!isset($dataElement->Address)) {
            return;
        }

        if (isset($dataElement->Address->Name1)) {
            $resultElement->Address->Name1 = $dataElement->Address->Name1;
        }

        if (isset($dataElement->Address->Name2)) {
            $resultElement->Address->Name2 = $dataElement->Address->Name2;
        }

        if (isset($dataElement->Address->Name3)) {
            $resultElement->Address->Name3 = $dataElement->Address->Name3;
        }

        if (isset($dataElement->Address->Street)) {
            $resultElement->Address->Street1 = $dataElement->Address->Street;
        }

        if (isset($dataElement->Address->StreetNumber)) {
            $resultElement->Address->Street2 = $dataElement->Address->StreetNumber;
        }

        if (isset($dataElement->Address->ZIPCode)) {
            $resultElement->Address->ZipCode = $dataElement->Address->ZIPCode;
        }

        if (isset($dataElement->Address->City)) {
            $resultElement->Address->City = $dataElement->Address->City;
        }

        if (isset($dataElement->Address->Province)) {
            $resultElement->Address->Province = $dataElement->Address->Province;
        }

        if (isset($dataElement->Address->CountryCode)) {
            $resultElement->Address->Country = $dataElement->Address->CountryCode;
        }

        if (isset($dataElement->Address->eMail)) {
            $resultElement->Email = $dataElement->Address->eMail;
        }

        if (isset($dataElement->Address->FixedLinePhonenumber)) {
            $resultElement->Phone->Contact = $dataElement->Address->FixedLinePhonenumber;
        }

        if (isset($dataElement->Address->MobilePhoneNumber)) {
            $resultElement->Mobile->Contact = $dataElement->Address->MobilePhoneNumber;
        }
    }

    protected function adaptWordkingDay($dataElement, $resultElement)
    {
        $resultElement->GLSWorkingDay = [];
        $dayMapping = [
            'MON' => 0,
            'TUE' => 1,
            'WED' => 2,
            'THU' => 3,
            'FRI' => 4,
            'SAT' => 5,
            'SUN' => 6,
        ];

        if (!isset($dataElement->WorkingDay)) {
            return;
        }

        foreach ($dataElement->WorkingDay as $d) {
            if (
                !isset($d->DayOfWeek) ||
                !isset($d->OpeningHours) ||
                !isset($d->OpeningHours->OpeningHours) ||
                empty($d->OpeningHours->OpeningHours)
            ) {
                continue;
            }

            $dayIndex = $dayMapping[$d->DayOfWeek];

            $resultEntry = new \stdClass();
            $resultEntry->Day = $dayIndex;
            $resultEntry->OpeningHours = new \stdClass();

            $this->adaptWordkingDayEntry(
                $d->OpeningHours,
                $resultEntry,
                'OpeningHours'
            );
            $this->adaptWordkingDayEntry(
                $d->OpeningHours,
                $resultEntry,
                'Breaks'
            );

            if (!isset($resultEntry->OpeningHours)) {
                continue;
            }

            $resultElement->GLSWorkingDay[$dayIndex] = $resultEntry;
        }
    }

    protected function adaptWordkingDayEntry(
        $dataEntry,
        $resultEntry,
        $column
    ) {
        $resultEntry->{$column} = new \stdClass();
        $resultHours = $resultEntry->{$column};

        $resultHours->Hours = new \stdClass();
        $resultHours->Hours->From = '';
        $resultHours->Hours->To = '';

        if (
            !property_exists($dataEntry, $column) ||
            empty($dataEntry->{$column})
        ) {
            return;
        }

        $dataHours = $dataEntry->{$column}[0];

        $resultHours->Hours->From = $this->convertTime($dataHours->From);
        $resultHours->Hours->To = $this->convertTime($dataHours->To);
    }

    protected function convertTime($time)
    {
        $date = new \DateTime();
        $date->setTimestamp($time / 1000);

        return $date->format('His');
    }
}
