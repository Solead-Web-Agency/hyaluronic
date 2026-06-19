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
{if isset($feedData)}
<a class="btn btn-success btn-xs"
   onclick="viewResponse('{$feedData['job_id']|escape:'javascript':'UTF-8'}')">
    <i class="process-icon-preview"></i>
</a>
<div id="myModal" class="modal">
    <div class="modal-content">
        <div class="row" style="padding-bottom: 10px;color: black;"><span class="close" id="close_m">X</span></div>
        <div id="popup_content_feed" style="">
        </div>
    </div>
</div>
{/if}
<script type="text/javascript">
    var modal = document.getElementById('myModal');
    var span = document.getElementById('close_m');
    span.onclick = function() {
        modal.style.display = "none";
    }
    function viewResponse(feedId) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedWishFeed',
                action: 'getFeedInfo',
                token: '{$wish_feed_token|escape:'javascript':'UTF-8'}',
                feedId: feedId
            },
        })
            .success(function (data) {
                var failedSkus = data.response;
                document.getElementById('myModal').style.display = "block";
                var html = '';
                if (data.id_cedwish_feed) {
                    html += '<div class="table-responsive">';
                    html += '<table class="table">';
                    html += '<thead><th>Sr. No.</th><th>Product ID</th><th>ERROR/SUCCESS</th></thead>';
                    if (failedSkus.length > 0) {
                        for (var i in failedSkus) {
                            if (typeof failedSkus[i].error_message == 'undefined') {
                                html += '<tr class="text-success"><td class="text-left">'+(parseInt(i)+1)+'</td><td class="text-left">'+failedSkus[i].product_id+'</td><td class="text-left text-success"> Product Updated Successfully </td></tr>';
                            } else {
                                html += '<tr class="text-danger"><td class="text-left">'+(parseInt(i)+1)+'</td><td class="text-left">'+failedSkus[i].product_id+'</td><td class="text-left text-danger">'+failedSkus[i].error_message+'</td></tr>';
                            }
                        }
                    }
                    html += '</table>';
                    html += '</div>';
                    $("#popup_content_feed").html(html);

                }
            })
            .fail(function () {
                wishModal.style.display = "none";
                $("#success-message").hide();
                $("#error-message").show();
                document.getElementById("default-error-message-text").innerHTML = "Something went Wrong. Please try again later";
            });
    }
    function close() {
        document.getElementById('myModal').style.display = "none";
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
        width: 60%; /* Could be more or less, depending on screen size */
    }

    /* The Close Button */
    .close {
        color: #000;
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
