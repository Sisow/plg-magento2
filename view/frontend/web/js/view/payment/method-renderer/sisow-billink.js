/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
		'Magento_Checkout/js/view/payment/default',
		'Magento_Checkout/js/action/select-payment-method',
		'Magento_Checkout/js/checkout-data',
		'mage/url',
		'jquery',
        "mage/validation"
    ],
    function (Component,
				selectPaymentMethodAction,
				checkoutData,
				url) {
		'use strict';
		var configSisow = window.checkoutConfig.payment.sisow;		
        return Component.extend({
			redirectAfterPlaceOrder: false,	
			defaults: {
				template: 'Sisow_Payment/payment/billink',
				coc: '',
				gender: '',
				day: '',
				month: '',
				year: '',
				phone: ''
			},
			initObservable: function () {
                this._super()
                    .observe('coc');
				this._super()
                    .observe('gender');
				this._super()
                    .observe('day');
				this._super()
                    .observe('month');
				this._super()
                    .observe('year');
				this._super()
                    .observe('phone');
					 
                return this;
            },			
			getData: function () {
                return {
                    "method": this.item.method,
                    "additional_data": {
						'coc': this.coc(),
						'gender': this.gender(),
						'day': this.day(),
						'month': this.month(),
						'year': this.year(),
						'phone': this.phone()
					}
                };
			},

			afterPlaceOrder: function () {
				window.location.replace(url.build('sisow/payment/start/'));
			},

			getYears: function () {
                return configSisow.years;
            },
			getLogo: function(){
				return configSisow.logoBillink;
			},
			getDisableShippingAddress: function(){
				return configSisow.disableshippingaddressBillink;
			},
			getB2bRequireGenderDoB: function(){
				return configSisow.b2brequiregenderdobBillink;
			},
			logoInCheckout: function(){
				return configSisow.logoCheckout;
			},
			offerB2b: function(){
				return configSisow.b2bBillink;
			},
            validate: function () {
				var $ = jQuery.noConflict();
                var form = $('form[data-role=form-sisowbillink]');
                return $(form).validation() && $(form).validation('isValid');
            },
			getBillingCountry: function()
			{
				var address = checkoutData.getBillingAddressFromData();
				if(address == null){
					address = checkoutData.getShippingAddressFromData();
				}
				
				if(address == null){
					return "";
				}
				else{
					return address.country_id;
				}
			},
			isB2BPayment: function(){
				var address = checkoutData.getBillingAddressFromData();
				if(address == null){
					address = checkoutData.getShippingAddressFromData();
				}
				
				if(address == null){
					return false;
				}
				else{
					return this.offerB2b() && address.company != "" && address.country_id == "NL";
				}
			},
			needPhone: function(){
				var address = checkoutData.getBillingAddressFromData();
				if(address == null){
					address = checkoutData.getShippingAddressFromData();
				}
				
				return address == null || typeof address.telephone === "undefined" || address.telephone == "";
			},
			needGenderDob: function() {
				return  ( this.isB2BPayment() == false || (this.isB2BPayment() && this.getB2bRequireGenderDoB()) );
			}
		});
    }
);
