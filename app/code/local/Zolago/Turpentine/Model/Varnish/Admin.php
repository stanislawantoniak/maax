<?php

class Zolago_Turpentine_Model_Varnish_Admin extends Nexcessnet_Turpentine_Model_Varnish_Admin {

    /**
     * @param $subPatterns regexs
     * @return array
     */
    public function flushMultiUrl( $subPatterns ) {
        $results = array();
        foreach( Mage::helper( 'turpentine/varnish' )->getSockets() as $socket ) {
            $socketName = $socket->getConnectionString();
            foreach ($subPatterns as $subPattern) {
                try {
                    // We don't use "ban_url" here, because we want to do lurker friendly bans.
                    // Lurker friendly bans get cleaned up, so they don't slow down Varnish.
                    $socket->ban('obj.http.X-Varnish-URL', '~', $subPattern);
                } catch (Mage_Core_Exception $e) {
                    $results[$socketName]['msg'][] = $e->getMessage();
                }
            }
        }
        return $results;
    }

	/**
	 * Generate and apply the config to the Varnish instances
	 *
	 * @param  Nexcessnet_Turpentine_Model_Varnish_Configurator_Abstract $cfgr
	 * @return bool
	 */
	public function applyConfig() {
		$result = array();
		$helper = Mage::helper( 'turpentine' );
		foreach( Mage::helper( 'turpentine/varnish' )->getSockets() as $socket ) {
			$cfgr = Zolago_Turpentine_Model_Varnish_Configurator_Abstract::getFromSocket( $socket );
			$socketName = $socket->getConnectionString();
			if( is_null( $cfgr ) ) {
				$result[$socketName] = 'Failed to load configurator';
			} else {
				$vcl = $cfgr->generate( $helper->shouldStripVclWhitespace('apply') );
				$vclName = Mage::helper( 'turpentine/data' )
					->secureHash( microtime() );
				try {
					$this->_testEsiSyntaxParam( $socket );
					$socket->vcl_inline( $vclName, $vcl );
					sleep( 1 ); //this is probably not really needed
					$socket->vcl_use( $vclName );
				} catch( Mage_Core_Exception $e ) {
					$result[$socketName] = $e->getMessage();
					continue;
				}
				$result[$socketName] = true;
			}
		}
		return $result;
	}

	/**
	 * Get a configurator based on the first socket in the server list
	 *
	 * @return Nexcessnet_Turpentine_Model_Varnish_Configurator_Abstract
	 */
	public function getConfigurator() {
		$sockets = Mage::helper( 'turpentine/varnish' )->getSockets();
		$cfgr = Zolago_Turpentine_Model_Varnish_Configurator_Abstract::getFromSocket( $sockets[0] );
		return $cfgr;
	}
}
