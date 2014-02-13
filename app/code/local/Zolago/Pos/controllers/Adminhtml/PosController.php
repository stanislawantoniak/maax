<?php
class Zolago_Pos_Adminhtml_PosController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function newAction() {
        $this->_forward("edit");
    }
    
    public function editAction(){
        $posId = $this->getRequest()->getParam("pos_id");
        try{
            $model = Mage::getModel("zolagopos/pos")->load($posId);
            Mage::register("zolagopos_current_pos", $model);
        }catch(Mage_Core_Exception $e){
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        }catch(Exception $e){
            $this->_getSession()->addError(Mage::helper('zolagopos')->__("Cannot find POS $e"));
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function vendorgridAction() {
        $posId = $this->getRequest()->getParam("pos_id");
        try{
            $model = Mage::getModel("zolagopos/pos")->load($posId);
            Mage::register("zolagopos_current_pos", $model);
        }catch(Mage_Core_Exception $e){
            Mage::logException($e);
        }
        $this->loadLayout();
        $this->renderLayout();
    }
}
