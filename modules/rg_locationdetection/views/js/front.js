/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

$(document).ready(function() {
    if (typeof(rgld_popup) != 'undefined') {
        $.rgldbox.open({
            type: 'html',
            src: rgld_popup,
            keyboard: false,
            afterShow: function() {
                $.get(rgld_path + 'ajax/popup_displayed.php?public_key=' + rgld_public_key);
            }
        });
    }

    if ($('div#rg_infobar').length && typeof(rgld_infobar_position) != 'undefined') {
        if (rgld_infobar_position == 'top') {
            $('div#rg_infobar').prependTo('body').slideDown('normal');
        } else {
            $('div#rg_infobar').appendTo('body').slideDown('normal');
        }

        $('#rg_infobar .close-button a').click(function(e) {
            e.preventDefault();
            $(this).closest('div#rg_infobar').slideUp('normal');
            $.get(rgld_path + 'ajax/dismiss_infobar.php?dismiss=true&public_key=' + rgld_public_key);
        });
    }
});
