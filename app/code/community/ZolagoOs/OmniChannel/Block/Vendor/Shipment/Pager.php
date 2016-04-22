<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Vendor_Shipment_Pager extends Mage_Page_Block_Html_Pager
{
    protected $_availableLimit  = array(10=>10,20=>20,50=>50,100=>100);
    protected $_dispersion      = 3;
    protected $_displayPages    = 10;
    protected $_showPerPage     = true;

    public function checkCompat()
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->setTemplate('page/html/pager13.phtml');
        }
    }
}