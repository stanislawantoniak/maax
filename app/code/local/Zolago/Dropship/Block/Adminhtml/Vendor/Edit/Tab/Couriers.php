<?php
/**
 * organize vendor tabs
 */
class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Couriers extends Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Abstract {

    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_form');
        $this->configKey = 'courier';
    }


}
