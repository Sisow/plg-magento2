<?php
namespace Sisow\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\Repository;

class SisowConfigProvider implements ConfigProviderInterface
{
	public function __construct(Sisow $sisow, ScopeConfigInterface $scopeConfig, DirectoryList $directory_list, Repository $assetRepo)
    {
        $this->_sisow = $sisow;
		$this->_scopeConfig = $scopeConfig;
		$this->_directoryList = $directory_list;
		$this->_assetRepo  = $assetRepo;
    }
	
	public function GetBanks()
	{
		$banks = array();
		$this->_sisow->DirectoryRequest($banks, false, (bool)$this->_scopeConfig->getValue('payment/sisow_ideal/testmode', ScopeInterface::SCOPE_STORE));
		
		$issuers = [];
		foreach($banks as $k => $v)
		{
			$issuers[] = [
				'bankid' => $k,
				'bankname' => $v
			];
		}
		return $issuers;
	}
	
	public function GetB2b($method)
	{
		return (bool)$this->_scopeConfig->getValue('payment/sisow_' . $method . '/b2b', ScopeInterface::SCOPE_STORE);
	}
	
	public function GetYears()
	{		
		$years = [];
		for($i = date("Y") - 17; $i > date("Y") - 125; $i--)
		{
			$years[] = [
				'value' => $i,
				'year' => $i
			];
		}
		return $years;
	}
	
	public function GetLogo($method)
	{
		return $this->_assetRepo->getUrl('Sisow_Payment::images/logo/' . $method . '.png');
	}
	
	public function getConfig()
    {
		$config = [];
		
		$config = array_merge_recursive($config, [
            'payment' => [
                'sisow' => [
					'issuers' => $this->GetBanks(),
					'b2b' => $this->GetB2b('afterpay'),
					'b2bBillink' => $this->GetB2b('billink'),
					'years' => $this->GetYears(),
					'logoAfterpay' => $this->GetLogo('afterpay'),
					'logoBillink' => $this->GetLogo('billink'),
					'logoIdeal' => $this->GetLogo('ideal'),
					'logoIdealqr' => $this->GetLogo('idealqr'), // nog toevoegen
					'logoBancontact' => $this->GetLogo('bancontact'),
					'logoHomepay' => $this->GetLogo('homepay'),
					'logoSofort' => $this->GetLogo('sofort'),
					'logoGiropay' => $this->GetLogo('giropay'),
					'logoEps' => $this->GetLogo('eps'),
					'logoMastercard' => $this->GetLogo('mastercard'),
					'logoMaestro' => $this->GetLogo('maestro'),
					'logoVisa' => $this->GetLogo('visa'),
					'logoVpay' => $this->GetLogo('vpay'),
					'logoPaypal' => $this->GetLogo('paypalec'),
					'logoBunq' => $this->GetLogo('bunq'),
					'logoVvv' => $this->GetLogo('vvvgiftcard'),
					'logoWebshop' => $this->GetLogo('webshopgiftcard'),
					'logoFocum' => $this->GetLogo('focum'),
					'logoBelfius' => $this->GetLogo('belfius'),
					'logoVpay' => $this->GetLogo('vpay'),
					'logoOverboeking' => $this->GetLogo('overboeking'),
					'logoCapayable' => $this->GetLogo('capayable'),
					'logoKbc' => $this->GetLogo('kbc'),
					'logoCbc' => $this->GetLogo('cbc'),
					'logoCheckout' => (bool)$this->_scopeConfig->getValue('payment/general/checkoutlogo', ScopeInterface::SCOPE_STORE)
                ],
            ],
        ]);

        return $config;
    }
}