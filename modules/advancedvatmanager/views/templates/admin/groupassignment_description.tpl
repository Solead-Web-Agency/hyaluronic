{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}

<p>{l s='The customer who have a valid VAT number in his address will be assigned to the group selected depends on address country. If the client has several addresses from different countries, the system will add him to as many groups as it has configured by countries.' mod='advancedvatmanager'}</p>
<br />
<p><strong>{l s='Option [Use as default group]:' mod='advancedvatmanager'}</strong> {l s='If this option is enabled, when the customer is assigned to a group depending on the country of the address created, this group will become the default group for this customer. If the customer adds more addresses from different countries, the default group will change to the group it was added to last.' mod='advancedvatmanager'}</p>
<br />
<p><strong>{l s='Option [Change default group with address]:' mod='advancedvatmanager'}</strong> {l s='If this option is enabled, when the customer selects a certain address to place their order in the checkout process, the customer default group will be changed to the group that belongs to the country of the selected address.' mod='advancedvatmanager'}</p>
<br />
<p><strong>{l s='Option [Remove customer from another groups]:' mod='advancedvatmanager'}</strong> {l s='If this option is activated, the customer will be assigned to the group selected by the country of the address created and will be removed from the rest of the groups to which they previously belonged and it will be assigned to this group as default group. This option is useful when you want the customer to only belong to one group and not to as many as addresses from different countries have been created in your account.' mod='advancedvatmanager'}</p>

<p>{l s='When a customer deletes an address in his account, if he was assigned to the group configured for that country and has no more addresses with the same country, he will be removed from that group. If it had more addresses with the same country, it would still be assigned to that group. If the customer is only assigned to a group belonging to the country of his address and he removes the address, the system will remove him from that group and to avoid being left without being assigned to any group and thus avoid a conflict in the database data, the system will reassign it to the group that is configured as default in Prestashop for registered customers.' mod='advancedvatmanager'}</p>