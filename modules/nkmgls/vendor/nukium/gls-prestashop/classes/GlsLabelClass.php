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
class GlsLabelClass extends ObjectModel
{
    public $id_order;

    public $shipping_number;

    public $weight;

    public $gls_product;

    public $delivery_date;

    public $reference1;

    public $reference2;

    public $date_add;

    public $date_upd;

    public static $definition = [
        'table' => 'gls_label',
        'primary' => 'id_gls_label',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'shipping_number' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255, 'required' => true],
            'weight' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
            'gls_product' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255, 'required' => true],
            'delivery_date' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'reference1' => ['type' => self::TYPE_STRING, 'size' => 255],
            'reference2' => ['type' => self::TYPE_STRING, 'size' => 255],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function createDbTable()
    {
        return Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gls_label` (
            `id_gls_label` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_order` int(10) UNSIGNED NOT NULL,
            `shipping_number` varchar(255) NOT NULL,
            `weight` decimal(20,6) NOT null,
            `gls_product` varchar(255) NOT NULL,
            `delivery_date` datetime NOT NULL,
            `reference1` varchar(255) DEFAULT NULL,
            `reference2` varchar(255) DEFAULT NULL,
            `date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_gls_label`),
            KEY `id_order` (`id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
    }

    public static function removeDbTable()
    {
        return Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'gls_label`');
    }
}
