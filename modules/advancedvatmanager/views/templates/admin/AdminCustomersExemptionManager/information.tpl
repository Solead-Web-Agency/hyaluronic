{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
<div id="top-index" class="panel">
    <h3><i class="far fa-info-circle"></i>&nbsp;{l s='Information' mod='advancedvatmanager'}</h3>
    <div class="alert alert-info">
        <p>{l s='In this section you can add customers to whom the tax exemption will be applied or tax will be forced to apply whether or not they have an address with a valid VAT number. This list is compatible with multi-store mode and exemptions can be granted to customers in all stores or or in any store.' mod='advancedvatmanager'}</p>
        <p>{l s='This option takes precedence over the other tax exemption options configured on the module configuration page.' mod='advancedvatmanager'}</p>       
    </div>
    <div class="alert alert-warning">
        <p>{l s='When a customer is added to the list, they will instantly be granted either a VAT exemption or the obligation to pay VAT, depending on the option chosen in "VAT option".' mod='advancedvatmanager'}</p>
        <p>{l s='This means that, depending on the option chosen, the customer will always pay VAT or will be exempt from paying VAT regardless of the other conditions that they meet. If you want the client to have the VAT exemption according to the standard conditions of the module, you will have to remove it from this list.' mod='advancedvatmanager'}</p>       
    </div>
</div>
