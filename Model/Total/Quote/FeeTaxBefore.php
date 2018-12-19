<?php
namespace Sisow\Payment\Model\Total\Quote;

class FeeTaxBefore extends \Sisow\Payment\Model\Total\Quote\Fee
{
    /**
     * @param ConfigProviderAccount     $configProviderAccount
     * @param ConfigProviderBuckarooFee $configProviderBuckarooFee
     * @param Factory                   $configProviderMethodFactory
     * @param PriceCurrencyInterface    $priceCurrency
     * @param Data                      $catalogHelper
     */
    public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Helper\Data $catalogHelper
    ) {
        parent::__construct(
			$scopeConfig,
            $priceCurrency
        );
    }
    /**
     * Collect buckaroo fee related items and add them to tax calculation
     *
     * @param  \Magento\Quote\Model\Quote                          $quote
     * @param  \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param  \Magento\Quote\Model\Quote\Address\Total            $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $paymentMethod = $quote->getPayment()->getMethod();
        if (!$paymentMethod || strpos($paymentMethod, 'sisow_') !== 0) {
            return $this;
        }
        $methodInstance = $quote->getPayment()->getMethodInstance();
        if (!$methodInstance instanceof \Sisow\Payment\Model\Method\AbstractSisow) {
            return $this;
        }
        $basePaymentFee = $this->getBaseFee($methodInstance, $quote);
		
        if ($basePaymentFee < 0.01) {
            return $this;
        }
        $paymentFee = $this->priceCurrency->convert($basePaymentFee, $quote->getStore());
		
        $productTaxClassId = $this->scopeConfig->getValue('sisow/general/feetaxclass', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $address = $shippingAssignment->getShipping()->getAddress();
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $associatedTaxables = $address->getAssociatedTaxables();
        if (!$associatedTaxables) {
            $associatedTaxables = [];
        }
				
        $associatedTaxables[] = [
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => 'sisow_fee',
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => 'sisow_fee',
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $paymentFee,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $basePaymentFee,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $productTaxClassId,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => false,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE
            => \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE,
        ];
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $address->setAssociatedTaxables($associatedTaxables);
        return $this;
    }
}