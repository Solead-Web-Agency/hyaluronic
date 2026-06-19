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
use Nukium\GLS\Common\Legacy\GlsController;
use Nukium\GLS\Common\Service\Handler\Legacy\GlsApiHandler;
use Nukium\GLS\Common\Service\Handler\Legacy\GlsControllerHandler;

class GlsControllerShipIt extends GlsController
{
    protected $glsApiHandler;

    public static function createInstance($_params = null)
    {
        $glsControllerHandler = GlsControllerHandler::getInstance();

        return $glsControllerHandler->createShipIt($_params);
    }

    public function __construct(
        GlsApiHandler $glsApiHandler,
        $_params = null
    ) {
        parent::__construct(
            $_params
        );

        $this->glsApiHandler = $glsApiHandler;

        if (!is_null($_params)) {
            $this->ws_login = $_params['GLS_API_LOGIN'];
            $this->ws_pwd = $_params['GLS_API_PWD'];
        }
    }

    public function checkAuth()
    {
        return $this->executeRequest('POST', 'backend/rs/parcelshop/address', [
            'ZIPCode' => '34000',
            'CountryCode' => 'FR',
        ]);
    }

    public function searchRelay(
        $_cp,
        $_city = '',
        $_country = 'FR',
        $_street = '',
        $options = []
    ) {
        $body = [
            'ZIPCode' => $_cp,
            'CountryCode' => $_country,
        ];

        if (!empty($_city)) {
            $body['City'] = $_city;
        }

        if (!empty($_street)) {
            $body['Street'] = $_street;
        }

        $response = $this->executeRequest(
            'POST',
            'backend/rs/parcelshop/address',
            $body
        );

        return $this->handleSearchRelay($response, $options);
    }

    public function getRelayDetail($_id)
    {
        $response = $this->executeRequest('GET', "backend/rs/parcelshop/{$_id}");

        return $this->handleRelayDetail($_id, $response);
    }

    protected function executeRequest($method, $resource, $body = '')
    {
        try {
            $api = $this->glsApiHandler->createShipIt(
                $this->ws_login,
                $this->ws_pwd
            );
        } catch (GlsApiException $e) {
            return null;
        }

        $result = $api->executeRequest($method, $resource, $body);

        if (!empty($api->error) || $result === false) {
            return null;
        }

        return $result;
    }
}
