<?php

class Zolago_Banner_Model_Finder extends Varien_Object
{
	
	public function __construct() {
		$inItems = func_get_arg(0);
		$grouped = array();
		$items = array();
		$now = new Zend_Date();
		
		if(is_array($inItems)){
			Mage::throwException("No banner items");
		}
		
		foreach($inItems as $item){
			$_item = new Varien_Object($item);
			$start = new Zend_Date($_item->getCampaignDateFrom());
			$stop = new Zend_Date($_item->getCampaignDateTo());
			
			if($now->compare($start)>0 && $now->compare($stop)<0){
				$grouped[$_item->getBannerShow()]
					  [$_item->getType()]
					  [$_item->getPosition()]
					  [$_item->getPriority()] = $item;
				$items[] = $item;
			}
			
		}
		parent::__construct(array(
			"grouped"=>$grouped,
			"items" => $items
		));
	}
	
	/**
	 * @param Varien_Object $request - 
	 *      banner_show - string (required)
	 *		type - string (required)
	 *		slots - int (required)
	 */
	public function request($request) {
		
		$grouped = $this->getGrouped();
		$items = $this->getItems();
		
		
		$requestToArray = array(
			$request->getBannerShow(),
			$request->getType()
		);
		
		$candidates = $this->_requestArray($grouped, $requestToArray);
		
		// Empty items - null returned
		if(empty($items)){
			return array();
		}
		
		// 
		$out = array();
		$slotIndex = 1;
		$positionIndex = 0;
		
		
		while($slotIndex<$request->getSlots() && $i<count($items)){
			// Increment lookup position
			$positionIndex++;
			if(isset($candidates[$positionIndex]) && count($candidates[$positionIndex])){
				$key = min(array_keys($candidates));
				$out[] = $candidates[$positionIndex][$key];
				$slotIndex++;
				// Remove item
				unset($candidates[$positionIndex][$key]);
			}
			
			// Circular
			if($positionIndex<$request->getSlots()){
				$positionIndex = 0;
			}
			
			
		}
		
	}
	
	protected function _getFirstItem(array $candidates) {
		return $candidates[min(array_keys($candidates))];
	}
	
	protected function _requestArray(array $array, array $requestArray){
		$candidate = $array;
		foreach ($requestArray as $key){
			if(isset($candidate[$key])){
				$candidate = $candidate[$key];
			}else{
				return null;
			}
		}
		return $candidate;
	}
	

}