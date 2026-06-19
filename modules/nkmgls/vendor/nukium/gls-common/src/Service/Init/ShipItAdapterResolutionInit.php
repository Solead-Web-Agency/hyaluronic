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

namespace Nukium\GLS\Common\Service\Init;

use Nukium\GLS\Common\Service\Container;
use Nukium\GLS\Common\Service\ShipIt\Adapter\LabelAdapter;
use Nukium\GLS\Common\Service\ShipIt\Adapter\RelayAdapter;
use Nukium\GLS\Common\Service\ShipIt\Adapter\TrackingAdapter;
use Nukium\GLS\Common\Service\ShipIt\ShipItAdapterResolution;

class ShipItAdapterResolutionInit
{
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

    public function init()
    {
        $container = Container::getInstance();

        $shipItAdapterResolution = $container->get(ShipItAdapterResolution::class);

        $labelAdapter = $container->get(LabelAdapter::class);

        $relayAdapter = $container->get(RelayAdapter::class);

        $trackingAdapter = $container->get(TrackingAdapter::class);

        $shipItAdapterResolution->register($labelAdapter);
        $shipItAdapterResolution->register($relayAdapter);
        $shipItAdapterResolution->register($trackingAdapter);
    }
}
