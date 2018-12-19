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
		'mage/url'
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
				template: 'Sisow_Payment/payment/default'
			},
			getLogo: function(){
				return configSisow.logoVpay;
			},
			logoInCheckout: function(){
				return configSisow.logoCheckout;
			},
			afterPlaceOrder: function () {
				window.location.replace(url.build('sisow/payment/start/'));
			}
			
		});
    }
);
