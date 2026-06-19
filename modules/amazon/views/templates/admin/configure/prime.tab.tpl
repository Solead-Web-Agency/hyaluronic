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

<div id="menudiv-prime" class="tabItem {if $prime.selected_tab}selected{/if} panel form-horizontal">
    <h3>{l s='Prime' mod='amazon'}</h3>

    <div>
        <div class="form-group">
            <label class="control-label col-lg-3" rel="delivery_experience">
                <span>{l s='Delivery Experience' mod='amazon'}</span>
            </label>
            <div class="margin-form col-lg-9">
                <select name="prime_config[delivery_experience]" title="Delivery experience" style="width:500px;">
                    <option value="" disabled="disabled">
                        {l s='Choose the delivery confirmation level' mod='amazon'}
                    </option>
                    {foreach from=$prime.available_delivery_experiences key=delivery_experience item=delivery_experience_label}
                        <option value="{$delivery_experience|escape:'htmlall':'UTF-8'}"
                                {if $prime.delivery_experience == $delivery_experience}selected{/if}>
                            {$delivery_experience_label|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group two-px-margin-bottom">
            <label class="control-label col-lg-3" rel="carrier_will_pickup">
                <span>{l s='Carrier will pick up' mod='amazon'}</span>
            </label>
            <div class="margin-form col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="prime_config[carrier_will_pickup]" id="carrier_will_pickup-1" value="1"
                       {if $prime.carrier_will_pickup}checked{/if} />
		    <label for="carrier_will_pickup-1" class="label-checkbox">{l s='Yes' mod='amazon'}</label>
		    <input type="radio" name="prime_config[carrier_will_pickup]" id="carrier_will_pickup-2" value="0"
                   {if !$prime.carrier_will_pickup}checked{/if} />
		    <label for="carrier_will_pickup-2" class="label-checkbox">{l s='No' mod='amazon'}</label>
		    <a class="slide-button btn"></a>
            </span>
            </div>
        </div>
    </div>

    <div class="cleaner"><br/></div>
    <div class="form-group col-lg-12">
        <hr class="amz-separator" style="width:30%"/>
    </div>

    <div>
        <div class="form-group">
            <label class="control-label col-lg-3" style="color:grey">{l s='Alternate address' mod='amazon'}</label>
        </div>
        <div class="cleaner"><br/></div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="prime_shop_name">
                <span>{l s='Shop name' mod='amazon'}</span>
            </label>
            <div class="col-lg-6">
                <input class="form-control" type="text" id="prime_shop_name" name="prime_config[shop_name]"
                       value="{$prime.shop_name|escape:'htmlall':'UTF-8'}">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="prime_address1">
                <span>{l s='Address1' mod='amazon'}</span>
            </label>
            <div class="col-lg-6">
                <input class="form-control" type="text" id="prime_address1" name="prime_config[address1]"
                       value="{$prime.address1|escape:'htmlall':'UTF-8'}">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="prime_address2">
                <span>{l s='Address2' mod='amazon'}</span>
            </label>
            <div class="col-lg-6">
                <input class="form-control" type="text" id="prime_address2" name="prime_config[address2]"
                       value="{$prime.address2|escape:'htmlall':'UTF-8'}">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="prime_city">
                <span>{l s='City' mod='amazon'}</span>
            </label>
            <div class="col-lg-6">
                <input class="form-control" type="text" id="prime_city" name="prime_config[city]"
                       value="{$prime.city|escape:'htmlall':'UTF-8'}">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="prime_postcode">
                <span>{l s='Postcode' mod='amazon'}</span>
            </label>
            <div class="col-lg-6">
                <input class="form-control" type="text" id="prime_postcode" name="prime_config[postcode]"
                       value="{$prime.postcode|escape:'htmlall':'UTF-8'}">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="prime_country">
                <span>{l s='Country' mod='amazon'}</span>
            </label>
            <div class="margin-form col-lg-6">
                <select id="prime_country" name="prime_config[country]">
                    <option value="">
                        {l s='Choose your country' mod='amazon'}
                    </option>
                    {foreach from=$prime.platforms key=iso_code item=country_name}
                        <option value="{$iso_code|escape:'htmlall':'UTF-8'}"
                                {if $iso_code == $prime.country}selected{/if}>
                            {$country_name|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="prime_email">
                <span>{l s='Email' mod='amazon'}</span>
            </label>
            <div class="col-lg-6">
                <input class="form-control" type="text" id="prime_email" name="prime_config[email]"
                       value="{$prime.email|escape:'htmlall':'UTF-8'}">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="prime_phone">
                <span>{l s='Phone' mod='amazon'}</span>
            </label>
            <div class="col-lg-6">
                <input class="form-control" type="text" id="prime_phone" name="prime_config[phone]"
                       value="{$prime.phone|escape:'htmlall':'UTF-8'}">
            </div>
        </div>
    </div>

    {$prime.tab_footer|escape:'quotes':'UTF-8'}
</div>
