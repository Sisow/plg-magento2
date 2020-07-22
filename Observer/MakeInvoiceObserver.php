<?php


namespace Sisow\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class MakeInvoiceObserver implements ObserverInterface
{
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
       $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodCode = $method->getCode();

        if ($methodCode == 'sisow_afterpay' && (bool)$this->_scopeConfig->getValue('payment/'.$methodCode.'/createinvoice', ScopeInterface::SCOPE_STORE)) {
            $ostate = $order->getState();
            $ostatus = $order->getStatus();

            $mStatus = $this->_scopeConfig->getValue('payment/'.$methodCode.'/make_invoice_on_status', ScopeInterface::SCOPE_STORE);
            if (!$mStatus  || $ostatus != $mStatus) {
                return $this;
            }

            $payment->getMethodInstance()->capture($payment,round($order->getGrandTotal(), 2));
        }
    }
}