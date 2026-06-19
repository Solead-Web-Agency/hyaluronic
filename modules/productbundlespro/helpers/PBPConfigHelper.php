<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

class PBPConfigHelper
{
    /** @var int  */
    static $LAYOUT_FULL_WIDTH = 1;

    /** @var int  */
    static $LAYOUT_HALF_WIDTH = 2;

    /**
     * @param $id_shop
     * @return bool|string
     */
    public static function getBundleDiscountCombinable($id_shop)
    {
        return Configuration::get('pbp_bundle_discount_combinable');
    }

    /**
     * @return int
     */
    public static function isStockManagementEnabled()
    {
        return (int)Configuration::get('PS_STOCK_MANAGEMENT');
    }

    /**
     * @param $layout
     * @param $id_tab
     */
    public static function setTabBundleLayout($layout, $id_tab)
    {
        Configuration::updateValue('pbp_tab_bundle_layout_' . $id_tab, $layout);
    }

    /**
     * @param $id_tab
     */
    public static function getTabBundleLayout($id_tab)
    {
        $layout = Configuration::get('pbp_tab_bundle_layout_'.$id_tab);
        if ($layout == 0) {
            return self::$LAYOUT_FULL_WIDTH;
        } else {
            return $layout;
        }
    }
}
