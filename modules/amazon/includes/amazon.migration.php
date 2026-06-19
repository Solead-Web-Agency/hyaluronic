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
if (!defined('_PS_VERSION_')) { exit; }
class AmazonMigration
{
    // Migration version can be different between stores
    const CONFIG_MIGRATION_VERSION = 'AMAZON_MIGRATION_VERSION';

    /** @var Amazon */
    protected $module;

    /** @var AmazonDBManager */
    protected $dbManager;

    /** @var AmazonLogger */
    protected $logger;

    /** @var string */
    protected $version;

    public function __construct($module)
    {
        $this->module = $module;
        $this->version = Configuration::get(self::CONFIG_MIGRATION_VERSION);

        require_once dirname(__FILE__) . '/amazon.db.manager.php';
        $this->dbManager = new AmazonDBManager($module);
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function needMigrate()
    {
        return version_compare($this->version, '4.10', '<');
    }

    /**
     * On installation, on postProcess
     * @return bool
     */
    public function migrateMarketplaceTables()
    {
        return $this->dbManager->addMarketPlaceTables();
    }

    public function migrateDuringSaveConfiguration()
    {
        $this->dbManager->migrateCarrierMappingOutgoing(true);
    }

    /**
     * Data migration when loading setting - module configure
     * @return void
     */
    public function migrateDuringLoadConfiguration()
    {
        $this->dbManager->migrateCarrierMappingIncomingCarrier();
        $this->migrate_5_2_remove_obsolete_file();
    }

    // todo: Handle error
    public function migrate()
    {
        $migrationVersion = $this->version ?: '0.0.0';

        if (version_compare($migrationVersion, '4.9.352', '<')
            && $this->migrate_4_9_352_update_vcs_structure_and_truncate_duplication()) {
            Configuration::updateValue(self::CONFIG_MIGRATION_VERSION, '4.9.352');
        }
        if (version_compare($migrationVersion, '4.9.387', '<')) {
            $migrate4_9_387 = $this->migrate_4_9_387_install_amazon_states();
            if ($migrate4_9_387['code'] === 1) {
                Configuration::updateValue(self::CONFIG_MIGRATION_VERSION, '4.9.387');
            }
        }
        if (version_compare($migrationVersion, '4.10', '<')
            && $this->migrate_4_10_upgrade_ps_context_structure()) {
            Configuration::updateValue(self::CONFIG_MIGRATION_VERSION, '4.10');
        }
        if (version_compare($migrationVersion, '5.2', '<')
            && $this->migrate_5_2_remove_obsolete_file()) {
            Configuration::updateValue(self::CONFIG_MIGRATION_VERSION, '5.2');
        }

        // Additional job
        // $this->clearLogs(); Remove this function when updating settings. (We have auto call function for this).
    }

    protected function clearLogs()
    {
        // There is 50% percent that triggers logs clearance
        if ((rand(0, 1) - 0.5) < 0) {
            require_once dirname(__FILE__) . '/../classes/amazon.logger.class.php';
            $logger = new AmazonLogger('');   // Dummy instance
            $logger->clearOldLogs();
        }
    }

    protected function migrate_4_9_352_update_vcs_structure_and_truncate_duplication()
    {
        require_once dirname(__FILE__) . '/../classes/amazon.vidr_shipment.class.php';

        // todo: Remove in future when VIDR works fine
        AmazonDBManager::upgradeStructureTableVIDRShipment();
        AmazonDBManager::upgradeStructureTableVIDRShipment2();  // 2021-06-01

        $tbl = AmazonVIDRShipment::getTableName();
        $sql = "DELETE v1 FROM `$tbl` v1 JOIN `$tbl` v2
                USING (`shipping_id`, `marketplace`, `transaction_id`, `transaction_type`)
                WHERE v1.id < v2.id";
        return Db::getInstance()->execute($sql);
    }

    protected function migrate_4_9_387_install_amazon_states()
    {
        return $this->installAmazonStates();
    }

    protected function migrate_4_10_upgrade_ps_context_structure()
    {
        $oldContext = AmazonDataAdjustment::unserialize(  // TODO: Validation: unserialize is deprecated and will be removed soon.
            AmazonTools::decode(
                AmazonConfiguration::getGlobalValue(AmazonConstant::PS_CONTEXT_DATA)
            )
        );
        if (!is_array($oldContext) || !count($oldContext)) {
            $oldContext = array();
        }

        return AmazonConfiguration::updateGlobalValue(
            AmazonConstant::PS_CONTEXT_4_10,
            AmazonTools::encode(
                json_encode($oldContext)
            )
        );
    }

    protected function migrate_5_2_remove_obsolete_file()
    {
        $path = _PS_MODULE_DIR_ . '/amazon/classes/seller_partner/feeds/AmazonSPFeedMessageOrderAcknowledgement.php';
        if (file_exists($path)) {
            return @unlink($path);
        }

        return true;
    }

    public function installAmazonStates()
    {
        require_once dirname(__FILE__) . '/../classes/amazon.remote.downloader.class.php';
        $downloader = new AmazonRemoteDownloader(AmazonRemoteDownloader::RES_STATES, false);
        if (!$downloader->downloadResource()) {
            $resultCode = -1;
        } else {
            $sqls = $downloader->getResource();
            if (!$this->dbManager->runFromTexts($sqls)) {
                $resultCode = -2;
            } else {
                $resultCode = 1;
            }
        }

        return array('code' => $resultCode, 'debug' => $downloader->getErrors());
    }

    public function getDbManager()
    {
        return $this->dbManager;
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return $this->dbManager->getErrors();
    }
}
