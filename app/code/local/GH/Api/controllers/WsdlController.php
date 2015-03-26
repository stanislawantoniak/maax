<?php
/**
 * display wsdl
 */
class GH_Api_WsdlController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
        $this->getResponse()	
            ->setHeader('Content-type','text/xml',true);
    }
    public function testAction() {
        $this->indexAction();
    }
}