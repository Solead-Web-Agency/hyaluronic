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

{function name="displayButton"}
    <input type="radio"
        {if $btn_attr}
            {foreach $btn_attr as $key => $value}
                {if $value !== true && $value !== false}
                    {$key|escape:'htmlall':'UTF-8'}="{$value|escape:'htmlall':'UTF-8'}"
                {elseif $value === true}
                    {$key|escape:'htmlall':'UTF-8'}="{$key|escape:'htmlall':'UTF-8'}"
                {/if}
            {/foreach}
        {/if}
    />

    {* BUTTON 1's - LABEL *}
    <label class="label-checkbox"
        {if $btn_attr
            && array_key_exists('id', $btn_attr)
        }
            for="{$btn_attr.id|escape:'htmlall':'UTF-8'}"
        {/if}
    >
        {$btn|escape:'htmlall':'UTF-8'}
    </label>
{/function}

{* SWITCHER - OPENED TAG *}
<span 
    {if $switcher_attr}
        {if !array_key_exists('class', $switcher_attr)}
            class="{if $is_symfony_page}ps-switch{else}switch prestashop-switch{/if}"
        {/if}
        
        {foreach $switcher_attr as $key => $value}
            {if $value !== true && $value !== false}
                {if $key == 'class'}
                    class="{if $is_symfony_page}ps-switch{else}switch prestashop-switch{/if} {$value|escape:'htmlall':'UTF-8'}"
                {else}
                    {$key|escape:'htmlall':'UTF-8'}="{$value|escape:'htmlall':'UTF-8'}"
                {/if}
            {elseif $value === true}
                {$key|escape:'htmlall':'UTF-8'}="{$key|escape:'htmlall':'UTF-8'}"
            {/if}
        {/foreach}
    {else}
        class="{if $is_symfony_page}ps-switch{else}switch prestashop-switch{/if}"
    {/if}
>

    {if $is_symfony_page}
        {displayButton btn=$btn_two btn_attr=$btn_two_attr}
        {displayButton btn=$btn_one btn_attr=$btn_one_attr}
    {else}
        {displayButton btn=$btn_one btn_attr=$btn_one_attr}
        {displayButton btn=$btn_two btn_attr=$btn_two_attr}
    {/if}

    <a class="slide-button btn"></a>

{* SWITCHER - CLOSED TAG *}
</span>