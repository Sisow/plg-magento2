/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Sisow_Payment/js/view/checkout/summary/fee',
		'Magento_Checkout/js/model/quote',
		'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, totals) {
        'use strict';

        return Component.extend({
			totals: quote.getTotals(),
            /**
             * @override
             */
            isDisplayed: function () {
                if (this.totals()) {
                    if(totals.getSegment('sisow_fee') != null && totals.getSegment('sisow_fee').value > 0)
                        return true;
                }
                return false;
            },
			
			isAvailable: function() {
                if (this.totals()) {
					if(totals.getSegment('sisow_fee') == null || totals.getSegment('sisow_fee').value <= 0)
						return false;
                }
                return true;
			}
        });
    }
);