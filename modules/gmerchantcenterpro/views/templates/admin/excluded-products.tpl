{*
*
* Google merchant center Pro
*
* @author BusinessTech.fr
* @copyright Business Tech
*
*           ____    _______
*          |  _ \  |__   __|
*          | |_) |    | |
*          |  _ <     | |
*          | |_) |    | |
*          |____/     |_|
*
*}
<div class="bootstrap" id="gmc" style="min-width: 300px;">
    <h4 class="text-center"><i class="fa fa-exclamation-circle"></i> {l s='Excluded product by this rule' mod='gmerchantcenterpro'} </h4>
    <div class="clr_10"></div>
    <div class="clr_hr"></div>
    <div class="clr_20"></div>

    {if !empty($aProductsData)}
        <table class="table table-bordered">
            <thead>
            <th class="text-center"><b>{l s='id' mod='gmerchantcenterpro'}</b></th>
            <th class="text-center"><b>{l s='Product name' mod='gmerchantcenterpro'}</b></th>
            </thead>
            <tbody>
            {foreach from=$aProductsData item=aProduct key=sKey}
                <tr class="text-center">
                    <td> {$aProduct.id|escape:'htmlall':'UTF-8'} </td>
                    <td> {$aProduct.name|escape:'htmlall':'UTF-8'}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {else}
        <p class="alert alert-danger">
            {l s='No products affected by this rule' mod='gmerchantcenterpro'}
        </p>
    {/if}
</div>
