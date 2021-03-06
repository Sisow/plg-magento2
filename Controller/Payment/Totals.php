<?php
/**
 * Copyright Sisow 2016
 * created by Sisow(support@sisow.nl)
 */
namespace Sisow\Payment\Controller\Payment;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
class Totals extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJson;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_helper;

    //BigBridge Add TotalsCollector so the shippingmethod costs can actually be added to the quote when the payment method changes.
    protected $totalsCollector;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_resultJson = $resultJson;
        $this->quoteRepository = $quoteRepository;
        $this->totalsCollector = $totalsCollector;
    }
    /**
     * Trigger to re-calculate the collect Totals
     *
     * @return bool
     */
    public function execute()
    {
        $response = [
            'errors' => false,
            'message' => 'Re-calculate successful.'
        ];
        try {
            $this->quoteRepository->get($this->_checkoutSession->getQuoteId());
            $quote = $this->_checkoutSession->getQuote();
            //Trigger to re-calculate totals
            $payment = $this->_helper->jsonDecode($this->getRequest()->getContent());
            $this->_checkoutSession->getQuote()->getPayment()->setMethod($payment['payment']);
            $this->totalsCollector->collectAddressTotals($quote, $quote->getShippingAddress());
            $this->quoteRepository->save($quote->collectTotals());
            //@deprecated save collect totals function
            //$this->_checkoutSession->getQuote()->collectTotals()->save();
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultJson = $this->_resultJson->create();
        return $resultJson->setData($response);
    }
}