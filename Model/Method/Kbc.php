<?php

namespace Sisow\Payment\Model\Method;

class Kbc extends AbstractSisow
{
	protected $_code = 'sisow_kbc';
	
	protected $_canUseCheckout = true;
	

}