<?php
/**
* 2007-2021 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2021 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

$DOCUMENT_ROOT = explode('modules', dirname(__FILE__));
require_once($DOCUMENT_ROOT[0].'config/config.inc.php');
require_once($DOCUMENT_ROOT[0].'init.php');
$detail = Db::getInstance()->getRow(
    'SELECT o.id_order, o.id_customer,CONCAT(a.lastname," ",a.firstname) AS recipent,a.address1, a.postcode, a.city, co.iso_code,a.phone, a.phone_mobile,c.email, msr.insurance_level, msr.selected_relay_num,
	msr.selected_relay_adr1,msr.selected_relay_adr2, msr.selected_relay_adr3,
	msr.selected_relay_adr4, msr.selected_relay_postcode, msr.selected_relay_city, msr.selected_relay_country_iso FROM '._DB_PREFIX_.'order_carrier oc
	LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
	LEFT JOIN '._DB_PREFIX_.'customer c ON o.id_customer=c.id_customer
	LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery=a.id_address
	LEFT JOIN '._DB_PREFIX_.'country co ON a.id_country=co.id_country
	LEFT JOIN '._DB_PREFIX_.'mondialrelay_selected_relay msr ON o.id_order=msr.id_order
    WHERE oc.id_order_carrier= 3224'
);

$Enseigne = Configuration::get('HL_CARRIER10_id1');
$privatekey = Configuration::get('HL_CARRIER10_id2');
$ModeCol = 'REL';
$ModeLiv = '24R';
$NDossier = $detail['id_order'];
$NClient = $detail['id_customer'];
$Expe_Langage = 'FR';
$Expe_Ad1 = 'Mehdi Tebaoui';
$Expe_Ad2 = '';
$Expe_Ad3 = '149 Avenue henri barbusse';
$Expe_Ad4 = '';
$Expe_Ville = 'DRANCY';
$Expe_CP = '93700';
$Expe_Pays = 'FR';
$Expe_Tel1 = '+33610568060';
$Expe_Tel2 = '';
$Expe_Mail = '';
$Dest_Langage = 'FR';
$Dest_Ad1 = $detail['recipent'];
$Dest_Ad2 = '';
$Dest_Ad3 = $detail['address1'];
$Dest_Ad4 = $detail['address2'];
$Dest_Ville = $detail['city'];
$Dest_CP = $detail['postcode'];
$Dest_Pays = $detail['iso_code'];
$Dest_Tel1 = $detail['phone'];
$Dest_Tel2 = $detail['phone_mobile'];
$Dest_Mail = $detail['email'];
$Poids = '15';
$Longueur = '';
$Taille = '';
$NbColis = '1';
$CRT_Valeur = '0';
$CRT_Devise = 'EUR';
$Exp_Valeur = '';
$Exp_Devise = '';
$COL_Rel_Pays = 'XX';
$COL_Rel = 'AUTO';
$LIV_Rel_Pays = $detail['selected_relay_country_iso'];
$LIV_Rel = $detail['selected_relay_num'];
$TAvisage = 'O';
$TReprise = 'N';
$Montage = '0';
$TRDV = 'N';
$Assurance = $detail['insurance_level'];
$Instructions = '';
$Security = $Enseigne.$ModeCol.$ModeLiv.$NDossier.$NClient.$Expe_Langage.$Expe_Ad1.$Expe_Ad2.$Expe_Ad3.$Expe_Ad4.$Expe_Ville.$Expe_CP.$Expe_Pays.$Expe_Tel1.$Expe_Tel2.$Expe_Mail.$Dest_Langage.$Dest_Ad1.$Dest_Ad2.$Dest_Ad3.$Dest_Ad4.$Dest_Ville.$Dest_CP.$Dest_Pays.$Dest_Tel1.$Dest_Tel2.$Dest_Mail.$Poids.$Longueur.$Taille.$NbColis.$CRT_Valeur.$CRT_Devise.$Exp_Valeur.$Exp_Devise.$COL_Rel_Pays.$COL_Rel.$LIV_Rel_Pays.$LIV_Rel.$TAvisage.$TReprise.$Montage.$TRDV.$Assurance.$Instructions.$privatekey;
$Securitymd5 = Tools::strtoupper(md5($Security));
//print_r($Security);
//print_r($Securitymd5);exit;
$Texte = '';

$data = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:web="http://www.mondialrelay.fr/webservice/">
   <soap:Header/>
   <soap:Body>
      <web:WSI2_CreationEtiquette>
         <web:Enseigne>'.$Enseigne.'</web:Enseigne>
         <web:ModeCol>'.$ModeCol.'</web:ModeCol>
         <web:ModeLiv>'.$ModeLiv.'</web:ModeLiv>
         <web:NDossier>'.$NDossier.'</web:NDossier>
         <web:NClient>'.$NClient.'</web:NClient>
         <web:Expe_Langage>'.$Expe_Langage.'</web:Expe_Langage>
         <web:Expe_Ad1>'.$Expe_Ad1.'</web:Expe_Ad1>
         <web:Expe_Ad2>'.$Expe_Ad2.'</web:Expe_Ad2>
         <web:Expe_Ad3>'.$Expe_Ad3.'</web:Expe_Ad3>
         <web:Expe_Ad4>'.$Expe_Ad4.'</web:Expe_Ad4>
         <web:Expe_Ville>'.$Expe_Ville.'</web:Expe_Ville>
         <web:Expe_CP>'.$Expe_CP.'</web:Expe_CP>
         <web:Expe_Pays>'.$Expe_Pays.'</web:Expe_Pays>
         <web:Expe_Tel1>'.$Expe_Tel1.'</web:Expe_Tel1>
         <web:Expe_Tel2>'.$Expe_Tel2.'</web:Expe_Tel2>
         <web:Expe_Mail>'.$Expe_Mail.'</web:Expe_Mail>
         <web:Dest_Langage>'.$Dest_Langage.'</web:Dest_Langage>
         <web:Dest_Ad1>'.$Dest_Ad1.'</web:Dest_Ad1>
         <web:Dest_Ad2>'.$Dest_Ad2.'</web:Dest_Ad2>
         <web:Dest_Ad3>'.$Dest_Ad3.'</web:Dest_Ad3>
         <web:Dest_Ad4>'.$Dest_Ad4.'</web:Dest_Ad4>
         <web:Dest_Ville>'.$Dest_Ville.'</web:Dest_Ville>
         <web:Dest_CP>'.$Dest_CP.'</web:Dest_CP>
         <web:Dest_Pays>'.$Dest_Pays.'</web:Dest_Pays>
         <web:Dest_Tel1>'.$Dest_Tel1.'</web:Dest_Tel1>
         <web:Dest_Tel2>'.$Dest_Tel2.'</web:Dest_Tel2>
         <web:Dest_Mail>'.$Dest_Mail.'</web:Dest_Mail>
         <web:Poids>'.$Poids.'</web:Poids>
         <web:Longueur>'.$Longueur.'</web:Longueur>
         <web:Taille>'.$Taille.'</web:Taille>
         <web:NbColis>'.$NbColis.'</web:NbColis>
         <web:CRT_Valeur>'.$CRT_Valeur.'</web:CRT_Valeur>
         <web:CRT_Devise>'.$CRT_Devise.'</web:CRT_Devise>
         <web:Exp_Valeur>'.$Exp_Valeur.'</web:Exp_Valeur>
         <web:Exp_Devise>'.$Exp_Devise.'</web:Exp_Devise>
         <web:COL_Rel_Pays>'.$COL_Rel_Pays.'</web:COL_Rel_Pays>
         <web:COL_Rel>'.$COL_Rel.'</web:COL_Rel>
         <web:LIV_Rel_Pays>'.$LIV_Rel_Pays.'</web:LIV_Rel_Pays>
         <web:LIV_Rel>'.$LIV_Rel.'</web:LIV_Rel>
         <web:TAvisage>'.$TAvisage.'</web:TAvisage>
         <web:TReprise>'.$TReprise.'</web:TReprise>
         <web:Montage>'.$Montage.'</web:Montage>
         <web:TRDV>'.$TRDV.'</web:TRDV>
         <web:Assurance>'.$Assurance.'</web:Assurance>
         <web:Instructions>'.$Instructions.'</web:Instructions>
         <web:Security>'.$Securitymd5.'</web:Security>
         <web:Texte>'.$Texte.'</web:Texte>
      </web:WSI2_CreationEtiquette>
   </soap:Body>
</soap:Envelope>';
print_r($data);
echo "\n";

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://api.mondialrelay.com/Web_Services.asmx',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: text/xml',
    'Cookie: JSESSSIONID=920958640.1.776776712.1965228544'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
header("Content-Type: text/xml");
echo $response;
