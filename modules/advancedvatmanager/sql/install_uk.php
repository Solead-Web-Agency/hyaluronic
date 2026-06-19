<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


$id_uk = Country::getByIso('GB');
$insert_uk_states = "INSERT INTO `"._DB_PREFIX_."state` (`id_country`, `id_zone`, `iso_code`, `name`,`tax_behavior`, `active`) VALUES
($id_uk, 1,'GB-ABE','Aberdeen City','0','1'),
($id_uk, 1,'GB-ABD','Aberdeenshire','0','1'),
($id_uk, 1,'GB-ANS','Angus','0','1'),
($id_uk, 1,'GB-ANT','Antrim','0','1'),
($id_uk, 1,'GB-ARD','Ards','0','1'),
($id_uk, 1,'GB-AGB','Argyll and Bute','0','1'),
($id_uk, 1,'GB-ARM','Armagh','0','1'),
($id_uk, 1,'GB-BLA','Ballymena','0','1'),
($id_uk, 1,'GB-BLY','Ballymoney','0','1'),
($id_uk, 1,'GB-BNB','Banbridge','0','1'),
($id_uk, 1,'GB-BDG','Barking and Dagenham','0','1'),
($id_uk, 1,'GB-BNE','Barnet','0','1'),
($id_uk, 1,'GB-BNS','Barnsley','0','1'),
($id_uk, 1,'GB-BAS','Bath and North East Somerset','0','1'),
($id_uk, 1,'GB-BDF','Bedford','0','1'),
($id_uk, 1,'GB-BFS','Belfast City','0','1'),
($id_uk, 1,'GB-BEX','Bexley','0','1'),
($id_uk, 1,'GB-BIR','Birmingham','0','1'),
($id_uk, 1,'GB-BBD','Blackburn with Darwen','0','1'),
($id_uk, 1,'GB-BPL','Blackpool','0','1'),
($id_uk, 1,'GB-BGW','Blaenau Gwent','0','1'),
($id_uk, 1,'GB-BOL','Bolton','0','1'),
($id_uk, 1,'GB-BMH','Bournemouth','0','1'),
($id_uk, 1,'GB-BRC','Bracknell Forest','0','1'),
($id_uk, 1,'GB-BRD','Bradford','0','1'),
($id_uk, 1,'GB-BEN','Brent','0','1'),
($id_uk, 1,'GB-BGE','Bridgend','0','1'),
($id_uk, 1,'GB-BRY','Bromley','0','1'),
($id_uk, 1,'GB-BKM','Buckinghamshire','0','1'),
($id_uk, 1,'GB-BUR','Bury','0','1'),
($id_uk, 1,'GB-CAY','Caerphilly','0','1'),
($id_uk, 1,'GB-CLD','Calderdale','0','1'),
($id_uk, 1,'GB-CAM','Cambridgeshire','0','1'),
($id_uk, 1,'GB-CMD','Camden','0','1'),
($id_uk, 1,'GB-CRF','Cardiff','0','1'),
($id_uk, 1,'GB-CMN','Carmarthenshire','0','1'),
($id_uk, 1,'GB-CKF','Carrickfergus','0','1'),
($id_uk, 1,'GB-CSR','Castlereagh','0','1'),
($id_uk, 1,'GB-CBF','Central Bedfordshire','0','1'),
($id_uk, 1,'GB-CGN','Ceredigion','0','1'),
($id_uk, 1,'GB-CHE','Cheshire East','0','1'),
($id_uk, 1,'GB-CHW','Cheshire West and Chester','0','1'),
($id_uk, 1,'GB-BST','City of Bristol','0','1'),
($id_uk, 1,'GB-DER','City of Derby','0','1'),
($id_uk, 1,'GB-EDH','City of Edinburgh','0','1'),
($id_uk, 1,'GB-LCE','City of Leicester','0','1'),
($id_uk, 1,'GB-LND','City of London','0','1'),
($id_uk, 1,'GB-MAN','City of Manchester','0','1'),
($id_uk, 1,'GB-NGM','City of Nottingham','0','1'),
($id_uk, 1,'GB-PTE','City of Peterborough','0','1'),
($id_uk, 1,'GB-PLY','City of Plymouth','0','1'),
($id_uk, 1,'GB-POR','City of Portsmouth','0','1'),
($id_uk, 1,'GB-SLF','City of Salford','0','1'),
($id_uk, 1,'GB-STH','City of Southampton','0','1'),
($id_uk, 1,'GB-STE','City of Stoke-on-Trent','0','1'),
($id_uk, 1,'GB-WSM','City of Westminster','0','1'),
($id_uk, 1,'GB-WLV','City of Wolverhampton','0','1'),
($id_uk, 1,'GB-CLK','Clackmannanshire','0','1'),
($id_uk, 1,'GB-CLR','Coleraine','0','1'),
($id_uk, 1,'GB-CWY','Conwy','0','1'),
($id_uk, 1,'GB-CKT','Cookstown','0','1'),
($id_uk, 1,'GB-CON','Cornwall','0','1'),
($id_uk, 1,'GB-COV','Coventry','0','1'),
($id_uk, 1,'GB-CGV','Craigavon','0','1'),
($id_uk, 1,'GB-CRY','Croydon','0','1'),
($id_uk, 1,'GB-CMA','Cumbria','0','1'),
($id_uk, 1,'GB-DAL','Darlington','0','1'),
($id_uk, 1,'GB-DEN','Denbighshire','0','1'),
($id_uk, 1,'GB-DBY','Derbyshire','0','1'),
($id_uk, 1,'GB-DRY','Derry City','0','1'),
($id_uk, 1,'GB-DEV','Devon','0','1'),
($id_uk, 1,'GB-DNC','Doncaster','0','1'),
($id_uk, 1,'GB-DOR','Dorset','0','1'),
($id_uk, 1,'GB-DOW','Down','0','1'),
($id_uk, 1,'GB-DUD','Dudley','0','1'),
($id_uk, 1,'GB-DGY','Dumfries and Galloway','0','1'),
($id_uk, 1,'GB-DND','Dundee City','0','1'),
($id_uk, 1,'GB-DGN','Dungannon and South Tyrone','0','1'),
($id_uk, 1,'GB-DUR','Durham','0','1'),
($id_uk, 1,'GB-EAL','Ealing','0','1'),
($id_uk, 1,'GB-EAY','East Ayrshire','0','1'),
($id_uk, 1,'GB-EDU','East Dunbartonshire','0','1'),
($id_uk, 1,'GB-ELN','East Lothian','0','1'),
($id_uk, 1,'GB-ERW','East Renfrewshire','0','1'),
($id_uk, 1,'GB-ERY','East Riding of Yorkshire','0','1'),
($id_uk, 1,'GB-ESX','East Sussex','0','1'),
($id_uk, 1,'GB-ENF','Enfield','0','1'),
($id_uk, 1,'GB-ESS','Essex','0','1'),
($id_uk, 1,'GB-FAL','Falkirk','0','1'),
($id_uk, 1,'GB-FER','Fermanagh','0','1'),
($id_uk, 1,'GB-FIF','Fife','0','1'),
($id_uk, 1,'GB-FLN','Flintshire','0','1'),
($id_uk, 1,'GB-GAT','Gateshead','0','1'),
($id_uk, 1,'GB-GLG','Glasgow City','0','1'),
($id_uk, 1,'GB-GLS','Gloucestershire','0','1'),
($id_uk, 1,'GB-GRE','Greenwich','0','1'),
($id_uk, 1,'GB-GWN','Gwynedd','0','1'),
($id_uk, 1,'GB-HCK','Hackney','0','1'),
($id_uk, 1,'GB-HAL','Halton','0','1'),
($id_uk, 1,'GB-HMF','Hammersmith and Fulham','0','1'),
($id_uk, 1,'GB-HAM','Hampshire','0','1'),
($id_uk, 1,'GB-HRY','Haringey','0','1'),
($id_uk, 1,'GB-HRW','Harrow','0','1'),
($id_uk, 1,'GB-HPL','Hartlepool','0','1'),
($id_uk, 1,'GB-HAV','Havering','0','1'),
($id_uk, 1,'GB-HEF','Herefordshire','0','1'),
($id_uk, 1,'GB-HRT','Hertfordshire','0','1'),
($id_uk, 1,'GB-HLD','Highland','0','1'),
($id_uk, 1,'GB-HIL','Hillingdon','0','1'),
($id_uk, 1,'GB-HNS','Hounslow','0','1'),
($id_uk, 1,'GB-IVC','Inverclyde','0','1'),
($id_uk, 1,'GB-AGY','Isle of Anglesey','0','1'),
($id_uk, 1,'GB-IOW','Isle of Wight','0','1'),
($id_uk, 1,'GB-ISL','Islington','0','1'),
($id_uk, 1,'GB-KEC','Kensington and Chelsea','0','1'),
($id_uk, 1,'GB-KEN','Kent','0','1'),
($id_uk, 1,'GB-KHL','Kingston upon Hull','0','1'),
($id_uk, 1,'GB-KTT','Kingston upon Thames','0','1'),
($id_uk, 1,'GB-KIR','Kirklees','0','1'),
($id_uk, 1,'GB-KWL','Knowsley','0','1'),
($id_uk, 1,'GB-LBH','Lambeth','0','1'),
($id_uk, 1,'GB-LAN','Lancashire','0','1'),
($id_uk, 1,'GB-LRN','Larne','0','1'),
($id_uk, 1,'GB-LDS','Leeds','0','1'),
($id_uk, 1,'GB-LEC','Leicestershire','0','1'),
($id_uk, 1,'GB-LEW','Lewisham','0','1'),
($id_uk, 1,'GB-LMV','Limavady','0','1'),
($id_uk, 1,'GB-LIN','Lincolnshire','0','1'),
($id_uk, 1,'GB-LSB','Lisburn','0','1'),
($id_uk, 1,'GB-LIV','Liverpool','0','1'),
($id_uk, 1,'GB-LUT','Luton','0','1'),
($id_uk, 1,'GB-MFT','Magherafelt','0','1'),
($id_uk, 1,'GB-MDW','Medway','0','1'),
($id_uk, 1,'GB-MTY','Merthyr Tydfil','0','1'),
($id_uk, 1,'GB-MRT','Merton','0','1'),
($id_uk, 1,'GB-MDB','Middlesbrough','0','1'),
($id_uk, 1,'GB-MLN','Midlothian','0','1'),
($id_uk, 1,'GB-MIK','Milton Keynes','0','1'),
($id_uk, 1,'GB-MON','Monmouthshire','0','1'),
($id_uk, 1,'GB-MRY','Moray','0','1'),
($id_uk, 1,'GB-MYL','Moyle','0','1'),
($id_uk, 1,'GB-NTL','Neath Port Talbot','0','1'),
($id_uk, 1,'GB-NET','Newcastle upon Tyne','0','1'),
($id_uk, 1,'GB-NWM','Newham','0','1'),
($id_uk, 1,'GB-NWP','Newport','0','1'),
($id_uk, 1,'GB-NYM','Newry and Mourne','0','1'),
($id_uk, 1,'GB-NTA','Newtownabbey','0','1'),
($id_uk, 1,'GB-NFK','Norfolk','0','1'),
($id_uk, 1,'GB-NAY','North Ayrshire','0','1'),
($id_uk, 1,'GB-NDN','North Down','0','1'),
($id_uk, 1,'GB-NEL','North East Lincolnshire','0','1'),
($id_uk, 1,'GB-NIR','North Ireland','0','1'),
($id_uk, 1,'GB-NLK','North Lanarkshire','0','1'),
($id_uk, 1,'GB-NLN','North Lincolnshire','0','1'),
($id_uk, 1,'GB-NSM','North Somerset','0','1'),
($id_uk, 1,'GB-NTY','North Tyneside','0','1'),
($id_uk, 1,'GB-NYK','North Yorkshire','0','1'),
($id_uk, 1,'GB-NTH','Northamptonshire','0','1'),
($id_uk, 1,'GB-NBL','Northumberland','0','1'),
($id_uk, 1,'GB-NTT','Nottinghamshire','0','1'),
($id_uk, 1,'GB-OLD','Oldham','0','1'),
($id_uk, 1,'GB-OMH','Omagh','0','1'),
($id_uk, 1,'GB-ORK','Orkney Islands','0','1'),
($id_uk, 1,'GB-ELS','Outer Hebrides','0','1'),
($id_uk, 1,'GB-OXF','Oxfordshire','0','1'),
($id_uk, 1,'GB-PEM','Pembrokeshire','0','1'),
($id_uk, 1,'GB-PKN','Perth and Kinross','0','1'),
($id_uk, 1,'GB-POL','Poole','0','1'),
($id_uk, 1,'GB-POW','Powys','0','1'),
($id_uk, 1,'GB-RDG','Reading','0','1'),
($id_uk, 1,'GB-RDB','Redbridge','0','1'),
($id_uk, 1,'GB-RCC','Redcar and Cleveland','0','1'),
($id_uk, 1,'GB-RFW','Renfrewshire','0','1'),
($id_uk, 1,'GB-RCT','Rhondda Cynon Taf','0','1'),
($id_uk, 1,'GB-RIC','Richmond upon Thames','0','1'),
($id_uk, 1,'GB-RCH','Rochdale','0','1'),
($id_uk, 1,'GB-ROT','Rotherham','0','1'),
($id_uk, 1,'GB-RUT','Rutland','0','1'),
($id_uk, 1,'GB-SAW','Sandwell','0','1'),
($id_uk, 1,'GB-SCB','Scottish Borders','0','1'),
($id_uk, 1,'GB-SFT','Sefton','0','1'),
($id_uk, 1,'GB-SHF','Sheffield','0','1'),
($id_uk, 1,'GB-ZET','Shetland Islands','0','1'),
($id_uk, 1,'GB-SHR','Shropshire','0','1'),
($id_uk, 1,'GB-SLG','Slough','0','1'),
($id_uk, 1,'GB-SOL','Solihull','0','1'),
($id_uk, 1,'GB-SOM','Somerset','0','1'),
($id_uk, 1,'GB-SAY','South Ayrshire','0','1'),
($id_uk, 1,'GB-SGC','South Gloucestershire','0','1'),
($id_uk, 1,'GB-SLK','South Lanarkshire','0','1'),
($id_uk, 1,'GB-STY','South Tyneside','0','1'),
($id_uk, 1,'GB-SOS','Southend-on-Sea','0','1'),
($id_uk, 1,'GB-SWK','Southwark','0','1'),
($id_uk, 1,'GB-SHN','St. Helens','0','1'),
($id_uk, 1,'GB-STS','Staffordshire','0','1'),
($id_uk, 1,'GB-STG','Stirling','0','1'),
($id_uk, 1,'GB-SKP','Stockport','0','1'),
($id_uk, 1,'GB-STT','Stockton-on-Tees','0','1'),
($id_uk, 1,'GB-STB','Strabane','0','1'),
($id_uk, 1,'GB-SFK','Suffolk','0','1'),
($id_uk, 1,'GB-SND','Sunderland','0','1'),
($id_uk, 1,'GB-SRY','Surrey','0','1'),
($id_uk, 1,'GB-STN','Sutton','0','1'),
($id_uk, 1,'GB-SWA','Swansea','0','1'),
($id_uk, 1,'GB-SWD','Swindon','0','1'),
($id_uk, 1,'GB-TAM','Tameside','0','1'),
($id_uk, 1,'GB-TFW','Telford and Wrekin','0','1'),
($id_uk, 1,'GB-BNH','The City of Brighton and Hove','0','1'),
($id_uk, 1,'GB-THR','Thurrock','0','1'),
($id_uk, 1,'GB-TOB','Torbay','0','1'),
($id_uk, 1,'GB-TOF','Torfaen','0','1'),
($id_uk, 1,'GB-TWH','Tower Hamlets','0','1'),
($id_uk, 1,'GB-TRF','Trafford','0','1'),
($id_uk, 1,'GB-VGL','Vale of Glamorgan','0','1'),
($id_uk, 1,'GB-WKF','Wakefield','0','1'),
($id_uk, 1,'GB-WLL','Walsall','0','1'),
($id_uk, 1,'GB-WFT','Waltham Forest','0','1'),
($id_uk, 1,'GB-WND','Wandsworth','0','1'),
($id_uk, 1,'GB-WRT','Warrington','0','1'),
($id_uk, 1,'GB-WAR','Warwickshire','0','1'),
($id_uk, 1,'GB-WBK','West Berkshire','0','1'),
($id_uk, 1,'GB-WDU','West Dunbartonshire','0','1'),
($id_uk, 1,'GB-WLN','West Lothian','0','1'),
($id_uk, 1,'GB-WSX','West Sussex','0','1'),
($id_uk, 1,'GB-WGN','Wigan','0','1'),
($id_uk, 1,'GB-WIL','Wiltshire','0','1'),
($id_uk, 1,'GB-WNM','Windsor and Maidenhead','0','1'),
($id_uk, 1,'GB-WRL','Wirral','0','1'),
($id_uk, 1,'GB-WOK','Wokingham','0','1'),
($id_uk, 1,'GB-WOR','Worcestershire','0','1'),
($id_uk, 1,'GB-WRX','Wrexham','0','1'),
($id_uk, 1,'GB-YOR','York','0','1');";

$insert_northIreland = "INSERT INTO `"._DB_PREFIX_."state` (`id_country`, `id_zone`, `iso_code`, `name`,`tax_behavior`, `active`) VALUES
($id_uk, 1,'GB-NIR','North Ireland','0','1');";

// Create UK states
if (!State::getStatesByIdCountry($id_uk)) {
    if (Db::getInstance()->execute($insert_uk_states) === false) {
        die('Error while creating UK states.');
    }
    $country = new Country($id_uk);
    $country->contains_states = 1;
    $country->update(); 
    
    $addressFormat = new AddressFormat();     
}

if (!in_array('State:name', AddressFormat::getOrderedAddressFields($id_uk))) {
    Db::getInstance()->update('address_format', array('format'=>pSQL('firstname lastname
company
vat_number
address1
address2
postcode city
Country:name
State:name
phone')), 'id_country ='.(int)$id_uk);    
}
// Create only North Ireland state into UK country.
else if (!State::getIdByIso('GB-NIR')) {
    if (Db::getInstance()->execute($insert_northIreland) === false) {
        die('Error while creating North Ireland state.');
    }       
}

