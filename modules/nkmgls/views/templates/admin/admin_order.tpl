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
{if $address_delivery}

    <div class="modal fade" id="glsChangeRelayModal" tabindex="-1" role="dialog" aria-labelledby="glsChangeRelayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if} gls-container">
                            {if $is_relay_carrier}
                                <div class="row">
                                    <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if}">
                                        <div class="gls-heading bg-primary text-white">
                                            {l s='The following GLS Relais are available around your address' mod='nkmgls'}
                                        </div>
                                    </div>
                                    <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if}">
                                        <div class="gls-search bg-faded">
                                            <a href="#gls-search-form" class="gls-search-form-toggler d-block" data-toggle="collapse">{l s='Find GLS Relais around another address' mod='nkmgls'}</a>
                                            <div id="gls-search-form" class="collapse">
                                                <div class="form-group input-group mb-0">
                                                    <input type="search" name="gls_search_postcode" id="gls-search-postcode" class="gls-search-input form-control" placeholder="{l s='Postcode (required)' mod='nkmgls'}" />
                                                    <input type="search" name="gls_search_city" id="gls-search-city" class="gls-search-input form-control" placeholder="{l s='City (optional)' mod='nkmgls'}" />
                                                    <input type="hidden" name="gls_search_country" value="{$address_country_code|escape:'quotes':'UTF-8'}" />
                                                </div>
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-primary btn-block gls-search-relay">
                                                        <i class="material-icons search fa fa-search fa-fw">search</i><span class="btn-text">{l s='Search' mod='nkmgls'}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {if $current_customer_address !== null}
                                        <div
                                            id="gls-relay-address-data"
                                            style="display: none;"
                                            data-address-address1="{$current_customer_address.address1}"
                                            data-address-city="{$current_customer_address.city}"
                                            data-address-postcode="{$current_customer_address.postcode}"
                                        ></div>
                                    {/if}
                                    <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if} col-lg-5 gls-relay-list" style="display: none;">
                                    </div>
                                    <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if} col-lg-7 gls-relay-map" style="display: none;">
                                        <div id="gls-map"></div>
                                    </div>
                                    <div id="gls-relay-loader" class="gls-france-loader-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" x="0" y="0" viewBox="0 0 40 40">
                                            <circle cx="20" cy="20" r="18"/>
                                        </svg>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {if empty($parcel_shop_id)}
                        <input type="hidden" name="modal_gls_customer_mobile" id="modal_gls_customer_mobile" value="{$address_delivery->phone_mobile|escape:'quotes':'UTF-8'}">
                    {/if}
                    <input type="hidden" name="modal_gls_id_order" id="modal_gls_id_order" value="{$id_order|escape:'quotes':'UTF-8'}">
                    <button type="button" class="btn btn-tertiary-outline btn-lg" data-dismiss="modal">{l s='Cancel' mod='nkmgls'}</button>
                    <button type="button" class="btn btn-primary btn-lg" id="saveGlsChangeRelay">{l s='Save changes' mod='nkmgls'}</button>
                    <button class="btn btn-primary-reverse onclick btn-lg unbind GlsChangeRelayModalLoader" style="display: none;"></button>
                    <div class="alert alert-danger" role="alert" id="gls-error-modal" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

{/if}