{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{* Load *}
<link rel=stylesheet type="text/css" href="{$bootstrap}">
<link rel=stylesheet type="text/css" href="{$css}">
<script type="text/javascript" src="{$jquery}"></script>
<script type="text/javascript" src="{$js}"></script>
{if isset($controller_url)}
<script type="text/javascript">
	var cap_controller_url = "{$controller_url nofilter}";
</script>
{/if}

{* Template *}
<div class="container">
	<div class="row">
		<a href="{$url}"><img class="col-centered" src="{$logo}"/></a>
	</div>
	<div class="row">
		{if !$wrongToken}
			{if !$bCustomerAlreadyUnsubscribed}
				<h4>{l s='You will no longer receive a cart reminder email on your email address: ' mod='pscartabandonmentpro'}<br/>{$email}</h4>
				<div id="action">
					<a href="{$url}" class="btn btn-primary">{l s='Cancel' mod='pscartabandonmentpro'}</a>
					<button name="confirmUnsubscribe" type="text" class="btn btn-secondary">{l s='Confirm' mod='pscartabandonmentpro'}</button>
					<input type="hidden" value="{$token}" name="tk" id="token" />
					<input type="hidden" value="{$customerid}" name="customer" id="customer"/>
				</div>
				<div id="ajax_return" class="alert"></div>
			{else}
				<h4>{l s='You already no longer receive a cart reminder email on your email address: ' mod='pscartabandonmentpro'}<br/>{$email}</h4>
				<a href="{$url}" class="btn btn-primary">{l s='Return to the shop' mod='pscartabandonmentpro'}</a>
			{/if}
		{else} 
			<h4>{l s='Bad token' mod='pscartabandonmentpro'}</h4>
		{/if}
	</div>
</div>
