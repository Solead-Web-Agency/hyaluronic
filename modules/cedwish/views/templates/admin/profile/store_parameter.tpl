<!--
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */
-->

<div>
    <div class="alert alert-info" role="alert">
        <p class="alert-text">
            {l s='These Paramters will decide which items belong to this profile.' mod='cedwish'}
        </p>
    </div>
    <div class="form-group form-group-lg">
        <label class="form-control-label" for="name">
            {l s='Name' mod='cedwish'}
        </label>
        <input
                type="text"
                class="form-control form-control-lg"
                placeholder="{l s='Enter Any Name or Identifier for this profile.' mod='cedwish'}"
                id="name"
                name="name"
                value="{if $profile && $profile->name}{$profile->name|escape:'htmlall':'UTF-8'}{/if}"
        />
        <input
                type="hidden"
                class="form-control form-control-lg"
                placeholder="{l s='Enter Any Name or Identifier for this profile.' mod='cedwish'}"
                id="id_cedwish_profile"
                name="id_cedwish_profile"
                value="{if $profile && $profile->id_cedwish_profile}{$profile->id_cedwish_profile|escape:'htmlall':'UTF-8'}{/if}"
        />
    </div>
    <div class="form-group form-group-lg">
        <label class="form-control-label" for="price_from">{l s='Price From' mod='cedwish'}</label>
        <input
                type="text"
                class="form-control form-control-lg"
                placeholder="{l s='Enter Any Name or Identifier for this profile.' mod='cedwish'}"
                id="name"
                name="price_from"
                value="{if $profile && $profile->price_from}{$profile->price_from|escape:'htmlall':'UTF-8'}{/if}"
        />
    </div>
    <div class="form-group form-group-lg">
        <label class="form-control-label" for="price_to">{l s='Price To' mod='cedwish'}</label>
        <input
                type="text"
                class="form-control form-control-lg"
                placeholder="{l s='Enter Any Name or Identifier for this profile.' mod='cedwish'}"
                id="name"
                name="price_to"
                value="{if $profile && $profile->price_to}{$profile->price_to|escape:'htmlall':'UTF-8'}{/if}"
        />
    </div>
    <div class="form-group form-group-lg">
        <label class="form-control-label" for="manufacturers">{l s='Manufacturer' mod='cedwish'}</label>
        <select class=form-control" multiple name="manufacturers[]" data-toggle="manufacturers" aria-hidden="true">
            {foreach $manufacturer_list as $manufacturer}
                {if in_array($manufacturer['id_manufacturer'], $selected_manufacturers)}
                    <option
                            value="{$manufacturer['id_manufacturer']|escape:'htmlall':'UTF-8'}"
                            selected="selected"
                    >
                        {else}
                    <option
                    value="{$manufacturer['id_manufacturer']|escape:'htmlall':'UTF-8'}"
                    >
                {/if}
                {$manufacturer['name']|escape:'htmlall':'UTF-8'}
                </option>
            {/foreach}
        </select>
    </div>
    <div class="form-group form-group-lg">
        <label class="form-control-label" for="suppliers">{l s='Suppliers' mod='cedwish'}</label>
        <select class=form-control" multiple name="suppliers[]" data-toggle="suppliers" aria-hidden="true">
            {foreach $supplier_list as $supplier}
                {if in_array($supplier['id_supplier'], $selected_suppliers)}
                    <option
                            value="{$supplier['id_supplier']|escape:'htmlall':'UTF-8'}"
                            selected="selected"
                    >
                        {else}
                    <option
                    value="{$supplier['id_supplier']|escape:'htmlall':'UTF-8'}"
                    >
                {/if}
                {$supplier['name']|escape:'htmlall':'UTF-8'}
                </option>
            {/foreach}
        </select>
    </div>
    <div class="form-group form-group-lg">
        <label class="form-control-label" for="categories">{l s='Categories' mod='cedwish'}</label>
        {$storeCategories}
    </div>
    <div>
        <label class="form-control-label" for="status">{l s='Status' mod='cedwish'}</label>
        <select class=form-control" name="status" data-toggle="status" aria-hidden="true">
            {if $profile && $profile->status}
                <option selected="selected" value="1">{l s='Active' mod='cedwish'}</option>
                <option value="0">{l s='Inactive' mod='cedwish'}</option>
            {else}
                <option value="1">{l s='Active' mod='cedwish'}</option>
                <option selected="selected" value="0">{l s='Inactive' mod='cedwish'}</option>
            {/if}
        </select>
    </div>
</div>
