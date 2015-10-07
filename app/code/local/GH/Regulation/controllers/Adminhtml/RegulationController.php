<?php
/**
 * controller for crud with documents kind
 */
class GH_Regulation_Adminhtml_RegulationController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * show kind grid
     */
     
     public function kindAction() {
         $this->loadLayout();
         $this->renderLayout();
     }
     
    /**
     * show type grid
     */
     public function typeAction() {
         $this->loadLayout();
         $this->renderLayout();
     }
    /**
     * show list grid
     */
     public function listAction() {
         $this->loadLayout();
         $this->renderLayout();
     }

}