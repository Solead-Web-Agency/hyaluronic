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
*  @copyright  2018 Azelab
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*}
<table id="arcontactus-prompt-table" class="table table-bordered table-striped table-condensed">
    <thead>
        <tr>
            <th style="width: 120px">{l s='Position' mod='arcontactus'}</th>
            <th>{l s='Message' mod='arcontactus'}</th>
            <th>{l s='Active' mod='arcontactus'}</th>
            {if $multishop}
                <th>{l s='Shop context' mod='arcontactus'}</th>
            {/if}
            <th style="width: 120px"></th>
        </tr>
    </thead>
    <tbody>
        {if $promptModels}
            {foreach $promptModels as $model}
                <tr data-id="{$model.id_prompt|escape:'htmlall':'UTF-8'}">
                    <td class="pointer dragHandle center positionImage">
                        <div class="dragGroup">
                            <div class="positions">
                                {$model.position|escape:'htmlall':'UTF-8'}
                            </div>
                        </div>
                    </td>
                    <td>
                        {$model.message|escape:'htmlall':'UTF-8'}
                    </td>
                    <td>
                        {if $model.status}
                            <a href="#" onclick="arCU.prompt.toggle({$model.id_prompt|intval}); return false;" class="label label-success">
                                {l s='Yes' mod='arcontactus'}
                            </a>
                        {else}
                            <a href="#" onclick="arCU.prompt.toggle({$model.id_prompt|intval}); return false;" class="label label-danger">
                                {l s='No' mod='arcontactus'}
                            </a>
                        {/if}
                    </td>
                    {if $multishop}
                        <td>
                            {if $model.id_shop == 0}
                                {l s='All shops' mod='arcontactus'}
                            {else}
                                {if isset($shops[$model.id_shop])}
                                    <a href="{$link->getPageLink("index", true, null, null, false, $model.id_shop)|escape:'htmlall':'UTF-8'}" target="_blank">
                                        {$shops[$model.id_shop].name|escape:'htmlall':'UTF-8'}
                                    </a>
                                {else}
                                    [{l s='Deleted' mod='arcontactus'}]
                                {/if}
                            {/if}
                        </td>
                    {/if}
                    <td>
                        <div class="btn-group pull-right">
                            <a href="#" title="{l s='Edit' mod='arcontactus'}" onclick="arCU.prompt.edit({$model.id_prompt|intval}); return false;" class="edit btn btn-default" data-id="{$model.id_prompt|intval}">
                                <i class="icon-pencil"></i> {l s='Edit' mod='arcontactus'}
                            </a>
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <i class="icon-caret-down"></i>&nbsp;
                            </button>
                            <ul class="dropdown-menu">
                                <li>    
                                    <a href="#" title="{l s='Delete' mod='arcontactus'}" onclick="arCU.prompt.remove({$model.id_prompt|intval}); return false;" data-id="{$model.id_prompt|intval}" class="delete">
                                        <i class="icon-trash"></i> {l s='Delete' mod='arcontactus'}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="7">
                    {l s='No items found' mod='arcontactus'}
                </td>
            </tr>
        {/if}
    </tbody>
</table>