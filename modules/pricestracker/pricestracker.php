<?php
/** * PROPERTY
*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the idIA Tech License
 * You can't :
 * 1) Modify the source code
 * 2) Sell or distribute this module without our agreement
 * 3) Use this module with more than one domain (1 licence = 1 domain)
 * 4) Divert the source code
 *
 * @author    	idIA Tech
 * @copyright   Copyright (c) 2013 idIA Tech (https://www.idia-tech.com)
 * @license     https://www.storeinterfacer.com/conditions-generales.php idIA Tech (Proprietary)
 * @category    main
 */


if (!defined('_PS_VERSION_'))
  exit;
  
class Pricestracker extends Module
{
	public function __construct()
	{
		

		/***********GONFIG CLIENT**************/
		$this->licence='hyaluronicfillermarket';
		$this->version2='MEGA'; // Silver Gold Diamond MEGA
		$this->modeTest=false; //true false ou '11157,3679,2033,11156,533,3524,659,328,3789,3056'
		$this->stats=true; //true false
		/***********GONFIG CLIENT**************/
		
		
		$this->nbProduitsTest=10; // nombre de produits de test
		$this->maj=true; //DEVRAIS ÊTRE A true. ATTENTION OPTION DANGEREUSE ! Mise à jour du module (true) OU réinitialisation complète (false) !?!
		$this->apporteur=''; // prestatoolbox idiatech forum addons
		
		
		
		$this->name = 'pricestracker';
		$this->tab = 'analytics_stats'; //Statistiques et analyses
		$this->version = '3.7.8';
		$this->author = 'idIA Tech';
		$this->need_instance = 0;
        $this->bootstrap = false;
		$this->ps_versions_compliancy = [
            'min' => '1.4.0.1',
            'max' => '8.99.99',
        ];
		
		parent::__construct();
		
		$this->displayName = $this->l('PricesTracker - Competitive Intelligence'); //PricesTracker - Veille concurrentielle
		$this->description = $this->l('Monitor prices of your competitors\' sites'); //Surveillez les prix des sites de vos concurrents
				
		if(false) //is debug
		{
			@ini_set('display_errors', 'on');
			@error_reporting(E_ALL | E_STRICT);
		}
	}
	
//check prod :

//install ADDONS

// if(Tools::getValue('fav')>0 && $this->version2!='Silver')   //GOLD - DIAMOND
//pages/communication.php	    elseif(Tools::isSubmit('getPageToCrawl') && Tools::isSubmit('id_concurrents'))         //GOLD - DIAMOND
//pages/communication.php	    elseif(Tools::isSubmit('setProximite'))         //GOLD - DIAMOND
//pages/frequence.php        //GOLD - DIAMOND

// if($this->version2!='Diamond') fputcsv($fp, array('For version DIAMOND'),';');      //DIAMOND
// $smarty->assign('cronRegles', 'DIAMOND version' );           //DIAMOND
//pages/executeRules.php             //DIAMOND

	
	
