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
<div class="modal-body">
    <div class="d-flex flex-column justify-content-center align-items-center">
        <img src="{$gls_img_path|escape:'quotes':'UTF-8'}gls-file.gif" alt="icône" class="merging-icon">
        <div class="progress">
            <div class="progress-bar bg-primary" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
        </div>
        <ul class="progress-steps">
            <li><span class="icon-stack icon-lg"><i class="icon icon-circle icon-stack-2x text-muted"></i><i class="icon icon-check icon-stack-1x icon-inverse hidden"></i></span> {l s='Preparation of the environment' mod='nkmgls'}</li>
            <li><span class="icon-stack icon-lg"><i class="icon icon-circle icon-stack-2x text-muted"></i><i class="icon icon-check icon-stack-1x icon-inverse hidden"></i></span> {l s='Generation of labels' mod='nkmgls'} <span class="counter">0</span> / <span class="total-counter">0</span></li>
            <li><span class="icon-stack icon-lg"><i class="icon icon-circle icon-stack-2x text-muted"></i><i class="icon icon-check icon-stack-1x icon-inverse hidden"></i></span> {l s='Generation of final PDF file' mod='nkmgls'}</li>
        </ul>
        <div id="gls-generate-label-error" class="alert alert-danger" role="alert" style="display: none;">
            {l s='An error occured, please contact technical support' mod='nkmgls'}
        </div>
        <div id="gls-generate-label-warning" class="alert alert-warning " role="alert" style="display: none;">
            {l s='You can download successfully generated labels, some are in error and have to be regenerated' mod='nkmgls'}
        </div>
        <button class="btn btn-primary btn-lg" disabled>{l s='Download labels' mod='nkmgls'}</button>
    </div>
</div>