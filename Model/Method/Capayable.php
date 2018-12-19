<?php

namespace Sisow\Payment\Model\Method;

use Magento\Framework\DataObject;

class Capayable extends AbstractSisow
{
	protected $_code = 'sisow_capayable';
	
	protected $_canUseCheckout = true;
	protected $_canRefund = false;
	
	/**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = false;

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
        $infoInstance->setAdditionalInformation('coc', $additionalData->getData('coc'));

		return $this;
    }
	
	
}