<?php

class Zolago_Review_Model_Review  extends Unirgy_DropshipVendorRatings_Model_Review
{
	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @return bool
	 */
	public function isProductEntity(Mage_Core_Model_Abstract $object) {
		return $object->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE)
				==$object->getEntityId();
	}
	
	/**
	 * 
	 * @return type
	 */
	public function aggregate() {
		$return = parent::aggregate();
		if($this->isProductEntity($this)){
			$this->getResource()->transferRatingToProduct($this);
		}
		return $return;
	}

    /**
     * @return mixed
     */
    public function getDetailHtmlFormatted()
    {
        return preg_replace('/^\s*(?:<br\s*\/?>\s*)*/i', '', $this->getDetailHtml());
    }

}