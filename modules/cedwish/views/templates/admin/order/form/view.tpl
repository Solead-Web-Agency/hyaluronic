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
 * @package   CedClaroshop
 */
-->
{if $wish_error}
    <div class="alert alert-danger">
        {$wish_error|escape:'htmlall':'UTF-8'}
        <button type="button" class="close" data-dismiss="alert">×</button>
    </div>
{/if}
<div class="panel">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-4">
                <p>{l s='Wish Order Id' mod='cedwish'} : {$marketplace_order_id|escape:'htmlall':'UTF-8'}</p>
            </div>
            <div class="col-md-4">
                <p>{l s='Released At' mod='cedwish'} : {$released_at|escape:'htmlall':'UTF-8'}</p>
            </div>
            <div class="col-md-2">
                <p>{l s='Store Order Id' mod='cedwish'} : {$store_order_id|escape:'htmlall':'UTF-8'}</p>
            </div>
            <div class="col-md-2">
                <span style="border-radius: 10px; padding: 2px;background-color: #b3d4fc">
                    {l s='State' mod='cedwish'} : {$state|escape:'htmlall':'UTF-8'}
                </span>
            </div>
        </div>
    </div>
    <div class="panel-body wish_order_detail_view">
        {if !empty($wish_order)}
        <fieldset>
            <legend>{l s='Order Details' mod='cedwish'}</legend>
            {if isset($wish_order['id'])}
                <div class="row">
                    <div class="col-md-6">
                        <p>{l s='Order ID' mod='cedwish'}</p>
                    </div>
                    <div class="col-md-6">
                        <p>{$wish_order['id']|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </div>
            {/if}
            {if isset($wish_order['transaction_id'])}
                <div class="row">
                    <div class="col-md-6">
                        <p>{l s='Transaction ID' mod='cedwish'}</p>
                    </div>
                    <div class="col-md-6">
                        <p>{$wish_order['transaction_id']|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </div>
            {/if}
            {if isset($wish_order['state'])}
                <div class="row">
                    <div class="col-md-6">
                        <p>{l s='State' mod='cedwish'}</p>
                    </div>
                    <div class="col-md-6">
                        <p>{$wish_order['state']|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </div>
            {/if}
            {if isset($wish_order['fulfillment_requirements']['expected_ship_time'])}
                <div class="row">
                    <div class="col-md-6">
                        <p>{l s='Expected Ship Time' mod='cedwish'}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-danger">
                            {$wish_order['fulfillment_requirements']['expected_ship_time']|escape:'htmlall':'UTF-8'}
                        </p>
                    </div>
                </div>
            {/if}
            {if isset($wish_order['updated_at'])}
                <div class="row">
                    <div class="col-md-6">
                        <p>{l s='Last Updated' mod='cedwish'}</p>
                    </div>
                    <div class="col-md-6">
                        <p>{$wish_order['updated_at']|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </div>
            {/if}
        </fieldset>
        <fieldset>
            <legend>{l s='Product Details' mod='cedwish'}</legend>
            {if !empty($wish_order['product_information'])}
                {foreach $wish_order['product_information'] as $key => $value}
                    <div class="row">
                        <div class="col-md-6">
                            <p>{$key|escape:'htmlall':'UTF-8'}</p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                {if is_array($value)}
                                    <pre>{$value|var_export|escape:'htmlall':'UTF-8'}</pre>
                                {elseif $key=='variation_image_url'}
                                    <img src="{$value|escape:'htmlall':'UTF-8'}" height="50" width="50"/>
                                {else}
                                    {$value|escape:'htmlall':'UTF-8'}
                                {/if}
                            </p>
                        </div>
                    </div>
                {/foreach}
            {/if}
        </fieldset>
        <fieldset>
            <legend>{l s='Shipping Details' mod='cedwish'}</legend>
            {if !empty($wish_order['full_address']['shipping_detail'])}
                {foreach $wish_order['full_address']['shipping_detail'] as $key => $value}
                    <div class="row">
                        <div class="col-md-6">
                            <p>{$key|escape:'htmlall':'UTF-8'}</p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                {if $key=='phone_number'}
                                {$value['number']|escape:'htmlall':'UTF-8'}
                                {elseif is_array($value)}
                                    <pre>{$value|var_export|escape:'htmlall':'UTF-8'}</pre>
                                {else}
                                    {$value|escape:'htmlall':'UTF-8'}
                                {/if}
                            </p>
                        </div>
                    </div>
                {/foreach}
            {/if}
        </fieldset>
        <fieldset>
            <legend>{l s='Tracking Details' mod='cedwish'}</legend>
            {if !empty($wish_order['tracking_information'])}
                {foreach $wish_order['tracking_information'] as $key => $value}
                    <div class="row">
                        <div class="col-md-6">
                            <p>{$key|escape:'htmlall':'UTF-8'}</p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                {if is_array($value)}
                            <pre>{$value|var_export|escape:'htmlall':'UTF-8'}</pre>
                            {else}
                            {$value|escape:'htmlall':'UTF-8'}
                            {/if}
                            </p>
                        </div>
                    </div>
                {/foreach}
            {else}
                <span style="border-radius: 10px;background-color: #cccccc;padding: 1%;">
                    {l s='Not shipped yet' mod='cedwish'}
                </span>
            {/if}
        </fieldset>
        <fieldset>
            <legend>{l s='Fulfillment Records Details' mod='cedwish'}</legend>
            {if !empty($wish_order['fulfillment_records'])}
                {foreach $wish_order['fulfillment_records'] as $fulfillment_records}
                    {foreach $fulfillment_records as $key => $value}
                        <div class="row">
                            <div class="col-md-6">
                                <p>{$key|escape:'htmlall':'UTF-8'}</p>
                            </div>
                            <div class="col-md-6">
                                <p>
                                    {if is_array($value)}
                                <pre>{$value|var_export|escape:'htmlall':'UTF-8'}</pre>
                                {else}
                                {$value|escape:'htmlall':'UTF-8'}
                                {/if}
                                </p>
                            </div>
                        </div>
                    {/foreach}
                {/foreach}
            {/if}
        </fieldset>
        <fieldset>
            <legend>{l s='Order Payment Details' mod='cedwish'}</legend>
            {if !empty($wish_order['order_payment'])}
                {foreach $wish_order['order_payment'] as $fulfillment_records}
                    {foreach $fulfillment_records as $key => $value}
                        <div class="row">
                            <div class="col-md-6">
                                <p>{$key|escape:'htmlall':'UTF-8'}</p>
                            </div>
                            <div class="col-md-6">
                                <p>
                                    {if is_array($value)}
                                        {$value['amount']|escape:'htmlall':'UTF-8'}
                                        &nbsp;{$value['currency_code']|escape:'htmlall':'UTF-8'}
                                {else}
                                {$value|escape:'htmlall':'UTF-8'}
                                {/if}
                                </p>
                            </div>
                        </div>
                    {/foreach}
                {/foreach}
            {/if}
        </fieldset>
        <fieldset>
            <legend>{l s='Warehouse Information' mod='cedwish'}</legend>
            {if !empty($wish_order['warehouse_information'])}
                {foreach $wish_order['warehouse_information'] as $key => $value}
                        <div class="row">
                            <div class="col-md-6">
                                <p>{$key|escape:'htmlall':'UTF-8'}</p>
                            </div>
                            <div class="col-md-6">
                                <p>
                                    {if is_array($value)}
                                <pre>{$value|var_export|escape:'htmlall':'UTF-8'}</pre>
                                {else}
                                {$value|escape:'htmlall':'UTF-8'}
                                {/if}
                                </p>
                            </div>
                        </div>

                {/foreach}
            {/if}
        </fieldset>
        <fieldset>
            <legend>{l s='Refunds' mod='cedwish'}</legend>
            {if !empty($wish_order['refunds'])}
                {foreach $wish_order['refunds'] as $refunds}
                {foreach $refunds as $key => $value}
                        <div class="row">
                            <div class="col-md-6">
                                <p>{$key|escape:'htmlall':'UTF-8'}</p>
                            </div>
                            <div class="col-md-6">
                                <p>
                                    {if is_array($value)}
                                        {$value['amount']|escape:'htmlall':'UTF-8'}
                                        &nbsp;{$value['currency_code']|escape:'htmlall':'UTF-8'}
                                    {else}
                                        {$value|escape:'htmlall':'UTF-8'}
                                    {/if}
                                </p>
                            </div>
                        </div>
                {/foreach}
                {/foreach}
            {/if}
        </fieldset>
        {/if}
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <div class="panel-heading">
                        <i class="icon-file-text"></i> {l s='TRACKING INFO' mod='cedwish'}
                    </div>
                    <div class="panel-body">
                        <div class="form-group row">
                            <label class="control-label required col-lg-3 text-right shipment-heading"
                                   for="shipping_carrier">
                                {l s='Origin Country' mod='cedwish'}
                            </label>
                            <div class="col-lg-9">
                                <select name="origin_country" class="" id="origin_country">
                                    <option value=""></option>
                                    {if isset($shippingCountries) && count($shippingCountries)}
                                        {foreach $shippingCountries as $iso_code => $country}
                                            {if isset($wish_order['tracking_information']['0']['origin_country']) && ($wish_order['tracking_information']['0']['origin_country']==$iso_code)}
                                                <option selected
                                                        value="{$iso_code|escape:'htmlall':'UTF-8'}">{$country|escape:'htmlall':'UTF-8'}</option>
                                            {elseif $country_selected == $iso_code}
                                                <option selected
                                                        value="{$iso_code|escape:'htmlall':'UTF-8'}">{$country|escape:'htmlall':'UTF-8'}</option>
                                            {else}
                                                <option value="{$iso_code|escape:'htmlall':'UTF-8'}">{$country|escape:'htmlall':'UTF-8'}</option>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </select>
                                <p class="help-block">
                                    {l s='Country code of the country the order is being shipped from.' mod='cedwish'}
                                </p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="control-label required col-lg-3 text-right shipment-heading"
                                   for="shipping_carrier">
                                {l s='TRACKING PROVIDER' mod='cedwish'}
                            </label>
                            <div class="col-lg-9">
                                <select name="shipping_provider" class="" id="shipping_provider">
                                    <option value=""></option>
                                    {if isset($shippingCarriers) && count($shippingCarriers)}
                                        {foreach $shippingCarriers as $carrier}
                                            {if isset($wish_order['tracking_information']['0']['shipping_provider']['name']) && ($wish_order['tracking_information']['0']['shipping_provider']['name']==$carrier['name'])}
                                                <option selected="selected"
                                                        value="{$carrier['name']|escape:'htmlall':'UTF-8'}"
                                                >
                                                    {$carrier['name']|escape:'htmlall':'UTF-8'}
                                                </option>
                                            {elseif $carrier_selected == $carrier['name']}
                                                <option selected="selected"
                                                        value="{$carrier['name']|escape:'htmlall':'UTF-8'}"
                                                >
                                                    {$carrier['name']|escape:'htmlall':'UTF-8'}
                                                </option>
                                            {else}
                                                <option value="{$carrier['name']|escape:'htmlall':'UTF-8'}">{$carrier['name']|escape:'htmlall':'UTF-8'}</option>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </select>
                                <p class="help-block">
                                    {l s='The carrier that will be shipping your package to its destination.' mod='cedwish'}
                                </p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="control-label col-lg-3 required text-right shipment-heading"
                                   for="tracking_number">
                                {l s='TRACKING NUMBER' mod='cedwish'}
                            </label>
                            <div class="col-lg-9">
                                {if isset($wish_order['tracking_information']['0']['tracking_number']) && ($wish_order['tracking_information']['0']['tracking_number'])}
                                    <input
                                            type="text"
                                            name="tracking_number"
                                            id="tracking_number"
                                            value="{$wish_order['tracking_information']['0']['tracking_number']|escape:'htmlall':'UTF-8'}"
                                    />
                                {else}
                                    <input type="text" name="tracking_number" id="tracking_number" class="">
                                {/if}
                                <p class="help-block">
                                    {l s='The unique identifier that your carrier provided so that the user can track their package as it is being delivered.
                                     Tracking number should only contain alphanumeric characters with no space between them.' mod='cedwish'}
                                </p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="control-label col-lg-3 text-right shipment-heading" for="ship_note">
                                {l s='SHIP NOTE' mod='cedwish'}
                            </label>
                            <div class="col-lg-9">
                                {if isset($wish_order['tracking_information']['0']['ship_note']) && ($wish_order['tracking_information']['0']['ship_note'])}
                                    <textarea
                                            class=""
                                            rows="4"
                                            id="ship_note"
                                            name="ship_note"
                                    >{$wish_order['tracking_information']['0']['ship_note']|escape:'htmlall':'UTF-8'}</textarea>
                                {else}
                                    <textarea class="" rows="4" id="ship_note" name="ship_note"></textarea>
                                {/if}
                                <p class="help-block">
                                    {l s='A note to yourself when you marked the order as shipped' mod='cedwish'}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button
                                onclick="shipOrder(this, '{$marketplace_order_id|escape:'htmlall':'UTF-8'}')"
                                class="btn-primary btn-lg"
                                {if isset($wish_order['tracking_information']['0']['tracking_number']) && ($wish_order['tracking_information']['0']['tracking_number'])}
                                    disabled="disabled"
                                {/if}
                                type="button"
                        >
                            {l s='Ship' mod='cedwish'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            function shipOrder(button_object, wish_order_id) {
                button_object.disabled = true;
                $.ajax({
                    type: "POST",
                    url: 'ajax-tab.php',
                    data: {
                        ajax: true,
                        controller: 'AdminCedWishOrder',
                        action: 'createShipment',
                        token: "{$wish_order_token|escape:'htmlall':'UTF-8'}",
                        ship_note: $('#ship_note').val(),
                        tracking_number: $('#tracking_number').val(),
                        shipping_provider: $('#shipping_provider').val(),
                        origin_country: $('#origin_country').val(),
                        id_cedwish_order: "{$id_cedwish_order|escape:'htmlall':'UTF-8'}",
                        wish_order_id: wish_order_id,
                    },
                    success: function (response) {
                        response = JSON.parse(response);
                        button_object.disabled = false;
                        if (response.success && response.message) {
                            var success_message = response.message;
                            alert(success_message);
                        } else if (response.message) {
                            alert(response.message);
                        } else {
                            alert("Some error occurred");
                        }
                    },
                    statusCode: {
                        500: function (xhr) {
                            if (window.console) console.log(xhr.responseText);
                        },
                        400: function (response) {
                            $("#progress").append(
                                '<span style="color:Red;">Some error while reimport contact developer</span>'
                            );
                        },
                        404: function (response) {
                            $("#progress").append(
                                '<span style="color:Red;">Some error while reimport contact developer</span>'
                            );
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        if (window.console) console.log(xhr.responseText);
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    },
                });
            }
        </script>
    </div>
</div>
<style>
    .wish_order_detail_view fieldset {
        padding-bottom: 1%;
    }
</style>
