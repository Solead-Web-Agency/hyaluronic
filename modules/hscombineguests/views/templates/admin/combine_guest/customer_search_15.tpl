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
        <ul>
            <li>
                <div class="customerName">
                    <strong>{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}</strong>
                    <span class="customerBirthday">
                        {if isset($customer.birthday) && $customer.birthday != '0000-00-00'}
                            {$customer.birthday|escape:'htmlall':'UTF-8'}
                        {/if}
                    </span>
                </div>
                <p class="customerEmail"><a href="mailto: {$customer.email|escape:'htmlall':'UTF-8'}"> {$customer.email|escape:'htmlall':'UTF-8'}</a></p>
                                <p>
                                        {l s='Language:' mod='hscombineguests'} {$customer['language']|escape:'htmlall':'UTF-8'}<br />
                                        {l s='Newsletter:' mod='hscombineguests'} {if $customer.newsletter|escape:'htmlall':'UTF-8'}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}<br />
                                        {l s='Opt in:' mod='hscombineguests'} {if $customer.optin}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}<br />
                                        {l s='Age:' mod='hscombineguests'}
                                        {if isset($customer.birthday) && $customer.birthday != '0000-00-00'}
                                                {$customer.age|escape:'htmlall':'UTF-8'}
                                        {else}
                                                {l s='Unknown' mod='hscombineguests'}
                                        {/if}
                                </p>
                                <p>
                                        {l s='Last update:' mod='hscombineguests'} {dateFormat date=$customer.date_upd full=true}<br />
                                        {l s='Status:' mod='hscombineguests'} {if $customer.active}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}
                                </p>
                                <div class="clearfix">
                                        {if $customer.is_guest}
                                                {l s='This customer is registered as' mod='hscombineguests'} <b>&nbsp;{l s='Guest' mod='hscombineguests'}</b>
                                        {else}
                                                {l s='This customer is registered as' mod='hscombineguests'}<b>&nbsp;{l s='Customer' mod='hscombineguests'}</b>
                                        {/if}
                                        <input type="checkbox" style="float: right;" name='customers[]'value='{$customer['id_customer']|intval}'>
                                </div>
            </li>
        </ul>
    {/foreach}
<div class="clear">&nbsp;</div>
<div class="clearfix">
    <button disabled='disabled' style="float: right;" class="button" id="submitCombineGuest" type="submit">
        {l s='Combine' mod='hscombineguests'}
    </button>
</div>
</form>
{else}
    <div class="warn">{l s='No customers found' mod='hscombineguests'}</div>
{/if}
