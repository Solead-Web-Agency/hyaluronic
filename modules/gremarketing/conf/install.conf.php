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

/*
 * defines install library path
 * uses => to include class files
 */
define('_GR_PATH_LIB_INSTALL', _GR_PATH_LIB . 'install/');
/*
 * defines installation sql file
 * uses => only with DB install action
 */
define('_GR_INSTALL_SQL_FILE', 'install.sql'); // comment if not use SQL
/*
 * defines uninstallation sql file
 * uses => only with DB uninstall action
 */
define('_GR_UNINSTALL_SQL_FILE', 'uninstall.sql'); // comment if not use SQL
/*
 * defines constant for plug SQL install/uninstall debug
 * uses => set "true" only in debug mode - exceeds install sql execution
 */
define('_GR_LOG_JAM_SQL', false); // comment if not use SQL
/*
 * defines constant for plug CONFIG install/uninstall debug
 * uses => set "true" only in debug mode - exceeds uninstall sql execution
 */
define('_GR_LOG_JAM_CONFIG', false);
