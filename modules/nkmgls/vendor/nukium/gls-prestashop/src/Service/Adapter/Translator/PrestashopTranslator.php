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

namespace Nukium\PrestaShop\GLS\Service\Adapter\Translator;

use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorInterface;

class PrestashopTranslator implements TranslatorInterface
{
    private static $instance;

    protected $module;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                \NkmGls::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        \NkmGls $module
    ) {
        $this->module = $module;
    }

    public function trans($name, $options = [])
    {
        $vars = [];
        if (isset($options['vars'])) {
            $vars = $options['vars'];
        }

        $r = $this->module->l($name, '_translation');

        foreach ($vars as $key => $value) {
            $r = str_replace($key, $value, $r);
        }

        return $r;
    }
}
