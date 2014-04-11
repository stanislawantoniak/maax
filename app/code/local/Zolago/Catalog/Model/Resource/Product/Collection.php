<?php
class Zolago_Catalog_Model_Resource_Product_Collection 
	extends Mage_Catalog_Model_Resource_Product_Collection
{
   public function addImagesCount($withExcluded=true) {
	   $mediaAttribute = Mage::getSingleton('eav/config')->
			   getAttribute(Mage_Catalog_Model_Product::ENTITY, "media_gallery");
	   
	   $select = $this->getSelect();
	   $adapter = $select->getAdapter();
	   
		$select->joinLeft(
			array("gallery"=>$this->getTable("catalog/product_attribute_media_gallery")), 
			"e.entity_id=gallery.entity_id",
			array()
		);
		
	   $select->group("e.entity_id");
	   
	   if(!$withExcluded){
		   $conditions = array(
			   "gallery.value_id=gallery_value.value_id",
			   $adapter->quoteInto("gallery_value.store_id=?", $this->getStoreId()),
			   $adapter->quoteInto("gallery_value.disabled=?", 0)
		   );
			$select->joinLeft(
				array("gallery_value"=>$this->getTable("catalog/product_attribute_media_gallery_value")), 
				join(" AND ", $conditions),
				array()
			 );
			
		$select->columns(array("images_count"=>new Zend_Db_Expr("IFNULL(COUNT(gallery_value.value_id),0)")));
	   }else{
			$select->columns(array("images_count"=>new Zend_Db_Expr("IFNULL(COUNT(gallery.value_id),0)")));
	   }
	   
	   
	   return $this;
   }
   
   public function setOrder($attribute, $dir = 'desc') {
	   if($attribute=="images_count"){
		   $this->getSelect()->order("images_count"." ". $dir);
	   }
	   return parent::setOrder($attribute, $dir);
   }
   
   public function load($printQuery = false, $logQuery = false) {
	   //Mage::log($this->getSelect()."");
	   return parent::load($printQuery, $logQuery);
   }

   protected function _getSelectCountSql($select = null, $resetLeftJoins = true) {
	   $select = parent::_getSelectCountSql($select, $resetLeftJoins);
	   $select->reset(Zend_Db_Select::GROUP);
	   return $select;
   }
   
}


