{*
* 2007-2020 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2020 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<ul class="ets-trans-tree-web-page">
    {foreach $treeWebPageOption as $op}
        <li>
            <input type="checkbox" id="{$op.name|escape:'html':'UTF-8'}" name="trans_wd[]" value="{$op.name|escape:'html':'UTF-8'}" {if in_array($op.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if}/>
            <label for="{$op.name|escape:'html':'UTF-8'}">{$op.title|escape:'html':'UTF-8'}</label>
            {if isset($op.items) && $op.items}
                <a data-toggle="collapse" class="collapsed" href="#cp_{$op.name|escape:'html':'UTF-8'}"><i class="fa fa-angle-down"></i></a>
                <ul class="sub-tree collapse" id="cp_{$op.name|escape:'html':'UTF-8'}">
                    {foreach $op.items as $op2}
                        <li>
                            <input type="checkbox" id="{$op2.name|escape:'html':'UTF-8'}" name="trans_wd[]" value="{$op2.name|escape:'html':'UTF-8'}" {if in_array($op2.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if}/>
                            <label for="{$op2.name|escape:'html':'UTF-8'}">{$op2.title|escape:'html':'UTF-8'}</label>
                            {if isset($op2.items) && $op2.items}
                                <a data-toggle="collapse" href="#cp_{$op2.name|escape:'html':'UTF-8'}" class="collapsed"><i class="fa fa-angle-down"></i></a>
                                <ul class="sub-tree collapse" id="cp_{$op2.name|escape:'html':'UTF-8'}">
                                    {foreach $op2.items as $op3}
                                        <li>
                                            <input type="checkbox" id="{$op3.name|escape:'html':'UTF-8'}" name="trans_wd[]"
                                                   value="{$op3.name|escape:'html':'UTF-8'}" {if in_array($op3.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                            <label for="{$op3.name|escape:'html':'UTF-8'}">{$op3.title|escape:'html':'UTF-8'}</label>
                                            {if isset($op3.items) && $op3.items}
                                                <a data-toggle="collapse" class="collapsed" href="#cp_{$op3.name|escape:'html':'UTF-8'}"><i class="fa fa-angle-down"></i></a>
                                                <ul class="sub-tree collapse" id="cp_{$op3.name|escape:'html':'UTF-8'}">
                                                    {foreach $op3.items as $op4}
                                                        <li>
                                                            <input type="checkbox" id="{$op4.name|escape:'html':'UTF-8'}" name="trans_wd[]"
                                                                   value="{$op4.name|escape:'html':'UTF-8'}" {if in_array($op4.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                                            <label for="{$op4.name|escape:'html':'UTF-8'}">{$op4.title|escape:'html':'UTF-8'}</label>
                                                            {if isset($op4.items) && $op4.items}
                                                                <a data-toggle="collapse" class="collapsed" href="#cp_{$op4.name|escape:'html':'UTF-8'}"><i class="fa fa-angle-down"></i></a>
                                                                <ul class="sub-tree collapse" id="cp_{$op4.name|escape:'html':'UTF-8'}">
                                                                    {foreach $op4.items as $op5}
                                                                        <li>
                                                                            <input type="checkbox" id="{$op5.name|escape:'html':'UTF-8'}" name="trans_wd[]"
                                                                                   value="{$op5.name|escape:'html':'UTF-8'}" {if in_array($op5.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                                                            <label for="{$op5.name|escape:'html':'UTF-8'}">{$op5.title|escape:'html':'UTF-8'}</label>
                                                                            {if isset($op5.emails) && $op5.emails}
                                                                                <a data-toggle="collapse" class="collapsed" href="#cp_{$op5.name|escape:'html':'UTF-8'}"><i class="fa fa-angle-down"></i></a>
                                                                                <ul class="sub-tree collapse" id="cp_{$op5.name|escape:'html':'UTF-8'}">
                                                                                    {foreach $op5.emails as $mailItem}
                                                                                        <li>
                                                                                            <input type="checkbox" id="{$mailItem.val|escape:'html':'UTF-8'}" name="trans_wd[]"
                                                                                                   value="{$mailItem.val|escape:'html':'UTF-8'}" {if in_array($mailItem.val, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                                                                            <label for="{$mailItem.val|escape:'html':'UTF-8'}">
                                                                                                ({if $mailItem.type == 'core_email'}{l s='Core email' mod='ets_translate'}{else}{l s='Module:' mod='ets_translate'} {$mailItem.name|escape:'html':'UTF-8'}{/if}
                                                                                                ) {$mailItem.file|escape:'html':'UTF-8'}
                                                                                            </label>
                                                                                        </li>
                                                                                    {/foreach}
                                                                                </ul>
                                                                            {/if}
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            {/if}
                                                            {if isset($op4.emails) && $op4.emails}
                                                                <a data-toggle="collapse" class="collapsed" href="#em_{$op4.name|escape:'html':'UTF-8'}"><i class="fa fa-angle-down"></i></a>
                                                                <ul class="sub-tree collapse" id="em_{$op4.name|escape:'html':'UTF-8'}">
                                                                    {foreach $op4.emails as $mailItem}
                                                                        <li>
                                                                            <input type="checkbox" id="{$mailItem.val|escape:'html':'UTF-8'}" name="trans_wd[]"
                                                                                   value="{$mailItem.val|escape:'html':'UTF-8'}" {if in_array($mailItem.val, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                                                            <label for="{$mailItem.val|escape:'html':'UTF-8'}">
                                                                                ({if $mailItem.type == 'core_email'}{l s='Core email' mod='ets_translate'}{else}{l s='Module:' mod='ets_translate'} {$mailItem.name|escape:'html':'UTF-8'}{/if}
                                                                                ) {$mailItem.file|escape:'html':'UTF-8'}
                                                                            </label>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            {/if}
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            {/if}
                                        </li>
                                    {/foreach}
                                </ul>
                            {/if}
                        </li>
                    {/foreach}
                </ul>
            {/if}
        </li>
    {/foreach}
</ul>