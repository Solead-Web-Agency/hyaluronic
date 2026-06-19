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

namespace Nukium\GLS\Common\Service\API;

use Nukium\GLS\Common\Service\HttpClient\LegacyClient;

class ShipmentApi
{
    const RESOURCE = 'backend/rs/shipments';

    private static $instance = null;

    protected $client;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                LegacyClient::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        LegacyClient $client
    ) {
        $this->client = $client;
    }

    public function processing($body, $options = [])
    {
        $options = array_merge($options, [
            'body' => $body,
        ]);

        return $this->client->processing('POST', self::RESOURCE, $options);
    }
}
