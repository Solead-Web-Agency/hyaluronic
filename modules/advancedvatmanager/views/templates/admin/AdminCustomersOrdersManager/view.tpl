{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
<fieldset>
    <div class="panel udo_view_logs">
        <div class="panel-heading">
            <i class="fal fa-shopping-cart"></i> {l s='Order details' mod='advancedvatmanager'} #{$advancedvatmanager->id|escape:'htmlall':'UTF-8'}
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-2">{l s='ID' mod='advancedvatmanager'}</label>
            <div class="col-lg-9">{$advancedvatmanager->id|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-2">{l s='ID Customer' mod='advancedvatmanager'}</label>
            <div class="col-lg-9">{$advancedvatmanager->id_customer|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-2">{l s='ID Order' mod='advancedvatmanager'}</label>
            <div class="col-lg-9">{$advancedvatmanager->id_order|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-2">{l s='Shop' mod='advancedvatmanager'}</label>
            <div class="col-lg-9">{$shop|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-2">{l s='Tax exempt' mod='advancedvatmanager'}</label>
            <div class="col-lg-9">{if $advancedvatmanager->notax}{l s='Yes' mod='advancedvatmanager'}{else}{l s='No' mod='advancedvatmanager'}{/if}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-2">{l s='Brexit' mod='advancedvatmanager'}</label>
            <div class="col-lg-9">{if $advancedvatmanager->brexit}{l s='Yes' mod='advancedvatmanager'}{else}{l s='No' mod='advancedvatmanager'}{/if}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-2">{l s='Invoice number' mod='advancedvatmanager'}</label>
            <div class="col-lg-9">{$advancedvatmanager->invoice|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-2">{l s='Date updated' mod='advancedvatmanager'}</label>
            <div class="col-lg-9">{$advancedvatmanager->date_upd|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-2">{l s='Date added' mod='advancedvatmanager'}</label>
            <div class="col-lg-9">{$advancedvatmanager->date_add|escape:'htmlall':'UTF-8'}</div>
        </div>
    </div>
</fieldset>
