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
{extends file="helpers/form/form.tpl"}

{block name="fieldset"}
    <div class="form-contenttab tab-pane{if $f == 0} active{/if}" id="nkmgls-tab-{$f|escape:'quotes':'UTF-8'}" role="tabpanel">
        <div class="row">
            <div class="col-md-12">
                {$smarty.block.parent}
            </div>
        </div>
    </div>
{/block}

{block name="after"}
    <div class="form-footer">
        <div class="col-lg-11 text-right">
            <a class="btn btn-primary save uppercase js-btn-save" type="submit" href="#" data-toggle="tooltip" title="{l s='Save module configuration' mod='nkmgls'}">
                <span>{l s='Save' mod='nkmgls'}</span>
            </a>
        </div>
    </div>
{/block}

{block name="input_row"}
    {if $input.type == 'select' && isset($input.carrier_img)}
        <div class="col-lg-2 text-center">
            <img src="{$input.carrier_img|escape:'quotes':'UTF-8'}" alt="{$input.label|escape:'quotes':'UTF-8'}" class="img-responsive center-block m-b-1" />
        </div>
        {$smarty.block.parent}
    {elseif $input.type == 'hr'}
        <hr />
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="script"}

    $(document).ready(function() {

        $('.btn.create-carrier').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: "{$ajax_uri|escape:'javascript':'UTF-8'}",
                data: {
                    ajax: "1",
                    code: $(this).data('carrier'),
                },
                dataType : "json",
                success: function(jsonData) {
                    if (jsonData.hasError) {
                        alert(jsonData.errors);
                    } else {
                        var url = window.location.search;
                        var re = new RegExp('[?&]tab=([^&]*)');
                        if(url.match(re)) {
                            window.location.search = window.location.search.replace(re, '&tab=' + $('#form-nav .nav-item.active > a').attr('href').substr(1));
                        } else {
                            window.location.search += '&tab=' + $('#form-nav .nav-item.active > a').attr('href').substr(1);
                        }
                    }
                }
            });

        });
    });

{/block}