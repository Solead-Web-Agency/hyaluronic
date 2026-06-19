<?php
/**
* 2007-2023 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_4_0()
{
    $query = array();
    if (Configuration::get("updatestatusversion") == null) {
        Configuration::updateValue("updatestatusversion", '1.4.0');
        $query[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'hl_carrier`;';
        $query[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_carrier` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `active` tinyint(1) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `url` varchar(500) NOT NULL,
                  `method` tinyint(1) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';
        $query[]  = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_history` (
                  `id` int(10) NOT NULL AUTO_INCREMENT,
                  `id_order` int(10) NOT NULL,
                  `hl_carrier` int(11) NOT NULL,
                  `method` tinyint(1) NOT NULL,
                  `success_response` tinyint(1) NOT NULL,
                  `event_code` tinyint(1) NOT NULL,
                  `carrier_response` varchar(128) NOT NULL,
                  `step_date` datetime NOT NULL,
                  `email_sent` tinyint(1) NOT NULL,
                  `date_add` datetime NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8';
                /*
        $query[] = "INSERT INTO `"._DB_PREFIX_."hl_carrier` (`id`, `active`, `name`, `url`, `method`) VALUES
                (1, 1, 'Colissimo', 'http://www.colissimo.fr/portail_colissimo/suivreResultat.do?parcelnumber=@', 0),
                (2, 0, 'Chronopost', 'http://www.chronopost.fr/expedier/inputLTNumbers.do?chronoNumbers=@', 0),
                (3, 1, 'GLS France', 'https://gls-group.eu/app/service/open/rest/FR/fr/rstt001?match=@', 0),
                (4, 0, 'Coliprivé', 'https://www.colisprive.com/moncolis/pages/detailColis.aspx?numColis=@', 0),
                (5, 0, 'Fedex', 'https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber=@', 0),
                (6, 0, 'TNT', 'http://www.tnt.fr/public/suivi_colis/recherche/visubontransport.do?bonTransport=@', 0),
                (7, 0, 'Lettre suivie - La Poste', 'http://www.csuivi.courrier.laposte.fr/suivi/index?id=@', 0),
                (8, 0, 'DPD France', 'https://tracking.dpd.de/parcelstatus?query=@', 0),
                (9, 0, 'UPS', 'https://wwwapps.ups.com/WebTracking/
                processRequest?HTMLVersion=5.0&Requester=NES&AgreeToTermsAndConditions=yes&loc=fr_FR&tracknum=@', 0),
                (10, 0, 'Mondial Relay', 'http://www.mondialrelay.fr/
                suivi-de-colis?codeMarque=EC&numeroExpedition=@', 0)";
                */
        foreach ($query as $q) {
            Db::getInstance()->execute($q);
        }
        addtab();
        return true;
    } elseif (version_compare(_PS_VERSION_, '1.3.0', '>=')) {
        addtab();
        return true;
    }
    return true;
}

function addtab()
{
    Db::getInstance()->insert(
        'tab',
        array(
            'id_parent' => -1,
            'class_name' => 'AdmindeliveryorderautoupdateAjax',
            'module' => 'deliveryorderautoupdate',
            'active' => 1
        )
    );
}
