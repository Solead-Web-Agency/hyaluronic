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

use Nukium\GLS\Common\Entity\CacheInterface;

class GlsCacheClass extends ObjectModel implements CacheInterface
{
    public $id;

    public $footprint;

    public $data;

    public $ttl;

    public $isStored = true;

    public static $definition = [
        'table' => 'gls_cache',
        'primary' => 'id_gls_cache',
        'multilang' => false,
        'fields' => [
            'footprint' => [
                'type' => self::TYPE_STRING,
                'required' => true,
            ],
            'data' => [
                'type' => self::TYPE_STRING,
                'required' => true,
            ],
            'ttl' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
        ],
    ];

    public function getMyFootprint()
    {
        return $this->footprint;
    }

    public function setMyFootprint($footprint)
    {
        $this->footprint = $footprint;

        return $this;
    }

    public function getMyData()
    {
        return $this->data;
    }

    public function setMyData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getMyTtl()
    {
        return new DateTime($this->ttl);
    }

    public function setMyTtl(DateTimeInterface $ttl)
    {
        $this->ttl = $ttl->format('Y-m-d');

        return $this;
    }

    public function getMyIsStored()
    {
        return $this->isStored;
    }

    public function setMyIsStored($isStored)
    {
        $this->isStored = $isStored;

        return $this;
    }

    public function save($null_values = false, $auto_date = true)
    {
        if ($this->getMyIsStored()) {
            return $this->update($null_values);
        } else {
            return $this->add($auto_date, $null_values);
        }
    }

    public static function createDbTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'gls_cache (
                id_gls_cache INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                footprint VARCHAR(128) NOT NULL,
                data TEXT NOT NULL,
                ttl datetime NOT NULL,
                PRIMARY KEY(id_gls_cache)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
        );
    }

    public static function removeDbTable()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'gls_cache`');
    }
}
