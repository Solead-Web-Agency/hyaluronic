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

<link rel=stylesheet type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

{if !empty($sendList)}
	{foreach from=$sendList item=customers key=key}
	<div class="container col-lg-12">
		<h3><b>Reminder #{$key} : </b>{$customers|count} mails have been sent.</h3>
		<table class="table table-striped">
			<tr>
				<th>ID CUSTOMER</th>
				<th>ID CART</th>
				<th>FIRSTNAME</th>
				<th>LASTNAME</th>
				<th>EMAIL</th>
			</tr>
			{foreach from=$customers item=item key=key2}
			<tr>
				<td>{$item.id_customer}</td>
				<td>{$item.id_cart}</td>
				<td>{$item.firstname}</td>
				<td>{$item.lastname}</td>
				<td>{$item.email}</td>
			</tr>
			{/foreach}
		</table>
	</div>
	{/foreach}
{else}
	<div class="container col-lg-12">
		<h3 style="text-align:center;">{l s='No email was sent.' mod='pscartabandonmentpro'}</h3>
		<h4 style="text-align:center;">{l s='You may check the shop Logs in your backoffice.' mod='pscartabandonmentpro'}</h4>
	</div>
{/if}

{* custom CSS *}
<style>
	.container{
		margin-top: 25px;
	}
</style>