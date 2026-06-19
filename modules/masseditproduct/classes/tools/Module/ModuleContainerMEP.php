<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2012-2019 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class ModuleContainerMEP
{
    /**
     * @var Module
     */
    public $module;
    /**
     * @var Context
     */
    public $context;
    /**
     * @var array
     */
    public $hooks = array();

    /**
     * @var array
     */
    public $classes = array();

    /**
     * @var array
     */
    public $config = array();

    /**
     * @var array
     */
    public $tabs = array();

    public $documentation = false;
    public $documentation_type = null;

    public $install_fixtures = false;

    const DOCUMENTATION_TYPE_TAB = 'tab';
    const DOCUMENTATION_TYPE_SIMPLE = 'simple';

    /**
     * ModuleContainerMEP constructor.
     * @param $module Module
     */
    protected function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->documentation_type = self::DOCUMENTATION_TYPE_SIMPLE;
    }

    /**
     * @var LoggerMEP
     */
    protected static $instance = null;

    public static function getInstance($module)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($module);
        }
        return self::$instance;
    }

    /**
     * @param array $hooks
     * @return $this
     */
    public function setHooks($hooks)
    {
        $this->hooks = $hooks;
        return $this;
    }

    /**
     * @param array $classes
     * @return $this
     */
    public function setClasses($classes)
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * @param array $configs
     * @return $this
     */
    public function setConfig($configs)
    {
        $this->config = $configs;
        return $this;
    }

    /**
     * @param array $tabs
     * @return $this
     */
    public function setTabs($tabs)
    {
        $this->tabs = $tabs;
        return $this;
    }

    public function getTabs()
    {
        return $this->formatTabs($this->tabs);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setInstallFixtures($value)
    {
        $this->install_fixtures = $value;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setDocumentationType($type)
    {
        $this->documentation_type = $type;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setDocumentation($value)
    {
        $this->documentation = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function registerHooks()
    {
        foreach ($this->hooks as $hook) {
            $this->module->registerHook($hook);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function installClasses()
    {
        foreach ($this->classes as $class) {
            HelperDbMEP::loadClass($class)->installDb();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function uninstallClasses()
    {
        foreach ($this->classes as $class) {
            HelperDbMEP::loadClass($class)->uninstallDb();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function installConfig()
    {
        foreach ($this->config as $name => $value) {
            $type = (is_array($this->config[$name]) ? ConfMEP::TYPE_ARRAY : ConfMEP::TYPE_STRING);
            ConfMEP::setConf($name, $value, $type);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function uninstallConfig()
    {
        foreach (array_keys($this->config) as $name) {
            ConfMEP::deleteConf($name);
        }
        return true;
    }

    /**
     * @param array $tabs
     * @return array
     */
    public function formatTabs($tabs)
    {
        if (version_compare(_PS_VERSION_, '1.7.1.0', '>=')) {
            foreach ($tabs as &$tab) {
                if (!is_array($tab['name'])) {
                    $tab['name'] = array('en' => $tab['name']);
                }

                $languages = ToolsModuleMEP::getLanguages(false);
                $name = array();
                foreach ($languages as $language) {
                    $name[$language['locale']] = (isset($tab['name'][$language['iso_code']])
                        ? $tab['name'][$language['iso_code']] :
                        $tab['name']['en']);
                }

                $tab = array(
                    'class_name' => $tab['tab'],
                    'ParentClassName' => $tab['parent'],
                    'name' => $name,
                    'visible' => (!isset($tab['visible']) ? true : $tab['visible']),
                    'icon' => (!isset($tab['icon']) ? '' : $tab['icon']),
                );
            }
        }
        return $tabs;
    }

    /**
     * @return bool
     */
    public function installTabs()
    {
        if (version_compare(_PS_VERSION_, '1.7.1.0', '<')) {
            foreach ($this->tabs as $tab) {
                ToolsModuleMEP::createTab(
                    $this->module->name,
                    $tab['tab'],
                    $tab['parent'],
                    $tab['name'],
                    (isset($tab['visible']) ? !$tab['visible'] : false)
                );
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function uninstallTabs()
    {
        foreach ($this->tabs as $tab) {
            ToolsModuleMEP::deleteTab(
                isset($tab['tab']) ? $tab['tab'] : $tab['class_name']
            );
        }
        return true;
    }

    /**
     * @return bool
     */
    public function install()
    {
        return $this->registerHooks()
        && $this->installClasses()
        && $this->importFixtures()
        && $this->installConfig()
        && $this->installTabs();
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return $this->uninstallClasses()
        && $this->uninstallConfig()
        && $this->uninstallTabs();
    }

    public function getDocumentation()
    {
        DocumentationMEP::assignDocumentation();
        $return_back_link = '#';
        if (count($this->tabs)) {
            $return_back_link = $this->context->link->getAdminLink(
                isset($this->tabs[0]['class_name'])
                ? $this->tabs[0]['class_name']
                : $this->tabs[0]['tab']
            );
        }

        $this->context->smarty->assign('return_back_link', $return_back_link);
        return ToolsModuleMEP::fetchTemplate('admin/documentation.tpl');
    }

    public function getContent($content)
    {
        if (!$this->documentation) {
            return $content;
        } else {
            if ($this->documentation_type == self::DOCUMENTATION_TYPE_SIMPLE) {
                return $this->getDocumentation();
            }
            SmartyMEP::registerSmartyFunctions();
            $this->context->smarty->assign(array(
                'content_tab' => $content,
                'documentation' => $this->getDocumentation()
            ));
            return ToolsModuleMEP::fetchTemplate('admin/content.tpl');
        }
    }

    public function importFixtures()
    {
        if (!$this->install_fixtures) {
            return true;
        }
        $fixture_class = FixtureMEP::getInstance($this->module->name);

        foreach ($this->classes as $class_name) {
            $fixture_class->importEntity($class_name);
        }
        return true;
    }
    
    public function addColumn()
    {
        $res = true;
        $list_fields = Db::getInstance()->executeS('SHOW FIELDS FROM `'._DB_PREFIX_.'product_shop`');
        if (is_array($list_fields)) {
            foreach ($list_fields as $k => &$field) {
                $field = $field['Field'];
            }
            if (!in_array('final_price', $list_fields) && $res) {
                $res = Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'product_shop`
                ADD `final_price` DECIMAL(20,6) NOT NULL');
            }
        }
        return $res;
    }
}
