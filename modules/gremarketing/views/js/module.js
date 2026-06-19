/*
 * 2003-2019 Business Tech
 *
 *  @author    Business Tech SARL <https://addons.prestashop.com/en/6_business-tech>
 *  @copyright 2003-2019 Business Tech SARL
 */

// declare main object of module
var GrModule = function(sName){
	// set name
	this.name = sName;

	// set name
	this.oldVersion = false;

	// set translated js msgs
	this.msgs = {};

	// stock error array
	this.aError = [];

	// set url of admin img
	this.sImgUrl = '';

	// set url of module's web service
	this.sWebService = '';

	// set this in obj context
	var oThis = this;

	/**
	 * show() method show effect and assign HTML in
	 *
	 * @param string sId : container to show in
	 * @param string sHtml : HTML to display
	 */
	this.show = function(sId, sHtml){
		$("#" + sId).html(sHtml).css('style', 'none');
		$("#" + sId).show('fast');
	};

	/**
	 * hide() method hide effect and delete html
	 *
	 * @param string sId : container to hide in
	 */
	this.hide = function(sId, bOnlyHide){
		$('#' + sId).hide('fast');
		if (bOnlyHide == null) {
			$('#' + sId).empty();
		}
		//$("#" + sId).hide('fast', function(){
		//		$("#" + sId).html('');
		//	}
		//);
	};

	/**
	 * form() method check all fields of current form and execute : XHR or submit => used for update all admin config
	 *
	 * @see ajax
	 * @param string sForm : form
	 * @param string sURI : query params used for XHR
	 * @param string sRequestParam : param action and type in order to send with post mode
	 * @param string sToDisplay :
	 * @param string sToHide : force to hide specific ID
	 * @param bool bSubmit : used only for sending main form
	 * @param bool bFancyBox : used only for fancybox in xhr
	 * @param string oCallBack : list of objects of callback to execute as ajax request
	 * @param string sErrorType :
	 * @param string sLoadBar :
	 * @param string sScrollTo :
	 * @return string : HTML returned by smarty
	 */
	this.form = function(sForm, sURI, sRequestParam, sToDisplay, sToHide, bSubmit, bFancyBox, oCallBack, sErrorType, sLoadBar, sScrollTo) {
		// set loading bar
		if (sLoadBar) {
			$('#bt_loading-div-'+sLoadBar).show();
		}

		// set return validation
		var aError = [];

		// get all fields of form
		var fields = $("#" + sForm).serializeArray();

		// set counter
		var iCounter = 0;

		// set bIsError
		var bIsError = false;

		// set check review
		var bCheckReview = false;
		var bCheckedReview = false;

		// check element form
		jQuery.each(fields, function(i, field) {
			bIsError = false;

			switch(field.name) {
				default:
					break;
			}

			if (($('input[name="' + field.name + '"]') != undefined
				|| $('textarea[name="' + field.name + '"]') != undefined
				|| $('select[name="' + field.name + '"]').length != undefined)
				&& bIsError == true
			) {
				if ($('input[name="' + field.name + '"]').length != 0) {
					$('input[name="' + field.name + '"]').parent().addClass('has-error has-feedback');
					$('input[name="' + field.name + '"]').append('<span class="icon-remove-sign"></span>');
				}
				if ($('textarea[name="' + field.name + '"]').length != 0) {
					$('textarea[name="' + field.name + '"]').parent().addClass('has-error has-feedback');
					$('textarea[name="' + field.name + '"]').append('<span class="icon-remove-sign"></span>');
				}
				if ($('select[name="' + field.name + '"]').length != 0) {
					$('select[name="' + field.name + '"]').parent().addClass('has-error has-feedback');
					$('select[name="' + field.name + '"]').append('<span class="icon-remove-sign"></span>');
				}
				++iCounter;
			}
		});

		// use case - no errors in form
		if (oThis.aError.length == 0) {
			// use case - Ajax request
			if (bSubmit == undefined || bSubmit == null || !bSubmit) {
				if (sLoadBar && sToHide != null) {
					oThis.hide(sToHide, true);
				}

				// format object of fields in string to execute Ajax request
				var sFormParams = $.param(fields);

				if (sRequestParam != null && sRequestParam != '') {
					sFormParams = sRequestParam + '&' + sFormParams;
				}

				// execute Ajax request
				this.ajax(sURI, sFormParams, sToDisplay, sToHide, bFancyBox, null, sLoadBar, sScrollTo, oCallBack);

				return true;
			}
			// use case - send form
			else {
				// hide loading bar
				if (sLoadBar) {
					$('#bt_loading-div-'+sLoadBar).hide();
				}
				document.forms[sForm].submit();
				return true;
			}
		}
		// display errors
		this.displayError(sErrorType);

		// set loading bar
		if (sLoadBar) {
			('#bt_loading-div-'+sLoadBar).hide();
		}

		return false;
	};


	/**
	 * ajax() method execute XHR
	 *
	 * @param string sURI : query params used for XHR
	 * @param string sParams :
	 * @param string sToShow :
	 * @param string sToHide :
	 * @param bool bFancyBox : used only for fancybox in xhr
	 * @param bool bFancyBoxActivity : used only for fancybox in xhr
	 * @param string sLoadBar : used only for loading
	 * @param string sScrollTo : used only for scrolling
	 * @param obj oCallBack : used only for callback to execute as ajax request
	 * @return string : HTML returned by smarty
	 */
	this.ajax = function(sURI, sParams, sToShow, sToHide, bFancyBox, bFancyBoxActivity, sLoadBar, sScrollTo, oCallBack) {

		if (bFancyBox && bFancyBoxActivity != false) {
			$.fancybox.showActivity();
		}

		sParams = 'sMode=xhr' + ((sParams == null || sParams == undefined) ? '' : '&' + sParams);

		// configure XHR
		$.ajax({
			type : 'POST',
			url : sURI,
			data : sParams,
			dataType : 'html',
			success: function(data) {
				// hide loading bar
				if (sLoadBar) {
					$('#bt_loading-div-'+sLoadBar).hide();
				}

				if (bFancyBox) {
					// update fancybox content
					$.fancybox(data);
				}
				else if (sToShow != null && sToHide != null) {
					// same hide and show
					if (sToShow == sToHide) {
						oThis.hide(sToHide);
						setTimeout('', 1000);
						oThis.show(sToShow, data);
					}
					else {
						oThis.hide(sToHide);
						setTimeout('', 1000);
						oThis.show(sToShow, data);
					}
				}
				else if (sToShow != null) {
					oThis.show(sToShow, data);
				}
				else if (sToHide != null) {
					oThis.hide(sToHide);
				}

				if (sScrollTo !== null && typeof sScrollTo !== 'undefined') {
					var iPosTop = $(sScrollTo).offset().top-30;
					if (iPosTop < 0) iPosTop = 0;
					$(document).scrollTop(iPosTop);
				}

				// execute others ajax request if needed. In this case, we can update any other tab from the module in the same time
				if (oCallBack != null && oCallBack.length != 0) {
					for (var fx in oCallBack) {
						oThis.ajax(oCallBack[fx].url, oCallBack[fx].params, oCallBack[fx].toShow, oCallBack[fx].toHide, oCallBack[fx].bFancybox, oCallBack[fx].bFancyboxActivity, oCallBack[fx].sLoadbar, oCallBack[fx].sScrollTo , oCallBack[fx].oCallBack);
					}
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				$("#" + oThis.name + "FormError").addClass('bt_alert bt_alert-error');
				oThis.show("#" + oThis.name + "FormError", '<h3>internal error</h3>');
			}
		});
	};

	/**
	 * displayError() method display errors
	 *
	 * @param string sType : type of container
	 * @return bool
	 */
	this.displayError = function(sType) {
		if (oThis.aError.length != 0) {
			var sError = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><ul class="list-unstyled">';
			for (var i = 0; i < oThis.aError.length;++i) {
				sError += '<li>' + oThis.aError[i] + '</li>';
			}
			sError += '</ul></div>';
			$("#bt_error-" + sType).html(sError);
			$("#bt_error-" + sType).slideDown();

			// flush errors
			oThis.aError = [];

			return false;
		}
	};

	/**
	 * changeSelect() method displays or hide related option form
	 *
	 * @param string sId : type of container
	 * @param mixed mDestId
	 * @param string sDestId2
	 * @param string sType of second dest id
	 * @param bool bForce
	 * @param bool mVal
	 */
	this.changeSelect = function(sId, mDestId, sDestId2, sDestIdToHide, bForce, mVal) {
		if (bForce) {
			if (typeof mDestId == 'string') {
				mDestId = [mDestId];
			}

			for (var i = 0; i < mDestId.length; ++i) {
				if (mVal) {
					$("#" + mDestId[i]).fadeIn('fast', function() {$("#" + mDestId[i]).css('display', 'block')});
				}
				else {
					$("#" + mDestId[i]).fadeOut('fast');
				}
			}
		}
		else {
			$("#" + sId).bind('change', function (event){
				$("#" + sId + " input:checked").each(function (){
					switch ($(this).val()) {
						case 'true' :
							// display option features
							$("#" + sDestId).fadeIn('fast', function() {$("#" + sDestId).css('display', 'block')});
							break;
						default:
							// hide option features
							$("#" + sDestId).fadeOut('fast');

							// set to false
							if (sDestId2 && sDestIdToHide) {
								$("#" + sDestId2 + " input").each(function (){
										switch ($(this).val()) {
											case 'false' :
												$(this).attr('checked', 'checked');
												// hide option features
												$("#" + sDestIdToHide).fadeOut('fast');
												break;
											default:
												$(this).attr('checked', '');
												break;
										}
									}
								);
							}
							break;
					}
				});
			});
		}
	};

	/**
	 * selectAll() method select / deselect all checkbox
	 *
	 * @param string sId : type of container
	 * @param string sCible : all checkbox to process
	 * @return
	 */
	this.selectAll = function(sId, sCible){
		$("#" + sId).bind('change', function (event){
			if (this.checked == true) {
				$(sCible).attr('checked', true);
			}
			else{
				$(sCible).attr('checked', false);
			}
		});
	};
};