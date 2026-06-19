{*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @package   cdc_googletagmanager
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}
{if !empty($dataLayer)}
<script>
	var nextActionName = null;

var cdcGtmOpcLastStep = 1;
var cdcGtmOpc = {
	pushCheckoutStep : function(step, option) {
		console.log("[call] step: " + step + " : " + option);
		if(step > cdcGtmOpcLastStep) {
			var dataLayerOpc = {$dataLayer nofilter};
			dataLayerOpc.event = "checkout_opc";
			dataLayerOpc.ecommerce.checkout.actionField.step = step;
			dataLayerOpc.ecommerce.checkout.actionField.option = option;
			dataLayer.push(dataLayerOpc);
			console.log("[sent] step: " + step + " : " + option);
			cdcGtmOpcLastStep = step;
		}
	}
}

// address
function gtmOpcAddress() {
	// Deprecated, address step is no longer tracked by GA
	//cdcGtmOpc.pushCheckoutStep(2, "address");
}

// delivery option chosen
function gtmOpcDelivery($submitBtn, e, shippingName) {
	nextActionName = "delivery";

	setTimeout(function() {
		if(nextActionName == "delivery") {
			nextActionName = null;
			shippingName = shippingName || 'Unknown shipping method';
			cdcGtm.handleValidateShippingStep($submitBtn, e, shippingName.trim(), true);
		}
	}, 100);
}

// payment chosen
function gtmOpcPayment(paymentName) {
	nextActionName = "payment";

	setTimeout(function() {
		if(nextActionName == "payment") {
			nextActionName = null;
			paymentName = paymentName || 'Unknown payment';
			cdcGtm.handleValidatePayementStep(paymentName.trim());
		}
	}, 100);
}


// supercheckout - Knowband - One Page Checkout, Social Login & Mailchimp v3.0.7
if($("#velsof_supercheckout_form").length) {
	console.log("[CDCGTM] supercheckout - Knowband");
	$("#checkoutShippingAddress").on("click", function(e) {
		if(!e.isTrigger) {
			gtmOpcAddress();
		}
	});
	$("#velsof_supercheckout_form").on("click", "#shipping-method", function(e) {
		if(!e.isTrigger) {
			gtmOpcDelivery($(this), e);
		}
	});
	$("#velsof_supercheckout_form").on("click", "#payment-method", function(e) {
		if(!e.isTrigger) {
			gtmOpcPayment();
		}
	});
}

// onepagecheckoutps - PresTeamShop - One Page Checkout PrestaShop v1.3.8 -> v2.2.2
else if($("#onepagecheckoutps").length) {
	console.log("[CDCGTM] onepagecheckoutps - PresTeamShop");
	$("#onepagecheckoutps").on("click", "#onepagecheckoutps_step_one", function(e) {
		if(!e.isTrigger) {
			gtmOpcAddress();
		}
	});
	$("#onepagecheckoutps").on("click", "#onepagecheckoutps_step_two", function(e) {
		if(!e.isTrigger) {
			gtmOpcDelivery($(this), e);
		}
	});
	$("#onepagecheckoutps").on("click", "#onepagecheckoutps_step_three", function(e) {
		if(!e.isTrigger) {
			gtmOpcPayment();
		}
	});
}

// bestkit_opc - best-kit - One Step Checkout / One Page Checkout v1.6.7
else if($("#3column_opc").length || $("#bigcart_opc").length) {
	var $opc_wrapper = null;
	// 2 styles: 3 columns or bigcart
	if($("#3column_opc").length) {
		$opc_wrapper = $("#3column_opc");
	} else {
		$opc_wrapper = $("#bigcart_opc");
	}
	console.log("[CDCGTM] bestkit_opc - best-kit - style: " + $opc_wrapper.prop('id'));
	$opc_wrapper.on("click", "#opc_account", function(e) {
		if(!e.isTrigger) {
			gtmOpcAddress();
		}
	});
	$opc_wrapper.on("click", "#opc_delivery_methods", function(e) {
		if(!e.isTrigger) {
			gtmOpcDelivery($(this), e);
		}
	});
	$opc_wrapper.on("click", "#opc_payments", function(e) {
		if(!e.isTrigger) {
			gtmOpcPayment();
		}
	});
}

// klarnaofficial - Prestaworks AB - Klarna v1.8.42
else if($(".kco-main").length) {
	console.log("[CDCGTM] klarnaofficial - Prestaworks AB");
	$(".kco-main").on("click", "#klarnacarrier", function(e) {
		if(!e.isTrigger) {
			gtmOpcDelivery($(this), e);
		}
	});
	$(".kco-main").on("click", "#klarna-checkout-container", function(e) {
		if(!e.isTrigger) {
			gtmOpcPayment();
		}
	});
}

// steasycheckout
else if($(".steco_container").length) {
	let $opc_wrapper = $(".steco_container");
	console.log("[CDCGTM] steasycheckout");
	$opc_wrapper.on("click", ".steco_addresses", function(e) {
		if(!e.isTrigger) {
			gtmOpcAddress();
		}
	});
	$opc_wrapper.on("click", ".steco_delivery", function(e) {
		if(!e.isTrigger) {
			gtmOpcDelivery($(this), e);
		}
	});
	$opc_wrapper.on("click", ".steco_payment, .steco-custom-input", function(e) {
		if(!e.isTrigger) {
			gtmOpcPayment();
		}
	});
}


// default Prestashop 1.5 / 1.6 OPC
else {
	$("#order-opc").on("click", "#opc_account", function(e) {
		if(!e.isTrigger) {
			gtmOpcAddress();
		}
	});
	$("#order-opc").on("click", "#carrier_area", function(e) {
		if(!e.isTrigger) {
			gtmOpcDelivery($(this), e);
		}
	});
	$("#order-opc").on("click", "#opc_payment_methods .payment_module", function(e) {
		if(!e.isTrigger) {
			gtmOpcPayment($(this).text());
		}
	});
}

</script>
{/if}
