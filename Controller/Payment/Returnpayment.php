<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Sisow\Payment\Controller\Payment;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Session;
 
class Returnpayment extends \Magento\Framework\App\Action\Action
{		
	protected $_checkoutSession;
	public function __construct(\Magento\Framework\App\Action\Context $context,
								Session $checkoutSession)
    {
		$this->_checkoutSession = $checkoutSession;
		
        parent::__construct($context);
    }
	
    public function execute()
    {
		$orderid = $this->getRequest()->getParam('ec');
		$status = $this->getRequest()->getParam('status');
		
		$resultRedirect = $this->resultRedirectFactory->create();
		
		//Load Order
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderid);
		
		if($status == 'Success')
		{
			$this->_checkoutSession->start();
			$resultRedirect->setPath('checkout/onepage/success');
		}
		else
		{	
			$this->_getCheckoutSession()->restoreQuote();
			$this->messageManager->addNotice(__('Payment not completed'));
			$resultRedirect->setPath('checkout/cart');
		}
		return $resultRedirect;
	}
	
	/**
     * Return checkout session object
     *
     * @return Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}