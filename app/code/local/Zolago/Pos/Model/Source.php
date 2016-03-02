<?php
class Zolago_Pos_Model_Source extends Varien_Object {
    
    
    /**
     * list of calendars
     *
     * @return array
     */

    public function toOptionHash() {
        $collection = Mage::getModel('zolagopos/pos')->getCollection();
        $vendorId = $this->getRequest()->getParam('id');
        $out = array (	
            '0' => Mage::helper('zolagopos')->__('-- empty --'),
        );        
        foreach ($collection as $item) {
            $out[$item->getPosId()] = $item->getName();
        }
        return $out;
    }
}