<?php
/**
 * cron.php file execute add your description
 *
 * @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 * @copyright 2003-2019 Business Tech SARL
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/gmerchantcenter.php');

// get the token
$sToken = Tools::getValue('token');

/* instantiate the main class */
$oModule = new GMerchantCenter();

if ($sToken == GMerchantCenter::$conf['GMC_FEED_TOKEN']) {
    // use case - handle to generate XML files
    $_POST['sAction'] = Tools::getIsset('sAction') ? Tools::getValue('sAction') : 'generate';
    $_POST['sType'] = Tools::getIsset('sType') ? Tools::getValue('sType') : 'cron';

    echo $oModule->getContent();
} else {
    echo 'Invalid security token';
}
