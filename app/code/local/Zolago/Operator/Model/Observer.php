<?php
class Zolago_Operator_Model_Observer {


    protected function _checkAllow($session,$name) {
        return $session->isAllowed($name);
    }
    /**
     * @param type $observer
     * @return bool
     */
    public function handleControllerActionPredispatch($observer) {
        $event = $observer->getEvent();
        $controller = $event->getControllerAction();
        /* @var $controller Mage_Core_Controller_Front_Action */

        // Apply only for dropship conntrollers
        if(!($controller instanceof Unirgy_Dropship_Controller_VendorAbstract)) {
            return;
        }
        $request = $controller->getRequest();
        /* @var $request Mage_Core_Controller_Request_Http */
        $response = $controller->getResponse();
        /* @var $response Mage_Core_Controller_Response_Http */
        $session = Mage::getSingleton("udropship/session");
        /* @var $session Zolago_Dropship_Model_Session */
        if(!$session->isOperatorMode()) {
            return;
        }
        $operator = $session->getOperator();
        // Apply ACL
        $controllerName = $request->getControllerName();
        $module = $request->getModuleName();
        $action = $request->getActionName();


        $isAllowed = false;

        // Check resource level
        $resourceModuleLevel = $module;
        if ($this->_checkAllow($session,$module) 
         || $this->_checkAllow($session,$module . "/" . $controllerName)
         || $this->_checkAllow($session,$module . "/" . $controllerName . "/" . $action)) {

            if ($request->getModuleName()=="udpo") {
                $isAllowed = $this->_checkForUdpo($request, $operator);                
            } else {
                $isAllowed = true;
            }
        }

        // Process access-denied if not allowed
        if(!$isAllowed) {
            $this->_redirectNotAllowed($response, $request);
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
            // Mass actions are prepared via Zolago_Po_Helper_Data::getVendorPoColleciton()
            // with operator filter applayed
        case "udpoDeleteTrack":
        case "updatePos":
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
        if($poId!==null) {
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
        if(!$request->isAjax()) {
            $response->setRedirect(Mage::getUrl("udropship/vendor/dashboard"));
            //$response->setBody("ACL Denied");
        } else {
            $response->setHeader("content-type", "application/json");
            $response->setBody(Zend_Json::encode(array("status"=>0, "message"=>"ACL Denied")));
        }
        $response->sendResponse();
        exit;
    }
}