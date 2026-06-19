{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
<div class="troubleshooting_item">
    <h4 class="troubleshooting_title">{l s='The customer has an address with a valid VAT number but the shop shows prices with VAT' mod='advancedvatmanager'}</h4>
    <ul class="troubleshooting_list">
        <li>{l s='Check that the customer is not using another address that has recently been created and has not been validated for the purchase process.' mod='advancedvatmanager'}</li>
        <li>{l s='Check that the "VAT exemption" option in the VAT exemption settings section is activated.' mod='advancedvatmanager'}</li>
        <li>{l s='Check that the VAT number has been correctly validated in the "Customer VAT number management" section.' mod='advancedvatmanager'}</li>
        <li>{l s='Check that the customer is not included in the list of the customer VAT exemption management section with the "force VAT collection" option selected.' mod='advancedvatmanager'}</li>
        <li>{l s='Check if the client address corresponds to the United Kingdom and complies with the VAT collection conditions.' mod='advancedvatmanager'}</li>
        <li>{l s='Check that the address that the customer has active to make the purchase is the one that corresponds to the "Type of address" option selected in the VAT exemption configuration section.' mod='advancedvatmanager'}</li>
        <li>{l s='Check that you do not have a module installed that controls taxes and is conflicting with the Advanced VAT Manager module.' mod='advancedvatmanager'}</li> 
    </ul>    
</div>
<div class="troubleshooting_item">
    <h4 class="troubleshooting_title">{l s='When a customer saves an address with a valid VAT number and is exempt from VAT, the prices of the products are displayed without VAT but the prices of the product list on the main page continue to be displayed with VAT' mod='advancedvatmanager'}</h4>
    <ul class="troubleshooting_list">
        <li>{l s='This is a problem caused by the site cache. It happens more frequently if you have the Prestashop cache enabled and the [Never collect template files] option in smarty section. To solve it, the Prestashop cache must be cleaned. It is recommended to perform a periodic cleaning of the Prestashop cache using CRON tasks.' mod='advancedvatmanager'}</li>
    </ul>    
</div>
<div class="troubleshooting_item">
    <h4 class="troubleshooting_title">{l s='The module is attempting to validate the VAT numbers of customers residing in the country where the store is located.' mod='advancedvatmanager'}</h4>
    <ul class="troubleshooting_list">
        <li>{l s='Check that in the "Countries for validation" option in the "activation" section, the local country of the store is not selected.' mod='advancedvatmanager'}</li>
    </ul>    
</div>