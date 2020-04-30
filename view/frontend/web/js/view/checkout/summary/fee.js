define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Sisow_Payment/checkout/summary/fee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                return this.isFullMode() && this.getPureValue() !== 0;
            },
            getValue: function() {
                var price = 0;
                if (this.totals()) {
					if(totals.getSegment('sisow_fee') == null)
						return price;
                    price = totals.getSegment('sisow_fee').value;
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function() {
                var price = 0;
                if (this.totals()) {
                    if(totals.getSegment('sisow_fee') == null)
                        return price;
                    price = this.totals().base_fee;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            },
            getPureValue: function() {
                var price = 0;
                if (this.totals()) {
                    if(totals.getSegment('sisow_fee') == null)
                        return price;
                    price = totals.getSegment('sisow_fee').value;
                }
                return price;
            }
        });
    }
);