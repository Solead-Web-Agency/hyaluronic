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

namespace ZendeskAddon\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PsAccount
{
    protected $module;
    protected $context;

    public function __construct($module, $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    /**
     * Retrieve Ps Account Service
     */
    public function getPsAccount()
    {
        $accountsService = null;
        try {
            /** @var \PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts $accountsFacade */
            $accountsFacade = $this->module->getService('zendesk.ps_accounts_facade');
            /** @var \PrestaShop\Module\PsAccounts\Service\PsAccountsService $accountsService */
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (\PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
            return null;
        }

        if ($accountsFacade !== null) {
            try {
                \Media::addJsDef([
                    'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                        ->present($this->module->name),
                ]);
            } catch (\Exception $e) {
                $this->context->controller->errors[] = 'bbb ' . $e->getMessage();
            }
        }

        return $accountsService;
    }

    /**
     * Retrieve billings service
     */
    public function getBillings()
    {
        $subscription = [];
        try {
            $cache_id = 'ZENDESK_LAST_SUBSCRIPTION_SHOP_' . $this->context->shop->id;
            $lastSubscription = \Configuration::getGlobalValue($cache_id);
            if ($lastSubscription !== false) {
                $subscription = json_decode($lastSubscription, true);
                $subscription['finished'] = false;
                $timeUpdate = $subscription['next_billing_at'];
                $dateUpdated = (new \DateTimeImmutable())->setTimestamp($timeUpdate);
                $diff = $dateUpdated->diff(new \DateTimeImmutable('now'));
                $total_minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                if ($total_minutes > 60 * 24) {
                    $subscription['finished'] = true;
                }
            }

            if (empty($subscription) === false && isset($subscription['finished']) && $subscription['finished'] === false) {
                return $subscription;
            }

            // Load the service for PrestaShop Billing
            $billingService = $this->module->getService('zendesk.ps_billings_service');

            // Retrieve current subscription
            $currentSubscription = $billingService->getCurrentSubscription();
            // We test here the success of the request in the response's body.
            if (!empty($currentSubscription['success'])) {
                $subscriptionBody = $currentSubscription['body'];
                $subscription = [
                    'next_billing_at' => $subscriptionBody['next_billing_at'],
                    'id' => $subscriptionBody['id'],
                    'status' => $subscriptionBody['status'],
                    'plan_id' => $subscriptionBody['plan_id'],
                    'id_shop' => $this->context->shop->id,
                ];
                \Configuration::updateGlobalValue($cache_id, json_encode($subscription));

                return $subscription;
            }
        } catch (\Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();
        }

        return [];
    }

    public function getActiveSubscription($shops)
    {
        foreach ($shops as $oneShop) {
            $confId = 'ZENDESK_LAST_SUBSCRIPTION_SHOP_' . $oneShop['id_shop'];
            $lastSubscription = \Configuration::getGlobalValue($confId);
            if ($lastSubscription !== false) {
                return json_decode($lastSubscription, true);
            }
        }

        return false;
    }
}
