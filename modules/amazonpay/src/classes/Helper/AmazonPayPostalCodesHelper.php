<?php
/**
 * 2007-2025 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2025 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AmazonPayPostalCodesHelper
{
    /**
     * @param $postcode
     * @param $iso_code
     *
     * @return bool|int
     */
    public static function getIdByPostalCodeAndCountry($postcode, $iso_code)
    {
        if ($iso_code == 'IT') {
            $province = self::getItalianProvince($postcode);
            if ($province) {
                return State::getIdByName($province);
            }
        } elseif ($iso_code == 'ES') {
            $province = self::getSpanishProvince($postcode);
            if ($province) {
                return State::getIdByName($province);
            }
        } elseif ($iso_code == 'UK' || $iso_code == 'GB') {
            $province = self::getUKProvince($postcode);
            if ($province) {
                return State::getIdByName($province);
            }
        }

        return false;
    }

    /**
     * @param $state
     *
     * @return bool|int
     */
    public static function getIdByFuzzyName($state)
    {
        if (empty($state)) {
            return false;
        }
        if ($state == 'Beds' || $state == 'Beds.') {
            $state = 'Bedfordshire';
        } elseif ($state == 'Dev' || $state == 'Dev.') {
            $state = 'Devon';
        } elseif ($state == 'Dor' || $state == 'Dor.') {
            $state = 'Dorset';
        } elseif ($state == 'Co Dur' || $state == 'Co Dur.') {
            $state = 'Durham';
        } elseif ($state == 'Glos' || $state == 'Glos.' || $state == 'Gloucs' || $state == 'Gloucs.') {
            $state = 'Gloucestershire';
        } elseif ($state == 'Hants' || $state == 'Hants.') {
            $state = 'Hampshire';
        } elseif ($state == 'Mx' || $state == 'Middx' || $state == 'Mddx' || $state == 'Mx.' || $state == 'Middx.' || $state == 'Mddx.') {
            $state = 'Middlesex';
        } elseif ($state == 'Northants' || $state == 'Northants.') {
            $state = 'Northamptonshire';
        } elseif ($state == 'Northumb' || $state == 'Northd' || $state == 'Northumb.' || $state == 'Northd.') {
            $state = 'Northumberland';
        } elseif ($state == 'Oxon' || $state == 'Oxon.') {
            $state = 'Oxfordshire';
        } elseif ($state == 'Rut' || $state == 'Rut.') {
            $state = 'Rutland';
        } elseif ($state == 'Salop' || $state == 'Salop.') {
            $state = 'Shropshire';
        } elseif ($state == 'Som' || $state == 'Som.') {
            $state = 'Somerset';
        } elseif ($state == 'Sy' || $state == 'Sy.') {
            $state = 'Surrey';
        } elseif ($state == 'Sx' || $state == 'Ssx' || $state == 'Sx.' || $state == 'Ssx.') {
            $state = 'Sussex';
        } elseif ($state == 'Warks' || $state == 'War' || $state == 'Warks.' || $state == 'War.') {
            $state = 'Warwickshire';
        } elseif ($state == 'Worcs' || $state == 'Worsts' || $state == 'Worcs.' || $state == 'Worsts.') {
            $state = 'Worcestershire';
        } elseif ($state == 'Cthen' || $state == 'Cthen.') {
            $state = 'Carmarthenshire';
        } elseif ($state == 'Mon' || $state == 'Mon.') {
            $state = 'Monmouthshire';
        } elseif ($state == 'Pem' || $state == 'Pem.') {
            $state = 'Pembrokeshire';
        } elseif ($state == 'County Antrim' || $state == 'Co Antrim' || $state == 'Co. Antrim') {
            $state = 'Antrim';
        } elseif ($state == 'County Armagh' || $state == 'Co Armagh' || $state == 'Co. Armagh') {
            $state = 'Armagh';
        } elseif ($state == 'County Down' || $state == 'Co Down' || $state == 'Co. Down') {
            $state = 'Down';
        } elseif ($state == 'County Fermanagh' || $state == 'Co Fermanagh' || $state == 'Co. Fermanagh') {
            $state = 'Fermanagh';
        } elseif ($state == 'Derry' || $state == 'County Londonderry' || $state == 'Co Londonderry' || $state == 'Co. Londonderry') {
            $state = 'Londonderry';
        } elseif ($state == 'County Tyrone' || $state == 'Co Tyrone' || $state == 'Co. Tyrone') {
            $state = 'Tyrone';
        }
        if ($state == 'Londonderry') {
            $sstate = Tools::substr($state, 0, 8);
        } else {
            $sstate = Tools::substr($state, 0, 4);
        }
        if ($sstate == 'East') {
            $sstate = Tools::substr($state, 0, 8);
        }
        $result = (int) Db::getInstance()->getValue('
			SELECT `id_state`
			FROM `' . _DB_PREFIX_ . 'state`
			WHERE `name` LIKE \'' . pSQL($sstate) . '%\'
		');

        return $result;
    }

    /**
     * @param string $pc
     */
    public static function getItalianProvince($pc)
    {
        $pc = (int) $pc;
        if ($pc >= 15121 && $pc <= 15122) {
            return 'Alessandria';
        } elseif ($pc >= 60121 && $pc <= 60131) {
            return 'Acona';
        } elseif ($pc == 11100) {
            return 'Aosta';
        } elseif ($pc == 52100) {
            return 'Arezzo';
        } elseif ($pc == 63100) {
            return 'Ascoli Piceno';
        } elseif ($pc == 14100) {
            return 'Asti';
        } elseif ($pc == 83100) {
            return 'Avellino';
        } elseif ($pc >= 70121 && $pc <= 70132) {
            return 'Bari';
        } elseif (in_array($pc, [76123, 76011, 76016, 76017, 76125, 76121, 76012, 76013, 76014, 76015])) {
            return 'Barletta-Andria-Trani';
        } elseif ($pc == 32100) {
            return 'Belluno';
        } elseif ($pc == 82100) {
            return 'Beneveto';
        } elseif ($pc >= 24121 && $pc <= 24129) {
            return 'Bergamo';
        } elseif ($pc == 13900) {
            return 'Biella';
        } elseif ($pc >= 40121 && $pc <= 40141) {
            return 'Bologna';
        } elseif ($pc == 39100) {
            return 'Bolzano';
        } elseif ($pc >= 25121 && $pc <= 25136) {
            return 'Brescia';
        } elseif ($pc == 72100) {
            return 'Brindisi';
        } elseif ($pc >= /* 0 */ 9121 && $pc <= /* 0 */ 9134) {
            return 'Cagliari';
        } elseif ($pc == 93100) {
            return 'Caltanissetta';
        } elseif ($pc == 86100) {
            return 'Campobasso';
        } elseif ($pc == /* 0 */ 9013) {
            return 'Carbonia-Iglesias';
        } elseif ($pc == 81100) {
            return 'Caserta';
        } elseif ($pc >= 95121 && $pc <= 95131) {
            return 'Catania';
        } elseif ($pc == 88100) {
            return 'Catanzaro';
        } elseif ($pc == 66100) {
            return 'Chieti';
        } elseif ($pc == 22100) {
            return 'Como';
        } elseif ($pc == 87100) {
            return 'Cosenza';
        } elseif ($pc == 26100) {
            return 'Cremona';
        } elseif ($pc == 88900) {
            return 'Crontone';
        } elseif ($pc == 12100) {
            return 'Cuneo';
        } elseif ($pc == 94100) {
            return 'Enna';
        } elseif ($pc == 63900) {
            return 'Fermo';
        } elseif ($pc >= 44121 && $pc <= 44124) {
            return 'Ferrara';
        } elseif ($pc >= 50121 && $pc <= 50145) {
            return 'Firenze';
        } elseif ($pc >= 71121 && $pc <= 71122) {
            return 'Foggia';
        } elseif (in_array($pc, [47121, 47122, 47021, 47032, 47030, 47011, 47521, 47522, 47042, 47012, 47013, 47034, 47010, 47035, 47043, 47020, 47014, 47025, 47015, 47020, 47010, 47016, 47017, 47030, 47018, 47027, 47039, 47019, 47028])) {
            return 'Forli-Cesena';
        } elseif ($pc == /* 0 */ 3100) {
            return 'Frosinone';
        } elseif ($pc >= 16121 && $pc <= 16167) {
            return 'Genova';
        } elseif ($pc == 34170) {
            return 'Gorizia';
        } elseif ($pc == 58100) {
            return 'Grosetto';
        } elseif ($pc == 18100) {
            return 'Imperia';
        } elseif ($pc == 86170) {
            return 'Isernia';
        } elseif ($pc == 67100) {
            return 'L\'Aquila';
        } elseif ($pc >= 19121 && $pc <= 19137) {
            return 'La Spezia';
        } elseif ($pc == /* 0 */ 4100) {
            return 'Latina';
        } elseif ($pc == 73100) {
            return 'Lecce';
        } elseif ($pc == 23900) {
            return 'Lecco';
        } elseif ($pc >= 57121 && $pc <= 57128) {
            return 'Livorno';
        } elseif ($pc == 26900) {
            return 'Lodi';
        } elseif ($pc == 55100) {
            return 'Lucca';
        } elseif ($pc == 62100) {
            return 'Macerata';
        } elseif ($pc == 46100) {
            return 'Mantova';
        } elseif ($pc == 54100) {
            return 'Massa';
        } elseif ($pc == 75100) {
            return 'Matera';
        } elseif (in_array($pc, [/* 0 */ 9020, /* 0 */ 9021, /* 0 */ 9022, /* 0 */ 9025, /* 0 */ 9027, /* 0 */ 9029, /* 0 */ 9030, /* 0 */ 9031, /* 0 */ 9035, /* 0 */ 9036, /* 0 */ 9037, /* 0 */ 9038, /* 0 */ 9039, /* 0 */ 9040])) {
            return 'Medio Campidano';
        } elseif ($pc >= 98121 && $pc <= 98168) {
            return 'Messina';
        } elseif ($pc >= 20121 && $pc <= 20162) {
            return 'Milano';
        } elseif ($pc >= 41121 && $pc <= 41126) {
            return 'Modena';
        } elseif ($pc == 20900) {
            return 'Monza e della Brianza';
        } elseif ($pc >= 80121 && $pc <= 80147) {
            return 'Napoli';
        } elseif ($pc == 28100) {
            return 'Novara';
        } elseif ($pc == /* 0 */ 8100) {
            return 'Nuoro';
        } elseif ($pc == 84061) {
            return 'Ogliastra';
        } elseif ($pc == /* 0 */ 7026) {
            return 'Olbia-Tempio';
        } elseif ($pc == /* 0 */ 9170) {
            return 'Oristano';
        } elseif ($pc >= 35121 && $pc <= 35143) {
            return 'Padova';
        } elseif ($pc >= 90121 && $pc <= 90151) {
            return 'Palermo';
        } elseif ($pc >= 43121 && $pc <= 43126) {
            return 'Parma';
        } elseif ($pc == 27100) {
            return 'Pavia';
        } elseif ($pc >= /* 0 */ 6121 && $pc <= /* 0 */ 6135) {
            return 'Perugia';
        } elseif ($pc >= 61121 && $pc <= 61122) {
            return 'Pesaro-Urbino';
        } elseif ($pc >= 65121 && $pc <= 65129) {
            return 'Pescara';
        } elseif ($pc >= 29121 && $pc <= 29122) {
            return 'Piacenza';
        } elseif ($pc >= 56121 && $pc <= 56128) {
            return 'Pisa';
        } elseif ($pc == 51100) {
            return 'Pistoia';
        } elseif ($pc == 33170) {
            return 'Pordenone';
        } elseif ($pc == 85100) {
            return 'Potenza';
        } elseif ($pc == 59100) {
            return 'Prato';
        } elseif ($pc == 97100) {
            return 'Ragusa';
        } elseif ($pc >= 48121 && $pc <= 48125) {
            return 'Ravenna';
        } elseif ($pc >= 89121 && $pc <= 89135) {
            return 'Reggio Calabria';
        } elseif ($pc >= 42121 && $pc <= 42124) {
            return 'Reggio Emilia';
        } elseif ($pc == /* 0 */ 2100) {
            return 'Rieti';
        } elseif ($pc >= 47921 && $pc <= 47924) {
            return 'Rimini';
        } elseif ($pc >= /* 00 */ 118 && $pc <= /* 00 */ 199) {
            return 'Roma';
        } elseif ($pc == 45100) {
            return 'Rovigo';
        } elseif ($pc >= 84121 && $pc <= 84135) {
            return 'Salerno';
        } elseif ($pc == /* 0 */ 7100) {
            return 'Sassari';
        } elseif ($pc == 17100) {
            return 'Savona';
        } elseif ($pc == 53100) {
            return 'Siena';
        } elseif ($pc == 96100) {
            return 'Siracusa';
        } elseif ($pc == 23100) {
            return 'Sondrio';
        } elseif ($pc >= 74121 && $pc <= 74123) {
            return 'Taranto';
        } elseif ($pc == 64100) {
            return 'Teramo';
        } elseif ($pc == /* 0 */ 5100) {
            return 'Terni';
        } elseif ($pc >= 10121 && $pc <= 10156) {
            return 'Torino';
        } elseif ($pc == 91100) {
            return 'Trapani';
        } elseif ($pc >= 38121 && $pc <= 38123) {
            return 'Trento';
        } elseif ($pc == 31100) {
            return 'Treviso';
        } elseif ($pc >= 34121 && $pc <= 34151) {
            return 'Trieste';
        } elseif ($pc == 33100) {
            return 'Udine';
        } elseif ($pc == 21100) {
            return 'Varese';
        } elseif ($pc >= 30121 && $pc <= 30176) {
            return 'Venezia';
        } elseif (in_array($pc, [28921, 28922, 28923, 28924, 28925, 28877, 28899, 28861, 28831, 28832, 28842, 28833, 28814, 28822, 28881, 28875, 28801, 28865, 28827, 28853, 28863, 28823, 28883, 28816, 28876, 28854, 28895, 28817, 28843, 28824, 28877, 28885, 28818, 28803, 28896, 28804, 28838, 28826, 28859, 28879, 28819,
            28856, 28841, 28813, 28851, 28846, 28873, 28821, 28815, 28825, 28891, 28852, 28862, 28845, 28827, 28887, 28836, 28828, 28893, 28894, 28855, 28802, 28864, 28891, 28887, 28884, 28886, 28866, 28898, 28856, 28857, 58858, 28868, 28897, 28868, 28844, 28805])) {
            return 'Verbano-Cusio-Ossola';
        } elseif ($pc == 13100) {
            return 'Vercelli';
        } elseif ($pc >= 37121 && $pc <= 37142) {
            return 'Verona';
        } elseif ($pc == 89900) {
            return 'Vibo Valentia';
        } elseif ($pc == 36100) {
            return 'Vicenza';
        } elseif ($pc == /* 0 */ 1100) {
            return 'Viterbo';
        }

        return false;
    }

    /**
     * @param string $pc
     */
    public static function getSpanishProvince($pc)
    {
        $pc = Tools::substr($pc, 0, 2);
        switch ($pc) {
            case '01':
                return 'Álava';
            case '02':
                return 'Albacete';
            case '03':
                return 'Alacant';
            case '04':
                return 'Almería';
            case '33':
                return 'Asturias';
            case '05':
                return 'Ávila';
            case '06':
                return 'Badajoz';
            case '07':
                return 'Balears';
            case '08':
                return 'Barcelona';
            case '09':
                return 'Burgos';
            case '10':
                return 'Cáceres';
            case '11':
                return 'Cádiz';
            case '39':
                return 'Cantabria';
            case '12':
                return 'Castelló';
            case '13':
                return 'Ciudad Real';
            case '14':
                return 'Córdoba';
            case '16':
                return 'Cuenca';
            case '17':
                return 'Girona';
            case '18':
                return 'Granada';
            case '19':
                return 'Guadalajara';
            case '20':
                return 'Gipuzkoa';
            case '21':
                return 'Huelva';
            case '22':
                return 'Huesca';
            case '23':
                return 'Jaén';
            case '26':
                return 'La Rioja';
            case '35':
                return 'Las Palmas';
            case '24':
                return 'León';
            case '25':
                return 'Lleida';
            case '27':
                return 'Lugo';
            case '28':
                return 'Madrid';
            case '29':
                return 'Málaga';
            case '30':
                return 'Murcia';
            case '31':
                return 'Nafarroa';
            case '32':
                return 'Ourense';
            case '34':
                return 'Palencia';
            case '36':
                return 'Pontevedra';
            case '37':
                return 'Salamanca';
            case '38':
                return 'Santa Cruz de Tenerife';
            case '40':
                return 'Segovia';
            case '41':
                return 'Sevilla';
            case '42':
                return 'Soria';
            case '43':
                return 'Tarragona';
            case '44':
                return 'Teruel';
            case '45':
                return 'Toledo';
            case '46':
                return 'València';
            case '47':
                return 'Valladolid';
            case '48':
                return 'Bizkaia';
            case '49':
                return 'Zamora';
            case '50':
                return 'Zaragoza';
            case '51':
                return 'Ceuta';
            case '52':
                return 'Melilla';
        }

        return false;
    }

    /**
     * @param $pc
     */
    public static function getUKProvince($pc)
    {
        $pc = strstr($pc, ' ', true);

        if (in_array($pc, ['AB10', 'AB11', 'AB12', 'AB15', 'AB16'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB21', 'AB22', 'AB23', 'AB24', 'AB25'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB99'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB13'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB14'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB32'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB33'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB34'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB35'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB36'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB41'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB42'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB43'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB51'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB52'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB53'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['AB54'])) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, ['DD1', 'DD2', 'DD3', 'DD4', 'DD5'])) {
            return 'Angus';
        } elseif (in_array($pc, ['DD7'])) {
            return 'Angus';
        } elseif (in_array($pc, ['DD8'])) {
            return 'Angus';
        } elseif (in_array($pc, ['DD8'])) {
            return 'Angus';
        } elseif (in_array($pc, ['DD9'])) {
            return 'Angus';
        } elseif (in_array($pc, ['DD10'])) {
            return 'Angus';
        } elseif (in_array($pc, ['DD11'])) {
            return 'Angus';
        } elseif (in_array($pc, ['BT1', 'BT2', 'BT3', 'BT4', 'BT5', 'BT6', 'BT7', 'BT8', 'BT9'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT10', 'BT11', 'BT12', 'BT13', 'BT14', 'BT15', 'BT16', 'BT17'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT29'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT27', 'BT28'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT29'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT36', 'BT37'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT58'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT38'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT39'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT40'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT41'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT42', 'BT43', 'BT44'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT53'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT54'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT56'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['BT57'])) {
            return 'Antrim';
        } elseif (in_array($pc, ['PA21'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA22'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA23'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA24', 'PA25', 'PA26', 'PA27'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA28'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA29'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA30', 'PA31'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA32'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA33'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA34', 'PA37'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA80'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA35'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA36'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PA38'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PH36'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PH49'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['PH50'])) {
            return 'Argyll';
        } elseif (in_array($pc, ['BT60', 'BT61'])) {
            return 'Armagh';
        } elseif (in_array($pc, ['BT62', 'BT63', 'BT64', 'BT65', 'BT66', 'BT67'])) {
            return 'Armagh';
        } elseif (in_array($pc, ['BA1', 'BA2'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BA3'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS0', 'BS1', 'BS2', 'BS3', 'BS4', 'BS5', 'BS6', 'BS7', 'BS8', 'BS9'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS10', 'BS11', 'BS13', 'BS14', 'BS15', 'BS16'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS20'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS30', 'BS31', 'BS32', 'BS34', 'BS35', 'BS36', 'BS37', 'BS39'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS40', 'BS41', 'BS48', 'BS49'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS80'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS98', 'BS99'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS21'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS22', 'BS23', 'BS24'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS25'])) {
            return 'Avon';
        } elseif (in_array($pc, ['BS29'])) {
            return 'Avon';
        } elseif (in_array($pc, ['GL9'])) {
            return 'Avon';
        } elseif (in_array($pc, ['KA1', 'KA2', 'KA3'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA4'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA5'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA6', 'KA7', 'KA8'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA9'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA10'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA11', 'KA12'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA13'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA14', 'KA15'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA16'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA17'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA18'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA19'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA20'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA21'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA22'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA23'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA24'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA25'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA26'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA29'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['KA30'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['PA17'])) {
            return 'Ayrshire';
        } elseif (in_array($pc, ['AB37'])) {
            return 'Banffshire';
        } elseif (in_array($pc, ['AB38'])) {
            return 'Banffshire';
        } elseif (in_array($pc, ['AB44'])) {
            return 'Banffshire';
        } elseif (in_array($pc, ['AB45'])) {
            return 'Banffshire';
        } elseif (in_array($pc, ['AB55'])) {
            return 'Banffshire';
        } elseif (in_array($pc, ['AB56'])) {
            return 'Banffshire';
        } elseif (in_array($pc, ['LU1', 'LU2', 'LU3', 'LU4'])) {
            return 'Bedfordshire';
        } elseif (in_array($pc, ['LU5', 'LU6'])) {
            return 'Bedfordshire';
        } elseif (in_array($pc, ['LU7'])) {
            return 'Bedfordshire';
        } elseif (in_array($pc, ['MK40', 'MK41', 'MK42', 'MK43', 'MK44', 'MK45'])) {
            return 'Bedfordshire';
        } elseif (in_array($pc, ['SG15'])) {
            return 'Bedfordshire';
        } elseif (in_array($pc, ['SG16'])) {
            return 'Bedfordshire';
        } elseif (in_array($pc, ['SG17'])) {
            return 'Bedfordshire';
        } elseif (in_array($pc, ['SG18'])) {
            return 'Bedfordshire';
        } elseif (in_array($pc, ['SG19'])) {
            return 'Bedfordshire';
        } elseif (in_array($pc, ['GU47'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG1', 'RG2', 'RG4', 'RG5', 'RG6', 'RG7', 'RG8'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG10', 'RG19'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG30', 'RG31'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG12'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG42'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG14'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG20'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG17'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG18', 'RG19'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG40', 'RG41'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['RG45'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['SL1', 'SL2', 'SL3'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['SL95'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['SL4'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['SL5'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['SL6'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['SL60'])) {
            return 'Berkshire';
        } elseif (in_array($pc, ['TD2'])) {
            return 'Berwickshire';
        } elseif (in_array($pc, ['TD3'])) {
            return 'Berwickshire';
        } elseif (in_array($pc, ['TD4'])) {
            return 'Berwickshire';
        } elseif (in_array($pc, ['TD10', 'TD11'])) {
            return 'Berwickshire';
        } elseif (in_array($pc, ['TD12'])) {
            return 'Berwickshire';
        } elseif (in_array($pc, ['TD13'])) {
            return 'Berwickshire';
        } elseif (in_array($pc, ['TD14'])) {
            return 'Berwickshire';
        } elseif (in_array($pc, ['HP5'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['HP6', 'HP7'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['HP8'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['HP9'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['HP10', 'HP11', 'HP12', 'HP13', 'HP14', 'HP15'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['HP16'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['HP17', 'HP18', 'HP19'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['HP20', 'HP21', 'HP22'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['HP22', 'HP27'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['MK1', 'MK2', 'MK3', 'MK4', 'MK5', 'MK6', 'MK7', 'MK8', 'MK9'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['MK10', 'MK11', 'MK12', 'MK13', 'MK14', 'MK15', 'MK17', 'MK19'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['MK77'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['MK16'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['MK18'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['MK46'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['SL0'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['SL7'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['SL8'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['SL9'])) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, ['KW1'])) {
            return 'Caithness';
        } elseif (in_array($pc, ['KW2', 'KW3'])) {
            return 'Caithness';
        } elseif (in_array($pc, ['KW5'])) {
            return 'Caithness';
        } elseif (in_array($pc, ['KW6'])) {
            return 'Caithness';
        } elseif (in_array($pc, ['KW7'])) {
            return 'Caithness';
        } elseif (in_array($pc, ['KW12'])) {
            return 'Caithness';
        } elseif (in_array($pc, ['KW14'])) {
            return 'Caithness';
        } elseif (in_array($pc, ['CB1', 'CB2', 'CB3', 'CB4', 'CB5'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['CB21', 'CB22', 'CB23', 'CB24', 'CB25'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['CB6', 'CB7'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['PE1', 'PE2', 'PE3', 'PE4', 'PE5', 'PE6', 'PE7', 'PE8'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['PE99'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['PE13', 'PE14'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['PE15'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['PE16'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['PE19'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['PE26', 'PE28', 'PE29'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['PE27'])) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, ['GY1', 'GY2', 'GY3', 'GY4', 'GY5', 'GY6', 'GY7', 'GY8', 'GY9'])) {
            return 'Channel Islands';
        } elseif (in_array($pc, ['GY10'])) {
            return 'Channel Islands';
        } elseif (in_array($pc, ['JE1', 'JE2', 'JE3', 'JE4', 'JE5'])) {
            return 'Channel Islands';
        } elseif (in_array($pc, ['CH1', 'CH2', 'CH3', 'CH4'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CH70'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CH88'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CH99'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CW1', 'CW2', 'CW3', 'CW4'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CW98'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CW5'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CW6'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CW7'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CW8', 'CW9'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CW10'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CW11'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['CW12'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['M33'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SK1', 'SK2', 'SK3', 'SK4', 'SK5', 'SK6', 'SK7'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SK12'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SK8'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SK9'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SK9'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SK10', 'SK11'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SK14'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SK15'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SK16'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['SY14'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['WA1', 'WA2', 'WA3', 'WA4', 'WA5'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['WA55'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['WA6'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['WA7'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['WA8'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['WA88'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['WA13'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['WA14', 'WA15'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['WA16'])) {
            return 'Cheshire';
        } elseif (in_array($pc, ['FK10'])) {
            return 'Clackmannan';
        } elseif (in_array($pc, ['FK10'])) {
            return 'Clackmannan';
        } elseif (in_array($pc, ['FK11'])) {
            return 'Clackmannan';
        } elseif (in_array($pc, ['FK12'])) {
            return 'Clackmannan';
        } elseif (in_array($pc, ['FK13'])) {
            return 'Clackmannan';
        } elseif (in_array($pc, ['FK14'])) {
            return 'Clackmannan';
        } elseif (in_array($pc, ['TS1', 'TS2', 'TS3', 'TS4', 'TS5', 'TS6', 'TS7', 'TS8', 'TS9'])) {
            return 'Cleveland';
        } elseif (in_array($pc, ['TS10', 'TS11'])) {
            return 'Cleveland';
        } elseif (in_array($pc, ['TS12', 'TS13'])) {
            return 'Cleveland';
        } elseif (in_array($pc, ['TS14'])) {
            return 'Cleveland';
        } elseif (in_array($pc, ['TS15'])) {
            return 'Cleveland';
        } elseif (in_array($pc, ['TS16', 'TS17', 'TS18', 'TS19'])) {
            return 'Cleveland';
        } elseif (in_array($pc, ['TS20', 'TS21'])) {
            return 'Cleveland';
        } elseif (in_array($pc, ['TS22', 'TS23'])) {
            return 'Cleveland';
        } elseif (in_array($pc, ['TS24', 'TS25', 'TS26', 'TS27'])) {
            return 'Cleveland';
        } elseif (in_array($pc, ['CH5'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['CH6'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['CH6'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['CH7'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['CH7'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['CH8'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL11', 'LL12', 'LL13', 'LL14'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL15'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL16'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL17', 'LL18'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL18'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL19'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL20'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL21'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL22'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['LL28', 'LL29'])) {
            return 'Clwyd';
        } elseif (in_array($pc, ['EX23'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL10', 'PL11'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL12'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL13'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL14'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL15'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL17'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL18'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL18'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL22'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL23'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL24'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL25', 'PL26'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL27'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL28'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL29'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL30', 'PL31'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL32'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL33'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL34'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['PL35'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR1', 'TR2', 'TR3', 'TR4'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR5'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR6'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR7', 'TR8'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR9'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR10'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR11'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR12', 'TR13'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR14'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR15', 'TR16'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR17'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR18', 'TR19'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR20'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR26'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['TR27'])) {
            return 'Cornwall';
        } elseif (in_array($pc, ['Postcodes'])) {
            return 'County/State';
        } elseif (in_array($pc, ['CA1', 'CA2', 'CA3', 'CA4', 'CA5', 'CA6'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA99'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA7'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA8'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA9'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA10', 'CA11'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA12'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA13'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA14'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA95'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA15'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA16'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA17'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA18'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA19'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA20'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA21'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA22'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA23'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA24'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA25'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA26'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA27'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['CA28'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA7'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA8', 'LA9'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA10'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA11'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA12'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA13', 'LA14'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA14', 'LA15'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA16'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA17'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA18', 'LA19'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA20'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA21'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA22'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['LA23'])) {
            return 'Cumbria';
        } elseif (in_array($pc, ['DE1', 'DE3'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE21', 'DE22', 'DE23', 'DE24'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE65'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE72', 'DE73', 'DE74'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE99'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE4'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE5'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE6'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE7'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE11', 'DE12'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE45'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE55'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE56'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['DE75'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['S18'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['S32', 'S33'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['S40', 'S41', 'S42', 'S43', 'S44', 'S45', 'S49'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['SK13'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['SK17'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['SK22', 'SK23'])) {
            return 'Derbyshire';
        } elseif (in_array($pc, ['EX1', 'EX2', 'EX3', 'EX4', 'EX5', 'EX6'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX7'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX8'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX9'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX10'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX11'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX12'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX13'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX14'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX15'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX16'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX17'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX18'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX19'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX20'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX20'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX21'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX22'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX24'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX31', 'EX32'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX33'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX34'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX34'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX35'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX35'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX36'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX37'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX38'])) {
            return 'Devon';
        } elseif (in_array($pc, ['EX39'])) {
            return 'Devon';
        } elseif (in_array($pc, ['PL1', 'PL2', 'PL3', 'PL4', 'PL5', 'PL6', 'PL7', 'PL8', 'PL9'])) {
            return 'Devon';
        } elseif (in_array($pc, ['PL95'])) {
            return 'Devon';
        } elseif (in_array($pc, ['PL16'])) {
            return 'Devon';
        } elseif (in_array($pc, ['PL19'])) {
            return 'Devon';
        } elseif (in_array($pc, ['PL20'])) {
            return 'Devon';
        } elseif (in_array($pc, ['PL21'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ1', 'TQ2'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ3', 'TQ4'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ5'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ6'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ7'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ8'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ9'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ9', 'TQ10'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ11'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ12', 'TQ13'])) {
            return 'Devon';
        } elseif (in_array($pc, ['TQ14'])) {
            return 'Devon';
        } elseif (in_array($pc, ['BH1', 'BH2', 'BH3', 'BH4', 'BH5', 'BH6', 'BH7', 'BH8', 'BH9'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BH10', 'BH11'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BH12', 'BH13', 'BH14', 'BH15', 'BH16', 'BH17'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BH18'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BH19'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BH20'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BH21'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BH22'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BH23'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BH31'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['DT1', 'DT2'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['DT3', 'DT4'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['DT5'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['DT6'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['DT7'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['DT8'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['DT9'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['DT10'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['DT11'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['SP7'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['SP8'])) {
            return 'Dorset';
        } elseif (in_array($pc, ['BT18'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT19'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT20'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT21'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT22', 'BT23'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT24'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT25'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT26'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT30'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT31'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT32'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT33'])) {
            return 'Down';
        } elseif (in_array($pc, ['BT34', 'BT35'])) {
            return 'Down';
        } elseif (in_array($pc, ['DG1', 'DG2'])) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, ['DG3'])) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, ['DG4'])) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, ['DG10'])) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, ['DG11'])) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, ['DG12'])) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, ['DG13'])) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, ['DG14'])) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, ['DG16'])) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, ['G81'])) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, ['G82'])) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, ['G83'])) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, ['G83'])) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, ['G84'])) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, ['DH1', 'DH6', 'DH7', 'DH8'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DH97', 'DH98', 'DH99'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DH2', 'DH3'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DH8'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DH8', 'DH9'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DL1', 'DL2', 'DL3'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DL98'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DL4'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DL5'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DL12'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DL13', 'DL14'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DL15'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DL16'])) {
            return 'Durham';
        } elseif (in_array($pc, ['DL16', 'DL17'])) {
            return 'Durham';
        } elseif (in_array($pc, ['SR7'])) {
            return 'Durham';
        } elseif (in_array($pc, ['SR8'])) {
            return 'Durham';
        } elseif (in_array($pc, ['TS28'])) {
            return 'Durham';
        } elseif (in_array($pc, ['TS29'])) {
            return 'Durham';
        } elseif (in_array($pc, ['SA14', 'SA15'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA16'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA17'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA17'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA18'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA19'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA19'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA19'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA20'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA31', 'SA32', 'SA33'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA34'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA35'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA36'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA37'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA38'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA39'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA40'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA41'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA42'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA43'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA44'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA45'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA46', 'SA48'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA47'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA48'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA61', 'SA62'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA63'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA64'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA65'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA66'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA67'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA68'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA69'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA70'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA71', 'SA72'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA72'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SA73'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SY23'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SY23'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SY23'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SY24'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SY24'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SY24'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SY25'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['SY25'])) {
            return 'Dyfed';
        } elseif (in_array($pc, ['EH31'])) {
            return 'East Lothian';
        } elseif (in_array($pc, ['EH32'])) {
            return 'East Lothian';
        } elseif (in_array($pc, ['EH32'])) {
            return 'East Lothian';
        } elseif (in_array($pc, ['EH33', 'EH34', 'EH35'])) {
            return 'East Lothian';
        } elseif (in_array($pc, ['EH36'])) {
            return 'East Lothian';
        } elseif (in_array($pc, ['EH39'])) {
            return 'East Lothian';
        } elseif (in_array($pc, ['EH40'])) {
            return 'East Lothian';
        } elseif (in_array($pc, ['EH41'])) {
            return 'East Lothian';
        } elseif (in_array($pc, ['EH42'])) {
            return 'East Lothian';
        } elseif (in_array($pc, ['BN1', 'BN2'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN41', 'BN42', 'BN45'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN50', 'BN51'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN88'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN3'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN52'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN7', 'BN8'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN9'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN10'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN20', 'BN21', 'BN22', 'BN23'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN24'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN25'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN26'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['BN27'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['RH18'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN2', 'TN5'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN6'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN7'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN19'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN20'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN21'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN22'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN31'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN32'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN33'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN34', 'TN35'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN36'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN37', 'TN38'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN39'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['TN40'])) {
            return 'East Sussex';
        } elseif (in_array($pc, ['CB10', 'CB11'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM0'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM0'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM1', 'CM2', 'CM3'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM92', 'CM98', 'CM99'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM4'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM5'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM6', 'CM7'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM7'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM77'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM8'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM9'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM11', 'CM12'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM13', 'CM14', 'CM15'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM16'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM17', 'CM18', 'CM19'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM20'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CM24'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CO1', 'CO2', 'CO3', 'CO4', 'CO5', 'CO6', 'CO7'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CO9'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CO11'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CO12'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CO13'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CO14'])) {
            return 'Essex';
        } elseif (in_array($pc, ['CO15', 'CO16'])) {
            return 'Essex';
        } elseif (in_array($pc, ['EN9'])) {
            return 'Essex';
        } elseif (in_array($pc, ['IG1', 'IG2', 'IG3', 'IG4', 'IG5', 'IG6'])) {
            return 'Essex';
        } elseif (in_array($pc, ['IG7', 'IG8'])) {
            return 'Essex';
        } elseif (in_array($pc, ['IG8'])) {
            return 'Essex';
        } elseif (in_array($pc, ['IG9'])) {
            return 'Essex';
        } elseif (in_array($pc, ['IG10'])) {
            return 'Essex';
        } elseif (in_array($pc, ['IG11'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM1', 'RM2', 'RM3', 'RM4', 'RM5', 'RM6', 'RM7'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM8', 'RM9'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM10'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM11', 'RM12'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM13'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM14'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM15'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM16', 'RM17'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM20'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM18'])) {
            return 'Essex';
        } elseif (in_array($pc, ['RM19'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS0', 'SS1'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS1', 'SS2', 'SS3'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS22'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS99'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS4'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS5'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS6'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS7'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS8'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS9'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS11', 'SS12'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS13', 'SS14', 'SS15', 'SS16'])) {
            return 'Essex';
        } elseif (in_array($pc, ['SS17'])) {
            return 'Essex';
        } elseif (in_array($pc, ['BT74'])) {
            return 'Fermanagh';
        } elseif (in_array($pc, ['BT92', 'BT93', 'BT94'])) {
            return 'Fermanagh';
        } elseif (in_array($pc, ['DD6'])) {
            return 'Fife';
        } elseif (in_array($pc, ['DD6'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY1', 'KY2'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY3'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY4'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY4'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY5'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY6', 'KY7'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY8', 'KY9'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY10'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY11', 'KY12'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY99'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY11'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY14', 'KY15'])) {
            return 'Fife';
        } elseif (in_array($pc, ['KY16'])) {
            return 'Fife';
        } elseif (in_array($pc, ['GL1', 'GL2', 'GL3', 'GL4'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL19'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL5', 'GL6'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL7'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL7'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL7'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL8'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL10'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL11'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL11', 'GL12'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL13'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL14'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL14'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL14'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL15'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL15'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL16'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL17'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL17'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL17'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL17'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL17'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL18'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL18'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL20'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL50', 'GL51', 'GL52', 'GL53', 'GL54'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL55'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['GL56'])) {
            return 'Gloucestershire';
        } elseif (in_array($pc, ['NP4'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP7'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP10', 'NP11', 'NP18', 'NP19'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP20'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP12'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP13'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP15'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP16'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP22'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP23'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP24'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP25'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP26'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['NP44'])) {
            return 'Gwent';
        } elseif (in_array($pc, ['LL23'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL24'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL25'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL26'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL27'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL30'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL31', 'LL32'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL31'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL33'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL34'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL35'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL36'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL37'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL38'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL39'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL40'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL41'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL42'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL43'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL44'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL45'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL46'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL47'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL48'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL49'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL51'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL52'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL53'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL54', 'LL55'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL56'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL57'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL58'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL59'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL60'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL61'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL62'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL63'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL64'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL77'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL65'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL66'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL67'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL68'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL69'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL70'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL71'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL72'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL73'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL74'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL75'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL76'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL77'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['LL78'])) {
            return 'Gwynedd';
        } elseif (in_array($pc, ['BH24'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['BH25'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['GU11', 'GU12'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['GU14'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['GU30'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['GU31', 'GU32'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['GU33'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['GU34'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['GU35'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['GU46'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['GU51', 'GU52'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO1', 'PO2', 'PO3', 'PO6'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO4', 'PO5'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO7', 'PO8'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO9'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO9'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO10'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO11'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO12', 'PO13'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO12', 'PO13'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['PO14', 'PO15', 'PO16', 'PO17'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['RG21', 'RG22', 'RG23', 'RG24', 'RG25', 'RG28'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['RG26'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['RG27', 'RG29'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['RG28'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO14', 'SO15', 'SO16', 'SO17', 'SO18', 'SO19'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO30', 'SO31', 'SO32'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO40', 'SO45'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO52'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO97'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO20'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO21', 'SO22', 'SO23', 'SO25'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO24'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO40', 'SO43'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO41'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO42'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO50', 'SO53'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SO51'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SP6'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['SP10', 'SP11'])) {
            return 'Hampshire';
        } elseif (in_array($pc, ['HR1', 'HR2', 'HR3', 'HR4'])) {
            return 'Herefordshire';
        } elseif (in_array($pc, ['HR5'])) {
            return 'Herefordshire';
        } elseif (in_array($pc, ['HR6'])) {
            return 'Herefordshire';
        } elseif (in_array($pc, ['HR7'])) {
            return 'Herefordshire';
        } elseif (in_array($pc, ['HR8'])) {
            return 'Herefordshire';
        } elseif (in_array($pc, ['HR9'])) {
            return 'Herefordshire';
        } elseif (in_array($pc, ['AL1', 'AL2', 'AL3', 'AL4'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['AL5'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['AL6', 'AL7'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['AL7', 'AL8'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['AL9'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['AL10'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['CM21'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['CM22', 'CM23'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['EN4', 'EN5'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['EN6'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['EN7', 'EN8'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['EN77'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['EN10', 'EN11'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['EN11'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['HP1', 'HP2', 'HP3'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['HP4'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['HP23'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG1', 'SG2'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG3'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG4', 'SG5', 'SG6'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG6'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG7'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG8'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG9'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG10'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG11', 'SG12'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['SG13', 'SG14'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['WD3'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['WD4', 'WD18'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['WD5'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['WD6'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['WD7'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['WD17', 'WD18', 'WD19'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['WD24', 'WD25'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['WD99'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['WD23'])) {
            return 'Hertfordshire';
        } elseif (in_array($pc, ['HS8'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['IV1', 'IV2', 'IV3', 'IV5'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['IV13'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['IV63'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['IV99'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['IV4'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH19'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH20'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH21'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH22'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH23'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH24'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH25'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH30'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH31'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH32'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH33'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH34'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH35'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH37'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH38'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH39'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['PH40', 'PH41'])) {
            return 'Inverness-shire';
        } elseif (in_array($pc, ['KA27'])) {
            return 'Isle of Arran';
        } elseif (in_array($pc, ['HS9'])) {
            return 'Isle of Barra';
        } elseif (in_array($pc, ['HS7'])) {
            return 'Isle of Benbecula';
        } elseif (in_array($pc, ['PA20'])) {
            return 'Isle of Bute';
        } elseif (in_array($pc, ['PH44'])) {
            return 'Isle of Canna';
        } elseif (in_array($pc, ['PA78'])) {
            return 'Isle of Coll';
        } elseif (in_array($pc, ['PA61'])) {
            return 'Isle of Colonsay';
        } elseif (in_array($pc, ['KA28'])) {
            return 'Isle of Cumbrae';
        } elseif (in_array($pc, ['PH42'])) {
            return 'Isle of Eigg';
        } elseif (in_array($pc, ['PA41'])) {
            return 'Isle of Gigha';
        } elseif (in_array($pc, ['HS3', 'HS5'])) {
            return 'Isle of Harris';
        } elseif (in_array($pc, ['PA76'])) {
            return 'Isle of Iona';
        } elseif (in_array($pc, ['PA42', 'PA43', 'PA44', 'PA45', 'PA46', 'PA47', 'PA48', 'PA49'])) {
            return 'Isle of Islay';
        } elseif (in_array($pc, ['PA60'])) {
            return 'Isle of Jura';
        } elseif (in_array($pc, ['HS1'])) {
            return 'Isle of Lewis';
        } elseif (in_array($pc, ['HS2'])) {
            return 'Isle of Lewis';
        } elseif (in_array($pc, ['IM1', 'IM2', 'IM3', 'IM4', 'IM5', 'IM6', 'IM7', 'IM8', 'IM9'])) {
            return 'Isle of Man';
        } elseif (in_array($pc, ['IM99'])) {
            return 'Isle of Man';
        } elseif (in_array($pc, ['PA62', 'PA63', 'PA64', 'PA65', 'PA66', 'PA67', 'PA68', 'PA69'])) {
            return 'Isle of Mull';
        } elseif (in_array($pc, ['PA70', 'PA71', 'PA72', 'PA73', 'PA74', 'PA75'])) {
            return 'Isle of Mull';
        } elseif (in_array($pc, ['HS6'])) {
            return 'Isle of North Uist';
        } elseif (in_array($pc, ['PH43'])) {
            return 'Isle of Rum';
        } elseif (in_array($pc, ['HS4'])) {
            return 'Isle of Scalpay';
        } elseif (in_array($pc, ['IV41', 'IV42', 'IV43', 'IV44', 'IV45', 'IV46', 'IV47', 'IV48', 'IV49'])) {
            return 'Isle of Skye';
        } elseif (in_array($pc, ['IV55', 'IV56'])) {
            return 'Isle of Skye';
        } elseif (in_array($pc, ['IV51'])) {
            return 'Isle of Skye';
        } elseif (in_array($pc, ['PA77'])) {
            return 'Isle of Tiree';
        } elseif (in_array($pc, ['PO30'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO30'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO41'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO31'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO32'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO33'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO34'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO35'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO36'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO36', 'PO37'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO38'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO39'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['PO40'])) {
            return 'Isle of Wight';
        } elseif (in_array($pc, ['TR21', 'TR22', 'TR23', 'TR24', 'TR25'])) {
            return 'Isles of Scilly';
        } elseif (in_array($pc, ['BR1', 'BR2'])) {
            return 'Kent';
        } elseif (in_array($pc, ['BR2'])) {
            return 'Kent';
        } elseif (in_array($pc, ['BR3'])) {
            return 'Kent';
        } elseif (in_array($pc, ['BR4'])) {
            return 'Kent';
        } elseif (in_array($pc, ['BR5', 'BR6'])) {
            return 'Kent';
        } elseif (in_array($pc, ['BR7'])) {
            return 'Kent';
        } elseif (in_array($pc, ['BR8'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT1', 'CT2', 'CT3', 'CT4'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT5'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT6'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT7', 'CT9'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT8'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT9'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT10'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT11', 'CT12'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT13'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT14'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT15', 'CT16', 'CT17'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT18', 'CT19'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT20'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT50'])) {
            return 'Kent';
        } elseif (in_array($pc, ['CT21'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA1', 'DA2', 'DA4'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA10'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA3'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA5'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA6', 'DA7'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA7'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA16'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA8'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA18'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA9'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA10'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA11', 'DA12', 'DA13'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA14', 'DA15'])) {
            return 'Kent';
        } elseif (in_array($pc, ['DA17'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME1', 'ME2', 'ME3'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME4', 'ME5'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME6'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME20'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME6'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME6'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME19'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME7', 'ME8'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME9'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME10'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME11'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME12'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME13'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME14', 'ME15', 'ME16', 'ME17', 'ME18'])) {
            return 'Kent';
        } elseif (in_array($pc, ['ME99'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN1', 'TN2', 'TN3', 'TN4'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN8'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN9'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN10', 'TN11', 'TN12'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN13', 'TN14', 'TN15'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN16'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN17', 'TN18'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN23', 'TN24', 'TN25', 'TN26', 'TN27'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN28'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN29'])) {
            return 'Kent';
        } elseif (in_array($pc, ['TN30'])) {
            return 'Kent';
        } elseif (in_array($pc, ['AB30'])) {
            return 'Kincardineshire';
        } elseif (in_array($pc, ['AB31'])) {
            return 'Kincardineshire';
        } elseif (in_array($pc, ['AB39'])) {
            return 'Kincardineshire';
        } elseif (in_array($pc, ['DG5'])) {
            return 'Kirkcudbrightshire';
        } elseif (in_array($pc, ['DG6'])) {
            return 'Kirkcudbrightshire';
        } elseif (in_array($pc, ['DG7'])) {
            return 'Kirkcudbrightshire';
        } elseif (in_array($pc, ['G1', 'G2', 'G3', 'G4', 'G5', 'G9'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['G11', 'G12', 'G13', 'G14', 'G15'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['G20', 'G21', 'G22', 'G23'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['G31', 'G32', 'G33', 'G34'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['G40', 'G41', 'G42', 'G43', 'G44', 'G45', 'G46'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['G51', 'G52', 'G53', 'G58'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['G60', 'G61', 'G62', 'G63', 'G64', 'G65', 'G66', 'G67', 'G68', 'G69'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['G70', 'G71', 'G72', 'G73', 'G74', 'G75', 'G76', 'G77', 'G78', 'G79'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['G90'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML1'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML2'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML3'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML4'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML5'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML6'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML7'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML8'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML9'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML10'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML11'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['ML12'])) {
            return 'Lanarkshire';
        } elseif (in_array($pc, ['BB1', 'BB2', 'BB6'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BB3'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BB4'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BB5'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BB7'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BB8'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BB9'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BB10', 'BB11', 'BB12'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BB18'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BB94'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BL0', 'BL8', 'BL9'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BL1', 'BL2', 'BL3', 'BL4', 'BL5', 'BL6', 'BL7'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BL11'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['BL78'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['FY0', 'FY1', 'FY2', 'FY3', 'FY4'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['FY5'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['FY6'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['FY7'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['FY8'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['L39'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['L40'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['LA1', 'LA2'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['LA3', 'LA4'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['LA5', 'LA6'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M1', 'M2', 'M3', 'M4', 'M8', 'M9'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M11', 'M12', 'M13', 'M14', 'M15', 'M16', 'M17', 'M18', 'M19'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M20', 'M21', 'M22', 'M23', 'M24', 'M25', 'M26', 'M27', 'M28', 'M29'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M30', 'M31', 'M32', 'M34', 'M35', 'M38'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M40', 'M41', 'M43', 'M44', 'M45', 'M46'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M60', 'M61'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M90', 'M99'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M3', 'M5', 'M6', 'M7'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M50'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['M60'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['OL1', 'OL2', 'OL3', 'OL4', 'OL8', 'OL9'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['OL95'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['OL5', 'OL6', 'OL7'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['OL10'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['OL11', 'OL12', 'OL16'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['OL13'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['OL14'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['OL15', 'OL16'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['PR0', 'PR1', 'PR2', 'PR3', 'PR4', 'PR5'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['PR6', 'PR7'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['PR25', 'PR26'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['WN1', 'WN2', 'WN3', 'WN4', 'WN5', 'WN6', 'WN8'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['WN7'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['WN8'])) {
            return 'Lancashire';
        } elseif (in_array($pc, ['LE1', 'LE2', 'LE3', 'LE4', 'LE5', 'LE6', 'LE7', 'LE8', 'LE9'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE19'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE21'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE41'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE55'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE87'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE94', 'LE95'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE10'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE11', 'LE12'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE13', 'LE14'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE17'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE18'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE65'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE67'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE67'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['LE67'])) {
            return 'Leicestershire';
        } elseif (in_array($pc, ['DN21'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['LN1', 'LN2', 'LN3', 'LN4', 'LN5', 'LN6'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['LN7', 'LN8'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['LN9'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['LN10'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['LN11'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['LN12'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['LN13'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['NG31', 'NG32', 'NG33'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['NG34'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['PE9'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['PE10'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['PE11', 'PE12'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['PE20', 'PE21', 'PE22'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['PE23'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['PE24', 'PE25'])) {
            return 'Lincolnshire';
        } elseif (in_array($pc, ['E1', 'E1W'])) {
            return 'London';
        } elseif (in_array($pc, ['E2', 'E3', 'E4', 'E5', 'E6', 'E7', 'E8', 'E9'])) {
            return 'London';
        } elseif (in_array($pc, ['E10', 'E11', 'E12', 'E13', 'E14', 'E15', 'E16', 'E17', 'E18'])) {
            return 'London';
        } elseif (in_array($pc, ['E20'])) {
            return 'London';
        } elseif (in_array($pc, ['E77'])) {
            return 'London';
        } elseif (in_array($pc, ['E98'])) {
            return 'London';
        } elseif (in_array($pc, ['EC1A', 'EC1M', 'EC1N', 'EC1P', 'EC1R', 'EC1V', 'EC1Y'])) {
            return 'London';
        } elseif (in_array($pc, ['EC2A', 'EC2M', 'EC2N', 'EC2P', 'EC2R', 'EC2V', 'EC2Y'])) {
            return 'London';
        } elseif (in_array($pc, ['EC3A', 'EC3M', 'EC3N', 'EC3P', 'EC3R', 'EC3V'])) {
            return 'London';
        } elseif (in_array($pc, ['EC4A', 'EC4M', 'EC4N', 'EC4P', 'EC4R', 'EC4V', 'EC4Y'])) {
            return 'London';
        } elseif (in_array($pc, ['EC50'])) {
            return 'London';
        } elseif (in_array($pc, ['N1', 'N1C', 'N1P'])) {
            return 'London';
        } elseif (in_array($pc, ['N2', 'N3', 'N4', 'N5', 'N6', 'N7', 'N8', 'N9'])) {
            return 'London';
        } elseif (in_array($pc, ['N10', 'N11', 'N12', 'N13', 'N14', 'N15', 'N16', 'N17', 'N18', 'N19'])) {
            return 'London';
        } elseif (in_array($pc, ['N20', 'N21', 'N22'])) {
            return 'London';
        } elseif (in_array($pc, ['N81'])) {
            return 'London';
        } elseif (in_array($pc, ['NW1', 'NW1W'])) {
            return 'London';
        } elseif (in_array($pc, ['NW2', 'NW3', 'NW4', 'NW5', 'NW6', 'NW7', 'NW8', 'NW9'])) {
            return 'London';
        } elseif (in_array($pc, ['NW10', 'NW11'])) {
            return 'London';
        } elseif (in_array($pc, ['NW26'])) {
            return 'London';
        } elseif (in_array($pc, ['SE1', 'SE1P'])) {
            return 'London';
        } elseif (in_array($pc, ['SE2', 'SE3', 'SE4', 'SE5', 'SE6', 'SE7', 'SE8', 'SE9'])) {
            return 'London';
        } elseif (in_array($pc, ['SE10', 'SE11', 'SE12', 'SE13', 'SE14', 'SE15', 'SE16', 'SE17', 'SE18', 'SE19'])) {
            return 'London';
        } elseif (in_array($pc, ['SE20', 'SE21', 'SE22', 'SE23', 'SE24', 'SE25', 'SE26', 'SE27', 'SE28'])) {
            return 'London';
        } elseif (in_array($pc, ['SW1A', 'SW1E', 'SW1H', 'SW1P', 'SW1V', 'SW1W', 'SW1X', 'SW1Y'])) {
            return 'London';
        } elseif (in_array($pc, ['SW2', 'SW3', 'SW4', 'SW5', 'SW6', 'SW7', 'SW8', 'SW9'])) {
            return 'London';
        } elseif (in_array($pc, ['SW10', 'SW11', 'SW12', 'SW13', 'SW14', 'SW15', 'SW16', 'SW17', 'SW18', 'SW19'])) {
            return 'London';
        } elseif (in_array($pc, ['SW20'])) {
            return 'London';
        } elseif (in_array($pc, ['SW95'])) {
            return 'London';
        } elseif (in_array($pc, ['W1A', 'W1B', 'W1C', 'W1D', 'W1F', 'W1G', 'W1H', 'W1J', 'W1K', 'W1S', 'W1T', 'W1U', 'W1W'])) {
            return 'London';
        } elseif (in_array($pc, ['W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8', 'W9'])) {
            return 'London';
        } elseif (in_array($pc, ['W10', 'W11', 'W12', 'W13', 'W14'])) {
            return 'London';
        } elseif (in_array($pc, ['WC1A', 'WC1B', 'WC1E', 'WC1H', 'WC1N', 'WC1R', 'WC1V', 'WC1X'])) {
            return 'London';
        } elseif (in_array($pc, ['WC2A', 'WC2B', 'WC2E', 'WC2H', 'WC2N', 'WC2R'])) {
            return 'London';
        } elseif (in_array($pc, ['BT45'])) {
            return 'Londonderry';
        } elseif (in_array($pc, ['BT46'])) {
            return 'Londonderry';
        } elseif (in_array($pc, ['BT47', 'BT48'])) {
            return 'Londonderry';
        } elseif (in_array($pc, ['BT49'])) {
            return 'Londonderry';
        } elseif (in_array($pc, ['BT51', 'BT52'])) {
            return 'Londonderry';
        } elseif (in_array($pc, ['BT55'])) {
            return 'Londonderry';
        } elseif (in_array($pc, ['CH25'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH41', 'CH42'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH26'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH43'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH27'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH44', 'CH45'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH28', 'CH29'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH30', 'CH31', 'CH32'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH46', 'CH47', 'CH48', 'CH49'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH60', 'CH61', 'CH62', 'CH63'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH33'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH64'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH34'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CH65', 'CH66'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L1', 'L2', 'L3', 'L4', 'L5', 'L6', 'L7', 'L8', 'L9'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L10', 'L11', 'L12', 'L13', 'L14', 'L15', 'L16', 'L17', 'L18', 'L19'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L20', 'L21', 'L22', 'L23', 'L24', 'L25', 'L26', 'L27', 'L28', 'L29'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L31', 'L32', 'L33', 'L36', 'L37', 'L38'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L67', 'L68', 'L69'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L70', 'L71', 'L72', 'L73', 'L74', 'L75'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L20'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L30'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L69'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L80'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['GIR'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['L34', 'L35'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['PR8', 'PR9'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['WA9'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['WA10', 'WA11'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['WA12'])) {
            return 'Merseyside';
        } elseif (in_array($pc, ['CF31', 'CF32', 'CF33', 'CF35'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF34'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF36'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF37', 'CF38'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF39'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF40'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF41'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF42'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF43'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF44'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF45'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF46'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF47', 'CF48'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF72'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF81'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF82'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['CF83'])) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, ['EN1', 'EN2', 'EN3'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['HA0', 'HA9'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['HA1', 'HA2', 'HA3'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['HA4'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['HA5'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['HA6'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['HA7'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['HA8'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW1', 'TW2'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW3', 'TW4', 'TW5', 'TW6'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW7'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW8'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW11'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW12'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW13', 'TW14'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW15'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW16'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW17'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['TW18', 'TW19'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['UB1', 'UB2', 'UB3'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['UB3', 'UB4'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['UB5'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['UB5', 'UB6'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['UB18'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['UB7', 'UB8'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['UB8', 'UB9'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['UB10', 'UB11'])) {
            return 'Middlesex';
        } elseif (in_array($pc, ['EH1', 'EH2', 'EH3', 'EH4', 'EH5', 'EH6', 'EH7', 'EH8', 'EH9'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH10', 'EH11', 'EH12', 'EH13', 'EH14', 'EH15', 'EH16', 'EH17'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH91', 'EH95', 'EH99'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH14'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH14'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH14'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH18'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH19'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH20'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH21'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH22'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH23'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH24'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH25'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH26'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH27'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH28'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH37'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['EH38'])) {
            return 'Midlothian';
        } elseif (in_array($pc, ['IV30'])) {
            return 'Moray';
        } elseif (in_array($pc, ['IV31'])) {
            return 'Moray';
        } elseif (in_array($pc, ['IV32'])) {
            return 'Moray';
        } elseif (in_array($pc, ['IV36'])) {
            return 'Moray';
        } elseif (in_array($pc, ['PH26'])) {
            return 'Moray';
        } elseif (in_array($pc, ['IV12'])) {
            return 'Nairnshire';
        } elseif (in_array($pc, ['IP20'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['IP21', 'IP22'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['IP98'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['IP24', 'IP25', 'IP26'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR1', 'NR2', 'NR3', 'NR4', 'NR5', 'NR6', 'NR7', 'NR8', 'NR9'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR10', 'NR11', 'NR12', 'NR13', 'NR14', 'NR15', 'NR16', 'NR18', 'NR19'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR26', 'NR28'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR99'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR17'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR18'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR19'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR20'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR21'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR22'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR23'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR24'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR25'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR26'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR27'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR28'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR29'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['NR30', 'NR31'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['PE30', 'PE31', 'PE32', 'PE33', 'PE34'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['PE35'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['PE36'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['PE37'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['PE38'])) {
            return 'Norfolk';
        } elseif (in_array($pc, ['DN14'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU1', 'HU2', 'HU3', 'HU4', 'HU5', 'HU6', 'HU7', 'HU8', 'HU9'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU10', 'HU11', 'HU12'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU13'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU14'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU15'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU16'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU20'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU17'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU18'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['HU19'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['YO15', 'YO16'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['YO25'])) {
            return 'North Humberside';
        } elseif (in_array($pc, ['BD23', 'BD24'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['BD24'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['DL6', 'DL7'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['DL8'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['DL8'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['DL8'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['DL9'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['DL10', 'DL11'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['HG1', 'HG2', 'HG3'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['HG4'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['HG5'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['LS24'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO1'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO10', 'YO19'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO23', 'YO24', 'YO26'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO30', 'YO31', 'YO32'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO41', 'YO42', 'YO43'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO51'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO60', 'YO61', 'YO62'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO90', 'YO91'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO7'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO8'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO11', 'YO12', 'YO13'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO14'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO17'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO18'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['YO21', 'YO22'])) {
            return 'North Yorkshire';
        } elseif (in_array($pc, ['NN1', 'NN2', 'NN3', 'NN4', 'NN5', 'NN6', 'NN7'])) {
            return 'Northamptonshire';
        } elseif (in_array($pc, ['NN8', 'NN9'])) {
            return 'Northamptonshire';
        } elseif (in_array($pc, ['NN29'])) {
            return 'Northamptonshire';
        } elseif (in_array($pc, ['NN10'])) {
            return 'Northamptonshire';
        } elseif (in_array($pc, ['NN11'])) {
            return 'Northamptonshire';
        } elseif (in_array($pc, ['NN12'])) {
            return 'Northamptonshire';
        } elseif (in_array($pc, ['NN13'])) {
            return 'Northamptonshire';
        } elseif (in_array($pc, ['NN14', 'NN15', 'NN16'])) {
            return 'Northamptonshire';
        } elseif (in_array($pc, ['NN17', 'NN18'])) {
            return 'Northamptonshire';
        } elseif (in_array($pc, ['NE22'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE23'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE24'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE41'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE42'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE43'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE44'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE45'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE46', 'NE47', 'NE48'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE49'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE61', 'NE65'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE62'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE63'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE64'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE66'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE66', 'NE69'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE67'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE68'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE70'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['NE71'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['TD12'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['TD12'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['TD15'])) {
            return 'Northumberland';
        } elseif (in_array($pc, ['DN22'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG1', 'NG2', 'NG3', 'NG4', 'NG5', 'NG6', 'NG7', 'NG8', 'NG9'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG10', 'NG11', 'NG12', 'NG13', 'NG14', 'NG15', 'NG16', 'NG17'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG80'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG90'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG17'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG18', 'NG19'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG20', 'NG21'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG70'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG22', 'NG23', 'NG24'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['NG25'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['S80', 'S81'])) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, ['KW15'])) {
            return 'Orkney';
        } elseif (in_array($pc, ['KW16'])) {
            return 'Orkney';
        } elseif (in_array($pc, ['KW17'])) {
            return 'Orkney';
        } elseif (in_array($pc, ['OX1', 'OX2', 'OX3', 'OX4'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX33'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX44'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX5'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX7'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX9'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX10'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX11'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX12'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX13', 'OX14'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX15', 'OX16', 'OX17'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX18'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX18'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX18'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX20'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX25', 'OX26', 'OX27'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX28', 'OX29'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX39'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['OX49'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['RG9'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['SN7'])) {
            return 'Oxfordshire';
        } elseif (in_array($pc, ['EH43'])) {
            return 'Peeblesshire';
        } elseif (in_array($pc, ['EH44'])) {
            return 'Peeblesshire';
        } elseif (in_array($pc, ['EH45'])) {
            return 'Peeblesshire';
        } elseif (in_array($pc, ['EH46'])) {
            return 'Peeblesshire';
        } elseif (in_array($pc, ['KY13'])) {
            return 'Perthshire and Kinross';
        } elseif (in_array($pc, ['FK15'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['FK16'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['FK17', 'FK18'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['FK19'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['FK20'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['FK21'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['PH1', 'PH2'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['PH14'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['PH3', 'PH4'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['PH5', 'PH6', 'PH7'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['PH8'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['PH9'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['PH16', 'PH17', 'PH18'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['PH10', 'PH11', 'PH12', 'PH13'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['PH15'])) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, ['LD1'])) {
            return 'Powys';
        } elseif (in_array($pc, ['LD2'])) {
            return 'Powys';
        } elseif (in_array($pc, ['LD3'])) {
            return 'Powys';
        } elseif (in_array($pc, ['LD4'])) {
            return 'Powys';
        } elseif (in_array($pc, ['LD5'])) {
            return 'Powys';
        } elseif (in_array($pc, ['LD6'])) {
            return 'Powys';
        } elseif (in_array($pc, ['LD7'])) {
            return 'Powys';
        } elseif (in_array($pc, ['LD8'])) {
            return 'Powys';
        } elseif (in_array($pc, ['NP7', 'NP8'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY15'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY16'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY17'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY17'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY18'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY19'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY20'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY21'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY22'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY22'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY22'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY22'])) {
            return 'Powys';
        } elseif (in_array($pc, ['SY22'])) {
            return 'Powys';
        } elseif (in_array($pc, ['PA1', 'PA2', 'PA3'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA4'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA5', 'PA6', 'PA9'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA10'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA7'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA8'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA11'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA12'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA13'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA14'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA15', 'PA16'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA18'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['PA19'])) {
            return 'Renfrewshire';
        } elseif (in_array($pc, ['IV6'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV7'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV15', 'IV16'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV8'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV9'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV10'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV11'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV14'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV17'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV18'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV19'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV20'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV21'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV22'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV23'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV26'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV40'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV52'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV53'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['IV54'])) {
            return 'Ross-shire';
        } elseif (in_array($pc, ['TD5'])) {
            return 'Roxburghshire';
        } elseif (in_array($pc, ['TD6'])) {
            return 'Roxburghshire';
        } elseif (in_array($pc, ['TD8'])) {
            return 'Roxburghshire';
        } elseif (in_array($pc, ['TD9'])) {
            return 'Roxburghshire';
        } elseif (in_array($pc, ['TD9'])) {
            return 'Roxburghshire';
        } elseif (in_array($pc, ['LE15'])) {
            return 'Rutland';
        } elseif (in_array($pc, ['LE16'])) {
            return 'Rutland';
        } elseif (in_array($pc, ['TD1'])) {
            return 'Selkirkshire';
        } elseif (in_array($pc, ['TD7'])) {
            return 'Selkirkshire';
        } elseif (in_array($pc, ['ZE1', 'ZE2', 'ZE3'])) {
            return 'Shetland';
        } elseif (in_array($pc, ['SY1', 'SY2', 'SY3', 'SY4', 'SY5'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY99'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY6'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY7'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY7'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY7'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY8'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY9'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY10', 'SY11'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY12'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['SY13'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['TF1', 'TF2', 'TF3', 'TF4', 'TF5', 'TF6', 'TF7', 'TF8'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['TF9'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['TF10'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['TF11'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['TF12'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['TF13'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['WV15', 'WV16'])) {
            return 'Shropshire';
        } elseif (in_array($pc, ['BA4'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA5'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA6'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA7'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA8'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA9'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA9'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA10'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA11'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA16'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BA20', 'BA21', 'BA22'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BS26'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BS27'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['BS28'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA1', 'TA2', 'TA3', 'TA4'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA5', 'TA6', 'TA7'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA8'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA9'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA10'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA11'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA12'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA13'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA14'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA15'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA16'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA17'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA18'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA19'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA20'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA21'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA22'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA23'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['TA24'])) {
            return 'Somerset';
        } elseif (in_array($pc, ['CF3', 'CF5'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF10', 'CF11', 'CF14', 'CF15'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF23', 'CF24'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF30'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF91', 'CF95', 'CF99'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF61'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF71'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF62', 'CF63'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF64'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF64'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['CF71'])) {
            return 'South Glamorgan';
        } elseif (in_array($pc, ['DN15', 'DN16', 'DN17'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN18'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN19'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN20'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN31', 'DN32', 'DN33', 'DN34', 'DN36', 'DN37'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN41'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN35'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN38'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN39'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN40'])) {
            return 'South Humberside';
        } elseif (in_array($pc, ['DN1', 'DN2', 'DN3', 'DN4', 'DN5', 'DN6', 'DN7', 'DN8', 'DN9'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['DN10', 'DN11', 'DN12'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['DN55'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['S10', 'S11', 'S12', 'S13', 'S14', 'S17'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['S20', 'S21', 'S25', 'S26'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['S35', 'S36'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['S95', 'S96', 'S97', 'S98', 'S99'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['S60', 'S61', 'S62', 'S63', 'S65', 'S66'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['S64'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['S70', 'S71', 'S72', 'S73', 'S74', 'S75'])) {
            return 'South Yorkshire';
        } elseif (in_array($pc, ['B77', 'B78', 'B79'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['DE13', 'DE14', 'DE15'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['ST1', 'ST2', 'ST3', 'ST4', 'ST6', 'ST7', 'ST8', 'ST9'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['ST5'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['ST13'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['ST14'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['ST15'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['ST16', 'ST17', 'ST18', 'ST19'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['ST20', 'ST21'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['WS7'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['WS11', 'WS12'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['WS13', 'WS14'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['WS15'])) {
            return 'Staffordshire';
        } elseif (in_array($pc, ['FK1', 'FK2'])) {
            return 'Sterling';
        } elseif (in_array($pc, ['FK3'])) {
            return 'Sterling';
        } elseif (in_array($pc, ['FK4'])) {
            return 'Sterling';
        } elseif (in_array($pc, ['FK5'])) {
            return 'Sterling';
        } elseif (in_array($pc, ['FK6'])) {
            return 'Sterling';
        } elseif (in_array($pc, ['FK7', 'FK8', 'FK9'])) {
            return 'Sterling';
        } elseif (in_array($pc, ['CB8'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['CB9'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['CO8'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['CO10'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP1', 'IP2', 'IP3', 'IP4', 'IP5', 'IP6', 'IP7', 'IP8', 'IP9'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP10'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP11'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP12', 'IP13'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP14'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP15'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP16'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP17'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP18'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP19'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP21', 'IP23'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP27'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP28', 'IP29'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['IP30', 'IP31', 'IP32', 'IP33'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['NR32', 'NR33'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['NR34'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['NR35'])) {
            return 'Suffolk';
        } elseif (in_array($pc, ['CR0', 'CR9'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR44'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR90'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR2'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR3'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR3'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR4'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR5'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR6'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR7'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR8'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['CR8'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU1', 'GU2', 'GU3', 'GU4', 'GU5'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU6'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU7', 'GU8'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU9'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU10'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU15', 'GU16', 'GU17'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU95'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU18'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU19'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU20'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU21', 'GU22', 'GU23', 'GU24'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU25'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU26', 'GU27'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['GU27'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT1', 'KT2'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT3'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT4'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT5', 'KT6'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT7'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT8'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT8'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT9'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT10'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT11'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT12'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT13'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT14'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT15'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT16'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT17', 'KT18', 'KT19'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT20'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT21'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['KT22', 'KT23', 'KT24'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['RH1'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['RH2'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['RH3', 'RH4'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['RH4', 'RH5'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['RH6'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['RH7'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['RH8'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['RH9'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['SM1', 'SM2', 'SM3'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['SM4'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['SM5'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['SM6'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['SM7'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['TW9'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['TW10'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['TW20'])) {
            return 'Surrey';
        } elseif (in_array($pc, ['IV24'])) {
            return 'Sutherland';
        } elseif (in_array($pc, ['IV25'])) {
            return 'Sutherland';
        } elseif (in_array($pc, ['IV27'])) {
            return 'Sutherland';
        } elseif (in_array($pc, ['IV28'])) {
            return 'Sutherland';
        } elseif (in_array($pc, ['KW8'])) {
            return 'Sutherland';
        } elseif (in_array($pc, ['KW9'])) {
            return 'Sutherland';
        } elseif (in_array($pc, ['KW10'])) {
            return 'Sutherland';
        } elseif (in_array($pc, ['KW11'])) {
            return 'Sutherland';
        } elseif (in_array($pc, ['KW13'])) {
            return 'Sutherland';
        } elseif (in_array($pc, ['DH4', 'DH5'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE1', 'NE2', 'NE3', 'NE4', 'NE5', 'NE6', 'NE7'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE12', 'NE13', 'NE15', 'NE16', 'NE17', 'NE18', 'NE19'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE20', 'NE27'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE82', 'NE83', 'NE85', 'NE88'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE98', 'NE99'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE8', 'NE9'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE10', 'NE11'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE92'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE21'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE25', 'NE26'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE28'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE29'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE30'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE31'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE32'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE33', 'NE34'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE35'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE36'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE37', 'NE38'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE39'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['NE40'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['SR1', 'SR2', 'SR3', 'SR4', 'SR5', 'SR6', 'SR9'])) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, ['BT68'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['BT69'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['BT70', 'BT71'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['BT75'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['BT76'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['BT77'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['BT78', 'BT79'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['BT80'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['BT81'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['BT82'])) {
            return 'Tyrone';
        } elseif (in_array($pc, ['B49'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['B50'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['B80'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV8'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV9'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV10', 'CV11', 'CV13'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV12'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV21', 'CV22', 'CV23'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV31', 'CV32', 'CV33'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV34', 'CV35'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV36', 'CV37'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV37'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['CV47'])) {
            return 'Warwickshire';
        } elseif (in_array($pc, ['SA1', 'SA2', 'SA3', 'SA4', 'SA5', 'SA6', 'SA7', 'SA8', 'SA9'])) {
            return 'West Glamorgan';
        } elseif (in_array($pc, ['SA80'])) {
            return 'West Glamorgan';
        } elseif (in_array($pc, ['SA99'])) {
            return 'West Glamorgan';
        } elseif (in_array($pc, ['SA10', 'SA11'])) {
            return 'West Glamorgan';
        } elseif (in_array($pc, ['SA12', 'SA13'])) {
            return 'West Glamorgan';
        } elseif (in_array($pc, ['EH29'])) {
            return 'West Lothian';
        } elseif (in_array($pc, ['EH30'])) {
            return 'West Lothian';
        } elseif (in_array($pc, ['EH47', 'EH48'])) {
            return 'West Lothian';
        } elseif (in_array($pc, ['EH49'])) {
            return 'West Lothian';
        } elseif (in_array($pc, ['EH51'])) {
            return 'West Lothian';
        } elseif (in_array($pc, ['EH52'])) {
            return 'West Lothian';
        } elseif (in_array($pc, ['EH53', 'EH54'])) {
            return 'West Lothian';
        } elseif (in_array($pc, ['EH55'])) {
            return 'West Lothian';
        } elseif (in_array($pc, ['B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8', 'B9'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B10', 'B11', 'B12', 'B13', 'B14', 'B15', 'B16', 'B17', 'B18', 'B19'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B20', 'B21', 'B23', 'B24', 'B25', 'B26', 'B27', 'B28', 'B29'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B30', 'B31', 'B32', 'B33', 'B34', 'B35', 'B36', 'B37', 'B38'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B40', 'B42', 'B43', 'B44', 'B45', 'B46', 'B47', 'B48'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B99'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B62', 'B63'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B64'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B65'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B66', 'B67'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B68', 'B69'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B70', 'B71'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B72', 'B73', 'B74', 'B75', 'B76'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B90', 'B91', 'B92', 'B93', 'B94'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['B95'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['CV1', 'CV2', 'CV3', 'CV4', 'CV5', 'CV6', 'CV7', 'CV8'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['DY1', 'DY2', 'DY3'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['DY4'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['DY5'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['DY6'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['DY7', 'DY8', 'DY9'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['WS1', 'WS2', 'WS3', 'WS4', 'WS5', 'WS6', 'WS8', 'WS9'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['WS10'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['WV1', 'WV2', 'WV3', 'WV4', 'WV5', 'WV6', 'WV7', 'WV8', 'WV9'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['WV1'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['WV14'])) {
            return 'West Midlands';
        } elseif (in_array($pc, ['BN5'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BN6'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BN11', 'BN12', 'BN13', 'BN14'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BN91', 'BN99'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BN15'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BN99'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BN16', 'BN17'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BN18'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BN43'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BN44'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['GU28'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['GU29'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['PO18', 'PO19'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['PO20'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['PO21', 'PO22'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['RH6'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['RH10', 'RH11'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['RH77'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['RH12', 'RH13'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['RH14'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['RH15'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['RH16', 'RH17'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['RH19'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['RH20'])) {
            return 'West Sussex';
        } elseif (in_array($pc, ['BD1', 'BD2', 'BD3', 'BD4', 'BD5', 'BD6', 'BD7', 'BD8', 'BD9'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['BD10', 'BD11', 'BD12', 'BD13', 'BD14', 'BD15'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['BD98', 'BD99'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['BD16'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['BD97'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['BD17', 'BD18'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['BD98'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['BD19'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['BD20', 'BD21', 'BD22'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['HD1', 'HD2', 'HD3', 'HD4', 'HD5', 'HD7', 'HD8'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['HD6'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['HD9'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['HX1', 'HX2', 'HX3', 'HX4'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['HX1', 'HX5'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['HX6'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['HX7'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['LS1', 'LS2', 'LS3', 'LS4', 'LS5', 'LS6', 'LS7', 'LS8', 'LS9'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['LS10', 'LS11', 'LS12', 'LS13', 'LS14', 'LS15', 'LS16', 'LS17', 'LS18', 'LS19'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['LS20', 'LS25', 'LS26', 'LS27'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['LS88'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['LS98', 'LS99'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['LS21'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['LS22', 'LS23'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['LS28'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['LS29'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF1', 'WF2', 'WF3', 'WF4'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF90'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF5'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF6'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF10'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF7', 'WF8', 'WF9'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF10'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF11'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF12', 'WF13'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF14'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF15', 'WF16'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF16'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['WF17'])) {
            return 'West Yorkshire';
        } elseif (in_array($pc, ['DG8'])) {
            return 'Wigtownshire';
        } elseif (in_array($pc, ['DG9'])) {
            return 'Wigtownshire';
        } elseif (in_array($pc, ['BA12'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['BA13'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['BA14'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['BA15'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN1', 'SN2', 'SN3', 'SN4', 'SN5', 'SN6'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN25', 'SN26'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN38'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN99'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN8'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN9'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN10'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN11'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN12'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN13', 'SN15'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN14', 'SN15'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SN16'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SP1', 'SP2', 'SP3', 'SP4', 'SP5'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['SP9'])) {
            return 'Wiltshire';
        } elseif (in_array($pc, ['B60', 'B61'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['B96', 'B97', 'B98'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['DY10', 'DY11', 'DY14'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['DY12'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['DY13'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['WR1', 'WR2', 'WR3', 'WR4', 'WR5', 'WR6', 'WR7', 'WR8'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['WR78'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['WR99'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['WR9'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['WR10'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['WR11'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['WR11', 'WR12'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['WR13', 'WR14'])) {
            return 'Worcestershire';
        } elseif (in_array($pc, ['WR15'])) {
            return 'Worcestershire';
        }

        return false;
    }
}
