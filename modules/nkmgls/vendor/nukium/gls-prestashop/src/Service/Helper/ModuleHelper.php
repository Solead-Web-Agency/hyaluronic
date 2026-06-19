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

namespace Nukium\PrestaShop\GLS\Service\Helper;

use Nukium\GLS\Common\Exception\LogicException;
use Nukium\GLS\Common\Value\GlsValue;
use Nukium\PrestaShop\GLS\Service\Adapter\Config\PrestashopConfig;

class ModuleHelper
{
    private static $instance = null;

    protected $module;

    protected $context;

    protected $config;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                \NkmGls::getInstance(),
                \Context::getContext(),
                PrestashopConfig::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        \NkmGls $module,
        \Context $context,
        PrestashopConfig $config
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->config = $config;
    }

    public function getLocalPath()
    {
        return $this->module->getLocalPath();
    }

    public function generateCronUri()
    {
        $cronQuery = [
            'secure_key' => $this->config->get('GLS_SECURE_KEY'),
            'action' => 'get_tracking',
        ];

        if (
            \Shop::isFeatureActive() &&
            \Shop::getContext() === \Shop::CONTEXT_SHOP
        ) {
            $cronQuery['id_shop'] = \Shop::getContextShopID();
        }

        return $this->context->link->getModuleLink(
            'nkmgls',
            'tracking',
            $cronQuery
        );
    }

    public function getCarrierDefinition($carrierCode)
    {
        $resolve = [
            GlsValue::GLS_RELAIS => 'GLSRELAIS',
            GlsValue::GLS_CHEZ_VOUS => 'GLSCHEZVOUS',
            GlsValue::GLS_CHEZ_VOUS_PLUS => 'GLSCHEZVOUSPLUS',
            GlsValue::GLS_AVANT_13H => 'GLS13H',
        ];

        if (!isset($resolve[$carrierCode])) {
            throw new LogicException("Carrier code {$carrierCode} does not exists");
        }

        $key = $resolve[$carrierCode];
        $result = \NkmGls::$carrier_definition[$key];
        $result['key'] = $key;

        return $result;
    }
}
