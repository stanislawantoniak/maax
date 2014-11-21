<?php
class Zolago_Catalog_Model_Resource_Vendor_Product_Collection 
	extends Zolago_Catalog_Model_Resource_Vendor_Collection_Abstract
{
	protected $_productMockup;
	
  /**
   * @param bool $withExcluded
   * @return Zolago_Catalog_Model_Resource_Vendor_Product_Collection
   */
   public function addImagesCount($withExcluded=true) {
	   if($this->getFlag("images_count_added")){
		   return $this;
	   }
	   $mediaAttribute = Mage::getSingleton('eav/config')->
			   getAttribute(Mage_Catalog_Model_Product::ENTITY, "media_gallery");
	   $imageAttribute = Mage::getSingleton('eav/config')->
			   getAttribute(Mage_Catalog_Model_Product::ENTITY, "image");
	   /* @var $imageAttribute Mage_Catalog_Model_Resource_Eav_Attribute */
	   $select = $this->getSelect();
	   $adapter = $select->getAdapter();
	   
	   $imageTabele = $imageAttribute->getBackendTable();
	   $expression = null;
	   $select->joinLeft(
			   array("base_image_default"=>$imageTabele), 
			   sprintf(
					"base_image_default.entity_id=e.entity_id AND base_image_default.attribute_id=%s AND base_image_default.store_id=%s", 
					$adapter->quote($imageAttribute->getId()),
					$adapter->quote(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
			   ),
			   array()
	   );
	   
	   if($this->getStoreId()){
			$select->joinLeft(
					array("base_image_store"=>$imageTabele), 
					sprintf(
						 "base_image_store.entity_id=e.entity_id AND base_image_store.attribute_id=%s AND base_image_store.store_id=%s", 
						 $adapter->quote($imageAttribute->getId()),
						 $adapter->quote($this->getStoreId())
					),
					array()
			);
			$baseImageExpression = "IF(".
					"base_image_store.value_id>0,".
					"base_image_store.value!='no_selection',".
					"IF(".
						"base_image_default.value_id>0,".
						"base_image_default.value!='no_selection',".
						"0))";
	   }else{
			$baseImageExpression = "IF(base_image_default.value_id, base_image_default.value!='no_selection', 0)";
	   }
	   
	   if(!$withExcluded){
		   
		   $subselect = $select->getAdapter()->select();
		   $subselect->from(
				   array("gallery" => $this->getTable("catalog/product_attribute_media_gallery")),
				   array(new Zend_Db_Expr("COUNT(gallery.value_id)"))
			);
		   		   
		   $subselect->joinLeft(
				   array("gallery_value_default" => $this->getTable("catalog/product_attribute_media_gallery_value")),
				   $adapter->quoteInto("gallery_value_default.value_id=gallery.value_id AND gallery_value_default.store_id=?", 
						   Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID),
				   array()
		   );
		   
		   if($this->getStoreId()){
				$subselect->joinLeft(
				   array("gallery_value_store" => $this->getTable("catalog/product_attribute_media_gallery_value")),
				   $adapter->quoteInto("gallery_value_store.value_id=gallery.value_id AND gallery_value_store.store_id=?", 
						   $this->getStoreId()),
				   array()
				 );
				$subselect->where("IF(gallery_value_store.value_id>0, gallery_value_store.disabled, gallery_value_default.disabled)=?", 0);
		   }else{
			   $subselect->where("gallery_value_default.disabled=?", 0);
		   }
		   
		   $subselect->group("gallery.entity_id");
		   $subselect->where("gallery.entity_id=e.entity_id");
		   $subselect->where("gallery.attribute_id=?", $mediaAttribute->getId());

		   
		   $expression = new Zend_Db_Expr("IFNULL(($subselect), $baseImageExpression)");
		   
		   $select->columns(array("images_count"=> $expression));
	   }else{
		 $expression = new Zend_Db_Expr("GREATEST(COUNT(gallery.value_id),$baseImageExpression)");
		 $select->joinLeft(
			 array("gallery"=>$this->getTable("catalog/product_attribute_media_gallery")), 
			 $adapter->quoteInto("e.entity_id=gallery.entity_id AND gallery.attribute_id=?", $mediaAttribute->getId()),
			 array(array("images_count"=>$expression))
		 );
		 $select->group("e.entity_id");
	   }
	   $this->_joinFields['images_count'] = array(
		   "table" => false,
		   "field" => $expression
	   );
	   
	   $this->setFlag("images_count_added", true);
	   return $this;
   }
   
   /**
    * @param Varien_Db_Select | null $select
    * @param bol $resetLeftJoins
    * @return Varien_Db_Select
    */
   protected function _getSelectCountSql($select = null, $resetLeftJoins = true) {
	   $select = parent::_getSelectCountSql($select, $resetLeftJoins);
	   $select->reset(Zend_Db_Select::GROUP);
	   return $select;
   }
   
   
   	/**
	 * @return Zolago_Catalog_Model_Vendor_Product_Grid
	 */
	public function getGridModel() {
		return Mage::getSingleton('zolagocatalog/vendor_product_grid');
	}
	
	/**
	 * Add thumbs
	 * @param array $item
	 * @return array
	 */
	protected function _mapItem(array $item) {
		$mockup = $this->_getProductMockup();
		$mockup->setData($item);
		$mockup->setId($item['entity_id']);
		
		if(isset($item['thumbnail'])){
			$thumbUrl = Mage::helper('catalog/image')->
				init($mockup, 'thumbnail')->
				keepAspectRatio(true)->
				constrainOnly(true)->
				keepFrame(true)->
				resize(
					Zolago_Catalog_Block_Vendor_Product_Grid::THUMB_WIDTH,
					Zolago_Catalog_Block_Vendor_Product_Grid::THUMB_HEIGHT
				);

			$item['thumbnail_url'] = (string)$thumbUrl;
			if(!empty($item['thumbnail']) && $item['thumbnail']!="no_selection"){
				$item['thumbnail'] = Mage::getBaseUrl("media") . "catalog/product/" . $item['thumbnail'];
			}else{
				$item['thumbnail'] = null;
			}
		}else{
			$item['thumbnail'] = null;
		}
		
		return $item;
	}
	
	/**
	 * @return Mage_Catalog_Model_Product
	 */
	protected function _getProductMockup() {
		if(!$this->_productMockup){
			$this->_productMockup = Mage::getModel("catalog/product");
		}
		return $this->_productMockup;
	}
}


