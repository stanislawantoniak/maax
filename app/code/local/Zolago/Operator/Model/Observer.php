<?php
class Zolago_Operator_Model_Observer {
	
	protected $_safeActions = array(
		"login",
		"logout",
		"password",
		"passwordPost",
	);
	
	/**
	 * @param type $observer
	 * @return bool
	 */
	public function handleControllerActionPredispatch($observer) {
		$event = $observer->getEvent();
		$controller = $event->getControllerAction();
		/* @var $controller Mage_Core_Controller_Front_Action */
		
		// Apply only for dropship conntrollers
		if($controller instanceof Unirgy_Dropship_Controller_VendorAbstract){
			$request = $controller->getRequest();
			/* @var $request Mage_Core_Controller_Request_Http */
			$response = $controller->getResponse();
			/* @var $response Mage_Core_Controller_Response_Http */
			$session = Mage::getSingleton("udropship/session");
			/* @var $session Zolago_Dropship_Model_Session */
			if($session->isOperatorMode()){
				$operator = $session->getOperator();
				// Apply ACL
				$controllerName = $request->getControllerName();
				$module = $request->getModuleName();
				$action = $request->getActionName();
				// Allow safe actions
				if(in_array($action, $this->_safeActions)){
					return;
				}
				// Check action
				$resourceModuleLevel = $module;
				$resourceControllerLevel = $resourceModuleLevel . "/" . $controllerName;
				$resourceActionLevel = $resourceControllerLevel . "/" . $action;
				$isAllowed = false;
				
				if($session->isAllowed($resourceModuleLevel)){
					$isAllowed = true;
				}
				
				if(!$isAllowed && $session->isAllowed($resourceControllerLevel)){
					$isAllowed = true;
				}
				
				if(!$isAllowed && $session->isAllowed($resourceActionLevel)){
					$isAllowed = true;
				}
				
				// Check operator - pos - order assigment
				if($isAllowed && $controller instanceof Unirgy_DropshipPo_VendorController){
					$isAllowed = $this->_checkForUdpo($request, $operator);
				}
				
				if(!$isAllowed){
					$this->_redirectNotAllowed($response, $request);
				}
			}
		}
	}
	
	/**
	 * @param Mage_Core_Controller_Request_Http $request
	 * @param Zolago_Operator_Model_Operator $operator
	 * @return boolean
	 */
	protected function _checkForUdpo(Mage_Core_Controller_Request_Http $request, 
			Zolago_Operator_Model_Operator $operator) {
		
		$poId = null;
		switch ($request->getActionName()) {
			// Mass actions are prepared via helper::getVendorPoColleciton
			// with operator filter also
			case "udpoDeleteTrack":
			case "addUdpoComment":
			case "shipmentInfo":
			case "udpoInfo":
			case "udpoPost":
				$poId = $request->getParam("id");
			break;
			case "udpoPdf":
				$poId = $request->getParam("udpo_id");
			break;
		}
		if($poId!==null){
			return $operator->isAllowedToPo($poId);
		}
		return true;
	}
	
	/**
	 * @param Mage_Core_Controller_Response_Http $response
	 * @param Mage_Core_Controller_Request_Http $request
	 */
	protected function _redirectNotAllowed(
			Mage_Core_Controller_Response_Http $response, 
			Mage_Core_Controller_Request_Http $request) {

			$request->setDispatched(true);
			// Mage::getSingleton("udropship/session")->addError("No privilages");
			if(!$request->isAjax()){
				$response->setRedirect(Mage::getUrl("udropship/index"));
			}else{
				$response->setBody("Your request has been rejected");
			}
			$response->sendResponse();
			exit;
	}
}