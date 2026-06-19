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
<a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$status->module_shipping_status_code]->color|escape:'htmlall':'UTF-8'}">
    <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$status->module_shipping_status_code]->id_status|escape:'htmlall':'UTF-8'}.png" />
</a>
<div class="right_shipping">
    {if isset($statuses[$status->module_shipping_status_code]->{$lang})}
        {$statuses[$status->module_shipping_status_code]->{$lang}|escape:'htmlall':'UTF-8'}
    {else}
        {$statuses[$status->module_shipping_status_code]->EN|escape:'htmlall':'UTF-8'}
    {/if}
     <br /> <span class="step_even"> {$status->carrier_shipping_status_date|escape:'htmlall':'UTF-8'} </span>
</div>