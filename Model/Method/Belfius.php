<?php

namespace Sisow\Payment\Model\Method;

class Belfius extends AbstractSisow
{
	protected $_code = 'sisow_belfius';
	
	protected $_canUseCheckout = true;
	

}