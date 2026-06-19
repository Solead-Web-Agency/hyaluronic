{**
 * 2007-2020 PrestaShop
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
 * @author    DPD France S.A.S. <support.ecommerce@dpd.fr>
 * @copyright 2020 DPD France S.A.S.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}

<script type="text/javascript">
{literal}

var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();

function dpdfrance_relais_ajaxupdate(address, zipcode, city, action, dpdfrance_cart_id)
{
    if ((zipcode && zipcode.length >= 5 && action == 'search') || action == 'reset') {
        $('#dpdfrance_reset_submit').after(' <img src="{/literal}{$dpdfrance_base_dir|escape:'htmlall':'UTF-8'}{literal}/views/img/front/relais/loader.gif"/>');
        $.ajax('{/literal}{$dpdfrance_base_dir|escape:'htmlall':'UTF-8'}{literal}/ajax.php?action_ajax_dpdfrance=ajaxUpdatePoints', {
            data: {
                'address': address,
                'zipcode': zipcode,
                'city': city,
                'action': action,
                'dpdfrance_cart_id': dpdfrance_cart_id,
                'dpdfrance_token': dpdfrance_token,
            },
            success: function(data) {
                var $html = $(data);
                $('#dpdfrance_relais_point_table').html($html);
                $('#dpdfrance_relais_point_table').children('#dpdfrance_relais_point_table').css('display', '');
                dpdfrance_checkPudo();
            }
        });
    } else {
        $('#dpdfrance_search_zipcode').css('border', '1px solid #dc0032');
    }
}

$(document).ready(function(){
    setTimeout(function () {
        if ($('#input_dpdfrance_predict_gsm_dest:-webkit-autofill').val()) {
            dpdfrance_checkGSM();
        }
    }, 25);
    $("#input_dpdfrance_predict_gsm_dest").keyup(function() {
        delay(function(){
            dpdfrance_checkGSM();
        }, 200);
    });
    if ($("#div_dpdfrance_predict_block").is(":visible")) {
        dpdfrance_checkGSM();
    }
});
{/literal}
</script>

<noscript>
    <tr>
        <td colspan="5"><div class="dpdfrance_relais_error"><strong>{l s='It seems that your browser doesn\'t allow Javascript execution, therefore DPD Relais is not available. Please change browser settings, or try another browser.' mod='dpdfrance'}</strong></div></td>
    </tr><br/>
    <div style="display:none;">
</noscript>

<div id="dpdfrance_relais_filter" onclick="
var i = 1;
for (i=1; i<6; i++){
    document.getElementById('dpdfrance_relais_filter').style.display='none';
    document.getElementById('dpdfrance_relaydetail'+i).style.display='none';
}">
</div>

<table align="center" id="dpdfrance_relais_point_table" class="dpdfrance_fo" style="display:none;">
{if isset($error)}
    <tr>
        <td colspan="5"><div class="dpdfrance_relais_error"> {$error|escape:'htmlall':'UTF-8'} </div></td>
    </tr>
{else}
    {if $dpdfrance_relais_status == 'error'}
        <tr>
            <td colspan="5" style="padding:0px;"><div class="dpdfrance_relais_error"><p>{l s='It seems that you haven\'t selected a DPD Pickup point, please pick one from this list' mod='dpdfrance'}</p></div></td>
        </tr>
    {/if}

    <tr>
        <td colspan="5" style="padding:0px;">
            <div id="dpdfrance_div_relais_header"><p>{l s='Please select your DPD Relais parcelshop among this list' mod='dpdfrance'}</p></div>
            {if $ssl == 0 || $ssl_everywhere == 1}
                <div id="dpdfrance_div_relais_srch_link"><span onMouseOver="javascript:this.style.cursor='pointer';javascript:this.style.textDecoration='underline';" onMouseOut="javascript:this.style.cursor='auto';javascript:this.style.textDecoration='none';" onClick="$('#dpdfrance_div_relais_srch_panel').slideToggle();">{l s='Search for Pickup points near another address' mod='dpdfrance'}</span>
                <div id="dpdfrance_div_relais_srch_panel" style="display:none;">
                    <input type="text" id="dpdfrance_search_address" placeholder="{l s='Address' mod='dpdfrance'}" /><br/>
                    <input type="text" id="dpdfrance_search_zipcode" maxlength="5" placeholder="{l s='Postcode' mod='dpdfrance'}" /> <input type="text" id="dpdfrance_search_city" placeholder="{l s='City' mod='dpdfrance'}" />
                    <button type="button" id="dpdfrance_search_submit" name="dpdfrance_search_submit" onclick="dpdfrance_relais_ajaxupdate($('#dpdfrance_search_address').val(), $('#dpdfrance_search_zipcode').val(), $('#dpdfrance_search_city').val(), 'search', dpdfrance_cart_id);">{l s='Search' mod='dpdfrance'}</button>
                    <button type="button" id="dpdfrance_reset_submit" name="dpdfrance_reset_submit" onclick="dpdfrance_relais_ajaxupdate($('#dpdfrance_search_address').val(), $('#dpdfrance_search_zipcode').val(), $('#dpdfrance_search_city').val(), 'reset', dpdfrance_cart_id);">{l s='Reset' mod='dpdfrance'}</button>
                </div>
            {/if}
        </td>
    </tr>

    {if isset($dpdfrance_relais_empty)}
        <tr>
            <td colspan="5" style="padding:0px;"><div class="dpdfrance_relais_error"><p>{l s='There are no Pickup points near this address, please modify it.' mod='dpdfrance'}</p></div></td>
        </tr>
    {/if}

