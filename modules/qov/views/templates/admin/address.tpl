{**
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
* @copyright 2010-9999 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* CONTACT WITH DEVELOPER http://mypresta.eu
* support@mypresta.eu
*}

<div class="col-md-4">
    {if Configuration::get('QOV_CUSTOMER')==1}
        <div class="col-md-12">
            <h4>{l s='Customer' mod='qov'}</h4>
            {if ($customer->isGuest())}
                {l s='This order has been placed by a guest.' mod='qov'}
                {if (!Customer::customerExists($customer->email))}
                    <dl class="well list-detail">
                        <dt>{l s='Email' mod='qov'}</dt>
                        <dd><a href="mailto:{$customer->email}"><i class="icon-envelope-o"></i> {$customer->email}</a></dd>
                        <dt>{l s='Customer name' mod='qov'}</dt>
                        <dd>{$customer->firstname} {$customer->lastname}</dd>
                    </dl>
                    <form method="post" action="index.php?tab=AdminCustomers&amp;id_customer={$customer->id}&amp;id_order={$order->id|intval}&amp;token={Tools::getAdminTokenLite('AdminCustomers')}">
                        <input type="hidden" name="id_lang" value="{$order->id_lang}"/>
                        <input class="btn btn-default" type="submit" name="submitGuestToCustomer"
                               value="{l s='Transform a guest into a customer' mod='qov'}"/>
                        <p class="help-block">{l s='This feature will generate a random password and send an email to the customer.' mod='qov'}</p>
                    </form>
                {else}
                    <div class="alert alert-warning">
                        {l s='A registered customer account has already claimed this email address' mod='qov'}
                    </div>
                {/if}
            {else}
                <dl class="well list-detail">
                    <dt>{l s='Email' mod='qov'}</dt>
                    <dd><a href="mailto:{$customer->email}"><i class="icon-envelope-o"></i> {$customer->email}</a></dd>
                    <dt>{l s='Account registered' mod='qov'}</dt>
                    <dd class="text-muted"><i class="icon-calendar-o"></i> {dateFormat date=$customer->date_add full=true}</dd>
                    <dt>{l s='Valid orders placed' mod='qov'}</dt>
                    <dd><span class="badge">{$customerStats['nb_orders']|intval}</span></dd>
                    <dt>{l s='Total spent since registration' mod='qov'}</dt>
                    <dd>
                        <span class="badge badge-success">{Tools::displayPrice(Tools::ps_round(Tools::convertPrice($customerStats['total_orders']), 2))}</span>
                    </dd>
                    {if Configuration::get('PS_B2B_ENABLE')}
                        <dt>{l s='Siret' mod='qov'}</dt>
                        <dd>{$customer->siret}</dd>
                        <dt>{l s='APE' mod='qov'}</dt>
                        <dd>{$customer->ape}</dd>
                    {/if}
                </dl>
            {/if}
        </div>
        {if Configuration::get('QOV_CUSTOMNOTE')==1}
            <div class="col-md-12">
                <div class="panel panel-sm">
                    <div class="panel-heading"><i class="icon-eye-slash"></i>{l s='Private note' mod='qov'}</div>
                    <form id="customer_note" class="form-horizontal" action="{if $ps_version_176 == true}{$link->getAdminLink('AdminCustomers', true, [], ['updateCustomerNote' => 1, 'id_customer' => $customer->id])}{else}ajax.php{/if}" method="post" onsubmit="saveCustomerNote({$customer->id});return false;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <textarea name="{if $ps_version_176 == true}private_note[note]{else}note{/if}" id="noteContent" class="textarea-autosize" onkeyup="$(this).val().length > 0 ? $('#submitCustomerNote').removeAttr('disabled') : $('#submitCustomerNote').attr('disabled', 'disabled')"  style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;">{$customer->note}</textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" id="submitCustomerNote" class="btn btn-default pull-right" disabled="disabled">
                                    <i class="icon-save"></i> {l s='Save' mod='qov'}
                                </button>
                            </div>
                        </div>
                        <span id="note_feedback"></span>
                    </form>
                </div>
            </div>
        {/if}
    {else}
        {if Configuration::get('QOV_CUSTOMNOTE')==1}
            <div class="col-md-12">
                <h4>{l s='Customer' mod='qov'}</h4>
                <div class="panel panel-sm">
                    <div class="panel-heading"><i class="icon-eye-slash"></i>{l s='Private note' mod='qov'}</div>
                    <form id="customer_note" class="form-horizontal" action="{if $ps_version_176 == true}{$link->getAdminLink('AdminCustomers', true, [], ['updateCustomerNote' => 1, 'id_customer' => $customer->id])}{else}ajax.php{/if}" method="post" onsubmit="saveCustomerNote({$customer->id});return false;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <textarea name="{if $ps_version_176 == true}private_note[note]{else}note{/if}" id="noteContent" class="textarea-autosize" onkeyup="$(this).val().length > 0 ? $('#submitCustomerNote').removeAttr('disabled') : $('#submitCustomerNote').attr('disabled', 'disabled')"  style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;">{$customer->note}</textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" id="submitCustomerNote" class="btn btn-default pull-right" disabled="disabled">
                                    <i class="icon-save"></i> {l s='Save' mod='qov'}
                                </button>
                            </div>
                        </div>
                        <span id="note_feedback"></span>
                    </form>
                </div>
            </div>
        {/if}
    {/if}
    {if Configuration::get('QOV_SHIPPING')==1}
        {if Configuration::get('QOV_SHIPPING_ADDRESS')==1}
            <div class="{if Configuration::get('QOV_SHIPPING_INVOICE')==1}col-md-6{else}col-md-12{/if}">
                <h4>{l s='Shipping address' mod='qov'}</h4>
                <div class="well">
                    <div class="row">
                        <div class="col-sm-12">
                            {AddressFormat::generateAddress($addresses.delivery)|nl2br}
                            {if Configuration::get('QOV_SHIPPING_ID')==1 || Configuration::get('QOV_SHIPPING_REF')==1}
                                <br />
                                {if Configuration::get('QOV_SHIPPING_ID')==1}{l s='ID:' mod='qov'} {$order->id}{/if}{if Configuration::get('QOV_SHIPPING_ID')==1 && Configuration::get('QOV_SHIPPING_REF')==1}, {/if}{if Configuration::get('QOV_SHIPPING_REF')==1}{l s='REF:' mod='qov'} {$order->reference}{/if}
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        {if Configuration::get('QOV_SHIPPING_INVOICE')==1}
            <div class="{if Configuration::get('QOV_SHIPPING_ADDRESS')==1}col-md-6{else}col-md-12{/if}">
                <h4>{l s='Invoice address' mod='qov'}</h4>
                <div class="well">
                    <div class="row">
                        <div class="col-sm-12">
                            {AddressFormat::generateAddress($addresses.invoice)|nl2br}
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        <div class="col-md-12">
            <h4>{l s='Shipping' mod='qov'}</h4>
            <div class="well">
                <div class="row">
                    <div class="col-sm-12">
                        {$carrier->name nofilter}
                        {if $carrier->is_free==1}(<ins>{l s='Free Shipping' mod='qov'}</ins>){/if}<br/>
                        {if Configuration::get('QOV_PARCEL_WEIGHT')==1}{l s='Parcel weight' mod='qov'}: {$total_weight|string_format:"%.3f"} {Configuration::get('PS_WEIGHT_UNIT')}{/if}<br/>
                        <span class="badge">{Tools::convertPrice($order->total_shipping)|floatval} {l s='Tax incl.' mod='qov'}</span>
                        ({Tools::convertPrice($order->total_shipping_tax_excl)|floatval} {l s='Tax excl.' mod='qov'})
                        <div class="row" style="margin-top:10px;">
                            <div class='col-sm-12'>
                                {if Configuration::get('QOV_SHIPPING_EDIT')==1}<span style="float:left; margin-bottom:5px;">{l s='tracking number' mod='qov'}</span>{/if}
                                <span style="float:right; margin-bottom:5px;">
                                    {if $tracking}
                                        <a href="{$tracking}" id="TrackingLink{$order->id}" target="_blank">
                                            {l s='track package' mod='qov'}
                                            <span id="TrackingNumber{$order->id}">{$order->shipping_number}</span>
                                        </a>
                                    {/if}
                                </span>
                                <div class="row">
                                    {if Configuration::get('QOV_SHIPPING_EDIT')==1}
                                        <div class="col-md-10"><input title="{$order->id}" value="{$order->shipping_number}" class="trackingInputField" type='text' name="TrackingNumberValue" id="TrackingNumberValue{$order->id}"/></div>
                                        <div class="col-md-2">
                                            <div class="btn btn-default" name="submitChangeCurrency" onclick="ChangeTrackingNumber('{$order->id}',$('#TrackingNumberValue{$order->id}').val());">
                                                <i class="icon-save" id="TrackingSaveIcon{$order->id}"></i> {l s='save' mod='qov'}</div>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>