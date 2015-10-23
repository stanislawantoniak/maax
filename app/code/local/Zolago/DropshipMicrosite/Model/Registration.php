<?php

/**
 * Class Zolago_DropshipMicrosite_Model_Registration
 */
class Zolago_DropshipMicrosite_Model_Registration extends Unirgy_DropshipMicrosite_Model_Registration {

    /**
     * @return Unirgy_Dropship_Model_Vendor|Zolago_Dropship_Model_Vendor
     */
    public function toVendor()
    {
        $vendor = Mage::getModel('udropship/vendor')->load(Mage::getStoreConfig('udropship/microsite/template_vendor'));
        $carrierCode = $this->getCarrierCode() ? $this->getCarrierCode() : $vendor->getCarrierCode();
        $vendor->getShippingMethods();
        $vendor->unsetData('vendor_name');
        $vendor->unsetData('confirmation_sent');
        $vendor->unsetData('url_key');
        $vendor->unsetData('email');

        ////////
        $vendor->unsetData('billing_street');
        $vendor->unsetData('city');
        ////////


        $vendor->addData($this->getData());
        $vendor->setCarrierCode($carrierCode);
        Mage::helper('udropship')->loadCustomData($vendor);
        $vendor->setPassword(Mage::helper('core')->decrypt($this->getPasswordEnc()));
        $vendor->unsVendorId();
        $shipping = $vendor->getShippingMethods();
        $postedShipping = array();
        foreach ($shipping as $sId=>&$_s) {
            foreach ($_s as &$s) {
                if ($s['carrier_code']==$vendor->getCarrierCode()) {
                    $s['carrier_code'] = null;
                }
                unset($s['vendor_shipping_id']);
                $s['on'] = true;
                $postedShipping[$s['shipping_id']] = $s;
            }
        }
        unset($_s);
        unset($s);
        $vendor->setPostedShipping($postedShipping);
        $vendor->setShippingMethods($shipping);
        return $vendor;
    }

}