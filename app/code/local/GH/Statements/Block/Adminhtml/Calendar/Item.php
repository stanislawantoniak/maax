<?php
class GH_Statements_Block_Adminhtml_Calendar_Item extends Mage_Adminhtml_Block_Widget_Container {
 
    protected function _getCalendarName() {
        $id = $this->getRequest()->get('id');
        $obj = Mage::getModel('ghstatements/calendar')->load($id);
        return $obj->getName();
    }
    
}