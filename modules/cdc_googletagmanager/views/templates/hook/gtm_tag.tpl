{*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @package   cdc_googletagmanager
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<script data-keepinline="true">
    var cdcGtmApi = '{if !empty($cdcGtmApi)}{$cdcGtmApi|escape:'htmlall':'UTF-8'}{/if}';
    var ajaxShippingEvent = {if isset($ajaxShippingEvent)}{$ajaxShippingEvent|escape:'htmlall':'UTF-8'}{else}1{/if};
    var ajaxPaymentEvent = {if isset($ajaxPaymentEvent)}{$ajaxPaymentEvent|escape:'htmlall':'UTF-8'}{else}1{/if};

/* datalayer */
dataLayer = window.dataLayer || [];
{if !empty($preDataLayer)}dataLayer.push({$preDataLayer nofilter});{/if}
{if !empty($dataLayer)}
    let cdcDatalayer = {$dataLayer nofilter};
    dataLayer.push(cdcDatalayer);
{/if}

/* call to GTM Tag */
{if !isset($load_gtm_script) || $load_gtm_script}
{if !empty($override_gtm_tag)}
    {$override_gtm_tag nofilter}
{else}
{literal}(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
}){/literal}(window,document,'script','dataLayer','{$gtm_id|escape:'htmlall':'UTF-8'}');
{/if}
{/if}

/* async call to avoid cache system for dynamic data */
{if $async_user_info}
var cdcgtmreq = new XMLHttpRequest();
cdcgtmreq.onreadystatechange = function() {
    if (cdcgtmreq.readyState == XMLHttpRequest.DONE ) {
        if (cdcgtmreq.status == 200) {
          	var datalayerJs = cdcgtmreq.responseText;
            try {
                var datalayerObj = JSON.parse(datalayerJs);
                dataLayer = dataLayer || [];
                dataLayer.push(datalayerObj);
            } catch(e) {
               console.log("[CDCGTM] error while parsing json");
            }

            {if $gtm_debug}
            // display debug
            console.log('[CDCGTM] DEBUG ENABLED');
            console.log(datalayerObj);
            document.addEventListener('DOMContentLoaded', function() {
              if(document.getElementById("cdcgtm_debug_asynccall")) {
                  document.getElementById("cdcgtm_debug_asynccall").innerHTML = datalayerJs;
              }
            }, false);
            {/if}
        }
        dataLayer.push({
          'event': '{$event_datalayer_ready|escape:'htmlall':'UTF-8'}'
        });
    }
};
cdcgtmreq.open("GET", "{$async_url|escape:'htmlall':'UTF-8'}" /*+ "?" + new Date().getTime()*/, true);
cdcgtmreq.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
cdcgtmreq.send();
{else}
dataLayer.push({
  'event': '{$event_datalayer_ready|escape:'htmlall':'UTF-8'}'
});
{/if}
</script>