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
{*
<span class="label color_field " style="{if $order.current_state_color}background-color:{$order.current_state_color|escape:'htmlall':'UTF-8'};{else}font-size:12px;{/if}">
	{$order.current_state|escape:'htmlall':'UTF-8'}
</span>
*}
{if $server_success == 0}
	<a target="_blank" class="list-action-enable action-disabled" href="#" title="Disable" status="0" >
		<i class="icon-remove"></i>
	</a>
{else}
	<a target="_blank" class="list-action-enable action-enabled" href="#" title="Active">
		<i class="icon-check"></i>
	</a>
{/if}