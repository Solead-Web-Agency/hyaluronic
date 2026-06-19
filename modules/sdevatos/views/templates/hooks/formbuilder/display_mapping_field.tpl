{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevCrossProduct
 * Support by mail : contact@scaledev.fr
 *}

<div class="margin-form col-lg-4">
    {* FIELD - OPENED TAG *}
    {if array_key_exists('values', $values.$values_type)
        && is_array($values.$values_type.values)
    }
        <select
    {else}
        <input type="text"
    {/if}

    {* ATTRIBUTES *}
    {if array_key_exists('attr', $values.$values_type)
        && $values.$values_type.attr
    }
        {foreach $values.$values_type.attr as $key => $value}
            {if $value !== true}
                {if $key == 'class'}
                    class="form-control {$value|escape:'htmlall':'UTF-8'}"
                {else}
                    {$key|escape:'htmlall':'UTF-8'}="{$value|escape:'htmlall':'UTF-8'}"
                {/if}
            {else}
                {$key|escape:'htmlall':'UTF-8'}="{$key|escape:'htmlall':'UTF-8'}"
            {/if}
        {/foreach}
    {else}
        class="form-control"
    {/if}

    {* FIELD - CLOSED TAG *}
    {if array_key_exists('values', $values.$values_type)
        && is_array($values.$values_type.values)
    }
        >
            {* DEFAULT OPTION *}
            {if array_key_exists('default_value', $values.$values_type)}
                <option value="default">{$values.$values_type.default_value|escape:'htmlall':'UTF-8'}</option>
            {/if}

            {* OPTIONS *}
            {foreach $values.$values_type.values as $key => $value_original}
                <option value="{$key|escape:'htmlall':'UTF-8'}">{$value_original|escape:'htmlall':'UTF-8'}</option>
            {/foreach}
        </select>
    {else}
        value="{$values.$values_type.values|escape:'htmlall':'UTF-8'}"/>
    {/if}
</div>