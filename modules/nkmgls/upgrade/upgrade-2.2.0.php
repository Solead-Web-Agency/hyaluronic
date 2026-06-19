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

function upgrade_module_2_2_0($object)
{
    Configuration::updateValue('GLS_ADD_PRICE_FREE_CARRIER_ENABLE', 1);
    Configuration::updateValue('GLS_SSL_PATCH', 0);

    try {
        $sql = new DbQuery();
        $sql->select('id_carrier')
            ->from('carrier')
            ->where('name=\'GLS Point Relais®\'')
            ->where('external_module_name=\'nkmgls\'')
            ->where('deleted=0');
        foreach (Db::getInstance()->ExecuteS($sql) as $value) {
            $carrier = new Carrier((int) $value['id_carrier']);
            $carrier->name = 'GLS Relais';
            $carrier->delay = NkmGls::$carrier_definition['GLSRELAIS']['delay'];

            foreach (Language::getLanguages(true) as $language) {
                if (array_key_exists($language['iso_code'], NkmGls::$carrier_definition['GLSRELAIS']['delay'])) {
                    $carrier->delay[$language['id_lang']] = NkmGls::$carrier_definition['GLSRELAIS']['delay'][$language['iso_code']];
                } else {
                    $carrier->delay[$language['id_lang']] = NkmGls::$carrier_definition['GLSRELAIS']['delay']['fr'];
                }
            }
            $carrier->update();

            $old_logo = _PS_SHIP_IMG_DIR_ . '/' . (int) $value['id_carrier'] . '.jpg';
            if (file_exists($old_logo)) {
                copy(dirname(__FILE__) . '/../views/img/admin/glsrelais.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $value['id_carrier'] . '.jpg');
            }

            $old_tmp_logo = _PS_TMP_IMG_DIR_ . '/carrier_mini_' . (int) $value['id_carrier'] . '_1.jpg';
            if (file_exists($old_tmp_logo)) {
                copy(dirname(__FILE__) . '/../views/img/admin/glsrelais.jpg', _PS_TMP_IMG_DIR_ . '/carrier_mini_' . $value['id_carrier'] . '_1.jpg');
            }
        }
    } catch (Exception $e) {
    }

    return true;
}
