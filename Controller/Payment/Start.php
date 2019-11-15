<?php
/**
 * Copyright Sisow 2016
 * created by Sisow(support@sisow.nl)
 */

namespace Sisow\Payment\Controller\Payment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Calculation;
use Sisow\Payment\Model\Sisow;


class Start extends Action
{
    private $arg;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Quote
     */
    private $quote = false;

    /**
     * var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var BuilderInterface
     */
    private $transactionBuilder;

    /**
     * @var Calculation
     */
    private $taxCalculation;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var Sisow
     */
    private $sisow;

    /**
     * Start constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @param PaymentHelper $paymentHelper
     * @param OrderSender $orderSender
     * @param InvoiceSender $invoiceSender
     * @param BuilderInterface $transactionBuilder
     * @param Calculation $taxCalculation
     * @param InvoiceService $invoiceService
     * @param OrderRepository $orderRepository
     * @param InvoiceRepository $invoiceRepository
     * @param Sisow $sisow
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        PaymentHelper $paymentHelper,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        BuilderInterface $transactionBuilder,
        Calculation $taxCalculation,
        InvoiceService $invoiceService,
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository,
        Sisow $sisow
    )
    {
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->paymentHelper = $paymentHelper;
        $this->transactionBuilder = $transactionBuilder;
        $this->taxCalculation = $taxCalculation;
        $this->invoiceService = $invoiceService;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->sisow = $sisow;
        parent::__construct($context);
    }


    /**
     * say hello text
     */
    public function execute()
    {
        $discountTax = 0;
        $order = $this->checkoutSession->getLastRealOrder();

        // validate if order is loaded
        if (empty($order->getId())) {
            $this->_redirect('checkout/cart');
            return;
        }

        // get payment code
        $magentoPaymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $paymentCode = substr($magentoPaymentCode, 6);

        if ($paymentCode == 'overboeking') {
            $this->_redirect($this->_url->getUrl('checkout/onepage/success'));
            return;
        }

        $this->arg = array();
        $this->arg['ipaddress'] = $_SERVER['REMOTE_ADDR'];
        $this->arg['billing_firstname'] = $order->getBillingAddress()->getFirstname();
        if ($paymentCode == 'afterpay' && !empty($this->arg['billing_firstname']) && strlen($this->arg['billing_firstname']) > 1)
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
        if ($paymentCode == 'afterpay' && !empty($this->arg['shipping_firstname']) && strlen($this->arg['shipping_firstname']) > 1)
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
        foreach ($order->getAllItems() as $item) {

            if($item->getParentItemId())
                continue;

            $this->arg['product_id_' . $i] = $item->getSku();
            $this->arg['product_description_' . $i] = $item->getName();
            $this->arg['product_quantity_' . $i] = round($item->getQtyOrdered(), 0);
            $this->arg['product_tax_' . $i] = round(($item->getRowTotalInclTax() - $item->getRowTotal()) * 100, 0);
            $this->arg['product_netprice_' . $i] = round($item->getPrice() * 100, 0);
            $this->arg['product_nettotal_' . $i] = round($item->getRowTotal() * 100, 0);
            $this->arg['product_total_' . $i] = round($item->getRowTotalInclTax() * 100, 0);
            $this->arg['product_type_' . $i] = $item->getIsVirtual() ? 'digital' : 'physical';

            // load product details
            $product = $item->getProduct();

            // calculate tax class on bundle
            if ($product->getTypeId() == 'bundle' && $this->arg['product_tax_' . $i] > 0) {
                $taxRateRequest = $this->taxCalculation->getRateRequest(null, null, null, $order->getStore());
                $productTaxClassId = $product->getData('tax_class_id');
                $taxRate = $this->taxCalculation->getRate($taxRateRequest->setData('product_class_id', $productTaxClassId));

                $this->arg['product_taxrate_' . $i] = round($taxRate * 100, 0);
            }
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
            $this->arg['product_taxrate_' . $i] = round((($this->arg['product_total_' . $i] / $this->arg['product_nettotal_' . $i]) - 1) * 100) * 100;
            $this->arg['product_type_' . $i] = 'shipping_fee';

            $i++;
        }
/*
        $giftCardsAmount = $order->get->getGiftCardsAmount();
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
*/
        $discount = $order->getDiscountAmount();
        if ($discount && $discount < 0) {

            $taxCalculation = $this->scopeConfig->getValue('tax/calculation/price_includes_tax', ScopeInterface::SCOPE_STORE);

            if ($taxCalculation) {
                $total = round($discount * 100, 0);
                $netTotal = round(($discount + $discountTax), 2) * 100;
            } else {
                $total = round(($discount - $discountTax) * 100, 0);
                $netTotal = round($discount * 100, 0);
            }

            $this->arg['product_id_' . $i] = 'discount';
            $this->arg['product_description_' . $i] = $order->getDiscountDescription();
            $this->arg['product_quantity_' . $i] = 1;
            $this->arg['product_weight_' . $i] = 0;
            $this->arg['product_tax_' . $i] = $total - $netTotal;
            $this->arg['product_taxrate_' . $i] = round(((100 * $total) / $netTotal) - 100) * 100;

            if ($this->arg['product_taxrate_' . $i] == 2200 || $this->arg['product_taxrate_' . $i] == 2000 || $this->arg['product_taxrate_' . $i] == 1900) {
                $this->arg['product_taxrate_' . $i] = 2100;
            }

            $this->arg['product_netprice_' . $i] = $netTotal;
            $this->arg['product_price_' . $i] = $total;
            $this->arg['product_nettotal_' . $i] = $netTotal;
            $this->arg['product_total_' . $i] = $total;
            $this->arg['product_type_' . $i] = 'discount';
            $i++;
        }

        // Add Sisow Fee
        $i = $this->_addSisowFee($i, $order);

        // Add Fooman lines
        $this->_addFoomanTotalLines($i, $order);

        $this->arg['ipaddress'] = $_SERVER['REMOTE_ADDR'];

        $this->arg['currency'] = $order->getOrderCurrencyCode();
        $this->arg['tax'] = round(($order->getTaxAmount() * 100.0));
        $this->arg['weight'] = round(($order->getWeight() * 100.0));
        $this->arg['shipping'] = round(($order->getShippingAmount() * 100.0));

        $testmode = $this->scopeConfig->getValue('payment/' . $magentoPaymentCode . '/testmode', ScopeInterface::SCOPE_STORE);
        $this->arg['testmode'] = $testmode ? 'true' : 'false';

        /*
        if($payment == 'afterpay' && (bool)$this->scopeConfig->getValue('payment/'.$code.'/createinvoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
            $this->arg['makeinvoice'] = 'true';
        */
        if (($paymentCode == 'afterpay' || $paymentCode == 'billink') && !(bool)$this->scopeConfig->getValue('payment/' . $magentoPaymentCode . '/b2b', ScopeInterface::SCOPE_STORE)) {
            $this->arg['billing_company'] = '';
            $this->arg['shipping_company'] = '';
        }
        
        $this->sisow->payment = $paymentCode;
        $this->sisow->amount = $order->getGrandTotal();
        $this->sisow->purchaseId = $order->getRealOrderId();
        $this->sisow->entranceCode = $order->getEntityId();
        $description = $this->scopeConfig->getValue('payment/' . $magentoPaymentCode . '/description', ScopeInterface::SCOPE_STORE);
        $this->sisow->description = empty($description) ? $order->getRealOrderId() : $description . $order->getRealOrderId();
        $this->sisow->returnUrl = $paymentCode == 'overboeking' ? $order->getStore()->getBaseUrl() : $this->_url->getUrl('sisow/payment/returnpayment') . '?entityid=true';
        $this->sisow->cancelUrl = $this->sisow->returnUrl;
        $this->sisow->notifyUrl = $this->_url->getUrl('sisow/payment/notify') . '?entityid=true';
        $this->sisow->callbackUrl = $this->sisow->notifyUrl;
        
        $method = $order->getPayment();
        if ($this->sisow->payment == 'ideal') {
            $this->sisow->issuerId = $method->getAdditionalInformation('issuerid');
        } else if ($this->sisow->payment == 'giropay' || $this->sisow->payment == 'eps') {
            $this->arg['bic'] = $method->getAdditionalInformation('bic');
        } else if ($this->sisow->payment == 'focum') {
            $this->arg['gender'] = $method->getAdditionalInformation('gender');
            $this->arg['birthdate'] = $method->getAdditionalInformation('dob');
            $this->arg['iban'] = $method->getAdditionalInformation('iban');
        } else if ($this->sisow->payment == 'afterpay' || $this->sisow->payment == 'capayable' || $this->sisow->payment == 'billink') {
            $this->arg['gender'] = $method->getAdditionalInformation('gender');
            $this->arg['birthdate'] = $method->getAdditionalInformation('dob');
            $this->arg['billing_coc'] = $method->getAdditionalInformation('coc');

            $phone = $method->getAdditionalInformation('phone');

            if (!empty($phone)) {
                $this->arg['shipping_phone'] = $phone;
                $this->arg['billing_phone'] = $phone;
            }
        } else if ($this->sisow->payment == 'overboeking') {
            $days = $this->scopeConfig->getValue('payment/' . $magentoPaymentCode . '/days', ScopeInterface::SCOPE_STORE);
            $include = $this->scopeConfig->getValue('payment/' . $magentoPaymentCode . '/include', ScopeInterface::SCOPE_STORE);

            $this->arg['including'] = $include ? 'true' : 'false';
            if ($days > 0)
                $this->arg['days'] = $days;
        }

        if (($ex = $this->sisow->TransactionRequest($this->arg)) < 0) {
            try{
                $order->registerCancellation('Failed to start Transaction (' . $ex . ', ' . $this->sisow->errorCode . ', ' . $this->sisow->errorMessage . ')');
                $this->orderRepository->save($order);
            }catch (LocalizedException $e){}

            $this->checkoutSession->restoreQuote();

            if ($this->sisow->payment == 'focum') {
                $this->messageManager->addErrorMessage('Op dit moment is het niet mogelijk om te betalen via Focum Achterafbetalen, kies een andere betaaloptie.');
            }else if ($this->sisow->payment == 'billink') {
                $this->messageManager->addErrorMessage('Op dit moment is het niet mogelijk om te betalen via Billink, kies een andere betaaloptie.');
            }else if ($this->sisow->payment == 'afterpay') {
                $errorMessage = $this->sisow->errorMessage;

                $defaultError = 'Helaas is uw aanvraag op dit moment niet door AfterPay geaccepteerd. Voor vragen kunt u contact opnemen met AfterPay of op de website kijken bij "veel gestelde vragen" via de link http://www.afterpay.nl/page/consument-faq onder het kopje "Gegevenscontrole". Wij adviseren u voor een andere betaalmethode te kiezen om alsnog de betaling van uw bestelling af te ronden.';

                if (!empty($errorMessage) && strpos($errorMessage, 'Reservation not possible (Failed;') !== false) {
                    $errorMessage = str_replace('Reservation not possible (Failed;', '', $errorMessage);
                    $errorMessage = substr($errorMessage, 0, strlen($errorMessage) - 1);

                    if ($errorMessage == 'Afterpay Technical Error' || $errorMessage == 'Aanvraag komt niet in aanmerking voor AfterPay')
                        $this->messageManager->addErrorMessage($defaultError);
                    else
                        $this->messageManager->addErrorMessage($errorMessage);
                } else {
                    $this->messageManager->addErrorMessage($defaultError);
                }
            } else if ($this->sisow->payment == 'klarna') {
                $this->messageManager->addErrorMessage('Op dit moment is het niet mogelijk om te betalen via Klarna, kies een andere betaaloptie.');
            }else {
                $this->messageManager->addErrorMessage(__('Error on starting the transaction') . ' (' . $ex . ', ' . $this->sisow->errorCode . ')');
            }
            $this->_redirect('checkout/cart');
            return;
        }

        $order->getPayment()->setAdditionalInformation('trxId', $this->sisow->trxId)->save();

        if ($this->sisow->payment == 'overboeking' || $this->sisow->payment == 'ebill' || $this->sisow->payment == 'focum' || $this->sisow->payment == 'afterpay' || $this->sisow->payment == 'billink') {
            // set transaction status to processing
            if ($this->sisow->payment == 'focum' || $this->sisow->payment == 'billink' || $this->sisow->payment == 'afterpay' ) {
                // get payment
                $orderPayment = $order->getPayment();

                // set payment values
                $orderPayment->setPreparedMessage('Sisow status Reservation')
                    ->setTransactionId($this->sisow->trxId)
                    ->setCurrencyCode($order->getBaseCurrencyCode())
                    ->setIsTransactionClosed(0)
                    ->registerAuthorizationNotification($order->getBaseGrandTotal());

                // save order
                $this->orderRepository->save($order);

                // notify customer
                if (!$order->getEmailSent()) {
                    $this->orderSender->send($order);
                }

                if ((bool)$this->scopeConfig->getValue('payment/' . $magentoPaymentCode . '/createinvoice', ScopeInterface::SCOPE_STORE)) {
                    try {
                        $invoice = $this->invoiceService->prepareInvoice($order);
                        //$invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                        $invoice->register();
                        $this->invoiceRepository->save($invoice);
                    }
                    catch (LocalizedException $e){}
                    // save payment
                    $orderPayment = $order->getPayment();

                    $orderPayment->setTransactionId($this->sisow->trxId)
                        ->setCurrencyCode($order->getBaseCurrencyCode())
                        ->setPreparedMessage('Sisow status Success')
                        ->setIsTransactionClosed(1)
                        ->registerCaptureNotification($order->getBaseGrandTotal());

                    $this->orderRepository->save($order);
                }
            }

            $this->_redirect($this->_url->getUrl('checkout/onepage/success'));
        } else
            $this->_redirect($this->sisow->issuerUrl);
        return;
    }

