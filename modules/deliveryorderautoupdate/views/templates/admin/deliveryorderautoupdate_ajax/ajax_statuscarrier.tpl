{*
* 2007-2023 PrestaShop
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
*  @author Helloshop <contact@prestashop.com>
*  @copyright  2007-2023 Helloshop
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Helloshop
*}
{assign var=event_code value="_"|explode:$order.result}
<a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$event_code[0]]->color|escape:'htmlall':'UTF-8'}" href="{if isset($order.tracking_url)}{$order.tracking_url|escape:'htmlall':'UTF-8'}{/if}" title="Active" status="1">
	<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$event_code[0]]->id_status|escape:'htmlall':'UTF-8'}.png" />
</a>
<div class="right_shipping">
	{$event_code[1]|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$order.step_date|escape:'htmlall':'UTF-8'} {if $email_sent}<i class="icon-envelope"></i>{/if}</span>
</div>
