<?php

class Zolago_Dropship_Model_Observer extends Unirgy_Dropship_Model_Observer{
	
	/**
	 * Clear afetr laod quote better performance
	 * @todo possible bugs not compatibile with unirgy?
	 * @param type $observer
	 * @return Zolago_Dropship_Model_Observer
	 */
	public function sales_quote_load_after($observer){
		if(Mage::helper("zolagocommon")->isUserDataRequest()){
			return;
		}
		parent::sales_quote_load_after($observer);
	}
	
	/**
	 * @param type $observer
	 * @return \Zolago_Dropship_Model_Observer
	 */
	public function bindLocale($observer)
    {
		if(!Mage::registry("dropship_switch_lang")){
			return;
		}
		
		// Handle locale
		$session = Mage::getSingleton('udropship/session');
		
		if ($locale=$observer->getEvent()->getLocale()) {
            if ($choosedLocale = $session->getLocale()) {
                $locale->setLocaleCode($choosedLocale);
            }
        }
        return $this;
    }

    /**
     * Add Data to Save Action
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return Zolago_Dropship_Model_Observer
     */	
	public function addOrbaShippingData(Varien_Event_Observer $observer)
	{
	$time = Mage::getSingleton('core/date');
        $track = $observer->getEvent()->getTrack();
		$carrierCode = $track->getCarrierCode();
		
		if (in_array($carrierCode,array(Orba_Shipping_Model_Carrier_Dhl::CODE,Orba_Shipping_Model_Carrier_Ups::CODE))
			&& Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode)->isTrackingAvailable()
			&& !$track->getWebApi()) {
				$track->setNextCheck(date('Y-m-d H:i:s', $time));
				$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING);
		}

		return $this;
	}

    public function udropship_adminhtml_vendor_tabs_after($observer) {

        $block = $observer->getEvent()->getBlock();
        $id    = $observer->getEvent()->getId();
        $v     = Mage::helper('udropship')->getVendor($id);

        if (!$block instanceof Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs
            || !Mage::app()->getRequest()->getParam('id', 0)
        ) {
            return;
        }

        if ($block instanceof Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs) {
            $addressBlock = Mage::app()
                ->getLayout()
                ->createBlock('zolagodropship/adminhtml_vendor_edit_tab_address', 'vendor.address')
                ->setVendorId($v)
                ->toHtml();

            $block->addTab('address', array(
                'label'     => Mage::helper('udropship')->__('Address'),
                'after'     => 'form_section',
                'content'	=> $addressBlock
            ));

            $couriersBlock = Mage::app()
                ->getLayout()
                ->createBlock('zolagodropship/adminhtml_vendor_edit_tab_couriers', 'vendor.couriers')
                ->setVendorId($v)
                ->toHtml();

            $block->addTab('couriers', array(
                'label'     => Mage::helper('udropship')->__('Couriers'),
                'after'     => 'form_section',
                'content'	=> $couriersBlock
            ));


        }
    }
}
