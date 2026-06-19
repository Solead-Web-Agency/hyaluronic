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
{$title|escape:'htmlall':'UTF-8'}
<br>
<div class="form-group-inline">
	<div class="input-group">
		<input type="text" class="form-control copy-input" value="{$cron_uri|escape:'quotes':'UTF-8'}" disabled>
        <span class="input-group-btn">
        	<button class="btn btn-default copy-button" type="button" data-placement="button"><i class="icon-copy"></i> {l s='Copy' mod='nkmgls'}</button>
		</span>
	</div>
    <div class="form-group">
    	<button class="btn btn-default exec-button" type="button" data-placement="top" data-toggle="tooltip" title="{l s='Save before you can run this' mod='nkmgls'}">{$btn_title|escape:'htmlall':'UTF-8'}</button>
	</div>
</div>
<br>
{$title_path|escape:'htmlall':'UTF-8'}
<div class="input-group">
	<input type="text" class="form-control copy-input" value="{$cron_path|escape:'quotes':'UTF-8'}" disabled>
    <span class="input-group-btn">
    	<button class="btn btn-default copy-button" type="button" data-placement="button"><i class="icon-copy"></i> {l s='Copy' mod='nkmgls'}</button>
    </span>
</div>