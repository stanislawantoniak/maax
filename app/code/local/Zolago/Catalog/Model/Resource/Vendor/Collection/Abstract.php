<?php
abstract class Zolago_Catalog_Model_Resource_Vendor_Collection_Abstract
	extends Mage_Catalog_Model_Resource_Product_Collection
{
 
	
	/**
	 * @return array
	 */
	public function prepareRestResponse(array $sort, array $range) {
		
		$collection = $this;
		
		$select = $collection->getSelect();
		/* @var $select Varien_Db_Select */
		
		if($sort){
			$select->order($sort['order'] . " " . $sort['dir']);
		}
		
		// Pepare total
		$totalSelect = clone $select;
		$adapter = $select->getAdapter();
		
		$totalSelect->reset(Zend_Db_Select::COLUMNS);
		$totalSelect->reset(Zend_Db_Select::ORDER);
		$totalSelect->resetJoinLeft();
		$totalSelect->columns(new Zend_Db_Expr("COUNT(e.entity_id)"));
		
		$total = $adapter->fetchOne($totalSelect);

		// Pepare range
		$start = $range['start'];
		$end = $range['end'];
		if($end > $total){
			$end = $total;
		}
		// Make limit
		$select->limit($end-$start, $start);
		
		$items = $adapter->fetchAll($select);
		
		
		foreach($items as &$item){
			$item['can_collapse'] = true;//
			$item['entity_id'] = (int)$item['entity_id'];
			//$item['campaign_regular_id'] = "Lorem ipsum dolor sit manet"; /** @todo impelemnt **/
			$item['store_id'] = $collection->getStoreId();
			$item = $this->_mapItem($item);
		}
		
		return array(
			"items" => $items,
			"start" => $start,
			"end"	=> $end,
			"total" => $total
		); 
	}
	
	/**
	 * @param array $item
	 * @return array
	 */
	protected function _mapItem(array $item) {
		return $item;
	}
   
}


