<?php
namespace Sisow\Payment\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\Store;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    protected $salesSetupFactory;
    /**
     * @var \Magento\Quote\Setup\QuoteSetupFactory
     */
    protected $quoteSetupFactory;
    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $encryptor;

    /**
     * @param \Magento\Sales\Setup\SalesSetupFactory                   $salesSetupFactory
     * @param \Magento\Quote\Setup\QuoteSetupFactory                   $quoteSetupFactory
     * @param \Magento\Framework\Encryption\Encryptor                  $encryptor
     */
    public function __construct(
        \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory,
        \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory,
        \Magento\Framework\Encryption\Encryptor $encryptor
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->encryptor = $encryptor;
    }
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
		
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->installFeeColumns($setup);
        }
    }
	
	private function installFeeColumns(ModuleDataSetupInterface $setup){
		
        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);
        
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
        
        $quoteInstaller->addAttribute(
            'quote',
            'sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $quoteInstaller->addAttribute(
            'quote',
            'base_sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		
        $quoteInstaller->addAttribute(
            'quote',
            'base_sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		        
        $quoteInstaller->addAttribute(
            'quote',
            'sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $quoteInstaller->addAttribute(
            'quote',
            'sisow_fee_base_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $quoteInstaller->addAttribute(
            'quote',
            'sisow_fee_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $quoteInstaller->addAttribute(
            'quote_address',
            'sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $quoteInstaller->addAttribute(
            'quote_address',
            'base_sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		        
        $quoteInstaller->addAttribute(
            'quote_address',
            'base_sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $quoteInstaller->addAttribute(
            'quote_address',
            'sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $quoteInstaller->addAttribute(
            'quote_address',
            'sisow_fee_base_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $quoteInstaller->addAttribute(
            'quote_address',
            'sisow_fee_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'base_sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_invoiced',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'base_sisow_fee_invoiced',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_refunded',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'base_sisow_fee_refunded',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		
        $salesInstaller->addAttribute(
            'order',
            'base_sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_base_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_base_tax_amount_invoiced',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_tax_amount_invoiced',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_base_tax_amount_refunded',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_tax_amount_refunded',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );		
		$salesInstaller->addAttribute(
            'order',
            'sisow_fee_incl_tax_invoiced',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        $salesInstaller->addAttribute(
            'order',
            'base_sisow_fee_incl_tax_invoiced',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        $salesInstaller->addAttribute(
            'order',
            'sisow_fee_incl_tax_refunded',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        $salesInstaller->addAttribute(
            'order',
            'base_sisow_fee_incl_tax_refunded',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		
        
        $salesInstaller->addAttribute(
            'invoice',
            'base_sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'invoice',
            'sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		
        $salesInstaller->addAttribute(
            'invoice',
            'sisow_fee_base_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'invoice',
            'sisow_fee_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		$salesInstaller->addAttribute(
            'invoice',
            'sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        $salesInstaller->addAttribute(
            'invoice',
            'base_sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'creditmemo',
            'base_sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'creditmemo',
            'sisow_fee',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		        
        $salesInstaller->addAttribute(
            'creditmemo',
            'sisow_fee_base_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        
        $salesInstaller->addAttribute(
            'creditmemo',
            'sisow_fee_tax_amount',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
		$salesInstaller->addAttribute(
            'creditmemo',
            'sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        $salesInstaller->addAttribute(
            'creditmemo',
            'base_sisow_fee_incl_tax',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
        );
        return $this;
	}
}