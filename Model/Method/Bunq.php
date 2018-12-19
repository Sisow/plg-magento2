<?php

namespace Sisow\Payment\Model\Method;

class Bunq extends AbstractSisow
{
	protected $_code = 'sisow_bunq';
	
	protected $_canUseCheckout = true;
	

}