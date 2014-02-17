<?php
class Zolago_Pos_Dropship_PosController 
    extends Zolago_Dropship_Controller_Vendor_Abstract
{
	/**
	 * Pos listing action
	 */
	public function indexAction(){
		$this->_renderPage(null,'zolagopos');
	}
	
	/**
	 * Pos Edit
	 */
	public function editAction(){
		$this->_registerModel();
		$this->_renderPage(null,'zolagopos');
	}
	
	/**
	 * Pos Edit
	 */
	public function newAction(){
		$this->_forward("edit");
	}
	
	
	/**
	 * Save Pos
	 */
	public function saveAction(){
		$this->_registerModel();
	}
	
	/**
	 * Register current model to use by blocks
	 */
	protected function _registerModel(){
		$posId = $this->getRequest()->getParam("pos_id");
		$pos = Mage::getModel("zolagopos/pos");
		if($posId){
			$pos->load($posId);
		}
		Mage::register("current_pos", $pos);
	}
}
