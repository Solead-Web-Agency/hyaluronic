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
{if $relay_points}
    {assign var="labels" value="ABCDEFGHIJKLMNOPQRSTUVWXYZ"}
    {foreach from=$relay_points key=k item=v}
        <div class="row show relay-enable">
            <div class="col-xs-12">
                <div class="card gls-relay-infos">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xs-2 col-sm-2 col-lg-1 col-xl-2 col-select-relay">
                                    <span class="custom-radio">
                                        <input
                                            type="radio"
                                            name="gls_relay"
                                            id="gls-relay-{$v->Parcelshop->ParcelShopId}"
                                            class="gls-select-relay"
                                            data-glsrelayid="{$v->Parcelshop->ParcelShopId}"
                                            value="{$v->Parcelshop->ParcelShopId}"
                                            {if $current_relay == $v->Parcelshop->ParcelShopId}checked{/if}
                                        />
                                    <span></span>
                                    </span>
                            </div>
                            <label class="col-xs-10 col-sm-7 col-lg-9 col-xl-10" for="gls-relay-{$v->Parcelshop->ParcelShopId}">
                                <div class="card-title">
                                    <span class="gls-relay-name"><strong>{$v->Parcelshop->Address->Name1}</strong></span><span class="separator"> - </span><span class="gls-relay-label">{$labels.$k}</span>
                                </div>
                                <div class="card-text gls-relay-address">
                                    <div class="gls-relay-address">
                                        {$v->Parcelshop->Address->Street1}
                                        <br/>
                                        {$v->Parcelshop->Address->ZipCode|cat:' '|cat:$v->Parcelshop->Address->City}
                                    </div>
                                </div>
                            </label>
                            <div class="col-xs-10 offset-xs-2 col-sm-3 offset-sm-0 col-lg-2 col-xl-10 offset-xl-2">
                                <div class="gls-relay-distance text-muted">
                                    {$v->AirLineDistance|string_format:"%.2f"} km
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
    <script type="text/javascript">
        var glsGmapsMarkers = [];
        {foreach from=$relay_points key=k item=v}
            {capture name=opening_hours assign=opening_hours}
            {strip}
                {if isset($v->Parcelshop->GLSWorkingDay) && $v->Parcelshop->GLSWorkingDay|is_array && $v->Parcelshop->GLSWorkingDay|@count > 0}
                    <table class="gls-relay-info-open-hours">
                        <tbody>
                            {foreach from=$trans_days key=day item=dname}
                                <tr{if $smarty.now|date_format:"%u" - 1 == $day} class="today"{/if}>
                                    {if isset($v->Parcelshop->GLSWorkingDay[$day])}
                                        {assign var=dvalue value=$v->Parcelshop->GLSWorkingDay[$day]}
                                        <th>{l s=$dname mod='nkmgls'}</th>
                                        <td>{$dvalue->OpeningHours->Hours->From|substr:0:2}:{$dvalue->OpeningHours->Hours->From|substr:2:2}{literal} - {/literal}
                                            {if $dvalue->Breaks->Hours->From != ''}
                                                {$dvalue->Breaks->Hours->From|substr:0:2}:{$dvalue->Breaks->Hours->From|substr:2:2}<br/>{$dvalue->Breaks->Hours->To|substr:0:2}:{$dvalue->Breaks->Hours->To|substr:2:2} - {$dvalue->OpeningHours->Hours->To|substr:0:2}:{$dvalue->OpeningHours->Hours->To|substr:2:2}
                                            {else}
                                                {$dvalue->OpeningHours->Hours->To|substr:0:2}:{$dvalue->OpeningHours->Hours->To|substr:2:2}
                                            {/if}
                                        </td>
                                    {else}
                                        <th>{l s=$dname mod='nkmgls'}</th>
                                        <td>{l s='Closed' mod='nkmgls'}</td>
                                    {/if}
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                {/if}
            {/strip}
            {/capture}
            glsGmapsMarkers.push({ldelim}"lat" : {$v->Parcelshop->GLSCoordinates->Latitude}, "lng" : {$v->Parcelshop->GLSCoordinates->Longitude}, "name" : "{$v->Parcelshop->Address->Name1|escape:'javascript'}", "id" : "{$labels.$k}", "infos" : "{$opening_hours|escape:'javascript' nofilter}"{rdelim});
        {/foreach}
    </script>
{/if}
