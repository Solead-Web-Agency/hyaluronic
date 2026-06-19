<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

$sql = array();
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_group`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_country`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_with_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_reminder`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_index`';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_email_queue`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_email_template`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_email_template_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_tracking`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_display_tracking`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_discount`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_cart`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_unsubscribers`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_shopping_cart`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_index_customer`';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_form`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_form_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_field`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_field_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_field_value`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_form_submit`';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_mail_log`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_display_log`';

foreach ($sql as $query) {
	if (Db::getInstance()->execute($query) == false) {
		return false;
	}
}
