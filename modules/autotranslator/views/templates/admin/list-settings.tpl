{*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<form method="post" action="" class="form-horizontal list-params clearfix">
	<div class="sort-and-filter pull-left">
		{foreach $special_params as $ct => $param}
			{foreach $param as $name => $options}
				<select name="{$name|escape:'html':'UTF-8'}[]" class="update-list inline-block special-param first-empty-option show-ids {$ct|escape:'html':'UTF-8'}{if $ct != $current_ct} hidden{/if}" multiple data-qs="20">
					{foreach $options as $opt_name => $display_name}
						<option value="{$opt_name|escape:'html':'UTF-8'}">{$display_name|escape:'html':'UTF-8'}</option>
					{/foreach}
				</select>
			{/foreach}
		{/foreach}
		<div class="inline-block sorting">
			<label class="inline-block">{l s='Sort by' mod='autotranslator'}</label>
			<select name="order_by" class="update-list inline-block order-by">
				{foreach $sorting_options as $opt_name => $o}
					<option value="{$opt_name|escape:'html':'UTF-8'}" class="{if !empty($o.class)}special-option {$o.class|escape:'html':'utf-8'}{/if}"{if $order.by == $opt_name} selected{/if}>{$o.name|escape:'html':'UTF-8'}</option>
				{/foreach}
			</select>
			<a href="#" class="icon-long-arrow-down order-way-label{if $order.way == 'DESC'} active{/if}" data-way="DESC"></a>
			<a href="#" class="icon-long-arrow-up order-way-label{if $order.way == 'ASC'} active{/if}" data-way="ASC"></a>
			<input type="hidden" name="order_way" value="{$order.way|escape:'html':'UTF-8'}" class="order-way update-list">
		</div>
	</div>
	<div class="lang-selection pull-right">
		<label class="inline-block">{l s='Original language' mod='autotranslator'}</label>
		<select name="at_from" class="update-list inline-block from-lang">
			{foreach array_keys($languages) as $iso}
				<option value="{$iso|escape:'html':'UTF-8'}"{if $iso == $lang_from} selected{/if}>
					{$iso|upper|escape:'html':'UTF-8'}
				</option>
			{/foreach}
		</select>
		<label class="inline-block">{l s='Translate to' mod='autotranslator'}</label>
		<select name="at_to[]" class="update-list inline-block to-lang" data-all="{l s='All languages' mod='autotranslator'}" multiple>
			{foreach array_keys($languages) as $iso}
				<option value="{$iso|escape:'html':'UTF-8'}"{if $iso == $lang_from} class="hidden"{else} selected{/if}>
					{$iso|upper|escape:'html':'UTF-8'}
				</option>
			{/foreach}
		</select>
	</div>
</form>
{* since 3.0.0 *}
