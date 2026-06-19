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

<table width="100%" id="body" border="0" cellpadding="0" cellspacing="0" style="margin:0;">
	<tr>
		<td colspan="12">
            <table class="product" width="100%" cellpadding="4" cellspacing="0">
                <thead>
                    <tr>
                        <th class="product header small" width="8%">{l s='Shipping No.' mod='nkmgls'}</th>
                        <th class="product header small" width="14%">{l s='Product/Service' mod='nkmgls'}</th>
                        <th class="product header small" width="9%">{l s='Order' mod='nkmgls'}</th>
                        <th class="product header small" width="8%">{l s='Order date' mod='nkmgls'}</th>
                        <th class="product header small" width="16%">{l s='Recipient name' mod='nkmgls'}</th>
                        <th class="product header small" width="17%">{l s='Address' mod='nkmgls'}</th>
                        <th class="product header small" width="7%">{l s='Postcode' mod='nkmgls'}</th>
                        <th class="product header small" width="10%">{l s='City' mod='nkmgls'}</th>
                        <th class="product header small" width="6%">{l s='Country' mod='nkmgls'}</th>
                        <th class="product header-right small" width="5%">{l s='Weight (kg)' mod='nkmgls'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $packages as $package}
		                <tr class="product">
                            <td class="center">{$package.shipping_number|escape:'htmlall':'UTF-8'}</td>
                            {if is_array($product_code) && isset($product_code[$package.gls_product])}
                                <td>{$product_code[$package.gls_product]|escape:'htmlall':'UTF-8'}</td>
                            {else}
                                <td>{$package.gls_product|escape:'htmlall':'UTF-8'}</td>
                            {/if}
                            <td class="center">{if $gls_order_reference_enable}{$package.order_reference|escape:'htmlall':'UTF-8'}{else}{$package.id_order|escape:'htmlall':'UTF-8'}{/if}</td>
                            <td class="center">{dateFormat date=$package.order_date full=0}</td>
                            <td>{$package.customer|escape:'htmlall':'UTF-8'}</td>
                            <td>{$package.address1|escape:'htmlall':'UTF-8'}
                                {if !empty($package.address2)}
                                    <br>{$package.address2|escape:'htmlall':'UTF-8'}
                                {/if}
                            </td>
                            <td>{$package.postcode|escape:'htmlall':'UTF-8'}</td>
                            <td>{$package.city|escape:'htmlall':'UTF-8'}</td>
                            <td>{$package.iso_code|escape:'htmlall':'UTF-8'}</td>
                            <td class="right">{$package.weight|floatval}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </td>
    </tr>

    <tr>
		<td colspan="12" height="30">&nbsp;</td>
	</tr>

    <tr>
		<td colspan="12">
            <table>
                <tr class="big">
                    <td style="width: 35%" class="border">
                        <b>{l s='Customer signature' mod='nkmgls'}</b>
                        <br><br><br><br><br>
                    </td>
                    <td style="width: 30%"></td>
                    <td style="width: 35%" class="border">
                        <b>{l s='Carrier signature' mod='nkmgls'}</b>
                        <br><br><br><br><br>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>