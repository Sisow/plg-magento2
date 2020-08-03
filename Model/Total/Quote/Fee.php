<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sisow\Payment\Model\Total\Quote;


use Magento\Store\Model\ScopeInterface;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
   /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $scopeConfig = null;

    protected $priceCurrency = null;

    public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
		)
    {
		$this->setCode('sisow_fee');
		
		$this->scopeConfig = $scopeConfig;
		$this->priceCurrency = $priceCurrency;
    }
	
	public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) 
	{	
        parent::collect($quote, $shippingAssignment, $total);
				
		$paymentMethod = $quote->getPayment()->getMethod();

        // set fee to zero
        $total->setSisowFee(0);
        $total->setBaseSisowFee(0);

        $quote->setSisowFee(0);
        $quote->setBaseSisowFee(0);

        if (!$paymentMethod || strpos($paymentMethod, 'sisow_') !== 0) {
            return $this;
        }

        $methodInstance = $quote->getPayment()->getMethodInstance();
        if (!$methodInstance instanceof \Sisow\Payment\Model\Method\AbstractSisow) {
            return $this;
        }

		$baseFeeAmount = $this->getBaseFee($methodInstance, $quote);

        /* nodig voor 2.1.9 */
        //$total->setTotalAmount('fee', 0);
        //$total->setBaseTotalAmount('fee', 0);

		if($baseFeeAmount <= 0){
			return $this;
		}
		
		$feeAmount = $this->priceCurrency->convert($baseFeeAmount, ScopeInterface::SCOPE_STORE);

		$quote->setSisowFee($feeAmount);
		$quote->setBaseSisowFee($baseFeeAmount);

		/* nodig voor 2.1.9 */
        //$total->setTotalAmount('fee', $feeAmount);
        //$total->setBaseTotalAmount('fee', $baseFeeAmount);

		$total->setSisowFee($feeAmount);
		$total->setBaseSisowFee($baseFeeAmount);
		
		$total->setBaseGrandTotal($total->getBaseGrandTotal() + $baseFeeAmount);
		$total->setGrandTotal($total->getGrandTotal() + $feeAmount);

        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {						
        return [
            'code' => 'sisow_fee',
            'title' => 'Payment Fee',
            'value' => $this->scopeConfig->getValue('payment/general/displayfeeinctax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $total->getSisowFeeInclTax() : $total->getSisowFee()
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Payment Fee');
    }

    /**
     * @param \Sisow\Payment\Model\Method\AbstractSisow $methodInstance
     * @param \Magento\Quote\Model\Quote $quote
     * @return float|int
     */
	public function getBaseFee(
        \Sisow\Payment\Model\Method\AbstractSisow $methodInstance,
        \Magento\Quote\Model\Quote $quote
    ) {
		$baseFeeAmount = 0.00;
		$feeSetting = $this->scopeConfig->getValue('payment/' . $quote->getPayment()->getMethod() . '/paymentfee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		// get total inc tax
		if ($quote->getShippingAddress()) {
            $address = $quote->getShippingAddress();
        } else {
            $address = $quote->getBillingAddress();
        }
		
		// get total
		$quoteTotal = $address->getBaseSubtotalTotalInclTax();

		// validate if there are fee settings, if there are calc fee
		if(!empty($feeSetting)){
			foreach(explode(';', $feeSetting) as $singleFee)
			{
				if($singleFee < 0)
				{
					$baseFeeAmount += round(($quoteTotal * ($singleFee * -1)) / 100, 2);
				}
				else if($singleFee > 0)
				{
					$baseFeeAmount += $singleFee;
				}
			}
			
			$baseFeeAmount = round($baseFeeAmount, 2);
		}
		
		return $baseFeeAmount;
	}
}