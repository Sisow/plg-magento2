<?php

namespace Sisow\Payment\Model\Method;

use Magento\Framework\DataObject;

class Billink extends AbstractSisow
{
	protected $_code = 'sisow_billink';

    protected $_sisowCreditRefund = true;
	protected $_canUseCheckout = true;
	
	/**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;
		
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
        $infoInstance->setAdditionalInformation('coc', $additionalData->getData('coc'));
		$infoInstance->setAdditionalInformation('phone', $additionalData->getData('phone'));

		return $this;
    }
	
	
}