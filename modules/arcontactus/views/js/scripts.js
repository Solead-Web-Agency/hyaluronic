/*
* 2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/
var arcuGoTop = false;
window.addEventListener('load', function(){
    if (arcuGoTop){
        document.getElementById('arcu-go-top').addEventListener('click', function(){
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
        window.addEventListener('scroll', function(e){
            arCuScroll();
        });
        arCuScroll();
    }
    
    $('body').on('click', '.arcu-close-btn', function(){
        arCuCloseQRCode();
    });
    $('body').on('click', '#arcu-qr-modal-backdrop', function(){
        arCuCloseQRCode();
    });
});

function arCuScroll(){
    if (pageYOffset > 300) {
        document.getElementById('arcu-go-top').classList.add('active');
    } else {
        document.getElementById('arcu-go-top').classList.remove('active');
    }
}


function arCuGetCookie(cookieName){
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(cookieName + "=");
        if (c_start != -1) {
            c_start = c_start + cookieName.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return 0;
};
function arCuCreateCookie(name, value, days){
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
};
function arCuShowMessage(index){
    if (arCuPromptClosed){
        return false;
    }
    if (typeof arCuMessages[index] !== 'undefined'){
        jQuery('#arcontactus').contactUs('showPromptTyping');

        _arCuTimeOut = setTimeout(function(){
            if (arCuPromptClosed){
                return false;
            }
            jQuery('#arcontactus').contactUs('showPrompt', {
                content: arCuMessages[index]
            });
            index ++;
            _arCuTimeOut = setTimeout(function(){
                if (arCuPromptClosed){
                    return false;
                }
                arCuShowMessage(index);
            }, arCuMessageTime);
        }, arCuTypingTime);
    }else{
        if (arCuCloseLastMessage){
            jQuery('#arcontactus').contactUs('hidePrompt');
        }
        if (arCuLoop){
            arCuShowMessage(0);
        }
    }
};

function arCuShowMessages(){
    setTimeout(function(){
        clearTimeout(_arCuTimeOut);
        arCuShowMessage(0);
    }, arCuDelayFirst);
};

function arCuShowQRCode(data, title){
    arCuBlockUI(false);
    jQuery('#arcontactus').contactUs('closeMenu');
    jQuery.ajax({
        type: 'GET',
        url: arcuOptions.ajaxUrl,
        dataType: 'json',
        data: {
            action : 'getQRCode',
            ajax : true,
            data: data
        },
        success: function(data){
            jQuery('#arcu-qr-modal').remove();
            jQuery('#arcu-qr-modal-backdrop').remove();
            var $modal = $('<div>', {
                id: 'arcu-qr-modal'
            });
            var $backdrop = $('<div>', {
                id: 'arcu-qr-modal-backdrop'
            });
            var $img = $('<img>', {
                src: data.qrcodeFile
            });
            var $title = $('<h4>');
            var $close = $('<button>', {
                class: 'arcu-close-btn',
                type: 'button'
            });
            $title.text(title);
            $modal.append($close);
            $modal.append($title);
            $modal.append($img);
            jQuery('body').append($backdrop);
            jQuery('body').append($modal);
            $img.on('load', function(){
                arCuUnBlockUI(false);
                setTimeout(function(){
                    if (jQuery('#page').length){
                        jQuery('#page').addClass('arcu-blur');
                    } else if(jQuery('body>main').length) {
                        jQuery('body>main').addClass('arcu-blur');
                    }
                    jQuery('#arcu-qr-modal').addClass('active');
                    jQuery('#arcu-qr-modal-backdrop').addClass('active');
                }, 200);
            });
        }
    }).fail(function(){
        arCuUnBlockUI(false);
    });
}

function arCuCloseQRCode(){
    jQuery('#arcu-qr-modal').removeClass('active');
    jQuery('#arcu-qr-modal-backdrop').removeClass('active');
    jQuery('.arcu-blur').removeClass('arcu-blur');
    setTimeout(function(){
        jQuery('#arcu-qr-modal').remove();
        jQuery('#arcu-qr-modal-backdrop').remove();
    }, 400);
}

function arCuBlockUI(selector){
    if (selector){
        $(selector).addClass('ar-blocked');
        $(selector).find('.ar-loading').remove();
        $(selector).append('<div class="ar-loading"><div class="ar-loading-inner">Loading...</div></div>');
    }else{
        $('#ar-static-loader').remove();
        $('body').append('<div id="ar-static-loader"><div class="ar-loading-inner"></div></div>');
    }
};
function arCuUnBlockUI(selector){
    if (selector){
        $(selector).find('.ar-loading').remove();
        $(selector).removeClass('ar-blocked');
    }else{
        $('#ar-static-loader').remove();
    }
};