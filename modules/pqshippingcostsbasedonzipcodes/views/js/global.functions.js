/**
 * ProQuality (c) All rights reserved.
 *
 * DISCLAIMER
 *
 * Do not edit, modify or copy this file.
 * If you wish to customize it, contact us at addons4prestashop@gmail.com.
 *
 * @author    Andrei Cimpean (ProQuality) <addons4prestashop@gmail.com>
 * @copyright 2015-2016 ProQuality
 * @license   Do not edit, modify or copy this file
 */
var scboz_request, scboz_requests = [];
/*new ajax controller function, better than the old one; v2.2*/
function scbozAjaxController(params, callback, abort_active_requests)
{
	/*
params.load*           = type: string (required)
                       = desc: php switch to load

params.divs*           = type: object (required)
                       = desc: object of divs to be populated

params.params*         = type: object (required)
                       = desc: object of vars to be sent to the php switch

params.preloader       = type: object (optional)
                       = desc: object of preloader proprietes
(
	params.preloader.divs  = type: object (required if preloader active)
	                       = desc: object of divs to populate the preloader

	params.preloader.type  = type: string (optional: 1 ; 2)
	                       = desc: type of preloader (when preloader is in the same div as the response ; when preloader is in the other div as the response)

	params.preloader.style = type: string (optional: 1 ; 2)
	                       = desc: style of preloader (style of animated image)
)

params.append          = type: boolean (true|false) (optional)
                       = desc: if is set to true it will append instead of replace content of divs

params.complete          = type: string (optional)
                       = desc: if is set to "loop" then it loops trough this function again (used to comet push)

callback               = type: callback function (optional)
					   = desc: function to put the ajax result

abort_active_requests  = type: boolean (true|false) (optional)
					   = desc: if is set to true it will abort all current ajax requests runned trough this function
*/
	/*verifying null args*/
	params.divs = typeof(params.divs) != 'undefined' ? params.divs : null;
	params.preloader = typeof(params.preloader) != 'undefined' ? params.preloader : null;
	callback = typeof(callback) != 'undefined' ? callback : null;
	abort_active_requests = typeof(abort_active_requests) != 'undefined' ? abort_active_requests : null;
	var timestamp = Math.round(new Date().getTime() / 1000);
	/*controller and params*/
	var load_url = scboz_url + 'ajax.php?time=' + timestamp;
	/*extending the params*/
	params.params = $.extend(
	{
		'type': params.load,
		'token': scboz_token
	}, params.params);
	/*preloader start*/
	if (params.preloader && params.preloader.divs != null)
	{
		preload(params.preloader.divs, 'on', params.preloader.style);
	}
	/*delete active requests*/
	if (abort_active_requests != null) abortActiveRequests();
	scboz_request = $.ajax(
	{
		url: load_url,
		type: 'POST',
		async: false,
		data: params.params,
		success: function(result)
		{
			if (params.divs)
			{
				if (isJson(result))
				{
					var res = JSON.parse(result);
					if (typeof(res.response) != 'undefined')
					{
						if (res.success === true)
						{
							$.each(params.divs, function(k, v)
							{
								if (params.append == true) $('#' + v).append(res.response);
								else $('#' + v).html(res.response);
							});
						}
					}
					else
					{
						$.each(params.divs, function(k, v)
						{
							if (params.append == true) $('#' + v).append(result);
							else $('#' + v).html(result);
						});
					}
				}
				else
				{
					$.each(params.divs, function(k, v)
					{
						if (params.append == true) $('#' + v).append(result);
						else $('#' + v).html(result);
					});
				}
			}
			/*preloader end*/
			if (params.preloader && params.preloader.type == 2)
			{
				preload(params.preloader.divs, 'off', params.preloader.style);
			}
			/*callback*/
			if (typeof(callback) === "function") callback(result);
			if (params.complete == 'loop')
			{
				/*console.log('am ajuns in (if loop) : '+params.params.timestamp);*/
				if (result)
				{
					try
					{
						var res = JSON.parse(result);
						params.params.timestamp = res.timestamp;
						/*params.params.action = res.action;
							    console.log('am ajuns in (if result) : '+params.params.timestamp);*/
					}
					catch (e)
					{
						/*JSON parse error, this is not json (or JSON isn't in your browser)*/
					}
				}
				else
				{
					/*console.log('am ajuns in (if NOT result) : '+params.params.timestamp);*/
					params.params.data = '';
					/*params.params.timestamp = 0;*/
				}
			}
			return true;
		},
		complete: function()
		{
			if (params.complete == 'loop')
			{
				scbozAjaxController(params, callback);
				/*params.complete = '';*/
			}
			return true;
		},
		error: function()
		{
			if (params.complete == 'loop')
			{
				/*if(typeof(callback) === "function") callback(result);
		          	 	scbozAjaxController(params, callback);*/
			}
			return false;
		},
	});
	scboz_requests.push(scboz_request);
}

function preload(divs, action, style)
{
	if (style == 1)
	{
		var html = '<div style="height: 100%; width: 100%;" align="center"><img border="0" src="' + scboz_path + 'views/img/ajax-loader.gif"></div>';
	}
	else if (style == 2)
	{
		var html = '<table cellpadding="0" cellspacing="0"><tr><td>Loading...&nbsp;&nbsp;</td><td><img border="0" src="' + scboz_path + 'views/img/ajax-loader.gif"></td></tr></table>';
	}
	else if (style == 3)
	{
		var html = '<img border="0" src="' + scboz_path + 'views/img/ajax-loader.gif">';
	}
	if (action == 'on')
	{
		$.each(divs, function(k, v)
		{
			$('#' + v).html(html);
		});
	}
	else
	{
		$.each(divs, function(k, v)
		{
			$('#' + v).html('');
		});
	}
}

