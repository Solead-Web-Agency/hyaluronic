<?php
/**
* 2007-2023 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

class InstallDemo
{
    public function insertData()
    {
        $sql = array();
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_history` (
                  `id` int(10) NOT NULL AUTO_INCREMENT,
                  `id_order` int(10) NOT NULL,
                  `id_order_carrier` int(10) NOT NULL,
                  `hl_carrier` int(11) NOT NULL,
                  `method` tinyint(1) NOT NULL,
                  `success_response` tinyint(1) NOT NULL,
                  `event_code` tinyint(1) NOT NULL,
                  `carrier_response` varchar(128) NOT NULL,
                  `step_date` datetime NOT NULL,
                  `email_sent` tinyint(1) NOT NULL,
                  `date_add` datetime NOT NULL,
                  PRIMARY KEY (`id`),
				  KEY `id_order` (`id_order`),
				  KEY `id_order_carrier` (`id_order_carrier`)
                ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_carrier` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `active` tinyint(1) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `url` varchar(500) NOT NULL,
                  `method` tinyint(1) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_carrier_matching` (
                  `id_carrier_hl` int(11) NOT NULL,
                  `id_carrier_ps` int(11) NOT NULL,
                  PRIMARY KEY (`id_carrier_hl`,`id_carrier_ps`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_email` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `id_order` int(11) NOT NULL,
				  `id_order_carrier` int(11) NOT NULL,
				  `id_tracking_history` int(11) DEFAULT NULL,
				  `shipping_status` tinyint(1) NOT NULL,
			      `date_sent` datetime NOT NULL,
				  `email_status` tinyint(1) NOT NULL,
                  PRIMARY KEY (`id`),
				  KEY `id_order` (`id_order`),
				  KEY `id_tracking_history` (`id_tracking_history`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';
        $sql[] = 'INSERT IGNORE INTO `'._DB_PREFIX_.'configuration_lang`
		(`id_configuration`, `id_lang`, `value`, `date_upd`)
			(SELECT c.id_configuration, l.id_lang,
			CASE WHEN l.iso_code LIKE "en" THEN "Delivery tracking of your order"
			WHEN l.iso_code LIKE "fr" THEN "Suivi de livraison de votre commande"
			WHEN l.iso_code LIKE "es" THEN "Seguimiento de la entrega de su pedido"
			WHEN l.iso_code LIKE "it" THEN "Tracciamento della consegna del tuo ordine"
			WHEN l.iso_code LIKE "de" THEN "Sendungsverfolgung Ihrer Bestellung"
			WHEN l.iso_code LIKE "nl" THEN "Levering volgen van uw bestelling"
			ELSE "Delivery tracking of your order"
			END,CURRENT_TIMESTAMP FROM '._DB_PREFIX_.'lang l
			LEFT JOIN '._DB_PREFIX_.'configuration c ON c.name LIKE "DELIVERY_EMAIL_SUBJECT")';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_sent_report` (
				`id_tracking_sent_report` int(1) NOT NULL AUTO_INCREMENT,
				`id_order_carrier` int(11) NOT NULL,
				`id_connector` int(11) NOT NULL,
				`id_status` int(11) NOT NULL,
				`carrier_status_code` varchar(128) NOT NULL,
				`recipient` tinyint(1) NOT NULL,
				PRIMARY KEY (`id_tracking_sent_report`),
				UNIQUE KEY `send_report`
                (`id_order_carrier`,`id_connector`,`id_status`,`carrier_status_code`,`recipient`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_disable` (
				`id_order_carrier` int(11) NOT NULL,
				PRIMARY KEY (`id_order_carrier`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_current_status` (
            `id_order_carrier` int(11) NOT NULL,
            `id_status` int(11) NOT NULL,
            `date` datetime NOT NULL,
            PRIMARY KEY (`id_order_carrier`)
         ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_events` (
		    `id_tracking_events` int(11) NOT NULL AUTO_INCREMENT,
            `id_order_carrier` int(11) NOT NULL,
            `id_status` int(11) NOT NULL,
            `event_description` varchar(255) NOT NULL,
		    `date` datetime NOT NULL,
            PRIMARY KEY (`id_tracking_events`),
			UNIQUE `id_order_carrier` (`id_order_carrier`,`event_description`,`date`)
         ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_return` (
           `id_return` int(11) NOT NULL AUTO_INCREMENT,
           `id_order` int(11) NOT NULL,
           `id_order_return` int(11) NULL,
           `id_connector` int(11) NULL,
           `shipping_number` varchar(64) CHARACTER SET utf8 NOT NULL,
           `shipping_status` int(11) NULL,
           `date_add` datetime NOT NULL,
           `status_date` datetime NOT NULL,
           PRIMARY KEY (`id_return`),
           KEY `id_order` (`id_order`),
           KEY `id_order_return` (`id_order_return`),
           KEY `id_connector` (`id_connector`),
           UNIQUE `shipping_number` (`shipping_number`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_issue` (
		  `id_issue` int(11) NOT NULL AUTO_INCREMENT,
		  `id_order_carrier` int(11) NOT NULL,
		  `issue_type` int(11) NOT NULL,
			PRIMARY KEY (`id_issue`),
			KEY `id_order_carrier` (`id_order_carrier`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_issue_status` (
		  `id_tracking_issue_status` int(11) NOT NULL AUTO_INCREMENT,
		  `id_issue` int(11) NOT NULL,
		  `status` int(11) NOT NULL,
		  `detail` varchar(128) NULL,
		  `date` datetime NOT NULL,
			PRIMARY KEY (`id_tracking_issue_status`),
			KEY `id_issue` (`id_issue`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_status_matching` (
        `id_connector` int(11) NOT NULL,
        `id_status` int(11) NOT NULL,
        `code` varchar(128) NOT NULL,  
        PRIMARY KEY (`id_connector`,`code`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }
    }
}
