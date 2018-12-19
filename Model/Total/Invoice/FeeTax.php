<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sisow\Payment\Model\Total\Invoice;


class FeeTax extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
       /**
     * Collect Sisow fee tax totals
     *
     * @param  \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $SisowFeeTaxAmountLeft = $order->getSisowFeeTaxAmount() - $order->getSisowFeeTaxAmountInvoiced();
        $baseSisowFeeTaxAmountLeft = $order->getSisowFeeBaseTaxAmount()
            - $order->getSisowFeeBaseTaxAmountInvoiced();
        $SisowFeeInclTaxLeft = $order->getSisowFeeInclTax() - $order->getSisowFeeInclTaxInvoiced();
        $baseSisowFeeInclTaxLeft = $order->getBaseSisowFeeInclTax() - $order->getBaseSisowFeeInclTaxInvoiced();
        if ($order->getSisowFeeBaseTaxAmount() && $baseSisowFeeTaxAmountLeft > 0) {
            if ($baseSisowFeeTaxAmountLeft < $invoice->getBaseGrandTotal()) {
                $invoice->setGrandTotal($invoice->getGrandTotal() + $SisowFeeTaxAmountLeft);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseSisowFeeTaxAmountLeft);
            } else {
                $SisowFeeTaxAmountLeft = $invoice->getTaxAmount();
                $baseSisowFeeTaxAmountLeft = $invoice->getBaseTaxAmount();
                $invoice->setGrandTotal(0);
                $invoice->setBaseGrandTotal(0);
            }
            $invoice->setTaxAmount($invoice->getTaxAmount() + $SisowFeeTaxAmountLeft);
            $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $baseSisowFeeTaxAmountLeft);
            $invoice->setSisowFeeTaxAmount($SisowFeeTaxAmountLeft);
            $invoice->setSisowFeeBaseTaxAmount($baseSisowFeeTaxAmountLeft);
            $invoice->setSisowFeeInclTax($SisowFeeInclTaxLeft);
            $invoice->setBaseSisowFeeInclTax($baseSisowFeeInclTaxLeft);
        }
        return $this;
    }
}