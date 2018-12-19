<?php

namespace Sisow\Payment\Model\Method;

use Magento\Framework\DataObject;

class Klarna extends AbstractSisow
{
	protected $_code = 'sisow_klarna';
	
	protected $_canUseCheckout = true;
	protected $_canRefund = false;
		
	public function assignData(\Magento\Framework\DataObject $data)
    {		
		$additionalData = $data->getAdditionalData();

        if (!is_array($data->getAdditionalData())) {
            return $this;
        }
	
        $additionalData = new DataObject($additionalData);

        $infoInstance = $this->getInfoInstance();

        $infoInstance->setAdditionalInformation('gender', $additionalData->getData('gender'));
        $infoInstance->setAdditionalInformation('dob', $additionalData->getData('day') . $additionalData->getData('month') . $additionalData->getData('year'));

		return $this;
    }
	
	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $sisow = $this->_objectManager->create('Sisow\Payment\Model\Sisow');
		
		if($sisow->InvoiceRequest($payment->getParentTransactionId()) < 0)
		{
			throw new \Magento\Framework\Exception\LocalizedException(
                'Sisow Focum Create Invoice failed: ' . $ex
            );
		}

        return $this;
    }
}