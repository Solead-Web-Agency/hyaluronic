{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}

<!-- Modal for checking VAT START -->
<div id="avm_checkCustomerVAT" class="avm_modal_check_vat" data-sm-init="true">
    <div class="slickmodal-header">
        <div class="row slickmodal-title">
            <h2 class="title"><i class="fal fa-fingerprint"></i> {l s='Check VAT system' mod='advancedvatmanager'}</h2>
        </div>
    </div>
    <div class="slickmodal-body">
        <div class="loading_container">
            <div class="loader_center"></div>
            <h4>{l s='Checking...' mod='advancedvatmanager'}</h4>
        </div>
        
        <div style="display:none" class="vat-result">
            <div style="display:none" class="valid"><i class="far fa-check-circle"></i> <span class="valid_message"></span></div>
            <div style="display:none" class="invalid"><i class="far fa-times-circle"></i> <span class="invalid_message"></span></div>             
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td><span class="title_list">{l s='Customer:' mod='advancedvatmanager'}</span></td>
                        <td><span class="customer"></span></td>
                    </tr>
                    <tr>
                        <td><span class="title_list">{l s='VAT number:' mod='advancedvatmanager'}</span></td>
                        <td><span class="vat_number"></span></td>
                    </tr>
                    <tr>
                        <td><span class="title_list">{l s='Country ISO:' mod='advancedvatmanager'}</span></td>
                        <td><span class="country_iso"></span></td>
                    </tr>
                    <tr>
                        <td><span class="title_list">{l s='Company:' mod='advancedvatmanager'}</span></td>
                        <td><span class="company"></span></td>
                    </tr>
                    <tr>
                        <td><span class="title_list">{l s='Address:' mod='advancedvatmanager'}</span></td>
                        <td><span class="address"></span></td>
                    </tr>
                    <tr>
                        <td><span class="title_list">{l s='Request date:' mod='advancedvatmanager'}</span></td>
                        <td><span class="request_date"></span></td>
                    </tr>
                    <tr>
                        <td><span class="title_list">{l s='System:' mod='advancedvatmanager'}</span></td>
                        <td><span class="check_system"></span></td>
                    </tr>
                </tbody>
            </table>
            <button class="avm_checkCustomerVAT_closeBTN btn btn-danger" data-sm-close="true">{l s='Close' mod='advancedvatmanager'}</button>
        </div>
    </div>
</div>
<!-- Modal for checking VAT END -->