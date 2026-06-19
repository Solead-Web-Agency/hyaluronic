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
class GlsTrackingStateClass extends ObjectModel
{
    public $id_order;

    public $current_state;

    public $date_add;

    public $date_upd;

    public static $definition = [
        'table' => 'gls_tracking_state',
        'primary' => 'id_gls_tracking_state',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'current_state' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 30, 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function createDbTable()
    {
        return Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gls_tracking_state` (
            `id_gls_tracking_state` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_order` int(10) UNSIGNED NOT NULL,
            `current_state` varchar(30) NOT NULL,
            `date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_gls_tracking_state`),
            KEY `id_order` (`id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
    }

    public static function removeDbTable()
    {
        return Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'gls_tracking_state`');
    }

    public static function getByIdOrder($idOrder)
    {
        if (!empty($idOrder) && is_numeric($idOrder) && (int) $idOrder > 0) {
            $sql = new DbQuery();
            $sql->select('t.*');
            $sql->from('gls_tracking_state', 't');
            $sql->where('t.`id_order` = ' . (int) $idOrder);

            $result = Db::getInstance()->getRow($sql);
            if ($result) {
                return $result;
            }
        }

        return false;
    }
}
