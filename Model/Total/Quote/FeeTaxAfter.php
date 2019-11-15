<?php

namespace Sisow\Payment\Model\Total\Quote;

use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class FeeTaxAfter extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
	public function __construct(){
		$this->setCode('tax_sisow_fee');
	}
	
    /**
     * Collect sisow fee tax totals
     *
     * @param \Magento\Quote\Model\Quote                          $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total            $total
     *
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $total->setSisowFeeInclTax(0);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $total->setBaseSisowFeeInclTax(0);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $total->setSisowFeeTaxAmount(0);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $total->setSisowFeeBaseTaxAmount(0);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }
		
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $extraTaxableDetails = $total->getExtraTaxableDetails();
        if (empty($extraTaxableDetails['sisow_fee'])) {
            return $this;
        }
        $itemTaxDetails = $extraTaxableDetails['sisow_fee'];
        if (empty($itemTaxDetails[CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE][0])) {
            return $this;
        }

        $sisowFeeTaxDetails = $itemTaxDetails[CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE][0];
        $sisowFeeBaseTaxAmount = $sisowFeeTaxDetails['base_row_tax'];
        $sisowFeeTaxAmount = $sisowFeeTaxDetails['row_tax'];
        $sisowFeeInclTax = $sisowFeeTaxDetails['price_incl_tax'];
        $sisowFeeBaseInclTax = $sisowFeeTaxDetails['base_price_incl_tax'];
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $total->setSisowFeeInclTax($sisowFeeInclTax);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $total->setBaseSisowFeeInclTax($sisowFeeBaseInclTax);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $total->setSisowFeeBaseTaxAmount($sisowFeeBaseTaxAmount);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $total->setSisowFeeTaxAmount($sisowFeeTaxAmount);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $quote->setSisowFeeInclTax($sisowFeeInclTax);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $quote->setBaseSisowFeeInclTax($sisowFeeBaseInclTax);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $quote->setSisowFeeBaseTaxAmount($sisowFeeBaseTaxAmount);
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $quote->setSisowFeeTaxAmount($sisowFeeTaxAmount);
        return $this;
    }
    /**
     * Assign Sisow fee tax totals and labels to address object
     *
     * @param \Magento\Quote\Model\Quote               $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     *
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        return [
            'code' => 'sisow_fee',
            'title' => $this->getLabel(),
            'sisow_fee' => $total->getSisowFee(),
            'base_sisow_fee' => $total->getBaseSisowFee(),
            'sisow_fee_incl_tax' => $total->getSisowFeeInclTax(),
            'base_sisow_fee_incl_tax' => $total->getBaseSisowFeeInclTax(),
            'sisow_fee_tax_amount' => $total->getSisowFeeTaxAmount(),
            'sisow_fee_base_tax_amount' => $total->getSisowFeeBaseTaxAmount(),
        ];
    }
    /**
     * Get Sisow label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Payment Fee');
    }
}