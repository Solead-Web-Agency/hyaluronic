<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is controller for CRON job for export
 */
class ElegantalEasyImportExportModuleFrontController extends ModuleFrontController
{

    public function display()
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', 600);

        $secure_key = $this->module->getSetting('security_token_key');
        $model = new ElegantalEasyImportExport(Tools::getValue('id'));

        if (!$secure_key || Tools::getValue('secure_key') != $secure_key) {
            die('Access Denied.');
        } elseif (!$model || !$model->id) {
            die('Object not found.');
        } elseif (!$model->active) {
            die('Export rule is not active.');
        }

        if (Tools::getValue('action') == 'download') {
            return $this->downloadExportFile($model);
        }

        $result = $model->export();

        if (isset($result['success']) && $result['success']) {
            $message = isset($result['count']) ? $result['count'] . ' ' : '';
            $message .= ($model->entity == 'combination') ? 'combinations exported successfully' : 'products exported successfully';
            die($message);
        } elseif (isset($result['message'])) {
            die($result['message']);
        }
        exit;
    }

    private function downloadExportFile($model)
    {
        if (!is_file($model->file_path)) {
            die('File not found.');
        }
        $mime = 'application/octet-stream';
        switch ($model->file_format) {
            case 'csv':
                $mime = 'text/csv';
                break;
            case 'xml':
                $mime = 'text/xml';
                break;
            case 'json':
                $mime = 'application/json';
                break;
            case 'xls':
                $mime = 'application/vnd.ms-excel';
                break;
            case 'xlsx':
                $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'txt':
                $mime = 'text/plain';
                break;
            default:
                break;
        }
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($model->file_path) . '"');
        header('Content-Length: ' . filesize($model->file_path));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');
        while (ob_get_level()) {
            ob_end_clean();
        }
        readfile($model->file_path);
        exit;
    }
}
