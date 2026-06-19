{*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<table class="table resource-list">
	<tbody class="dynamic-rows">
	{if empty($items)}
		<tr>
			<td class="list-empty">
				<div class="list-empty-msg">
					<i class="icon-warning-sign list-empty-icon"></i>
					{if isset($items)}{l s='No items' mod='autotranslator'}{/if}
				</div>
			</td>
		</tr>
	{else}
		{foreach $items as $item}
		<tr data-identifier="{$item.$identifier|escape:'html':'UTF-8'}">
			{if !$item.name}{$item.name = '--'}{/if}
			{foreach array_keys($fields_list) as $i => $prop}
				{$is_first = !$i}
				<td>
					<label>
						{if $is_first}
							<input type="checkbox" name="items[]" class="item-checkbox" value="{$item.$identifier|escape:'html':'UTF-8'}">
							{if $item.$identifier|intval}
								<span class="item-identifier">
									{$item.$identifier|intval}
									{if isset($item.identifier_extension)}{$item.identifier_extension|escape:'html':'UTF-8'}{/if}
								</span>
							{/if}
						{/if}
						<span class="item-value">
							{$item.$prop|escape:'html':'UTF-8'}
							{if !empty($item.is_custom_value)}<span class="i">({l s='custom value' mod='autotranslator'})</span>{/if}
						</span>
					</label>
				</td>
			{/foreach}
			<td class="ajax-response"></td>
			<td class="translation-stats">
				{include file="./translation-stats.tpl" stats=$item.stats}
			</td>
			{if $order.by != 'name' && $order.by != 'id' && isset($item[$order.by])}
				<td>
					<span class="item-sorting-value">{$item[$order.by]|escape:'html':'UTF-8'}</span>
				</td>
			{/if}
			<td width="100" class="last">
				<a href="#" class="btn btn-default translateCurrent">
					<span class="stop-txt"><i class="icon-loading"></i> {l s='Stop' mod='autotranslator'}</span>
					<span class="main-txt">{l s='Translate' mod='autotranslator'}</span>
				</a>
			</td>
		</tr>
		{/foreach}
	{/if}
	</tbody>
</table>
{if isset($total)}
	<div class="text-left clearfix">
		<div class="inline-block">
			{include file="./pagination.tpl" npp=$pagination.npp p=$pagination.p total=$total}
		</div>
		<div class="list-actions pull-right">
			<label class="check-all-label">
				<input type="checkbox" class="checkAllItems"> <span class="label-txt uppercase">{l s='Check all' mod='autotranslator'}</span>
			</label>
			<a href="#" class="btn btn-primary translateSelected disabled">
				<span class="stop-txt">
					<i class="icon-loading"></i> {l s='Stop' mod='autotranslator'}
					<span class="progress-num o-7"><span class="processed-num">0</span>/<span class="checked-num">0</span></span>
				</span>
				<span class="main-txt">
					{l s='Bulk translate' mod='autotranslator'}
					<span class="checked-num parentheses-wrap o-7">0</span>
				</span>
			</a>
		</div>
	</div>
{/if}
{* since 3.0.1 *}
