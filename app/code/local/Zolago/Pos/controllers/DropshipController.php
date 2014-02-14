<?php
class Zolago_Pos_DropshipController 
    extends Mage_Core_Controller_Front_Action
{
    
    /**
     * pos list
     */
    public function listAction() {
        Mage::getSingleton('udropship/session')->addError(Mage::helper("udropship")
                ->__('EDIT POS is not implemented yet!!!!'));                            
        return $this->_redirectReferer();
    }
}
