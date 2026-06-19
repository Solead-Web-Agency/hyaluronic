{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2020, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div id="elegantal_header_row" class="row" title="{l s='This is the header row number. Normally the first row will be header in import file. If your file has no header, make this number 0 here.' mod='elegantaleasyimport'}">
    <div class="col-xs-6">
        {l s='Header Row' mod='elegantaleasyimport'}:
    </div>
    <div class="col-xs-6">
        <div class="input-group">
            <span class="input-group-btn">
                <a class="btn btn-default" href="{$adminUrl|escape:'html':'UTF-8'}&event=selectHeaderRow&header_row={$model.header_row|intval - 1}&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}" {if $model.header_row < 1}disabled="disabled"{/if}>-</a>
            </span>
            <input type="text" class="form-control" value="{$model.header_row|intval}" disabled="disabled" readonly>
            <span class="input-group-btn">
                <a class="btn btn-default" href="{$adminUrl|escape:'html':'UTF-8'}&event=selectHeaderRow&header_row={$model.header_row|intval + 1}&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">+</a>
            </span>
        </div>
    </div>
    <a href="#" class="ignore_all_columns">
        <i class="icon-undo" style="font-size: 13px"></i> {l s='Unset all columns' mod='elegantaleasyimport'}
    </a>
</div>