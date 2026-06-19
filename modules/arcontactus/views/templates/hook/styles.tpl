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

{if ($menuConfig->menu_bg)}
    .arcontactus-widget .messangers-block{
        background-color: {$menuConfig->menu_bg|escape:'htmlall':'UTF-8'};
    }
    .arcontactus-widget .messangers-block::before{
        border-top-color: {$menuConfig->menu_bg|escape:'htmlall':'UTF-8'};
    }
{/if}
{if ($menuConfig->menu_color)}
    .messangers-block .messanger p, .messangers-block .messanger .arcu-item-label{
        color:  {$menuConfig->menu_color|escape:'htmlall':'UTF-8'};
    }
{/if}
{if ($menuConfig->menu_hcolor)}
    .messangers-block .messanger:hover p, .messangers-block .messanger:hover .arcu-item-label{
        color:  {$menuConfig->menu_hcolor|escape:'htmlall':'UTF-8'};
    }
{/if}
{if ($menuConfig->menu_hbg)}
    .messangers-block .messanger:hover{
        background-color:  {$menuConfig->menu_hbg|escape:'htmlall':'UTF-8'};
    }
{/if}
#arcontactus-message-callback-phone-submit{
    font-weight: normal;
}
.grecaptcha-badge{
    display: none;
}
{if ($buttonConfig->x_offset)}
    .arcontactus-widget.{$buttonConfig->position|escape:'htmlall':'UTF-8'}.arcontactus-message{
        {if ($buttonConfig->position == 'left')}
            left: {$buttonConfig->x_offset|intval}px;
        {/if}
        {if ($buttonConfig->position == 'right')}
            right: {$buttonConfig->x_offset|intval}px;
        {/if}
    }
{/if}
{if ($buttonConfig->y_offset)}
    .arcontactus-widget.{$buttonConfig->position|escape:'htmlall':'UTF-8'}.arcontactus-message{
        bottom: {$buttonConfig->y_offset|intval}px;
    }
{/if}
{if ($buttonConfig->position == 'storefront')}
    .arcontactus-widget .arcontactus-message-button{
        display: none;
    }
    .arcontactus-widget.arcontactus-message{
        bottom: -1000px;
    }
{/if}
{if ($menuConfig->shadow_size)}
    .arcontactus-widget .messangers-block, .arcontactus-widget .arcontactus-prompt, .arcontactus-widget .callback-countdown-block{
        box-shadow: 0 0 {$menuConfig->shadow_size|intval}px rgba(0, 0, 0, {$menuConfig->shadow_opacity|escape:'htmlall':'UTF-8'});
    }
{/if}
.arcontactus-widget .arcontactus-message-button .pulsation{
    -webkit-animation-duration:{$buttonConfig->pulsate_speed|intval / 1000}s;
    animation-duration: {$buttonConfig->pulsate_speed|intval / 1000}s;
}
{if ($menuConfig->item_border_style != 'none' && $menuConfig->item_border_color)}
.arcontactus-widget.arcontactus-message .messangers-block .messangers-list li{
    border-bottom: 1px {$menuConfig->item_border_style|escape:'htmlall':'UTF-8'} {$menuConfig->item_border_color|escape:'htmlall':'UTF-8'};
}
.arcontactus-widget.arcontactus-message .messangers-block .messangers-list li:last-child{
    border-bottom: 0 none;
}
{/if}
#ar-zalo-chat-widget{
    display: none;
}
#ar-zalo-chat-widget.active{
    display: block;
}
{if (!$isMobile)}
.arcontactus-widget .messangers-block,
.arcontactus-widget .arcu-popup{
    {if ($menuConfig->menu_width)}
        width: {$menuConfig->menu_width|intval}px;
    {else}
        width: auto;
    {/if}
}
.messangers-block .messanger p, .messangers-block .messanger .arcu-item-label{
    {if (!$menuConfig->menu_width)}
        white-space: nowrap;
    {/if}
}
{if ($callbackConfig->popup_width)}
.arcontactus-widget .callback-countdown-block{
    width: {$callbackConfig->popup_width|intval}px;
}
{/if}
{/if}

.arcontactus-widget.no-bg .messanger .arcu-item-label{
    background: {$menuConfig->menu_bg|escape:'htmlall':'UTF-8'};
}
.arcontactus-widget.no-bg .messanger:hover .arcu-item-label{
    background: {$menuConfig->menu_hbg|escape:'htmlall':'UTF-8'};
}
.arcontactus-widget.no-bg .messanger .arcu-item-label:before,
.arcontactus-widget.no-bg .messanger:hover .arcu-item-label:before{
    border-left-color: {$menuConfig->menu_hbg|escape:'htmlall':'UTF-8'};
}
.arcontactus-widget.left.no-bg .messanger:hover .arcu-item-label:before{
    border-right-color: {$menuConfig->menu_hbg|escape:'htmlall':'UTF-8'};
    border-left-color: transparent;
}

{if ($menuConfig->shadow_size)}
    .arcontactus-widget.no-bg .messanger:hover .arcu-item-label{
        box-shadow: 0 0 {$menuConfig->shadow_size|intval}px rgba(0, 0, 0, {$menuConfig->shadow_opacity|escape:'htmlall':'UTF-8'});
    }
{/if}

@media(max-width: 428px){
    .arcontactus-widget.{$buttonConfig->position|escape:'htmlall':'UTF-8'}.arcontactus-message.opened,
    .arcontactus-widget.{$buttonConfig->position|escape:'htmlall':'UTF-8'}.arcontactus-message.open,
    .arcontactus-widget.{$buttonConfig->position|escape:'htmlall':'UTF-8'}.arcontactus-message.popup-opened{
        left: 0;
        right: 0;
        bottom: 0;
    }
}
{if isset($customCss)}
    {$customCss nofilter}
{/if}