{*
**
* Newsletter Popup Module 
* 2007-2015 logicalit.com
* NOTICE OF LICENSE
*
* Copy Right, all rights reserved: logicalit.com
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customise this module for your
* needs please contact http://www.logicalit.com/contact-us/
*
*    @author logicalit.com
*    @copyright  2007-2015 logicalit.com
*    @version  Release: $Revision: 1.0
*    @license  http://www.logicalit.com/prestashop-modules/en/content/3-terms-and-conditions-of-use
*
*}

<div class="panel">
    <h3><i class="icon icon-tags"></i> {l s='Hotjar Tracking Code Install' mod='hotjar'}</h3>
    <a href="https://www.hotjar.com/" target="_blank"><img src="{$this_path_ssl|escape:'htmlall':'UTF-8'}config-logo.png"></a>
    <p></p>
    <p>
        {l s='Use hotjar recordings to identify usability issues by watching recordings of real visitors on 
                     your site as they click, tap, move their cursor, type and navigate across pages.' mod='hotjar'} 

    </p>
    <p>
        {l s='To install the Hotjar tracking code simply copy the tracking code from your Hotjar admin and paste it in the form below and click save.'}
    </p>
</div>

<form id="module_form" class="defaultForm form-horizontal" action="{$form_link}" method="post" enctype="multipart/form-data" novalidate>
    
    <div class="panel" id="fieldset_0">

        <div class="panel-heading">
            <i class="icon-cogs"></i>Configuration
        </div>

        <div class="form-wrapper">

            <div class="form-group">
                <label class="control-label col-lg-3">
                    Hotjar Tracking Code
                </label>
                <div class="col-lg-9">
                    <textarea
                           name="hotjar_code"
                           id="hotjar_code"
                           rows="10"
                           />{if isset($hotjar_code)}{$hotjar_code}{/if}</textarea>

                    <p class="help-block">
                        Paste your Hotjar tracking code here
                    </p>
                </div>
            </div>
        </div><!-- /.form-wrapper -->



        <div class="panel-footer">
            <button type="submit" value="1"	id="module_form_submit_btn" name="submitUpdate" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Save
            </button>
        </div>

    </div>


</form>
