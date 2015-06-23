<?php
class GH_Dhl_Adminhtml_DhlController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function newAction() {
        $this->_forward("edit");
    }
    
    public function editAction(){

        $this->loadLayout();
        $this->renderLayout();
    }
   
    
    public function saveAction() {}
        

    
    
    public function deleteAction() {}
        

}
