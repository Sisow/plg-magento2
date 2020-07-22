<?php
namespace Sisow\Payment\Model\Total\Quote;

use Magento\Catalog\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Sisow\Payment\Model\Method\AbstractSisow;

class FeeTaxBefore extends \Sisow\Payment\Model\Total\Quote\Fee
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $catalogHelper
     */
    public function __construct(
		ScopeConfigInterface $scopeConfig,
        PriceCurrencyInterface $priceCurrency,
        Data $catalogHelper
    ) {
        parent::__construct(
			$scopeConfig,
            $priceCurrency
        );
    }
    /**
     * Collect Sisow fee related items and add them to tax calculation
     *
     * @param  Quote                          $quote
     * @param  ShippingAssignmentInterface $shippingAssignment
     * @param  Total            $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $paymentMethod = $quote->getPayment()->getMethod();
        if (!$paymentMethod || strpos($paymentMethod, 'sisow_') !== 0) {
            return $this;
        }
        $methodInstance = $quote->getPayment()->getMethodInstance();
        if (!$methodInstance instanceof AbstractSisow) {
            return $this;
        }
        $basePaymentFee = $this->getBaseFee($methodInstance, $quote);
		
        if ($basePaymentFee < 0.01) {
            return $this;
        }
        $paymentFee = $this->priceCurrency->convert($basePaymentFee, ScopeInterface::SCOPE_STORE);
		
        $productTaxClassId = $this->scopeConfig->getValue('sisow/general/feetaxclass', ScopeInterface::SCOPE_STORE);
        $address = $shippingAssignment->getShipping()->getAddress();
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $associatedTaxables = $address->getAssociatedTaxables();
        if (!$associatedTaxables) {
            $associatedTaxables = [];
        }
				
        $associatedTaxables[] = [
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => 'sisow_fee',
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => 'sisow_fee',
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $paymentFee,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $basePaymentFee,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $productTaxClassId,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => false,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE
            => CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE,
        ];
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $address->setAssociatedTaxables($associatedTaxables);
        return $this;
    }
}