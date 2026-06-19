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

namespace Nukium\GLS\Common\Service\ShipIt\Adapter;

use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\AuthErrorComponent;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\DefaultErrorComponent;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\TrackingComponent;

class TrackingAdapter extends AbstractAdapter
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                AuthErrorComponent::getInstance(),
                DefaultErrorComponent::getInstance(),
                TrackingComponent::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        AuthErrorComponent $authErrorComponent,
        DefaultErrorComponent $defaultErrorComponent,
        TrackingComponent $trackingComponent
    ) {
        parent::__construct(
            $authErrorComponent,
            $defaultErrorComponent
        );

        $this->addComponent($trackingComponent);
    }

    public function isAdapter($resource)
    {
        return strpos($resource, 'tracking/references/') !== false;
    }

    protected function adaptMethod($event)
    {
        return 'POST';
    }
}
