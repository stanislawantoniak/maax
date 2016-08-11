<?php
class Zolago_Pos_Model_Source extends Varien_Object {
    
    
    /**
     * list of POSes
     *
     * @return array
     */
    public function toOptionHash() {
		/** @var Zolago_Pos_Model_Resource_Pos_Collection $collection */
        $collection = Mage::getModel('zolagopos/pos')->getCollection();
        $out = array (	
            '0' => Mage::helper('zolagopos')->__('-- empty --'),
        );        
        foreach ($collection as $item) {
            $out[$item->getPosId()] = $item->getName();
        }
        return $out;
    }
}