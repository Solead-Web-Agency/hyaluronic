<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

class CedWishMenu
{
    public static $module_tabs = array(
        array(
            'class' => 'AdminCedWish',
            'name' => 'Wish Integration',
            'parent' => 0
        ),
        array(
            'class' => 'AdminCedWishProfile',
            'name' => 'Profile',
            'parent' => 'AdminCedWish'
        ),
        array(
            'class' => 'AdminCedWishProduct',
            'name' => 'Product(s)',
            'parent' => 'AdminCedWish'
        ),
        array(
            'class' => 'AdminCedWishOrder',
            'name' => 'Order(s)',
            'parent' => 'AdminCedWish'
        ),
        array(
            'class' => 'AdminCedWishFeed',
            'name' => 'Bulk Process(s)',
            'parent' => 'AdminCedWish'
        ),
        array(
            'class' => 'AdminCedWishColorMapping',
            'name' => 'Color Mapping',
            'parent' => 'AdminCedWish'
        ),
        array(
            'class' => 'AdminCedWishWarehouse',
            'name' => 'Warehouse',
            'parent' => 'AdminCedWish'
        ),
        array(
            'class' => 'AdminCedWishBatch',
            'name' => 'Sync Existing',
            'parent' => 'AdminCedWish'
        ),
        array(
            'class' => 'AdminCedWishSetting',
            'name' => 'Configuration',
            'parent' => 'AdminCedWish'
        ),
        array(
            'class' => 'AdminCedWishBulk',
            'name' => 'Bulk Process',
            'parent' => 'AdminCedWish',
            'active' => 0
        )
    );
    protected $name = '';

    public function __construct($module_name)
    {
        $this->name = $module_name;
    }

    public function createTabs()
    {
        foreach (self::$module_tabs as $tab) {
            if ($tab['parent']) {
                $parent = (int)Tab::getIdFromClassName(trim($tab['parent']));
            } else {
                $parent = 0;
            }
            $active = 1;
            if (isset($tab['active']) && ($tab['active'] == 0)) {
                $active = 0;
            }

            $this->installTab(
                $tab['class'],
                $tab['name'],
                $parent,
                $active
            );
        }
    }

    public function installTab($class_name, $tab_name, $parent, $active = 1)
    {
        $tab = new Tab();
        $tab->active = $active;
        $tab->class_name = $class_name;
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tab_name;
        }
        if ($parent == 0 && _PS_VERSION_ >= '1.7') {
            $tab->id_parent = (int)Tab::getIdFromClassName('SELL');
            $tab->icon = 'flight';
        } else {
            $tab->id_parent = $parent;
        }
        $tab->module = $this->name;
        return $tab->add();
    }

    public function removeTabs()
    {
        foreach (self::$module_tabs as $tab) {
            $this->uninstallTab($tab['class']);
        }
    }

    public function uninstallTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            try {
                $tab = new Tab($id_tab);
                return $tab->delete();
            } catch (PrestaShopDatabaseException $e) {
                return false;
            } catch (PrestaShopException $e) {
                return false;
            }
        } else {
            return false;
        }
    }
}
