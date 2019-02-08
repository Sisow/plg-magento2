<?php
/**
 * Copyright © 2016 Sisow
 * created by Sisow(support@sisow.nl)
 */
namespace Sisow\Payment\Controller\Payment;
use Magento\Framework\Controller\ResultFactory;
 
class Notify  extends \Magento\Framework\App\Action\Action
{
	/**
     * @var OrderSender
     */
	protected $orderSender;
	
	/**
     * @var invoiceSender
     */
	protected $invoiceSender;
	
	protected $scopeConfig;
		
	public function __construct(\Magento\Framework\App\Action\Context $context,
								\Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
								\Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
								\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
								\Magento\Sales\Model\OrderRepository $orderRepository
								)
    {
        parent::__construct($context);
		$this->orderSender = $orderSender;
		$this->invoiceSender = $invoiceSender;
		$this->scopeConfig = $scopeConfig;
		$this->orderRepository = $orderRepository;
    }
	
    public function execute()
    {
		$orderId = $this->getRequest()->getParam('ec');
		$status = $this->getRequest()->getParam('status');
		$trxid = $this->getRequest()->getParam('trxid');
		$sha = $this->getRequest()->getParam('sha1');
		$merchantid = $this->scopeConfig->getValue('sisow/general/merchantid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$merchantkey = $this->scopeConfig->getValue('sisow/general/merchantkey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		// Validate Notify
		if(sha1($trxid . $orderId . $status . $merchantid . $merchantkey) != $sha)
			exit('Invalid Notify!');
				
		// Load Order
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
		
		// Load TrxId
		$trxidOrder = $order->getPayment()->getAdditionalInformation('trxId');
		
		if(empty($trxidOrder) && empty($trxid)){
			exit('No trxid!');
		}
		else if(!empty($trxidOrder) && $trxidOrder != $trxid && $order->getPayment()->getMethod() != 'sisow_ebill' && $order->getPayment()->getMethod() != 'sisow_overboeking' && $order->getPayment()->getMethod() != 'sisow_idealqr'){
			exit('Order id doesn\'t belong to order!');
		}
		else if($order->getPayment()->getMethod() == 'sisow_overboeking' && !empty($trxidOrder))		
		{						
			$trxid = $trxidOrder;					
		}
				
		// Load Sisow Model
		$sisow = $this->_objectManager->create('Sisow\Payment\Model\Sisow');
		
		// Execute StatusRequest
		if(($ex = $sisow->StatusRequest($trxid)) < 0)
			exit('StatusRequest failed');
				
		// Sisow status set?
		if(empty($sisow->status))
			return false;

		switch($sisow->status)
		{
			case "Cancelled":
			case "Expired":
			case "Failure":
			case "Denied":
				if($order::STATE_NEW != $order->getState())
					exit('Order already processed!');
				
				$order->registerCancellation('Order status from Sisow: ' . $sisow->status)->Save();
				break;
			case "Reversed":
				$order->registerCancellation('Order status from Sisow: ' . $sisow->status)->Save();
				break;
			case "Paid":
			case "Success":
				$payment = $order->getPayment();
				$payment->setTransactionId($trxid);
				$payment->setCurrencyCode($order->getBaseCurrencyCode());
				$payment->setPreparedMessage('Order status from Sisow: ' . $sisow->status);
				$payment->setIsTransactionClosed(1);
				$payment->registerCaptureNotification($sisow->amount, true);
				
				// get status
				$status_success = $this->scopeConfig->getValue('sisow/general/successpayment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				
				$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus($status_success);	
				$this->orderRepository->save($order);
				
				// notify customer
				if(!$order->getEmailSent()) {
					$this->orderSender->send($order);
				}

				// Sending invoice
				$invoice = $payment->getCreatedInvoice();
				if($invoice != null){
					$this->invoiceSender->send($invoice);
					$order->addStatusHistoryComment(__('You notified customer about invoice #%1.', $invoice->getIncrementId()))->setIsCustomerNotified(true);
					$this->orderRepository->save($order);
				}			

				if($order->getIsVirtual())
				{
					$order->setState($order::STATE_COMPLETE);
					$order->addStatusHistoryComment('Sisow status complete, virtual products', true);
					$this->orderRepository->save($order);
				}
				break;
			case "Reservation":
			case "Pending":
				if($order::STATE_NEW != $order->getState())
					exit('Order already processed!');
				
				$order->hold()->save();
				break;
			case "Open":
				break;
			case "Refund":
				$order->registerCancellation('Order status from Sisow: ' . $sisow->status)->Save();
				break;
			default:
				exit('Status unknown');
		}
		
		exit('Notify OK!');
	}
}