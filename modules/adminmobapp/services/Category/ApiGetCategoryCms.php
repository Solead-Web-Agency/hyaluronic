<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2023
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once(dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/Core.php');
class ApiGetCategoryCms extends Core
{
    public function getData()
    {
        $id_lang = Tools::getValue('id_lang');
        $id_lang = $this->context->language->id;
        $cms_category = CMSCategory::getSimpleCategories($id_lang);
        $this->response['response'] = array(
            'status' => 'success',
            'message' => 'success',
            'data' => $cms_category
        );

        return $this->fetchJSONResponse();
    }
}
