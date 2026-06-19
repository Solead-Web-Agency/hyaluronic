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
class ApiSetCategoryGroup extends Core
{
    public function getData()
    {
        $id_category = (int) Tools::getValue('id_category');
        $id_groups = Tools::getValue('id_groups');

        if (!$id_category || empty($id_groups)) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Invalid category ID or groups'
            );
            return $this->fetchJSONResponse();
        }

        if (!is_array($id_groups)) {
            $id_groups = explode(',', $id_groups);
        }

        try {
            $success = $this->setCategoryGroups($id_category, $id_groups);

            $this->response['response'] = array(
                'status' => 'success',
                'message' => 'Category groups updated successfully',
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

    private function setCategoryGroups($id_category, $id_groups)
    {
        if (!is_array($id_groups)) {
            $id_groups = explode(',', $id_groups);
        }
        $category = new Category($id_category);
        if (Validate::isLoadedObject($category)) {
            $category->cleanGroups();
            $category->addGroups($id_groups);
            return true;
        }
        return false;
    }
}
