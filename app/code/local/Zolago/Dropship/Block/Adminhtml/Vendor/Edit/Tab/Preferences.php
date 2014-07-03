<?php
class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Preferences extends Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Preferences
{
    protected function _prepareForm()
    {
    	$vendor = Mage::registry('vendor_data');
    	$hlp = Mage::helper('udropship');
		
		$children = Mage::getConfig()->getNode('global/udropship/vendor/fields')->children();
		
        foreach ($children as $code=>$node) {
            
			$note = $hlp->__((string)$node->note);
			
			if($code == 'max_shipping_days'){
				
				$maxShippingDays = Mage::getStoreConfig('udropship/vendor/max_shipping_days');
				
				Mage::getConfig()->setNode('global/udropship/vendor/fields/max_shipping_days/note', $note . sprintf(" (Default value is: %u )", $maxShippingDays));

			}
			elseif($code == 'max_shipping_time'){
				
				$maxShippingTime = Mage::getStoreConfig('udropship/vendor/max_shipping_time');
				
				Mage::getConfig()->setNode('global/udropship/vendor/fields/max_shipping_time/note', $note . sprintf(" (Default value is: %s)", str_replace(',', ':', $maxShippingTime)));
				
			}
		}

        return parent::_prepareForm();
    }
}