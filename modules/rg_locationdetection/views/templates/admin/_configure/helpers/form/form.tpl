{**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 *}

{extends file="helpers/form/form.tpl"}

{block name="footer" prepend}
    {if isset($dev_url) && $dev_url}
        <div class="alert alert-info">
            {l s='Once configuration is ready and saved, you can test by clicking the link below.' mod='rg_locationdetection'}<br>
            &#9679; {l s='With this link you will reset the cookie all the time.' mod='rg_locationdetection'}<br>
            &#9679; {l s='You can use a custom IP, just change the IP at the end of the link (by default is your current IP).' mod='rg_locationdetection'}<br><br>
            <strong><a href="{$dev_url|escape:'htmlall':'UTF-8'}" target="_blank">{$dev_url|escape:'htmlall':'UTF-8'}</a></strong><br><br>
            <strong>{l s='NOTE:' mod='rg_locationdetection'}</strong> {l s='Is important to know that in debug mode some features doesn\'t works as normally, because some of them need cookies.' mod='rg_locationdetection'}<br><br>
            {l s='You can configure this URL as a cronjob every 2 weeks to keep the default IP database up-to-date' mod='rg_locationdetection'}:<br>
            <strong><a href="{$cron_url|escape:'htmlall':'UTF-8'}" target="_blank">{$cron_url|escape:'htmlall':'UTF-8'}</a></strong>
        </div>
    {/if}
{/block}

{block name="after"}
    {if isset($dev_url) && $dev_url && $smarty.const._PS_VERSION_ < 1.6}
        <div class="hint" style="display: block;">
            {l s='Once configuration is ready and saved, you can test by clicking the link below.' mod='rg_locationdetection'}<br>
            &#9679; {l s='With this link you will reset the cookie all the time.' mod='rg_locationdetection'}<br>
            &#9679; {l s='You can use a custom IP, just change the IP at the end of the link (by default is your current IP).' mod='rg_locationdetection'}<br><br>
            <strong><a href="{$dev_url|escape:'htmlall':'UTF-8'}" target="_blank">{$dev_url|escape:'htmlall':'UTF-8'}</a></strong><br><br>
            <strong>{l s='NOTE:' mod='rg_locationdetection'}</strong> {l s='Is important to know that in debug mode some features doesn\'t works as normally, because some of them need cookies.' mod='rg_locationdetection'}<br><br>
            {l s='You can configure this URL as a cronjob every 2 weeks to keep the default IP database up-to-date' mod='rg_locationdetection'}<br>
            <strong><a href="{$cron_url|escape:'htmlall':'UTF-8'}" target="_blank">{$cron_url|escape:'htmlall':'UTF-8'}</a></strong>
        </div>
    {/if}
    <script type="text/javascript">
        $(document).ready(function() {
            if ($('.no-autosize').length) {
                var element = $('.no-autosize').find('.textarea-autosize');
                if (element) {
                    element.each(function() {
                        $(this).toggleClass('textarea-autosize textarea-no-autosize');
                    });
                }
            }
        });
    </script>
{/block}

{block name="input"}
    {if $input.type == 'rg-multiple-checkbox'}
        {assign var=groups value=$input.values}
        {include file='./form_multiple_selector.tpl'}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
