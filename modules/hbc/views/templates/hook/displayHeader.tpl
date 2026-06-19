{*
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA PL MILOSZ MYSZCZUK VATEU: PL9730945634
* @copyright 2010-2024 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* CONTACT WITH DEVELOPER http://mypresta.eu
* support@mypresta.eu
*}

<div class="alert alert-danger">
    {l s='You run module "hide by country" in simulate mode. This mode allows to test the module for various IP addresses.' mod='hbc'}<br />
    {l s='Your simulated IP address is:' mod='hbc'} <strong>{Configuration::get('HBC_SIMULATE_IP')}</strong>. {l s='Geolocation identify this visit as a visit from country:' mod='hbc'}<strong> {$hbc_country}</strong><br/>
    {if isset($hbc_cart_country)}
        {l s='You also activated option to identify origin of customer by its delivery address. Your cart delivery address is' mod='hbc'}: <strong>{$hbc_cart_country}</strong><br/>
        {l s='When origin identificaiton by delivery address will be active - module will hide product if its visibility will be disabled for at least one from these two countries' mod='hbc'}<br/>
    {/if}

    <br/>
    {l s='This message will disappear once you will disable the simulate mode on module configuration page.' mod='hbc'}<br />
</div>