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

<tr class="amazon-details amazon-item-title">
    <td class="col-left" rel="product_tax_override"><span>{l s='Product Tax' mod='amazon'}</span></td>
    <td style="padding-bottom:5px;">
        <select name="amz-product_tax_override-{$data.id_lang|intval}" id="amz-product_tax_override-{$data.id_lang|intval}">
            <option value="">{l s='N/A' mod='amazon'}</option>
            {if isset($data.product_tax_list) && is_array($data.product_tax_list)}
                {foreach from=$data.product_tax_list item=ptc}
                    <option value="{$ptc.ptc|escape:'htmlall':'UTF-8'}" {if $data.default == $ptc.ptc}selected{/if}>{$ptc.description|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            {/if}
        </select>
        <span class="amz-small-line">{l s='Product Tax Override' mod='amazon'}</span><br />
        <span class="amz-small-line propagation">{l s='Propagate this value to all products in this' mod='amazon'} :
            <a href="javascript:void(0)" class="amz-propagate-product_tax_override-cat amz-link">[ {l s='Category' mod='amazon'}
                ]</a>&nbsp;&nbsp;
            <a href="javascript:void(0)" class="amz-propagate-product_tax_override-shop amz-link">[ {l s='Store' mod='amazon'}
                ]</a>&nbsp;&nbsp;
            <a href="javascript:void(0)" class="amz-propagate-product_tax_override-manufacturer amz-link">[
                {l s='Manufacturer' mod='amazon'} ]</a>&nbsp;&nbsp;
            <a href="javascript:void(0)" class="amz-propagate-product_tax_override-supplier amz-link">[ {l s='Supplier' mod='amazon'}
                ]</a>&nbsp;&nbsp;
        </span>
    </td>
</tr>