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

namespace Nukium\PrestaShop\GLS\Service\Repository;

class AbstractRepository
{
    const NO_SHOP_MODE = 0;
    const COLUMN_SHOP_MODE = 1;
    const TABLE_SHOP_MODE = 2;

    protected $className;

    protected $tableName;

    protected $defaultOptions;

    public function __construct($className, $defaultOptions)
    {
        $tableName = $className::$definition['table'];

        if (!isset($defaultOptions['alias'])) {
            $defaultOptions['alias'] = 'a';
        }

        if (!isset($defaultOptions['primaryIdColumnName'])) {
            $defaultOptions['primaryIdColumnName'] = "id_{$tableName}";
        }

        if (!isset($defaultOptions['shopAlias'])) {
            $defaultOptions['shopAlias'] = 'shop';
        }

        if (!isset($defaultOptions['shopMode'])) {
            $defaultOptions['shopMode'] = self::NO_SHOP_MODE;
        }

        if (!isset($defaultOptions['shopShareMode'])) {
            $defaultOptions['shopShareMode'] = false;
        }

        if (!isset($defaultOptions['shopTableName'])) {
            $defaultOptions['shopTableName'] = "{$tableName}_shop";
        }

        $this->className = $className;
        $this->tableName = $tableName;
        $this->defaultOptions = $defaultOptions;
    }

    public function createQueryBuilder($options = [])
    {
        $alias = $this->getOption($options, 'alias');

        $sql = new \DbQuery();
        $sql->from($this->tableName, $alias);

        $this->addShopConstraint($sql, $options);

        return $sql;
    }

    public function createQueryBuilderForId($id, $options = [])
    {
        $id = pSQL($id);
        $alias = $this->getOption($options, 'alias');
        $primaryIdColumnName = $this->getOption($options, 'primaryIdColumnName');

        $options['shopMode'] = (isset($options['shopMode']))
            ? $options['shopMode']
            : self::NO_SHOP_MODE
        ;
        $options['shopShareMode'] = (isset($options['shopShareMode']))
            ? $options['shopShareMode']
            : false
        ;

        return $this->createQueryBuilder($options)
            ->where("{$alias}.{$primaryIdColumnName} = {$id}")
        ;
    }

    public function findBy($criteria = [], $options = [])
    {
        $alias = $this->getOption($options, 'alias');

        $select = (isset($criteria['select']))
            ? $criteria['select']
            : "{$alias}.*"
        ;
        $where = (isset($criteria['where']))
            ? $criteria['where']
            : []
        ;
        $orderBy = (isset($criteria['orderBy']))
            ? $criteria['orderBy']
            : null
        ;
        $limit = (isset($criteria['limit']))
            ? $criteria['limit']
            : null
        ;
        $offset = (isset($criteria['offset']))
            ? $criteria['offset']
            : 0
        ;

        $sql = $this->createQueryBuilder($options)
            ->select($select)
        ;

        foreach ($where as $value) {
            $sql->where($value);
        }

        if ($orderBy !== null) {
            $sql->orderBy($orderBy);
        }

        if ($limit !== null) {
            $sql->limit($limit, $offset);
        }

        $res = \Db::getInstance()->executeS($sql);

        if (!is_array($res)) {
            return [];
        }

        return \ObjectModel::hydrateCollection(
            $this->className,
            $res
        );
    }

    public function findOneBy($criteria = [], $options = [])
    {
        $res = $this->findBy($criteria, $options);

        if (empty($res)) {
            return null;
        }

        return $res[0];
    }

    protected function addShopConstraint(\DbQuery $sql, $options = [])
    {
        $alias = $this->getOption($options, 'alias');
        $shopMode = $this->getOption($options, 'shopMode');
        $shopShareMode = $this->getOption($options, 'shopShareMode');

        switch ($shopMode) {
            case self::COLUMN_SHOP_MODE:
                $sql->where($this->getShopSqlRestriction($shopShareMode, $alias));
                break;

            case self::TABLE_SHOP_MODE:
                $shopAlias = $this->getOption($options, 'shopAlias');
                $shopTableName = $this->getOption($options, 'shopTableName');
                $primaryIdColumnName = $this->getOption($options, 'primaryIdColumnName');

                $sql->leftJoin(
                    $shopTableName,
                    $shopAlias,
                    "{$alias}.{$primaryIdColumnName} = {$shopAlias}.{$primaryIdColumnName}"
                );
                $sql->where($this->getShopSqlRestriction($shopShareMode, $shopAlias));
                break;

            default:
                return;
        }
    }

    protected function getShopSqlRestriction($shopShareMode, $alias)
    {
        return '1 ' . \Shop::addSqlRestriction($shopShareMode, $alias);
    }

    protected function getOption($options, $name)
    {
        if (!isset($options[$name])) {
            return $this->defaultOptions[$name];
        }

        return $options[$name];
    }
}
