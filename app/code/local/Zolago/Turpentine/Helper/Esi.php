<?php

class Zolago_Turpentine_Helper_Esi extends Nexcessnet_Turpentine_Helper_Esi {
  
	const DEFAULT_ESI_PRIV_TTL = 3600;
	
    /**
     * Get the default private ESI block TTL
	 * If mage cookie epires = 0 (use browser session) then its contant
     * @return string
     */
    public function getDefaultEsiTtl() {
		if($this->getSystemCookieLifeTime() && (int)$this->getSystemCookieLifeTime()>0){
			return parent::getDefaultEsiTtl();
		}
        return self::DEFAULT_ESI_PRIV_TTL;
    }
  
    /**
     * Return cookie real lifetime if 0 use browser session
     * @return string
     */
    public function getSystemCookieLifeTime() {
        return trim( Mage::getStoreConfig( 'web/cookie/cookie_lifetime' ) );
    }

}
