{*
* 2017 Areama
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
*  @author Areama <contact@areama.net>
*  @copyright  2017 Areama
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*}

<div class="ar-modules-title">{l s='modules that may interest you:' mod='arcontactus'}</div>
<ul class="areama-modules">
{foreach $data as $module}
    <li>
        <a href="{$module->url|escape:'htmlall':'UTF-8'}" target="_blank">
            <div class="areama-module-logo">
                <img src="{$module->logo_url|escape:'htmlall':'UTF-8'}" alt="{$module->title|escape:'htmlall':'UTF-8'}" />
            </div>
            <div class="areama-module-content">
                <div class="areama-module-title">
                    {$module->title|escape:'htmlall':'UTF-8'}
                </div>
                {if $module->rate_count}
                <div class="areama-module-rate">
                    <span title="{$module->avg_rate|escape:'htmlall':'UTF-8'}" class="ar-stars module-rate-{$module->rate|escape:'htmlall':'UTF-8'}">&nbsp;</span> 
                    <span class="ar-votes">({$module->rate_count|escape:'htmlall':'UTF-8'} {l s='votes' mod='arcontactus'})</span>
                </div>
                {/if}
                <div class="areama-module-description">
                    {$module->description|escape:'htmlall':'UTF-8'}
                </div>
            </div>
            {if $module->installed}
                <div class="areama-module-price">
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-check-circle fa-w-16 fa-3x"><path fill="currentColor" d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z" class=""></path></svg>
                    <p class="sm">
                        {l s='Module installed' mod='arcontactus'}
                    </p>
                </div>
            {else}
                <div class="areama-module-price">
                    <p>&euro;{$module->price|escape:'htmlall':'UTF-8'}</p>
                    <button class="btn btn-default">
                        {l s='Buy module' mod='arcontactus'}
                    </button>
                </div>
            {/if}
        </a>
    </li>
{/foreach}
</ul>