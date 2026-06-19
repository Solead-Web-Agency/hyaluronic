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

{* FORM-GROUP - OPENED TAG *}
{if $form_group}
    <div
        {if $form_group_attr}
            {if !array_key_exists('class', $form_group_attr)}
                class="form-group {if $is_symfony_page}row{/if}"
            {/if}

            {foreach $form_group_attr as $key => $value}
                {if $value !== true && $value !== false}
                    {if $key == 'class'}
                        class="form-group {$value|escape:'htmlall':'UTF-8'} {if $is_symfony_page}row{/if}"
                    {else}
                        {$key|escape:'htmlall':'UTF-8'}="{$value|escape:'htmlall':'UTF-8'}"
                    {/if}
                {elseif $value === true}
                    {$key|escape:'htmlall':'UTF-8'}="{$key|escape:'htmlall':'UTF-8'}"
                {/if}
            {/foreach}
        {else}
            class="form-group {if $is_symfony_page}row{/if}"
        {/if}
    >
{/if}

{* LABEL *}
{if $label}
    <label
        {if $attr
            && array_key_exists('id', $attr)
        }
            for="{$attr.id|escape:'htmlall':'UTF-8'}"
        {/if}

        {if $label_attr}
            {foreach $label_attr as $key => $value}
                {if $value !== true && $value !== false}
                    {$key|escape:'htmlall':'UTF-8'}="{if $is_symfony_page}{$value|replace:'control-label':'form-control-label'|escape:'htmlall':'UTF-8'}{else}{$value|escape:'htmlall':'UTF-8'}{/if}"
                {else $value === true}
                    {$key|escape:'htmlall':'UTF-8'}="{$key|escape:'htmlall':'UTF-8'}"
                {/if}
            {/foreach}
        {/if}
    >
        {$label|escape:'htmlall':'UTF-8'}
    </label>
{/if}

{* MARGIN-FORM - OPENED TAG *}
{if $margin_form_attr}
    <div
        {if !array_key_exists('class', $margin_form_attr)}
            class="{if $is_symfony_page}col-sm{else}margin-form{/if}"
        {/if}
        
        {foreach $margin_form_attr as $key => $value}
            {if $value !== true && $value !== false}
                {if $key == 'class'}
                    class="{if $is_symfony_page}{$value|regex_replace:'/^col-([a-z]+-[0-9]+)$/':'col-sm'|escape:'htmlall':'UTF-8'}{else}margin-form {$value|escape:'htmlall':'UTF-8'}{/if}"
                {else}
                    {$key|escape:'htmlall':'UTF-8'}="{$value|escape:'htmlall':'UTF-8'}"
                {/if}
            {elseif $value === true}
                {$key|escape:'htmlall':'UTF-8'}="{$key|escape:'htmlall':'UTF-8'}"
            {/if}
        {/foreach}
    >
{/if}

{* PREFIX - OPENED TAG *}
{if $prefix_left
    || $prefix_right
}
    <div class="input-group">
{/if}

{* PREFIX LEFT *}
{if $prefix_left}
    <div class="input-group-{if $is_symfony_page}prepend{else}addon{/if}">
        <span class="input-group-text">{$prefix_left|escape:'htmlall':'UTF-8'}</span>
    </div>
{/if}