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
    var ETS_ABANCART_LC_DATASET = {if isset($line_chart.datasets)}{$line_chart.datasets|json_encode}{else}{literal}{}{/literal}{/if};
    var ETS_ABANCART_LC_DATA_AXESX = {if isset($line_chart.dataAxesX)}{$line_chart.dataAxesX|json_encode}{else}{literal}{}{/literal}{/if};
    var ETS_ABANCART_LC_TITLE = '{if isset($line_chart.title)}{$line_chart.title|escape:'html':'UTF-8'}{/if}';
    var ETS_ABANCART_LC_AXESX = '{if isset($line_chart.axesX)}{$line_chart.axesX|escape:'html':'UTF-8'}{/if}';
    var ETS_ABANCART_LC_AXESY = '{if isset($line_chart.axesY)}{$line_chart.axesY|escape:'html':'UTF-8'}{/if}';
    var ETS_ABANCART_LC_MINY = {if isset($line_chart.minY)}{$line_chart.minY|intval}{/if};
    var ETS_ABANCART_LC_MAXY = {if isset($line_chart.maxY)}{$line_chart.maxY|intval}{/if};
</script>
<div class="ets_abancart_dashboard">
	<div class="ets_abancart_heading ets_abancart_chart dashboard">
		{include file="./time-series.tpl" chart_type='line' dashboard=true}
	</div>
	<div class="ets_abancart_stats">
        {if !empty($top_stats)}{foreach from=$top_stats item='stat'}
			<div class="ets_abancart_item {$stat.class|escape:'html':'UTF-8'}">
				<h4 class="ets_abancart_title">{$stat.name|escape:'html':'UTF-8'}</h4>
				<i class="{$stat.icon|escape:'html':'UTF-8'}"> </i>
				<span class="ets_abancart_stats_price">{$stat.label|replace:'[1]':'<span class="ets_abancart_label">'|replace:'[/1]':'</span>' nofilter}</span>&nbsp;
				<p class="ets_abancart_desc">{$stat.desc|escape:'html':'UTF-8'}</p>
                {if isset($stat.link) && is_array($stat.link) && $stat.link|count}
					<a class="ets_abancart_tab_link" href="{$stat.link.href nofilter}">{$stat.link.title|escape:'html':'UTF-8'}
					<i class="lh_16 ets_svg"><svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M979 960q0 13-10 23l-466 466q-10 10-23 10t-23-10l-50-50q-10-10-10-23t10-23l393-393-393-393q-10-10-10-23t10-23l50-50q10-10 23-10t23 10l466 466q10 10 10 23zm384 0q0 13-10 23l-466 466q-10 10-23 10t-23-10l-50-50q-10-10-10-23t10-23l393-393-393-393q-10-10-10-23t10-23l50-50q10-10 23-10t23 10l466 466q10 10 10 23z"/></svg>
					</i></a>
                {/if}
			</div>
        {/foreach}{/if}
	</div>
	<div class="ets_abancart_charts">
		<div class="col-xs-12 col-sm-6 ets_abancart_charts_col chart-col_1">
			<div class="ets_abancart_chart chart1" style="position: relative; width:100%; clear: both;">
				<h4 class="ets_abancart_chart_title">
					{l s='Statistic' mod='ets_abandonedcart'}
					<span class="ets_abancart_chart_info">
						<i class="ets_svg_fill_gray">
							<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1008 1200v160q0 14-9 23t-23 9h-160q-14 0-23-9t-9-23v-160q0-14 9-23t23-9h160q14 0 23 9t9 23zm256-496q0 50-15 90t-45.5 69-52 44-59.5 36q-32 18-46.5 28t-26 24-11.5 29v32q0 14-9 23t-23 9h-160q-14 0-23-9t-9-23v-68q0-35 10.5-64.5t24-47.5 39-35.5 41-25.5 44.5-21q53-25 75-43t22-49q0-42-43.5-71.5t-95.5-29.5q-56 0-95 27-29 20-80 83-9 12-25 12-11 0-19-6l-108-82q-10-7-12-20t5-23q122-192 349-192 129 0 238.5 89.5t109.5 214.5zm-368-448q-130 0-248.5 51t-204 136.5-136.5 204-51 248.5 51 248.5 136.5 204 204 136.5 248.5 51 248.5-51 204-136.5 136.5-204 51-248.5-51-248.5-136.5-204-204-136.5-248.5-51zm768 640q0 209-103 385.5t-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/></svg>
						</i>
						<span class="text-show_hover">{l s='The total turnover earned from recovered carts during a time range' mod='ets_abandonedcart'}</span>
					</span>
				</h4>
                <div class="ets_ab_filter_flex">
					<select class="ets_ac_tracking_filter_item ets_ac_tracking_filter_all ets_ac_ft_all ets_ac_tracking_filter_email ets_ac_ft_email" name="ets_ac_ft_all">
						<option value="recovered_carts" selected="selected">{l s='Recovered carts' mod='ets_abandonedcart'}</option>
						<option value="abancart_mail_sent">{l s='Abandoned carts mail sent(s)' mod='ets_abandonedcart'}</option>
					</select>
				</div>
				<div style="position: relative; width:100%!important; clear: both;">
					<canvas id="ets_abancart_chart1" style="position: relative; width:100%!important; height: 500px!important;"></canvas>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 ets_abancart_charts_col chart-col_3">
			<div class="ets_abancart_chart chart3" style="position: relative; width:100%; clear: both;">
				<h4 class="ets_abancart_title">{l s='Campaign tracking' mod='ets_abandonedcart'}</h4>
				<div class="ets_abancart_group_stats">
					<div class="ets_abancart_stats_item">
						<span class="ets_abancart_label"><strong>{l s='Reminder type' mod='ets_abandonedcart'}</strong></span>
						<span class="ets_abancart_totals"><strong>{l s='Execution times' mod='ets_abandonedcart'}</strong></span>
					</div>
					<div class="ets_abancart_stats_campaigns">
                        {$stats nofilter}
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="ets_abancart_tables">
		<h3 class="ets_abancart_title">{l s='Recent recovered orders' mod='ets_abandonedcart'}</h3>
		{if isset($html) && $html}
			<div class="ets_abancart_campaign_recent email active">
				{$html nofilter}
			</div>
		{/if}
	</div>
</div>