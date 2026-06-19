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
    var ETS_AC_LINK_REMINDER_ADMIN = "{$linkReminderEmail nofilter}";
    var ETS_AC_LINK_CAMPAIGN_TRACKING = "{$linkCampaignTracking nofilter}";
    var ETS_AC_LOGO_LINK = "{$logoLink nofilter}";
    var ETS_AC_IMG_MODULE_LINK = "{$imgModuleDir nofilter}";
    var ETS_AC_FULL_BASE_URL = "{$fullBaseUrl nofilter}";
    var ETS_AC_ADMIN_CONTROLLER= "{$smarty.get.controller|escape:'html':'UTF-8'}";
    var ETS_AC_TRANS = {literal}{}{/literal};
    ETS_AC_TRANS['clear_tracking'] = "{l s='Clear tracking' mod='ets_abandonedcart'}";
    ETS_AC_TRANS['email_temp_setting'] = "{l s='Email template settings' mod='ets_abandonedcart'}";
    ETS_AC_TRANS['confirm_clear_tracking'] = "{l s='Clear tracking will also delete all data of Campaign tracking table and statistic data of Dashboard. Do you want to clear tracking?' mod='ets_abandonedcart'}";
    ETS_AC_TRANS['confirm_delete_lead_field'] = "{l s='Do you want to delete this field?' mod='ets_abandonedcart'}";
    ETS_AC_TRANS['lead_form_not_found'] = "{l s='Lead form does not exist' mod='ets_abandonedcart'}";
    ETS_AC_TRANS['lead_form_disabled'] = "{l s='Lead form is disabled' mod='ets_abandonedcart'}";
</script>
