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

{* Date picker *}
<script src="{$datepicker_script}"></script>
<div class="panel panel-default col-lg-4 col-lg-offset-7 col-md-4 col-md-offset-7">
    <div class="panel-body">
        <div class="input-group col-lg-5">
            <span class="input-group-addon">{l s='From' mod='pscartabandonmentpro'}</span>
            <input class="form-group" autocomplete="off" type="text" id="from" name="from" placeholder="{l s='start' mod='pscartabandonmentpro'}">
        </div>
        <div class="input-group col-lg-5 col-lg-offset-2">
            <span class="input-group-addon">{l s='To' mod='pscartabandonmentpro'}</span>
            <input class="form-group" autocomplete="off" type="text" id="to" name="to" placeholder="{l s='now' mod='pscartabandonmentpro'}">
        </div>
    </div>
</div>

{* Global View *}
<div id="view_datas">
    {include file='./datas.tpl'}
</div>