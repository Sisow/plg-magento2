<?php

namespace Sisow\Payment\Model\Method;

class Mastercard extends AbstractSisow
{
	protected $_code = 'sisow_mastercard';
	
	protected $_canUseCheckout = true;
	

}