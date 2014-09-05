<?php

class Zolago_Banner_Model_Finder extends Varien_Object
{
	
	public function __construct() {
		$inItems = func_get_arg(0);
		
		$grouped = array();
		$items = array();
		
		if(!is_array($inItems)){
			Mage::throwException("No banner items");
		}
		
		foreach($inItems as $item){
			$_item = new Varien_Object($item);
			$grouped[$_item->getBannerShow()]
				  [$_item->getType()]
				  [$_item->getPosition()]
				  [$_item->getPriority()] = $_item;
			$items[] = $_item;
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
		$slotIndex = 0;
		$positionIndex = 0;
		
		
		while($slotIndex<$request->getSlots() && !empty($candidates)){
			// Increment lookup position
			$positionIndex++;
			if(isset($candidates[$positionIndex]) && count($candidates[$positionIndex])){
				$key = min(array_keys($candidates[$positionIndex]));
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
		
		return $out;
		
	}
	
	/**
	 * @param array $array
	 * @param array $requestArray
	 * @return array
	 */
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