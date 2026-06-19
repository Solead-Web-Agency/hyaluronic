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
 * @package   CedWish
 */
-->

<button id="cedwish-product-modal-button" style="display: none;" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#cedwish-product-modal">Open Modal</button>
<div id="cedwish-product-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content" style="width: min-content;min-width:90%;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="wish_popup_title">{l s='Wish Product' mod='cedwish'}</h4>
            </div>
            <div class="modal-body" id="list_content">

            </div>
        </div>
    </div>
</div>
<script type="application/javascript">
    function viewProductError(button_object, product_id,id_product_attribute, product_grid_token) {
        button_object.style.disabled = true;
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishProduct',
                action: 'getProductError',
                token: product_grid_token,
                product_id:product_id,
                id_product_attribute:id_product_attribute,
            },
            success: function(response) {
                button_object.style.disabled = false;
                var obj = JSON.parse(response);
                document.getElementById('list_content').innerHTML = obj.message;
                document.getElementById('cedwish-product-modal-button').click();
            },
            statusCode: {
                500: function(xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    $("#list_content").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                },
                404: function (response) {
                    $("#list_content").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

            },
        });
    }

    function viewProductData(button_object, marketplace_id,product_grid_token) {
        button_object.style.disabled = true;
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishProduct',
                action: 'getProductData',
                token: product_grid_token,
                marketplace_id:marketplace_id,
            },
            success: function(response) {
                button_object.style.disabled = false;
                var obj = JSON.parse(response);
                document.getElementById('list_content').innerHTML = obj.message;
                document.getElementById('cedwish-product-modal-button').click();
            },
            statusCode: {
                500: function(xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    $("#list_content").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                },
                404: function (response) {
                    $("#list_content").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

            },
        });
    }

    function viewProductReturnSetting(button_object, marketplace_id,product_grid_token, id_product, id_product_attribute) {
        button_object.style.disabled = true;
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishProduct',
                action: 'getProductReturnSetting',
                token: product_grid_token,
                marketplace_id:marketplace_id,
                id_product:id_product,
                id_product_attribute:id_product_attribute,
            },
            success: function(response) {
                button_object.style.disabled = false;
                var obj = JSON.parse(response);
                document.getElementById('list_content').innerHTML = obj.message;
                document.getElementById('cedwish-product-modal-button').click();
            },
            statusCode: {
                500: function(xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    $("#list_content").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                },
                404: function (response) {
                    $("#list_content").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

            },
        });
    }

    function unenrollProductReturnSetting(button_object, marketplace_id,product_grid_token, region) {
        button_object.style.disabled = true;
        var accpet = confirm("Are you Sure Want to UnEnroll Product");
        if (accpet) {
            $.ajax({
                type: "POST",
                url: 'ajax-tab.php',
                data: {
                    ajax: true,
                    controller: 'AdminCedWishProduct',
                    action: 'unEnrollProductReturnSetting',
                    token: product_grid_token,
                    marketplace_id:marketplace_id,
                    region:region,
                },
                success: function(response) {
                    button_object.style.disabled = false;
                    var obj = JSON.parse(response);
                    document.getElementById('list_content').innerHTML = obj.message;
                    document.getElementById('cedwish-product-modal-button').click();
                    location = location.href;
                },
                statusCode: {
                    500: function(xhr) {
                        if (window.console) console.log(xhr.responseText);
                    },
                    400: function (response) {
                        $("#list_content").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                    },
                    404: function (response) {
                        $("#list_content").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if (window.console) console.log(xhr.responseText);
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

                },
            });
        }
    }

</script>
{literal}
<style>
    th.cedwish_product_name {
        width: 250px !important;
    }
    td.cedwish_product_name {
        width: 250px !important;
    }
</style>
{/literal}
