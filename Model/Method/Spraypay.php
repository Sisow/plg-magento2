<?php

namespace Sisow\Payment\Model\Method;

class Spraypay extends AbstractSisow
{
    protected $_code = 'sisow_spraypay';

    protected $_canUseCheckout = true;
}