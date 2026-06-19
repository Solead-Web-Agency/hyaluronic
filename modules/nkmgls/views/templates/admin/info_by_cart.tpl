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
{if $address_delivery && $address_delivery_formatted}
    <div class="row addresses gls-order-detail">
        <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if}">
            {if $ps_version == '1.7'}
                <div class="info-block">
            {/if}
            {if empty($parcel_shop_id)}
                <div class="alert alert-warning" role="alert">
                    <p class="text-warning">{l s='There is no GLS Relais associated to the carrier, please select one.' mod='nkmgls'}</p>
                </div>
            {/if}
            <div id="delivery-address-relay" class="well {if $ps_version === '1.7'}mb-2{/if}">
                <h4 style="margin-top: 0;">{l s='GLS Relais delivery address' mod='nkmgls'}</h4>
                <p>{l s='GLS Relais ID:' mod='nkmgls'} {if !empty($parcel_shop_id)}{$parcel_shop_id|escape:'htmlall':'UTF-8'}{else}{l s='NC' mod='nkmgls'}{/if}</p>
                <div>{if !empty($parcel_shop_id)}{$address_delivery_formatted nofilter}{/if}</div>
            </div>
            <button type="button" class="btn btn-primary glsChangeRelayModal" data-toggle="modal" data-target="#glsChangeRelayModal">
                {if !empty($parcel_shop_id)}
                    {l s='Change GLS Relais delivery address' mod='nkmgls'}
                {else}
                    {l s='Add GLS Relais delivery address' mod='nkmgls'}
                {/if}
            </button>
            {if $ps_version == '1.7'}
                </div>
            {/if}
        </div>
    </div>
{/if}