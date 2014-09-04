<?php

require_once Mage::getConfig()->getModuleDir('controllers', 'Unirgy_DropshipVendorAskQuestion') 
		. DS . "CustomerController.php";


class Zolago_DropshipVendorAskQuestion_CustomerController extends Unirgy_DropshipVendorAskQuestion_CustomerController
{
    public function postAction()
    {
        parent::postAction();
		
		// Force redirection if flag setted
		if($this->getRequest()->getParam("redirect_referer")){
			$this->_redirectReferer();
		}

		return $this;
    }
}