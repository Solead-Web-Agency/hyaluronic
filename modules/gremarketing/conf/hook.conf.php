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
 * defines hook library path
 * uses => to include class files
 */
define('_GR_PATH_LIB_HOOK', _GR_PATH_LIB . 'hook/');

/* defines hook tpl path
 * uses => to set good absolute path
 */
define('_GR_TPL_HOOK_PATH', 'hook/');
/*
 * defines footer tpl
 * uses => with display front interface
 */
define('_GR_TPL_FOOTER', 'footer.tpl');
