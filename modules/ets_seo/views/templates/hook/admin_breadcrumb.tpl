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

<div class="ets_seo_breadcrumb">
    {if $current_controller != $dashboard_controller.controller}
        <ul class="nav navbar-nav ets_seo_breadcrumb-list">
            <li class="ets_seo_breadcrumb-item"><a href="{$dashboard_controller.link|escape:'quotes'}"><i class="fa fa-home"></i></a></li>
        {if isset($parent_controller) &&  $parent_controller}
            <li class="ets_seo_breadcrumb-item"><span><a href="{$all_menus[$parent_controller].link|escape:'quotes'}">{$all_menus[$parent_controller].title|escape:'html':'UTF-8'}</a></span></li>
        {/if}
            <li class="ets_seo_breadcrumb-item">
                <span>
                {if $controller_link}
                    <a href="{$controller_link|escape:'quotes'}">
                {/if}
                {$all_menus[$current_controller].title|escape:'html':'UTF-8'}
                 {if $controller_link}
                    </a>
                  {/if}
                </span>
            </li>
        {if $page_name}
            <li class="ets_seo_breadcrumb-item"><span>{$page_name|escape:'html':'UTF-8'}</span></li>
        {/if}
        </ul>
    {/if}
</div>
