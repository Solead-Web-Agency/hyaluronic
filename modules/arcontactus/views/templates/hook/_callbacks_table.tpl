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
<table id="arcontactus-callbacks-table" class="table table-bordered table-striped table-condensed">
    <thead>
        <tr>
            <th>{l s='id' mod='arcontactus'}</th>
            <th>{l s='Customer' mod='arcontactus'}</th>
            <th>{l s='Phone' mod='arcontactus'}</th>
            <th>{l s='Request time' mod='arcontactus'}</th>
            <th>{l s='Update time' mod='arcontactus'}</th>
            <th>{l s='Status' mod='arcontactus'}</th>
            {if $multishop}
                <th>{l s='Shop context' mod='arcontactus'}</th>
            {/if}
            <th style="width: 120px"></th>
        </tr>
    </thead>
    <tbody>
        {if $callbacks}
            {foreach $callbacks as $model}
                <tr data-id="{$model.id_callback|escape:'htmlall':'UTF-8'}">
                    <td class="">
                        {$model.id_callback|escape:'htmlall':'UTF-8'}
                    </td>
                    <td>
                        {if $model.id_user and isset($model.customer)}
                            {$model.customer->firstname|escape:'htmlall':'UTF-8'} {$model.customer->lastname|escape:'htmlall':'UTF-8'} - {$model.customer->email|escape:'htmlall':'UTF-8'}
                        {else}
                            -
                        {/if}
                    </td>
                    <td>
                        {$model.phone|escape:'htmlall':'UTF-8'}
                    </td>
                    <td>
                        {$model.created_at|escape:'htmlall':'UTF-8'}
                    </td>
                    <td>
                        {$model.updated_at|escape:'htmlall':'UTF-8'}
                    </td>
                    <td>
                        <a href="#" onclick="arCU.callback.toggle({$model.id_callback|intval},0); return false;" style="margin-right: 3px" class="label {if $model.status == 0}label-danger{else}label-default{/if}">
                            {l s='New' mod='arcontactus'}
                        </a>

                        <a href="#" onclick="arCU.callback.toggle({$model.id_callback|intval},1); return false;" style="margin-right: 3px" class="label {if $model.status == 1}label-success{else}label-default{/if}">
                            {l s='Done' mod='arcontactus'}
                        </a>

                        <a href="#" onclick="arCU.callback.toggle({$model.id_callback|intval},2); return false;" class="label {if $model.status == 2}label-warning{else}label-default{/if}">
                            {l s='Ignored' mod='arcontactus'}
                        </a>
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
                        <a href="#" title="{l s='Delete' mod='arcontactus'}" onclick="arCU.callback.remove({$model.id_callback|intval}); return false;" data-id="{$model.id_callback|intval}" class="btn btn-default">
                            <i class="icon-trash"></i> {l s='Delete' mod='arcontactus'}
                        </a>
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