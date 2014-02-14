<?php
class Zolago_Dropship_PosController 
    extends Zolago_Dropship_Controller_Abstract
{
    
    /**
     * pos list
     */
    public function listAction() {
        Mage::getSingleton('udropship/session')->addError(Mage::helper("zolagopos")
                ->__('EDIT POS is not implemented yet!!!!'));                            
        $this->_setTheme();
        $this->loadLayout();
        $this->renderLayout();
                        
    }
}
