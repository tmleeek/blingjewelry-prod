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

var AmazonWidgets = function (merchant_id, module_base_path){
	this.merchantId = merchant_id;
	this.createStandardWidget = function(orderpath, form, standardcallback) {		
		this.orderpath = orderpath;
		this.formelement = form;
		this.standardcallback = standardcallback;
		this.standardWidget = new CBA.Widgets.StandardCheckoutWidget({
			merchantId:this.merchantId,
				orderInput: {
					format: "HTML",
					value: this.formelement
				},
			onOpen : standardcallback
		});	
	}

	this.createInlineWidget = function(session_path , inlinecallback, addressbook, sizeval, colorval, backgroundval) {
		this.session_path = session_path;
		this.inlinecallback = inlinecallback;
		_a_widgets = this;
		
		this.inlineWidget = new CBA.Widgets.InlineCheckoutWidget ({
			merchantId: this.merchantId,
			buttonSettings: {size:sizeval, color:colorval, background:backgroundval},
			buttonType: addressbook,
			onAuthorize: function (widget) {
				_a_widgets.contract_id = widget.getPurchaseContractId();
				$jQueryconflict.get(session_path, {contract_id:_a_widgets.contract_id}, function (data){
					inlinecallback();
				});
			}
		});
	};
	
	this.createWalletWidget = function (displayMode) {
		//_a_widgets =this;		
		this.walletWidget = new CBA.Widgets.WalletWidget({
			merchantId: this.merchantId,
			displayMode : displayMode,
			onPaymentSelect: function(widget) {
				document.getElementById("confirmAction").style.display = "inline-block";
				document.getElementById("confirmMessagePayment").style.display = "none";
			}
		});
	};
	
	this.createAddressWidget = function(shipping_path, target_id, callback) {
		this.shipping_path = shipping_path;
		this.target_id     = target_id;
		_b_widgets = this;
		this.addressWidget = new CBA.Widgets.AddressWidget({
			merchantId: this.merchantId ,
			onAddressSelect: function (widget){
				callback(shipping_path, target_id);
			}
		});
	}
	
	this.setAddressId = function (address_id){
		this.address_id = address_id;
	}
	
	this.renderWidget = function (widget,divid){
		widget.render(divid);
	};	
};



var checkHanleClose = '';var checkHanleCloseOnlySuccess = '';var yesvarexistsornot = ''; var notlcoseexistance = ''; var yesopenexistance = '';
function checkExistanceVar() { for(var A in CBA.Widgets.mediator.registry) { yesvarexistsornot = 'loaded'; var B=CBA.Widgets.mediator.registry[A];
if(typeof (B.popupWindow)!="undefined"&&typeof (B.popupWindowName)!="undefined"&&!B.popupWindow.closed) { yesopenexistance = 'yes'; } else { setTimeout("checkExistanceVar()",2500); } }	 if(yesvarexistsornot == '') { setTimeout("checkExistanceVar()", 2500); } if(yesopenexistance == 'yes') { notlcoseexistance = ''; setTimeout("isExistanceVarClose('checkExistanceVar')", 2500); yesopenexistance = ''; } }
function isExistanceVarClose(valueget) { for(var A in CBA.Widgets.mediator.registry) { var B=CBA.Widgets.mediator.registry[A];
if(typeof (B.popupWindow)!="undefined"&&typeof (B.popupWindowName)!="undefined"&&!B.popupWindow.closed) {} else { notlcoseexistance = 'yes'; yesvarexistsornot = ''; accordion.openSection('ixcba-orderitems'); } } if(notlcoseexistance == '') {setTimeout("isExistanceVarClose('isExistanceVarClose')", 2500);} }