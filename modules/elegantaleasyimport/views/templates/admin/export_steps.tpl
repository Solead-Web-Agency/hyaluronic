{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2020, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2">
            <div class="elegantalSteps">
                <div class="row elegantalSteps-row">
                    <div class="col-xs-4 elegantalSteps-step">
                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=exportEdit{if $model}&id_elegantaleasyimport_export={$model.id_elegantaleasyimport_export|intval}{/if}" class="btn {if $step == 1}btn-primary{else}btn-default{/if} btn-circle">1</a>
                        <p>{l s='CONFIGURE' mod='elegantaleasyimport'}</p>
                    </div>
                    <div class="col-xs-4 elegantalSteps-step">
                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=exportColumns{if $model}&id_elegantaleasyimport_export={$model.id_elegantaleasyimport_export|intval}{/if}" class="btn {if $step == 2}btn-primary{else}btn-default{/if} btn-circle" {if !$model || !$model.id_elegantaleasyimport_export}disabled="disabled"{/if}>2</a>
                        <p>{l s='CHOOSE COLUMNS' mod='elegantaleasyimport'}</p>
                    </div>
                    <div class="col-xs-4 elegantalSteps-step">
                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=export{if $model}&id_elegantaleasyimport_export={$model.id_elegantaleasyimport_export|intval}{/if}" class="btn {if $step == 3}btn-primary{else}btn-default{/if} btn-circle"{if !$model || !$model.id_elegantaleasyimport_export || !$model.columns}disabled="disabled"{/if}>3</a>
                        <p>{l s='EXPORT' mod='elegantaleasyimport'}</p>
                    </div> 
                </div>
            </div>
            <br>
        </div>
    </div>
</div>