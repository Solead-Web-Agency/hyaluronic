{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*
*}

<fieldset id="steavisgarantis_custom" style="display:none;">
        <input type="hidden" name="steavisgarantis_custom" value="1" />
        <input type="hidden" name="steavisgarantis_customer_id" value="{$steavisgarantis_customer_id|escape:'htmlall':'UTF-8'}" />
        
        <img style="display: block;padding-top: 16px;" width="130" src="{$modules_dir|escape:'htmlall':'UTF-8'}steavisgarantis/views/img/steavisgarantis_logo_{$sagLang|escape:'htmlall':'UTF-8'}.png" alt="{l s='Review requests consent' mod='steavisgarantis'}">
        <div style="display: block;vertical-align:top;padding-left: 2px;padding-top: 10px;">
            <p class="checkbox" style="padding-bottom: 5px;">
                <input type="checkbox" name="steavisgarantis_consent" id="steavisgarantis_consent" value="1" autocomplete="off" {if ($consent)}checked="checked{/if}>
                <label for="steavisgarantis_consent" style="display: inline;"> {l s='Send me a review request from Guaranteed Reviews Company following my order' mod='steavisgarantis'}</label>
            </p>
            <span><strong>
                {l s='Please check the box above to be able to rate us after your order.' mod='steavisgarantis'}
                <br><br>
            </strong></span>
            
            
        </div>
</fieldset>
<script>
{literal}
window.onload = function() {
  steavisgarantisBloc = document.getElementById("steavisgarantis_custom");
  mainCarrierDiv = document.getElementsByClassName('order_carrier_content')[0];
  if (!(typeof mainCarrierDiv !== 'undefined')) {
    mainCarrierDiv = document.getElementById("delivery");
  }
    
  if (!(typeof mainCarrierDiv !== 'undefined')) {
    mainCarrierDiv = document.getElementById("checkout-delivery-step");
  }
  
  if (typeof mainCarrierDiv !== 'undefined') {
    mainCarrierDiv.appendChild(steavisgarantisBloc);
  } 
  else {
      console.log("SAG : Unable to find class or id of carrier bloc");
  }
  steavisgarantisBloc.style.display = 'block';
};
{/literal}
</script>
