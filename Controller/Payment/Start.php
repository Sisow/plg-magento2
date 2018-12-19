<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Sisow\Payment\Controller\Payment;
use Magento\Framework\Controller\ResultFactory;
use Magento\Payment\Helper\Data as PaymentHelper;
 
class Start  extends \Magento\Framework\App\Action\Action
{
	private $arg;
	
	/**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
	
	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
	
	/**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
	
	/**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote = false;
	
	/** 
	var PaymentHelper 
	*/ 
	protected $_paymentHelper; 

	
	/**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		PaymentHelper $paymentHelper,
		\Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
		\Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
		\Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
		\Magento\Tax\Model\Calculation $taxCalculation,
		\Magento\Sales\Model\Service\InvoiceService $invoiceService
    ) {
		$this->orderSender = $orderSender;
		$this->invoiceSender = $invoiceSender;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
		$this->scopeConfig = $scopeConfig;
		$this->_paymentHelper = $paymentHelper;
		$this->_transactionBuilder = $transactionBuilder;
		$this->_taxCalculation = $taxCalculation;
		$this->_invoiceService = $invoiceService;
		parent::__construct($context);
    }
	
	
    /**
     * say hello text
     */
    public function execute()
    {		
		$discountTax = 0;
		$orderid = $this->checkoutSession->getLastRealOrderId();
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderid);
		$code = $order->getPayment()->getMethodInstance()->getCode();
		$payment = substr($code, 6);
		
		if($payment == 'overboeking'){
			$this->getResponse()->setRedirect($this->_url->getUrl('checkout/onepage/success'));
			return;
		}
		
		$this->arg = array();
		$this->arg['ipaddress'] = $_SERVER['REMOTE_ADDR'];
		$this->arg['billing_firstname'] = $order->getBillingAddress()->getFirstname();
		if($payment == 'afterpay' && !empty($this->arg['billing_firstname']) && strlen($this->arg['billing_firstname']) > 1)
			$this->arg['billing_firstname'] = substr($this->arg['billing_firstname'], 0, 1);
		$this->arg['billing_lastname'] = $order->getBillingAddress()->getLastname();
		$this->arg['billing_mail'] = $order->getBillingAddress()->getEmail();
		$this->arg['billing_company'] = $order->getBillingAddress()->getCompany();
		$this->arg['billing_address1'] = $order->getBillingAddress()->getStreetLine(1);
		$this->arg['billing_address2'] = $order->getBillingAddress()->getStreetLine(2);
		$this->arg['billing_zip'] = $order->getBillingAddress()->getPostcode();
		$this->arg['billing_city'] = $order->getBillingAddress()->getCity();
		$this->arg['billing_countrycode'] = $order->getBillingAddress()->getCountryId();
		$this->arg['billing_phone'] = $order->getBillingAddress()->getTelephone();
		
		$shipping = empty($order->getShippingAddress()) ? $order->getBillingAddress() : $order->getShippingAddress();
		
		$this->arg['shipping_firstname'] = $shipping->getFirstname();
		if($payment == 'afterpay' && !empty($this->arg['shipping_firstname']) && strlen($this->arg['shipping_firstname']) > 1)
			$this->arg['shipping_firstname'] = substr($this->arg['shipping_firstname'], 0, 1);
		$this->arg['shipping_lastname'] = $shipping->getLastname();
		$this->arg['shipping_mail'] = $shipping->getEmail();
		$this->arg['shipping_company'] = $shipping->getCompany();
		$this->arg['shipping_address1'] = $shipping->getStreetLine(1);
		$this->arg['shipping_address2'] = $shipping->getStreetLine(2);
		$this->arg['shipping_zip'] = $shipping->getPostcode();
		$this->arg['shipping_city'] = $shipping->getCity();
		$this->arg['shipping_countrycode'] = $shipping->getCountryId();
		$this->arg['shipping_phone'] = $shipping->getTelephone();
		
