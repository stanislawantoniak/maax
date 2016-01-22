<?php
/**
 * remember link after login
 */

class Zolago_Dropship_Block_Vendor_Login 
    extends Mage_Core_Block_Template {
    
    
    public function getRedirectUrl() {
        $url = Mage::registry('redirect_login_url');
        return $url;
    }
}