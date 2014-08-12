<?php

class Zolago_Dropship_Model_Observer {
	
	/**
	 * 
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
        $track = $observer->getEvent()->getTrack();
		$carrierCode = $track->getCarrierCode();
		
		if (in_array($carrierCode,array(Orba_Shipping_Model_Carrier_Dhl::CODE,Orba_Shipping_Model_Carrier_Ups::CODE))
			&& Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode)->isTrackingAvailable()
			&& !$track->getWebApi()) {
				$track->setNextCheck(date('Y-m-d H:i:s', time()));
				$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING);
		}

		return $this;
	}
}
