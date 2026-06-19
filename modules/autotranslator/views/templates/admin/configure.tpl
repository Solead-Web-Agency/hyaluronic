{*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="at-container{if $no_provider} no-provider{/if}">
	<div class="resource-settings">
		<div class="at-panel">{include file="./resource-settings.tpl"}</div>
	</div>
	<div class="api-settings">
		<div class="at-panel">{include file="./api-settings.tpl"}</div>
	</div>
	<div class="at-panel resource-list clear-both">
		{include file="./list-settings.tpl"}
		<div class="dynamic-list">
			<div class="dynamic-list-placeholder">{* placeholder will be replaced dynamically *}
				{if $no_provider}{l s='Please select translation provider' mod='autotranslator'}{/if}
			</div>
		</div>
	</div>
	<div class="module-info text-center clear-both">
		<div class="at-panel">{include file="./module-info.tpl"}</div>
	</div>
</div>
{* since 3.0.0 *}
