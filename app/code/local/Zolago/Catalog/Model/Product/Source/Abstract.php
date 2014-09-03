<?php
/** 
 *source for flag options
 */
abstract class Zolago_Catalog_Model_Product_Source_Abstract 
        extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

	protected $_force = false;

	
	public function setForceTranslate($value) {
		$this->_force = $value;
		return $this;
	}
	
}