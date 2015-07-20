<?php
class GH_Statements_Model_Source_Calendar extends Varien_Object {
    
    
    /**
     * list of calendars
     *
     * @return array
     */

    public function toOptionHash() {
        $collection = Mage::getModel('ghstatements/calendar')->getCollection();
        $out = array (	
            '0' => Mage::helper('ghstatements')->__('-- empty --'),
        );        
        foreach ($collection as $item) {
            $out[$item->getCalendarId()] = $item->getName();
        }
        return $out;
    }
}