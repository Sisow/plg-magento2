<?php
namespace Sisow\Payment\Model\Config\Source\Tax;

class Taxclass extends \Magento\Tax\Model\TaxClass\Source\Product
{
    public function toOptionArray()
    {
        return $this->getAllOptions(false);
    }
}
?>