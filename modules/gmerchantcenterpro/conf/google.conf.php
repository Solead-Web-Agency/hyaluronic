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

/* defines title length */
define('_GMCP_FEED_TITLE_LENGTH', 150);

/* defines the limit of additional images you can provide */
define('_GMCP_IMG_LIMIT', 10);

/* defines the limit of number of custom label you can provide */
define('_GMCP_CUSTOM_LABEL_LIMIT', 5);

/* defines the limit of number of custom label you can provide */
define('_GMCP_PROMOTION_LONG_TITLE', 60);

/* defines the limit of 10 first promotion_id */
define('_GMCP_PROMOTION_ID_NUMBER', 9);

/* defines material tag length length */
define('_GMCP_MATERIAL_LENGTH', 200);

/* defines promotion id length */
define('_GMCP_PROMO_ID_LENGTH', 50);

/* defines forbidden string for the data feed */
$GLOBALS['GMCP_FORBIDDEN_STRING'] = array(
    'special_symbol_1' => array(
        'sToReplace' => '&',
        'sReplaceBy' => '',
    ),
    'special_symbol_2' => array(
        'sToReplace' => '!',
        'sReplaceBy' => '',
    ),
    'special_symbol_3' => array(
        'sToReplace' => '***',
        'sReplaceBy' => '',
    ),
);
