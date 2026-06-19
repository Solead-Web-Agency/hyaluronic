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
class ApiGetCategoryGroup extends Core
{
    public function getData()
    {
        $id_category = (int) Tools::getValue('id_category');

        if (!$id_category) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Invalid category ID or groups'
            );
            return $this->fetchJSONResponse();
        }

        try {
            $success = $this->getCategoryGroups($id_category);

            $this->response['response'] = array(
                'status' => 'success',
                'message' => 'Category groups successfully',
                'data' => $success,
            );
        } catch (Exception $e) {
            error_log('Error updating category groups: ' . $e->getMessage());
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            );
        }

        return $this->fetchJSONResponse();
    }

    private function getCategoryGroups($id_category)
    {
        $category = new Category($id_category);
        $result = $category->getGroups();
        
        return $result;
    }
}
