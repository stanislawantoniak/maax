<?php

class Zolago_DropshipVendorRatings_Model_Resource_Review_Product_Collection 
	extends ZolagoOs_OmniChannelVendorRatings_Model_Mysql4_Review_Product_Collection {

	protected function _joinFields() {
		$reviewTable = Mage::getSingleton('core/resource')->getTableName('review/review');
		$reviewDetailTable = Mage::getSingleton('core/resource')->getTableName('review/review_detail');
		$reviewEntityTable = Mage::getSingleton('core/resource')->getTableName('review/review_entity');

		$this->addAttributeToSelect('name')
				->addAttributeToSelect('sku');

		$entityCondition = $this->getSelect()->getAdapter()->quoteInto(
				're.entity_id = rt.entity_id AND re.entity_code=?', 
				Mage_Review_Model_Review::ENTITY_PRODUCT_CODE
		);
		
		$this->getSelect()
				->join(
						array('rt' => $reviewTable), 
						'rt.entity_pk_value = e.entity_id', 
						array('review_id', 'created_at', 'entity_pk_value', 'status_id')
				)
				->join(
						array('re' => $reviewEntityTable), 
						$entityCondition,
						array()
				)
				->join(
						array('rdt' => $reviewDetailTable), 
						'rdt.review_id = rt.review_id'
				);
		return $this;
	}

}