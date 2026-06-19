<?php
/**
 * admin.conf.php file defines all needed constants and variables for admin context
 *
 *@author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 *@copyright 2003-2018 Business Tech SARL
 *@license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*
 * include common conf
 */
require_once(dirname(__FILE__) . '/common.conf.php');

/* defines modules support product id */
define('_GACT_SUPPORT_ID', '16397');

/* defines activate the BT support if false we use the ADDONS support url */
define('_GACT_SUPPORT_BT', false);
//define('_GACT_SUPPORT_BT', true);

/* defines activate the BT support if false we use the ADDONS support url */
define('_GACT_SUPPORT_URL', 'https://addons.prestashop.com/');
//define('_GACT_SUPPORT_URL', 'http://www.businesstech.fr/');

/* defines feed list settings tpl */
define('_GACT_TPL_WELCOME', 'welcome-include.tpl');

/*
 * defines admin library path
 * uses => to include class files
 */
define('_GACT_PATH_LIB_ADMIN', _GACT_PATH_LIB . 'admin/');
/*
 * defines admin path tpl
 * uses => to set good absolute path
 */
define('_GACT_TPL_ADMIN_PATH', 'admin/');
/*
 * defines confirm tpl
 * uses => with display admin interface
 */
define('_GACT_TPL_CONFIRM', 'confirm.tpl');
/*
 * defines header tpl
 * uses => with display admin interface
 */
define('_GACT_TPL_HEADER', 'header.tpl');
/*
 * defines body tpl
 * uses => with display admin interface
 */
define('_GACT_TPL_BODY', 'body.tpl');
/*
 * defines basic tpl
 * uses => with display admin interface
 */
define('_GACT_TPL_BASIC_SETTINGS', 'basics.tpl');
/*
 * defines advance tpl
 * uses => with display admin interface
 */
define('_GACT_TPL_ADVANCED_SETTINGS', 'advanced-settings.tpl');
/*
 * defines constant for external BT API URL
 * uses => with display admin interface
 */
define('_GACT_BT_FAQ_MAIN_URL', 'http://faq.businesstech.fr/');
/*
 * defines variable for sql update
 * uses => with admin
 */
$GLOBALS['GACT_SQL_UPDATE'] = array(
//    'table' => array(
//        'orders' => _GACT_ORDER_SQL_FILE,
//    ),
//    'field' => array(
//        'type' => array(
//            'table' => 'orders',
//            'file'  => _GACT_ORDER_ADD_SQL_FILE,
//        ),
//    ),
);
/*
 * defines variable for setting all request params
 * uses => with admin interface
 */
$GLOBALS['GACT_REQUEST_PARAMS'] = array(
    'advice' => array('action' => 'display', 'type' => 'advice'),
    'basic' => array('action' => 'update', 'type' => 'basic'),
    'advanced' => array('action' => 'update', 'type' => 'advanced'),
);