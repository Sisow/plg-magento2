<?php

namespace Sisow\Payment\Model\Method;

class Homepay extends AbstractSisow
{
	protected $_code = 'sisow_homepay';
	
	protected $_canUseCheckout = true;
	protected $_canRefund = false;
	

}