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
use Nukium\GLS\Common\Service\API\TrackingApi;
use Nukium\GLS\Common\Service\ShipIt\ShipItComponentInterface;
use Nukium\GLS\Common\Value\GlsValue;

class TrackingComponent implements ShipItComponentInterface
{
    private static $instance = null;

    protected $translator;

    protected $trackingApi;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                TranslatorFactory::getInstance(),
                TrackingApi::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        TranslatorInterface $translator,
        TrackingApi $trackingApi
    ) {
        $this->translator = $translator;
        $this->trackingApi = $trackingApi;
    }

    public function adaptEventBefore($event)
    {
        foreach ($this->extractTrackIds($event) as $trackId) {
            $data = $this->trackingApi->getParcelDetailsByID($trackId, [
                'auth' => $event['auth'],
            ]);

            if (!isset($event['response'])) {
                $event['response'] = $data['response'];
                $event['response']['content'] = [];
            }

            if ($data['response']['status'] !== 200) {
                continue;
            }

            $content = $data['response']['content'];

            if (!isset($content->UnitDetail)) {
                continue;
            }

            $event['response']['content'][] = $content->UnitDetail;
			$event['response']['status'] = $data['response']['status'];
        }

        return $event;
    }

    public function adaptEventAfter($event)
    {
        $data = $event['original_response']['content'];
        $result = $event['response']['content'];

        $result->parcels = [];

        foreach ($data as $e) {
            $resultElement = new \stdClass();
            $resultElement->trackid = $e->TrackID;
            $resultElement->status = '';
            $resultElement->references = [];
            $resultElement->events = [];

            if (isset($e->History) && !empty($e->History)) {
                foreach (array_reverse($e->History) as $h) {
                    $statesAdapter = GlsValue::TRACKING_STATES_ADAPTER;

                    if (
                        !isset($h->StatusCode) ||
                        !isset($statesAdapter[$h->StatusCode])
                    ) {
                        continue;
                    }

                    $resultElement->status = $statesAdapter[$h->StatusCode];
                    break;
                }
            }

            $result->parcels[] = $resultElement;
        }

        if (empty($result->parcels)) {
            throw new GlsApiException($this->translator->trans(
                'No data was found. Please try again later.'
            ));
        }

        return $event;
    }

    protected function extractTrackIds($event)
    {
        $temp = $event['original_resource'];
        $temp = explode('/', $temp);
        $temp = end($temp);
        $temp = trim($temp);

        return explode(',', $temp);
    }
}
