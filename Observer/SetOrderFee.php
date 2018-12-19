<?php

namespace Sisow\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetOrderFee implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        if ($quote->getBaseSisowFee() > 0) {
            $order->setSisowFee($quote->getSisowFee());
            $order->setBaseSisowFee($quote->getBaseSisowFee());
            $order->setSisowFeeTaxAmount($quote->getSisowFeeTaxAmount());
            $order->setSisowFeeBaseTaxAmount($quote->getSisowFeeBaseTaxAmount());
            $order->setSisowFeeInclTax($quote->getSisowFeeInclTax());
            $order->setBaseSisowFeeInclTax($quote->getBaseSisowFeeInclTax());
        }
    }
}