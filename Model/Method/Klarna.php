<?php

namespace Sisow\Payment\Model\Method;

use Magento\Framework\DataObject;

class Klarna extends AbstractSisow
{
	protected $_code = 'sisow_klarna';
	
	protected $_canUseCheckout = true;
    protected $_sisowCreditRefund = true;

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
}