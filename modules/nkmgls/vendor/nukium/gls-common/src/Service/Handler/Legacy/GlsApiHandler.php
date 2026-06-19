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

use Nukium\GLS\Common\Legacy\Adapter\GlsApiShipIt;
use Nukium\GLS\Common\Legacy\GlsApi;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigFactory;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigInterface;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorFactory;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorInterface;
use Nukium\GLS\Common\Service\Adapter\Utility\Component\DefaultUtility;
use Nukium\GLS\Common\Service\Adapter\Utility\UtilityFactory;
use Nukium\GLS\Common\Service\HttpClient\GlsCurlClient;
use Nukium\GLS\Common\Service\ShipIt\ShipItAdapterResolution;

class GlsApiHandler
{
    private static $instance = null;

    protected $config;

    protected $client;

    protected $translator;

    protected $utility;

    protected $shipItAdapterResolution;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                ConfigFactory::getInstance(),
                GlsCurlClient::getInstance(),
                TranslatorFactory::getInstance(),
                UtilityFactory::getInstance(),
                ShipItAdapterResolution::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        ConfigInterface $config,
        GlsCurlClient $client,
        TranslatorInterface $translator,
        DefaultUtility $utility,
        ShipItAdapterResolution $shipItAdapterResolution
    ) {
        $this->config = $config;
        $this->client = $client;
        $this->translator = $translator;
        $this->utility = $utility;
        $this->shipItAdapterResolution = $shipItAdapterResolution;
    }

    public function create($login, $pwd, $lang = '')
    {
        if ((int) $this->config->get('GLS_IS_USING_SHIPIT_API') === 1) {
            return $this->createShipIt($login, $pwd);
        }

        return new GlsApi(
            $this->client,
            $this->translator,
            $this->utility,
            $login,
            $pwd,
            $lang
        );
    }

    public function createShipIt($login, $pwd)
    {
        return new GlsApiShipIt(
            $this->client,
            $this->translator,
            $this->utility,
            $this->shipItAdapterResolution,
            $login,
            $pwd
        );
    }

    public function autoCreateShipIt()
    {
        $login = $this->config->get('GLS_API_LOGIN');
        $pwd = $this->config->get('GLS_API_PWD');

        return $this->createShipIt($login, $pwd);
    }
}
