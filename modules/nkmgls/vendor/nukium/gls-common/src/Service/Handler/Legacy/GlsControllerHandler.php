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

namespace Nukium\GLS\Common\Service\Handler\Legacy;

use Nukium\GLS\Common\Legacy\Adapter\GlsControllerShipIt;
use Nukium\GLS\Common\Legacy\GlsController;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigFactory;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigInterface;

class GlsControllerHandler
{
    private static $instance = null;

    protected $config;

    protected $glsApiHandler;

    public function __construct(
        ConfigInterface $config,
        GlsApiHandler $glsApiHandler
    ) {
        $this->config = $config;
        $this->glsApiHandler = $glsApiHandler;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                ConfigFactory::getInstance(),
                GlsApiHandler::getInstance()
            );
        }

        return self::$instance;
    }

    public function create($params = null)
    {
        $isUsingShipIt = (int) $this->config->get('GLS_IS_USING_SHIPIT_API');
        if (!is_null($params) && isset($params['GLS_IS_USING_SHIPIT_API'])) {
            $isUsingShipIt = (int) $params['GLS_IS_USING_SHIPIT_API'];
        }

        if ($isUsingShipIt === 1) {
            return $this->createShipIt($params);
        }

        return new GlsController(
            $params
        );
    }

    public function createShipIt($params = null)
    {
        return new GlsControllerShipIt(
            $this->glsApiHandler,
            $params
        );
    }
}
