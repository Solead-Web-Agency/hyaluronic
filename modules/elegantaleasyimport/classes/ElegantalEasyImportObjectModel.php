<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is a parent class for all object models in the module
 */
class ElegantalEasyImportObjectModel extends ObjectModel
{

    /**
     * Module settings
     * @var array
     */
    public $settings = array();

    /**
     * Replicate default language value to all other languages on multilang property if the other languages are not set
     * @var boolean
     */
    public $replicate_all_languages = true;

    /**
     * Table name of this model
     * @var string
     */
    public $tableName;

    /**
     * Cached models
     * @var array
     */
    private static $_models = array();

    /**
     * Object properties should be defined here.
     * Instead of defining properties individually in each class, it is better to do it here because we already have list of properties.
     * @param int $id
     * @param int $id_lang
     * @param int $id_shop
     */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        // Get definition. Prestashop way.
        $class_name = get_class($this);
        $def = ObjectModel::getDefinition($class_name);

        // Init properties of object
        $languages = Language::getLanguages(false);
        foreach ($def['fields'] as $attr => $options) {
            if (isset($def['multilang']) && $def['multilang'] && isset($options['lang']) && $options['lang'] == true) {
                foreach ($languages as $lang) {
                    $this->{$attr}[$lang['id_lang']] = null;
                }
            } else {
                $this->$attr = null;
            }
        }

        parent::__construct($id, $id_lang, $id_shop);

        // Set primary key value
        $pk = $this->def['primary'];
        $this->{$pk} = $this->id;

