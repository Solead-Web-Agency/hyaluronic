<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is controller for CRON job for import
 */
class ElegantalEasyImportImportModuleFrontController extends ModuleFrontController
{

    public function display()
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', 600);

        $this->module->initModel(Tools::getValue('id'));
        $model = $this->module->model;

        $secure_key = $this->module->getSetting('security_token_key');
        if (!$secure_key || Tools::getValue('secure_key') != $secure_key) {
            die('Access Denied.');
        } elseif (!$model || !$model->id) {
            die('Object not found.');
        } elseif (!$model->active) {
            die('Import rule is not active.');
        } elseif (!$model->is_cron) {
            die('Import rule is not enabled for CRON.');
        }

        try {
            $csvRowsCount = ElegantalEasyImportCsv::model()->countAll(array(
                'condition' => array(
                    'id_elegantaleasyimport' => $model->id,
                )
            ));

            $file = ElegantalEasyImportTools::getRealPath($model->csv_file);

            if (!$file || !is_file($file) || !filesize($file)) {
                // if file does not exist, download and start import
                $this->module->downloadImportFile();

                // Save csv rows in db so that import will start from next execution
                $this->module->saveCsvRowsInDb();
            } elseif (empty($csvRowsCount)) {
                $old_md5 = $model->cron_csv_file_md5;
                $old_size = $model->cron_csv_file_size;

                // if file does not exist, download and start import
                $this->module->downloadImportFile();

                $new_md5 = $model->cron_csv_file_md5;
                $new_size = $model->cron_csv_file_size;

                // If CRON was never run or if file is new, save rows in db
                if (empty($model->last_import_date) || $old_size != $new_size || $old_md5 != $new_md5) {
                    // Save csv rows in db so that import will start from next execution
                    $this->module->saveCsvRowsInDb();
                }
            } elseif ($csvRowsCount > 0) {
                $limit = (int) $model->product_limit_per_request;
                $limit = ($limit > 0 && $limit < 10000) ? $limit : 50;
                if ($model->entity == 'product') {
                    $this->module->importProductDataFromCsv($limit);
                } elseif ($model->entity == 'combination') {
                    $this->module->importCombinationDataFromCsv($limit);
                }
            }
        } catch (Exception $e) {
            $model->error_log .= (empty($model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . 'CRON: ' . $e->getMessage();
            if ($this->module->getSetting('is_debug_mode')) {
                $model->update();
                die($e->getMessage());
            }
        }

        $model->update();

        die('CRON executed successfully.');
    }
}
