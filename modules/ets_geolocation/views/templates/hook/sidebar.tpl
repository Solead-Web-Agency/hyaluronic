{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
<div class="ets_geo_tabspace"></div>
<div class="ets_geo_tabwrapper">
    <div class="with_tabs">
        <div class="page_head_tabs">
            <ul>
                {if $list}
                    {foreach from=$list item='tab'}
                        <li>
                            <a class="{if $active == $tab.id }current{/if} list-tab-item" href="{$tab.url|escape:'html':'UTF-8'}" id="{$tab.id|escape:'html':'UTF-8'}">
                                <i class="icon_{$tab.label|escape:'html':'UTF-8'}"></i>
                                {$tab.label|escape:'html':'UTF-8'}
                                {if isset($tab.total_result) && $tab.total_result} ({$tab.total_result|intval}){/if}
                            </a>
                        </li>
                    {/foreach}
                {/if}
            </ul>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var space_top = $('.ets_geo_tabspace').offset().top - 15;
        $('.ets_geo_tabwrapper').css('top',space_top+'px');
    });
</script>