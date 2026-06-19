<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

include_once dirname(__FILE__).'/ArContactUsMenuConfigAbstract.php';

class ArContactUsMenuMobileConfig extends ArContactUsMenuConfigAbstract
{
    public function overrideUnsafeAttributes()
    {
        return array(
            'menu_width'
        );
    }
    
    public function attributeDefaults()
    {
        return array(
            'menu_size' => 'small',
            'menu_width' => '0',
            'item_style' => 'rounded',
            'item_border_style' => '#dashed',
            'item_border_color' => '#dddddd',
            'menu_header_on' => '1',
            'menu_header' => 'How would you like to contact us?',
            'header_close' => '1',
            'header_close_bg' => '#008749',
            'header_close_color' => '#ffffff',
            'menu_bg' => '#ffffff',
            'menu_color' => '#3b3b3b',
            'menu_subtitle_color' => '#787878',
            'menu_subtitle_hcolor' => '#787878',
            'menu_hbg' => '#f0f0f0',
            'menu_hcolor' => '#3b3b3b',
            'shadow_size' => '30',
            'shadow_opacity' => '0.2'
        );
    }
}
