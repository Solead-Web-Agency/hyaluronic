<!--
/**
* CedCommerce
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement(EULA)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://cedcommerce.com/license-agreement.txt
*
* @author    CedCommerce Core Team <connect@cedcommerce.com>
* @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
* @license   http://cedcommerce.com/license-agreement.txt
* @category  Ced
* @package   cedwish
*/
-->

<div class="panel">
    <form name="cedwish_profile_form" method="post" onsubmit="return validateForm();">
    <div class="panel-body">
            <div class="productTabs">
                <ul class="tab nav nav-tabs">
                    <li class="tab-row active">
                        <a class="tab-page" href="#profileInfo" data-toggle="tab">
                            <i class="icon-file-text"></i> {l s='Store Parameters' mod='cedwish'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#profileAttribute" data-toggle="tab">
                            <i class="icon-wrench"></i> {l s='Attribute(s)' mod='cedwish'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#profileShipping" data-toggle="tab">
                            <i class="icon-wrench"></i> {l s='Shipping(s)' mod='cedwish'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#profileDefaultValue" data-toggle="tab">
                            <i class="icon-book"></i> {l s='Product Settings' mod='cedwish'}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="panel tab-pane fade in active row" id="profileInfo">
                    {include file="./store_parameter.tpl"}
                </div>
                <div class="panel tab-pane" id="profileAttribute">
                    {include file="./category_attribute_mapping.tpl"}
                </div>
                <div class="panel tab-pane" id="profileShipping">
                    {include file="./product_shipping.tpl"}
                </div>
                <div class="panel tab-pane" id="profileDefaultValue">
                    {include file="./product_setting.tpl"}
                </div>
            </div>

    </div>
    <div class="panel-footer">
        <button type="submit" value="1" id="cedwish_profile_form_submit_btn" name="submitAddcedwish_profile"
                class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Save' mod='cedwish'}
        </button>
    </div>
    </form>
</div>

<script>
    function closeMessage() {
        $("#error-message").hide();
    }

    function validateForm() {
        var has_error = false;
        var message = '';
        var name = document.getElementById('name').value;

        if (name == "") {
            has_error = true;
            message += '<br>' + '{l s='Name is required Field' mod='cedwish'}';
        }

        if (has_error) {
            $("#error-message").show();
            document.getElementById("default-error-message-text").innerHTML = message;
            document.getElementById("profileCode").focus();
            return false;
        } else
            return true;
    }
</script>
