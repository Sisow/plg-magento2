<?php
namespace Sisow\Payment\Block\Adminhtml\Sales\Order\Invoice;
class Totals extends \Magento\Framework\View\Element\Template
{
	protected $scopeConfig = null; 

    public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
		)
    {
		$this->scopeConfig = $scopeConfig;
    }
	
    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }
    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $invoice = $this->getInvoice();
        
		if($invoice->getSisowFee() > 0)
		{
			$displayfee = $this->scopeConfig->getValue('payment/general/displayfeeinctax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			
			$total = new \Magento\Framework\DataObject(
				[
					'code' => 'sisow_fee',
					'value' => $displayfee ? $invoice->getSisowFeeInclTax() : $invoice->getSisowFee(),
					'label' => __('Payment Fee'),
				]
			);
			$this->getParentBlock()->addTotalBefore($total, 'grand_total');
		}
		
        return $this;
    }
}