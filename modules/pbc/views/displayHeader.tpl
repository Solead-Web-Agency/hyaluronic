{*
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
* @copyright 2010-2021 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* CONTACT WITH DEVELOPER http://mypresta.eu
* support@mypresta.eu
*}

<div class="alert alert-danger">
    {l s='You run module "price by country" in simulate mode. This mode allows to test the module for various IP addresses.' mod='pbc'}<br/>
    {if isset($pbc_cart_country) && Configuration::get('PBC_DELIVERY') == 1}
        {l s='You activated option to identify origin of customer by its delivery address. Your cart delivery address is' mod='pbc'}:
        <strong>{$pbc_cart_country}</strong>
        <br/>
        {l s='When origin identificaiton by delivery address will be active - module will alter price of product if delivery address will match to defined conditions.' mod='pbc'}
        <br/>
    {else}
        {l s='Your simulated IP address is:' mod='pbc'}
        <strong>{Configuration::get('PBC_SIMULATE_IP')}</strong>
        . {l s='Geolocation identify this visit as a visit from country:' mod='pbc'}<strong> {$pbc_country}</strong>
        <br/>
    {/if}
    <br/>
    {l s='This message will disappear once you will disable the simulate mode on module configuration page.' mod='pbc'}<br/>
</div>