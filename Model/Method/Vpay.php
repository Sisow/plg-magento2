<?php

namespace Sisow\Payment\Model\Method;

class Vpay extends AbstractSisow
{
	protected $_code = 'sisow_vpay';
	
	protected $_canUseCheckout = true;
	

}