<?php
/**
 * get description controller
 */
class Modago_Integrator_GetPrDscZxCController extends Modago_Integrator_Controller_Abstract {
    /**
     * get description file
     */
     public function indexAction() {
         $generate = $this->getRequest()->getParam('generate',false);
         $this->_getFile(Modago_Integrator_Helper_Data::FILE_DESCRIPTIONS,$generate);
     }     

}