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
    <fieldset>
        {if $regions|count >= 1}
            {if $psIsGt15}<h3>{else}<legend>{/if}
            <img src="{$images|escape:'htmlall':'UTF-8'}world.gif" class="middle" alt="Choose Amazon Platform"/>
            {l s='Choose Amazon Platform' mod='amazon'}
            {if $psIsGt15}</h3>{else}</legend>{/if}

            {foreach from=$regions key=regionKey item=marketplaces name=regionLoop}
                <fieldset class="country-fieldset">
                    {if $psIsGt15}<h3>{else}<legend>{/if}
                            {$regionKey|escape:'htmlall':'UTF-8'}
                        {if $psIsGt15}</h3>{else}</legend>{/if}
                    <table class="country-selector">
                        <tr>
                            {if ($marketplaces.isUnified)}
                                <td>
                                    <input type="radio" name="amazon_sp_region" title="Region"
                                           id="amazon-sp-region-{$regionKey|escape:'htmlall':'UTF-8'}"
                                           {if $smarty.foreach.regionLoop.first}checked{/if}
                                           data-region-id="{$regionKey|escape:'htmlall':'UTF-8'}"
                                           value="{$regionKey|escape:'htmlall':'UTF-8'}">
                                </td>
                                <td>
                                    <label for="amazon-sp-region-{$regionKey|escape:'htmlall':'UTF-8'}">
                                        {foreach from=$marketplaces.regionData item=spMkp}
                                            <img src="{$images|escape:'htmlall':'UTF-8'|cat:'geo_flags_web2/'|cat:$spMkp['mkpId']|cat:'_64px.png'}"
                                                 alt="{$spMkp['iso']|escape:'htmlall':'UTF-8'}"/>
                                        {/foreach}
                                    </label>
                                </td>
                            {else}
                                <td>
                                    {foreach from=$marketplaces.regionData item=spMkp}
                                        <label for="amazon-sp-mkp-{$spMkp.mkpId|escape:'htmlall':'UTF-8'}">
                                            <span>
                                                <input type="radio" name="amazon_sp_region" title="Marketplace"
                                                       id="amazon-sp-mkp-{$spMkp.mkpId|escape:'htmlall':'UTF-8'}"
                                                       {if $smarty.foreach.regionLoop.first}checked{/if}
                                                       data-mkp-id="{$spMkp.mkpId|escape:'htmlall':'UTF-8'}"
                                                       value="{$spMkp.mkpId|escape:'htmlall':'UTF-8'}">
                                            </span>
                                            <img src="{$images|escape:'htmlall':'UTF-8'|cat:'geo_flags_web2/'|cat:$spMkp['mkpId']|cat:'_64px.png'}"
                                                 alt="{$spMkp['iso']|escape:'htmlall':'UTF-8'}"/>
                                        </label>
                                    {/foreach}
                                </td>
                            {/if}
                        </tr>
                    </table>
                </fieldset>
            {/foreach}
        {/if}
    </fieldset>
</form>
