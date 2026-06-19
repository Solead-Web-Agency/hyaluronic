/**
 * CREA4YOU CONFIDENTIAL
 * _____________________
 *
 * [2011] - [2019]
 * 
 * @author Youness EL GHAZI <contact@@crea4you.fr> 
 *
 * All Rights Reserved.
 *
 * NOTICE:  All information contained herein is, and remains
 * the property of Crea4You - Youness EL GHAZI and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Crea4You Youness EL GHAZI.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Crea4You Youness EL GHAZI.
 */

function C4Y_prohibitRightClick() {
    document.addEventListener('contextmenu', event => {
        event.preventDefault()
        C4Y_openModal();
    });
}

function C4Y_prohibitDragAndDrop() {
    document.ondragstart = function() {
        C4Y_openModal();
        return false;
    };
}

function C4Y_prohibitSelection() {
    document.onselectstart = function(e) { 
        C4Y_disabledEvent(e);
        C4Y_openModal();
        return false;
    };
}

function C4Y_prohibitCtrlKeys(keys) {
    if (typeof keys === 'undefined' || keys.length <= 0) {
        return;
    }
    document.addEventListener("keydown", function(e) {
        document.onkeydown = function(e) {
            keys.forEach(key => {
                var charcodeLower = parseInt(key.toLowerCase().charCodeAt(0));
                var charcodeUpper = parseInt(key.toUpperCase().charCodeAt(0));
                if (e.ctrlKey && (e.keyCode == parseInt(charcodeLower) || e.keyCode == parseInt(charcodeUpper))) {
                    C4Y_disabledEvent(e);
                    C4Y_openModal();
                }
            });
        }
    }, false);
}

function C4Y_enable_modal(title, message, icon_path, close_label) {
    window.c4y_sp_modal = new tingle.modal({
        footer: true,
        stickyFooter: false,
        closeMethods: ['overlay', 'button', 'escape'],
        closeLabel: "Close",
        cssClass: ['sp-modal'],
    });
    window.c4y_sp_modal.setContent(
        '<img src="'+ icon_path +'" class="forbidden" alt="Forbidden action"/>' +
        '<h1>'+ title +'</h1>' +
        '<p>'+ message +'</p>' 
    );
    window.c4y_sp_modal.addFooterBtn(close_label, 'tingle-btn tingle-btn--warning', function() {
        window.c4y_sp_modal.close();
    });
}

function C4Y_openModal() {
    if (window.c4y_sp_modal && window.c4y_sp_modal.isOpen() === false) {
        window.c4y_sp_modal.open();
    }
}

function C4Y_disabledEvent(e) {
    if (e.stopPropagation) {
      e.stopPropagation();
    } else if (window.event) {
      window.event.cancelBubble = true;
    }
    e.preventDefault();
    return false;
}