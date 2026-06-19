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
        <div class="col-xs-12">
            <article id="delivery-address-relay" class="box">
                <h4>{l s='GLS Relais delivery address' mod='nkmgls'}</h4>
                <address>{$address_delivery_formatted nofilter}</address>
            </article>
        </div>
    </div>

{/if}
