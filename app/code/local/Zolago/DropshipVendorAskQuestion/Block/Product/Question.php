<?php

class Zolago_DropshipVendorAskQuestion_Block_Product_Question extends Unirgy_DropshipVendorAskQuestion_Block_Product_Question
{
	protected $po;

    public function getFormAction()
    {
        return $this->getUrl('udqa/customer/post',array("_secure"=>true));
    }

	public function getVendorsList() {
        /** @var Zolago_Dropship_Model_Source $modelUds */
        $modelUds = Mage::getSingleton('zolagodropship/source');
		$vendors = $modelUds->getCanAskBrandshops();
        $local = $this->getLocalVendorId();
        $v = array();
        foreach ($vendors as $vendor) {
            /** @var Zolago_Dropship_Model_Vendor $vendor */
            $v[$vendor->getVendorId()] = $vendor->getVendorName();
        }
		unset($v[$local]);
		return $v;
	}

	public function getLocalVendorId() {
        /** @var Zolago_Dropship_Helper_Data $hlp */
        $hlp = Mage::helper('udropship/data');
		return $hlp->getLocalVendorId();
	}

	public function isGallery()
	{
		return in_array('help_contact_gallery', $this->getLayout()->getUpdate()->getHandles());
	}

	public function isOwnStore() {
		/** @var Zolago_Common_Helper_Data $hlp */
		$hlp = Mage::helper('zolagocommon');
		return $hlp->isOwnStore();
	}

	public function getOwnStoreVendorId() {
		return Mage::app()->getWebsite()->getVendorId();
	}

	/**
	 * returns array with customer's ['name'], ['email'] and ['id'] by po id
	 * @param $poId
	 * @return array
	 */
	public function getCustomerDataByPoId($poId) {
		$po = $this->getPo($poId);
		if($po->getId()) {
			$out = array();
			if(!is_null($po->getCustomerId())) { //registered customer po
				/** @var Zolago_Customer_Model_Customer $customer */
				$customer = Mage::getModel('zolagocustomer/customer')->load($po->getCustomerId());
				$out['name'] = $customer->getName();
				$out['email'] = $customer->getEmail();
				$out['id'] = $customer->getId();
			} else { //guest po
				$out['name'] = $po->getShippingAddress()->getName();
				$out['email'] = $po->getCustomerEmail();
				$out['id'] = null;
			}
			return $out;
		}
		return array();
	}

	public function isPoContactTokenValid($poId,$poToken) {
		$po = $this->getPo($poId);
		return $po->getId() && $po->getContactToken() == $poToken;
	}

	/**
	 * @param int|string $poId
	 * @return Zolago_Po_Model_Po
	 */
	public function getPo($poId) {
		if(!$this->po || $this->po->getId() != $poId) {
			/** @var Zolago_Po_Model_Po $po */
			$po = Mage::getModel('zolagopo/po');
			$this->po = $po->load($poId);
		}
		return $this->po;
	}
}