<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 * @package   cdc_googletagmanager
 */

if (!defined('_PS_VERSION_')) { exit; }

/**
 *
 * Object Model to log datalayers
 *
 */
class CdcGtmDataLayer extends ObjectModel
{

	/**
	 * Fields
	 */
	public $id_cdc_gtm_datalayer;
	public $event;
	public $uri;
	public $datalayer;
    public $id_shop;
	public $date_add;
	public $date_upd;

	/**
	 * Definition
	 * @var unknown
	 */
	public static $definition = array (
			'table' => 'cdc_gtm_datalayer',
			'primary' => 'id_cdc_gtm_datalayer',
			'fields' => array (
                    'event' => 	    array('type' => self::TYPE_STRING),
                    'uri' => 	    array('type' => self::TYPE_STRING),
					'datalayer' => 	array('type' => self::TYPE_STRING),
                    'id_shop' => 	array('type' => self::TYPE_INT),
					'date_add' => 	array('type' => self::TYPE_DATE),
					'date_upd' => 	array('type' => self::TYPE_DATE)
			)
	);


	public static function createTable()
	{
		return Db::getInstance()->Execute("
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_.self::$definition['table']."` (
				`id_cdc_gtm_datalayer` int(11) NOT NULL AUTO_INCREMENT,
				`event` varchar(64) DEFAULT NULL,
				`uri` varchar(128) DEFAULT NULL,
				`datalayer` TEXT NOT NULL,
				`id_shop` int(11) NOT NULL,
				`date_add` datetime DEFAULT NULL,
				`date_upd` datetime DEFAULT NULL,
				PRIMARY KEY (`id_cdc_gtm_datalayer`),
				KEY `event` (`event`)
			) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
	}

	public static function deleteTable()
	{
		return Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::$definition['table'].'`');
	}



	/**
	 * Purge table
	 */
	protected function purge($max_records = 10000)
	{
        $sql = "SELECT COUNT(*) FROM `"._DB_PREFIX_.self::$definition['table']."`";
        $count = (int) Db::getInstance()->getValue($sql);

        if($count > $max_records) {
            $sql = "DELETE FROM `"._DB_PREFIX_.self::$definition['table']."` ORDER BY ".self::$definition['primary']." ASC LIMIT ".($count - $max_records);
            return Db::getInstance()->execute($sql);
        }
        return true;
	}

    /**
     * Log the datalayer in the database
     * @param $dataLayerJs
     */
    public function logDataLayerInDb($event, $dataLayerJs)
    {
        $this->event = $event;
        $this->datalayer = $dataLayerJs;
        $this->uri = strlen($_SERVER['REQUEST_URI']) > 128 ? substr($_SERVER['REQUEST_URI'],0,128) : $_SERVER['REQUEST_URI'];
        $this->id_shop = Context::getContext()->shop->id;
        $this->save();

        // trigger purge
        if($this->id % 100 == 0) {
            $this->purge();
        }
    }


}
