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

namespace Nukium\GLS\Common\Service\ShipIt;

use Nukium\GLS\Common\Service\ShipIt\Adapter\NoShipItAdapter;

class ShipItAdapterResolution
{
    private static $instance = null;

    protected $noShipItAdapter;

    protected $adapters = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                NoShipItAdapter::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        NoShipItAdapter $noShipItAdapter
    ) {
        $this->noShipItAdapter = $noShipItAdapter;
    }

    public function register(ShipItAdapterInterface $adapter)
    {
        $this->adapters[] = $adapter;

        return $this;
    }

    public function getAdapter($resource)
    {
        $adapter = $this->noShipItAdapter;

        foreach ($this->adapters as $e) {
            if (!$e->isAdapter($resource)) {
                continue;
            }

            $adapter = $e;
            break;
        }

        return $adapter;
    }
}
