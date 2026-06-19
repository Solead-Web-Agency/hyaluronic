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
class ApiSetCategoryCms extends Core
{
    public function getData()
    {
        $id_cms = (int) Tools::getValue('id_cms');
        $id_category = (int) Tools::getValue('id_category');

        if (!$id_cms || !$id_category) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Invalid or missing parameters: id_cms and id_category are required'
            );
            return $this->fetchJSONResponse();
        }

        try {
            $cmspage = new CMS($id_cms);

            if (!Validate::isLoadedObject($cmspage)) {
                $this->response['response'] = array(
                    'status' => 'error',
                    'message' => 'CMS page not found'
                );
                return $this->fetchJSONResponse();
            }

            $cmspage->id_cms_category = $id_category;
            $update = $cmspage->save();

            if ($update) {
                $this->response['response'] = array(
                    'status' => 'success',
                    'message' => 'CMS page category updated successfully',
                    'data' => $update
                );
            } else {
                $this->response['response'] = array(
                    'status' => 'error',
                    'message' => 'Failed to update CMS page category'
                );
            }
        } catch (Exception $e) {
            error_log('Error updating CMS page category: ' . $e->getMessage());
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred while updating CMS page category'
            );
        }

        return $this->fetchJSONResponse();
    }
}
