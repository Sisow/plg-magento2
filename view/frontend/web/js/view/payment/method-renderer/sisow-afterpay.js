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
				template: 'Sisow_Payment/payment/afterpay',
				coc: '',
				gender: '',
				day: '',
				month: '',
				year: ''
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
						'year': this.year()
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
				return configSisow.logoAfterpay;
			},
			logoInCheckout: function(){
				return configSisow.logoCheckout;
			},
			offerB2b: function(){
				return configSisow.b2b;
			},
			getDisableShippingAddress: function(){
				return configSisow.disableshippingaddressAfterpay;
			},
            validate: function() {
				var $ = jQuery.noConflict();
                var form = $('form[data-role=form-sisowafterpay]');
                return $(form).validation() && $(form).validation('isValid');
            },
			getAfterpayTerms: function(){
				if(this.isB2BPayment() && this.getBillingCountry() == 'NL'){
					return "https://www.afterpay.nl/nl/algemeen/zakelijke-partners/betalingsvoorwaarden-zakelijk";
				}
				else if(this.getBillingCountry() == 'BE'){
					return "https://www.afterpay.be/be/footer/betalen-met-afterpay/betalingsvoorwaarden";
				}
				else{
					return "https://www.afterpay.nl/nl/algemeen/betalen-met-afterpay/betalingsvoorwaarden";
				}
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
			}
		});
    }
);
