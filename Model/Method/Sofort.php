<?php

namespace Sisow\Payment\Model\Method;

class Sofort extends AbstractSisow
{
	protected $_code = 'sisow_sofort';
	
	protected $_canUseCheckout = true;
	

}