{*
* 2007-2022 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

<!-- Modal -->
<div class="modal fade" id="etsAcModalImportEmailTemplate" tabindex="-1" role="dialog" aria-labelledby="etsAcModalImportEmailTemplateLabel">
    <div class="modal-dialog" role="document">
        <form action="{$linkImportEmailTemplate nofilter}" type="POST" class="form-horizontal" id="formEtsAcImportEmailTemplate">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="etsAcModalImportEmailTemplateLabel">{l s='Import new email template' mod='ets_abandonedcart'}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-errors"></div>
                    <div class="form-group row">
                        <label class="control-label col-lg-4 required">{l s='File' mod='ets_abandonedcart'}</label>
                        <div class="col-lg-8">
                            <input type="file" name="email_template" required="required" />
                            <p class="help-block mt-10px">{l s='Allow files: *.zip. Limit: %s Mb.' sprintf=[$maxSizeUpload] mod='ets_abandonedcart'}
                                <a href="{$linkConfig|escape:'quotes':'UTF-8'}">{l s='Config max upload file' mod='ets_abandonedcart'}</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel' mod='ets_abandonedcart'}</button>
                    <button type="button" class="btn btn-primary js-ets-ac-import-email-temp" name="etsAcImportEmailTemplate">{l s='Import' mod='ets_abandonedcart'}</button>
                </div>
            </div>
        </form>
    </div>
</div>