		$i = 1;
		foreach($order->getAllVisibleItems() as $item)
		{			
			$this->arg['product_id_' . $i] = $item->getSku();
			$this->arg['product_description_' . $i] = $item->getName();
			$this->arg['product_quantity_' . $i] = (int)($item->getQtyOrdered() ? $item->getQtyOrdered() : $item->getQty());
			$this->arg['product_tax_' . $i] = round(($item->getRowTotalInclTax() - $item->getRowTotal()) * 100, 0);
			$this->arg['product_netprice_' . $i] = round($item->getPrice() * 100, 0);
			$this->arg['product_nettotal_' . $i] = round($item->getRowTotal() * 100, 0);
			$this->arg['product_total_' . $i] = round($item->getRowTotalInclTax() * 100, 0);
						
			if ($item->getParentItem()) {
				$product = $item->getParentItem()->getProduct();
			} else {
				$product = $item->getProduct();
			}
			
			if($product->getTypeId() == 'bundle' && $this->arg['product_tax_' . $i] > 0)
				$this->arg['product_taxrate_' . $i] = (round(($this->arg['product_total_' . $i] * 100) / $this->arg['product_nettotal_' . $i]) - 100) * 100;
			else
				$this->arg['product_taxrate_' . $i] = round($item->getTaxPercent() * 100, 0);

			$i++;
			
			$discountTax += ($item->getRowTotalInclTax() - $item->getRowTotal()) - $item->getTaxAmount();
		}
				
		$shipping = $order->getShippingAmount();
		if ($shipping > 0) {
			$shiptax = $shipping + $order->getShippingTaxAmount();
			$this->arg['product_id_' . $i] = 'shipping';
			$this->arg['product_description_' . $i] = 'Verzendkosten';
			$this->arg['product_quantity_' . $i] = 1;
			$this->arg['product_weight_' . $i] = 0;
			$this->arg['product_tax_' . $i] = round($order->getShippingTaxAmount() * 100, 0);
			$this->arg['product_netprice_' . $i] = round($shipping * 100, 0);
			$this->arg['product_nettotal_' . $i] = round($shipping * 100, 0);
			$this->arg['product_total_' . $i] = round($shiptax * 100, 0);
			$this->arg['product_taxrate_' . $i] = round((($this->arg['product_total_' . $i] / $this->arg['product_nettotal_' . $i]) -1) * 100) * 100;
			
			$i++;
		}
		
		$giftCardsAmount = $order->getGiftCardsAmount();
		if ($giftCardsAmount > 0) {
			$giftCardsAmount = -1 * $giftCardsAmount;
			$this->arg['product_id_' . $i] = 'giftcard';
			$this->arg['product_description_' . $i] = 'Gift Card';
			$this->arg['product_quantity_' . $i] = 1;
			$this->arg['product_weight_' . $i] = 0;
			$this->arg['product_tax_' . $i] = round(0 * 100, 0);
			$this->arg['product_taxrate_' . $i] = round(0 * 100, 0);
			$this->arg['product_netprice_' . $i] = round($giftCardsAmount * 100, 0);
			$this->arg['product_price_' . $i] = round($giftCardsAmount * 100, 0);
			$this->arg['product_nettotal_' . $i] = round($giftCardsAmount * 100, 0);
			$this->arg['product_total_' . $i] = round($giftCardsAmount * 100, 0);
			$i++;
		}
		
		$rewardCurrency = $order->getRewardCurrencyAmount();
		if ($rewardCurrency > 0) {
			$rewardCurrency = -1 * $rewardCurrency;
			$this->arg['product_id_' . $i] = 'rewardpoints';
			$this->arg['product_description_' . $i] = 'Reward points';
			$this->arg['product_quantity_' . $i] = 1;
			$this->arg['product_weight_' . $i] = 0;
			$this->arg['product_tax_' . $i] = round(0 * 100, 0);
			$this->arg['product_taxrate_' . $i] = round(0 * 100, 0);
			$this->arg['product_netprice_' . $i] = round($rewardCurrency * 100, 0);
			$this->arg['product_price_' . $i] = round($rewardCurrency * 100, 0);
			$this->arg['product_nettotal_' . $i] = round($rewardCurrency * 100, 0);
			$this->arg['product_total_' . $i] = round($rewardCurrency * 100, 0);
			$i++;
		}

