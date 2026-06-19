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
 * @package   CedEbay
 */
 -->
<style>
    .control-label {
        text-align: right;
        margin-bottom: 0;
        padding-top: 7px;
    }

    .control-label {
        text-align: right;
        margin-bottom: 0;
        padding-top: 7px;
    }

    #importer-overlay {
        position: fixed; /* Sit on top of the page content */
        display: none; /* Hidden by default */
        width: 100%; /* Full width (cover the whole page) */
        height: 100%; /* Full height (cover the whole page) */
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.1); /* Black background with opacity */
        z-index: 2; /* Specify a stack order in case you're using a different order for other elements */
        cursor: pointer; /* Add a pointer on hover */
    }

    .overlay-content {
        position: relative;
        top: 50%; /* 25% from the top */
        width: 100%; /* 100% width */
        text-align: center; /* Centered text/links */
    }
</style>
<div class="bootstrap" id="importer_error" style="display: none;">
    <div class="alert alert-danger">
        <button type="button" class="close close-message" onclick="closeErrorMessage()">×</button>
        <span id="importer_error_msg">Error</span>
    </div>
</div>
<div class="bootstrap" id="importer_success" style="display: none;">
    <div class="alert alert-success">
        <button type="button" class="close close-message" onclick="closeErrorMessage()">×</button>
        <span id="importer_success_msg">Success</span>
    </div>
</div>
<div id="importer-overlay">
    <div class="overlay-content">
        Loading...
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-tags"></i> {l s='Import Wish Order' mod='cedwish'}
    </div>
    <div class="panel-body">
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Wish Order ID' mod='cedwish'}
            </label>
            <div class="col-lg-6">
                <input class="form-control" name="import_order_id" id="import_order_id">
                <p class="help-block">
                    {l s='Wish Order ID you want to import' mod='cedwish'}
                </p>
            </div>
            <div class="col-lg-3">
                <button
                        type="button"
                        id="saveAccount"
                        onclick="importOrder()"
                        class="btn btn-primary"
                >
                    {l s='Fetch Order' mod='cedwish'}
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    function importOrder() {
        $("#importer-overlay").show();
        $("#importer_error").hide();
        $("#importer_success").hide();
        var import_order_id = $('#import_order_id').val();
        if (import_order_id) {
            $.ajax({
                type: 'POST',
                url: 'ajax-tab.php',
                data: {
                    controller: 'AdminCedWishOrder',
                    ajax: true,
                    action: 'searchImportOrder',
                    orderId: import_order_id,
                    token: '{$wish_order_fetch_token|escape:'htmlall':'UTF-8'}'
                },
                success: function (response) {
                    response = JSON.parse(response);
                    console.log(response);
                    if (response.success==true) {
                        $('html,body').scrollTop(0);
                        $("#importer_success").show();
                        $("#importer_success_msg").html(response.message);
                    } else {
                        $('html,body').scrollTop(0);
                        $("#importer_error").show();
                        $("#importer_error_msg").html(response.message);
                    }
                    $("#importer-overlay").hide();
                },
            });
        } else {
            $('html,body').scrollTop(0);
            $("#importer_error").show();
            $("#importer_error_msg").html('Invalid order id');
            $("#importer-overlay").hide();
        }
    }

    function closeErrorMessage() {
        $("#importer_error_msg").html('');
        $("#importer_error").hide();
    }

</script>
