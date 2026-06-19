<?php
/**
 * common.conf.php file defines all needed constants and variables for all context of using module - install / admin / hook / tab
 *
 *@author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 *@copyright 2003-2018 Business Tech SARL
 *@license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*
 * defines constant of module name
 * uses => set short name of module
 */
define('_GACT_MODULE_NAME', 'GACT');
/*
 * defines set module name
 * uses => on setting name of module
 */
define('_GACT_MODULE_SET_NAME', 'gadwordstracking');
/*
 * defines root path of module
 * uses => with all included files
 */
define('_GACT_PATH_ROOT', _PS_MODULE_DIR_ . _GACT_MODULE_SET_NAME . '/');
/*
 * defines conf path
 * uses => with including conf files in match environment
 */
define('_GACT_PATH_CONF', _GACT_PATH_ROOT . 'conf/');
/*
 * defines libraries path
 * uses => with all class files
 */
define('_GACT_PATH_LIB', _GACT_PATH_ROOT . 'lib/');
/*
 * defines sql path
 * uses => with all SQL script
 */
define('_GACT_PATH_SQL', _GACT_PATH_ROOT . 'sql/');
/*
 * defines common library path
 * uses => to include class files
 */
define('_GACT_PATH_LIB_COMMON', _GACT_PATH_LIB . 'common/');
/*
 * defines dynamic tags library path
 * uses => to include class files
 */
define('_GACT_PATH_LIB_DYN_TAGS', _GACT_PATH_LIB . 'tags/');
/*
 * defines views folder
 * uses => to include css / js / templates files
 */
define('_GACT_PATH_VIEWS', 'views/');
/*
 * defines js URL
 * uses => to include js files on templates (use prestashop constant _MODULE_DIR_)
 */
define('_GACT_URL_JS', _MODULE_DIR_ . _GACT_MODULE_SET_NAME . '/views/js/');
/*
 * defines css URL
 * uses => to include css files on templates (use prestashop constant _MODULE_DIR_)
 */
define('_GACT_URL_CSS', _MODULE_DIR_ . _GACT_MODULE_SET_NAME . '/views/css/');
/*
 * defines MODULE URL
 * uses => to execute updating of callback review value
 */
define('_GACT_MODULE_URL', _MODULE_DIR_ . _GACT_MODULE_SET_NAME . '/');
/*
 * defines img path
 * uses => to include all used images
 */
define('_GACT_PATH_IMG', 'img/');
/*
 * defines img URL
 * uses => to include img files in templates (use Prestashop constant _MODULE_DIR_)
 */
define('_GACT_URL_IMG',
    _MODULE_DIR_ . _GACT_MODULE_SET_NAME . '/views/' . _GACT_PATH_IMG);
/*
 * defines tpl path name
 * uses => with included templates
 */
define('_GACT_PATH_TPL_NAME', _GACT_PATH_VIEWS . 'templates/');
/*
 * defines tpl path
 * uses => with included templates
 */
define('_GACT_PATH_TPL', _GACT_PATH_ROOT . _GACT_PATH_TPL_NAME);
/*
 * defines constant of error tpl
 * uses => with display error  tpl
 */
define('_GACT_TPL_ERROR', 'error.tpl');
/*
 * defines google javascript tpl
 * uses => with display admin / hook interface
 */
define('_GACT_TPL_GOOGLE_JAVASCRIPT', 'google-javascript.tpl');
/*
 * defines google javascript tpl
 * uses => with display admin / hook interface
 */
define('_GACT_TPL_REGENERATE_GOOGLE_JAVASCRIPT',
    'google-regenerate-javascript.tpl');
/*
 * defines activate / deactivate debug mode
 * uses => only in debug / programming mode
 */
define('_GACT_DEBUG', false);
/*
 * defines constant to use or not js on submit action
 * uses => only in debug mode - test checking control on server side
 */
define('_GACT_USE_JS', true);
/*
 * defines variable for admin ctrl name
 */
define('_GACT_PARAM_CTRL_NAME', 'sController');
/*
 * defines variable for admin ctrl name
 */
define('_GACT_ADMIN_CTRL', 'admin');
/*
 * defines variable for setting configuration options
 * uses => with install or update action - declare all mandatory values stored by prestashop in module using
 */
$GLOBALS['GACT_CONFIGURATION'] = array(
    'GACT_MODULE_VERSION'   => '1.0.0',
    'GACT_CONVERSION_ID'    => '',
    'GACT_CONVERSION_LABEL' => '',
    'GACT_USE_TAX'          => 1,
    'GACT_USE_SHIPPING'     => 1,
    'GACT_USE_WRAPPING'     => 1,
    'GACT_DISPLAY_ADVICE'   => 1,
);
/*
 * defines variable for setting hooks
 * uses =>  in INSTALL / ADMIN / HOOK mode
 */
$GLOBALS['GACT_HOOKS'] = array(
    array(
        'name'  => ((version_compare(_PS_VERSION_, '1.5.0', '>')) ? 'displayHeader' : 'header'),
        'use'   => false,
        'title' => 'Header',
    ),
    array(
        'name'  => ((version_compare(_PS_VERSION_, '1.5.0', '>')) ? 'actionValidateOrder' : 'newOrder'),
        'use'   => false,
        'title' => 'Validate Order',
    ),
);

/*
 * defines variable for setting dynamic tags type
 * uses =>  in install / ADMIN / HOOK mode
 */
$GLOBALS['GACT_TAGS_TYPE'] = array('purchase' => 'purchase');
/*
 * defines variable for translating js msg
 * uses =>  with admin interface - declare all displayed error messages
 */
$GLOBALS['GACT_JS_MSG'] = array();