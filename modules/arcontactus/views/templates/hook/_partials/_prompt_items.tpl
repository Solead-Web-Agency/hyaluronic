{*
* 2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*}
<div class="arcontactus-config-panel" id="arcontactus-prompt-items">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-list"></i> {l s='Prompt messages' mod='arcontactus'}
        </div>
        <div class="form-wrapper">
            <p class="text-right">
                <button class="btn btn-success" type="button" onclick="arCU.prompt.add()"><i class="icon-plus"></i> {l s='Add' mod='arcontactus'}</button>
            </p>
            {include file="./_prompt_item_form.tpl"}
            {include file="./../_prompt_table.tpl"}
        </div>
    </div>
</div>