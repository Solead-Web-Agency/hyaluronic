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

function upgrade_module_5_5_2($module)
{
    $result = true;
    if(empty(cdc_googletagmanager::getConfigValue('PRODUCT_ID_FORMAT')) && !empty($product_id_prefix = cdc_googletagmanager::getConfigValue('REMARKETING_PRODUCTPREF'))) {
        Configuration::updateValue( cdc_googletagmanager::getConfigName('PRODUCT_ID_FORMAT'), $product_id_prefix.'{ID}' );
        Configuration::deleteByName(cdc_googletagmanager::getConfigName('REMARKETING_PRODUCTPREF'));
    }

    if (version_compare(_PS_VERSION_, '1.7', '<')) {
        $result &= $module->addTab("AdminCdcGoogletagmanagerDatalayer", "GTM Datalayer", "AdminParentStats");
    } else {
        $result &= $module->addTab("AdminCdcGoogletagmanagerDatalayer", "GTM Datalayer", "AdminAdvancedParameters");
    }

    $result &= CdcGtmDataLayer::createTable();
    return $result;
}