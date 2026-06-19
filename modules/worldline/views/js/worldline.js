/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
**/
var p = function () {
    console.log(arguments);
};

// Main Function
var Main = function () {
    // function to custom select
    var runCustomElement = function () {

        // check submit
        var is_submit = $("#modulecontent").attr('role');
        if (is_submit === 1) {
            $(".list-group-item").each(function() {
                if ($(this).hasClass('active')) {
                    $(this).removeClass("active");
                }
                else if ($(this).attr('href') === "#config") {
                    $(this).addClass("active");
                }
            });
            $('#config').addClass("active");
            $('#documentation').removeClass("active");
        }

        // toggle panel
        $(".list-group-item").on('click', function() {
            var $el = $(this).parent().closest(".list-group").children(".active");
            if ($el.hasClass("active")) {
                $el.removeClass("active");
                $(this).addClass("active");
            }
        });

        // Hide ugly toolbar
        $('table[class="table"]').each(function(){
            $(this).hide();
            $(this).next('div.clear').hide();
        });

        // Hide ugly multishop select
        if (typeof(_PS_VERSION_) !== 'undefined') {
            var version = _PS_VERSION_.substr(0,3);
            if(version === '1.5') {
                $('.multishop_toolbar').addClass("panel panel-default");
                $('.shopList').removeClass("chzn-done").removeAttr("id").css("display", "block").next().remove();
                cloneMulti = $(".multishop_toolbar").clone(true, true);
                $(".multishop_toolbar").first().remove();
                cloneMulti.find('.shopList').addClass('selectpicker show-menu-arrow').attr('data-live-search', 'true');
                cloneMulti.insertBefore("#modulecontent");
                // Copy checkbox for multishop
                cloneActiveShop = $.trim($('table[class="table"] tr:nth-child(2) th').first().html());
                $(cloneActiveShop).insertAfter("#tab_translation");
            }
        }

        // Fix bug form builder + bootstrap select
        var z = 1;
        $('.selectpicker').each(function(){
            var select = $(this);
            select.on('click', function() {
                $(this).parents('.bootstrap-select').addClass('open');
                $(this).parents('.bootstrap-select').toggleClass('open');
            });
        });
    };
    return {
        //main function to initiate template pages
        init: function () {
            runCustomElement();
        }
    };
}();

$(function() {
    // Load functions
    Main.init();
    $("#production").click(function(){
        $(".atos_mode_only").removeAttr('disabled');
        $("#2").show('fast');
        $("#3").show('fast');
    });
    $("#test_mode").click(function(){
        $(".atos_mode_only").attr('disabled','disabled');
        $("#2").hide('fast');
        $("#3").hide('fast');
    });

    $("#2xOn").click(function(){
        $("#2x_from").removeAttr('disabled');
    });
    $("#2xOff").click(function(){
        $("#2x_from").attr('disabled','disabled');
    });
    $("#3xOn").click(function(){
        $("#3x_from").removeAttr('disabled');
    });
    $("#3xOff").click(function(){
        $("#3x_from").attr('disabled','disabled');
    });
    $("#4xOn").click(function(){
        $("#4x_from").removeAttr('disabled');
    });
    $("#4xOff").click(function(){
        $("#4x_from").attr('disabled','disabled');
    });

    $('input[type="text"]').keyup(function(){
        $(this).css('border', '1px solid green');
    });
    $('input[type="text"]').blur(function(){
        $(this).css('border', '1px solid #CCCCCC');
    });
});

function isInt(val){
    if(parseInt(val)!==val) return false;
    return true;
}
