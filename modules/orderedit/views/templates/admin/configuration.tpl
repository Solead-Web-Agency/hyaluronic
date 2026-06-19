{**
* OrderEdit
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2021 silbersaiten
* @version   2.0.0
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}

<form method="post">
    <div class="panel">
        <div class="panel-heading">{l s='Orderedit configuration' mod='orderedit'}</div>
        <div class="panel-body">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Show the search orders field' mod='orderedit'}</label>
                <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="show_search_field" id="show_search_field_on" value="1"
                                   {if $show_search_field}checked="checked"{/if}/>
                            <label for="show_search_field_on">
                                {l s='Yes' mod='orderedit'}
                            </label>
                            <input type="radio" name="show_search_field" id="show_search_field_off" value="0"
                                   {if !$show_search_field}checked="checked"{/if}/>
                            <label for="show_search_field_off">
                                {l s='No' mod='orderedit'}
                            </label>
                            <a class="slide-button btn"></a>
                        </span>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" name="submitOrderEditConfig"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i>{l s='Save' mod='orderedit'}
            </button>
        </div>
    </div>
</form>
