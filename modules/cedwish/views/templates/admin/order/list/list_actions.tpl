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

 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   cedwish
 */
-->
<button
        type="button"
        class="btn btn-info btn-lg hidden"
        data-toggle="modal"
        data-target="#module-modal-cedwish"
        id="module-modal-cedwish-button"
        style="display: none;"
>
    <i class="material-icons">edit</i>
</button>
<div id="module-modal-cedwish" class="modal  modal-vcenter fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title module-modal-title">Order Edit and Re Import</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <input type="hidden" id="wish_order_id" />
                            <label class="text-danger" id="order-error-message"></label>
                            <textarea
                                    rows="25"
                                    name="feed_content"
                                    class="form-control"
                                    id="feed_content"
                            >
                                Please Wait.....
                            </textarea>
                        </div>
                        <div class="text-center">
                            <button type="button"
                                    onclick="saveAndResend(this,document.getElementById('wish_order_id').value);"
                                    class="btn btn-primary">Save and Reimport
                            </button>
                        </div>
                    </div>
                    <div id="progress">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function prettyPrint(ugly) {
        return  JSON.stringify(ugly, undefined, 4);
    }
    function getOrderDetails(button_object, wish_order_id) {
        button_object.disabled = true;
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishOrder',
                action: 'getOrderDetails',
                token: "{$wish_order_token|escape:'htmlall':'UTF-8'}",
                wish_order_id: wish_order_id,
            },
            success: function (response) {
                response = JSON.parse(response);
                button_object.disabled = false;
                if (response.message) {
                    var success_message = response.message;
                    success_message = prettyPrint(success_message)
                    if (document.getElementById('progress')) {
                        document.getElementById('progress').innerHTML = '';
                    }
                    document.getElementById('order-error-message').innerHTML = '';
                    if (document.getElementById('order-error-message')
                        && document.getElementById('order-error-message'+wish_order_id)
                    ) {
                        document.getElementById('order-error-message').innerHTML
                            = document.getElementById('order-error-message'+wish_order_id).value;
                    }
                    if (document.getElementById('feed_content')) {
                        document.getElementById('feed_content').innerHTML = success_message;
                    }
                    if (document.getElementById('wish_order_id')) {
                        document.getElementById('wish_order_id').value = wish_order_id;
                    }
                    if (document.getElementById('module-modal-cedwish-button')) {
                        document.getElementById('module-modal-cedwish-button').click();
                    }
                } else {
                    alert('Some error occurred ');
                }
            },
            statusCode: {
                500: function (xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    $("#progress").append(
                        '<span style="color:Red;">Some error while reimport contact developer</span>'
                    );
                },
                404: function (response) {
                    $("#progress").append(
                        '<span style="color:Red;">Some error while reimport contact developer</span>'
                    );
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            },
        });
    }
    function saveAndResend(button_object, wish_order_id) {
        button_object.disabled = true;
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishOrder',
                action: 'resubmitFeed',
                token: "{$wish_order_token|escape:'htmlall':'UTF-8'}",
                feed_content: $('#feed_content').val(),
                wish_order_id: wish_order_id,
            },
            success: function (response) {
                response = JSON.parse(response);
                button_object.disabled = false;
                if (response.success && response.message) {
                    var success_message = response.message;
                    $("#progress").append(
                        '<li class="alert alert-success" >' + success_message + '</li>'
                    );
                } else if(response.message){
                    $("#progress").append(
                        '<li class="alert alert-danger">'+ response.message + '</li>'
                    );
                } else {
                    $("#progress").append(
                        '<li class="alert alert-danger">Some Error Occurred </li>'
                    );
                }
                location = location.href;
            },
            statusCode: {
                500: function (xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    $("#progress").append(
                        '<span style="color:Red;">Some error while reimport contact developer</span>'
                    );
                },
                404: function (response) {
                    $("#progress").append(
                        '<span style="color:Red;">Some error while reimport contact developer</span>'
                    );
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            },
        });
    }
</script>
