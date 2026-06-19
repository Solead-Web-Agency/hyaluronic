<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }
/**
 * todo: Migrate all admin configuration > Marketplaces to this class
 */
class AmazonAdminConfigurationMarketplaces
{
    /** @var Amazon */
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function customerGroups($marketplaceId, $id_lang)
    {
        $constNoneBiz = AmazonConstant::CUSTOMER_GROUP_NONE_BUSINESS_INCL_VAT;
        $constBizVatIncl = AmazonConstant::CUSTOMER_GROUP_BUSINESS_VAT_INCL;
        $constBizVatExcl = AmazonConstant::CUSTOMER_GROUP_BUSINESS_OUTSIDE_COUNTRY_VAT_EXCL;
        // This makes sure the configuration are latest
        $moduleConfig = $this->module->getConfig();

        if (AmazonTools::isCustomerGroupMarketplaceId($marketplaceId)) {
            // None business incl VAT
            if (isset($moduleConfig['mkp_groups']) && isset($moduleConfig['mkp_groups'][$id_lang]) && isset($moduleConfig['mkp_groups'][$id_lang][$constNoneBiz])) {
                $none_business_group = $moduleConfig['mkp_groups'][$id_lang][$constNoneBiz];
            } else {
                $none_business_group = null;
            }

            // Business sale channel VAT incl
            if (isset($moduleConfig['mkp_groups']) && isset($moduleConfig['mkp_groups'][$id_lang]) && isset($moduleConfig['mkp_groups'][$id_lang][$constBizVatIncl])) {
                $business_group = $moduleConfig['mkp_groups'][$id_lang][$constBizVatIncl];
            } else {
                $business_group = null;
            }

            // Business outside sale channel VAT excl
            if (isset($moduleConfig['mkp_groups']) && isset($moduleConfig['mkp_groups'][$id_lang]) && isset($moduleConfig['mkp_groups'][$id_lang][$constBizVatExcl])) {
                $business_outside_country_group = $moduleConfig['mkp_groups'][$id_lang][$constBizVatExcl];
            } else {
                $business_outside_country_group = null;
            }

            return array(
                $constNoneBiz => $none_business_group,
                $constBizVatIncl => $business_group,
                $constBizVatExcl => $business_outside_country_group,
            );
        }

        return array();
    }
}
