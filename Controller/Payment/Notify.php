<?php
/**
 * Copyright � 2016 Sisow
 * created by Sisow(support@sisow.nl)
 */
namespace Sisow\Payment\Controller\Payment;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\ScopeInterface;
use Sisow\Payment\Model\Sisow;

class Notify  extends Action
{
	/**
     * @var OrderSender
     */
	private $orderSender;
	
	/**
     * @var invoiceSender
     */
    private $invoiceSender;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Sisow
     */
    private $sisow;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Notify constructor.
     * @param Context $context
     * @param OrderSender $orderSender
     * @param InvoiceSender $invoiceSender
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderRepository $orderRepository
     * @param Sisow $sisow
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
	public function __construct(Context $context,
                                OrderSender $orderSender,
                                InvoiceSender $invoiceSender,
                                ScopeConfigInterface $scopeConfig,
                                OrderRepository $orderRepository,
                                Sisow $sisow,
                                SearchCriteriaBuilder $searchCriteriaBuilder
                                )
    {
        parent::__construct($context);
		$this->orderSender = $orderSender;
		$this->invoiceSender = $invoiceSender;
		$this->scopeConfig = $scopeConfig;
		$this->orderRepository = $orderRepository;
		$this->sisow = $sisow;
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }
	
    public function execute()
    {
		$orderId = $this->getRequest()->getParam('ec');
		$status = $this->getRequest()->getParam('status');
		$trxid = $this->getRequest()->getParam('trxid');
		$sha = $this->getRequest()->getParam('sha1');
        $loadByEntityId = $this->getRequest()->getParam('entityid') == 'true';
		$merchantid = $this->scopeConfig->getValue('sisow/general/merchantid', ScopeInterface::SCOPE_STORE);
		$merchantkey = $this->scopeConfig->getValue('sisow/general/merchantkey', ScopeInterface::SCOPE_STORE);
		
		// Validate Notify
		if(sha1($trxid . $orderId . $status . $merchantid . $merchantkey) != $sha) {
            exit('Invalid Notify!');
        }

        $order = null;
		// Load Order

        if($loadByEntityId) {
            try {
                $order = $this->orderRepository->get($orderId);
            } catch (InputException $e) {
                exit('Error while loading order!');
            } catch (NoSuchEntityException $e) {
                echo 'Error while loading order! Try fallback....';
            }
        }

        if (!$loadByEntityId || empty($order)) { // fallback if not found by entity_id
            try {
                $this->searchCriteriaBuilder->addFilter('increment_id', $orderId);
                $orders = $this->orderRepository->getList($this->searchCriteriaBuilder->create());

                if($orders->getTotalCount() != 1){
                    exit('Number of orders found: ' .$orders->getTotalCount());
                }else{
                    $orderArray = $orders->getItems();
                    $order = reset($orderArray);
                }
            } catch (InputException $e) {
                exit('Error while loading order!');
            } catch (NoSuchEntityException $e) {
                exit('Error while loading order!');
            }
        }

		// validate if order is loaded
        if (empty($order->getId())) {
            exit('No order loaded');
        }


        // Load TrxId
		$trxidOrder = $order->getPayment()->getAdditionalInformation('trxId');
		
		if(empty($trxidOrder) && empty($trxid)){
			exit('No trxid!');
		}
		else if($order->getPayment()->getMethod() == 'sisow_overboeking' && !empty($trxidOrder))		
		{						
			$trxid = $trxidOrder;					
		}

		// Execute StatusRequest
		if(($ex = $this->sisow->StatusRequest($trxid)) < 0)
			exit('StatusRequest failed');
				
		// Sisow status set?
		if(empty($this->sisow->status)) {
            return exit('No sisow status');
        }

		switch($this->sisow->status)
		{
			case "Cancelled":
			case "Expired":
			case "Failure":
			case "Denied":
				if($order::STATE_NEW != $order->getState())
					exit('Order already processed!');

                $order->cancel();
                $order->addCommentToStatusHistory('Order status from Sisow: ' . $this->sisow->status);
                $this->orderRepository->save($order);
				break;
			case "Reversed":
            case "Refund":
                $order->cancel();
                $order->addCommentToStatusHistory('Order status from Sisow: ' . $this->sisow->status);
                $this->orderRepository->save($order);
				break;
			case "Paid":
			case "Success":
			    $orderstate = $order->getState();
			    if (Order::STATE_PROCESSING == $orderstate || Order::STATE_COMPLETE == $orderstate)
                    exit('Order already processed!');

				$amount = $this->sisow->amount;
				
				if($order->getPayment()->getMethod() == 'sisow_vvv'){
					$amount = $order->getGrandTotal();
				}
				
				$payment = $order->getPayment();
				$payment->setTransactionId($trxid);
				$payment->setCurrencyCode($order->getOrderCurrencyCode());
				$payment->setPreparedMessage('Order status from Sisow: ' . $this->sisow->status);
				$payment->setIsTransactionClosed(1);
				$payment->registerCaptureNotification($amount, true);
				
				// get status
				$status_success = $this->scopeConfig->getValue('sisow/general/successpayment', ScopeInterface::SCOPE_STORE);
				
				$order->setState(Order::STATE_PROCESSING)->setStatus($status_success);
				$this->orderRepository->save($order);
				
				// notify customer
				if(!$order->getEmailSent()) {
					$this->orderSender->send($order);
				}

				// Sending invoice
				$invoice = $payment->getCreatedInvoice();
				if($invoice != null && !$invoice->getEmailSent()){
				    try {
                        $this->invoiceSender->send($invoice);
                        $order->addCommentToStatusHistory(__('You notified customer about invoice #%1.', $invoice->getIncrementId()))->setIsCustomerNotified(true);
                        $this->orderRepository->save($order);
                    }catch (Exception $ex){}
				}			

				if($order->getIsVirtual())
				{
					$order->setState($order::STATE_COMPLETE);
					$order->addCommentToStatusHistory('Sisow status complete, virtual products', true);
					$this->orderRepository->save($order);
				}
				break;
			case "Reservation":
			case "Pending":
				if($order::STATE_NEW != $order->getState())
					exit('Order already processed!');

                $payment = $order->getPayment();
                $payment->setTransactionId($trxid);
                $payment->setCurrencyCode($order->getOrderCurrencyCode());
                $payment->setPreparedMessage('Order status from Sisow: ' . $this->sisow->status);
                $payment->setIsTransactionClosed(false);
                $payment->registerAuthorizationNotification($this->sisow->amount);
				
				$order->hold()->save();
				break;
			case "Open":
				break;
			default:
				exit('Status unknown');
		}
		
		exit('Notify OK!');
	}
}