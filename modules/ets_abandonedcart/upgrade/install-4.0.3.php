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

function upgrade_module_4_0_3($object)
{
    try {
        EtsAbancartTools::createMailUploadFolder();
        $object->registerHook('actionAdminControllerSetMedia');
        $object->registerHook('displayBoPurchasedProduct');
        //Install tabs
        if ($id_parent = $object->_addTab(['label' => $object->l('Customer reminders'), 'origin' => 'Customer reminders'])) {
            $tabs = EtsAbancartDefines::getInstance($object)->getFields('menus');
            $object->_addTabs($id_parent, $tabs);
        }
        $emailsTemp = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "ets_abancart_email_template` WHERE is_init=1");
        foreach ($emailsTemp as $temp) {
            if ($temp['id_ets_abancart_email_template'] == 1) {
                Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "ets_abancart_email_template` SET type_of_campaign='without_discount' WHERE id_ets_abancart_email_template=1");
            } else {
                Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "ets_abancart_email_template` SET type_of_campaign='with_discount' WHERE id_ets_abancart_email_template=" . (int)$temp['id_ets_abancart_email_template']);
            }
        }
        //Email template

        $templates = array('email', 'customer');
        $languages = Language::getLanguages(false);
        $shops = Shop::getShops(false);
        if ($templates) {
            foreach ($templates as $type) {
                if (($dir = dirname(__FILE__) . '/../views/img/init/' . $type) && is_dir($dir)) {
                    if ($type == 'email') {
                        $files = array(1, 2, 3, 4, 5);
                    } else {
                        $files = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);
                    }
                    $ik = 0;
                    foreach ($files as $file) {
                        if ($file) {
                            //
                        }
                        $ik++;
                        $folderPath = $dir . '/' . (int)$ik;
                        foreach ($shops as $shop) {
                            if ($type == 'email') {
                                if ($ik > 4) {
                                    $emailTemplate = new EtsAbancartEmailTemplate();
                                    $emailTemplate->id_shop = (int)$shop['id_shop'];
                                    $emailTemplate->thumbnail = $type . (int)$ik . '.jpg';
                                    $emailTemplate->template_type = 'email';
                                    $emailTemplate->type_of_campaign = $ik == 5 ? 'without_discount' : 'with_discount';
                                    $emailTemplate->name = array();
                                    $emailTemplate->temp_path = array();
                                    $emailTemplate->is_init = 1;
                                    $emailTemplate->folder_name = $type . (int)$ik;
                                    $tempPath = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $emailTemplate->folder_name;
                                    EtsAbancartTools::copyFolder($folderPath, $tempPath);
                                    foreach ($languages as $l) {
                                        if (!file_exists($tempPath . '/index_' . $l['iso_code'] . '.html')) {
                                            @copy($tempPath . '/index_en.html', $tempPath . '/index_' . $l['iso_code'] . '.html');
                                        }
                                        $emailTemplate->temp_path[$l['id_lang']] = 'index_' . $l['iso_code'] . '.html';
                                        $emailTemplate->name[$l['id_lang']] = pSQL(Tools::ucfirst($type)) . ' template ' . (int)$ik;
                                    }
                                    $emailTemplate->add();
                                }

                            } else {
                                $emailTemplate = new EtsAbancartEmailTemplate();
                                $emailTemplate->id_shop = (int)$shop['id_shop'];
                                $emailTemplate->thumbnail = $type . (int)$ik . '.jpg';
                                $emailTemplate->template_type = 'customer';
                                $emailTemplate->type_of_campaign = in_array($ik, array(1, 4, 6, 8, 11)) ? 'without_discount' : 'with_discount';
                                $emailTemplate->is_init = 1;
                                $emailTemplate->name = array();
                                $emailTemplate->temp_path = array();
                                $emailTemplate->folder_name = $type . (int)$ik;
                                $tempPath = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $emailTemplate->folder_name;
                                EtsAbancartTools::copyFolder($folderPath, $tempPath);
                                foreach ($languages as $l) {
                                    if (!file_exists($tempPath . '/index_' . $l['iso_code'] . '.html')) {
                                        @copy($tempPath . '/index_en.html', $tempPath . '/index_' . $l['iso_code'] . '.html');
                                    }
                                    $emailTemplate->temp_path[$l['id_lang']] = 'index_' . $l['iso_code'] . '.html';
                                    $emailTemplate->name[$l['id_lang']] = pSQL(Tools::ucfirst($type)) . ' template ' . (int)$ik;
                                }
                                $emailTemplate->add();
                            }
                        }
                    }
                }
            }
        }

        Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "ets_abancart_email_template` SET type_of_campaign='both' WHERE is_init!='1'");
        Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index` 
                ADD `id_ets_abancart_reminder` INT(11) UNSIGNED NOT NULL AFTER `id_customer`, 
                ADD `id_ets_abancart_campaign` INT(11) UNSIGNED NOT NULL AFTER `id_ets_abancart_reminder`,
                DROP PRIMARY KEY, 
                ADD PRIMARY KEY (`id_cart`, `id_ets_abancart_reminder`, `id_ets_abancart_campaign`) USING BTREE
                ;
        ');

        //Update discount option
        $campaignCustomers = Db::getInstance()->executeS("SELECT id_ets_abancart_campaign FROM `" . _DB_PREFIX_ . "ets_abancart_campaign` WHERE campaign_type='customer'");
        Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "ets_abancart_campaign` SET email_timing_option=" . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_REGISTRATION . " WHERE  campaign_type='customer'");

        Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "ets_abancart_reminder` SET `quantity`=1, `quantity_per_user`=1");

        //Update campaign group
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($campaignCustomers as $campaign) {
            foreach ($groups as $group) {
                Db::getInstance()->execute("INSERT IGNORE INTO `" . _DB_PREFIX_ . "ets_abancart_campaign_group` (id_ets_abancart_campaign, id_group) VALUES(" . (int)$campaign['id_ets_abancart_campaign'] . ", " . (int)$group['id_group'] . ")");
            }
        }
    }
    catch (Exception $ex){
        //
    }

    return true;
}

function format_contents($file)
{
    $content = Tools::file_get_contents($file);
    $shop = Context::getContext()->shop;
    $image = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain) . $shop->getBaseURI() . 'modules/ets_abandonedcart/views/img/origin/bg_email.jpg';
    $content = str_replace('[image]', $image, $content);

    return $content;
}