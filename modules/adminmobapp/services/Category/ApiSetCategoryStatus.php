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
class ApiSetCategoryStatus extends Core
{
    public function getData()
    {
        $id_category = (int) Tools::getValue('id_category');
        $status = (int) Tools::getValue('status');

        if (!$id_category) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Invalid or missing parameters:  id_category is required'
            );
            return $this->fetchJSONResponse();
        }

        try {
            $category = new Category($id_category);
            
            if (!Validate::isLoadedObject($category)) {
                $this->response['response'] = array(
                    'status' => 'error',
                    'message' => 'category page not found'
                );
                return $this->fetchJSONResponse();
            }
            
            if ($status == 1) {
                $status = 1;
            } else {
                $status = 0;
            }
            $category->active = $status;
            $update = $category->save();
            if ($update) {
                $this->response['response'] = array(
                    'status' => 'success',
                    'message' => 'Category updated successfully',
                    'data' => $update
                );
            } else {
                $this->response['response'] = array(
                    'status' => 'error',
                    'message' => 'Failed to update category page'
                );
            }
        } catch (Exception $e) {
            error_log('Error updating CMS page category: ' . $e->getMessage());
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred while updating category page'
            );
        }

        return $this->fetchJSONResponse();
    }
}