function abortActiveRequests()
{
	//req = typeof(req) != 'undefined' ? req : null;
	for (i = 0; i < scboz_requests.length; i++)
	{
		scboz_requests[i].abort();
	}
	scboz_requests = [];
}

function isJson(str)
{
	try
	{
		JSON.parse(str);
	}
	catch (e)
	{
		return false;
	}
	return true;
}

function l_scboz(name, prefix)
{
	prefix = typeof(prefix) != 'undefined' ? prefix : 'scboz';	

	if (LANG[prefix].hasOwnProperty(name)) 
		return LANG[prefix][name];
	else
	{
		console.warn('Js translation ' + name + ' do not exists.')
		return name;
	}
}

function updateTips(t)
{
	var tips = $(".validate-tips");
	tips.text(t).addClass("ui-state-highlight");
	setTimeout(function()
	{
		tips.removeClass("ui-state-highlight", 1500);
	}, 500);
}

function checkLength(o, n, min, max)
{
	if (o.val().length > max || o.val().length < min)
	{
		o.addClass("ui-state-error");
		updateTips("Length of " + n + " must be between " + min + " and " + max + ".");
		return false;
	}
	else
	{
		return true;
	}
}

function checkRegexp(o, regexp, n)
{
	if (!(regexp.test(o.val())))
	{
		o.addClass("ui-state-error");
		updateTips(n);
		return false;
	}
	else
	{
		return true;
	}
}

function scbozAddToolbarBtn(type)
{
	if (type == 'video') $('div.btn-toolbar > ul').prepend('<li><a id="desc-module-video" class="scboz toolbar_btn" title="Module Video Tutorial" href="' + scboz_video_link + '" target="_blank"><i style="margin-top: 2px !important;" class="fa fa-youtube fa-2x"></i><div style="margin-top: 4px !important;">Module Video</div></a></li>');
	if (type == 'documentation') $('div.btn-toolbar > ul').prepend('<li><a id="desc-module-documentation" class="scboz toolbar_btn" title="Module Documentation" href="' + scboz_doc_link + '" target="_blank"><i style="margin-top: 2px !important;" class="fa fa-life-ring fa-2x"></i><div style="margin-top: 4px !important;">Documentation</div></a></li>');
	if (type == 'contact') $('div.btn-toolbar > ul').prepend('<li><a id="desc-module-contact" class="scboz toolbar_btn" title="Contact Developer" href="' + scboz_support_link + '" target="_blank"><i style="margin-top: 2px !important;" class="fa fa-code fa-2x"></i><div style="margin-top: 4px !important;">Contact Dev. (suggestions)</div></a></li>');
	if (type == 'modules') $('div.btn-toolbar > ul').prepend('<li><a id="desc-module-modules" class="scboz toolbar_btn" title="Modules by this developer" href="' + scboz_dev_modules_link + '" target="_blank"><i style="margin-top: 2px !important;" class="fa fa-puzzle-piece fa-2x"></i><div style="margin-top: 4px !important;">Dev. Modules</div></a></li>');
	if (type == 'separator') $('div.btn-toolbar > ul').prepend('<li style="line-height: 40px; vertical-align: middle; color: #222222 !important; opacity: 0.3; filter: alpha(opacity=30);">|</li>');
}

function validateInput(text)
{
	if (/[^a-zA-Z0-9]/.test(text))
	{
		return false;
	}
	return true;
}

function ucfirst(string)
{
	return string.charAt(0).toUpperCase() + string.slice(1);
}

function getUrlParameter(sParam)
{
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++)
	{
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam)
		{
			return sParameterName[1];
		}
	}
}

function getDateTime(is_day, is_month, is_year, is_hour, is_minute, is_second)
{
	is_day = typeof(is_day) != 'undefined' ? is_day : true;
	is_month = typeof(is_month) != 'undefined' ? is_month : true;
	is_year = typeof(is_year) != 'undefined' ? is_year : true;
	is_hour = typeof(is_hour) != 'undefined' ? is_hour : true;
	is_minute = typeof(is_minute) != 'undefined' ? is_minute : true;
	is_second = typeof(is_second) != 'undefined' ? is_second : true;
	var now = new Date();
	var year = now.getFullYear();
	var month = now.getMonth() + 1;
	var day = now.getDate();
	var hour = now.getHours();
	var minute = now.getMinutes();
	var second = now.getSeconds();
	if (month.toString().length == 1)
	{
		var month = '0' + month;
	}
	if (day.toString().length == 1)
	{
		var day = '0' + day;
	}
	if (hour.toString().length == 1)
	{
		var hour = '0' + hour;
	}
	if (minute.toString().length == 1)
	{
		var minute = '0' + minute;
	}
	if (second.toString().length == 1)
	{
		var second = '0' + second;
	}
	/*console.log(is_day+', '+is_month+', '+is_year+', '+is_hour+', '+is_minute+', '+is_second);*/
	var dateTime = '';
	if (is_day == true) dateTime += day;
	if (is_month == true) dateTime += '/' + month;
	if (is_year == true) dateTime += '/' + year;
	if (is_hour == true) dateTime += ' ' + hour;
	if (is_minute == true) dateTime += ':' + minute;
	if (is_second == true) dateTime += ':' + second;
	/*var dateTime = day+'/'+month+'/'+year+' '+hour+':'+minute+':'+second; */
	return dateTime;
}

function isJson(str)
{
	try
	{
		JSON.parse(str);
	}
	catch (e)
	{
		return false;
	}
	return true;
}