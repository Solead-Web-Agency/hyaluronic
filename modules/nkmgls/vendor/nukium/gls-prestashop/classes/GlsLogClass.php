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
class GlsLogClass extends ObjectModel
{
    public $id_shop_group;

    public $id_employee;

    public $message;

    public $date_add;

    public static $definition = [
        'table' => 'gls_log',
        'primary' => 'id_gls_log',
        'fields' => [
            'id_shop_group' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'message' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 16777216],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function createDbTable()
    {
        return Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gls_log` (
            `id_gls_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_shop_group` int(11) UNSIGNED NOT NULL DEFAULT \'1\',
            `id_shop` int(11) UNSIGNED NOT NULL DEFAULT \'1\',
            `id_employee` int(10) UNSIGNED DEFAULT NULL,
            `message` text NOT NULL,
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_gls_log`),
            KEY `id_shop_group` (`id_shop_group`),
            KEY `id_shop` (`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
    }

    public static function removeDbTable()
    {
        return Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'gls_log`');
    }

    public function log($message)
    {
        if (!empty($message)) {
            $context = Context::getContext();

            $this->message = $message;
            $this->id_shop = ($this->id_shop) ? $this->id_shop : $context->shop->id;
            $this->id_shop_group = ($this->id_shop_group) ? $this->id_shop_group : $context->shop->id_shop_group;

            $idEmployee = 0;
            if ($this->id_employee) {
                $idEmployee = $this->id_employee;
            } elseif (Validate::isLoadedObject($context->employee)) {
                $idEmployee = $context->employee->id;
            }

            $this->id_employee = $idEmployee;

            return parent::add();
        }
    }
}
