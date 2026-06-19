{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
<h4>{l s='CRON TASK DETAILS' mod='advancedvatmanager'}</h4>
<p>{l s='The url to add to the CRON task server is the following:' mod='advancedvatmanager'}</p>
<br />
{foreach Shop::getshops() as $shop}
    <p><strong>{l s='Shop' mod='advancedvatmanager'} {$shop['name']|escape:'htmlall':'UTF-8'}</strong>
    <br />
    <strong><a href="{Tools::getShopDomainSsl(true)|escape:'htmlall':'UTF-8'}{$module_dir|escape:'htmlall':'UTF-8'}checkcustomervat-cron{if version_compare(_PS_VERSION_, '1.7.0.0', '>=')}17{else}16{/if}.php?token={Tools::substr(Tools::encrypt('advancedvatmanager'), 0, 12)|escape:'htmlall':'UTF-8'}&id_shop={$shop['id_shop']|escape:'htmlall':'UTF-8'}&id_lang={Context::getContext()->language->id|escape:'htmlall':'UTF-8'}" target="_blank">{Tools::getShopDomainSsl(true)|escape:'htmlall':'UTF-8'}{$module_dir|escape:'htmlall':'UTF-8'}checkcustomervat-cron{if version_compare(_PS_VERSION_, '1.7.0.0', '>=')}17{else}16{/if}.php?token={Tools::substr(Tools::encrypt('advancedvatmanager'), 0, 12)|escape:'htmlall':'UTF-8'}&id_shop={$shop['id_shop']|escape:'htmlall':'UTF-8'}&id_lang={Context::getContext()->language->id|escape:'htmlall':'UTF-8'}</a></strong></p>
{/foreach}
<br />
<p>{l s='This CRON task is to check all customer address to validate the VAT number. The system will only analyze the addresses of the clients that meet the conditions configured in the configuration page of this module. Regular execution of this cron job protects the vendor from purchasing with tax application errors when the customer VAT number was previously valid and is now no longer valid.' mod='advancedvatmanager'}</p>
<p>{l s='You should configure a CRON task in your server panel (ask to your hosting server provider) or in "Cron tasks manager" Prestashop module. You should configure the frecuency to execute this CRON task. The CRON task will be execute depends on the options enabled below.' mod='advancedvatmanager'}</p>
<p>{l s='NOTE: It is recommended to run this CRON task during off-peak hours to avoid deteriorating store performance. It is also recommended to run this task once a day at least.' mod='advancedvatmanager'}</p>
<br />
<p><strong>{l s='The Cron task will scan all customer addresses stored in the database from the beginning unless you have selected the [Continue scan process from last address scanned] option.' mod='advancedvatmanager'}</strong></p>
