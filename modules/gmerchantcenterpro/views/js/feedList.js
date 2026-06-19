/*
 * 2003-2016 Business Tech
 *
 *  @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 *  @copyright 2003-2021 Business Tech SARL
 */
// declare the custom label js object
var GmcProFeedList = function (sName) {

	// set name
	this.name = sName;

	// set name
	this.oldVersion = false;

	// set translated js msgs
	this.msgs = {};

	// stock error array
	this.aError = [];

	// set this in obj context
	var oThis = this;

	/***
	 * dynamicDisplay() method manage dynamic display for feedlist page
	 *
	 */
	this.dynamicDisplay = function () {

		$('.js-copy').click(function() {
			var text = $(this).attr('data-copy');
			var el = $(this);
			oThis.copyToClipboard(text, el);
		});

		$("#btn-xml-product").click(function() {
			$(".bt-fb-cron-product").slideDown();
			$(".bt-fb-fly-product").slideUp();
			$(".xml-product").css('border','#72C279 2px solid');
			$(".xml-fly").css('border','#CCCED7 2px solid');
			$(".icon-active-cog").css('background','#72C279');
			$(".icon-active-cog").addClass('fa-spin');
			$(".icon-active-file").css('background','#434955');
			$(".icon-active-file").removeClass('pulse pulse_infnite');
			$("#btn-xml-product").css('text-decoration','underline');
			$("#btn-xml-product").css('background-color','#60ba68');
			$("#btn-fly-product").css('text-decoration','none');
			$("#btn-fly-product").css('background-color','#72C279');

			document.cookie = "sDisplayExport=xml-product";

		});

		$("#btn-fly-product").click(function() {

			$(".bt-fb-cron-product").slideUp();
			$(".bt-fb-fly-product").slideDown();
			$(".xml-product").css('border','#CCCED7 2px solid');
			$(".xml-fly").css('border','#72C279 2px solid');
			$(".icon-active-cog").css('background','#434955');
			$(".icon-active-cog").removeClass('fa-spin');
			$(".icon-active-file").css('background','#72C279');
			$(".icon-active-file").addClass('pulse pulse_infnite');
			$("#btn-xml-product").css('text-decoration','none');
			$("#btn-xml-product").css('background-color','#72C279');
			$("#btn-fly-product").css('text-decoration','underline');
			$("#btn-fly-product").css('background-color','#60ba68');
			document.cookie = "sDisplayExport=fly-product";
		});

		$(document).ready(function() {

			var sModeDisplay = oThis.getCookieValue("sDisplayExport");

			if ( sModeDisplay == 'xml-product') {
				$(".bt-fb-cron-product").slideDown();
				$(".bt-fb-fly-product").slideUp();
				$(".xml-product").css('border','#72C279 2px solid');
				$(".xml-fly").css('border','#CCCED7 2px solid');
				$(".icon-active-cog").css('background','#72C279');
				$(".icon-active-cog").addClass('fa-spin');
				$(".icon-active-file").css('background','#434955');
				$("#btn-xml-product").css('text-decoration','underline');
				$("#btn-xml-product").css('background-color','#60ba68');
				$("#btn-fly-product").css('text-decoration','none');
				$("#btn-fly-product").css('background-color','#72C279');
			}
			else if ( sModeDisplay == 'fly-product') {
				$(".bt-fb-cron-product").slideUp();
				$(".bt-fb-fly-product").slideDown();
				$(".xml-product").css('border','#CCCED7 2px solid');
				$(".icon-active-cog").removeClass('fa-spin');
				$(".xml-fly").css('border','#72C279 2px solid');
				$(".icon-active-cog").css('background','#434955');
				$(".icon-active-file").css('background','#72C279');
				$("#btn-xml-product").css('text-decoration','none');
				$("#btn-xml-product").css('background-color','#72C279');
				$("#btn-fly-product").css('text-decoration','underline');
				$("#btn-fly-product").css('background-color','#60ba68')
			}
			else {
				$(".bt-fb-cron-product").slideUp();
				$(".bt-fb-fly-product").slideUp();
				$(".xml-product").css('border','#CCCED7 2px solid');
				$(".xml-fly").css('border','#CCCED7 2px solid');
				$(".icon-active-cog").css('background','#72C279');
				$(".icon-active-file").css('background','#72C279');
				$("#btn-xml-product").css('text-decoration','none');
				$("#btn-xml-product").css('background-color','#60ba68');
				$("#btn-fly-product").css('text-decoration','none');
				$("#btn-fly-product").css('background-color','#60ba68')
			}
		});
	};

	/***
	 * copyToClipboard() manage the copy to clipboard
	 *
	 * @string sText
	 * @string el
	 */
	this.copyToClipboard = function (text, el) {

		var copyTest = document.queryCommandSupported('copy');
		var elOriginalText = el.attr('data-original-title');

		if (copyTest === true) {
			var copyTextArea = document.createElement("textarea");
			copyTextArea.value = text;
			document.body.appendChild(copyTextArea);
			copyTextArea.select();
			try {
				var successful = document.execCommand('copy');
				var msg = successful ? 'Copied!' : 'Whoops, not copied!';
				el.attr('data-original-title', msg).tooltip('show');
			} catch (err) {
				console.log('Oops, unable to copy');
			}
			document.body.removeChild(copyTextArea);
			el.attr('data-original-title', elOriginalText);
		} else {
			// Fallback if browser doesn't support .execCommand('copy')
			window.prompt("Copy to clipboard: Ctrl+C or Command+C, Enter", text);
		}
	};
	/***
	 * getCookieValue() manage the cookie value
	 *
	 * @string cname
	 */
	this.getCookieValue = function (cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for(var i=0; i<ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1);
			if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
		}
		return "";
	};



};