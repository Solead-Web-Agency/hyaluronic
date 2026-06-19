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

<form action="#" id="country-selector" name="country-selector" method="POST">
    {if isset($show_country_selector) && $show_country_selector}
        <fieldset>
            {if $psIsGt15}
            <h3>{else}
                <legend>{/if}<img src="{$images|escape:'quotes':'UTF-8'}world.gif" alt=""
                                  class="middle"/> {l s='Choose Amazon Platform' mod='amazon'}{if $psIsGt15}
            </h3>
            {else}</legend>{/if}

            <table class="country-selector">
                <tr>
                    {foreach from=$marketplaces item=marketplace}
                        <td><img src="{$marketplace.image|escape:'quotes':'UTF-8'}"
                                 alt="{$marketplace.name|escape:'quotes':'UTF-8'}"
                                 class="sp_marketplace_flag"
                                 data-mkp-id="{$marketplace.mkpId|escape:'htmlall':'UTF-8'}" />
                        </td>
                    {/foreach}
                </tr>
                <tr>
                    {foreach from=$marketplaces item=marketplace}
                        <td>
                            <input type="radio" name="amazon_lang"
                               value="{$marketplace.id_lang|intval}" rel="1"    {*legacy APIs*}
                               class="{$marketplace.mkpId|escape:'htmlall':'UTF-8'}"    {*SP APIs*}
                               data-mkp-id="{$marketplace.mkpId|escape:'htmlall':'UTF-8'}"
                               title="{$marketplace.name|escape:'htmlall':'UTF-8'}"
                                {if isset($master_marketplace_idLang) && $master_marketplace_idLang == $marketplace.id_lang} checked {/if}
                            />
                        </td>
                    {/foreach}
                </tr>
                <tr>
                    {foreach from=$marketplaces item=marketplace}
                        <td><span class="name sp_marketplace_name"
                                  data-mkp-id="{$marketplace.mkpId|escape:'htmlall':'UTF-8'}">
                            {$marketplace.name|escape:'quotes':'UTF-8'}
                            </span></td>
                    {/foreach}
                </tr>

            </table>

        </fieldset>
    {else}
        {foreach from=$marketplaces item=marketplace}
            <input type="radio" name="amazon_lang" value="{$marketplace.id_lang|intval}" rel="1"
                   data-mkp-id="{$marketplace.mkpId|escape:'htmlall':'UTF-8'}"
                   style="display:none;" title="{$marketplace.name|escape:'htmlall':'UTF-8'}"
                   {if isset($master_marketplace_idLang) && $master_marketplace_idLang == $marketplace.id_lang} checked {/if}
            />
        {/foreach}
    {/if}

</form>
