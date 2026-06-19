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

namespace Nukium\GLS\Common\Legacy\Adapter;

use Nukium\GLS\Common\Exception\GlsApiException;
use Nukium\GLS\Common\Legacy\GlsApi;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorInterface;
use Nukium\GLS\Common\Service\Adapter\Utility\Component\DefaultUtility;
use Nukium\GLS\Common\Service\Handler\Legacy\GlsApiHandler;
use Nukium\GLS\Common\Service\HttpClient\GlsCurlClient;
use Nukium\GLS\Common\Service\ShipIt\ShipItAdapterResolution;
use Nukium\GLS\Common\Value\GlsValue;

class GlsApiShipIt extends GlsApi
{
    protected $adapterResolution;

    public $fullEvent = false;

    public static function createInstance($login, $pwd, $_lang = '')
    {
        $glsApiHandler = GlsApiHandler::getInstance();

        return $glsApiHandler->createShipIt($login, $pwd);
    }

    public static function autoCreateInstance()
    {
        $glsApiHandler = GlsApiHandler::getInstance();

        return $glsApiHandler->autoCreateShipIt();
    }

    public function __construct(
        GlsCurlClient $glsCurlClient,
        TranslatorInterface $translator,
        DefaultUtility $utility,
        ShipItAdapterResolution $adapterResolution,
        $login,
        $pwd
    ) {
        parent::__construct(
            $glsCurlClient,
            $translator,
            $utility,
            $login,
            $pwd
        );

        $this->rest_api_url = GlsValue::SHIPIT_REST_API_URL;

        $this->adapterResolution = $adapterResolution;
    }

    public function executeRequest($method, $resource, $body = '')
    {
        try {
            $adapter = $this->adapterResolution->getAdapter($resource);
            $event = [
                'auth' => [
                    'login' => $this->api_login,
                    'pwd' => $this->api_pwd,
                ],
                'original_method' => $method,
                'method' => $method,
                'original_resource' => $resource,
                'resource' => $resource,
                'original_body' => $body,
                'body' => $body,
            ];

            $event = $adapter->adaptEventBefore($event);

            if (!isset($event['response'])) {
                $event['response'] = parent::executeRequest(
                    $event['method'],
                    $event['resource'],
                    $event['body']
                );
            }

            if ($event['response'] === false) {
                return false;
            }

            $event['original_response'] = $event['response'];

            $event = $adapter->adaptEventAfter($event);

            if ($this->fullEvent) {
                return $event;
            }

            return $event['response']['content'];
        } catch (GlsApiException $e) {
            $this->error[] = $e->getMessage();

            return false;
        }
    }

    protected function buildHeaders()
    {
        return [
            'Accept: application/glsVersion1+json, application/json',
            'Content-Type: application/glsVersion1+json',
        ];
    }

    protected function handleResponse($response)
    {
        return $response;
    }
}
