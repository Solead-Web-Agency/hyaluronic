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
var PRO = PRO ||
{};
PRO.settings = (
{
	dom: null,
	box: null,
	init: function(box)
	{
		var self = this;
		// isPS16
		// console.log(dataStorage);
		self.box = box;
		self.ready(function(dom)
		{
			//var dataStorage = box.dataStorage;
			function isPS16()
			{
				return true; //dataStorage.data.isPS16;
			}

			function getPadding()
			{
				return dom.noBootstrap.innerWidth() - dom.noBootstrap.width();
			}

			function run()
			{
				setTimeout(function()
				{
					var contentWidth = dom.box.width(),
						sliderWidth = dom.navSlider.width(),
						widthWidth = $(window).width();
					// alert alert-danger
					// var newWidth = (((widthWidth - sliderWidth)/widthWidth) * 100);
					// 	newWidthPercent =  newWidth + '%' 
					// dom.box.width(newWidthPercent);
					var newWidth = widthWidth - sliderWidth - getPadding();
					//console.log(newWidth);
					if (newWidth > 800)
					{
						dom.box.width(newWidth);
						dom.alertDanger.css('width', newWidth + 'px');
					}
					// var paddingWidth = dom.alertDanger.getPaddingWidth(),
					// 	alertDangerWidth = dom.newsletterpro.width();
					// console.log(dom.alertDanger.css('padding-left'))
					// console.log(alertDangerWidth);
					dom.alertDanger.css('margin-left', '-10px');
					// dom.alertDanger.width(dom.newsletterpro.width());
					// console.log(newWidth);
					// console.log(dom.alertDanger);
					// refershCurrentTab();
				}, 50);
			}
			if (isPS16())
			{
				dom.menuCollapse.on('click', function(event)
				{
					run();
				});
				$(window).resize(function()
				{
					run();
				});
				run();
			}
		});
	},
	ready: function(func)
	{
		var self = this;
		$(document).ready(function()
		{
			var navSlider = $('#nav-sidebar');
			self.dom = {
				box: $('#scboz_box'),
				navSlider: navSlider,
				menuCollapse: navSlider.find('.menu-collapse'),
				alertDanger: $('.alert.alert-danger'),
				noBootstrap: $('#content.nobootstrap'),
			};
			func(self.dom);
		});
	},
}.init(PRO));