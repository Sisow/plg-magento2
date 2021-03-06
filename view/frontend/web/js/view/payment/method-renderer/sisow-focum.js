/**
 * Copyright � 2015 Magento. All rights reserved.
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
				template: 'Sisow_Payment/payment/focum',
				iban: '',
				gender: '',
				day: '',
				month: '',
				year: ''
			},
			initObservable: function () {
                this._super()
                    .observe('iban');
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
						'iban': this.iban(),
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
				return configSisow.logoFocum;
			},
			logoInCheckout: function(){
				return configSisow.logoCheckout;
			},
			getDisableShippingAddress: function(){
				return false;
			},
            validate: function () {
				var $ = jQuery.noConflict();
                var form = $('form[data-role=form-sisowfocum]');
                return $(form).validation() && $(form).validation('isValid');
            }		
		});
    }
);
