{*
* Do not edit the file if you want to upgrade the module in future.
*
* @author    Globo Jsc <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @link	     http://www.globosoftware.net
* @license   please read license in file license.txt
*/
*}

{if $html == 'tabs'}
    <div class="linktabs gupsellpro-tabmenu">
        <nav class="list-group linktabs" style="display: flex;">
            <a style="width: 25%;" class="list-group-item {if $controller == 'AdminGupsellanalytics'}active{/if}" href="{$link->getAdminLink('AdminGupsellanalytics')|escape:'html':'UTF-8'}">{l s='Dashboard' mod='g_upsellpro'}</a>
            <a style="width: 25%;" class="list-group-item {if $controller == 'AdminGupselloffers'}active{/if}" href="{$link->getAdminLink('AdminGupselloffers')|escape:'html':'UTF-8'}">{l s='Offers' mod='g_upsellpro'}</a>
            <a style="width: 25%;" class="list-group-item {if $controller == 'AdminGupsellsettings'}active{/if}" href="{$link->getAdminLink('AdminGupsellsettings')|escape:'html':'UTF-8'}">{l s='Settings' mod='g_upsellpro'}</a>
            <a style="width: 25%;" class="list-group-item {if $controller == 'AdminGupsellfaqs'}active{/if}" href="{$link->getAdminLink('AdminGupsellfaqs')|escape:'html':'UTF-8'}">{l s='FAQs' mod='g_upsellpro'}</a>
       </nav>
    </div>
{elseif $html == 'Addition'}
    {if $additions && $type !='addnew'}
        {foreach from=$additions key=key item=addition}
            <tr data-number="{math equation="x + y" x=$key y=1}">
                <td>
                    <input type="number" class="form-control" name="volumes[{$key|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][minqty]" value="{$addition[$showpage]['minqty']|escape:'html':'UTF-8'}" min="1"/>
                </td>
                <td>
                    <select name="volumes[{$key|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][discounttype]" class="discounttype_active_val">
                        <option value="1" {if $addition[$showpage]['discounttype'] == 1}selected="selected"{/if}>{l s='%' mod='g_upsellpro'}</option>
                        <option value="0" {if $addition[$showpage]['discounttype'] == 0}selected="selected"{/if}>{l s='Amount' mod='g_upsellpro'}</option>
                    </select>
                </td>
                <td>
                    <div class="col-lg-4">
                        <input  type="number" class="form-control" name="volumes[{$key|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][discount]" value="{$addition[$showpage]['discount']|escape:'html':'UTF-8'}" min="0"/>
                    </div>
                    <div class="col-lg-4 gnone discounttype_active {if $addition[$showpage]['discounttype'] == 0}active{/if}">
                        <select name="volumes[{$key|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][id_currency]">
                            {foreach from=$gupsellcurrencies item=curren}
                                <option value="{$curren['id_currency']|escape:'html':'UTF-8'}" {if $addition[$showpage]['id_currency'] == $curren['id_currency']}selected="selected"{/if}>{$curren['name']|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-lg-4 gnone discounttype_active {if $addition[$showpage]['discounttype'] == 0}active{/if}">
                        <select name="volumes[{$key|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][reduction_tax]">
                            <option value="0" {if $addition[$showpage]['reduction_tax'] == 0}selected="selected"{/if}>{l s='Tax excluded' mod='g_upsellpro'}</option>
                            <option value="1" {if $addition[$showpage]['reduction_tax'] == 1}selected="selected"{/if}>{l s='Tax included' mod='g_upsellpro'}</option>
                        </select>
                    </div>
                </td>
                <td>
                    <span class="mostpopular_label"><input type="checkbox" class="volumes_extra_mostpopular" name="volumes[{$key|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][mostpopular]" value="1" {if isset($addition[$showpage]['mostpopular']) && $addition[$showpage]['mostpopular'] == 1}checked="checked"{/if}></span>
                </td>
                <td>
                    <button type="button" class="btn btn-default remove_volume_row"><i class="icon-trash"></i></button>
                </td>
            </tr>
        {/foreach}
    {else}
        <tr data-number="{$number|escape:'html':'UTF-8'}">
            <td>
                <input type="number" class="form-control" name="volumes[{$number|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][minqty]" value="{$number_qty|escape:'html':'UTF-8'}" min="1"/>
            </td>
            <td>
                <select name="volumes[{$number|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][discounttype]" class="discounttype_active_val">
                    <option value="1">{l s='%' mod='g_upsellpro'}</option>
                    <option value="0">{l s='Amount' mod='g_upsellpro'}</option>
                </select>
            </td>
            <td>
                <div class="col-lg-4">
                    <input  type="number" class="form-control" name="volumes[{$number|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][discount]" value="0" min="0"/>
                </div>
                <div class="col-lg-4 gnone discounttype_active">
                    <select name="volumes[{$number|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][id_currency]">
                        {foreach from=$gupsellcurrencies item=curren}
                            <option value="{$curren['id_currency']|escape:'html':'UTF-8'}" >{$curren['name']|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-4 gnone discounttype_active">
                    <select name="volumes[{$number|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][reduction_tax]">
                        <option value="0">{l s='Tax excluded' mod='g_upsellpro'}</option>
                        <option value="1">{l s='Tax included' mod='g_upsellpro'}</option>
                    </select>
                </div>
            </td>
            <td>
                <span class="mostpopular_label"><input type="checkbox" class="volumes_extra_mostpopular" name="volumes[{$number|escape:'html':'UTF-8'}][{$showpage|escape:'html':'UTF-8'}][mostpopular]" value="1" ></span>
            </td>
            <td>
                <button type="button" class="btn btn-default remove_volume_row"><i class="icon-trash"></i></button>
            </td>
        </tr>
    {/if}
{/if}