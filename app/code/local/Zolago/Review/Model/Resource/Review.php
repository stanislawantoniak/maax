<?php
/**
 * Review resource model
 *
 * @category    Zolago
 * @package     Zolago_Review
 */
class Zolago_Review_Model_Resource_Review extends Mage_Review_Model_Resource_Review
{
	
	
	const ATTRIBUTE_PRODUCT_RATING = "product_rating";
	const MAX_RATE = 5;
	
	
    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     * @return Mage_Review_Model_Resource_Review
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getWriteAdapter();
        /**
         * save detail
         */
        $detail = array(
            'title'     => $object->getTitle(),
            'detail'    => $object->getDetail(),
            'nickname'  => $object->getNickname(),
            'recommend_product'  => $object->getRecommendProduct()
        );

        $select = $adapter->select()
            ->from($this->_reviewDetailTable, 'detail_id')
            ->where('review_id = :review_id');
        $detailId = $adapter->fetchOne($select, array(':review_id' => $object->getId()));

        if ($detailId) {
            $condition = array("detail_id = ?" => $detailId);
            $adapter->update($this->_reviewDetailTable, $detail, $condition);
        } else {
            $detail['store_id']   = $object->getStoreId();
            $detail['customer_id']= $object->getCustomerId();
            $detail['review_id']  = $object->getId();
            $adapter->insert($this->_reviewDetailTable, $detail);
        }


        /**
         * save stores
         */
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = array('review_id = ?' => $object->getId());
            $adapter->delete($this->_reviewStoreTable, $condition);

            $insertedStoreIds = array();
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = array(
                    'store_id' => $storeId,
                    'review_id'=> $object->getId()
                );
                $adapter->insert($this->_reviewStoreTable, $storeInsert);
            }
        }
		
        // reaggregate ratings, that depend on this review
        $this->_aggregateRatings(
            $this->_loadVotedRatingIds($object->getId()),
            $object->getEntityPkValue()
        );
		
		
		
        return $this;
    }

	/**
	 * Inject transfer after product delete;
	 * @param Mage_Core_Model_Abstract $object
	 * @return Zolago_Review_Model_Resource_Review
	 */
	public function afterDeleteCommit(Mage_Core_Model_Abstract $object){
		$return = parent::afterDeleteCommit($object);
		if($object->isProductEntity($object)){
			$this->transferRatingToProduct($object);
		}
		return $return;
	}
	
	/**
	 * 
	 * @param int $productId
	 * @return \Zolago_Review_Model_Resource_Review
	 */
	public function transferRatingToProduct(Mage_Core_Model_Abstract $object) {
	
		
		$entityPkValue = $object->getEntityPkValue();
		$newValue = $this->getAvgRating($entityPkValue);
		
		$product = Mage::getModel("catalog/product")->setId($entityPkValue);
		/* @var $product Mage_Catalog_Model_Product */
		$productResource = $product->getResource();
		/* @var $productResource Mage_Catalog_Model_Resource_Product */
		$attribute = $this->getAttribute();
		
		$oldValue = $productResource->getAttributeRawValue(
				$entityPkValue, 
				$attribute->getAttributeCode(), 
				Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
		);
	
		// changes based on product attribute and current value
		if($newValue==$oldValue){
			return $this;
		}

		// Update attribute
		$actionModel = Mage::getSingleton('catalog/product_action');
		/* @var $actionModel Zolago_Catalog_Model_Product_Action */
		
		//Mage::log("Update to $newValue");

		$actionModel->updateAttributes(
				array($entityPkValue), 
				array(self::ATTRIBUTE_PRODUCT_RATING => $newValue), 
				// Rating is global attribute
				Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID 
		);
		
		return $this;
	}
	

	/**
	 * @param int $entityPkValue
	 * @return int
	 */
	public function getAvgRating($entityPkValue) {
			$select = $this->getReadConnection()->select();
			
			$select->from(
					array("agr" => $this->getTable('rating/rating_vote_aggregated')), 
					array("avg" => new Zend_Db_Expr('AVG(agr.percent_approved)'))
			);
			$select->where("agr.entity_pk_value=?", $entityPkValue);
			$select->where("agr.store_id=?", Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID); // possible bug with multistore?
			$select->group("agr.entity_pk_value");
			
			$valuePercent = $this->getReadConnection()->fetchOne($select);
			
			return $this->_caclualteValueForAttribute($valuePercent);
	}
	
	/**
	 * @return Mage_Catalog_Model_Resource_Eav_Attribute
	 */
	public function getAttribute() {
		return Mage::getSingleton('eav/config')->getAttribute(
				Mage_Catalog_Model_Product::ENTITY, 
				self::ATTRIBUTE_PRODUCT_RATING
		);
	}
	
	/**
	 * @param type $percent
	 * @return int
	 */
	protected function _caclualteValueForAttribute($percent) {
		
		$attribute = $this->getAttribute();
		
		if(!$attribute->getId()){
			Mage::throwException("No rating attribute");
		}
		
		if($percent===null){
			return 0;
		}
		
		$value = round(($percent/100) * self::MAX_RATE);
		$source = $attribute->getSource();
		
		/* @var $source Mage_Eav_Model_Entity_Attribute_Source_Abstract */
		foreach($source->getAllOptions() as $option){
			if($option['value']==$value){
				return $option['value'];
			}
		}
		
		return 0;
	}
}