    /**
     * Return checkout quote object
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    private function _addFoomanTotalLines($i, Order $order)
    {
        $extAttr = $order->getExtensionAttributes();
        if (!$extAttr) {
            return $i;
        }

        if (!method_exists($extAttr, 'getFoomanTotalGroup')) {
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
            $this->arg['product_type_' . $i] = 'surcharge';

            $i++;
        }

        return $i;
    }

    private function _addSisowFee($i, Order $order)
    {
        if ($order->getSisowFee() > 0) {
            $this->arg['product_id_' . $i] = 'payfee';
            $this->arg['product_description_' . $i] = 'Payment Fee';
            $this->arg['product_quantity_' . $i] = 1;
            $this->arg['product_netprice_' . $i] = round($order->getSisowFee() * 100);
            $this->arg['product_total_' . $i] = round($order->getSisowFeeInclTax() * 100);
            $this->arg['product_nettotal_' . $i] = $this->arg['product_netprice_' . $i];
            $this->arg['product_tax_' . $i] = $this->arg['product_total_' . $i] - $this->arg['product_netprice_' . $i];
            $this->arg['product_taxrate_' . $i] = $this->arg['product_netprice_' . $i] == $this->arg['product_total_' . $i] ? 0 : (round($this->arg['product_total_' . $i] / $order->getSisowFee()) - 100) * 100;

            if ($this->arg['product_taxrate_' . $i] == 2200 || $this->arg['product_taxrate_' . $i] == 2000 || $this->arg['product_taxrate_' . $i] == 1900) {
                $this->arg['product_taxrate_' . $i] = 2100;
            }

            $this->arg['product_type_' . $i] = 'surcharge';

            $i++;
        }

        return $i;
    }
}

