<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

class RevolutApplePayOnBoardingHelper
{
    protected $module;
    public $domain_name = '';
    public $domain_onboarding_file_remote_link = 'https://assets.revolut.com/api-docs/merchant-api/files/domain_validation_file_prod';
    public $domain_onboarding_file_name = 'apple-developer-merchantid-domain-association';
    public $domain_onboarding_file_directory = '.well-known';
    public $onboarding_file_dir = '';
    public $onboarding_file_path = '';

    public function __construct($rev_module)
    {
        $this->module = $rev_module;
        $this->domain_name = Tools::getHttpHost(false);
        $this->onboarding_file_dir = _PS_ROOT_DIR_ . '/' . $this->domain_onboarding_file_directory;
        $this->onboarding_file_path = $this->onboarding_file_dir . '/' . $this->domain_onboarding_file_name;

        $this->maybeOnboardApplePayMerchant();
    }

    public function maybeOnboardApplePayMerchant()
    {
        if (!$this->checkIsShopNeedsOnboarding()) {
            return false;
        }

        if (!$this->downloadOnboardingFile()) {
            Configuration::updateValue('apple_pay_merchant_onboarded_domain', '');
            Configuration::updateValue('apple_pay_merchant_onboarded', 'no');

            return false;
        }

        if (!$this->registerDomain()) {
            $this->module->setErrorMessage($this->module->l('Can not on-board Apple Pay merchant'), []);

            Configuration::updateValue('apple_pay_merchant_onboarded_domain', '');
            Configuration::updateValue('apple_pay_merchant_onboarded', 'no');

            return false;
        }

        $this->removeOnboardingFile();

        Configuration::updateValue('apple_pay_merchant_onboarded_domain', $this->domain_name);
        Configuration::updateValue('apple_pay_merchant_onboarded_api_key', $this->module->revolutApi->api_key);
        Configuration::updateValue('apple_pay_merchant_onboarded', 'yes');

        $this->module->setSuccessMessage($this->module->l('Apple Pay merchant on-boarded successfully: ' . $this->domain_name));
    }

    public function registerDomain()
    {
        try {
            $request_body = array(
                'domain' => $this->domain_name,
            );
            $this->module->revolutApi->apiRequest('/apple-pay/domains/register', $request_body, true, true);
        } catch (Exception $e) {
            $this->module->logError($e->getMessage());
            return false;
        }

        return true;
    }

    public function downloadOnboardingFile()
    {
        if (!file_exists($this->onboarding_file_dir) && !@mkdir($this->onboarding_file_dir, 0755)) {
            $this->module->setErrorMessage($this->module->l('Can not on-board Apple Pay merchant: Can not create directory'), []);
            return false;
        }

        if (!file_put_contents($this->onboarding_file_path, Tools::file_get_contents($this->domain_onboarding_file_remote_link))) {
            $this->module->setErrorMessage($this->module->l('Can not on-board Apple Pay merchant: Can not locate on-boarding file'), []);
            return false;
        }

        return true;
    }

    public function removeOnboardingFile()
    {
        if (!unlink($this->onboarding_file_path)) {
            $this->module->setErrorMessage($this->module->l('Can not remove on-boarding file'), []);
        }
    }

    public function checkIsShopNeedsOnboarding()
    {
        if ($this->checkIsAlreadyOnboarded()) {
            return false;
        }

        if (!$this->checkIsApiKeyConfigured()) {
            return false;
        }

        return true;
    }

    public function checkIsAlreadyOnboarded()
    {
        return Configuration::get('apple_pay_merchant_onboarded') === 'yes'
            && Configuration::get('apple_pay_merchant_onboarded_api_key') === $this->module->revolutApi->api_key
            && $this->domain_name === Configuration::get('apple_pay_merchant_onboarded_domain');
    }

    public function checkIsApiKeyConfigured()
    {
        return !empty($this->module->revolutApi->api_key) && $this->module->revolutApi->mode != 'sandbox';
    }
}
