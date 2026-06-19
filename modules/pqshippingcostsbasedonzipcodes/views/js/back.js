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
/*document.write( generateRatingStars(3) );*/
var test;
$(window).load(function() {});
$(window).resize(function() {}).resize();
$(window).click(function() {});
$(document).ready(function()
{
	//adding header buttons
	if (getUrlParameter('controller') == 'AdminModules' && getUrlParameter('configure') == 'pqshippingcostsbasedonzipcodes')
	{
		scbozAddToolbarBtn('separator');
		//scbozAddToolbarBtn('video');
		scbozAddToolbarBtn('documentation');
		scbozAddToolbarBtn('contact');
		scbozAddToolbarBtn('modules');
	}
	/*init the tabs*/
	$("#scboz_tabs").tabs().show();

	$('#csv_upload').uploadifive(
	{
		'multi': false,
		'formData':
		{
			'location': 'uploads'
		},
		'buttonText': 'Upload',
		"uploadScript"     : scboz_path+"libraries/uploadify/uploadifive.php",
		'onUploadComplete': function(file, data)
		{
			if (data == 'error1')
			{
				alert(l_scboz('File exists, choose different filename.'));
			}
			else if (data == 'error2')
			{
				alert(l_scboz('Invalid file type.'));
			}
			else
			{
				//$("#tabs_configuration_a").trigger('click');
				if ($.fn.DataTable.isDataTable('#conditions_grid')) 
					conditions_datatable.api().ajax.reload();
				alert(l_scboz(data));
				/*$("#offline_img").attr('src', scboz_path + 'views/img/iconsets/'+data );*/
			}
		},
		/*height     : 20,
        width      : 50,*/
	});




	$(document).on('click', '#scboz_manage_countries', function()
	{
		window.open("index.php?controller=AdminCountries&token=" + scboz_countries_token, '_blank');
	});
	$(document).on('click', '#scboz_manage_zones', function()
	{
		window.open("index.php?controller=AdminZones&token=" + scboz_zones_token, '_blank');
	});
	$(document).on('change', '#filter_c', function()
	{
		if ($(this).val() == 'Y')
		{
			$("#zipcode_tr").hide();
			$("#zipcode_min_tr").show();
			$("#zipcode_max_tr").show();
		}
		else
		{
			$("#zipcode_tr").show();
			$("#zipcode_min_tr").hide();
			$("#zipcode_max_tr").hide();
		}
	});
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	conditions_table = scboz_db_prefix + scboz_module_name + '_conditions';
	//var countries = scboz_countries; //console.log(countries);
	//var zones = scboz_zones; //console.log(zones);
	//var country_lang_table = scboz_db_prefix+'country_lang';
	//var zone_table = scboz_db_prefix+'zone';
	conditions_editor = new $.fn.dataTable.Editor(
	{
		ajax: scboz_grid_path + 'conditions',
		table: "#conditions_grid",
		fields: [
			//{ label: l_scboz("ID:"), name: conditions_table+".iso_code", type: "select" },
			{
				label: l_scboz("Countries:"),
				name: conditions_table + ".id_country",
				type: "select",
				options: scboz_countries_ids
			},
			//{ label: l_scboz("Country:"), name: conditions_table+".id_country", },
			//{ label: l_scboz("Zone:"), name: conditions_table+".id_zone", },
			{
				label: l_scboz("Zones:"),
				name: conditions_table + ".id_zone",
				type: "select",
				options: scboz_zones_ids
			},
			{
				label: l_scboz("Filter:"),
				name: conditions_table + ".filter",
				type: "select",
				options: [
				{
					label: l_scboz("Is range"),
					value: "range"
				},
				{
					label: l_scboz("Is equal with"),
					value: "equal"
				},
				{
					label: l_scboz("Starts with"),
					value: "starts"
				}, ],
			},
			{
				label: l_scboz("Zipcode min:"),
				name: conditions_table + ".zipcode_min",
			},
			{
				label: l_scboz("Zipcode max:"),
				name: conditions_table + ".zipcode_max",
			},
			//{ label: l_scboz("Date:"), name: conditions_table+".upd_date", },
		]
	});
	conditions_editor.dependent(conditions_table + ".filter", function(val)
	{
		if (val !== 'range')
		{
			$('#DTE_Field_' + conditions_table + '-zipcode_max').val($('#DTE_Field_' + conditions_table + '-zipcode_min').val());
			//console.log($('.DTE_Label').find('label[for="DTE_Field_'+conditions_table+'-zipcode_min"]').html());//'Zipcode: <div class="DTE_Label_Info" data-dte-e="msg-label"></div>'
			return {
				hide: [conditions_table + ".zipcode_max"]
			};
		}
		else
		{
			return {
				show: [conditions_table + ".zipcode_max"]
			};
		}
		//return val === 'N' ? {  } : {  };
	});
	var conditions_editor_create_button = {
		extend: "create",
		editor: conditions_editor
	};
	var conditions_editor_edit_button = {
		extend: "edit",
		editor: conditions_editor
	};
	var conditions_editor_remove_button = {
		extend: "remove",
		editor: conditions_editor
	};
	var conditions_editor_buttons = [];
	if (scboz_employee_is_superadmin == 'Y')
	{
		conditions_editor_buttons.push(conditions_editor_create_button)
		conditions_editor_buttons.push(conditions_editor_edit_button)
		conditions_editor_buttons.push(conditions_editor_remove_button);
	}
	else
	{
		conditions_editor_buttons.push(conditions_editor_create_button);
	}
	conditions_editor.one('open', function()
	{
		$('#DTE_Field_' + conditions_table + '-id_country').after('<a style="margin-left: 5px;" href="javascript:{}" name="scboz_manage_countries" id="scboz_manage_countries" class="scboz button" title="Manage Countries"><span class="fa fa-cog"></span> ' + l_scboz('Manage') + '</a>');
		$('#DTE_Field_' + conditions_table + '-id_zone').after('<a style="margin-left: 5px;" href="javascript:{}" name="scboz_manage_zones" id="scboz_manage_zones" class="scboz button" title="Manage zones"><span class="fa fa-cog"></span> ' + l_scboz('Manage') + '</a>');
		//console.log(conditions_editor.get());
	});
	var _return = false;
	conditions_editor.on('preSubmit', function()
	{
		//console.log(conditions_editor.get());
		var data_to_send = conditions_editor.get();
		var data = {
			'country': data_to_send[conditions_table + '.id_country'],
			'zone': data_to_send[conditions_table + '.id_zone'],
		};
		//return console.log(data);
		var params = {
			'load': 'validateDataTablesInfo',
			'divs': null,
			'params':
			{
				'data': data,
			},
			/*'preloader' : {
                'divs' : {
                    0 : 'conditions_ajax_load_span',
                },
                'type' : 2,
                'style' : 3,
            }*/
		};
		scbozAjaxController(params, function(result)
		{
			var res = JSON.parse(result);
			//console.log(res);
			if (res.success != true)
			{
				alert(res.response);
				_return = false;
			}
			else _return = true;
		});
		return _return;
	});
	conditions_datatable = $('#conditions_grid').dataTable(
	{
		dom: "Bfrtip",
		//"processing": true,
		//"serverSide": true,
		"pageLength": 50,
		"autoWidth": false,
		ajax:
		{
			url: scboz_grid_path + 'conditions',
			type: 'POST',
			/*error: function (xhr, error, thrown) {
       			alert( 'You are not logged in' );
    		}*/
		},
		columns: [
		{
			data: conditions_table + ".id_country",
			className: "dt-body-center",
			render: function(data, type, row, meta)
			{
				//console.log(countries);
				return scboz_countries[data];
			}
		},
		{
			data: conditions_table + ".id_zone",
			className: "dt-body-center",
			render: function(data, type, row, meta)
			{
				//console.log(countries);
				return scboz_zones[data];
			}
		},
		{
			data: conditions_table + ".filter",
			className: "dt-body-center",
			render: function(data, type, row, meta)
			{
				if (data === 'range')
					return 'Is range';
				else if (data === 'equal')
					return 'Is equal with';
				else if (data === 'starts')
					return 'Starts with';
				//return data === 'Y' ? 'Yes' : 'No';
			}
		},
		{
			data: conditions_table + ".zipcode_min",
			className: "dt-body-center"
		},
		{
			data: conditions_table + ".zipcode_max",
			className: "dt-body-center"
		},
		{
			data: conditions_table + ".upd_date",
			className: "dt-body-center"
		}, ],
		select: true,
		buttons: conditions_editor_buttons,
	});
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	$(document).on('keyup change', '#DTE_Field_' + conditions_table + '-zipcode_min', function()
	{
		if ($('#DTE_Field_' + conditions_table + '-filter').val() != 'range')
			$('#DTE_Field_' + conditions_table + '-zipcode_max').val($(this).val());
	});


}); /*// end document ready*/