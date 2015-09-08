<?php
/** 
 * Source for flag options
 */
abstract class Zolago_Catalog_Model_Product_Source_Abstract 
        extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    /**
     * Force getAllOptions to don't take from cache ( $this->_options )
     * Note: used for solr indexing and correct language in process
     * @var bool
     */
	protected $_force = false;

    /**
     * @param $value
     * @return $this
     */
	public function setForceTranslate($value) {
		$this->_force = $value;
		return $this;
	}
	
}