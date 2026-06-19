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

use Nukium\GLS\Common\Value\GlsValue;
use Nukium\PrestaShop\GLS\Service\Handler\DTO\Adapter\Carrier\PrestashopCarrierHandler;

function upgrade_module_3_1_0($module)
{
    $carrierHandler = PrestashopCarrierHandler::getInstance();

    $modulePath = $module->getLocalPath();

    $carrierHandler->refreshCarriersData([
        GlsValue::GLS_RELAIS => 'GLS Relais',
        GlsValue::GLS_CHEZ_VOUS => 'GLS Chez vous',
        GlsValue::GLS_CHEZ_VOUS_PLUS => 'GLS Chez vous +',
        GlsValue::GLS_AVANT_13H => 'GLS avant 13h',
    ]);

    @unlink("{$modulePath}cron.php");

    return true;
}
