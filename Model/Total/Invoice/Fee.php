<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sisow\Payment\Model\Total\Invoice;


class Fee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
   /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null; 
    protected $scopeConfig = null; 
	protected $taxCalculation = null; 

    public function __construct(
		\Magento\Quote\Model\QuoteValidator $quoteValidator,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Tax\Model\Calculation $taxCalculation)
    {
        $this->quoteValidator = $quoteValidator;
		$this->scopeConfig = $scopeConfig;
		$this->taxCalculation = $taxCalculation;
    }
	
	public function collect(
        \Magento\Sales\Model\Order\Invoice $invoice
    ) 
	{
		$order = $invoice->getOrder();
		
		$SisowFeeLeft = $order->getSisowFee() - $order->getSisowFeeInvoiced();
		$baseSisowFeeLeft = $order->getBaseSisowFee() - $order->getBaseSisowFeeInvoiced();
		
		if ($order->getBaseSisowFee() && $baseSisowFeeLeft > 0) {
            if ($baseSisowFeeLeft < $invoice->getBaseGrandTotal()) {
                $invoice->setGrandTotal($invoice->getGrandTotal() + $SisowFeeLeft);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseSisowFeeLeft);
            } else {
                $SisowFeeLeft = $invoice->getGrandTotal();
                $baseSisowFeeLeft = $invoice->getBaseGrandTotal();
                $invoice->setGrandTotal(0);
                $invoice->setBaseGrandTotal(0);
            }

            $invoice->setSisowFee($SisowFeeLeft);
            $invoice->setBaseSisowFee($baseSisowFeeLeft);
        }
		
        return $this;
    } 
}