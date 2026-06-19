{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="tab-pane panel" id="faq">
    <div class="panel-heading"><i class="icon-question"></i> {l s='FAQ' mod='worldline'}</div>
    {foreach from=$apifaq item=categorie name='faq'}
        <span class="faq-h1">{$categorie->title|escape:'htmlall':'UTF-8'}</span>
        <ul>
            {foreach from=$categorie->blocks item=QandA}
                {if !empty($QandA->question)}
                    <li>
                        <span class="faq-h2"><i class="icon-info-circle"></i> {$QandA->question|escape:'htmlall':'UTF-8'}</span>
                        <p class="faq-text hide">
                            {$QandA->answer|escape:'htmlall':'UTF-8'|replace:"\n":"<br />"}
                        </p>
                    </li>
                {/if}
            {/foreach}
        </ul>
        {if !$smarty.foreach.faq.last}<hr/>{/if}
    {/foreach}
</div>
