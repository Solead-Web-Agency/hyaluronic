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
                    </div>
                </div>
                <div class="col-md-3 right-column">
                    <div class="row">
                        {foreach from=$technical_requirements.errors item=message}
                            <div class="col-md-12">
                                <div class="alert alert-danger" role="alert">
                                    <p class="alert-text">
                                        {$message}
                                    </p>
                                </div>
                            </div>
                        {/foreach}
                        {foreach from=$technical_requirements.warnings item=message}
                            <div class="col-md-12">
                                <div class="alert alert-warning" role="alert">
                                    <p class="alert-text">
                                        {$message}
                                    </p>
                                </div>
                            </div>
                        {/foreach}
                        <div class="col-md-12">
                            <div class="alert alert-warning" role="alert">
                                <p class="alert-text">
                                    {$gls_config_contact}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-warning" role="alert">
                                <p class="alert-text">
                                    {l s='Remember to configure the shipping costs for your different' mod='nkmgls'} <a class="alert-link" href="{$carrier_link|escape:'quotes':'UTF-8'}">{l s='Carriers' mod='nkmgls'}</a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info" role="alert">
                                <p class="alert-text">
                                    {l s='To export your orders or import the tracking numbers, go to your' mod='nkmgls'} <a class="alert-link" href="{$order_link|escape:'quotes':'UTF-8'}">{l s='Orders' mod='nkmgls'}</a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info" role="alert">
                                <p class="alert-text">
                                    {l s='To print your delivery labels, go to your' mod='nkmgls'} <a class="alert-link" href="{$label_link|escape:'quotes':'UTF-8'}">{l s='Orders' mod='nkmgls'}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>