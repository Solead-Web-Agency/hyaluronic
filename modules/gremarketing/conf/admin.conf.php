<?php

/**
 * Google Dynamic Remarketing
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

// include common conf
require_once(dirname(__FILE__) . '/common.conf.php');

/* defines modules support product id */
define('_GR_SUPPORT_ID', '9736');

/* defines activate the BT support if false we use the ADDONS support url */
define('_GR_SUPPORT_BT', false);
//define('_GR_SUPPORT_BT', true);

/* defines activate the BT support if false we use the ADDONS support url */
define('_GR_SUPPORT_URL', 'https://addons.prestashop.com/');
//define('_GR_SUPPORT_URL', 'http://www.businesstech.fr/');

/*
 * defines admin library path
 * uses  => to include class files
 *
 */
define('_GR_PATH_LIB_ADMIN', _GR_PATH_LIB . 'admin/');
/*
 * defines admin path tpl
 * uses  => to set good absolute path
 *
 */
define('_GR_TPL_ADMIN_PATH', 'admin/');
/*
 * defines header tpl
 * uses  => with display admin interface
 *
 */
define('_GR_TPL_HEADER', 'header.tpl');
/*
 * defines body tpl
 * uses  => with display admin interface
 *
 */
define('_GR_TPL_BODY', 'body.tpl');
/*
 * defines technical check tpl
 * uses  => with display admin interface
 *
 */
define('_GR_TPL_WELCOME', 'welcome-include.tpl');
/*
 * defines technical check tpl
 * uses  => with display admin interface
 *
 */
define('_GR_TPL_TECH_CHECK', 'technical-check.tpl');
/*
 * defines basic tpl
 * uses  => with display admin interface
 *
 */
define('_GR_TPL_BASIC_SETTINGS', 'basics.tpl');
/*
 * defines remarketing modal tpl
 * uses  => with display admin interface
 *
 */
define('_GR_TPL_RENDERING_GOOGLE', 'rendering-google-javascript.tpl');
/*
 * defines remarketing dynamic tpl
 * uses  => with display admin interface
 *
 */
define('_GR_TPL_DYNAMIC_SETTINGS', 'dynamic-remarketing.tpl');
/*
 * defines constant for external BT API URL
 * uses  => with display admin interface
 *
 */
define('_GR_BT_API_MAIN_URL', 'https://api.businesstech.fr:441/prestashop-modules/'); // MOD David
/*
 * defines constant for external BT API URL
 * uses  => with display admin interface
 *
 */
define('_GR_BT_FAQ_MAIN_URL', 'http://faq.businesstech.fr/'); // MOD David
/* defines loader gif name */
define('_GR_LOADER_GIF', 'bx_loader.gif');
define('_GR_LOADER_GIF_BIG', 'ajax-loader.gif');
/*
 * defines variable for sql update
 * uses  => with admin
 *
 */
$GLOBALS['GR_SQL_UPDATE'] = array();
/*
 * defines variable for setting all request params
 * uses  => with admin interface
 *
 */
$GLOBALS['GR_REQUEST_PARAMS'] = array(
    'basic' => array('action' => 'update', 'type' => 'basic'),
    'dynamic' => array('action' => 'update', 'type' => 'dynamic'),
    'javascript' => array('action' => 'display', 'type' => 'javascript'),
);
