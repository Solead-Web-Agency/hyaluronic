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

<tr style="border-bottom: 1px solid">
    <td style="padding: 5px">{$order.id_order|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 5px; max-width: 200px">{$order.id_carrier|escape:'htmlall':'UTF-8'}_{$order.carrier_name|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 5px">{$order.carrier|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 5px">{$order.tracking_number|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 5px">
        <table style="max-width: 200px">
            <tr>
                <td><a target="_blank" style="padding: 6px;background: #ec0000;width: 18px;display: inline-block;height: 18px;max-height:18px;margin-right:5px;border-radius: 3px;box-sizing:content-box;background: {$statuses[$status->module_shipping_status_code]->color|escape:'htmlall':'UTF-8'}" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}">
                    <img width="18" height="18" src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$status->module_shipping_status_code]->id_status|escape:'htmlall':'UTF-8'}.png" />
                </a></td>
                <td>
                    <div><strong>
                    {if isset($statuses[$status->module_shipping_status_code]->{$iso_code})}
                    {$statuses[$status->module_shipping_status_code]->{$iso_code}|escape:'htmlall':'UTF-8'}
                    {else}
                    {$statuses[$status->module_shipping_status_code]->EN|escape:'htmlall':'UTF-8'}
                    {/if}
                    </strong></div>
                    <div>{$status->carrier_shipping_status_date|escape:'htmlall':'UTF-8'}</div>
                </td>
            </tr>
        </table>
    </td>
</tr>