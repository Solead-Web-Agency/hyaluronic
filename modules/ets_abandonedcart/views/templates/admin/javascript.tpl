{*
* 2007-2022 ETS-Soft
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
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<script type="text/javascript">
    state_token = '{getAdminToken tab='AdminStates'}';
    address_token = '{getAdminToken tab='AdminAddresses'}';
    var ets_abancart_changed_confirm = '{l s='Chosen email template has been modified. Do you want to continue replace email template?' mod='ets_abandonedcart' js=1}';
    var ets_abancart_btn_finish = '{l s='Finish' mod='ets_abandonedcart' js=1}';
    var ets_abancart_btn_sendmail = '{l s='Send email' mod='ets_abandonedcart' js=1}';
    var ets_abancart_btn_continue = '{l s='Continue' mod='ets_abandonedcart' js=1}';
    var ets_abancart_validate = '{l s='is invalid' mod='ets_abandonedcart' js=1}';
    var ets_abancart_required = '{l s='is required' mod='ets_abandonedcart' js=1}';
    var ets_abancart_temp_required = '{l s='Email template is required' mod='ets_abandonedcart' js=1}';
    /*var ets_abancart_frequency_configured = '{l s='Timing has not been configured' mod='ets_abandonedcart' js=1}';*/
    var ETS_AB_HTML_PURIFIER = {$html_purifier|intval};
    {if !empty($img_dir)}
    var ets_abancart_img_dir='{$img_dir nofilter}';
    {/if}
    var ETS_ABANCART_PS_VERSION_17 = {$is17|intval};
    {if !empty($link_back_office)}
    var ETS_ABANCART_AJAX_LINK = '{$link_back_office nofilter}';
    {/if}
    if (ETS_ABANCART_PS_VERSION_17 && typeof $.fn.mColorPicker !== "undefined") {
        $.fn.mColorPicker.defaults.imageFolder = baseDir + 'img/admin/';
    }
    {if isset($campaign_url) && $campaign_url}
    var ETS_ABANCART_CAMPAIGN_URL='{$campaign_url nofilter}';
    {/if}
</script>
{if !empty($js_files)}{foreach from=$js_files item='js_file'}
    <script src="{$js_file nofilter}"></script>
{/foreach}{/if}