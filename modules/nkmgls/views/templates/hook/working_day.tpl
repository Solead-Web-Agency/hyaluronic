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
<table class="gls-relay-info-open-hours table" style="width:100%">
	<tbody>
		{foreach from=$trans_days key=day item=dname}
			<tr>
                {if isset($workingDayObject[$day])}
                    <th valign="top" style="text-align:left;">
                    	<font size="2" face="Open-sans, sans-serif" color="#666666">
                    		<span style="color:#333">
                    			<strong>
                    				<span data-html-only="1">{$dname}</span>
                    			</strong>
                    		</span>
                    	</font>
                    </th>
					<td>
						<font size="2" face="Open-sans, sans-serif" color="#666666">
							<span data-html-only="1">{$workingDayObject[$day]['OpeningHours']['Hours']['From']|substr:0:2}:{$workingDayObject[$day]['OpeningHours']['Hours']['From']|substr:2:2}{literal} - {/literal}
                    			{if isset($workingDayObject[$day]['Breaks']['Hours']['From'])}
                        			{$workingDayObject[$day]['Breaks']['Hours']['From']|substr:0:2}:{$workingDayObject[$day]['Breaks']['Hours']['From']|substr:2:2}
                        			<br/>{$workingDayObject[$day]['Breaks']['Hours']['To']|substr:0:2}:{$workingDayObject[$day]['Breaks']['Hours']['To']|substr:2:2}
                        			{literal} - {/literal}{$workingDayObject[$day]['OpeningHours']['Hours']['To']|substr:0:2}:{$workingDayObject[$day]['OpeningHours']['Hours']['To']|substr:2:2}
                    			{else}
                        			{$workingDayObject[$day]['OpeningHours']['Hours']['To']|substr:0:2}:{$workingDayObject[$day]['OpeningHours']['Hours']['To']|substr:2:2}
								{/if}
                    	</span>
                    	</font>
                    	</td>
                {else}
                    <th valign="top" style="text-align:left;">
                    	<font size="2" face="Open-sans, sans-serif" color="#666666"><span style="color:#333"><strong><span data-html-only="1">{$dname}</span></strong></span></font>
					</th>
					<td><font size="2" face="Open-sans, sans-serif" color="#666666"><span>{l s='Closed' mod='nkmgls'}</span></font></td>
                {/if}
            </tr>
		{/foreach}
	</tbody>
</table>
