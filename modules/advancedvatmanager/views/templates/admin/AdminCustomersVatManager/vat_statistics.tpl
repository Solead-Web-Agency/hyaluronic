{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
<div class="panel col-lg-12 vat_statistics_panel">
    <div class="panel-heading">
        <i class="fad fa-chart-bar"></i> <span class="panel-heading-title">{l s='VAT number Statistics' mod='advancedvatmanager'}</span>
    </div>
    <div class="row">
        <div class="col-lg-3 statistic_container">
            <div class="icon_container valid_value">
                <span class="counter">{$total_vat_valid|escape:'htmlall':'UTF-8'}</span> <i class="fal fa-badge-percent"></i>
            </div>
            <p class="counter_description valid_value">{l s='Addresses with valid VAT' mod='advancedvatmanager'}</p>
        </div>
        <div class="col-lg-3 statistic_container">
            <div class="icon_container invalid_value">
                <span class="counter">{$total_vat_invalid|escape:'htmlall':'UTF-8'}</span> <i class="fal fa-badge-percent"></i>
            </div>
            <p class="counter_description invalid_value">{l s='Addresses with invalid VAT ' mod='advancedvatmanager'}</p>
        </div>
        <div class="col-lg-3 statistic_container">
            <div class="icon_container valid_value">
                <span class="counter">{$total_company_valid|escape:'htmlall':'UTF-8'}</span> <i class="fal fa-briefcase"></i>
            </div>
            <p class="counter_description valid_value">{l s='Addresses with valid company name' mod='advancedvatmanager'}</p>
        </div>
        <div class="col-lg-3 statistic_container">
            <div class="icon_container invalid_value">
                <span class="counter">{$total_company_invalid|escape:'htmlall':'UTF-8'}</span> <i class="fal fa-briefcase"></i>
            </div>
            <p class="counter_description invalid_value">{l s='Addresses with invalid company name' mod='advancedvatmanager'}</p>
        </div>          
    </div>
</div>