<?php

class Zolago_DropshipVendorAskQuestion_Block_Product_Question extends Unirgy_DropshipVendorAskQuestion_Block_Product_Question
{
    public function getFormAction()
    {
        return $this->getUrl('udqa/customer/post');
    }

/*	todo: check if it's needed
 *     public function getVendors()
    {
        $product = $this->getProduct();
        $simpleProducts = array();
        if ($product->getTypeId()=='configurable') {
            $simpleProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
        }
        array_unshift($simpleProducts, $product);
        $vendors = Mage::getSingleton('udropship/source')->getVendors(true);
        $vIds = array();
        $isUdm = Mage::helper('udropship')->isUdmultiActive();
        foreach ($simpleProducts as $p) {
            if ($isUdm) {
                $_vIds = $p->getMultiVendorData();
                $_vIds = is_array($_vIds) ? array_keys($_vIds) : array();
                $vIds = array_merge($vIds, $_vIds);
            } else {
                $vIds[] = $p->getUdropshipVendor();
            }
        }
        $vIds = array_filter($vIds);
        return array_intersect_key($vendors, array_flip($vIds));
    }
    public function addToParentGroup($groupName)
    {
        if ($this->getParentBlock()) {
            $this->getParentBlock()->addToChildGroup($groupName, $this);
        }
        return $this;
    }
*/

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
}