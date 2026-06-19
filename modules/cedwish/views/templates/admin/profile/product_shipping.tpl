<!--
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */
-->


<div class="row">
    <div class="panel">
        <div class="panel-heading">
            <label>
                {l s="Update Default Shipping" mod="cedwish"} -
                <span style="color:red;"> {l s='Required' mod='cedwish'}</span>
            </label>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                {if !empty($warehouses)}
                    <table class="table">
                        {foreach $warehouses as $key => $warehouse}
                            <tr>
                                <td>
                                    <input
                                            name="default_shipping_prices[{$warehouse['id']|escape:'htmlall':'UTF-8'}][warehouse_id]"
                                            readonly="readonly"
                                            type="hidden"
                                            value="{$warehouse['id']|escape:'htmlall':'UTF-8'}"
                                    />
                                    <input
                                            readonly="readonly"
                                            type="text"
                                            class="required"
                                            value="{$warehouse['name']|escape:'htmlall':'UTF-8'}"
                                    />
                                </td>
                                <td>
                                    {if isset($profile_data['default_shipping_prices'][$warehouse['id']]['default_shipping_price']['amount'])}
                                        <input
                                                name="default_shipping_prices[{$warehouse['id']|escape:'htmlall':'UTF-8'}][default_shipping_price][amount]"
                                                type="text"
                                                class="required"
                                                value="{$profile_data['default_shipping_prices'][$warehouse['id']]['default_shipping_price']['amount']|escape:'htmlall':'UTF-8'}"
                                        />
                                    {else}
                                        <input
                                                name="default_shipping_prices[{$warehouse['id']|escape:'htmlall':'UTF-8'}][default_shipping_price][amount]"
                                                type="text"
                                                class="required"
                                                value="{$default_shipping_amount|escape:'htmlall':'UTF-8'}"
                                        />
                                    {/if}
                                </td>
                                <td>
                                    <input
                                            name="default_shipping_prices[{$warehouse['id']|escape:'htmlall':'UTF-8'}][default_shipping_price][currency_code]"
                                            readonly="readonly"
                                            type="text"
                                            value="{$wish_currency|escape:'htmlall':'UTF-8'}"
                                    />
                                </td>
                            </tr>
                        {/foreach}
                    </table>
                {/if}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="panel">
        <div class="panel-heading">
            <label>{l s='Warehouse To Shipping' mod='cedwish'}</label>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                {if !empty($warehouses)}
                    {foreach $warehouses as $warehouse}
                        <fieldset>
                            <legend>
                                {l s='For Warehouse' mod='cedwish'} :
                                <b>
                                    {$warehouse['name']|escape:'htmlall':'UTF-8'}
                                </b>
                            </legend>
                        </fieldset>
                        <table
                                class="table table-bordered"
                                id="wishShippingPrice{$warehouse['id']|escape:'htmlall':'UTF-8'}"
                        >
                            <thead>
                                <tr class="headings">
                                    <th>{l s='Country' mod='cedwish'}</th>
                                    <th>{l s='Price' mod='cedwish'}</th>
                                    <th>{l s='Wish Currency' mod='cedwish'}</th>
                                    <th>{l s='Max Delivery Days' mod='cedwish'}</th>
                                    <th>{l s='Is Enabled' mod='cedwish'}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {if isset($profile_data)
                                && isset($profile_data['warehouse_to_shippings'][$warehouse['id']]['shipping_details'])
                                && count($profile_data['warehouse_to_shippings'][$warehouse['id']]['shipping_details'])
                                }
                                    {foreach $profile_data['warehouse_to_shippings'][$warehouse['id']]['shipping_details'] as $key => $shipPrice}
                                        <tr>
                                            <td>
                                                <select name="warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][shipping_details][{$key|escape:'htmlall':'UTF-8'}][destination]">
                                                    {foreach $shippableCountries as $code => $shipcountry}
                                                        <option value="{$code|escape:'htmlall':'UTF-8'}"
                                                                {if $shipPrice['destination'] == $code}
                                                                    selected="selected"
                                                                {/if}
                                                        >{$shipcountry|escape:'htmlall':'UTF-8'}</option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td>
                                                <input class="input-text" type="text"
                                                        {if isset($shipPrice) && isset($shipPrice['price']['amount'])}
                                                            onkeypress='numberValidate(event, this)'
                                                            placeholder="Price"
                                                            value="{$shipPrice['price']['amount']|escape:'htmlall':'UTF-8'}"
                                                        {else}
                                                            value=""
                                                        {/if}
                                                       name="warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][shipping_details][{$key|escape:'htmlall':'UTF-8'}][price][amount]">
                                            </td>
                                            <td>
                                                <input
                                                        class='input-text shipping_price'
                                                        type='text'
                                                        readonly='readonly'
                                                        value='{$shipPrice['price']['currency_code']|escape:'htmlall':'UTF-8'}'
                                                        name='warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][shipping_details][{$key|escape:'htmlall':'UTF-8'}][price][currency_code]'
                                                />
                                            </td>
                                            <td>
                                                <input
                                                        class='input-text shipping_price'
                                                        type='text'
                                                        value='{$shipPrice['max_delivery_days']|escape:'htmlall':'UTF-8'}'
                                                        name='warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][shipping_details][{$key|escape:'htmlall':'UTF-8'}][max_delivery_days]'
                                                />
                                            </td>
                                            <td>
                                                <select name='warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][shipping_details][{$key|escape:'htmlall':'UTF-8'}][is_enabled]'>
                                                    {if $shipPrice['is_enabled']}
                                                        <option selected="selected" value='true'>Enabled</option>
                                                        <option value='false'>Disabled</option>
                                                    {else}
                                                        <option value='true'>Enabled</option>
                                                        <option selected="selected" value='false'>Disabled</option>
                                                    {/if}
                                                    </select>
                                            </td>
                                            <td>
                                                <button class='btn btn-danger' onclick='deleteRow(this)'>
                                                    <i class='icon-eraser'></i> <span> Delete</span>
                                                </button>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-left">
                                        <button id="addToEndBtn" class="btn btn-primary btn-sm" type="button" onclick=
                                        "addNewShippingPriceRow('{$warehouse['id']|escape:'htmlall':'UTF-8'}')" style="">
                                            <i class="icon-plus-sign-alt"></i>
                                            <span>{l s='Add Shipping' mod='cedwish'}</span>
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    <div class="panel" style="margin-top: 1%">
                        <div class="panel-heading">
                            <label>{l s='Shipping Overrides' mod='cedwish'}</label>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                            <table class="table table-bordered" id="wishShippingOverride{$warehouse['id']|escape:'htmlall':'UTF-8'}">
                                <thead>
                                    <tr class="headings">
                                        <th>{l s='Region' mod='cedwish'}</th>
                                        <th>{l s='Price' mod='cedwish'}</th>
                                        <th>{l s='Wish Currency' mod='cedwish'}</th>
                                        <th>{l s='Max Delivery Days' mod='cedwish'}</th>
                                        <th>{l s='Is Enabled' mod='cedwish'}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if isset($profile_data)
                                    && isset($profile_data['warehouse_to_shippings'][$warehouse['id']]['overrides'])
                                    && count($profile_data['warehouse_to_shippings'][$warehouse['id']]['overrides'])
                                    }
                                        {foreach $profile_data['warehouse_to_shippings'][$warehouse['id']]['overrides'] as $key => $shipPrice}
                                            <tr>
                                                <td>
                                                    <select name="warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][overrides][{$key|escape:'htmlall':'UTF-8'}][destination]">
                                                        {foreach $shippableRegions as $iso_code => $shipRegions}
                                                            <optgroup label="{$shippableCountries[$iso_code]|escape:'htmlall':'UTF-8'}">
                                                            {foreach $shipRegions as $shipRegion}
                                                            <option value="{$shipRegion['0']|escape:'htmlall':'UTF-8'}"
                                                                    {if $shipRegion['0'] == $shipPrice['destination']}
                                                                        selected="selected"
                                                                    {/if}
                                                            >
                                                                {$shipRegion['1']|escape:'htmlall':'UTF-8'}
                                                                - ({$shipRegion[2]|escape:'javascript':'UTF-8'})
                                                            </option>
                                                            {/foreach}
                                                            </optgroup>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                                <td>
                                                    <input class="input-text" type="text"
                                                            {if isset($shipPrice) && isset($shipPrice['price']['amount'])}
                                                                onkeypress='numberValidate(event, this)'
                                                                placeholder="Price"
                                                                value="{$shipPrice['price']['amount']|escape:'htmlall':'UTF-8'}"
                                                            {else}
                                                                value=""
                                                            {/if}
                                                           name="warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][overrides][{$key|escape:'htmlall':'UTF-8'}][price][amount]">
                                                </td>
                                                <td>
                                                    <input
                                                            class='input-text shipping_price'
                                                            type='text'
                                                            readonly='readonly'
                                                            value='{$shipPrice['price']['currency_code']|escape:'htmlall':'UTF-8'}'
                                                            name='warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][overrides][{$key|escape:'htmlall':'UTF-8'}][price][currency_code]'
                                                    />
                                                </td>
                                                <td>
                                                    <input
                                                            class='input-text shipping_price'
                                                            type='text'
                                                            value='{$shipPrice['max_delivery_days']|escape:'htmlall':'UTF-8'}'
                                                            name='warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][overrides][{$key|escape:'htmlall':'UTF-8'}][max_delivery_days]'
                                                    />
                                                </td>
                                                <td>
                                                    <select name='warehouse_to_shippings[{$warehouse['id']|escape:'htmlall':'UTF-8'}][overrides][{$key|escape:'htmlall':'UTF-8'}][is_enabled]'>
                                                        {if $shipPrice['is_enabled']}
                                                            <option selected="selected" value='true'>Enabled</option>
                                                            <option value='false'>Disabled</option>
                                                        {else}
                                                            <option value='true'>Enabled</option>
                                                            <option selected="selected" value='false'>Disabled</option>
                                                        {/if}
                                                    </select>
                                                <td>
                                                    <button class='btn btn-danger' onclick='deleteRow(this)'>
                                                        <i class='icon-eraser'></i> <span> Delete</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    {/if}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="text-left">
                                            <button
                                                    class="btn btn-primary btn-sm"
                                                    type="button"
                                                    onclick="addNewShippingOverrideRow('{$warehouse['id']|escape:'htmlall':'UTF-8'}')"
                                            >
                                                <i class="icon-plus-sign-alt"></i>
                                                <span>{l s='Add Override' mod='cedwish'}</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            </div>
                        </div>
                    </div>
                    {/foreach}
                {/if}
            </div>
        </div>
    </div>
