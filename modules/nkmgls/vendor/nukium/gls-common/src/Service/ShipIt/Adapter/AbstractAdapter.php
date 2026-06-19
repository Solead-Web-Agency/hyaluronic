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
use Nukium\GLS\Common\Service\ShipIt\ShipItAdapterInterface;
use Nukium\GLS\Common\Service\ShipIt\ShipItComponentInterface;

abstract class AbstractAdapter implements ShipItAdapterInterface
{
    protected $components = [];

    public function __construct(
        AuthErrorComponent $authErrorComponent,
        DefaultErrorComponent $defaultErrorComponent
    ) {
        $this->addComponent($authErrorComponent);
        $this->addComponent($defaultErrorComponent);
    }

    public function adaptEventBefore($event)
    {
        $event['method'] = $this->adaptMethod($event);
        $event['resource'] = $this->adaptResource($event);

        foreach ($this->components as $e) {
            $event = $e->adaptEventBefore($event);
        }

        return $event;
    }

    public function adaptEventAfter($event)
    {
        $event = $this->initResponseContent($event);

        foreach ($this->components as $e) {
            $event = $e->adaptEventAfter($event);
        }

        return $event;
    }

    protected function addComponent(ShipItComponentInterface $component)
    {
        $this->components[] = $component;

        return $this;
    }

    protected function initResponseContent($event)
    {
        $event['response']['content'] = new \stdClass();

        return $event;
    }

    protected function adaptMethod($event)
    {
        return $event['method'];
    }

    protected function adaptResource($event)
    {
        return $event['resource'];
    }
}
