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

class PricestrackerClrModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{


		function runSql($sql) {
			foreach ($sql as $s) {
				if (!Db::getInstance()->Execute($s)){
					return FALSE;
				}
			}
			return TRUE;
		}
		
		function sql_alternative($insert_table)
		{
			$nb_insert_groupes=200;
			
			$backupfile=dirname(__FILE__).'/exportBDDPricestracker.sql';
		
			$fp = @fopen($backupfile, 'w');
			
			if ($fp === false) {
				echo 'Unable to create backup file'.' "'.addslashes($backupfile).'"';
				return false;
			}
			
			$id = realpath($backupfile);
			
			fwrite($fp, "\n".'SET NAMES \'utf8\';'."\n\n");
			
			// Find all tables
			$tables = Db::getInstance()->executeS('SHOW TABLES');
			$found = 0;
			foreach ($tables as $table) {
				$table = current($table);
			
				// Skip tables which do not start with _DB_PREFIX_
				if (strlen($table) < strlen(_DB_PREFIX_) || strncmp($table, _DB_PREFIX_, strlen(_DB_PREFIX_)) != 0 || !in_array(str_replace(_DB_PREFIX_,'',$table),$insert_table)) {
					continue;
				}
				
			
				// Export the table schema
				$schema = Db::getInstance()->executeS('SHOW CREATE TABLE `'.$table.'`');
			
				if (count($schema) != 1 || !isset($schema[0]['Table']) || !isset($schema[0]['Create Table'])) {
					fclose($fp);
					echo ('An error occurred while backing up. Unable to obtain the schema of').' "'.$table;
					return false;
				}
			
				fwrite($fp, "/*---------------------*/\n/* Scheme for table ".$schema[0]['Table']." */\n");
		
				fwrite($fp, 'DROP TABLE IF EXISTS `'.$schema[0]['Table'].'`;'."\n");
				
				fwrite($fp, "/*---------------------*/\n");
			
				fwrite($fp, $schema[0]['Create Table'].";\n\n");
			
				
				$data = Db::getInstance()->query('SELECT * FROM `'.$schema[0]['Table'].'`', false);
				$sizeof = DB::getInstance()->NumRows();
				$lines = explode("\n", $schema[0]['Create Table']);
		
				if ($data && $sizeof > 0) {
					// Export the table data
					fwrite($fp, "/*---------------------*/\n");
					fwrite($fp, 'INSERT INTO `'.$schema[0]['Table']."` VALUES\n");
					$i = 1;
					while ($row = DB::getInstance()->nextRow($data)) {
						$s = '(';
		
						foreach ($row as $field => $value) {
							$tmp = "'".pSQL($value, true)."',";
							if ($tmp != "'',") {
								$s .= $tmp;
							} else {
								foreach ($lines as $line) {
									if (strpos($line, '`'.$field.'`') !== false) {
										if (preg_match('/(.*NOT NULL.*)/Ui', $line)) {
											$s .= "'',";
										} else {
											$s .= 'NULL,';
										}
										break;
									}
								}
							}
						}
						$s = rtrim($s, ',');
		
						if ($i % $nb_insert_groupes == 0 && $i < $sizeof) {
							$s .= ");\n/*---------------------*/\nINSERT INTO `".$schema[0]['Table']."` VALUES\n";
						} elseif ($i < $sizeof) {
							$s .= "),\n";
						} else {
							$s .= ");\n";
						}
		
						fwrite($fp, $s);
						++$i;
					}
				}
			}
			$found++;
		
			
			fclose($fp);
			if ($found == 0) {
				echo ('No valid tables were found to backup.');
				return false;
			}
			
			return true;
		}
		
		if(Tools::isSubmit('export'))
		{
			sql_alternative(array('pricestracker_produits_etrangers','pricestracker_associations','pricestracker_proximites','pricestracker_concurrents','pricestracker_favoris','pricestracker_favoris_product','pricestracker_regles','pricestracker_regles_association','pricestracker_liens_prioritaires','pricestracker_proximite_regles','pricestracker_proposition','pricestracker_historique'));
			$shop = Context::getContext()->shop;
			$domaineApplet=(_PS_VERSION_ < '1.5'?(($_SERVER['HTTPS']?'https://':'http://').$_SERVER['HTTP_HOST'].'/'):$shop->getBaseURL());
			if(strpos($domaineApplet,$_SERVER['HTTP_HOST'])===FALSE) $domaineApplet=($_SERVER['HTTPS']?'https://':'http://').$_SERVER['HTTP_HOST'].'/';
			Tools::redirect($domaineApplet.'modules/pricestracker/pages/exportBDDPricestracker.sql');
			die;
		}
		if(Tools::isSubmit('base'))
		{
			$sql = array();
		
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_produits_etrangers`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_associations`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_proximites`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_concurrents`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_favoris`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_favoris_product`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_regles`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_regles_association`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_liens_prioritaires`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_proximite_regles`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_historique`';
			$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'pricestracker_proposition`';
			
			runSql($sql);
		}
		
		
		function clearDir($dossier) {
			$ouverture=@opendir($dossier);
			if (!$ouverture) return;
			while($fichier=readdir($ouverture)) {
				if ($fichier == '.' || $fichier == '..') continue;
					if (is_dir($dossier."/".$fichier)) {
						$r=clearDir($dossier."/".$fichier);
						if (!$r) return false;
					}
					else {
						$r=@unlink($dossier."/".$fichier);
						if (!$r) return false;
					}
			}
			closedir($ouverture);
			$r=@rmdir($dossier);
			@rename($dossier,"trash");
			return true;
		}
		
		clearDir('../../pricestracker');
	}
}