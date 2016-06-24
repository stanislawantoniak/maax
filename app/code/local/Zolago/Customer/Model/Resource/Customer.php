<?php

class Zolago_Customer_Model_Resource_Customer extends Mage_Customer_Model_Resource_Customer {

	/**
	 * Retrieve customer entity default attributes
	 * FIX: bug with is_active
	 *
	 * @return array
	 */
	protected function _getDefaultAttributes() {
		return array(
			'entity_type_id',
			'attribute_set_id',
			'created_at',
			'updated_at',
			'increment_id',
			'store_id',
			'website_id',
			'is_active'
		);
	}
}
