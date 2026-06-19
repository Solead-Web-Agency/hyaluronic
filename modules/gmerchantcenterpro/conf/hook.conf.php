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

/* defines hook library path */
define('_GMCP_PATH_LIB_HOOK', _GMCP_PATH_LIB . 'hook/');

/* defines gsa library path */
define('_GMCP_PATH_GSA_LIB', _GMCP_PATH_ROOT . 'lib/');

/* defines front tpl path */
define('_GMCP_TPL_FRONT_PATH', 'front/');

/* defines MODELS path */
define('_GMCP_GSA_MODELS', _GMCP_PATH_GSA_LIB . 'models/');

/* defines front tpl path */
define('_GMCP_GSA_LIB', _GMCP_PATH_GSA_LIB . 'shopping-action/');

/* defines front tpl path */
define('_GMCP_GSA_PAYMENT_MODULE', _GMCP_GSA_MODELS . 'payment-module/');

/* defines hook empty tpl path */
define('_GMCP_TPL_EMPTY', 'empty.tpl');

/* defines variable for setting all request params */
$GLOBALS['GMCP_REQUEST_PARAMS'] = array();
