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

class NkmGlsTrackingModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (Shop::isFeatureActive()) {
            if (
                Tools::getIsset('id_shop') &&
                in_array(Tools::getValue('id_shop'), Shop::getCompleteListOfShopsID())
            ) {
                Shop::setContext(
                    Shop::CONTEXT_SHOP,
                    (int) Tools::getValue('id_shop')
                );
            } else {
                Shop::setContext(Shop::CONTEXT_ALL);
            }
        }

        $secure_key = Configuration::get('GLS_SECURE_KEY');
        if ($this->module->active && Tools::getIsset('secure_key') && $secure_key === Tools::getValue('secure_key')) {
            try {
                $this->module->updateOrderStates();
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }

    public function display()
    {
        return null;
    }

    public function displayAjaxUpdateTrackingState()
    {
        $return = [
            'result' => false,
            'message' => '',
        ];
        if (Tools::getIsset('id_order') && (int) Tools::getValue('id_order') > 0) {
            try {
                $result = $this->module->updateOrderStates((int) Tools::getValue('id_order'));
                if ($result) {
                    if (isset($result['error'])) {
                        $return = [
                            'result' => false,
                            'message' => $result['message'],
                        ];
                    } else {
                        $return = [
                            'result' => true,
                            'current_state' => (isset($result['current_state']) ? $result['current_state'] : ''),
                            'current_state_date' => (isset($result['current_state_date']) ? $result['current_state_date'] : ''),
                            'message' => (isset($result['message']) ? $result['message'] : ''),
                        ];
                    }
                }
            } catch (Exception $e) {
                $return['message'] = $e->getMessage() . ' [' . $e->getCode() . ']';
            }
        }

        header('Content-Type: application/json');
        $this->ajaxDie(json_encode($return));
    }
}
