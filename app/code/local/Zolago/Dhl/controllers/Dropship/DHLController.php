<?php
/**
 * dhl controller
 */
class Zolago_DHL_Dropship_DHLController extends Zolago_Dropship_Controller_Vendor_Abstract {
    
    /**
     * dhl test
     */
    public function indexAction() {
        $model = Mage::getModel('zolagodhl');
        die('ok');
    }
}