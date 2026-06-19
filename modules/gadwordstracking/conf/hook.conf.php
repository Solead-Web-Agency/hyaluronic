<?php
/**
 * hook.conf.php file defines all needed constants and variables for hook context
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
 * defines hook library path
 * uses => to include class files
 */
define('_GACT_PATH_LIB_HOOK', _GACT_PATH_LIB . 'hook/');
/*
 * defines hook tpl path
 * uses => to set good absolute path
 */
define('_GACT_TPL_HOOK_PATH', 'hook/');
/*
 * defines header tpl
 * uses => with display admin interface
 */
define('_GACT_TPL_HEADER', 'header.tpl');