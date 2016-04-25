<?php
/**
  
 */

/**
* Currently not in use
*/
class ZolagoOs_OmniChannelShippingClass_Model_Source extends ZolagoOs_OmniChannel_Model_Source_Abstract
{
    const VENDOR_SHIP_CLASS_US = 1;
    const VENDOR_SHIP_CLASS_INT = 2;

    const CUSTOMER_SHIP_CLASS_US = 1;
    const CUSTOMER_SHIP_CLASS_INT = 2;

    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpv = Mage::helper('udshipclass');

        switch ($this->getPath()) {

        case 'vendor_ship_class':
            $options = Mage::getResourceSingleton('udshipclass/vendor_collection')->toOptionHash();
            $options[-1] = $hlpv->__('* Other Vendor');
            break;

        case 'customer_ship_class':
            $options = Mage::getResourceSingleton('udshipclass/customer_collection')->toOptionHash();
            $options[-1] = $hlpv->__('* Other Customer');
            break;

        default:
            Mage::throwException($hlp->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>$hlp->__('* Please select')) + $options;
        }

        return $options;
    }
}