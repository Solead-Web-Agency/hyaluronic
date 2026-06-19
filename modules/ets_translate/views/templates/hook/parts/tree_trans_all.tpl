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

{if isset($treeWebTranslations) && $treeWebTranslations && isset($wdConfig) && $wdConfig}
    {if $wdConfig == 'wd_all'}
        {$treeWebTranslations.all.title|escape:'html':'UTF-8'}
    {else}
        {assign 'ETS_TRANS_WD_CONFIG' ','|explode:$wdConfig}

        <ul>
            {foreach $treeWebTranslations as $k1=>$op}
                {if $k1 == 'all'}{continue}{/if}
                {if in_array($op.name, $ETS_TRANS_WD_CONFIG) || strpos($wdConfig, $k1) !== false}
                    <li>
                        <span>{$op.title|escape:'html':'UTF-8'} {if isset($op.items) && $op.items && in_array($op.name, $ETS_TRANS_WD_CONFIG)}({l s='All' mod='ets_translate'}){/if}</span>
                        {if isset($op.items) && $op.items && !in_array($op.name, $ETS_TRANS_WD_CONFIG)}
                            <ul class="sub-tree">
                                {foreach $op.items as $k2=> $op2}
                                    {if in_array($op2.name, $ETS_TRANS_WD_CONFIG) || strpos($wdConfig, $k2) !== false}
                                        <li>
                                            <span>{$op2.title|escape:'html':'UTF-8'} {if isset($op2.items) && $op2.items && in_array($op2.name, $ETS_TRANS_WD_CONFIG)}({l s='All' mod='ets_translate'}){/if}</span>

                                            {if isset($op2.items) && $op2.items && !in_array($op2.name, $ETS_TRANS_WD_CONFIG)}
                                                <ul class="sub-tree">
                                                    {foreach $op2.items as $k3=>$op3}
                                                        {if in_array($op3.name, $ETS_TRANS_WD_CONFIG) || strpos($wdConfig,$k3) !== false}
                                                            <li>
                                                                <span>{$op3.title|escape:'html':'UTF-8'} {if isset($op3.items) && $op3.items && in_array($op3.name, $ETS_TRANS_WD_CONFIG)}({l s='All' mod='ets_translate'}){/if}</span>
                                                                {if isset($op3.items) && $op3.items && !in_array($op3.name, $ETS_TRANS_WD_CONFIG)}
                                                                    <ul class="sub-tree">
                                                                        {foreach $op3.items as $k4=>$op4}
                                                                            {if in_array($op4.name, $ETS_TRANS_WD_CONFIG) || strpos($wdConfig, $k4) !== false}
                                                                                <li>
                                                                                    <span>{$op4.title|escape:'html':'UTF-8'} {if isset($op4.items) && $op4.items && in_array($op4.name, $ETS_TRANS_WD_CONFIG)}({l s='All' mod='ets_translate'}){/if}</span>
                                                                                    {if isset($op4.items) && $op4.items && !in_array($op4.name, $ETS_TRANS_WD_CONFIG)}
                                                                                        <ul class="sub-tree">
                                                                                            {foreach $op4.items as $k5=>$op5}
                                                                                                {if in_array($op5.name, $ETS_TRANS_WD_CONFIG) || strpos($wdConfig, $k5) !== false}
                                                                                                    <li>
                                                                                                        <span>{$op5.title|escape:'html':'UTF-8'} {if isset($op5.items) && $op5.items && in_array($op5.name, $ETS_TRANS_WD_CONFIG)}({l s='All' mod='ets_translate'}){/if}</span>
                                                                                                        {if isset($op5.emails) && $op5.emails && !in_array($op5.name, $ETS_TRANS_WD_CONFIG)}
                                                                                                            <ul class="sub-tree">
                                                                                                                {foreach $op5.emails as $mailItem}
                                                                                                                    {if in_array($mailItem.val, $ETS_TRANS_WD_CONFIG)}
                                                                                                                        <li>
                                                                                                                            <span>{$mailItem.title|escape:'html':'UTF-8'}</span>
                                                                                                                        </li>
                                                                                                                    {/if}
                                                                                                                {/foreach}
                                                                                                            </ul>
                                                                                                        {/if}
                                                                                                    </li>
                                                                                                {/if}
                                                                                            {/foreach}
                                                                                        </ul>
                                                                                    {/if}
                                                                                    {if isset($op4.emails) && $op4.emails && !in_array($op4.name, $ETS_TRANS_WD_CONFIG)}
                                                                                        <ul class="sub-tree">
                                                                                            {foreach $op4.emails as $k5=>$mailItem}
                                                                                                {if in_array($mailItem.val, $ETS_TRANS_WD_CONFIG)}
                                                                                                    <li>
                                                                                                        <span>{$mailItem.title|escape:'html':'UTF-8'}</span>
                                                                                                    </li>
                                                                                                {/if}
                                                                                            {/foreach}
                                                                                        </ul>
                                                                                    {/if}
                                                                                </li>
                                                                            {/if}
                                                                        {/foreach}
                                                                    </ul>
                                                                {/if}
                                                            </li>
                                                        {/if}
                                                    {/foreach}
                                                </ul>
                                            {/if}
                                        </li>
                                    {/if}
                                {/foreach}
                            </ul>
                        {/if}
                    </li>
                {/if}
            {/foreach}
        </ul>
    {/if}
{/if}