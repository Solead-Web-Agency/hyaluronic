{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
{if !$emptyVAT}
<a href="{$href|escape:'htmlall':'UTF-8'}" title="{$action|escape:'htmlall':'UTF-8'}">
	<i class="fas fa-user-cog"></i> {l s='Validate VAT with VIES' mod='advancedvatmanager'}
</a>
{/if}