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

<table class="cap-email_preview_cart" cellpadding="0" cellspacing="0" width="100%" border="0" style="cellspacing:0;color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;">
    <tr>
        {foreach from=$aProducts item=item key=key}
            <td class="columns mobile-view">
                <div>
                    <img src="{$item.image}" />
                    <h2 class="columns-title" style="text-align:center;margin-bottom:5px"><a href="{$item.link}" style="text-transform: uppercase; text-decoration: none">{$item.name}</a></h2>
                    <p class="columns-description" style="text-align:center;color: #9B9B9B;max-width:145px; margin: 0 auto">{($item.description)|truncate:120|strip_tags}</p>
                    <p class="columns-price" style="text-align:center;margin-top:5px;font-weight:bold">{$item.price}</p>
                </div>
            </td>
        {/foreach}
    </tr>
</table>