{*
* 2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*}

{*<div id="arcu-go-top">
    <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="angle-up" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-angle-up fa-w-10 fa-3x"><path fill="currentColor" d="M168.5 164.2l148 146.8c4.7 4.7 4.7 12.3 0 17l-19.8 19.8c-4.7 4.7-12.3 4.7-17 0L160 229.3 40.3 347.8c-4.7 4.7-12.3 4.7-17 0L3.5 328c-4.7-4.7-4.7-12.3 0-17l148-146.8c4.7-4.7 12.3-4.7 17 0z" class=""></path></svg>
</div>*}
{if $vkIntegrated}
    <script type="text/javascript" src="https://vk.com/js/api/openapi.js?157"></script>
    <!-- VK Widget -->
    {if !$isMobile}
    <style type="text/css">
        #vk_community_messages{
            {if $buttonConfig->position == 'right'}
                right: -10px !important;
            {else}
                left: -10px !important;
            {/if}
        }
    </style>
    {/if}
    <div id="vk_community_messages"></div>
{/if}
{if $jivosite}
    <style type="text/css">
        .globalClass_ET{
            display: none
        }
        .globalClass_ET.active{
            display: block
        }
    </style>
    <script src="//code.jivosite.com/widget.js" data-jv-id="{$liveChatConfig->jivosite_id|escape:'htmlall':'UTF-8'}" async></script>
{/if}
{if $phplive}
    <span style="color: #0000FF; text-decoration: underline; line-height: 0px !important; cursor: pointer; position: fixed; bottom: 0px; right: 20px; z-index: 20000000;" id="phplive_btn_1576807307"></span>
{/if}
{if $hubspot}
    <script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/{$liveChatConfig->hubspot_id|escape:'htmlall':'UTF-8'}.js"></script>
{/if}
{if $socialintents}
    <script src="//www.socialintents.com/api/socialintents.1.3.js#{$liveChatConfig->socialintents_id|escape:'htmlall':'UTF-8'}" async="async"></script>
{/if}
<style type="text/css">
    {if $phplive}
        #phplive_btn_1576807307_clone{
            display: none !important;
        }
    {/if}
    {if $paldesk}
        #paldesk-widget-btnframe{
            display: none;
        }
    {/if}
    {if $hubspot}
        #hubspot-messages-iframe-container{
            display: none !important;
        }
        #hubspot-messages-iframe-container.active{
            display: initial !important;
        }
    {/if}
    {if $facebookIntegrated}
        #fb-root{
            visibility: hidden;
        }
        #fb-root.active{
            visibility: visible;
        }
    {/if}
