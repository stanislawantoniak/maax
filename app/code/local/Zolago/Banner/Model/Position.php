<?php

class Zolago_Banner_Model_Finder extends Varien_Object
{
	
	public function __construct() {
		$grouped = array();
		$items = array();
		
		foreach(func_get_args() as $item){
			$_item = new Varien_Object($item);
			$grouped[$_item->getBannerShow()]
				  [$_item->getType()]
				  [$_item->getPosition()]
				  [$_item->getPriority()] = $item;
			$items[] = $item;
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
	 *		position - int (required)
	 *		priority - int (required)
	 */
	public function request(Varien_Object $request) {
		$candidate = null;
		$grouped = $this->getGrouped();
		$items = $this->getItems();
		
		// Empty items - null returned
		if(empty($items)){
			return null;
		}
		
		// Just one item - return it
		if(count($items)==1){
			return $items[0];
		}
		
		$requestToArray = array(
			$request->getBannerShow(),
			$request->getType(),
			$request->getPosition(),
			$request->getPriority()
		);
		
		$candidate = $this->_requestArray($grouped, $requestToArray);
		
		// Prefect match
		if(!is_null($candidate)){
			return $candidate;
		}
		
		////////////////////////////////////////////////////////////////////////
		// Fallbacks....
		////////////////////////////////////////////////////////////////////////
		
		// Not found but some priorioty in current position exists
		// Return the first priority
		$priorioty = array_pop($requestToArray);
		$candidates = $this->_requestArray($grouped, $requestToArray);
		if(is_array($candidates) && !empty($candidates)){
			return $this->_getFirstItem($candidates);
		}
		
		// Not found in candiadate in - check other positions
		// Return after and before positions best priorioty 
		// or first priorioty
		$position = array_pop($requestToArray);
		$positions = $this->_requestArray($grouped, $requestToArray);
		if(is_array($positions) && !empty($positions)){
			unset($positions[$position]);
			$before = $after = array();
			foreach(array_values($positions) as $_position){
				if($item = current($_position)){
					// Start from after, then add before positions
					if($item->getPosition()>$position){
						$before[] = $_position;
					}else{
						$after[] = $_position;
					}
				}
			}
			// Reordered array
			$positions = $before + $after; 
			
			foreach($positions as $_position){
				if(empty($_position)){
					continue;
				}
				// Prefered priorioty found in posiotion
				if(isset($_position[$priorioty])){
					return $_position[$priorioty];
				}
				
				return $this->_getFirstItem($_position);
				
			}
		}
		
		return null;
		
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