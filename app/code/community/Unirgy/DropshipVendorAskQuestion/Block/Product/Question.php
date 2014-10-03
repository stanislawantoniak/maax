<?php

class Unirgy_DropshipVendorAskQuestion_Block_Product_Question extends Mage_Catalog_Block_Product_View_Description
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
		$vendors = Mage::getSingleton('udropship/source')->getVendors();
		$local = $this->getLocalVendorId();
		unset($vendors[$local]);
		return $vendors;
	}

	public function getLocalVendorId() {
		return Mage::helper('udropship/data')->getLocalVendorId();
	}

	public function isGallery()
	{
		return in_array('help_contact_gallery', $this->getLayout()->getUpdate()->getHandles());
	}
}