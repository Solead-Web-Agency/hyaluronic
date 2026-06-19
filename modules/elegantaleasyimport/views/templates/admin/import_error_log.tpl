{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2020, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-exclamation-triangle"></i> {l s='Error log of import rule' mod='elegantaleasyimport'} "{$model.name|escape:'html':'UTF-8'}"
        </div>
        <div class="panel-body">
            <div class="well">
                {if $model.error_log}
                    <p>{l s='Error Log:' mod='elegantaleasyimport'}</p>
                    <div>{$model.error_log|escape:'html':'UTF-8'|nl2br}</div>
                {else}
                    <p>{l s='No errors found.' mod='elegantaleasyimport'}</p>
                {/if}
            </div>
        </div>
        <div class="panel-footer">
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importClearErrorLog&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}" class="pull-right btn btn-default">
                <i class="process-icon-cancel"></i> {l s='Clear Logs' mod='elegantaleasyimport'}
            </a>
            <a href="{$adminUrl|escape:'html':'UTF-8'}" class="btn btn-default">
                <i class="process-icon-back"></i> {l s='Back' mod='elegantaleasyimport'}
            </a>
        </div>
    </div>
</div>