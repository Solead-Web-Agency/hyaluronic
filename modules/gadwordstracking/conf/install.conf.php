<?php
/**
 * install.conf.php file defines all needed constants and variables used in installation of module
 *
 *@author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 *@copyright 2003-2018 Business Tech SARL
 *@license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*
 * include common conf
 */
require_once(dirname(__FILE__) . '/common.conf.php');

/*
 * defines install library path
 * uses => to include class files
 */
define('_GACT_PATH_LIB_INSTALL', _GACT_PATH_LIB . 'install/');
/*
 * defines installation sql file
 * uses =>  only with DB install action
 */
define('_GACT_INSTALL_SQL_FILE', 'install.sql'); // comment if not use SQL
/*
 * defines uninstallation sql file
 * uses => only with DB uninstall action
 */
define('_GACT_UNINSTALL_SQL_FILE', 'uninstall.sql'); // comment if not use SQL
/*
 * defines constant for plug SQL install/uninstall debug
 * uses => set "true" only in debug mode - exceeds install sql execution
 */
define('_GACT_LOG_JAM_SQL', false); // comment if not use SQL
/*
 * defines constant for plug CONFIG install/uninstall debug
 * uses => set "true" only in debug mode - exceeds uninstall sql execution
 */
define('_GACT_LOG_JAM_CONFIG', false);