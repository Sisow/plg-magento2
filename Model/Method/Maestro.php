<?php

namespace Sisow\Payment\Model\Method;

class Maestro extends AbstractSisow
{
	protected $_code = 'sisow_maestro';
	
	protected $_canUseCheckout = true;
	

}