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

<!-- START: Group (Only for EU) -->
{if isset($general.mkp_groups) && count($general.mkp_groups) > 0}
    <label class="control-label col-lg-3"
           style="color:grey;">{l s='Customer Group' mod='amazon'}</label>

    <div class="col-lg-9">&nbsp;<br/><br/></div>

    {* None business incl VAT *}
    <div class="form-group">
        <label class="control-label col-lg-3"><span>{l s='None Business incl VAT' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9 ptc">
            <select name="mkp_group[{$id_lang|intval}][{AmazonConstant::CUSTOMER_GROUP_NONE_BUSINESS_INCL_VAT}]" id="mkp_group[{$id_lang|intval}][{AmazonConstant::CUSTOMER_GROUP_NONE_BUSINESS_INCL_VAT}]">
                <option></option>
                {foreach from=$parameters.settings.customer_groups key=id_customer_group item=customer_group}
                    <option value="{$id_customer_group|intval}"
                            {if $general.mkp_groups.none_business_incl_vat == $id_customer_group}selected{/if}>{$customer_group.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
    </div>
    {* Business VAT incl *}
    <div class="form-group">
        <label class="control-label col-lg-3">
            <span>{l s='Business' mod='amazon'}</span>
            <span style="text-transform: uppercase;">{$marketplace.region|escape:'htmlall':'UTF-8'}</span>
            <span>{l s='VAT Included' mod='amazon'}</span>
        </label>

        <div class="margin-form col-lg-9 ptc">
            <select name="mkp_group[{$id_lang|intval}][{AmazonConstant::CUSTOMER_GROUP_BUSINESS_VAT_INCL}]" id="mkp_group[{$id_lang|intval}][{AmazonConstant::CUSTOMER_GROUP_BUSINESS_VAT_INCL}]">
                <option></option>
                {foreach from=$parameters.settings.customer_groups key=id_customer_group item=customer_group}
                    <option value="{$id_customer_group|intval}"
                            {if $general.mkp_groups.business_vat_incl == $id_customer_group}selected{/if}>{$customer_group.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
    </div>

    {* Business outside country VAT excl *}
    <div class="form-group">
        <label class="control-label col-lg-3">
            <span>{l s='Business Outside' mod='amazon'}</span>
            <span style="text-transform: uppercase;">{$marketplace.region|escape:'htmlall':'UTF-8'}</span>
            <span>{l s='VAT excl' mod='amazon'}</span>
        </label>

        <div class="margin-form col-lg-9 ptc">
            <select name="mkp_group[{$id_lang|intval}][{AmazonConstant::CUSTOMER_GROUP_BUSINESS_OUTSIDE_COUNTRY_VAT_EXCL}]" id="mkp_group[{$id_lang|intval}][{AmazonConstant::CUSTOMER_GROUP_BUSINESS_OUTSIDE_COUNTRY_VAT_EXCL}]">
                <option></option>
                {foreach from=$parameters.settings.customer_groups key=id_customer_group item=customer_group}
                    <option value="{$id_customer_group|intval}"
                            {if $general.mkp_groups.business_outside_country_vat_excl == $id_customer_group}selected{/if}>{$customer_group.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
    </div>


    <div class="form-group">
        <div class="margin-form col-lg-12">
            <hr class="amz-separator"
                style="width:30%; {if !$psIsGt15}margin-top: 40px;{else}margin-top: 40px;{/if}"/>
        </div>
    </div>
{/if}
<!-- END: Group -->
