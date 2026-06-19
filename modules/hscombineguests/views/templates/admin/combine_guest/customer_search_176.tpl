{**
* Combine guests for PrestaShop
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if !empty($customers)}
    <form id="hs_combine_guests" action="{$link|escape:'htmlall':'UTF-8'}&current_customer_id={$curent_customer_id|intval}&action=combineGuests" method="post">
        <div class="row">
        {foreach from=$customers item=customer}
            <div class="customerCard col-lg-3 cleafix">
                <div class="col2">
                    <div class="card" >
                        <h4 class="card-header">
                            <i class="material-icons">person</i>
                            {$customer.firstname|escape:'htmlall':'UTF-8'}
                            {$customer.lastname|escape:'htmlall':'UTF-8'}
                            [{$customer.id_customer|intval}]
                            &nbsp;-&nbsp;
                            <a href="mailto:{$customer.email|escape:'htmlall':'UTF-8'}"><i class="icon-envelope"></i>
                                {$customer.email|escape:'htmlall':'UTF-8'}
                            </a>
                        </h4>
                        <div class="card-body">
                            <div class="row mb-1">
                                <div class="col-4 text-right">{l s='Social Title' mod='hscombineguests'}</div>
                                <div class="col-8">
                                    {$customer.sex|escape:'htmlall':'UTF-8'}
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-4 text-right">{l s='Age' mod='hscombineguests'}</div>
                                <div class="col-8">
                                    {if isset($customer.birthday) && $customer.birthday != '0000-00-00'}
                                        {l s='%1d years old (birth date: %s)' sprintf=[$customer['age'], $customer['birthday']] mod='hscombineguests'}
                                    {else}
                                        {l s='Unknown' mod='hscombineguests'}
                                    {/if}
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-4 text-right">{l s='Registration Date' mod='hscombineguests'}</div>
                                <div class="col-8">{dateFormat date=$customer.date_add full=true}</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-4 text-right">{l s='Last Visit' mod='hscombineguests'}</div>
                                <div class="col-8">{$customer.last_visit|escape:'htmlall':'UTF-8'}</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-4 text-right">{l s='Language' mod='hscombineguests'}</div>
                                <div class="col-8">{$customer.language|escape:'htmlall':'UTF-8'}</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-4 text-right">{l s='Registrations' mod='hscombineguests'}</div>
                                <div class="col-8">
                                    {if $customer.newsletter}
                                        <span class="badge badge-success rounded pt-0 pb-0">
                                            <i class="material-icons">check</i>{l s='Newsletter' mod='hscombineguests'}
                                        </span>
                                    {else}
                                        <span class="badge badge-danger rounded pt-0 pb-0">
                                            <i class="material-icons">cancel</i>{l s='Newsletter' mod='hscombineguests'}
                                        </span>
                                    {/if}
                                    &nbsp;
                                    {if $customer.optin}
                                        <span class="badge badge-success rounded pt-0 pb-0">
                                            <i class="material-icons">check</i>{l s='Partner offers' mod='hscombineguests'}
                                        </span>
                                    {else}
                                        <span class="badge badge-danger rounded pt-0 pb-0">
                                            <i class="material-icons">cancel</i>{l s='Partner offers' mod='hscombineguests'}
                                        </span>
                                    {/if}
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-4 text-right">{l s='Latest Update' mod='hscombineguests'}</div>
                                <div class="col-8">
                                    {dateFormat date=$customer.date_upd full=true}
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-4 text-right">{l s='Status' mod='hscombineguests'}</div>
                                <div class="col-8">
                                    {if $customer.active}
                                        <span class="badge badge-success rounded pt-0 pb-0">
                                            <i class="material-icons">check</i>{l s='Active' mod='hscombineguests'}
                                        </span>
                                    {else}
                                        <span class="badge badge-danger rounded pt-0 pb-0">
                                            <i class="material-icons">cancel</i>{l s='Inactive' mod='hscombineguests'}
                                        </span>
                                    {/if}
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
            </div>
        {/foreach}
        <div class="col-lg-12">
            <button disabled='disabled' class="btn btn-default pull-right btn-primary" id="submitCombineGuest" type="submit">
                <i class="material-icons">arrow_right</i>
                &nbsp;{l s='Combine' mod='hscombineguests'}
            </button>
        </div>
    </div>
    </form>
{else}
    <div class="alert alert-warning"><i class="icon-warning-sign"></i>&nbsp;{l s='No customers found' mod='hscombineguests'}</div>
{/if}
