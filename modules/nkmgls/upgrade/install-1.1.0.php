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

if (!class_exists('AdminGlsLabelController')) {
    require_once dirname(__FILE__) . '../controllers/admin/AdminGlsLabelController.php';
}

function upgrade_module_1_1_0()
{
    Configuration::updateValue('GLS_API_CUSTOMER_ID', '');
    Configuration::updateValue('GLS_API_CONTACT_ID', '');
    Configuration::updateValue('GLS_API_DELIVERY_LABEL_FORMAT', 'A6');
    Configuration::updateValue('GLS_API_SHOP_RETURN_SERVICE', '0');
    Configuration::updateValue('GLS_API_SHOP_RETURN_EMAIL_ALERT', '0');
    Configuration::updateValue('GLS_API_LOGIN', '');
    Configuration::updateValue('GLS_API_PWD', '');
    Configuration::updateValue('GLS_API_SHOP_RETURN_ADDRESS', '1');

    return AdminGlsLabelController::installInBO();
}