{foreach from=$dpdfrance_relais_points item=points name=dpdfranceRelaisLoop}

<tr class="dpdfrance_lignepr" onclick="dpdfrance_registerPudo('{$points.relay_id|escape:'htmlall':'UTF-8'}');document.getElementById('{$points.relay_id|escape:'htmlall':'UTF-8'}').checked=true;">
        <td align="left" class="dpdfrance_logorelais"></td>
        <td align="left" class="dpdfrance_adressepr"><b>{$points.shop_name|escape:'htmlall':'UTF-8'}</b><br/>{$points.address1|escape:'htmlall':'UTF-8'}<br/>{$points.postal_code|escape:'htmlall':'UTF-8'} {$points.city|escape:'htmlall':'UTF-8'}<br/></td>
        <td align="right" class="dpdfrance_distancepr">{$points.distance|escape:'htmlall':'UTF-8'} km</td>
        <td align="center" class="dpdfrance_popinpr">
            <span onMouseOver="javascript:this.style.cursor='pointer';" onMouseOut="javascript:this.style.cursor='auto';"
                onClick="openDpdfranceDialog('dpdfrance_relaydetail{$smarty.foreach.dpdfranceRelaisLoop.index+1|escape:'htmlall':'UTF-8'}','map_canvas{$smarty.foreach.dpdfranceRelaisLoop.index+1|escape:'htmlall':'UTF-8'}',{$points.coord_lat|escape:'htmlall':'UTF-8'},{$points.coord_long|escape:'htmlall':'UTF-8'},'{$dpdfrance_base_dir|escape:'htmlall':'UTF-8'}')">
                <u>{l s='More details' mod='dpdfrance'}</u>
            </span>
        </td>
        <td align="center" class="dpdfrance_radiopr">
        {if $dpdfrance_selectedrelay == $points.relay_id}
            <input type="radio" name="dpdfrance_relay_id" id="{$points.relay_id|escape:'htmlall':'UTF-8'}" value="{$points.relay_id|escape:'htmlall':'UTF-8'}" checked="checked">
        {else}
            <input type="radio" name="dpdfrance_relay_id" id="{$points.relay_id|escape:'htmlall':'UTF-8'}" value="{$points.relay_id|escape:'htmlall':'UTF-8'}" {if $smarty.foreach.dpdfranceRelaisLoop.first} checked="checked" {/if}>
        {/if}
            <label for="{$points.relay_id|escape:'htmlall':'UTF-8'}"><span><span></span></span><b>ICI</b></label>
        </td>
</tr>

