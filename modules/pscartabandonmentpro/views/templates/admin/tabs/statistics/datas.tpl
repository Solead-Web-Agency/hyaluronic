{*
* 2007-2019 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}

{* Global View *}
<div class="panel panel-default col-lg-10 col-lg-offset-1 col-md-12 col-md-offset-0">
    <div class="panel-heading">
        <i class="material-icons">remove_red_eye</i>{l s='Global view' mod='pscartabandonmentpro'}
    </div>
    {include file='./elements/global_view.tpl'}
</div>

{* Stats by reminder *}
{assign var="i" value="0"}
{foreach from=$generalStatsList item=item key=key}
{assign var="i" value="{$i + 1}"}
<div class="panel panel-default col-lg-10 col-lg-offset-1 col-md-12 col-md-offset-0">
    <div class="panel-heading">
        <i class="material-icons">email</i>{l s='Reminder' mod='pscartabandonmentpro'} {$i} ({$item.cart_frequency_number} {$item.cart_frequency_type})
    </div>
    {include file='./elements/range_datas.tpl' data=$item}
</div>
{/foreach}