</div>
<script>
    var wish_localised_currency = '{$wish_currency|escape:'htmlall':'UTF-8'}';
    function addNewShippingPriceRow(warehouse) {
        var next_row = $("#wishShippingPrice"+warehouse+" tbody").find("tr").length + 1;
        var new_row = "";
        new_row += "<tr id='"+next_row+"'>" +
            "<td>" +
            "<select class='country_destinations' name='warehouse_to_shippings["+warehouse+"][shipping_details]["+next_row+"][destination]'>";
        {foreach $shippableCountries as $code => $shipcountry}
        new_row+= "<option value='{$code|escape:'javascript':'UTF-8'}'>{$shipcountry|escape:'javascript':'UTF-8'}</option>";
        {/foreach}
        new_row+= "</select>" +
            "</td>" +
            "<td>" +
            "<input onkeypress='numberValidate(event, this)' placeholder='Price' class='input-text shipping_price' type='text'"+
            "value='' name='warehouse_to_shippings["+warehouse+"][shipping_details]["+next_row+"][price][amount]'>" +
            "</td>" +
            "<td>" +
            "<input class='input-text shipping_price' type='text' readonly='readonly' "+
            "value='"+wish_localised_currency+"' name='warehouse_to_shippings["+warehouse+"][shipping_details]["+next_row+"][price][currency_code]'>" +
            "</td>" +
            "<td>" +
            "<input onkeypress='numberValidate(event, this)' placeholder='Max Delivery Days' class='input-text shipping_price' type='text'"+
            "value='' name='warehouse_to_shippings["+warehouse+"][shipping_details]["+next_row+"][max_delivery_days]'>" +
            "</td>" +
            "<td>" +
            "<select name='warehouse_to_shippings["+warehouse+"][shipping_details]["+next_row+"][is_enabled]'>";
                new_row+= "<option value='true'>Enabled</option>";
                new_row+= "<option value='true'>Disabled</option>";
                new_row+= "</select>" +
            "<td>";
        new_row += "<button class='btn btn-danger' onclick='deleteRow(this)'>";
        new_row += "<span><i class='icon-eraser'></i> Delete</span></button></td></tr>";
        $("#wishShippingPrice"+warehouse+" tbody").append(
            new_row);
    }

    function addNewShippingOverrideRow(warehouse) {
        var override_next_row = $("#wishShippingOverride"+warehouse+" tbody").find("tr").length + 1;
        var new_row = "";
        new_row += "<tr id='"+override_next_row+"'>" +
            "<td>" +
            "<select name='warehouse_to_shippings["+warehouse+"][overrides]["+override_next_row+"][destination]'>";
        {foreach $shippableRegions as $iso_code => $ship_regions}
        new_row+="<optgroup label='{$shippableCountries[$iso_code]|escape:'htmlall':'UTF-8'}'>";
        {foreach $ship_regions as $ship_region}
        new_row+= "<option value='{$ship_region[0]|escape:'javascript':'UTF-8'}'>"
        new_row+= "{$ship_region[1]|escape:'javascript':'UTF-8'} - ({$ship_region[2]|escape:'javascript':'UTF-8'})";
        new_row+= "</option>";
        {/foreach}
        new_row+= "</optgroup>";
        {/foreach}
        new_row+= "</select>" +
            "</td>" +
            "<td>" +
            "<input onkeypress='numberValidate(event, this)' placeholder='Price' class='input-text shipping_price' type='text'"+
            "value='' name='warehouse_to_shippings["+warehouse+"][overrides]["+override_next_row+"][price][amount]'>" +
            "</td>" +
            "<td>" +
            "<input class='input-text shipping_price' type='text' readonly='readonly' "+
            "value='"+wish_localised_currency+"' name='warehouse_to_shippings["+warehouse+"][overrides]["+override_next_row+"][price][currency_code]'>" +
            "</td>" +
            "<td>" +
            "<input onkeypress='numberValidate(event, this)' placeholder='Max Delivery Days' class='input-text shipping_price' type='text'"+
            "value='' name='warehouse_to_shippings["+warehouse+"][overrides]["+override_next_row+"][max_delivery_days]'>" +
            "</td>" +
            "<td>" +
            "<select name='warehouse_to_shippings["+warehouse+"][overrides]["+override_next_row+"][is_enabled]'>";
                new_row+= "<option value='true'>Enabled</option>";
                new_row+= "<option value='true'>Disabled</option>";
                new_row+= "</select>" +
            "<td>";
        new_row += "<button class='btn btn-danger' onclick='deleteRow(this)'>";
        new_row += "<span><i class='icon-eraser'></i> Delete</span></button></td></tr>";
        $("#wishShippingOverride"+warehouse+" tbody").append(
            new_row);
    }

    function deleteRow(obj)
    {
        $(obj).closest("tr").remove();
    }
    function numberValidate(event, el) {
        console.log(el);
        var attr = el.placeholder;
        const charCode = (event.which) ? event.which : event.keyCode;
        if (charCode !== 46 && charCode!== 8 && charCode!==8 && (charCode < 48 || charCode > 57)) {
            event.preventDefault();
            return false;
        }
    }
    function closeMessage(){
        $("#error-message").hide();
    }
</script>
