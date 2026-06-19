{**
* Price increment/discount by customer groups
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*}

<style type="text/css">
	.nobootstrap {
		min-width: 0 !important;
		padding: 100px 30px 0 !important;
	}
	.nobootstrap .margin-form {
		font-size: 0.9em !important;
	}

	.company {
		border: 1px solid black;
		background-color: #2A2A2A;
		color: #FFF;
		overflow: hidden;
		padding: 20px;
		margin: 15px 0;
	}
	.company a{
		color: white;
		font-weight: bold;
	}
	.company ul {
		margin: 6px 0 12px;
		padding-left: 40px;
		list-style-type: disc;
	}
	.company ul li {
		color: #FFF;
	}
	.company .logo {
		padding-bottom: 10px;
	}
</style>

<h2>{$displayName|escape:'htmlall':'UTF-8'} - {l s='Configuration' mod='groupinc'}</h2>

<div class="company">
	<div class="logo">
		<img src="{$gi_path|escape:'htmlall':'UTF-8'}img/logo_idnovate.png" title="idnovate.com" alt="idnovate.com" />
	</div>
	<div class="content">
		{l s='We offer you free assistance to install and set up the module. If you have any problem you can:' mod='groupinc'}
		<ul>
			<li>{l s='Contact us through or website' mod='groupinc'} <a target="_blank" href="http://www.idnovate.com" title="www.idnovate.com">www.idnovate.com</a></li>
			<li>{l s='Send us an email to' mod='groupinc'} <a href="mailto:info@idnovate.com" title="{l s='Contact idnovate.com' mod='groupinc'}">info@idnovate.com</a></li>
	</div>
</div>

{if isset($errors) && $errors|@count > 0}
<div class="bootstrap">
	<div class="module_confirmation alert error alert-warning">
		<strong>{l s='There are errors:' mod='groupinc'}</strong>
		<ol>
			{foreach from=$errors item=error}
				<li>{$error|escape:'htmlall':'UTF-8'}</li>
			{/foreach}
		</ol>
	</div>
</div>
{/if}
{if isset($success) && $success}
	<div class="bootstrap">
		<div class="module_confirmation conf confirm alert alert-success">{l s='Settings updated' mod='groupinc'}</div>
	</div>
{/if}

<div style="clear: both"></div>

<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" id="groupinc">
	<fieldset>
		<legend>
			<img src="../img/admin/edit.gif" />
			{l s='Module configuration' mod='groupinc'}
		</legend>

		{foreach from=$groups item=group}
		<label>{$group.name|escape:'htmlall':'UTF-8'}</label>
		<div class="margin-form">
			<input type="field" name="reduction[{$group.id_group|escape:'htmlall':'UTF-8'}]" value="{if isset($group_values[$group.id_group])}{$group_values[$group.id_group]|escape:'htmlall':'UTF-8'}{else}0{/if}" size=5 /> %
		</div>
		{/foreach}
		<div class="margin-form">
		<span>{l s='Define a positive value to set an increment' mod='groupinc'}</span><br />
		<span>{l s='Define a negative value to set a discount' mod='groupinc'}</span>
		</div>
	</fieldset>

	<div style="clear: both;"></div>
	<br />

	<center>
		<input type="submit" name="submitForm" value="{l s='Update settings' mod='groupinc'}" class="button" />
	</center>
	<hr />
</form>