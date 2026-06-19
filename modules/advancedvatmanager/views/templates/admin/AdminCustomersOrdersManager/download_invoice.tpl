{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
{if $invoice_exist}
    <a href="{$href|escape:'htmlall':'UTF-8'}" class="send" title="{$action|escape:'htmlall':'UTF-8'}">
    	<i class="far fa-file-invoice"></i> {l s='Download invoice' mod='advancedvatmanager'}
    </a>
{/if}
