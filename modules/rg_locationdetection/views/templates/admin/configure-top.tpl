{**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 *}

<div class="panel{if $smarty.const._PS_VERSION_ < 1.6} toolbarBox{/if}">
    <img src="{$rg_locationdetection._path|escape:'htmlall':'UTF-8'}logo.png" id="module_logo" class="pull-left" style="margin-right: 10px;" />
    <p>
        <strong>{$rg_locationdetection.displayName|escape:'htmlall':'UTF-8'}</strong> - v{$rg_locationdetection.version|escape:'htmlall':'UTF-8'}<br />
        {$rg_locationdetection.description|escape:'htmlall':'UTF-8'}<br />
        {l s='Author' mod='rg_locationdetection'} <a href="{$rg_locationdetection.author_link|escape:'htmlall':'UTF-8'}" target="_bank">{$rg_locationdetection.author|escape:'htmlall':'UTF-8'}</a>
    </p>
</div>