	//installation
	public function install()
	{
		global $cookie;
		
		$sql = array();
		
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_produits_etrangers` (
				  `id_produits_etrangers` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `id_concurrents` int(10) unsigned NOT NULL,
				  `nom` varchar(255) DEFAULT NULL,
				  `prix` varchar(160) DEFAULT NULL,
				  `image` TEXT DEFAULT NULL,
				  `quantite` int(6) DEFAULT NULL,
				  `reference` varchar(128) DEFAULT NULL,
				  `additionalInfos` varchar(255) DEFAULT NULL,
				  `marketplace` varchar(30) DEFAULT NULL,
				  `lien` text,
				  `frequenceMaj` decimal(5,1) DEFAULT NULL,
				  `date` int(13) DEFAULT NULL,
				  `date_ajout` int(13) DEFAULT NULL,

                  PRIMARY KEY (`id_produits_etrangers`),
				  INDEX `id_concurrents` (`id_concurrents`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_associations` (
                  `id_associations` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `id_produits_etrangers` bigint(20) UNSIGNED NOT NULL,
				  `id_product` INT( 10 ) UNSIGNED NOT NULL,
                  `seuil_programmer` TEXT DEFAULT NULL,
                  `automatique` TINYINT(1) NOT NULL DEFAULT \'0\',
				  
                  PRIMARY KEY (`id_associations`),
				   INDEX `id_produits_etrangers` (`id_produits_etrangers`),
				  INDEX `id_product` (`id_product`),
				  UNIQUE (`id_produits_etrangers` ,`id_product`)
               ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_proximites` (
                  `id_proximites` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `id_product` INT( 10 ) UNSIGNED NOT NULL,
                  `id_concurrents` int(10) unsigned NOT NULL,
				  `id_produits_etrangers` bigint(20) UNSIGNED NOT NULL,
				  `proximite` INT( 8 ) UNSIGNED NOT NULL,
				  
                  PRIMARY KEY (`id_proximites`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_concurrents` (
                  `id_concurrents` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `nom` VARCHAR( 40 ),
				  `url` TEXT,
				  `ordre` DECIMAL(4,1),
				  `httpAgent` TEXT,
				  `profondeur` INT(3),
				  `delai` INT(7),
				  `maxUrl` INT(10),
				  `regexTitre` TEXT,
				  `regexPrix` TEXT,
				  `regexUrlBloquer` TEXT,
				  `regexImage` text,
				  `regexRef` text,
				  `regexQuantite` text,
				  `masqueImage` int(2) DEFAULT NULL,
				  `masqueRef` int(2) DEFAULT NULL,
				  `masqueQuantite` int(2) DEFAULT NULL,
				  `masqueTitre` INT(2),
				  `masquePrix` INT(2),
				  `nb_taches` INT(4),
				  `suivi_cookies` INT(1) DEFAULT \'2\',
				  `urls_sav_progression` INT(10),
				  `id_unique` INT(11),
				  `codeGroovy` LONGTEXT,
				  `codeLiens` LONGTEXT,
				  `codeFinal` LONGTEXT,
				  `actif` INT(1),
				  
                  PRIMARY KEY (`id_concurrents`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_favoris` (
                  `id_favoris` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `id_shop` INT( 10 ) UNSIGNED NOT NULL,
				  `nom` VARCHAR( 40 ),
				  
                  PRIMARY KEY (`id_favoris`)
              ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_favoris_product` (
				  `id_favoris_product` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
				  `id_favoris` INT( 10 ) UNSIGNED NOT NULL,
				  `id_product` INT( 10 ) UNSIGNED NOT NULL,
				  
                  PRIMARY KEY (`id_favoris`,`id_product`),
  				  INDEX `id_favoris_product` (`id_favoris_product`),
  				  INDEX `id_favoris` (`id_favoris`),
				  INDEX `id_product` (`id_product`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_regles` (
                  `id_regles` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `nom` VARCHAR( 250 ),
				  `regle` TEXT,
				  
                  PRIMARY KEY (`id_regles`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_regles_association` (
                  `id_regles_association` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `id_regles` INT( 10 ) UNSIGNED NOT NULL,
				  `id_associations` INT( 10 ) UNSIGNED NOT NULL,
				  `arguments` TEXT,
				  
                  PRIMARY KEY (`id_regles_association`),
  				  INDEX `id_associations` (`id_associations`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 
				

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_historique` (
                  `id_historique` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `id_product` INT( 10 ) UNSIGNED NOT NULL,
				  `id_produits_etrangers` bigint(20) UNSIGNED NOT NULL,
                  `prix` VARCHAR( 20 ),
                  `date` INT(13),
				  
                  PRIMARY KEY (`id_historique`),
				  INDEX `id_product` (`id_product`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 		
				

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_proposition` (
				  `id_proposition` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `id_product` int(10) unsigned NOT NULL,
				  `prix` varchar(700) NOT NULL,
				  
				  PRIMARY KEY (`id_proposition`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 				
				

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_liens_prioritaires` (
				  `id_liens_prioritaires` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `lien` text NOT NULL,
				  `id_product_a_associer` int(10) unsigned NOT NULL,
				  `id_concurrents` int(11) unsigned NOT NULL,
				  
				  PRIMARY KEY (`id_liens_prioritaires`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 		
				

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name.'_proximite_regles` (
				  `id_proximite_regles` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `id_category` int(10) unsigned NOT NULL,
				  `id_favoris` int(10) unsigned NOT NULL,
				  `id_concurrents` int(10) unsigned NOT NULL,
				  `max_produits` int(3) unsigned NOT NULL,
				  `max_asso_pe` int(3) unsigned NOT NULL,
				  `max_suggestion` int(3) unsigned NOT NULL,
				  `min_proximite` decimal(8,3) NOT NULL,
				  `just_nvo` int(1) NOT NULL,
				  `utiliser_chatgpt` int(1) NOT NULL,
				  `aller_jusquau_chatgpt` int(10) unsigned NOT NULL,
				  `modele_chatgpt` varchar(70) NOT NULL,
				  PRIMARY KEY (`id_proximite_regles`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'; 	
		
		
		$sql[] = 'INSERT INTO `'._DB_PREFIX_.$this->name.'_proximite_regles` ( `id_category`, `id_favoris`, `id_concurrents`, `max_produits`, `min_proximite, modele_chatgpt`, `just_nvo`,max_asso_pe,utiliser_chatgpt,aller_jusquau_chatgpt,aller_jusquau_chatgpt) VALUES
( 0, 0, 0, 1, 500.000, 1,0,0,0,0,\'\')'; 	
		$sql[] = 'INSERT INTO `'._DB_PREFIX_.$this->name.'_proximite_regles` (`id_category`, `id_favoris`, `id_concurrents`, `max_produits`, `min_proximite`, `just_nvo`,max_asso_pe,max_suggestion,utiliser_chatgpt,aller_jusquau_chatgpt, modele_chatgpt) VALUES
( 0, 0, 0, 1, 10000.000, 1,0,0,0,0,\'\')'; 	
		$sql[] = 'INSERT INTO `'._DB_PREFIX_.$this->name.'_proximite_regles` ( `id_category`, `id_favoris`, `id_concurrents`, `max_produits`, `min_proximite`, `just_nvo`,max_asso_pe,max_suggestion,utiliser_chatgpt,aller_jusquau_chatgpt, modele_chatgpt) VALUES
( 0, 0, 0, 1, 100.000, 1,1,0,1,4, \'gpt-4o-mini\')'; 	


		$sql[] = '
DELIMITER //

CREATE PROCEDURE AddPrixPrecalculeColumn()
BEGIN
    DECLARE column_exists INT DEFAULT 0;

    -- Vérifier si la colonne existe
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
	WHERE table_name = \''._DB_PREFIX_.'product\'
	AND table_schema = \''._DB_NAME_.'\'
	AND column_name = \'prix_precalcule\';

    -- Définir la requête en fonction de l\'existence de la colonne
    IF column_exists = 0 THEN
        SET @s = "ALTER TABLE `ps_product` ADD `prix_precalcule` DECIMAL( 10,4 )";
    ELSE
        SET @s = "SELECT 1";
    END IF;

    -- Préparer et exécuter la requête
    PREPARE stmt FROM @s;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END//

DELIMITER ;

-- Appeler la procédure
CALL AddPrixPrecalculeColumn();';

        

		if(!Configuration::getGlobalValue($this->name.'_CLEFACCES'))
		{
			$clef=mt_rand(1,999999999);
			if(_PS_VERSION_ >= '1.5') Configuration::updateGlobalValue($this->name.'_CLEFACCES', $clef);
			else Configuration::updateValue($this->name.'_CLEFACCES', $clef);
		}







		//version test
		if(!Configuration::get($this->name.'_DATEINSTALL')) Configuration::updateGlobalValue($this->name.'_DATEINSTALL', time());
		
		
		
		
		if (parent::install() == false 
		
			|| !$this->registerHook( _PS_VERSION_ >= '1.5' ? 'displayBackOfficeTop' : 'backOfficeTop')
			
			|| Configuration::updateValue($this->name.'_NbParPage_corr', '20') == false
			|| Configuration::updateValue($this->name.'_NbParPage_comp', '30') == false
			|| Configuration::updateValue($this->name.'_JOURSMAJ', '0') == false
			|| Configuration::updateValue($this->name.'_MAJASSOCIATION', '1') == false
			|| Configuration::updateGlobalValue($this->name.'_NOMULTISQL', '1') == false
			
			|| !$this->runSql($sql)
		)
		  return false;
		  

		if($this->version2=='MEGA')
		{
			$nb_regles=Db::getInstance()->getValue('
					SELECT COUNT(id_regles)
					FROM `'._DB_PREFIX_.$this->name.'_regles`
					WHERE 1=1');
					
			if($nb_regles<6)
			{
				$sql[] = 'INSERT INTO `'._DB_PREFIX_.$this->name.'_regles` ( `nom`, `regle`) VALUES
	( \''.pSQL($this->l('Mail alert : price change (reference price)')).'\', \'if((float)$arguments[0]!=$competitorPrice )\r\n{\r\n  $alertMailGrouped=true;\r\n  $newArguments=$competitorPrice;\r\n}\'),
	( \''.pSQL($this->l('Mail alert : price between(limit max[default:my price],limit min[optionnal])')).'\', \'$limite_max= @$arguments[0] ? (float)@$arguments[0] : $oldPrice;\r\nif($competitorPrice<=$limite_max) $alertMailGrouped=true;\r\n\r\nif(@$arguments[1] && $competitorPrice<@$arguments[1]) $alertMailGrouped=false;\'),
	( \''.pSQL($this->l('Adjust on competitor lower(lower of X than the competitor[default:0],my price limit[optionnal])')).'\', \'if($competitorPrice-(float)@$arguments[0]<$oldPrice)\r\n{\r\n  @$newPrice = $competitorPrice-(float)@$arguments[0];\r\n  if($newPrice<(float)@$arguments[1]) $newPrice=(float)$arguments[1];\r\n}\'),
	( \''.pSQL($this->l('Adjust on competitor upper(my price limit[optionnal])')).'\', \'if($competitorPrice>$oldPrice)\r\n{\r\n  $newPrice = $competitorPrice;\r\n  if(@$arguments[0] && $newPrice > (float)@$arguments[0]) $newPrice = false;\r\n}\'),
	( \''.pSQL($this->l('Adjust always on competitor(lower of X than the competitor[default:0])')).'\', \'@$newPrice = $competitorPrice-(float)@$arguments[0];\'),
	( \''.pSQL($this->l('Adjust always on minimum competitors\' price (lower of X than the competitor[default:0])')).'\', \'$prices=getCompetitorsPrices();\r\nforeach($prices as $price)\r\n{\r\n   if(!@$newSpecificPrice || $newPrice>$price[\\\'price\\\']-(float)@$arguments[0]) @$newPrice=$price[\\\'price\\\']-(float)@$arguments[0];\r\n}\');
	';
			}
		}
		
		//$this->telechargerApplet();
		if(!is_readable(dirname(__FILE__).'/pages/communication.php')) $this->recursiveChmod(dirname(__FILE__),0777,0777);
		if(!is_readable(dirname(__FILE__).'/pages/communication.php')) $this->recursiveChmod(dirname(__FILE__),0644,0755);
		
		return true;
	}
	
	public function uninstall() {
        $sql = array();
	
		if(!$this->maj)
		{
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_produits_etrangers`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_associations`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_proximites`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_concurrents`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_favoris`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_favoris_product`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_regles`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_regles_association`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_liens_prioritaires`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_proximite_regles`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_proposition`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_historique`';
		}
		else
		{	
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_proximite_regles` ADD `modele_chatgpt` varchar(70) NOT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_proximite_regles` ADD `aller_jusquau_chatgpt` int(10) unsigned NOT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_proximite_regles` ADD `utiliser_chatgpt` int(1) NOT NULL');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'product` ADD `prix_precalcule` DECIMAL( 10,4 )');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `urls_sav_progression` INT(10) NOT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `suivi_cookies` INT(1) DEFAULT \'2\'');
			
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_produits_etrangers` ADD `date_ajout` int(13) DEFAULT NULL');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `id_unique` INT(11) DEFAULT NULL');
			$scripts = Db::getInstance()->executeS('
			SELECT id_concurrents
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE id_unique IS NULL OR id_unique=\'\'');
			foreach($scripts as $script) Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.$this->name.'_concurrents` SET `id_unique`=\''.mt_rand(1,999999).'\' WHERE id_concurrents='.(int)$script['id_concurrents']);

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_associations` ADD `date_ajout` int(13) DEFAULT NULL');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_associations` CHANGE `automatique` `automatique` TINYINT(1) NOT NULL DEFAULT \'0\'; ');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_regles_association`  ADD INDEX ( `id_associations` )');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_produits_etrangers` ADD `additionalInfos` varchar(255) DEFAULT NULL');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `codeLiens` LONGTEXT');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `codeFinal` LONGTEXT');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'product` ADD prix_precalcule DECIMAL( 10,4 )');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_produits_etrangers`  ADD INDEX ( `id_concurrents` )');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_associations`  ADD INDEX ( `id_produits_etrangers` )');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_associations`  ADD INDEX ( `id_product` )');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_favoris_product`  ADD INDEX ( `id_favoris` )');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_favoris_product`  ADD INDEX ( `id_product` )');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_historique`  ADD INDEX ( `id_product` )');
			
			
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_associations` ADD `automatique` TINYINT(1) NOT NULL');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_produits_etrangers` ADD `quantite` int(6) DEFAULT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_produits_etrangers` ADD `reference` varchar(128) DEFAULT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_produits_etrangers` ADD `marketplace` varchar(30) DEFAULT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_produits_etrangers` ADD `image` TEXT DEFAULT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `nb_taches` INT(4) NOT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `regexQuantite` text');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `masqueQuantite` INT(2) NOT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `regexImage` text');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `regexRef` text');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `masqueImage` INT(2) NOT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `masqueRef` INT(2) NOT NULL');

			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_proximites` ADD `id_concurrents` int(10) unsigned NOT NULL');			
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_associations` ADD `seuil_programmer` TEXT');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `regexUrlBloquer` TEXT');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_regles_association` ADD `arguments` TEXT');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_favoris` ADD `id_shop` INT( 10 ) NOT NULL');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `ordre` DECIMAL( 7,2 )');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `codeGroovy` LONGTEXT');
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$this->name.'_concurrents` ADD `actif` INT(1) NOT NULL');
			
		}

		//Configuration::deleteByName($this->name.'_CLEFACCES');
		
		if (!parent::uninstall() OR
            !$this->runSql($sql) 
        ) {
            return FALSE;
        }

        return TRUE;
    }

	
	public function runSql($sql) {
        foreach ($sql as $s) {
            error_log(print_r($s,true));
			if (!Db::getInstance()->Execute($s)){
				return FALSE;
			}
        }
        
        return TRUE;
    }
	
	public function recursiveChmod($path, $filePerm=0644, $dirPerm=0755) {
		// Check if the path exists
		if (!file_exists($path)) {
			return(false);
		}
		
		// See whether this is a file
		if (is_file($path)) {
			// Chmod the file with our given filepermissions
			chmod($path, $filePerm);
		
		// If this is a directory...
		} elseif (is_dir($path)) {
			// Then get an array of the contents
			$foldersAndFiles = scandir($path);
			
			// Remove "." and ".." from the list
			$entries = array_slice($foldersAndFiles, 2);
			
			// Parse every result...
			foreach ($entries as $entry) {
				// And call this function again recursively, with the same permissions
				$this->recursiveChmod($path."/".$entry, $filePerm, $dirPerm);
			}
			
			// When we are done with the contents of the directory, we chmod the directory itself
			chmod($path, $dirPerm);
		}
		
		// Everything seemed to work out well, return true
		return(true);
	}	
	
	
	public function findDebut($txt,$aTrouver)
	{
		if(  preg_match("#^".$aTrouver."([0-9]+)$#isU",$txt,$sub) )
		{
			return $sub[1];
		}
		return false;
	}

	public function telechargerApplet()
	{
		@mkdir(dirname(__FILE__)."/",0777);
		
	   $this->telechargerFichierApplet('run.jar');
 	}
	
	public function telechargerFichierApplet($fichier)
	{
		$f = fopen("https://www.idia-tech.com/pricestracker-crawler/".$fichier,"r" );
	   $f2 = fopen(dirname(__FILE__)."/".$fichier,"w+" );
	   while ($r=fread($f,8192) ) {
		 fwrite($f2,$r);
	   }
	   fclose($f2);
	   fclose($f); 
	}



	//configuration du module
	public function getContent()
	{
		global $smarty,$cookie;
		
		$lien=$this->getLien();
		$urlDebut=$this->getURLdebut();
	  	$smarty->assign('lien', $lien );
	  	$smarty->assign('urlDebut', $urlDebut );
		$smarty->assign('version2', $this->version2 );
		$smarty->assign('ps_version', _PS_VERSION_ );
		
		// Récupérer la langue de l'utilisateur
		$languageCode = Context::getContext()->language->iso_code;
		
		// Définir les codes de langue français acceptés
		$frenchLanguages = array('fr', 'fr-ch', 'fr-be', 'fr-ca');
		
		$is_francais = false;
		// Vérifier si la langue de l'utilisateur est l'une des langues françaises
		if (in_array(strtolower($languageCode), $frenchLanguages)) $is_francais = true;
		
	  	$smarty->assign('is_francais', $is_francais );
		
		if(Configuration::getGlobalValue($this->name.'_DATEINSTALL')==-1)
		{
			echo $this->l('Error: please contact us').' ('.Configuration::getGlobalValue($this->name.'_DATEINSTALL').')'; die;
		}
		if($this->modeTest && Configuration::getGlobalValue($this->name.'_DATEINSTALL')+3600*24*30*2<time())
		{
			mail('contact@idia-tech.com','Date limite de la version test de PricesTracker atteinte',"Site : ".$_SERVER['HTTP_HOST']."\nClef : ".(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') )."\nDate d'installation : ".date("d/m/Y",Configuration::getGlobalValue($this->name.'_DATEINSTALL')));
			echo $this->l('You have reached the deadline for the trial version').' ('.date("d/m/Y",Configuration::getGlobalValue($this->name.'_DATEINSTALL')).' - '.Configuration::getGlobalValue($this->name.'_DATEINSTALL').')'; die;
		}


		if(Tools::isSubmit('telechargerApplet')) $this->telechargerApplet();

/*
		//verif existance applet
		if(!is_readable(dirname(__FILE__).'/applet/applet.jar'))
		{
			$txt= $this->l('Error PricesTracker : bad installation').'<br>';
			$monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
			$txt.='<strong><a href="'.$monUrl.'&telechargerApplet">'. $this->l(' click here to add files not installed (long operation)').'</a></strong>';
			$txt.= '<br><br><br><br><br>';
			return $txt;
		}
		*/
		
		if(Tools::isSubmit('voirUrl'))
		{
			echo __PS_BASE_URI__ .'///';
			$sites=Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'shop_url`
			WHERE 1=1');
			print_r($sites);
			die;
		}
		if(Tools::isSubmit('setUrlSite'))
		{
			Db::getInstance()->execute("
			UPDATE `"._DB_PREFIX_."shop_url`
			SET domain='vps203907.ovh.net',domain_ssl='vps203907.ovh.net'
			WHERE id_shop=1");
			die;
		}

		$nbProduitsTest=max( $this->nbProduitsTest ,count(explode(',', Configuration::getGlobalValue('pricestracker_MODETEST') ? Configuration::getGlobalValue('pricestracker_MODETEST') : $this->modeTest)));
		
		if (Tools::isSubmit('okJNLP')) //analyse
		{
			header("Content-type: application/x-java-jnlp-file"); 
			header("Content-disposition: attachment; filename=\"analysis.jnlp\"");

			$lienApplet= (Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_).__PS_BASE_URI__.'modules/pricestracker/pages/communication.php?clef='.(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') ) ;
			$lienArchive= (Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_).__PS_BASE_URI__.'modules/pricestracker/applet/' ;
			$forcerMaj= $this->version2!='Silver'?( (int)Tools::getValue('forcerMaj') ):1 ;		//forçage pour Silver
			$pasRapprochementsTextes= $this->version2!='Silver'?( (int)Tools::getValue('pasRapprochementsTextes') ):1 ;		//forçage pour Silver

			$seulementProximite= $this->version2!='Silver'?( (int)Tools::getValue('seulementProximite') ):0 ;		//forçage pour Silver
			$exclureNomIdentique= $this->version2!='Silver'?( (int)Tools::getValue('exclureNomIdentique') ):0 ;		//forçage pour Silver
			$historique= $this->version2!='Silver'?( (int)Tools::getValue('historique') ):0 ;		//forçage pour Silver
			$exectuerRegles= $this->version2!='Silver'?( (int)Tools::getValue('exectuerRegles') ):0 ;		//forçage pour Silver
			$logFichier= (int)Tools::getValue('logFichier') ;

			include('views/jnlp.php');
			die;
		}
		elseif (Tools::isSubmit('o')) //operations et liens
		{
			$lienBase=(_PS_VERSION_ < '1.5'?('http://'.$_SERVER['HTTP_HOST'].'/'):$shop->getBaseURL()).'modules/pricestracker/pages';
			echo"Effacer les produits d'un concurrent : $lienBase/operations.php?effacerConcurrent&id=<br>
			Prolonger l'essai : $lienBase/operations.php?prolongerEssai<br>
			Effacer les associations : $lienBase/operations.php?effacerAssociations";
			die;
		}
		elseif (Tools::isSubmit('analyse')) //analyse
		{
			if(Tools::isSubmit('ajIPmaintenance'))
			{
				Configuration::updateValue('PS_MAINTENANCE_IP',  Configuration::get('PS_MAINTENANCE_IP').','.Tools::getRemoteAddr()  );
			}
			
			$entrees=false;
			if(!Tools::getValue('forcerMaj'))
			{
				$entrees = 'SELECT id_produits_etrangers
				FROM `'._DB_PREFIX_.$this->name.'_produits_etrangers`
				WHERE 1=1';
				$entrees=(int)Db::getInstance()->getValue($entrees);
			}
			$smarty->assign('aucuneEntree', $entrees ); //$entrees?false:true

			$shop = Context::getContext()->shop;
			//$shop = new Shop((int)Configuration::get('PS_SHOP_DEFAULT'));

			$domaineApplet=(_PS_VERSION_ < '1.5'?(($this->isSecure()?'https://':'http://').$_SERVER['HTTP_HOST'].'/'):$shop->getBaseURL());
			if(strpos($domaineApplet,$_SERVER['HTTP_HOST'])===FALSE) $domaineApplet=($this->isSecure()?'https://':'http://').$_SERVER['HTTP_HOST'].'/';
			if($this->isSecure()) $domaineApplet=str_replace('http://','https://',$domaineApplet);

			$is_mac=false;
			if(strpos(Tools::strtolower($_SERVER['HTTP_USER_AGENT']),'mac')!==FALSE) $is_mac=true;
			
			$smarty->assign('lienApplet', $domaineApplet.'modules/pricestracker/pages/communication.php?clef='.(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') ) );
			$smarty->assign('lienArchive', $domaineApplet.'modules/pricestracker/applet/' );
			$smarty->assign('isMac', $is_mac );
			$smarty->assign('okApplet', Tools::isSubmit('okApplet') );
			$smarty->assign('testExtraction', Tools::getValue('testExtraction') );
			$smarty->assign('forcerMaj', $this->version2!='Silver'?( (int)Tools::getValue('forcerMaj') ):1 );		//forçage pour Silver
			$smarty->assign('pasRapprochementsTextes', $this->version2!='Silver'?( (int)Tools::getValue('pasRapprochementsTextes') ):1 );		//forçage pour Silver
			$smarty->assign('seulementProximite', $this->version2!='Silver'?( (int)Tools::getValue('seulementProximite') ):0 );		//forçage pour Silver
			$smarty->assign('exclureNomIdentique', $this->version2!='Silver'?( Tools::isSubmit('okApplet')?((int)Tools::getValue('exclureNomIdentique')):1 ):0 );		//forçage pour Silver
			$smarty->assign('historique', $this->version2!='Silver'?( Tools::isSubmit('okApplet')?((int)Tools::getValue('historique')):1 ):0 );		//forçage pour Silver
			$smarty->assign('exectuerRegles', $this->version2!='Silver'?( Tools::isSubmit('okApplet')?((int)Tools::getValue('exectuerRegles')):1 ):0 );		//forçage pour Silver
			$smarty->assign('logFichier', Tools::isSubmit('okApplet')?((int)Tools::getValue('logFichier')):1 );
			$smarty->assign('version2', $this->version2 );
			$smarty->assign('version', $this->version2 );
			$smarty->assign('stats', $this->stats );
			$smarty->assign('modeTest', $this->modeTest );

			$this->context->controller->addJS( $this->_path .'js/youtubepopup.js');
			return $this->display(__FILE__, 'views/analyse.tpl');
		}
		elseif (Tools::isSubmit('bat')) //bat
		{
			ob_end_clean();
			$is_mac=false;
			if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'mac')!==FALSE)
			{
				$is_mac=true;
				header("Content-type: application/x-sh"); 
				header("Content-disposition: attachment; filename=\"LAUNCH_PricesTracker".preg_replace("#\s+#isU","-",(Tools::getValue('testExtraction')?'_test':'')).".sh\"");
			}
			else
			{
				header("Content-type: application/bat"); 
				header("Content-disposition: attachment; filename=\"LAUNCH_PricesTracker".(Tools::getValue('testExtraction')?'_test':'').".bat\"");
			}
			
			$shop = new Shop((int)Configuration::get('PS_SHOP_DEFAULT'));
			
			$arguments = Tools::getValue('argument').',pricestracker';

			$testExtraction='';
			if(Tools::getValue('testExtraction'))
			{
				$testExtraction=' '.str_replace('---PLUS==--==','+',Tools::getValue('testExtraction'));
				
				$user_agent = getenv("HTTP_USER_AGENT");
				
				if(strpos($user_agent, "Mac") === FALSE)
				{
					$testExtraction=str_replace('^','^^',$testExtraction);
					$testExtraction=str_replace('&','^&',$testExtraction);
					$testExtraction=str_replace('<','^<',$testExtraction);
					$testExtraction=str_replace('>','^>',$testExtraction);
					$testExtraction=str_replace('|','^|',$testExtraction);
					$testExtraction=str_replace('`','^`',$testExtraction);
					$testExtraction=str_replace('%','%%',$testExtraction);
					$testExtraction=str_replace(',','^,',$testExtraction);
					$testExtraction=str_replace(';','^;',$testExtraction);
					$testExtraction=str_replace('=','^=',$testExtraction);
					$testExtraction=str_replace('(','^(',$testExtraction);
					$testExtraction=str_replace(')','^)',$testExtraction);
					//$testExtraction=str_replace('!','^^!',$testExtraction);
					$testExtraction=str_replace('"','\"',$testExtraction);
					//$testExtraction=str_replace('\\','\\\\',$testExtraction); //les backslashes posent encore problème
					//$testExtraction=str_replace('[','\[',$testExtraction);
					//$testExtraction=str_replace(']','\]',$testExtraction);
					//$testExtraction=str_replace('?','\"',$testExtraction);
					//$testExtraction=str_replace('.','\.',$testExtraction);
					//$testExtraction=str_replace('*','\*',$testExtraction);
					//$testExtraction=str_replace('?','\?',$testExtraction);
				}
				else $testExtraction=" '".str_replace('---PLUS==--==','+',Tools::getValue('testExtraction'))."'";

			}
			elseif($arguments)
			{
				if(strpos($user_agent, "Mac") !== FALSE) $testExtraction=' \''.$arguments."'";
				else $testExtraction=' '.$arguments;
			}

echo ($is_mac?'/usr/bin/':'start /MIN ').'java -Djdk.http.auth.tunneling.disabledSchemes="" -Djdk.http.auth.proxying.disabledSchemes="" -Dsun.net.http.allowRestrictedHeaders=true -Dhttps.protocols="TLSv1,TLSv1.1,TLSv1.2,TLSv1.3" -Djdk.tls.client.protocols="TLSv1,TLSv1.1,TLSv1.2,TLSv1.3" -DontCompileHugeMethods -Dfile.encoding=UTF-8 -Xms1g -jar run.jar '.(_PS_VERSION_ < '1.5'?('http://'.$_SERVER['HTTP_HOST']):(Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_)).__PS_BASE_URI__.'modules/pricestracker/pages/communication.php?clef='.(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') ).$testExtraction;

			die;
		}	
		
		elseif (Tools::isSubmit('grl')) //analyse
		{
			
			$nomConcurrent=false;
			$testExtraction='';
			if(Tools::getValue('testExtraction'))
			{
				$testExtraction=str_replace('---PLUS----','+',Tools::getValue('testExtraction'));
				preg_match("#^-?([0-9]+)__________#isU",Tools::getValue('testExtraction'),$subs);
				if($subs[1])
				{
					$id_concurrent=$subs[1];
					$nomConcurrent = Db::getInstance()->getValue('SELECT nom
						FROM `'._DB_PREFIX_.$this->name.'_concurrents`
						WHERE `id_concurrents` = '.$id_concurrent
					);
				}
			}
			
			header('Content-Description: File Transfer');
			header("Content-type: text/grl"); 
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header("Content-disposition: attachment; filename=\"".($testExtraction?'Test on ':'Launch scripts of ')." ".preg_replace("#[^0-9A-Za-z]#isU",'_',($nomConcurrent?$nomConcurrent:Configuration::get('PS_SHOP_NAME'))).".grl\"");
				
			
			$is_mac=false;
			if(strpos(Tools::strtolower($_SERVER['HTTP_USER_AGENT']),'mac')!==FALSE)
			{
				$is_mac=true;
			}
			

			$shop = new Shop((int)Configuration::get('PS_SHOP_DEFAULT'));
			
			$lien_communication = (_PS_VERSION_ < '1.5'?('http://'.$_SERVER['HTTP_HOST']):(Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_)).__PS_BASE_URI__.'modules/pricestracker/pages/communication.php?clef='.(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') );

echo $lien_communication."\n".
$testExtraction."\npricestracker,identity=pricestracker,".Tools::getValue('argument')."\n".Configuration::get('PS_SHOP_NAME');

			die;
		}
		elseif (Tools::isSubmit('grs')) //analyse
		{
			header('Content-Description: File Transfer');
			header("Content-Disposition: attachment; filename=\"Interface_".preg_replace("#\s+#isU","-",preg_replace("#[^0-9A-Za-z]#isU",'_',Configuration::get('PS_SHOP_NAME')))."_on_PricesTracker_Developper.grs\"");
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header("Content-type: text/grs"); 
			
			$is_mac=false;
			if(strpos(Tools::strtolower($_SERVER['HTTP_USER_AGENT']),'mac')!==FALSE)
			{
				$is_mac=true;
			}

			$shop = new Shop((int)Configuration::get('PS_SHOP_DEFAULT'));
			
			$url_backoffice=($_SERVER['HTTPS']?'https://':'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$url_backoffice=preg_replace("#/[^/]*$#isU","/",$url_backoffice);
			
			$lien_communication = (_PS_VERSION_ < '1.5'?('http://'.$_SERVER['HTTP_HOST']):(Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_)).__PS_BASE_URI__.'modules/pricestracker/pages/communication.php?clef='.(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') );

echo $lien_communication."\n".
Configuration::get('PS_SHOP_NAME')." (PricesTracker)\n".$url_backoffice;

			die;
		}	
		elseif (Tools::isSubmit('stats') && $this->stats) //analyse
		{
			ob_end_clean();
			
			include('pages/stats.php');
			die;
		}
		elseif (Tools::isSubmit('statsAjax') && $this->stats) //analyse
		{
			ob_end_clean();
			
			include('pages/statsAjax.php');
			die;
		}
		elseif (Tools::isSubmit('tablCat') && $this->version2=='MEGA') //Tableaux de bord
		{
			//langue
			$langue=(int)Configuration::get('PS_LANG_DEFAULT');
						
			//concurrents
			$concurrents = Db::getInstance()->executeS('
			SELECT nom,id_concurrents
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE 1=1');
			$tabConcurrents=array();
			foreach($concurrents as $c)
			{
				$tabConcurrents[$c['id_concurrents']] = $c['nom'];
			}
			
			//catégorie
			$id_category=(int)Tools::getValue('id_category');
			
			//filtre concurrents
			$id_concurrents=array();
			$tab_concurrents=preg_split("#[^0-9\-]+#is",Tools::getValue('id_concurrents'));
			foreach($tab_concurrents as $v)
			{
				if($v && $v!=-1) $id_concurrents[]=$v;
			}
			$id_concurrents=array_unique($id_concurrents);

			//concurrents filtrés
			$concurrents_filtrer = Db::getInstance()->executeS('
			SELECT nom,id_concurrents
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE '.($id_concurrents?'id_concurrents IN ('.implode(',',$id_concurrents).')':'1=1'));
			$tabConcurrents_filtrer=array();
			foreach($concurrents_filtrer as $c)
			{
				$tabConcurrents_filtrer[$c['id_concurrents']] = $c['nom'];
			}

			//produits de la catégorie
			if($id_category) $category=new Category($id_category);
			else $category=Category::getRootCategory();
			
			$cat_inf=Category::getChildren($category->id,$langue);
			if(!$cat_inf)
			{
				header('Location: '.$lien.'&comparaison&id_category='.$category->id.'&id_concurrents='.implode(',',$id_concurrents));
				die;
			}
			
			$liste_cat=array();
			$cat_pe=array();
			$cat_p=array();
			foreach($cat_inf as $cat_i)
			{
				$category_inf=new Category($cat_i['id_category']);
				$liste_cat[]=$category_inf;
				
				@$cat_pe[ $cat_i['id_category'] ]=array();
				@$cat_p[ $cat_i['id_category'] ]=array();
			
				$ids_produits='-1';
				$produits_asso = Db::getInstance()->executeS('
				SELECT id_product
				FROM `'._DB_PREFIX_.'category` C,`'._DB_PREFIX_.'category_product` CP
				WHERE C.id_category=CP.id_category AND nleft>='.$category_inf->nleft.' AND nright<='.$category_inf->nright.
				' GROUP BY id_product');
				foreach($produits_asso as $prod) $ids_produits.=','.$prod['id_product'];
				
				//recherche des produits associés
				$produitsEtrangers = Db::getInstance()->executeS('
				SELECT A.id_product AS id_product,A.id_produits_etrangers,A.seuil_programmer,PE.id_concurrents,PE.prix
				FROM `'._DB_PREFIX_.$this->name.'_associations` A ,`'._DB_PREFIX_.$this->name.'_produits_etrangers` PE
				WHERE A.id_product IN ('.$ids_produits.') AND PE.id_produits_etrangers=A.id_produits_etrangers '.($id_concurrents?(' AND PE.id_concurrents IN ('.implode(',',$id_concurrents).')'):'')
				.' GROUP BY A.id_associations,PE.id_concurrents');
				
				$isPrixGros=Configuration::get('pricestracker_PRIXGROS');
				$isHT=Configuration::get('pricestracker_HT');
				$isPrixBase=Configuration::get('pricestracker_PRIXBASE');
	
				foreach($produitsEtrangers as $produitEtranger)
				{
					$produit=new Product($produitEtranger['id_product']);
					@$cat_pe[ $cat_i['id_category'] ][ $produitEtranger['id_concurrents'] ]+=$produitEtranger['prix'];
	
					if($isPrixBase) $monPrix=$produit->price;
					else
					{
						if($isPrixGros)
						{
							if($isHT) $monPrix=$produit->getPriceMin(false);
							else $monPrix=$produit->getPriceMin();
						}
						else
						{
							if($isHT) $monPrix=$produit->getPrice(false);
							else $monPrix=$produit->getPrice();
						}
					}
	
					@$cat_p[ $cat_i['id_category'] ][ $produitEtranger['id_concurrents'] ]+=$monPrix;
				}
			}
			
			$smarty->assign('id_category', (int)Tools::getValue('id_category') );
			$smarty->assign('get_id_concurrents', Tools::getValue('id_concurrents') );
			$smarty->assign('id_concurrents', $id_concurrents );
			$smarty->assign('langue', $langue );
			$smarty->assign('concurrents', $tabConcurrents );
			$smarty->assign('concurrents_filtrer', $tabConcurrents_filtrer );
			//print_r($cat_p);print_r($cat_pe);die;
			$smarty->assign('liste_cat', $liste_cat );
			$smarty->assign('cat_p', $cat_p );
			$smarty->assign('cat_pe', $cat_pe );

			$smarty->error_reporting = 0;

			return $this->display(__FILE__, 'views/tablCat.tpl');
		}
		//DEBUG : si vide ajouter prix_precalcule DECIMAL 10,4 à la table product
		elseif (Tools::isSubmit('tableaubord') && $this->version2=='MEGA') //Tableaux de bord
		{
			ini_set('memory_limit', '50G');
			ini_set('suhosin.memory_limit', '50G');
			
			//langue
			$langue=(int)Configuration::get('PS_LANG_DEFAULT');
						
			//concurrents
			$concurrents = Db::getInstance()->executeS('
			SELECT nom,id_concurrents
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE 1=1');
			$tabConcurrents=array();
			foreach($concurrents as $c)
			{
				$tabConcurrents[$c['id_concurrents']] = $c['nom'];
			}

			//marques
			$marques_db = Manufacturer::getManufacturers();
			$marques=array();
			foreach($marques_db as $m)
			{
				$marques[$m['id_manufacturer']] = $m['name'];
			}
			
			//filtre catégorie
			$id_category=(int)Tools::getValue('id_category');
			
			//filtre concurrents
			$id_concurrents=array();
			$tab_concurrents=preg_split("#[^0-9\-]+#is",Tools::getValue('id_concurrents'));
			foreach($tab_concurrents as $v)
			{
				if($v && $v!=-1) $id_concurrents[]=$v;
			}
			$id_concurrents=array_unique($id_concurrents);
			
						
			//concurrents filtrer
			$concurrents_filtrer = Db::getInstance()->executeS('
			SELECT nom,id_concurrents
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE 1=1'.($id_concurrents?' AND id_concurrents IN ('.implode(',',$id_concurrents).')':''));
			$tabConcurrents_filtrer=array();
			foreach($concurrents_filtrer as $c)
			{
				$tabConcurrents_filtrer[$c['id_concurrents']] = $c['nom'];
			}
		
			$where='';
			$from='';
			//where favoris
			if(Tools::getValue('fav') && $this->version2!='Silver')
			{
				//favoris															
				if(Tools::getValue('fav')==-1)										
				{
					$favorisProduct = Db::getInstance()->executeS('
					SELECT DISTINCT id_product
					FROM `'._DB_PREFIX_.$this->name.'_associations`
					WHERE 1=1');													
				}
				else																
				{																	
					$favorisProduct = Db::getInstance()->executeS('
					SELECT id_product
					FROM `'._DB_PREFIX_.$this->name.'_favoris_product`
					WHERE id_favoris='.(int)Tools::getValue('fav'));				
				}
																					
				$whereIn='-1';														
				foreach($favorisProduct as $fav) $whereIn.=','.$fav['id_product'];

				$where.=' AND P.id_product IN ( '.$whereIn.' )';
			}
			
			//where Shop
			$id_shop=0;
			if( _PS_VERSION_ >= '1.5' && $cookie->shopContext && preg_match("#s-([0-9]+)#isU",$cookie->shopContext,$subShop))
			{
				$id_shop=$subShop[1];
				$where.=' AND PS.id_shop='.(int)$id_shop;
			}
			
			//where marque
			$id_manufacturer=(int)Tools::getValue('id_manufacturer');
			if($id_manufacturer) { $where.=' AND P.id_manufacturer='.$id_manufacturer; }
			
			//where catégorie
			if($id_category) { $where.=' AND CP.id_category='.$id_category; $from.=' JOIN `'._DB_PREFIX_.'category_product` CP ON P.id_product=CP.id_product'; }
			
			//where produit
			if(Tools::isSubmit('id_product')) { $where.=' AND P.id_product='.(int)Tools::getValue('id_product'); }
			
			//where concurrents
			if($id_concurrents) { $where.=' AND SPE.id_concurrents IN ('.implode(',',$id_concurrents).')'; $from.=' JOIN `'._DB_PREFIX_.'pricestracker_associations` SA ON P.id_product=SA.id_product JOIN `'._DB_PREFIX_.'pricestracker_produits_etrangers` SPE ON SPE.id_produits_etrangers=SA.id_produits_etrangers'; }
			
			//multiboutique
			if( _PS_VERSION_ >= '1.5') {	$from.=' JOIN `'._DB_PREFIX_.'product_shop` PS ON P.id_product=PS.id_product';	}
		
			//recherche produits
			$produits = Db::getInstance()->executeS('
			SELECT P.id_product AS id_product,P.prix_precalcule,P.wholesale_price,Sa.sale_nbr AS nb_ventes
			FROM `'._DB_PREFIX_.'product_lang` L JOIN `'._DB_PREFIX_.'product` P ON L.id_product=P.id_product '.$from.' LEFT JOIN '._DB_PREFIX_.'product_sale Sa ON Sa.id_product=P.id_product
			WHERE id_lang='.$langue.'  '.$where.'
			GROUP BY P.id_product'
			);
			

			$isPrixGros=Configuration::get('pricestracker_PRIXGROS');
			$isHT=Configuration::get('pricestracker_HT');
			$isPrixBase=Configuration::get('pricestracker_PRIXBASE');

			//mise en tableau des produits
			$tabProduits=array();
			$tabProduitsEtrangers=array();
			foreach($produits as $p)
			{
				//recherche des produits associés
				$produitsEtrangers = Db::getInstance()->executeS('
				SELECT A.id_produits_etrangers,A.id_associations,A.seuil_programmer,PE.prix,PE.id_concurrents
				FROM `'._DB_PREFIX_.$this->name.'_associations` A JOIN `'._DB_PREFIX_.$this->name.'_produits_etrangers` PE ON PE.id_produits_etrangers=A.id_produits_etrangers
				WHERE A.id_product='.$p['id_product'].'   '.($id_concurrents?(' AND PE.id_concurrents IN ('.implode(',',$id_concurrents).')'):'')
				);
				
				$tabProduitsEtrangers[$p['id_product']]=array();
				
				foreach($produitsEtrangers as $pe)
				{
					$nvlElement=array(
						'seuil' => $pe['seuil_programmer']?eval($pe['seuil_programmer']):false,
						'id_produits_etrangers' => $pe['id_produits_etrangers'],
						'id_associations' => $pe['id_associations'],
						'prix' => $pe['prix'],
						'id_concurrents' => $pe['id_concurrents'],
					);
					
					$monPrix=$p['prix_precalcule'];
					/*if($isPrixBase) $monPrix=$product->price;
					else
					{
						if($isPrixGros)
						{
							if($isHT) $monPrix=$product->getPriceMin(false);
							else $monPrix=$product->getPriceMin();
						}
						else
						{
							if($isHT) $monPrix=$product->getPrice(false);
							else $monPrix=$product->getPrice();
						}
					}*/

					$difference=$monPrix-$nvlElement['prix'];
					if($monPrix!=0) $taux_diff=100.*$difference/$monPrix;
					else $taux_diff=0;
					
					$ne_pas_prendre=false;
					if(Tools::getValue('type_diff')=='devise' && ( (Tools::getValue('diff_min')!='' && $difference<str_replace(',','.',(Tools::getValue('diff_min')<Tools::getValue('diff_max') || Tools::getValue('diff_min')=='' || Tools::getValue('diff_max')=='')?Tools::getValue('diff_min'):Tools::getValue('diff_max'))) || (Tools::getValue('diff_max')!='' && $difference>str_replace(',','.',(Tools::getValue('diff_min')<Tools::getValue('diff_max') || Tools::getValue('diff_min')=='' || Tools::getValue('diff_max')=='')?Tools::getValue('diff_max'):Tools::getValue('diff_min'))) )) $ne_pas_prendre=true;
					if(Tools::getValue('type_diff')=='pourc' && ( (Tools::getValue('diff_min')!='' && $taux_diff<str_replace(',','.',(Tools::getValue('diff_min')<Tools::getValue('diff_max') || Tools::getValue('diff_min')=='' || Tools::getValue('diff_max')=='')?Tools::getValue('diff_min'):Tools::getValue('diff_max'))) || (Tools::getValue('diff_max')!='' && $taux_diff>str_replace(',','.',(Tools::getValue('diff_min')<Tools::getValue('diff_max') || Tools::getValue('diff_min')=='' || Tools::getValue('diff_max')=='')?Tools::getValue('diff_max'):Tools::getValue('diff_min'))) )) $ne_pas_prendre=true;
					if($monPrix<str_replace(',','.',Tools::getValue('prix_min')) || (Tools::getValue('prix_max')!='' && $monPrix>str_replace(',','.',Tools::getValue('prix_max'))) ) $ne_pas_prendre=true;
					
					
					if(!$ne_pas_prendre) $tabProduitsEtrangers[$p['id_product']][]=$nvlElement;
				}

				if($tabProduitsEtrangers[$p['id_product']]) $tabProduits[ $p['id_product'] ]=$p;
			}
			//print_r($tabProduitsEtrangers);
			
			//favoris
			$favoris = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.$this->name.'_favoris`
			WHERE id_shop=0 OR id_shop='.(int)$id_shop);
			
			if( _PS_VERSION_ >= '1.6')
			{
	
				//catégories
				$currentIndex = '&id_category='.(int)$id_category;
				if (!$id_category)
					$id_category = 1;
	
				// arbre catégories
				$tree = new HelperTreeCategories('categories-tree', $this->l('Filter by category'));
				$tree->setAttribute('is_category_filter', (bool)$id_category)
					->setAttribute('base_url', preg_replace('#&id_category=[0-9]*#', '', $currentIndex).'&token=')
					->setInputName('id-category')
					->setSelectedCategories(array((int)$id_category));
				$smarty->assign('category_tree', $tree->render());

			}
			
			$plusOuMoinsCher=array();
			$ca=array();
			$num_marge=array();
			$somme_prix=array();
			$nb_produits=array();
			$nb_produits_total=0;
			$id_product_associes=array();
			foreach($tabProduitsEtrangers as $id_product=>$pe_groupe)
			{
				foreach($pe_groupe as $pe)
				{
					//prix de mon produit
					$produit=$tabProduits[$id_product];
					
					$monPrix=$produit['prix_precalcule'];
/*					if($isPrixBase) $monPrix=$produit->price;
					else
					{
						if($isPrixGros)
						{
							if($isHT) $monPrix=$produit->getPriceMin(false);
							else $monPrix=$produit->getPriceMin();
						}
						else
						{
							if($isHT) $monPrix=$produit->getPrice(false);
							else $monPrix=$produit->getPrice();
						}
					}
					*/
					
					if(!$monPrix)
					{

						$prixMonProduit=Product::getPriceStatic($id_product,true);
						if($isPrixBase)
						{
							$produit_ob=new Product($id_product);
							$monPrix=$produit_ob->price;
						}
						else
						{
							$produit_ob=new Product($id_product);
							if($isPrixGros)
							{
								if($isHT) $monPrix=$produit_ob->getPriceMin(false);
								else $monPrix=$produit_ob->getPriceMin();
							}
							else
							{
								if($isHT) $monPrix=$produit_ob->getPrice(false);
								else $monPrix=$produit_ob->getPrice();
							}
						}
						
						$monPrix=$prixMonProduit;
						Db::getInstance()->update(
							'product',
							array(
								'prix_precalcule'=>$prixMonProduit,
							), 'id_product = '.$id_product	
						);
					}
			

					if($monPrix!=0)
					{
						$difference=100*($monPrix-$pe['prix'])/$monPrix;
						//plus ou moins cher
						
						if($difference<-20) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][-20]++; @$plusOuMoinsCher[ 'tous' ][-20]++;  }
						elseif($difference<-15) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][-15]++; @$plusOuMoinsCher[ 'tous' ][-15]++;  }
						elseif($difference<-10) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][-10]++; @$plusOuMoinsCher[ 'tous' ][-10]++;  }
						elseif($difference<-5) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][-5]++; @$plusOuMoinsCher[ 'tous' ][-5]++;  }
						elseif($difference<-2) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][-2]++; @$plusOuMoinsCher[ 'tous' ][-2]++;  }
						elseif($difference<0) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][-1]++; @$plusOuMoinsCher[ 'tous' ][-1]++;  }
						elseif($difference==0) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][0]++; @$plusOuMoinsCher[ 'tous' ][0]++;  }
						elseif($difference<2) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][1]++; @$plusOuMoinsCher[ 'tous' ][1]++;  }
						elseif($difference<5) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][2]++; @$plusOuMoinsCher[ 'tous' ][2]++;  }
						elseif($difference<10) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][5]++; @$plusOuMoinsCher[ 'tous' ][5]++;  }
						elseif($difference<15) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][10]++; @$plusOuMoinsCher[ 'tous' ][10]++;  }
						elseif($difference<20) { @$plusOuMoinsCher[ $pe['id_concurrents'] ][15]++; @$plusOuMoinsCher[ 'tous' ][15]++;  }
						else { @$plusOuMoinsCher[ $pe['id_concurrents'] ][20]++; @$plusOuMoinsCher[ 'tous' ][20]++;  }
						
						if($difference<0)  { @$plusOuMoinsCher[ $pe['id_concurrents'] ][-100]++; @$plusOuMoinsCher[ 'tous' ][-100]++;  }
						elseif($difference>0)  { @$plusOuMoinsCher[ $pe['id_concurrents'] ][100]++; @$plusOuMoinsCher[ 'tous' ][100]++;  }
						else  { @$plusOuMoinsCher[ $pe['id_concurrents'] ][3]++; @$plusOuMoinsCher[ 'tous' ][3]++;  }
					}
					
					$nb_ventes=$produit['nb_ventes'];
					//stats
					@$nb_produits[ $pe['id_concurrents'] ]++;
					$nb_produits_total++;
					if($nb_ventes>0)
					{
						@$ca[ $pe['id_concurrents'] ]+=$pe['prix']*$nb_ventes;
						@$num_marge[ $pe['id_concurrents'] ]+=($pe['prix']-$produit['wholesale_price'])*$nb_ventes;
					}
					@$somme_prix[ $pe['id_concurrents'] ]+=$pe['prix'];
					
					if(!@is_array($id_product_associes[ $pe['id_concurrents'] ])) $id_product_associes[ $pe['id_concurrents'] ]=array();
					$id_product_associes[ $pe['id_concurrents'] ][]=$id_product;
				}
			}
			
			//print_r($plusOuMoinsCher);
			
			$nb_commandes=array();
			$nb_ventes_concurrent=array();
			$ca_moi=array();
			$num_marge_moi=array();
			$somme_prix_moi=array();
			$numeros_commandes_product=array();
			foreach($tabConcurrents as $id_concurrent=>$nom_concurrent)
			{
				
				$numeros_commandes=array();
				$nb_total_ventes=0;
				foreach($tabProduits as $id_product=>$produit)
				{
					if(!isset($id_product_associes[$id_concurrent]) || !in_array($id_product,$id_product_associes[$id_concurrent])) continue;
					
					$commandes=Db::getInstance()->executeS('
					SELECT o.id_order AS id_order
					FROM '._DB_PREFIX_.'orders o
					LEFT JOIN '._DB_PREFIX_.'order_detail od
						ON o.id_order = od.id_order
					WHERE od.product_id = '.(int)$id_product.'
					ORDER BY o.date_add DESC
					');

					foreach($commandes as $com)
					{
						$numeros_commandes[]=$com['id_order'];
						if(!isset($numeros_commandes_product[$id_product])) $numeros_commandes_product[$id_product]=array();
						$numeros_commandes_product[$id_product][]=$com['id_order'];
					}
					
					$monPrix=$produit['prix_precalcule'];
/*					if($isPrixBase) $monPrix=$produit->price;
					else
					{
						if($isPrixGros)
						{
							if($isHT) $monPrix=$produit->getPriceMin(false);
							else $monPrix=$produit->getPriceMin();
						}
						else
						{
							if($isHT) $monPrix=$produit->getPrice(false);
							else $monPrix=$produit->getPrice();
						}
					}
					*/
					
					$nb_ventes=$produit['nb_ventes'];
					if($nb_ventes>0)
					{
						$nb_total_ventes+=$nb_ventes;
						@$ca_moi[ $id_concurrent ]+=$monPrix*$nb_ventes;
						@$num_marge_moi[ $id_concurrent ]+=($monPrix-$produit['wholesale_price'])*$nb_ventes;
					}
					@$somme_prix_moi[ $id_concurrent ]+=$monPrix;
				}
				$nb_commandes[$id_concurrent]=count(array_unique($numeros_commandes));
				$nb_ventes_concurrent[$id_concurrent]=$nb_total_ventes;
			}

			//historique
			$id_product_associes_tous=array();
			$historique=array();
			$historique[0]=array();
			foreach($tabConcurrents as $id_concurrent=>$nom_concurrent)
			{
				if(isset($id_product_associes[$id_concurrent])) $id_product_associes_tous=array_merge($id_product_associes_tous,$id_product_associes[$id_concurrent]);
				$historique[$id_concurrent*1]=array();
			}
			if(!$id_product_associes_tous) $historique_prix=array();
			else
			{
				$historique_prix = Db::getInstance()->ExecuteS('SELECT H.id_produits_etrangers,H.date,H.id_product,H.prix,PE.id_concurrents
					FROM `'._DB_PREFIX_.$this->name.'_historique` H
					LEFT JOIN '._DB_PREFIX_.$this->name.'_produits_etrangers PE ON PE.id_produits_etrangers=H.id_produits_etrangers
					WHERE H.id_product IN ( '.implode(',',$id_product_associes_tous).' )
					GROUP BY H.id_historique
					ORDER BY H.date ASC,PE.id_concurrents'
				);
			}
			$id_concurrent_courant=-1;
			$date_courante=-1;
			$numeros_commandes=array();
			foreach($historique_prix as $h)
			{
				//changement de concurrent
				if($id_concurrent_courant!=$h['id_concurrents'] || $date_courante!=$h['date'])
				{
					if(Tools::getValue('historique')=='panier') $indicateur_den=count(array_unique($numeros_commandes));
					if($id_concurrent_courant!=-1)
					{
						$historique[ $id_concurrent_courant ][]=array(
							'date'=>$date_courante,
							'indicateur'=>$indicateur/($indicateur_den?$indicateur_den:1),
						);
					}
					$id_concurrent_courant=$h['id_concurrents']*1;
					$indicateur=0;
					$indicateur_den=0;
					//changement de date
					if($date_courante!=$h['date'])
					{
						$date_courante=$h['date'];
					}

				}
				
				//prix de mon produit
				$produit=$tabProduits[$h['id_product']];
				
				$monPrix=$produit['prix_precalcule'];
	/*			if($isPrixBase) $monPrix=$produit->price;
				else
				{
					if($isPrixGros)
					{
						if($isHT) $monPrix=$produit->getPriceMin(false);
						else $monPrix=$produit->getPriceMin();
					}
					else
					{
						if($isHT) $monPrix=$produit->getPrice(false);
						else $monPrix=$produit->getPrice();
					}
				}
				*/
				
				if($monPrix<str_replace(',','.',Tools::getValue('prix_min')) || (Tools::getValue('prix_max')!='' && $monPrix>str_replace(',','.',Tools::getValue('prix_max'))) ) continue;
				
				$nb_ventes=$produit['nb_ventes'];
				
				if($nb_ventes>=0)
				{
					if(Tools::getValue('historique')=='panier') { 
						$indicateur+=$nb_ventes*$h['prix']; 
						foreach($numeros_commandes_product[$id_product] as $item) $numeros_commandes[]=$item;
						//$numeros_commandes=array_merge($numeros_commandes,$numeros_commandes_product[$id_product]);
					}
					elseif(Tools::getValue('historique')=='ca') $indicateur+=$nb_ventes*$h['prix'];

					elseif(Tools::getValue('historique')=='marge') { $indicateur+=($h['prix']-$produit['wholesale_price'])*$nb_ventes; $indicateur_den+=$nb_ventes*$h['prix']; }
				}
				if(!Tools::getValue('historique') || Tools::getValue('historique')=='prix') { $indicateur+=$h['prix']; $indicateur_den++; }
			}
			if($id_concurrent_courant!=-1)
			{
				$historique[ $id_concurrent_courant ][]=array(
					'date'=>$date_courante,
					'indicateur'=>$indicateur/($indicateur_den?$indicateur_den:1),
				);
			}
			

			$smarty->assign('id_category', (int)Tools::getValue('id_category') );
			$smarty->assign('get_id_concurrents', Tools::getValue('id_concurrents') );
			$smarty->assign('id_concurrents', $id_concurrents );
			$smarty->assign('fav', $this->version2=='Silver'?0:((int)Tools::getValue('fav')) ); //limitation pour la version Silver
			$smarty->assign('langue', $langue );
			$smarty->assign('concurrents', $tabConcurrents );
			$smarty->assign('marques', $marques );
			$smarty->assign('id_manufacturer', $id_manufacturer );
			$smarty->assign('concurrents_filtrer', $tabConcurrents_filtrer );
			$smarty->assign('favoris', $favoris );
			$smarty->assign('devise', $this->getDevise() );
			$smarty->assign('historique_get', Tools::getValue('historique') );
			
			$smarty->assign('moinsDonnes', !(boolean)Tools::getValue('moinsDonnes') );
			$smarty->assign('plusOuMoinsCher', $plusOuMoinsCher );
			$smarty->assign('historique', $historique );
			$smarty->assign('nb_commandes', $nb_commandes );
			$smarty->assign('nb_ventes_concurrent', $nb_ventes_concurrent );
			$smarty->assign('nb_produits', $nb_produits );
			$smarty->assign('nb_produits_total', $nb_produits_total );
			$smarty->assign('ca', $ca );
			$smarty->assign('num_marge', $num_marge );
			$smarty->assign('somme_prix', $somme_prix );
			$smarty->assign('ca_moi', $ca_moi );
			$smarty->assign('num_marge_moi', $num_marge_moi );
			$smarty->assign('somme_prix_moi', $somme_prix_moi );

			$smarty->error_reporting = 0;

			return $this->display(__FILE__, 'views/tableaubord.tpl');
		}
		elseif (Tools::isSubmit('concurrents') || Tools::isSubmit('supprConcurrent')) //gestion concurrents
		{
			if(Tools::getValue('ajMarketplace')=='amazon' && $this->version2=='MEGA')
			{
				$langue=Language::getIsoById((int)Configuration::get('PS_LANG_DEFAULT'));
				$ext='com';
				if($langue=='en') $ext='co.uk';
				elseif($langue=='fr') $ext='fr';
				elseif($langue=='de') $ext='de';
				elseif($langue=='it') $ext='it';
				elseif($langue=='es') $ext='es';
				elseif($langue=='ja') $ext='co.jp';
				elseif($langue=='zh' || $langue=='tw') $ext='cn';

				DbCoreCompaSPpricestracker::insert(
					$this->name.'_concurrents',
					array(
						'nom'=>'Amazon '.strtoupper($langue),
						'url'=>'http://www.amazon.'.$ext,
						'ordre'=>1,
						'httpAgent'=>'Twitterbot/1.0', //'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
						'profondeur'=>7,
						'delai'=>100,
						'maxUrl'=>100000,
						'regexTitre'=>'',
						'masqueTitre'=>1,
						'regexPrix'=>(''),
						'masquePrix'=>1,
						'regexImage'=>(''),
						'masqueImage'=>1,
						'regexQuantite'=>(''),
						'masqueQuantite'=>1,
						'regexRef'=>(''),
						'masqueRef'=>1,
						'regexUrlBloquer'=>'',
						'actif'=>1,
					)
				);
			}
			elseif(Tools::getValue('ajMarketplace')=='ebay' && $this->version2=='MEGA')
			{
				DbCoreCompaSPpricestracker::insert(
					$this->name.'_concurrents',
					array(
						'nom'=>'Ebay',
						'url'=>'http://www.ebay.fr',
						'ordre'=>1,
						'httpAgent'=>'Twitterbot/1.0',
						'profondeur'=>7,
						'delai'=>100,
						'maxUrl'=>100000,
						'regexTitre'=>'',
						'masqueTitre'=>1,
						'regexPrix'=>(''),
						'masquePrix'=>1,
						'regexImage'=>(''),
						'masqueImage'=>1,
						'regexQuantite'=>(''),
						'masqueQuantite'=>1,
						'regexRef'=>(''),
						'masqueRef'=>1,
						'regexUrlBloquer'=>'',
						'actif'=>1,
					)
				);
			}
			elseif(Tools::getValue('ajMarketplace')=='priceminister' && $this->version2=='MEGA')
			{
				DbCoreCompaSPpricestracker::insert(
					$this->name.'_concurrents',
					array(
						'nom'=>'PriceMinister',
						'url'=>'http://www.priceminister.com',
						'ordre'=>1,
						'httpAgent'=>'Twitterbot/1.0',
						'profondeur'=>7,
						'delai'=>100,
						'maxUrl'=>100000,
						'regexTitre'=>'',
						'masqueTitre'=>1,
						'regexPrix'=>(''),
						'masquePrix'=>1,
						'regexImage'=>(''),
						'masqueImage'=>1,
						'regexQuantite'=>(''),
						'masqueQuantite'=>1,
						'regexRef'=>(''),
						'masqueRef'=>1,
						'regexUrlBloquer'=>'',
						'actif'=>1,
					)
				);
			}
			elseif(Tools::getValue('ajMarketplace')=='cdiscount' && $this->version2=='MEGA')
			{
				DbCoreCompaSPpricestracker::insert(
					$this->name.'_concurrents',
					array(
						'nom'=>'CDiscount',
						'url'=>'http://www.cdiscount.com',
						'ordre'=>1,
						'httpAgent'=>'Twitterbot/1.0',
						'profondeur'=>7,
						'delai'=>100,
						'maxUrl'=>100000,
						'regexTitre'=>'',
						'masqueTitre'=>1,
						'regexPrix'=>(''),
						'masquePrix'=>1,
						'regexImage'=>(''),
						'masqueImage'=>1,
						'regexQuantite'=>(''),
						'masqueQuantite'=>1,
						'regexRef'=>(''),
						'masqueRef'=>1,
						'regexUrlBloquer'=>'',
						'actif'=>1,
					)
				);
			}
			if(Tools::isSubmit('supprConcurrent'))
			{
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_concurrents`
				WHERE id_concurrents='.(int)Tools::getValue('id'));
			}
			if(Tools::isSubmit('actif'))
			{
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.$this->name.'_concurrents`
				SET actif='.((int)Tools::getValue('actif')).'
				WHERE id_concurrents='.(int)Tools::getValue('id'));
			}
			
			
			$concurrents = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE 1=1');
	
			$smarty->assign('concurrents', $concurrents );
			$smarty->assign('version2', $this->version2 );
			$smarty->assign('modeTest', $this->modeTest );
			return $this->display(__FILE__, 'views/concurrents.tpl');
		}
		elseif (Tools::isSubmit('ajConcurrent')) //ajout concurrents
		{
			if(!Tools::isSubmit('id'))
			{
				if($this->modeTest)
				{
					$nbConcurrents = Db::getInstance()->getValue('
					SELECT COUNT(id_concurrents)
					FROM `'._DB_PREFIX_.$this->name.'_concurrents`
					WHERE 1=1');
					if($nbConcurrents>=1)
					{
						header('Location: '.$lien.'&concurrents');
						die;
					}
				}
				
				DbCoreCompaSPpricestracker::insert(
					$this->name.'_concurrents',
					array(
						'nom'=>$this->l('New competitor'),
						'ordre'=>1,
						'httpAgent'=>'Twitterbot/1.0',
						'profondeur'=>7,
						'delai'=>100,
						'maxUrl'=>100000,
						'regexTitre'=>('<h1[^>]*>([^<]+)</h1>'),
						'masqueTitre'=>1,
						'regexPrix'=>('<span [^>]*id="our_price_display"[^>]*>([^<]+)</span>'),
						'masquePrix'=>1,
						'regexImage'=>('<img [^>]*id="bigpic"[^>]*src="([^"><]+)"'),
						'masqueImage'=>1,
						'regexRef'=>('<span [^>]*class="editable"[^>]*>([^<]+)</span>'),
						'masqueRef'=>1,
						'regexUrlBloquer'=>'([^a-z0-9]logout)',
						'nb_taches'=>5,
						'actif'=>1,
						'id_unique'=>mt_rand(1,999999),
					)
				);
				$id=Db::getInstance()->Insert_ID();
				
				header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&id='.$id);
				die;
			}
			else
			{
				$id=(int)Tools::getValue('id');
				
				
				if(Tools::isSubmit('submit'))
				{
					$codeGroovy=$this->escapeCode('codeGroovy');
					$codeLiens=$this->escapeCode('codeLiens');
					$codeFinal=$this->escapeCode('codeFinal');

					DbCoreCompaSPpricestracker::update(
						$this->name.'_concurrents',
						array(
							'nom'=>pSQL(Tools::getValue('nom')),
							'url'=> pSQL((strpos(Tools::getValue('url'),'http://')===FALSE && strpos(Tools::getValue('url'),'https://')===FALSE ? 'http://':'')  . Tools::getValue('url')),
							'ordre'=>(int) Tools::getValue('ordre'),
							'httpAgent'=>str_replace("'","\'",Tools::getValue('httpAgent')),
							'profondeur'=>(int) Tools::getValue('profondeur'),
							'delai'=>(int)Tools::getValue('delai'),
							'maxUrl'=>pSQL(Tools::getValue('maxUrl')),
							'regexTitre'=>$this->escapeCode('regexTitre'),
							'masqueTitre'=>(int) (Tools::getValue('masqueTitre')>0?Tools::getValue('masqueTitre'):1),
							'regexPrix'=>$this->escapeCode('regexPrix'),
							'masquePrix'=>(int) (Tools::getValue('masquePrix')>0?Tools::getValue('masquePrix'):1),
							'regexImage'=>$this->escapeCode('regexImage'),
							'masqueImage'=>(int) (Tools::getValue('masqueImage')>0?Tools::getValue('masqueImage'):1),
							'regexRef'=>$this->escapeCode('regexRef'),
							'masqueRef'=>(int) (Tools::getValue('masqueRef')>0?Tools::getValue('masqueRef'):1),
							'regexUrlBloquer'=>$this->escapeCode('regexUrlBloquer'),
							'nb_taches'=>(int) Tools::getValue('nb_taches'),
							'suivi_cookies'=>(int) Tools::getValue('suivi_cookies'),
							'codeGroovy'=>$codeGroovy,
							'codeLiens'=>$codeLiens,
							'codeFinal'=>$codeFinal,
						), 'id_concurrents = '.$id
					);
				}
				
				
				if(Tools::isSubmit('submit'))
				{
					$concurrent = Db::getInstance()->getRow('SELECT *
					FROM `'._DB_PREFIX_.$this->name.'_concurrents`
					WHERE `id_concurrents` = '.$id
					);
	
					//enregistrement en archive sur le wiki société
					$domaine_site=$_SERVER['SERVER_NAME'];
					$titre_wiki=ucfirst(Tools::getValue('nom').' ('.$domaine_site.')');
					
					$contenu_wiki=(strpos(Tools::getValue('url'),'http://')===FALSE && strpos(Tools::getValue('url'),'https://')===FALSE ? 'http://':'')  . Tools::getValue('url')."\n\n";
					$contenu_wiki.="== Données client ==\n\n"."http://".$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]."\n\n";
					$contenu_wiki.=Configuration::get('PS__NAME')."\n\n";
					$contenu_wiki.='Licence : '.$this->licence."\n\n";
					$contenu_wiki.=(Configuration::get('PS__EMAIL')?Configuration::get('PS__EMAIL'):Configuration::get('PS_SHOP_EMAIL'))."\n\n";
					$contenu_wiki.=$this->getBaseLink()."\n\n";
					$cookie=Context::getContext()->cookie;
					if($cookie->id_employee)
					{
						$empl=new Employee($cookie->id_employee);
						$contenu_wiki.=$empl->firstname.' '.$empl->lastname."\n\n";
					}
					$contenu_wiki.="== Auteur ==\n\n";
					$contenu_wiki.="IP : ".Tools::getRemoteAddr()."\n\n";
					$contenu_wiki.="Navigateur : ".$_SERVER['HTTP_USER_AGENT']."\n\nDepuis le module"."\n\n";
					$contenu_wiki.="\n\n";
					$contenu_wiki.="== URL exclus ==\n\n".$_POST['regexUrlBloquer']."\n\n";
					$contenu_wiki.="== Titre ==\n\n".'<source lang="javascript">'.$_POST['regexTitre'].'</source>'."\n\nMasque : ".(int) (Tools::getValue('masqueTitre')>0?Tools::getValue('masqueTitre'):1)."\n\n";
					$contenu_wiki.="== Prix ==\n\n".'<source lang="javascript">'.$_POST['regexPrix'].'</source>'."\n\nMasque : ".(int) (Tools::getValue('masquePrix')>0?Tools::getValue('masquePrix'):1)."\n\n";
					if(@$_POST['regexImage']) $contenu_wiki.="== Image ==\n\n".'<source lang="javascript">'.$_POST['regexImage'].'</source>'."\n\nMasque : ".(int) (Tools::getValue('masqueImage')>0?Tools::getValue('masqueImage'):1)."\n\n";
					if(@$_POST['regexRef']) $contenu_wiki.="== Référence ==\n\n".'<source lang="javascript">'.$_POST['regexRef'].'</source>'."\n\nMasque : ".(int) (Tools::getValue('masqueRef')>0?Tools::getValue('masqueRef'):1)."\n\n";
					if(@$_POST['codeGroovy']) $contenu_wiki.="== Script exécuté sur la page produit ==\n\n".'<source lang="javascript">'.@$_POST['codeGroovy'].'</source>'."\n\n";
					if(@$_POST['codeLiens']) $contenu_wiki.="== Script initial ==\n\n".'<source lang="javascript">'.@$_POST['codeLiens'].'</source>'."\n\n";
					if(@$_POST['codeFinal']) $contenu_wiki.="== Script final ==\n\n".'<source lang="javascript">'.@$_POST['codeFinal'].'</source>'."\n\n";
					
					$suivi_cookies = (int)Tools::getValue('suivi_cookies');
					if($suivi_cookies==0) $suivi_cookies='Aucun suivi (0)';
					elseif($suivi_cookies==1) $suivi_cookies='Suivi lors du crawling (1)';
					elseif($suivi_cookies==2) $suivi_cookies='Suivi lors du crawling et des fonctions post, getPage, etc. (2)';
					elseif($suivi_cookies==3) $suivi_cookies='Suivi lors du crawling et des fonctions post, getPage, etc. et des erreurs HTTP (3)';
					
					$contenu_wiki.="== Autres paramètres ==\n\n"."\n\nUser-Agent : ".Tools::getValue('httpAgent')."\n\n\n\n\n\nProfondeur : ".Tools::getValue('profondeur')."\n\nDélai entre les requêtes : ".Tools::getValue('delai')."\n\nNombre maximum d'URL à visiter : ".Tools::getValue('maxUrl')."\n\nNombre de tâche de crawling en paralèlle : ".Tools::getValue('nb_taches')."\n\nMode de suivi des cookies : ".$suivi_cookies."\n\nSauvergarde automatique tous les : ".Tools::getValue('urls_sav_progression')." URLs"."\n\nID Unique du script : ".$concurrent['id_unique'];
					
					
					$this->post(array(
						'nom_page'=>str_replace(array('[',']','{','}','|','#'),'-',$titre_wiki),
						'contenu'=>$contenu_wiki,
					),
					'https://www.storeinterfacer.com/ajouterWikiPricestracker.php');
					
				}
			}
			
			$concurrent = Db::getInstance()->getRow('SELECT *
				FROM `'._DB_PREFIX_.$this->name.'_concurrents`
				WHERE `id_concurrents` = '.$id
			);
			
			$smarty->assign('id', (int)Tools::getValue('id') );
			$smarty->assign('concurrent', $concurrent );

			$this->context->controller->addJS( $this->_path .'js/youtubepopup.js');
			return $this->display(__FILE__, 'views/ajConcurrent.tpl');
		}
		elseif(Tools::isSubmit('crawling'))
		{
			$idCrawling=(int)Tools::getValue('idCrawling');
		}
		if (Tools::isSubmit('cloudAjax')) //cloud
		{
			if (Tools::isSubmit('demandeRapport'))
			{
				echo Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?demandeRapport=1&codeCloud='.urlencode(Tools::getValue('codeCloud')).'&id_serveur='.urlencode(Tools::getValue('id_serveur')).'&id_crawling='.urlencode(Tools::getValue('id_crawling')));
			}
			elseif (Tools::isSubmit('recuperer_action'))
			{
				echo Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?recuperer_action='.urlencode(Tools::getValue('recuperer_action')).'&codeCloud='.urlencode(Tools::getValue('codeCloud')).'&id_serveur='.urlencode(Tools::getValue('id_serveur')));
			}
			elseif (Tools::isSubmit('recuperer_regexUrlBloquer'))
			{
				echo Db::getInstance()->getValue('
					SELECT regexUrlBloquer
					FROM `'._DB_PREFIX_.$this->name.'_concurrents`
					WHERE id_concurrents='.(int)Tools::getValue('id_concurrents'));
			}
			elseif (Tools::isSubmit('demanderFiltrageLiens'))
			{
				DbCoreCompaSPpricestracker::update(
					'pricestracker_concurrents',
					array(
						'regexUrlBloquer'=>$this->escapeCodeGet('regex'),
					), 'id_concurrents = '.(int)Tools::getValue('id_concurrents')
				);

				echo Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?demanderFiltrageLiens=1&codeCloud='.urlencode(Tools::getValue('codeCloud')).'&id_serveur='.urlencode(Tools::getValue('id_serveur')).'&id_crawling='.urlencode(Tools::getValue('id_crawling')).'&regex='.urlencode(Tools::getValue('regex')).'&reponse='.urlencode(Tools::getValue('reponse')));
			}
			elseif (Tools::isSubmit('demanderRapportMemoire'))
			{
				echo Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?demanderRapportMemoire=1&codeCloud='.urlencode(Tools::getValue('codeCloud')).'&id_serveur='.urlencode(Tools::getValue('id_serveur')).'&id_crawling='.urlencode(Tools::getValue('id_crawling')));
			}
			elseif (Tools::isSubmit('demanderArret'))
			{
				echo Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?demanderArret=1&codeCloud='.urlencode(Tools::getValue('codeCloud')).'&id_serveur='.urlencode(Tools::getValue('id_serveur')).'&id_crawling='.urlencode(Tools::getValue('id_crawling')));
			}
			elseif (Tools::isSubmit('synchroniserConsole'))
			{
				echo Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?synchroniserConsole=1&codeCloud='.urlencode(Tools::getValue('codeCloud')).'&id_serveur='.urlencode(Tools::getValue('id_serveur')).'&id_crawling='.urlencode(Tools::getValue('id_crawling')));
			}
			elseif (Tools::isSubmit('demanderChangerEtats'))
			{
				echo Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?demanderChangerEtats=1&codeCloud='.urlencode(Tools::getValue('codeCloud')).'&id_serveur='.urlencode(Tools::getValue('id_serveur')).'&id_crawling='.urlencode(Tools::getValue('id_crawling')).'&afficher_liens_en_cours_crawling='.urlencode(Tools::getValue('afficher_liens_en_cours_crawling')).'&afficher_liens_attente='.urlencode(Tools::getValue('afficher_liens_attente')));
			}
			
			die;
		}
		if (Tools::isSubmit('cloud') && Tools::isSubmit('id')) //cloud
		{
			$id_concurrent=(int)Tools::getValue('id');
			
			$concurrent = Db::getInstance()->getRow('SELECT *
				FROM `'._DB_PREFIX_.$this->name.'_concurrents`
				WHERE `id_concurrents` = '.$id_concurrent
			);
			
			$message='';
			
			if(Tools::isSubmit('majCodeCloud'))
			{
				$codeCloudPropose=Tools::getValue('codeCloud');

				$jsonIdentiteClient=Tools::file_get_contents('https://www.storeinterfacer.com/achat_credits.php?identification_compte='.urlencode($codeCloudPropose));
				if(!$jsonIdentiteClient) $message=$this->l('No cloud account found for this code');
				else
				{
					$jsonIdentiteClient=json_decode($jsonIdentiteClient);
					
					if($jsonIdentiteClient)
					{
						if($jsonIdentiteClient->admin)
						{
							setcookie('CODECLOUD',$codeCloudPropose,time()+24*3600*900,'/', $_SERVER['HTTP_HOST'], false, true);
						}
						else
						{
							if(_PS_VERSION_ >= '1.5') Configuration::updateGlobalValue('pricestracker_CODECLOUD', $jsonIdentiteClient->code);
							else Configuration::updateValue('pricestracker_CODECLOUD', $jsonIdentiteClient->code);
						}
						
						$message=$this->l('Your idIA Tech Cloud code is registered');
					}
					else  $message=$this->l('No cloud account found for this code');
				}
			}
			
			$typeProxy=$this->typeProxy($concurrent);
			
			if(Tools::isSubmit('programmer'))
			{
				$parametres=array(
					'lien_connexion'=>(_PS_VERSION_ < '1.5'?('http://'.$_SERVER['HTTP_HOST']):(Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_)).__PS_BASE_URI__.'modules/pricestracker/pages/communication.php?clef='.(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') ),
					'nom_crawling'=>$concurrent['nom'].' #'.(int)$concurrent['id_concurrents'].' scheduled by a user (PricesTracker'.((int)Tools::getValue('partial')?"/PARTIAL":"/COMPLETE").')',
					'proxy_type'=>$typeProxy,
					'script'=>json_encode(array(
						'codeLiens'=>$concurrent['codeLiens'],
						'codeGroovy'=>$concurrent['codeGroovy'],
						'codeFinal'=>$concurrent['codeFinal'],
						'name'=>$concurrent['nom'],
						'actif'=>$concurrent['actif'],
						'delai'=>$concurrent['delai'],
						'httpAgent'=>$concurrent['httpAgent'],
						'maxUrl'=>$concurrent['maxUrl'],
						'nbTaches'=>$concurrent['nb_taches'],
						'urlsAutoSavProgression'=>$concurrent['urls_sav_progression'],
						'modeSuiviCookie'=>$concurrent['suivi_cookies'],
						'profondeur'=>$concurrent['profondeur'],
						'regexUrlBloquer'=>$concurrent['regexUrlBloquer'],
						'url'=>$concurrent['url'],
						'id_unique'=>$concurrent['id_unique'],
						'regexTitre'=>$concurrent['regexTitre'],
						'regexPrix'=>$concurrent['regexPrix'],
						'regexImage'=>$concurrent['regexImage'],
						'regexQuantite'=>$concurrent['regexQuantite'],
						'regexRef'=>$concurrent['regexRef'],
						'masqueTitre'=>$concurrent['masqueTitre'],
						'masquePrix'=>$concurrent['masquePrix'],
						'masqueImage'=>$concurrent['masqueImage'],
						'masqueQuantite'=>$concurrent['masqueQuantite'],
						'masqueRef'=>$concurrent['masqueRef'],
					)),
					'id_unique'=>(int)$concurrent['id_unique'],
					'options'=>'id='.(int)$concurrent['id_unique'].','.((int)Tools::getValue('partial')?',partial':''),
					'nom_site'=>Configuration::get('PS_SHOP_NAME'),
				);
				
				$programme=array(
					'minutes'=>Tools::getValue('minutes'),
					'heures'=>Tools::getValue('heures'),
					'joursDuMois'=>Tools::getValue('joursMois'),
					'joursDeSemaine'=>Tools::getValue('joursSemaine'),
					'mois'=>Tools::getValue('mois'),
					'activer'=>(int)Tools::getValue('actif'),
					'nePasLancerSiDejaEnCours'=>0,
				);
				
				$json_reponse=$this->post(array(
					'parametres'=>json_encode($parametres),
					'id_script_unique'=>(int)$concurrent['id_unique'],
					'codeCloud'=>$this->getCodeCloud(),
					'codeCloudDonneurOrdre'=>$this->getCodeCloud(),
					'ne_pas_lancer_si_tache_deja_en_cours'=>0,
					'programme'=>json_encode($programme),
					'nom'=>$concurrent['nom'].' #'.(int)$concurrent['id_concurrents'].' scheduled by a user (PricesTracker'.((int)Tools::getValue('partial')?"/PARTIAL":"/COMPLETE").')',
					'pricestracker'=>'1',
				),
				'https://www.storeinterfacer.com/ajouter_action_cloud.php');

				if(strpos($json_reponse,'TACHE_PLANIFIEE')!==FALSE) $message=$this->l('Your competitor analysis is scheduled in our Cloud');
				else $message=$this->l('ERROR !!!').$json_reponse;
			}
			elseif(Tools::isSubmit('supprimer_programme'))
			{
				$json_reponse=$this->post(array(
					'supprimerCron'=>1,
					'id_script_unique'=>(int)$concurrent['id_unique'],
					'codeCloud'=>$this->getCodeCloud(),
					'codeCloudDonneurOrdre'=>$this->getCodeCloud(),
					'pricestracker'=>'1',
					'partial'=>(int)Tools::getValue('partial'),
				),
				'https://www.storeinterfacer.com/ajouter_action_cloud.php');

				if(strpos($json_reponse,'OK')!==FALSE) $message=$this->l('Your task is removed from our Cloud');
				else $message=$this->l('ERROR !!!').$json_reponse;
			}

			if(Tools::isSubmit('lancerCloud'))
			{
				$parametres=array(
					'lien_connexion'=>(_PS_VERSION_ < '1.5'?('http://'.$_SERVER['HTTP_HOST']):(Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_)).__PS_BASE_URI__.'modules/pricestracker/pages/communication.php?clef='.(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') ),
					'nom_crawling'=>$concurrent['nom'].' #'.(int)$concurrent['id_concurrents'].' launched by a user (PricesTracker'.((int)Tools::getValue('partialLancer')?"/PARTIAL":"/COMPLETE").')',
					'proxy_type'=>$typeProxy,
					'script'=>json_encode(array(
						'codeLiens'=>$concurrent['codeLiens'],
						'codeGroovy'=>$concurrent['codeGroovy'],
						'codeFinal'=>$concurrent['codeFinal'],
						'name'=>$concurrent['nom'],
						'actif'=>$concurrent['actif'],
						'delai'=>$concurrent['delai'],
						'httpAgent'=>$concurrent['httpAgent'],
						'maxUrl'=>$concurrent['maxUrl'],
						'nbTaches'=>$concurrent['nb_taches'],
						'urlsAutoSavProgression'=>$concurrent['urls_sav_progression'],
						'modeSuiviCookie'=>$concurrent['suivi_cookies'],
						'profondeur'=>$concurrent['profondeur'],
						'regexUrlBloquer'=>$concurrent['regexUrlBloquer'],
						'url'=>$concurrent['url'],
						'id_unique'=>$concurrent['id_unique'],
						'regexTitre'=>$concurrent['regexTitre'],
						'regexPrix'=>$concurrent['regexPrix'],
						'regexImage'=>$concurrent['regexImage'],
						'regexQuantite'=>$concurrent['regexQuantite'],
						'regexRef'=>$concurrent['regexRef'],
						'masqueTitre'=>$concurrent['masqueTitre'],
						'masquePrix'=>$concurrent['masquePrix'],
						'masqueImage'=>$concurrent['masqueImage'],
						'masqueQuantite'=>$concurrent['masqueQuantite'],
						'masqueRef'=>$concurrent['masqueRef'],
					)),
					'id_unique'=>(int)$concurrent['id_unique'],
					'options'=>'id='.(int)$concurrent['id_unique'].','.((int)Tools::getValue('partialLancer')?',partial':''),
					'nom_site'=>Configuration::get('PS_SHOP_NAME'),
				);
				
				$json_reponse = $this->post(array(
					'parametres'=>json_encode($parametres),
					'id_script_unique'=>(int)$concurrent['id_unique'],
					'codeCloud'=>$this->getCodeCloud(),
					'codeCloudDonneurOrdre'=>$this->getCodeCloud(),
					'ne_pas_lancer_si_tache_deja_en_cours'=>0,
					'programme'=>'',
					'nom'=>$concurrent['nom'].' #'.(int)$concurrent['id_concurrents'].' launched by a user (PricesTracker'.((int)Tools::getValue('partialLancer')?"/PARTIAL":"/COMPLETE").')',
					'pricestracker'=>'1',
				),
				'https://www.storeinterfacer.com/ajouter_action_cloud.php');
				
				if(strpos($json_reponse,'ACTION_CREE')!==FALSE) $message=$this->l('Your competitor analysis is launched in our Cloud');
				else $message=$this->l('ERROR !!!').$json_reponse;
			}
			
			$identification_compte=NULL;
			$programme=array();
			$listeCrawlings=array();
			if($this->getCodeCloud())
			{
				$jsonIdentiteClient=Tools::file_get_contents('https://www.storeinterfacer.com/achat_credits.php?identification_compte='.urlencode($this->getCodeCloud()));
				if(!$jsonIdentiteClient) $identification_compte=NULL;
				else
				{
					$jsonIdentiteClient=json_decode($jsonIdentiteClient);
					
					$identification_compte=@$jsonIdentiteClient->societe."<br>".@$jsonIdentiteClient->nom." ".@$jsonIdentiteClient->prenom."<br>".@$jsonIdentiteClient->email."<br>".@$jsonIdentiteClient->site;
					
					if($jsonIdentiteClient->admin) $identification_compte=$this->l('Cloud Administrator - your code is registred in cookie, the website owner does not have it').'<br>'.$identification_compte;

					$programmeComplete=json_decode(Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?getProgrammeComplete='.urlencode($concurrent['id_unique'])));
					$programmePartial=json_decode(Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?getProgrammePartial='.urlencode($concurrent['id_unique'])));
					
					$listeCrawlings=json_decode(Tools::file_get_contents('https://www.storeinterfacer.com/pricestracker_cloud_78242.php?listeCrawlings='.urlencode($concurrent['id_unique'])."&codeCloud=".urlencode($this->getCodeCloud())));
				}
			}

			$smarty->assign('id', $id_concurrent );
			$smarty->assign('concurrent', $concurrent );
			$smarty->assign('identification_compte', str_replace('<br>',"\n",$identification_compte) );
			
			$smarty->assign('minutes', $programmeComplete ? str_replace('0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59','*',$programmeComplete->minutes) : '' );
			$smarty->assign('heures', $programmeComplete ? str_replace('0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23','*',$programmeComplete->heures) : '' );
			$smarty->assign('joursMois', $programmeComplete ? str_replace('1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31','*',$programmeComplete->jours_du_mois) : '' );
			$smarty->assign('joursSemaine', $programmeComplete ? str_replace('1,2,3,4,5,6,7','*',$programmeComplete->jours_de_semaine) : '' );
			$smarty->assign('mois', $programmeComplete ? str_replace('1,2,3,4,5,6,7,8,9,10,11,12','*',$programmeComplete->mois) : '' );
			$smarty->assign('actif', $programmeComplete ? $programmeComplete->actif : '1' );
			$smarty->assign('prochainesProgrammationsComplete', $programmeComplete->actif ? implode(', ', $this->getNextRunDates($programmeComplete->minutes, $programmeComplete->heures, $programmeComplete->jours_du_mois, $programmeComplete->mois, $programmeComplete->jours_de_semaine)) : '-' );
			
			
			$smarty->assign('minutes_partial', $programmePartial ? str_replace('0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59','*',$programmePartial->minutes) : '' );
			$smarty->assign('heures_partial', $programmePartial ? str_replace('0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23','*',$programmePartial->heures) : '' );
			$smarty->assign('joursMois_partial', $programmePartial ? str_replace('1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31','*',$programmePartial->jours_du_mois) : '' );
			$smarty->assign('joursSemaine_partial', $programmePartial ? str_replace('1,2,3,4,5,6,7','*',$programmePartial->jours_de_semaine) : '' );
			$smarty->assign('mois_partial', $programmePartial ? str_replace('1,2,3,4,5,6,7,8,9,10,11,12','*',$programmePartial->mois) : '' );
			$smarty->assign('actif_partial', $programmePartial ? $programmePartial->actif : '1' );
			$smarty->assign('prochainesProgrammationsPartiel', $programmePartial->actif ? implode(', ', $this->getNextRunDates($programmePartial->minutes, $programmePartial->heures, $programmePartial->jours_du_mois, $programmePartial->mois, $programmePartial->jours_de_semaine)) : '-' );
			
			$smarty->assign('partialLancer', (int)Tools::getValue('partialLancer') );
			$smarty->assign('message', $message );
			$smarty->assign('typeProxy', $typeProxy );
			$smarty->assign('crawlings', $listeCrawlings );
			$smarty->assign('codeCloud', $this->getCodeCloud() );

			$this->context->controller->addJS( $this->_path .'js/youtubepopup.js');
			return $this->display(__FILE__, 'views/cloud.tpl');

		}
		elseif (Tools::isSubmit('communication')) //comparaison de prix
		{
			ob_end_clean();
			
			include('pages/communication.php');
			die;
		}
		elseif (Tools::isSubmit('proximite') && $this->version2=='MEGA') //proximité
		{
			//concurrents
			$concurrents = Db::getInstance()->executeS('
			SELECT nom,id_concurrents
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE 1=1');
			$tabConcurrents=array();
			foreach($concurrents as $c)
			{
				$tabConcurrents[$c['id_concurrents']] = $c['nom'];
			}


			if(Tools::isSubmit('supprProx'))
			{
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_proximite_regles`
				WHERE id_proximite_regles='.(int)Tools::getValue('id'));
			}
			if(Tools::isSubmit('displayProx'))
			{
				Configuration::updateValue($this->name.'_MONTRER_PROX', !Configuration::get($this->name.'_MONTRER_PROX') );
			}
			if(Tools::isSubmit('supprAsso'))
			{
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_associations`
				WHERE 1=1');
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_regles_association`
				WHERE 1=1');
			}
			if(Tools::isSubmit('supprAssoAutomatiques'))
			{
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_associations`
				WHERE automatique=1');
			}
			
			if(Tools::isSubmit('submit'))
			{
				DbCoreCompaSPpricestracker::insert(
					$this->name.'_proximite_regles',
					array(
						'id_category'=>(int)Tools::getValue('id_category'),
						'id_favoris'=>(int)Tools::getValue('id_favoris'),
						'id_concurrents'=>(int)Tools::getValue('id_concurrents'),
						'max_produits'=>(int)Tools::getValue('max_produits'),
						'max_asso_pe'=>(int)Tools::getValue('max_asso_pe'),
						'max_suggestion'=>(int)Tools::getValue('max_suggestion'),
						'min_proximite'=>(float)Tools::getValue('min_proximite'),
						'just_nvo'=>(int)Tools::getValue('just_nvo'),
						'utiliser_chatgpt'=>(int)Tools::getValue('utiliser_chatgpt'),
						'aller_jusquau_chatgpt'=>(int)Tools::getValue('aller_jusquau_chatgpt'),
						'modele_chatgpt'=>pSQL(Tools::getValue('modele_chatgpt')),
					)
				);
			}



			$proximites = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.$this->name.'_proximite_regles`
			WHERE 1=1');

			$smarty->assign('concurrents', $tabConcurrents );
			$smarty->assign('proximites', $proximites );
			$smarty->assign('displayProx', Configuration::get($this->name.'_MONTRER_PROX') );
			return $this->display(__FILE__, 'views/proximite.tpl');
		}
		elseif (Tools::isSubmit('croisement')) //matching produits
		{
			//pages
			$page=(int)Tools::getValue('page');
			if(!$page) $page=1;
			
			//langue
			$langue=(int)Configuration::get('PS_LANG_DEFAULT');
			
			//filtre catégorie
			$id_category=(int)Tools::getValue('id_category');
			
			//filtre concurrents
			$id_concurrents=array();
			$tab_concurrents=preg_split("#[^0-9\-]+#is",Tools::getValue('id_concurrents'));
			foreach($tab_concurrents as $v)
			{
				if($v && $v!=-1) $id_concurrents[]=$v;
			}
			$id_concurrents=array_unique($id_concurrents);
				
			//marques
			$marques_db = Manufacturer::getManufacturers();
			$marques=array();
			foreach($marques_db as $m)
			{
				$marques[$m['id_manufacturer']] = $m['name'];
			}
		
			//concurrents
			$concurrents = Db::getInstance()->executeS('
			SELECT nom,id_concurrents,url
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE 1=1'.
			($id_concurrents?(' AND id_concurrents IN ('.implode(',',$id_concurrents).')'):''));
			$tabConcurrents=array();
			$tabConcurrentsUrl=array();
			foreach($concurrents as $c)
			{
				$tabConcurrents[$c['id_concurrents']] = $c['nom'];
				$tabConcurrentsUrl[$c['id_concurrents']] = $c['url'];
			}

			//concurrents tous
			$concurrents2 = Db::getInstance()->executeS('
			SELECT nom,id_concurrents
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE 1=1');
			$tabConcurrents2=array();
			foreach($concurrents2 as $c)
			{
				$tabConcurrents2[$c['id_concurrents']] = $c['nom'];
			}
		
			$where='';
			$from='';
			//where recherche
			if(Tools::getValue('rech'))
			{
				if(@$_POST['rech']) $page=1;
				$mots=preg_split("#\s+#is", $this->stripAccents(pSQL(Tools::getValue('rech'))) );
				foreach($mots as $mot) $where.=' AND (L.name LIKE \'%'.$mot.'%\' OR P.reference LIKE \'%'.$mot.'%\' OR P.ean13 LIKE \'%'.$mot.'%\' OR P.id_product = \''.$mot.'\')';
			}
			
			//where favoris
			if(Tools::getValue('fav') && $this->version2!='Silver')
			{
				
				
				
				
				//favoris															//GOLD - DIAMOND
				if(Tools::getValue('fav')==-1)										//GOLD - DIAMOND
				{
					$favorisProduct = Db::getInstance()->executeS('
					SELECT DISTINCT id_product
					FROM `'._DB_PREFIX_.$this->name.'_associations`
					WHERE 1=1');													//GOLD - DIAMOND
				}
				elseif(Tools::getValue('fav')==-2)									//GOLD - DIAMOND
				{																	//GOLD - DIAMOND
					$favorisProduct = Db::getInstance()->executeS('
					SELECT DISTINCT F.id_product AS id_product
					FROM `'._DB_PREFIX_.'product` F
					WHERE NOT EXISTS (SELECT A.id_product FROM `'._DB_PREFIX_.$this->name.'_associations` A'.($id_concurrents?',`'._DB_PREFIX_.'pricestracker_produits_etrangers` PE':'').' WHERE A.id_product=F.id_product '.($id_concurrents?(' AND PE.id_produits_etrangers=A.id_produits_etrangers AND PE.id_concurrents IN ('.implode(',',$id_concurrents).')'):'').')');		
				}
				elseif(Tools::getValue('fav')==-3)									//GOLD - DIAMOND
				{																	//GOLD - DIAMOND
					$favorisProduct = Db::getInstance()->executeS('
					SELECT DISTINCT P.id_product AS id_product
					FROM `'._DB_PREFIX_.'product` P
					WHERE NOT EXISTS (SELECT FP.id_product FROM `'._DB_PREFIX_.$this->name.'_favoris_product` FP WHERE FP.id_product=P.id_product)');		
				}
				elseif(Tools::getValue('fav')==-4)									//GOLD - DIAMOND
				{																	//GOLD - DIAMOND
					$favorisProduct = Db::getInstance()->executeS('
					SELECT DISTINCT P.id_product AS id_product
					FROM `'._DB_PREFIX_.'product` P
					WHERE NOT EXISTS (SELECT A.id_associations FROM `'._DB_PREFIX_.$this->name.'_regles_association` RA, `'._DB_PREFIX_.$this->name.'_associations` A WHERE A.id_product=P.id_product AND A.id_associations = RA.id_associations ) AND EXISTS (SELECT A2.id_associations FROM `'._DB_PREFIX_.$this->name.'_associations` A2 WHERE A2.id_product=P.id_product )');
				}
				else																//GOLD - DIAMOND
				{																	//GOLD - DIAMOND
					$favorisProduct = Db::getInstance()->executeS('
					SELECT id_product
					FROM `'._DB_PREFIX_.$this->name.'_favoris_product`
					WHERE id_favoris='.(int)Tools::getValue('fav'));				//GOLD - DIAMOND
				}
																					//GOLD - DIAMOND
				$whereIn='-1';														//GOLD - DIAMOND
				foreach($favorisProduct as $fav) $whereIn.=','.$fav['id_product'];	//GOLD - DIAMOND
																					//GOLD - DIAMOND
				$where.=' AND P.id_product IN ( '.$whereIn.' )';						//GOLD - DIAMOND




			}
			
			//where shop
			$id_shop=0;
			if( _PS_VERSION_ >= '1.5' && $cookie->shopContext && preg_match("#s-([0-9]+)#isU",$cookie->shopContext,$subShop))
			{
				$id_shop=$subShop[1];
				$where.=' AND PS.id_shop='.(int)$id_shop;
			}
			
			//multiboutique
			if( _PS_VERSION_ >= '1.5') {	$from.=',`'._DB_PREFIX_.'product_shop` PS ';	$where.=' AND P.id_product=PS.id_product';	}
			
			$afficherInnactifs=Configuration::get('pricestracker_PRODUITSACTIFS');
			
			//produits actifs
			//if( Configuration::get('pricestracker_PRODUITSACTIFS') ) {	$where.=' AND P.active=1';	}
			
			//where catégorie
			if($id_category) { $where.=' AND P.id_product=CP.id_product AND CP.id_category='.$id_category; $from.=',`'._DB_PREFIX_.'category_product` CP'; }

			//where marque
			$id_manufacturer=(int)Tools::getValue('id_manufacturer');
			if($id_manufacturer) { $where.=' AND P.id_manufacturer='.$id_manufacturer; }
						
			//where produit
			$id_product=(int)Tools::getValue('id_product');
			if($id_product) $where.=' AND P.id_product='.$id_product;
			
			//order
			$orderBy=Tools::getValue('orderBy');
			$order='L.name ASC';
			if($orderBy=='creation') $order='P.date_add DESC';
			elseif($orderBy=='reference') $order='P.reference ASC';
			
			//where mode test
			if($this->modeTest)
			{
				$modeTest=$this->modeTest;
				if(Configuration::getGlobalValue('pricestracker_MODETEST') && !is_string($this->modeTest)) $modeTest=Configuration::getGlobalValue('pricestracker_MODETEST');
				$produit_plus_vendus=Db::getInstance()->executeS('SELECT p.`id_product` AS id_product
						FROM `'._DB_PREFIX_.'product` p
						LEFT JOIN `'._DB_PREFIX_.'product_sale` ps ON p.`id_product` = ps.`id_product`
						'.(_PS_VERSION_ >= '1.5'?Shop::addSqlAssociation('product_sale', 'ps', false):'').'
						WHERE 1 = 1 '.(!$afficherInnactifs?'':'AND p.active=1').' '.(is_string($modeTest)?' AND p.`id_product` IN ('.$modeTest.')':'').'
						ORDER BY ps.sale_nbr DESC,p.`id_product` DESC
						LIMIT '.$nbProduitsTest);
				$whereInIds='-1';
				foreach($produit_plus_vendus as $prodId) $whereInIds.=','.$prodId['id_product'];
				$where.=' AND P.id_product IN ('.$whereInIds.')';
			}
	
			if(empty($tabConcurrents)) return 'You must add competitors';
			
			$nbProduuitsParPages=(int)Configuration::get('pricestracker_NbParPage_corr');
			if(!$nbProduuitsParPages) $nbProduuitsParPages=20;
			$nbProduitsAafficher=max(1,round($nbProduuitsParPages/count($tabConcurrents)));
			//nombre de pages
			$total = 'SELECT COUNT(DISTINCT P.id_product)
			FROM `'._DB_PREFIX_.'product_lang` L,`'._DB_PREFIX_.'product` P '.$from.'
			WHERE id_lang='.$langue.' '.(!$afficherInnactifs?'':'AND P.active=1').' AND L.id_product=P.id_product '.$where;
			$total=Db::getInstance()->getValue($total);
			$total=ceil($total/$nbProduitsAafficher);

			$page_init=$page;
			$isPrixGros=Configuration::get('pricestracker_PRIXGROS');
			$isHT=Configuration::get('pricestracker_HT');
			$isPrixBase=Configuration::get('pricestracker_PRIXBASE');


			$tabProduits=array();
			
			
			do
			{
				if(!$page) $page=1;
				//recherehce des produits
				$produits = Db::getInstance()->executeS('
				SELECT P.id_product AS id_product
				FROM `'._DB_PREFIX_.'product_lang` L,`'._DB_PREFIX_.'product` P '.$from.'
				WHERE id_lang='.$langue.' '.(!$afficherInnactifs?'':'AND P.active=1').' AND L.id_product=P.id_product '.$where.'
				GROUP BY P.id_product
				ORDER BY '.$order.'
				LIMIT '.(!$this->modeTest?((($page-1)*$nbProduitsAafficher).','.$nbProduitsAafficher):$nbProduitsTest));


				foreach($produits as $p)
				{
					$nvlElement=new Product($p['id_product']);
						
					if($isPrixBase) $monPrix=$nvlElement->price;
					else
					{
						if($isPrixGros)
						{
							if($isHT) $monPrix=$nvlElement->getPriceMin(false);
							else $monPrix=$nvlElement->getPriceMin();
						}
						else
						{
							if($isHT) $monPrix=$nvlElement->getPrice(false);
							else $monPrix=$nvlElement->getPrice();
						}
					}

	
					$ne_pas_prendre=false;
					if($monPrix<str_replace(',','.',Tools::getValue('prix_min')) || (Tools::getValue('prix_max')!='' && $monPrix>str_replace(',','.',Tools::getValue('prix_max'))) ) $ne_pas_prendre=true;
					
					if(!$ne_pas_prendre) $tabProduits[]=$nvlElement;

				}
			} while( count($tabProduits)<$nbProduitsAafficher && ++$page < $total );
			//$total=$total-($page-$page_init)+1;
			if($page > $total) $page=$total;
			
			//recherehce des produits
			if(!$page) $page=1;
			$produits = Db::getInstance()->executeS('
			SELECT P.id_product AS id_product
			FROM `'._DB_PREFIX_.'product_lang` L,`'._DB_PREFIX_.'product` P '.$from.'
			WHERE id_lang='.$langue.' '.(!$afficherInnactifs?'':'AND P.active=1').' AND L.id_product=P.id_product '.$where.'
			GROUP BY P.id_product
			ORDER BY '.$order.'
				LIMIT '.(!$this->modeTest?((($page-1)*$nbProduitsAafficher).','.$nbProduitsAafficher):$nbProduitsTest));
			
			$tabProduits=array();
			foreach($produits as $p)
			{
				$prod=new Product($p['id_product']);
				$tabProduits[]=$prod;
			}

			//favoris
			$favoris = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.$this->name.'_favoris`
			WHERE id_shop=0 OR id_shop='.(int)$id_shop);

			
			if( _PS_VERSION_ >= '1.6')
			{
				//catégories
				$currentIndex = '&id_category='.(int)$id_category;
				if (!$id_category)
					$id_category = 1;
	
				// arbre catégories
				$tree = new HelperTreeCategories('categories-tree', $this->l('Filter by category'));
				$tree->setAttribute('is_category_filter', (bool)$id_category)
					->setAttribute('base_url', preg_replace('#&id_category=[0-9]*#', '', $currentIndex).'&token=')
					->setInputName('id-category')
					->setSelectedCategories(array((int)$id_category));
				$smarty->assign('category_tree', $tree->render());
			}



			$smarty->assign('id_product', $id_product );
			$smarty->assign('id_category', (int)Tools::getValue('id_category') );
			$smarty->assign('get_id_concurrents', Tools::getValue('id_concurrents') );
			$smarty->assign('id_concurrents', $id_concurrents );
			$smarty->assign('rech', Tools::getValue('rech') );
			$smarty->assign('total', $total );
			$smarty->assign('produits', $tabProduits );
			$smarty->assign('page', $page );
			$smarty->assign('htmlPage', $this->pagesProposer($page,$total,$lien.'&croisement&rech='.urlencode(stripslashes(Tools::getValue('rech'))).'&fav='.($this->version2=='Silver'?0:((int)Tools::getValue('fav'))).'&id_category='.((int)Tools::getValue('id_category')).'&id_concurrents='.Tools::getValue('id_concurrents').'&prix_min='.Tools::getValue('prix_min').'&prix_max='.Tools::getValue('prix_max').'&id_manufacturer='.Tools::getValue('id_manufacturer').'&page=', 'padding:4px; display:inline' ));
			$smarty->assign('fav', $this->version2=='Silver'?0:((int)Tools::getValue('fav')) ); //limitation pour la version Silver
			$smarty->assign('favoris', $favoris );
			$smarty->assign('langue', $langue );
			$smarty->assign('concurrents', $tabConcurrents );
			$smarty->assign('concurrents_url', $tabConcurrentsUrl );
			$smarty->assign('concurrents2', $tabConcurrents2 );
			$smarty->assign('marques', $marques );
			$smarty->assign('dernier_crawl', Configuration::getGlobalValue('pricestracker_DERNIER_CRAWL') );
			$smarty->assign('id_manufacturer', $id_manufacturer );
			$smarty->assign('orderBy', $orderBy );
			$smarty->assign('typeImage', method_exists('ImageType','getFormatedName')?ImageType::getFormatedName('small'):ImageType::getFormattedName('small') );
			$smarty->assign('prixDeGros', Configuration::get('pricestracker_PRIXGROS') );	
			$smarty->assign('prixDeBase', Configuration::get('pricestracker_PRIXBASE') );
			$smarty->assign('preremplissageRecherche', Configuration::get('pricestracker_preremplissageRecherche') );			
			$smarty->assign('ht', Configuration::get('pricestracker_HT') );	
			$smarty->assign('version2', $this->version2 );
			$smarty->assign('devise', $this->getDevise() );
			$smarty->error_reporting = 0;
			$smarty->assign('link', $this->context->link );
			$smarty->assign('modeTest', $this->modeTest );
			
			$this->context->controller->addJS( $this->_path .'js/youtubepopup.js');
			return $this->display(__FILE__, 'views/croisement.tpl');
		}
		elseif (Tools::isSubmit('association')) //iframe association produits
		{
			if(ob_get_length()>0) ob_end_clean();
			
			//places de marchés
			$nomConcurrent=Db::getInstance()->getValue('
			SELECT nom
			FROM `'._DB_PREFIX_.'pricestracker_concurrents`
			WHERE id_concurrents='.(int)Tools::getValue('id_concurrents'));

			$version2=$this->version2;
			include('pages/association'.
			( (strpos($nomConcurrent,'Amazon')!==FALSE || $nomConcurrent=='Ebay' || $nomConcurrent=='PriceMinister' /*|| $nomConcurrent=='CDiscount'*/) ? '_marketplace' : '' )
			.'.php');
			die;
		}
		elseif (Tools::isSubmit('associationAjax')) //iframe association produits ajax
		{

			ob_end_clean();
			
			$id_product=(int)Tools::getValue('id_product');
			$id_produits_etrangers=Tools::getValue('id_produits_etrangers');
			$associer=(int)Tools::getValue('associer');
			
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_associations`
			WHERE id_product='.$id_product.' AND id_produits_etrangers='.$id_produits_etrangers);

			if($associer)
			{
				DbCoreCompaSPpricestracker::insert(
					$this->name.'_associations',
					array(
						'id_product'=>$id_product,
						'id_produits_etrangers'=>$id_produits_etrangers,
					)
				);
				echo'1';
			}
			else echo'0';
			die;
		}
		elseif (Tools::isSubmit('comparaison') || Tools::isSubmit('exporter')) //comparaison de prix
		{
			//pages
			$page=(int)Tools::getValue('page');
			if(!$page) $page=1;

			$afficherInnactifs=Configuration::get('pricestracker_PRODUITSACTIFS');
			
			//langue
			$langue=(int)Configuration::get('PS_LANG_DEFAULT');
			//concurrents
			$concurrents = Db::getInstance()->executeS('
			SELECT nom,id_concurrents
			FROM `'._DB_PREFIX_.$this->name.'_concurrents`
			WHERE 1=1');
			$tabConcurrents=array();
			foreach($concurrents as $c)
			{
				$tabConcurrents[$c['id_concurrents']] = $c['nom'];
			}
			
			//filtre catégorie
			$id_category=(int)Tools::getValue('id_category');
			
			//filtre concurrents
			$id_concurrents=array();
			$tab_concurrents=preg_split("#[^0-9\-]+#is",Tools::getValue('id_concurrents'));
			foreach($tab_concurrents as $v)
			{
				if($v && $v!=-1) $id_concurrents[]=$v;
			}
			$id_concurrents=array_unique($id_concurrents);

			//marques
			$marques_db = Manufacturer::getManufacturers();
			$marques=array();
			foreach($marques_db as $m)
			{
				$marques[$m['id_manufacturer']] = $m['name'];
			}
			
		
			$where='';
			$from='';
			//where recherche
			if(Tools::getValue('rech'))
			{
				if(isset($_POST['rech']) && $_POST['rech']) $page=1;
				$mots=preg_split("#\s+#is", $this->stripAccents(pSQL(Tools::getValue('rech'))) );
				foreach($mots as $mot) $where.=' AND (L.name LIKE \'%'.$mot.'%\' OR P.reference LIKE \'%'.$mot.'%\' OR P.ean13 LIKE \'%'.$mot.'%\' OR P.id_product = \''.$mot.'\')';
			}
			//where favoris
			if(Tools::getValue('fav') && $this->version2!='Silver')
			{
				
				
				
				
				//favoris															//GOLD - DIAMOND
				if(Tools::getValue('fav')==-1)										//GOLD - DIAMOND
				{
					$favorisProduct = Db::getInstance()->executeS('
					SELECT DISTINCT id_product
					FROM `'._DB_PREFIX_.$this->name.'_associations`
					WHERE 1=1');													//GOLD - DIAMOND
				}
				elseif(Tools::getValue('fav')==-2)									//GOLD - DIAMOND
				{																	//GOLD - DIAMOND
					$favorisProduct = Db::getInstance()->executeS('
					SELECT DISTINCT P.id_product AS id_product
					FROM `'._DB_PREFIX_.'product` P
					WHERE NOT EXISTS (SELECT A.id_product FROM `'._DB_PREFIX_.$this->name.'_associations` A WHERE A.id_product=P.id_product)');													//GOLD - DIAMOND
				}
				elseif(Tools::getValue('fav')==-3)									//GOLD - DIAMOND
				{																	//GOLD - DIAMOND
					$favorisProduct = Db::getInstance()->executeS('
					SELECT DISTINCT P.id_product AS id_product
					FROM `'._DB_PREFIX_.'product` P
					WHERE NOT EXISTS (SELECT FP.id_product FROM `'._DB_PREFIX_.$this->name.'_favoris_product` FP WHERE FP.id_product=P.id_product)');		
				}
				elseif(Tools::getValue('fav')==-4)									//GOLD - DIAMOND
				{																	//GOLD - DIAMOND
					$favorisProduct = Db::getInstance()->executeS('
					SELECT DISTINCT P.id_product AS id_product
					FROM `'._DB_PREFIX_.'product` P
					WHERE NOT EXISTS (SELECT A.id_associations FROM `'._DB_PREFIX_.$this->name.'_regles_association` RA, `'._DB_PREFIX_.$this->name.'_associations` A WHERE A.id_product=P.id_product AND A.id_associations = RA.id_associations ) AND EXISTS (SELECT A2.id_associations FROM `'._DB_PREFIX_.$this->name.'_associations` A2 WHERE A2.id_product=P.id_product )');
				}
				else																//GOLD - DIAMOND
				{																	//GOLD - DIAMOND
					$favorisProduct = Db::getInstance()->executeS('
					SELECT id_product
					FROM `'._DB_PREFIX_.$this->name.'_favoris_product`
					WHERE id_favoris='.(int)Tools::getValue('fav'));				//GOLD - DIAMOND
				}
																					//GOLD - DIAMOND
				$whereIn='-1';														//GOLD - DIAMOND
				foreach($favorisProduct as $fav) $whereIn.=','.$fav['id_product'];	//GOLD - DIAMOND
																					//GOLD - DIAMOND
				$where.=' AND P.id_product IN ( '.$whereIn.' )';						//GOLD - DIAMOND




			}
			
			//where Shop
			$id_shop=0;
			if( _PS_VERSION_ >= '1.5' && $cookie->shopContext && preg_match("#s-([0-9]+)#isU",$cookie->shopContext,$subShop))
			{
				$id_shop=$subShop[1];
				$where.=' AND PS.id_shop='.(int)$id_shop;
			}
						
			//where catégorie
			if($id_category) { $where.=' AND CP.id_category='.$id_category; $from.=' JOIN `'._DB_PREFIX_.'category_product` CP ON P.id_product=CP.id_product'; }
			
			//where produit
			if(Tools::isSubmit('id_product')) { $where.=' AND P.id_product='.(int)Tools::getValue('id_product'); }
			
			//where concurrents
			if($id_concurrents) { $where.='   AND SPE.id_concurrents IN ('.implode(',',$id_concurrents).')'; $from.=' JOIN `'._DB_PREFIX_.'pricestracker_associations` SA ON P.id_product=SA.id_product JOIN `'._DB_PREFIX_.'pricestracker_produits_etrangers` SPE ON SPE.id_produits_etrangers=SA.id_produits_etrangers'; }
			
			//where marque
			$id_manufacturer=(int)Tools::getValue('id_manufacturer');
			if($id_manufacturer) { $where.=' AND P.id_manufacturer='.$id_manufacturer; }
			
			//order
			$orderBy=Tools::getValue('orderBy');
			$order='L.name ASC';
			if($orderBy=='creation') $order='P.date_add DESC';
			elseif($orderBy=='reference') $order='P.reference ASC';

			//where mode test
			if($this->modeTest)
			{				
				$modeTest=$this->modeTest;
				if(Configuration::getGlobalValue('pricestracker_MODETEST') && !is_string($this->modeTest)) $modeTest=Configuration::getGlobalValue('pricestracker_MODETEST');

				$produit_plus_vendus=Db::getInstance()->executeS('SELECT p.`id_product` AS id_product
						FROM `'._DB_PREFIX_.'product` p
						LEFT JOIN `'._DB_PREFIX_.'product_sale` ps ON p.`id_product` = ps.`id_product`
						'.(_PS_VERSION_ >= '1.5'?Shop::addSqlAssociation('product_sale', 'ps', false):'').'
						WHERE 1 = 1 '.(!$afficherInnactifs?'':'AND p.active=1').' '.(is_string($modeTest)?' AND p.`id_product` IN ('.$modeTest.')':'').'
						ORDER BY ps.sale_nbr DESC,p.`id_product` DESC
						LIMIT '.$nbProduitsTest);
				$whereInIds='-1';
				foreach($produit_plus_vendus as $prodId) $whereInIds.=','.$prodId['id_product'];
				$where.=' AND P.id_product IN ('.$whereInIds.')';
			}
			
			//multiboutique
			if( _PS_VERSION_ >= '1.5') {	$from.=' JOIN `'._DB_PREFIX_.'product_shop` PS ON P.id_product=PS.id_product ';	}
			
			//produits actifs
			//if( Configuration::get('pricestracker_PRODUITSACTIFS') ) {	$where.=' AND P.active=1';	}
			
			
			$nbProduuitsParPages=(int)Configuration::get('pricestracker_NbParPage_comp');
			if(!$nbProduuitsParPages) $nbProduuitsParPages=30;
	
			//pb pages
			$total = 'SELECT COUNT(DISTINCT P.id_product)
			FROM `'._DB_PREFIX_.'product_lang` L,`'._DB_PREFIX_.'product` P '.$from.'
			WHERE id_lang='.$langue.' '.(!$afficherInnactifs?'':'AND P.active=1').' AND L.id_product=P.id_product '.$where;
			$total=Db::getInstance()->getValue($total);
			$total=ceil($total/$nbProduuitsParPages);
			
			
			$tabProduits=array();
			$tabProduitsEtrangers=array();
			$nbProdConcurrents=array();
				
	
			$isPrixGros=Configuration::get('pricestracker_PRIXGROS');
			$isHT=Configuration::get('pricestracker_HT');
			$isPrixBase=Configuration::get('pricestracker_PRIXBASE');
			
			$page_init=$page;
			do
			{
					
				//recherche produits
				$produits = Db::getInstance()->executeS('
				SELECT P.id_product AS id_product, Pr.prix AS prixPropose
				FROM `'._DB_PREFIX_.'product_lang` L JOIN `'._DB_PREFIX_.'product` P LEFT JOIN `'._DB_PREFIX_.$this->name.'_proposition` Pr ON Pr.id_product=P.id_product '.$from.'
				WHERE id_lang='.$langue.' '.(!$afficherInnactifs?'':'AND P.active=1').' AND L.id_product=P.id_product '.$where.'
				GROUP BY P.id_product
				ORDER BY '.$order
					.(!$this->modeTest?(
						((!Tools::isSubmit('exporterHoriz') && !Tools::isSubmit('exporter'))?   (' LIMIT '.(($page-1)*$nbProduuitsParPages).','.$nbProduuitsParPages)   :  '')
					):' LIMIT '.$nbProduitsTest)
				);
				
	
				//mise en tableau des produits



				foreach($produits as $p)
				{
					$product=new Product($p['id_product']);
					$product->prix_propose=$p['prixPropose'];

					
					//recherche des produits associés
					$produitsEtrangers = Db::getInstance()->executeS('
					SELECT A.id_produits_etrangers,A.id_associations,A.seuil_programmer
					FROM `'._DB_PREFIX_.$this->name.'_associations` A ,`'._DB_PREFIX_.$this->name.'_produits_etrangers` PE
					WHERE A.id_product='.$p['id_product'].' AND PE.id_produits_etrangers=A.id_produits_etrangers '.($id_concurrents?(' AND PE.id_concurrents IN ('.implode(',',$id_concurrents).')'):'')
					.' ORDER BY id_concurrents ASC');

					
					$tabProduitsEtrangers[$p['id_product']]=array();
					
					foreach($produitsEtrangers as $pe)
					{
						$produitEtranger = Db::getInstance()->getRow('
						SELECT prix,nom,lien,date,frequenceMaj,id_concurrents
						FROM `'._DB_PREFIX_.$this->name.'_produits_etrangers`
						WHERE id_produits_etrangers='.$pe['id_produits_etrangers'].'
						ORDER BY id_concurrents');
						
						$isAlerteMail= Db::getInstance()->getValue('
						SELECT id_regles_association
						FROM `'._DB_PREFIX_.$this->name.'_regles_association`
						WHERE id_associations='.$pe['id_associations'].' AND id_regles=1');
						
						$seuil=false;
						if($pe['seuil_programmer'])
						{
							try {
								$seuil=eval($pe['seuil_programmer']);
							} catch (Exception $e) {
								echo 'Programmed threshold exception in code ['.$pe['seuil_programmer'].']: '.$e->getMessage()."<br>";
							}
						}
						
						$nvlElement=array(
							'seuil' => $seuil,

							'id_produits_etrangers' => $pe['id_produits_etrangers'],
							'id_associations' => $pe['id_associations'],
							'prix' => $produitEtranger['prix'],
							'nom' => $produitEtranger['nom'],
							'lien' => $produitEtranger['lien'],

							'date' => $produitEtranger['date'],
							'frequenceMaj' => $produitEtranger['frequenceMaj'],
							'isAlerteMail' => $isAlerteMail ? true:false,
							'id_concurrents' => $produitEtranger['id_concurrents'],
						);
						
						if($isPrixBase) $monPrix=$product->price;
						else
						{
							if($isPrixGros)
							{
								if($isHT) $monPrix=$product->getPriceMin(false);
								else $monPrix=$product->getPriceMin();
							}
							else
							{
								if($isHT) $monPrix=$product->getPrice(false);
								else $monPrix=$product->getPrice();
							}
						}
	
						$difference=$monPrix-$nvlElement['prix'];
						$taux_diff=0;
						if($monPrix) $taux_diff=100.*$difference/$monPrix;
						
						$ne_pas_prendre=false;
						if(Tools::getValue('type_diff')=='devise' && ( (Tools::getValue('diff_min')!='' && $difference<str_replace(',','.',(Tools::getValue('diff_min')<Tools::getValue('diff_max') || Tools::getValue('diff_min')=='' || Tools::getValue('diff_max')=='')?Tools::getValue('diff_min'):Tools::getValue('diff_max'))) || (Tools::getValue('diff_max')!='' && $difference>str_replace(',','.',(Tools::getValue('diff_min')<Tools::getValue('diff_max') || Tools::getValue('diff_min')=='' || Tools::getValue('diff_max')=='')?Tools::getValue('diff_max'):Tools::getValue('diff_min'))) )) $ne_pas_prendre=true;
						if(Tools::getValue('type_diff')=='pourc' && ( (Tools::getValue('diff_min')!='' && $taux_diff<str_replace(',','.',(Tools::getValue('diff_min')<Tools::getValue('diff_max') || Tools::getValue('diff_min')=='' || Tools::getValue('diff_max')=='')?Tools::getValue('diff_min'):Tools::getValue('diff_max'))) || (Tools::getValue('diff_max')!='' && $taux_diff>str_replace(',','.',(Tools::getValue('diff_min')<Tools::getValue('diff_max') || Tools::getValue('diff_min')=='' || Tools::getValue('diff_max')=='')?Tools::getValue('diff_max'):Tools::getValue('diff_min'))) )) $ne_pas_prendre=true;
						if($monPrix<str_replace(',','.',Tools::getValue('prix_min')) || (Tools::getValue('prix_max')!='' && $monPrix>str_replace(',','.',Tools::getValue('prix_max'))) ) $ne_pas_prendre=true;
						
						if(!$ne_pas_prendre)
						{
							$tabProduitsEtrangers[$p['id_product']][]=$nvlElement;
							@$nbProdConcurrents[$p['id_product'].'c'.$produitEtranger['id_concurrents']]++;
						}
					}
					
					if((Tools::getValue('diff_min')!='' || Tools::getValue('diff_max')!='' || Tools::getValue('prix_min')!='' || Tools::getValue('prix_max')!=''))
					{
						if($tabProduitsEtrangers[$p['id_product']]) $tabProduits[ $p['id_product'] ]=$product;
					}
					else $tabProduits[ $p['id_product'] ]=$product;
				}
				
				if(Tools::isSubmit('exporter')) //export
				{
					ob_end_clean();
				
					$fp = fopen(dirname(__FILE__).'/export.csv', 'w');
					
					$titres=array(
						utf8_decode($this->l('Your product')),
						utf8_decode($this->l('Product ID')),
						utf8_decode($this->l('Reference of your product')),
						utf8_decode($this->l('Link of your product')),
						utf8_decode($this->l('Your price')),
						utf8_decode($this->l('Comptetitor\'s price')),
						utf8_decode($this->l('Comptetitor\'s product')),
						utf8_decode($this->l('Link of the comptetitor\'s product')),
						utf8_decode($this->l('Date of update')),
						utf8_decode($this->l('Frequency of update')),
						utf8_decode($this->l('Competitor')),
					);
					if(Configuration::get('pricestracker_IDSEXPORT'))
					{
						$titres[]=utf8_decode($this->l('ID of rules'));
						$titres[]=utf8_decode($this->l('ID of favorites'));
					}
	
					fputcsv($fp,$titres ,';');
	
					if($this->version2!='Diamond' && $this->version2!='MEGA') fputcsv($fp, array('For version DIAMOND'),';');
					else
					{
					
	
	
	
						foreach ($tabProduitsEtrangers as $id_product => $groupePe) {				//DIAMOND
							$produit=$tabProduits[$id_product];										//DIAMOND
							
							
							
							if(Configuration::get('pricestracker_IDSEXPORT'))
							{
								$favoris=array();
								$favorisProduct = Db::getInstance()->executeS('
								SELECT id_favoris
								FROM `'._DB_PREFIX_.$this->name.'_favoris_product`
								WHERE id_product='.(int)$id_product);	
								foreach($favorisProduct as $favp) $favoris[]=$favp['id_favoris'];

								$regles=array();
								$regleProduct = Db::getInstance()->executeS('
								SELECT RA.id_regles
								FROM `'._DB_PREFIX_.$this->name.'_regles_association` RA,`'._DB_PREFIX_.$this->name.'_associations` A
								WHERE RA.id_associations=A.id_associations AND A.id_product='.(int)$id_product);	
								foreach($regleProduct as $rr) $regles[]=$rr['id_regles'];

							}
																									//DIAMOND
							foreach($groupePe as $pe)												//DIAMOND
							{																		//DIAMOND
								$donnees=array(													//DIAMOND
									utf8_decode(strip_tags($produit->name[$langue])),							//DIAMOND
									utf8_decode($produit->id),								//DIAMOND
									utf8_decode($produit->reference),								//DIAMOND
									utf8_decode($produit->getLink()),								//DIAMOND
									utf8_decode($produit->getPrice(Configuration::get('pricestracker_HT'))),								//DIAMOND
									utf8_decode($pe['prix']),										//DIAMOND
									utf8_decode($pe['nom']),										//DIAMOND
									utf8_decode($pe['lien']),										//DIAMOND
									utf8_decode(date('Y/m/d',$pe['date'])),							//DIAMOND
									utf8_decode($pe['frequenceMaj']),								//DIAMOND
									utf8_decode($tabConcurrents[ $pe['id_concurrents'] ]),			//DIAMOND
								);

								if(Configuration::get('pricestracker_IDSEXPORT'))
								{
									$donnees[]=utf8_decode(implode(',',array_unique($regles)));
									$donnees[]=utf8_decode(implode(',',array_unique($favoris)));
								}
								
											
								fputcsv($fp, $donnees ,';');																//DIAMOND
																									//DIAMOND
							}																		//DIAMOND
						}																			//DIAMOND
			
			
			
			
					}
					
					fclose($fp);
					
					Tools::redirect((Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_).__PS_BASE_URI__.'modules/pricestracker/export.csv');
				}
				
				if(Tools::isSubmit('exporterHoriz')) //export
				{
					ob_end_clean();
			
					$fp = fopen(dirname(__FILE__).'/export.csv', 'w');
					
					$entetes=array(
						utf8_decode($this->l('Your product')),
						utf8_decode($this->l('Product ID')),
						utf8_decode($this->l('Reference of your product')),
						utf8_decode($this->l('Your price')),
					);
					$iExport=3;
					if(Configuration::get('pricestracker_IDSEXPORT'))
					{
						$entetes[]=utf8_decode($this->l('ID of rules'));
						$entetes[]=utf8_decode($this->l('ID of favorites'));
						$iExport+=2;
					}
					
					$corresp_conc=array();
					foreach($tabConcurrents as $id_c=>$nomC)
					{
						$entetes[]=utf8_decode($nomC.' / '.$this->l('Price'));
						$entetes[]=utf8_decode($nomC.' / '.$this->l('Last update'));
						$corresp_conc[$id_c]=$iExport;
						$iExport+=2;
					}
	
					fputcsv($fp, $entetes,';');
	
					if($this->version2!='Diamond' && $this->version2!='MEGA') fputcsv($fp, array('For version DIAMOND'),';');
					else
					{
					
	
	
	
						foreach($tabProduits as $id_product=>$p)
						{
							$donnees=array(													//DIAMOND
								utf8_decode(strip_tags($p->name[$langue])),							//DIAMOND
								utf8_decode($p->id),								//DIAMOND
								utf8_decode($p->reference),								//DIAMOND
								utf8_decode($p->getPrice(Configuration::get('pricestracker_HT'))),								//DIAMOND
							);
							
							if(Configuration::get('pricestracker_IDSEXPORT'))
							{
								$favoris=array();
								$favorisProduct = Db::getInstance()->executeS('
								SELECT id_favoris
								FROM `'._DB_PREFIX_.$this->name.'_favoris_product`
								WHERE id_product='.(int)$id_product);	
								foreach($favorisProduct as $favp) $favoris[]=$favp['id_favoris'];

								$regles=array();
								$regleProduct = Db::getInstance()->executeS('
								SELECT RA.id_regles
								FROM `'._DB_PREFIX_.$this->name.'_regles_association` RA,`'._DB_PREFIX_.$this->name.'_associations` A
								WHERE RA.id_associations=A.id_associations AND A.id_product='.(int)$id_product);	
								foreach($regleProduct as $rr) $regles[]=$rr['id_regles'];
								
								$donnees[]=utf8_decode(implode(',',array_unique($regles)));
								$donnees[]=utf8_decode(implode(',',array_unique($favoris)));

							}

							$pes=$tabProduitsEtrangers[$id_product];
							foreach($pes as $pe)
							{
								$pe_id_concurrents=$pe['id_concurrents'];
								$iExport=$corresp_conc[$pe_id_concurrents];
								$donnees[$iExport]=$pe['prix'];
								$donnees[$iExport+1]=date('Y/m/d',$pe['date']);
							}
							
							for($i=0;$i<3+count($tabConcurrents)*2;$i++)
							{
								if(!isset($donnees[$i])) $donnees[$i]='';
							}
							ksort($donnees);
						
							fputcsv($fp, $donnees,';');												//DIAMOND
						}																			//DIAMOND
			
			
			
			
					}
					
					fclose($fp);
					
					Tools::redirect((Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_).__PS_BASE_URI__.'modules/pricestracker/export.csv');
				}
			} while( count($tabProduits)<$nbProduuitsParPages && ++$page < $total );
			
			//$total=$total-($page-$page_init)+1;
			if($page > $total) $page=$total;
			
			
			//favoris
			$favoris = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.$this->name.'_favoris`
			WHERE id_shop=0 OR id_shop='.(int)$id_shop);

			
			$regles = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.$this->name.'_regles`
			WHERE 1=1');

			
			
			if( _PS_VERSION_ >= '1.6')
			{
	
				//catégories
				$currentIndex = '&id_category='.(int)$id_category;
				if (!$id_category)
					$id_category = 1;
	
				// arbre catégories
				$tree = new HelperTreeCategories('categories-tree', $this->l('Filter by category'));
				$tree->setAttribute('is_category_filter', (bool)$id_category)
					->setAttribute('base_url', preg_replace('#&id_category=[0-9]*#', '', $currentIndex).'&token=')
					->setInputName('id-category')
					->setSelectedCategories(array((int)$id_category));
				$smarty->assign('category_tree', $tree->render());

			}


			$smarty->assign('id_category', (int)Tools::getValue('id_category') );
			$smarty->assign('get_id_concurrents', Tools::getValue('id_concurrents') );
			$smarty->assign('id_concurrents', $id_concurrents );
			$smarty->assign('rech', Tools::getValue('rech') );
			$smarty->assign('fav', $this->version2=='Silver'?0:((int)Tools::getValue('fav')) ); //limitation pour la version Silver
			$smarty->assign('total', $total );
			$smarty->assign('produits', $tabProduits );
			$smarty->assign('marques', $marques );
			$smarty->assign('id_manufacturer', $id_manufacturer );
			$smarty->assign('orderBy', $orderBy );
			$smarty->assign('page', $page );
			$smarty->assign('htmlPage', $this->pagesProposer($page,$total,$lien.'&comparaison&rech='.urlencode(stripslashes(Tools::getValue('rech'))).'&fav='.($this->version2=='Silver'?0:((int)Tools::getValue('fav'))).'&id_category='.((int)Tools::getValue('id_category')).'&id_concurrents='.Tools::getValue('id_concurrents').'&prix_min='.Tools::getValue('prix_min').'&prix_max='.Tools::getValue('prix_max').'&diff_max='.Tools::getValue('diff_max').'&diff_min='.Tools::getValue('diff_min').'&type_diff='.Tools::getValue('type_diff').'&id_manufacturer='.Tools::getValue('id_manufacturer').'&page=', 'padding:4px; display:inline') );
			$smarty->assign('langue', $langue );
			$smarty->assign('concurrents', $tabConcurrents );
			$smarty->assign('produitsEtrangers', $tabProduitsEtrangers );
			$smarty->assign('nbProdConcurrents', $nbProdConcurrents );
			$smarty->assign('lienAdmin', $this->getLienAdminProduit() );
			$smarty->assign('favoris', $favoris );
			$smarty->assign('regles', $regles );
			$smarty->assign('version2', $this->version2 );
			$smarty->assign('typeImage', method_exists('ImageType','getFormatedName')?ImageType::getFormatedName('small'):ImageType::getFormattedName('small') );
			$smarty->assign('versionPS', _PS_VERSION_ );
			$smarty->assign('link', $this->context->link );
			$smarty->assign('ht', Configuration::get('pricestracker_HT') );	
			$smarty->assign('prixDeGros', Configuration::get('pricestracker_PRIXGROS') );	
			$smarty->assign('modeTest',  $this->modeTest );
			$smarty->assign('prixDeBase', Configuration::get('pricestracker_PRIXBASE') );	
			$smarty->assign('devise', $this->getDevise() );
			$smarty->assign('urlPage', "http".((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')?'s':'')."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
			
			$smarty->error_reporting = 0;
			
			//$smarty->error_reporting=E_ALL & ~E_NOTICE;
			$this->context->controller->addJS( $this->_path .'js/youtubepopup.js');
			return $this->display(__FILE__, 'views/comparaison.tpl');
		}
		elseif (Tools::isSubmit('prixAjax') && $this->version2=='MEGA') //changement de prix ajax
		{
			ob_end_clean();
			
			include('pages/prix.php');
			die;
		}
		elseif (Tools::isSubmit('seuil')) //seuil programmé
		{
			if(Tools::isSubmit('submit'))
			{
				$codeSeuil=$_POST['codeSeuil'];
				if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) $codeSeuil=stripslashes($codeSeuil);
				$codeSeuil=str_replace('\\','\\\\',$codeSeuil);
				$codeSeuil=str_replace("'","\'",$codeSeuil);

				DbCoreCompaSPpricestracker::update(
					$this->name.'_associations',
					array(
						'seuil_programmer'=>$codeSeuil,
					), 'id_associations = '.(int)Tools::getValue('id')
				);
			}
			elseif(Tools::isSubmit('okFonction'))
			{
				$codeSeuil=$this->codeSeuil(Tools::getValue('fonction'));
				if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) $codeSeuil=stripslashes($codeSeuil);
				$codeSeuil=str_replace('\\','\\\\',$codeSeuil);
				$codeSeuil=str_replace("'","\'",$codeSeuil);

				DbCoreCompaSPpricestracker::update(
					$this->name.'_associations',
					array(
						'seuil_programmer'=>$codeSeuil,
					), 'id_associations = '.(int)Tools::getValue('id')
				);
			}
			
			$produitEtranger = Db::getInstance()->getValue('
					SELECT seuil_programmer
					FROM `'._DB_PREFIX_.$this->name.'_associations`
					WHERE id_associations='.(int)Tools::getValue('id'));
					
			$smarty->assign('codeSeuil', $produitEtranger );
			
			return $this->display(__FILE__, 'views/seuilProgrammer.tpl');
		}
		elseif (Tools::isSubmit('rapideMarketplace') && $this->version2=='MEGA') //comparatif rapide des marketplaces
		{
			$rech=Tools::getValue('rech');
			$trouver=false;
			$corrections='';
			
			error_reporting(0);
			
			if($rech)
			{
				//amazon
				require 'pages/libAmazon/AmazonECS.class.php';
	
				defined('AWS_API_KEY') or define('AWS_API_KEY', Configuration::get('AMAZON_AWS_KEY_ID')?Configuration::get('AMAZON_AWS_KEY_ID'):'AKIAJOAOVQ3K3JXRKDJQ' );
				defined('AWS_API_SECRET_KEY') or define('AWS_API_SECRET_KEY',Configuration::get('AMAZON_SECRET_KEY')?Configuration::get('AMAZON_SECRET_KEY'):'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' );
				defined('AWS_ASSOCIATE_TAG') or define('AWS_ASSOCIATE_TAG', 'pricestracker'.date("YmdHis"));
							
				try
				{
					$lg_amz='es'; //de, com, co.uk, ca, fr, co.jp, it, cn, es
					$amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, $lg_amz,AWS_ASSOCIATE_TAG);
					// searching again
					$response = $amazonEcs->responseGroup('Small,Images,ItemAttributes,SalesRank,OfferSummary')->category('All')->search(trim($rech));
	//print_r($response);
					if($response->Items && $response->Items->Item)
					{
						foreach($response->Items->Item as $item)
						{
							if(!is_array($response->Items->Item)) $item=$response->Items->Item;
							if(!$trouver)
							{
								$lien_amazon=$item->DetailPageURL;
								$rang_amazon=$item->SalesRank;
								$image=$item->MediumImage->URL;
								$isbn=$item->ItemAttributes->ISBN;
								$ean=$item->ItemAttributes->EAN;
								$titre=$item->ItemAttributes->Title;
								$prix_conseille=$item->ItemAttributes->ListPrice->Amount/100;
								$prix_amazon=$item->OfferSummary->LowestNewPrice->Amount/100;
								$prix_occas_amazon=$item->OfferSummary->LowestUsedPrice->Amount/100;
								//$prix_collection_amazon=$item->OfferSummary->LowestCollectiblePrice->Amount/100;
								$id_amazon=$item->ASIN;

								$trouver=true;
								
								if(!is_array($response->Items->Item)) break;
							}
							else
							{
								$corrections.='<div><a data-ean="'.$item->ItemAttributes->EAN.'" class="correction" href="#">'.$item->ItemAttributes->Title.'</a></div>';
							}
						}
					}
				}
				catch(Exception $e)
				{
				  echo $e->getMessage();
				}
	
				if($trouver)
				{
					$resp = simplexml_load_file('https://ws.priceminister.com/listing_ssl_ws?action=listing&login='.(Configuration::get('pricestracker_PM_login')?Configuration::get('pricestracker_PM_login'):'Ceubex').'&pwd='.(Configuration::get('pricestracker_PM_token')?Configuration::get('pricestracker_PM_token'):'a15a388fef514f6b8c7590e5a71b3c71').'&version=2015-07-05&scope=PRICING&nbproductsperpage=1&refs='.urlencode($ean));
					
					if($resp->response->status=='ok')
					{
						foreach($resp->response->products->product as $item) {
							$image_priceminister   = $item->image->url;
							$lien_priceminister  = $item->url;
							$titre_priceminister = $item->headline;
							$prix_priceminister = trim($item->bestprices->new->advertprice->amount);
							$frais_priceminister=trim($item->bestprices->new->shippingcost->amount);
							$prix_occas_priceminister = trim($item->bestprices->used->advertprice->amount);
							$frais_occas_priceminister=trim($item->bestprices->used->shippingcost->amount);
							$reference_priceminister=$item->references->barcode;
							$id_priceminister=trim($item->productid);
							
							//print_r($item->bestprices);
						  
							break;
						}
					}
				}
			}
			
			$smarty->assign('trouver', $trouver );
			$smarty->assign('corrections', $corrections );


			$smarty->assign('rang_amazon', $rang_amazon );
			$smarty->assign('image', $image );
			$smarty->assign('isbn', $isbn );
			$smarty->assign('ean', $ean );
			$smarty->assign('titre', $titre );
			$smarty->assign('prix_conseille', $prix_conseille );
			$smarty->assign('reference_priceminister', $reference_priceminister );
			
			$smarty->assign('lien_amazon', $lien_amazon );
			$smarty->assign('prix_amazon', $prix_amazon );
			$smarty->assign('prix_occas_amazon', $prix_occas_amazon );
			$smarty->assign('id_amazon', $id_amazon );
			
			$smarty->assign('image_priceminister', $image_priceminister );
			$smarty->assign('lien_priceminister', $lien_priceminister );
			$smarty->assign('titre_priceminister', $titre_priceminister );
			$smarty->assign('prix_priceminister', $prix_priceminister );
			$smarty->assign('frais_priceminister', $frais_priceminister );
			$smarty->assign('prix_occas_priceminister', $prix_occas_priceminister );
			$smarty->assign('frais_occas_priceminister', $frais_priceminister );
			$smarty->assign('id_priceminister', $id_priceminister );
			
			$smarty->error_reporting = 0;
			return $this->display(__FILE__, 'views/rapideMarketplace.tpl');
		}
		elseif (Tools::isSubmit('regles') || Tools::isSubmit('supprRegle') || Tools::isSubmit('supprToutesReglesAsso')) //règles
		{
			if(Tools::isSubmit('supprRegle'))
			{
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_regles`
				WHERE id_regles='.(int)Tools::getValue('id'));
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_regles_association`
				WHERE id_regles='.(int)Tools::getValue('id'));
			}
			if(Tools::isSubmit('supprToutesReglesAsso'))
			{
				Db::getInstance()->Execute('TRUNCATE TABLE  `'._DB_PREFIX_.$this->name.'_regles_association`');
			}
			
			
			$regles = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.$this->name.'_regles`
			WHERE 1=1');
	
			$smarty->assign('regles', $regles );
			return $this->display(__FILE__, 'views/regles.tpl');
		}
		elseif ((Tools::isSubmit('regleFavoris') || Tools::isSubmit('supprRegleFav')) && $this->version2=='MEGA') //ajout règle
		{
			$id_concurrent=preg_replace("#[^0-9,]#isU","",Tools::getValue('id_concurrent'));
			$id_favoris=preg_replace("#[^0-9,]#isU","",Tools::getValue('id_fav'));
			$arguments=Tools::getValue('arguments');
			$associationsMultiples=(int)Tools::getValue('associationsMultiples');
			if($arguments=='null') die("ERROR Argument = null");
			
			$id_regle=(int)Tools::getValue('id_regle');
			
			$produits = Db::getInstance()->executeS('
			SELECT DISTINCT A.id_associations
			FROM '.($id_favoris?'`'._DB_PREFIX_.'pricestracker_favoris_product` FP,':'').'`'._DB_PREFIX_.'pricestracker_associations` A'.($id_concurrent?',`'._DB_PREFIX_.'pricestracker_produits_etrangers` PE':'').'
			WHERE 1=1 '
			.($id_favoris?' AND FP.id_product=A.id_product AND FP.id_favoris IN ('.$id_favoris.')':'').'  '
			.($id_concurrent?' AND PE.id_produits_etrangers=A.id_produits_etrangers AND PE.id_concurrents IN ('.$id_concurrent.')':'')
			.(!$associationsMultiples?' AND NOT EXISTS (SELECT id_regles_association FROM `'._DB_PREFIX_.'pricestracker_regles_association` RA WHERE RA.id_regles = '.(int)$id_regle.' AND RA.id_associations = A.id_associations)':'')
			);
			
			foreach($produits as $id)
			{
				if(Tools::isSubmit('supprRegleFav'))
				{
					Db::getInstance()->Execute('
					DELETE FROM `'._DB_PREFIX_.'pricestracker_regles_association`
					WHERE id_associations='.(int)$id['id_associations'].' AND id_regles='.$id_regle
					);
				}
				else
				{
					DbCoreCompaSPpricestracker::insert(
						'pricestracker_regles_association',
						array(
							'id_associations'=>(int)$id['id_associations'],
							'arguments'=>pSQL($arguments),
							'id_regles'=>$id_regle,
						)
					);
				}
			}
			die;
		}
		elseif (Tools::isSubmit('ajRegle')) //ajout règle
		{
			if(!Tools::isSubmit('id'))
			{
				DbCoreCompaSPpricestracker::insert(
					$this->name.'_regles',
					array(
						'nom'=>$this->l('New pricing rule'),
					)
				);
				$id=Db::getInstance()->Insert_ID();
				
				header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&id='.$id);
				die;
			}
			else
			{
				$id=(int)Tools::getValue('id');
				
				if(Tools::isSubmit('submit'))
				{
					$codeRegle=$_POST['regle'];
					if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) $codeRegle=stripslashes($codeRegle);
					$codeRegle=str_replace('\\','\\\\',$codeRegle);
					$codeRegle=str_replace("'","\'",$codeRegle);

					DbCoreCompaSPpricestracker::update(
						$this->name.'_regles',
						array(
							'nom'=>pSQL(Tools::getValue('nom')),
							'regle'=>$codeRegle,
						), 'id_regles = '.$id
					);
					
					
					//enregistrement en archive sur le wiki société
					$domaine_site=$_SERVER['SERVER_NAME'];
					$titre_wiki='*Règle de prix* '.ucfirst(Tools::getValue('nom').' ('.$domaine_site.')');
					
					$contenu_wiki.="== Données client ==\n\n"."http://".$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]."\n\n";
					$contenu_wiki.=Configuration::get('PS__NAME')."\n\n";
					$contenu_wiki.='Licence : '.$this->licence."\n\n";
					$contenu_wiki.=(Configuration::get('PS__EMAIL')?Configuration::get('PS__EMAIL'):Configuration::get('PS_SHOP_EMAIL'))."\n\n";
					$contenu_wiki.=$this->getBaseLink()."\n\n";
					$cookie=Context::getContext()->cookie;
					if($cookie->id_employee)
					{
						$empl=new Employee($cookie->id_employee);
						$contenu_wiki.=$empl->firstname.' '.$empl->lastname."\n\n";
					}
					$contenu_wiki.="== Auteur ==\n\n";
					$contenu_wiki.="IP : ".Tools::getRemoteAddr()."\n\n";
					$contenu_wiki.="Navigateur : ".$_SERVER['HTTP_USER_AGENT']."\n\nDepuis le module"."\n\n";
					$contenu_wiki.="\n\n";
					
					$codeRegle2=$_POST['regle'];
					if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) $codeRegle=stripslashes($codeRegle2);
					$contenu_wiki.="== Règle ==\n\n".'<source lang="php">'.$codeRegle2.'</source>'."\n\n";
					
					$this->post(array(
						'nom_page'=>str_replace(array('[',']','{','}','|','#'),'-',$titre_wiki),
						'contenu'=>$contenu_wiki,
						'categories'=>'Règles de prix',
					),
					'https://www.storeinterfacer.com/ajouterWikiPricestracker.php');
				}
			}
			
			$regle = Db::getInstance()->getRow('SELECT *
				FROM `'._DB_PREFIX_.$this->name.'_regles`
				WHERE `id_regles` = '.$id
			);
			
			$smarty->assign('id', (int)Tools::getValue('id') );
			$smarty->assign('regle', $regle );
			$smarty->assign('site', $_SERVER['HTTP_HOST'] );
			$smarty->assign('clef', (_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') ) );
			return $this->display(__FILE__, 'views/ajRegle.tpl');
		}
		elseif (Tools::isSubmit('regleAjax')) //comparaison de prix- regle
		{
			$aucune=$this->l('None');
			$txt_args=$this->l('Arguments (between ,)');
			include('pages/regles.php');
			die;
		}
		elseif (Tools::isSubmit('notificationAjax') && $this->version2=='MEGA') //alerte mail
		{
			ob_end_clean();
			
			include('pages/reglesNotification.php');
			die;
		}
		elseif (Tools::isSubmit('frequenceAjax')) //comparaison de prix- fréquence màj
		{
			ob_end_clean();
			
			if($this->version2=='Silver') die;					//limitation de la Silver
			
			include('pages/frequence.php');
			die;
		}
		elseif (Tools::isSubmit('favorisAjax')) //comparaison de prix- favoris
		{
			ob_end_clean();
			
			$aucun=$this->l('None');
			include('pages/favoris.php');
			die;
		}
		elseif (Tools::isSubmit('favoris') || Tools::isSubmit('supprFavoris')) //favoris
		{
			if(Tools::isSubmit('supprFavoris'))
			{
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_favoris`
				WHERE id_favoris='.(int)Tools::getValue('id'));
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_favoris_product`
				WHERE id_favoris='.(int)Tools::getValue('id'));
			}
			
			
			$favoris = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.$this->name.'_favoris`
			WHERE 1=1');
	
			$smarty->assign('favoris', $favoris );
			return $this->display(__FILE__, 'views/favoris.tpl');
		}
		elseif (Tools::isSubmit('ajFavoris')) //ajout favoris
		{
			if(!Tools::isSubmit('id'))
			{
				$id_shop=0;
				if($cookie->shopContext && preg_match("#s-([0-9]+)#isU",$cookie->shopContext,$subShop)) $id_shop=$subShop[1];

				DbCoreCompaSPpricestracker::insert(
					$this->name.'_favoris',
					array(
						'nom'=>$this->l('New favorite'),
						'id_shop'=>(int)$id_shop,
					)
				);
				$id=Db::getInstance()->Insert_ID();
				
				header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&id='.$id);
				die;
			}
			else
			{
				$id=(int)Tools::getValue('id');
				
				if(Tools::isSubmit('submit'))
				{
					DbCoreCompaSPpricestracker::update(
						$this->name.'_favoris',
						array(
							'nom'=>pSQL(Tools::getValue('nom')),
						), 'id_favoris = '.$id
					);
				}
			}
			
			$favoris = Db::getInstance()->getRow('SELECT *
				FROM `'._DB_PREFIX_.$this->name.'_favoris`
				WHERE `id_favoris` = '.$id
			);
			
			$smarty->assign('id', (int)Tools::getValue('id') );
			$smarty->assign('favoris', $favoris );
			return $this->display(__FILE__, 'views/ajFavoris.tpl');
		}
		elseif (Tools::isSubmit('ajFavCat') && $this->version2=='MEGA') //ajout de toute une catégorie
		{
			ob_end_clean();
			
			$id_fav=(int)Tools::getValue('id_fav');
			$categorie=new Category((int)Tools::getValue('id_category'));
			$produits=$categorie->getProductsWs();
			foreach($produits as $id)
			{
				DbCoreCompaSPpricestracker::insert(
					'pricestracker_favoris_product',
					array(
						'id_product'=>(int)$id['id'],
						'id_favoris'=>$id_fav,
					)
					, false, true, DbCoreCompaSPpricestracker::INSERT_IGNORE
				);
			}
			die;
		}
		elseif (Tools::isSubmit('ajFavIds') && $this->version2=='MEGA') //ajout de toute une catégorie
		{
			ob_end_clean();
			
			$id_fav=(int)Tools::getValue('id_fav');
			$ids=explode(',', Tools::getValue('ids'));
			foreach($ids as $id)
			{
				DbCoreCompaSPpricestracker::insert(
					'pricestracker_favoris_product',
					array(
						'id_product'=>(int)$id['id'],
						'id_favoris'=>$id_fav,
					)
					, false, true, DbCoreCompaSPpricestracker::INSERT_IGNORE
				);
			}
			die;
		}
		elseif (Tools::isSubmit('supprFavIds') && $this->version2=='MEGA') //ajout de toute une catégorie
		{
			ob_end_clean();
			
			$id_fav=(int)Tools::getValue('id_fav');
			$ids=explode(',', Tools::getValue('ids'));
			foreach($ids as $id)
			{
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_favoris_product`
				WHERE id_product='.(int)(int)$id['id'].' AND id_favoris='.$id_fav);
			}
			die;
		}
		elseif (Tools::isSubmit('supprTousFav') && $this->version2=='MEGA') //ajout de toute une catégorie
		{
			ob_end_clean();
			
			$id_fav=(int)Tools::getValue('id_fav');
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.$this->name.'_favoris_product`
				WHERE id_favoris='.$id_fav);
			die;
		}
		elseif (Tools::isSubmit('ajFavManuf') && $this->version2=='MEGA') //ajout de toute une catégorie
		{
			$id_fav=(int)Tools::getValue('id_fav');
			$manuf=new Manufacturer((int)Tools::getValue('id_manufacturer'));
			$produits=$manuf->getProductsLite(1);
			foreach($produits as $id)
			{
				DbCoreCompaSPpricestracker::insert(
					'pricestracker_favoris_product',
					array(
						'id_product'=>(int)$id['id_product'],
						'id_favoris'=>$id_fav,
					)
					, false, true, DbCoreCompaSPpricestracker::INSERT_IGNORE
				);
			}
			die;
		}
		elseif (Tools::isSubmit('ajFavAll') && $this->version2=='MEGA') //ajout de toute une catégorie
		{
			ob_end_clean();
			
			$id_fav=(int)Tools::getValue('id_fav');
			$langue=(int)Configuration::get('PS_LANG_DEFAULT');
			$produits=Product::getProducts($langue, 0, 1000000, 'id_product', 'ASC');
			foreach($produits as $id)
			{
				DbCoreCompaSPpricestracker::insert(
					'pricestracker_favoris_product',
					array(
						'id_product'=>(int)$id['id_product'],
						'id_favoris'=>$id_fav,
					)
					, false, true, DbCoreCompaSPpricestracker::INSERT_IGNORE
				);
			}
			die;
		}
		elseif (Tools::isSubmit('ajFavNom') && $this->version2=='MEGA') //ajout des produits avec le nom suivant
		{
			ob_end_clean();
			
			$langue=(int)Configuration::get('PS_LANG_DEFAULT');

			$id_fav=(int)Tools::getValue('id_fav');
			$where=Tools::getValue('nom');
			if(!$where) die;
			$where=preg_replace_callback("#([^()|&]+)#is",array($this,'callback_ajFavNom'),$where);
			$where=preg_replace("#([^()|&]+)#is",'name LIKE \'%\1%\'',$where);
			$where=str_replace('|',' OR ',$where);
			$where=str_replace('&',' AND ',$where);
			
			$produits = Db::getInstance()->executeS('
			SELECT id_product
			FROM `'._DB_PREFIX_.'product_lang`
			WHERE id_lang='.$langue.' AND ('.$where.')
			GROUP BY id_product'
			);

			foreach($produits as $id)
			{
				DbCoreCompaSPpricestracker::insert(
					'pricestracker_favoris_product',
					array(
						'id_product'=>(int)$id['id_product'],
						'id_favoris'=>$id_fav,
					)
					, false, true, DbCoreCompaSPpricestracker::INSERT_IGNORE
				);
			}
			die;
		}
		elseif (Tools::isSubmit('historique')) //historique de prix
		{
			$id_product=(int)Tools::getValue('id');
			$productPS=new Product($id_product);
			
			$nomsSQL = Db::getInstance()->ExecuteS('SELECT PE.id_produits_etrangers AS id_produits_etrangers,PE.nom AS nom,C.nom AS concurrent
				FROM `'._DB_PREFIX_.$this->name.'_historique` H,`'._DB_PREFIX_.$this->name.'_produits_etrangers` PE,`'._DB_PREFIX_.$this->name.'_concurrents` C
				WHERE H.`id_product` = '.$id_product.' AND H.id_produits_etrangers=PE.id_produits_etrangers AND C.id_concurrents=PE.id_concurrents
				GROUP BY H.id_produits_etrangers'
			);
			
			$nomsPE=array();
			foreach($nomsSQL as $nom)
			{
				$nomsPE[ $nom['id_produits_etrangers'] ]=array(
					'nom'=>$nom['nom'],
					'concurrent'=>$nom['concurrent']
				);
			}
			
			$historique = Db::getInstance()->ExecuteS('SELECT id_produits_etrangers,date,prix
				FROM `'._DB_PREFIX_.$this->name.'_historique`
				WHERE `id_product` = '.$id_product.'
				ORDER BY id_produits_etrangers ASC,date ASC'
			);
			
			$langue=(int)Configuration::get('PS_LANG_DEFAULT');
			
			$smarty->assign('devise', $this->getDevise() );
			$smarty->assign('langue', $langue );
			$smarty->assign('productPS', $productPS );
			$smarty->assign('nomsPE', $nomsPE );
			$smarty->assign('historique', $historique );
			return $this->display(__FILE__, 'views/historique.tpl');
		}
		elseif (Tools::isSubmit('upgrade')) //upgrade
		{
			$smarty->assign('version2', $this->version2 );
			return $this->display(__FILE__, 'views/upgrade.tpl');
		}
		elseif (Tools::isSubmit('contact')) //ajout favoris
		{
			return $this->display(__FILE__, 'views/contact.tpl');
		}
		elseif (Tools::isSubmit('clear_seuil')) //ajout favoris
		{
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.$this->name.'_associations`
				SET seuil_programmer=\'\' 
				WHERE 1=1'
			);
		}
		elseif (Tools::isSubmit('clear_precalcul')) //ajout favoris
		{
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product`
				SET prix_precalcule=NULL
				WHERE 1=1'
			);
		}
		elseif(Tools::isSubmit('config'))
		{
			if(Tools::isSubmit('AWS_API_KEY')) Configuration::updateValue('AMAZON_AWS_KEY_ID', trim(Tools::getValue('AWS_API_KEY')));
			if(Tools::isSubmit('API_SECRET_KEY')) Configuration::updateValue('AMAZON_SECRET_KEY', trim(Tools::getValue('API_SECRET_KEY')));
			Configuration::updateValue('pricestracker_Ebay_AppId', trim(Tools::getValue('Ebay_AppId')));
			Configuration::updateValue('pricestracker_Ebay_GlobalID', trim(Tools::getValue('Ebay_GlobalID')));
			Configuration::updateValue('cdiscount_ApiKey', trim(Tools::getValue('cdiscount_ApiKey')));
			Configuration::updateValue('cdiscount_ApiPass', trim(Tools::getValue('cdiscount_ApiPass')));
			Configuration::updateValue('pricestracker_PM_login',trim(Tools::getValue('PM_login')));
			Configuration::updateValue('pricestracker_PM_token',trim( Tools::getValue('PM_token')));
			
			Configuration::updateValue($this->name.'_NbParPage_comp', (int)Tools::getValue('NbParPage_comp'));
			Configuration::updateValue($this->name.'_NbParPage_corr', (int)Tools::getValue('NbParPage_corr'));
			Configuration::updateValue($this->name.'_DERNIERS', (int)Tools::getValue('derniers'));
			Configuration::updateValue($this->name.'_JOURSMAJ', (int)Tools::getValue('joursMaj'));
			Configuration::updateValue($this->name.'_MAJASSOCIATION', (int)Tools::getValue('majAssociation'));
			Configuration::updateValue($this->name.'_HT', (int)Tools::getValue('ht'));
			Configuration::updateValue($this->name.'_PRIXBASE', (int)Tools::getValue('prixBase'));
			Configuration::updateValue($this->name.'_PRIXGROS', (int)Tools::getValue('prixGros'));
			Configuration::updateValue($this->name.'_PRODUITSACTIFS', (int)Tools::getValue('produitsActifs'));
			Configuration::updateValue($this->name.'_IDSEXPORT', (int)Tools::getValue('idEnExport'));
			Configuration::updateValue($this->name.'_SEUILS', (int)Tools::getValue('seuils'));
			Configuration::updateValue($this->name.'_exclureNom', (int)Tools::getValue('exclureNom'));
			Configuration::updateValue($this->name.'_exclureRef', (int)Tools::getValue('exclureRef'));
			Configuration::updateValue($this->name.'_exclureEan', (int)Tools::getValue('exclureEan'));
			Configuration::updateValue($this->name.'_exclureUPC', (int)Tools::getValue('exclureUPC'));
			Configuration::updateValue($this->name.'_exclureRefFournisseur', (int)Tools::getValue('exclureRefFournisseur'));
			Configuration::updateValue($this->name.'_preremplissageRecherche', Tools::getValue('preremplissageRecherche'));
			Configuration::updateGlobalValue('pricestracker_NOMULTISQL', (int)Tools::getValue('nomultisql'));
			
			if(Tools::getValue('forcer_jours_tous'))
			{
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.$this->name.'_produits_etrangers`
					SET frequenceMaj=\''.(int)Tools::getValue('joursMaj').'\' 
					WHERE 1=1'
				);
			}
		}

		$smarty->assign('cronApplet', 'Just for DIAMOND version' );
		$smarty->assign('cronRegles', 'Just for DIAMOND version' );
		$smarty->assign('cronHistorique', 'Just for DIAMOND version' );





		if($this->version2=='Diamond' || $this->version2=='MEGA')	 												 																					//DIAMOND
		{
			$smarty->assign('cronRegles', (Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_).__PS_BASE_URI__.'modules/pricestracker/pages/executeRules.php?clef='.(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') ) );		//DIAMOND
			$lienApplet= (Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_).__PS_BASE_URI__.'modules/pricestracker/pages/communication.php?clef='.(_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') );						//DIAMOND

			$smarty->assign('cronHistorique', $lienApplet.'&historique' );		//DIAMOND
			$smarty->assign('cronApplet', 'java -Djdk.http.auth.tunneling.disabledSchemes="" -Djdk.http.auth.proxying.disabledSchemes="" -Dsun.net.http.allowRestrictedHeaders=true -Djdk.tls.client.protocols="TLSv1,TLSv1.1,TLSv1.2,TLSv1.3" -Xms2g -Dhttps.protocols="TLSv1,TLSv1.1,TLSv1.2" -DontCompileHugeMethods -Dfile.encoding=UTF-8 -jar '.dirname(__FILE__).'/run.jar '.$lienApplet );																//DIAMOND
		}																																									//DIAMOND
		
		
		
		
		$smarty->assign('NbParPage_corr', Configuration::get('pricestracker_NbParPage_corr') );	
		$smarty->assign('NbParPage_comp', Configuration::get('pricestracker_NbParPage_comp') );	
		$smarty->assign('derniers', Configuration::get('pricestracker_DERNIERS') );	
		$smarty->assign('jours', Configuration::get('pricestracker_JOURSMAJ') );	
		$smarty->assign('ht', Configuration::get('pricestracker_HT') );	
		$smarty->assign('prixBase', Configuration::get('pricestracker_PRIXBASE') );	
		$smarty->assign('prixGros', Configuration::get('pricestracker_PRIXGROS') );	
		$smarty->assign('produitsActifs', Configuration::get('pricestracker_PRODUITSACTIFS') );	
		$smarty->assign('idEnExport', Configuration::get('pricestracker_IDSEXPORT') );	
		$smarty->assign('seuils', Configuration::get('pricestracker_SEUILS') );		
		$smarty->assign('exclureNom', Configuration::get('pricestracker_exclureNom') );
		$smarty->assign('exclureRefFournisseur', Configuration::get('pricestracker_exclureRefFournisseur') );		
		$smarty->assign('preremplissageRecherche', Configuration::get('pricestracker_preremplissageRecherche') );		
		$smarty->assign('nomultisql', Configuration::get('pricestracker_NOMULTISQL') );		
		$smarty->assign('exclureUPC', Configuration::get('pricestracker_exclureUPC') );		
		$smarty->assign('exclureEan', Configuration::get('pricestracker_exclureEan') );		
		$smarty->assign('exclureRef', Configuration::get('pricestracker_exclureRef') );	
		$smarty->assign('majasso', Configuration::get('pricestracker_MAJASSOCIATION') );	

		$smarty->assign('version', $this->version2 );	
		
		$smarty->assign('version_text', $this->version.' - '.$this->version2.' '.($this->stats?'<i>Stats</i>':'<i>NoStats</i>').' '.($this->modeTest?'(Test)':'').' - '.$this->licence );	
		
		$module_instance=Module::getInstanceByName($this->name);
		$isHook=!empty(Db::getInstance()->executeS("SELECT * FROM "._DB_PREFIX_."hook_module WHERE `id_hook` = ".(int) Hook::getIdByName(_PS_VERSION_ >= '1.5' ? 'displayBackOfficeTop' : 'backOfficeTop')." AND `id_module` = ".(int) $module_instance->id."  AND `id_shop` = ".Context::getContext()->shop->id));
		$smarty->assign('isHook', Context::getContext()->shop->id?$isHook:true );	

		$this->context->controller->addJS( $this->_path .'js/youtubepopup.js');
		return $this->display(__FILE__, 'views/about.tpl'); //defaut
	}




	//HOOKS

	
	//admin: onglet administration
	public function hookDisplayBackOfficeTop($params)
	{
		global $cookie;
		$lien=$this->getLien();
		$urlDebut=$this->getURLdebut();
		if( _PS_VERSION_ >= '1.7')
		{
			$jsMenu="<li class=\"link-levelone has_submenu maintab pricestracker-menu\"> <a class=\"link title has_submenu\" href=\"$lien&analyse\"><i class=\"material-icons mi-desktop_mac\">search</i> <span>".$this->l('PricesTracker')."</span></a>     <ul class=\"submenu\"> ";
			
			
			if($this->version2=='MEGA') $jsMenu.="<li class=\"has_submenu link-leveltwo\" onmouseover=\"$(\\'#sousDashboardPricesTracker\\').show()\" onmouseout=\"$(\\'#sousDashboardPricesTracker\\').hide()\"> <a class=\"link\" href=\"$lien&tableaubord\">".addslashes($this->l('Dashboard'))."</a>             </li>  <li class=\"link-leveltwo\">     <a href=\"$lien&tablCat\" class=\"link\">".addslashes($this->l('Dashboard of categories'))."     </a>    </li>           ";
																																																																 			$jsMenu.="<li class=\"link-leveltwo\"><a href=\"$lien&analyse\" class=\"link\">".addslashes($this->l('Analysis'))."</a></li>  ";
			
			$jsMenu.="<li class=\"link-leveltwo\"><a href=\"$lien&croisement\" class=\"link\">".addslashes($this->l('Product matching'))."</a></li>   	<li class=\"link-leveltwo\"><a href=\"$lien&comparaison&fav=-1\" class=\"link\">".addslashes($this->l('Competitors\' prices'))."</a></li>    ".($this->stats?("<li class=\"link-leveltwo\"><a href=\"$lien&stats\" class=\"link\">".addslashes($this->l('Statistics'))."</a></li>"):'')."  	 ";
			
			//if($this->version2=='MEGA') $jsMenu.="   <li class=\"link-leveltwo\"><a class=\"link\" href=\"$lien&rapideMarketplace\" style=\"cursor:pointer\">".addslashes($this->l('Rapid Pricing'))."</a></li>    ";
			
			$jsMenu.="	<li class=\"link-leveltwo\"><a href=\"".$urlDebut."modules/pricestracker/Manuel".(Language::getIsoById($this->context->language->id)=='fr' ? '' : '_en').".pdf\" target=\"_blank\" class=\"link\">".addslashes($this->l('User Guide/FAQ'))."</a></li>    	".($this->version2!='MEGA'?("<li class=\"link-leveltwo\"><a href=\"$lien&upgrade\" class=\"link\">".addslashes($this->l('More functions - Higher Version'))."</a></li> "):'');
			
			$jsMenu.="    </ul></li>";
			$html="<script> $(document).ready(function() {   $('ul.main-menu').append('".$jsMenu."');   $('ul.menu').append('".$jsMenu."'); 
				$('#maintab-AdminParentModules').removeClass('active');
				$('#subtab-AdminParentModulesSf').removeClass('open');
				 });  </script>";
			
			return $html;

		}		if( _PS_VERSION_ >= '1.6')
		{
			$token = Tools::getAdminTokenLite('AdminEmployees');
			$tokenDeco = Tools::getAdminTokenLite('AdminDashboard');
			$tokenImport = Tools::getAdminTokenLite('AdminImport');

			$html="<script> $(document).ready(function() {   $('ul.menu').append('<li class=\"maintab has_submenu\"> <a class=\"title\" href=\"$lien&analyse\" style=\"cursor:pointer\"><i class=\"icon-AdminParentStats\"></i> <span>".$this->l('PricesTracker')."</span></a>     <ul class=\"submenu\"> ";
			
			if($this->version2=='MEGA') $html.="<li class=\"has_submenu\" onmouseover=\"$(\\'#sousDashboardPricesTracker\\').show()\" onmouseout=\"$(\\'#sousDashboardPricesTracker\\').hide()\"> <a class=\"title\" href=\"$lien&tableaubord\">".addslashes($this->l('Dashboard'))."</a>              <ul class=\"submenu\" id=\"sousDashboardPricesTracker\" style=\"display:none; margin-left:-15px\">         <li>     <a href=\"$lien&tableaubord\">".addslashes($this->l('Main'))."     </a>    </li>           <li>     <a href=\"$lien&tablCat\">".addslashes($this->l('Categories'))."     </a>    </li>             </ul>  </li>";
																																																															 $html.="<li><a href=\"$lien&analyse\">".addslashes($this->l('Analysis'))."</a></li>  ";
			
			$html.="<li><a href=\"$lien&croisement\">".addslashes($this->l('Product matching'))."</a></li>   	<li><a href=\"$lien&comparaison&fav=-1\">".addslashes($this->l('Competitors\' prices'))."</a></li>    ".($this->stats?("<li><a href=\"$lien&stats\">".addslashes($this->l('Statistics'))."</a></li>"):'')."  	 ";
			
			//if($this->version2=='MEGA') $html.="   <li><a class=\"title\" href=\"$lien&rapideMarketplace\" style=\"cursor:pointer\">".addslashes($this->l('Rapid Pricing'))."</a></li>    ";
			
			$html.="	<li><a href=\"".$urlDebut."modules/pricestracker/Manuel".(Language::getIsoById($this->context->language->id)=='fr' ? '' : '_en').".pdf\" target=\"_blank\">".addslashes($this->l('User Guide/FAQ'))."</a></li>    	".($this->version2!='MEGA'?("<li><a href=\"$lien&upgrade\">".addslashes($this->l('More functions - Higher Version'))."</a></li> "):'');
			
			$html.="    </ul></li>');
				$('#maintab-AdminParentModules').removeClass('active');
				  });  </script>";
			
			return $html;
		}
		elseif( _PS_VERSION_ >= '1.5')
		{
			$token = Tools::getAdminTokenLite('AdminEmployees');
			$tokenDeco = Tools::getAdminTokenLite('AdminDashboard');
			$tokenImport = Tools::getAdminTokenLite('AdminImport');

			$html="<script> $(document).ready(function() {   $('ul#menu').append('<li class=\"submenu_size maintab\"> <a class=\"title\" href=\"$lien&analyse\" style=\"cursor:pointer\"><img src=\"".$urlDebut."modules/pricestracker/logo.png\" height=16 width=16 > ".$this->l('PricesTracker')."</a>     <ul class=\"submenu\"> ";
			
			if($this->version2=='MEGA') $html.="<li> <a href=\"$lien&tableaubord\">".addslashes($this->l('Dashboard'))."</a>  </li>               <li>     <a href=\"$lien&tablCat\">".addslashes($this->l('Dashboard for Categories'))."     </a>    </li>          ";
																																																															 $html.="<li><a href=\"$lien&analyse\">".addslashes($this->l('Analysis'))."</a></li>  ";
			
			$html.="<li><a href=\"$lien&croisement\">".addslashes($this->l('Product matching'))."</a></li>   	<li><a href=\"$lien&comparaison&fav=-1\">".addslashes($this->l('Competitors\' prices'))."</a></li>    ".($this->stats?("<li><a href=\"$lien&stats\">".addslashes($this->l('Statistics'))."</a></li>"):'')."  	 ";
			
			//if($this->version2=='MEGA') $html.="   <li><a href=\"$lien&rapideMarketplace\" style=\"cursor:pointer\">".addslashes($this->l('Rapid Pricing'))."</a></li>    ";
			
			$html.="	<li><a href=\"".$urlDebut."modules/pricestracker/Manuel".(Language::getIsoById($this->context->language->id)=='fr' ? '' : '_en').".pdf\" target=\"_blank\">".addslashes($this->l('User Guide/FAQ'))."</a></li>    	".($this->version2!='MEGA'?("<li><a href=\"$lien&upgrade\">".addslashes($this->l('More functions - Higher Version'))."</a></li> "):'');
			
			$html.="    </ul></li>');  });  </script>";
			
			return $html;
		}
		else //version 1.4
		{
			return "<script> $(document).ready(function() {  if($('ul#menu li:first').hasClass('active')) $('ul#submenu').append('<li> <a href=\"$lien\" style=\"cursor:pointer\"><img src=\"".$urlDebut."modules/pricestracker/logo.png\" height=16 width=16 > ".$this->l('PricesTracker')."</a>     </li>');  });  </script>";
		}
	}
	/*
	protected function initTabsDefinition()
	{
		$this->tabs = array(
			'AdminAuctionsTab' => array(
				'parent' => null,
				'active' => 1,
				'title' => array(
					Language::getIdByIso('en') => 'Auctions',
					Language::getIdByIso('pl') => 'Aukcje',
				)
			),
			'AdminAuctions' => array(
				'parent' => 'AdminAuctionsTab',
				'active' => 1,
				'title' => array(
					Language::getIdByIso('en') => 'Auctions',
					Language::getIdByIso('pl') => 'Aukcje',
				)
			),
			'AdminAuctionsView' => array(
				'parent' => 'AdminAuctions',
				'active' => 1,
				'title' => array(
					Language::getIdByIso('en') => 'Auction',
					Language::getIdByIso('pl') => 'Aukcja',
				)
			),
			'AdminAuctionsWinners' => array(
				'parent' => 'AdminAuctionsTab',
				'active' => 1,
				'title' => array(
					Language::getIdByIso('en') => 'Winners',
					Language::getIdByIso('pl') => 'Zwycięzcy',
				)
			),
			'AdminAuctionsTemplates' => array(
				'parent' => 'AdminAuctionsTab',
				'active' => 1,
				'title' => array(
					Language::getIdByIso('en') => 'Templates',
					Language::getIdByIso('pl') => 'Szablony',
				)
			),
		);
	}
	*/

	function pagesProposer($courante,$max,$lien,$style='')
	{
		$page=1;
		$html='';
		//debut
		for(;$page<5 && $page<=$max;$page++) $html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
		//autour de courante
		if($courante>$page-2)
		{
			$ancien=$page;
			$page=max($page,$courante-1);
			if($ancien!=$page) $html.='... ';
			for($iPage=0;$iPage<4 && $page<=$max;$page++)
			{
				$html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
				$iPage++;
			}
		}
		//milieu
		$milieu=round($max/2);
		if($milieu>$page-2)
		{
			$ancien=$page;
			$page=max($page,$milieu-1);
			if($ancien!=$page) $html.='... ';
			for($iPage=0;$iPage<3 && $page<=$max;$page++)
			{
				$html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
				$iPage++;
			}
		}
		//autour de courante
		if($courante>$page-2)
		{
			$ancien=$page;
			$page=max($page,$courante-1);
			if($ancien!=$page) $html.='... ';
			for($iPage=0;$iPage<4 && $page<=$max;$page++)
			{
				$html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
				$iPage++;
			}
		}
		//fin
		if($max>$page)
		{
			$ancien=$page;
			$page=max($page,$max-3);
			if($ancien!=$page) $html.='... ';
			for(;$page<=$max;$page++) $html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
		}
		
		return $html;
	}

	public function getNextRunDates($minute, $hour, $dayOfMonth, $month, $dayOfWeek, $count = 5) {
		if($minute == NULL || $minute=='' || $hour == NULL || $hour=='' || $dayOfMonth == NULL || $dayOfMonth=='' || $month == NULL || $month=='' || $dayOfWeek == NULL || $dayOfWeek=='') return array();
		$dates = array();
		$currentDate = new DateTime();
		$currentDate->setTime($currentDate->format('H'), $currentDate->format('i'), 0);
	
		while (count($dates) < $count) {
			if ($this->matchCron($currentDate, $minute, $hour, $dayOfMonth, $month, $dayOfWeek)) {
				$dates[] = $currentDate->format('Y-m-d H:i');
			}
			$currentDate->modify('+1 minute');
		}
	
		return $dates;
	}
	
	public function matchCron($date, $minute, $hour, $dayOfMonth, $month, $dayOfWeek) {
		return $this->matchPart($date->format('i'), $minute) &&
			   $this->matchPart($date->format('H'), $hour) &&
			   $this->matchPart($date->format('d'), $dayOfMonth) &&
			   $this->matchPart($date->format('m'), $month) &&
			   $this->matchPart($date->format('w'), $dayOfWeek);
	}
	
	public function matchPart($datePart, $cronPart) {
		if ($cronPart === '*') {
			return true;
		}
	
		foreach (explode(',', $cronPart) as $part) {
			if (strpos($part, '/') !== false) {
				list($range, $step) = explode('/', $part);
				if ($range === '*') {
					$range = '0-59';
				}
				list($start, $end) = explode('-', $range);
				if ($datePart >= $start && $datePart <= $end && (($datePart - $start) % $step) === 0) {
					return true;
				}
			} elseif (strpos($part, '-') !== false) {
				list($start, $end) = explode('-', $part);
				if ($datePart >= $start && $datePart <= $end) {
					return true;
				}
			} else {
				if ($datePart == $part) {
					return true;
				}
			}
		}
	
		return false;
	}

	
	public function codeSeuil($fonction)
	{
		if($fonction=='') return '';
		
		$getPrice='getPrice(';
		if( Configuration::get('pricestracker_PRIXGROS') ) $getPrice='getPriceMin(';
		if( Configuration::get('pricestracker_HT') ) $getPrice.='false';
		else $getPrice.='true';

		$prixAffichage='$product->'.$getPrice.')';
		if( Configuration::get('pricestracker_PRIXBASE') ) $prixAffichage='$product->price';
		
		$codeFonction=$fonction;
		$codeFonction=preg_replace("#^DIV\(([0-9]+)\)$#sU",'return $product->'.$getPrice.') / \1;',$codeFonction);
		$codeFonction=preg_replace("#^MULT\(([0-9]+)\)$#sU",'return $product->'.$getPrice.') * \1;',$codeFonction);
		$codeFonction=preg_replace("#^COMB\(([0-9]+)\)$#sU",'return $product->'.$getPrice.', \1 );',$codeFonction);
		$codeFonction=preg_replace("#^DIV_COMPETITOR\(([0-9]+)\)$#sU",'$produitEtranger[\'prix\'] = $produitEtranger[\'prix\'] / \1;',$codeFonction);
		$codeFonction=preg_replace("#^MULT_COMPETITOR\(([0-9]+)\)$#sU",'$produitEtranger[\'prix\'] = $produitEtranger[\'prix\'] * \1;',$codeFonction);
		$codeFonction=preg_replace("#^ADD\(([0-9]+)\)$#sU",'return $product->'.$getPrice.') + \1;',$codeFonction);
		$codeFonction=preg_replace("#^SUB\(([0-9]+)\)$#sU",'return $product->'.$getPrice.') - \1;',$codeFonction);
		$codeFonction=preg_replace("#^ADD_COMPETITOR\(([0-9]+)\)$#sU",'$produitEtranger[\'prix\'] = $produitEtranger[\'prix\'] + \1;',$codeFonction);
		$codeFonction=preg_replace("#^SUB_COMPETITOR\(([0-9]+)\)$#sU",'$produitEtranger[\'prix\'] = $produitEtranger[\'prix\'] - \1;',$codeFonction);

		if($codeFonction!=$fonction) return $codeFonction;
		return false;
	}
	
	function isSecure() {
	  return
		(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| $_SERVER['SERVER_PORT'] == 443;
	}
	
	public function getLien()
	{
		global $cookie;
		
		if( _PS_VERSION_ >= '1.5')
		{
			$token = Tools::getAdminTokenLite('AdminModules');
			$lien="index.php?controller=AdminModules&configure=".$this->name."&token=".$token;
		}
		else
		{
			$token = Tools::getAdminToken('AdminModules'.intval(Tab::getIdFromClassName('AdminModules')).intval($cookie->id_employee));
			$lien="index.php?tab=AdminModules&configure=".$this->name."&token=".$token;
		}
		
		$lien=str_replace('index.php/module/index.php','index.php',$lien);
		return $lien;
	}
	
	public function getURLdebut()
	{
		return (Tools::usingSecureMode()?_PS_BASE_URL_SSL_:_PS_BASE_URL_).__PS_BASE_URI__;
	}

	public function stripAccents($string){
		$accents = array('À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ò','Ó','Ô','Õ','Ö',
		'Ù','Ú','Û','Ü','Ý','à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ð','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ');
		$sans = array('A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','O','O','O','O','O',
		'U','U','U','U','Y','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','o','u','u','u','u','y','y');
		return str_replace($accents, $sans, $string);
	}
	
	
	public function typeProxy($concurrent){
		$proxy="SANS_PROXY";
		if(!$concurrent) return $proxy;
		
		$scriptsConcatenes=($concurrent['codeLiens'] ? "":$concurrent['codeLiens']).($concurrent['codeGroovy'] ? "":$concurrent['codeGroovy']).($concurrent['codeFinal'] ? "":$concurrent['codeFinal']);
		
		if($scriptsConcatenes && (strpos($scriptsConcatenes,"changeProxy")!==FALSE || strpos($scriptsConcatenes,"proxyChange")!==FALSE)) $proxy="PROXY_SIMPLE";
		if($concurrent['codeGroovy'] && (strpos($concurrent['codeGroovy'],"changeProxy")!==FALSE || strpos($concurrent['codeGroovy'],"proxyChange")!==FALSE)) $proxy="PROXY_TOURNANT";
		
		if(preg_match("#actionError\(.*((changeProxy)|(proxyChange)).*\)#sU", $scriptsConcatenes)) $proxy="PROXY_TOURNANT";
		
		return $proxy;
	}
	
	
	public function getLienAdminProduit()
	{
		global $cookie;
		
		if( _PS_VERSION_ >= '1.5')
		{
			$token = Tools::getAdminTokenLite('AdminProducts');
			$lien="index.php?controller=AdminProducts&key_tab=Prices&updateproduct&token=".$token;
		}
		else
		{
			$token = Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee));
			$lien="index.php?tab=AdminCatalog&updateproduct&token=".$token;
		}
		$lien=str_replace('index.php/module/index.php','index.php',$lien);
		return $lien;
	}

	public function getDevise()
	{
		$context = Context::getContext();
		$currency = $context->currency;
		$devise='€';
		if (is_array($currency))
		{
			$devise = $currency['sign'];
		}
		elseif (is_object($currency))
		{
			$devise = $currency->sign;
		}
		
		return $devise;
	}
	
	public function getCodeCloud()
	{
		$codeCloudAdmin=@$_COOKIE['CODECLOUD'];
		if($codeCloudAdmin) return $codeCloudAdmin;
		return Configuration::getGlobalValue('pricestracker_CODECLOUD');
	}
	
	public function escapeCode($variable)
	{
		$codeGroovy=@$_POST[$variable];
		if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) $codeGroovy=stripslashes($codeGroovy);
		$codeGroovy=str_replace('\\','\\\\',$codeGroovy);
		$codeGroovy=str_replace("'","\'",$codeGroovy);
		
		return $codeGroovy;
	}
	
	
	public function escapeCodeGet($variable)
	{
		$codeGroovy=@$_GET[$variable];
		if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) $codeGroovy=stripslashes($codeGroovy);
		$codeGroovy=str_replace('\\','\\\\',$codeGroovy);
		$codeGroovy=str_replace("'","\'",$codeGroovy);
		
		return $codeGroovy;
	}
	
	public function getBaseLink($idShop = null, $ssl = null, $relativeProtocol = false)
    {
        if (null === $ssl) {
            $ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
        }

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $idShop !== null) {
            $shop = new Shop($idShop);
        } else {
            $shop = Context::getContext()->shop;
        }

        if ($relativeProtocol) {
            $base = '//' . ($ssl && Configuration::get('PS_SSL_ENABLED') ? $shop->domain_ssl : $shop->domain);
        } else {
            $base = (($ssl && Configuration::get('PS_SSL_ENABLED')) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
        }

        return $base . $shop->getBaseURI();
    }
	
	public function post($postfields,$url,$binaire=false,$sansReponse=false)
	{
		if(function_exists('curl_init'))
		{
		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			if($binaire) curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			if($sansReponse)
			{
				curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 1);
			}
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			return curl_exec($ch);
			
		}
	}
	
	function callback_ajFavNom($matches) {
		return $this->stripAccents(pSQL(trim($matches[1])));
	}
	


	
	//compatibité
	public function hookBackOfficeTop($params) { return $this->hookDisplayBackOfficeTop($params); }

}





abstract class DbCoreCompaSPpricestracker
{
	const INSERT = 1;
	const INSERT_IGNORE = 2;
	const REPLACE = 3;

	static public function insert($table, $data, $null_values = false, $use_cache = true, $type = DbCoreCompaSPpricestracker::INSERT, $add_prefix = true)
	{//estimation taille catalogue pour éviter les erreurs de max_execution_time à l'affichage
		if( _PS_VERSION_ >= '1.5' ) return Db::getInstance()->insert($table, $data, $null_values , $use_cache , $type , $add_prefix);
		
		if (!$data && !$null_values)
			return true;

		if ($add_prefix)
			$table = _DB_PREFIX_.$table;

		if ($type == DbCoreCompaSPpricestracker::INSERT)
			$insert_keyword = 'INSERT';
		else if ($type == DbCoreCompaSPpricestracker::INSERT_IGNORE)
			$insert_keyword = 'INSERT IGNORE';
		else if ($type == DbCoreCompaSPpricestracker::REPLACE)
			$insert_keyword = 'REPLACE';
		else
			throw new PrestaShopDatabaseException('Bad keyword, must be Db::INSERT or Db::INSERT_IGNORE or Db::REPLACE');


		// Check if $data is a list of row
		$current = current($data);
		if (!is_array($current) || isset($current['type']))
			$data = array($data);

		$keys = array();
		$values_stringified = array();
		foreach ($data as $row_data)
		{
			$values = array();
			foreach ($row_data as $key => $value)
			{
				if (isset($keys_stringified))
				{
					// Check if row array mapping are the same
					if (!in_array("`$key`", $keys))
						throw new PrestaShopDatabaseException('Keys form $data subarray don\'t match');
				}
				else
					$keys[] = "`$key`";

				if (!is_array($value))
					$value = array('type' => 'text', 'value' => $value);
				if ($value['type'] == 'sql')
					$values[] = $value['value'];
				else
					$values[] = $null_values && ($value['value'] === '' || is_null($value['value'])) ? 'NULL' : "'{$value['value']}'";
			}
			$keys_stringified = implode(', ', $keys);
			$values_stringified[] = '('.implode(', ', $values).')';
		}

		$sql = $insert_keyword.' INTO `'.$table.'` ('.$keys_stringified.') VALUES '.implode(', ', $values_stringified);
		return Db::getInstance()->Execute($sql);
	}

	/**
	 * @param string $table Table name without prefix
	 * @param array $data Data to insert as associative array. If $data is a list of arrays, multiple insert will be done
	 * @param string $where WHERE condition
	 * @param int $limit
	 * @param bool $null_values If we want to use NULL values instead of empty quotes
	 * @param bool $use_cache
	 * @param bool $add_prefix Add or not _DB_PREFIX_ before table name
	 * @return bool
	 */
	static public function update($table, $data, $where = '', $limit = 0, $null_values = false, $use_cache = true, $add_prefix = true)
	{
		if( _PS_VERSION_ >= '1.5' ) return Db::getInstance()->update($table, $data,$where , $limit , $null_values , $use_cache , $add_prefix );

		if (!$data)
			return true;

		if ($add_prefix)
			$table = _DB_PREFIX_.$table;

		$sql = 'UPDATE `'.$table.'` SET ';
		foreach ($data as $key => $value)
		{
			if (!is_array($value))
				$value = array('type' => 'text', 'value' => $value);
			if ($value['type'] == 'sql')
				$sql .= "`$key` = {$value['value']},";
			else
				$sql .= ($null_values && ($value['value'] === '' || is_null($value['value']))) ? "`$key` = NULL," : "`$key` = '{$value['value']}',";
		}

		$sql = rtrim($sql, ',');
		if ($where)
			$sql .= ' WHERE '.$where;
		if ($limit)
			$sql .= ' LIMIT '.(int)$limit;
		echo $sql;
		return Db::getInstance()->Execute($sql);
	}

}
