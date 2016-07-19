<?php
class Zolago_Turpentine_Model_Observer_Varnish extends Nexcessnet_Turpentine_Model_Observer_Varnish {
    
	/**
	 * Do overiade cache mode
	 * @param mixed $eventObject
	 * @return null
	 */
    public function setCacheFlagHeader( $eventObject ) {
        $response = $eventObject->getResponse();
		/* @var $response Zend_Http_Response */
        if( Mage::helper( 'turpentine/varnish' )->shouldResponseUseVarnish() ) {
			$onlyAllowed = 1;
			$flag = 1;
			
			// Use only manual setted flag
			// In default do not cache
			if($onlyAllowed){
				$flag = '0';
				if(Mage::registry('turpentine_nocache_flag')===0 || 
				   Mage::registry('turpentine_nocache_flag')==="0" || 
				   Mage::registry('turpentine_nocache_flag')===false){
					$flag = '1';
				}
			}else{
			// In defualt do cache
				$flag = '1';
				if(Mage::registry('turpentine_nocache_flag')){
					$flag = '0';
				}
			}
			
            $response->setHeader( 'X-Turpentine-Cache-Only-Allowed', $onlyAllowed);
            $response->setHeader( 'X-Turpentine-Cache', $flag);
			
            if( Mage::helper( 'turpentine/varnish' )->getVarnishDebugEnabled() ) {
                Mage::helper( 'turpentine/debug' )->logDebug(
                    'Set Varnish cache flag header to: ' . $flag . " / " . 
					str_replace('%', '%%', Mage::app()->getRequest()->getRequestUri())
				);
                Mage::helper( 'turpentine/debug' )->logDebug(
                    'Set Varnish cache mode header to: ' . $onlyAllowed );
				
            }
        }
    }
}
