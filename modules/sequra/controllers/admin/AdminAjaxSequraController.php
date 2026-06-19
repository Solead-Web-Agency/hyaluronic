<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    SeQura Tech <prestashop@sequra.com>
 * @copyright Since 2013 SeQura WorldWide SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PrestashopSequra\Configuration\Settings;
use PrestaShop\Module\PrestashopSequra\Logger;

class AdminAjaxSequraController extends ModuleAdminController
{
    /**
     * @var Sequra
     */
    public $module;

    /**
     * @var bool
     */
    public $ajax = true;

    /**
     * @var bool
     */
    protected $json = true;

    /**
     * AJAX: Update Configurations Key
     */
    public function ajaxProcessUpdateConfigKey()
    {
        $key = $this->safeGetKey();
        $value = Tools::getValue('value');
        if (strpos($key, 'SEQURA') !== 0) {
            $this->ensureResponse(null, 400, 'Invalid key');
        }

        if ($key == 'SEQURA_COUNTRIES') {
            $countries = array_filter(
                explode(',', $value),
                function ($country) {
                    return in_array($country, ['ES', 'IT', 'FR', 'PT']);
                }
            );
            array_walk(
                $countries,
                [
                    '\PrestaShop\Module\PrestashopSequra\Configuration\PaymentMethodsSettings',
                    'updateActivePaymentMethods',
                ]
            );
            $value = implode(',', $countries);
        } elseif ('SEQURA_ALLOW_IP' == $key) {
            $value = trim($value);
            if ('' !== $value) {
                $ips = array_map('trim', explode(',', $value));
                foreach ($ips as $ip) {
                    if (preg_match(Settings::IP_REGEX, $ip) !== 1) {
                        $this->ensureResponse(null, 400, 'Invalid IP');
                    }
                }
                $value = implode(',', $ips);
            }
        } elseif ('SEQURA_FOR_SERVICES_END_DATE' == $key) {
            $value = trim($value);
            if ('' !== $value) {
                if (preg_match(Settings::ISO8061_REGEX, $value) !== 1
                || 'P' === $value
                || 'T' === substr($value, -1)) {
                    $this->ensureResponse(null, 400, 'Invalid service end date');
                }
            }
        } elseif ('SEQURA_BANNERS' == $key) {
            try {
                $banner = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON');
                }

                // check if has the correct keys.
                $requiredKeys = ['country', 'link', 'image', 'hook'];
                foreach ($requiredKeys as $requiredKey) {
                    if (!isset($banner[$requiredKey])) {
                        throw new Exception('Invalid JSON');
                    }
                }

                // capitalize the country code.
                $banner['country'] = strtoupper($banner['country']);

                $banners = Configuration::get('SEQURA_BANNERS');
                $banners = empty($banners) ? [] : json_decode($banners, true);

                // check if the banner already exists.
                $bannerIndex = array_search($banner['country'], array_column($banners, 'country'));
                if (false !== $bannerIndex) {
                    $banners[$bannerIndex] = $banner;
                } else {
                    $banners[] = $banner;
                }
                // Then encode the banners to save them in the database.
                $value = json_encode($banners);
            } catch (Throwable $e) {
                $this->ensureResponse(null, 400, $e->getMessage());
            }
        }

        $is_enable_debug = $key == 'SEQURA_ENABLE_DEBUG';

        if ($is_enable_debug && '0' === $value) {
            // debug mode is disabled.
            $logger = new Logger();
            $logger->logDebug('Debug mode disabled', __FUNCTION__, __CLASS__);
        }

        Configuration::updateValue($key, $value);

