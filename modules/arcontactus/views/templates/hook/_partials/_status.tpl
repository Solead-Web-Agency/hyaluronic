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
<a href="#" data-status="0" data-id="{$model.id_callback|intval}" onclick="arCU.callback.toggle({$model.id_callback|intval},0); return false;" style="margin-right: 3px" class="arcu-status-label label {if $model.status == 0}label-danger{else}label-default{/if}">
    {l s='New' mod='arcontactus'}
</a>

<a href="#" data-status="1" data-id="{$model.id_callback|intval}" onclick="arCU.callback.toggle({$model.id_callback|intval},1); return false;" style="margin-right: 3px" class="arcu-status-label label {if $model.status == 1}label-success{else}label-default{/if}">
    {l s='Done' mod='arcontactus'}
</a>

<a href="#" data-status="2" data-id="{$model.id_callback|intval}" onclick="arCU.callback.toggle({$model.id_callback|intval},2); return false;" class="arcu-status-label label {if $model.status == 2}label-warning{else}label-default{/if}">
    {l s='Ignored' mod='arcontactus'}
</a>