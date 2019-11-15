<?php

namespace Sisow\Payment\Model\Method;

use Magento\Framework\DataObject;

class Klarna extends AbstractSisow
{
	protected $_code = 'sisow_klarna';
	
	protected $_canUseCheckout = true;
	protected $_canRefund = false;
}