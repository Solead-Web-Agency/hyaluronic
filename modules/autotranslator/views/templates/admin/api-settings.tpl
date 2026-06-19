{*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="selected-provider">
	<label class="label-inline">{l s='Translation provider' mod='autotranslator'}</label>
	<a href="#" class="toggleAPISettings">
		<span class="selected-provider-name dynamic-value b">
			{if $selected_provider_info.name}
				{$selected_provider_info.name|escape:'html':'UTF-8'}
			{else}
				{l s='no provider selected' mod='autotranslator'}
			{/if}
		</span>
		<i class="icon icon-pencil"></i>
	</a>
</div>
<div class="provider-stats at-panel-footer">
	{l s='Characters processed today' mod='autotranslator'}: <span class="selected-stats-d dynamic-value b">{$selected_provider_info.stats.day|intval}</span>,
	{l s='this month' mod='autotranslator'}: <span class="selected-stats-m dynamic-value b">{$selected_provider_info.stats.month|intval}</span>
	<i class="icon-question-circle label-tooltip" data-toggle="tooltip" title="{l s='Including HTML tags' mod='autotranslator'}"></i>
</div>
<div class="api-settings-modal">
	{foreach $providers as $provider_name => $p}
		<div class="api-info{if !$p.selected} hidden{/if}" data-provider="{$provider_name|escape:'html':'UTF-8'}">
			{if isset($p.models_data) && !empty($p.models_data.not_supported)}
				<div class="api-warning alert-danger">
					<span class="b">{l s='Some translation directions are not supported ' mod='autotranslator'}:</span>
					{', '|implode:$p.models_data.not_supported|escape:'html':'UTF-8'}
				</div>
				{if !empty($p.models_data.supported)}
					<div class="api-warning alert-success">
						<span class="b">{l s='Supported translation directions are' mod='autotranslator'}:</span>
						{', '|implode:$p.models_data.supported|escape:'html':'UTF-8'}
					</div>
				{/if}
			{/if}
			{if !empty($p.not_supported_languages)}
				<div class="api-warning alert-danger">
					<span class="b">{l s='Some languages are not supported ' mod='autotranslator'}:</span>
					{', '|implode:$p.not_supported_languages|escape:'html':'UTF-8'}
				</div>
			{/if}
		</div>
	{/foreach}
	<div class="api-provider">
		<label>{l s='Select translation provider' mod='autotranslator'}</label>
		<select name="provider" class="auto-width">
			{foreach $providers as $provider_name => $p}
				<option value="{$provider_name|escape:'html':'UTF-8'}"{if $p.selected} selected{/if}>{$p.name|escape:'html':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
	{foreach $providers as $provider_name => $p}
	<form class="api-credentials-form{if !$p.selected} hidden{/if}" data-provider="{$provider_name|escape:'html':'UTF-8'}">
		{if !empty($p.links.pricing)}
			<a href="{$p.links.pricing|escape:'html':'UTF-8'}" class="api-link inline-block m-right" target="_blank">
				{l s='Pricing options' mod='autotranslator'} <i class="icon-external-link-sign"></i>
			</a>
		{/if}
		{if (!empty($p.additional_info))}
			<div class="api-additional-info inline-block">{$p.additional_info|escape:'html':'UTF-8'}</div>
		{/if}
		{foreach $p.fields as $name => $f}
			<label>{$f.label|escape:'html':'UTF-8'}</label>
			{if $f.type == 'select'}
				<select name="{$name|escape:'html':'UTF-8'}" class="auto-width">
					{foreach $f.options as $o_value => $o_label}
						<option value="{$o_value|escape:'html':'UTF-8'}"{if $o_value == $f.value} selected{/if}>{$o_label|escape:'html':'UTF-8'}</option>
					{/foreach}
				</select>
			{else}
				<input type="text" name="{$name|escape:'html':'UTF-8'}" value="{$f.value|escape:'html':'UTF-8'}">
			{/if}
		{/foreach}
		<div class="how-to">
			<a href="#" class="how-to-label">
				{if $provider_name == 'IBMTranslate'}
					{l s='How to get credentials' mod='autotranslator'}
				{else}
					{l s='How to get API key' mod='autotranslator'}
				{/if}
				<i class="icon-info-circle"></i>
			</a>
			<div class="how-to-content">
				{include file="./how-to.tpl"}
			</div>
		</div>
	</form>
	{/foreach}
	<div class="api-modal-footer">
		<button type="button" class="btn btn-default saveAPI"><i class="process-icon-save"></i> {l s='Save' mod='autotranslator'}</button>
	</div>
	<a href="#" class="closeModal">&times;</a>
</div>
<div class="api-settings-modal-overlay"></div>
{* since 3.0.3 *}
