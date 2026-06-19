/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

var MPTools = {

	waitStart: function () {
		$("body").append("<div class='mp-wait-wrapper'><svg class='circular'><circle class='path' cx='50' cy='50' r='20' fill='none' stroke-width='2' stroke-miterlimit='10'/></svg></div>");
	},

	waitEnd: function () {
		$(".mp-wait-wrapper").remove();
	},

	handleAjaxResponse : function(json, $error_msg_wrapper) {
		var result = true;
		var error_msg = '';

		if (typeof json === 'undefined' || json == null || json == '') return true;

		if (typeof json.meta !== 'undefined') {
			if (typeof json.meta.error !== 'undefined') {
				if (json.meta.error == true) {
					result = false;
					if (typeof json.content !== 'undefined') {
						for (i=0; i<= json.content.length-1; i++) {
							$(json.content[i].dom_element).addClass("error");
							$(json.content[i].dom_element).parent().closest("div").addClass('has-danger');
							error_msg += json.content[i].message + "<br>";
						}
					}
					if (error_msg != '') {
						$error_msg_wrapper.html(error_msg);
						$error_msg_wrapper.show();
					}

				}
			}
		}
		return result;
	},

    /**
     * Merge a url wioth extra param string
     * @param url
     * @param param_string
     * @returns {string}
     */
    joinUrl : function(url, param_string) {
        var return_url = '';

        if (url.indexOf('?') > 0) {
            return_url = url + '&' + param_string;
        } else {
            return_url = url + '?' + param_string;
        }
	   return return_url;
    }
};

