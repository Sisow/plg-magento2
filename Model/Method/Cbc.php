<?php

namespace Sisow\Payment\Model\Method;

class Cbc extends AbstractSisow
{
	protected $_code = 'sisow_cbc';
	
	protected $_canUseCheckout = true;
	

}