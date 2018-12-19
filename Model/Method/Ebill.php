<?php

namespace Sisow\Payment\Model\Method;

class Ebill extends AbstractSisow
{
	protected $_code = 'sisow_ebill';
	
	protected $_canUseCheckout = false;
	
	public function getOrderPlaceRedirectUrl()
	{
		return '';
	}
}