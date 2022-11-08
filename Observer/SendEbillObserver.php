<?php

namespace Sisow\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendEbillObserver implements ObserverInterface
{
	protected $_sisow;
	protected $_scopeConfig;
	protected $_urlInterface;
	
  public function __construct(
	\Sisow\Payment\Model\Sisow $sisow,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	\Magento\Framework\UrlInterface $urlInterface,
	\Magento\Framework\Message\ManagerInterface $messageManager
  )
  {
	$this->_sisow = $sisow;
	$this->_scopeConfig = $scopeConfig;
	$this->_urlInterface = $urlInterface;
	$this->_messageManager = $messageManager;
  }

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$order = $observer->getEvent()->getOrder();
		$payment = $order->getPayment();
		$method = $payment->getMethodInstance();
		$methodCode = $method->getCode();
		
		if($methodCode != 'sisow_overboeking' && $methodCode != 'sisow_ebill')
			return $this;

        // load correct merchant info
        $this->_sisow->loadMerchantByStoreId($order->getStoreId());
		
		// order already processed
		$trxId = $order->getPayment()->getAdditionalInformation('trxId');
		
		if(!empty($trxId))
			return $this;

		// generate billing info
		$arg = array();
		$arg['ipaddress'] = $_SERVER['REMOTE_ADDR'];
		$arg['billing_firstname'] = $order->getBillingAddress()->getFirstname();
		$arg['billing_lastname'] = $order->getBillingAddress()->getLastname();
		$arg['billing_mail'] = $order->getBillingAddress()->getEmail();
		$arg['billing_company'] = $order->getBillingAddress()->getCompany();
		$arg['billing_address1'] = $order->getBillingAddress()->getStreetLine(1);
		$arg['billing_address2'] = $order->getBillingAddress()->getStreetLine(2);
		$arg['billing_zip'] = $order->getBillingAddress()->getPostcode();
		$arg['billing_city'] = $order->getBillingAddress()->getCity();
		$arg['billing_countrycode'] = $order->getBillingAddress()->getCountryId();
		$arg['billing_phone'] = $order->getBillingAddress()->getTelephone();
		
		$days = $this->_scopeConfig->getValue('payment/'.$methodCode.'/days', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$include = $this->_scopeConfig->getValue('payment/'.$methodCode.'/include', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		$arg['including'] = $include ? 'true' : 'false';
		if($days > 0)
			$arg['days'] = $days;
		
		$testmode = $this->_scopeConfig->getValue('payment/'.$methodCode.'/testmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$arg['testmode'] = $testmode ? 'true' : 'false';

		$this->_sisow->payment = substr($methodCode, 6);
		$this->_sisow->amount = $order->getBaseGrandTotal();
		$this->_sisow->purchaseId = $order->getIncrementId();
		$this->_sisow->entranceCode = $order->getEntityId();
		$description = $this->_scopeConfig->getValue('payment/'.$methodCode.'/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$this->_sisow->description = empty($description) ? $order->getIncrementId() : $description . $order->getIncrementId();
		$this->_sisow->returnUrl = $this->_urlInterface->getBaseUrl();
		$this->_sisow->cancelUrl = $this->_urlInterface->getBaseUrl();
        $this->_sisow->notifyUrl = $this->_urlInterface->getBaseUrl() . 'sisow/payment/notify' . '?entityid=true';
        $this->_sisow->callbackUrl = $this->_sisow->notifyUrl;

		if($this->_sisow->TransactionRequest($arg) < 0)
			$this->_messageManager->addError(__("Failed to create %1!", $method->getTitle()) . " (" . $this->_sisow->errorCode . ", " . $this->_sisow->errorMessage . ")");
		else{
			$order->getPayment()->setAdditionalInformation('trxId', $this->_sisow->trxId)->save();
			
			$this->_messageManager->addSuccess(__("Buckaroo %1 created!", $method->getTitle()));
		}
		return $this;
	}
}