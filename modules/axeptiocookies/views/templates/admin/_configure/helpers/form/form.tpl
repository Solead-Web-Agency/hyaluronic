{*
*  @author DECTYS SARL <florian@dectys.com>
*  @copyright  DECTYS SARL
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'switch_with_lang'}
		<div class="col-lg-3">
			{foreach from=$languages item=language}
				{if $languages|count > 1}
					<div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
				{/if}
				<div class="form-group">
					<div class="col-lg-9">

						<span class="switch prestashop-switch fixed-width-lg">
							{foreach $input.values as $value}
								<input type="radio" name="{$input.name}_{$language.id_lang}"{if $value.value == 1} id="{$input.name}_{$language.id_lang}_on"{else} id="{$input.name}_{$language.id_lang}_off"{/if} value="{$value.value}"{if $fields_value[$input.name][$language.id_lang] == $value.value} checked="checked"{/if}{if (isset($input.disabled) && $input.disabled) or (isset($value.disabled) && $value.disabled)} disabled="disabled"{/if} data-check="{$fields_value[$input.name][$language.id_lang]}"/>
							{strip}
								<label {if $value.value == 1} for="{$input.name}_{$language.id_lang}_on"{else} for="{$input.name}_off"{/if}>
								{if $value.value == 1}
									{l s='Yes' d='Admin.Global'}
								{else}
									{l s='No' d='Admin.Global'}
								{/if}
							</label>
							{/strip}
							{/foreach}
							<a class="slide-button btn"></a>
						</span>

					</div>
					{if $languages|count > 1}
						<div class="col-lg-2">
							<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
								{$language.iso_code}
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								{foreach from=$languages item=lang}
								<li><a href="javascript:hideOtherLanguage({$lang.id_lang});" tabindex="-1">{$lang.name}</a></li>
								{/foreach}
							</ul>
						</div>
					{/if}
				</div>
				{if $languages|count > 1}
					</div>
				{/if}
			{/foreach}

			<script>
				$(document).ready(function(){
					$('label[for^="AXEPTIO_COOKIES_LIVE_MODE"]').click(function(){
						$(this).prev().prop("checked", true);
					});
				});
			</script>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
