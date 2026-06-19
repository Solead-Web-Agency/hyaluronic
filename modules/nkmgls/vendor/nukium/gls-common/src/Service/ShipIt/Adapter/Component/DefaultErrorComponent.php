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

namespace Nukium\GLS\Common\Service\ShipIt\Adapter\Component;

use Nukium\GLS\Common\Exception\GlsApiException;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorFactory;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorInterface;
use Nukium\GLS\Common\Service\ShipIt\ShipItComponentInterface;

class DefaultErrorComponent implements ShipItComponentInterface
{
    private static $instance = null;

    protected $translator;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                TranslatorFactory::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function adaptEventBefore($event)
    {
        return $event;
    }

    public function adaptEventAfter($event)
    {
        $response = $event['response'];
        $statusCode = $response['status'];

        if ($statusCode >= 500) {
            throw new GlsApiException($this->translator->trans(
                'Service temporarily unavailable, try again later.'
            ));
        }

        if ($statusCode !== 200) {
            throw new GlsApiException($this->translator->trans(
                'An error occured , please contact technical support.'
            ));
        }

        return $event;
    }
}
