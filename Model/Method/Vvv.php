<?php

namespace Sisow\Payment\Model\Method;

class Vvv extends AbstractSisow
{
	protected $_code = 'sisow_vvv';
	
	protected $_canUseCheckout = true;
	protected $_canRefund = false;

}