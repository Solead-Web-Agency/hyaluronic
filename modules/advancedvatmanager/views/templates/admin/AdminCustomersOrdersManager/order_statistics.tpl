{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
<div id="orders_statistics" class="panel">
    <h3><i class="fad fa-chart-bar"></i>&nbsp;{l s='Order statistics' mod='advancedvatmanager'}</h3>
    <div class="row dates_container">
        <label for="order_date_input_from">{l s='Date from:' mod='advancedvatmanager'}</label>  
        <div class="input-group date_container col-lg-2" data-provide="datetimepicker">
            <input id="order_date_input_from" name="order_date_input_from" type="text" class="form-control date" placeholder="{l s='Date from' mod='advancedvatmanager'}">
            <div class="input-group-addon">
                <i class="fal fa-calendar-alt"></i>
            </div>
        </div>
        <label for="order_date_input_to">{l s='Date to:' mod='advancedvatmanager'}</label>  
        <div class="input-group date_container col-lg-2" data-provide="datetimepicker">
            <input id="order_date_input_to" name="order_date_input_to" type="text" class="form-control date" placeholder="{l s='Date to' mod='advancedvatmanager'}">
            <div class="input-group-addon">
                <i class="fal fa-calendar-alt"></i>
            </div>
        </div>
        <button type="button" id="total_orders_filtered_by_dates" name="total_orders_filtered_by_dates" class="btn btn-light" value="1"><i class="fal fa-filter"></i> {l s='Filter orders' mod='advancedvatmanager'}</button>
        <button style="display:none;" type="submit" id="reset_total_orders_filter" name="reset_total_orders_filter" class="btn btn-danger" value="1"><i class="fal fa-sync-alt"></i> {l s='Reset filter' mod='advancedvatmanager'}</button>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="total_container">
                <h4 class="brexit_total"><i class="fal fa-shopping-cart"></i> {l s='Total paid in Brexit orders' mod='advancedvatmanager'}</h4>
                <div class="icon_container">
                    <img src="../modules/advancedvatmanager/views/img/united-kingdom.png" width="50" height="50"> <span class="total_brexit">{$total_brexit|escape:'htmlall':'UTF-8'}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="total_container">
                <h4 class="tax_exempt_total"><i class="fal fa-badge-percent"></i> {l s='Total paid in tax exempt orders' mod='advancedvatmanager'}</h4>
                <div class="icon_container">
                    <img src="../modules/advancedvatmanager/views/img/european-union.png" width="50" height="50"> <span class="total_tax_exempt">{$total_tax_exempt|escape:'htmlall':'UTF-8'}</span>
                </div>
            </div>
        </div>
    </div>
</div>
