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
<div class="panel nkmgls_detail">

    <div class="panel-heading">
        <i class="icon-truck"></i> {l s='GLS carrier' mod='nkmgls'}
    </div>

    <p class="gls-logo"><img src="{$gls_logo|escape:'quotes':'UTF-8'}" alt="GLS" class="img-responsive center-block" /></p>

    {if $is_gls}
        <h4>{l s='GLS Tracking information' mod='nkmgls'}</h4>
        <div class="gls-tracking-information">
            {if $current_state != ''}
                <p><b>{l s='State:' mod='nkmgls'}</b> <span class="gls-tracking-current-state">{$current_state|escape:'htmlall':'UTF-8'}</span> ({l s='Updated on:' mod='nkmgls'} <span class="gls-tracking-current-state-date">{dateFormat date=$trackingState.date_upd full=1}</span>)</p>
            {else}
                <p class="gls-tracking-information-empty-template" style="display: none"><b>{l s='State:' mod='nkmgls'}</b> <span class="gls-tracking-current-state"></span> ({l s='Updated on:' mod='nkmgls'} <span class="gls-tracking-current-state-date"></span>)</p>
                <p class="gls-tracking-information-unavailable">{l s='Information not available' mod='nkmgls'}</p>
            {/if}
            <form id="gls-admin-order-check-tracking-state" action="{$link_tracking|escape:'quotes':'UTF-8'}" method="post">
                <input type="hidden" name="id_order" value="{$id_order|intval}" />
                <button type="submit" id="updateTrackingState" name="updateTrackingState" class="btn btn-primary" onclick="$(this).val('1')">{l s='Check state' mod='nkmgls'}</button>
            </form>
        </div>

        <hr/>
    {/if}

    <div class="alert alert-info" role="alert">
        <p class="alert-text">{l s='You can print a GLS shipping label even if a GLS carrier is not associated to this order. Additional information will be required before the shipping label is generated.' mod='nkmgls'}</p>
    </div>

    <form id="gls-admin-order-print-label" action="{$link|escape:'quotes':'UTF-8'}" method="post" class="defaultForm form-inline">
        <input type="hidden" name="GLS_LABEL_SINGLE_TYPE" value="shipment" />
        <input type="hidden" name="GLS_LABEL_ORDER_ID" value="{$id_order|intval}" />
        <input type="hidden" name="GLS_LABEL_ORDER_REF" value="" />
        <input type="hidden" name="gls_print_label_from_order" value="1" />
        <div class="form-group">
            <label for="GLS_LABEL_SINGLE_NEW_ORDER_STATE">{l s='Change order status to' mod='nkmgls'}</label>
            <select name="GLS_LABEL_SINGLE_NEW_ORDER_STATE" class="form-control" id="GLS_LABEL_SINGLE_NEW_ORDER_STATE">
                <option value="0">{l s='(No change)' mod='nkmgls'}</option>
                {foreach from=$order_status item=item key=key name=status}
                    <option value="{$item['id_option']|escape:'quotes':'UTF-8'}" {if $order_status_selected == $item['id_option']}selected="selected"{/if}>{$item['name']|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <button type="submit" id="generateLabelStep3" name="generateLabelStep3" class="btn btn-primary" onclick="$(this).val('1')">{l s='Print label' mod='nkmgls'}</button>
    </form>

</div>
