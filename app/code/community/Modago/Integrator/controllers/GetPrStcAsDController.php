<?php
/**
 * get description controller
 */
class Modago_Integrator_GetPrStcAsDController extends Modago_Integrator_Controller_Abstract {
    /**
     * get description file
     */
     public function indexAction() {
         $this->_getFile(Modago_Integrator_Helper_Data::FILE_STOCKS,true);
     }     

}