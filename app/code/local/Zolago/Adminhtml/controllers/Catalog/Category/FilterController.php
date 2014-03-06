<?php
class Zolago_Adminhtml_Catalog_Category_FilterController 
	extends Mage_Adminhtml_Controller_Action
{
    
    public function editAction(){
		$category=$this->_registerObject();
		$resMapper = Mage::getResourceModel('zolagomapper/mapper');
		/* @var $resMapper Zolago_Mapper_Model_Resource_Mapper */
		var_dump($resMapper->getAttributesByCategory($category->getId()));
		die;
        $this->loadLayout();
        $this->renderLayout();
    }
	
	/**
	 * @return Mage_Catalog_Model_Category
	 */
	protected function _registerObject(){
		if(!Mage::registry("current_category")){
			$category = Mage::getModel("catalog/category");
			/* @var $category Mage_Catalog_Model_Category */
			$paramId = $this->getRequest()->getParam("category_id");
			if($paramId){
				$category->load($paramId);
			}
			
			Mage::register("current_category", $category);
		}
		return Mage::registry("current_category");
	}
        
}
