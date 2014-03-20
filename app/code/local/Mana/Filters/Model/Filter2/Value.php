<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Models/DB-backed model */
/**
 * INSERT HERE: what is this model for 
 * @author Mana Team
 */
class Mana_Filters_Model_Filter2_Value extends Mana_Db_Model_Object {
    protected $_eventPrefix = 'mana_filter_value';
    /**
     * Invoked during model creation process, this method associates this model with resource and resource
     * collection classes
     */
	protected function _construct() {
		$this->_init(strtolower('Mana_Filters/Filter2_Value'));
	}

	public function loadByFilterPosition($filterId, $position) {
	    /* @var $resource Mana_Filters_Resource_Filter2_Value */
	    $resource = $this->_getResource();
        $resource->loadByFilterPosition($this, $filterId, $position);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;

        return $this;
    }
}
