{**
 * Combine guests for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}


{if !empty($customers)}
    <form id="hs_combine_guests" action="{$link|escape:'htmlall':'UTF-8'}&current_customer_id={$curent_customer_id|intval}&action=combineGuests" method="post">
   {foreach from=$customers item=customer}
    <div class="customerCard col-lg-3">
        <div class="panel clearfix">
           <div class="panel-heading">
                    <i class="icon-user"></i>
                    {$customer.firstname|escape:'htmlall':'UTF-8'}
                    {$customer.lastname|escape:'htmlall':'UTF-8'}
                    [{$customer.id_customer|intval}]
                    &nbsp;-&nbsp;
                    <a href="mailto:{$customer.email|escape:'htmlall':'UTF-8'}"><i class="icon-envelope"></i>
                            {$customer.email|escape:'htmlall':'UTF-8'}
                    </a>
            </div>
            <div class="form-horizontal">
               <div class="row">
                        <label class="control-label col-lg-3">{l s='Social Title' mod='hscombineguests'}</label>
                        <div class="col-lg-8">
                                <p class="form-control-static">{$customer.sex|escape:'htmlall':'UTF-8'}</p>
                        </div>
                </div>
                <div class="row">
                        <label class="control-label col-lg-3">{l s='Age' mod='hscombineguests'}</label>
                        <div class="col-lg-8">
                                <p class="form-control-static">
                                        {if isset($customer.birthday) && $customer.birthday != '0000-00-00'}
                                                {l s='%1d years old (birth date: %s)' sprintf=[$customer['age'], $customer['birthday']] mod='hscombineguests'}
                                        {else}
                                                {l s='Unknown' mod='hscombineguests'}
                                        {/if}
                                </p>
                        </div>
                </div>
               <div class="row">
                    <label class="control-label col-lg-3">{l s='Registration Date' mod='hscombineguests'}</label>
                    <div class="col-lg-8">
                            <p class="form-control-static">{dateFormat date=$customer.date_add full=true}</p>
                    </div>
                </div>
               <div class="row">
                        <label class="control-label col-lg-3">{l s='Last Visit' mod='hscombineguests'}</label>
                        <div class="col-lg-8">
                                <p class="form-control-static"> {$customer.last_visit|escape:'htmlall':'UTF-8'}</p>
                        </div>
                </div>
                <div class="row">
                    <label class="control-label col-lg-3">{l s='Language' mod='hscombineguests'}</label>
                    <div class="col-lg-8">
                        <p class="form-control-static">
                            {$customer.language|escape:'htmlall':'UTF-8'}
                        </p>
                    </div>
                </div>
                <div class="row">
                    <label class="control-label col-lg-3">{l s='Registrations' mod='hscombineguests'}</label>
                    <div class="col-lg-8">
                            <p class="form-control-static">
                                    {if $customer.newsletter}
                                            <span class="label label-success">
                                                    <i class="icon-check"></i>
                                                    {l s='Newsletter' mod='hscombineguests'}
                                            </span>
                                    {else}
                                            <span class="label label-danger">
                                                    <i class="icon-remove"></i>
                                                    {l s='Newsletter' mod='hscombineguests'}
                                            </span>
                                    {/if}
                                    &nbsp;
                                    {if $customer.optin}
                                            <span class="label label-success">
                                                    <i class="icon-check"></i>
                                                    {l s='Opt in' mod='hscombineguests'}
                                            </span>
                                            {else}
                                            <span class="label label-danger">
                                                    <i class="icon-remove"></i>
                                                    {l s='Opt in' mod='hscombineguests'}
                                            </span>
                                    {/if}
                            </p>
                    </div>
                </div>
                <div class="row">
                    <label class="control-label col-lg-3">{l s='Latest Update' mod='hscombineguests'}</label>
                    <div class="col-lg-8">
                            <p class="form-control-static">{dateFormat date=$customer.date_upd full=true}</p>
                    </div>
                </div>
                <div class="row">
                        <label class="control-label col-lg-3">{l s='Status' mod='hscombineguests'}</label>
                        <div class="col-lg-8">
                                <p class="form-control-static">
                                        {if $customer.active}
                                                <span class="label label-success">
                                                        <i class="icon-check"></i>
                                                        {l s='Active' mod='hscombineguests'}
                                                </span>
                                        {else}
                                                <span class="label label-danger">
                                                        <i class="icon-remove"></i>
                                                        {l s='Inactive' mod='hscombineguests'}
                                                </span>
                                        {/if}
                                </p>
                        </div>
                </div>
            </div>
                            <div class="panel-footer">
                                    {if $customer.is_guest}
                                            {l s='This customer is registered as' mod='hscombineguests'} <b>{l s='Guest' mod='hscombineguests'}</b>
                                    {else}
                                            {l s='This customer is registered as ' mod='hscombineguests'}<b>{l s='Customer' mod='hscombineguests'}</b>
                                    {/if}

                                    <input type="checkbox" class="setup-customer btn btn-default pull-right" name='customers[]'value='{$customer['id_customer']|intval}'>
                            </div>
        </div>
    </div>
    {/foreach}
    <div class="col-lg-12">
        <button disabled='disabled' class="btn btn-default pull-right" id="submitCombineGuest" type="submit">
            <i class="icon-arrow-right"></i>
            &nbsp;{l s='Combine' mod='hscombineguests'}
        </button>
    </div>

     </form>
{else}
    <div class="alert alert-warning"><i class="icon-warning-sign"></i>&nbsp;{l s='No customers found' mod='hscombineguests'}</div>
{/if}
