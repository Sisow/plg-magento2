<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sisow\Payment\Model\Total\PDF;


class Fee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
		)
    {
		$this->scopeConfig = $scopeConfig;
    }
	
	public function getTotalsForDisplay()
    {
		$displayfee = $this->scopeConfig->getValue('payment/general/displayfeeinctax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		if($displayfee)
			$amount = $this->getOrder()->getSisowFeeInclTax();
		else
			$amount = $this->getOrder()->getSisowFee();
		
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }
  
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => '€' . str_replace('.', ',', number_format($amount, 2)), 'label' => __('Payment Fee'), 'font_size' => $fontSize];
        return [$total];
    }
	
	 /**
     * Check if we can display total information in PDF
     *
     * @return bool
     */
	 
    public function canDisplay()
    {
        $amount = $this->getAmount();
        return !empty($amount) && $amount > 0;
    }
	
	/**
     * Get Total amount from source
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getOrder()->getSisowFee();
    }
}