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
{$style_tab}

<table style="width: 100%">
    <tr>
        <td style="width: 35%">
            <table class="border">
                <tr style="width: 100%;">
                    <td>
                        {$shop_address}
                        <br><br>
                        {l s='Client No.:' mod='nkmgls'} {$client_number|escape:'htmlall':'UTF-8'}
                        <br>
                        {l s='Web:' mod='nkmgls'} {$shop_url|escape:'htmlall':'UTF-8'}
                    </td>
                </tr>
            </table>
        </td>
        <td style="width: 65%">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;" class="h1">
                        {l s='PACKING LIST' mod='nkmgls'}
                    </td>
                    <td style="width: 50%;"></td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="left">
                        <br><br>
                        {l s='Delivery date to GLS:' mod='nkmgls'} <b>{$packing_list_date|escape:'htmlall':'UTF-8'}</b><br>
                        {l s='Number of packages:' mod='nkmgls'} <b>{$nb_labels|escape:'htmlall':'UTF-8'}</b><br>
                        {l s='Total weight (Kg):' mod='nkmgls'} <b>{$total_weight|escape:'htmlall':'UTF-8'}</b>
                    </td>
                    <td style="width: 60%;" class="right">
                        <img src="{$gls_logo|escape:'quotes':'UTF-8'}" alt="GLS" width="{$width_logo|escape:'quotes':'UTF-8'}" height="{$height_logo|escape:'quotes':'UTF-8'}" />
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
