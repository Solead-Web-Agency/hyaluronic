<?php
/**
 *  Module made by Nukium
 *
 *  @author    Nukium
 *  @copyright 2023 Nukium SAS
 *  @license   All rights reserved
 *
 * ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
 * ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
 * ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
 * ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
 * ██   ████  ██████  ██   ██ ██  ██████  ██      ██
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use Nukium\GLS\Common\Exception\GlsApiException;
use Nukium\GLS\Common\Legacy\GlsApi;
use Nukium\GLS\Common\Legacy\GlsController;
use Nukium\GLS\Common\Service\Adapter\Shop\ShopFactory;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorFactory;
use Nukium\GLS\Common\Service\Helper\GlsHelper;
use Nukium\GLS\Common\Service\Routine\AllowedServicesRoutine;
use Nukium\GLS\Common\Value\GlsValue;
use Nukium\PrestaShop\GLS\Service\Handler\DTO\Adapter\Address\PrestashopAddressHandler;
use Nukium\PrestaShop\GLS\Service\Handler\DTO\Adapter\Carrier\PrestashopCarrierHandler;
use Nukium\PrestaShop\GLS\Service\Helper\EnvHelper;
use Nukium\PrestaShop\GLS\Service\Helper\ModuleHelper;
use Nukium\PrestaShop\GLS\Service\Init\PrestashopGlsInit;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once dirname(__FILE__) . '/vendor/autoload.php';

if (!class_exists('AdminGlsOrderController')) {
    require_once dirname(__FILE__) . '/controllers/admin/AdminGlsOrderController.php';
}
if (!class_exists('AdminGlsAjaxController')) {
    require_once dirname(__FILE__) . '/controllers/admin/AdminGlsAjaxController.php';
}
if (!class_exists('AdminGlsLabelController')) {
    require_once dirname(__FILE__) . '/controllers/admin/AdminGlsLabelController.php';
}
if (!class_exists('AdminGlsPackingListController')) {
    require_once dirname(__FILE__) . '/controllers/admin/AdminGlsPackingListController.php';
}

class NkmGls extends CarrierModule implements WidgetInterface
{
    private static $instance;

    public static $lang_doc = ['fr', 'en'];

    public static $carrier_definition = [
        'GLSCHEZVOUS' => [
            'name' => 'En entreprise',
            'delay' => [
                'fr' => 'Livraison en 24/48h réservée exclusivement aux professionnels.',
            ],
            'grade' => 9,
        ],
        'GLSRELAIS' => [
            'name' => 'En Relais GLS',
            'delay' => [
                'fr' => 'Livraison en 24/48h dans l\'un des Relais GLS de votre choix. Vous êtes informé par e-mail et SMS dès l\'arrivée du colis.',
            ],
            'grade' => 9,
        ],
        'GLSCHEZVOUSPLUS' => [
            'name' => 'À domicile',
            'delay' => [
                'fr' => 'Livraison en 24/48h. Vous êtes prévenu par e-mail et SMS, avec possibilité, la veille, de changer d\'adresse ou de date. Suivi géolocalisé sur mobile le jour de la livraison.',
            ],
            'grade' => 9,
        ],
        'GLS13H' => [
            'name' => 'Express',
            'delay' => [
                'fr' => 'Livraison le lendemain avant 13H en France métropolitaine.',
            ],
            'grade' => 9,
        ],
    ];

    public static $trackingUrl = 'https://gls-group.eu/FR/fr/suivi-colis?match=@';

    private static $ftp_host = 'ftp.gls-france.com';
    private static $ftp_login = 'addonline';
    private static $ftp_pwd = '-mAfXmTqC';

    public $importDirectory = '';

    protected $addressHandler;

    protected $carrierHandler;

    protected $shopService;

    protected $translatorService;

    protected $glsHelper;

    protected $moduleHelper;

    protected $envHelper;

    protected $allowedServicesRoutine;

    public static $trackingStates = [
        'PREADVICE' => 'En cours de préparation',
        'INTRANSIT' => 'Acheminement en cours',
        'INWAREHOUSE' => 'Au dépôt de livraison',
        'INDELIVERY' => 'En livraison',
        'DELIVERED' => 'Livré',
        'NOTDELIVERED' => 'Ne peut pas être livré aujourd\'hui',
        'DELIVEREDPS' => 'Livré en relais',
    ];

    private $templates = [
        'displayCarrierExtraContent' => 'carrier_extra_content.tpl',
        'displayOrderDetail' => 'order_detail.tpl',
        'displayAfterCarrier' => 'error.tpl',
        'displayInfoByCart' => 'info_by_cart.tpl',
        'displayWorkingsDay' => 'working_day.tpl',
        'displayAdminOrderLeft' => 'admin_order_left.tpl',
        'displayAdminOrder' => 'admin_order.tpl',
    ];

    public $id_carrier = null;

    private static $old_shop_context = [];

    public function __construct()
    {
        self::$instance = $this;

        $this->name = 'nkmgls';
        $this->tab = 'shipping_logistics';
        $this->version = '3.1.0';
        $this->author = 'Nukium';
        $this->module_key = 'd792bc0479dc21963d46aae4c3fa0dbd';
        $this->need_instance = 0;

        $this->bootstrap = true;

        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];

        $this->displayName = $this->l('GLS, your shipping partner');
        $this->description = $this->l('Give your customers the choice of the shipping method that suits them.');

        parent::__construct();

        if (!extension_loaded('soap')) {
            $this->warning .= $this->l('The SOAP extension is not available or configured on the server ; The
            module will not work without this extension ! Please contact your host to activate it in your PHP
            installation.');
        }

        $this->initialize();
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    private function initialize()
    {
        $secure_key = Configuration::get('GLS_SECURE_KEY');
        if ($secure_key === false) {
            Configuration::updateValue('GLS_SECURE_KEY', Tools::strtoupper(Tools::passwdGen(16)));
        }

        $this->importDirectory = _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR;

        self::$trackingStates = [
            'PREADVICE' => $this->l('In preparation'),
            'INTRANSIT' => $this->l('In transit'),
            'INWAREHOUSE' => $this->l('In delivery depot'),
            'INDELIVERY' => $this->l('In delivery'),
            'DELIVERED' => $this->l('Delivered'),
            'NOTDELIVERED' => $this->l('Cannot be delivered today'),
            'DELIVEREDPS' => $this->l('Delivered in GLS Relais'),
        ];

        if ($this->getPrestaShopVersion() === '1.7') {
            $this->templates['displayAdminOrderLeft'] = 'admin_order_main_bottom.tpl';
        }

        PrestashopGlsInit::getInstance()->init();

        $this->addressHandler = PrestashopAddressHandler::getInstance();
        $this->carrierHandler = PrestashopCarrierHandler::getInstance();
        $this->shopService = ShopFactory::getInstance();
        $this->translatorService = TranslatorFactory::getInstance();
        $this->glsHelper = GlsHelper::getInstance();
        $this->moduleHelper = ModuleHelper::getInstance();
        $this->envHelper = EnvHelper::getInstance();
        $this->allowedServicesRoutine = AllowedServicesRoutine::getInstance();
    }

    public function install()
    {
        self::$old_shop_context['type'] = Shop::getContext();
        if (Shop::getContext() == Shop::CONTEXT_GROUP) {
            self::$old_shop_context['id'] = Shop::getContextShopGroupID(true);
        } else {
            self::$old_shop_context['id'] = Shop::getContextShopID(true);
        }

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        Configuration::updateValue('GLS_GLSRELAIS_XL_ONLY', '');
        Configuration::updateValue('GLS_WSLOGIN', '');
        Configuration::updateValue('GLS_WSPWD', '');
        Configuration::updateValue('GLS_AGENCY_CODE', '');
        Configuration::updateValue('GLS_GOOGLE_MAPS_API_KEY', '');
        Configuration::updateValue('GLS_GOOGLE_MAPS_API_SCRIPT_ENABLE', '1');
        Configuration::updateValue('GLS_ORDER_PREFIX_ENABLE', '1');
        Configuration::updateValue('GLS_LAST_SYNCHRO_DATE', date('Y-m-d H:i:s'));
        Configuration::updateValue('GLS_SYNCHRO_FILE_CONTENT_HASH', '');
        Configuration::updateValue('GLS_EXPORT_AUTOMATION', '0');
        Configuration::updateValue('GLS_EXPORT_NEW_ORDER_STATE', '');
        Configuration::updateValue('GLS_EXPORT_ORDER_STATE', '');
        Configuration::updateValue('GLS_IMPORT_AUTOMATION', '0');
        Configuration::updateValue('GLS_IMPORT_NEW_ORDER_STATE', '');
        Configuration::updateValue('GLS_API_CUSTOMER_ID', '');
        Configuration::updateValue('GLS_API_CONTACT_ID', '');
        Configuration::updateValue('GLS_API_DELIVERY_LABEL_FORMAT', 'A6');
        Configuration::updateValue('GLS_API_SHOP_RETURN_SERVICE', '0');
        Configuration::updateValue('GLS_API_SHOP_RETURN_EMAIL_ALERT', '0');
        Configuration::updateValue('GLS_API_LOGIN', '');
        Configuration::updateValue('GLS_API_PWD', '');
        Configuration::updateValue('GLS_API_SHOP_RETURN_ADDRESS', '1');
        Configuration::updateValue('GLS_ADD_PRICE_MOUNTAIN', 0);
        Configuration::updateValue('GLS_ADD_PRICE_FR_ISLAND', 0);
        Configuration::updateValue('GLS_ADD_PRICE_CORSICA', 0);
        Configuration::updateValue('GLS_ADD_PRICE_GB_ISLAND', 0);
        Configuration::updateValue('GLS_ADD_PRICE_SP_PT_ISLAND', 0);
        Configuration::updateValue('GLS_ADD_PRICE_ISLANDS', 0);
        Configuration::updateValue('GLS_ORDER_PREFIX', '');
        Configuration::updateValue('GLS_CUSTOM_EXPORT_PATH_ENABLE', 0);
        Configuration::updateValue('GLS_CUSTOM_EXPORT_PATH', '');
        Configuration::updateValue('GLS_EXPORT_ORDER_REFERENCE_ENABLE', 0);
        Configuration::updateValue('GLS_GOOGLE_MAPS_ENABLE', 0);
        Configuration::updateValue('GLS_ADD_PRICE_FREE_CARRIER_ENABLE', 1);
        Configuration::updateValue('GLS_SSL_PATCH', 0);
        Configuration::updateValue('GLS_IS_USING_SHIPIT_API', '1');
        Configuration::updateValue('GLS_CAN_USE_LEGACY_API', '0');
        Configuration::updateValue('GLS_API_DEBUG', '0');
        Configuration::updateValue('GLS_SHIPIT_MIDDLEWARE', 'GLS FR PrestaShop');

        foreach (array_keys(self::$carrier_definition) as $key) {
            Configuration::updateValue('GLS_' . $key . '_ID', '');
            Configuration::updateValue('GLS_' . $key . '_LOG', '');
        }

        $installDB = $this->installDBConfig();

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install()
            && $installDB
            && AdminGlsOrderController::installInBO()
            && AdminGlsLabelController::installInBO()
            && AdminGlsAjaxController::installInBO()
            && AdminGlsPackingListController::installInBO()
            && $this->installHooks();
    }

    public function uninstall()
    {
        Configuration::deleteByName('GLS_GLSRELAIS_XL_ONLY');
        Configuration::deleteByName('GLS_WSLOGIN');
        Configuration::deleteByName('GLS_WSPWD');
        Configuration::deleteByName('GLS_AGENCY_CODE');
        Configuration::deleteByName('GLS_GOOGLE_MAPS_API_KEY');
        Configuration::deleteByName('GLS_GOOGLE_MAPS_API_SCRIPT_ENABLE');
        Configuration::deleteByName('GLS_ORDER_PREFIX_ENABLE');
        Configuration::deleteByName('GLS_LAST_SYNCHRO_DATE');
        Configuration::deleteByName('GLS_SYNCHRO_FILE_CONTENT_HASH');
        Configuration::deleteByName('GLS_SECURE_KEY');
        Configuration::deleteByName('GLS_EXPORT_AUTOMATION');
        Configuration::deleteByName('GLS_EXPORT_NEW_ORDER_STATE');
        Configuration::deleteByName('GLS_EXPORT_ORDER_STATE');
        Configuration::deleteByName('GLS_IMPORT_AUTOMATION');
        Configuration::deleteByName('GLS_IMPORT_NEW_ORDER_STATE');
        Configuration::deleteByName('GLS_API_CUSTOMER_ID');
        Configuration::deleteByName('GLS_API_CONTACT_ID');
        Configuration::deleteByName('GLS_API_DELIVERY_LABEL_FORMAT');
        Configuration::deleteByName('GLS_API_SHOP_RETURN_SERVICE');
        Configuration::deleteByName('GLS_API_SHOP_RETURN_EMAIL_ALERT');
        Configuration::deleteByName('GLS_API_LOGIN');
        Configuration::deleteByName('GLS_API_PWD');
        Configuration::deleteByName('GLS_API_SHOP_RETURN_ADDRESS');
        Configuration::deleteByName('GLS_API_SHOP_RETURN_ADDRESS_NAME');
        Configuration::deleteByName('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS1');
        Configuration::deleteByName('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS2');
        Configuration::deleteByName('GLS_API_SHOP_RETURN_ADDRESS_POSTCODE');
        Configuration::deleteByName('GLS_API_SHOP_RETURN_ADDRESS_CITY');
        Configuration::deleteByName('GLS_API_SHOP_RETURN_ADDRESS_COUNTRY');
        Configuration::deleteByName('GLS_ADD_PRICE_MOUNTAIN');
        Configuration::deleteByName('GLS_ADD_PRICE_FR_ISLAND');
        Configuration::deleteByName('GLS_ADD_PRICE_CORSICA');
        Configuration::deleteByName('GLS_ADD_PRICE_GB_ISLAND');
        Configuration::deleteByName('GLS_ADD_PRICE_SP_PT_ISLAND');
        Configuration::deleteByName('GLS_ADD_PRICE_ISLANDS');
        Configuration::deleteByName('GLS_ORDER_PREFIX');
        Configuration::deleteByName('GLS_CUSTOM_EXPORT_PATH_ENABLE');
        Configuration::deleteByName('GLS_CUSTOM_EXPORT_PATH');
        Configuration::deleteByName('GLS_EXPORT_ORDER_REFERENCE_ENABLE');
        Configuration::deleteByName('GLS_GOOGLE_MAPS_ENABLE');
        Configuration::deleteByName('GLS_ADD_PRICE_FREE_CARRIER_ENABLE');
        Configuration::deleteByName('GLS_SSL_PATCH');
        Configuration::deleteByName('GLS_IS_USING_SHIPIT_API');
        Configuration::deleteByName('GLS_CAN_USE_LEGACY_API');
        Configuration::deleteByName('GLS_API_DEBUG');
        Configuration::deleteByName('GLS_SHIPIT_MIDDLEWARE');

        foreach (array_keys(self::$carrier_definition) as $key) {
            $gls_carrier_id = Configuration::get('GLS_' . $key . '_ID');

            if (!empty($gls_carrier_id)) {
                $history_log = explode('|', Configuration::get('GLS_' . $key . '_LOG'));
                $history_log[] = $gls_carrier_id;
                Configuration::updateValue('GLS_' . $key . '_LOG', implode('|', array_map('intval', $history_log)));

                if (Validate::isLoadedObject($object = new Carrier((int) $gls_carrier_id))) {
                    $object->active = false;
                    $object->update();
                }
            }

            $gls_carrier_id = Configuration::deleteByName('GLS_' . $key . '_ID');
        }

        return parent::uninstall()
            && Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'gls_agency_postcode`')
            && GlsLogClass::removeDbTable()
            && GlsLabelClass::removeDbTable()
            && GlsTrackingStateClass::removeDbTable()
            && GlsCacheClass::removeDbTable()
            && AdminGlsOrderController::removeFromBO()
            && AdminGlsLabelController::removeFromBO()
            && AdminGlsAjaxController::removeFromBO()
            && AdminGlsPackingListController::removeFromBO()
        ;
    }

    private function installHooks()
    {
        if ($this->getPrestaShopVersion() === '1.7') {
            $hooks = true;
        } else {
            $hooks = $this->registerHook('displayAdminOrderLeft');
        }

        return
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('actionAdminControllerSetMedia') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayCarrierExtraContent') &&
            $this->registerHook('actionCarrierUpdate') &&
            $this->registerHook('actionObjectCarrierUpdateAfter') &&
            $this->registerHook('displayOrderDetail') &&
            $this->registerHook('displayAfterCarrier') &&
            $this->registerHook('actionValidateStepComplete') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionCarrierProcess') &&
            $this->registerHook('registerGDPRConsent') &&
            $this->registerHook('actionDeleteGDPRCustomer') &&
            $this->registerHook('actionObjectCustomerDeleteAfter') &&
            $this->registerHook('actionExportGDPRData') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('displayAdminOrderMainBottom') &&
            $hooks
        ;
    }

    private function installDBConfig()
    {
        if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gls_cart_carrier` (
            `id_cart` int(10) UNSIGNED NOT NULL DEFAULT \'0\',
            `id_customer` int(10) unsigned NOT NULL DEFAULT \'0\',
            `id_carrier` int(10) UNSIGNED NOT NULL DEFAULT \'0\',
            `gls_product` varchar(255) NOT NULL,
            `parcel_shop_id` varchar(255) DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,
            `address1` varchar(255) DEFAULT NULL,
            `address2` varchar(255) DEFAULT NULL,
            `postcode` varchar(255) DEFAULT NULL,
            `city` varchar(255) DEFAULT NULL,
            `phone` varchar(255) DEFAULT NULL,
            `phone_mobile` varchar(255) DEFAULT NULL,
            `customer_phone_mobile` varchar(255) NOT NULL,
            `id_country` int(10) unsigned DEFAULT NULL,
            `parcel_shop_working_day` TEXT DEFAULT NULL,
            PRIMARY KEY (`id_cart`,`id_customer`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;')) {
            return false;
        }

        if (Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gls_agency_postcode` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `agency_code` varchar(255) NOT NULL,
            `postcode_start` varchar(5) NOT NULL,
            `postcode_end` varchar(5) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `agency_code` (`agency_code`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;')) {
            $csv_file = new NkmCSVReader();
            $csv_content = $csv_file->parse_file($this->importDirectory . 'tbzipdeltimes.csv', false, true);
            $this->synchronizeAgencyPostcodeRestriction(true, $csv_content);
        } else {
            return false;
        }

        if (!GlsLabelClass::createDbTable()) {
            return false;
        }

        if (!GlsTrackingStateClass::createDbTable()) {
            return false;
        }

        if (!GlsLogClass::createDbTable()) {
            return false;
        }

        if (!GlsCacheClass::createDbTable()) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('z.`id_zone`')
            ->from('zone', 'z')
            ->where('z.`name` LIKE \'%France%\'');
        $id_zone_france = Db::getInstance()->getValue($sql);
        if (!$id_zone_france) {
            $zone = new Zone();
            $zone->name = 'France';
            $zone->active = true;
            if (!$zone->add()) {
                $this->_errors[] = $this->l('Impossible to create carrier zone France.');

                return false;
            }

            $id_zone_france = $zone->id;
        }

        $id_country_france = Country::getByIso('FR');
        if (!$id_country_france) {
            $this->_errors[] = $this->l('Country FR not found.');

            return false;
        }

        $old_id_zone_france = Country::getIdZone($id_country_france);
        if ($id_zone_france != $old_id_zone_france) {
            $country = new Country();
            $country->affectZoneToSelection([$id_country_france], $id_zone_france);

            $sql = new DbQuery();
            $sql->select('c.*')
                ->from('carrier', 'c')
                ->leftJoin('carrier_zone', 'cz', 'cz.`id_carrier` = c.`id_carrier`')
                ->leftJoin('zone', 'z', 'z.`id_zone` = ' . (int) $old_id_zone_france)
                ->where('c.`active` = 1')
                ->where('cz.`id_zone` = ' . (int) $old_id_zone_france)
                ->where('z.`active` = 1');

            foreach (Db::getInstance()->executeS($sql) as $value) {
                $sql = new DbQuery();
                $sql->select('*')
                    ->from('carrier_zone')
                    ->where('`id_carrier` = ' . (int) $value['id_carrier'])
                    ->where('`id_zone` = ' . (int) $id_zone_france);
                if (!Db::getInstance()->getValue($sql)) {
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'carrier_zone` (`id_carrier`, `id_zone`) VALUES (' . (int) $value['id_carrier'] . ', ' . (int) $id_zone_france . ')');
                    Db::getInstance()->execute('
                        INSERT INTO `' . _DB_PREFIX_ . 'delivery` (`id_carrier`, `id_shop`, `id_shop_group`, `id_range_price`, `id_range_weight`, `id_zone`, `price`) (
                            SELECT ' . (int) $value['id_carrier'] . ', `id_shop`, `id_shop_group`, `id_range_price`, `id_range_weight`, ' . (int) $id_zone_france . ', `price`
                            FROM `' . _DB_PREFIX_ . 'delivery`
                            WHERE `id_carrier` = ' . (int) $value['id_carrier'] . '
                            AND `id_zone` = ' . (int) $old_id_zone_france . '
                        )
                    ');
                }
            }
        }

        $create_carrier = true;
        foreach (self::$carrier_definition as $key => $value) {
            $create_carrier &= $this->createCarrier($key, $id_zone_france);
        }

        return $create_carrier;
    }

    public function getContent()
    {
        $output = null;

        if ((bool) Tools::isSubmit('submit' . $this->name) === true) {
            $output = $this->postProcess();
        }

        if (in_array($this->context->language->iso_code, self::$lang_doc)) {
            $this->context->smarty->assign('iso_lang', $this->context->language->iso_code);
        } else {
            $this->context->smarty->assign('iso_lang', 'en');
        }

        $contactUrl = $this->envHelper->get('CONTACT_LINK');

        $this->context->smarty->assign([
            'ps_version' => $this->getPrestaShopVersion(),
            'order_link' => $this->context->link->getAdminLink('AdminGlsOrder'),
            'label_link' => $this->context->link->getAdminLink('AdminGlsLabel'),
            'carrier_link' => $this->context->link->getAdminLink('AdminCarriers'),
            'technical_requirements' => $this->checkTechnicalRequirements(),
            'gls_config_contact' => $this->translatorService->trans('You must be a GLS customer to use this module, if you are not yet [a]contact us[/a]', [
                'vars' => [
                    '[a]' => '<a class="alert-link" href="' . $contactUrl . '" target="_blank" rel="noopener">',
                    '[/a]' => '</a>',
                ],
            ]),
        ]);

        $header = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure_header.tpl');
        $footer = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $header . $this->renderForm() . $this->renderLog() . $footer;
    }

    public function checkTechnicalRequirements()
    {
        $errors = [];
        $warnings = [];

        foreach (['curl', 'soap', 'ftp'] as $ext) {
            if (!extension_loaded($ext)) {
                $errors[] = sprintf($this->l('The "%s" extension is not available or configured on the server ; The
                module will not work without this extension ! Please contact your host to activate it in your PHP
                installation.'), $ext);
            }
        }

        $shopAddress = $this->shopService->getAddress();
        $countryCode = $shopAddress->getCountryCode();
        $zipCode = $shopAddress->getZipCode();

        $storesSettingsLink = $this->context->link->getAdminLink('AdminStores');
        $storesSettingsLink .= '#store_fieldset_contact';

        if (
            empty($countryCode) ||
            empty($zipCode)
        ) {
            $errors[] = $this->translatorService->trans('You must enter your contact details in your [a]stores settings[/a]', [
                'vars' => [
                    '[a]' => "<a href=\"{$storesSettingsLink}\" class=\"alert-link\">",
                    '[/a]' => '</a>',
                ],
            ]);
        }

        if ($countryCode !== 'FR') {
            $warnings[] = $this->translatorService->trans('This module can only be used by GLS France customers. You may get malfunctions with the [a]configured sender address[/a]', [
                'vars' => [
                    '[a]' => "<a href=\"{$storesSettingsLink}\" class=\"alert-link\">",
                    '[/a]' => '</a>',
                ],
            ]);
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' .
            $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->title = $this->l('Configure carriers');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'ajax_uri' => $this->context->link->getAdminLink('AdminGlsAjax') . '&ajax=1&action=createCarrier',
        ];

        return $helper->generateForm($this->getConfigForm());
    }

    public function renderLog()
    {
        $helper = $this->initList();

        if (Tools::getIsset('submitReset' . $helper->table)) {
            $_POST = [];
            $this->_filter = false;
            unset($this->_filterHaving);
            unset($this->_having);
        }

        $sql = new DbQuery();
        $sql->select('l.*')
            ->from('gls_log', 'l');

        if ((bool) Tools::isSubmit('submitFilter' . $helper->table) === true && Tools::getValue('action') != 'reset_filters') {
            $date_add = Tools::getValue($helper->table . 'Filter_date_add');
            if ($date_add && is_array($date_add) && count($date_add) > 0) {
                if (isset($date_add[0]) && !empty($date_add[0])) {
                    if (!Validate::isDate($date_add[0])) {
                        $this->errors[] = $this->trans('The \'From\' date format is invalid (YYYY-MM-DD)', [], 'Admin.Notifications.Error');
                    } else {
                        $sql->where('date_add >= \'' . pSQL(Tools::dateFrom($date_add[0])) . '\'');
                    }
                }

                if (isset($date_add[1]) && !empty($date_add[1])) {
                    if (!Validate::isDate($date_add[1])) {
                        $this->errors[] = $this->trans('The \'To\' date format is invalid (YYYY-MM-DD)', [], 'Admin.Notifications.Error');
                    } else {
                        $sql->where('date_add <= \'' . pSQL(Tools::dateTo($date_add[1])) . '\'');
                    }
                }
            }

            $message = Tools::getValue($helper->table . 'Filter_message');
            if ($message) {
                $sql->where('message LIKE \'%' . pSQL(trim($message)) . '%\'');
            }
        }

        $orderBy = Tools::getValue($helper->table . 'Orderby', 'l.date_add');
        $orderWay = Tools::getValue($helper->table . 'Orderway', 'DESC');

        if (Validate::isOrderBy($orderBy) && Validate::isOrderWay($orderWay)) {
            $sql->orderBy($orderBy . ' ' . $orderWay);
        }

        $sql->limit(10000);

        $data = Db::getInstance()->ExecuteS($sql);

        $helper->listTotal = count($data);

        if ($helper->listTotal > 0) {
            $page = ($page = Tools::getValue('submitFilter' . $helper->table)) ? $page : 1;
            $pagination = ($pagination = Tools::getValue($helper->table . '_pagination')) ? $pagination : 50;
            $data = $this->paginateLog($data, $page, $pagination);

            $content = $helper->generateList($data, $this->fields_list);
        } else {
            $content = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/log.tpl');
        }

        return $content;
    }

    protected function getConfigForm()
    {
        $carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, null);
        foreach ($carriers as $key => $value) {
            $carriers[$key]['name'] .= ' (' . $this->l('ID:') . ' ' . $value['id_carrier'] . ')';
        }

        $address = $this->context->shop->getAddress();
        $contactUrl = $this->envHelper->get('CONTACT_LINK');

        $this->context->smarty->assign([
            'gls_logo' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/admin/gls-logo.jpg',
            'gls_intro_contact' => $this->translatorService->trans('You are not yet a GLS customer? [a]Get in touch with us[/a].', [
                'vars' => [
                    '[a]' => '<a href="' . $contactUrl . '" target="_blank">',
                    '[/a]' => '</a>',
                ],
            ]),
        ]);

        $fields_form = [];

        $fields_form[0] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Welcome to the GLS official module'),
                ],
                'input' => [
                    [
                        'type' => 'html',
                        'name' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/config_intro.tpl'),
                        'label' => '',
                        'col' => '12',
                    ],
                ],
            ],
        ];

        $input = [];
        $i = 0;
        $len = count(self::$carrier_definition);
        foreach (self::$carrier_definition as $key => $value) {
            $this->context->smarty->assign(['carrier_key' => $key]);
            $input[] = [
                'type' => 'select',
                'label' => $value['name'],
                'name' => 'GLS_' . $key . '_ID',
                'required' => true,
                'options' => [
                    'query' => $carriers,
                    'id' => 'id_carrier',
                    'name' => 'name',
                    'default' => ['value' => 0, 'label' => $this->l('None - Disable this service')],
                ],
                'col' => '12',
                'form_group_class' => 'col-lg-4',
                'carrier_img' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/admin/' . Tools::strtolower($key) . '.jpg',
            ];
            $input[] = [
                'type' => 'html',
                'name' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/config_btn_create_carrier.tpl'),
                'label' => '&nbsp;',
                'col' => '12',
                'form_group_class' => 'col-lg-6 new-carrier',
            ];

            if ($key == 'GLSRELAIS') {
                $input[] = [
                    'type' => 'switch',
                    'label' => $this->l('Display only XL GLS Relais'),
                    'name' => 'GLS_GLSRELAIS_XL_ONLY',
                    'required' => true,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                    'hint' => $this->l('GLS Relais that can store packages greater than 20 kg, if the length exceeds 120cm or dimension l x 2w x h exceeds 180cm.'),
                    'lang' => false,
                    'form_group_class' => 'col-lg-10 extra-config',
                    'col' => '12',
                ];
            }

            if ($i !== $len - 1) {
                $input[] = [
                    'type' => 'hr',
                    'name' => 'hr',
                ];
            }
            ++$i;
        }

        $input[] = [
            'type' => 'html',
            'name' => $this->l('Additional price by geographical area'),
            'label' => '',
            'col' => '12',
            'form_group_class' => 'panel-heading',
        ];
        $input[] = [
            'type' => 'switch',
            'label' => $this->l('Apply if delivery is free'),
            'name' => 'GLS_ADD_PRICE_FREE_CARRIER_ENABLE',
            'required' => true,
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes'),
                ],
                [
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No'),
                ],
            ],
            'hint' => $this->l('By activating this option, the additional cost will be applied to the amount of the delivery even if the delivery is free'),
            'lang' => false,
            'col' => '12',
        ];
        $input[] = [
            'type' => 'text',
            'label' => $this->l('Additional price for mountain area'),
            'name' => 'GLS_ADD_PRICE_MOUNTAIN',
            'suffix' => $this->context->currency->sign,
            'class' => 'input fixed-width-sm',
            'col' => '12',
            'form_group_class' => 'col-lg-6',
            'lang' => false,
        ];
        $input[] = [
            'type' => 'text',
            'label' => $this->l('Additional price for French islands'),
            'name' => 'GLS_ADD_PRICE_FR_ISLAND',
            'suffix' => $this->context->currency->sign,
            'class' => 'input fixed-width-sm',
            'col' => '12',
            'form_group_class' => 'col-lg-6',
            'lang' => false,
            'hint' => $this->l('Corsica and DOM-TOM excluded.'),
        ];
        $input[] = [
            'type' => 'text',
            'label' => $this->l('Additional price for Corsica'),
            'name' => 'GLS_ADD_PRICE_CORSICA',
            'suffix' => $this->context->currency->sign,
            'class' => 'input fixed-width-sm',
            'col' => '12',
            'form_group_class' => 'col-lg-6',
            'lang' => false,
        ];
        $input[] = [
            'type' => 'text',
            'label' => $this->l('Additional price for British islands'),
            'name' => 'GLS_ADD_PRICE_GB_ISLAND',
            'suffix' => $this->context->currency->sign,
            'class' => 'input fixed-width-sm',
            'col' => '12',
            'form_group_class' => 'col-lg-6',
            'lang' => false,
        ];
        $input[] = [
            'type' => 'text',
            'label' => $this->l('Additional price for Spanish and Portuguese islands'),
            'name' => 'GLS_ADD_PRICE_SP_PT_ISLAND',
            'suffix' => $this->context->currency->sign,
            'class' => 'input fixed-width-sm',
            'col' => '12',
            'form_group_class' => 'col-lg-6',
            'lang' => false,
            'hint' => $this->l('Balearic Islands excluded.'),
        ];
        $input[] = [
            'type' => 'text',
            'label' => $this->l('Additional price for islands from other countries'),
            'name' => 'GLS_ADD_PRICE_ISLANDS',
            'suffix' => $this->context->currency->sign,
            'class' => 'input fixed-width-sm',
            'col' => '12',
            'form_group_class' => 'col-lg-6',
            'lang' => false,
        ];

        $fields_form[1] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Select a carrier for each GLS service you want to propose'),
                ],
                'input' => $input,
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $this->context->smarty->assign([
            'anchor_title' => $this->l('Click here to retrieve your Google Maps API Key'),
            'anchor_link' => 'https://developers.google.com/maps/documentation/javascript/get-api-key',
            'anchor_target' => 'target="_blank"',
        ]);
        $anchorTpl = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/anchor.tpl');

        $this->context->smarty->assign([
            'cron_uri' => $this->moduleHelper->generateCronUri(),
            'title' => $this->l('You can set a cron job that will update orders states depending on gls tracking status using the following URL:'),
            'btn_title' => $this->l('Run the automatic update now'),
        ]);
        $trackingCronTpl = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/tracking_cron.tpl');

        $accountInputs = [
            [
                'type' => 'text',
                'label' => $this->l('Webservice login'),
                'required' => true,
                'lang' => false,
                'name' => 'GLS_WSLOGIN',
                'hint' => $this->l('Information required to search and display GLS Relais'),
                'col' => '12',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Webservice password'),
                'required' => true,
                'lang' => false,
                'name' => 'GLS_WSPWD',
                'hint' => $this->l('Information required to search and display GLS Relais'),
                'col' => '12',
            ],
            [
                'type' => 'text',
                'label' => $this->l('GLS agency code'),
                'required' => true,
                'lang' => false,
                'name' => 'GLS_AGENCY_CODE',
                'hint' => $this->l('Fill in the GLS agency code from where the package is sent.'),
                'col' => '12',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Web API login'),
                'required' => false,
                'lang' => false,
                'name' => 'GLS_API_LOGIN',
                'hint' => $this->l('Information required to use labels printing and to automatically update the order\'s state depending of the gls tracking status'),
                'col' => '12',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Web API password'),
                'required' => false,
                'lang' => false,
                'name' => 'GLS_API_PWD',
                'hint' => $this->l('Information required to use labels printing and to automatically update the order\'s state depending of the gls tracking status'),
                'col' => '12',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Customer ID (Web API)'),
                'required' => false,
                'lang' => false,
                'name' => 'GLS_API_CUSTOMER_ID',
                'hint' => $this->l('Information required to use labels printing'),
                'col' => '12',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Contact ID (Web API)'),
                'required' => false,
                'lang' => false,
                'name' => 'GLS_API_CONTACT_ID',
                'hint' => $this->l('Information required to use labels printing'),
                'col' => '12',
            ],
        ];

        if (Configuration::get('GLS_CAN_USE_LEGACY_API') === '1') {
            array_unshift($accountInputs, [
                'type' => 'switch',
                'label' => $this->l('Use ShipIt API'),
                'required' => true,
                'lang' => false,
                'name' => 'GLS_IS_USING_SHIPIT_API',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ],
                ],
                'col' => '12',
            ]);
        }

        $fields_form[2] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Fill in your account details'),
                ],
                'input' => $accountInputs,
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $fields_form[3] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('GLS Relais display'),
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Use Google Maps instead of OpenStreetMap'),
                        'required' => true,
                        'lang' => false,
                        'name' => 'GLS_GOOGLE_MAPS_ENABLE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                        'hint' => $this->l('Enable this option if you have a problem to display the map with OpenStreetMap or if you have a google account well configured.'),
                        'col' => '12',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Google Maps API Key'),
                        'required' => true,
                        'lang' => false,
                        'name' => 'GLS_GOOGLE_MAPS_API_KEY',
                        'desc' => $anchorTpl,
                        'col' => '12',
                        'form_group_class' => 'google-maps',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Include Google Maps API Script'),
                        'required' => true,
                        'lang' => false,
                        'name' => 'GLS_GOOGLE_MAPS_API_SCRIPT_ENABLE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                        'hint' => $this->l('Disable this option if you have a problem to display the map.'),
                        'col' => '12',
                        'form_group_class' => 'google-maps',
                    ],
                    [
                        'type' => 'html',
                        'name' => $this->l('Export / Import options'),
                        'label' => '',
                        'col' => '12',
                        'form_group_class' => 'panel-heading',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Include an order prefix in GLS export/import'),
                        'required' => true,
                        'lang' => false,
                        'name' => 'GLS_ORDER_PREFIX_ENABLE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                        'hint' => $this->l('This option is required for multishops, it is enabled by default.'),
                        'col' => '12',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Set order prefix'),
                        'required' => false,
                        'lang' => false,
                        'name' => 'GLS_ORDER_PREFIX',
                        'desc' => $this->l('Alphanumeric characters only.'),
                        'hint' => $this->l('Leave empty to use the default prefix which is the Shop ID.'),
                        'col' => '12',
                        'form_group_class' => 'order-prefix',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Use order reference instead of ID'),
                        'required' => true,
                        'lang' => false,
                        'name' => 'GLS_EXPORT_ORDER_REFERENCE_ENABLE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                        'hint' => $this->l('Use order reference instead of the order ID on the export, for the reference displayed on label and the packing list'),
                        'col' => '12',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Customize orders export path'),
                        'required' => true,
                        'lang' => false,
                        'name' => 'GLS_CUSTOM_EXPORT_PATH_ENABLE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                        'col' => '12',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Customized orders export path'),
                        'required' => false,
                        'lang' => false,
                        'name' => 'GLS_CUSTOM_EXPORT_PATH',
                        'desc' => $this->l('Use an absolute path.'),
                        'col' => '12',
                        'form_group_class' => 'custom-export-path',
                    ],
                    [
                        'type' => 'html',
                        'name' => $this->l('Labels printing'),
                        'label' => '',
                        'col' => '12',
                        'form_group_class' => 'panel-heading',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Delivery label format'),
                        'name' => 'GLS_API_DELIVERY_LABEL_FORMAT',
                        'required' => false,
                        'options' => [
                            'query' => [
                                ['id' => 'A4', 'name' => $this->l('A4')],
                                ['id' => 'A5', 'name' => $this->l('A5')],
                                ['id' => 'A6', 'name' => $this->l('A6')],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'col' => '12',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable shop return service'),
                        'required' => false,
                        'name' => 'GLS_API_SHOP_RETURN_SERVICE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                        'col' => '12',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable shop return notification email'),
                        'required' => false,
                        'name' => 'GLS_API_SHOP_RETURN_EMAIL_ALERT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                        'hint' => $this->l('Enable this option if you want to notify the customer after return label is generated.'),
                        'col' => '12',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Use the same return address as the one configured for the shop'),
                        'required' => false,
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                        'col' => '12',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Company or return contact name'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_NAME',
                        'required' => true,
                        'col' => '12',
                        'form_group_class' => 'return-address',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Address', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_ADDRESS1',
                        'required' => true,
                        'default_value' => $address->address1,
                        'col' => '12',
                        'form_group_class' => 'return-address',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Address (2)', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_ADDRESS2',
                        'col' => '12',
                        'form_group_class' => 'return-address',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Zip/postal code', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_POSTCODE',
                        'required' => true,
                        'col' => '12',
                        'form_group_class' => 'return-address',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('City', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_CITY',
                        'required' => true,
                        'col' => '12',
                        'form_group_class' => 'return-address',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Country', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_COUNTRY',
                        'required' => true,
                        'options' => [
                            'query' => Country::getCountries($this->context->language->id),
                            'id' => 'id_country',
                            'name' => 'name',
                        ],
                        'col' => '12',
                        'form_group_class' => 'return-address last',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Name', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_NAME_DEFAULT',
                        'required' => true,
                        'disabled' => true,
                        'col' => '12',
                        'form_group_class' => 'return-address default-address',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Address', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_ADDRESS1_DEFAULT',
                        'required' => true,
                        'default_value' => $address->address1,
                        'disabled' => true,
                        'col' => '12',
                        'form_group_class' => 'return-address default-address',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Address (2)', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_ADDRESS2_DEFAULT',
                        'disabled' => true,
                        'col' => '12',
                        'form_group_class' => 'return-address default-address',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Zip/postal code', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_POSTCODE_DEFAULT',
                        'required' => true,
                        'disabled' => true,
                        'col' => '12',
                        'form_group_class' => 'return-address default-address',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('City', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_CITY_DEFAULT',
                        'required' => true,
                        'disabled' => true,
                        'col' => '12',
                        'form_group_class' => 'return-address default-address',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Country', [], 'Admin.Global'),
                        'name' => 'GLS_API_SHOP_RETURN_ADDRESS_COUNTRY_DEFAULT',
                        'required' => true,
                        'options' => [
                            'query' => Country::getCountries($this->context->language->id),
                            'id' => 'id_country',
                            'name' => 'name',
                        ],
                        'disabled' => true,
                        'col' => '12',
                        'form_group_class' => 'return-address default-address last',
                    ],
                    [
                        'type' => 'html',
                        'name' => $this->l('Tracking configuration'),
                        'label' => '',
                        'col' => '12',
                        'form_group_class' => 'panel-heading',
                    ],
                    [
                        'type' => 'html',
                        'name' => $trackingCronTpl,
                        'label' => '',
                        'col' => '12',
                    ],
                    [
                        'type' => 'html',
                        'name' => $this->l('PrestaShop order state'),
                        'label' => $this->l('GLS Tracking status'),
                        'form_group_class' => 'tracking-states tracking-states-title',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        foreach (self::$trackingStates as $key => $value) {
            array_push(
                $fields_form[3]['form']['input'],
                [
                    'type' => 'select',
                    'label' => $value,
                    'name' => 'GLS_TRACKING_API_ORDER_STATE_' . $key,
                    'required' => true,
                    'options' => [
                        'query' => OrderState::getOrderStates($this->context->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name',
                        'default' => ['value' => 0, 'label' => $this->l('None - Ignore this state')],
                    ],
                    'form_group_class' => 'tracking-states',
                ]
            );
        }

        $this->context->smarty->assign([
            'gls_logo' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/admin/gls-logo.jpg',
            'zones_link' => $this->context->link->getAdminLink('AdminZones'),
        ]);

        $fields_form[4] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Support'),
                ],
                'input' => [
                    [
                        'type' => 'html',
                        'name' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/config_faq.tpl'),
                        'label' => '',
                        'col' => '12',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable SSL V1 configuration'),
                        'required' => false,
                        'name' => 'GLS_SSL_PATCH',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                        'hint' => $this->l('Enable this option if you have a bad response error when printing a label'),
                        'col' => '12',
                    ],
                ],
            ],
        ];

        $fields_form[5] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Logs'),
                ],
            ],
        ];

        return $fields_form;
    }

    public function paginateLog($data, $page = 1, $pagination = 50)
    {
        if (count($data) > $pagination) {
            $data = array_slice($data, $pagination * ($page - 1), $pagination);
        }

        return $data;
    }

    private function initList()
    {
        $this->fields_list = [
            'message' => [
                'title' => $this->l('Message'),
                'type' => 'text',
                'filter_key' => 'message',
                'width' => '500',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'filter_key' => 'date_add',
                'type' => 'date',
                'width' => '245',
                'align' => 'right',
            ],
        ];

        $helper = new HelperList();
        $helper->module = $this;
        $helper->shopLinkType = '';
        $helper->no_link = true;
        $helper->show_toolbar = false;
        $helper->simple_header = false;
        $helper->row_hover = false;
        $helper->actions = [''];
        $helper->identifier = 'id_gls_log';
        $helper->table = 'gls_log';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        return $helper;
    }

    public function getConfigFormValues()
    {
        $address = $this->context->shop->getAddress();

        $fields_values = [
            'GLS_GLSRELAIS_XL_ONLY' => Tools::getValue('GLS_GLSRELAIS_XL_ONLY', Configuration::get('GLS_GLSRELAIS_XL_ONLY')),
            'GLS_IS_USING_SHIPIT_API' => Tools::getValue('GLS_IS_USING_SHIPIT_API', Configuration::get('GLS_IS_USING_SHIPIT_API')),
            'GLS_WSLOGIN' => Tools::getValue('GLS_WSLOGIN', Configuration::get('GLS_WSLOGIN')),
            'GLS_WSPWD' => Tools::getValue('GLS_WSPWD', Configuration::get('GLS_WSPWD')),
            'GLS_AGENCY_CODE' => Tools::getValue('GLS_AGENCY_CODE', Configuration::get('GLS_AGENCY_CODE')),
            'GLS_GOOGLE_MAPS_API_KEY' => Tools::getValue('GLS_GOOGLE_MAPS_API_KEY', Configuration::get('GLS_GOOGLE_MAPS_API_KEY')),
            'GLS_GOOGLE_MAPS_API_SCRIPT_ENABLE' => Tools::getValue('GLS_GOOGLE_MAPS_API_SCRIPT_ENABLE', Configuration::get('GLS_GOOGLE_MAPS_API_SCRIPT_ENABLE')),
            'GLS_ORDER_PREFIX_ENABLE' => Tools::getValue('GLS_ORDER_PREFIX_ENABLE', Configuration::get('GLS_ORDER_PREFIX_ENABLE')),
            'GLS_ORDER_PREFIX' => Tools::getValue('GLS_ORDER_PREFIX', Configuration::get('GLS_ORDER_PREFIX')),
            'GLS_API_CUSTOMER_ID' => Tools::getValue('GLS_API_CUSTOMER_ID', Configuration::get('GLS_API_CUSTOMER_ID')),
            'GLS_API_CONTACT_ID' => Tools::getValue('GLS_API_CONTACT_ID', Configuration::get('GLS_API_CONTACT_ID')),
            'GLS_API_DELIVERY_LABEL_FORMAT' => Tools::getValue('GLS_API_DELIVERY_LABEL_FORMAT', Configuration::get('GLS_API_DELIVERY_LABEL_FORMAT')),
            'GLS_API_SHOP_RETURN_SERVICE' => Tools::getValue('GLS_API_SHOP_RETURN_SERVICE', Configuration::get('GLS_API_SHOP_RETURN_SERVICE')),
            'GLS_API_SHOP_RETURN_EMAIL_ALERT' => Tools::getValue('GLS_API_SHOP_RETURN_EMAIL_ALERT', Configuration::get('GLS_API_SHOP_RETURN_EMAIL_ALERT')),
            'GLS_API_LOGIN' => Tools::getValue('GLS_API_LOGIN', Configuration::get('GLS_API_LOGIN')),
            'GLS_API_PWD' => Tools::getValue('GLS_API_PWD', Configuration::get('GLS_API_PWD')),
            'GLS_API_SHOP_RETURN_ADDRESS' => Tools::getValue('GLS_API_SHOP_RETURN_ADDRESS', Configuration::get('GLS_API_SHOP_RETURN_ADDRESS')),
            'GLS_API_SHOP_RETURN_ADDRESS_NAME' => Tools::getValue('GLS_API_SHOP_RETURN_ADDRESS_NAME', Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_NAME', null, null, null, Configuration::get('PS_SHOP_NAME'))),
            'GLS_API_SHOP_RETURN_ADDRESS_ADDRESS1' => Tools::getValue('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS1', Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS1', null, null, null, $address->address1)),
            'GLS_API_SHOP_RETURN_ADDRESS_ADDRESS2' => Tools::getValue('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS2', Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS2', null, null, null, $address->address2)),
            'GLS_API_SHOP_RETURN_ADDRESS_POSTCODE' => Tools::getValue('GLS_API_SHOP_RETURN_ADDRESS_POSTCODE', Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_POSTCODE', null, null, null, $address->postcode)),
            'GLS_API_SHOP_RETURN_ADDRESS_CITY' => Tools::getValue('GLS_API_SHOP_RETURN_ADDRESS_CITY', Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_CITY', null, null, null, $address->city)),
            'GLS_API_SHOP_RETURN_ADDRESS_COUNTRY' => Tools::getValue('GLS_API_SHOP_RETURN_ADDRESS_COUNTRY', Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_COUNTRY', null, null, null, (int) $address->id_country)),
            'GLS_API_SHOP_RETURN_ADDRESS' => Tools::getValue('GLS_API_SHOP_RETURN_ADDRESS', Configuration::get('GLS_API_SHOP_RETURN_ADDRESS')),
            'GLS_API_SHOP_RETURN_ADDRESS_NAME_DEFAULT' => Configuration::get('PS_SHOP_NAME'),
            'GLS_API_SHOP_RETURN_ADDRESS_ADDRESS1_DEFAULT' => $address->address1,
            'GLS_API_SHOP_RETURN_ADDRESS_ADDRESS2_DEFAULT' => $address->address2,
            'GLS_API_SHOP_RETURN_ADDRESS_POSTCODE_DEFAULT' => $address->postcode,
            'GLS_API_SHOP_RETURN_ADDRESS_CITY_DEFAULT' => $address->city,
            'GLS_API_SHOP_RETURN_ADDRESS_COUNTRY_DEFAULT' => (int) $address->id_country,
            'GLS_ADD_PRICE_MOUNTAIN' => Tools::getValue('GLS_ADD_PRICE_MOUNTAIN', Configuration::get('GLS_ADD_PRICE_MOUNTAIN')),
            'GLS_ADD_PRICE_FR_ISLAND' => Tools::getValue('GLS_ADD_PRICE_FR_ISLAND', Configuration::get('GLS_ADD_PRICE_FR_ISLAND')),
            'GLS_ADD_PRICE_CORSICA' => Tools::getValue('GLS_ADD_PRICE_CORSICA', Configuration::get('GLS_ADD_PRICE_CORSICA')),
            'GLS_ADD_PRICE_GB_ISLAND' => Tools::getValue('GLS_ADD_PRICE_GB_ISLAND', Configuration::get('GLS_ADD_PRICE_GB_ISLAND')),
            'GLS_ADD_PRICE_SP_PT_ISLAND' => Tools::getValue('GLS_ADD_PRICE_SP_PT_ISLAND', Configuration::get('GLS_ADD_PRICE_SP_PT_ISLAND')),
            'GLS_ADD_PRICE_ISLANDS' => Tools::getValue('GLS_ADD_PRICE_ISLANDS', Configuration::get('GLS_ADD_PRICE_ISLANDS')),
            'GLS_CUSTOM_EXPORT_PATH_ENABLE' => Tools::getValue('GLS_CUSTOM_EXPORT_PATH_ENABLE', Configuration::get('GLS_CUSTOM_EXPORT_PATH_ENABLE')),
            'GLS_CUSTOM_EXPORT_PATH' => Tools::getValue('GLS_CUSTOM_EXPORT_PATH', Configuration::get('GLS_CUSTOM_EXPORT_PATH')),
            'GLS_EXPORT_ORDER_REFERENCE_ENABLE' => Tools::getValue('GLS_EXPORT_ORDER_REFERENCE_ENABLE', Configuration::get('GLS_EXPORT_ORDER_REFERENCE_ENABLE')),
            'GLS_GOOGLE_MAPS_ENABLE' => Tools::getValue('GLS_GOOGLE_MAPS_ENABLE', Configuration::get('GLS_GOOGLE_MAPS_ENABLE')),
            'GLS_ADD_PRICE_FREE_CARRIER_ENABLE' => Tools::getValue('GLS_ADD_PRICE_FREE_CARRIER_ENABLE', Configuration::get('GLS_ADD_PRICE_FREE_CARRIER_ENABLE')),
            'GLS_SSL_PATCH' => Tools::getValue('GLS_SSL_PATCH', Configuration::get('GLS_SSL_PATCH')),
        ];

        foreach (array_keys(self::$carrier_definition) as $key) {
            $fields_values['GLS_' . $key . '_ID'] = Tools::getValue('GLS_' . $key . '_ID', Configuration::get('GLS_' . $key . '_ID'));
        }

        foreach (self::$trackingStates as $key => $value) {
            $fields_values['GLS_TRACKING_API_ORDER_STATE_' . $key] = Tools::getValue(
                'GLS_TRACKING_API_ORDER_STATE_' . $key,
                Configuration::get('GLS_TRACKING_API_ORDER_STATE_' . $key)
            );
        }

        return $fields_values;
    }

    protected function postProcess()
    {
        $output = null;
        $output_info = '';

        $form_values = $this->getConfigFormValues();

        if ($form_values['GLS_GLS13H_ID']) {
            if (!$form_values['GLS_AGENCY_CODE']) {
                $output .= $this->displayError($this->l('Your GLS agency code is necessary to propose the service GLS before 13H.'));
            }
        }

        if (
            ($form_values['GLS_GLSRELAIS_ID'] && !$form_values['GLS_IS_USING_SHIPIT_API'] && (!$form_values['GLS_WSLOGIN'] || !$form_values['GLS_WSPWD'])) ||
            ($form_values['GLS_IS_USING_SHIPIT_API'] && (!$form_values['GLS_API_LOGIN'] || !$form_values['GLS_API_PWD']))
        ) {
            $output .= $this->displayError($this->l('Your webservice login and password are necessary to propose the service GLS Relais.'));
        } else {
            @ini_set('default_socket_timeout', '15');

            $gls = GlsController::createInstance($form_values);
            $result = $gls->checkAuth();

            @ini_restore('default_socket_timeout');

            if (!$result) {
                $output_info .= $this->displayWarning($this->l('GLS WebService temporarily unavailable.'));
            } elseif ($result && isset($result->exitCode->ErrorCode) && $result->exitCode->ErrorCode == 502) {
                $output .= $this->displayError($this->l('Incorrect GLS webservice login and/or password.'));
            }
        }

        $this->context->smarty->assign([
            'anchor_title' => $this->l('Click here to get an API Key'),
            'anchor_link' => 'https://developers.google.com/maps/documentation/javascript/get-api-key',
            'anchor_target' => 'target="_blank"',
        ]);

        if (
            $form_values['GLS_GLSRELAIS_ID'] &&
            $form_values['GLS_GOOGLE_MAPS_ENABLE'] &&
            !$form_values['GLS_GOOGLE_MAPS_API_KEY'] &&
            $form_values['GLS_GOOGLE_MAPS_API_SCRIPT_ENABLE']
        ) {
            $output .= $this->displayError($this->l('A Google Maps API Key is necessary to locate GLS Relais using Google Maps.')
                . Tools::nl2br("\n") . $this->context->smarty->fetch($this->local_path . 'views/templates/admin/anchor.tpl'));
        }

        $same_carrier_tmp = [];
        foreach (self::$carrier_definition as $key => $value) {
            if (in_array($form_values['GLS_' . $key . '_ID'], $same_carrier_tmp) && $form_values['GLS_' . $key . '_ID'] != '0') {
                $output .= $this->displayError(sprintf($this->l('An error occured on "%s", you have to select different carriers by services.'), $value['name']));
                break;
            } else {
                $same_carrier_tmp[] = $form_values['GLS_' . $key . '_ID'];
            }
        }

        if (!$output) {
            foreach ($form_values as $key => $value) {
                switch ($key) {
                    case 'GLS_GLSRELAIS_ID':
                    case 'GLS_GLS13H_ID':
                    case 'GLS_GLSCHEZVOUS_ID':
                    case 'GLS_GLSCHEZVOUSPLUS_ID':
                        $old_carrier_id = Configuration::get($key);

                        if (is_numeric($value) && (int) $value > 0 && (int) $value != (int) $old_carrier_id) {
                            $this->defineCarrierAsModule((int) $value);

                            if (Validate::isLoadedObject($object = new Carrier((int) $value))) {
                                $object->active = true;
                                $object->update();
                            }
                        }

                        if ((int) $old_carrier_id > 0 && (int) $value != (int) $old_carrier_id) {
                            if (Validate::isLoadedObject($object = new Carrier((int) $old_carrier_id))) {
                                $object->active = false;
                                $object->update();
                            }

                            $log_key = Tools::strReplaceFirst('_ID', '_LOG', $key);
                            $history_log = explode('|', Configuration::get($log_key));
                            $history_log[] = $old_carrier_id;
                            Configuration::updateValue($log_key, implode('|', array_map('intval', $history_log)));
                        }

                        Configuration::updateValue($key, trim($value));
                        break;
                    case 'GLS_ADD_PRICE_MOUNTAIN':
                    case 'GLS_ADD_PRICE_FR_ISLAND':
                    case 'GLS_ADD_PRICE_CORSICA':
                    case 'GLS_ADD_PRICE_GB_ISLAND':
                    case 'GLS_ADD_PRICE_SP_PT_ISLAND':
                    case 'GLS_ADD_PRICE_ISLANDS':
                        $v = trim($value);
                        if (empty($v)) {
                            $v = 0;
                        }

                        if (is_numeric($v) && (float) $v >= 0) {
                            Configuration::updateValue($key, $v);
                        } else {
                            $output .= $this->displayError($this->l('Invalid additional price, use only number greater than or equal to 0.'));
                        }
                        break;
                    case 'GLS_ORDER_PREFIX':
                        $v = trim($value);
                        if (!empty($v) && !preg_match('/^[[:alnum:]]+$/', $v)) {
                            $output .= $this->displayError($this->l('Order prefix invalid, use alphanumeric characters only.'));
                        } else {
                            Configuration::updateValue($key, $v);
                        }
                        break;
                    default:
                        Configuration::updateValue($key, trim($value));
                        break;
                }
            }

            if (!$output) {
                $output = $this->displayConfirmation($this->l('Settings updated')) . $output_info;
            }
        }

        return $output;
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == $this->name) {
            if (method_exists($this->context->controller, 'addJquery')) {
                $this->context->controller->addJquery();
            }
            $this->context->controller->addCSS($this->_path . '/views/css/admin.css');
            $this->context->controller->addJS($this->_path . '/views/js/admin.js');
        } elseif ($this->getPrestaShopVersion() === '1.7' && Tools::getValue('controller') == 'AdminOrders') {
            $this->includeAdminOrderAssets();
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == $this->name) {
            if (method_exists($this->context->controller, 'addJquery')) {
                $this->context->controller->addJquery();
            }
            $this->context->controller->addCSS($this->_path . '/views/css/admin.css');
            $this->context->controller->addJS($this->_path . '/views/js/admin.js');
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        if ($this->getPrestaShopVersion() !== '1.7') {
            $this->includeAdminOrderAssets();
        }
        $vars = $this->displayInfoByCart(null, (int) $params['id_order'], true);
        if (!empty($vars)) {
            $this->context->smarty->assign($vars);

            return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/' . $this->templates['displayAdminOrder']);
        }
    }

    public function includeAdminOrderAssets()
    {
        $module_admin_link = $this->context->link->getAdminLink('AdminGlsAjax', false) . '&ajax=1&token=' . Tools::getAdminTokenLite('AdminGlsAjax');

        Media::addJsDef([
            'gls_ajax_change_relay_point_url' => $module_admin_link . '&action=changeRelayPoint',
            'gls_ajax_search_relay_url' => $module_admin_link . '&action=searchRelay',
            'gls_js_general_error' => $this->l('Unexpected error occured.'),
            'gls_js_relay_error' => $this->l('Please select a GLS Relais on the list.'),
            'gls_js_search_error' => $this->l('Please fill-in a valid postcode.'),
            'gls_js_mobile_error' => $this->l('Please fill-in a valid mobile number (e.g. +XXXXXXXXXXX or 0XXXXXXXXX).'),
            'gls_js_update_success' => $this->l('Successfully updated'),
            'google_maps_enable' => (bool) Configuration::get('GLS_GOOGLE_MAPS_ENABLE'),
            'gls_marker_path' => $this->context->shop->getBaseURL(true, false) . $this->_path . 'views/img/front/',
        ]);

        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJquery();
        }

        if (!Configuration::get('GLS_GOOGLE_MAPS_ENABLE')) {
            $this->context->controller->addCSS(
                'https://unpkg.com/leaflet/dist/leaflet.css'
            );
            $this->context->controller->addJS(
                'https://unpkg.com/leaflet/dist/leaflet.js'
            );
        } else {
            $this->context->controller->addJS(
                'https://maps.googleapis.com/maps/api/js?key=' . Configuration::get('GLS_GOOGLE_MAPS_API_KEY')
            );
        }

        $this->context->controller->addCSS($this->_path . '/views/css/admin-orderview.css');
        $this->context->controller->addJS($this->_path . '/views/js/admin-orderview.js');
    }

    public function renderWidget($hookName, array $_params)
    {
        switch ($hookName) {
            case 'displayHeader':
                $this->hookDisplayHeader();
                break;
            case 'displayCarrierExtraContent':
                return $this->hookDisplayCarrierExtraContent($_params);
            case 'displayAdminOrderLeft':
            case 'displayAdminOrderMain':
            case 'displayAdminOrderMainBottom':
                $statuses = [];
                foreach (OrderState::getOrderStates($this->context->language->id) as $value) {
                    $statuses[] = ['id_option' => $value['id_order_state'], 'name' => $value['name']];
                }

                $result = $this->getWidgetVariables($hookName, $_params);

                $result['order_status'] = $statuses;
                $result['order_status_selected'] = (int) Configuration::get('GLS_LABEL_SINGLE_NEW_ORDER_STATE');
                $this->smarty->assign($result);

                return $this->display(__FILE__, $this->templates['displayAdminOrderLeft']);
            default:
                break;
        }
    }

    public function getWidgetVariables($hookName, array $params)
    {
        switch ($hookName) {
            case 'displayAdminOrderLeft':
            case 'displayAdminOrderMain':
            case 'displayAdminOrderMainBottom':
                $order = new Order((int) $params['id_order']);
                $carrier = new Carrier((int) $order->id_carrier);
                $orderIsGls = ($carrier->external_module_name == 'nkmgls');

                $trackingState = false;
                $current_state = '';
                if ($orderIsGls) {
                    $trackingState = GlsTrackingStateClass::getByIdOrder((int) $params['id_order']);
                    if ($trackingState) {
                        $current_state = self::$trackingStates[$trackingState['current_state']];
                    }
                }

                return [
                    'link' => $this->context->link->getAdminLink('AdminGlsLabel'),
                    'link_tracking' => $this->context->link->getModuleLink($this->name, 'tracking', ['ajax' => 1, 'action' => 'updateTrackingState'], null, null, Configuration::get('PS_SHOP_DEFAULT')),
                    'gls_logo' => $this->context->shop->getBaseURL(true, false) . $this->_path . 'views/img/admin/gls-logo.jpg',
                    'id_order' => (int) $params['id_order'],
                    'trackingState' => $trackingState,
                    'current_state' => $current_state,
                    'is_gls' => $orderIsGls,
                ];
            default:
                break;
        }
    }

    public function createCarrier($_code, $_id_zone_france = null)
    {
        if (!array_key_exists($_code, self::$carrier_definition)) {
            $this->_errors[] = $this->l('GLS carrier code is wrong.');

            return false;
        }

        if (Shop::isFeatureActive() && !empty(self::$old_shop_context)) {
            Shop::setContext(self::$old_shop_context['type'], self::$old_shop_context['id']);
        }

        $carrier = new Carrier();
        $carrier->name = self::$carrier_definition[$_code]['name'];
        $carrier->id_tax_rules_group = 0;
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->url = self::$trackingUrl;
        $carrier->delay = self::$carrier_definition[$_code]['delay'];
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = true;

        $carrier->need_range = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'nkmgls';
        $carrier->grade = self::$carrier_definition[$_code]['grade'];

        foreach (Language::getLanguages(true) as $language) {
            if (array_key_exists($language['iso_code'], self::$carrier_definition[$_code]['delay'])) {
                $carrier->delay[$language['id_lang']] = self::$carrier_definition[$_code]['delay'][$language['iso_code']];
            } else {
                $carrier->delay[$language['id_lang']] = self::$carrier_definition[$_code]['delay']['fr'];
            }
        }

        if ($carrier->add()) {
            $groups = Group::getgroups(true);
            $tmp_groups = [];
            foreach ($groups as $value) {
                $tmp_groups[] = $value['id_group'];
            }
            $carrier->setGroups($tmp_groups, false);

            switch ($_code) {
                case 'GLSRELAIS':
                case 'GLSCHEZVOUSPLUS':
                case 'GLS13H':
                    if (is_null($_id_zone_france)) {
                        $sql = new DbQuery();
                        $sql->select('z.`id_zone`')
                            ->from('zone', 'z')
                            ->where('z.`name` LIKE \'%France%\'');
                        foreach (Db::getInstance()->ExecuteS($sql) as $value) {
                            $carrier->addZone((int) $value['id_zone']);
                        }
                    } else {
                        $carrier->addZone((int) $_id_zone_france);
                    }

                    break;
                default:
                    $zones = Zone::getZones();
                    foreach ($zones as $zone) {
                        $carrier->addZone($zone['id_zone']);
                    }
                    break;
            }

            if (!copy(dirname(__FILE__) . '/views/img/admin/' . Tools::strtolower($_code) . '.jpg', _PS_SHIP_IMG_DIR_ . '/' . $carrier->id . '.jpg')) {
                $this->_errors[] = sprintf($this->l('Error to copy GLS carrier logo %s'), $_code);
            }
        } else {
            $this->_errors[] = sprintf($this->l('Error to create GLS carrier %s'), $_code);

            return false;
        }

        $old_id_carrier = Configuration::get('GLS_' . Tools::strtoupper($_code) . '_ID');
        if ($old_id_carrier) {
            $history_log = explode('|', Configuration::get('GLS_' . Tools::strtoupper($_code) . '_LOG'));
            $history_log[] = $old_id_carrier;
            Configuration::updateValue('GLS_' . Tools::strtoupper($_code) . '_LOG', implode('|', array_map('intval', $history_log)));

            if (Validate::isLoadedObject($object = new Carrier((int) $old_id_carrier))) {
                $object->active = false;
                $object->update();
            }
        }

        return Configuration::updateValue('GLS_' . Tools::strtoupper($_code) . '_ID', (int) $carrier->id);
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        $cart = $params;
        if (
            !($cart instanceof Cart) ||
            !Validate::isLoadedObject($cart)
        ) {
            if (!Validate::isLoadedObject($this->context->cart)) {
                return $shipping_cost;
            }

            $cart = $this->context->cart;
        }

        if ((int) $this->id_carrier == (int) Configuration::get('GLS_GLS13H_ID')) {
            if (Configuration::get('GLS_AGENCY_CODE')) {
                $this->synchronizeAgencyPostcodeRestriction();

                $address = new Address((int) $cart->id_address_delivery);

                $query = new DbQuery();
                $query->select('g.*')
                    ->from('gls_agency_postcode', 'g')
                    ->where('g.`agency_code` = \'' . pSQL(Configuration::get('GLS_AGENCY_CODE')) . '\'')
                    ->where('\'' . pSQL($address->postcode) . '\' >= `postcode_start`')
                    ->where('\'' . pSQL($address->postcode) . '\' <= `postcode_end`');
                if (!Db::getInstance()->getRow($query)) {
                    return false;
                }
            } else {
                return false;
            }
        } elseif ((int) $this->id_carrier == (int) Configuration::get('GLS_GLSRELAIS_ID')) {
            if (
                (!Configuration::get('GLS_IS_USING_SHIPIT_API') && (!Configuration::get('GLS_WSLOGIN') || !Configuration::get('GLS_WSPWD'))) ||
                (Configuration::get('GLS_IS_USING_SHIPIT_API') && (!Configuration::get('GLS_API_LOGIN') || !Configuration::get('GLS_API_PWD')))
            ) {
                return false;
            }
        }

        if ((int) $this->id_carrier == (int) Configuration::get('GLS_GLSRELAIS_ID')
            || (int) $this->id_carrier == (int) Configuration::get('GLS_GLS13H_ID')
            || (int) $this->id_carrier == (int) Configuration::get('GLS_GLSCHEZVOUS_ID')
            || (int) $this->id_carrier == (int) Configuration::get('GLS_GLSCHEZVOUSPLUS_ID')) {
            $address = new Address((int) $cart->id_address_delivery);
            $country_iso = Country::getIsoById($address->id_country);

            $applyFreeCarrier = Configuration::get('GLS_ADD_PRICE_FREE_CARRIER_ENABLE', null, null, null, 1);
            if ($applyFreeCarrier || (!$applyFreeCarrier && $shipping_cost > 0)) {
                $extra_price = $this->getAdditionalPrice($country_iso, $address->postcode);
                if (is_numeric($extra_price) && (float) $extra_price > 0) {
                    $shipping_cost += (float) $extra_price;
                }
            }
        }

        $psAddress = new Address((int) $cart->id_address_delivery);
        $psCarrier = new Carrier($this->id_carrier);
        $adaptedAddress = $this->addressHandler->adapt($psAddress);
        $adaptedCarrier = $this->carrierHandler->adapt($psCarrier);

        if (!$this->allowedServicesRoutine->isAllowedCarrier($adaptedCarrier, $adaptedAddress)) {
            return false;
        }

        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return $params;
    }

    public function hookDisplayCarrierExtraContent($_params)
    {
        if (
            isset($_params['carrier']) ||
            (isset($_params['fromDisplayInfoByCart']) && $_params['fromDisplayInfoByCart'])
        ) {
            $cart_carrier_detail = false;
            $config = $this->getConfigFormValues();

            $isGlsRelais = ($_params['carrier']['id'] == $config['GLS_GLSRELAIS_ID']);
            $isGlsChezVous = ($_params['carrier']['id'] == $config['GLS_GLSCHEZVOUS_ID']);
            $isGlsChezVousPlus = ($_params['carrier']['id'] == $config['GLS_GLSCHEZVOUSPLUS_ID']);
            $isGlsAvant13h = ($_params['carrier']['id'] == $config['GLS_GLS13H_ID']);

            if ($isGlsChezVous) {
                return '';
            }

            if (isset($_params['fromDisplayInfoByCart']) && $_params['fromDisplayInfoByCart']) {
                $cart_carrier_detail = $_params['cart_carrier_detail'];
                $address = $_params['forceAddressDelivery'];
                $isGlsRelais = $this->carrierIsGLSRelais((int) $_params['carrier']['id']);
            }

            $forceGsm = (
                $isGlsRelais ||
                $isGlsChezVousPlus ||
                $isGlsAvant13h
            );

            if (empty($cart_carrier_detail)) {
                $cart_carrier_detail = self::getCartCarrierDetail(
                    $_params['cart']->id,
                    $_params['cart']->id_customer,
                    $_params['carrier']['id']
                );
            }

            $current_customer_address = null;

            if (
                isset($_params['cart']->id_address_delivery) &&
                (int) $_params['cart']->id_address_delivery > 0
            ) {
                $current_customer_address = new Address((int) $_params['cart']->id_address_delivery);
                $current_customer_address = $current_customer_address->getFields();

                if (
                    empty($current_customer_address['phone_mobile']) &&
                    !empty($current_customer_address['phone'])
                ) {
                    $current_customer_address['phone_mobile'] = $current_customer_address['phone'];
                }
            }

            $templateParams = [
                'force_gsm' => $forceGsm,
                'is_relay_carrier' => $isGlsRelais,
                'name_carrier' => $_params['carrier']['name'],
                'id_carrier' => $_params['carrier']['id'],
                'current_customer_address' => $current_customer_address,
                'current_customer_mobile' => ($cart_carrier_detail && !empty($cart_carrier_detail['customer_phone_mobile']))
                    ? $cart_carrier_detail['customer_phone_mobile']
                    : $current_customer_address['phone_mobile'],
                'customer_mobile_title' => '',
            ];

            if ($isGlsRelais) {
                $templateParams['customer_mobile_title'] = $this->l('Please fill in your mobile number, you will be notified by sms for the delivery in Relais GLS');
            }

            if ($isGlsChezVousPlus) {
                $templateParams['customer_mobile_title'] = $this->l('Please fill in your mobile number, you will be notified by sms for the FLEX delivery');
            }

            if ($isGlsAvant13h) {
                $templateParams['customer_mobile_title'] = $this->l('Please fill in your mobile number to make use of');
                $templateParams['customer_mobile_title'] .= ' ' . $templateParams['name_carrier'];
            }

            if (isset($_params['fromDisplayInfoByCart']) && $_params['fromDisplayInfoByCart']) {
                return $templateParams;
            }

            $this->smarty->assign($templateParams);

            return $this->fetch('module:' . $this->name . '/views/templates/hook/' . $this->templates['displayCarrierExtraContent']);
        }
    }

    public function hookDisplayHeader()
    {
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        if (isset($this->context->controller->page_name) && Tools::strpos($this->context->controller->page_name, 'checkout') !== false) {
            $module_link = $this->context->link->getModuleLink($this->name, 'checkout', [], true);
            if (strpos($module_link, '?') !== false) {
                $module_link .= '&';
            } else {
                $module_link .= '?';
            }
            Media::addJsDef([
                'gls_ajax_save_phone_mobile_url' => $module_link . 'ajax=1&action=savePhoneMobile',
                'gls_ajax_select_relay_point_url' => $module_link . 'ajax=1&action=selectRelayPoint',
                'gls_ajax_search_relay_url' => $module_link . 'ajax=1&action=searchRelay',
                'glsrelais_carrier_id' => (int) Configuration::get('GLS_GLSRELAIS_ID'),
                'gls13h_carrier_id' => (int) Configuration::get('GLS_GLS13H_ID'),
                'glschezvousplus_carrier_id' => (int) Configuration::get('GLS_GLSCHEZVOUSPLUS_ID'),
                'gls_js_general_error' => $this->l('Unexpected error occured.'),
                'gls_js_relay_error' => $this->l('Please select a GLS Relais on the list.'),
                'gls_js_search_error' => $this->l('Please fill-in a valid postcode.'),
                'gls_js_mobile_error' => $this->l('Please fill-in a valid mobile number (e.g. +XXXXXXXXXXX or 0XXXXXXXXX).'),
                'google_maps_enable' => (bool) Configuration::get('GLS_GOOGLE_MAPS_ENABLE'),
                'gls_marker_path' => $this->context->shop->getBaseURL(true, false) . $this->_path . 'views/img/front/',
            ]);

            if (!Configuration::get('GLS_GOOGLE_MAPS_ENABLE')) {
                $this->context->controller->registerStylesheet(
                    'module-gls-openstreetmap',
                    'https://unpkg.com/leaflet/dist/leaflet.css',
                    ['server' => 'remote']
                );
                $this->context->controller->registerJavascript(
                    'module-gls-openstreetmap',
                    'https://unpkg.com/leaflet/dist/leaflet.js',
                    ['server' => 'remote']
                );
            } elseif (Configuration::get('GLS_GOOGLE_MAPS_API_SCRIPT_ENABLE')) {
                $this->context->controller->registerJavascript(
                    'module-gls-googlemaps',
                    'https://maps.googleapis.com/maps/api/js?key=' . Configuration::get('GLS_GOOGLE_MAPS_API_KEY'),
                    ['server' => 'remote', 'attributes' => ['async', 'defer']]
                );
            }

            $this->context->controller->registerJavascript(
                'module-gls-frontjs',
                'modules/' . $this->name . '/views/js/front.js',
                ['position' => 'bottom', 'priority' => 100]
            );
        }

        $this->context->controller->registerStylesheet(
            'module-gls-frontcss',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all']
        );
    }

    public function hookActionCarrierUpdate($params)
    {
        foreach (array_keys(self::$carrier_definition) as $key) {
            if (Shop::isFeatureActive()) {
                foreach (Shop::getShops(true) as $shop) {
                    if ((int) $params['id_carrier'] == (int) Configuration::get('GLS_' . $key . '_ID', null, $shop['id_shop_group'], $shop['id_shop'])) {
                        Configuration::updateValue('GLS_' . $key . '_ID', (int) $params['carrier']->id, false, $shop['id_shop_group'], $shop['id_shop']);
                        $history_log = explode('|', Configuration::get('GLS_' . $key . '_LOG', null, $shop['id_shop_group'], $shop['id_shop']));
                        $history_log[] = $params['id_carrier'];
                        Configuration::updateValue('GLS_' . $key . '_LOG', implode('|', array_map('intval', $history_log)), false, $shop['id_shop_group'], $shop['id_shop']);
                    }
                }
            } elseif ((int) $params['id_carrier'] == (int) Configuration::get('GLS_' . $key . '_ID')) {
                Configuration::updateValue('GLS_' . $key . '_ID', (int) $params['carrier']->id);
                $history_log = explode('|', Configuration::get('GLS_' . $key . '_LOG'));
                $history_log[] = $params['id_carrier'];
                Configuration::updateValue('GLS_' . $key . '_LOG', implode('|', array_map('intval', $history_log)));
            }
        }
    }

    public function hookActionObjectCarrierUpdateAfter($params)
    {
        if (Validate::isLoadedObject($params['object']) && $params['object']->deleted) {
            if (!Carrier::getCarrierByReference($params['object']->id_reference)) {
                foreach (array_keys(self::$carrier_definition) as $key) {
                    if (Shop::isFeatureActive()) {
                        foreach (Shop::getShops(true) as $shop) {
                            if ((int) $params['object']->id == (int) Configuration::get('GLS_' . $key . '_ID', null, $shop['id_shop_group'], $shop['id_shop'])) {
                                Configuration::updateValue('GLS_' . $key . '_ID', '', false, $shop['id_shop_group'], $shop['id_shop']);
                                $history_log = explode('|', Configuration::get('GLS_' . $key . '_LOG', null, $shop['id_shop_group'], $shop['id_shop']));
                                $history_log[] = $params['object']->id;
                                Configuration::updateValue('GLS_' . $key . '_LOG', implode('|', array_map('intval', $history_log)), false, $shop['id_shop_group'], $shop['id_shop']);
                            }
                        }
                    } elseif ((int) $params['object']->id == (int) Configuration::get('GLS_' . $key . '_ID')) {
                        Configuration::updateValue('GLS_' . $key . '_ID', '');
                        $history_log = explode('|', Configuration::get('GLS_' . $key . '_LOG'));
                        $history_log[] = $params['object']->id;
                        Configuration::updateValue('GLS_' . $key . '_LOG', implode('|', array_map('intval', $history_log)));
                    }
                }
            }
        }
    }

    public function defineCarrierAsModule($id_carrier)
    {
        $sql = new DbQuery();
        $sql->select('external_module_name')
            ->from('carrier')
            ->where('id_carrier = ' . (int) $id_carrier);
        $external_module = Db::getInstance()->getValue($sql);

        if ($external_module !== false && $external_module != 'nkmgls') {
            Db::getInstance()->execute('
            UPDATE ' . _DB_PREFIX_ . 'carrier
            SET shipping_handling = 0,
                is_module = 1,
                shipping_external = 1,
                need_range = 1,
                external_module_name = \'nkmgls\'
            WHERE  id_carrier = ' . (int) $id_carrier);
        }
    }

    public function hookDisplayOrderDetail($params)
    {
    }

    public static function getCartCarrierDetail($_id_cart, $_id_customer, $_id_carrier = null)
    {
        $query = new DbQuery();
        $query->select('c.*')
            ->from('gls_cart_carrier', 'c')
            ->where('c.`id_customer` = ' . (int) $_id_customer)
            ->where('c.`id_cart` = ' . (int) $_id_cart);

        if (!empty($_id_carrier)) {
            $query->where('c.`id_carrier` = ' . (int) $_id_carrier);
        }

        return Db::getInstance()->getRow($query);
    }

    public function hookDisplayAfterCarrier()
    {
        return $this->fetch('module:' . $this->name . '/views/templates/hook/' . $this->templates['displayAfterCarrier']);
    }

    public function hookActionValidateStepComplete($params)
    {
        if ($params['step_name'] == 'delivery') {
            if (isset($params['request_params']['delivery_option'])) {
                $id_carrier_selected = (int) reset($params['request_params']['delivery_option']);

                if ($id_carrier_selected == (int) Configuration::get('GLS_GLSRELAIS_ID')
                    || $id_carrier_selected == (int) Configuration::get('GLS_GLS13H_ID')
                    || $id_carrier_selected == (int) Configuration::get('GLS_GLSCHEZVOUSPLUS_ID')
                ) {
                    if (!isset($params['request_params']['gls_customer_mobile_' . $id_carrier_selected])
                        || (isset($params['request_params']['gls_customer_mobile_' . $id_carrier_selected])
                        && empty($params['request_params']['gls_customer_mobile_' . $id_carrier_selected]))
                    ) {
                        $params['completed'] &= false;
                    }

                    if ($id_carrier_selected == (int) Configuration::get('GLS_GLSRELAIS_ID')) {
                        if (!self::getCartCarrierDetail($params['cookie']->id_cart, $params['cookie']->id_customer, $id_carrier_selected)) {
                            $params['completed'] &= false;
                        }
                    }
                }
            }
        }

        return $params['completed'];
    }

    public function hookActionCarrierProcess($params)
    {
        if (!empty($params['cart'])) {
            $cart_detail = self::getCartCarrierDetail((int) $params['cart']->id, (int) $params['cart']->id_customer);

            if ($cart_detail && (int) $cart_detail['id_carrier'] != (int) $params['cart']->id_carrier) {
                Db::getInstance()->delete('gls_cart_carrier', 'id_cart = "' . pSQL((int) $params['cart']->id) . '"');
            }
        }
    }

    public function synchronizeAgencyPostcodeRestriction($_force = false, $_file_content = null)
    {
        if (Tools::getValue('ajax') != '1') {
            $toSynchronize = false;
            $last_update_date = Configuration::get('GLS_LAST_SYNCHRO_DATE', '');

            $last_update = new DateTime($last_update_date);
            $now = new DateTime();

            if (extension_loaded('ftp') && (empty($last_update_date) || $last_update->format('Ymd') < $now->format('Ymd'))) {
                $filename = 'tbzipdeltimes_' . date('Ymd') . '.csv';
                $handle = fopen($this->importDirectory . $filename, 'w');

                $conn_id = ftp_connect(self::$ftp_host, 21, 5);
                if ($conn_id === false) {
                    return;
                }
                $login_result = ftp_login($conn_id, self::$ftp_login, self::$ftp_pwd);

                if ($login_result) {
                    $files = ftp_nlist($conn_id, '.');
                    if (empty($files)) {
                        ftp_pasv($conn_id, true);
                        $files = ftp_nlist($conn_id, '.');
                    }

                    if (is_array($files) && !empty($files)) {
                        Configuration::updateValue('GLS_LAST_SYNCHRO_DATE', date('Y-m-d H:i:s'));

                        foreach ($files as $f) {
                            if (preg_match('/^tbzipdeltimes_(\d{8}).csv$/i', $f, $date)) {
                                if (empty($last_update_date) || $date[1] > $last_update->format('Ymd')) {
                                    if (ftp_fget($conn_id, $handle, $f, FTP_ASCII, 0)) {
                                        $hash = md5_file($this->importDirectory . $filename);
                                        if ($hash != Configuration::get('GLS_SYNCHRO_FILE_CONTENT_HASH', '')) {
                                            Configuration::updateValue('GLS_SYNCHRO_FILE_CONTENT_HASH', $hash);
                                            $toSynchronize = true;
                                            $csv_file = new NkmCSVReader();
                                            $_file_content = $csv_file->parse_file($this->importDirectory . $filename, false, true);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                ftp_close($conn_id);
                fclose($handle);
                unlink($this->importDirectory . $filename);
            }

            if ($_force || $toSynchronize) {
                if (!empty($_file_content) && is_array($_file_content) && count($_file_content) > 0) {
                    $query = '';
                    foreach ($_file_content as $line) {
                        $query .= '(\'' . pSQL($line[0]) . '\', \'' . pSQL($line[1]) . '\', \'' . pSQL($line[2]) . '\'),';
                    }
                    $query = trim($query, ',');
                    if (!empty($query)) {
                        if (Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'gls_agency_postcode` WHERE 1')) {
                            Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'gls_agency_postcode` (`agency_code`, `postcode_start`, `postcode_end`) VALUES ' . $query);
                        }
                    }
                }
            }
        }
    }

    public function getCarrierIdHistory($_code = null)
    {
        $search = [];
        if (!is_null($_code) && array_key_exists($_code, self::$carrier_definition)) {
            $search[$_code] = self::$carrier_definition[$_code];
        } else {
            $search = self::$carrier_definition;
        }

        $result = [];
        foreach (array_keys($search) as $key) {
            $log = Configuration::get('GLS_' . $key . '_LOG', null, $this->context->shop->id_shop_group, $this->context->shop->id);
            if (!empty($log)) {
                $history_log = explode('|', Configuration::get('GLS_' . $key . '_LOG', null, $this->context->shop->id_shop_group, $this->context->shop->id));
            } else {
                $history_log = [];
            }

            $carrier_id = Configuration::get('GLS_' . $key . '_ID', 0, $this->context->shop->id_shop_group, $this->context->shop->id);
            if ((int) $carrier_id > 0) {
                $history_log[] = Configuration::get('GLS_' . $key . '_ID', null, $this->context->shop->id_shop_group, $this->context->shop->id);
            }

            foreach ($history_log as $k => $v) {
                if (empty($v)) {
                    unset($history_log[$k]);
                }
            }
            $result[$key] = $history_log;
        }

        return $result;
    }

    public function cronTask()
    {
        try {
            if (Tools::getIsset('action') && Tools::getValue('action') === 'get_tracking') {
                $this->updateOrderStates();
            } else {
                $controller = new AdminGlsOrderController(true);
                $exportConfig = $controller->getConfigFormValues('export');
                $importConfig = $controller->getConfigFormValues('import');

                if ($importConfig['GLS_IMPORT_AUTOMATION']
                    && (!Tools::getIsset('action') || (Tools::getIsset('action') && Tools::getValue('action') === 'import'))
                ) {
                    $controller->importWinexpe();
                    if (!empty($controller->errors)) {
                        $gls_log = new GlsLogClass();
                        $gls_log->log($this->l('Automatic import:') . ' ' . implode(Tools::nl2br("\n"), array_map('pSQL', $controller->errors)));
                    }
                }

                if ($exportConfig['GLS_EXPORT_AUTOMATION']
                    && (!Tools::getIsset('action') || (Tools::getIsset('action') && Tools::getValue('action') === 'export'))
                ) {
                    $controller->exportWinexpe();
                    if (!empty($controller->errors)) {
                        $gls_log = new GlsLogClass();
                        $gls_log->log($this->l('Automatic export:') . ' ' . implode(Tools::nl2br("\n"), array_map('pSQL', $controller->errors)));
                    }
                }
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function displayInfoByCart($_id_cart, $_id_order = false, $fromDisplayAdminOrder = false)
    {
        $order = new Order(Tools::getValue('id_order'));

        if (empty($_id_cart) && (int) $_id_order > 0) {
            $_id_cart = $order->id_cart;
        }

        $cart = new Cart((int) $_id_cart);

        $query = new DbQuery();
        $query->select('c.*')
            ->from('gls_cart_carrier', 'c')
            ->where('c.`id_cart` = ' . (int) $_id_cart);
        $result = Db::getInstance()->getRow($query);

        $isGLSRelais = $this->carrierIsGLSRelais((int) $order->id_carrier);

        if ($isGLSRelais) {
            $parcel_shop_id = null;
            if ($result && $result['id_carrier'] == (int) $order->id_carrier && !empty($result['city'])) {
                $addressDelivery = new Address();
                $addressDelivery->company = $result['name'];
                $addressDelivery->address1 = $result['address1'];
                $addressDelivery->address2 = $result['address2'];
                $addressDelivery->postcode = $result['postcode'];
                $addressDelivery->city = $result['city'];
                $addressDelivery->phone_mobile = $result['phone_mobile'];
                $addressDelivery->phone = $result['phone'];
                $addressDelivery->id_country = $result['id_country'];
                $addressDelivery->country = Country::getNameById($this->context->language->id, (int) $result['id_country']);

                $parcel_shop_id = $result['parcel_shop_id'];
            } else {
                $addressDelivery = new Address((int) $order->id_address_delivery);
            }

            $addressDeliveryCountryIso = Country::getIsoById($addressDelivery->id_country);
            $addressDeliveryCountryIso = Tools::strtoupper($addressDeliveryCountryIso);

            $params = [
                'id_order' => (int) $order->id,
                'parcel_shop_id' => $parcel_shop_id,
                'address_delivery' => $addressDelivery,
                'address_delivery_formatted' => AddressFormat::generateAddress($addressDelivery, [], Tools::nl2br("\n")),
                'ps_version' => $this->getPrestaShopVersion(),
                'address_country_code' => $addressDeliveryCountryIso,
            ];

            if ($fromDisplayAdminOrder) {
                $relayContent = $this->hookDisplayCarrierExtraContent([
                    'cart' => $cart,
                    'carrier' => ['id' => $order->id_carrier, 'name' => ''],
                    'cart_carrier_detail' => ($result ? $result : ''),
                    'fromDisplayInfoByCart' => true,
                    'forceAddressDelivery' => new Address((int) $order->id_address_delivery),
                ]);

                if (is_array($relayContent) && count($relayContent) > 0) {
                    $params = array_merge($params, $relayContent);
                }

                return $params;
            }

            $this->context->smarty->assign($params);

            return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/' . $this->templates['displayInfoByCart']);
        }
    }

    private function getPrestaShopVersion()
    {
        if (version_compare(_PS_VERSION_, '1.7.', '<')) {
            return '1.6';
        } elseif (version_compare(_PS_VERSION_, '1.7.1', '<')) {
            return '1.7.0';
        } elseif (version_compare(_PS_VERSION_, '1.7.2', '<')) {
            return '1.7.1';
        } elseif (version_compare(_PS_VERSION_, '1.7.7', '<')) {
            return '1.7.6';
        } else {
            return '1.7';
        }
    }

    public function hookActionValidateOrder($params)
    {
        $order_detail = self::getCartCarrierDetail($params['order']->id_cart, $params['order']->id_customer);

        if ($order_detail && (int) $order_detail['id_carrier'] != (int) $params['order']->id_carrier) {
            Db::getInstance()->delete('gls_cart_carrier', 'id_cart = "' . pSQL($params['order']->id_cart) . '"');
            PrestaShopLogger::addLog('NkmGls::validateOrder - GLS infos deleted (idC : ' . $order_detail['id_carrier'] . ' / idO : ' . $params['order']->id_carrier . ')', 3, null, 'Nkmgls', (int) $params['order']->id_cart, true);
        } else {
            $glsRelaisIds = $this->getCarrierIdHistory('GLSRELAIS');
            $addressDelivery = $this->getGlsRelayAddress($params['order']->id_cart, $params['order']->id_carrier, $params['order']->id_customer);

            if ($addressDelivery
                && $addressDelivery['addressObject']
                && isset($glsRelaisIds['GLSRELAIS'])
                && in_array($params['order']->id_carrier, $glsRelaisIds['GLSRELAIS'])
            ) {
                $id_lang = (int) $params['order']->id_lang;
                $customer = new Customer($params['order']->id_customer);
                $carrier = new Carrier((int) $params['order']->id_carrier);

                $orderStatus = null;
                if (isset($params['orderStatus']) && Validate::isLoadedObject($params['orderStatus'])) {
                    $orderStatus = $params['orderStatus'];
                }

                try {
                    $new_address = clone $addressDelivery['addressObject'];
                    $customer_address = new Address($params['order']->id_address_delivery);
                    $new_address->alias = $carrier->name;
                    $new_address->id_customer = $params['order']->id_customer;
                    $new_address->firstname = $customer_address->firstname;
                    $new_address->lastname = $customer_address->lastname;

                    if (!$new_address->id_country) {
                        $new_address->id_country = 8;
                    }

                    if (empty($new_address->phone)) {
                        $new_address->phone = $customer_address->phone;
                    }

                    if (empty($new_address->phone_mobile)) {
                        $new_address->phone_mobile = $addressDelivery['glsOrderDetail']['customer_phone_mobile'];
                    }

                    $new_address->deleted = true;
                    $new_address->add();

                    $params['order']->id_address_delivery = $new_address->id;
                    $params['order']->update();
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('NkmGls::validateOrder - Try clone address (idNA : ' . json_encode((array) $addressDelivery['addressObject']) . ' / e : ' . $e . ')', 3, null, 'Nkmgls', (int) $params['order']->id_cart, true);
                }

                if (!is_null($orderStatus) && $orderStatus->id != Configuration::get('PS_OS_CANCELED') && $orderStatus->id != Configuration::get('PS_OS_ERROR')) {
                    if (isset($addressDelivery['glsOrderDetail'])) {
                        $GLSWorkingDayObject = json_decode($addressDelivery['glsOrderDetail']['parcel_shop_working_day'], true);
                    }

                    $working_days = $this->displayWorkingsDay($GLSWorkingDayObject);
                    if (!is_array($working_days) || empty($working_days)) {
                        $working_days = ['html' => '', 'txt' => ''];
                    }

                    $vars = [
                        '{gls_logo}' => $this->context->shop->getBaseURL(true, false) . $this->_path . 'views/img/mails/gls-logo.jpg',
                        '{firstname}' => $customer->firstname,
                        '{lastname}' => $customer->lastname,
                        '{email}' => $customer->email,
                        '{order_name}' => $params['order']->reference,
                        '{date}' => Tools::displayDate($params['order']->date_add, null),
                        '{payment}' => Tools::substr($params['order']->payment, 0, 255),
                        '{carrier}' => $carrier->name,
                        '{address_delivery_formatted_html}' => AddressFormat::generateAddress($addressDelivery['addressObject'], [], Tools::nl2br("\n")),
                        '{address_delivery_formatted_txt}' => AddressFormat::generateAddress($addressDelivery['addressObject'], [], "\n"),
                        '{address_delivery_hours_html}' => $working_days['html'],
                        '{address_delivery_hours_txt}' => $working_days['txt'],
                    ];

                    if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
                        $lang_param = (int) $id_lang;
                    } else {
                        $lang = new Language((int) $id_lang);
                        $lang_param = $lang->getLocale();
                    }

                    return Mail::Send(
                        (int) $id_lang,
                        'gls_new_order',
                        $this->l('GLS Order confirmation', false, $lang_param),
                        $vars,
                        $customer->email,
                        $customer->firstname . ' ' . $customer->lastname,
                        null,
                        null,
                        null,
                        null,
                        dirname(__FILE__) . '/mails/',
                        false
                    );
                }
            }
        }
    }

    public function displayWorkingsDay($_GLSWorkingDayObject = [])
    {
        $trans_days = [
            '0' => $this->l('Monday'),
            '1' => $this->l('Tuesday'),
            '2' => $this->l('Wednesday'),
            '3' => $this->l('Thursday'),
            '4' => $this->l('Friday'),
            '5' => $this->l('Saturday'),
            '6' => $this->l('Sunday'),
        ];

        $html = '';
        $txt = '';

        if ($_GLSWorkingDayObject && is_array($_GLSWorkingDayObject) && count($_GLSWorkingDayObject) > 0) {
            $this->smarty->assign([
                'trans_days' => $trans_days,
                'workingDayObject' => $_GLSWorkingDayObject,
            ]);
            $html = $this->fetch('module:' . $this->name . '/views/templates/hook/' . $this->templates['displayWorkingsDay']);

            foreach ($trans_days as $day => $dname) {
                if (isset($_GLSWorkingDayObject[$day])) {
                    if ($_GLSWorkingDayObject[$day]['Breaks']['Hours']['From']) {
                        $txt .= $dname . ': ' . "\n" .
                            Tools::substr($_GLSWorkingDayObject[$day]['OpeningHours']['Hours']['From'], 0, 2) . ':' . Tools::substr($_GLSWorkingDayObject[$day]['OpeningHours']['Hours']['From'], 2, 2) .
                            ' - ' . Tools::substr($_GLSWorkingDayObject[$day]['Breaks']['Hours']['From'], 0, 2) . ':' . Tools::substr($_GLSWorkingDayObject[$day]['Breaks']['Hours']['From'], 2, 2) .
                            "\n" . Tools::substr($_GLSWorkingDayObject[$day]['Breaks']['Hours']['To'], 0, 2) . ':' . Tools::substr($_GLSWorkingDayObject[$day]['Breaks']['Hours']['To'], 2, 2) .
                            ' - ' . Tools::substr($_GLSWorkingDayObject[$day]['OpeningHours']['Hours']['To'], 0, 2) . ':' . Tools::substr($_GLSWorkingDayObject[$day]['OpeningHours']['Hours']['To'], 2, 2);
                    } else {
                        $txt .= $dname . ': ' . Tools::substr($_GLSWorkingDayObject[$day]['OpeningHours']['Hours']['From'], 0, 2) . ':' . Tools::substr($_GLSWorkingDayObject[$day]['OpeningHours']['Hours']['From'], 2, 2) .
                        Tools::substr($_GLSWorkingDayObject[$day]['OpeningHours']['Hours']['To'], 0, 2) . ':' . Tools::substr($_GLSWorkingDayObject[$day]['OpeningHours']['Hours']['To'], 2, 2);
                    }
                } else {
                    $txt .= $dname . ': ' . $this->l('Closed');
                }
                $txt .= "\n";
            }
        }

        return ['txt' => $txt, 'html' => $html];
    }

    public function getGlsRelayAddress($_id_cart, $_id_carrier, $_id_customer)
    {
        if (empty($_id_cart) || empty($_id_carrier) || empty($_id_customer)) {
            return false;
        }

        $order_detail = self::getCartCarrierDetail($_id_cart, $_id_customer, $_id_carrier);

        if ($order_detail) {
            $addressDelivery = new Address();
            $addressDelivery->company = $order_detail['name'];
            $addressDelivery->address1 = $order_detail['address1'];
            $addressDelivery->address2 = $order_detail['address2'];
            $addressDelivery->postcode = $order_detail['postcode'];
            $addressDelivery->city = $order_detail['city'];
            $addressDelivery->phone_mobile = $order_detail['phone_mobile'];
            $addressDelivery->phone = $order_detail['phone'];
            $addressDelivery->id_country = $order_detail['id_country'];
            $addressDelivery->country = Country::getNameById($this->context->language->id, (int) $order_detail['id_country']);

            return ['addressObject' => $addressDelivery, 'glsOrderDetail' => $order_detail];
        }

        return false;
    }

    public function sendInTransitEmail($_order_carrier, $_order, $_id_carrier = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.1', '<') || Shop::isFeatureActive()) {
            if (is_null($_id_carrier)) {
                $_id_carrier = $_order->id_carrier;
            }

            $customer = new Customer((int) $_order->id_customer);
            $carrier = new Carrier((int) $_id_carrier, $_order->id_lang);

            if (Validate::isLoadedObject($customer) && Validate::isLoadedObject($carrier)) {
                $translator = Context::getContext()->getTranslator();
                $shippingNumber = $_order->getWsShippingNumber();

                $orderLanguage = new Language((int) $_order->id_lang);
                $templateVars = [
                    '{followup}' => str_replace(
                        '@',
                        $shippingNumber,
                        $carrier->url
                    ),
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname,
                    '{id_order}' => $_order->id,
                    '{shipping_number}' => $shippingNumber,
                    '{order_name}' => $_order->getUniqReference(),
                ];

                return Mail::Send(
                    (int) $_order->id_lang,
                    'in_transit',
                    $translator->trans(
                        'Package in transit',
                        [],
                        'Emails.Subject',
                        $orderLanguage->locale
                    ),
                    $templateVars,
                    $customer->email,
                    $customer->firstname . ' ' . $customer->lastname,
                    null,
                    null,
                    null,
                    null,
                    _PS_MAIL_DIR_,
                    false,
                    (int) $_order->id_shop
                );
            }
        } else {
            return @$_order_carrier->sendInTransitEmail($_order);
        }

        return false;
    }

    public function getAdditionalPrice($_country_iso, $_postcode)
    {
        $_postcode = str_replace(' ', '', $_postcode);
        if (!empty($_country_iso) && !empty($_postcode)) {
            if (($_country_iso == 'FR' && preg_match('/^20\d{3}$/', $_postcode)) || $_country_iso == 'COS') {
                return Configuration::get('GLS_ADD_PRICE_CORSICA');
            } elseif ($_country_iso == 'FR' && in_array(
                $_postcode,
                ['22870', '29242', '29253', '29259', '29990', '56360', '56590', '56780', '56840', '85350']
            )) {
                return Configuration::get('GLS_ADD_PRICE_FR_ISLAND');
            } elseif ($_country_iso == 'GB' && preg_match('/^(GY|HS|IM|JE|KW15|KW16|KW17|ZE){1}.*$/', $_postcode)) {
                return Configuration::get('GLS_ADD_PRICE_GB_ISLAND');
            } elseif (($_country_iso == 'ES' && preg_match('/^(7|07).*$/', $_postcode))
                || ($_country_iso == 'GR' && in_array(
                    $_postcode,
                    ['18010', '18020', '18040', '18050', '18900', '28100', '29100', '31100', '37002', '37003', '49100',
                        '64004', '68002', '70014', '70300', '70400', '71300', '72100', '72200', '72300', '73100', '74100', '81100',
                        '81107', '81400', '82100', '83100', '83300', '84001', '84002', '84003', '84005', '84006', '84100', '84200',
                        '84300', '84400', '84500', '84600', '84700', '84801', '85100', '85200', '85300', '85400', '85700', '85900', ]
                ))
                || ($_country_iso == 'IT' && preg_match('/^(07|08|09|9).*$/', $_postcode))
            ) {
                return Configuration::get('GLS_ADD_PRICE_ISLANDS');
            } elseif (($_country_iso == 'ES' && preg_match('/^(35|38|51|52).*$/', $_postcode))
                || ($_country_iso == 'PT' && preg_match('/^9.*$/', $_postcode))
            ) {
                return Configuration::get('GLS_ADD_PRICE_SP_PT_ISLAND');
            } elseif ($_country_iso == 'FR' && in_array(
                $_postcode,
                ['04160', '04170', '04240', '04260', '04310', '04330', '04360', '04370', '04400', '04510', '04530', '04600',
                    '04850', '05100', '05120', '05150', '05160', '05170', '05200', '05220', '05240', '05250', '05260', '05290', '05310',
                    '05320', '05330', '05340', '05350', '05460', '05470', '05500', '05560', '05600', '05700', '05800', '06380', '06390',
                    '06420', '06430', '06440', '06450', '06460', '06470', '06540', '06620', '06660', '06710', '06750', '06830', '06850',
                    '06910', '09110', '09140', '09220', '09230', '09300', '09390', '09460', '15140', '15300', '25160', '25190', '25240',
                    '25370', '25380', '25430', '25470', '31110', '38112', '38114', '38142', '38190', '38250', '38350', '38410', '38520',
                    '38580', '38650', '38660', '38680', '38700', '38710', '38730', '38740', '38750', '38770', '38830', '38860', '38880',
                    '38930', '38970', '63113', '63240', '63610', '63680', '63850', '64440', '64490', '64560', '64570', '65110', '65120',
                    '65170', '65240', '65260', '65400', '65510', '65710', '66120', '66210', '66230', '66260', '66320', '66340', '66360',
                    '66480', '66720', '66730', '66760', '66800', '66820', '73120', '73130', '73140', '73150', '73170', '73210', '73220',
                    '73260', '73270', '73300', '73320', '73340', '73350', '73360', '73440', '73450', '73470', '73480', '73500', '73520',
                    '73530', '73550', '73570', '73590', '73620', '73630', '73640', '73660', '73670', '73700', '73710', '73720', '73730', '73790',
                    '73870', '74110', '74120', '74170', '74190', '74220', '74230', '74250', '74260', '74310', '74340', '74360', '74390', '74400',
                    '74420', '74430', '74440', '74450', '74470', '74480', '74490', '74550', '74560', '74660', '74740', '74920', '83560', '83630', ]
            )) {
                return Configuration::get('GLS_ADD_PRICE_MOUNTAIN');
            }
        }

        return null;
    }

    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['id_customer']) &&
            Validate::isLoadedObject(new Customer((int) $customer['id']))) {
            if (Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'gls_cart_carrier` SET `customer_phone_mobile`=\'\', `id_customer`=' . (int) Configuration::get('PSGDPR_ANONYMOUS_CUSTOMER') . ' WHERE id_customer = ' . (int) $customer['id'])) {
                return json_encode(true);
            }

            return json_encode($this->l('GLS : Unable to delete customer using id.'));
        }
    }

    public function hookActionObjectCustomerDeleteAfter($params)
    {
        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'gls_cart_carrier` SET `customer_phone_mobile`=\'\', `id_customer`=' . (int) Configuration::get('PSGDPR_ANONYMOUS_CUSTOMER') . ' WHERE id_customer = ' . (int) $params['object']->id);
    }

    public function hookActionExportGDPRData($customer)
    {
        if (!empty($customer['id']) &&
            Validate::isLoadedObject(new Customer((int) $customer['id']))) {
            $data = [];
            $res = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . "gls_cart_carrier WHERE id_customer = '" . (int) $customer['id'] . "'");
            if ($res) {
                foreach ($res as $value) {
                    array_push($data, [
                        $this->l('ID cart') => $value['id_cart'],
                        $this->l('Customer phone mobile') => $value['customer_phone_mobile'],
                    ]);
                }

                return json_encode($data);
            }

            return json_encode($this->l('No data found.'));
        }
    }

    public static function setCartCarrierDetail($id_order, $parcel_shop_id)
    {
        try {
            if (!empty($id_order) && is_numeric($id_order) && (int) $id_order > 0 && !empty($parcel_shop_id)) {
                if (Validate::isLoadedObject($order = new Order($id_order))) {
                    if ((int) $order->id_carrier != (int) Configuration::get('GLS_GLSRELAIS_ID')) {
                        $history_log = explode('|', Configuration::get('GLS_GLSRELAIS_LOG'));
                        if (!in_array($order->id_carrier, $history_log)) {
                            return false;
                        }
                    }
                    if (empty($order->id_cart)) {
                        return false;
                    }

                    $query = new DbQuery();
                    $query->select('c.*')
                        ->from('gls_cart_carrier', 'c')
                        ->where('c.`id_customer` = ' . (int) $order->id_customer)
                        ->where('c.`id_cart` = ' . (int) $order->id_cart);

                    if (Db::getInstance()->getRow($query)) {
                        if (Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'gls_cart_carrier` SET `parcel_shop_id` = \'' . pSQL($parcel_shop_id)
                            . '\' WHERE `id_customer`=' . (int) $order->id_customer . ' AND `id_cart`=' . (int) $order->id_cart)) {
                            return true;
                        }
                    } else {
                        if (Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'gls_cart_carrier`
                            (`id_customer`, `id_cart`, `id_carrier`, `gls_product`, `customer_phone_mobile`, `parcel_shop_id`)
                            VALUES (' . (int) $order->id_customer . ', ' . (int) $order->id_cart . ', ' . (int) $order->id_carrier . ', \'\', \'\', \'' . pSQL($parcel_shop_id) . '\')')) {
                            return true;
                        }
                    }
                }
            }
        } catch (Exception $e) {
        }

        return false;
    }

    public function updateOrderStates($idOrder = false)
    {
        $date = new DateTime();
        $date->modify('-1 month');
        $sql = new DbQuery();
        $sql->select('o.*, oc.tracking_number, `gts`.`current_state` as `current_tracking_state`, `gts`.`id_gls_tracking_state`')
            ->from('orders', 'o')
            ->leftJoin('gls_tracking_state', 'gts', 'o.`id_order` = gts.`id_order`')
            ->leftJoin('order_carrier', 'oc', 'o.`id_order` = oc.`id_order`')
            ->leftJoin('carrier', 'ca', 'o.`id_carrier` = ca.`id_carrier`')
            ->where('ca.id_carrier IS NOT NULL')
            ->where('ca.`external_module_name` = \'nkmgls\'');

        $idShop = null;
        if ($idOrder !== false && (int) $idOrder > 0) {
            $sql->where('o.id_order = ' . (int) $idOrder);
            $idShop = Configuration::get('PS_SHOP_DEFAULT');
        } else {
            $sql->where('gts.`current_state` IS NULL OR gts.`current_state` != \'DELIVERED\'')
                ->where('o.current_state != \'' . (int) Configuration::get('PS_OS_CANCELED') . '\'')
                ->where('DATE_FORMAT(o.date_upd, \'%Y-%m-%d\') <= \'' . date('Y-m-d') . '\' AND DATE_FORMAT(o.date_upd, \'%Y-%m-%d\') >= \'' . $date->format('Y-m-d') . '\'');

            if (Shop::isFeatureActive() && Shop::getContextShopID()) {
                $sql->where('o.id_shop = ' . Shop::getContextShopID());
                $idShop = Shop::getContextShopID();
            }

            $sql->orderBy('o.id_order DESC');
        }

        $result = Db::getInstance()->ExecuteS($sql);

        $gls_log = new GlsLogClass();

        if (!$result) {
            return;
        }

        foreach ($result as $order) {
            try {
                $api = GlsApi::createInstance(
                    Configuration::get('GLS_API_LOGIN', null, null, (int) $order['id_shop']),
                    Configuration::get('GLS_API_PWD', null, null, (int) $order['id_shop'])
                );
            } catch (GlsApiException $e) {
                $gls_log->log($this->l('GLS Web API connection error for update tracking states'));
                continue;
            }

            $tracking_numbers = '';
            if (!empty($order['tracking_number'])) {
                $tracking_numbers = $order['tracking_number'];
            } elseif (!empty($order['shipping_number'])) {
                $tracking_numbers = $order['shipping_number'];
            }

            $tracking_numbers = trim((string) $tracking_numbers);

            if (!empty($tracking_numbers)) {
                try {
                    $tracking = $api->get('tracking/references/' . $tracking_numbers);
                    if ($tracking && isset($tracking->parcels) && is_array($tracking->parcels) && count($tracking->parcels) > 0) {
                        $status = '';
                        $updateStatus = true;
                        foreach ($tracking->parcels as $p) {
                            if (empty($status)) {
                                $status = $p->status;
                            } elseif ($status != $p->status) {
                                $updateStatus = false;
                            }
                        }

                        if ($updateStatus && !empty($status) && $status != $order['current_tracking_state']) {
                            $newOrderState = Configuration::get('GLS_TRACKING_API_ORDER_STATE_' . $status, null, $order['id_shop_group'], $order['id_shop']);

                            if (empty($newOrderState) && empty($idOrder)) {
                                $api->reset();
                                continue;
                            }

                            if (!empty($newOrderState) && is_numeric($newOrderState) && (int) $newOrderState != (int) $order['current_state']) {
                                $orderObject = new Order($order['id_order']);
                                $orderObject->setCurrentState($newOrderState);
                            }

                            $glsTrackingObject = new GlsTrackingStateClass($order['id_gls_tracking_state']);
                            $glsTrackingObject->id_order = $order['id_order'];
                            $glsTrackingObject->current_state = $status;
                            $glsTrackingObject->save();

                            if ($idOrder !== false && (int) $idOrder > 0) {
                                return [
                                    'current_state' => self::$trackingStates[$status],
                                    'current_state_date' => Tools::displayDate(date('Y-m-d H:i:s'), null, true),
                                ];
                            }
                        } elseif ($idOrder !== false && (int) $idOrder > 0) {
                            return ['message' => $this->l('State is already up-to-date')];
                        }
                    } else {
                        $error_msg = '';
                        if (is_array($api->error) && count($api->error) > 0) {
                            foreach ($api->error as $error) {
                                if (is_array($error) && isset($error['message'])) {
                                    $error_msg = $error['message'] . ' [' . $error['code'] . ']';
                                } else {
                                    $error_msg = $error;
                                }
                                $gls_log->log(sprintf($this->l('Tracking state error for order %s:'), $order['id_order']) . ' ' . $error_msg);
                            }
                        }

                        if (empty($error_msg)) {
                            $error_msg = $this->l('An error occured , please contact technical support.');
                        }

                        if ($idOrder !== false && (int) $idOrder > 0) {
                            return ['error' => true, 'message' => $error_msg];
                        }
                    }
                } catch (Exception $e) {
                    $gls_log->log($this->l('Tracking state Exception:') . ' ' . $e->getMessage() . ' [' . $e->getCode() . ']');
                }

                $api->reset();
            } elseif ($idOrder !== false && (int) $idOrder > 0) {
                return ['error' => true, 'message' => $this->l('There is no tracking number on the order')];
            }
        }

        if ($idOrder !== false && (int) $idOrder > 0) {
            return ['error' => true, 'message' => $this->l('An error occured , please contact technical support.')];
        }
    }

    public function carrierIsGLSRelais($idCarrier)
    {
        $glsRelaisIds = $this->getCarrierIdHistory('GLSRELAIS');

        if ((int) $idCarrier > 0 && isset($glsRelaisIds['GLSRELAIS']) && in_array($idCarrier, $glsRelaisIds['GLSRELAIS'])) {
            return true;
        }

        return false;
    }

    public function getGlsProductCode($idCarrier, $countryCode = 'FR')
    {
        $carriersIdHistory = $this->getCarrierIdHistory();

        if (!empty($idCarrier) && is_numeric($idCarrier)) {
            foreach ($carriersIdHistory as $k => $v) {
                if (!in_array($idCarrier, $v)) {
                    continue;
                }

                switch ($k) {
                    case 'GLSRELAIS':
                        $carrierCode = GlsValue::GLS_RELAIS;
                        break;

                    case 'GLSCHEZVOUSPLUS':
                        $carrierCode = GlsValue::GLS_CHEZ_VOUS_PLUS;
                        break;

                    case 'GLS13H':
                        $carrierCode = GlsValue::GLS_AVANT_13H;
                        break;

                    default:
                        $carrierCode = GlsValue::GLS_CHEZ_VOUS;
                        break;
                }

                $adaptedCarrier = $this->carrierHandler->create()
                    ->setIsGls(true)
                    ->setCode($carrierCode)
                ;

                $adaptedAddress = $this->addressHandler->create()
                    ->setCountryCode($countryCode)
                ;

                return $this->glsHelper->getProductCode(
                    $adaptedCarrier,
                    $adaptedAddress
                );
            }
        }

        return false;
    }
}
