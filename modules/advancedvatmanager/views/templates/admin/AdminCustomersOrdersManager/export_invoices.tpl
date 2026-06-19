{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
<div id="orders_invoices_donwload" class="col-lg-4 panel">
    <h3><i class="fal fa-file-export"></i></i>&nbsp;{l s='Invoice exportation' mod='advancedvatmanager'}</h3>
    <div class="alert alert-info">{l s='The date range corresponds to the order creation date.' mod='advancedvatmanager'}</div>
    <form method="POST">
        <div class="row invoice_dates_container">
            <label for="date_input_from">{l s='Date from:' mod='advancedvatmanager'}</label>  
            <div class="input-group date_container col-lg-6" data-provide="datetimepicker">
                <input id="invoice_date_input_from" name="invoice_date_input_from" type="text" class="form-control date" placeholder="{l s='Date from' mod='advancedvatmanager'}">
                <div class="input-group-addon">
                    <i class="fal fa-calendar-alt"></i>
                </div>
            </div>
            <label for="date_input_to">{l s='Date to:' mod='advancedvatmanager'}</label>  
            <div class="input-group date_container col-lg-6" data-provide="datetimepicker">
                <input id="invoice_date_input_to" name="invoice_date_input_to" type="text" class="form-control date" placeholder="{l s='Date to' mod='advancedvatmanager'}">
                <div class="input-group-addon">
                    <i class="fal fa-calendar-alt"></i>
                </div>
            </div>
        </div>
        <div class="button_export_container">
            <button type="submit" class="btn btn-success" name="submit_export_brexit_invoices"><i class="fal fa-download"></i> {l s='Download Brexit invoices' mod='advancedvatmanager'}</button>
            <button type="submit" class="btn btn-success" name="submit_export_tax_exempt_invoices"><i class="fal fa-download"></i> {l s='Download tax exempt invoices' mod='advancedvatmanager'}</button>
        </div>
    </form>
</div>
