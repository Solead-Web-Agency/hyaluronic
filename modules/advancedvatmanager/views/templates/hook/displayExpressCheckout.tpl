{**
 * 2017-2022 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2022 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}

<div style="margin-top: 15px;" class="alert alert-info">
    {if Configuration::get('ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS') == 1}
        {l s='Purchases over %s are not allowed' sprintf=[$currency_amount] mod='advancedvatmanager'}
    {else if Configuration::get('ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS') == 2}
        {l s='Purchases below %s are not allowed' sprintf=[$currency_amount] mod='advancedvatmanager'}
    {/if}
</div>