<div id="dpdfrance_relaydetail{$smarty.foreach.dpdfranceRelaisLoop.index+1|escape:'htmlall':'UTF-8'}" class="dpdfrance_relaisbox" style="display:none;">

    <div class="dpdfrance_relaisboxclose" onclick="
        document.getElementById('dpdfrance_relaydetail{$smarty.foreach.dpdfranceRelaisLoop.index+1|escape:'htmlall':'UTF-8'}').style.display='none';
        document.getElementById('dpdfrance_relais_filter').style.display='none'">
        <img src="{$dpdfrance_base_dir|escape:'htmlall':'UTF-8'}/views/img/front/relais/box-close.png"/>
    </div>

    <div class="dpdfrance_relaisboxcarto" id="map_canvas{$smarty.foreach.dpdfranceRelaisLoop.index+1|escape:'htmlall':'UTF-8'}"></div>

    <div id="relaisboxbottom" class="dpdfrance_relaisboxbottom">
        <div id="relaisboxadresse" class="dpdfrance_relaisboxadresse">
        <div class="dpdfrance_relaisboxadresseheader">{l s='Your DPD Pickup point' mod='dpdfrance'}</div><br/>
            <b>{$points.shop_name|escape:'htmlall':'UTF-8'}</b><br/>
            {$points.address1|escape:'htmlall':'UTF-8'}<br/>
            {if isset($points.address2)}
                {$points.address2|escape:'htmlall':'UTF-8'}<br/>
            {/if}
            {$points.postal_code|escape:'htmlall':'UTF-8'} {$points.city|escape:'htmlall':'UTF-8'}<br/>
            {if isset($points.local_hint)}
                <p>{l s='Landmark' mod='dpdfrance'} : {$points.local_hint|escape:'htmlall':'UTF-8'}</p>
            {/if}
        </div>

        <div class="dpdfrance_relaisboxhoraires">
            <div class="dpdfrance_relaisboxhorairesheader">{l s='Opening hours' mod='dpdfrance'}</div><br/>
                <p>
                    <span class="dpdfrance_relaisboxjour">{l s='Monday' mod='dpdfrance'} : </span>
                    {if !isset($points.monday)} {l s='Closed' mod='dpdfrance'}
                    {else}
                        {if $points.monday[0]}
                            {$points.monday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.monday[1])}
                                & {$points.monday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="dpdfrance_relaisboxjour">{l s='Tuesday' mod='dpdfrance'} : </span>
                    {if !isset($points.tuesday)} {l s='Closed' mod='dpdfrance'}
                    {else}
                        {if $points.tuesday[0]}
                            {$points.tuesday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.tuesday[1])}
                                & {$points.tuesday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="dpdfrance_relaisboxjour">{l s='Wednesday' mod='dpdfrance'} : </span>
                    {if !isset($points.wednesday)} {l s='Closed' mod='dpdfrance'}
                    {else}
                        {if $points.wednesday[0]}
                            {$points.wednesday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.wednesday[1])}
                                & {$points.wednesday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="dpdfrance_relaisboxjour">{l s='Thursday' mod='dpdfrance'} : </span>
                    {if !isset($points.thursday)} {l s='Closed' mod='dpdfrance'}
                    {else}
                        {if $points.thursday[0]}
                            {$points.thursday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.thursday[1])}
                                & {$points.thursday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="dpdfrance_relaisboxjour">{l s='Friday' mod='dpdfrance'} : </span>
                    {if !isset($points.friday)} {l s='Closed' mod='dpdfrance'}
                    {else}
                        {if $points.friday[0]}
                            {$points.friday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.friday[1])}
                                & {$points.friday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="dpdfrance_relaisboxjour">{l s='Saturday' mod='dpdfrance'} : </span>
                    {if !isset($points.saturday)} {l s='Closed' mod='dpdfrance'}
                    {else}
                        {if $points.saturday[0]}
                            {$points.saturday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.saturday[1])}
                                & {$points.saturday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="dpdfrance_relaisboxjour">{l s='Sunday' mod='dpdfrance'} : </span>
                    {if !isset($points.sunday)} {l s='Closed' mod='dpdfrance'}
                    {else}
                        {if $points.sunday[0]}
                            {$points.sunday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.sunday[1])}
                                & {$points.sunday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>
            </div>

            <div id="relaisboxinfos" class="dpdfrance_relaisboxinfos">
                <div class="dpdfrance_relaisboxinfosheader">{l s='More info' mod='dpdfrance'}</div><br/>
                <h5>{l s='Distance in km' mod='dpdfrance'} : </h5>{$points.distance|escape:'htmlall':'UTF-8'} km <br/>
                <h5>{l s='DPD Relais code' mod='dpdfrance'} : </h5>{$points.relay_id|escape:'htmlall':'UTF-8'} <br/>
                {if isset($points.closing_period[0])}
                    <h4><img src="{$dpdfrance_base_dir|escape:'htmlall':'UTF-8'}/views/img/front/relais/warning.png"/> {l s='Closing period' mod='dpdfrance'} : </h4>{$points.closing_period[0]|escape:'htmlall':'UTF-8'} <br/>
                {/if}
                {if isset($points.closing_period[1])}
                    <h4></h4>{$points.closing_period[1]|escape:'htmlall':'UTF-8'} <br/>
                {/if}
                {if isset($points.closing_period[2])}
                    <h4></h4>{$points.closing_period[2]|escape:'htmlall':'UTF-8'} <br/>
                {/if}
            </div>
        </div>
    </div>
{/foreach}

{/if}
</table>
<noscript></div></noscript>

<div id="div_dpdfrance_predict_block" class="dpdfrance_fo" style="display:none;">
    <div id="div_dpdfrance_predict_header"><p>{l s='Your order will be delivered by DPD with Predict service' mod='dpdfrance'}</p></div>
    <div class="module" id="predict">
        <div id="div_dpdfrance_predict_logo"></div>
        <div class="copy"> 
            <p><h2>{l s='With Predict, be independent with your deliveries !' mod='dpdfrance'} :</h2></p>
            <br/>
            <p><h2>{l s='How it works:' mod='dpdfrance'}</h2></p>
            <ul>
                <li>{l s='A first SMS will be sent to you when your parcel is taken in charge by DPD France. This SMS will also tell you the expected delivery date.' mod='dpdfrance'}</li>
                <li>{l s='On the day of delivery, you will receive an SMS and an email indicating the delivery time slot and your secure 4-digit code. This code will allow you to receive your order in complete security.' mod='dpdfrance'}</li>
                <li>{l s='Upon delivery, transmit your code to the DPD France delivery person. Your parcel will be delivered to you against signature.' mod='dpdfrance'}</li>
            </ul>
			<br/>
			<p><h2>{l s='The Predict service allows you to be autonomous on your delivery thanks to a wide choice of reprogramming options until the day before or in case of absence :' mod='dpdfrance'}</h2></p>
            <ul>
                <li>{l s='Delivery of your parcel at pudo’s' mod='dpdfrance'}</li>
                <li>{l s='Delivery to your workplace' mod='dpdfrance'}</li>
                <li>{l s='Delivery to another address' mod='dpdfrance'}</li>
				<li>{l s='Delivery to a neighbour' mod='dpdfrance'}</li>
                <li>{l s='Withdrawal in a DPD France depot' mod='dpdfrance'}</li>
                <li>{l s='Delivery to another date' mod='dpdfrance'}</li>
            </ul>
			<p><h2>{l s='Benefit from the advantages of Predict delivery !' mod='dpdfrance'}</h2></p>
        </div>
        <br/>
        <div id="div_dpdfrance_dpd_logo"></div>
    </div>

    <div id="div_dpdfrance_predict_gsm">
        {l s='Get all the advantages of DPD\'s Predict service by providing a french GSM number here ' mod='dpdfrance'} 
        <input type="text" name="dpdfrance_predict_gsm_dest" id="input_dpdfrance_predict_gsm_dest" maxlength="17" value="{$dpdfrance_predict_gsm_dest|escape:'htmlall':'UTF-8'}"></input><div id="dpdfrance_predict_gsm_button">></div>
    </div>

    <div id="dpdfrance_predict_error" class="warnmsg" style="display:none;">{l s='It seems that the GSM number you provided is incorrect. Please provide a french GSM number, starting with 06 or 07, on 10 consecutive digits.' mod='dpdfrance'}</div>
</div>