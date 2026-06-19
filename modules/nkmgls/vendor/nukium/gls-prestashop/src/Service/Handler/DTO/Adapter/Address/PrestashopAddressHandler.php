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

namespace Nukium\PrestaShop\GLS\Service\Handler\DTO\Adapter\Address;

use Nukium\GLS\Common\Service\Handler\DTO\Adapter\Address\AddressHandler;

class PrestashopAddressHandler extends AddressHandler
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function adapt($address)
    {
        $idCountry = (int) $address->id_country;
        $ignoreRestriction = false;

        if (
            !\Validate::isLoadedObject($address) &&
            empty($address->postcode) &&
            $idCountry === 0
        ) {
            $ignoreRestriction = true;
        }

        $countryCode = \Country::getIsoById($idCountry);

        return $this->create()
            ->setZipCode($address->postcode)
            ->setCountryCode($countryCode)
            ->setIgnoreRestriction($ignoreRestriction)
        ;
    }
}
