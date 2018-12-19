/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Sisow_Payment/js/action/checkout/cart/totals'
    ],
    function($, ko ,quote, totals) {
        'use strict';
        var isLoading = ko.observable(false);

        return function (paymentMethod) {
            quote.paymentMethod(paymentMethod);
            totals(isLoading, paymentMethod['method']);
        }
    }
);