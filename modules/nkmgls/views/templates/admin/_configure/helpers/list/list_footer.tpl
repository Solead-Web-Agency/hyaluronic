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
{extends file="helpers/list/list_footer.tpl"}

{block name="footer"}
    {if $smarty.get.controller == 'AdminGlsLabel'}
        <div class="panel-footer">
            <button type="submit" value="1" name="generateLabelStep3" class="btn btn-default pull-right">
                <i class="process-icon-next"></i>{l s='Next' mod='nkmgls'}
            </button>
            <button type="submit" value="1" name="generateLabelStep2Cancel" class="btn btn-default">
                <i class="process-icon-cancel"></i>{l s='Cancel' mod='nkmgls'}
            </button>
        </div>
    {elseif $smarty.get.controller == 'AdminGlsPackingList'}
        <div class="panel-footer">
            {if isset($smarty.post.GLS_PACKING_LIST_ORDER_DATE_FROM_FILTER)}
                <input type="hidden" name="GLS_PACKING_LIST_ORDER_DATE_FROM_FILTER" id="GLS_PACKING_LIST_ORDER_DATE_FROM_FILTER" value="{$smarty.post.GLS_PACKING_LIST_ORDER_DATE_FROM_FILTER|escape:'quotes':'UTF-8'}">
            {/if}
            {if isset($smarty.post.GLS_PACKING_LIST_ORDER_DATE_TO_FILTER)}
                <input type="hidden" name="GLS_PACKING_LIST_ORDER_DATE_TO_FILTER" id="GLS_PACKING_LIST_ORDER_DATE_TO_FILTER" value="{$smarty.post.GLS_PACKING_LIST_ORDER_DATE_TO_FILTER|escape:'quotes':'UTF-8'}">
            {/if}
            <button type="submit" value="1" name="printPackingList" class="btn btn-default pull-right">
                <i class="process-icon-next"></i>{l s='Print packing list' mod='nkmgls'}
            </button>
            <button type="submit" value="1" name="generatePackingListStep2Cancel" class="btn btn-default">
                <i class="process-icon-cancel"></i>{l s='Cancel' mod='nkmgls'}
            </button>
        </div>
    {else}
        <div class="panel-footer">
            <button type="submit" value="1" name="exportOrderSelected" class="btn btn-default pull-right">
                <i class="process-icon-upload"></i>{l s='Export' mod='nkmgls'}
            </button>
            <a href="#" class="close-export-modal btn btn-default">
                <i class="process-icon-cancel"></i>{l s='Cancel' mod='nkmgls'}
            </a>
        </div>
    {/if}
{/block}
