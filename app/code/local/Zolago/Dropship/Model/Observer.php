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
	

}
