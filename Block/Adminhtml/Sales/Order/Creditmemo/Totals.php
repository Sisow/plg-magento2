<?php
namespace Sisow\Payment\Block\Adminhtml\Sales\Order\Creditmemo;
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
    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $creditMemo = $this->getCreditmemo();
        
		if($creditMemo->getSisowFee() > 0)
		{
			$displayfee = $this->scopeConfig->getValue('payment/general/displayfeeinctax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			
			$total = new \Magento\Framework\DataObject(
				[
					'code' => 'sisow_fee',
					'value' => $displayfee ? $creditMemo->getSisowFeeInclTax() : $creditMemo->getSisowFee(),
					'label' => __('Payment Fee'),
				]
			);
			$this->getParentBlock()->addTotalBefore($total, 'grand_total');
		}
		
        return $this;
    }
}