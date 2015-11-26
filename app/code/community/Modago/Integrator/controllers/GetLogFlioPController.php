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
         if ($oldFlag = $this->getRequest()->getParam('old',false)) {
             $filename .= '.old';
         }
         if (file_exists($filename)) {
             $this->addHttpHeaders($filename);
             readfile($filename);
             if (!$oldFlag) {
                 rename($filename,$filename.'.old');
             }
         } else {
             echo 'No log file';
         }
         die();
     }     

}