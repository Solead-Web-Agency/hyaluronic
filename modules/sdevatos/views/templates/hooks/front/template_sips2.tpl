{**
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
 *}
 
<form class="hidden" method="POST" action="{$url}" id="sdevatos-form">
    <input type="hidden" id="Data" name="Data" value="{$data_exploded.0|replace:'Data=':''}" />
    <input type="hidden" id="InterfaceVersion" name="InterfaceVersion" value="{$data_exploded.1|replace:'InterfaceVersion=':''}" />
    <input type="hidden" id="Seal" name="Seal" value="{$data_exploded.2|replace:'Seal=':''}" />
    <input type="hidden" id="Encode" name="Encode" value="{$data_exploded.3|replace:'Encode=':''}" />
    <input type="hidden" id="SealAlgorithm" name="SealAlgorithm" value="{$data_exploded.4|replace:'SealAlgorithm=':''}" />
</form>
