<?php
abstract class Zolago_Common_Model_Log_Abstract {
	
	abstract protected function _logEvent($object, $comment);
	
	/**
	 * @param array $fileds
	 * @param Varien_Object $object1
	 * @param Varien_Object $object2
	 * @return array
	 */
	protected function _prepareChangeLog(array $fileds, Varien_Object $object1, Varien_Object $object2) {
		$out = array();
		foreach(array_keys($fileds) as $key){
			$old = (string)$object1->getData($key);
			$new = (string)$object2->getData($key);
			if(trim($new)!=trim($old)){
				$out[] = $fileds[$key] . ": " . $old . "&rarr;" . $new; 
			}
		}
		return $out;
	}
	
}
