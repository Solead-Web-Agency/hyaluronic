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
<div class="col-xs-12 gls-container" data-carrier="{$id_carrier}">

{if $force_gsm}
    <div class="form-group has- gls-mobile">
        <label
            for="gls-customer-mobile-{$id_carrier}"
            class="form-control-label"
        >
            {$customer_mobile_title}
        </label>
        <input
            type="tel"
            id="gls-customer-mobile-{$id_carrier}"
            name="gls_customer_mobile_{$id_carrier}"
            class="form-control form-control- gls-customer-mobile"
            value="{$current_customer_mobile}"
        />
    </div>
{/if}

{if $is_relay_carrier}
    <div class="row">
        <div class="col-xs-12">
            <div class="gls-heading bg-primary text-white">
                {l s='The following GLS Relais are available around your address' mod='nkmgls'}
                <div class="gls-sub-heading text-white">
                    {l s='You have the choice between the following alternatives:' mod='nkmgls'}
                    <ul>
                        <li>{l s='a Point Relais®, among the approved merchants of the Mondial Relay network' mod='nkmgls'}</li>
                        <li>{l s='a Voisin-Relais, from a network of trusted individuals, selected by our partner Pickme, identified in the list by their first names' mod='nkmgls'}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="gls-search bg-faded">
                <a href="#gls-search-form" class="gls-search-form-toggler d-block" data-toggle="collapse">{l s='Find GLS Relais around another address' mod='nkmgls'}</a>
                <div id="gls-search-form" class="collapse">
                    <div class="form-group input-group mb-0">
                        <input type="search" name="gls_search_postcode" id="gls-search-postcode" class="gls-search-input form-control" placeholder="{l s='Postcode (required)' mod='nkmgls'}" />
                        <input type="search" name="gls_search_city" id="gls-search-city" class="gls-search-input form-control" placeholder="{l s='City (optional)' mod='nkmgls'}" />
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-primary btn-block gls-search-relay">
                            <i class="material-icons search fa fa-search fa-fw">search</i><span class="btn-text">{l s='Search' mod='nkmgls'}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div
        class="row"
        id="gls-relay-error"
        style="display: none;"
    >
        <div class="alert alert-danger col-xs-12" role="alert">
            <i class="material-icons">error_outline</i>
            <span class="alert-text"></span>
        </div>
    </div>
    <div id="gls-relay-container">
        <div class="row">
            {if $current_customer_address !== null}
                <div
                    id="gls-relay-address-data"
                    style="display: none;"
                    data-address-address1="{$current_customer_address.address1}"
                    data-address-city="{$current_customer_address.city}"
                    data-address-postcode="{$current_customer_address.postcode}"
                ></div>
            {/if}
            <div
                class="col-xs-12 col-xl-5 gls-relay-list"
                style="display: none;"
            ></div>
            <div
                class="col-xs-12 col-xl-7 gls-relay-map"
                style="display: none;"
            >
                <div id="gls-map"></div>
            </div>
            <div id="gls-relay-loader" class="gls-france-loader-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" x="0" y="0" viewBox="0 0 40 40">
                    <circle cx="20" cy="20" r="18"/>
                </svg>
            </div>
        </div>
    </div>
{/if}

</div>
