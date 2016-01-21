<?php

/**
 * Class Zolago_Catalog_Model_Category_Filter
 *
 * @method string getFilterId()
 * @method string getAttributeId()
 * @method string getCategoryId()
 * @method string getSortOrder()
 * @method string getShowMultiple()
 * @method string getUseSpecifiedOptions()
 * @method string getSpecifiedOptions()
 * @method string getFrontendRenderer()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method string getCanShowMore()
 * @method string getParentAttributeId()
 * @method string getIsRolled()
 *
 * @method $this setFilterId($value)
 * @method $this setAttributeId($value)
 * @method $this setCategoryId($value)
 * @method $this setSortOrder($value)
 * @method $this setShowMultiple($value)
 * @method $this setUseSpecifiedOptions($value)
 * @method $this setSpecifiedOptions($value)
 * @method $this setFrontendRenderer($value)
 * @method $this setCreatedAt($value)
 * @method $this setUpdatedAt($value)
 * @method $this setCanShowMore($value)
 * @method $this setParentAttributeId($value)
 * @method $this setIsRolled($value)
 */
class Zolago_Catalog_Model_Category_Filter extends Mage_Core_Model_Abstract{


	protected function _construct() {
        $this->_init('zolagocatalog/category_filter');
    }
    protected $_eventPrefix = "zolago_catalog_category_filter";

	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Abstract|false
	 */
	public function getAttribute() {
		if(!$this->hasData("attribute")){
			if($this->getAttributeId()){
				$eav = Mage::getSingleton('eav/config');
				/* @var $eav Mage_Eav_Model_Config */
				$this->setData("attribute", $eav->getAttribute(
						Mage_Catalog_Model_Product::ENTITY, $this->getAttributeId()
				));
			}
		}
		return $this->getData("attribute");
	}
  
    
}

