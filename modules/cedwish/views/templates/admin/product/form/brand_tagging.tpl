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
{if $brand_count}
    <div class="panel">
        <h3><i class="icon-tag"></i> {l s='Wish Brands in item(s)' mod='cedwish'}</h3>
        <div class="row" style="padding: 2%;">
            <div>
                <input
                        type="text"
                        id="brand_name"
                        name="brand_name"
                        class="form-control"
                        placeholder="{l s='Type Here to Search Brand..' mod='cedwish'}"
                />
                <input
                        type="hidden"
                        id="wish_brand_id"
                        name="wish_brand_id"
                        class="form-control"
                />
            </div>
        </div>
        <div class="row" style="padding: 2%;">
            <div>
                <div class="buttons">
                    <button
                            id="wishBulkUploadProduct"
                            data-token="{$token|escape:'htmlall':'UTF-8'}"
                            class="btn btn-primary"
                            onclick="processReport();"
                    >
                        {l s='Process Upload/Update Products' mod="cedwish"}
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="myModal" class="modal">
                <div class="modal-content">
                    <span class="close" id="close_model">&times;</span>
                    <div id="popup_content_loader">
                        <p>
                            {l s='Please wait processing Product........' mod='cedwish'}
                        </p>
                        <p>
                            {l s='This will take time as Product count is large' mod='cedwish'}
                        </p>
                    </div>
                </div>
            </div>
            <ol id="progress" style="display: initial;">
            </ol>
        </div>
    </div>
    <script type="text/javascript">
        var modal = document.getElementById('myModal');
        var span = document.getElementById("close_model");
        span.onclick = function() {
            modal.style.display = "none";
        }
    </script>
{else}
    <div class="panel">
        <p>{l s="You need to fetch Brands First." mod='cedwish'}</p>
        <ul id="progress"></ul>
        <button
                class="btn-primary btn"
                onclick="getWishBrand('')"
                id="get_brand_button"
                type="button"
                data-token="{$token|escape:'htmlall':'UTF-8'}"
        >
            {l s="Get Brands." mod='cedwish'}
        </button>
    </div>
{/if}
<script type="text/javascript">
    function getWishBrand(id_min){
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishBulk',
                action: 'getBrand',
                id_min: id_min,
                token: $('#get_brand_button').attr('data-token')
            },
            success: function(response){
                response = JSON.parse(response);
                if(response.id){
                    $("#progress").append('<li class="alert alert-success" >500 more Brands fetched</li>');
                    getWishBrand(response.id);
                } else if(response.success){
                    $("#progress").append('<li class="alert alert-success" >'+response.message+'</li>');
                    location = location.href;
                } else {
                    $("#progress").append('<li class="alert alert-danger">'+response.message+'</li>');
                }
            },
            statusCode: {
                500: function(xhr) {
                    if(window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    $("#progress").append('<span style="color:Red;">Error While Getting Brands </span>');
                },
                404: function (response) {

                    $("#progress").append('<span style="color:Red;">Error While Getting Brands</span>');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if(window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

            },
        });
    }
    $('#brand_name').autocomplete('{$controllerUrl}', {
            minChars: 3,
            max: 15,
            width: 250,
            selectFirst: false,
            scroll: true,
            dataType: "json",
            formatItem: function (data, i, max, value, term) {
                return value;
            },
            parse: function (data) {
                var mytab = new Array();
                for (var i = 0; i < data.length; i++)
                    mytab[mytab.length] = { value: data[i].name, data:data[i].id};
                return mytab;
            },
            extraParams: {
                ajax: "1",
                token: $('#wishBulkUploadProduct').attr('data-token'),
                tab: "AdminCedWishBulk",
                action: "brandTagging",
                dataType: "json"
            }
        }
    ).result(function(event, value, label) {
        $('#brand_name').val(label);
        $('#wish_brand_id').val(value);
    });

    var report_data = '{$brand_tagging|escape:'mail':'UTF-8'}';
    var chunklimit = 1000;
    report_data = JSON.parse(report_data);
    var chunk_array=[];
    $.each(report_data, function(key ,value){
        chunk_array.push(value);
    });
    chunked_array =[];
    while(chunk_array.length){
        chunked_array.push(chunk_array.splice(0,chunklimit));
    }
    chunked_array.reverse();
    function processReport() {
        var url = '{$controllerUrl|escape:'htmlall':'UTF-8'}';
        var clen=chunked_array.length;
        if(clen){
            modal.style.display = "block";
            sendUpdateRequest(chunked_array,url+'&is_ajax=1');
        }
        else
            $("#progress").append('<li class="alert alert-info" > No Report Recieved for Upload/Update Products. </li>');
    }

    function sendUpdateRequest(chunked_array,url){
        var len=chunked_array.length-1;
        if(chunked_array[len]){
            $.ajax({
                type: "POST",
                url: 'ajax-tab.php',
                data: {
                    ajax: true,
                    controller: 'AdminCedWishBulk',
                    action: 'bulkBrandTagging',
                    wish_brand_id: $("#wish_brand_id").val(),
                    brand_name: $("#brand_name").val(),
                    token: $('#wishBulkUploadProduct').attr('data-token'),
                    selected:chunked_array[len]
                },
                success: function(response){
                    response = JSON.parse(response);
                    if(response.success){
                        var obj = response;
                        if (obj.message) {
                            $("#progress").append('<li class="alert alert-success" >'+obj.message+'</li>');
                        }
                        if (!obj.success && obj.message) {
                            $("#progress").append('<li class="alert alert-danger" >'+obj.message+'</li>');
                        }
                        if(len!=0){
                            chunked_array.splice(len,1);
                            sendUpdateRequest(chunked_array,url);
                        }
                    } else {
                        $("#progress").append('<li class="alert alert-danger">Error While Uploading/Updating Products Please Check</li>');
                    }
                    if (len==0) {
                        modal.style.display = "none";
                    }
                }
                ,
                statusCode: {
                    500: function(xhr) {
                        if(window.console) console.log(xhr.responseText);
                    },
                    400: function (response) {
                        $("#progress").append('<span style="color:Red;">Error While Uploading/Updating Products Please Check</span>');
                    },
                    404: function (response) {

                        $("#progress").append('<span style="color:Red;">Error While Uploading/Updating Products Please Check</span>');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if(window.console) console.log(xhr.responseText);
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

                },
            });

        }else {
            $("#progress").append('<li class="alert alert-info" > NO Report.</li>');
            modal.style.display = "none";
        }
    }
</script>
<style type="text/css">
    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content/Box */
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 20%; /* Could be more or less, depending on screen size */
    }

    /* The Close Button */
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>
