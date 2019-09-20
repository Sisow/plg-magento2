<?php
/**
 * Copyright Sisow 2016
 * created by Sisow(support@sisow.nl)
 */
namespace Sisow\Payment\Controller\Payment;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Session;
use Magento\Tax\Api\TaxClassManagementInterface;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\GroupManagementInterface as CustomerGroupManagement;
use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;

use Magento\Tax\Model\Plugin\OrderSave;
 
class Taxreset extends \Magento\Framework\App\Action\Action
{		
	protected $_checkoutSession;
	protected $_quoteFactory;
	protected $_quoteDetailsFactory;
	protected $_addressFactory;
    protected $_regionFactory;
    protected $_customerRepository;
    protected $_customerGroupManagement;
    protected $_customerGroupRepository;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		
		\Magento\Tax\Model\Plugin\OrderSave $orderSave,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Quote\Model\Quote\TotalsCollector $quoteTotalsCollector,
		\Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
		\Magento\Tax\Model\Sales\Total\Quote\Tax $quoteTaxTotals,
		\Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor $shippingAssignmentBuilder
		)
    {		
		$this->_orderSave = $orderSave;		
		$this->_orderRepository = $orderRepository;
		$this->_quoteTotalsCollector = $quoteTotalsCollector;
		$this->_orderExtensionFactory = $orderExtensionFactory;
		$this->_quoteTaxTotals = $quoteTaxTotals;
		$this->_shippingAssignmentBuilder = $shippingAssignmentBuilder;
		
        parent::__construct($context);
    }
	
    public function execute()
    {
		$orders = $this->_objectManager->create('Magento\Sales\Model\Order')->getCollection();
		
		foreach($orders as $order){
			$paymentCode = $order->getPayment()->getMethodInstance()->getCode();
			
			// validate method
			if(strpos($paymentCode, 'sisow') !== false)
				continue;
			
			// load totals
			$tax = $this->_objectManager->create('Magento\Sales\Model\Order\Tax')->load($order->getId(), 'order_id');
			
			if($tax->getId())
				continue;
			
			$this->FixOrder($order);
			print_r($order->getIncrementId() . ' - ' . $paymentCode . ' - tax fixed!<br/>');
		}
		exit('Order taxes fixed!');
	}
	
	private function FixOrder($order){		
		$quoteId = $order->getQuoteId();
		$quote = $this->_objectManager->create('Magento\Quote\Model\Quote')->loadByIdWithoutStore($quoteId);
		
		$quoteAddress = $this->_quoteTotalsCollector->collectQuoteTotals($quote);
					
		$shippingAssignment = $this->_shippingAssignmentBuilder->create($quote);
		
		$taxTotals = $this->_quoteTaxTotals->collect($quote, $shippingAssignment, $quoteAddress);

		// create extensions			
		 /** @var \Magento\Sales\Model\Order $order */
		$taxes = $quoteAddress->getAppliedTaxes();

		$extensionAttributes = $order->getExtensionAttributes();
		if ($extensionAttributes == null) {
			$extensionAttributes = $this->_orderExtensionFactory->create();
		}
		if (!empty($taxes)) {
			foreach ($taxes as $key => $tax) {
				$appliedTax = $this->_objectManager->create('Magento\Tax\Model\TaxDetails\AppliedTax');
				$tax['rates'] = $appliedTax->setRates($tax['rates']);
				$tax['extension_attributes'] = $tax['rates'];
				$taxes[$key] = $tax;
			}
			$extensionAttributes->setAppliedTaxes($taxes);
			$extensionAttributes->setConvertingFromQuote(true);
		}
		
		$itemAppliedTaxes = $quoteAddress->getItemsAppliedTaxes();
		
		$itemAppliedTaxesModified = [];
		if (!empty($itemAppliedTaxes)) {
			foreach ($itemAppliedTaxes as $key => $itemAppliedTaxItem) {
				if (is_array($itemAppliedTaxItem) && !empty($itemAppliedTaxItem)) {
					foreach ($itemAppliedTaxItem as $itemAppliedTax) {
						$appliedTax = $this->_objectManager->create('Magento\Tax\Model\TaxDetails\AppliedTax');
						$itemAppliedTax['rates'] = $appliedTax->setRates($itemAppliedTax['rates']);
		
						$itemAppliedTaxesModified[$key]['type'] = $itemAppliedTax['item_type'];
						$itemAppliedTaxesModified[$key]['item_id'] = $itemAppliedTax['item_id'];
						$itemAppliedTaxesModified[$key]['associated_item_id'] = $itemAppliedTax['associated_item_id'];
						$itemAppliedTax['extension_attributes'] = $itemAppliedTax['rates'];
						$itemAppliedTaxesModified[$key]['applied_taxes'][] = $itemAppliedTax;
					}
				}
			}
			$extensionAttributes->setItemAppliedTaxes($itemAppliedTaxesModified);
		}
		$order->setExtensionAttributes($extensionAttributes);
		$order->save();
		
		// save taxes
		$this->_orderSave->afterSave($this->_orderRepository, $order);
	}
}