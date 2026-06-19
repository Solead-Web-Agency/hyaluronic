/**
* 2007-2023 Helloshop
*
* Tracking Center
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

$(document).ready(function(){
	var timer;
	var url_ajax = $("input[name='url_ajax']").val();
	var url_root = $("input[name='url_root']").val();
	var token = $("input[name='secure_key']").val();
	if ($(".datepicker").length > 0)
	$(".datepicker").datepicker({
		prevText: '',
		nextText: '',
		dateFormat: 'yy-mm-dd'
	});

	$('.test_event_email').click(function() {
		var email = $('input[name="email_test"]').val();
		var id_order = $('select[name="orders_test"]').val();
		if ((email.length) && (id_order)) {
			$.ajax({
				type: 'POST',
				headers: { "cache-control": "no-cache" },
				url: url_ajax,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'TrackConfig',
					ajax : true,
					email : email,
					l : 1,
					language : $('select[name="language_test"]').val(),
					id_order : id_order,
					secure_key: token,
				},
				success: function(data)
				{
					$(".alert-successed").show();
					$(".alert-error").hide();
					$(".alert-error_order").hide();
				}
			});
		} else {
			if (!id_order) {
				$(".alert-error_order").show();
			} else {
				$(".alert-error_order").hide();
			}
			if (email.length == 0) {
				$(".alert-error").show();
			} else {
				$(".alert-error").hide();
			}
			$(".alert-successed").hide();
		}
	});

	$(".close_popup").click(function(){
		$(".box_carrier").css("display", "none");
		$(".message_infor_box").css("display", "none");
		$(".config_carrier_modules").empty();
	});


	// $(".Create_status").click(function(){
	// 	$(".popup_parent").show();
	// 	$.ajax({
	// 		url: url_root+'modules/deliveryorderautoupdate/ajax_order.php',
	// 		headers: { "cache-control": "no-cache" },
	// 		async: true,
	// 		data: {
	// 			l : 9,
	// 			status : $(this).attr("data-id"),
	// 			token : token
	// 		},
	// 		success: function(data)
	// 		{
	// 			$('.note_conf').show();
	// 			$(".popup_parent").hide();
	// 			setTimeout(function() {
	// 				$('.note_conf').css('display', "none");
	// 				location.reload();
	// 			}, 2000);
	// 		}
	// 	});
	// });
	$('#all_couriers').off('click', ".del_carrier");
	$('#all_couriers').on('click', ".del_carrier", function(event) {
		event.preventDefault();
		e = $(this);
		id = $(this).attr("id_carrier");
		console.log(id);
		hasCre = parseInt(e.attr('data-credential'));
		deleteCre = false;
		if (hasCre) {
			// deleteCre = confirm("Do you want to delete credentials ?");
			$( "#dialog-confirm" ).dialog({
				position: { my: "center", at: "center", of: window },
				resizable: false,
				height: "auto",
				width: 400,
				modal: true,
				buttons: {
					"Yes": function() {
						deleteCre = true;
						$( this ).dialog( "close" );
					},
					"No": function() {
						deleteCre = false;
						$( this ).dialog( "close" );
					}
				},
				close: function (event, ui) {
					$.ajax({
						type: 'POST',
						headers: { "cache-control": "no-cache" },
						url: url_ajax,
						async: true,
						cache: false,
						data: {
							action: 'AllCountry',
							ajax: 1,
							l: 5,
							id: id,
							secure_key: token,
							deleteCre: deleteCre,
						},
						success: function(data)
						{
							tr = e.closest('tr');
							tr.find('#installed').hide();
							tr.find('#not-installed').show();
						},
					});
				}
			});
		} else {
			$.ajax({
				type: 'POST',
				headers: { "cache-control": "no-cache" },
				url: url_ajax,
				async: true,
				cache: false,
				data: {
					action: 'AllCountry',
					ajax: 1,
					l: 5,
					id: id,
					secure_key: token,
					deleteCre: deleteCre,
				},
				success: function(data)
				{
					tr = e.closest('tr');
					tr.find('#installed').hide();
					tr.find('#not-installed').show();
				},
			});
		}
	});

	/*carrier form*/
	// $(".remove").click(function(){
	// 	$(this).parent().parent().remove();
	// });
	$("input[name='track_method']").change(function() {
		var radio = $("input[name='track_method']:checked").val();
		checkTrack(radio);
	});
	checkTrack($("input[name='track_method']:checked").val());
	function checkTrack(radio)
	{
		if (radio == 0) {
			$(".track_method_2").css('display', 'none');
			$(".carrier_sub_conf").css('display', 'none');
			$(".carrier_emb_conf").css('display', 'inline-block');
		} else if (radio == 2) {
			$(".track_method_2").css('display', 'block');
			$(".carrier_sub_conf").css('display', 'none');
			$(".carrier_emb_conf").css('display', 'none');
		}
	}
	function getListKey(key)
	{
		var list_key = '';
		var list_keycarrier = $("."+key).html();
		if (list_keycarrier.length > 0) {
			$("."+key+" .carrier_area .col-md-10").each(function(){

				if (list_key.length == 0) {
					list_key = $(this).html();
				} else {
					list_key = list_key +','+ $(this).html();
				}
			});
		}
		return list_key;
	}
	// $('.list_detectedcarrier').click(function() {
	// 	var emb = $(this).data('emb');
	// 	var id_carriername = $(this).data('id_carriername');
	// 	var modulecarrier = $(this).val();
	// 	if (emb) {
	// 		$(".carrier_sub_conf").hide();
	// 		$(".carrier_emb_conf").show();
	// 	} else {
	// 		$(".carrier_sub_conf").show();
	// 		$(".carrier_emb_conf").hide();
	// 	}
	// 	$.ajax({
	// 		type: 'POST',
	// 		url: url_root+'modules/deliveryorderautoupdate/ajax_order.php' + '?rand=' + new Date().getTime(),
	// 		cache: false,
	// 		data: {
	// 			l : 10,
	// 			emb : emb,
	// 			modulecarrier : modulecarrier,
	// 			id_carriername : id_carriername,
	// 			token : token
	// 		}
	// 	});
	// });
	$(".save_carrier").click(function(){
		var update = $(this).attr("edit");
		var id = $(this).attr("id_carrier");
		var list_carrier_string = '';
		var carrier_emb = new Array();
		var carrier_sub = new Array();
		var carrier_emb_count = $('input[name="carrier_emb_count"]').val();
		var carrier_sub_count = $('input[name="carrier_sub_count"]').val();
		if (carrier_emb_count) {
			for (var i=0; i<carrier_emb_count; i++) {
				carrier_emb.push({"name" : $('.emb_carrier_'+i).attr('name'), "val" : $('.emb_carrier_'+i).val()});
			}
		}
		if (carrier_sub_count) {
			for (var j=0; j<carrier_sub_count; j++) {
				carrier_sub.push({"name" : $('.sub_carrier_'+j).attr('name'), "val" : $('.sub_carrier_'+j).val()});
			}
		}

		var track_method = $("input[name='track_method']:checked").val();
		//var carrier_url = $('.carrier_url_'+id).val();


		//list_carrier_string = getListKey('list_carrier');

		$.ajax({
			type: 'POST',
			headers: { "cache-control": "no-cache" },
			url: url_ajax,
			data: {
				controller : 'AdmindeliveryorderautoupdateAjax',
				action : 'SaveCarrier',
				ajax : true,
				id_carrier : $(this).attr("id_carrier"),
				edit : update,
				active : 0,//$("input[name='delivery_carrier_active']:checked").val(),
				id : id,
				//list_carrier : list_carrier_string,
				carrier_emb : carrier_emb,
				carrier_sub : carrier_sub,
				secure_key: token,
				methor : 0//track_method
			},
			success: function(data)
			{
				data = JSON.parse(data);
				console.log(data);
				$('#all_couriers').find(`.del_carrier[id_carrier=${id}]`).attr('data-credential', data.credential);
				$(".close_popup").click();
				// $(".my_couriers").click();
				$(".alert_save_carrier").show();
				setTimeout(function() {
					$('.alert_save_carrier').css('display', "none");
				}, 2000);
			}
		});
	});
	// $(".addlist_keycarrier, .addlist_notcarrier, .addlist_takecarrier, .addlist_shipped_number, .addlist_delivery_parcel, .addlist_return_shipper, .addlist_out_delivery").click(function(){
	// 	var delivery_key = $(this).parent().prev().children();
	// 	var list_carrier = $(this).parent().next().children();
	// 	if ((delivery_key.val().length > 1) && (list_carrier.html().indexOf(delivery_key.val()) == -1)) {
	// 		$.ajax({
	// 			type: 'POST',
	// 			url: url_root+'modules/deliveryorderautoupdate/ajax_order.php' + '?rand=' + new Date().getTime(),
	// 			async: true,
	// 			cache: false,
	// 			data: 'l=2&carrier_config='+delivery_key.val()+'&token='+token,
	// 			success: function(data)
	// 			{
	// 				list_carrier.append(data);
	// 				delivery_key.val('');
	// 			},
	// 		});
	// 	}
	// });
});