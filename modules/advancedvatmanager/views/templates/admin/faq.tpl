{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
<div class="faq_item">
    <h4 class="faq_title">{l s='When the customer has already saved an address with a valid VAT number, does he have to validate the address every time he places an order?' mod='advancedvatmanager'}</h4>
    <p>{l s='When a customer creates an address with a valid VAT number, the system validates it and saves the validation result in the database, so whenever he uses the same address to place orders, he will not have to validate it again.' mod='advancedvatmanager'}<br />{l s='If the VAT number validation Cron task is executed and the VAT number of that customer is no longer valid, the system will detect this and it will be then that the customer will no longer be VAT exempt until they insert a valid VAT number again in the direction.' mod='advancedvatmanager'}</p>
</div>
<div class="faq_item">
    <h4 class="faq_title">{l s='If the store already has customer addresses with VAT numbers saved before the module installation, How can the addresses be checked to find out if customers have entered a valid VAT number or not?' mod='advancedvatmanager'}</h4>
    <p>{l s='The module has a CRON task function to check the VAT numbers of all customer addresses that are already stored in the database. Once the module is installed in the store, the system will automatically check the VAT numbers of customers who create an address or modify the one they already have created.' mod='advancedvatmanager'}</p>
</div> 
<div class="faq_item">
    <h4 class="faq_title">{l s='Whenever I run the CRON task to check customer VAT numbers, does the process check all the addresses in the database again?' mod='advancedvatmanager'}</h4>
    <p>{l s='The system will check all customer addresses stored in the database unless the "Continue scanning process from last scanned address" option is enabled in the CRON settings section.' mod='advancedvatmanager'}</p>
</div>
<div class="faq_item">
    <h4 class="faq_title">{l s='I am using a Marketplaces module that imports customer data and orders generated on the platform. However, this platform controls the prices and tax allocation of these orders. How can I configure the module so that it does not try to validate the VAT numbers of the imported customer data by the marketplace module and allows the marketplace to manage the taxes and prices of the orders that are imported?' mod='advancedvatmanager'}</h4>
    <p>{l s='To disable the validation of customer VAT numbers that are imported from these modules for synchronizing data with marketplaces, you must disable the "Back-office validation" option in the "Activation" section of the configuration page of this module. Note that depending on the type of execution of the data import operation of these marketplace modules, it may not be enough to disable this option.' mod='advancedvatmanager'}</p>
</div>  