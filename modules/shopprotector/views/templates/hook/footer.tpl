{**
 * CREA4YOU CONFIDENTIAL
 * _____________________
 *
 * [2011] - [2019] Crea4You Youness EL GHAZI
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
 *}

 <script type="text/javascript">
    window.onload = function () {
        // Disable Right Click
        if ({$sp_disable_right_click}) {
            C4Y_prohibitRightClick();
        }
        // Disable Selection
        if ({$sp_disable_selection}) {
            C4Y_prohibitSelection();
        }
        // Disable Drag & Drop
        if ({$sp_disable_drag_drop}) {
           C4Y_prohibitDragAndDrop();
        }
        // Enable modal window
        if ({$sp_show_modal}) {
            C4Y_enable_modal("{$sp_modal_title}", "{$sp_modal_message}",
                            "{$module_path}/views/img/forbidden.png", "{l s='Close' mod='shopprotector'}")
        }
        
        // Forbidden keys
        var forbidden_keys = {$sp_forbidden_keys|json_encode nofilter}
        C4Y_prohibitCtrlKeys(forbidden_keys);
    }
 </script>
 