<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevAtos
 * Support by mail : contact@scaledev.fr
 */

use ScaleDEV\SdevAtos\SdevModule;

require_once(dirname(__FILE__).'/functions.php');

// START COMMON CLASSES FOR ALL SCALEDEV MODULES.
require_once(dirname(__FILE__).'/classes/SdevModule.php');
require_once(dirname(__FILE__).'/classes/SdevTools.php');
require_once(dirname(__FILE__).'/classes/SdevDbTools.php');
require_once(dirname(__FILE__).'/classes/SdevDate.php');
require_once(dirname(__FILE__).'/classes/SdevConfiguration.php');
// END COMMON CLASSES FOR ALL SCALEDEV MODULES.

// START VENDOR
require_once(dirname(__FILE__).'/vendor/helperform/autoload.php');
// END VENDOR

// START CLASSES
require_once(dirname(__FILE__).'/classes/'.SdevModule::NAME.'Module.php');
require_once(dirname(__FILE__).'/classes/'.SdevModule::NAME.'Ws.php');
require_once(dirname(__FILE__).'/classes/'.SdevModule::NAME.'ObjectModel.php');
require_once(dirname(__FILE__).'/classes/'.SdevModule::NAME.'Model.php');
require_once(dirname(__FILE__).'/classes/'.SdevModule::NAME.'Form.php');
// END CLASSES

// START MODELS
require_once(dirname(__FILE__).'/models/'.SdevModule::NAME.'Contract.php');
require_once(dirname(__FILE__).'/models/'.SdevModule::NAME.'PaymentMethod.php');
// END MODELS
