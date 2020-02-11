<?php
namespace Sisow\Payment\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class SisowHandler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/sisow.log';
}