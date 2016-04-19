<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrosite_Model_Registration extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('umicrosite/registration');
        parent::_construct();
    }
/*
    protected function _afterLoad()
    {
        parent::_afterLoad();
        Mage::helper('udropship')->loadCustomData($this);
    }
*/
    public function validate()
    {
        $hlp = Mage::helper('umicrosite');
        $dhlp = Mage::helper('udropship');
        extract($this->getData());
        
        $_isQuickRegister = Mage::getStoreConfig('udropship/microsite/allow_quick_register');

        if (!isset($vendor_name) || !isset($telephone) || !isset($email) ||
            !isset($password) || !isset($password_confirm)
        ) {
            Mage::throwException($hlp->__('Incomplete form data'));
        }
        if (!$_isQuickRegister) {
            if (!isset($carrier_code) || !isset($url_key)
                || !isset($street1) || !isset($city) || !isset($country_id)
            ) {
                Mage::throwException($hlp->__('Incomplete form data'));
            }
        }
        if ($password!=$password_confirm) {
            Mage::throwException($hlp->__('Passwords do not match'));
        }
        $collection = Mage::getModel('udropship/vendor')->getCollection()
            ->addFieldToFilter('email', $email);
        foreach ($collection as $dup) {
            if (Mage::getStoreConfig('udropship/vendor/unique_email')) {
                Mage::throwException($dhlp->__('A vendor with supplied email already exists.'));
            }
            if (Mage::helper('core')->validateHash($password, $dup->getPasswordHash())) {
                Mage::throwException($dhlp->__('A vendor with supplied email and password already exists.'));
            }
        }
        if (isset($url_key)) {
            $vendor = Mage::getModel('udropship/vendor')->load($url_key, 'url_key');
            if ($vendor->getId()) {
                Mage::throwException($hlp->__('This subdomain is already taken, please choose another.'));
            }
            if (Mage::helper('udropship')->isUrlKeyReserved($url_key)) {
                Mage::throwException(Mage::helper('udropship')->__('This URL Key is reserved. Please choose another.'));
            }
        }
        if (Mage::getStoreConfig('udropship/vendor/unique_vendor_name')) {
            $collection = Mage::getModel('udropship/vendor')->getCollection()
                ->addFieldToFilter('vendor_name', $vendor_name);
            foreach ($collection as $dup) {
                Mage::throwException(Mage::helper('udropship')->__('A vendor with supplied name already exists.'));
            }
        }
        $this->setStreet(@$street1."\n".@$street2);
        $this->setPasswordEnc(Mage::helper('core')->encrypt($password));
        $this->setPasswordHash(Mage::helper('core')->getHash($password, 2));
        $this->unsPassword();
        $this->setRemoteIp($_SERVER['REMOTE_ADDR']);
        $this->setRegisteredAt(now());
        $this->setStoreId(Mage::app()->getStore()->getId());

        return $this;
    }

    protected function _afterSave()
    {
        if ($this->_inAfterSave) {
            return;
        }
        $this->_inAfterSave = true;

        parent::_afterSave();

        if (!empty($_FILES)) {
            $baseDir = Mage::getConfig()->getBaseDir('media').DS.'registration'.DS.$this->getId();
            Mage::getConfig()->createDirIfNotExists($baseDir);
            foreach ($_FILES as $k=>$img) {
                if (empty($img['tmp_name']) || empty($img['name']) || empty($img['type'])) {
                    continue;
                }
                if (!@move_uploaded_file($img['tmp_name'], $baseDir.DS.$img['name'])) {
                    Mage::throwException('Error while uploading file: '.$img['name']);
                }
                $this->setData($k, 'registration/'.$this->getId().'/'.$img['name']);
            }
            $this->save();
        }
        $this->_inAfterSave = false;
    }

    public function toVendor()
    {
        $vendor = Mage::getModel('udropship/vendor')->load(Mage::getStoreConfig('udropship/microsite/template_vendor'));
        $carrierCode = $this->getCarrierCode() ? $this->getCarrierCode() : $vendor->getCarrierCode();
        $vendor->getShippingMethods();
        $vendor->unsetData('vendor_name');
        $vendor->unsetData('confirmation_sent');
        $vendor->unsetData('url_key');
        $vendor->unsetData('email');
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