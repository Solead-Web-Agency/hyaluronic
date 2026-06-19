{*
* 2003-2018 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2018 Business Tech SARL
*}
<br/>
<div class="bootstrap">
	<div class="alert alert-danger" >
		{foreach from=$aErrors name=condition key=nKey item=aError}
		<h3>{$aError.msg|escape:'htmlall':'UTF-8'}</h3>
		{if $bDebug == true}
		<ol>
			{if !empty($aError.code)}<li>{l s='Error code' mod='gadwordstracking'} : {$aError.code|intval}</li>{/if}
			{if !empty($aError.file)}<li>{l s='Error file' mod='gadwordstracking'} : {$aError.file|escape:'htmlall':'UTF-8'}</li>{/if}
			{if !empty($aError.line)}<li>{l s='Error line' mod='gadwordstracking'} : {$aError.line|intval}</li>{/if}
			{if !empty($aError.context)}<li>{l s='Error context' mod='gadwordstracking'} : {$aError.context|escape:'htmlall':'UTF-8'}</li>{/if}
		</ol>
		{/if}
		{/foreach}
	</div>
</div>