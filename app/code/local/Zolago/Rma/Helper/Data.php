<?php
class Zolago_Rma_Helper_Data extends Unirgy_Rma_Helper_Data{
	
	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @param string $status
	 * @return Zolago_Rma_Model_Rma
	 */
	public function processSaveStatus(Zolago_Rma_Model_Rma $rma, $status) {
		$oldStatus = $rma->getRmaStatus();
		if($status!=$oldStatus){
			$rma->setRmaStatus($status);
			$rma->getResource()->saveAttribute($rma, 'rma_status');
			// Trigger event
			Mage::dispatchEvent("zolagorma_rma_status_changed", array(
				"rma"			=> $rma, 
				"new_status"	=> $status,
				"old_status"	=> $oldStatus
			));
		}
		return $rma;
	}
	
	/**
	 * 
	 * @param type $items
	 * @return type
	 */
    public function getItemList($items) {
        $out = array();
        $child = array();
        foreach ($items as $item) {
            if ($parentId = $item->getParentItemId()) {
                $child[$parentId][]  = $item;
            }
        }
        foreach ($items as $item) {
            $max = intval($item->getQty());
            if (!$item->getParentItemId()) {
                for ($a = 0; $a<$max; $a++) {
                    $entity_id = $item->getEntityId();
                    if (!empty($child[$item->getOrderItemId()])) {                        
                        $name = '';
                        foreach ($child[$item->getOrderItemId()] as $ch) {
                            $name .= $ch->getName();
                        }
                    } else {
                        $name = $item->getName();
                    }
                    $out[$entity_id][$a] = array (
                                               'entityId' => $entity_id,
                                               'name' => $name,
                                           );
                }
            }
        }
        return $out;

    }    
} 