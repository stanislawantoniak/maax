<?php


class Zolago_Dropship_Block_Adminhtml_Vendor_Helper_Form_Category 
    extends Varien_Data_Form_Element_Abstract{

	
	/**
	 * 
	 * @return Mage_Core_Model_Resource_Store_Group_Collection
	 */
	public function getStoreGroupCollection() {
		return Mage::getResourceModel('core/store_group_collection');
	}
	
	public function isMultiWebsites() {
		return !Mage::app()->isSingleStoreMode();
	}
	
    public function getValueElementChooserUrl($gid){
		$url = 'zolagoosadmin/adminhtml_widget/chooser'
			.'/attribute/category_ids'
			. '/form/chooser_text_'.$gid;	
        return Mage::helper('adminhtml')->getUrl($url);
    }

	public function getElementHtml() {
		$block = Mage::getSingleton('core/layout')->
				createBlock("core/template")->
				setTemplate("zolagodropship/vendor/helper/form/category.phtml")->
				setElement($this);
		return $block->toHtml();
	}

}