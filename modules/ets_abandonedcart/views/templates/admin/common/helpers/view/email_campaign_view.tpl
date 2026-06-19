{*
* 2007-2022 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<script type="text/javascript">
    {if !empty($line_chart)}
    var ETS_ABANCART_LC_DATASET = {$line_chart.datasets|json_encode};
    var ETS_ABANCART_LC_DATA_AXESX = {$line_chart.dataAxesX|json_encode};
    var ETS_ABANCART_LC_TITLE = '{$line_chart.title|escape:'html':'UTF-8'}';
    var ETS_ABANCART_LC_AXESX = '{$line_chart.axesX|escape:'html':'UTF-8'}';
    var ETS_ABANCART_LC_AXESY = '{$line_chart.axesY|escape:'html':'UTF-8'}';
    var ETS_ABANCART_LC_MINY= {$line_chart.minY|intval};
    var ETS_ABANCART_LC_MAXY= {$line_chart.maxY|intval};
    {/if}
    var ets_ac_trans = {literal}{}{/literal};
    ets_ac_trans['date_range_required'] = "{l s='Time range from and time range to is required' mod='ets_abandonedcart'}";
    ets_ac_trans['date_range_invalid'] = "{l s='Time range is invalid' mod='ets_abandonedcart'}";
    ets_ac_trans['date_range_from_less_than'] = "{l s='Time range to must be greater than time range from' mod='ets_abandonedcart'}";
</script>
{assign var="isReminderSendEmail" value=$campaign->campaign_type == 'email' || $campaign->campaign_type == 'customer'}
<div class="ets_ac_view_campaign">
    <div class="page-body">
        <div class="alert alert-warning js-ets-ac-alert-no-reminder{if isset($countReminder)&& $countReminder} hide{/if}">{l s='Campaign is not running because no reminders have been added' mod='ets_abandonedcart'}. {if isset($linkAddReminder) && $linkAddReminder}<a href="{$linkAddReminder nofilter}" class="ets_ac_add_reminder_btn_msg">{l s='Add reminder' mod='ets_abandonedcart'}</a>{/if}</div>
        <section>
            <div class="row ets_box_flex">
                <div class="col-xs-6 col-sm-6 col-lg-{if $isReminderSendEmail}4{else}6{/if} ets_ac_view_campaign_box_item">
                    <div class="panel">
                        <div class="ets_abancart_chart_title">
                            {l s='Campaign information' mod='ets_abandonedcart'}
                            
                        </div>
                        <a href="{$linkEditCampaign nofilter}" class="edit-campaign">
                            <i class="ets_svg_icon ets_svg_fill_gray ets_svg_fill_white">
                                <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M491 1536l91-91-235-235-91 91v107h128v128h107zm523-928q0-22-22-22-10 0-17 7l-542 542q-7 7-7 17 0 22 22 22 10 0 17-7l542-542q7-7 7-17zm-54-192l416 416-832 832h-416v-416zm683 96q0 53-37 90l-166 166-416-416 166-165q36-38 90-38 53 0 91 38l235 234q37 39 37 91z"/></svg>
                            </i>
                        </a>
                        <div class="ets_ac_view_campaign_box_item pt_20">
                            <div class="form-group row">
                                <label class="col-sm-6 control-label">{l s='Name' mod='ets_abandonedcart'}</label>
                                <div class="col-sm-6">
                                    {$campaign->name|escape:'html':'UTF-8'}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 control-label">{l s='Reminder type' mod='ets_abandonedcart'}</label>
                                <div class="col-sm-6">
                                    {if $campaign->campaign_type == 'email'}
                                        {l s='Automated abandoned cart emails' mod='ets_abandonedcart'}
                                    {elseif $campaign->campaign_type == 'customer'}
                                        {l s='Custom emails and newsletter' mod='ets_abandonedcart'}
                                    {elseif $campaign->campaign_type == 'popup'}
                                        {l s='Popup reminder' mod='ets_abandonedcart'}
                                    {elseif $campaign->campaign_type == 'bar'}
                                        {l s='Highlight bar reminder' mod='ets_abandonedcart'}
                                    {elseif $campaign->campaign_type == 'cart'}
                                        {l s='Manually abandoned carts emails' mod='ets_abandonedcart'}
                                    {elseif $campaign->campaign_type == 'leave'}
                                        {l s='Leaving website reminder' mod='ets_abandonedcart'}
                                    {elseif $campaign->campaign_type == 'browser'}
                                        {l s='Web push notification' mod='ets_abandonedcart'}
                                    {/if}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 control-label">{l s='Available from' mod='ets_abandonedcart'}</label>
                                <div class="col-sm-6">
                                    <p>{if $campaign->available_from}{$campaign->available_from|escape:'html':'UTF-8'}{else}--{/if}</p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 control-label">{l s='Available to' mod='ets_abandonedcart'}</label>
                                <div class="col-sm-6">
                                    <p>{if $campaign->available_to}{$campaign->available_to|escape:'html':'UTF-8'}{else}--{/if}</p>
                                </div>
                            </div>
                            {if $campaign->campaign_type == 'customer' && isset($emailSendOption)}
                                <div class="form-group row">
                                    <label class="col-sm-6 control-label">{l s='When to send email' mod='ets_abandonedcart'}</label>
                                    <div class="col-sm-6">
                                        {foreach $emailSendOption as $item}
                                            {if $item.id == $campaign->email_timing_option}
                                                {$item.name|escape:'html':'UTF-8'}
                                                {break}
                                            {/if}
                                        {/foreach}
                                    </div>
                                </div>
                            {/if}
                            {if $campaign->campaign_type !== 'customer'}
                                <div class="form-group row">
                                    <label class="col-sm-6 control-label">{l s='Applicable user group' mod='ets_abandonedcart'}</label>
                                    <div class="col-sm-6">
                                        {if $campaign_groups}
                                            {foreach $campaign_groups as $g}
                                                <span class="label label-group-campaign">{$g.name|escape:'html':'UTF-8'}</span>
                                            {/foreach}
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                            {if $campaign->campaign_type == 'customer' && $campaign->email_timing_option|intval !== 1}
                                <div class="form-group row">
                                    <label class="col-sm-6 control-label">{l s='Has placed order' mod='ets_abandonedcart'}</label>
                                    <div class="col-sm-6">
                                        {if $campaign->has_placed_orders == 'all'}
                                            <span class="label label-info">{l s='All' mod='ets_abandonedcart'}</span>
                                        {elseif $campaign->has_placed_orders == 'yes'}
                                            <span class="label label-success">{l s='Yes' mod='ets_abandonedcart'}</span>
                                        {elseif $campaign->has_placed_orders == 'no'}
                                            <span class="label label-success">{l s='No' mod='ets_abandonedcart'}</span>
                                        {/if}
                                    </div>
                                </div>
                                {if $campaign->has_placed_orders == 'yes'}
                                    <div class="form-group row">
                                        <label class="col-sm-6 control-label">{l s='From total order value' mod='ets_abandonedcart'}</label>
                                        <div class="col-sm-6">
                                            <p>{if $campaign->min_total_order}{convertPrice price=$campaign->min_total_order}{else}--{/if}</p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-6 control-label">{l s='To total order value' mod='ets_abandonedcart'}</label>
                                        <div class="col-sm-6">
                                            <p>{if $campaign->max_total_order}{convertPrice price=$campaign->max_total_order}{else}--{/if}</p>
                                        </div>
                                    </div>
                                        <div class="form-group row">
                                        <label class="col-sm-6 control-label">{l s='From last order' mod='ets_abandonedcart'}</label>
                                        <div class="col-sm-6">
                                            <p>{if $campaign->last_order_from}{$campaign->last_order_from|escape:'html':'UTF-8'}{else}--{/if}</p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-6 control-label">{l s='To last order' mod='ets_abandonedcart'}</label>
                                        <div class="col-sm-6">
                                            <p>{if $campaign->last_order_to}{$campaign->last_order_to|escape:'html':'UTF-8'}{else}--{/if}</p>
                                        </div>
                                    </div>
                                    {if isset($purchasedProducts) && $purchasedProducts}
                                        <div class="form-group row">
                                            <label class="col-sm-6 control-label">{l s='Purchased products' mod='ets_abandonedcart'}</label>
                                            <div class="col-sm-6">
                                                {$purchasedProducts nofilter}
                                            </div>
                                        </div>
                                    {/if}
                                    {if isset($notPurchasedProducts) && $notPurchasedProducts}
                                        <div class="form-group row">
                                            <label class="col-sm-6 control-label">{l s='Not purchased products' mod='ets_abandonedcart'}</label>
                                            <div class="col-sm-6">
                                                {$notPurchasedProducts nofilter}
                                            </div>
                                        </div>
                                    {/if}
                                {/if}
                            {else}
                                {if $campaign->campaign_type == 'popup' || $campaign->campaign_type == 'bar' || $campaign->campaign_type == 'browser'}
                                    <div class="form-group row">
                                        <label class="col-sm-6 control-label">{l s='Has product in shopping cart?' mod='ets_abandonedcart'}</label>
                                        <div class="col-sm-6">
                                            {if $campaign->has_product_in_cart == 0}
                                                <span class="label label-default">{l s='No' mod='ets_abandonedcart'}</span>
                                            {elseif $campaign->has_product_in_cart == 1}
                                                <span class="label label-success">{l s='Yes' mod='ets_abandonedcart'}</span>
                                            {else}
                                                <span class="label label-info">{l s='Both' mod='ets_abandonedcart'}</span>
                                            {/if}
                                        </div>
                                    </div>
                                {/if}
                                {if $campaign->campaign_type == 'email' || $campaign->campaign_type == 'customer' || $campaign->has_product_in_cart == 1}
                                    <div class="form-group row">
                                        <label class="col-sm-6 control-label">{l s='From total cart value' mod='ets_abandonedcart'}</label>
                                        <div class="col-sm-6">
                                            <p>{if $campaign->min_total_cart}{convertPrice price=$campaign->min_total_cart}{else}--{/if}</p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-6 control-label">{l s='To total cart value' mod='ets_abandonedcart'}</label>
                                        <div class="col-sm-6">
                                            <p>{if $campaign->max_total_cart}{convertPrice price=$campaign->max_total_cart}{else}--{/if}</p>
                                        </div>
                                    </div>
                                {/if}
                            {/if}
                            {if $campaign->campaign_type != 'customer'}
                                <div class="form-group row">
                                    <label class="col-sm-6 control-label">{l s='Cart has applied a voucher code' mod='ets_abandonedcart'}</label>
                                    <div class="col-sm-6">
                                        {if $campaign->has_applied_voucher == 'yes'}
                                            <span class="label label-success">{l s='Yes' mod='ets_abandonedcart'}</span>
                                        {elseif $campaign->has_applied_voucher == 'no'}
                                            <span class="label label-default">{l s='No' mod='ets_abandonedcart'}</span>
                                        {elseif $campaign->has_applied_voucher == 'both'}
                                            <span class="label label-info">{l s='Both' mod='ets_abandonedcart'}</span>
                                        {else}
                                            --
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                            {if $campaign->email_timing_option|intval != 1}
                                <div class="form-group row">
                                    <label class="col-sm-6 control-label">{l s='Countries' mod='ets_abandonedcart'}</label>
                                    <div class="col-sm-6">
                                        {if $is_all_country}
                                            {l s='All countries' mod='ets_abandonedcart'}
                                        {elseif $campaign_countries == 'unknown'}
                                            <span class="label label-group-campaign">{l s='Unknown country' mod='ets_abandonedcart'}</span>
                                        {elseif $campaign_countries && is_array($campaign_countries)}
                                            {foreach $campaign_countries as $c}
                                                <span class="label label-group-campaign">{$c.name|escape:'html':'UTF-8'}</span>
                                            {/foreach}
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                            <div class="form-group row">
                                <label class="col-sm-6 control-label">{l s='Languages' mod='ets_abandonedcart'}</label>
                                <div class="col-sm-6">
                                    {if $is_all_lang}
                                        {l s='All languages' mod='ets_abandonedcart'}
                                    {elseif $campaign_languages}
                                        {foreach $campaign_languages as $l}
                                            <span class="label label-group-campaign">{$l.name|escape:'html':'UTF-8'}</span>
                                        {/foreach}
                                    {/if}
                                </div>
                            </div>
                            {if $campaign->campaign_type == 'email'}
                            <div class="form-group row">
                                <label class="col-sm-6 control-label">{l s='Customers have subscribed to receive newsletter?' mod='ets_abandonedcart'}</label>
                                <div class="col-sm-6">
                                    {if $campaign->newsletter == 1}
                                        <span class="label label-success">{l s='Yes' mod='ets_abandonedcart'}</span>
                                    {elseif $campaign->newsletter == 0}
                                        <span class="label label-default">{l s='No' mod='ets_abandonedcart'}</span>
                                    {else}
                                        <span class="label label-info">{l s='Both' mod='ets_abandonedcart'}</span>
                                    {/if}
                                </div>
                            </div>
                            {/if}
                            <div class="form-group row">
                                <label class="col-sm-6 control-label">{l s='Status' mod='ets_abandonedcart'}</label>
                                <div class="col-sm-6">
                                    {if $campaign->enabled}
                                        <span class="label label-success">{l s='Enabled' mod='ets_abandonedcart'}</span>
                                    {else}
                                        <span class="label label-default">{l s='Disabled' mod='ets_abandonedcart'}</span>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-6 col-sm-6 col-lg-{if $isReminderSendEmail}4{else}6{/if} ets_ac_view_campaign_box_item">
                    <div class="panel ets_abancart_chart chart1">
                        <div class="ets_abancart_chart_title">
                            {l s='Statistic' mod='ets_abandonedcart'}
                        </div>
                        <div class="ets_ab_filter_flex">
                            {if $isReminderSendEmail}
                                <select class="ets_ac_tracking_filter_item ets_ac_tracking_filter_all ets_ac_ft_all ets_ac_tracking_filter_email ets_ac_ft_email" name="ets_ac_ft_all">
                                    <option value="email_sent">{l s='Email sent' mod='ets_abandonedcart'}</option>
                                    <option value="email_fail">{l s='Email failed' mod='ets_abandonedcart'}</option>
                                    <option value="email_read">{l s='Email read' mod='ets_abandonedcart'}</option>
                                    <option value="email_queue">{l s='Email queue' mod='ets_abandonedcart'}</option>
                                    {if $campaign->campaign_type == 'email'}
                                        <option value="recovered_carts" selected="selected">{l s='Recovered carts' mod='ets_abandonedcart'}</option>
                                    {/if}
                                </select>
                            {elseif isset($reminders) && $reminders}
                                <select class="ets_ac_tracking_filter_item ets_ac_tracking_filter_other ets_ac_ft_other" name="ets_ac_ft_other">
                                    <option value="all_reminders">{l s='All reminders' mod='ets_abandonedcart'}</option>
                                    {foreach from=$reminders item='reminder'}
                                        <option value="{$reminder.id_ets_abancart_reminder|intval}">{if $reminder.title|trim!==''}{$reminder.title|escape:'html':'UTF-8'}{else}#{$reminder.id_ets_abancart_reminder|intval}{/if}</option>
                                    {/foreach}
                                </select>
                            {/if}
                            {if isset($time_series) && is_array($time_series) && $time_series|count > 0}
                                <select class="ets_abancart_time_series">
                                    {foreach from=$time_series key='id' item='option'}
                                        <option value="{$id|escape:'html':'UTF-8'}" {if !empty($option.default)}selected="selected"{/if}>{$option.label|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            {/if}
                        </div>
                        {if isset($time_series) && is_array($time_series) && $time_series|count > 0}
                            <div class="ets_abancart_form_group input-group" style="width: 100%;">
                                <div class="ets_abancart_group">
                                    <input placeholder="{l s='From' mod='ets_abandonedcart'}" name="from_time" list="autocompleteOff" autocomplete="off" class="datepicker" value="" type="text" />
                                </div>
                                <div class="ets_abancart_group">
                                    <input placeholder="{l s='To' mod='ets_abandonedcart'}" name="to_time" list="autocompleteOff" autocomplete="off" class="datepicker" value="" type="text" />
                                </div>
                                <button class="ets_abancart_btn_apply btn btn-primary" name="ets_abancart_btn_apply">
                                    {l s='Apply' mod='ets_abandonedcart'}
                                </button>
                            </div>
                        {/if}
                        <canvas id="ets_abancart_chart1" style="position: relative; width:100%!important; height: 500px!important;"></canvas>
                    </div>
                </div>
                {if $isReminderSendEmail}
                    <div class="col-xs-12 col-sm-12 col-lg-4 ets_ac_view_campaign_box_item">
                        <div class="panel">
                            <div class="ets_abancart_chart_title">
                                {l s='Last email sent' mod='ets_abandonedcart'}
                            </div>
                            <button class="btn btn-default" data-toggle="modal" data-target="#etsAcModalDownloadEmailTracking">
                                <i class="ets_svg_icon ets_svg_fill_gray ets_svg_fill_hover_white">
                                <svg class="w_16 h_16" width="16" height="16" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1344 1344q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm256 0q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm128-224v320q0 40-28 68t-68 28h-1472q-40 0-68-28t-28-68v-320q0-40 28-68t68-28h465l135 136q58 56 136 56t136-56l136-136h464q40 0 68 28t28 68zm-325-569q17 41-14 70l-448 448q-18 19-45 19t-45-19l-448-448q-31-29-14-70 17-39 59-39h256v-448q0-26 19-45t45-19h256q26 0 45 19t19 45v448h256q42 0 59 39z"/></svg>
                                </i> {l s='Download full list' mod='ets_abandonedcart'}
                            </button>
                            <div class="ets_ac_view_campaign_box_item pt_20">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{l s='IP address' mod='ets_abandonedcart'}</th>
                                            <th>{l s='Customer name' mod='ets_abandonedcart'}</th>
                                            <th>{l s='Email' mod='ets_abandonedcart'}</th>
                                            <th>{l s='Reminder ID' mod='ets_abandonedcart'}</th>
                                            <th>{l s='Status' mod='ets_abandonedcart'}</th>
                                            <th>{l s='Date' mod='ets_abandonedcart'}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    {if $last_email_sent}
                                        {foreach $last_email_sent as $item}
                                            <tr>
                                                <td>{if $item.ip_address}{$item.ip_address|escape:'html':'UTF-8'}{else}<span class="text-center">--</span>{/if}</td>
                                                <td>{if $item.firstname || $item.lastname}{$item.firstname|escape:'html':'UTF-8'} {$item.lastname|escape:'html':'UTF-8'}{else}<span class="text-center">--</span>{/if}</td>
                                                <td>{if $item.email}{$item.email|escape:'html':'UTF-8'}{else}<span class="text-center">--</span>{/if}</td>
                                                <td>{$item.id_ets_abancart_reminder|escape:'html':'UTF-8'}</td>
                                                <td>
                                                    {if $item.delivered}
                                                        <span data-toggle="tooltip" data-original-title="{l s='Sent successfully' mod='ets_abandonedcart'}" data-placement="bottom">
                                                            <icon class="ets-ac-icon ets-ac-icon-checked icon-success">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                                                                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                                  <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                                                </svg>
                                                            </icon>
                                                        </span>
                                                    {elseif !$item.delivered}
                                                        <span data-toggle="tooltip" data-original-title="{l s='Sending failed' mod='ets_abandonedcart'}" data-placement="bottom">
                                                            <icon class="ets-ac-icon ets-ac-icon-close icon-error">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                                                                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                                  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                                                </svg>
                                                            </icon>
                                                        </span>
                                                    {/if}
                                                </td>
                                                <td>{$item.date_add|escape:'html':'UTF-8'}</td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td colspan="100%" class="text-center">{l s='No data found' mod='ets_abandonedcart'}</td>
                                        </tr>
                                    {/if}
                                    </tbody>
                                </table>
                                <div class="modal fade" id="etsAcModalDownloadEmailTracking" tabindex="-1" role="dialog" aria-labelledby="etsAcModalDownloadEmailTrackingLabel">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{$linkSubmitExport nofilter}" method="post">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title" id="etsAcModalDownloadEmailTrackingLabel">{l s='Download full list' mod='ets_abandonedcart'}</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-errors"></div>
                                                    {l s='Times' mod='ets_abandonedcart'}
                                                    <select class="ets_ac_popup_filter_time" name="filter_time">
                                                        <option value="this_year" selected="selected">{l s='This year' mod='ets_abandonedcart'}</option>
                                                        <option value="last_year">{l s='Last year' mod='ets_abandonedcart'}</option>
                                                        <option value="this_month">{l s='This month' mod='ets_abandonedcart'}</option>
                                                        <option value="last_month">{l s='Last month' mod='ets_abandonedcart'}</option>
                                                        <option value="today">{l s='Today' mod='ets_abandonedcart'}</option>
                                                        <option value="yesterday">{l s='Yesterday' mod='ets_abandonedcart'}</option>
                                                        <option value="time_range">{l s='Time range' mod='ets_abandonedcart'}</option>
                                                    </select>
                                                    <div class="ets_ac_popup_time_range_box time_range_box row hide">
                                                        <div class="col-lg-6">
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1">{l s='From' mod='ets_abandonedcart'}</span>
                                                                <input type="text" name="time_range_from" list="autocompleteOff" autocomplete="off" class="form-control datepicker ets_ac_popup_datepicker" placeholder="yyy-mm-dd">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="input-group">
                                                                <span class="input-group-addon " id="basic-addon1">{l s='To' mod='ets_abandonedcart'}</span>
                                                                <input type="text" name="time_range_to" class="form-control datepicker ets_ac_popup_datepicker" autocomplete="off" list="autocompleteOff" placeholder="yyy-mm-dd" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default js-ets-ac-export-campaign-close" data-dismiss="modal">
                                                        <i class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
                                                            <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1490 1322q0 40-28 68l-136 136q-28 28-68 28t-68-28l-294-294-294 294q-28 28-68 28t-68-28l-136-136q-28-28-28-68t28-68l294-294-294-294q-28-28-28-68t28-68l136-136q28-28 68-28t68 28l294 294 294-294q28-28 68-28t68 28l136 136q28 28 28 68t-28 68l-294 294 294 294q28 28 28 68z"/></svg>
                                                        </i> {l s='Close' mod='ets_abandonedcart'}
                                                    </button>
                                                    <button type="submit" class="btn btn-primary js-ets-ac-export-campaign-tracking">
                                                        <i class="ets_svg_fill_white ets_svg_hover_fill_white lh_16">
                                                            <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1344 1344q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm256 0q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm128-224v320q0 40-28 68t-68 28h-1472q-40 0-68-28t-28-68v-320q0-40 28-68t68-28h465l135 136q58 56 136 56t136-56l136-136h464q40 0 68 28t28 68zm-325-569q17 41-14 70l-448 448q-18 19-45 19t-45-19l-448-448q-31-29-14-70 17-39 59-39h256v-448q0-26 19-45t45-19h256q26 0 45 19t19 45v448h256q42 0 59 39z"/></svg>
                                                        </i> {l s='Download' mod='ets_abandonedcart'}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </section>
        <section>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ets_abancart_reminder">
                        {$table_reminder nofilter}
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

