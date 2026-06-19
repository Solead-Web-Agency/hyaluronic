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

namespace Nukium\GLS\Common\Service\ShipIt\Adapter;

use Nukium\GLS\Common\Exception\GlsApiException;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\AuthErrorComponent;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\DefaultErrorComponent;
use Nukium\GLS\Common\Service\ShipIt\Adapter\Component\RelayComponent;

class RelayAdapter extends AbstractAdapter
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                AuthErrorComponent::getInstance(),
                DefaultErrorComponent::getInstance(),
                RelayComponent::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        AuthErrorComponent $authErrorComponent,
        DefaultErrorComponent $defaultErrorComponent,
        RelayComponent $relayComponent
    ) {
        parent::__construct(
            $authErrorComponent,
            $defaultErrorComponent
        );

        $this->addComponent($relayComponent);
    }

    public function isAdapter($resource)
    {
        return strpos($resource, 'backend/rs/parcelshop/') !== false;
    }

    public function adaptEventAfter($event)
    {
        try {
            return parent::adaptEventAfter($event);
        } catch (GlsApiException $e) {
            $event = $this->initResponseContent($event);

            $result = $event['response']['content'];
            $message = $e->getMessage();

            $errorCode = $e->getCode();
            if ($errorCode === 0) {
                $errorCode = 1;
            }

            $result->exitCode->ErrorCode = $errorCode;
            $result->exitCode->ErrorDscr = $message;
            $result->ExitCode->ErrorCode = $errorCode;
            $result->ExitCode->ErrorDscr = $message;
        }

        return $event;
    }

    protected function initResponseContent($event)
    {
        $event = parent::initResponseContent($event);

        $result = $event['response']['content'];

        $result->exitCode = new \stdClass();
        $result->exitCode->ErrorCode = 0;

        $result->ExitCode = new \stdClass();
        $result->ExitCode->ErrorCode = 0;

        return $event;
    }
}
