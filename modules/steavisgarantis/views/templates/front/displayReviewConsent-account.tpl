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

<fieldset>
        <input type="hidden" name="steavisgarantis_custom" value="1" />
        <input type="hidden" name="steavisgarantis_customer_id" value="{$steavisgarantis_customer_id|escape:'htmlall':'UTF-8'}" />
        <h3 class="page-heading">{l s='Review consent' mod='steavisgarantis'}</h3>
        <p class="checkbox">
            <input type="checkbox" name="steavisgarantis_consent" id="steavisgarantis_consent" value="1" autocomplete="off" {if ($consent)}checked="checked{/if}">
            <label for="steavisgarantis_consent">{l s='Receive a review request from Guaranteed Reviews Company following my order' mod='steavisgarantis'}</label>
        </p>
</fieldset>

