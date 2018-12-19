<?php

namespace Sisow\Payment\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Config\Source\Order\Status;

class SuccessPayment extends Status
{
    protected $_stateStatuses = [Order::STATE_PROCESSING];
}