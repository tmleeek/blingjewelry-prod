/**
 * Mageix LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to Mageix LLC's  End User License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://mageix.com/index.php/license-guide/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to webmaster@mageix.com so we can send you a copy immediately.
 *
 * DISCLAIMER

 *
 * @category	Mageix
 * @package 	Mageix_Ixcba
 * @copyright   Copyright (c) 2011 Mageix LLC (http://mageix.com)
 * @license	http://mageix.com/index.php/license-guide/  End User License Agreement
 */

/*
 * Magento Mageix IXCBA Module
 *
 * @category   Checkout & Payments
 * @package	Mageix_Ixcba
 * @copyright  Copyright (c) 2012 Mageix LLC (http://mageix.com)
  *@licence 	http://mageix.com/index.php/license-guide/ 
 */

function isShippingMethodSelected (form) {
	var ischecked = '';
	var methods = document.getElementsByName('shipping_method');
        if (methods.length==0) {
            alert(Translator.translate('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'));
            return false;
        }

	for (var i=0; i<methods.length; i++) {
            if (methods[i].checked) {
                ischecked = 'checked';
            }
        }

	if(ischecked != 'checked') {
		alert(Translator.translate('Please specify shipping method.'));
	        return false;
	}else{
		return true;
	}
}

function inlineCheckout (button) {
	hideShowMessage('successMessagePayment', 'block');
	hideShowMessage('confirmAction', 'none');
	saveUrl = button.form.action;
	var request = new Ajax.Request(
		saveUrl,
		{
			method: 'post',
			onSuccess: onCheckoutSuccess,
			onFailure: onCheckoutFailed,
			parameters: Form.serialize(button.form)
		}
	);
	return false;
}

function onCheckoutSuccess(data) {
	var hidden_success_url = '';
	if(document.getElementById("hidden_success_url")) {
		hidden_success_url = document.getElementById("hidden_success_url").value;
	}
	response = $jQueryconflict.parseJSON(data.responseText);
	if (response["success"] == true) {
		window.location = hidden_success_url+"?amazonorderid="+response["amazon_order_id"];
	} else {
		hideShowMessage('successMessagePayment', 'none');
		hideShowMessage('confirmAction', 'inline-block');
		alert(response["error_messages"]);
	}
}

function onCheckoutFailed(data) {
	hideShowMessage('successMessagePayment', 'none');
	hideShowMessage('confirmAction', 'inline-block');
	alert("Your order details cannot submit. Please contact administrator.");
}

function hideShowMessage(id, action) { if(document.getElementById(id)){document.getElementById(id).style.display = action;} }