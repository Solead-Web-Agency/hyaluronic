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

if (!class_exists('GlsTrackingStateClass')) {
    require_once dirname(__FILE__) . '/../classes/GlsTrackingStateClass.php';
}
if (!class_exists('GlsLabelClass')) {
    require_once dirname(__FILE__) . '/../classes/GlsLabelClass.php';
}
if (!class_exists('AdminGlsPackingListController')) {
    require_once dirname(__FILE__) . '/../controllers/admin/AdminGlsPackingListController.php';
}

function upgrade_module_2_0_0($object)
{
    $return = true;

    try {
        $return &= GlsLabelClass::createDbTable();
    } catch (PrestaShopDatabaseException $e) {
        $return &= false;
    }

    try {
        $return &= GlsTrackingStateClass::createDbTable();
    } catch (PrestaShopDatabaseException $e) {
        $return &= false;
    }

    $return &= $object->registerHook('displayAdminOrder');
    $return &= AdminGlsPackingListController::installInBO();

    return $return;
}
