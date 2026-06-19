{*
* Do not edit the file if you want to upgrade the module in future.
*
* @author    Globo Jsc <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @link	     http://www.globosoftware.net
* @license   please read license in file license.txt
*/
*}
{if $combination_html == 'check_box'}
    <div class="gupsell_variant-list"><ul class="nav">{foreach from=$combinations item=combination}<li><div class="checkbox"><label class="col-lg-12" for="gupsell_attribute{$display|escape:'html':'UTF-8'}_check_{$combination['id_product_attribute']|escape:'html':'UTF-8'}"><input type="checkbox" name="gupsell_attribute{$display|escape:'html':'UTF-8'}[{$id_product|escape:'html':'UTF-8'}][]" id="gupsell_attribute{$display|escape:'html':'UTF-8'}_check_{$combination['id_product_attribute']|escape:'html':'UTF-8'}" value="{$combination['id_product_attribute']|escape:'html':'UTF-8'}" {$combination['checked']|escape:'html':'UTF-8'}> {$combination['attributes']|escape:'html':'UTF-8'} - {$combination['combination_price']|escape:'html':'UTF-8'} </label> </div> </li>{/foreach} </ul> <button type="button" class="gupsell-close-box" data-id="{$id_product|escape:'html':'UTF-8'}" data-type="{$display|escape:'html':'UTF-8'}"><i class="icon-close"></i></button> </div>
{elseif $combination_html == 'category'}
    <option value="{$category['id_category']|escape:'html':'UTF-8'}" {$selected|escape:'html':'UTF-8'}>{'&nbsp;'|str_repeat:($category['level_depth']*5)}{$category['name']|escape:'html':'UTF-8'}{$shop->name|escape:'html':'UTF-8'}</option>
{elseif $combination_html == 'producthtml'}
    <tr class="gupsell_row{$display|escape:'html':'UTF-8'}_{$productObj->id|escape:'html':'UTF-8'}">
        <td>
            {$productObj->id|escape:'html':'UTF-8'}
        </td>    
        <td>
            <span class="gupsellicon-img"><img class="imgm img-thumbnail" src="{$img|escape:'html':'UTF-8'}" /></span>
        </td>
        <td>
            <a href="{$url|escape:'html':'UTF-8'}" target="_blank">{$productObj->name|escape:'html':'UTF-8'}</a>
        </td>
        <td>
            <label class="mostpopular_label"><input type="checkbox" name="mostpopular[]" value="{$productObj->id|escape:'html':'UTF-8'}" {if $productObj->id == $mostpopular} checked="checked"{/if}></label>
        </td>
        <td>
            <a type="button" class="gupselldelete_{$display|escape:'html':'UTF-8'}product pull-right btn btn-default gbtn-default-red" data-id="{$productObj->id|escape:'html':'UTF-8'}" data-type="{$display|escape:'html':'UTF-8'}"><i class="icon-trash"></i></a> 
            <a type="button" class="gupselledit_{$display|escape:'html':'UTF-8'}product pull-right btn btn-default gbtn-default" data-id="{$productObj->id|escape:'html':'UTF-8'}" data-type="{$display|escape:'html':'UTF-8'}" ><i class="icon-pencil"></i></a>
        </td>
    </tr>
    {if $Productcombiehtml != '' }
        <tr class="gupsell_atributerow{$display|escape:'html':'UTF-8'}_{$productObj->id|escape:'html':'UTF-8'} gupsell_variant-list-box gnone">
            <td colspan="4">{$Productcombiehtml|escape:'quotes':'UTF-8'}</td>
        </tr>
    {/if}
{elseif $combination_html == 'cathtml'}
    <tr class="gupsell_collectionrow_{$cat->id|escape:'html':'UTF-8'}">
        <td>
            {$cat->id|escape:'html':'UTF-8'}
        </td>    
        <td>
            <span class="gupsellicon-img"><img class="imgm img-thumbnail" src="{$img|escape:'html':'UTF-8'}" /></span>
        </td>    
        <td>
            <a href="{$url|escape:'html':'UTF-8'}" target="_blank">{$cat->name|escape:'html':'UTF-8'}</a>
        </td>
        <td>
            <a type="button" class="gupselldelete_collection pull-right btn btn-default gbtn-default-red" data-id="{$cat->id|escape:'html':'UTF-8'}" data-type=""><i class="icon-trash"></i></a> 
            <a type="button" class="gupselledit_collection pull-right btn btn-default gbtn-default" data-id="{$cat->id|escape:'html':'UTF-8'}" data-type="" ><i class="icon-pencil"></i></a>
        </td>
    </tr>
{/if}