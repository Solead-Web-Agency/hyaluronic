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
<div class="row">
    <div class="col-xl-10 offset-xl-1 module-page">
        <div class="tabs js-tabs">
            <ul class="nav nav-tabs js-nav-tabs" id="form-nav" role="tablist">
                <li class="nav-item active">
                    <a href="#nkmgls-tab-0" role="tab" data-toggle="tab" class="nav-link">{if $ps_version == '1.6'}<i class="icon-comment"></i>{else}<i class="material-icons">sms</i>{/if} {l s='Presentation' mod='nkmgls'}</a>
                </li>
                <li class="nav-item">
                    <a href="#nkmgls-tab-1" role="tab" data-toggle="tab" class="nav-link">{if $ps_version == '1.6'}<i class="icon-truck"></i>{else}<i class="material-icons">local_shipping</i>{/if} {l s='Carriers' mod='nkmgls'}</a>
                </li>
                <li class="nav-item">
                    <a href="#nkmgls-tab-2" role="tab" data-toggle="tab" class="nav-link">{if $ps_version == '1.6'}<i class="icon-user"></i>{else}<i class="material-icons">person</i>{/if} {l s='GLS Account' mod='nkmgls'}</a>
                </li>
                <li class="nav-item">
                    <a href="#nkmgls-tab-3" role="tab" data-toggle="tab" class="nav-link">{if $ps_version == '1.6'}<i class="icon-cog"></i>{else}<i class="material-icons">settings</i>{/if} {l s='Settings' mod='nkmgls'}</a>
                </li>
                <li class="nav-item">
                    <a href="#nkmgls-tab-4" role="tab" data-toggle="tab" class="nav-link">{if $ps_version == '1.6'}<i class="icon-bug"></i>{else}<i class="material-icons">bug_report</i>{/if} {l s='Support' mod='nkmgls'}</a>
                </li>
                <li class="nav-item">
                    <a href="#nkmgls-tab-5" role="tab" data-toggle="tab" class="nav-link">{if $ps_version == '1.6'}<i class="icon-list-ul"></i>{else}<i class="material-icons">format_list_bulleted</i>{/if} {l s='Logs' mod='nkmgls'}</a>
                </li>
            </ul>
        </div>
        <div class="container-fluid config-wrapper">
            <div class="row">
                <div class="col-md-9 left-column">
                    <div class="tab-content">