<?php
class Zolago_Adminhtml_Block_Catalog_Category_Filters extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
    {
        $this->_objectId = 'filter_id';
        $this->_blockGroup = 'zolagoadminhtml';
        $this->_controller = 'catalog_category_filter';
				
		parent::__construct();
	}
	
	public function getHeaderText() {
		return Mage::helper('adminhtml')->__("Edit filters for %s", $this->getCategoryName());
	}

}
