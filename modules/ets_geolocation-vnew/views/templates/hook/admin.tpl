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
{if $ets_geolocation_error_message}
    {$ets_geolocation_error_message nofilter}
{/if}
<script type="text/javascript"> 
    var ets_geolocation_ajax_url = '{$ets_geolocation_ajax_url nofilter}'; 
    var ets_geolocation_author_ajax_url ='{$ets_geolocation_author_ajax_url nofilter}';
    var ets_geolocation_default_lang = {$ets_geolocation_default_lang|intval};
    var ets_geolocation_is_updating = {$ets_geolocation_is_updating|intval};                            
    var ets_geolocation_is_config_page = {$ets_geolocation_is_config_page|intval};
    var ets_geolocation_invalid_file = '{$ets_geolocation_invalid_file|escape:'html':'UTF-8'}';
    var send_mail_label='{l s='Also send this response to customer via email' js=1 mod='ets_geolocation'}';
</script>
<div class="bootstrap back_end_ets_geo">
    {$ets_geolocation_sidebar nofilter}
    <div class="ets_geolocation_form_content_admin {if $control} ets_geolocation_form_{$control|escape:'html':'UTF-8'}{/if}">

        <div class="geo_center_content {if $control} ets_geolocation{$control|escape:'html':'UTF-8'}{/if}">
            {$ets_geolocation_body_html nofilter}
        </div>
    </div>
</div>