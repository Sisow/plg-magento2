<?php
namespace Sisow\Payment\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Fee extends AbstractTotal
{
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
	
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
		parent::collect($creditmemo);
		
		$order = $creditmemo->getOrder();	
		
		$feeAmount = $order->getBaseSisowFee();
				
		if(empty($feeAmount))
			return $this;
		
		// get tax
		$feeTax = $order->getPayment()->getAdditionalInformation('sisowfeetax');
		
        if ($feeAmount > 0) {
            $FeeLeft     = $feeAmount - $order->getFeeRefunded();
            $baseFeeLeft = $feeAmount - $order->getBaseFeeRefunded();
            if ($baseFeeLeft > 0) {
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $FeeLeft);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFeeLeft);
                $creditmemo->setFee($FeeLeft);
                $creditmemo->setBaseFee($baseFeeLeft);
            }
        } else {
            $Fee     = $feeAmount;//$order->getFeeInvoiced();
            $baseFee = $feeAmount;//$order->getBaseFeeInvoiced();
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $Fee);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFee);
            $creditmemo->setFee($Fee);
            $creditmemo->setBaseFee($baseFee);
        }

        return $this;
    }
}