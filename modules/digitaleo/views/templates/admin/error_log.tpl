{*
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from DIGITALEO SAS
* Use, copy, modification or distribution of this source file without written
* license agreement from the DIGITALEO SAS is strictly forbidden.
*
*  @author      Digitaleo
*  @copyright   2018 Digitaleo
*  @license     All Rights Reserved
*}
<div class="dgo_image_title"><span>{l s='Error Log' mod='digitaleo'}</span></div>
<div class="dgo_tab_content">
    <table class="dgo_table">
        <tr>
            <th>{l s='Error Date' mod='digitaleo'}</th>
            <th>{l s='API Call' mod='digitaleo'}</th>
            <th>{l s='Parameters' mod='digitaleo'}</th>
            <th>{l s='Return' mod='digitaleo'}</th>
            <th>{l s='HTTP Code' mod='digitaleo'}</th>
        </tr>
        {if !empty($error_log)}
        {foreach from=$error_log item=log}
        <tr>
            <td style="vertical-align:top">{$log.date_error|escape:'htmlall':'UTF-8'|date_format:"%d/%m/%Y %H:%M"}</td>
            <td style="vertical-align:top">{$log.api_call|escape:'htmlall':'UTF-8'}</td>
            <td style="vertical-align:top">
            <a href="#parameters{$log.id_error|escape:'htmlall':'UTF-8'}" class="fancybox_inline"><i class="icon-eye" aria-hidden="true"></i> {$log.parameters|print_r:'true'|escape:'htmlall':'UTF-8'|truncate:120}</a>
            <div id="parameters{$log.id_error|escape:'htmlall':'UTF-8'}" class="inline_fancybox"><pre>{$log.parameters|print_r:'true'|escape:'htmlall':'UTF-8'}</pre></div>
            </td>
            <td style="vertical-align:top">
            <a href="#return{$log.id_error|escape:'htmlall':'UTF-8'}" class="fancybox_inline"><i class="icon-eye" aria-hidden="true"></i> {$log.return|print_r:'true'|escape:'htmlall':'UTF-8'|truncate:120}</a>
            <div id="return{$log.id_error|escape:'htmlall':'UTF-8'}" class="inline_fancybox"><pre>{$log.return|print_r:'true'|escape:'htmlall':'UTF-8'}</pre></div>
            </td>
            <td style="vertical-align:top">{$log.http_code|escape:'htmlall':'UTF-8'}</td>
        </tr>
        {/foreach}
        {else}
        <tr>
            <td colspan="6" class="center info">{l s='There is no error log entries' mod='digitaleo'}</td>
        </tr>
        {/if}
    </table>
    {include file='./pagination.tpl'}
</div>
<script>
$(".fancybox_inline").fancybox();
</script>