        if ($key == 'SEQURA_COUNTRIES') {
            $this->module->setAllowedCountries($this->context->shop->id);
        }
        if ($is_enable_debug && '1' === $value) {
            // debug mode is enabled.
            $logger = new Logger();
            $logger->logDebug('Debug mode enabled', __FUNCTION__, __CLASS__);
        }
        $this->ensureResponse();
    }

    public function displayAjaxGetConfig()
    {
        $this->ensureResponse($this->module->getConfigJson(false));
    }

    public function displayAjaxGetCustomCSS()
    {
        $settings = new Settings($this->module);
        $response = $settings->getFileContents($settings->getCustomCssPath());
        $response = base64_encode($response);
        $this->ensureResponse($response);
    }

    public function displayAjaxResetCustomCSS()
    {
        $settings = new Settings($this->module);
        $file = $settings->getCustomCssThemePath();
        if (file_exists($file)) {
            unlink($file);
        }
        $response = $settings->getFileContents($settings->getCustomCssModulePath());
        $response = base64_encode($response);
        $this->ensureResponse($response);
    }

    /**
     * AJAX: Get log content
     */
    public function displayAjaxGetLogs()
    {
        try {
            $logger = new Logger();
            $content = $logger->getLogContent();
            $this->ensureResponse(base64_encode($content));
        } catch (Exception $e) {
            $this->ensureResponse(null, 500, $e->getMessage());
        }
    }

    /**
     * AJAX: Clear log content
     */
    public function ajaxProcessClearLogs()
    {
        try {
            (new Logger())->clearLog();
            $this->ensureResponse();
        } catch (Exception $e) {
            $this->ensureResponse(null, 500, $e->getMessage());
        }
    }

    public function ajaxProcessSetCustomCSS()
    {
        $settings = new Settings($this->module);
        $file = $settings->getCustomCssThemePath();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(
            $file,
            base64_decode(Tools::getValue('custom_css'))
        );
        $this->ensureResponse(sprintf($this->module->l('Custom CSS saved to %s', 'AdminAjaxSequraController'), $file));
    }

    /**
     * AJAX: uploadBanner
     */
    public function ajaxProcessUploadBanner()
    {
        if (empty($_FILES['image'])) {
            $this->ensureResponse(null, 400, 'Bad args: image not found');
        }

        // check if the file is an image.
        $image = $_FILES['image'];
        $imageInfo = getimagesize($image['tmp_name']);
        if (false === $imageInfo) {
            $this->ensureResponse(null, 400, 'Bad args: the file is not a valid image');
        }

        $country = $this->getBannerCountry();
        if (empty($country)) {
            $this->ensureResponse(null, 400, 'Bad args: country is required and must be a non-empty string');
        }

        $path = join(DIRECTORY_SEPARATOR, ['sequra', strtolower($country), 'banner', basename($image['name'])]);
        $uploadPath = _PS_UPLOAD_DIR_ . $path;

        // check if the directory exists
        $dir = dirname($uploadPath);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $this->ensureResponse(null, 500, 'Error creating directory ' . $dir);
        }

        if (!move_uploaded_file($image['tmp_name'], $uploadPath)) {
            $this->ensureResponse(null, 500, 'Error uploading image');
        }

        $url = _PS_BASE_URL_ . str_replace(_PS_ROOT_DIR_, '', _PS_UPLOAD_DIR_) . $path;
        $this->ensureResponse($url);
    }

    /**
     * Get the country from the request.
     *
     * @return string|false
     */
    private function getBannerCountry()
    {
        $country = Tools::getValue('country', false);
        if (empty($country)) {
            return false;
        }
        $country = preg_replace('/[^a-zA-Z0-9_\-]/', '', $country);

        if ('' === $country) {
            return false;
        }

        return $country;
    }

    /**
     * AJAX: deleteBannerImage
     */
    public function ajaxProcessDeleteBannerImage()
    {
        $country = $this->getBannerCountry();
        if (empty($country)) {
            $this->ensureResponse(null, 400, 'Bad args: country is required and must be a non-empty string');
        }

        // get the image name from the configuration.
        $banners = Configuration::get('SEQURA_BANNERS');
        $banners = empty($banners) ? [] : json_decode($banners, true);
        $bannerIndex = array_search($country, array_column($banners, 'country'));
        if (false !== $bannerIndex) {
            $image = $banners[$bannerIndex]['image'];
            $path = join(DIRECTORY_SEPARATOR, ['sequra', strtolower($country), 'banner', basename($image)]);
            $uploadPath = _PS_UPLOAD_DIR_ . $path;

            // try to delete the file or throw an error if it fails.
            if (file_exists($uploadPath) && !unlink($uploadPath)) {
                $this->ensureResponse(null, 500, 'Error deleting image');
            }
        }
        $this->ensureResponse();
    }

    /**
     * AJAX: getPaymentMethods
     */
    public function ajaxProcessGetPaymentMethods()
    {
        $settings = new Settings($this->module);
        $this->ensureResponse($settings->getMethodsForAllCountries());
    }

    private function safeGetKey()
    {
        $key = Tools::getValue('key', '');
        if (strpos($key, 'SEQURA_') !== 0) {
            $this->ensureResponse(null, 400, 'Invalid key');
        }

        return $key;
    }

    /**
     * TODO: Check if this method is still needed or if it can be removed.
     * AJAX: Change prestashop rounding settings
     *
     * PS_ROUND_TYPE need to be set to 1 (Round on each item)
     * PS_PRICE_ROUND_MODE need to be set to 2 (Round up away from zero, wh
     */
    public function ajaxProcessEditRoundingSettings()
    {
        Configuration::updateValue('PS_ROUND_TYPE', '1');
        Configuration::updateValue('PS_PRICE_ROUND_MODE', '2');
        $this->ensureResponse(json_encode(true), 200, null, false);
    }

    /**
     * Helper function to ensure the response always has the correct format.
     * If the response should be json encoded, the $response will be converted
     * to an array with a key 'success' equals to 'true' or 'false' depending on
     * the $response_code, then json encoded. The original response will be under
     * the key 'value'.
     *
     * @param mixed $response The response to send
     * @param int $response_code The response code to send
     * @param string $error_message The error message to send if the response code is not 2xx and must be json encoded
     * @param bool $do_json_encode If the response should be json encoded
     */
    private function ensureResponse($response = null, $response_code = 200, $error_message = null, $do_json_encode = true)
    {
        $response_code = (int) $response_code;
        http_response_code($response_code);

        $value = $response;
        if ($do_json_encode) {
            $value = ['success' => $response_code >= 200 && $response_code < 300];
            if (null !== $response) {
                $value['value'] = $response;
            }

            if (!$value['success'] && null !== $error_message) {
                $value['error'] = $error_message;
            }
            $value = json_encode($value);
        } else {
            $value = $response;
        }

        if (version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            $this->ajaxRender($value);
            exit;
        } else {
            $this->ajaxDie($value);
        }
    }
}
