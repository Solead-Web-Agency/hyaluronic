{*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<form class="resource-settings-form">
	<label class="inline-block">{l s='Translate' mod='autotranslator'}</label>
	<select name="at_ct" class="inline-block resource-options">
		{foreach $content_types as $val => $name}
			<option value="{$val|escape:'html':'UTF-8'}"{if $current_ct == $val} selected{/if}>{$name|escape:'html':'UTF-8'}</option>
		{/foreach}
	</select>
	<div class="inline-block resource-fields">{* filled dynamically *}</div>
	<div class="at-panel-footer">
		<label class="override-label">
			<input type="checkbox" class="dont_overwrite_existing" name="dont_overwrite_existing"{if empty($overwrite_existing)} checked{/if}>
			{l s='Translate [1]only empty[/1] fields or fields with [1]same value[/1] as original' mod='autotranslator' tags=['<span class="b">']}
		</label>
	</div>
</form>
{* since 3.0.0 *}
