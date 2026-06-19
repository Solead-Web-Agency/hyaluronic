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

namespace Nukium\GLS\Common\Service\ShipIt\Routine;

use Nukium\GLS\Common\Value\GlsErrorValue;

class ShipItErrorRoutine
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
    }

    public function isInvalidRequest($response)
    {
        $statusCode = $response['status'];

        return $statusCode === 400;
    }

    public function isInvalidType($response, $type)
    {
        $errorType = $this->getHeaderValue(
            $response['headers'],
            'error'
        );

        return $errorType === $type;
    }

    public function isInvalidField($response, $field)
    {
        return
            $this->isInvalidRequest($response) &&
            $this->isInvalidType($response, GlsErrorValue::TYPE_INVALID_FIELD) &&
            $this->hasArg($response, $field)
        ;
    }

    public function isRequiredField($response, $field)
    {
        return
            $this->isInvalidRequest($response) &&
            $this->isInvalidType($response, GlsErrorValue::TYPE_REQUIRED_PARAMETER) &&
            $this->hasArg($response, $field)
        ;
    }

    public function hasArg($response, $arg)
    {
        $errorArgs = $this->getHeaderValue(
            $response['headers'],
            'args'
        );

        return
            $errorArgs !== null &&
            strpos($errorArgs, $arg) !== false
        ;
    }

    protected function getHeaderValue($headers, $key)
    {
        if (!isset($headers[$key])) {
            return null;
        }

        if (is_array($headers[$key])) {
            if (empty($headers[$key])) {
                return null;
            }

            return $headers[$key][0];
        }

        if (is_string($headers[$key])) {
            return $headers[$key];
        }

        return null;
    }
}
