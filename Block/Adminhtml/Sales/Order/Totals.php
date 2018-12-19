<?php
namespace Sisow\Payment\Block\Adminhtml\Sales\Order;
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
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }
    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }
    /**
     * @return $this
     */
    public function initTotals()
    {
        $order = $this->getOrder();
        
		if($order->getSisowFee() > 0)
		{
			$displayfee = $this->scopeConfig->getValue('payment/general/displayfeeinctax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			
			$total = new \Magento\Framework\DataObject(
				[
					'code' => 'sisow_fee',
					'value' => $displayfee ? $order->getSisowFeeInclTax() : $order->getSisowFee(),
					'label' => __('Payment Fee'),
				]
			);
			$this->getParentBlock()->addTotalBefore($total, 'grand_total');
		}
		
        return $this;
    }
}