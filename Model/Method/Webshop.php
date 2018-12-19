<?php

namespace Sisow\Payment\Model\Method;

class Webshop extends AbstractSisow
{
	protected $_code = 'sisow_webshop';
	
	protected $_canUseCheckout = true;
	protected $_canRefund = false;

}