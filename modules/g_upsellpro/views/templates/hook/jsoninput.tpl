{*
* Do not edit the file if you want to upgrade in future.
*
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @license   please read license in file license.txt
* @link	     http://www.globosoftware.net
*/
*}
<div class="gnone hidden upsell-jsoninput">
    <input type="hidden" class="upsellfields-popup" value="{$popup_shows|escape:'html':'UTF-8'}"/>
    <input type="hidden" class="upsellfields-page_name" value="{$page_name|escape:'html':'UTF-8'}"/>
    <input type="hidden" class="upsellfields-floating" value="{$floating_shows|escape:'html':'UTF-8'}"/>
    <input type="hidden" class="upsellfields-time-show" value="{$getConfigFieldsValues['GSELL_MAIN_POPUP_DELAY']|escape:'html':'UTF-8'}"/>
    <input type="hidden" class="upsellfields-popup-titlenext" value="{l s='Next Offer →' mod='g_upsellpro'}"/>
    <input type="hidden" class="upsellfields-popup-titleback" value="{l s='← Previous Offer' mod='g_upsellpro'}"/>
</div>