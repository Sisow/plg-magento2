<?php

namespace Sisow\Payment\Model\Method;

use Magento\Framework\DataObject;

class Eps extends AbstractSisow
{
	protected $_code = 'sisow_eps';
	
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

        $infoInstance->setAdditionalInformation('bic', $additionalData->getData('bic'));

		return $this;
    }
}