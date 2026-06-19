{*
*
* Google merchant center Pro
*
* @author BusinessTech.fr
* @copyright Business Tech
*
*           ____    _______
*          |  _ \  |__   __|
*          | |_) |    | |
*          |  _ <     | |
*          | |_) |    | |
*          |____/     |_|
*
*}
<div class="bootstrap">
	{if !empty($bUpdate)}
		<div class="alert alert-success">{l s='Your matching with Google categories has been updated' mod='gmerchantcenterpro'}</div>
	{elseif !empty($aErrors)}
		{include file="`$sErrorInclude`"}
	{/if}
</div>