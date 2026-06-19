{**
* Price increment/reduction by groups, categories and more
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*}

<script src="{$module_dir}/views/js/cd.js"></script>

{if isset($countdown) && isset($text) && $text != ''}
    {if $countdown != ''}
        <div class="col-xs-12">
            <div class="col-md-6"><div class="groupincText {if isset($classText)}{$classText}{/if}">{$text|escape:'quotes':'UTF-8' nofilter}</div></div>
            <div class="col-md-6">{$cd_style|escape:'quotes':'UTF-8' nofilter}</div>
        </div>
    {else}
        <div class="col-xs-12">
            <div class="groupincText {if isset($classText)}{$classText}{/if}">{$text|escape:'quotes':'UTF-8' nofilter}</div>
        </div>
    {/if}
{else if isset($countdown)}
    <div class="col-xs-12 {if isset($classText)}{$classText}{/if}">{$cd_style|escape:'quotes':'UTF-8' nofilter}</div>
{else if isset($text)}
    <div class="col-xs-12"><div class="groupincText ">{$text|escape:'quotes':'UTF-8' nofilter}</div></div>
{/if}



{if isset($countdown)}
    <script type="text/javascript">
        displayCountdown("{$countdown|escape:'quotes':'UTF-8' nofilter}", ".countdown_{$id_product|escape:'quotes':'UTF-8' nofilter}", "{$today|escape:'quotes':'UTF-8' nofilter}", "{$day_txt|escape:'quotes':'UTF-8' nofilter}", "{$hour_txt|escape:'quotes':'UTF-8' nofilter}", "{$minute_txt|escape:'quotes':'UTF-8' nofilter}", "{$second_txt|escape:'quotes':'UTF-8' nofilter}");
    </script>
{/if}