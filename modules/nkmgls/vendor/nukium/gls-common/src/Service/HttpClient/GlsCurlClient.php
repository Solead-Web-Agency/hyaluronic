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

namespace Nukium\GLS\Common\Service\HttpClient;

use Nukium\GLS\Common\DTO\HttpClient\HeaderParser;
use Nukium\GLS\Common\Exception\GlsApiException;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigFactory;
use Nukium\GLS\Common\Service\Adapter\Config\ConfigInterface;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorFactory;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorInterface;

class GlsCurlClient
{
    private static $instance = null;

    protected $translator;

    protected $config;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                TranslatorFactory::getInstance(),
                ConfigFactory::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        TranslatorInterface $translator,
        ConfigInterface $config
    ) {
        $this->translator = $translator;
        $this->config = $config;

        if (!extension_loaded('curl')) {
            throw new GlsApiException(
                $this->translator->trans('You need to enable cURL extension to use GLS')
            );
        }
    }

    public function request($method, $url, $options)
    {
        $session = curl_init($url);

        if ($session === false) {
            throw new GlsApiException(
                $this->translator->trans('Impossible to make cURL request. Please contact your host.')
            );
        }

        $headerParser = new HeaderParser();

        $timeout = $this->getOption($options, 'timeout');
        $headers = $this->getOption($options, 'headers');
        $credentials = $this->getOption($options, 'credentials');
        $body = $this->getOption($options, 'body');
        $returnArray = $this->getOption($options, 'return_array');

        if (!empty($credentials)) {
            $headers[] = 'Authorization: Basic ' . base64_encode($credentials);
        }

        $curl_options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADERFUNCTION => $headerParser,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        if ((int) $this->config->get('GLS_SSL_PATCH') === 1) {
            $curl_options[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;
            $curl_options[CURLOPT_SSL_CIPHER_LIST] = 'DEFAULT@SECLEVEL=1';
        }

        if (!empty($body)) {
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($session, $curl_options);
        $responseContent = curl_exec($session);
        $responseHeaders = $headerParser->getHeaders();
        $responseStatusCode = curl_getinfo($session, CURLINFO_HTTP_CODE);

        if ($responseContent === false) {
            throw new GlsApiException(
                'Bad response (CURL Error: ' . curl_error($session) . ')'
            );
        }

        curl_close($session);

        return [
            'headers' => $responseHeaders,
            'status' => $responseStatusCode,
            'content' => json_decode($responseContent, $returnArray),
        ];
    }

    protected function getDefaultOptions()
    {
        return [
            'timeout' => 60,
            'headers' => [],
            'credentials' => '',
            'body' => '',
            'return_array' => false,
        ];
    }

    protected function getOption($options, $key)
    {
        if (!isset($options[$key])) {
            $defaultOptions = $this->getDefaultOptions();

            return $defaultOptions[$key];
        }

        return $options[$key];
    }
}
