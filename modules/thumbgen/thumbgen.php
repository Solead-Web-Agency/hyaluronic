<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ThumbGen extends Module
{
    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }

        $this->name = 'thumbgen';
        $this->tab = 'quick_bulk_update';
        $this->version = '1.1.0';
        $this->author = 'Prestapro';
        $this->need_instance = 0;
        $this->controllers = array('AdminThumbGen');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->secure_key = Tools::hash($this->name);
        $this->settings = null;
        $this->settings_key = 'TG_SETTINGS';
        $this->module_key = 'd8256e2bbaed8a65fa4786e57a2ffd94';
        $this->module_id = null;
        $this->legacy = false;
        $this->default_settings = array(
            'image_types' => array(),
            'chunk' => 10,
            'required_memory' => 0,
        );
        $this->table_image_progress = _DB_PREFIX_.$this->name.'_image_progress';
        $this->table_skipped = _DB_PREFIX_.$this->name.'_skipped';
        $this->image_groups = array(
            'c' => array(
                'name'=> 'categories',
                'title' => $this->l('Categories'),
            ),
            'm' => array(
                'name'=> 'manufacturers',
                'title' => $this->l('Manufacturers'),
            ),
            'p' => array(
                'name'=> 'products',
                'title' => $this->l('Products'),
            ),
            's' => array(
                'name'=> 'suppliers',
                'title' => $this->l('Suppliers'),
            ),
            'st' => array(
                'name'=> 'stores',
                'title' => $this->l('Stores'),
            ),
        );
        $this->notifications = array(
            'general' => array(
                'success' => $this->l('Import finished!'),
                'error' => $this->l('Error'),
            ),
            'validation' => array(
                'key' => $this->l('Invalid secure key'),
                'token' => $this->l('Invalid access token'),
            ),
            'thumbnail_generation' => array(
                'success' => $this->l('Image thumbnails have been regenerated!'),
                'stop' => $this->l('Thumbnail generation stopped'),
                'error' => $this->l('Error generating image thumbnails'),
                'no_images' => $this->l('No images have been found'),
                'incomplete' => $this->l('Not all images have been resized. Try increasing the PHP memory limit.'),
                'confirm' => $this->l('Start thumbnail regeneration? This process may take a while.'),
                'active' => $this->l('Regenerating image thumbnails...'),
            ),
            'log' => array(
                'success' => $this->l('Log file successfully cleared'),
                'error' => $this->l('Error clearing log file'),
                'confirm' => $this->l('Are you sure you want to clear the log? This action cannot be undone.'),
            ),
        );
        $this->log_name = $this->name.'_log.txt';
        $this->log_path = _PS_MODULE_DIR_.$this->name.'/'.$this->log_name;
        $this->log = null;

        if (version_compare(_PS_VERSION_, '1.7.1', '<')) {
            $this->legacy = 1;
        }

        parent::__construct();

        $this->displayName = $this->l('Thumbnail Regenerator');
        $this->description = $this->l('Reliably regenerate thumbnails for all images in your shop.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    private function log($message = '', $data = array())
    {
        file_put_contents($this->local_path.'log.txt', sprintf(
            "[%s] [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'],
            $message,
            var_export($data, true)
        ), FILE_APPEND);
    }

    private function logMessage($message, $level = 'info')
    {
        if (!in_array($level, array('info', 'warning', 'error'))) {
            $level = 'info';
        }

        if ($this->log !== null) {
            fwrite($this->log, sprintf(
                "[%s] [%s] %s\n",
                Tools::strtoupper(Tools::substr($level, 0, 1)),
                date('Y-m-d H:i:s'),
                $message
            ));
        }
    }

    public function clearLog()
    {
        if (file_exists($this->log_path)) {
            return unlink($this->log_path);
        }
    }

    private function checkResult($new_result, $result)
    {
        if (!$new_result) {
            $result = false;
        }

        return $result;
    }

    public function prepareTable($action)
    {
        $result = true;
        $db = Db::getInstance();
        $sql = array();

        switch ($action) {
            case 'add':
                $sql[] =
                    'CREATE TABLE IF NOT EXISTS `'.$this->table_image_progress.'` (
                        `id_image_progress` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `name` VARCHAR(255) NOT NULL,
                        `path` VARCHAR(255) NOT NULL,
                        `type` VARCHAR(255) NOT NULL,
                        `status` TINYINT UNSIGNED NOT NULL,
                        PRIMARY KEY (`id_image_progress`),
                        INDEX (`type`),
                        INDEX (`status`)
                    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
                $sql[] =
                    'CREATE TABLE IF NOT EXISTS `'.$this->table_skipped.'` (
                        `id_skipped` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `name` VARCHAR(255) NOT NULL,
                        `path` VARCHAR(255) NOT NULL,
                        `type` VARCHAR(255) NOT NULL,
                        `status` TINYINT UNSIGNED NOT NULL,
                        PRIMARY KEY (`id_skipped`),
                        INDEX (`status`)
                    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
                break;

            case 'remove':
                $sql[] = 'DROP TABLE IF EXISTS `'.$this->table_image_progress.'`';
                $sql[] = 'DROP TABLE IF EXISTS `'.$this->table_skipped.'`';
                break;
        }

        foreach ($sql as $query) {
            $result = $this->checkResult($db->execute($query), $result);
        }

        return $result;
    }

    public function prepareController($action)
    {
        $result = true;

        if ($this->legacy == 1) {
            switch ($action) {
                case 'add':
                    $tab = new Tab();
                    $tab->active = 1;
                    $tab->class_name = $this->controllers[0];
                    $tab->name = array();

                    foreach (Language::getLanguages(true) as $lang) {
                        $tab->name[$lang['id_lang']] = $this->displayName;
                    }

                    $tab->id_parent = 0;
                    $tab->position = 0;
                    $tab->module = $this->name;
                    $result = $tab->save();
                    break;

                case 'remove':
                    $id_tab = (int)Tab::getIdFromClassName($this->controllers[0]);

                    if ($id_tab) {
                        $tab = new Tab($id_tab);
                        $result = $tab->delete();
                    }

                    break;

                default:
                    $result = false;
                    break;
            }
        }

        return $result;
    }

    public function install()
    {
        if (!parent::install()
        || !$this->prepareTable('add')
        || !$this->prepareController('add')
        || !$this->registerHook('displayBackOfficeHeader')
        || !Configuration::updateValue($this->settings_key, Tools::jsonEncode($this->default_settings))) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()
        || !$this->prepareTable('remove')
        || !$this->prepareController('remove')
        || !Configuration::deleteByName($this->settings_key)) {
            return false;
        }

        return true;
    }

    private function displayChangelog()
    {
        $changelog_path = $this->local_path.'CHANGELOG.md';
        $result = $this->l('Changelog not found');

        if (file_exists($changelog_path)) {
            $changelog = trim(str_replace(
                array('# Changelog', '##'),
                array('', '####'),
                Tools::file_get_contents($changelog_path)
            ));

            if (!empty($changelog)) {
                require_once($this->local_path.'vendor/Parsedown.php');
                $parsedown = new Parsedown();
                $result = $parsedown->text($changelog);
            }
        }

        return $result;
    }

    private function getPublishedProducts($count = 6)
    {
        $result = array();
        $file_path = $this->local_path.'products.json';

        if (file_exists($file_path)) {
            $result = Tools::jsonDecode(Tools::file_get_contents($file_path), true);
        }

        shuffle($result);

        if (!is_numeric($count) || !in_array($count, array(3, 4, 5, 6))) {
            $count = 6;
        }

        return array_slice($result, 0, $count);
    }

    public function getSettings()
    {
        if ($this->settings === null) {
            $this->settings = Tools::jsonDecode(Configuration::get($this->settings_key), true);
        }

        return $this->settings;
    }

    public function updateSettings($key, $value)
    {
        $settings = $this->getSettings();

        if (isset($settings[$key])) {
            $settings[$key] = $value;
            Configuration::updateValue($this->settings_key, Tools::jsonEncode($settings));
        }
    }

    private function insertData($db, $table, $columns, $values)
    {
        return $db->execute(sprintf(
            'INSERT INTO `%s` (`%s`) VALUES %s',
            $table,
            implode('`,`', array_map('pSQL', $columns)),
            rtrim($values, ',')
        ));
    }

    private function isEnoughMemory($img)
    {
        $result = array(
            'enough' => true,
            'required' => 0,
        );
        $details = getimagesize($img);

        if (!is_array($details) || !isset($details['bits'])) {
            return $result;
        }

        $memory_limit = Tools::getMemoryLimit();

        if (isset($details['bits']) && function_exists('memory_get_usage') && (int)$memory_limit != -1) {
            $memory_usage = memory_get_usage();
            $channels = isset($details['channels']) ? ($details['channels'] / 8) : 1;

            $tweak_factor = 1.7;
            $KB64 = 65536;
            $MB = 1048576;
            $result['required'] =
                ($details[0] * $details[1] * $details['bits'] * $channels + $KB64) * $tweak_factor + $memory_usage;

            if ($result['required'] > $memory_limit - $MB) {
                $result['enough'] = false;
            }
        }

        return $result;
    }

    private function getSkippedCount($db)
    {
        return $db->getValue(sprintf(
            'SELECT COUNT(*) FROM `%s` WHERE `status` = 2',
            pSQL($this->table_image_progress)
        ));
    }

    private function getProgress($db, $type, $total = 0)
    {
        if ($type != 'skipped') {
            if ($total <= 0) {
                $total = $db->getValue(sprintf(
                    'SELECT COUNT(*) FROM `%s` WHERE type = "%s"',
                    pSQL($this->table_image_progress),
                    pSQL($type)
                ));
            }

            $processed = $db->getValue(sprintf(
                'SELECT COUNT(*) FROM `%s` WHERE `status` > 0 AND type = "%s"',
                pSQL($this->table_image_progress),
                pSQL($type)
            ));
        } else {
            if ($total <= 0) {
                $total = $db->getValue(sprintf(
                    'SELECT COUNT(*) FROM `%s` WHERE `status` = 2',
                    pSQL($this->table_image_progress)
                ));
            }

            $processed = $db->getValue(sprintf(
                'SELECT COUNT(*) FROM `%s` WHERE `status` > 0',
                pSQL($this->table_skipped)
            ));
        }

        return array(
            'percent' => ($total > 0) ? floor(($processed * 100) / $total) : 0,
            'processed' => $processed,
            'total' => $total,
            'skipped' => $this->getSkippedCount($db),
        );
    }

    private function convertToMB($value)
    {
        return round((int)$value / 1024 / 1024);
    }

    public function generateThumbnails($type = 'c', $image_formats = array(), $mode = 'start')
    {
        $db = Db::getInstance();
        $columns = array('name', 'path', 'type', 'status');
        $count = $total = $images_to_process_total = 0;
        $insert_values = null;
        $chunk = 10;
        $settings = $this->getSettings();

        if (Validate::isUnsignedInt($settings['chunk']) && $settings['chunk'] <= 10) {
            $chunk = $settings['chunk'];
        }

        $processed_ids = $skipped_ids = array();
        $this->log = fopen($this->log_path, 'a');

        if ($type != 'skipped') {
            if ($mode == 'continue') {
                $images_to_process_total = $db->getValue(sprintf(
                    'SELECT COUNT(*) FROM `%s` WHERE `type` = "%s"',
                    pSQL($this->table_image_progress),
                    pSQL($this->image_groups[$type]['name'])
                ));
            } else {
                $settings['required_memory'] = 0;

                $db->execute(sprintf(
                    'DELETE FROM `%s` WHERE type = "%s"',
                    pSQL($this->table_image_progress),
                    pSQL($this->image_groups[$type]['name'])
                ));
            }

            if ($images_to_process_total <= 0) {
                $result = true;
                $root_path = realpath(_PS_IMG_DIR_.$type);

                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($root_path),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    $name = basename($name);

                    if (!$file->isDir() && preg_match('/^[0-9]+\.jpg$/', $name)) {
                        $insert_values .= sprintf(
                            '("%s", "%s", "%s", 0),',
                            pSQL($name),
                            pSQL(dirname($file->getRealPath())),
                            pSQL($this->image_groups[$type]['name'])
                        );
                        $count++;
                        $total++;

                        if ($count >= 100) {
                            $insert_result = $this->insertData(
                                $db,
                                $this->table_image_progress,
                                $columns,
                                $insert_values
                            );
                            $result = $this->checkResult($insert_result, $result);
                            $count = 0;
                            $insert_values = null;
                        }
                    }
                }

                if (!empty($insert_values)) {
                    $insert_result = $this->insertData($db, $this->table_image_progress, $columns, $insert_values);
                    $result = $this->checkResult($insert_result, $result);
                }

                $images_to_process_total = $total;
            }

            if ($mode == 'start') {
                $this->logMessage(sprintf(
                    $this->l('Start thumbnail generation for group %s. Total number of images to process: %d.'),
                    $this->image_groups[$type]['title'],
                    $images_to_process_total
                ), 'info');
            }

            $unprocessed_images = $db->executeS(sprintf(
                'SELECT `id_image_progress`, `name`, `path`, `type`
                FROM `%s`
                WHERE `status` = 0 AND `type` = "%s"
                ORDER BY `id_image_progress` ASC
                LIMIT %d',
                pSQL($this->table_image_progress),
                pSQL($this->image_groups[$type]['name']),
                (int)$chunk
            ));
        } else {
            if ($mode == 'start') {
                $settings['required_memory'] = 0;

                $db->execute(sprintf(
                    'TRUNCATE `%s`',
                    pSQL($this->table_skipped)
                ));
                $db->execute(sprintf(
                    'INSERT INTO `%s`
                    SELECT `id_image_progress`, `name`, `path`, `type`, 0 FROM `%s`
                    WHERE `status` = 2',
                    pSQL($this->table_skipped),
                    pSQL($this->table_image_progress)
                ));
            }

            $images_to_process_total = $db->getValue(sprintf(
                'SELECT COUNT(*) FROM `%s`',
                pSQL($this->table_skipped)
            ));

            if ($mode == 'start') {
                $this->logMessage(sprintf(
                    $this->l('Start thumbnail generation for skipped images. Total number of images to process: %d.'),
                    $images_to_process_total
                ), 'info');
            }

            $unprocessed_images = $db->executeS(sprintf(
                'SELECT `id_skipped` AS id_image_progress, `name`, `path`, `type`
                FROM `%s`
                WHERE `status` = 0
                ORDER BY `id_skipped` ASC
                LIMIT %d',
                pSQL($this->table_skipped),
                (int)$chunk
            ));
        }

        $formats = ImageType::getImagesTypes();
        $high_dpi = (bool)Configuration::get('PS_HIGHT_DPI');

        foreach ($unprocessed_images as $img) {
            $original_image = $img['path'].'/'.$img['name'];
            $memory = $this->isEnoughMemory($original_image);

            if (!$memory['enough']) {
                $this->logMessage(sprintf(
                    $this->l('There is not enough memory for resizing the following image: %s. At least %d MB of memory must be allocated to PHP in order to resize this image.'),
                    $original_image,
                    $this->convertToMB($memory['required'])
                ), 'warning');

                $skipped_ids[] = (int)$img['id_image_progress'];

                if ($settings['required_memory'] < $memory['required']) {
                    $settings['required_memory'] = $memory['required'];
                    $this->updateSettings('required_memory', (int)$memory['required']);
                }

                continue;
            }

            foreach ($formats as $format) {
                if ($format[$img['type']] != 1
                || (is_array($image_formats)
                    && !empty($image_formats)
                    && !in_array($format['id_image_type'], $image_formats))) {
                    continue;
                }

                $thumbnail = sprintf(
                    '%s/%s-%s.jpg',
                    $img['path'],
                    Tools::substr(str_replace('_thumb.', '.', $img['name']), 0, -4),
                    Tools::stripslashes($format['name'])
                );

                ImageManager::resize(
                    $original_image,
                    $thumbnail,
                    (int)$format['width'],
                    (int)$format['height'],
                    'jpg',
                    true
                );

                if ($high_dpi) {
                    ImageManager::resize(
                        $original_image,
                        sprintf(
                            '%s/%s-%s2x.jpg',
                            $img['path'],
                            Tools::substr($img['name'], 0, -4),
                            Tools::stripslashes($format['name'])
                        ),
                        (int)$format['width'] * 2,
                        (int)$format['height'] * 2,
                        'jpg',
                        true
                    );
                }
            }

            $processed_ids[] = (int)$img['id_image_progress'];
        }

        if (is_array($processed_ids) && !empty($processed_ids)) {
            $db->execute(sprintf(
                'UPDATE `%s` SET `status` = 1 WHERE `id_image_progress` IN (%s)',
                pSQL($this->table_image_progress),
                implode(',', array_map('intval', $processed_ids))
            ));

            if ($type == 'skipped') {
                $db->execute(sprintf(
                    'UPDATE `%s` SET `status` = 1 WHERE `id_skipped` IN (%s)',
                    pSQL($this->table_skipped),
                    implode(',', array_map('intval', $processed_ids))
                ));
            }
        }

        if (is_array($skipped_ids) && !empty($skipped_ids)) {
            $db->execute(sprintf(
                'UPDATE `%s` SET `status` = 2 WHERE `id_image_progress` IN (%s)',
                pSQL($this->table_image_progress),
                implode(',', array_map('intval', $skipped_ids))
            ));

            if ($type == 'skipped') {
                $db->execute(sprintf(
                    'UPDATE `%s` SET `status` = 2 WHERE `id_skipped` IN (%s)',
                    pSQL($this->table_skipped),
                    implode(',', array_map('intval', $skipped_ids))
                ));
            }
        }

        $progress = $this->getProgress(
            $db,
            ($type == 'skipped') ? $type : $this->image_groups[$type]['name'],
            $images_to_process_total
        );
        $progress['required_memory'] = $this->convertToMB($settings['required_memory']);

        if ($progress['percent'] == 100) {
            if ($type != 'skipped') {
                $message = sprintf(
                    $this->l('Thumbnail generation for group %s finished.'),
                    $this->image_groups[$type]['title']
                );
            } else {
                $message = $this->l('Thumbnail generation for previously skipped images finished.');
            }

            $this->logMessage($message, 'info');
        }

        fclose($this->log);

        return $progress;
    }

    /**
     * Modified version of https://gist.github.com/lorenzos/1711e81a9162320fde20
     * @license http://creativecommons.org/licenses/by/3.0/
     */
    private function tailFile($file_path, $lines = 100, $adaptive = true)
    {
        $f = false;

        if (file_exists($file_path)) {
            $f = fopen($file_path, 'rb');
        }

        if ($f === false) {
            return false;
        }

        if (!$adaptive) {
            $buffer = 4096;
        } else {
            $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
        }

        fseek($f, -1, SEEK_END);

        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }

        $output = '';
        $chunk = '';

        while (ftell($f) > 0 && $lines >= 0) {
            $seek = min(ftell($f), $buffer);
            fseek($f, -$seek, SEEK_CUR);
            $output = ($chunk = fread($f, $seek)) . $output;
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            $lines -= substr_count($chunk, "\n");
        }

        while ($lines++ < 0) {
            $output = Tools::substr($output, strpos($output, "\n") + 1);
        }

        fclose($f);

        return explode("\n", trim($output));
    }

    public function displayLog()
    {
        $this->context->smarty->assign(array($this->name => array(
            'log' => $this->tailFile($this->log_path),
        )));

        return $this->display($this->local_path, 'views/templates/admin/tab-log.tpl');
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') != 'AdminModules' || Tools::getValue('configure') != $this->name) {
            return false;
        }

        $this->context->controller->addCSS($this->_path.'views/css/back.css');
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'views/js/back.js');

        Media::addJsDef(array($this->name => array(
            'controllerPath' => $this->context->link->getAdminLink($this->controllers[0]),
            'secureKey' => $this->secure_key,
            'notification' => $this->notifications,
        )));
    }

    public function getContent()
    {
        $output = null;
        $groups = array();
        $db = Db::getInstance();
        $settings = $this->getSettings();

        foreach ($this->image_groups as $key => $group) {
            $groups[$key] = array(
                'name' => $group['name'],
                'title' => $group['title'],
                'progress' => $this->getProgress($db, $group['name']),
            );
        }

        $this->context->smarty->assign(array($this->name => array(
            'documentation_link' => $this->_path.'readme_en.pdf',
            'image_types' => ImageType::getImagesTypes(),
            'checked' => $settings['image_types'],
            'chunk' => $settings['chunk'],
            'groups' => $groups,
            'skipped' => $this->getSkippedCount($db),
            'required_memory' => $this->convertToMB($settings['required_memory']),
            'log' => $this->tailFile($this->log_path),
            'changelog' => $this->displayChangelog(),
            'version' => $this->version,
            'module_id' => $this->module_id,
            'products' => $this->getPublishedProducts(Tools::getValue('promo_product'), 0),
            'module_path' => $this->_path,
            'promo_mode' => Tools::getValue('promo_mode'),
        )));
        $output .= $this->display($this->local_path, 'views/templates/admin/configure.tpl');

        return $output;
    }
}
