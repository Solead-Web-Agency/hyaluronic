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

namespace Nukium\GLS\Common\Legacy;

use Nukium\GLS\Common\Exception\GlsApiException;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorInterface;
use Nukium\GLS\Common\Service\Adapter\Utility\Component\DefaultUtility;
use Nukium\GLS\Common\Service\Handler\Legacy\GlsApiHandler;
use Nukium\GLS\Common\Service\HttpClient\GlsCurlClient;
use Nukium\GLS\Common\Value\GlsValue;

class GlsApi
{
    protected $client;

    protected $translator;

    protected $utility;

    protected $rest_api_url;

    protected $api_login = '';

    protected $api_pwd = '';

    protected $lang = 'en';

    public $error = [];

    public $returnArray = false;

    public static function createInstance($login, $pwd, $_lang = '')
    {
        $glsApiHandler = GlsApiHandler::getInstance();

        return $glsApiHandler->create($login, $pwd, $_lang);
    }

    public function __construct(
        GlsCurlClient $client,
        TranslatorInterface $translator,
        DefaultUtility $utility,
        $login,
        $pwd,
        $_lang = ''
    ) {
        $this->client = $client;
        $this->translator = $translator;
        $this->utility = $utility;

        if (
            empty($_lang) ||
            $this->utility->strlen($_lang) !== 2
        ) {
            $_lang = $this->lang;
        }

        $this->rest_api_url = GlsValue::LEGACY_REST_API_URL;
        $this->api_login = $login;
        $this->api_pwd = $pwd;
        $this->lang = $_lang;

        if (empty($login) || empty($pwd)) {
            throw new GlsApiException(
                $this->translator->trans('You must configure the GLS module')
            );
        }
    }

    public function executeRequest($method, $resource, $body = '')
    {
        try {
            $url = $this->rest_api_url . $resource;

            if (
                is_array($body) &&
                isset($body['additional_temp_data'])
            ) {
                unset($body['additional_temp_data']);
            }

            $result = $this->client->request($method, $url, [
                'headers' => $this->buildHeaders(),
                'credentials' => $this->buildCredentials(),
                'body' => $body,
                'return_array' => $this->returnArray,
            ]);

            $result = $this->handleResponse($result);
        } catch (GlsApiException $e) {
            $this->error[] = $e->getMessage();
        }

        if (!empty($this->error)) {
            return false;
        }

        return $result;
    }

    public function post($_resource, $_body = '')
    {
        return $this->executeRequest('POST', $_resource, $_body);
    }

    public function get($_resource)
    {
        return $this->executeRequest('GET', $_resource);
    }

    public function reset()
    {
        $this->error = [];
    }

    protected function buildHeaders()
    {
        return [
            'Accept-Language: ' . $this->lang,
            'Accept: application/json',
            'Content-Type: application/json',
        ];
    }

    protected function buildCredentials()
    {
        return $this->api_login . ':' . $this->api_pwd;
    }

    protected function handleResponse($response)
    {
        $result = $response['content'];

        if (
            isset($result->errors) &&
            is_array($result->errors) &&
            !empty($result->errors)
        ) {
            foreach ($result->errors as $value) {
                $this->error[] = [
                    'code' => $value->exitCode,
                    'message' => $value->description,
                ];
            }

            $result = false;
        }

        return $result;
    }
}
