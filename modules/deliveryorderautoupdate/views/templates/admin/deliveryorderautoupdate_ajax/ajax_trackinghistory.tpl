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

{if !$history}
<tr class="tr_cols empty-row">
    <td colspan="5">
        <h1 style="text-align:center">{l s='No tracking yet' mod='deliveryorderautoupdate'}</h1>
    </td>
</tr>
{else}
{foreach $history as $hs}
<tr class="tr_cols">
    <td class=" center fit-cell">
        <span class="title_box htr_date_add" data-id="{$hs.date_add|escape:'htmlall':'UTF-8'}"> {$hs.date_add|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td class=" left carrier_response" data-id="{$hs.carrier_response|escape:'htmlall':'UTF-8'}">
        {if $hs.success_response == 0}
        <a target="_blank" class="list-action-enable action-disabled" href="#" title="Disable" status="0" >
            <i class="icon-remove"></i>
        </a>
        {elseif $hs.success_response == 1}
        <a target="_blank" class="list-action-enable action-enabled" href="#" title="Active">
            <i class="icon-check"></i>
        </a>
        {/if}
        {$hs.carrier_response|escape:'htmlall':'UTF-8'}
    </td>
    <td class="left fit-cell">
        <div class="htr_shipping">
            {if isset($hs.event_code)}
            <a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$hs.event_code]->color|escape:'htmlall':'UTF-8'}" title="Active" status="1">
                <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$hs.event_code]->id_status|escape:'htmlall':'UTF-8'}.png" />
            </a>
            <div class="right_shipping">
                {$hs.shipping_status|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$hs.step_date|escape:'htmlall':'UTF-8'}</span>
            </div>
            {/if}
        </div>
    </td>
    <td class=" center">
        <span class="title_box htr_method" data-id="{$hs.carrier|escape:'htmlall':'UTF-8'}">
            {if isset($methods[$hs.carrier])}
            {$methods[$hs.carrier]|escape:'htmlall':'UTF-8'}
            {else}
            {$hs.carrier|escape:'htmlall':'UTF-8'}
            {/if}
        </span>
    </td>
    <td class="htr_email center" data-id="{if $hs.email_sent == 1}1{else}2{/if}">
        {if $hs.email_sent == 1}
        <i class="icon-envelope" data-id_order="{$hs.id_order|escape:'htmlall':'UTF-8'}" style="cursor: pointer;"></i>
        <span class="result"></span>
        {/if}
    </td>
</tr>
{/foreach}
{/if}
<script type="text/javascript">
    $(document).ready(function () {
        var timer;
        var step = 1;

        $("select[name='douhistory_Filter_mail'], select[name='douhistory_Filter_shipping']").change(function() {
            filterHistory_Carrier();
        });
        $("input[name='douhistory_Filter_id_track'], input[name='douhistory_Filter_order'], input[name='douhistory_Filter_date'], input[name='douhistory_Filter_carrier'], input[name='douhistory_Filter_carrier_response'], input[name='douhistory_Filter_method']").keyup(function() {
            console.log('alo');
            clearTimeout(timer);
            timer = setTimeout(function() {
                filterHistory_Carrier();
            }, 1000);
        });
        function filterHistory_Carrier()
        {
            var date = $("input[name='douhistory_Filter_date']").val();
            var carrier_response = $("input[name='douhistory_Filter_carrier_response']").val().toLowerCase();
            var shipping = $("select[name='douhistory_Filter_shipping']").val().toLowerCase();
            var method = $("input[name='douhistory_Filter_method']").val().toLowerCase();
            var email = $("select[name='douhistory_Filter_mail']").val();
            $(".dl_dashboard tbody tr").each(function() {
                var date_ = $(this).find('.htr_date_add').data('id');
                var carrier_response_ = $(this).find('.carrier_response').data('id').toLowerCase();
                var shipping_ = $(this).find('.htr_shipping').data('id').toLowerCase();
                var method_ = $(this).find('.htr_method').data('id').toLowerCase();
                var email_ = $(this).find('.htr_email').data('id');

                var hide_date = 0;
                var hide_carrier_response = 0;
                var hide_shipping = 0;
                var hide_method = 0;
                var hide_email = 0;
                if (!date && !carrier_response && !shipping && !method && !email) {
                    $(this).show();
                } else {

                    if (email) {
                        if (email == email_) {
                            hide_email = 0;
                        } else {
                            hide_email = 1;
                        }
                    } else {
                        hide_email = 0;
                    }

                    if (date) {
                        if (date_.indexOf(date) == 0) {
                            hide_date = 0;
                        } else {
                            hide_date = 1;
                        }
                    } else {
                        hide_date = 0;
                    }
                    if (carrier_response) {
                        if (carrier_response_.indexOf(carrier_response) == 0) {
                            hide_carrier_response = 0;
                        } else {
                            hide_carrier_response = 1;
                        }
                    } else {
                        hide_carrier_response = 0;
                    }
                    if (shipping) {
                        if (shipping_.indexOf(shipping) == 0) {
                            hide_shipping = 0;
                        } else {
                            hide_shipping = 1;
                        }
                    } else {
                        hide_shipping = 0;
                    }
                    if (method) {
                        if (method_.indexOf(method) == 0) {
                            hide_method = 0;
                        } else {
                            hide_method = 1;
                        }
                    } else {
                        hide_method = 0;
                    }
                    if ((hide_date) || (hide_carrier_response) || (hide_shipping) || (hide_method) || (hide_email)) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                }
            });
        }
    });
</script>