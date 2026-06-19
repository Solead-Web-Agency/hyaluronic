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

<label class="control-label col-lg-3" rel="prime_order"><span>{l s='For Prime Orders' mod='amazon'}</span></label>

<div class="margin-form col-lg-9">
    {foreach from=$prime_carriers key=index item=carrier}
        <div id="prime-carrier-group-{$id_lang|intval}-{$index|escape:'htmlall':'UTF-8'}"
             class="carrier-group">
            <select name="amazon_prime_carrier[{$id_lang|intval}][]"
                    style="width:230px; display: inline;">
                <option disabled="disabled">{l s='Choose the associated carrier on Amazon' mod='amazon'}</option>
                <option value=""></option>
                {if isset($carrier.amazon_carrier) && is_array($carrier.amazon_carrier)}
                    {foreach from=$carrier.amazon_carrier key=key item=amazon_carrier}
                        <option value="{$key|escape:'htmlall':'UTF-8'}"
                                {if $amazon_carrier.selected}selected{/if}>{$amazon_carrier.name|escape:'quotes':'UTF-8'}</option>
                    {/foreach}
                {/if}
            </select>
            <span style="position:relative;top:-4px;">&nbsp;&nbsp;<img
                        src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png"
                        style="max-height:16px;opacity:0.5"
                        alt=""/>&nbsp;&nbsp;</span>
            <select name="prime_carrier[{$id_lang|intval}][]" style="width:230px; display: inline;">
                <option disabled="disabled">{l s='Choose an appropriate carrier for Amazon Orders' mod='amazon'}</option>
                <option value="0"></option>
                {if isset($carrier.prestashop_carrier) && is_array($carrier.prestashop_carrier)}
                    {foreach from=$carrier.prestashop_carrier key=id_carrier item=prestashop_carrier}
                        <option value="{$id_carrier|intval}"
                                {if $prestashop_carrier.selected}selected{/if}>{$prestashop_carrier.name|escape:'htmlall':'UTF-8'}{if $prestashop_carrier.is_module}&nbsp;({l s='Module' mod='amazon'}){/if}</option>
                    {/foreach}
                {/if}
            </select>
            &nbsp;&nbsp;
            <span class="add-carrier addnewprimecarrier"
                  rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_add|escape:'quotes':'UTF-8'}>
                                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}plus.png"
                                         alt="{l s='Add a new carrier' mod='amazon'}"/></span>
            <span class="remove-carrier removeprimecarrier"
                  rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_del|escape:'quotes':'UTF-8'}>
                                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}minus.png"
                                         alt="{l s='Add a new carrier' mod='amazon'}"/></span>
            <br/>
        </div>
        <!-- eof div carrier group -->
    {/foreach}
    <div id="new-prime-carriers-{$id_lang|intval}"></div>

    <br/>
</div>