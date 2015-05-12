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
}
