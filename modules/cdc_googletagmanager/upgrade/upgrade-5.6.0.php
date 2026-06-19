<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) { exit; }

include_once(_CDCGTM_DIR_.'/classes/CdcGtmDataLayer.php');

function upgrade_module_5_6_0($module)
{
    $google_script_server_url = cdc_googletagmanager::getConfigValue('GOOGLE_SCRIPT_SERVER_URL');

    if(empty(cdc_googletagmanager::getConfigValue('OVERRIDE_GTM_TAG')) && !empty($google_script_server_url)) {
        $override_gtm_tag = "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'".$google_script_server_url."?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','[[GTM-ID]]');";
        Configuration::updateValue( cdc_googletagmanager::getConfigName('OVERRIDE_GTM_TAG'), $override_gtm_tag );

    }
    Configuration::deleteByName(cdc_googletagmanager::getConfigName('GOOGLE_SCRIPT_SERVER_URL'));

    return true;
}