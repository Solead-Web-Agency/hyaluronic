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
                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importEdit{if $model}&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}{/if}" class="btn {if $step == 1}btn-primary{else}btn-default{/if} btn-circle">1</a>
                        <p>{l s='FILE UPLOAD' mod='elegantaleasyimport'}</p>
                    </div>
                    <div class="col-xs-4 elegantalSteps-step">
                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importMapping{if $model}&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}{/if}" class="btn {if $step == 2}btn-primary{else}btn-default{/if} btn-circle" {if !$model || !$model.id_elegantaleasyimport}disabled="disabled"{/if}>2</a>
                        <p>{l s='MAPPING' mod='elegantaleasyimport'}</p>
                    </div>
                    <div class="col-xs-4 elegantalSteps-step">
                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=import{if $model}&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}{/if}" class="btn {if $step == 3}btn-primary{else}btn-default{/if} btn-circle"{if !$model || !$model.id_elegantaleasyimport || !$model.map}disabled="disabled"{/if}>3</a>
                        <p>{l s='IMPORT' mod='elegantaleasyimport'}</p>
                    </div> 
                </div>
            </div>
            <br>
        </div>
    </div>
</div>