		$discount = $order->getDiscountAmount();
		if ($discount && $discount < 0) {	
		
			$taxCalculation = $this->scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
						
			if($taxCalculation){
				$total = round($discount * 100, 0);
				$netTotal = round(($discount + $discountTax), 2) * 100;
			}
			else{
				$total = round(($discount - $discountTax) * 100, 0);
				$netTotal = round($discount * 100, 0);
			}
		
			$this->arg['product_id_' . $i] = 'discount';
			$this->arg['product_description_' . $i] = $order->getDiscountDescription();
			$this->arg['product_quantity_' . $i] = 1;
			$this->arg['product_weight_' . $i] = 0;
			$this->arg['product_tax_' . $i] = $total - $netTotal;
			$this->arg['product_taxrate_' . $i] = round(((100 * $total) / $netTotal) - 100) * 100;
			
			if($this->arg['product_taxrate_' . $i] == 2200 || $this->arg['product_taxrate_' . $i] == 2000 || $this->arg['product_taxrate_' . $i] == 1900){
				$this->arg['product_taxrate_' . $i] = 2100;
			}
			
			$this->arg['product_netprice_' . $i] = $netTotal;
			$this->arg['product_price_' . $i] = $total;
			$this->arg['product_nettotal_' . $i] = $netTotal;
			$this->arg['product_total_' . $i] = $total;
			$i++;
		}
						
		// Add Sisow Fee
		$i = $this->_addSisowFee($i, $order);
		
		// Add Fooman lines
		$i = $this->_addFoomanTotalLines($i, $order);
		
		$this->arg['ipaddress'] = $_SERVER['REMOTE_ADDR'];
		
		$this->arg['currency'] = $order->getCurrencyCode();
		$this->arg['tax'] = round( ($order->getBaseTaxAmount() * 100.0) );
		$this->arg['weight'] = round( ($order->getWeight() * 100.0) );
		$this->arg['shipping'] = round( ($order->getBaseShippingAmount() * 100.0) );
		
