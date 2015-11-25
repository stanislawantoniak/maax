<?php
/**
 * get log controller
 */
class Modago_Integrator_GetLogFlioPController extends Modago_Integrator_Controller_Abstract {
    /**
     * get description file
     */
     public function indexAction() {
         $this->_checkAuthorization();
         $filename = Mage::helper('modagointegrator')->getPathLogFile();
         if (file_exists($filename)) {
             header("Content-Type: text/plain"); 
             readfile($filename);
         } else {
             echo 'No log file';
         }
         die();
     }     

}