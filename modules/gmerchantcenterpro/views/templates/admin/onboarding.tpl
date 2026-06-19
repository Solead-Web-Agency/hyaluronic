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
<div id="api-settings-form-container" class="panel">
    <div id="api-settings-form">
        {if !$isError}
            <p>{l s='Already subscribed? Link the module with your subscription and start configuration.' mod='gmerchantcenterpro'}
            <br />{l s='If you haven\'t subscribed yet, log into your dashboard to take your subscription and go back here.' mod='gmerchantcenterpro'}</p>
		{/if}
		
		{if $bAlreadyToken}
            <p class="alert alert-warning">{l s='You are already connected with ' mod='gmerchantcenterpro'}</p>
        {/if}
        <div class="row">
            <div class="col-xs-6">
                <a class="btn btn-lg col-xs-4 text-center pull-right" href="{$generatedLink}" title="{$linkTitle}">{$linkTitle}</a>
            </div>
            <div class="col-xs-6">
                <a class="btn btn-lg col-xs-4 text-center pull-left" target="_blank" href="{$sLoginUrl}">{l s='Log in' mod='gmerchantcenterpro'}</a>
            </div>
    </div>
</div>