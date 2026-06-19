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

{foreach $options as $radio_key => $radio name=radio_nb}
    <div class="radio{if $options_disabled && in_array($radio_key, $options_disabled)} disabled{/if}">
        <label>
            <input type="radio" value="{$radio_key|escape:'htmlall':'UTF-8'}"
                {if $attr}
                    {if $options_disabled
                        && in_array($radio_key, $options_disabled)
                    }
                        disabled="disabled"
                    {/if}
                    
                    {foreach $attr as $key => $value}
                        {if $value !== true}
                            {if $key == 'id'}
                                id="{$value|escape:'htmlall':'UTF-8'}_{$smarty.foreach.radio_nb.iteration}"
                                name="{$value|escape:'htmlall':'UTF-8'}"
                            {else}
                                {$key|escape:'htmlall':'UTF-8'}="{$value|escape:'htmlall':'UTF-8'}"
                            {/if}
                        {else}
                            {$key|escape:'htmlall':'UTF-8'}="{$key|escape:'htmlall':'UTF-8'}"
                        {/if}
                    {/foreach}
                {else}
                    {if $options_disabled
                        && in_array($radio_key, $options_disabled)
                    }
                        disabled="disabled"
                    {/if}
                {/if}

                {if $default_option
                    && $radio_key == $default_option
                }
                    checked="checked"
                {/if}
            >
            {$radio|escape:'htmlall':'UTF-8'}
        </label>
    </div>
{/foreach}