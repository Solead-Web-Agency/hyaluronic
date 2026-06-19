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
{assign var=params value=[
    'token' => Configuration::get('SDEVATOS_TOKEN')
]}
<div ng-controller="SdevAtosController"
    ng-init="getJsDefL('{$link->getModuleLink('sdevatos', 'js', $params)|escape:'html':'UTF-8'}')"
    id="module-{$module_name|escape:'htmlall':'UTF-8'}"
>
    {if $has_error}
        <div class="row">
            <div class="col-xs-12">
                <p class="payment_module">
                    {l s='Your order total must be greater than' mod='sdevatos'} {displayPrice price=1} {l s='in order to pay by credit card' mod='sdevatos'}.
                </p>
            </div>
        </div>
    {else}
        {foreach $payment_method_list as $payment_method}
            <div class="row">
                <div class="col-xs-12">
                    <p class="payment_module">
                        <a ng-click="actionPayment('{$payment_method.id_payment_method|intval}', '{$link->getModuleLink('sdevatos', 'displaypayment', $params)|escape:'html':'UTF-8'}')" href="javascript:void(0)" {if !$ps15}class="atos"{/if}>
                            {if $ps15}<img src="https://hyaluronicfillermarket.com/modules/payplug/views/img/logos_schemes_fr.svg" width="86" height="86" />{/if}
                            {l s='Pay by' mod='sdevatos'} {$payment_method.name|escape:'htmlall':'UTF-8'}
                        </a>
                    </p>
                </div>
            </div>
        {/foreach}

        <a id="sdevatos-response-link" href="#sdevatos-response">&nbsp;</a>
        <div id="sdevatos-response"></div>
    {/if}
</div>

<!-- Angular module. -->
<script>angular.bootstrap(document, ['SdevAtosApp'])</script>
