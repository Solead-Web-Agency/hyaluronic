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

{if isset($CED_WISH_ACCESS_TOKEN) && !empty($CED_WISH_REFRESH_TOKEN)}
    <button
            type="button"
            class="btn btn-primary btn-block"
            onclick="refreshToken()"
            value="1"
            name="refresh_wish_token"
    >
        {l s='Refresh Token' mod='cedwish'}
    </button>
    <button
            type="button"
            class="btn btn-danger btn-block"
            onclick="resetToken()"
            value="1"
            title="{l s='Reset Token means delete old token and perform authorization again.' mod='cedwish'}"
            name="refresh_wish_token"
    >
        {l s='Reset Token' mod='cedwish'}
    </button>
{else}
    <button
            type="button"
            class="btn btn-primary btn-block"
            onclick="getToken()"
            value="1"
            name="get_wish_token"
    >
        {l s='Get Token' mod='cedwish'}
    </button>
{/if}

<button id="ced_wish-authorise-modal-button" style="display: none;" type="button" class="btn btn-info btn-lg"
        data-toggle="modal" data-target="#ced_wish-authorise-modal">
    authorise
</button>
<div id="ced_wish-authorise-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Wish Authorise' mod='cedwish'}</h4>
            </div>
            <div class="modal-body" id="authorise_content">
                <h4 class="text-danger">{l s='Please do not close or reload the page, it will refreshed automatically
                once authorization done' mod='cedwish'}</h4>
                <p>{l s='Follow below instructions' mod='cedwish'}</p>
                <ol>
                    <li>{l s='Please Login to Wish and Click On "Allow access" Button at bottom.' mod='cedwish'}</li>
                    <li> {l s='Once you authorise it, you will see "Authorised Successfully with code and token
                on screen.' mod='cedwish'}</li>
                    <li>{l s='After that back to this tab again and wait this page will reloaded automatically.Once reloaded Click on "Save"
                Button below.' mod='cedwish'}</li>
                </ol>
                <ul style="list-style: none;">
                    <li>
                        <i class="icon-check-circle"></i>
                        {l s='Fetch Token' mod='cedwish'}
                        <div class="text-warning" id="token_generate_process">

                        </div>
                    </li>
                    <li>
                        <i class="icon-check-circle"></i>
                        {l s='Fetch Currency' mod='cedwish'}
                        <div class="text-info" id="currency_fetch_process">

                        </div>
                    </li>
                    <li>
                        <i class="icon-check-circle"></i>
                        {l s='Fetch Warehouses' mod='cedwish'}
                        <div class="text-info" id="warehouse_fetch_process">

                        </div>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button
                        id="ced_wish-authorise-modal-close"
                        type="button"
                        class="btn btn-default"
                        data-dismiss="modal">
                    {l s='Close' mod='cedwish'}
                </button>
            </div>
        </div>

    </div>
</div>
<script type="application/javascript">
    $("#CED_WISH_API_MODE").change(function (){
        var api_mode = this.value;
        if (api_mode) {
            $("#module_form_submit_btn").click();
        }
    });
    var admin_token = '{$auth_check_token|escape:'javascript':'UTF-8'}';
    function getToken() {
        $.ajax({
            type: 'POST',
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishSetting',
                action: 'authorisationUrl',
                token: admin_token,
                merchant_id: 'wish_merchant_validation',
            },
            success: function (json) {
                json = JSON.parse(json);
                try {
                    if (json.success) {
                        document.getElementById('ced_wish-authorise-modal-button').click();
                        isAuthorisationDone();
                        window.open(json.redirect_uri, '_blank');
                    } else {
                        alert(json.error);
                    }
                } catch (e) {
                    console.log(e.message);
                }
            }
        });
    }

    function resetToken() {
        $.ajax({
            type: 'POST',
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishSetting',
                action: 'resetToken',
                token: admin_token,
                merchant_id: 'wish_merchant_validation',
            },
            success: function (json) {
                json = JSON.parse(json);
                try {
                    if (json.success) {
                        location = location.href;
                    } else {
                        alert(json.error);
                    }
                } catch (e) {
                    console.log(e.message);
                }
            }
        });
    }

    function isAuthorisationDone() {
        $("#token_generate_process").html('<p>Fetching Token in Progress......</p>');
        $.ajax({
            type: 'POST',
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller:'AdminCedWishSetting',
                action: 'isAuthorisationDone',
                token: admin_token,
                merchant_id:'wish_merchant_validation',
            },
            success: function (json) {
                json = JSON.parse(json);
                try {
                    if (json.redirect) {
                        $("#token_generate_process").removeClass();
                        $("#token_generate_process").addClass('text-success');
                        $("#token_generate_process").html('<p> Token Fetched Successfully.</p>');
                        getCurrencyFromWish();
                        //getWishBrands(false);
                    } else if (json.waiting) {
                        setTimeout(function() {
                            isAuthorisationDone();
                        }, 5000);
                    } else {
                        alert(json['error']);
                    }
                } catch (e) {
                    console.log(e.message);
                }
            }
        });
    }

    function refreshToken() {
        $.ajax({
            type: 'POST',
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishSetting',
                action: 'refreshToken',
                token: admin_token,
                merchant_id: 'wish_merchant_validation',
            },
            success: function (json) {
                try {
                    if (json.success) {
                        alert("Token Refreshed Successfully");
                        location = location.href;
                    } else {
                        alert(json['error']);
                    }
                } catch (e) {
                    console.log(e.message);
                }
            }
        });
    }

    function getWarehouses() {
        $("#warehouse_fetch_process").addClass('text-warning');
        $("#warehouse_fetch_process").html('<p>Fetching Warehouse in Progress......</p>');
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller:'AdminCedWishSetting',
                action: 'getWarehouses',
                token: admin_token,
                merchant_id:'wish_merchant_validation',
            },
            success: function(response) {
                if (response.success) {
                    $("#warehouse_fetch_process").removeClass();
                    $("#warehouse_fetch_process").addClass('text-success');
                    $("#warehouse_fetch_process").html('<p>Warehouse fetched Successfully.</p>');
                    location = location.href;
                }
            },
            statusCode: {
                500: function(xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    if (window.console) console.log(xhr.responseText);
                },
                404: function (response) {
                    if (window.console) console.log(xhr.responseText);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            },
        });
    }

    function getCarriersList() {
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller:'AdminCedWishSetting',
                action: 'getCarriers',
                token: admin_token,
                merchant_id:'wish_merchant_validation',
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location = location.href;
                }
            },
            statusCode: {
                500: function(xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    if (window.console) console.log(xhr.responseText);
                },
                404: function (response) {
                    if (window.console) console.log(xhr.responseText);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            },
        });
    }

    function getColorsList() {
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller:'AdminCedWishSetting',
                action: 'getColors',
                token: admin_token,
                merchant_id:'wish_merchant_validation',
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location = location.href;
                }
            },
            statusCode: {
                500: function(xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    if (window.console) console.log(xhr.responseText);
                },
                404: function (response) {
                    if (window.console) console.log(xhr.responseText);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            },
        });
    }

    function getCurrencyFromWish() {
        $("#currency_fetch_process").addClass('text-warning');
        $("#currency_fetch_process").html('<p>Fetching Currency in Progress......</p>');
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller:'AdminCedWishSetting',
                action: 'getCurrencyFromWish',
                token: admin_token,
                merchant_id:'wish_merchant_validation',
            },
            success: function(response) {
                if (response.success) {
                    $("#currency_fetch_process").removeClass();
                    $("#currency_fetch_process").addClass('text-success');
                    $("#currency_fetch_process").html('<p>Currency fetched Successfully.</p>');
                    getWarehouses();
                }
            },
            statusCode: {
                500: function(xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    if (window.console) console.log(xhr.responseText);
                },
                404: function (response) {
                    if (window.console) console.log(xhr.responseText);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            },
        });
    }

    function getWishBrands(id_min) {
        $("#brand_fetch_process").addClass('text-warning');
        $("#brand_fetch_process").html('<p>Fetching Brands in Progress......</p>');
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller:'AdminCedWishSetting',
                action: 'getWishBrands',
                token: admin_token,
                merchant_id:'wish_merchant_validation',
            },
            success: function(response) {
                var currency_task = $("#currency_task");
                if (response.success) {
                    id_min = id_min+450;
                    currency_task.show();
                    currency_task.html('<p style="color:green;"><pre>' + response.message + '</pre></p>');
                    if(response.id_min)
                        getWishBrands(response.id_min);
                    else {
                        $("#brand_fetch_process").removeClass();
                        $("#brand_fetch_process").addClass('text-success');
                        $("#brand_fetch_process").html('<p>Brands fetched Successfully.</p>');
                    }
                } else {
                    $("#brand_fetch_process").removeClass();
                    $("#brand_fetch_process").addClass('text-success');
                    $("#brand_fetch_process").html('<p>Brands fetched Successfully.</p>');
                }
            },
            statusCode: {
                500: function(xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    if (window.console) console.log(xhr.responseText);
                },
                404: function (response) {
                    if (window.console) console.log(xhr.responseText);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            },
        });
    }
</script>
