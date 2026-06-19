{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}

<p>{l s='If the Brexit option is activated, the system will calculate cart total products amount and if it is equal or below to %s (equivalent to 135 GBP) to manage the taxes depends on the each situation. This option enables the validation of UK VAT numbers using the GOV.UK system API as long as the system API validation is selected.' sprintf=[$getGBPcurrencyAmount] mod='advancedvatmanager'}</p>
<p>{l s='This system is updated to the new Brexit legislation. Tax control works as follows:' mod='advancedvatmanager'}</p>
<br />
<p style="font-weight: 700;text-decoration:underline;">{l s='Cart amount equal to or less than 135GBP (not including shipping)' mod='advancedvatmanager'}</p>
<ul>
    <li><strong>{l s='Companies or individuals without VAT number registered in the UK:' mod='advancedvatmanager'}</strong> {l s='This store will charge the taxes that are configured in the UK country.' mod='advancedvatmanager'}</li>
    <li><strong>{l s='Businesses with a valid VAT number registered in the UK: ' mod='advancedvatmanager'}</strong> {l s='If you have the "VAT exemption in companies" option activated, then the store will not charge taxes and the invoice will include the message "reverse charge: customer to account for VAT to HMRC ". If the option is disabled, then it will also charge taxes.' mod='advancedvatmanager'}</li>
</ul>
<br />
<p style="font-weight: 700;text-decoration:underline;">{l s='Cart amount more than 135GBP (not including shipping)' mod='advancedvatmanager'}</p>
<ul>
    <li><strong>{l s='Companies or individuals with or without VAT number registered in the UK:' mod='advancedvatmanager'}</strong> {l s='This store will not charge the taxes.' mod='advancedvatmanager'}</li>
</ul>
<br />
<strong>{l s='IMPORTANT: During installation of this module, the system has created 232 United Kingdom states (If these were not created previously) and assign North Ireland to UK according to the Brexit laws. This improves the creation of customer addresses by assigning the correct province or state' mod='advancedvatmanager'}</strong>