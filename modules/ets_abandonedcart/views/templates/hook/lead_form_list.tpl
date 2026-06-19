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
<div class="ets-ac-lead-form-field-sc">
    {if isset($lead_forms) && $lead_forms}
        {foreach $lead_forms as $f}
        <div class="ets-ac-lead-form-field-item hide" data-id="{$f.id_ets_abancart_form|escape:'html':'UTF-8'}" data-enable="{$f.enable|escape:'html':'UTF-8'}">
            {include $module_dir|cat:'/views/templates/hook/lead_form_short_code.tpl' lead_form=$f field_types=$field_types reminderType=$reminderType}
        </div>
        {/foreach}
    {/if}
</div>