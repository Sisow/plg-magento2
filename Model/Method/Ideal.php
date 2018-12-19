<?php

namespace Sisow\Payment\Model\Method;

use Magento\Framework\DataObject;

class Ideal extends AbstractSisow
{
	protected $_code = 'sisow_ideal';
	
	protected $_canUseCheckout = true;

	public function assignData(\Magento\Framework\DataObject $data)
    {
        $additionalData = $data->getAdditionalData();

        if (!is_array($data->getAdditionalData())) {
            return $this;
        }

        $additionalData = new DataObject($additionalData);

        $infoInstance = $this->getInfoInstance();

        $infoInstance->setAdditionalInformation('issuerid', $additionalData->getData('issuerid'));

		return $this;
    }
}