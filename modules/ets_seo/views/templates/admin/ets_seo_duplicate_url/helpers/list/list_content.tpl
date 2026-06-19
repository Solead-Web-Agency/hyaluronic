{*
* 2007-2021 ETS-Soft
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
*  @copyright  2007-2021 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

{capture name='tr_count'}{counter name='tr_count'}{/capture}
<tbody>
{if count($list)}
    {foreach $list AS $index => $tr}
        <tr{if $position_identifier} id="tr_{$position_group_identifier nofilter}_{$tr.$identifier nofilter}_{if isset($tr.position['position'])}{$tr.position['position'] nofilter}{else}0{/if}"{/if} class="{if isset($tr.class)}{$tr.class nofilter}{/if} {if $tr@iteration is odd by 1}odd{/if}"{if isset($tr.color) && $color_on_bg} style="background-color: {$tr.color nofilter}"{/if} >
            {if $bulk_actions && $has_bulk_actions}
                <td class="row-selector text-center">
                    {if isset($list_skip_actions.delete)}
                        {if !in_array($tr.$identifier, $list_skip_actions.delete)}
                            <input type="checkbox" name="{$list_id nofilter}Box[]" value="{$tr.$identifier nofilter}"{if isset($checked_boxes) && is_array($checked_boxes) && in_array({$tr.$identifier nofilter}, $checked_boxes)} checked="checked"{/if} class="noborder" />
                        {/if}
                    {else}
                        <input type="checkbox" name="{$list_id nofilter}Box[]" value="{$tr.$identifier nofilter}"{if isset($checked_boxes) && is_array($checked_boxes) && in_array({$tr.$identifier nofilter}, $checked_boxes)} checked="checked"{/if} class="noborder" />
                    {/if}
                </td>
            {/if}
            {foreach $fields_display AS $key => $params}
                {block name="open_td"}
                    <td
                    {if isset($params.position)}
                        id="td_{if !empty($position_group_identifier)}{$position_group_identifier nofilter}{else}0{/if}_{$tr.$identifier nofilter}{if $smarty.capture.tr_count > 1}_{($smarty.capture.tr_count - 1)|intval}{/if}"
                    {/if}
                    class="{strip}{if !$no_link}pointer{/if}
					{if isset($params.position) && $order_by == 'position'  && $order_way != 'DESC'} dragHandle{/if}
					{if isset($params.class)} {$params.class nofilter}{/if}
					{if isset($params.align)} {$params.align nofilter}{/if}{/strip}"
                    {if (!isset($params.position) && !$no_link && !isset($params.remove_onclick))}
                        {if isset($tr.link) }
                            onclick="document.location = '{$tr.link nofilter}'">
                        {else}
                            onclick="document.location = '{$current_index|addslashes|escape:'html':'UTF-8'}&amp;{$identifier|escape:'html':'UTF-8'}={$tr.$identifier|escape:'html':'UTF-8'}{if $view}&amp;view{else}&amp;update{/if}{$table|escape:'html':'UTF-8'}{if $page > 1}&amp;page={$page|intval}{/if}&amp;token={$token|escape:'html':'UTF-8'}'">
                        {/if}
                    {else}
                        >
                    {/if}
                {/block}
                {block name="td_content"}
                    {if isset($params.prefix)}{$params.prefix nofilter}{/if}
                    {if isset($params.badge_success) && $params.badge_success && isset($tr.badge_success) && $tr.badge_success == $params.badge_success}<span class="badge badge-success">{/if}
                {if isset($params.badge_warning) && $params.badge_warning && isset($tr.badge_warning) && $tr.badge_warning == $params.badge_warning}<span class="badge badge-warning">{/if}
                {if isset($params.badge_danger) && $params.badge_danger && isset($tr.badge_danger) && $tr.badge_danger == $params.badge_danger}<span class="badge badge-danger">{/if}
                {if isset($params.color) && isset($tr[$params.color])}
                    <span class="label color_field" style="background-color:{$tr[$params.color] nofilter};color:{if Tools::getBrightness($tr[$params.color]) < 128}white{else}#383838{/if}">
                {/if}
                    {if isset($tr.$key)}
                        {if isset($params.active)}
                            {$tr.$key nofilter}
                        {elseif isset($params.callback)}
                            {if isset($params.maxlength) && Tools::strlen($tr.$key) > $params.maxlength}
                                <span title="{$tr.$key nofilter}">{$tr.$key nofilter}</span>
                            {else}
                                {$tr.$key nofilter}
                            {/if}
					{elseif isset($params.activeVisu)}
						{if $tr.$key}
                            <i class="icon-check-ok"></i> {l s='Enabled' mod='ets_seo'}
						{else}
							<i class="icon-remove"></i> {l s='Disabled' mod='ets_seo'}
                        {/if}
					{elseif isset($params.position)}
						{if !$filters_has_value && $order_by == 'position' && $order_way != 'DESC'}
                            <div class="dragGroup">
								<div class="positions">
									{$tr.$key.position + 1 nofilter}
								</div>
							</div>
                        {else}
                            {$tr.$key.position + 1 nofilter}
                        {/if}
					{elseif isset($params.image)}
						{$tr.$key nofilter}
					{elseif isset($params.icon)}
						{if is_array($tr[$key])}
                            {if isset($tr[$key]['class'])}
                                <i class="{$tr[$key]['class'] nofilter}"></i>
							{else}
								<img src="../img/admin/{$tr[$key]['src'] nofilter}" alt="{$tr[$key]['alt'] nofilter}" title="{$tr[$key]['alt'] nofilter}" />
                            {/if}
                        {/if}
					{elseif isset($params.type) && $params.type == 'price'}
						{if isset($tr.id_currency)}
                            {displayPrice price=$tr.$key currency=$tr.id_currency}
                        {else}
                            {displayPrice price=$tr.$key}
                        {/if}
					{elseif isset($params.float)}
						{$tr.$key nofilter}
					{elseif isset($params.type) && $params.type == 'date'}
						{dateFormat date=$tr.$key full=0}
					{elseif isset($params.type) && $params.type == 'datetime'}
						{dateFormat date=$tr.$key full=1}
					{elseif isset($params.type) && $params.type == 'decimal'}
						{$tr.$key nofilter}
					{elseif isset($params.type) && $params.type == 'percent'}
						{$tr.$key nofilter} {l s='%' mod='ets_seo'}
					{elseif isset($params.type) && $params.type == 'bool'}
            {if $tr.$key == 1}
                            {l s='Yes' mod='ets_seo'}
                        {elseif $tr.$key == 0 && $tr.$key != ''}
                            {l s='No' mod='ets_seo'}
                        {/if}
					{* If type is 'editable', an input is created *}
					{elseif isset($params.type) && $params.type == 'editable' && isset($tr.id)}
						<input type="text" name="{$key nofilter}_{$tr.id nofilter}" value="{$tr.$key|escape:'html':'UTF-8'}" class="{$key nofilter}" />
					{elseif $key == 'color'}
						{if !is_array($tr.$key)}
                            <div style="background-color: {$tr.$key nofilter};" class="attributes-color-container"></div>
						{else} {*TEXTURE*}
						<img src="{$tr.$key.texture nofilter}" alt="{$tr.name nofilter}" class="attributes-color-container" />
                        {/if}
					{elseif isset($params.maxlength) && Tools::strlen($tr.$key) > $params.maxlength}
						<span title="{$tr.$key|escape:'html':'UTF-8'}">{$tr.$key|truncate:$params.maxlength:'...'|escape:'html':'UTF-8'}</span>
                        {else}
                            {$tr.$key|escape:'html':'UTF-8'}
                        {/if}
                    {else}
                        {block name="default_field"}--{/block}
                    {/if}
                    {if isset($params.suffix)}{$params.suffix nofilter}{/if}
                {if isset($params.color) && isset($tr.color)}
                    </span>
                {/if}
                {if isset($params.badge_danger) && $params.badge_danger && isset($tr.badge_danger) && $tr.badge_danger == $params.badge_danger}</span>{/if}
                {if isset($params.badge_warning) && $params.badge_warning && isset($tr.badge_warning) && $tr.badge_warning == $params.badge_warning}</span>{/if}
                    {if isset($params.badge_success) && $params.badge_success && isset($tr.badge_success) && $tr.badge_success == $params.badge_success}</span>{/if}
                {/block}
                {block name="close_td"}
                    </td>
                {/block}
            {/foreach}

            {if $multishop_active && $shop_link_type}
                <td title="{$tr.shop_name nofilter}">
                    {if isset($tr.shop_short_name)}
                        {$tr.shop_short_name nofilter}
                    {else}
                        {$tr.shop_name nofilter}
                    {/if}
                </td>
            {/if}
            {if $has_actions}
                <td class="text-right">
                    {assign var='compiled_actions' value=array()}
                    {foreach $actions AS $key => $action}
                        {if isset($tr.$action)}
                            {if $key == 0}
                                {assign var='action' value=$action}
                            {/if}
                            {if $action == 'delete' && $actions|@count > 2}
                                {$compiled_actions[] = 'divider'}
                            {/if}
                            {$compiled_actions[] = $tr.$action}
                        {/if}
                    {/foreach}
                    {if $compiled_actions|count > 0}
                        {if $compiled_actions|count > 1}<div class="btn-group-action">{/if}
                        <div class="btn-group pull-right">
                            {$compiled_actions[0] nofilter}
                            {if $compiled_actions|count > 1}
                                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <i class="icon-caret-down"></i>&nbsp;
                                </button>
                                <ul class="dropdown-menu">
                                    {foreach $compiled_actions AS $key => $action}
                                        {if $key != 0}
                                            <li{if $action == 'divider' && $compiled_actions|count > 3} class="divider"{/if}>
                                                {if $action != 'divider'}{$action nofilter}{/if}
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ul>
                            {/if}
                        </div>
                        {if $compiled_actions|count > 1}</div>{/if}
                    {/if}
                </td>
            {/if}
        </tr>
    {/foreach}
{else}
    <tr>
        <td class="list-empty" colspan="100%">
            <div class="alert alert-info" style="margin-top: 15px;">
                {l s='Good! No duplicates found' mod='ets_seo'}
            </div>
        </td>
    </tr>
{/if}
</tbody>