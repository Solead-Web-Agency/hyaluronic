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

require_once _PS_MODULE_DIR_ . 'zendesk/vendor/autoload.php';

/*
Installation will fail on PS 1.6 if "use" statements are in the main module file
use ZendeskAddon\Hook\HookDispatcher;
use ZendeskClasslib\Extensions\ProcessLogger\ProcessLoggerExtension;
use ZendeskClasslib\Module;
*/
// Loading ZendeskApi
require_once _PS_MODULE_DIR_ . 'zendesk/config_prod.php';
require_once _PS_MODULE_DIR_ . 'zendesk/models/ZendeskApi.php';

// use ZendeskClasslib\Module; // Not compatible with PS 1.6

class Zendesk extends ZendeskClasslib\Module
{
    /**
     * List of objectModel used in this Module
     *
     * @var array
     */
    public $objectModels = [];

    /**
     * List of controllers used in this Module
     *
     * @var array
     */
    public $controllers = [];

    /**
     * List of admin controllers used in this Module
     *
     * @var array
     */
    public $moduleAdminControllers = [
        [
            'name' => [
                'en' => 'Zendesk',
                'fr' => 'Zendesk',
            ],
            'class_name' => 'AdminZendeskParentMain',
            'parent_class_name' => 'CONFIGURE',
            'visible' => false,
        ],
        [
            'name' => [
                'en' => 'Zendesk',
                'fr' => 'Zendesk',
            ],
            'class_name' => 'AdminZendeskParent',
            'parent_class_name' => 'AdminZendeskParentMain',
            'visible' => false,
        ],
        [
            'name' => [
                'en' => 'Settings',
                'fr' => 'Configuration',
            ],
            'class_name' => 'AdminZendeskConfiguration',
            'parent_class_name' => 'AdminZendeskParent',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Built For PrestaShop Invoices',
            ],
            'class_name' => 'AdminZendeskPrestaShopInvoices',
            'parent_class_name' => 'AdminZendeskParent',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Logs',
                'fr' => 'Logs',
            ],
            'class_name' => 'AdminZendeskProcessLogger',
            'parent_class_name' => 'AdminZendeskParent',
            'visible' => true,
        ],
    ];

    public $hooks = [];

    public $extensions = [
        ZendeskClasslib\Extensions\ProcessLogger\ProcessLoggerExtension::class,
    ];

    public $bootstrap = false;

    /** @var ZendeskApi */
    public $api;

    /** @var PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer */
    private $container;

    /** @var string The "Prestashop plugin" app id in Zendesk's catalog
     *  DO NOT CAST THIS AS AN INTEGER
     *  At some point, it might be too big for Prestashop's validation or the server's capacity
     */
    const ZENDESK_APP_ID = '128903'; // old : 86180 or 128240 id of prestashop application;

    const SUBDOMAIN = 'ZENDESK_SUBDOMAIN';

    const ONBOARDING = 'ZENDESK_ONBOARDING';

    const WIDGET = 'ZENDESK_WIDGET';

    const APP = 'ZENDESK_APP';

    const USERNAME = 'ZENDESK_USERNAME';

    const ID_BRAND = 'ZENDESK_ID_BRAND';
    const APIKEY = 'ZENDESK_APIKEY';

    const ORDER_ID_FIELD_ID = 'ZENDESK_ORDER_ID_FIELD_ID';

    const CONNECTOR_KEY = 'ZENDESK_CONNECTOR_KEY';

    const IS_FILE_LOGGER_ACTIVE = 'ZENDESK_IS_FILE_LOGGER_ACTIVE';

    const PROCESS_LOGGER_QUIET_MODE = 'ZENDESK_PROCESS_LOGGER_QUIET_MODE';

    const PRELOAD_WIDGET = 'ZENDESK_PRELOAD_WIDGET';

    const PRELOAD_WIDGET_CONTROLLERS = 'ZENDESK_PRELOAD_WIDGET_CONTROLLERS';

    const SHOP_URL = 'ZENDESK_SHOP_URL';

    public function __construct()
    {
        $this->name = 'zendesk';
        $this->tab = 'administration';
        $this->version = '2.1.0';
        $this->author = '202 ecommerce';
        $this->ps_versions_compliancy = [
            'min' => '1.6.1.11',
            'max' => _PS_VERSION_,
        ];
        $this->module_key = '478622aa5726d385d1de33ae1f543919';

        $this->bootstrap = false;
        parent::__construct();

        $this->displayName = $this->l('Zendesk');
        $this->description = $this->l('Zendesk helps you deliver the best customer support to your customers.');

        $this->api = new ZendeskApi();

        $this->hookDispatcher = new ZendeskAddon\Hook\HookDispatcher($this);
        $this->hooks = array_merge($this->hooks, $this->hookDispatcher->getAvailableHooks());

        if ($this->container === null) {
            $this->container = new PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name,
                $this->getLocalPath()
            );
        }
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        Configuration::updateValue(self::ONBOARDING, (int) true);
        Configuration::updateValue(self::WIDGET, 1);
        Configuration::updateValue(self::APP, 1);
        /* Connector */
        $this->installConnectorKey();

        // Set widget preload to false by default to avoid performance impact
        Configuration::updateValue(self::PRELOAD_WIDGET, 0);
        Configuration::updateValue(self::PRELOAD_WIDGET_CONTROLLERS, json_encode([
            'index' => 0,
            'product' => 0,
            'category' => 0,
            'cart' => 0,
            'order' => 0,
        ]));

        // Set Process Logger quiet mode at true by default
        Configuration::updateValue(self::PROCESS_LOGGER_QUIET_MODE, (int) true);

        // Load the PrestaShop Account utility
        $this->installPsAccount();

        return true;
    }

    public function addTab()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $tabCustomers = Tab::getInstanceFromClassName('AdminParentCustomer');
        } else {
            $tabCustomers = Tab::getInstanceFromClassName('AdminParentCustomerThreads');
        }

        if (!Validate::isLoadedObject($tabCustomers)) {
            return false;
        }
        $tab = new Tab();
        foreach (Language::getLanguages(true) as $language) {
            $tab->name[(int) $language['id_lang']] = 'Zendesk';
        }
        $tab->class_name = 'AdminZendesk';
        $tab->id_parent = $tabCustomers->id;
        $tab->module = $this->name;

        return $tab->add();
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all) && $this->addTab();
    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all) && $this->removeTab();
    }

    public function removeTab()
    {
        $tab = Tab::getInstanceFromClassName('AdminZendesk');

        while (Validate::isLoadedObject($tab)) {
            $tab->delete();
            $tab = Tab::getInstanceFromClassName('AdminZendesk');
        }

        return true;
    }

    public function getContent()
    {
        $configuration_url = $this->context->link->getAdminLink('AdminZendeskConfiguration');

        Tools::redirectAdmin($configuration_url);
    }

    /**
     * Handle Hooks loaded on extension
     *
     * @param string $name Hook name
     * @param array $arguments Hook arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ($result = $this->handleExtensionsHook(
            $name,
            !empty($arguments[0]) ? $arguments[0] : []
        )
        ) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    /**
     * Retrieve the service
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        return $this->container->getService($serviceName);
    }

    public function installPsAccount()
    {
        try {
            $this->getService('zendesk.ps_accounts_installer')->install();
        } catch (Exception $ex) {
            PrestaShopLogger::addLog('[ZENDESK] Ps Account - ' . $ex->getMessage());
        }
    }

    public function installConnectorKey()
    {
        $connectorKeyGlobal = Configuration::getGlobalValue(Zendesk::CONNECTOR_KEY);
        if (false !== $connectorKeyGlobal) {
            return true;
        }

        // We check if connector key is already set on one shop to prevent errors with plugin
        foreach (Shop::getShops() as $oneShop) {
            $connectorKey = Configuration::get(Zendesk::CONNECTOR_KEY, null, null, (int) $oneShop['id_shop']);
            if ($connectorKey !== false && empty($connectorKey) === false) {
                $connectorKeyGlobal = $connectorKey;
                break;
            }
        }

        if ($connectorKeyGlobal === false) {
            // If we didn't find one, we set default generation
            $connectorKeyGlobal = Tools::strtoupper(Tools::passwdGen(16));
        }

        Configuration::updateGlobalValue(Zendesk::CONNECTOR_KEY, $connectorKeyGlobal);
    }
}
