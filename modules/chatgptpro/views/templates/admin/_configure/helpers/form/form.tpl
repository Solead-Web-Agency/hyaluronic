{*
* 2007-2023 Weblir
*
*  @author    weblir <hello@weblir.com>
*  @copyright 2012-2023 weblir
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*  International Registered Trademark & Property of weblir.com
*
*  You are allowed to modify this copy for your own use only. You must not redistribute it. License
*  is permitted for one Prestashop instance only but you can install it on your test instances.
*}

{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.type == 'rangeslider'}
        <div class="col-lg-8">
            <div id="slider-range_{$input.name}"></div>
            <p>
                <label for="{$input.name}">{l s='Current value' mod='chatgptpro'}:</label>
                <input type="text" id="{$input.name}" name="{$input.name}" readonly style="border:0; color:#f6931f; font-weight:bold;">
            </p>
            <p class="help-block">{$input.desc}</p>
        </div>

        <script type="text/javascript">
        {literal}
            $( function() {
                $( "#slider-range_{/literal}{$input.name}{literal}" ).slider({
                     min: {/literal}{$input.min|floatval}{literal},
                     max: {/literal}{$input.max|floatval}{literal},
                     step: {/literal}{$input.step|floatval}{literal},
                     value: {/literal}{$input.value|floatval}{literal},
                     slide: function( event, ui ) {
                         $( "#{/literal}{$input.name}{literal}" ).val(ui.value);
                     }
                });
                $( "#{/literal}{$input.name}{literal}" ).val({/literal}{$input.value|floatval}{literal});
            });
        {/literal}
        </script>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}