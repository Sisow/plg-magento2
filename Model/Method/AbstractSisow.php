<?php

namespace Sisow\Payment\Model\Method;

class AbstractSisow extends \Magento\Payment\Model\Method\AbstractMethod
{
	    /**
     * Availability option
     *
     * @var bool
     */
	protected $_canAuthorize              = false;
    protected $_canCapturePartial = false;
	protected $_canCapture = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
	
	protected $_objectManager;
	
	protected $_urlBuilder;
	
	/**
     * @var OrderSender
     */
	protected $orderSender;
	
	/**
     * @var invoiceSender
     */
	protected $invoiceSender;
	
	protected $scopeConfig;
	
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
		\Magento\Framework\UrlInterface $urlBuilder,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
		\Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		//\Psr\Log\LoggerInterface $logger,
        array $data = []		
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

		$this->_urlBuilder = $urlBuilder;
		$this->_objectManager = $objectManager;
		$this->_minAmount = 0;
        $this->_maxAmount = 100000;
		
		$this->orderSender = $orderSender;
		$this->invoiceSender = $invoiceSender;
		$this->scopeConfig = $scopeConfig;
		
		$this->_logger = $logger;
    }

	public function getOrderPlaceRedirectUrl()
	{
		return $this->_urlBuilder->getUrl('sisow/payment/start');
	}

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return true;
    }
	
	public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $sisow = $this->_objectManager->create('Sisow\Payment\Model\Sisow');
		$sisow->amount = $amount;
		
		$method = $payment->getMethodInstance();
		
		if($method->getCode() == 'sisow_billink' )
		{
			$posts = array();
			$posts['tax'] = 2100;
			$posts['exclusive'] = 'false';
			$posts['description'] = 'refund';
			
			if($sisow->CreditInvoiceRequest($payment->getParentTransactionId(), $posts) < 0)
			{
			}
		}
		else
		{
			if($sisow->RefundRequest($payment->getParentTransactionId()) < 0)
			{
			}
		}
		
		
        return $this;
    }
	
	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $sisow = $this->_objectManager->create('Sisow\Payment\Model\Sisow');
		$trxid = $payment->getOrder()->getPayment()->getAdditionalInformation('trxId');
		if($sisow->InvoiceRequest($trxid) < 0)
		{

		}
		else
		{		
			$payment->setTransactionId($trxid)
			->setIsTransactionClosed(1)
			->save();
		}
        return $this;
    }
}