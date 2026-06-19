{*
* 2007-2021 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2021 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

<div class="ets_seo_menu">
    <ul class="nav navbar-nav">
        {foreach $menus as $menu}
            {assign 'menu_has_sub' 0}
            <li class="{if $current_controller == $menu.controller || $menu.controller == $parent_controller}active {/if}
                {if $menu.controller == 'AdminEtsSeoSearchAppearanceContentType'} hide-on-md{/if}
                {if $current_controller == 'AdminEtsSeoSearchAppearanceContentType' && $menu.controller == 'AdminEtsSeoSettings'} active-only-md{/if}
                {if isset($menu.has_sub) && $menu.has_sub}
            {foreach $submenus as $sub}
                        {if $sub.parent_controller == $menu.controller}
                        seo_menu_has_sub
                        {assign 'menu_has_sub' 1}
                        {break}
                        {/if}
            {/foreach}{/if}">
                <a href="{$menu.link|escape:'html':'UTF-8'}" class="{if isset($menu.menu_icon)}{$menu.menu_icon|escape:'html':'UTF-8'}{/if}">
                    <i class="icon"></i> {$menu.title|escape:'html':'UTF-8'}
                </a>
                {if isset($menu_has_sub) && $menu_has_sub}
                    <ul class="nav submenu">
                    {foreach $submenus as $sub}
                        {if $sub.parent_controller == $menu.controller}
                        <li>
                            <a href="{$sub.link|escape:'html':'UTF-8'}" class="{if isset($sub.menu_icon)}{$sub.menu_icon|escape:'html':'UTF-8'}{/if}">
                                <i class="icon"></i> {$sub.title|escape:'html':'UTF-8'}
                            </a>
                        </li>
                        {/if}
                    {/foreach}
                        {if $menu.controller == 'AdminEtsSeoSettings'}
                            <li class="show-on-md">
                                <a href="{$menus['AdminEtsSeoSearchAppearanceContentType'].link|escape:'html':'UTF-8'}" class="{if isset($menus['AdminEtsSeoSearchAppearanceContentType'].menu_icon)}{$menus['AdminEtsSeoSearchAppearanceContentType'].menu_icon|escape:'html':'UTF-8'}{/if}">
                                    <i class="icon"></i> {$menus['AdminEtsSeoSearchAppearanceContentType'].title|escape:'html':'UTF-8'}
                                </a>
                            </li>
                        {/if}
                    </ul>
                {/if}
            </li>
        {/foreach}
        {if isset($intro) && $intro}
        <li class="li_othermodules ">
            <a class="{if isset($refsLink) && $refsLink}refs_othermodules{else}link_othermodules{/if}" href="{$other_modules_link|escape:'html':'UTF-8'}" {if isset($refsLink) && $refsLink}target="_blank" {/if}>
                <span class="tab-title">{l s='Other modules' mod='ets_seo'}</span>
                <span class="tab-sub-title">{l s='Made by ETS-Soft' mod='ets_seo'}</span>
            </a>
        </li>
        {/if}
    </ul>
</div>
<div class="ets_menu_height"></div>