        $this->settings = $this->getModuleSettings();
    }

    /**
     * Returns the static model of the class. The model returned is a static instance of the class.
     * @param string $className
     * @return class
     */
    public static function model($className = __CLASS__)
    {
        if (isset(self::$_models[$className])) {
            return self::$_models[$className];
        } else {
            $model = self::$_models[$className] = new $className(null);
            return $model;
        }
    }

    /**
     * Checks if object model is multi-language
     * @return bool
     */
    public function isMultilang()
    {
        return !empty($this->def['multilang']);
    }

    /**
     * Returns attribute values of the model
     * @return array
     */
    public function getAttributes()
    {
        $languages = Language::getLanguages(false);
        $attrs = array();
        $pk = $this->def['primary'];

        $attrs[$pk] = (property_exists($this, $pk)) ? $this->$pk : null;

        foreach ($this->def['fields'] as $attr => $options) {
            if (isset($this->def['multilang']) && $this->def['multilang'] && isset($options['lang']) && $options['lang'] == true) {
                foreach ($languages as $lang) {
                    if ($this->id) {
                        $tmpModel = $this->findByPk($this->id, $lang['id_lang']);
                        $attrs[$attr][$lang['id_lang']] = $tmpModel[$attr];
                    } elseif (is_array($this->{$attr}) && isset($this->{$attr}[$lang['id_lang']])) {
                        $attrs[$attr][$lang['id_lang']] = $this->{$attr}[$lang['id_lang']];
                    } else {
                        $attrs[$attr][$lang['id_lang']] = null;
                    }
                }
            } else {
                $attrs[$attr] = $this->$attr;
            }
        }

        return $attrs;
    }

    /**
     * Returns object attributes by finding from db
     * @param int $id
     * @param int $id_lang
     * @return array
     */
    public function findByPk($id, $id_lang = null)
    {
        $id_shop = (int) Context::getContext()->shop->id;
        $id_lang = is_null($id_lang) ? (int) Context::getContext()->language->id : (int) $id_lang;

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from($this->tableName, 't');

        if ($this->isMultishop()) {
            $sql->innerJoin($this->tableName . '_shop', 's', 's.' . $this->def['primary'] . ' = t.' . $this->def['primary'] . ' AND s.id_shop = ' . (int) $id_shop);
        }

        if ($this->isMultilang()) {
            $sql->innerJoin($this->tableName . '_lang', 'l', 'l.' . $this->def['primary'] . ' = t.' . $this->def['primary'] . ' AND l.id_lang = ' . (int) $id_lang);
        }

        $sql->where('t.' . $this->def['primary'] . '=' . (int) $id);

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Returns attributes of one object by finding from db
     * @return array
     */
    public function find($criteria = array())
    {
        $id_shop = (int) Context::getContext()->shop->id;
        $id_lang = (int) Context::getContext()->language->id;

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from($this->tableName, 't');

        if ($this->isMultishop()) {
            $sql->innerJoin($this->tableName . '_shop', 's', 's.' . $this->def['primary'] . ' = t.' . $this->def['primary'] . ' AND s.id_shop = ' . (int) $id_shop);
        }

        if ($this->isMultilang()) {
            $sql->innerJoin($this->tableName . '_lang', 'l', 'l.' . $this->def['primary'] . ' = t.' . $this->def['primary'] . ' AND l.id_lang = ' . (int) $id_lang);
        }

        if (!empty($criteria['condition']) && is_array($criteria['condition'])) {
            $where = '';
            foreach ($criteria['condition'] as $key => $value) {
                $key = (strpos($key, '.') === false) ? 't.' . $key : $key;
                $where .= (Tools::strlen($where) > 0 ? ' AND ' : '') . pSQL($key) . " = " . (Validate::isInt($value) ? (int) $value : "'" . pSQL($value) . "' ");
            }
            if (!empty($where)) {
                $sql->where($where);
            }
        }

        if (!empty($criteria['order'])) {
            $orderBy = (strpos($criteria['order'], '.') === false && $criteria['order'] != 'RAND()') ? 't.' . $criteria['order'] : $criteria['order'];
            $sql->orderBy($orderBy);
        }

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Returns attributes of objects by finding from db
     * @return array
     */
    public function findAll($criteria = array())
    {
        $id_shop = (int) Context::getContext()->shop->id;
        $id_lang = (int) Context::getContext()->language->id;

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from($this->tableName, 't');

        if ($this->isMultishop()) {
            $sql->innerJoin($this->tableName . '_shop', 's', 's.' . $this->def['primary'] . ' = t.' . $this->def['primary'] . ' AND s.id_shop = ' . (int) $id_shop);
        }

        if ($this->isMultilang()) {
            $sql->innerJoin($this->tableName . '_lang', 'l', 'l.' . $this->def['primary'] . ' = t.' . $this->def['primary'] . ' AND l.id_lang = ' . (int) $id_lang);
        }

        if (!empty($criteria['condition']) && is_array($criteria['condition'])) {
            $where = "";
            foreach ($criteria['condition'] as $key => $value) {
                $key = (strpos($key, '.') === false) ? 't.' . $key : $key;
                $where .= (Tools::strlen($where) > 0) ? ' AND ' : '';
                if (is_array($value)) {
                    $count_value = count($value);
                    if ($count_value > 0) {
                        $where .= pSQL($key) . " IN (";
                        foreach ($value as $key_val => $val) {
                            $where .= ($count_value > 0 && $key_val > 0) ? ", " : "";
                            $where .= Validate::isInt($val) ? (int) $val : "'" . pSQL($val) . "'";
                        }
                        $where .= ")";
                    }
                } else {
                    $where .= pSQL($key) . " = " . (Validate::isInt($value) ? (int) $value : "'" . pSQL($value) . "' ");
                }
            }
            if (!empty($where)) {
                $sql->where($where);
            }
        }

        if (!empty($criteria['order'])) {
            $orderBy = (strpos($criteria['order'], '.') === false && $criteria['order'] != 'RAND()') ? 't.' . $criteria['order'] : $criteria['order'];
            $sql->orderBy($orderBy);
        }

        if (!empty($criteria['limit'])) {
            $offset = 0;
            if (isset($criteria['offset'])) {
                $offset = (int) $criteria['offset'];
            }
            $sql->limit($criteria['limit'], $offset);
        }

        return Db::getInstance()->executeS($sql, true, false);
    }

    /**
     * Returns number of records
     * @param array $criteria
     * @return int
     */
    public function countAll($criteria = array())
    {
        $id_shop = (int) Context::getContext()->shop->id;
        $id_lang = (int) Context::getContext()->language->id;

        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from($this->tableName, 't');

        if ($this->isMultishop()) {
            $sql->innerJoin($this->tableName . '_shop', 's', 's.' . $this->def['primary'] . ' = t.' . $this->def['primary'] . ' AND s.id_shop = ' . (int) $id_shop);
        }

        if ($this->isMultilang()) {
            $sql->innerJoin($this->tableName . '_lang', 'l', 'l.' . $this->def['primary'] . ' = t.' . $this->def['primary'] . ' AND l.id_lang = ' . (int) $id_lang);
        }

        if (!empty($criteria['condition']) && is_array($criteria['condition'])) {
            $where = '';
            foreach ($criteria['condition'] as $key => $value) {
                $key = (strpos($key, '.') === false) ? 't.' . $key : $key;
                $where .= (Tools::strlen($where) > 0 ? ' AND ' : '') . pSQL($key) . " = " . (Validate::isInt($value) ? (int) $value : "'" . pSQL($value) . "' ");
            }
            if (!empty($where)) {
                $sql->where($where);
            }
        }

        return (int) Db::getInstance()->getValue($sql);
    }

    /**
     * Validates and sets submitted values to model attributes
     * @param bool $htmlentities
     * @return array
     */
    public function validateAndAssignModelAttributes($htmlentities = true)
    {
        $languages = Language::getLanguages(false);
        if (method_exists($this, 'cacheFieldsRequiredDatabase')) {
            $this->cacheFieldsRequiredDatabase();
        }
        $errors = array();
        $required_fields_database = (isset(self::$fieldsRequiredDatabase[get_class($this)])) ? self::$fieldsRequiredDatabase[get_class($this)] : array();
        foreach ($this->def['fields'] as $field => $data) {
            // Init model attribute
            $this->{$field} = property_exists($this, $field) ? $this->{$field} : null;

            // Check if field is required by user
            if (in_array($field, $required_fields_database)) {
                $data['required'] = true;
            }

            // Setting submitted value to field
            if (isset($data['lang']) && $data['lang'] == true) {
                $firstValue = null;
                foreach ($languages as $lan) {
                    $value = Tools::getValue($field . '_' . $lan['id_lang']);
                    if (!$firstValue && $value) {
                        $firstValue = $value;
                    }
                    if (!$value && $firstValue && $this->replicate_all_languages) {
                        $value = $firstValue;
                    }
                    $this->{$field}[$lan['id_lang']] = $value;
                    if ($lan['id_lang'] == Context::getContext()->language->id && ($error = $this->validateModelAttribute($field, $value, $data, $htmlentities, $lan['iso_code'])) !== true) {
                        $errors[$field] = $error;
                    }
                }
            } else {
                $value = Tools::getValue($field, $this->{$field});
                if (is_array($value)) {
                    $value = ElegantalEasyImportTools::serialize($value);
                }
                $this->{$field} = $value;

                if (($error = $this->validateModelAttribute($field, $value, $data, $htmlentities)) !== true) {
                    $errors[$field] = $error;
                }
            }
        }

        return $errors;
    }

    /**
     * Validates attribute of model object
     * @param string $field
     * @param string $value
     * @param array $data
     * @param bool $htmlentities
     * @param string $lang
     * @return bool|string
     */
    protected function validateModelAttribute($field, $value, $data, $htmlentities = true, $lang = '')
    {
        $error = false;

        // Checking for required fields
        if (isset($data['required']) && $data['required'] && empty($value) && $value !== '0') {
            if (!$this->id || $field != 'passwd') {
                $error = '<b>' . self::displayFieldName($field, get_class($this), $htmlentities) . ' ' . $lang . ' </b> is required.';
            }
        }

        // Checking for maximum fields sizes
        if (isset($data['size']) && !empty($value) && Tools::strlen($value) > $data['size']) {
            $error = sprintf('%1$s is too long. Maximum length: %2$d', self::displayFieldName($field, get_class($this), $htmlentities) . ' ' . $lang, $data['size']);
        }

        // Checking for fields validity
        // Hack for postcode required for country which does not have postcodes
        if (!empty($value) || $value === '0' || ($field == 'postcode' && $value == '0')) {
            if (isset($data['validate']) && !call_user_func(array('Validate', $data['validate']), $value) && (!empty($value) || $data['required'])) {
                $error = '<b>' . self::displayFieldName($field, get_class($this), $htmlentities) . ' ' . $lang . '</b> is invalid.';
            }
        }

        return $error ? $error : true;
    }

    /**
     * Returns field name translation.
     * Overridden with purpose of formatting names
     *
     * @param string       $field        Field name
     * @param string       $class        ObjectModel class name
     * @param bool         $htmlentities If true, applies htmlentities() to result string
     * @param Context|null $context      Context object
     *
     * @return string
     */
    public static function displayFieldName($field, $class = __CLASS__, $htmlentities = true, Context $context = null)
    {
        $displayField = parent::displayFieldName($field, $class, $htmlentities, $context);
        return ($displayField === $field) ? ucwords(str_replace('_', ' ', $displayField)) : $displayField;
    }

    /**
     * Returns module settings
     * @return array
     */
    public function getModuleSettings()
    {
        $settings = array();
        $module_name = basename(realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));
        $module = Module::getInstanceByName($module_name);
        if ($module && Validate::isLoadedObject($module)) {
            $settings = $module->getSettings();
        }
        return $settings;
    }
}
