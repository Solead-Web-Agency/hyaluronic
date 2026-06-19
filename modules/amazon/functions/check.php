<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
/* Is ajax/cron file */
require_once(dirname(__FILE__).'/AmazonFunction.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.zip.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.data_adjustment.class.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonConnexionCheck extends AmazonFunction
{
    public function dispatch()
    {
        ob_start();

        if (!$this->functionAuthorization()) {
            die('Wrong Token');
        }

        switch (AmazonTools::getValue('action')) {
            case 'check_sp':
                $mkpId = AmazonTools::getValue('marketplaceId');
                $auths = AmazonTools::getValue('auth');
                echo json_encode($this->checkSPApi($mkpId, $auths));
                break;
            case 'php-info':
                $this->phpInfo();
                break;
            case 'prestashop-info':
                $this->prestashopInfo();
                break;
            case 'support_zip_file':
                $this->downloadSupportZipFile();
                break;
            case 'mode-dev':
                $this->prestashopModeDev();
                break;
            case 'participations':
                echo json_encode($this->participations());
                break;
            case 'service-status':
                echo json_encode($this->getServiceStatus(AmazonTools::getValue('marketplace_id')));
                break;
        }
    }

    public function getServiceStatus($marketplaceId)
    {
        $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($marketplaceId);
        if ($spMkp->isAuthenticated()) {
            $tryApi = $this->spApiSeller($spMkp);
            if ($tryApi['success']) {
                return array('pass' => true);
            }
        }

        return array('pass' => false);
    }

    public function prestashopModeDev()
    {
        $callback = Tools::getValue('callback');

        if ($callback == '?' || empty($callback)) {
            $callback = 'jsonp_'.time();
        }

        $message = null;
        $new_state = Tools::getValue('status');
        $new_state_text = !(bool)$new_state ? 'false' : 'true';

        if ($new_state !== '0' && $new_state !== '1') {
            die('Target status unknown');
        }

        if (!defined('_PS_CONFIG_DIR_')) {
            define('_PS_CONFIG_DIR_', _PS_ROOT_DIR_.'/config/');
        }

        $defines_inc_php = _PS_CONFIG_DIR_.'defines.inc.php';
        $defines_inc_php_bak = _PS_CONFIG_DIR_.'defines.inc.php.bak';

        if (!file_exists($defines_inc_php) || !is_writable($defines_inc_php)) {
            die('File doesnt exists or is not writeable');
        }

        if (!($md5_orig = md5_file($defines_inc_php))) {
            die(sprintf('Unable to generate md5 of file: %s', $defines_inc_php));
        }

        if (!AmazonTools::copy($defines_inc_php, $defines_inc_php_bak)) {
            die(sprintf('Unable to create a backup (from %s to %s)', $defines_inc_php, $defines_inc_php_bak));
        }

        if (!($md5_dest = md5_file($defines_inc_php_bak))) {
            die(sprintf('Unable to generate md5 of file: %s', $defines_inc_php_bak));
        }

        if (!Tools::strlen($md5_dest) || $md5_orig != $md5_dest) {
            die('md5sum mismatch, operation aborted');
        }

        $defines_inc_contents = AmazonTools::fileGetContents($defines_inc_php);

        if (!Tools::strlen($defines_inc_php)) {
            die('Unable to get file contents, operation aborted');
        }

        if (md5($defines_inc_contents) != $md5_dest) {
            die('md5sum mismatch, operation aborted');
        }

        $defines_inc_contents_out = preg_replace('/(_PS_MODE_DEV_[\"\'][\s,]*)(true|false|TRUE|FALSE)/', '$1'.$new_state_text, $defines_inc_contents);

        $length_diff = abs(Tools::strlen($defines_inc_contents) - Tools::strlen($defines_inc_contents_out));

        if ($length_diff > 1) {
            die('messup, operation aborted');
        }

        if (!file_put_contents($defines_inc_php, $defines_inc_contents_out)) {
            if (!AmazonTools::copy($defines_inc_php_bak, $defines_inc_php)) {
                die('/!\\ huge trouble: operation failed, backup restore failed too !');
            } else {
                die('operation failed backup restored');
            }
        } else {
            $message = sprintf(html_entity_decode('_PS_MODE_DEV_ switched to &lt;b&gt;%s&lt;/b&gt; with success'), !(bool)$new_state ? 'Off' : 'On');
        }

        $json = json_encode(array('status' => (bool)$new_state, 'message' => $message));

        echo (string)$callback.'('.$json.')';
        die;
    }

    /**
     * Create and serve support zip file
     */
    public function downloadSupportZipFile()
    {
        $screen_shot = Tools::getValue('screenShot');
        $includeScreenshot = (bool)$screen_shot;

        $file_prefix        = AmazonTools::getFriendlyUrl(Configuration::get('PS_SHOP_NAME'));
        $ps_info_file       = sprintf('%s%s-ps-info.txt', $this->path, $file_prefix);
        $php_info_file      = sprintf('%s%s-php-info.html', $this->path, $file_prefix);
        $screen_shot_file   = sprintf('%s%s-screen-shot.png', $this->path, $file_prefix);
        $log_path           = sprintf('%slogs', $this->path);
        $zip_file_path      = sprintf('%s%s-support.zip', $this->path, $file_prefix);

        file_put_contents($ps_info_file, $this->prestashopInfo(true));
        file_put_contents($php_info_file, $this->phpInfo(true));
        if ($includeScreenshot) {
            file_put_contents(
                $screen_shot_file,
                base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $screen_shot))//TODO: Validation: Use to evaluate base64 encoded values, required
            );            
        }

        @unlink($zip_file_path);

        // To create zip with root path
        chdir($this->path);
        $zipContent = array(basename($ps_info_file), basename($php_info_file), realpath($log_path));
        if ($includeScreenshot) {
            $zipContent[] = basename($screen_shot_file);
        }
        $zip = new AmazonZip($zip_file_path, $zipContent);

        if ($zip->createZip($zip_file_path, $zipContent) && file_exists($zip_file_path)) {
            header('Content-Type: application/octet-stream', true);
            header('Content-Disposition: attachment; filename='.$file_prefix.'-support.zip', true);
            echo Tools::file_get_contents($zip_file_path);
        } else {
            echo 'An error occurred...';
        }

        // Delete created files for security reasons
        @unlink($ps_info_file);
        @unlink($php_info_file);
        @unlink($screen_shot_file);
        @unlink($zip_file_path);

        exit;
    }

    public function prestashopInfo($return_data = false)
    {
        $content = null;
        $header_errors = ob_get_clean();
        $own_url = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'].$_SERVER['REQUEST_URI'] : sprintf('%s://%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI'];

        if ($return_data) {
            // Do not print downloadable link
        } elseif (Tools::getValue('download')) {
            header('Pragma: public');
            header('Cache-Control: no-cache');
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="'.AmazonTools::getFriendlyUrl(Configuration::get('PS_SHOP_NAME')).'-ps-infos.txt'.'"');
        } else {
            echo html_entity_decode('&lt;a href="'.$own_url.'&download=1" title="'.$this->l('Download').'" target="_blank"&gt;'.$this->l('Download').'&lt;/a&gt;');
        }

        if (version_compare(_PS_VERSION_, 1.5, '>=')) {
            $check_duplicate_sql = 'SELECT `name`, `id_shop`, `id_shop_group`, COUNT(*) as count
                FROM `'._DB_PREFIX_.'configuration` WHERE name like "%AMAZON%"
                GROUP BY `name`, `id_shop`,  `id_shop_group`
                HAVING COUNT(*) > 1';

            $results = Db::getInstance()->executeS($check_duplicate_sql);

            if (is_array($results) && count($results)) {
                $content .= Amazon::LF;
                $content .= 'Reason is: '.Amazon::LF;
                $content .= 'Duplicated configuration keys: '.Amazon::LF;

                foreach ($results as $result) {
                    $content .= $result['name'];
                    $content .= ": ";
                    $content .= $result['count'];
                    $content .= Amazon::LF;
                }
            }
        }

        if (version_compare(_PS_VERSION_, 1.5, '>=')) {
            $sort = 'ORDER by `name`,`id_shop`';
            $ps15 = true;
        } else {
            $sort = 'ORDER by `name`';
            $ps15 = false;
        }

        $results2 = null;
        $results = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE "PS_%" OR `name` LIKE "AMAZON_%" '.$sort);

        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_NEW_CONFIGURATION)) {
            $results2 = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_NEW_CONFIGURATION.'` WHERE `name` LIKE "AMAZON_%" ');
        }

        if (is_array($results) && is_array($results2)) {
            $new_results = array();
            foreach ($results as $result) {
                $new_results[] = $result;
            }
            foreach ($results2 as $result) {
                $new_results[] = $result;
            }
            ksort($new_results);
            $results = $new_results;
        }

        $ps_configuration = null;
        $to_ignore = array('EMAIL', 'PASSWORD', 'PASSWD', 'CONTEXT_DATA', 'WIZARD', 'AMAZON_REPRICING', 'KEY_ID', 'SECRET');
        $multistore_configurations = array();

        foreach ($results as $result) {
            $pass = true;
            foreach ($to_ignore as $ignore) {
                if (strpos($result['name'], $ignore) !== false) {
                    $pass = false;
                }
            }
            if (!$pass) {
                continue;
            }

            $value = $initial_value = $result['value'];

            if (base64_encode(base64_decode($value, true)) === $value) {//TODO: Validation: Use to evaluate base64 encoded values, required
                //TODO: Validation: Use to evaluate base64 encoded values, required
                $value = base64_decode($value, true);//TODO: Validation: Use to evaluate base64 encoded values, required
            } else {
                //TODO: Validation: Required by test above
                $value = $result['value'];
            }

            if ($ps15) {
                $ps_configuration .= sprintf('%-50s %03d %03d : %s'.Amazon::LF, $result['name'], $result['id_shop'], $result['id_shop_group'], $value);
            } else {
                $ps_configuration .= sprintf('%-50s : %s'.Amazon::LF, $result['name'], $value);
            }

            if (stristr($result['name'], 'AMAZON_') != false && isset($result['id_shop']) && $result['id_shop'] != null) {
                $name = $result['name'];
                if (!isset($multistore_configurations[$name]) && !empty($value)) {
                    $multistore_configurations[$name]= array();
                    $multistore_configurations[$name][] = $value;
                } else {
                    $multistore_configurations[$name][] = $value;
                }
            }
        }


        if (is_array($multistore_configurations) && count($multistore_configurations)) {
            $multistore_configurations_keys = array_keys($multistore_configurations);

            foreach ($multistore_configurations_keys as $key) {
                if (is_array($multistore_configurations[$key]) && count($multistore_configurations[$key]) > 1) {
                    $multistore_configurations[$key] = array_filter($multistore_configurations[$key]);
                    if ($multistore_configurations[$key] && $count = count($multistore_configurations[$key])) {
                        $diffs = array_unique($multistore_configurations[$key]);

                        if (count($diffs) != $count) {
                            unset($multistore_configurations_keys[$key]);
                        }
                    }
                }
            }
        }

        // Aug-23-2018: Remove ps_carriers_only option

        if (defined('Carrier::ALL_CARRIERS')) {
            $all_carriers = Carrier::ALL_CARRIERS;
        } elseif (defined('ALL_CARRIERS')) {
            $all_carriers = ALL_CARRIERS;
        } else {
            $all_carriers = 5;
        }

        $carriers = Carrier::getCarriers($this->id_lang, false, false, false, null, $all_carriers);

        $content .= $this->_psGeneralInfo($own_url) . Amazon::LF;

        if (class_exists('PrestaShopAutoload')) {
            $prestashopAutoLoad = PrestaShopAutoload::getInstance();
            $prestashopAutoLoad->generateIndex();
            $overrides = array();

            if (is_array($prestashopAutoLoad->index) && count($prestashopAutoLoad->index)) {
                foreach ($prestashopAutoLoad->index as $item) {
                    if (stripos($item['path'], 'override/') !== false) {
                        $overrides[] = $item['path'];
                    }
                }
            }
            if (is_array($overrides) && count($overrides)) {
                $content .= Amazon::LF;
                $content .= 'Overrides:'.Amazon::LF;
                foreach ($overrides as $override) {
                    $content .= $override.Amazon::LF;
                }
            }
        }

        if (version_compare(_PS_VERSION_, 1.5, '>=')) {
            $check_duplicate_sql = 'SELECT `name`, `id_shop`, `id_shop_group`, COUNT(*) as count
                FROM `'._DB_PREFIX_.'configuration` WHERE name like "%AMAZON%"
                GROUP BY `name`, `id_shop`,  `id_shop_group`
                HAVING COUNT(*) > 1';

            $results = Db::getInstance()->executeS($check_duplicate_sql);

            if (is_array($results) && count($results)) {
                $content .= Amazon::LF;
                $content .= 'Duplicated configuration keys: '.Amazon::LF;

                foreach ($results as $result) {
                    $content .= $result['name'];
                    $content .= ": ";
                    $content .= $result['count'];
                    $content .= Amazon::LF;
                }
            }
        }

        $patternTextPrintF = html_entity_decode('%-58s : &lt;b&gt;%s&lt;/b&gt;').Amazon::LF;

        $content .= Amazon::LF;
        $content .= 'Catalog: '.Amazon::LF;

        $content .= sprintf($patternTextPrintF, 'Categories', Db::getInstance()->getValue('SELECT count(`id_category`) as count FROM `'._DB_PREFIX_.'category`'));
        $content .= sprintf($patternTextPrintF, 'Products', Db::getInstance()->getValue('SELECT count(`id_product`) as count FROM `'._DB_PREFIX_.'product`'));
        $content .= sprintf($patternTextPrintF, 'Combinations', Db::getInstance()->getValue('SELECT count(`id_product`) as count FROM `'._DB_PREFIX_.'product_attribute`'));
        $content .= sprintf($patternTextPrintF, 'Attributes', Db::getInstance()->getValue('SELECT count(`id_attribute`) as count FROM `'._DB_PREFIX_.'attribute`'));
        $content .= sprintf($patternTextPrintF, 'Features', Db::getInstance()->getValue('SELECT count(`id_feature_value`) as count FROM `'._DB_PREFIX_.'feature_value`'));
        $content .= sprintf($patternTextPrintF, 'Specific Price', Db::getInstance()->getValue('SELECT count(`id_specific_price`) as count FROM `'._DB_PREFIX_.'specific_price`'));

        if (AmazonTools::tableExists(_DB_PREFIX_.self::TABLE_MARKETPLACE_ORDERS)) {
            $results = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.self::TABLE_MARKETPLACE_ORDERS.'` ORDER BY `id_order` DESC LIMIT '.(int)Tools::getValue('orders', 10));
            $content .= Amazon::LF;
            $content .= 'Last 10 Orders: '.Amazon::LF;

            if (is_array($results) && count($results)) {
                $colums = implode(',', array_keys(reset($results)));
                $content .= print_r($colums, true).Amazon::LF;
                foreach ($results as $result) {
                    $values = implode(',', $result);
                    $content .= print_r($values, true).Amazon::LF;
                }
            }
        }

        $categories = AmazonConfiguration::get('categories');
        $profiles = AmazonConfiguration::get('profiles');
        $profiles_categories = AmazonConfiguration::get('profiles_categories');
        $mapping = AmazonConfiguration::get('mapping');

        $content .= Amazon::LF;
        $content .= 'Amazon Tables: '.Amazon::LF;
        $content .= sprintf($patternTextPrintF, 'Categories', is_array($categories) ? count($categories, COUNT_RECURSIVE) : 0);
        $content .= sprintf($patternTextPrintF, 'Profiles', is_array($profiles) ? count($profiles) : 0);
        $content .= sprintf($patternTextPrintF, 'Profiles Values', count($profiles, COUNT_RECURSIVE));
        $content .= sprintf($patternTextPrintF, 'Profiles to Categories', is_array($profiles_categories) ? count($profiles_categories, COUNT_RECURSIVE) : 0);
        $content .= sprintf($patternTextPrintF, 'Mappings', is_array($mapping) ? count($mapping) : 0);
        $content .= sprintf($patternTextPrintF, 'Mappings Values', count($mapping, COUNT_RECURSIVE));

        $orders_states = OrderState::getOrderStates($this->id_lang);
        $content .= Amazon::LF;
        $content .= 'OrderStates: '.Amazon::LF;

        foreach ($orders_states as $key => $orders_state) {
            if ($key == 0) {
                $content .= implode(', ', array_keys($orders_state)).Amazon::LF;
            }
            $content .= implode(', ', $orders_state).Amazon::LF;
        }

        $content .= Amazon::LF;
        $content .= 'Carriers'.Amazon::LF;

        if (is_array($carriers) && count($carriers)) {
            foreach ($carriers as $key => $carrier) {
                if ($key == 0) {
                    $content .= implode(', ', array_keys($carrier)).Amazon::LF;
                }
                $content .= implode(', ', $carrier).Amazon::LF;
            }
        }
        $content .= Amazon::LF;


        $content .= 'Languages'.Amazon::LF;

        foreach (Language::getLanguages(false, $this->context->shop->id) as $language) {
            $id_lang = (int)$language['id_lang'];
            $active = (bool)$language['active'];
            $language_code = $language['language_code'];
            $iso_code = $language['iso_code'];
            $name = $language['name'];

            $content .= sprintf('%d: %s - %s - %s - %s'.Amazon::LF, $id_lang, $name, $iso_code, $language_code, $active ? 'Active' : 'Inactive');
        }
        $content .= Amazon::LF;

        if (is_array($multistore_configurations) && count($multistore_configurations)) {
            $content .= 'Multistore Configurations:'.Amazon::LF;
            foreach ($multistore_configurations as $key => $multistore_configuration) {
                if (count($multistore_configurations[$key]) > 1) {
                    $content .= sprintf('%-50s : %s'.Amazon::LF, $key, print_r($multistore_configuration, true));
                }
            }
            $content .= Amazon::LF;
        }

        // Current Queue
        //
        $languages = AmazonTools::languages();
        $action_queues = AmazonProduct::getCurrentQueue();
        $content .= 'Queues'.Amazon::LF;

        if (is_array($action_queues) && count($action_queues)) {
            foreach ($action_queues as $key => $action_queue) {
                if (isset($languages[$action_queue['id_lang']])) {
                    $lang = $languages[$action_queue['id_lang']]['name'];
                    $iso_code = $languages[$action_queue['id_lang']]['iso_code'];
                } else {
                    $lang = $this->l('Inactive');
                    $iso_code = null;
                }
                $date_min = AmazonTools::displayDate($action_queue['date_min'], $this->id_lang, true);
                $date_max = AmazonTools::displayDate($action_queue['date_max'], $this->id_lang, true);

                switch ($action_queue['action']) {
                    case self::ADD:
                        $action = $this->l('Add');
                        break;
                    case self::REMOVE:
                        $action = $this->l('Delete');
                        break;
                    case self::UPDATE:
                        $action = $this->l('Update');
                        break;
                }
                $content .= sprintf('Iso: %s Lang: %s Date Min: %s Date Max: %s Action: %s Count: %d', $iso_code, $lang, $date_min, $date_max, $action, $action_queue['count']);
                $content .= Amazon::LF;
            }
        } else {
            $content .= "Empty" . Amazon::LF;
        }


        $content .= Amazon::LF;
        $content .= 'Configuration: '.Amazon::LF;
        $content .= $ps_configuration;

        $content .= Amazon::LF;
        $content .= 'Amazon Categories: '.Amazon::LF;
        $content .= print_r($categories, true);

        $content .= Amazon::LF;
        $content .= 'Amazon Profiles to Categories: '.Amazon::LF;
        $content .= print_r(AmazonConfiguration::get('profiles_categories'), true);

        $content .= Amazon::LF;
        $content .= 'Amazon Profiles:'.Amazon::LF;
        $content .= print_r($profiles, true);

        if ($return_data) {
            $content .= Amazon::LF;
            $content .= $header_errors;

            return $content;
        } else {
            AmazonTools::pre(array($content));
            CommonTools::d($header_errors);
        }
    }

    public function phpInfo($return_data = false)
    {
        $content = '';
        $header_errors = ob_get_clean();

        ob_start();
        phpinfo(INFO_ALL & ~INFO_CREDITS & ~INFO_LICENSE & ~INFO_ENVIRONMENT & ~INFO_VARIABLES);
        $php_info = ob_get_clean();
        $php_info = preg_replace('/(a:link.*)|(body, td, th, h1, h2.*)|(img.*)/', '', $php_info);

        if ($download = Tools::getValue('download')) {
            header('Pragma: public');
            header('Cache-Control: no-cache');
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="'.AmazonTools::getFriendlyUrl(Configuration::get('PS_SHOP_NAME')).'-ps-infos.txt'.'"');

            $php_info = strip_tags(preg_replace('/<(td|th)[^>]*>/i', '<$1> ', $php_info));
            $php_info .= strip_tags($header_errors);
            die($php_info);
        } else {
            $content .= html_entity_decode("&lt;/pre&gt;") . Amazon::LF . Amazon::LF;
            $content .= html_entity_decode('&lt;h1&gt;PHP&lt;/h1&gt;'.Amazon::LF);
            $content .= html_entity_decode('&lt;div class="phpinfo"&gt;');
            $content .= $php_info;
            $content .= html_entity_decode('&lt;/div&gt;');
            $content .= $header_errors;

            if (!$return_data) {
                $protocol = Tools::strtolower(Tools::substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://';
                $own_url = sprintf('%s://%s', $protocol, $_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI'];
                echo html_entity_decode('&lt;a href="'.$own_url.'&download=1" title="'.$this->l('Download').'" target="_blank"&gt;'.$this->l('Download').'&lt;/a&gt;');
                die($content);
            } else {
                return $content;
            }
        }
    }

    /**
     * @param $marketplaceId
     * @param $auths // Dev mode: Check Connectivity
     * @return array
     * @throws Exception
     */
    public function checkSPApi($marketplaceId, $auths = null)
    {
        $logger = new AmazonLogger(AmazonLogger::CHANNEL_SP_API_SELLERS);
        $spConnector = AmazonSPConnectorPSMkp::initFromMarketplace($marketplaceId, null, $auths);
        if ($spConnector->belongToUnifiedEU() && !$auths) {
            $spConnector = new AmazonSPConnectorPSRegion($spConnector->getRegion());
        }

        return $this->spApiSeller($spConnector, $logger);
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $logger
     * @return array
     */
    private function spApiSeller($spConnector, $logger = null)
    {
        // Not yet check $spMkp->isAuthenticated() because the credentials may not saved yet (manual override in dev mode)

        $sellerAPI = new AmazonSPAPISellers($spConnector, $logger, $this->moduleFeatures->dev_mode);
        $sellerInfo = $sellerAPI->apiSeller();
        if ($logger) {
            $logger->debug('Get participant marketplaces:', $sellerInfo->getRawResponse());            
        }

        if ($sellerInfo->hasError()) {
            if ($sellerInfo->getUpstream()) {
                return array('success' => false, 'reason' => $sellerInfo->getUpstream()->getMessage());
            }
            return array('success' => false, 'reason' => $sellerInfo->getErrorMsg());
        }
        $listMkpParticipation = $sellerInfo->getStructuredPayload();
        if (!count($listMkpParticipation)) {
            return array('success' => false, 'reason' => $this->l('No available marketplace!'));
        }

        return array('success' => true, 'msg' => $this->l('Connection to Amazon : Ok'), 'data' => $listMkpParticipation);
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if(!$lang)
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }

    /**
     * Merchant: The seller id
     * Marketplace: marketplace name (from connector)
     * CC: country Code
     * Currency: The RED one is the currency is not imported in Localization.
     * Local status: if the language for this is active on local the status is green and opposite.
     * Remote status: if the participant is not in Suspended Listings (this property from the amazon response follow the connector create from marketplace) the status is green and opposite.
     * @return array
     * @throws Exception
     */
    public function participations()
    {
        $participations = array();
        $actives = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_LANG_ACTIVE);
        $mkpIDs = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_LANG_TO_AMZ_MKP_ID);

        if ($mkpIDs && is_array($mkpIDs)) {
            foreach ($mkpIDs as $psLang => $mkpID) {
                if ($mkpID) {
                    $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($mkpID);
                    if ($spMkp->isAuthenticated()) {
                        $sellerId = $spMkp->getSellerId();
                        $sellerApi = new AmazonSPAPISellers($spMkp);
                        $apiResponse = $sellerApi->apiSeller();
                        if (!$apiResponse->hasError()) {
                            /** @var AmazonSPDefMarketplaceParticipation[] $listMkpParticipation */
                            $listMkpParticipation = $apiResponse->getStructuredPayload();
                            if (is_array($listMkpParticipation) && count($listMkpParticipation)) {
                                foreach ($listMkpParticipation as $participation) {
                                    $marketplaceId = $participation->marketplace->id;
                                    
                                    $currencyCode = $participation->marketplace->defaultCurrencyCode;
                                    $currencyLoaded = false;
                                    if (($id_currency = Currency::getIdByIsoCode($currencyCode))) {
                                        $currency = new Currency($id_currency);
                                        if (Validate::isLoadedObject($currency)) {
                                            $currencyLoaded = true;
                                        }
                                    }
                                    
                                    $psStatus = isset($actives[$psLang]) && $actives[$psLang];
                                    $amzStatus = !$participation->participation->hasSuspendedListings;
                                    $participations[$sellerId][$marketplaceId] = array(
                                        'name' => $participation->marketplace->name,
                                        'cc' => $participation->marketplace->countryCode,
                                        'currency' => $participation->marketplace->defaultCurrencyCode,
                                        'currency_state' => $currencyLoaded,
                                        'domain' => $participation->marketplace->domainName,
                                        'l_status' => $psStatus ? $this->l('Active') : $this->l('Inactive'),
                                        'l_image' => $psStatus ? $this->images.'status_green.png' : $this->images.'status_red.png',
                                        'r_status' => $amzStatus ? $this->l('Valid') : $this->l('Suspended'),
                                        'r_image' => $amzStatus ? $this->images.'status_green.png' : $this->images.'status_red.png',
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return array(
            'error' => !count($participations),
            'errors' => '',
            'result' => '',
            'participations' => $participations,
        );
    }

    private function _psGeneralInfo($own_url)
    {
        $tabs = '';
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $tabs =  AmazonDataAdjustment::serialize($this->_psTabs());
        }

        $generalPSInfo = array(
            html_entity_decode('&lt;h1&gt;Prestashop&lt;/h1&gt;'),
            'Version: ' . _PS_VERSION_,
            'Module: ' . sprintf('%s/%s', $this->name, $this->version),
            'Expert Mode: ' . ($this->amazon_features['expert_mode'] ? 'Yes' : 'No'),
            'Mode Dev: ' . (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ ? 'Yes' : 'No'),
            $this->_psOverride(),
            'Live Configuration Fields: ' . Tools::getValue('fields'),
            'Max Input Vars: ' . @ini_get('max_input_vars') . '/' . @get_cfg_var('max_input_vars'),
            'Memory Limit: ' . @ini_get('memory_limit') . '/' . @get_cfg_var('memory_limit'),
            '',
            'DB prefix: ' . _DB_PREFIX_,
            'DB version: ' . Db::getInstance()->getVersion(),
            'DB time zone: ' . Db::getInstance()->getValue('SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP)'),
            'PHP time zone: ' . date_default_timezone_get(),
            $tabs,
            'Check URL: ' . $own_url,
        );

        return implode(Amazon::LF, $generalPSInfo);
    }

    private function _psTabs()
    {
        $result = array();
        $amazonTabs = Tab::getCollectionFromModule('amazon')->getResults();
        /** @var Tab $amazonTab */
        foreach ($amazonTabs as $amazonTab) {
            $tab = array(
                'name' => $amazonTab->class_name,
                'id' => $amazonTab->id,
                'parent' => $amazonTab->id_parent,
                'position' => $amazonTab->position,
                'active' => $amazonTab->active,
            );
            if ($this->ps16x || $this->ps17x) {
                $tab['hide_host_mode'] = $amazonTab->hide_host_mode;
            }
            $result[] = $tab;
        }
        
        return $result;
    }

    private function _psOverride()
    {
        $override = false;
        if (defined('_PS_OVERRIDE_DIR_') && !Configuration::get('PS_DISABLE_OVERRIDES')
            && ($override_content = AmazonTools::globRecursive(_PS_OVERRIDE_DIR_ . '*.php'))) {
            foreach ($override_content as $fn) {
                if (preg_match('/[A-Z]\w+.php$/', $fn)) {
                    $override = true;
                    break;
                }
            }
        }

        return 'Running Overrides: ' . ($override ? 'Yes' : 'No');
    }
}

$pmConnexionCheck = new AmazonConnexionCheck();
$pmConnexionCheck->dispatch();