		$testmode = $this->scopeConfig->getValue('payment/'.$code.'/testmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$this->arg['testmode'] = $testmode ? 'true' : 'false';
		
		/*
		if($payment == 'afterpay' && (bool)$this->scopeConfig->getValue('payment/'.$code.'/createinvoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
			$this->arg['makeinvoice'] = 'true';
		*/
		if(($payment == 'afterpay' || $payment == 'billink') && !(bool)$this->scopeConfig->getValue('payment/'.$code.'/b2b', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
		{
			$this->arg['billing_company'] = '';
			$this->arg['shipping_company'] = '';
		}		
		
		$sisow = $this->_objectManager->create('Sisow\Payment\Model\Sisow');
		$sisow->payment = substr($code, 6);
		$sisow->amount = $order->getGrandTotal();
		$sisow->purchaseId = $orderid;
		$description = $this->scopeConfig->getValue('payment/'.$code.'/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$sisow->description = empty($description) ? $orderid : $description . $orderid;
		$sisow->returnUrl = $this->_url->getUrl('sisow/payment/returnpayment');
		$sisow->cancelUrl = $this->_url->getUrl('sisow/payment/returnpayment');
		$sisow->notifyUrl = $this->_url->getUrl('sisow/payment/notify');
		$sisow->callbackUrl = $this->_url->getUrl('sisow/payment/notify');
		
		$method = $order->getPayment();   
		if($sisow->payment == 'ideal')
		{
			$sisow->issuerId = $method->getAdditionalInformation('issuerid');
			
			if(empty($sisow->issuerId))
			{
				$order->registerCancellation('No bank selected')->Save();
				$this->checkoutSession->restoreQuote();
				$this->messageManager->addErrorMessage(__('Please select your bank'));
				$this->_redirect('checkout/cart');
				return;
			}
		}
		else if($sisow->payment == 'giropay' || $sisow->payment == 'eps')
		{
			$this->arg['bic'] = $method->getAdditionalInformation('bic');
			
			if(empty($this->arg['bic']))
			{
				$order->registerCancellation('No bic entered')->Save();
				$this->checkoutSession->restoreQuote();
				$this->messageManager->addErrorMessage(__('Please enter a bic code'));
				$this->_redirect('checkout/cart');
				return;
			}
		}
		else if($sisow->payment == 'focum')
		{
			$this->arg['gender'] = $method->getAdditionalInformation('gender');
			$this->arg['birthdate'] = $method->getAdditionalInformation('dob');
			$this->arg['iban'] = $method->getAdditionalInformation('iban');
			
			if(empty($this->arg['gender']) || empty($this->arg['birthdate']) || empty($this->arg['iban']))
			{
				$order->registerCancellation('Missed some required info for focum actherafbetalen')->Save();
				$this->checkoutSession->restoreQuote();
				$this->messageManager->addErrorMessage(__('Enter the required information for Focum acteraf betalen'));
				$this->_redirect('checkout/cart');
				return;
			}
		}
		else if($sisow->payment == 'afterpay' || $sisow->payment == 'capayable' || $sisow->payment == 'billink')
		{
			$this->arg['gender'] = $method->getAdditionalInformation('gender');
			$this->arg['birthdate'] = $method->getAdditionalInformation('dob');
			$this->arg['billing_coc'] = $method->getAdditionalInformation('coc');
			
			$phone = $method->getAdditionalInformation('phone');
			
			if(!empty($phone))
			{
				$this->arg['shipping_phone'] = $phone;
				$this->arg['billing_phone'] = $phone;
			}
		}
		else if($sisow->payment == 'klarna')
		{
			$this->arg['gender'] = $method->getAdditionalInformation('gender');
			$this->arg['birthdate'] = $method->getAdditionalInformation('dob');
		}
		else if($sisow->payment == 'overboeking')
		{
			$days = $this->scopeConfig->getValue('payment/'.$code.'/days', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$include = $this->scopeConfig->getValue('payment/'.$code.'/include', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			
			$this->arg['including'] = $include ? 'true' : 'false';
			if($days > 0)
				$this->arg['days'] = $days;
		}
				
		if(($ex = $sisow->TransactionRequest($this->arg)) < 0)
		{			
			$order->registerCancellation('Failed to start Transaction ('.$ex.', '.$sisow->errorCode.', '.$sisow->errorMessage.')')->Save();
			
			$this->checkoutSession->restoreQuote();
			
			if($sisow->payment == 'focum')
				$this->messageManager->addErrorMessage('Op dit moment is het niet mogelijk om te betalen via Focum Achterafbetalen, kies een andere betaaloptie.');
			else if($sisow->payment == 'billink')
				$this->messageManager->addErrorMessage('Op dit moment is het niet mogelijk om te betalen via Billink, kies een andere betaaloptie.');
			else if($sisow->payment == 'afterpay'){
				$errorMessage = $sisow->errorMessage;
				
				$defaultError = 'Helaas is uw aanvraag op dit moment niet door AfterPay geaccepteerd. Voor vragen kunt u contact opnemen met AfterPay of op de website kijken bij "veel gestelde vragen" via de link http://www.afterpay.nl/page/consument-faq onder het kopje "Gegevenscontrole". Wij adviseren u voor een andere betaalmethode te kiezen om alsnog de betaling van uw bestelling af te ronden.';
								
				if(!empty($errorMessage) && strpos($errorMessage, 'Reservation not possible (Failed;') !== false){
					$errorMessage = str_replace('Reservation not possible (Failed;', '', $errorMessage);
					$errorMessage = substr($errorMessage, 0, strlen($errorMessage) -1);
					
					if($errorMessage == 'Afterpay Technical Error' || $errorMessage == 'Aanvraag komt niet in aanmerking voor AfterPay')
						$this->messageManager->addErrorMessage($defaultError);
					else
						$this->messageManager->addErrorMessage($errorMessage);
				}
				else{
					$this->messageManager->addErrorMessage($defaultError);
				}
			}
			else if($sisow->payment == 'klarna')
				$this->messageManager->addErrorMessage('Op dit moment is het niet mogelijk om te betalen via Klarna, kies een andere betaaloptie.');
			else
				$this->messageManager->addErrorMessage(__('Error on starting the transaction') . ' ('.$ex.', '.$sisow->errorCode.')');
			$this->_redirect('checkout/cart');
			return;
		}
		
		$order->getPayment()->setAdditionalInformation('trxId', $sisow->trxId)->save();
				
		if($sisow->payment == 'overboeking' || $sisow->payment == 'ebill' || $sisow->payment == 'focum' || $sisow->payment == 'afterpay' || $sisow->payment == 'klarna' || $sisow->payment == 'billink')
		{
			// set transaction status to processing
			if($sisow->payment == 'focum' || $sisow->payment == 'billink' || $sisow->payment == 'afterpay' || ($sisow->payment == 'klarna' && !$sisow->pendingKlarna))
			{
				// get payment
				$orderPayment = $order->getPayment();
			
				// set payment values
				$orderPayment->setPreparedMessage('Sisow status Reservation')
						->setTransactionId($sisow->trxId)
						->setCurrencyCode($order->getBaseCurrencyCode())
						->setIsTransactionClosed(0)
						->registerAuthorizationNotification($order->getBaseGrandTotal());
						
				$order->save();
				
				// notify customer
				if(!$order->getEmailSent()) {
					$this->orderSender->send($order);
				}
				
				if((bool)$this->scopeConfig->getValue('payment/'.$code.'/createinvoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
				{
					$invoice = $this->_invoiceService->prepareInvoice($order);
					$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
					$invoice->register();
					$invoice->save();
					
					// save payment
					$orderPayment = $order->getPayment();
					
					$orderPayment->setTransactionId($sisow->trxId)
									->setCurrencyCode($order->getBaseCurrencyCode())
									->setPreparedMessage('Sisow status Success')
									->setIsTransactionClosed(1)
									->registerCaptureNotification($order->getBaseGrandTotal());
									
					$order->save();
				}
			}
						
			$this->getResponse()->setRedirect($this->_url->getUrl('checkout/onepage/success'));
		}
		else
			$this->getResponse()->setRedirect($sisow->issuerUrl);
		return;
    }
	
	/**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }
	
	/**
     * Returns a list of action flags [flag_key] => boolean
     * @return array
     */
    public function getActionFlagList()
    {
        return [];
    }
	
	/**
     * Returns before_auth_url redirect parameter for customer session
     * @return null
     */
	public function getCustomerBeforeAuthUrl()
    {
        return;
    }
	
	/**
     * Returns login url parameter for redirect
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }
	
	private function _addFoomanTotalLines($i, $order)
    {		
        $extAttr = $order->getExtensionAttributes();
        if (!$extAttr) {
            return $i;
        }

        $foomanGroup = $extAttr->getFoomanTotalGroup();
        if (empty($foomanGroup)) {
            return $i;
        }

        $totals = $foomanGroup->getItems();
        if (empty($totals)) {
            return $i;
        }
		
        foreach ($totals as $total) {			
			$this->arg['product_id_' . $i] = 'foofee';
			$this->arg['product_description_' . $i] = $total->getLabel();
			$this->arg['product_quantity_' . $i] = 1;
			$this->arg['product_netprice_' . $i] = round($total->getBaseAmount() * 100);
			$this->arg['product_total_' . $i] = round(($total->getBaseAmount() + $total->getBaseTaxAmount()) * 100);
			$this->arg['product_nettotal_' . $i] = round($total->getBaseAmount() * 100);
			$this->arg['product_tax_' . $i] = round($total->getBaseTaxAmount() * 100);
			$this->arg['product_taxrate_' . $i] = round((100 * $total->getBaseTaxAmount()) / $total->getBaseAmount()) * 100;
			
            $i++;
        }
		
		return $i;
    }
	
	private function _addSisowFee($i, $order)
    {				
		if($order->getSisowFee() > 0)
		{							
			$this->arg['product_id_' . $i] = 'payfee';
			$this->arg['product_description_' . $i] = 'Payment Fee';
			$this->arg['product_quantity_' . $i] = 1;
			$this->arg['product_netprice_' . $i] = round($order->getSisowFee() * 100);
			$this->arg['product_total_' . $i] = round($order->getSisowFeeInclTax() * 100);
			$this->arg['product_nettotal_' . $i] = $this->arg['product_netprice_' . $i];
			$this->arg['product_tax_' . $i] = $this->arg['product_total_' . $i] - $this->arg['product_netprice_' . $i];
			$this->arg['product_taxrate_' . $i] = $this->arg['product_netprice_' . $i] == $this->arg['product_total_' . $i] ? 0 : (round($this->arg['product_total_' . $i] / $order->getSisowFee()) - 100) * 100;
			
			if($this->arg['product_taxrate_' . $i] == 2200 || $this->arg['product_taxrate_' . $i] == 2000 || $this->arg['product_taxrate_' . $i] == 1900){
				$this->arg['product_taxrate_' . $i] = 2100;
			}
			
			$i++;
		}
		
		return $i;
    }
}

