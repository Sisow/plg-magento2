<?php
/**
 * Copyright Buckaroo 2022
 * created by Buckaroo(support@buckaroo.nl)
 */

namespace Sisow\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Session;

class Returnpayment extends Action
{
    protected $_checkoutSession;

    public function __construct(
        Context $context,
        Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    public function execute()
    {
        $status = $this->getRequest()->getParam('status');

        // validate if it is a notify/callback url
        $notify = $this->getRequest()->getParam('notify');
        $callback = $this->getRequest()->getParam('callback');

        if ($notify == 'true' || $callback == 'true') {
            exit('No notify or callback URL');
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($status == 'Success' || $status == 'Reservation' || $status == 'Open') {
            $this->_checkoutSession->start();
            $resultRedirect->setPath('checkout/onepage/success');
        } else {
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