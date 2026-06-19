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

use Nukium\GLS\Common\Service\API\ShipmentApi;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\AuthErrorComponent;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\DefaultErrorComponent;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\LabelComponent;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\LabelErrorComponent;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\LabelInternationalComponent;

class LabelAdapter extends AbstractAdapter
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                AuthErrorComponent::getInstance(),
                DefaultErrorComponent::getInstance(),
                LabelInternationalComponent::getInstance(),
                LabelErrorComponent::getInstance(),
                LabelComponent::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        AuthErrorComponent $authErrorComponent,
        DefaultErrorComponent $defaultErrorComponent,
        LabelInternationalComponent $labelInternationalComponent,
        LabelErrorComponent $labelErrorComponent,
        LabelComponent $labelComponent
    ) {
        $this->addComponent($labelInternationalComponent);
        $this->addComponent($labelErrorComponent);

        parent::__construct(
            $authErrorComponent,
            $defaultErrorComponent
        );

        $this->addComponent($labelComponent);
    }

    public function isAdapter($resource)
    {
        return $resource === 'shipments';
    }

    public function adaptResource($event)
    {
        return ShipmentApi::RESOURCE;
    }
}
