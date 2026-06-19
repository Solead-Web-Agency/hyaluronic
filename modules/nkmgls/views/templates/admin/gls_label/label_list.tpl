{*
*  Module made by Nukium
*
*  @author    Nukium
*  @copyright 2023 Nukium SAS
*  @license   All rights reserved
*
* ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
* ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
* ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
* ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
* ██   ████  ██████  ██   ██ ██  ██████  ██      ██
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*}
<div class="alert alert-info" role="alert">
    <p class="alert-text">{$list|count} {l s='selected orders' mod='nkmgls'}</p>
</div>
<div class="panel col-lg-12">
    <h3><i class="icon-tags icon-fw"></i> {if isset($gls_label_single_type) && $gls_label_single_type == 'return'}{l s='Printing return label' mod='nkmgls'}{else}{l s='Printing delivery labels' mod='nkmgls'}{/if}</h3>
    <div class="table-responsive-row clearfix">
        <table id="table-labels" class="table">
            <thead>
                <tr class="nodrag nodrop">
                    <th class="fixed-width-xs text-center">
                        <a href="#" class="toggler" data-toggle="tooltip" title="{l s='Expand / collapse all' mod='nkmgls'}" aria-expanded="false">
                            <i class="icon icon-angle-down icon-lg"></i>
                        </a>
                    </th>
                    <th class="fixed-width-xs text-center">
                        <span class="title_box">{l s='ID' mod='nkmgls'}</span>
                    </th>
                    <th>
                        <span class="title_box">{l s='Reference' mod='nkmgls'}</span>
                    </th>
                    <th class="">
                        <span class="title_box">{l s='Customer' mod='nkmgls'}</span>
                    </th>
                    <th class="text-right">
                        <span class="title_box">{l s='Total' mod='nkmgls'}</span>
                    </th>
                    <th>
                        <span class="title_box">{l s='Payment' mod='nkmgls'}</span>
                    </th>
                    <th>
                        <span class="title_box">{l s='Status' mod='nkmgls'}</span>
                    </th>
                    <th>
                        <span class="title_box">{l s='Date' mod='nkmgls'}</span>
                    </th>
                    <th>
                        <span class="title_box">{l s='Carrier' mod='nkmgls'}</span>
                    </th>
                    <th class="fixed-width-xs">
                        <span class="title_box">{l s='Printable' mod='nkmgls'}</span>
                    </th>
                    <th class="action fixed-width-xs">
                        <span class="title_box"></span>
                    </th>
                </tr>
            </thead>
            {capture name='tr_count'}{counter name='tr_count'}{/capture}
            <tbody>
            {if count($list)}
                {foreach $list AS $index => $tr}
                    <tr>
                        <td class="fixed-width-xs text-center">
                            <a href="#" class="toggler" aria-expanded="false">
                                <i class="icon icon-angle-down icon-lg"></i>
                            </a>
                        </td>
                        <td class="fixed-width-xs text-center">
                            {$tr.id_order|escape:'htmlall':'UTF-8'}
                        </td>
                        <td>
                            {$tr.reference|escape:'htmlall':'UTF-8'}
                        </td>
                        <td>
                            {$tr.customer|escape:'htmlall':'UTF-8'}
                        </td>
                        <td class="text-right">
                            {displayPrice price=$tr.total_paid_tax_incl}
                        </td>
                        <td>
                            {$tr.payment|escape:'htmlall':'UTF-8'}
                        </td>
                        <td>
                            {$tr.osname|escape:'htmlall':'UTF-8'}
                        </td>
                        <td>
                            {dateFormat date=$tr.date_add full=1}
                        </td>
                        <td>
                            {$tr.caname|escape:'htmlall':'UTF-8'}
                        </td>
                        <td>
                            <span class="progress-text">{l s='Printable' mod='nkmgls'}</span>
                            <div class="progress" id="printing-progress-{$tr.id_order|escape:'quotes':'UTF-8'}">
                                <div class="progress-bar bg-primary" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </td>
                        <td class="action text-center">
                            <button type="button" id="btn-print-{$tr.id_order|escape:'quotes':'UTF-8'}" class="btn btn-primary btn-print btn-disabled" data-toggle="tooltip" title="{l s='Fill in the order informations before you can print the associated label' mod='nkmgls'}"><i class="icon icon-print icon-lg"></i><span class="text">{l s='Print' mod='nkmgls'}</span></button>
                        </td>
                    </tr>
                    <tr class="extra-content">
                        <td colspan="11">
                            <form action="#" id="extra-content-form-{$tr.id_order|escape:'quotes':'UTF-8'}" class="extra-content-form" style="display: none;">
                                <input type="hidden" name="order" value="{$tr.id_order|escape:'quotes':'UTF-8'}" />
                                <input type="hidden" id="local_print_{$tr.id_order|escape:'quotes':'UTF-8'}" name="local_print" value="0" />
                                <div class="form-wrapper clearfix">
                                    <div class="row big-gutters">
                                        <div class="col-md-4">
                                            <div class="card packages">
                                                <div class="card-header">
                                                    {l s='Packages' mod='nkmgls'} <span class="packages-count badge badge-primary">1</span>
                                                  </div>
                                                <div class="card-body">
                                                    <div class="row package">
                                                        <div class="col-12 package-title d-flex justify-content-between align-items-center">
                                                            <strong>{l s='Package #' mod='nkmgls'}<span class="package-index">1</span></strong>
                                                            <button type="button" class="btn btn-danger-outline remove-package d-none"><i class="icon icon-trash"></i></button>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label class="control-label required">
                                                                    {l s='Weight' mod='nkmgls'}
                                                                </label>
                                                                <div class="input-group" lang="en-US">
                                                                    <input name="weight[]" class="form-control weight-control" required="required" type="number" step="0.001" min="0.1" max="30" value="{if $tr.order_weight > 0}{$tr.order_weight|string_format:"%.3f"|escape:'quotes':'UTF-8'}{/if}" />
                                                                    <span class="input-group-addon">{l s='kg' mod='nkmgls'}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </diV>
                                                <div class="card-footer text-right">
                                                    <button type="button" class="btn btn-primary-outline add-package"><i class="icon icon-plus"></i> {l s='Add a package' mod='nkmgls'}</button>
                                                </div>
                                            </div>
                                        </div>
                                        {if !isset($gls_label_single_type) || $gls_label_single_type != 'return'}
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label required">
                                                        {l s='GLS service' mod='nkmgls'}
                                                    </label>
                                                    <div>
                                                        <select name="gls_service" class="gls-service form-control" required="required">

                                                            <option value=""></option>

                                                            {foreach $gls_carriers_ids as $key => $val}
                                                                {assign var="lastItem" value=end($val)}

                                                                {if $tr.id_carrier|in_array:$val}
                                                                    <option value="{$tr.id_carrier|escape:'quotes':'UTF-8'}" selected="selected"{if $tr.id_carrier|in_array:$gls_mobile_required} data-mobile-required="true"{/if}>{$tr.caname|escape:'htmlall':'UTF-8'} ({l s='ID:' mod='nkmgls'} {$tr.id_carrier|escape:'htmlall':'UTF-8'})</option>
                                                                {elseif $key != 'GLSRELAIS' && $key != 'GLS13H' && $lastItem|array_key_exists:$carriers}
                                                                    <option value="{$lastItem|escape:'quotes':'UTF-8'}"{if $lastItem|in_array:$gls_mobile_required} data-mobile-required="true"{/if}>{$carriers[$lastItem]['name']|escape:'htmlall':'UTF-8'} ({l s='ID:' mod='nkmgls'} {$lastItem|escape:'htmlall':'UTF-8'})</option>
                                                                {/if}

                                                            {/foreach}

                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label required">
                                                        {l s='Shipping date' mod='nkmgls'}
                                                    </label>
                                                    <div class="input-group">
                                                        {* <input name="delivery_date" value="{$smarty.now|date_format:"%Y-%m-%d"|escape:'quotes':'UTF-8'}" class="form-control" required="required" type="date" min="{$smarty.now|date_format:"%Y-%m-%d"|escape:'quotes':'UTF-8'}" /> *}
                                                        <input id="delivery-date-alt-{$tr.id_order|escape:'quotes':'UTF-8'}" name="delivery_date_alt" value="{$smarty.now|date_format:"%d/%m/%Y"|escape:'quotes':'UTF-8'}" class="form-control datepicker" required="required" type="text" />
                                                        <input id="delivery-date-{$tr.id_order|escape:'quotes':'UTF-8'}" name="delivery_date" value="{$smarty.now|date_format:"%Y-%m-%d"|escape:'quotes':'UTF-8'}" type="hidden" />
                                                        <div class="input-group-addon">
                                                            <i class="icon-calendar-o"></i>
                                                        </div>
                                                    </div>
                                                    <script type="text/javascript">
                                                        {literal}
                                                            $(function(){
                                                                $('#delivery-date-alt-{/literal}{$tr.id_order|escape:'htmlall':'UTF-8'}{literal}').datepicker({
                                                                    fielddateFormat: 'dd/mm/yy',
                                                                    altFormat: 'yy-mm-dd',
                                                                    altField: '#delivery-date-{/literal}{$tr.id_order|escape:'htmlall':'UTF-8'}{literal}',
                                                                    showButtonPanel: false,
                                                                    minDate: 0,
                                                                    onSelect: function(date) {
                                                                        $(this).datepicker('hide');
                                                                    }
                                                                });
                                                                $(document.body).on('click', '.datepicker:not(:disabled) ~ .input-group-addon', function() {
                                                                    $(this).siblings('.datepicker').datepicker('show');
                                                                });
                                                            });
                                                        {/literal}
                                                    </script>
                                                </div>
                                                <div class="form-group mobile-group" style="display: none;">
                                                    <label class="control-label required">
                                                        {l s='Mobile' mod='nkmgls'}
                                                    </label>
                                                    <div>
                                                        <input class="form-control" type="tel" name="mobile" value="{if !empty($tr.customer_phone_mobile)}{$tr.customer_phone_mobile|escape:'html':'UTF-8'}{else}{$tr.customer_phone|escape:'html':'UTF-8'}{/if}" required="required" />
                                                    </div>
                                                </div>
                                                {if $tr.id_country|in_array:$cee_countries === false}
                                                    <div class="form-group">
                                                        <label class="control-label required">
                                                            {l s='Incoterm' mod='nkmgls'}
                                                        </label>
                                                        <div>
                                                            <select name="incoterm" class="form-control" required="required">
                                                                <option value=""></option>
                                                                <option value="10">{l s='DDP' mod='nkmgls'} - {l s='Delivered goods, all costs paid including export and import duties and taxes.' mod='nkmgls'}</option>
                                                                <option value="20">{l s='DAP' mod='nkmgls'} - {l s='Goods delivered, unpaid clearance, unpaid taxes.' mod='nkmgls'}</option>
                                                                <option value="30">{l s='DDP, VAT unpaid' mod='nkmgls'} - {l s='Goods delivered, export and import duties paid, unpaid taxes.' mod='nkmgls'}</option>
                                                                <option value="40">{l s='DAP, cleared' mod='nkmgls'} - {l s='Goods delivered, no clearance, no taxes.' mod='nkmgls'}</option>
                                                                <option value="50">{l s='DDP' mod='nkmgls'} - {l s='Goods delivered, export and import duties paid, low value exemption free authorization.' mod='nkmgls'}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                {/if}
                                                {if $shop_return_service && in_array(Country::getIsoById($tr.id_country), array('FR', 'COS'))}
                                                    <div class="form-group">
                                                        <label class="control-label">{l s='Include return label' mod='nkmgls'}</label>
                                                        <span class="switch prestashop-switch fixed-width-lg">
                                                            <input id="return-label-{$tr.id_order|escape:'quotes':'UTF-8'}-1" name="return_label" value="1" type="radio" />
                                                            <label for="return-label-{$tr.id_order|escape:'quotes':'UTF-8'}-1">{l s='Yes' mod='nkmgls'}</label>
                                                            <input id="return-label-{$tr.id_order|escape:'quotes':'UTF-8'}-0" name="return_label" value="0" type="radio" checked="checked" />
                                                            <label for="return-label-{$tr.id_order|escape:'quotes':'UTF-8'}-0">{l s='No' mod='nkmgls'}</label>
                                                            <a class="slide-button btn"></a>
                                                        </span>
                                                    </div>
                                                {/if}
                                            </div>
                                        {/if}
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">
                                                    {l s='Supp reference 1' mod='nkmgls'}
                                                </label>
                                                <div>
                                                    <input class="form-control" type="text" name="reference1" value="{if $gls_order_reference_enable}{$tr.reference|escape:'quotes':'UTF-8'}{else}{$tr.id_order|escape:'quotes':'UTF-8'}{/if}" maxlength="20" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">
                                                    {l s='Supp reference 2' mod='nkmgls'}
                                                </label>
                                                <div>
                                                    <input class="form-control" type="text" name="reference2" value="" maxlength="20" />
                                                </div>
                                            </div>
                                            <input type="hidden" name="gls_label_single_type" value="{$gls_label_single_type|escape:'quotes':'UTF-8'}" />
                                        </div>
                                    </div>
                                  </div>
                            </form>
                        </td>
                    </tr>
                {/foreach}
            {else}
                <tr>
                    <td class="list-empty" colspan="11">
                        <div class="list-empty-msg">
                            <i class="icon-warning-sign list-empty-icon"></i>
                            {l s='No records found' mod='nkmgls'}
                        </div>
                    </td>
                </tr>
            {/if}
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <button type="button" id="gls-btn-print-all" class="btn btn-default pull-right" data-toggle="tooltip" title="{l s='This will print all labels with valid order\'s informations.' mod='nkmgls'}" data-counter="0" disabled>
            <i class="process-icon-print icon icon-print icon-lg"></i>{l s='Print all' mod='nkmgls'}
        </button>
        <form action="{$back_step2_url|escape:'quotes':'UTF-8'}" method="post" class="form-horizontal clearfix" id="form-back">
            <button type="submit" {if !isset($gls_label_single_type) || (isset($gls_label_single_type) && $gls_label_single_type != 'return')}id="generateLabelStep3Cancel" name="generateLabelStep3Cancel" {/if}class="btn btn-default pull-left" value="1"><i class="process-icon-back"></i> {l s='Back' mod='nkmgls'}</button>
        </form>
    </div>
</div>
