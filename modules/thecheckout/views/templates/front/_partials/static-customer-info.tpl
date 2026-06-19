{**
* NOTICE OF LICENSE
*
* This source file is subject to the Software License Agreement
* that is bundled with this package in the file LICENSE.txt.
*
*  @author    Peter Sliacky (Prestasmart)
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}
<div id="static-customer-info-container">
  {if !$s_customer.is_guest && $s_customer.is_logged}
    <a class="edit-customer-info" href="{$urls.pages.identity|escape:'javascript':'UTF-8'}">
      <div class="static-customer-info" data-edit-label="{l s='Edit' d='Shop.Theme.Actions'}">
        <div class="customer-name">{$s_customer.firstname|escape:'htmlall':'UTF-8'} {$s_customer.lastname|escape:'htmlall':'UTF-8'}</div>
        <div class="customer-email">{$s_customer.email|escape:'htmlall':'UTF-8'}</div>
      </div>
    </a>
  {/if}
</div>
