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

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_4_0_7()
{
    Db::getInstance()->execute("CREATE  INDEX ets_ab_reminder_i_currency ON `"._DB_PREFIX_."ets_abancart_reminder` (`id_currency`)");
    Db::getInstance()->execute("CREATE  INDEX ets_ab_ic_i_lang ON `"._DB_PREFIX_."ets_abancart_index_customer` (`id_lang`)");
    Db::getInstance()->execute("CREATE  INDEX ets_ab_fs_i_cl ON `"._DB_PREFIX_."ets_abancart_form_submit` (`id_customer`,`id_lang`, `id_currency`,`id_country`,`id_cart`)");
    Db::getInstance()->execute("CREATE  INDEX ets_ab_f_i_sed ON `"._DB_PREFIX_."ets_abancart_form` (`id_shop`,`is_init`, `enable`, `enable_captcha`, `display_thankyou_page`, `disable_captcha_lic`)");
    Db::getInstance()->execute("CREATE  INDEX ets_ab_eq_i_slr ON `"._DB_PREFIX_."ets_abancart_email_queue` (`id_shop`,`id_lang`,`id_cart`,`id_customer`,`id_ets_abancart_reminder`, `sent`)");
    Db::getInstance()->execute("CREATE  INDEX ets_ab_eq_i_em ON `"._DB_PREFIX_."ets_abancart_email_queue` (`email`)");
    return true;
}