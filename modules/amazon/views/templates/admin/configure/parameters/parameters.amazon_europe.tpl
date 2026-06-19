{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 *}
<div rel="amazon-europe" class="amazon-europe" style="display:none;">
    <div class="form-group">
        <label class="control-label col-lg-3"
               style="color:grey">{l s='Amazon Europe' mod='amazon'}</label><br/><br/>

        <label class="control-label col-lg-3"
               rel="amazon_europe"><span>{l s='Master Platform' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
            <select name="marketPlaceMaster" id="marketPlaceMaster"
                    class="{$parameters.settings.europe.class|escape:'htmlall':'UTF-8'}" style="width:280px">
                <option value=""
                        disabled="disabled">{l s='Choose the platform for this region' mod='amazon'}</option>
                <option value=""></option>
                {foreach from=$parameters.settings.europe.selector key=iso_code item=selector}
                    <option value="{$iso_code|escape:'htmlall':'UTF-8'}"
                            {if $selector.selected}selected{/if}>{$selector.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <!-- START: Advanced Settings -->
    <div rel="amazon-expert-mode" class="amazon-expert-mode">
        <div class="form-group">
            <label class="control-label col-lg-3"></label>
            <div class="col-lg-9">
                <span class="config-amz-europe">[ + ] {l s='Advanced Settings' mod='amazon'}</span>
                <span class="config-amz-europe"
                      style="display:none">[ - ] {l s='Quick Settings' mod='amazon'}</span><br/><br/>
            </div>
        </div>
        <div class="form-group" >
            <!-- Advanced Settings -->
            <div class="amz-europe-advanced-settings" style="display:none">
                <div class="form-group">
                    <label class="control-label col-lg-3" rel="amz-europe-separate-acc">
                        <span>{l s='Separate seller account' mod='amazon'}</span><sup class="expert">{l s='Expert' mod='amazon'}</sup>
                    </label>

                    <div class="margin-form col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="eu_separate_seller_acc" id="eu_separate_seller_acc" value="1"
                               {if $parameters.settings.eu_separate_seller_acc}checked="checked"{/if} /><label for="eu_separate_seller_acc"
                                                                                                    class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                        <input type="radio" name="eu_separate_seller_acc" id="eu_separate_seller_acc-2" value="0"
                               {if (!$parameters.settings.eu_separate_seller_acc)}checked="checked"{/if} /><label
                                for="eu_separate_seller_acc-2"
                                class="label-checkbox">{l s='No' mod='amazon'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Advanced Settings -->
</div>