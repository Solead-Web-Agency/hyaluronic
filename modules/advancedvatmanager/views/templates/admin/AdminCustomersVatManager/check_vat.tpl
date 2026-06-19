{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}

{if $vat_details !== false && !$emptyVAT}
    <input class="hidden" name="country_iso_{$id|escape:'htmlall':'UTF-8'}"  value="{$vat_details.country_iso|escape:'htmlall':'UTF-8'}" />
    <input class="hidden" name="customer_{$id|escape:'htmlall':'UTF-8'}"  value="{$vat_details.customer|escape:'htmlall':'UTF-8'}" />
    <input class="hidden" name="vat_number_{$id|escape:'htmlall':'UTF-8'}"  value="{$vat_details.vat_number|escape:'htmlall':'UTF-8'}" />
    <a class="checkCustomerVAT" class="checkCustomerVAT" href="#" title="{$action|escape:'htmlall':'UTF-8'}">
    	<i class="fas fa-user"></i> {$action|escape:'htmlall':'UTF-8'}
    </a>
{/if}
