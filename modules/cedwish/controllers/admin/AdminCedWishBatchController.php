<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'cedwish/classes/batch.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/api.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/helper.php';

class AdminCedWishBatchController extends ModuleAdminController
{
    public function __construct()
    {
        $this->batch = new CedWishBatch();
        $this->bootstrap = true;
        $this->table = 'cedwish_batch';
        $this->identifier = 'id_cedwish_batch';
        $this->_orderBy = 'id_cedwish_batch';
        $this->_orderWay = 'DESC';
        $this->lang = false;
        $this->list_no_link = true;
        $this->addRowAction('delete');
        $this->className = 'CedWishBatch';
        parent::__construct();

        $this->fields_list = array(
            'id_cedwish_batch' => array(
                'title' => $this->l('ID'),
                'type' => 'text',
            ),
            'job_id' => array(
                'title' => $this->l('Job ID'),
                'type' => 'text',
            ),
            'created_at' => array(
                'title' => $this->l('Created At'),
                'type' => 'text',
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'type' => 'text',
            ),
            'download_link' => array(
                'title' => $this->l('Download Link'),
                'align' => 'text-center',
                'type' => 'text',
                'filter' => false,
                'callback' => 'updateBatch',
            )
        );
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete'),
                'confirm' => $this->l(
                    'Delete Selected Account(s) ?'
                ),
                'icon' => 'icon-trash'
            )
        );
        if (Tools::getIsset('method') && (Tools::getValue('method') == 'download')) {
            $this->download();
        }
        if (Tools::getIsset('method') && (Tools::getValue('method') == 'create_new_report')) {
            $this->newReport();
        }
        if (Tools::getIsset('method') && Tools::getValue('method') == 'update') {
            $this->update();
        }
        if (Tools::getIsset('method') && Tools::getValue('method') == 'process') {
            $this->process();
        }
    }

    public function download()
    {
        $job_id = Tools::getValue('download_file');
        if ($job_id) {
            $filenames = Db::getInstance()->getValue(
                "SELECT `download_link` FROM `" . _DB_PREFIX_ . "cedwish_batch` 
                WHERE job_id = '" . pSQL($job_id) . "'"
            );
            if ($filenames) {
                $filenames = json_decode($filenames, true);
            }

            if (!empty($filenames)) {
                $zip = new ZipArchive();
                if (!is_dir(_PS_PDF_DIR_ . 'cedwish')) {
                    mkdir(_PS_PDF_DIR_ . 'cedwish', 0777, true);
                }
                $download_file = _PS_PDF_DIR_ . 'cedwish/' . $job_id . '.zip';
                @unlink($download_file);
                $zip->open($download_file, ZipArchive::CREATE);
                foreach ($filenames as $filename) {
                    $zip->addFile($filename, basename($filename));
                }
                $zip->close();
                header("Content-type: application/zip");
                header("Content-Disposition: attachment; filename=" . basename($download_file));
                header("Content-length: " . filesize($download_file));
                header("Pragma: no-cache");
                header("Expires: 0");
                @readfile($download_file);
                exit;
            }
        }
    }

    public function newReport()
    {
        try {
            $params = array();
            $apiHelper = new CedWishApi();
            $response = $apiHelper->createBatch($params);
            if (isset($response['code'])
                && ($response['code'] == 0)
                && isset($response['data']['id'])
            ) {
                $this->confirmations[] = 'Batch Requested Successfully.';
                $account = new CedWishBatch();
                $account->job_id = $response['data']['id'];
                $account->status = $response['data']['status'];
                $account->created_at = date("Y-m-d H:i:s");
                $account->add();
            } elseif (isset($response['message'])) {
                $this->errors[] = $response['message'];
            } else {
                $this->errors[] = 'Some Error While creating Job.';
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    public function update()
    {
        if (Tools::getIsset('job_id') && Tools::getValue('job_id')) {
            try {
                $apiHelper = new CedWishApi();
                $response = $apiHelper->updateBulkProductJob(trim(Tools::getValue('job_id')));
                if (isset($response['code'])
                    && ($response['code'] == 0)
                    && isset($response['data']['status'])
                ) {
                    $batch = new CedWishBatch();
                    $updated = $batch->updateBatchStatus(Tools::getValue('job_id'), $response['data']);
                    if ($updated) {
                        $this->confirmations[] = 'Updated Batch Successfully With Job ID '
                            . Tools::getValue('job_id');
                    } else {
                        $this->errors[] = 'Some error while updating status';
                    }
                } elseif (isset($response['message']['data']['message'])) {
                    $this->errors[] = $response['message']['data']['message'];
                } else {
                    $this->errors[] = 'Some Error While getting status of Job.';
                }
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        } else {
            $this->errors[] = 'No response From Wish.';
        }
    }

    public function process()
    {
        if (Tools::getIsset('job_id') && Tools::getValue('job_id')) {
            $batch = new CedWishBatch();
            $response = $batch->process(Tools::getValue('job_id'));
            if (isset($response['success']) && !empty($response['success'])) {
                $this->confirmations[] = implode(", ", $response['success']);
            }
            if (isset($response['error']) && !empty($response['error'])) {
                $this->errors[] = implode(", ", $response['error']);
            }
        }
    }

    public function updateBatch($field_data, $data)
    {
        if ($data['status'] != "READY") {
            $link = new LinkCore();
            $file_path = $link->getAdminLink('AdminCedWishBatch') . '&method=update&job_id=' . $data['job_id'];
            $this->context->smarty->assign(array(
                'file_path' => $file_path
            ));
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/batch/update.tpl'
            );
        } else {
            $link = new LinkCore();
            $file_path = $link->getAdminLink(
                'AdminCedWishBatch'
            ) . '&method=process&job_id=' . $data['job_id'];
            $field_data = $link->getAdminLink(
                'AdminCedWishBatch'
            ) . '&method=download&download_file=' . $data['job_id'];
            $this->context->smarty->assign(
                array(
                    'file_path' => $field_data,
                    'process_path' => $file_path
                )
            );

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/batch/download.tpl'
            );
        }
    }

    public function processBatch($rowData, $data)
    {
        $link = new LinkCore();
        $file_path = $link->getAdminLink(
            'AdminCedWishBatch'
        ) . '&method=process&BatchRequestId=' . $data['BatchRequestId'];
        $this->context->smarty->assign(
            array(
                'file_path' => $file_path,
                'row_data' => $rowData
            )
        );
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/batch/update.tpl'
        );
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $link = new LinkCore();
            $this->page_header_toolbar_btn['addcedwish_batch'] = array(
                'href' => $link->getAdminLink('AdminCedWishBatch') . '&method=create_new_report',
                'desc' => $this->l('Create New Batch'),
                'icon' => 'process-icon-plus'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function processDelete()
    {
        $id_cedwish_batch = Tools::getValue('id_cedwish_batch');
        if ((int)$id_cedwish_batch) {
            Db::getInstance()->execute(
                "DELETE FROM `" . _DB_PREFIX_ . "cedwish_batch` 
                WHERE id_cedwish_batch ='" . (int)$id_cedwish_batch . "'"
            );
            $this->confirmations[] = 'Deleted Successfully.';
        } else {
            $this->errors[] = 'Failed to delete.';
        }
    }

    public function processBulkDelete()
    {
        if (!empty($this->boxes)) {
            $status = Db::getInstance()->execute(
                "DELETE FROM `" . _DB_PREFIX_ . "cedwish_batch` 
                WHERE id_cedwish_batch IN ('" . implode("', '", array_map('intval', $this->boxes)) . "')"
            );
            if ($status) {
                $this->confirmations[] = 'Deleted Successfully.';
            } else {
                $this->confirmations[] = 'Failed to delete.';
            }
        } else {
            $this->errors[] = 'Failed to delete.';
        }
    }
}
