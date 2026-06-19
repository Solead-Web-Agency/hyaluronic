<?php
/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

require_once(dirname(__FILE__) . '/common.conf.php');

/* defines install library path */
define('_GMCP_PATH_LIB_INSTALL', _GMCP_PATH_LIB . 'install/');

/* defines installation sql file */
define('_GMCP_INSTALL_SQL_FILE', 'install.sql');

/* defines uninstallation sql file */
define('_GMCP_UNINSTALL_SQL_FILE', 'uninstall.sql');

/* defines constant for plug SQL install/uninstall debug */
define('_GMCP_LOG_JAM_SQL', false);

/* defines constant for plug CONFIG install/uninstall debug */
define('_GMCP_LOG_JAM_CONFIG', false);
