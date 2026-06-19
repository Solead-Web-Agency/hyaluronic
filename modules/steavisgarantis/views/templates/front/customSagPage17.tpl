{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*
*}

{extends file='page.tpl'}

{block name='page_title'}
  {l s='Review requests consent' mod='steavisgarantis'}
{/block}

{block name='page_content'}
{capture name=path}
        <a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}" rel="nofollow" title="{l s='My Account' mod='steavisgarantis'}">{l s='My Account' mod='steavisgarantis'}</a>
        <span class="navigation-pipe">&gt;</span>
        {l s='Review requests consent' mod='steavisgarantis'}
{/capture}
    
<div class="box">
    <form action="{$link->getModuleLink('steavisgarantis', 'consent')|escape:'htmlall':'UTF-8'}" method="post">
        <fieldset>
            <input type="hidden" name="steavisgarantis_custom" value="1" />
            <input type="hidden" name="steavisgarantis_customer_id" value="{$steavisgarantis_customer_id|escape:'htmlall':'UTF-8'}" />
            <h3 class="page-heading">{l s='Review consent' mod='steavisgarantis'}</h3>
            <p class="checkbox">
                <input type="checkbox" name="steavisgarantis_consent" id="steavisgarantis_consent" value="1" autocomplete="off" {if ($consent)}checked="checked{/if}">
                <label for="steavisgarantis_consent">{l s='Receive a review request from Guaranteed Reviews Company following my order' mod='steavisgarantis'}</label>
            </p>
        </fieldset>
        <p class="required submit">
            <button type="submit" name="submitAccount" id="submitAccount" class="btn btn-default button button-small">
                <span>
                    {l s='Save' mod='steavisgarantis'}
                    <i class="icon-chevron-right right"></i>
                </span>
            </button>
            <span><sup>*</sup>{l s='Required field' mod='steavisgarantis'}</span>
        </p>
    </form>
</div>
{/block}
{block name='page_footer'}
  {block name='my_account_links'}
    {include file='customer/_partials/my-account-links.tpl'}
  {/block}
{/block}

