<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminZendeskPrestaShopInvoicesController extends ModuleAdminController
{
    /** @var Zendesk */
    public $module;

    public function initContent()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->page_header_toolbar_btn['zendesk_config'] = [
                'href' => $this->context->link->getAdminLink('AdminZendeskConfiguration'),
                'desc' => $this->module->l('Configuration'),
                'icon' => 'process-icon-cogs',
            ];
            $this->page_header_toolbar_btn['zendesk_logs'] = [
                'href' => $this->context->link->getAdminLink('AdminZendeskProcessLogger'),
                'desc' => $this->module->l('Logs'),
                'icon' => 'process-icon-terminal',
            ];
            $this->page_header_toolbar_btn['zendesk_invoices'] = [
                'href' => $this->context->link->getAdminLink('AdminZendeskPrestaShopInvoices'),
                'desc' => 'PrestaShop ' . $this->module->l('Invoices'),
                'icon' => 'process-icon-envelope',
            ];
        }

        $tpl_vars = [];
        $accountsService = $this->module->getService('zendesk.ps_account_service');
        $psAccountService = $accountsService->getPsAccount();
        $psAccountInstalled = $psAccountService !== null;
        $billingFacade = null;
        if ($psAccountInstalled) {
            $tpl_vars['urlAccountsCdn'] = $psAccountService->getAccountsCdn();
            $tpl_vars['urlBilling'] = 'https://unpkg.com/@prestashopcorp/billing-cdc/dist/bundle.js';
            $billingsContextWrapper = $this->module->getService('zendesk.ps_billings_context_wrapper');
            $token = $billingsContextWrapper->getAccessToken();
            if (false === empty($token)) {
                $billingFacade = $this->module->getService('zendesk.ps_billings_facade');
                Media::addJsDef($billingFacade->present([
                    'tosLink' => 'https://www.202-ecommerce.com/mentions-legales/',
                    'privacyLink' => 'https://www.202-ecommerce.com/mentions-legales/',
                    'emailSupport' => 'contact@202-ecommerce.com',
                ]));
            }
        }
        $tpl_vars['psAccountInstalled'] = $psAccountInstalled && $billingFacade !== null;
        $tplFile = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/zendesk/prestashop_invoices.tpl';
        $tpl = $this->context->smarty->createTemplate($tplFile);
        $tpl->assign($tpl_vars);
        $this->content = $tpl->fetch();

        parent::initContent();
    }
}
