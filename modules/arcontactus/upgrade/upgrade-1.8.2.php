<?php
/**
* 2012-2018 Areama
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
*  @author    Areama <contact@areama.net>
*  @copyright 2018 Areama
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_8_2($module)
{
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'arcontactus`
	CHANGE COLUMN `icon` `icon` TEXT NULL DEFAULT NULL AFTER `id_contactus`';
    Db::getInstance()->execute($sql);
    
    $qrTitle = array();
    foreach (Language::getLanguages(true) as $lang) {
        $qrTitle[$lang['id_lang']] = '';
    }
    $data = array(
        'enable_qr' => 0,
        'qr_title' => $qrTitle,
        'qr_link' => '',
        'icon_type' => 'builtin',
        'icon_svg' => '',
        'icon_img' => '',
        'no_container' => 0
    );
    
    $sql = 'UPDATE `' . _DB_PREFIX_ . "arcontactus` SET `data` = '" . json_encode($data) . "'";
    Db::getInstance()->execute($sql);
    
    $defaults = array(
        'ARCU_DESKTOP' => 1,
        'ARCUB_BUTTON_ICON_TYPE' => 'builtin',
        'ARCUBM_BUTTON_ICON_TYPE' => 'builtin',
        'ARCUB_BUTTON_ICON_SIZE' => 24,
        'ARCUBM_BUTTON_ICON_SIZE' => 24,
        'ARCUB_ANIMATION' => 'flipInY',
        'ARCUBM_ANIMATION' => 'zoomIn',
        'ARCUM_MENU_STYLE' => 0,
        'ARCUMM_MENU_STYLE' => 0,
        'ARCUM_POPUP_ANIMATION' => 'fadeindown',
        'ARCUMM_POPUP_ANIMATION' => 'fadeindown',
        'ARCUM_ITEMS_ANIMATION' => 'downtoup',
        'ARCUMM_ITEMS_ANIMATION' => '',
        'ARCUB_ICON_ANIMATION_PAUSE' => 2000,
        'ARCUBM_ICON_ANIMATION_PAUSE' => 2000
    );
    
    foreach ($defaults as $key => $val) {
        Configuration::updateValue($key, $val);
    }
    
    return true;
}