</style>
{if $skypeIntegrated}
    <style type="text/css">
        #arcontactus-skype iframe[seamless="seamless"].swcChat_lwc{
            display: none;
        }
        #arcontactus-skype.active iframe[seamless="seamless"].swcChat_lwc{
            display: block;
        }
    </style>
    <script src="https://swc.cdn.skype.com/sdk/v1/sdk.min.js"></script>
    <span 
        class="skype-chat" 
        id="arcontactus-skype"
        style="display: none"
        data-can-close=true
        data-can-collapse=true
        data-can-upload-file=true
        data-show-header=true
        data-entry-animation=true
        {if $liveChatConfig->skype_type == 'skype'}
            data-contact-id="{$liveChatConfig->skype_id|escape:'htmlall':'UTF-8'}" 
        {else}
            data-bot-id="{$liveChatConfig->skype_id|escape:'htmlall':'UTF-8'}"
        {/if}
        data-color-message="{$liveChatConfig->skype_message_color|escape:'htmlall':'UTF-8'}"
    ></span>
{/if}
{if $zaloIntegrated}
    <div id="ar-zalo-chat-widget">
        <div class="zalo-chat-widget" data-oaid="{$liveChatConfig->zalo_id|escape:'htmlall':'UTF-8'}" data-welcome-message="{$liveChatConfig->zalo_welcome[$id_lang]|escape:'htmlall':'UTF-8'}" data-autopopup="0" data-width="{$liveChatConfig->zalo_width|intval}" data-height="{$liveChatConfig->zalo_height|intval}"></div>
    </div>
    <script src="https://sp.zalo.me/plugins/sdk.js"></script>
{/if}
{if $tidioIntegrated}
    <style type="text/css">
        #tidio-chat{
            display: none;
        }
        #tidio-chat.active{
            display: block;
        }
    </style>
    {if $liveChatConfig->tidio_userinfo}
        <script>
            document.tidioIdentify = {
                email: '{$customer->email|escape:'htmlall':'UTF-8'}',
                name: "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}",
            };
        </script>
    {/if}
    <script src="//code.tidio.co/{$liveChatConfig->tidio_key|escape:'htmlall':'UTF-8'}.js"></script>
{/if}
{if $botmake}
    <style type="text/css">
        #_chatBubble{
            display: none !important;
        }
    </style>
    <script type="text/javascript"> let headID = document.getElementsByTagName("head")[0]; let newCss = document.createElement('link'); newCss.rel = 'stylesheet'; newCss.type = 'text/css'; newCss.href = "https://botmake.io/embed/{$liveChatConfig->botmake_id|escape:'htmlall':'UTF-8'}.css"; let newScript = document.createElement('script'); newScript.src = "https://botmake.io/embed/{$liveChatConfig->botmake_id|escape:'htmlall':'UTF-8'}.js"; newScript.type = 'text/javascript'; headID.appendChild(newScript); headID.appendChild(newCss); </script> 
{/if}
<script>
    var lcpWidgetInterval;
    var closePopupTimeout;
    var lzWidgetInterval;
    var paldeskInterval;
    var hubspotInterval;
    var arcuOptions;
    {if ($promptConfig->enable_prompt && $messagesCount)}
        var arCuMessages = {$messages nofilter};
        var arCuLoop = {if $promptConfig->loop}true{else}false{/if};
        var arCuCloseLastMessage = {if $promptConfig->close_last}true{else}false{/if};
        var arCuPromptClosed = false;
        var _arCuTimeOut = null;
        var arCuDelayFirst = {$promptConfig->first_delay|intval};
        var arCuTypingTime = {$promptConfig->typing_time|intval};
        var arCuMessageTime = {$promptConfig->message_time|intval};
        var arCuClosedCookie = 0;
    {/if}
    var arcItems = [];
    {if $tawkToIntegrated}
        var tawkToSiteID = '{$liveChatConfig->tawk_to_site_id[$id_lang]|escape:'htmlall':'UTF-8'}';
        var tawkToWidgetID = '{$liveChatConfig->tawk_to_widget[$id_lang]|escape:'htmlall':'UTF-8'}';
        {literal}var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();{/literal}
    {/if}
    window.addEventListener('load', function(){
        jQuery('#arcontactus').remove();
        var $arcuWidget = jQuery('<div>', {
            id: 'arcontactus'
        });
        jQuery('body').append($arcuWidget);
        {if $promptConfig->show_after_close != '-1'}
            arCuClosedCookie = arCuGetCookie('arcu-closed');
        {/if}
        jQuery('#arcontactus').on('arcontactus.init', function(){
            jQuery('#arcontactus').addClass('arcuAnimated').addClass('{$buttonConfig->animation|escape:'htmlall':'UTF-8'}');
            setTimeout(function(){
                jQuery('#arcontactus').removeClass('{$buttonConfig->animation|escape:'htmlall':'UTF-8'}');
            }, 1000);
            var $key = $('<input>', {
                type: 'hidden',
                name: 'key',
                value: '{$securityKey|escape:'htmlall':'UTF-8'}'
            });
            jQuery('#arcontactus .callback-countdown-block-phone form').append($key);
            {if $popupConfig->phone_mask_on}
                jQuery.mask.definitions['#'] = "[0-9]";
                jQuery('#arcontactus .arcontactus-message-callback-phone').mask('{$popupConfig->phone_mask[$id_lang]|escape:'htmlall':'UTF-8'}');
            {/if}
        });
        {if ($promptConfig->enable_prompt && $messagesCount)}
            jQuery('#arcontactus').on('arcontactus.init', function(){
                if (arCuClosedCookie){
                    return false;
                }
                arCuShowMessages();
            });
            jQuery('#arcontactus').on('arcontactus.openMenu', function(){
                clearTimeout(_arCuTimeOut);
                if (!arCuPromptClosed){
                    arCuPromptClosed = true;
                    jQuery('#arcontactus').contactUs('hidePrompt');
                }
            });

            jQuery('#arcontactus').on('arcontactus.hidePrompt', function(){
                clearTimeout(_arCuTimeOut);
                if (arCuClosedCookie != "1"){
                    arCuClosedCookie = "1";
                    {if $promptConfig->show_after_close != '-1'}
                        arCuPromptClosed = true;
                        {if $promptConfig->show_after_close == '0'}
                            arCuCreateCookie('arcu-closed', 1, 0);
                        {else}
                            arCuCreateCookie('arcu-closed', 1, {$promptConfig->show_after_close|intval / 1440});
                        {/if}
                    {/if}
                }
            });
        {/if}
        {if ($popupConfig->close_timeout)}
            jQuery('#arcontactus').on('arcontactus.successCallbackRequest', function(){
                closePopupTimeout = setTimeout(function(){
                    jQuery('#arcontactus').contactUs('closeCallbackPopup');
                }, {$popupConfig->close_timeout|intval * 1000});
            });
            jQuery('#arcontactus').on('arcontactus.closeCallbackPopup', function(){
                clearTimeout(closePopupTimeout);
            })
        {/if}
        {foreach $items as $item}
            {if ($item.js && $item.type == 3)}
                jQuery('#arcontactus').on('arcontactus.successCallbackRequest', function(){
                    {$item.js nofilter}
                });
            {/if}
            var arcItem = {
            };
            {if ($item['id'])}
                arcItem.id = '{$item['id']|escape:'htmlall':'UTF-8'}';
            {/if}
            {if $item.type == 1}
                arcItem.onClick = function(e){
                    e.preventDefault();
                    jQuery('#arcontactus').contactUs('closeMenu');
                    {if $item.integration == 'tawkto'}
                        if (typeof Tawk_API == 'undefined'){
                            console.error('Tawk.to integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        clearInterval(tawkToHideInterval);
                        Tawk_API.showWidget();
                        Tawk_API.maximize();
                        tawkToInterval = setInterval(function(){
                            checkTawkIsOpened();
                        }, 100);
                    {elseif $item.integration == 'crisp'}
                        if (typeof $crisp == 'undefined'){
                            console.error('Crisp integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        $crisp.push(["do", "chat:show"]);
                        $crisp.push(["do", "chat:open"]);
                    {elseif $item.integration == 'intercom'}
                        if (typeof Intercom == 'undefined'){
                            console.error('Intercom integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        Intercom('show');
                    {elseif $item.integration == 'facebook'}
                        if (typeof FB == 'undefined' || typeof FB.CustomerChat == 'undefined'){
                            console.error('Facebook customer chat integration is disabled in module configuration');
                            return false;
                        }
                        clearInterval(hideCustomerChatInterval);
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#ar-fb-chat').addClass('active');
                        jQuery('.fb_customer_chat_bubble_animated_no_badge,.fb_customer_chat_bubble_animated_no_badge .fb_dialog_content').addClass('active');
                        setTimeout(function(){
                            FB.CustomerChat.show(true);
                            FB.CustomerChat.showDialog();
                        }, 500);
                    {elseif $item.integration == 'vk'}
                        if (typeof vkMessagesWidget == 'undefined'){
                            console.error('VK chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        vkMessagesWidget.expand();
                    {elseif $item.integration == 'zopim'}
                        {if $isZendesk}
                            if (typeof zE == 'undefined'){
                                console.error('Zendesk integration is disabled in module configuration');
                                return false;
                            }
                            zE('webWidget', 'show');
                            zE('webWidget', 'open');
                        {else}
                            if (typeof $zopim == 'undefined'){
                                console.error('Zendesk integration is disabled in module configuration');
                                return false;
                            }
                            $zopim.livechat.window.show();
                        {/if}
                        jQuery('#arcontactus').contactUs('hide');
                    {elseif $item.integration == 'skype'}
                        if (typeof SkypeWebControl == 'undefined'){
                            console.error('Skype integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus-skype').show().addClass('active');
                        SkypeWebControl.SDK.Chat.showChat();
                        SkypeWebControl.SDK.Chat.startChat({
                            ConversationId: '{$liveChatConfig->skype_id|escape:'htmlall':'UTF-8'}',
                            ConversationType: 'agent'
                        });
                        skypeWidgetInterval = setInterval(function(){
                            checkSkypeIsOpened();
                        }, 100);
                        jQuery('#arcontactus').contactUs('hide');
                    {elseif $item.integration == 'zalo'}
                        if (typeof ZaloSocialSDK == 'undefined'){
                            console.error('Zalo integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#ar-zalo-chat-widget').addClass('active');
                        ZaloSocialSDK.openChatWidget();
                        zaloWidgetInterval = setInterval(function(){
                            checkZaloIsOpened();
                        }, 100);
                    {elseif $item.integration == 'lhc'}
                        if (typeof $_LHC == 'undefined'){
                            console.error('Live Helper Chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#lhc_container_v2').addClass('active');
                        $_LHC.attributes.mainWidget.show();
                    {elseif $item.integration == 'smartsupp'}
                        if (typeof smartsupp == 'undefined'){
                            console.error('Smartsupp chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#chat-application').addClass('active');
                        smartsupp('chat:show');
                        smartsupp('chat:open');
                        ssInterval = setInterval(function(){
                            checkSSIsOpened();
                        }, 100);
                    {elseif $item.integration == 'livechat'}
                        if (typeof LC_API == 'undefined'){
                            console.error('Live Chat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        LC_API.open_chat_window();
                    {elseif $item.integration == 'tidio'}
                        if (typeof tidioChatApi == 'undefined'){
                            console.error('Tidio integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        tidioChatApi.show();
                        tidioChatApi.open();
                        jQuery('#tidio-chat').addClass('active');
                    {elseif $item.integration == 'livechatpro'}
                        if (typeof phpLiveChat == 'undefined'){
                            console.error('Live Chat Pro integration is disabled in module configuration');
                            return false;
                        }
                        {if !$isMobile}
                            jQuery('#arcontactus').contactUs('hide');
                        {/if}
                        jQuery('#customer-chat-iframe').addClass('active');
                        setTimeout(function(){
                            lcpWidgetInterval = setInterval(function(){
                                checkLCPIsOpened();
                            }, 100);
                        }, 500);
                        phpLiveChat.show();
                    {elseif $item.integration == 'livezilla'}
                        if (typeof OverlayChatWidgetV2 == 'undefined'){
                            console.error('Live Zilla integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jQuery('#lz_overlay_wm').addClass('active');
                        OverlayChatWidgetV2.Show();
                        lzWidgetInterval = setInterval(function(){
                            checkLZIsOpened();
                        }, 100);
                    {elseif $item.integration == 'jivosite'}
                        if (typeof jivo_api == 'undefined'){
                            console.error('Jivosite integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        jivo_api.open();
                    {elseif $item.integration == 'zoho'}
                        if (typeof $zoho == 'undefined'){
                            console.error('Zoho SalesIQ integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        $zoho.salesiq.floatwindow.visible('show');
                    {elseif $item.integration == 'fc'}
                        if (typeof fcWidget == 'undefined'){
                            console.error('FreshChat integration is disabled in module configuration');
                            return false;
                        }
                        jQuery('#arcontactus').contactUs('hide');
                        window.fcWidget.show();
                        window.fcWidget.open();
                    {elseif $item.integration == 'phplive'}
                        phplive_launch_chat_1();
                        jQuery('#arcontactus').contactUs('hide');
                    {elseif $item.integration == 'paldesk'}
                        window.BeeBeeate.widget.openChatWindow();
                        jQuery('#arcontactus').contactUs('hide');
                        paldeskInterval = setInterval(function(){
                            checkPaldeskIsOpened();
                        }, 100);
                    {elseif $item.integration == 'hubspot'}
                        window.HubSpotConversations.widget.open();
                        jQuery('#hubspot-messages-iframe-container').addClass('active');
                        jQuery('#arcontactus').contactUs('hide');
                        hubspotInterval = setInterval(function(){
                            checkHubspotIsOpened();
                        }, 200);
                    {elseif $item.integration == 'socialintents'}
                        SI_API.showTab();
                        SI_API.showPopup();
                        jQuery('#arcontactus').contactUs('hide');
                    {elseif $item.integration == 'botmake'}
                        jQuery('#chatWindow').show();
                        jQuery('#arcontactus').contactUs('hide');
                    {/if}
                    {if $item.js}
                        {$item.js nofilter}
                    {/if}
                }
            {elseif $item.js}
                arcItem.onClick = function(e){
                    {if $item.type == 2}
                        e.preventDefault();
                    {/if}
                    {$item['js'] nofilter}
                }
            {/if}
            arcItem.class = '{$item.class|escape:'htmlall':'UTF-8'}';
            arcItem.title = "{$item.title nofilter}"; {* Escaping can beak non-latin characters *}
            {if ($item['subtitle'])}
                arcItem.subTitle = "{$item.subtitle nofilter}"; {* Escaping can beak non-latin characters *}
            {/if}
            arcItem.icon = '{$item.icon nofilter}';
            arcItem.noContainer = {$item.no_container|intval};
            arcItem.href = '{if $item.type == '3'}callback{elseif $item.type == '0'}{$item.href nofilter}{/if}';
            arcItem.target = '{$item.target|escape:'htmlall':'UTF-8'}';
            arcItem.color = '{$item.color|escape:'htmlall':'UTF-8'}';
            {if $item.enable_qr and !$isMobile}
            arcItem.addons = [
                {
                    icon: '{$path|escape:'htmlall':'UTF-8'}views/img/qr.svg',
                    href: 'javascript:void(0);',
                    class: 'arcu-qr-addon',
                    color: '#000000',
                    target: '_self',
                    onClick: function(){
                        arCuShowQRCode('{$item.qr_link nofilter}', "{$item.qr_title nofilter}");
                        return false;
                    }
                },
            ];
            {/if}
            arcItems.push(arcItem);
        {/foreach}
        arcuOptions = {
            drag: {if $buttonConfig->drag}true{else}false{/if},
            mode: '{$mode|escape:'htmlall':'UTF-8'}',
            align: '{$buttonConfig->position|escape:'htmlall':'UTF-8'}',
            reCaptcha: {if $popupConfig->recaptcha}true{else}false{/if},
            reCaptchaKey: '{$popupConfig->key|escape:'htmlall':'UTF-8'}',
            countdown: {$popupConfig->timeout|intval},
            theme: '{$buttonConfig->button_color|escape:'htmlall':'UTF-8'}',
            {if ($buttonConfig->button_icon_type == 'builtin')}
                {if $buttonIcon}
                    buttonIcon: '{$buttonIcon nofilter}',
                {/if}
            {elseif ($buttonConfig->button_icon_type == 'fa')}
                buttonIcon: '{$buttonConfig->button_icon_svg nofilter}',
            {elseif ($buttonConfig->button_icon_type == 'uploaded')}
                buttonIcon: '<img src="{$uploadsUrl|escape:'htmlall':'UTF-8'}{$buttonConfig->button_icon_img nofilter}" />',
            {/if}
            {if $menuConfig->menu_header_on}
                showMenuHeader: true,
                menuHeaderText: "{$menuConfig->menu_header[$id_lang]|escape:'htmlall':'UTF-8'}",
            {/if}
            {if $menuConfig->header_close}
                showHeaderCloseBtn: true,
            {else}
                showHeaderCloseBtn: false,
            {/if}
            {if ($menuConfig->header_close_bg)}
                headerCloseBtnBgColor: '{$menuConfig->header_close_bg|escape:'htmlall':'UTF-8'}',
            {/if}
            {if ($buttonConfig->text[$id_lang])}
                buttonText: "{$buttonConfig->text[$id_lang] nofilter}",
            {else}
                buttonText: false,
            {/if}
            itemsIconType: '{$menuConfig->item_style|escape:'htmlall':'UTF-8'}',
            buttonSize: '{$buttonConfig->button_size|escape:'htmlall':'UTF-8'}',
            buttonIconSize: {$buttonConfig->button_icon_size|intval},
            menuSize: '{$menuConfig->menu_size|escape:'htmlall':'UTF-8'}',
            phonePlaceholder: "{$popupConfig->phone_placeholder[$id_lang]|escape:'htmlall':'UTF-8'}",
            callbackSubmitText: "{$popupConfig->btn_title[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            errorMessage: "{$popupConfig->fail_message[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            callProcessText: "{$popupConfig->proccess_message[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            callSuccessText: "{$popupConfig->success_message[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            iconsAnimationSpeed: {$buttonConfig->icon_speed|intval},
            iconsAnimationPause: {$buttonConfig->icon_animation_pause|intval},
            callbackFormText: "{$popupConfig->message[$id_lang]|replace:"\r\n":""|replace:"\n":"" nofilter}",
            items: arcItems,
            ajaxUrl: '{$ajaxUrl nofilter}', {* URL generated by Link object, no escape necessary. Escaping will break functionality *}
            {if ($promptConfig->prompt_position)}
                promptPosition: '{$promptConfig->prompt_position|escape:'htmlall':'UTF-8'}',
            {/if}
            {if $menuConfig->menu_style == 1}
                style: '{$menuConfig->sidebar_animation|escape:'htmlall':'UTF-8'}',
            {else}
                {if $menuConfig->popup_animation}
                    popupAnimation: '{$menuConfig->popup_animation|escape:'htmlall':'UTF-8'}',
                {/if}
                style: '',
            {/if}
            {if $menuConfig->items_animation}
                itemsAnimation: '{$menuConfig->items_animation|escape:'htmlall':'UTF-8'}',
            {/if}
            callbackFormFields: {
                {if ($popupConfig->name)}
                name: {
                    name: 'name',
                    enabled: true,
                    required: {if $popupConfig->name_required}true{else}false{/if},
                    type: 'text',
                    value: 112,
                    label: "{$popupConfig->name_title[$id_lang]|escape:'htmlall':'UTF-8'}",
                    placeholder: "{$popupConfig->name_placeholder[$id_lang]|escape:'htmlall':'UTF-8'}",
                    {if ($popupConfig->name_validation && $popupConfig->name_max_len)}
                        maxlength: {$popupConfig->name_max_len|intval},
                    {/if}
                },
                {/if}
                {if ($popupConfig->email_field)}
                email: {
                    name: 'email',
                    enabled: true,
                    required: {if $popupConfig->email_required}true{else}false{/if},
                    type: 'email',
                    label: "{$popupConfig->email_title[$id_lang]|escape:'htmlall':'UTF-8'}",
                    placeholder: "{$popupConfig->email_placeholder[$id_lang]|escape:'htmlall':'UTF-8'}",
                },
                {/if}
                phone: {
                    name: 'phone',
                    enabled: true,
                    required: true,
                    type: 'tel',
                    label: '',
                    placeholder: "{$popupConfig->phone_placeholder[$id_lang]|escape:'htmlall':'UTF-8'}"
                },
                {if $popupConfig->gdpr}
                gdpr: {
                    name: 'gdpr',
                    enabled: true,
                    required: true,
                    type: 'checkbox',
                    label: "{$popupConfig->gdpr_title[$id_lang]|escape:'htmlall':'UTF-8'}",
                }
                {/if}
            },
        };
        jQuery('#arcontactus').contactUs(arcuOptions);
        {if $tawkToIntegrated}
            window.addEventListener('unreadMessagesCountChanged', function(e){
                jQuery('#arcontactus').contactUs('hide');
                clearInterval(tawkToHideInterval);
                Tawk_API.showWidget();
                Tawk_API.maximize();
                tawkToInterval = setInterval(function(){
                    checkTawkIsOpened();
                }, 100);
            });
            Tawk_API.onChatMinimized = function(){
                Tawk_API.hideWidget();
                jQuery('#arcontactus').contactUs('show');
            };
            Tawk_API.onChatEnded = function(){
                Tawk_API.hideWidget();
                jQuery('#arcontactus').contactUs('show');
            };
            Tawk_API.onChatStarted = function(){
                jQuery('#arcontactus').contactUs('hide');
                clearInterval(tawkToHideInterval);
                Tawk_API.showWidget();
                Tawk_API.maximize();
                tawkToInterval = setInterval(function(){
                    checkTawkIsOpened();
                }, 100);
            };
            {if $liveChatConfig->tawk_to_userinfo && $customer->id}
                Tawk_API.visitor = {
                    name : "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}",
                    email : '{$customer->email|escape:'htmlall':'UTF-8'}'
                };
            {/if}
            (function(){
                var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
                s1.async=true;
                {if $liveChatConfig->tawk_to_custom_script}
                    s1.src='{$path nofilter}views/js/tawkto.custom.js';
                {else}
                    s1.src='https://embed.tawk.to/{$liveChatConfig->tawk_to_site_id[$id_lang]|escape:'htmlall':'UTF-8'}/{$liveChatConfig->tawk_to_widget[$id_lang]|escape:'htmlall':'UTF-8'}';
                {/if}
                
                s1.charset='UTF-8';
                s1.setAttribute('crossorigin','*');
                s0.parentNode.insertBefore(s1,s0);
            })();
        {/if}
        {if $facebookIntegrated}
            var hideCustomerChatInterval;
            FB.Event.subscribe('customerchat.dialogShow', function(){
                jQuery('#ar-fb-chat').addClass('active');
                jQuery('#fb-root').addClass('active');
                jQuery('#arcontactus').contactUs('hide');
                jQuery('.fb_customer_chat_bubble_animated_no_badge,.fb_customer_chat_bubble_animated_no_badge .fb_dialog_content').addClass('active');
            });
            FB.Event.subscribe('customerchat.dialogHide', function(){
                jQuery('#ar-fb-chat').removeClass('active');
                jQuery('#fb-root').removeClass('active');
                jQuery('#arcontactus').contactUs('show');
                jQuery('.fb_customer_chat_bubble_animated_no_badge,.fb_customer_chat_bubble_animated_no_badge .fb_dialog_content').removeClass('active');
                FB.CustomerChat.hide();
            });
            {*FB.Event.subscribe('customerchat.load', function(){
                hideCustomerChatInterval = setInterval(function(){
                    if ($('.fb_dialog').is(':visible')) {
                        FB.CustomerChat.hide();
                        clearInterval(hideCustomerChatInterval);
                    }
                }, 100);
            });*}
        {/if}
        {if $tidioIntegrated}
            function onTidioChatApiReady(){
                window.tidioChatApi.hide();
                jQuery('#tidio-chat').removeClass('active');
                setTimeout(function(){
                    jQuery('#arcontactus').contactUs('show');
                }, 1000);
            }
            function onTidioChatClose(){
                window.tidioChatApi.hide();
                jQuery('#tidio-chat').removeClass('active');
                jQuery('#arcontactus').contactUs('show');
            }
            if (window.tidioChatApi) {
                window.tidioChatApi.on("ready", onTidioChatApiReady);
                window.tidioChatApi.on("close", onTidioChatClose);
            }else{
                document.addEventListener("tidioChat-ready", onTidioChatApiReady);
                document.addEventListener("tidioChat-close", onTidioChatClose);
            }
        {/if}
        {if $paldesk}
            window.BeeBeeate.widget.closeChatWindow(function(){
                jQuery('#arcontactus').contactUs('show');
            }, function(error) {

            });
        {/if}
    });
    {if $intercomIntegrated}
        window.intercomSettings = {
            app_id: "{$liveChatConfig->intercom_app_id|escape:'htmlall':'UTF-8'}",
            alignment: 'right',     
            horizontal_padding: 20, 
            vertical_padding: 20
        };
        (function() {
            var w = window;
            var ic = w.Intercom;
            if (typeof ic === "function") {
                ic('reattach_activator');
                ic('update', intercomSettings);
            } else {
                var d = document;
                var i = function() {
                    i.c(arguments)
                };
                i.q = [];
                i.c = function(args) {
                    i.q.push(args)
                };
                w.Intercom = i;

                function l() {
                    var s = d.createElement('script');
                    s.type = 'text/javascript';
                    s.async = true;
                    s.src = 'https://widget.intercom.io/widget/{$liveChatConfig->intercom_app_id|escape:'htmlall':'UTF-8'}';
                    var x = d.getElementsByTagName('script')[0];
                    x.parentNode.insertBefore(s, x);
                }
                if (w.attachEvent) {
                    w.attachEvent('onload', l);
                } else {
                    w.addEventListener('load', l, false);
                }
            }
        })();
        Intercom('onHide', function(){
            jQuery('#arcontactus').contactUs('show');
        });
    {/if}
    {if $vkIntegrated}
        var vkMessagesWidget = VK.Widgets.CommunityMessages("vk_community_messages", {$liveChatConfig->vk_page_id|escape:'htmlall':'UTF-8'}, {
            disableButtonTooltip: 1,
            welcomeScreen: 0,
            expanded: 0,
            buttonType: 'no_button',
            widgetPosition: '{$buttonConfig->position|escape:'htmlall':'UTF-8'}'
        });
    {/if}
    {if $ssIntegrated}
        {literal}var _smartsupp = _smartsupp || {};{/literal}
        _smartsupp.key = '{$liveChatConfig->ss_key|escape:'htmlall':'UTF-8'}';
        window.smartsupp||(function(d) {
          var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
          s=d.getElementsByTagName('script')[0];c=d.createElement('script');
          c.type='text/javascript';c.charset='utf-8';c.async=true;
          c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
        })(document);
        {if $liveChatConfig->ss_userinfo and $customer->id}
            smartsupp('name', "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}");
            smartsupp('email', '{$customer->email|escape:'htmlall':'UTF-8'}');
            smartsupp('variables', {
                accountId: {
                    label: 'Customer ID',
                    value: {$customer->id|intval}
                }
            });
        {/if}
        var ssInterval;
        function checkSSIsOpened(){
            if (jQuery('#chat-application').height() < 300){
                jQuery('#arcontactus').contactUs('show');
                clearInterval(ssInterval);
                jQuery('#chat-application').removeClass('active');
            }
        }
        smartsupp('on', 'message', function(model, message) {
            if (message.type == 'agent') {
                jQuery('#chat-application').addClass('active');
                smartsupp('chat:open');
                jQuery('#arcontactus').contactUs('hide');
                setTimeout(function(){
                    ssInterval = setInterval(function(){
                        checkSSIsOpened();
                    }, 100);
                }, 500);
                
            }
        });
        
        function smartsuppOnMessage() {
            setInterval(function(){
                if (jQuery('#chat-application').height() > 200 && !jQuery('#chat-application').hasClass('active')) {
                    // new message received
                    clearInterval(ssInterval);
                    jQuery('#chat-application').addClass('active');
                    smartsupp('chat:open');
                    jQuery('#arcontactus').contactUs('hide');
                    setTimeout(function(){
                        ssInterval = setInterval(function(){
                            checkSSIsOpened();
                        }, 100);
                    }, 500);
                }
            }, 200);
        }
        
        smartsupp('chat:close');
        smartsuppOnMessage();
        
    {/if}
    {if $tawkToIntegrated}
        var tawkToInterval;
        var tawkToHideInterval;
        
        function tawkToHide(){
            tawkToHideInterval = setInterval(function(){
                if (typeof Tawk_API.hideWidget != 'undefined'){
                    Tawk_API.hideWidget();
                }
            }, 100);
        }
        
        function checkTawkIsOpened(){
            if (Tawk_API.isChatMinimized()){ 
                Tawk_API.hideWidget();
                jQuery('#arcontactus').contactUs('show');
                tawkToHide();
                clearInterval(tawkToInterval);
            }
        }
        tawkToHide();
    {/if}
    {if $zaloIntegrated}
        var zaloWidgetInterval;
        function checkZaloIsOpened(){
            if (jQuery('#ar-zalo-chat-widget>div').height() < 100){ 
                jQuery('#ar-zalo-chat-widget').removeClass('active');
                jQuery('#arcontactus').contactUs('show');
                clearInterval(zaloWidgetInterval);
            }
        }
    {/if}
    {if $lhcIntegrated}
        var LHC_API = LHC_API||{};
        LHC_API.args = {
            mode:'widget',
            lhc_base_url:'{$liveChatConfig->lhc_uri nofilter}',
            wheight:450,
            wwidth:350,
            pheight:520,
            pwidth:500,
            leaveamessage:true,
            department:[
                {$liveChatConfig->lhc_department|intval}
            ],
            check_messages: true
        };
        (function() {
            var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
            var date = new Date();po.src = '{$liveChatConfig->lhc_uri nofilter}design/defaulttheme/js/widgetv2/index.js?'+(""+date.getFullYear() + date.getMonth() + date.getDate());
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
        })();
        setTimeout(() => {
            $_LHC.eventListener.addListener('closeWidget', () => {
                $_LHC.attributes.mainWidget.hide();
                jQuery('#arcontactus').contactUs('show');
                jQuery('#lhc_container_v2').removeClass('active');
            });
            $_LHC.eventListener.addListener('showWidget', () => {
                jQuery('#arcontactus').contactUs('hide');
                jQuery('#lhc_container_v2').addClass('active');
            });
        }, 500);
    {/if}
    {if $lcIntegrated}
        {literal}window.__lc = window.__lc || {};{/literal}
        window.__lc.license = {$liveChatConfig->lc_key|escape:'htmlall':'UTF-8'};
        (function() {
          var lc = document.createElement('script'); lc.type = 'text/javascript'; lc.async = true;
          lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(lc, s);
        })();
        {literal}var LC_API = LC_API || {};{/literal}
        var livechat_chat_started = false;
        LC_API.on_before_load = function() {
            LC_API.hide_chat_window();
        };
        LC_API.on_after_load = function() {
            LC_API.hide_chat_window();
            {if $liveChatConfig->lc_userinfo && $customer->id}
                LC_API.set_visitor_name('{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}');
                LC_API.set_visitor_email('{$customer->email|escape:'htmlall':'UTF-8'}');
            {/if}
        };
        LC_API.on_chat_window_minimized = function(){
            LC_API.hide_chat_window();
            jQuery('#arcontactus').contactUs('show');
        };
        LC_API.on_message = function(data) {
            LC_API.open_chat_window();
            jQuery('#arcontactus').contactUs('hide');
        };
        LC_API.on_chat_started = function() {
            livechat_chat_started = true;
        };
    {/if}
    {if $skypeIntegrated}
        var skypeWidgetInterval;
        function checkSkypeIsOpened(){
            if (jQuery('#arcontactus-skype iframe').hasClass('close-chat')){ 
                jQuery('#arcontactus').contactUs('show');
                jQuery('#arcontactus-skype').hide().removeClass('active');
                clearInterval(skypeWidgetInterval);
            }
        }
    {/if}
    {if $lcp}
        function checkLCPIsOpened(){
            if (parseInt(jQuery('#customer-chat-iframe').css('bottom')) < -300){ 
                jQuery('#arcontactus').contactUs('show');
                jQuery('#customer-chat-iframe').removeClass('active');
                clearInterval(lcpWidgetInterval);
            }
        }
    {/if}
    {if $liveZilla}
        function checkLZIsOpened(){
            if (!jQuery('#lz_overlay_chat').is(':visible')){ 
                jQuery('#arcontactus').contactUs('show');
                jQuery('#lz_overlay_wm').removeClass('active');
                clearInterval(lzWidgetInterval);
            }
        }
    {/if}
    {if $lcp}
    (function(d,t,u,s,e){
        e=d.getElementsByTagName(t)[0];s=d.createElement(t);s.src=u;s.async=1;e.parentNode.insertBefore(s,e);
    })(document,'script','{$liveChatConfig->lcp_uri|escape:'htmlall':'UTF-8'}');
    {/if}
    {if $jivosite}
        {if ($liveChatConfig->jivosite_userinfo && $customer->id)}
            function jivo_onLoadCallback(state) {
                jivo_api.setContactInfo({
                    "name": "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}",
                    "email": "{$customer->email|escape:'htmlall':'UTF-8'}"
                }); 
            }
        {/if}
        function jivo_onChangeState(state) {
            if (state == 'chat' || state == 'offline' || state == 'introduce') {
                jQuery('.globalClass_ET').addClass('active');
                jQuery('#arcontactus').contactUs('hide');
            }
            if (state == 'call' || state == 'chat/call') {
                jQuery('.globalClass_ET').addClass('active');
                jQuery('#arcontactus').contactUs('hide');
            }
            if (state == 'label' || state == 'chat/min'){
                jQuery('.globalClass_ET').removeClass('active');
                jQuery('#arcontactus').contactUs('show');
            }
        } 
    {/if}
    {if $zoho}
        {literal}var $zoho=$zoho || {};
        $zoho.salesiq = $zoho.salesiq || {widgetcode:{/literal}"{$liveChatConfig->zoho_id|escape:'htmlall':'UTF-8'}"{literal}, values:{},ready:function(){}};var d=document;s=d.createElement("script");s.type="text/javascript";s.id="zsiqscript";s.defer=true;s.src="https://salesiq.zoho.eu/widget";t=d.getElementsByTagName("script")[0];t.parentNode.insertBefore(s,t);d.write("<div id='zsiqwidget'></div>");{/literal}
        $zoho.salesiq.ready=function(){
            $zoho.salesiq.floatbutton.visible("hide");
            $zoho.salesiq.floatwindow.minimize(function(){
                jQuery('#arcontactus').contactUs('show');
            });
            $zoho.salesiq.floatwindow.close(function(){
                jQuery('#arcontactus').contactUs('show');
            });
        }
    {/if}
    {if $freshChat}
        function initFreshChat() {
            window.fcWidget.init({
                token: "{$liveChatConfig->fc_token|escape:'htmlall':'UTF-8'}",
                host: "{$liveChatConfig->fc_host|escape:'htmlall':'UTF-8'}"
            });
            window.fcWidget.on("widget:closed", function(resp) {
                jQuery('#arcontactus').contactUs('show');
            });
            {*window.fcWidget.on("unreadCount:notify", function(resp) {
                jQuery('#arcontactus').contactUs('hide');
                window.fcWidget.show();
                window.fcWidget.open();
            });*}
            {if $liveChatConfig->fc_userinfo && $customer->id}
                window.fcWidget.user.setProperties({
                    firstName: "{$customer->firstname|escape:'htmlall':'UTF-8'}",
                    lastName: "{$customer->lastname|escape:'htmlall':'UTF-8'}",
                    email: "{$customer->email|escape:'htmlall':'UTF-8'}"
                });
            {/if}
        }
        
        {literal}function initialize(i,t){var e;i.getElementById(t)?initFreshChat():((e=i.createElement("script")).id=t,e.async=!0,e.src={/literal}"{$liveChatConfig->fc_host|escape:'htmlall':'UTF-8'}{literal}/js/widget.js",e.onload=initFreshChat,i.head.appendChild(e))}function initiateCall(){initialize(document,"freshchat-js-sdk")}window.addEventListener?window.addEventListener("load",initiateCall,!1):window.attachEvent("load",initiateCall,!1);{/literal}
    {/if}
    {if $phplive}
        {if $liveChatConfig->phplive_userinfo}
                var phplive_v = new Object ;
                phplive_v["name"] = "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}" ;
                phplive_v["email"] = "{$customer->email|escape:'htmlall':'UTF-8'}" ;
        {/if}
        (function() {
            var phplive_href = encodeURIComponent( location.href ) ;
            var phplive_e_1576807307 = document.createElement("script") ;
            phplive_e_1576807307.type = "text/javascript" ;
            phplive_e_1576807307.async = true ;
            phplive_e_1576807307.src = "{$liveChatConfig->phplive_src|escape:'htmlall':'UTF-8'}?v=1%7C1576807307%7C2%7C&r="+phplive_href;
            document.getElementById("phplive_btn_1576807307").appendChild( phplive_e_1576807307 ) ;
            if ( [].filter ) { document.getElementById("phplive_btn_1576807307").addEventListener( "click", function(){ phplive_launch_chat_1() } ) ; } else { document.getElementById("phplive_btn_1576807307").attachEvent( "onclick", function(){ phplive_launch_chat_1() } ) ; }
        })() ;
        function phplive_callback_minimize() {
                jQuery('#arcontactus').contactUs('show');
                phplive_embed_window_close(1);
        }
        function phplive_callback_close() {
                jQuery('#arcontactus').contactUs('show');
        }
    {/if}
    {if $paldesk}
            {if $liveChatConfig->paldesk_userinfo && $customer->id}
                custom_user_data = {
                    externalId: "{$customer->id|escape:'htmlall':'UTF-8'}",
                    email: "{$customer->email|escape:'htmlall':'UTF-8'}",
                    firstname: "{$customer->firstname|escape:'htmlall':'UTF-8'}",
                    lastname: "{$customer->lastname|escape:'htmlall':'UTF-8'}"
                };
            {/if}
            if("undefined"!==typeof requirejs){
                window.onload=function(e){
                    requirejs(["https://paldesk.io/api/widget-client?apiKey={$liveChatConfig->paldesk_key|escape:'htmlall':'UTF-8'}"],function(e){
                        "undefined"!==typeof custom_user_data&&(beebeeate_config.user_data=custom_user_data),BeeBeeate.widget.new(beebeeate_config)
                    })
                };
            }else{
                var s=document.createElement("script");s.async=!0,s.src="https://paldesk.io/api/widget-client?apiKey={$liveChatConfig->paldesk_key|escape:'htmlall':'UTF-8'}",s.onload=function(){
                    "undefined"!==typeof custom_user_data&&(beebeeate_config.user_data=custom_user_data),BeeBeeate.widget.new(beebeeate_config)
                };
                if(document.body){
                    document.body.appendChild(s)
                }else if(document.head){
                    document.head.appendChild(s)
                }
            }

            function checkPaldeskIsOpened() {
                if (jQuery('#paldesk-widget-mainframe').height() < 100){ 
                    jQuery('#arcontactus').contactUs('show');
                    clearInterval(paldeskInterval);
                }
            }
    {/if}
    {if $hubspot}
        function checkHubspotIsOpened(){
            if (jQuery('#hubspot-messages-iframe-container').height() < 200){ 
                jQuery('#hubspot-messages-iframe-container').removeClass('active');
                jQuery('#arcontactus').contactUs('show');
                clearInterval(hubspotInterval);
            }
        }
        if (window.HubSpotConversations) {
            initHubspotEvents();
        } else {
            window.hsConversationsOnReady = [
                () => {
                    initHubspotEvents();
                },
            ];
        }
        function initHubspotEvents(){
            window.HubSpotConversations.on('conversationStarted', payload => {
                jQuery('#arcontactus').contactUs('hide');
                jQuery('#hubspot-messages-iframe-container').addClass('active');
                window.HubSpotConversations.widget.open();
                hubspotInterval = setInterval(function(){
                    checkHubspotIsOpened();
                }, 200);
            });
            window.HubSpotConversations.on('unreadConversationCountChanged', payload => {
                jQuery('#arcontactus').contactUs('hide');
                jQuery('#hubspot-messages-iframe-container').addClass('active');
                window.HubSpotConversations.widget.open();
                hubspotInterval = setInterval(function(){
                    checkHubspotIsOpened();
                }, 200);
            });
        }
    {/if}
    {if $socialintents}
        function onSIApiReady() {
            SI_API.hidePopup();
            SI_API.hideTab();
            setTimeout(function(){
                SI_API.hidePopup();
                SI_API.hideTab();
            }, 200);
            {if $liveChatConfig->socialintents_userinfo && $customer->id}
                SI_API.setChatInfo("{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}", '{$customer->email|escape:'htmlall':'UTF-8'}');
            {/if}
            SI_API.onChatClosed = function(){
                SI_API.hideTab();
                SI_API.hidePopup();
                jQuery('#arcontactus').contactUs('show');
            };
            SI_API.onChatOpened = function(){
                jQuery('#arcontactus').contactUs('hide');
                SI_API.showTab();
            };
        };
        
    {/if}
</script>
{if $liveZilla}
    <script type="text/javascript" id="{$liveChatConfig->getLiveZillaId()|escape:'htmlall':'UTF-8'}" src="{$liveChatConfig->lz_id|escape:'htmlall':'UTF-8'}"></script>
{/if}
{if $zopimIntegrated}
    {if $isZendesk}
        <!--script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key={$liveChatConfig->zopim_id|escape:'htmlall':'UTF-8'}"> </script -->
        <script type="text/javascript">{literal}
            (function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
            d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
            _.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");$.setAttribute("id","ze-snippet");
            $.src="https://static.zdassets.com/ekr/snippet.js?key={/literal}{$liveChatConfig->zopim_id|escape:'htmlall':'UTF-8'}{literal}";z.t=+new Date;$.{/literal}
            type="text/javascript";e.parentNode.insertBefore($,e);$.addEventListener('load', function(){
                zE('webWidget:on', 'chat:connected', function(){
                    zE('webWidget', 'hide');
                });
                zE('webWidget:on', 'open', function(){
                    jQuery('#arcontactus').contactUs('hide');
                });
                zE('webWidget:on', 'close', function(){
                    zE('webWidget', 'hide');
                    jQuery('#arcontactus').contactUs('show');
                });
                zE('webWidget:on', 'chat:unreadMessages', function(msgs){
                    jQuery('#arcontactus').contactUs('hide');
                    zE('webWidget', 'show');
                    zE('webWidget', 'open');
                });
                {if $liveChatConfig->zopim_userinfo && $customer->id}
                    zE('webWidget', 'identify', {
                        name: "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}",
                        email: '{$customer->email|escape:'htmlall':'UTF-8'}'
                    });
                {/if}
                }) 
            })(document,"script");
        </script>
    {else}
        <script type="text/javascript">{literal}
            window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
            d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
            _.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
            $.src="https://v2.zopim.com/?{/literal}{$liveChatConfig->zopim_id|escape:'htmlall':'UTF-8'}{literal}";z.t=+new Date;$.
            type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");{/literal}
            $zopim(function(){
                $zopim.livechat.hideAll();
                {if $buttonConfig->position == 'left'}
                    $zopim.livechat.window.setPosition('bl');
                {else}
                    $zopim.livechat.window.setPosition('br');
                {/if}
                $zopim.livechat.window.onHide(function(){
                    $zopim.livechat.hideAll();
                    jQuery('#arcontactus').contactUs('show');
                });
            });
        </script>
    {/if}
{/if}

{if $crispIntegrated}
    <script type="text/javascript">
        window.$crisp=[];window.CRISP_WEBSITE_ID="{$liveChatConfig->crisp_site_id|escape:'htmlall':'UTF-8'}";(function(){
            d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);
        })();
        $crisp.push(["on", "session:loaded", function(){
            $crisp.push(["do", "chat:hide"]);
        }]);
        $crisp.push(["on", "chat:closed", function(){
            $crisp.push(["do", "chat:hide"]);
            jQuery('#arcontactus').contactUs('show');
        }]);
        $crisp.push(["on", "message:received", function(){
            $crisp.push(["do", "chat:show"]);
            jQuery('#arcontactus').contactUs('hide');
        }]);
    </script>
{/if}

{if $facebookIntegrated}
    {strip}<style type="text/css">
        #ar-fb-chat{
            display: none;
        }
        #ar-fb-chat.active{
            display: block;
        }
    </style>{/strip}
    <div id="ar-fb-chat">
        {if $liveChatConfig->fb_init}
            <script>
                {if $liveChatConfig->fb_one_line}
                    (function(d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) return;
                        js = d.createElement(s); js.id = id;
                        js.src = "//connect.facebook.net/{if $liveChatConfig->fb_lang[$id_lang]}{$liveChatConfig->fb_lang[$id_lang]|escape:'htmlall':'UTF-8'}{else}en_US{/if}/sdk/xfbml.customerchat.js#xfbml={if $liveChatConfig->fb_xfbml}1{else}0{/if}&version=v{if $liveChatConfig->fb_version|escape:'htmlall':'UTF-8'}{$liveChatConfig->fb_version}{else}10.0{/if}&autoLogAppEvents=1";
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk-chat'));
                {else}
                    window.fbAsyncInit = function() {
                        FB.init({
                            xfbml            : {if $liveChatConfig->fb_xfbml}true{else}false{/if},
                            version          : 'v{if $liveChatConfig->fb_version}{$liveChatConfig->fb_version|escape:'htmlall':'UTF-8'}{else}10.0{/if}'
                        });
                    };

                    (function(d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) return;
                        js = d.createElement(s); js.id = id;
                        js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk-chat'));
                {/if}
            </script>
        {/if}
        <div class="fb-customerchat" page_id="{$liveChatConfig->fb_page_id|escape:'htmlall':'UTF-8'}" {if $liveChatConfig->fb_color}theme_color="{$liveChatConfig->fb_color|escape:'htmlall':'UTF-8'}"{/if}></div>
    </div>
{/if}