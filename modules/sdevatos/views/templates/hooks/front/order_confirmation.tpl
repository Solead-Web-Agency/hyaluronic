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

{if $status == 'ok'}
    <p>
        {l s='Your order on' mod='sdevatos'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='is complete' mod='sdevatos'}.<br /><br />

        <span class="bold">{l s='Your order will be sent as soon as possible' mod='sdevatos'}.</span><br /><br />

        {l s='For any questions or for further information, please contact our' mod='sdevatos'}
        <a href="{$contact_link|escape:'htmlall':'UTF-8'}">{l s='customer support' mod='sdevatos'}</a>.
    </p>
{else}
    <p class="warning">
        {l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='sdevatos'}
        <a href="{$contact_link|escape:'htmlall':'UTF-8'}">{l s='customer support' mod='sdevatos'}</a>.
    </p>
{/if}
