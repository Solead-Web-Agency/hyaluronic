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

use Nukium\GLS\Common\Exception\GlsApiException;
use Nukium\GLS\Common\Service\Handler\Legacy\GlsApiHandler;

class LegacyClient
{
    private static $instance = null;

    protected $glsApiHandler;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                GlsApiHandler::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        GlsApiHandler $glsApiHandler
    ) {
        $this->glsApiHandler = $glsApiHandler;
    }

    public function processing(
        $method,
        $resource,
        $options = []
    ) {
        if (!isset($options['auth'])) {
            $client = $this->glsApiHandler->autoCreateShipIt();
        } else {
            $client = $this->glsApiHandler->createShipIt(
                $options['auth']['login'],
                $options['auth']['pwd']
            );
        }

        $client->fullEvent = true;

        if (isset($options['return_array'])) {
            $client->returnArray = $options['return_array'];
        }

        $body = '';
        if (isset($options['body'])) {
            $body = $options['body'];
        }

        $result = $client->executeRequest(
            $method,
            $resource,
            $body
        );

        if (!empty($client->error)) {
            throw new GlsApiException($client->error[0]);
        }

        if ($result === false) {
            throw new GlsApiException();
        }

        return $result;
    }
}
