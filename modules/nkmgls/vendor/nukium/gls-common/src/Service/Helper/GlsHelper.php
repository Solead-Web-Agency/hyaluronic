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

namespace Nukium\GLS\Common\Service\Helper;

use Nukium\GLS\Common\DTO\Adapter\Address;
use Nukium\GLS\Common\DTO\Adapter\Carrier;
use Nukium\GLS\Common\Value\GlsValue;

class GlsHelper
{
    const FRANCE_COUNTRY_CODES = [
        'FR',
        'MC',
    ];

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
    }

    public function getProductCode(
        Carrier $carrier,
        Address $destination
    ) {
        $code = $carrier->getCode();
        $isInternational = $this->isInternational($destination);

        switch ($code) {
            case GlsValue::GLS_RELAIS:
                if ($isInternational) {
                    return '26';
                } else {
                    return '17';
                }

                // no break
            case GlsValue::GLS_CHEZ_VOUS_PLUS:
                if ($isInternational) {
                    return '19';
                } else {
                    return '18';
                }

                // no break
            case GlsValue::GLS_AVANT_13H:
                return '16';

            default:
                if ($isInternational) {
                    return '01';
                } else {
                    return '02';
                }
        }
    }

    public function isInternational(Address $destination)
    {
        return !in_array(
            $destination->getCountryCode(),
            self::FRANCE_COUNTRY_CODES,
            true
        );
    }
}
