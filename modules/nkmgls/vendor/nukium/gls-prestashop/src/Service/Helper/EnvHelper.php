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

namespace Nukium\PrestaShop\GLS\Service\Helper;

use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorFactory;
use Nukium\PrestaShop\GLS\Service\Adapter\Translator\PrestashopTranslator;

class EnvHelper
{
    private static $instance = null;

    protected $moduleHelper;

    protected $translator;

    protected $data = null;

    protected $translateKeys = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                ModuleHelper::getInstance(),
                TranslatorFactory::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        ModuleHelper $moduleHelper,
        PrestashopTranslator $translator
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->translator = $translator;
    }

    public function get($key)
    {
        $this->load();

        if (!isset($this->data[$key])) {
            return null;
        }

        return $this->data[$key];
    }

    public function load()
    {
        if ($this->data !== null) {
            return;
        }

        $this->data = [];

        $path = $this->moduleHelper->getLocalPath();

        $this->loadFile("{$path}.env.json");

        if (isset($this->data['MODE'])) {
            $mode = $this->data['MODE'];
            $this->loadFile("{$path}.env.{$mode}.json");
        }
    }

    protected function loadFile($file)
    {
        if (!file_exists($file)) {
            return;
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return;
        }

        if (isset($data['TRANSLATE_KEYS'])) {
            $this->translateKeys = array_merge(
                $this->translateKeys,
                $data['TRANSLATE_KEYS']
            );
            unset($data['TRANSLATE_KEYS']);
        }

        foreach ($data as $key => &$value) {
            if (
                !isset($this->translateKeys[$key]) ||
                !$this->translateKeys[$key]
            ) {
                continue;
            }

            $value = $this->translator->trans($value);
        }
        unset($value);

        $this->data = array_merge($this->data, $data);
    }
}
