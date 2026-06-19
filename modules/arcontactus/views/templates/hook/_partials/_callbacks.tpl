{*
* 2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*}
<div class="arcontactus-config-panel" id="arcontactus-callbacks">
    <div class="panel">
        <div class="panel-heading show-heading">
            <i class="icon-cog"></i> {l s='Callback requests' mod='arcontactus'}
            <span class="panel-heading-action">
                <a class="list-toolbar-btn" onclick="arCU.callback.reload(); return false;" href="#">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Refresh list" data-html="true" data-placement="top">
                        <i class="process-icon-refresh"></i>
                    </span>
                </a>
            </span>
        </div>
        <div class="form-wrapper">
            <div id="form-arcu-callbacks-container">
                <div id="form-callbacks" class="arcu-placeholder">
                    <input type="hidden" name="page" value="1" />
                    <input type="hidden" name="selected_pagination" value="20" />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="arcontactus-custom-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="arcontactus-prompt-modal-title"></h4>
            </div>
            
            <div class="modal-body">
                
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='arcontactus'}</button>
            </div>
        </div>
    </div>
</div>