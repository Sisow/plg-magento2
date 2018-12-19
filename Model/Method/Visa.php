<?php

namespace Sisow\Payment\Model\Method;

class Visa extends AbstractSisow
{
	protected $_code = 'sisow_visa';
	
	protected $_canUseCheckout = true;
	

}