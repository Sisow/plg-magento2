<?php

namespace Sisow\Payment\Model\Method;

use Magento\Framework\DataObject;

class Focum extends AbstractSisow
{
	protected $_code = 'sisow_focum';
	
	protected $_canUseCheckout = true;
	protected $_canRefund = false;
	
	/**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
	 protected $_canCapturePartial = false;
		
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
        $infoInstance->setAdditionalInformation('iban', $additionalData->getData('iban'));

		return $this;
    }
}