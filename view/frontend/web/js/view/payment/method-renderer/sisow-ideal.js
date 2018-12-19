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
		'mage/validation'
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
				template: 'Sisow_Payment/payment/ideal',
				issuerid: ''
			},
			initObservable: function () {
                this._super()
                    .observe('issuerid');
                return this;
            },			
			getData: function () {
                return {
                    "method": this.item.method,
                    "additional_data": {
						'issuerid': this.issuerid()
					}
                };
			},

			afterPlaceOrder: function () {
				window.location.replace(url.build('sisow/payment/start/'));
			},
			
			getBanks: function () {
                return configSisow.issuers;
            },
			getLogo: function(){
				return configSisow.logoIdeal;
			},
			logoInCheckout: function(){
				return configSisow.logoCheckout;
			},
            validate: function () {
				var $ = jQuery.noConflict();
                var form = $('form[data-role=form-sisowideal]');
                return $(form).validation() && $(form).validation('isValid');
            }
		});
    }
);
