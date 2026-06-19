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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

if (!class_exists('AdminGlsOrderController')) {
    require_once dirname(__FILE__) . '/../admin/AdminGlsOrderController.php';
}

class NkmGlsWinexpeModuleFrontController extends ModuleFrontController
{
    const L_SPECIFIC = 'winexpe';

    public function __construct()
    {
        $this->module = 'nkmgls';
        parent::__construct();
    }

    public function postProcess()
    {
        Shop::setContext(Shop::CONTEXT_ALL);
        $secure_key = Configuration::get('GLS_SECURE_KEY');
        if ($this->module->active && Tools::getIsset('secure_key') && $secure_key === Tools::getValue('secure_key')) {
            try {
                $controller = new AdminGlsOrderController(true);
                $exportConfig = $controller->getConfigFormValues('export');
                $importConfig = $controller->getConfigFormValues('import');

                if ($importConfig['GLS_IMPORT_AUTOMATION']
                    && (!Tools::getIsset('action') || (Tools::getIsset('action') && Tools::getValue('action') === 'import'))
                ) {
                    $controller->importWinexpe();
                    if (!empty($controller->errors)) {
                        $gls_log = new GlsLogClass();
                        $gls_log->log($this->module->l('Automatic import:', self::L_SPECIFIC) . ' ' . implode(Tools::nl2br("\n"), array_map('pSQL', $controller->errors)));
                    }
                }

                if ($exportConfig['GLS_EXPORT_AUTOMATION']
                    && (!Tools::getIsset('action') || (Tools::getIsset('action') && Tools::getValue('action') === 'export'))
                ) {
                    $controller->exportWinexpe();
                    if (!empty($controller->errors)) {
                        $gls_log = new GlsLogClass();
                        $gls_log->log($this->module->l('Automatic export:', self::L_SPECIFIC) . ' ' . implode(Tools::nl2br("\n"), array_map('pSQL', $controller->errors)));
                    }
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }

    public function display()
    {
        return null;
    }
}
