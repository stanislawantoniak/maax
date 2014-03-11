<?php

class Zolago_Dropship_Model_Observer {
	
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
     * Add Zolago Dhl Data to Save Action
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return Zolago_Dropship_Model_Observer
     */	
	public function addZolagoDhlData(Varien_Event_Observer $observer)
	{
        $track = $observer->getEvent()->getTrack();
		$carrierCode = $track->getCarrierCode();
		
		if ($carrierCode == 'zolagodhl'
			&& Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode)->isTrackingAvailable()) {
			$track->setNextCheck(date('Y-m-d H:i:s', time()));
		}

		return $this;
	}
}
