<?php
class Zolago_Dropship_PosController 
    extends Mage_Core_Controller_Front_Action
{
    
    /**
     * pos list
     */
    public function listAction() {
        Mage::getSingleton('udropship/session')->addError(Mage::helper("zolagopos")
                ->__('EDIT POS is not implemented yet!!!!'));                            
        return $this->_redirectReferer();
    }
}
