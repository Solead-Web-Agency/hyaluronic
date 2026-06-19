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

use Nukium\GLS\Common\Service\API\ShipmentApi;
use Nukium\GLS\Common\Service\ShipIt\Routine\ShipItErrorRoutine;
use Nukium\GLS\Common\Service\ShipIt\ShipItComponentInterface;
use Nukium\GLS\Common\Value\GlsErrorValue;
use Nukium\GLS\Common\Value\GlsValue;

class LabelInternationalComponent implements ShipItComponentInterface
{
    private static $instance = null;

    protected $shipItErrorRoutine;

    protected $ShipmentApi;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                ShipItErrorRoutine::getInstance(),
                ShipmentApi::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        ShipItErrorRoutine $shipItErrorRoutine,
        ShipmentApi $ShipmentApi
    ) {
        $this->shipItErrorRoutine = $shipItErrorRoutine;
        $this->ShipmentApi = $ShipmentApi;
    }

    public function adaptEventBefore($event)
    {
        return $event;
    }

    public function adaptEventAfter($event)
    {
        if ($event['response']['status'] !== 400) {
            return $event;
        }

        $isNotAvailableShipment = $this->shipItErrorRoutine->isInvalidType(
            $event['response'],
            GlsErrorValue::TYPE_NOT_AVAILABLE_SHIPMENT
        );

        if (!$isNotAvailableShipment) {
            return $event;
        }

        $event['body']['Shipment']['Product'] = GlsValue::PRODUCT_EXPRESS;

        $data = $this->ShipmentApi->processing($event['body'], [
            'auth' => $event['auth'],
        ]);

        $event['original_response'] = $data['original_response'];
        $event['response'] = $data['response'];

        return $event;
    }
}
