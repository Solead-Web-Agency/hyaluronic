<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

/* Library */
include_once(_PS_MODULE_DIR_."/productbundlespro/lib/classes/PBPControllerCore.php");

/* Models */
include_once(_PS_MODULE_DIR_ . "/productbundlespro/models/PBPInstall.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/models/PBPBundleModel.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/models/PBPBundleLangModel.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/models/PBPTabLangModel.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/models/PBPProductOptionModel.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/models/PBPProductModel.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/models/PBPCartProductModel.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/models/PBPCartProductExtraModel.php");

/* Helpers */
include_once(_PS_MODULE_DIR_ . "/productbundlespro/helpers/PBPAjaxResponse.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/helpers/PBPBundleHelper.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/helpers/PBPConfigHelper.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/helpers/PBPMassAssignHelper.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/helpers/PBPProductHelper.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/helpers/PBPCartProductHelper.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/helpers/PBPCartProductExtraHelper.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/helpers/PBPUtilityHelper.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/helpers/PBPVoucherHelper.php");

/* Admin Controllers */
include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/admin/config/PBPAdminConfigMainController.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/admin/config/PBPAdminConfigGeneralController.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/admin/config/PBPAdminConfigTabsController.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/admin/config/PBPAdminConfigMassAssignController.php");

include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/admin/producttab/PBPAdminProductTabController.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/admin/producttab/PBPAdminProductTabGeneralController.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/admin/producttab/PBPAdminProductTabBundlesController.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/admin/producttab/PBPAdminProductTabBundlesExistingController.php");

include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/front/PBPFrontProductController.php");
include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/front/PBPFrontCartController.php");

/* Widget Controllers */
include_once(_PS_MODULE_DIR_ . "/productbundlespro/controllers/admin/widget/PBPProductSearchWidgetController.php");