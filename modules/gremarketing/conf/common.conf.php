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

/*
 * defines constant of module name
 * uses  => set short name of module
 */
define('_GR_MODULE_NAME', 'GR');
/*
 * defines set module name
 * uses  => on setting name of module
 */
define('_GR_MODULE_SET_NAME', 'gremarketing');
/*
 * defines root path of module
 * uses  => with all included files
 */
define('_GR_PATH_ROOT', _PS_MODULE_DIR_ . _GR_MODULE_SET_NAME . '/');
/*
 * defines conf path
 * uses  => with including conf files in match environment
 */
define('_GR_PATH_CONF', _GR_PATH_ROOT . 'conf/');
/*
 * defines libraries path
 * uses  => with all class files
 */
define('_GR_PATH_LIB', _GR_PATH_ROOT . 'lib/');
/*
 * defines sql path
 * uses  => with all SQL script
 */
define('_GR_PATH_SQL', _GR_PATH_ROOT . 'sql/');
/*
 * defines common library path
 * uses  => to include class files
 */
define('_GR_PATH_LIB_COMMON', _GR_PATH_LIB . 'common/');
/*
 * defines dynamic tags library path
 * uses  => to include class files
 */
define('_GR_PATH_LIB_DYN_TAGS', _GR_PATH_LIB . 'tags/');
/*
 * defines views folder
 * uses  => to include css / js / templates files
 */
define('_GR_PATH_VIEWS', 'views/');
/*
 * defines js URL
 * uses  => to include js files on templates (use prestashop constant _MODULE_DIR_)
 */
define('_GR_URL_JS', _MODULE_DIR_ . _GR_MODULE_SET_NAME . '/views/js/');
/*
 * defines css URL
 * uses  => to include css files on templates (use prestashop constant _MODULE_DIR_)
 */
define('_GR_URL_CSS', _MODULE_DIR_ . _GR_MODULE_SET_NAME . '/views/css/');
/*
 * defines MODULE URL
 * uses  => to execute updating of callback review value
 */
define('_GR_MODULE_URL', _MODULE_DIR_ . _GR_MODULE_SET_NAME . '/');
/*
 * defines img path
 * uses  => to include all used images
 */
define('_GR_PATH_IMG', 'img/');
/*
 * defines img URL
 * uses  => to include img files in templates (use Prestashop constant _MODULE_DIR_)
 */
define('_GR_URL_IMG', _MODULE_DIR_ . _GR_MODULE_SET_NAME . '/views/' . _GR_PATH_IMG);
/*
 * defines tpl path name
 * uses  => with included templates
 */
define('_GR_PATH_TPL_NAME', _GR_PATH_VIEWS . 'templates/');
/*
 * defines tpl path
 * uses  => with included templates
 */
define('_GR_PATH_TPL', _GR_PATH_ROOT . _GR_PATH_TPL_NAME);
/*
 * defines constant of error tpl
 * uses  => with display error - transverse tpl
 */
define('_GR_TPL_ERROR', 'error.tpl');
/*
 * defines confirm tpl
 * uses  => with display admin / hook interface
 */
define('_GR_TPL_CONFIRM', 'confirm.tpl');
/*
 * defines activate / deactivate debug mode
 * uses  => only in debug / programming mode
 */
define('_GR_DEBUG', false);
/*
 * defines constant to use or not js on submit action
 * uses => only in debug mode - test checking control on server side
 */
define('_GR_USE_JS', true);
/*
 * defines variable for setting configuration options
 * uses  => with install or update action - declare all mandatory values stored by prestashop in module using
 */
$GLOBALS['GR_CONFIGURATION'] = array(
    'GR_MODULE_VERSION' => '1.6.1',
    'GR_REMARKETING_ID' => '',
    'GR_REMARKETING_DYNAMIC' => 0,
    'GR_GOOGLE_PREFIX' => '',
    'GR_TAG_ON_ORDER_PAGE' => 1,
    'GR_USER_ID' => 1,
    'GR_COMBO_SEPARATOR' => 'v'
);
/*
 * defines variable for setting hooks
 * uses  => in INSTALL / ADMIN / HOOK mode
 *
 */
$GLOBALS['GR_HOOKS'] = array(
    array(
        'name' => 'displayHeader',
        'use' => false,
        'title' => 'Footer'
    ),
);
/*
 * defines variable for setting dynamic tags type
 * uses  => in install / ADMIN / HOOK mode
 *
 */
$GLOBALS['GR_TAGS_TYPE'] = array(
    'home' => 'home',
    'category' => 'category',
    'product' => 'product',
    'cart' => 'cart',
    'purchase' => 'purchase',
    'search' => 'searchresults',
    'other' => 'other'
);
/*
 * defines variable for translating js msg
 * uses  => with admin interface - declare all displayed error messages
 *
 */
$GLOBALS['GR_JS_MSG'] = array();
