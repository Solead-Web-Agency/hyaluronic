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

{if !$emails}
<tr class="tr_cols">
    <td colspan="7">
        <h1 style="text-align:center">{l s='No email yet' mod='deliveryorderautoupdate'}</h1>
    </td>
</tr>
{else}
{foreach $emails as $hs}
<tr class="tr_cols">
    <td class=" center">
        <span class="title_box htr_track_id" data-id="{$hs.id|escape:'htmlall':'UTF-8'}"> {$hs.id|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td class=" center">
        <span class="title_box htr_id_order" data-id="{$hs.id_order|escape:'htmlall':'UTF-8'}"> {$hs.id_order|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td class=" center">
        <span class="title_box htr_carrier_name"> {$hs.firstname|escape:'htmlall':'UTF-8'} {$hs.lastname|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td class=" center">
        <span class="title_box htr_id_tracking" data-id="{$hs.id_tracking_history|escape:'htmlall':'UTF-8'}"> {$hs.id_tracking_history|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td class=" center">
        <span class="title_box htr_date_add" data-id="{$hs.date_sent|escape:'htmlall':'UTF-8'}"> {$hs.date_sent|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td class=" left htr_shipping">
        {if isset($hs.shipping_status)}
        <a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$hs.shipping_status]->color|escape:'htmlall':'UTF-8'}" title="Active" status="1">
            <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$hs.shipping_status]->id_status|escape:'htmlall':'UTF-8'}.png" />
        </a>
        <div class="right_shipping">
            {$hs.status_text|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$hs.step_date|escape:'htmlall':'UTF-8'}</span>
        </div>
        {/if}

    </td>
    <td class="htr_email center">
       {if isset($email_statuses[$hs.email_status])}{$email_statuses[$hs.email_status]|escape:'htmlall':'UTF-8'}{/if}
   </td>
</tr>
{/foreach}
{/if}
<script type="text/javascript">
    $(document).ready(function () {
        var timer;
        var step = 1;
        $("#check_historymail").toggle(function() {
            $("#check_historymail span").html('<i class="icon-check-empty"></i>');
            checkDelBoxes($('#configuration_form').get(0), 'historyBox[]', false, 1);
        }, function(){
            $("#check_historymail span").html('<i class="icon-check-sign"></i>');
            checkDelBoxes($('#configuration_form').get(0), 'historyBox[]', true, 1);
        });
        $(".views_more").click(function() {
            var secure_key = $('input[name=token]').val();
            $.ajax({
                type: 'POST',
                url: $("input[name='admin_url']").val(),
                data: {
                    controller : 'AdmindeliveryorderautoupdateAjax',
                    action : 'EmailHistory',
                    step : step,
                    secure_key: secure_key
                },
                success: function(return_data)
                {
                    //var data = JSON.parse(return_data);
                    $(".dashboard_history").append(return_data);
                    step++;

                },
            });
        });
        $("select[name='douhistory_Filter_mail']").change(function() {
            filterHistoryCarrier();
        });
        $("input[name='douhistory_Filter_id_track'], input[name='douhistory_Filter_order'], input[name='douhistory_Filter_date'], input[name='douhistory_Filter_carrier'], input[name='douhistory_Filter_carrier_response'], input[name='douhistory_Filter_shipping'], input[name='douhistory_Filter_method']").keyup(function() {
            clearTimeout(timer);
            timer = setTimeout(function() {
                filterHistoryCarrier();
            }, 1000);
        });
        function filterHistoryCarrier()
        {
            var track_id = $("input[name='douhistory_Filter_id_track']").val();
            var order = $("input[name='douhistory_Filter_order']").val();
            var date = $("input[name='douhistory_Filter_date']").val();
            var carrier = $("input[name='douhistory_Filter_carrier']").val();
            var carrier_response = $("input[name='douhistory_Filter_carrier_response']").val();
            var shipping = $("input[name='douhistory_Filter_shipping']").val();
            var method = $("input[name='douhistory_Filter_method']").val();
            var email = $("select[name='douhistory_Filter_mail']").val();
            $(".dl_dashboard tbody tr").each(function() {
                var track_id_ = $(this).find('.htr_track_id').data('id');
                var order_ = $(this).find('.htr_id_order').data('id');
                var date_ = $(this).find('.htr_date_add').data('id');
                var carrier_ = $(this).find('.htr_carrier_name').data('id');
                var carrier_response_ = $(this).find('.carrier_response').data('id');
                var shipping_ = $(this).find('.htr_shipping').data('id');
                var method_ = $(this).find('.htr_method').data('id');
                var email_ = $(this).find('.htr_email').data('id');
                var hide_track_id = 0;
                var hide_order = 0;
                var hide_date = 0;
                var hide_carrier = 0;
                var hide_carrier_response = 0;
                var hide_shipping = 0;
                var hide_method = 0;
                var hide_email = 0;
                if (!track_id && !order && !date && !carrier && !carrier_response && !shipping && !method && !email) {
                    $(this).show();
                } else {
                    if (track_id) {
                        if (track_id == track_id_) {
                            hide_track_id = 0;
                        } else {
                            hide_track_id = 1;
                        }
                    } else {
                        hide_track_id = 0;
                    }
                    if (email) {
                        if (email == email_) {
                            hide_email = 0;
                        } else {
                            hide_email = 1;
                        }
                    } else {
                        hide_email = 0;
                    }
                    if (order) {
                        if (order == order_) {
                            hide_order = 0;
                        } else {
                            hide_order = 1;
                        }
                    } else {
                        hide_order = 0;
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
                    if (carrier) {
                        if (carrier_.indexOf(carrier) == 0) {
                            hide_carrier = 0;
                        } else {
                            hide_carrier = 1;
                        }
                    } else {
                        hide_carrier = 0;
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
                    if ((hide_track_id) || (hide_order) || (hide_date) || (hide_carrier) || (hide_carrier_response) || (hide_shipping) || (hide_method) || (hide_email)) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                }
            });
        }
    });
</script>
