<?php

namespace Sisow\Payment\Model\Method;

class Overboeking extends AbstractSisow
{
	protected $_code = 'sisow_overboeking';
	
	protected $_canUseCheckout = true;
	
	public function getOrderPlaceRedirectUrl()
	{
		return '';
	}
}